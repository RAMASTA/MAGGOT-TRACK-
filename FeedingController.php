<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Feeding;
use App\Support\Fmt;
use App\Support\Monthly;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedingController extends LogController
{
    protected string $model = Feeding::class;
    protected string $route = 'feedings';
    protected string $title = 'Pencatatan Pakan';
    protected string $dateField = 'feeding_date';

    protected function rules(): array
    {
        return [
            'batch_id' => $this->batchRule(),
            'feeding_date' => ['required', 'date', 'before_or_equal:today'],
            'feed_type' => ['required', 'string', 'max:100'],
            'feed_weight' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'waste_source' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'batch_id', 'type' => 'batch', 'label' => 'Batch', 'required' => true],
            ['name' => 'feeding_date', 'type' => 'date', 'label' => 'Tanggal pemberian', 'required' => true, 'default' => today()->toDateString()],
            ['name' => 'feed_type', 'type' => 'text', 'label' => 'Jenis pakan', 'required' => true, 'placeholder' => 'Contoh: Sisa sayur & buah',
                'list' => ['Sisa sayur & buah', 'Limbah dapur campuran', 'Ampas tahu', 'Ampas kelapa', 'Dedak', 'Lainnya']],
            ['name' => 'feed_weight', 'type' => 'number', 'label' => 'Berat pakan', 'unit' => 'kg', 'step' => '0.01', 'min' => '0.01', 'required' => true],
            ['name' => 'waste_source', 'type' => 'text', 'label' => 'Sumber sampah organik', 'placeholder' => 'Contoh: Pasar, rumah makan, dapur rumah', 'full' => true],
            ['name' => 'notes', 'type' => 'textarea', 'label' => 'Catatan'],
        ];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Tanggal', 'key' => 'feeding_date', 'type' => 'date'],
            ['label' => 'Batch', 'key' => 'batch.batch_name'],
            ['label' => 'Jenis pakan', 'key' => 'feed_type'],
            ['label' => 'Berat', 'key' => 'feed_weight', 'type' => 'number', 'suffix' => ' kg'],
            ['label' => 'Sumber', 'key' => 'waste_source'],
            ['label' => 'Catatan', 'key' => 'notes', 'type' => 'limit'],
        ];
    }

    protected function extra(Request $request): array
    {
        $uid = Auth::id();
        $total = (float) Feeding::forUser($uid)->sum('feed_weight');
        $perBatch = Batch::where('user_id', $uid)->withSum('feedings as feed_total', 'feed_weight')->get()
            ->filter(fn ($b) => (float) $b->feed_total > 0)->sortByDesc('feed_total')->take(8);
        [$labels, $data] = Monthly::series(Feeding::forUser($uid), 'feeding_date', 'feed_weight', today()->subMonths(5)->startOfMonth(), today());

        return [
            'summary' => [
                ['label' => 'Total pakan keseluruhan', 'value' => Fmt::num($total) . ' kg'],
                ['label' => 'Jumlah pencatatan', 'value' => Fmt::num(Feeding::forUser($uid)->count(), 0)],
                ['label' => 'Batch dengan pakan terbanyak', 'value' => $perBatch->first()?->batch_name ?? '—'],
            ],
            'chart' => ['title' => 'Konsumsi pakan bulanan (kg)', 'type' => 'bar', 'labels' => $labels,
                'datasets' => [['label' => 'Pakan (kg)', 'data' => $data]], 'empty' => array_sum($data) == 0],
            'sideList' => [
                'title' => 'Total pakan per batch',
                'items' => $perBatch->map(fn ($b) => [$b->batch_name, Fmt::num($b->feed_total) . ' kg'])->values()->all(),
            ],
        ];
    }
}
