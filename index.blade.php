@extends('layouts.app')
@section('title', 'Pencarian')
@section('content')
<h1 class="mb-1 text-2xl font-bold">Hasil pencarian</h1>
<p class="mb-6 text-sm text-slate-500">@if (mb_strlen($term) < 2) Ketik minimal 2 karakter pada kolom pencarian di atas. @else Kata kunci: “{{ $term }}” @endif</p>

@if (mb_strlen($term) >= 2)
    @if ($batches->isEmpty() && $feedings->isEmpty() && $harvests->isEmpty())
        <div class="card flex flex-col items-center gap-2 px-6 py-16 text-center text-slate-400"><i data-lucide="search-x" class="h-8 w-8"></i> Tidak ada hasil yang cocok.</div>
    @endif
    <div class="space-y-6">
        @if ($batches->isNotEmpty())
            <div class="card p-5"><h2 class="mb-2 font-semibold">Batch</h2>
                @foreach ($batches as $b)
                    <a href="{{ route('batches.show', $b->id) }}" class="flex justify-between gap-3 border-b border-slate-100 py-2 text-sm last:border-0 hover:text-forest dark:border-slate-700"><span>{{ $b->batch_name }}</span><span class="text-slate-400">{{ $b->batch_code }}</span></a>
                @endforeach</div>
        @endif
        @if ($feedings->isNotEmpty())
            <div class="card p-5"><h2 class="mb-2 font-semibold">Pakan</h2>
                @foreach ($feedings as $f)
                    <a href="{{ route('feedings.edit', $f->id) }}" class="flex justify-between gap-3 border-b border-slate-100 py-2 text-sm last:border-0 hover:text-forest dark:border-slate-700"><span>{{ $f->feed_type }} — {{ $f->batch->batch_name }}</span><span class="text-slate-400">{{ $f->feeding_date->translatedFormat('d M Y') }}</span></a>
                @endforeach</div>
        @endif
        @if ($harvests->isNotEmpty())
            <div class="card p-5"><h2 class="mb-2 font-semibold">Panen</h2>
                @foreach ($harvests as $h)
                    <a href="{{ route('harvests.edit', $h->id) }}" class="flex justify-between gap-3 border-b border-slate-100 py-2 text-sm last:border-0 hover:text-forest dark:border-slate-700"><span>{{ $h->utilization ?: 'Panen' }} — {{ $h->batch->batch_name }}</span><span class="text-slate-400">{{ $h->harvest_date->translatedFormat('d M Y') }}</span></a>
                @endforeach</div>
        @endif
    </div>
@endif
@endsection
