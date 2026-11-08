<table>
    <tr><td colspan="6"><b>MaggotTrack — Laporan Budidaya</b></td></tr>
    <tr><td colspan="6">Periode {{ $from->format('d/m/Y') }} - {{ $to->format('d/m/Y') }}</td></tr>
    <tr><td>Total produksi maggot (kg)</td><td>{{ $totals['harvest'] }}</td></tr>
    <tr><td>Total pakan digunakan (kg)</td><td>{{ $totals['feed'] }}</td></tr>
    <tr><td>Batch selesai</td><td>{{ $totals['completed'] }}</td></tr>
    <tr><td></td></tr>
    <tr><td><b>Bulan</b></td><td><b>Panen (kg)</b></td><td><b>Pakan (kg)</b></td></tr>
    @foreach ($labels as $i => $l)
        <tr><td>{{ $l }}</td><td>{{ $production[$i] }}</td><td>{{ $feedSeries[$i] }}</td></tr>
    @endforeach
    <tr><td></td></tr>
    <tr><td><b>Kode</b></td><td><b>Batch</b></td><td><b>Status</b></td><td><b>Pakan (kg)</b></td><td><b>Panen (kg)</b></td><td><b>Panen/pakan</b></td></tr>
    @foreach ($perBatch as $b)
        <tr><td>{{ $b['code'] }}</td><td>{{ $b['name'] }}</td><td>{{ $b['status'] }}</td><td>{{ $b['feed'] }}</td><td>{{ $b['harvest'] }}</td><td>{{ $b['ratio'] }}</td></tr>
    @endforeach
    <tr><td></td></tr>
    <tr><td><b>Jenis pakan</b></td><td><b>Pencatatan</b></td><td><b>Total (kg)</b></td></tr>
    @foreach ($feedRecap as $r)
        <tr><td>{{ $r->feed_type }}</td><td>{{ $r->n }}</td><td>{{ $r->total }}</td></tr>
    @endforeach
</table>
