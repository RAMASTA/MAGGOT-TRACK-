<?php

namespace App\Http\Controllers;

use App\Models\EnvironmentLog;
use App\Support\Fmt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EnvironmentLogController extends LogController
{
    protected string $model = EnvironmentLog::class;
    protected string $route = 'environment';
    protected string $title = 'Monitoring Lingkungan';
    protected string $dateField = 'measurement_date';

    public const MEDIA = ['Terlalu basah', 'Ideal', 'Terlalu kering'];
    public const ODOR = ['Normal', 'Asam', 'Amonia', 'Busuk menyengat'];

    protected function rules(): array
    {
        return [
            'batch_id' => $this->batchRule(),
            'measurement_date' => ['required', 'date', 'before_or_equal:today'],
            'temperature' => ['required', 'numeric', 'between:-10,80'],
            'humidity' => ['required', 'numeric', 'between:0,100'],
            'media_condition' => ['required', Rule::in(self::MEDIA)],
            'odor_condition' => ['required', Rule::in(self::ODOR)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function fields(): array
    {
        return [
            ['name' => 'batch_id', 'type' => 'batch', 'label' => 'Batch', 'required' => true],
            ['name' => 'measurement_date', 'type' => 'date', 'label' => 'Tanggal pengukuran', 'required' => true, 'default' => today()->toDateString()],
            ['name' => 'temperature', 'type' => 'number', 'label' => 'Suhu', 'unit' => '°C', 'step' => '0.1', 'min' => '-10', 'max' => '80', 'required' => true],
            ['name' => 'humidity', 'type' => 'number', 'label' => 'Kelembapan', 'unit' => '%', 'step' => '0.1', 'min' => '0', 'max' => '100', 'required' => true],
            ['name' => 'media_condition', 'type' => 'select', 'label' => 'Kondisi media', 'required' => true, 'options' => array_combine(self::MEDIA, self::MEDIA), 'default' => 'Ideal'],
            ['name' => 'odor_condition', 'type' => 'select', 'label' => 'Kondisi bau', 'required' => true, 'options' => array_combine(self::ODOR, self::ODOR), 'default' => 'Normal'],
            ['name' => 'notes', 'type' => 'textarea', 'label' => 'Catatan'],
        ];
    }

    protected function columns(): array
    {
        return [
            ['label' => 'Tanggal', 'key' => 'measurement_date', 'type' => 'date'],
            ['label' => 'Batch', 'key' => 'batch.batch_name'],
            ['label' => 'Suhu', 'key' => 'temperature', 'type' => 'number', 'decimals' => 1, 'suffix' => ' °C'],
            ['label' => 'Kelembapan', 'key' => 'humidity', 'type' => 'number', 'decimals' => 1, 'suffix' => ' %'],
            ['label' => 'Media', 'key' => 'media_condition', 'type' => 'badge'],
            ['label' => 'Bau', 'key' => 'odor_condition', 'type' => 'badge'],
            ['label' => 'Sumber', 'key' => 'source', 'type' => 'badge'],
        ];
    }

    protected function extra(Request $request): array
    {
        $uid = Auth::id();
        $week = EnvironmentLog::forUser($uid)->whereDate('measurement_date', '>=', today()->subDays(6)->toDateString());

        $daily = EnvironmentLog::forUser($uid)
            ->when($request->filled('batch_id'), fn ($q) => $q->where('batch_id', $request->batch_id))
            ->whereDate('measurement_date', '>=', today()->subDays(13)->toDateString())
            ->selectRaw('measurement_date as d, AVG(temperature) as t, AVG(humidity) as h')
            ->groupBy('d')->orderBy('d')->get();

        return [
            'summary' => [
                ['label' => 'Rata-rata suhu (7 hari)', 'value' => Fmt::num((clone $week)->avg('temperature') ?? 0, 1) . ' °C'],
                ['label' => 'Rata-rata kelembapan (7 hari)', 'value' => Fmt::num((clone $week)->avg('humidity') ?? 0, 1) . ' %'],
                ['label' => 'Jumlah pencatatan', 'value' => Fmt::num(EnvironmentLog::forUser($uid)->count(), 0)],
            ],
            'chart' => [
                'title' => 'Suhu & kelembapan harian (14 hari)',
                'type' => 'line',
                'labels' => $daily->map(fn ($r) => \Illuminate\Support\Carbon::parse($r->d)->translatedFormat('d M'))->all(),
                'datasets' => [
                    ['label' => 'Suhu (°C)', 'data' => $daily->map(fn ($r) => round((float) $r->t, 1))->all()],
                    ['label' => 'Kelembapan (%)', 'data' => $daily->map(fn ($r) => round((float) $r->h, 1))->all(), 'color' => '#0EA5E9'],
                ],
                'empty' => $daily->isEmpty(),
            ],
        ];
    }
}
