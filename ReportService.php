<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Feeding;
use App\Models\Harvest;
use App\Support\Monthly;
use Illuminate\Support\Carbon;

class ReportService
{
    public function build(int $uid, Carbon $from, Carbon $to): array
    {
        $f = $from->toDateString();
        $t = $to->toDateString();

        $feedInRange = fn () => Feeding::forUser($uid)->whereDate('feeding_date', '>=', $f)->whereDate('feeding_date', '<=', $t);
        $harvInRange = fn () => Harvest::forUser($uid)->whereDate('harvest_date', '>=', $f)->whereDate('harvest_date', '<=', $t);

        [$labels, $production] = Monthly::series(Harvest::forUser($uid), 'harvest_date', 'harvest_weight', $from, $to);
        [, $feedSeries] = Monthly::series(Feeding::forUser($uid), 'feeding_date', 'feed_weight', $from, $to);

        $perBatch = Batch::where('user_id', $uid)
            ->withSum(['feedings as feed_total' => fn ($q) => $q->whereDate('feeding_date', '>=', $f)->whereDate('feeding_date', '<=', $t)], 'feed_weight')
            ->withSum(['harvests as harvest_total' => fn ($q) => $q->whereDate('harvest_date', '>=', $f)->whereDate('harvest_date', '<=', $t)], 'harvest_weight')
            ->get()
            ->filter(fn ($b) => (float) $b->feed_total > 0 || (float) $b->harvest_total > 0)
            ->map(fn ($b) => [
                'code' => $b->batch_code,
                'name' => $b->batch_name,
                'status' => $b->status_label,
                'feed' => (float) $b->feed_total,
                'harvest' => (float) $b->harvest_total,
                'ratio' => (float) $b->feed_total > 0 ? round((float) $b->harvest_total / (float) $b->feed_total, 3) : null,
            ])->values();

        $feedRecap = $feedInRange()
            ->selectRaw('feed_type, SUM(feed_weight) as total, COUNT(*) as n')
            ->groupBy('feed_type')->orderByDesc('total')->get();

        return [
            'from' => $from,
            'to' => $to,
            'totals' => [
                'harvest' => (float) $harvInRange()->sum('harvest_weight'),
                'feed' => (float) $feedInRange()->sum('feed_weight'),
                'completed' => Batch::where('user_id', $uid)->where('status', 'selesai')
                    ->whereDate('actual_harvest_date', '>=', $f)->whereDate('actual_harvest_date', '<=', $t)->count(),
            ],
            'labels' => $labels,
            'production' => $production,
            'feedSeries' => $feedSeries,
            'perBatch' => $perBatch,
            'feedRecap' => $feedRecap,
        ];
    }
}
