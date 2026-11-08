<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Harvest;
use App\Support\Fmt;
use App\Support\Monthly;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class HarvestController extends LogController
{
    protected string $model = Harvest::class;
    protected string $route = 'harvests';
    protected string $title = 'Hasil Panen';
    protected string $dateField = 'harvest_date';

    public const CONDITIONS = ['Baik', 'Cukup', 'Kurang baik'];

    protected function rules(): array
    {
        return [
            'batch_id' => $this->batchRule(),
            'harvest_date' => ['required', 'date', 'before_or_equal:today'],
            'harvest_weight' => ['required', 'numeric', 'gt:0', 'max:1000000'],
            'harvest_condition' => ['required', Rule::in(self::CONDITIONS)],
            'utilization' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'finish_batch' => ['nullable', 'boolean'],
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'batch_id', 'type' => 'batch', 'label' => 'Batch', 'required' => true],
            ['name' => 'harvest_date', 'type' => 'date', 'label' => 'Tanggal panen', 'required' => true, 'default' => today()->toDateString()],
            ['name' => 'harvest_weight', 'type' => 'number', 'label' => 'Berat hasil panen', 'unit' => 'kg', 'step' => '0.01', 'min' => '0.01', 'required' => true],
            ['name' => 'harvest_condition', 'type' => 'select', 'label' => 'Kondisi hasil panen', 'required' => true, 'options' => array_combine(self::CONDITIONS, self::CONDITIONS), 'default' => 'Baik'],
            ['name' => 'utilization', 'type' => 'text', 'label' => 'Tujuan pemanfaatan / penjualan', 'full' => true, 'placeholder' => 'Contoh: Pakan ikan, dijual ke peternak lele',
                'list' => ['Pakan ikan', 'Pakan unggas', 'Dijual', 'Dikeringkan', 'Dipakai sendiri']],
            ['name' => 'finish_batch', 'type' => 'checkbox', 'label' => 'Tandai batch selesai dan catat tanggal ini sebagai tanggal panen aktual', 'default' => true],
            ['name' => 'notes', 'type' => 'textarea', 'label' => 'Catatan'],
        ];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Tanggal', 'key' => 'harvest_date', 'type' => 'date'],
            ['label' => 'Batch', 'key' => 'batch.batch_name'],
            ['label' => 'Berat', 'key' => 'harvest_weight', 'type' => 'number', 'suffix' => ' kg'],
            ['label' => 'Kondisi', 'key' => 'harvest_condition', 'type' => 'badge'],
            ['label' => 'Pemanfaatan', 'key' => 'utilization'],
            ['label' => 'Catatan', 'key' => 'notes', 'type' => 'limit'],
        ];
    }

    /** Simpan panen + perbarui status batch dalam satu transaksi (dipanggil dari LogController). */
    protected function afterSave($record, Request $request): void
    {
        if ($request->boolean('finish_batch')) {
            Batch::where('id', $record->batch_id)->update([
                'status' => 'selesai',
                'actual_harvest_date' => $record->harvest_date->toDateString(),
            ]);
        }
    }

    protected function extra(Request $request): array
    {
        $uid = Auth::id();
        $total = (float) Harvest::forUser($uid)->sum('harvest_weight');
        $count = Harvest::forUser($uid)->count();
        [$labels, $data] = Monthly::series(Harvest::forUser($uid), 'harvest_date', 'harvest_weight', today()->subMonths(5)->startOfMonth(), today());

        return [
            'summary' => [
                ['label' => 'Total hasil panen', 'value' => Fmt::num($total) . ' kg'],
                ['label' => 'Jumlah panen tercatat', 'value' => Fmt::num($count, 0)],
                ['label' => 'Rata-rata per panen', 'value' => $count ? Fmt::num($total / $count) . ' kg' : '0 kg'],
            ],
            'chart' => ['title' => 'Produksi panen bulanan (kg)', 'type' => 'bar', 'labels' => $labels,
                'datasets' => [['label' => 'Panen (kg)', 'data' => $data, 'color' => '#84CC16']], 'empty' => array_sum($data) == 0],
        ];
    }
}
