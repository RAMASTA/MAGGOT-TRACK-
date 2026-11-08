@extends('layouts.app')
@section('title', $batch->batch_name)
@section('content')
@use('App\Support\Fmt')
@php $rd = $batch->readiness; @endphp

<a href="{{ route('batches.index') }}" class="mb-4 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-forest dark:hover:text-lime-400"><i data-lucide="arrow-left" class="h-4 w-4"></i> Semua batch</a>

<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div>
        <div class="flex flex-wrap items-center gap-2">
            <h1 class="text-2xl font-bold">{{ $batch->batch_name }}</h1>
            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ \App\Models\Batch::statusColor($batch->status) }}">{{ $batch->status_label }}</span>
        </div>
        <p class="text-sm text-slate-400">{{ $batch->batch_code }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('batches.edit', $batch->id) }}" class="btn-ghost"><i data-lucide="pencil" class="h-4 w-4"></i> Edit</a>
        <form method="POST" action="{{ route('batches.destroy', $batch->id) }}" data-confirm="Hapus batch ini? Data pakan, pertumbuhan, lingkungan, dan panen terkait ikut disembunyikan.">
            @csrf @method('DELETE')
            <button type="submit" class="btn-ghost !text-red-600"><i data-lucide="trash-2" class="h-4 w-4"></i> Hapus</button>
        </form>
    </div>
</div>

<div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="card p-5"><p class="text-xs font-semibold uppercase text-slate-400">Umur batch</p><p class="mt-2 text-2xl font-extrabold text-forest dark:text-lime-400">{{ $batch->age_days }} hari</p></div>
    <div class="card p-5"><p class="text-xs font-semibold uppercase text-slate-400">Menuju panen</p>
        <p class="mt-2 text-2xl font-extrabold text-forest dark:text-lime-400">@if ($batch->days_left === null) — @elseif ($batch->days_left < 0) +{{ abs($batch->days_left) }} hari @else {{ $batch->days_left }} hari @endif</p>
        <span class="mt-1 inline-block rounded-full px-2 py-0.5 text-xs font-semibold {{ $rd['class'] }}">{{ $rd['label'] }}</span></div>
    <div class="card p-5"><p class="text-xs font-semibold uppercase text-slate-400">Total pakan</p><p class="mt-2 text-2xl font-extrabold text-forest dark:text-lime-400">{{ Fmt::num($feedTotal) }} kg</p></div>
    <div class="card p-5"><p class="text-xs font-semibold uppercase text-slate-400">Total panen</p><p class="mt-2 text-2xl font-extrabold text-forest dark:text-lime-400">{{ Fmt::num($harvestTotal) }} kg</p></div>
</div>

<div class="mb-6 grid gap-4 lg:grid-cols-3">
    <div class="lg:col-span-2">@include('partials.chart', ['chart' => $growthChart])</div>
    <div class="card p-5 text-sm">
        <h3 class="mb-3 font-semibold">Informasi batch</h3>
        <dl class="space-y-2">
            @foreach ([
                ['Tanggal telur', $batch->egg_date->translatedFormat('d M Y')],
                ['Tanggal penetasan', $batch->hatch_date?->translatedFormat('d M Y') ?? '—'],
                ['Populasi awal', $batch->initial_population !== null ? Fmt::num($batch->initial_population, 0) . ' ekor' : '—'],
                ['Metode', config('maggottrack.harvest_methods.' . $batch->harvest_method . '.label') ?? '—'],
                ['Target panen', $batch->target_harvest_date?->translatedFormat('d M Y') ?? '—'],
                ['Panen aktual', $batch->actual_harvest_date?->translatedFormat('d M Y') ?? '—'],
                ['Sampel terakhir', $lastGrowth ? Fmt::num($lastGrowth->sample_weight) . ' g' : '—'],
                ['Lingkungan terakhir', $lastEnv ? Fmt::num($lastEnv->temperature, 1) . '°C / ' . Fmt::num($lastEnv->humidity, 0) . '%' : '—'],
            ] as [$k, $v])
                <div class="flex justify-between gap-3"><dt class="text-slate-400">{{ $k }}</dt><dd class="text-right font-medium">{{ $v }}</dd></div>
            @endforeach
        </dl>
        @if ($batch->notes)<p class="mt-4 rounded-xl bg-slate-50 p-3 text-slate-600 dark:bg-slate-900/40 dark:text-slate-300">{{ $batch->notes }}</p>@endif
    </div>
</div>

<div class="mb-6 flex flex-wrap gap-2">
    <a href="{{ route('feedings.create', ['batch_id' => $batch->id]) }}" class="btn-lime"><i data-lucide="utensils" class="h-4 w-4"></i> Catat pakan</a>
    <a href="{{ route('growth.create', ['batch_id' => $batch->id]) }}" class="btn-lime"><i data-lucide="trending-up" class="h-4 w-4"></i> Catat pertumbuhan</a>
    <a href="{{ route('environment.create', ['batch_id' => $batch->id]) }}" class="btn-lime"><i data-lucide="thermometer" class="h-4 w-4"></i> Catat lingkungan</a>
    <a href="{{ route('harvests.create', ['batch_id' => $batch->id]) }}" class="btn-lime"><i data-lucide="package" class="h-4 w-4"></i> Catat panen</a>
