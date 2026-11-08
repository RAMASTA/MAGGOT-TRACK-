<?php

namespace App\Services;

use App\Models\Batch;
use Illuminate\Support\Collection;

class NotificationService
{
    private array $memo = [];

    /** Notifikasi dihitung dari data (tanpa tabel tambahan). */
    public function for(int $userId): Collection
    {
        return $this->memo[$userId] ??= $this->build($userId);
    }

    private function build(int $userId): Collection
    {
        $items = collect();
        $alert = (int) config('maggottrack.alert_days');

        Batch::where('user_id', $userId)
            ->where('status', '!=', 'selesai')
            ->whereNotNull('target_harvest_date')
            ->whereDate('target_harvest_date', '<=', today()->addDays($alert)->toDateString())
            ->orderBy('target_harvest_date')
            ->get()
            ->each(function (Batch $b) use ($items) {
                $d = $b->days_left;
                $text = $d < 0
                    ? "{$b->batch_name} melewati target panen " . abs($d) . ' hari.'
                    : ($d === 0 ? "{$b->batch_name} dijadwalkan panen hari ini." : "{$b->batch_name} panen dalam {$d} hari.");
                $items->push(['type' => $d < 0 ? 'danger' : 'warning', 'text' => $text, 'url' => route('batches.show', $b->id)]);
            });

        Batch::where('user_id', $userId)
            ->whereIn('status', ['penetasan', 'larva', 'pra_pupa'])
            ->whereDoesntHave('feedings', fn ($q) => $q->whereDate('feeding_date', today()->toDateString()))
            ->get()
            ->each(fn (Batch $b) => $items->push([
                'type' => 'info',
                'text' => "Belum ada catatan pakan hari ini untuk {$b->batch_name}.",
                'url' => route('feedings.create', ['batch_id' => $b->id]),
            ]));

        return $items->take(10)->values();
    }
}
