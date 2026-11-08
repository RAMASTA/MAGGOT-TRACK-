<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1E293B; }
    h1 { color: #14532D; font-size: 20px; margin: 0; }
    h2 { color: #14532D; font-size: 13px; margin: 18px 0 6px; }
    .muted { color: #64748B; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #14532D; color: #fff; text-align: left; padding: 6px; font-size: 10px; }
    td { padding: 6px; border-bottom: 1px solid #E2E8F0; }
    .r { text-align: right; }
    .box { width: 32%; display: inline-block; border: 1px solid #E2E8F0; border-radius: 6px; padding: 8px; margin-right: 1%; }
    .big { font-size: 16px; font-weight: bold; color: #14532D; }
</style>
</head>
<body>
<h1>MaggotTrack — Laporan Budidaya</h1>
<p class="muted">Periode {{ $from->translatedFormat('d M Y') }} – {{ $to->translatedFormat('d M Y') }} · Dicetak {{ now()->translatedFormat('d M Y H:i') }} · {{ auth()->user()->name }}</p>

<div style="margin-top:12px">
    <div class="box"><div class="muted">Total produksi maggot</div><div class="big">{{ \App\Support\Fmt::num($totals['harvest']) }} kg</div></div>
    <div class="box"><div class="muted">Total pakan digunakan</div><div class="big">{{ \App\Support\Fmt::num($totals['feed']) }} kg</div></div>
    <div class="box"><div class="muted">Batch selesai</div><div class="big">{{ $totals['completed'] }}</div></div>
</div>

<h2>Produksi dan pakan bulanan</h2>
<table>
    <tr><th>Bulan</th><th class="r">Panen (kg)</th><th class="r">Pakan (kg)</th></tr>
    @foreach ($labels as $i => $l)
        <tr><td>{{ $l }}</td><td class="r">{{ \App\Support\Fmt::num($production[$i]) }}</td><td class="r">{{ \App\Support\Fmt::num($feedSeries[$i]) }}</td></tr>
    @endforeach
</table>

<h2>Perbandingan antarbatch</h2>
<table>
    <tr><th>Kode</th><th>Batch</th><th>Status</th><th class="r">Pakan (kg)</th><th class="r">Panen (kg)</th><th class="r">Panen/pakan</th></tr>
    @forelse ($perBatch as $b)
        <tr><td>{{ $b['code'] }}</td><td>{{ $b['name'] }}</td><td>{{ $b['status'] }}</td><td class="r">{{ \App\Support\Fmt::num($b['feed']) }}</td><td class="r">{{ \App\Support\Fmt::num($b['harvest']) }}</td><td class="r">{{ $b['ratio'] === null ? '-' : \App\Support\Fmt::num($b['ratio'], 3) }}</td></tr>
    @empty
        <tr><td colspan="6" class="muted">Belum ada data pada periode ini.</td></tr>
    @endforelse
</table>

<h2>Rekap penggunaan pakan</h2>
<table>
    <tr><th>Jenis pakan</th><th class="r">Jumlah pencatatan</th><th class="r">Total (kg)</th></tr>
    @forelse ($feedRecap as $r)
        <tr><td>{{ $r->feed_type }}</td><td class="r">{{ $r->n }}</td><td class="r">{{ \App\Support\Fmt::num($r->total) }}</td></tr>
    @empty
        <tr><td colspan="3" class="muted">Belum ada data pada periode ini.</td></tr>
    @endforelse
</table>
</body>
</html>
