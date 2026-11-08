<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\EnvironmentLog;
use App\Models\Feeding;
use App\Models\GrowthLog;
use App\Models\Harvest;
use App\Support\Fmt;
use App\Support\Monthly;

class DashboardStatsService
{
    public function build(int $uid): array
    {
        $active = fn () => Batch::where('user_id', $uid)->where('status', '!=', 'selesai');

        $stats = [
            'active_batches' => $active()->count(),
            'population' => (int) $active()->sum('initial_population'),
            'feed_total' => (float) Feeding::forUser($uid)->sum('feed_weight'),
            'harvest_total' => (float) Harvest::forUser($uid)->sum('harvest_weight'),
        ];

        $nearHarvest = $active()
            ->whereNotNull('target_harvest_date')
            ->where(fn ($q) => $q->whereDate('target_harvest_date', '<=', today()->addDays(7)->toDateString())
                ->orWhere('status', 'siap_panen'))
            ->orderBy('target_harvest_date')->limit(5)->get();
        $stats['near_harvest'] = $nearHarvest->count();

        $activeBatches = $active()->orderByRaw('target_harvest_date is null')->orderBy('target_harvest_date')->limit(8)->get();

        $from = today()->subMonths(5)->startOfMonth();
        $to = today();
        [$feedLabels, $feedData] = Monthly::series(Feeding::forUser($uid), 'feeding_date', 'feed_weight', $from, $to);
        [$harvLabels, $harvData] = Monthly::series(Harvest::forUser($uid), 'harvest_date', 'harvest_weight', $from, $to);

        return [
            'stats' => $stats,
            'nearHarvest' => $nearHarvest,
            'activeBatches' => $activeBatches,
            'activities' => $this->activities($uid),
            'growthChart' => $this->growthChart($uid),
            'feedChart' => [
                'title' => 'Konsumsi pakan bulanan (kg)', 'type' => 'bar', 'labels' => $feedLabels,
                'datasets' => [['label' => 'Pakan (kg)', 'data' => $feedData]], 'empty' => array_sum($feedData) == 0,
            ],
            'harvestChart' => [
                'title' => 'Hasil panen bulanan (kg)', 'type' => 'bar', 'labels' => $harvLabels,
                'datasets' => [['label' => 'Panen (kg)', 'data' => $harvData, 'color' => '#84CC16']], 'empty' => array_sum($harvData) == 0,
            ],
        ];
    }

    /** Grafik berat sampel per umur untuk hingga 5 batch (atau satu batch bila $batchId diberikan). */
    public function growthChart(int $uid, ?int $batchId = null): array
    {
        $batches = Batch::where('user_id', $uid)
            ->when($batchId, fn ($q) => $q->where('id', $batchId))
            ->whereHas('growthLogs')
            ->with(['growthLogs' => fn ($q) => $q->orderBy('measurement_date')])
            ->orderByDesc('updated_at')->limit(5)->get();

        $datasets = $batches->map(fn (Batch $b) => [
            'label' => $b->batch_name,
            'data' => $b->growthLogs->map(fn ($g) => [
                'x' => max(0, (int) $b->egg_date->diffInDays($g->measurement_date, false)),
                'y' => (float) $g->sample_weight,
            ])->values()->all(),
        ])->values()->all();

        return [
            'title' => 'Berat sampel (g) menurut umur',
            'note' => 'Berat sampel, bukan total biomassa',
            'type' => 'line',
            'labels' => [],
            'datasets' => $datasets,
            'empty' => empty($datasets),
            'scales' => [
                'x' => ['type' => 'linear', 'title' => ['display' => true, 'text' => 'Umur (hari)']],
                'y' => ['beginAtZero' => true],
            ],
        ];
    }

    private function activities(int $uid)
    {
        $items = collect();
        Feeding::forUser($uid)->with('batch')->latest()->limit(5)->get()->each(fn ($f) => $items->push([
            'icon' => 'utensils', 'at' => $f->created_at,
            'text' => 'Pakan ' . Fmt::num($f->feed_weight) . " kg ({$f->feed_type}) — {$f->batch->batch_name}",
        ]));
        GrowthLog::forUser($uid)->with('batch')->latest()->limit(5)->get()->each(fn ($g) => $items->push([
            'icon' => 'trending-up', 'at' => $g->created_at,
            'text' => 'Sampel ' . Fmt::num($g->sample_weight) . " g — {$g->batch->batch_name}",
        ]));
        EnvironmentLog::forUser($uid)->with('batch')->latest()->limit(5)->get()->each(fn ($e) => $items->push([
            'icon' => 'thermometer', 'at' => $e->created_at,
            'text' => 'Lingkungan ' . Fmt::num($e->temperature, 1) . '°C / ' . Fmt::num($e->humidity, 0) . "% — {$e->batch->batch_name}",
        ]));
        Harvest::forUser($uid)->with('batch')->latest()->limit(5)->get()->each(fn ($h) => $items->push([
            'icon' => 'package', 'at' => $h->created_at,
            'text' => 'Panen ' . Fmt::num($h->harvest_weight) . " kg — {$h->batch->batch_name}",
        ]));
        Batch::where('user_id', $uid)->latest()->limit(5)->get()->each(fn ($b) => $items->push([
            'icon' => 'layers', 'at' => $b->created_at, 'text' => "Batch baru: {$b->batch_name}",
        ]));

        return $items->sortByDesc('at')->take(8)->values();
    }
}
