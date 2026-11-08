<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Monthly
{
    public static function ymExpr(string $col): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$col})"
            : "DATE_FORMAT({$col}, '%Y-%m')";
    }

    /** Jumlahkan kolom per bulan. Mengembalikan [labels, data] dengan bulan kosong = 0. */
    public static function series($query, string $dateCol, string $sumCol, Carbon $from, Carbon $to): array
    {
        $rows = $query
            ->whereDate($dateCol, '>=', $from->toDateString())
            ->whereDate($dateCol, '<=', $to->toDateString())
            ->selectRaw(self::ymExpr($dateCol) . " as ym, SUM({$sumCol}) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $labels = [];
        $data = [];
        $cursor = $from->copy()->startOfMonth();
        while ($cursor->lte($to)) {
            $labels[] = $cursor->translatedFormat('M Y');
            $data[] = round((float) ($rows[$cursor->format('Y-m')] ?? 0), 2);
            $cursor->addMonth();
        }
        return [$labels, $data];
    }
}
