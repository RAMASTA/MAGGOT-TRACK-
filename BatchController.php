<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Services\HarvestEstimator;
use App\Support\Fmt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BatchController extends Controller
{
    public function __construct(private HarvestEstimator $estimator) {}

    private function own($id): Batch
    {
        return Batch::where('user_id', Auth::id())->findOrFail($id);
    }

    private function rules(): array
    {
        return [
            'batch_name' => ['required', 'string', 'max:120'],
            'egg_date' => ['required', 'date'],
            'hatch_date' => ['nullable', 'date', 'after_or_equal:egg_date'],
            'initial_population' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'harvest_method' => ['nullable', Rule::in(array_keys(config('maggottrack.harvest_methods')))],
            'target_harvest_date' => ['nullable', 'date', 'after_or_equal:egg_date'],
            'status' => ['required', Rule::in(array_keys(Batch::STATUSES))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function fields(): array
    {
        $methods = collect(config('maggottrack.harvest_methods'))->map(fn ($m) => $m['label'])->all();

        return [
            ['name' => 'batch_name', 'type' => 'text', 'label' => 'Nama batch', 'required' => true, 'full' => true, 'placeholder' => 'Contoh: Batch Dapur Pekan 1'],
            ['name' => 'egg_date', 'type' => 'date', 'label' => 'Tanggal telur', 'required' => true, 'default' => today()->toDateString()],
            ['name' => 'hatch_date', 'type' => 'date', 'label' => 'Tanggal penetasan'],
            ['name' => 'initial_population', 'type' => 'number', 'label' => 'Jumlah awal telur / estimasi larva', 'unit' => 'ekor', 'step' => '1', 'min' => '0'],
            ['name' => 'harvest_method', 'type' => 'select', 'label' => 'Metode budidaya', 'options' => $methods, 'blank' => '— Pilih metode —'],
            ['name' => 'target_harvest_date', 'type' => 'date', 'label' => 'Target tanggal panen',
                'hint' => 'Kosongkan agar dihitung otomatis dari metode budidaya dan data historis.'],
            ['name' => 'status', 'type' => 'select', 'label' => 'Status budidaya', 'required' => true, 'options' => Batch::STATUSES, 'default' => 'persiapan'],
            ['name' => 'notes', 'type' => 'textarea', 'label' => 'Catatan'],
        ];
    }

    public function index(Request $request)
    {
        $request->validate(['q' => 'nullable|string|max:100', 'status' => ['nullable', Rule::in(array_keys(Batch::STATUSES))], 'from' => 'nullable|date', 'to' => 'nullable|date']);

        $rows = Batch::where('user_id', Auth::id())
            ->when($request->filled('q'), function ($q) use ($request) {
                $like = '%' . addcslashes($request->q, '%_\\') . '%';
                $q->where(fn ($w) => $w->where('batch_name', 'like', $like)->orWhere('batch_code', 'like', $like)->orWhere('notes', 'like', $like));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('egg_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('egg_date', '<=', $request->to))
            ->orderByDesc('egg_date')->orderByDesc('id')
            ->paginate(config('maggottrack.per_page'))->withQueryString();

        return view('crud.index', [
            'title' => 'Manajemen Batch',
            'route' => 'batches',
            'showRoute' => 'batches.show',
            'deleteMessage' => 'Hapus batch ini? Data pakan, pertumbuhan, lingkungan, dan panen terkait ikut disembunyikan.',
            'rows' => $rows,
            'createUrl' => route('batches.create'),
            'columns' => [
                ['label' => 'Kode', 'key' => 'batch_code'],
                ['label' => 'Nama batch', 'key' => 'batch_name'],
                ['label' => 'Tgl. telur', 'key' => 'egg_date', 'type' => 'date'],
                ['label' => 'Umur', 'key' => 'age_days', 'type' => 'number', 'decimals' => 0, 'suffix' => ' hari'],
                ['label' => 'Target panen', 'key' => 'target_harvest_date', 'type' => 'date'],
                ['label' => 'Status', 'key' => 'status', 'type' => 'status'],
            ],
            'filters' => [
                ['name' => 'q', 'type' => 'text', 'label' => 'Cari', 'placeholder' => 'Nama, kode, catatan'],
                ['name' => 'status', 'type' => 'select', 'label' => 'Status', 'options' => Batch::STATUSES],
                ['name' => 'from', 'type' => 'date', 'label' => 'Telur dari'],
                ['name' => 'to', 'type' => 'date', 'label' => 'Telur sampai'],
            ],
        ]);
    }

    public function create()
    {
        return view('crud.form', [
            'title' => 'Tambah Batch', 'fields' => $this->fields(), 'record' => null, 'batches' => collect(),
            'action' => route('batches.store'), 'method' => 'POST', 'backUrl' => route('batches.index'), 'hasFile' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $batch = DB::transaction(function () use ($data) {
            if (empty($data['target_harvest_date'])) {
                $data['target_harvest_date'] = $this->estimator
                    ->targetDate(Auth::id(), \Illuminate\Support\Carbon::parse($data['egg_date']), $data['harvest_method'] ?? null)
                    ->toDateString();
            }
            if ($data['status'] === 'selesai') {
                $data['actual_harvest_date'] = today()->toDateString();
            }
            return Batch::create($data + ['user_id' => Auth::id(), 'batch_code' => Batch::generateCode()]);
        });

        return redirect()->route('batches.show', $batch->id)->with('success', "Batch {$batch->batch_code} berhasil dibuat.");
    }

    public function show($id)
    {
        $batch = $this->own($id);
        $growth = $batch->growthLogs()->orderBy('measurement_date')->get();
        $lastEnv = $batch->environmentLogs()->orderByDesc('measurement_date')->orderByDesc('id')->first();
        $feedTotal = (float) $batch->feedings()->sum('feed_weight');
        $harvestTotal = (float) $batch->harvests()->sum('harvest_weight');

        $history = collect();
        $batch->feedings()->latest('feeding_date')->limit(8)->get()->each(fn ($r) => $history->push(['at' => $r->feeding_date, 'icon' => 'utensils', 'text' => 'Pakan ' . Fmt::num($r->feed_weight) . " kg ({$r->feed_type})"]));
        $growth->take(-8)->each(fn ($r) => $history->push(['at' => $r->measurement_date, 'icon' => 'trending-up', 'text' => 'Sampel ' . Fmt::num($r->sample_weight) . ' g, kondisi ' . strtolower($r->condition)]));
        $batch->environmentLogs()->latest('measurement_date')->limit(8)->get()->each(fn ($r) => $history->push(['at' => $r->measurement_date, 'icon' => 'thermometer', 'text' => Fmt::num($r->temperature, 1) . '°C, ' . Fmt::num($r->humidity, 0) . '% — media ' . strtolower($r->media_condition)]));
        $batch->harvests()->latest('harvest_date')->limit(8)->get()->each(fn ($r) => $history->push(['at' => $r->harvest_date, 'icon' => 'package', 'text' => 'Panen ' . Fmt::num($r->harvest_weight) . ' kg']));

        return view('batches.show', [
            'batch' => $batch,
            'feedTotal' => $feedTotal,
            'harvestTotal' => $harvestTotal,
            'lastGrowth' => $growth->last(),
            'lastEnv' => $lastEnv,
            'growthChart' => [
                'title' => 'Perkembangan berat sampel (g)',
                'note' => 'Berat sampel, bukan total biomassa',
                'type' => 'line',
                'labels' => $growth->map(fn ($g) => $g->measurement_date->translatedFormat('d M'))->all(),
                'datasets' => [['label' => 'Berat sampel (g)', 'data' => $growth->pluck('sample_weight')->map(fn ($v) => (float) $v)->all()]],
                'empty' => $growth->isEmpty(),
            ],
            'feedings' => $batch->feedings()->latest('feeding_date')->limit(5)->get(),
            'growthLogs' => $growth->reverse()->take(5)->values(),
            'envLogs' => $batch->environmentLogs()->latest('measurement_date')->limit(5)->get(),
            'harvests' => $batch->harvests()->latest('harvest_date')->limit(5)->get(),
            'photos' => $batch->photos()->latest()->get(),
            'history' => $history->sortByDesc('at')->take(12)->values(),
        ]);
    }

    public function edit($id)
    {
        $record = $this->own($id);

        return view('crud.form', [
            'title' => 'Edit Batch ' . $record->batch_code, 'fields' => $this->fields(), 'record' => $record, 'batches' => collect(),
            'action' => route('batches.update', $record->id), 'method' => 'PUT', 'backUrl' => route('batches.show', $record->id), 'hasFile' => false,
        ]);
    }

    public function update(Request $request, $id)
    {
        $batch = $this->own($id);
        $data = $request->validate($this->rules());

        DB::transaction(function () use ($batch, &$data) {
            if (empty($data['target_harvest_date'])) {
                $data['target_harvest_date'] = $this->estimator
                    ->targetDate(Auth::id(), $batch->egg_date, $data['harvest_method'] ?? null)->toDateString();
            }
            if ($data['status'] === 'selesai') {
                $data['actual_harvest_date'] = $batch->actual_harvest_date?->toDateString() ?? today()->toDateString();
            } else {
                $data['actual_harvest_date'] = null;
            }
            $batch->update($data);
        });

        return redirect()->route('batches.show', $batch->id)->with('success', 'Batch berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->own($id)->delete(); // soft delete: data dapat dipulihkan dari database

        return redirect()->route('batches.index')->with('success', 'Batch berhasil dihapus.');
    }
}
