<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Services\HarvestEstimator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class HarvestScheduleController extends Controller
{
    public function __construct(private HarvestEstimator $estimator) {}

    public function index()
    {
        $uid = Auth::id();
        $batches = Batch::where('user_id', $uid)->where('status', '!=', 'selesai')
            ->orderByRaw('target_harvest_date is null')->orderBy('target_harvest_date')->get();

        $counts = $batches->groupBy(fn ($b) => $b->readiness['key'])->map->count();

        $finished = Batch::where('user_id', $uid)->where('status', 'selesai')->whereNotNull('actual_harvest_date')
            ->orderByDesc('actual_harvest_date')->limit(8)->get();

        return view('schedule.index', [
            'rows' => $batches->map(fn ($b) => ['batch' => $b, 'estimate' => $this->estimator->days($uid, $b->harvest_method)]),
            'counts' => $counts,
            'finished' => $finished,
            'methods' => collect(config('maggottrack.harvest_methods'))->map(fn ($m) => $m['label'])->all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $batch = Batch::where('user_id', Auth::id())->where('status', '!=', 'selesai')->findOrFail($id);

        if ($request->boolean('apply_estimate')) {
            $target = $this->estimator->targetDate(Auth::id(), $batch->egg_date, $batch->harvest_method);
            $batch->update(['target_harvest_date' => $target->toDateString()]);
        } else {
            $data = $request->validate([
                'target_harvest_date' => ['required', 'date', 'after_or_equal:' . $batch->egg_date->toDateString()],
                'harvest_method' => ['nullable', Rule::in(array_keys(config('maggottrack.harvest_methods')))],
            ]);
            $batch->update($data);
        }

        return back()->with('success', 'Jadwal panen diperbarui.');
    }
}
