<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Basis CRUD untuk semua tabel log milik batch (pakan, pertumbuhan, lingkungan, panen).
 * Semua query dibatasi pada batch milik user yang login.
 */
abstract class LogController extends Controller
{
    protected string $model;
    protected string $route;
    protected string $title;
    protected string $dateField;
    /** Field form yang bukan kolom tabel. */
    protected array $nonColumns = ['photo', 'finish_batch'];

    abstract protected function rules(): array;
    abstract protected function fields(): array;
    abstract protected function columns(): array;

    protected function extra(Request $request): array { return []; }
    protected function afterSave($record, Request $request): void {}

    protected function batchRule(): array
    {
        return ['required', Rule::exists('batches', 'id')->where('user_id', Auth::id())->whereNull('deleted_at')];
    }

    protected function userBatches()
    {
        return Batch::where('user_id', Auth::id())
            ->orderByRaw("status = 'selesai'")->orderByDesc('egg_date')
            ->get(['id', 'batch_code', 'batch_name', 'status']);
    }

    protected function find($id)
    {
        return $this->model::forUser(Auth::id())->findOrFail($id);
    }

    protected function filters(): array
    {
        return [
            ['name' => 'batch_id', 'type' => 'select', 'label' => 'Batch',
                'options' => $this->userBatches()->mapWithKeys(fn ($b) => [$b->id => "{$b->batch_code} — {$b->batch_name}"])->all()],
            ['name' => 'from', 'type' => 'date', 'label' => 'Dari tanggal'],
            ['name' => 'to', 'type' => 'date', 'label' => 'Sampai tanggal'],
        ];
    }

    public function index(Request $request)
    {
        $request->validate(['batch_id' => 'nullable|integer', 'from' => 'nullable|date', 'to' => 'nullable|date']);

        $rows = $this->model::forUser(Auth::id())->with('batch')
            ->when($request->filled('batch_id'), fn ($q) => $q->where('batch_id', $request->batch_id))
            ->when($request->filled('from'), fn ($q) => $q->whereDate($this->dateField, '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate($this->dateField, '<=', $request->to))
            ->orderByDesc($this->dateField)->orderByDesc('id')
            ->paginate(config('maggottrack.per_page'))->withQueryString();

        return view('crud.index', array_merge([
            'title' => $this->title,
            'route' => $this->route,
            'rows' => $rows,
            'columns' => $this->columns(),
            'filters' => $this->filters(),
            'createUrl' => route($this->route . '.create', $request->only('batch_id')),
        ], $this->extra($request)));
    }

    public function create() { return $this->form(null); }

    public function edit($id) { return $this->form($this->find($id)); }

    protected function form($record)
    {
        $fields = $this->fields();
        return view('crud.form', [
            'title' => ($record ? 'Edit ' : 'Tambah ') . $this->title,
            'fields' => $fields,
            'record' => $record,
            'batches' => $this->userBatches(),
            'action' => $record ? route($this->route . '.update', $record->id) : route($this->route . '.store'),
            'method' => $record ? 'PUT' : 'POST',
            'backUrl' => route($this->route . '.index'),
            'hasFile' => collect($fields)->contains('type', 'file'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        DB::transaction(function () use ($data, $request) {
            $record = $this->model::create(Arr::except($data, $this->nonColumns));
            $this->afterSave($record, $request);
        });

        return redirect()->route($this->route . '.index')->with('success', $this->title . ' berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $record = $this->find($id);
        $data = $request->validate($this->rules());

        DB::transaction(function () use ($record, $data, $request) {
            $record->update(Arr::except($data, $this->nonColumns));
            $this->afterSave($record, $request);
        });

        return redirect()->route($this->route . '.index')->with('success', $this->title . ' berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->find($id)->delete();

        return redirect()->route($this->route . '.index')->with('success', 'Data berhasil dihapus.');
    }
}
