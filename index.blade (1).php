@extends('layouts.app')
@section('title', 'Jadwal Panen')
@section('content')
<div class="mb-6"><h1 class="text-2xl font-bold">Jadwal & Estimasi Panen</h1>
    <p class="text-sm text-slate-500">Estimasi memakai rata-rata batch selesai pada metode yang sama; jika datanya belum cukup, dipakai default di <code>config/maggottrack.php</code>.</p></div>

<div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ([['ready', 'Siap panen', 'text-green-800 dark:text-lime-400'], ['soon', 'Mendekati panen', 'text-amber-600'], ['overdue', 'Terlambat', 'text-red-600'], ['growing', 'Dalam pertumbuhan', 'text-forest dark:text-lime-400']] as [$k, $l, $c])
        <div class="card p-5"><p class="text-xs font-semibold uppercase text-slate-400">{{ $l }}</p><p class="mt-2 text-3xl font-extrabold {{ $c }}">{{ $counts[$k] ?? 0 }}</p></div>
    @endforeach
</div>

<div class="card mb-6 overflow-hidden">
    @if ($rows->isEmpty())
        <div class="flex flex-col items-center gap-3 px-6 py-16 text-center">
            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-lime-100 text-forest"><i data-lucide="calendar-days" class="h-7 w-7"></i></span>
            <p class="font-semibold">Belum ada batch aktif untuk dijadwalkan.</p>
            <a href="{{ route('batches.create') }}" class="btn-primary">Buat batch</a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-900/40">
                    <tr><th class="px-4 py-3">Batch</th><th class="px-4 py-3">Umur</th><th class="px-4 py-3">Target panen</th><th class="px-4 py-3">Sisa</th><th class="px-4 py-3">Kesiapan</th><th class="px-4 py-3">Estimasi</th><th class="px-4 py-3">Atur jadwal</th><th class="px-4 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach ($rows as $r)
                        @php $b = $r['batch']; $rd = $b->readiness; $e = $r['estimate']; @endphp
                        <tr class="align-top">
                            <td class="px-4 py-3"><a href="{{ route('batches.show', $b->id) }}" class="font-semibold hover:underline">{{ $b->batch_name }}</a><p class="text-xs text-slate-400">{{ $b->batch_code }}</p></td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $b->age_days }} hari</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $b->target_harvest_date?->translatedFormat('d M Y') ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3">@if ($b->days_left === null) — @elseif ($b->days_left < 0) −{{ abs($b->days_left) }} hari @else {{ $b->days_left }} hari @endif</td>
                            <td class="px-4 py-3"><span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold {{ $rd['class'] }}">{{ $rd['label'] }}</span></td>
                            <td class="px-4 py-3 text-xs text-slate-500"><p class="font-semibold text-slate-700 dark:text-slate-200">{{ $e['days'] }} hari</p>{{ $e['source'] }}<br>
                                <form method="POST" action="{{ route('schedule.update', $b->id) }}" class="mt-1">@csrf @method('PATCH')<input type="hidden" name="apply_estimate" value="1"><button type="submit" class="font-semibold text-forest underline dark:text-lime-400">Terapkan estimasi</button></form></td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('schedule.update', $b->id) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf @method('PATCH')
                                    <input type="date" name="target_harvest_date" value="{{ $b->target_harvest_date?->format('Y-m-d') }}" required class="input !w-40">
                                    <select name="harvest_method" class="input !w-44"><option value="">— Metode —</option>@foreach ($methods as $k => $label)<option value="{{ $k }}" @selected($b->harvest_method === $k)>{{ $label }}</option>@endforeach</select>
                                    <button type="submit" class="btn-ghost !px-3">Simpan</button>
                                </form>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right"><a href="{{ route('harvests.create', ['batch_id' => $b->id]) }}" class="btn-lime !px-3">Catat panen</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="card p-5">
    <h3 class="mb-3 font-semibold">Riwayat durasi budidaya (batch selesai)</h3>
    @forelse ($finished as $b)
        <div class="flex justify-between gap-3 border-b border-slate-100 py-2 text-sm last:border-0 dark:border-slate-700">
            <span>{{ $b->batch_name }} <span class="text-xs text-slate-400">· {{ config('maggottrack.harvest_methods.' . $b->harvest_method . '.label') ?? 'metode tidak diisi' }}</span></span>
            <span class="whitespace-nowrap font-semibold">{{ $b->age_days }} hari <span class="font-normal text-slate-400">({{ $b->actual_harvest_date->translatedFormat('d M Y') }})</span></span>
        </div>
    @empty
        <p class="py-6 text-center text-sm text-slate-400">Belum ada batch selesai. Data historis akan terbentuk setelah panen dicatat.</p>
    @endforelse
</div>
@endsection
