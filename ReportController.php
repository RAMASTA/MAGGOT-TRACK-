<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct(private ReportService $service) {}

    private function data(Request $request): array
    {
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date']);

        $from = $request->filled('from') ? Carbon::parse($request->from) : now()->startOfYear();
        $to = $request->filled('to') ? Carbon::parse($request->to) : now();
        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }

        return $this->service->build(Auth::id(), $from->copy()->startOfDay(), $to->copy()->endOfDay());
    }

    public function index(Request $request)
    {
        $d = $this->data($request);
        $batches = $d['perBatch'];

        return view('reports.index', $d + [
            'productionChart' => ['title' => 'Produksi maggot bulanan (kg)', 'type' => 'bar', 'labels' => $d['labels'],
                'datasets' => [['label' => 'Panen (kg)', 'data' => $d['production'], 'color' => '#84CC16']], 'empty' => array_sum($d['production']) == 0],
            'feedChart' => ['title' => 'Penggunaan pakan bulanan (kg)', 'type' => 'bar', 'labels' => $d['labels'],
                'datasets' => [['label' => 'Pakan (kg)', 'data' => $d['feedSeries']]], 'empty' => array_sum($d['feedSeries']) == 0],
            'compareChart' => ['title' => 'Perbandingan panen antarbatch (kg)', 'type' => 'bar', 'labels' => $batches->pluck('name')->all(),
                'datasets' => [
                    ['label' => 'Panen (kg)', 'data' => $batches->pluck('harvest')->all(), 'color' => '#84CC16'],
                    ['label' => 'Pakan (kg)', 'data' => $batches->pluck('feed')->all(), 'color' => '#14532D'],
                ], 'empty' => $batches->isEmpty()],
        ]);
    }

    public function pdf(Request $request)
    {
        return Pdf::loadView('reports.pdf', $this->data($request))->setPaper('a4')->download('laporan-maggottrack.pdf');
    }

    public function excel(Request $request)
    {
        return Excel::download(new ReportExport($this->data($request)), 'laporan-maggottrack.xlsx');
    }
}
