<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Gabungan</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #1f2937; }
        .header { text-align: center; margin-bottom: 18px; border-bottom: 2px solid #111827; padding-bottom: 10px; }
        .header h1 { font-size: 20px; margin: 0; }
        .header p { margin: 4px 0 0; font-size: 11px; color: #6b7280; }
        .meta { margin-bottom: 14px; font-size: 11px; color: #4b5563; text-align: center; }
        .section { margin-bottom: 22px; }
        .section-title { font-size: 14px; font-weight: 700; color: #111827; margin: 0 0 6px; border-left: 4px solid #1e40af; padding-left: 8px; }
        .section-desc { font-size: 10px; color: #6b7280; margin: 0 0 8px 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        th { background-color: #1e40af; color: white; padding: 6px 6px; text-align: left; font-size: 10px; }
        td { padding: 5px 6px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .summary { margin-top: 6px; border-top: 1px solid #e5e7eb; padding-top: 6px; }
        .summary h4 { font-size: 11px; margin: 0 0 6px; }
        .summary-item { display: flex; justify-content: space-between; padding: 2px 0; font-size: 10px; }
        .text-right { text-align: right; }
        .page-break { page-break-after: always; }
        .badge { display: inline-block; font-size: 9px; padding: 2px 6px; border-radius: 9999px; background: #eef2ff; color: #3730a3; }
        .empty { text-align: center; color: #9ca3af; padding: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Gabungan</h1>
        <p>KosManager — Rekap operasional &amp; keuangan</p>
        <p style="font-size:10px; color:#6b7280;">Pendapatan · Penghuni · Booking · Kamar · Tagihan — dalam 1 file</p>
    </div>

    <div class="meta">
        @if($startDate || $endDate)
            Periode: {{ $startDate ?? '-' }} s/d {{ $endDate ?? '-' }} &nbsp;|&nbsp;
        @endif
        @if($kosName)
            Kos: {{ $kosName }}
        @else
            Kos: Semua Kos
        @endif
        &nbsp;|&nbsp; Dicetak: {{ now()->format('d/m/Y H:i') }}
    </div>

    @foreach($sections as $idx => $sec)
        <div class="section {{ $idx < count($sections)-1 ? 'page-break' : '' }}">
            <div class="section-title">{{ $loop->iteration }}. {{ $sec['title'] }}</div>
            <p class="section-desc">{{ $sec['desc'] }}</p>

            <table>
                <thead>
                    <tr>
                        @foreach($sec['headers'] as $h)
                            <th>{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($sec['rows'] as $row)
                        <tr>
                            @foreach($row as $cell)
                                <td @if(is_numeric(str_replace(['Rp ','. ',','], '', $cell)) && str_contains($cell, 'Rp')) class="text-right" @endif>{{ $cell }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($sec['headers']) }}" class="empty">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
            </table>

            @if(!empty($sec['summary']))
                <div class="summary">
                    <h4>Ringkasan {{ $sec['title'] }}</h4>
                    @foreach($sec['summary'] as $k => $v)
                        <div class="summary-item"><span>{{ $k }}</span><span>{{ $v }}</span></div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</body>
</html>
