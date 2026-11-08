@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ $backUrl }}" class="mb-4 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-forest dark:hover:text-lime-400"><i data-lucide="arrow-left" class="h-4 w-4"></i> Kembali</a>
    <h1 class="mb-6 text-2xl font-bold">{{ $title }}</h1>

    <form method="POST" action="{{ $action }}" @if ($hasFile) enctype="multipart/form-data" @endif class="card space-y-5 p-6">
        @csrf
        @if ($method !== 'POST') @method($method) @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-200">
                <p class="font-semibold">Periksa kembali isian Anda:</p>
                <ul class="mt-1 list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($fields as $f)
                @php
                    $name = $f['name'];
                    $type = $f['type'] ?? 'text';
                    $val = old($name, $record ? ($record->{$name} ?? null) : ($f['default'] ?? request($name)));
                    if ($val instanceof \DateTimeInterface) { $val = $val->format('Y-m-d'); }
                    $wide = in_array($type, ['textarea', 'checkbox', 'file']) || ($f['full'] ?? false);
                    $err = $errors->has($name) ? 'border-red-500' : '';
                @endphp
                <div class="{{ $wide ? 'sm:col-span-2' : '' }}">
                    @if ($type !== 'checkbox')
                        <label class="label" for="f-{{ $name }}">{{ $f['label'] }} @if (!empty($f['required']))<span class="text-red-500">*</span>@endif</label>
                    @endif

                    @if ($type === 'batch')
                        @if ($batches->isEmpty())
                            <p class="rounded-xl bg-amber-50 p-3 text-sm text-amber-800 dark:bg-amber-900/20 dark:text-amber-200">Belum ada batch. <a href="{{ route('batches.create') }}" class="font-semibold underline">Buat batch dulu</a>.</p>
                        @else
                            <select id="f-{{ $name }}" name="{{ $name }}" class="input {{ $err }}" required>
                                <option value="">— Pilih batch —</option>
                                @foreach ($batches as $b)
                                    <option value="{{ $b->id }}" @selected((string) $val === (string) $b->id)>{{ $b->batch_code }} — {{ $b->batch_name }}{{ $b->status === 'selesai' ? ' (selesai)' : '' }}</option>
                                @endforeach
                            </select>
                        @endif
                    @elseif ($type === 'select')
                        <select id="f-{{ $name }}" name="{{ $name }}" class="input {{ $err }}" @if (!empty($f['required'])) required @endif>
                            @if (isset($f['blank']))<option value="">{{ $f['blank'] }}</option>@endif
                            @foreach ($f['options'] as $k => $text)
                                <option value="{{ $k }}" @selected((string) $val === (string) $k)>{{ $text }}</option>
                            @endforeach
                        </select>
                    @elseif ($type === 'textarea')
                        <textarea id="f-{{ $name }}" name="{{ $name }}" rows="3" class="input {{ $err }}">{{ $val }}</textarea>
                    @elseif ($type === 'checkbox')
                        <input type="hidden" name="{{ $name }}" value="0">
                        <label class="flex items-start gap-2 text-sm">
                            <input type="checkbox" name="{{ $name }}" value="1" @checked($val) class="mt-0.5 rounded border-slate-300 text-forest focus:ring-lime-500">
                            <span>{{ $f['label'] }}</span>
                        </label>
                    @elseif ($type === 'file')
                        <input id="f-{{ $name }}" type="file" name="{{ $name }}" accept="image/jpeg,image/png,image/webp" onchange="previewImg(this)" class="input {{ $err }} file:mr-3 file:rounded-lg file:border-0 file:bg-lime-100 file:px-3 file:py-1 file:text-sm file:font-semibold file:text-green-900">
                        <img id="preview-{{ $name }}" alt="Pratinjau" class="mt-2 hidden h-32 rounded-xl object-cover">
                        <p class="mt-1 text-xs text-slate-400">JPG, PNG, atau WEBP, maksimal 4 MB.</p>
                    @else
                        <div class="flex">
                            <input id="f-{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ $val }}"
                                   @if (!empty($f['required'])) required @endif
                                   @isset($f['step']) step="{{ $f['step'] }}" @endisset
                                   @isset($f['min']) min="{{ $f['min'] }}" @endisset
                                   @isset($f['max']) max="{{ $f['max'] }}" @endisset
                                   @isset($f['placeholder']) placeholder="{{ $f['placeholder'] }}" @endisset
                                   @isset($f['list']) list="dl-{{ $name }}" @endisset
                                   class="input {{ $err }} {{ isset($f['unit']) ? 'rounded-r-none' : '' }}">
                            @isset($f['unit'])
                                <span class="inline-flex items-center rounded-r-xl border border-l-0 border-slate-300 bg-slate-50 px-3 text-sm text-slate-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300">{{ $f['unit'] }}</span>
                            @endisset
                        </div>
                        @isset($f['list'])
                            <datalist id="dl-{{ $name }}">@foreach ($f['list'] as $opt)<option value="{{ $opt }}">@endforeach</datalist>
                        @endisset
                    @endif

                    @isset($f['hint'])<p class="mt-1 text-xs text-slate-400">{{ $f['hint'] }}</p>@endisset
                    @error($name)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            @endforeach
        </div>

        <div class="flex justify-end gap-2 border-t border-slate-100 pt-4 dark:border-slate-700">
            <a href="{{ $backUrl }}" class="btn-ghost">Batal</a>
            <button type="submit" class="btn-primary">Simpan</button>
        </div>
    </form>
</div>
@endsection