</div>

<div class="mb-6 grid gap-4 lg:grid-cols-2">
    @foreach ([
        ['Pakan terbaru', 'feedings.index', $feedings, fn ($r) => [$r->feeding_date, Fmt::num($r->feed_weight) . ' kg — ' . $r->feed_type]],
        ['Pertumbuhan terbaru', 'growth.index', $growthLogs, fn ($r) => [$r->measurement_date, Fmt::num($r->sample_weight) . ' g · ' . $r->condition]],
        ['Lingkungan terbaru', 'environment.index', $envLogs, fn ($r) => [$r->measurement_date, Fmt::num($r->temperature, 1) . '°C · ' . Fmt::num($r->humidity, 0) . '% · ' . $r->media_condition]],
        ['Panen', 'harvests.index', $harvests, fn ($r) => [$r->harvest_date, Fmt::num($r->harvest_weight) . ' kg · ' . $r->harvest_condition]],
    ] as [$label, $routeName, $items, $fmt])
        <div class="card p-5">
            <div class="mb-2 flex items-center justify-between"><h3 class="font-semibold">{{ $label }}</h3>
                <a href="{{ route($routeName, ['batch_id' => $batch->id]) }}" class="text-xs font-semibold text-forest hover:underline dark:text-lime-400">Lihat semua</a></div>
            @forelse ($items as $it)
                @php [$d, $t] = $fmt($it); @endphp
                <div class="flex justify-between gap-3 border-b border-slate-100 py-2 text-sm last:border-0 dark:border-slate-700"><span>{{ $t }}</span><span class="whitespace-nowrap text-slate-400">{{ $d->translatedFormat('d M') }}</span></div>
            @empty
                <p class="py-6 text-center text-sm text-slate-400">Belum ada catatan.</p>
            @endforelse
        </div>
    @endforeach
</div>

<div class="mb-6 card p-5">
    <h3 class="mb-3 font-semibold">Foto dokumentasi</h3>
    <form method="POST" action="{{ route('batches.photos.store', $batch->id) }}" enctype="multipart/form-data" class="mb-4 flex flex-wrap items-end gap-3">
        @csrf
        <div class="min-w-[14rem] flex-1">
            <label class="label" for="photo">Pilih foto</label>
            <input id="photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" onchange="previewImg(this)" required class="input file:mr-3 file:rounded-lg file:border-0 file:bg-lime-100 file:px-3 file:py-1 file:text-sm file:font-semibold file:text-green-900">
            @error('photo')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="min-w-[12rem] flex-1"><label class="label" for="description">Keterangan</label><input id="description" name="description" class="input" maxlength="200"></div>
        <button type="submit" class="btn-primary"><i data-lucide="upload" class="h-4 w-4"></i> Unggah</button>
        <img id="preview-photo" alt="Pratinjau" class="hidden h-16 rounded-lg object-cover">
    </form>
    @if ($photos->isEmpty())
        <p class="py-6 text-center text-sm text-slate-400">Belum ada foto.</p>
    @else
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($photos as $p)
                <figure class="group relative overflow-hidden rounded-xl border border-slate-100 dark:border-slate-700">
                    <a href="{{ $p->url }}" target="_blank" rel="noopener"><img src="{{ $p->url }}" alt="{{ $p->description }}" loading="lazy" class="h-32 w-full object-cover"></a>
                    <figcaption class="truncate px-2 py-1 text-xs text-slate-500">{{ $p->description ?: $p->created_at->translatedFormat('d M Y') }}</figcaption>
                    <form method="POST" action="{{ route('batches.photos.destroy', [$batch->id, $p->id]) }}" data-confirm="Hapus foto ini?" class="absolute right-1 top-1">
                        @csrf @method('DELETE')
                        <button type="submit" class="flex h-7 w-7 items-center justify-center rounded-lg bg-black/60 text-white hover:bg-red-600"><i data-lucide="trash-2" class="h-3.5 w-3.5"></i></button>
                    </form>
                </figure>
            @endforeach
        </div>
    @endif
</div>

<div class="card p-5">
    <h3 class="mb-3 font-semibold">Riwayat batch</h3>
    @forelse ($history as $h)
        <div class="flex gap-3 border-b border-slate-100 py-2.5 text-sm last:border-0 dark:border-slate-700">
            <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-lime-100 text-forest"><i data-lucide="{{ $h['icon'] }}" class="h-3.5 w-3.5"></i></span>
            <span class="flex-1">{{ $h['text'] }}</span><span class="whitespace-nowrap text-slate-400">{{ $h['at']->translatedFormat('d M Y') }}</span>
        </div>
    @empty
        <p class="py-6 text-center text-sm text-slate-400">Belum ada aktivitas pada batch ini.</p>
    @endforelse
</div>
@endsection
