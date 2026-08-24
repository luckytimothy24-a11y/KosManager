<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pendapatan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { font-size: 18px; margin: 0; }
        .header p { margin: 5px 0 0; font-size: 11px; color: #666; }
        .meta { margin-bottom: 15px; font-size: 11px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background-color: #1e40af; color: white; padding: 8px 6px; text-align: left; font-size: 11px; }
        td { padding: 6px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .summary { margin-top: 15px; border-top: 2px solid #e5e7eb; padding-top: 10px; }
        .summary h3 { font-size: 13px; margin-bottom: 8px; }
        .summary-item { display: flex; justify-content: space-between; padding: 3px 0; font-size: 11px; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>KosManager - Sistem Manajemen Kos</p>
    </div>
    @if($startDate || $endDate)
        <div class="meta">Periode: {{ $startDate ?? '-' }} s/d {{ $endDate ?? '-' }}</div>
    @endif
    <table>
        <thead>
            <tr>
                <th>No</th><th>Tanggal Bayar</th><th>Penghuni</th><th>Kamar</th><th>Kos</th><th>Nominal</th><th>Metode</th><th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->payment_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $item->penghuni->user->name ?? '-' }}</td>
                    <td>{{ $item->tagihan->kamar->room_number ?? '-' }}</td>
                    <td>{{ $item->tagihan->kamar->kos->name ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                    <td>{{ \PaymentLabels::paymentMethod($item->payment_method) }}</td>
                    <td>{{ \PaymentLabels::verificationLabel($item->verification_status) }}</td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($summary)
        <div class="summary">
            <h3>Ringkasan</h3>
            @foreach($summary as $key => $value)
                <div class="summary-item"><span>{{ $key }}</span><span>{{ $value }}</span></div>
            @endforeach
        </div>
    @endif
</body>
</html>
