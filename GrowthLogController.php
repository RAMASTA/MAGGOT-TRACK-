<?php

namespace App\Http\Controllers;

use App\Models\BatchPhoto;
use App\Models\GrowthLog;
use App\Services\DashboardStatsService;
use App\Support\Fmt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class GrowthLogController extends LogController
{
    protected string $model = GrowthLog::class;
    protected string $route = 'growth';
    protected string $title = 'Monitoring Pertumbuhan';
    protected string $dateField = 'measurement_date';

    public const CONDITIONS = ['Sangat baik', 'Baik', 'Cukup', 'Kurang baik'];

    protected function rules(): array
    {
        return [
            'batch_id' => $this->batchRule(),
            'measurement_date' => ['required', 'date', 'before_or_equal:today'],
            'sample_weight' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'average_length' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'condition' => ['required', Rule::in(self::CONDITIONS)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'batch_id', 'type' => 'batch', 'label' => 'Batch', 'required' => true],
            ['name' => 'measurement_date', 'type' => 'date', 'label' => 'Tanggal pengukuran', 'required' => true, 'default' => today()->toDateString()],
            ['name' => 'sample_weight', 'type' => 'number', 'label' => 'Berat sampel', 'unit' => 'gram', 'step' => '0.01', 'min' => '0.01', 'required' => true,
                'hint' => 'Berat sampel yang ditimbang, bukan total biomassa batch.'],
            ['name' => 'average_length', 'type' => 'number', 'label' => 'Panjang rata-rata', 'unit' => 'mm', 'step' => '0.01', 'min' => '0'],
            ['name' => 'condition', 'type' => 'select', 'label' => 'Kondisi maggot', 'required' => true, 'options' => array_combine(self::CONDITIONS, self::CONDITIONS), 'default' => 'Baik'],
            ['name' => 'photo', 'type' => 'file', 'label' => 'Foto dokumentasi (opsional)'],
            ['name' => 'notes', 'type' => 'textarea', 'label' => 'Catatan perkembangan'],
        ];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Tanggal', 'key' => 'measurement_date', 'type' => 'date'],
            ['label' => 'Batch', 'key' => 'batch.batch_name'],
            ['label' => 'Umur', 'key' => 'age_days', 'type' => 'number', 'decimals' => 0, 'suffix' => ' hari'],
            ['label' => 'Berat sampel', 'key' => 'sample_weight', 'type' => 'number', 'suffix' => ' g'],
            ['label' => 'Panjang', 'key' => 'average_length', 'type' => 'number', 'suffix' => ' mm'],
            ['label' => 'Kondisi', 'key' => 'condition', 'type' => 'badge'],
            ['label' => 'Catatan', 'key' => 'notes', 'type' => 'limit'],
        ];
    }

    protected function extra(Request $request): array
    {
        $uid = Auth::id();
        $latest = GrowthLog::forUser($uid)->orderByDesc('measurement_date')->orderByDesc('id')->first();

        return [
            'summary' => [
                ['label' => 'Jumlah pengukuran', 'value' => Fmt::num(GrowthLog::forUser($uid)->count(), 0)],
                ['label' => 'Berat sampel terakhir', 'value' => $latest ? Fmt::num($latest->sample_weight) . ' g' : '—'],
                ['label' => 'Rata-rata berat sampel', 'value' => Fmt::num(GrowthLog::forUser($uid)->avg('sample_weight') ?? 0) . ' g'],
            ],
            'chart' => app(DashboardStatsService::class)->growthChart($uid, $request->integer('batch_id') ?: null),
        ];
    }

    protected function afterSave($record, Request $request): void
    {
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('batches/' . $record->batch_id, 'public');
            BatchPhoto::create([
                'batch_id' => $record->batch_id,
                'growth_log_id' => $record->id,
                'image_path' => $path,
                'description' => 'Pengukuran ' . $record->measurement_date->format('d/m/Y'),
            ]);
        }
    }
}
