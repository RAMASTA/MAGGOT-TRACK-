@extends('layouts.app')
@section('title', $title)
@section('content')
@use('App\Support\Fmt')
@php
    $deleteMessage = $deleteMessage ?? 'Hapus data ini? Tindakan ini tidak dapat dibatalkan.';
@endphp

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-bold">{{ $title }}</h1>
    <a href="{{ $createUrl }}" class="btn-primary"><i data-lucide="plus" class="h-4 w-4"></i> Tambah</a>
</div>

@if (!empty($summary))
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        @foreach ($summary as $s)
            <div class="card p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $s['label'] }}</p>
                <p class="mt-2 truncate text-2xl font-extrabold text-forest dark:text-lime-400">{{ $s['value'] }}</p>
            </div>
        @endforeach
    </div>
@endif

@if (!empty($chart) || !empty($sideList))
    <div class="mb-6 grid gap-4 {{ !empty($chart) && !empty($sideList) ? 'lg:grid-cols-3' : '' }}">
        @if (!empty($chart))
            <div class="{{ !empty($sideList) ? 'lg:col-span-2' : '' }}">@include('partials.chart', ['chart' => $chart])</div>
        @endif
        @if (!empty($sideList))
            <div class="card p-5">
                <h3 class="mb-3 font-semibold">{{ $sideList['title'] }}</h3>
                @forelse ($sideList['items'] as [$name, $value])
                    <div class="flex items-center justify-between border-b border-slate-100 py-2 text-sm last:border-0 dark:border-slate-700">
                        <span class="truncate pr-2">{{ $name }}</span><span class="font-semibold">{{ $value }}</span>
                    </div>
                @empty
                    <p class="py-8 text-center text-sm text-slate-400">Belum ada data.</p>
                @endforelse
            </div>
        @endif
    </div>
@endif

<form method="GET" class="card mb-4 flex flex-wrap items-end gap-3 p-4">
    @foreach ($filters as $f)
        <div class="min-w-[10rem] flex-1">
            <label class="label">{{ $f['label'] }}</label>
            @if ($f['type'] === 'select')
                <select name="{{ $f['name'] }}" class="input">
                    <option value="">Semua</option>
                    @foreach ($f['options'] as $val => $text)
                        <option value="{{ $val }}" @selected((string) request($f['name']) === (string) $val)>{{ $text }}</option>
                    @endforeach
                </select>
            @else
                <input type="{{ $f['type'] }}" name="{{ $f['name'] }}" value="{{ request($f['name']) }}" placeholder="{{ $f['placeholder'] ?? '' }}" class="input">
            @endif
        </div>
    @endforeach
    <div class="flex gap-2">
        <button type="submit" class="btn-primary"><i data-lucide="search" class="h-4 w-4"></i> Terapkan</button>
        @if (request()->query())
            <a href="{{ url()->current() }}" class="btn-ghost">Reset</a>
        @endif
    </div>
</form>

<div class="card overflow-hidden">
    @if ($rows->count())
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-900/40 dark:text-slate-400">
                    <tr>
                        @foreach ($columns as $c)<th class="whitespace-nowrap px-4 py-3 font-semibold">{{ $c['label'] }}</th>@endforeach
                        <th class="px-4 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach ($rows as $row)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30">
                            @foreach ($columns as $c)
                                @php $v = data_get($row, $c['key']); $type = $c['type'] ?? 'text'; @endphp
                                <td class="px-4 py-3 align-top">
                                    @if ($type === 'date')
                                        <span class="whitespace-nowrap">{{ $v ? \Illuminate\Support\Carbon::parse($v)->translatedFormat('d M Y') : '—' }}</span>
                                    @elseif ($type === 'number')
                                        <span class="whitespace-nowrap">{{ $v === null ? '—' : Fmt::num($v, $c['decimals'] ?? 2) . ($c['suffix'] ?? '') }}</span>
                                    @elseif ($type === 'status')
                                        <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold {{ \App\Models\Batch::statusColor($v) }}">{{ \App\Models\Batch::STATUSES[$v] ?? $v }}</span>
                                    @elseif ($type === 'badge')
                                        <span class="whitespace-nowrap rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 dark:bg-slate-700 dark:text-slate-200">{{ $v ?: '—' }}</span>
                                    @elseif ($type === 'limit')
                                        <span class="text-slate-500 dark:text-slate-400">{{ $v ? \Illuminate\Support\Str::limit($v, 50) : '—' }}</span>
                                    @else
                                        {{ $v === null || $v === '' ? '—' : $v }}
                                    @endif
                                </td>
                            @endforeach
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                @isset($showRoute)
                                    <a href="{{ route($showRoute, $row->id) }}" class="icon-btn" title="Detail"><i data-lucide="eye" class="h-4 w-4"></i></a>
                                @endisset
                                <a href="{{ route($route . '.edit', $row->id) }}" class="icon-btn" title="Edit"><i data-lucide="pencil" class="h-4 w-4"></i></a>
                                <form method="POST" action="{{ route($route . '.destroy', $row->id) }}" class="inline" data-confirm="{{ $deleteMessage }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="icon-btn hover:!text-red-600" title="Hapus"><i data-lucide="trash-2" class="h-4 w-4"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($rows->hasPages())<div class="border-t border-slate-100 p-4 dark:border-slate-700">{{ $rows->links() }}</div>@endif
    @else
        <div class="flex flex-col items-center gap-3 px-6 py-16 text-center">
            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-lime-100 text-forest"><i data-lucide="inbox" class="h-7 w-7"></i></span>
            <p class="font-semibold">{{ request()->query() ? 'Tidak ada data yang cocok dengan filter.' : 'Belum ada data.' }}</p>
            <p class="text-sm text-slate-400">{{ request()->query() ? 'Ubah atau reset filter untuk melihat data lain.' : 'Mulai dengan menambahkan data pertama Anda.' }}</p>
            <a href="{{ $createUrl }}" class="btn-primary"><i data-lucide="plus" class="h-4 w-4"></i> Tambah data</a>
        </div>
    @endif
</div>
@endsection
