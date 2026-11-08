<?php

namespace App\Services;

use App\Models\Batch;
use Illuminate\Support\Carbon;

class HarvestEstimator
{
    /**
     * Estimasi lama budidaya (hari dari tanggal telur) per metode.
     * Memakai rata-rata batch selesai milik user bila datanya cukup,
     * jika tidak memakai default dari config/maggottrack.php.
     */
    public function days(int $userId, ?string $method): array
    {
        $default = (int) (config("maggottrack.harvest_methods.{$method}.days") ?? config('maggottrack.default_harvest_days'));

        $rows = Batch::where('user_id', $userId)
            ->where('status', 'selesai')
            ->whereNotNull('actual_harvest_date')
            ->when($method, fn ($q) => $q->where('harvest_method', $method))
            ->get(['egg_date', 'actual_harvest_date']);

        if ($rows->count() >= (int) config('maggottrack.min_history_samples')) {
            $avg = (int) round($rows->avg(fn ($b) => $b->egg_date->diffInDays($b->actual_harvest_date, false)));
            if ($avg > 0) {
                return ['days' => $avg, 'source' => 'rata-rata ' . $rows->count() . ' batch selesai'];
            }
        }

        return ['days' => $default, 'source' => 'default metode'];
    }

    public function targetDate(int $userId, Carbon $egg, ?string $method): Carbon
    {
        return $egg->copy()->addDays($this->days($userId, $method)['days']);
    }
}
