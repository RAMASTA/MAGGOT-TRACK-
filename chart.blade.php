@php
    $cid = 'chart-' . \Illuminate\Support\Str::random(6);
    $palette = ['#14532D', '#84CC16', '#0EA5E9', '#F59E0B', '#A855F7', '#EF4444'];
@endphp
<div class="card p-5">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-1">
        <h3 class="font-semibold">{{ $chart['title'] }}</h3>
        @if (!empty($chart['note']))<span class="text-xs text-slate-400">{{ $chart['note'] }}</span>@endif
    </div>
    @if (!empty($chart['empty']))
        <div class="flex h-56 flex-col items-center justify-center gap-2 text-sm text-slate-400">
            <i data-lucide="bar-chart-3" class="h-8 w-8"></i> Belum ada data untuk ditampilkan.
        </div>
    @else
        <div class="relative h-64"><canvas id="{{ $cid }}"></canvas></div>
        @push('scripts')
            <script>
                (function () {
                    const type = @json($chart['type']);
                    const pal = @json($palette);
                    const sets = @json($chart['datasets']).map((d, i) => {
                        const c = d.color || pal[i % pal.length];
                        return Object.assign({ borderColor: c, backgroundColor: type === 'bar' ? c : c + '33', tension: 0.3, borderWidth: 2, pointRadius: 3, borderRadius: 6 }, d);
                    });
                    new Chart(document.getElementById(@json($cid)), {
                        type: type,
                        data: { labels: @json($chart['labels'] ?? []), datasets: sets },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: sets.length > 1 } }, scales: @json((object) ($chart['scales'] ?? [])) }
                    });
                })();
            </script>
        @endpush
    @endif
</div>
