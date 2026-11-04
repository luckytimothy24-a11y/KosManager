<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kuitansi Pembayaran - {{ $pembayaran->payment_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; font-size: 11px; color: #333; line-height: 1.5; }
        
        .receipt { max-width: 100%; padding: 20px; }
        
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #a88468; padding-bottom: 15px; }
        .header h1 { font-size: 16px; color: #a88468; margin-bottom: 2px; letter-spacing: 1px; }
        .header h2 { font-size: 11px; color: #666; font-weight: normal; }
        .header .brand { font-size: 18px; font-weight: 800; color: #574136; margin-bottom: 4px; }
        
        .receipt-meta { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 10px; color: #666; }
        
        .section { margin-bottom: 15px; }
        .section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #a88468; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; margin-bottom: 8px; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 20px; }
        .info-row { display: flex; justify-content: space-between; padding: 3px 0; }
        .info-label { color: #666; }
        .info-value { font-weight: 600; color: #333; text-align: right; }
        
        .breakdown { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .breakdown td { padding: 4px 0; }
        .breakdown .label { color: #666; }
        .breakdown .amount { text-align: right; font-weight: 600; }
        .breakdown .total-row { border-top: 2px solid #a88468; font-size: 13px; }
        .breakdown .total-row td { padding-top: 8px; font-weight: 800; color: #574136; }
        .breakdown .discount { color: #16a34a; }
        .breakdown .penalty { color: #dc2626; }
        
        .terbilang { margin-top: 8px; padding: 8px 10px; background: #f8f6f4; border-radius: 4px; font-style: italic; font-size: 10px; color: #666; }
        
        .verification { margin-top: 15px; padding-top: 10px; border-top: 1px dashed #d1d5db; font-size: 10px; color: #666; }
        
        .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 9px; color: #999; }
        .footer .line { width: 60px; height: 1px; background: #a88468; margin: 8px auto; }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="brand">KosManager</div>
            <h1>KUITANSI PEMBAYARAN</h1>
            <h2>Bukti Pembayaran Resmi</h2>
        </div>

        <div class="receipt-meta">
            <span>No. Kuitansi: <strong>{{ $pembayaran->payment_number }}</strong></span>
            <span>Dicetak: {{ now()->translatedFormat('d M Y H:i') }}</span>
        </div>

        <div class="section">
            <div class="section-title">Data Penyewa</div>
            <div class="info-grid">
                <div class="info-row">
                    <span class="info-label">Nama</span>
                    <span class="info-value">{{ $pembayaran->penghuni?->user?->name ?? $pembayaran->booking?->user?->name ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Kamar</span>
                    <span class="info-value">{{ $pembayaran->tagihan?->kamar?->room_number ?? $pembayaran->booking?->kamar?->room_number ?? '-' }} &mdash; {{ $pembayaran->tagihan?->kamar?->room_name ?? $pembayaran->booking?->kamar?->room_name ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Kos</span>
                    <span class="info-value">{{ $pembayaran->tagihan?->kamar?->kos?->name ?? $pembayaran->booking?->kos?->name ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Alamat Kos</span>
                    <span class="info-value">{{ $pembayaran->tagihan?->kamar?->kos?->address ?? $pembayaran->booking?->kos?->address ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Detail Pembayaran</div>
            <div class="info-grid">
                <div class="info-row">
                    <span class="info-label">No. Pembayaran</span>
                    <span class="info-value">{{ $pembayaran->payment_number }}</span>
                </div>
                @if($pembayaran->isBookingPayment() && $pembayaran->booking)
                    <div class="info-row">
                        <span class="info-label">Kode Booking</span>
                        <span class="info-value">{{ $pembayaran->booking->booking_code }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Jenis</span>
                        <span class="info-value">Pembayaran Booking</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Periode</span>
                        <span class="info-value">{{ $pembayaran->booking->start_date?->translatedFormat('d M Y') ?? '-' }} &ndash; {{ $pembayaran->booking->end_date?->translatedFormat('d M Y') ?? '-' }}</span>
                    </div>
                @else
                    <div class="info-row">
                        <span class="info-label">No. Tagihan</span>
                        <span class="info-value">{{ $pembayaran->tagihan?->bill_number ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Jenis Tagihan</span>
                        <span class="info-value">{{ $pembayaran->tagihan?->bill_type ? \PaymentLabels::billType($pembayaran->tagihan->bill_type) : '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Periode</span>
                        <span class="info-value">{{ $pembayaran->tagihan?->period_start?->translatedFormat('d M Y') ?? '-' }} &ndash; {{ $pembayaran->tagihan?->period_end?->translatedFormat('d M Y') ?? '-' }}</span>
                    </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Metode Bayar</span>
                    <span class="info-value">{{ \PaymentLabels::paymentMethod($pembayaran->payment_method) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal Bayar</span>
                    <span class="info-value">{{ $pembayaran->payment_date?->translatedFormat('d M Y') ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Rincian Biaya</div>
            <table class="breakdown">
                @if($pembayaran->tagihan)
                    <tr>
                        <td class="label">Subtotal</td>
                        <td class="amount">Rp {{ number_format((float) ($pembayaran->tagihan->subtotal ?? 0), 0, ',', '.') }}</td>
                    </tr>
                    @if(($pembayaran->tagihan->discount ?? 0) > 0)
                        <tr>
                            <td class="label">Diskon</td>
                            <td class="amount discount">&minus; Rp {{ number_format((float) $pembayaran->tagihan->discount, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if(($pembayaran->tagihan->penalty ?? 0) > 0)
                        <tr>
                            <td class="label">Denda Keterlambatan</td>
                            <td class="amount penalty">+ Rp {{ number_format((float) $pembayaran->tagihan->penalty, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                @else
                    <tr>
                        <td class="label">Pembayaran Booking</td>
                        <td class="amount">Rp {{ number_format((float) ($pembayaran->booking?->price ?? $pembayaran->amount), 0, ',', '.') }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td>TOTAL DIBAYARKAN</td>
                    <td class="amount">Rp {{ number_format($pembayaran->amount, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="terbilang">
            Terbilang: <strong>{{ \App\Support\NumberToWords::toWords((int) $pembayaran->amount) }} rupiah</strong>
        </div>

        <div class="verification">
            <div class="info-row">
                <span class="info-label">Diverifikasi oleh</span>
                <span class="info-value">{{ $pembayaran->verifier->name ?? 'Sistem' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Pada</span>
                <span class="info-value">{{ $pembayaran->verified_at?->translatedFormat('d M Y H:i') ?? '-' }}</span>
            </div>
        </div>

        @php
            $qrData = 'KOSMANAGER|KUITANSI|' . $pembayaran->payment_number . '|' . $pembayaran->amount;
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($qrData) . '&size=120x120';
        @endphp
        <div style="text-align: center; margin-top: 15px;">
            <img src="{{ $qrUrl }}" alt="QR Kuitansi" style="width: 80px; height: 80px; margin: 0 auto;">
            <p style="font-size: 8px; color: #999; margin-top: 4px;">Scan untuk verifikasi kuitansi</p>
        </div>

        <div class="footer">
            <div class="line"></div>
            <p>Kuitansi ini sah dan diterbitkan oleh sistem KosManager.</p>
            <p>Simpan kuitansi ini sebagai bukti pembayaran Anda.</p>
            <p style="margin-top: 5px;">{{ config('app.name') }} &copy; {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>
