<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\CheckIn;
use App\Models\CheckOut;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        return match ($user->role) {
            'super_admin' => $this->superAdmin($user),
            'admin' => $this->admin($user),
            'owner' => $this->owner($user),
            'tenant' => $this->tenant($user),
            default => redirect()->route('login'),
        };
    }

    private function superAdmin($user)
    {
        $stats = [
            'total_users' => User::count(),
            'total_owners' => User::where('role', 'owner')->count(),
            'total_tenants' => User::where('role', 'tenant')->count(),
            'total_kos' => Kos::count(),
            'total_kamar' => Kamar::count(),
            'total_kamar_available' => Kamar::where('status', 'available')->count(),
            'total_kamar_occupied' => Kamar::where('status', 'occupied')->count(),
            'total_bookings' => Booking::count(),
            'total_pending_bookings' => Booking::where('status', 'pending')->count(),
            'total_penghunis' => Penghuni::where('status', 'active')->count(),
            'total_revenue' => Pembayaran::where('verification_status', 'approved')->sum('amount'),
        ];

        $revenueChart = self::monthlySeries(
            Pembayaran::where('verification_status', 'approved')
                ->whereBetween('payment_date', [now()->subMonths(5)->startOfMonth(), now()->endOfMonth()])
                ->get()
                ->groupBy(fn ($p) => $p->payment_date->format('Y-m'))
                ->map(fn ($g) => (int) $g->sum('amount'))
        );

        $bookingChart = self::monthlySeries(
            Booking::whereBetween('booking_date', [now()->subMonths(5)->startOfMonth(), now()->endOfMonth()])
                ->get()
                ->groupBy(fn ($b) => $b->booking_date->format('Y-m'))
                ->map(fn ($g) => $g->count())
        );

        return view('dashboard.super-admin', compact('stats', 'user', 'revenueChart', 'bookingChart'));
    }

    private function admin($user)
    {
        $kosIds = $user->assignedKos()->pluck('kos.id');

        $stats = [
            'pending_bookings' => Booking::where('status', 'pending')
                ->whereIn('kos_id', $kosIds)->count(),
            'pending_payments' => Pembayaran::where('verification_status', 'pending')
                ->whereHas('penghuni', fn ($q) => $q->whereIn('kos_id', $kosIds))->count(),
            'checkin_today' => CheckIn::whereDate('check_in_date', now()->toDateString())
                ->whereHas('kamar', fn ($q) => $q->whereIn('kos_id', $kosIds))->count(),
            'checkout_today' => CheckOut::whereDate('request_date', now()->toDateString())
                ->whereHas('kamar', fn ($q) => $q->whereIn('kos_id', $kosIds))->count(),
            'kamar_available' => Kamar::where('status', 'available')
                ->whereIn('kos_id', $kosIds)->count(),
            'kamar_occupied' => Kamar::where('status', 'occupied')
                ->whereIn('kos_id', $kosIds)->count(),
            'tagihan_overdue' => Tagihan::where('status', 'overdue')
                ->whereHas('kamar', fn ($q) => $q->whereIn('kos_id', $kosIds))->count(),
            'total_penghunis' => Penghuni::where('status', 'active')
                ->whereIn('kos_id', $kosIds)->count(),
        ];

        return view('dashboard.admin', compact('stats', 'user'));
    }

    private function owner($user)
    {
        $stats = [
            'total_kos' => Kos::where('owner_id', $user->id)->count(),
            'total_kamar' => Kamar::whereHas('kos', fn ($q) => $q->where('owner_id', $user->id))->count(),
            'total_kamar_available' => Kamar::where('status', 'available')->whereHas('kos', fn ($q) => $q->where('owner_id', $user->id))->count(),
            'total_kamar_occupied' => Kamar::where('status', 'occupied')->whereHas('kos', fn ($q) => $q->where('owner_id', $user->id))->count(),
            'total_kamar_maintenance' => Kamar::where('status', 'maintenance')->whereHas('kos', fn ($q) => $q->where('owner_id', $user->id))->count(),
            'total_pending_bookings' => Booking::whereHas('kos', fn ($q) => $q->where('owner_id', $user->id))->where('status', 'pending')->count(),
            'pending_payments' => Pembayaran::where('verification_status', 'pending')->whereHas('penghuni.kos', fn ($q) => $q->where('owner_id', $user->id))->count(),
            'total_penghunis' => Penghuni::whereHas('kos', fn ($q) => $q->where('owner_id', $user->id))->where('status', 'active')->count(),
            'total_revenue' => Pembayaran::where('verification_status', 'approved')->whereHas('penghuni.kos', fn ($q) => $q->where('owner_id', $user->id))->sum('amount'),
        ];

        $revenueChart = self::monthlySeries(
            Pembayaran::where('verification_status', 'approved')
                ->whereHas('penghuni.kos', fn ($q) => $q->where('owner_id', $user->id))
                ->whereBetween('payment_date', [now()->subMonths(5)->startOfMonth(), now()->endOfMonth()])
                ->get()
                ->groupBy(fn ($p) => $p->payment_date->format('Y-m'))
                ->map(fn ($g) => (int) $g->sum('amount'))
        );

        $totalKamar = max(1, $stats['total_kamar']);
        $stats['occupancy_percent'] = (int) round(($stats['total_kamar_occupied'] / $totalKamar) * 100);

        return view('dashboard.owner', compact('stats', 'user', 'revenueChart'));
    }

    private function tenant($user)
    {
        $penghuni = Penghuni::where('user_id', $user->id)->where('status', 'active')->first();
        $stats = [];
        $pendingCheckout = false;

        if ($penghuni) {
            $pendingCheckout = $penghuni->checkOuts()->where('status', 'pending')->exists();
            $kontrak = $penghuni->kontraks()->where('status', 'active')->first();

            $nearestDue = $penghuni->tagihans()
                ->whereIn('status', ['unpaid', 'overdue', 'pending_verification'])
                ->orderBy('due_date')
                ->value('due_date');

            $lastPayment = $penghuni->pembayarans()
                ->where('verification_status', 'approved')
                ->latest('payment_date')
                ->first();

            $stats = [
                'kos_name' => $penghuni->kos->name,
                'kamar_number' => $penghuni->kamar->room_number,
                'kontrak_status' => $kontrak?->status ?? '-',
                'kontrak_end' => $kontrak?->end_date,
                'tagihan_pending' => $penghuni->tagihans()->where('status', '!=', 'paid')->count(),
                'tagihan_overdue' => $penghuni->tagihans()->where('status', 'overdue')->count(),
                'tagihan_pending_verification' => $penghuni->tagihans()->where('status', 'pending_verification')->count(),
                'total_belum_dibayar' => (float) $penghuni->tagihans()->whereIn('status', ['unpaid', 'overdue'])->sum('total'),
                'nearest_due' => $nearestDue,
                'last_payment_amount' => $lastPayment?->amount,
                'last_payment_date' => $lastPayment?->payment_date,
            ];
        }

        return view('dashboard.tenant', compact('stats', 'user', 'penghuni', 'pendingCheckout'));
    }

    /**
     * Susun seri 6 bulan terakhir (label singkat + nilai, urut kronologis).
     */
    private static function monthlySeries($grouped): array
    {
        $labels = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->translatedFormat('M');
            $values[] = $grouped[$key] ?? 0;
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
