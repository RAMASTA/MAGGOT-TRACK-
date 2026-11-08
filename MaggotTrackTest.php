<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Feeding;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MaggotTrackTest extends TestCase
{
    use RefreshDatabase;

    private function makeBatch(User $u, array $o = []): Batch
    {
        return Batch::create($o + [
            'user_id' => $u->id,
            'batch_code' => Batch::generateCode(),
            'batch_name' => 'Batch Uji',
            'egg_date' => today()->subDays(10)->toDateString(),
            'target_harvest_date' => today()->addDays(5)->toDateString(),
            'status' => 'larva',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/batches')->assertRedirect('/login');
    }

    public function test_register_hashes_password_and_logs_in(): void
    {
        $this->post('/register', [
            'name' => 'Budi', 'email' => 'budi@example.com',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ])->assertRedirect(route('dashboard'));

        $user = User::firstOrFail();
        $this->assertNotSame('rahasia123', $user->password);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $u = User::factory()->create(['password' => 'benar-benar123']);
        $this->post('/login', ['email' => $u->email, 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_dashboard_shows_empty_state_for_new_user(): void
    {
        $this->actingAs(User::factory()->create())->get('/')
            ->assertOk()->assertSee('Belum ada batch aktif');
    }

    public function test_batch_gets_code_and_estimated_target_date(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->post('/batches', [
            'batch_name' => 'Batch A', 'egg_date' => today()->toDateString(),
            'status' => 'telur', 'harvest_method' => 'standar',
        ])->assertRedirect();

        $b = Batch::firstOrFail();
        $this->assertStringStartsWith('BSF-', $b->batch_code);
        $this->assertSame(today()->addDays(20)->toDateString(), $b->target_harvest_date->toDateString());
    }

    public function test_batch_age_and_days_left(): void
    {
        $b = $this->makeBatch(User::factory()->create());
        $this->assertSame(10, $b->age_days);
        $this->assertSame(5, $b->days_left);
    }

    public function test_user_cannot_open_or_delete_other_users_batch(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $b = $this->makeBatch($owner);

        $this->actingAs($intruder)->get(route('batches.show', $b->id))->assertNotFound();
        $this->actingAs($intruder)->delete(route('batches.destroy', $b->id))->assertNotFound();
        $this->assertNotSoftDeleted($b);
    }

    public function test_feeding_cannot_target_foreign_batch(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $b = $this->makeBatch($owner);

        $this->actingAs($intruder)->post('/feedings', [
            'batch_id' => $b->id, 'feeding_date' => today()->toDateString(),
            'feed_type' => 'Sisa sayur', 'feed_weight' => 5,
        ])->assertSessionHasErrors('batch_id');
        $this->assertDatabaseCount('feedings', 0);
    }

    public function test_feeding_crud_flow(): void
    {
        $u = User::factory()->create();
        $b = $this->makeBatch($u);

        $this->actingAs($u)->post('/feedings', [
            'batch_id' => $b->id, 'feeding_date' => today()->toDateString(),
            'feed_type' => 'Sisa sayur', 'feed_weight' => 12.5,
        ])->assertRedirect(route('feedings.index'));
        $f = Feeding::firstOrFail();

        $this->get('/feedings')->assertOk()->assertSee('12,5');

        $this->put(route('feedings.update', $f->id), [
            'batch_id' => $b->id, 'feeding_date' => today()->toDateString(),
            'feed_type' => 'Ampas tahu', 'feed_weight' => 3,
        ])->assertRedirect();
        $this->assertSame('Ampas tahu', $f->fresh()->feed_type);

        $this->delete(route('feedings.destroy', $f->id))->assertRedirect();
        $this->assertDatabaseCount('feedings', 0);
    }

    public function test_feeding_validation_rejects_negative_weight(): void
    {
        $u = User::factory()->create();
        $b = $this->makeBatch($u);
        $this->actingAs($u)->post('/feedings', [
            'batch_id' => $b->id, 'feeding_date' => today()->toDateString(),
            'feed_type' => 'X', 'feed_weight' => -1,
        ])->assertSessionHasErrors('feed_weight');
    }

    public function test_harvest_marks_batch_as_finished(): void
    {
        $u = User::factory()->create();
        $b = $this->makeBatch($u);

        $this->actingAs($u)->post('/harvests', [
            'batch_id' => $b->id, 'harvest_date' => today()->toDateString(),
            'harvest_weight' => 8.5, 'harvest_condition' => 'Baik', 'finish_batch' => '1',
        ])->assertRedirect();

        $b->refresh();
        $this->assertSame('selesai', $b->status);
        $this->assertSame(today()->toDateString(), $b->actual_harvest_date->toDateString());
    }

    public function test_growth_log_and_environment_log_are_saved(): void
    {
        $u = User::factory()->create();
        $b = $this->makeBatch($u);

        $this->actingAs($u)->post('/growth', [
            'batch_id' => $b->id, 'measurement_date' => today()->toDateString(),
            'sample_weight' => 0.35, 'condition' => 'Baik',
        ])->assertRedirect();
        $this->post('/environment', [
            'batch_id' => $b->id, 'measurement_date' => today()->toDateString(),
            'temperature' => 29.5, 'humidity' => 70, 'media_condition' => 'Ideal', 'odor_condition' => 'Normal',
        ])->assertRedirect();

        $this->assertDatabaseCount('growth_logs', 1);
        $this->assertDatabaseCount('environment_logs', 1);
        $this->get('/growth')->assertOk();
        $this->get('/environment')->assertOk();
    }

    public function test_schedule_search_and_report_pages_load(): void
    {
        $u = User::factory()->create();
        $this->makeBatch($u, ['batch_name' => 'Batch Cari']);

        $this->actingAs($u);
        $this->get('/schedule')->assertOk()->assertSee('Batch Cari');
        $this->get('/search?q=Cari')->assertOk()->assertSee('Batch Cari');
        $this->get('/reports')->assertOk();
        $this->get('/profile')->assertOk();
    }

    public function test_report_exports_download(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/reports/pdf')->assertOk();
        $this->get('/reports/excel')->assertOk();
    }
}
