@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@use('App\Support\Fmt')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold">Halo, {{ \Illuminate\Support\Str::of(auth()->user()->name)->before(' ') }}</h1>
        <p class="text-sm text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>
    <a href="{{ route('batches.create') }}" class="btn-primary"><i data-lucide="plus" class="h-4 w-4"></i> Batch baru</a>
</div>

<div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
    @foreach ([
        ['Batch aktif', Fmt::num($stats['active_batches'], 0), 'layers'],
        ['Estimasi populasi', Fmt::num($stats['population'], 0), 'bug'],
        ['Total pakan', Fmt::num($stats['feed_total']) . ' kg', 'utensils'],
        ['Total hasil panen', Fmt::num($stats['harvest_total']) . ' kg', 'package'],
        ['Mendekati panen', Fmt::num($stats['near_harvest'], 0), 'calendar-days'],
    ] as [$label, $value, $icon])
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</p>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-lime-100 text-forest"><i data-lucide="{{ $icon }}" class="h-4 w-4"></i></span>
            </div>
            <p class="mt-3 text-2xl font-extrabold text-forest dark:text-lime-400">{{ $value }}</p>
        </div>
    @endforeach
</div>
<p class="-mt-4 mb-6 text-xs text-slate-400">Estimasi populasi = jumlah awal telur/larva pada batch yang masih aktif.</p>

<div class="mb-6 grid gap-4 lg:grid-cols-3">
    <div class="lg:col-span-2">@include('partials.chart', ['chart' => $growthChart])</div>
    <div class="card p-5">
        <h3 class="mb-3 flex items-center gap-2 font-semibold"><i data-lucide="bell" class="h-4 w-4"></i> Notifikasi jadwal</h3>
        @forelse ($notifications as $n)
            <a href="{{ $n['url'] }}" class="flex gap-3 border-b border-slate-100 py-2.5 text-sm last:border-0 hover:text-forest dark:border-slate-700">
                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ ['danger' => 'bg-red-500', 'warning' => 'bg-amber-500', 'info' => 'bg-sky-500'][$n['type']] }}"></span>{{ $n['text'] }}
            </a>
        @empty
            <p class="py-10 text-center text-sm text-slate-400">Tidak ada jadwal yang perlu diperhatikan.</p>
        @endforelse
    </div>
</div>

<div class="mb-6 grid gap-4 lg:grid-cols-2">
    @include('partials.chart', ['chart' => $feedChart])
    @include('partials.chart', ['chart' => $harvestChart])
</div>

<div class="grid gap-4 lg:grid-cols-3">
    <div class="card overflow-hidden lg:col-span-2">
        <div class="flex items-center justify-between p-5 pb-3">
            <h3 class="font-semibold">Batch aktif</h3>
            <a href="{{ route('batches.index') }}" class="text-xs font-semibold text-forest hover:underline dark:text-lime-400">Lihat semua</a>
        </div>
        @if ($activeBatches->isEmpty())
            <div class="flex flex-col items-center gap-2 px-6 pb-10 pt-4 text-center text-sm text-slate-400">
                <i data-lucide="layers" class="h-8 w-8"></i> Belum ada batch aktif.
                <a href="{{ route('batches.create') }}" class="btn-primary mt-2">Buat batch pertama</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach ($activeBatches as $b)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30">
                                <td class="px-5 py-3"><a href="{{ route('batches.show', $b->id) }}" class="font-semibold hover:underline">{{ $b->batch_name }}</a><p class="text-xs text-slate-400">{{ $b->batch_code }}</p></td>
                                <td class="whitespace-nowrap px-3 py-3 text-slate-500">{{ $b->age_days }} hari</td>
                                <td class="whitespace-nowrap px-3 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $b->readiness['class'] }}">{{ $b->readiness['label'] }}</span></td>
                                <td class="whitespace-nowrap px-5 py-3 text-right text-slate-500">{{ $b->target_harvest_date?->translatedFormat('d M Y') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="card p-5">
        <h3 class="mb-3 font-semibold">Aktivitas terbaru</h3>
        @forelse ($activities as $a)
            <div class="flex gap-3 border-b border-slate-100 py-2.5 text-sm last:border-0 dark:border-slate-700">
                <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-lime-100 text-forest"><i data-lucide="{{ $a['icon'] }}" class="h-3.5 w-3.5"></i></span>
                <div class="min-w-0"><p class="truncate">{{ $a['text'] }}</p><p class="text-xs text-slate-400">{{ $a['at']->diffForHumans() }}</p></div>
            </div>
        @empty
            <p class="py-10 text-center text-sm text-slate-400">Belum ada aktivitas.</p>
        @endforelse
    </div>
</div>
@endsection
