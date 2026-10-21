<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Laporan pribadi tenant: ringkasan tagihan & pembayaran miliknya
 * sendiri. TIDAK mengekspos revenue owner, data tenant lain, atau
 * statistik global — hanya agregat atas penghuni milik user login.
 */
class TenantLaporanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $penghuniIds = $user->penghunis()->pluck('id');

        $byStatus = Tagihan::whereIn('penghuni_id', $penghuniIds)
            ->selectRaw('status, COUNT(*) as count, COALESCE(SUM(total), 0) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = Tagihan::whereIn('penghuni_id', $penghuniIds)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $stats = [
            'tagihan_unpaid' => (int) ($counts->get(Tagihan::STATUS_UNPAID, 0)),
            'tagihan_overdue' => (int) ($counts->get(Tagihan::STATUS_OVERDUE, 0)),
            'tagihan_pending' => (int) ($counts->get(Tagihan::STATUS_PAYMENT_PENDING, 0)),
            'tagihan_paid' => (int) ($counts->get(Tagihan::STATUS_PAID, 0)),
            'total_belum_dibayar' => (float) Tagihan::whereIn('penghuni_id', $penghuniIds)->payable()->sum('total'),
            'total_sudah_dibayar' => (float) Pembayaran::whereIn('penghuni_id', $penghuniIds)
                ->where('verification_status', Pembayaran::STATUS_APPROVED)->sum('amount'),
        ];

        $monthExpr = match (DB::connection()->getDriverName()) {
            'mysql' => "DATE_FORMAT(payment_date, '%Y-%m')",
            'pgsql' => "TO_CHAR(payment_date::date, 'YYYY-MM')",
            default => "strftime('%Y-%m', payment_date)",
        };

        $monthlyPaid = Pembayaran::whereIn('penghuni_id', $penghuniIds)
            ->where('verification_status', Pembayaran::STATUS_APPROVED)
            ->where('payment_date', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("{$monthExpr} as month, COALESCE(SUM(amount), 0) as total")
            ->groupBy('month')
            ->pluck('total', 'month');

        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthly[] = [
                'label' => $month->translatedFormat('M Y'),
                'total' => (float) ($monthlyPaid[$month->format('Y-m')] ?? 0),
            ];
        }

        $recentTagihan = Tagihan::with(['kamar.kos'])
            ->whereIn('penghuni_id', $penghuniIds)
            ->latest()
            ->limit(5)
            ->get();

        $recentPembayaran = Pembayaran::with(['tagihan'])
            ->whereIn('penghuni_id', $penghuniIds)
            ->latest()
            ->limit(5)
            ->get();

        return view('tenant.laporan.index', compact('stats', 'monthly', 'recentTagihan', 'recentPembayaran'));
    }
}
