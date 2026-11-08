@extends('layouts.app')
@section('title', 'Laporan')
@section('content')
@use('App\Support\Fmt')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div><h1 class="text-2xl font-bold">Laporan & Statistik</h1>
        <p class="text-sm text-slate-500">Periode {{ $from->translatedFormat('d M Y') }} – {{ $to->translatedFormat('d M Y') }}</p></div>
    <div class="flex gap-2">
        <a href="{{ route('reports.pdf', request()->query()) }}" class="btn-ghost"><i data-lucide="file-text" class="h-4 w-4"></i> Export PDF</a>
        <a href="{{ route('reports.excel', request()->query()) }}" class="btn-primary"><i data-lucide="download" class="h-4 w-4"></i> Export Excel</a>
    </div>
</div>

<form method="GET" class="card mb-6 flex flex-wrap items-end gap-3 p-4">
    <div><label class="label">Dari tanggal</label><input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="input"></div>
    <div><label class="label">Sampai tanggal</label><input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="input"></div>
    <button type="submit" class="btn-primary"><i data-lucide="search" class="h-4 w-4"></i> Terapkan</button>
    <a href="{{ route('reports.index') }}" class="btn-ghost">Tahun ini</a>
</form>

<div class="mb-6 grid gap-4 sm:grid-cols-3">
    @foreach ([['Total produksi maggot', Fmt::num($totals['harvest']) . ' kg'], ['Total pakan digunakan', Fmt::num($totals['feed']) . ' kg'], ['Batch selesai', Fmt::num($totals['completed'], 0)]] as [$l, $v])
        <div class="card p-5"><p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $l }}</p><p class="mt-2 text-2xl font-extrabold text-forest dark:text-lime-400">{{ $v }}</p></div>
    @endforeach
</div>

<div class="mb-6 grid gap-4 lg:grid-cols-2">
    @include('partials.chart', ['chart' => $productionChart])
    @include('partials.chart', ['chart' => $feedChart])
</div>
<div class="mb-6">@include('partials.chart', ['chart' => $compareChart])</div>

<div class="grid gap-4 lg:grid-cols-3">
    <div class="card overflow-hidden lg:col-span-2">
        <h3 class="p-5 pb-3 font-semibold">Perbandingan antarbatch</h3>
        @if ($perBatch->isEmpty())
            <p class="px-5 pb-10 pt-4 text-center text-sm text-slate-400">Belum ada data pakan atau panen pada periode ini.</p>
        @else
            <div class="overflow-x-auto"><table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500 dark:bg-slate-900/40"><tr><th class="px-4 py-2">Batch</th><th class="px-4 py-2">Status</th><th class="px-4 py-2 text-right">Pakan (kg)</th><th class="px-4 py-2 text-right">Panen (kg)</th><th class="px-4 py-2 text-right">Panen/pakan</th></tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach ($perBatch as $b)
                        <tr><td class="px-4 py-2"><p class="font-semibold">{{ $b['name'] }}</p><p class="text-xs text-slate-400">{{ $b['code'] }}</p></td><td class="px-4 py-2">{{ $b['status'] }}</td>
                            <td class="px-4 py-2 text-right">{{ Fmt::num($b['feed']) }}</td><td class="px-4 py-2 text-right">{{ Fmt::num($b['harvest']) }}</td><td class="px-4 py-2 text-right">{{ $b['ratio'] === null ? '—' : Fmt::num($b['ratio'], 3) }}</td></tr>
                    @endforeach
                </tbody>
            </table></div>
        @endif
    </div>
    <div class="card p-5">
        <h3 class="mb-3 font-semibold">Rekap pakan per jenis</h3>
        @forelse ($feedRecap as $r)
            <div class="flex items-center justify-between border-b border-slate-100 py-2 text-sm last:border-0 dark:border-slate-700"><span>{{ $r->feed_type }} <span class="text-xs text-slate-400">· {{ $r->n }}×</span></span><span class="font-semibold">{{ Fmt::num($r->total) }} kg</span></div>
        @empty
            <p class="py-10 text-center text-sm text-slate-400">Belum ada pakan pada periode ini.</p>
        @endforelse
    </div>
</div>
@endsection
