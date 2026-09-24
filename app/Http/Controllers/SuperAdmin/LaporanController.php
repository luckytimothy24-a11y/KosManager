<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $kosList = $user->isSuperAdmin() ? Kos::all() : ($user->isAdmin() ? $user->assignedKos : Kos::where('owner_id', $user->id)->get());

        $start = $request->start_date;
        $end = $request->end_date;
        $kosId = $request->kos_id ? (int) $request->kos_id : null;
        $kosIds = $this->accessibleKosIds($request);

        if ($kosIds !== null && $kosId !== null && !in_array($kosId, $kosIds, true)) {
            abort(403, 'Anda tidak memiliki akses ke kos ini.');
        }

        $sections = $this->gabunganSections($request);
        $data = [];
        foreach ($sections as $sec) {
            $data[$sec['key']] = ['items' => $sec['rows'], 'summary' => $sec['summary']];
        }

        return view('laporan.kos', compact('kosList', 'data', 'sections', 'start', 'end', 'kosId', 'user'));
    }

    /**
     * ID kos yang boleh diakses user saat ini.
     * Null = semua kos (super admin).
     */
    private function accessibleKosIds(Request $request): ?array
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            return null;
        }

        if ($user->isAdmin()) {
            return $user->assignedKos()->pluck('kos.id')->all();
        }

        return Kos::where('owner_id', $user->id)->pluck('id')->all();
    }

    private function gabunganSections(Request $request): array
    {
        $start = $request->start_date;
        $end = $request->end_date;
        $kosId = $request->kos_id ? (int) $request->kos_id : null;
        $kosIds = $this->accessibleKosIds($request);

        $pendapatan = $this->pendapatanData($start, $end, $kosId, $kosIds);
        $penghuni = $this->penghuniData($kosId, $kosIds);
        $booking = $this->bookingData($start, $end, $kosId, $kosIds);
        $kamar = $this->kamarData($kosId, $kosIds);
        $tagihan = $this->tagihanData($start, $end, $kosId, $kosIds);

        return [
            [
                'key' => 'pendapatan',
                'title' => 'Laporan Pendapatan',
                'desc' => 'Total pendapatan dari seluruh kos',
                'headers' => $this->getHeaders('pendapatan'),
                'rows' => $this->getRows('pendapatan', $pendapatan['items']),
                'summary' => $pendapatan['summary'] ?? [],
            ],
            [
                'key' => 'penghuni',
                'title' => 'Laporan Penghuni',
                'desc' => 'Data penghuni aktif per kos',
                'headers' => $this->getHeaders('penghuni'),
                'rows' => $this->getRows('penghuni', $penghuni['items']),
                'summary' => $penghuni['summary'] ?? [],
            ],
            [
                'key' => 'booking',
                'title' => 'Laporan Booking',
                'desc' => 'Riwayat booking dan statusnya',
                'headers' => $this->getHeaders('booking'),
                'rows' => $this->getRows('booking', $booking['items']),
                'summary' => $booking['summary'] ?? [],
            ],
            [
                'key' => 'kamar',
                'title' => 'Laporan Kamar',
                'desc' => 'Status kamar per kos',
                'headers' => $this->getHeaders('kamar'),
                'rows' => $this->getRows('kamar', $kamar['items']),
                'summary' => $kamar['summary'] ?? [],
            ],
            [
                'key' => 'tagihan',
                'title' => 'Laporan Tagihan',
                'desc' => 'Status tagihan dan pembayaran',
                'headers' => $this->getHeaders('tagihan'),
                'rows' => $this->getRows('tagihan', $tagihan['items']),
                'summary' => $tagihan['summary'] ?? [],
            ],
        ];
    }

    public function exportCombinedPdf(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'kos_id' => 'nullable|exists:kos,id',
        ]);

        $kosId = $request->kos_id ? (int) $request->kos_id : null;
        $kosIds = $this->accessibleKosIds($request);
        if ($kosIds !== null && $kosId !== null && ! in_array($kosId, $kosIds, true)) {
            abort(403, 'Anda tidak memiliki akses ke kos ini.');
        }

        $sections = $this->gabunganSections($request);
        $kosName = $kosId ? Kos::find($kosId)?->name : null;

        $pdf = Pdf::loadView('exports.pdf.gabungan', [
            'sections' => $sections,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
            'kosName' => $kosName,
        ]);

        return $pdf->download('laporan-gabungan.pdf');
    }

    public function exportCombinedExcel(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'kos_id' => 'nullable|exists:kos,id',
        ]);

        $kosId = $request->kos_id ? (int) $request->kos_id : null;
        $kosIds = $this->accessibleKosIds($request);
        if ($kosIds !== null && $kosId !== null && ! in_array($kosId, $kosIds, true)) {
            abort(403, 'Anda tidak memiliki akses ke kos ini.');
        }

        $sections = $this->gabunganSections($request);
        $kosName = $kosId ? Kos::find($kosId)?->name : null;

        $handle = fopen('php://temp', 'r+');
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($handle, ['Laporan Gabungan — KosManager'], ',');
        fputcsv($handle, ['Berisi: Pendapatan, Penghuni, Booking, Kamar, Tagihan (1 file)'], ',');
        if ($request->start_date || $request->end_date) {
            fputcsv($handle, ['Periode: '.($request->start_date ?? '-').' s/d '.($request->end_date ?? '-')], ',');
        }
        fputcsv($handle, ['Kos: '.($kosName ?? 'Semua Kos')], ',');
        fputcsv($handle, ['Dicetak: '.now()->format('d/m/Y H:i')], ',');
        fputcsv($handle, [], ',');

        foreach ($sections as $sec) {
            fputcsv($handle, [$sec['title'].' — '.$sec['desc']], ',');
            fputcsv($handle, $sec['headers'], ',');
            foreach ($sec['rows'] as $row) {
                fputcsv($handle, $row, ',');
            }
            if (empty($sec['rows'])) {
                fputcsv($handle, ['Tidak ada data'], ',');
            }
            if (! empty($sec['summary'])) {
                fputcsv($handle, [], ',');
                fputcsv($handle, ['Ringkasan '.$sec['title']], ',');
                foreach ($sec['summary'] as $k => $v) {
                    fputcsv($handle, [$k, $v], ',');
                }
            }
            fputcsv($handle, [], ',');
            fputcsv($handle, [], ',');
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="laporan-gabungan.csv"');
    }

    private function pendapatanData(?string $start, ?string $end, ?int $kosId, ?array $kosIds): array
    {
        $query = Pembayaran::with(['penghuni.user', 'tagihan.kamar.kos'])->where('verification_status', 'approved');
        $statsQuery = Pembayaran::where('verification_status', 'approved');
        if ($start) { $query->whereDate('payment_date', '>=', $start); $statsQuery->whereDate('payment_date', '>=', $start); }
        if ($end) { $query->whereDate('payment_date', '<=', $end); $statsQuery->whereDate('payment_date', '<=', $end); }
        if ($kosId) { $query->whereHas('penghuni.kos', fn ($q) => $q->where('id', $kosId)); $statsQuery->whereHas('penghuni.kos', fn ($q) => $q->where('id', $kosId)); }
        elseif ($kosIds !== null) { $query->whereHas('penghuni', fn ($q) => $q->whereIn('kos_id', $kosIds)); $statsQuery->whereHas('penghuni', fn ($q) => $q->whereIn('kos_id', $kosIds)); }
        $items = $query->latest('payment_date')->get();
        $stats = $statsQuery->selectRaw('COUNT(*) as count, COALESCE(SUM(amount), 0) as total')->first();
        return ['items' => $items, 'summary' => ['Total Pendapatan' => 'Rp '.number_format($stats->total, 0, ',', '.'), 'Jumlah Transaksi' => $stats->count]];
    }

    private function penghuniData(?int $kosId, ?array $kosIds): array
    {
        $query = Penghuni::with(['user', 'kos', 'kamar']);
        $statsQuery = Penghuni::query();
        if ($kosId) { $query->where('kos_id', $kosId); $statsQuery->where('kos_id', $kosId); }
        elseif ($kosIds !== null) { $query->whereIn('kos_id', $kosIds); $statsQuery->whereIn('kos_id', $kosIds); }
        $items = $query->get();
        $stats = $statsQuery->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count, SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_count")->first();
        return ['items' => $items, 'summary' => ['Total Penghuni Aktif' => (int) $stats->active_count, 'Total Penghuni Inactive' => (int) $stats->inactive_count]];
    }

    private function bookingData(?string $start, ?string $end, ?int $kosId, ?array $kosIds): array
    {
        $query = Booking::with(['user', 'kos', 'kamar']);
        $statsQuery = Booking::query();
        if ($start) { $query->whereDate('booking_date', '>=', $start); $statsQuery->whereDate('booking_date', '>=', $start); }
        if ($end) { $query->whereDate('booking_date', '<=', $end); $statsQuery->whereDate('booking_date', '<=', $end); }
        if ($kosId) { $query->where('kos_id', $kosId); $statsQuery->where('kos_id', $kosId); }
        elseif ($kosIds !== null) { $query->whereIn('kos_id', $kosIds); $statsQuery->whereIn('kos_id', $kosIds); }
        $items = $query->latest('booking_date')->get();
        $stats = $statsQuery->selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending, SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved, SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected")->first();
        return ['items' => $items, 'summary' => ['Total Booking' => $stats->total, 'Pending' => (int) $stats->pending, 'Approved' => (int) $stats->approved, 'Rejected' => (int) $stats->rejected]];
    }

    private function kamarData(?int $kosId, ?array $kosIds): array
    {
        $query = Kamar::with('kos');
        $statsQuery = Kamar::query();
        if ($kosId) { $query->where('kos_id', $kosId); $statsQuery->where('kos_id', $kosId); }
        elseif ($kosIds !== null) { $query->whereIn('kos_id', $kosIds); $statsQuery->whereIn('kos_id', $kosIds); }
        $items = $query->get();
        $stats = $statsQuery->selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available, SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) as booked, SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied, SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance")->first();
        return ['items' => $items, 'summary' => ['Total Kamar' => $stats->total, 'Available' => (int) $stats->available, 'Booked' => (int) $stats->booked, 'Occupied' => (int) $stats->occupied, 'Maintenance' => (int) $stats->maintenance]];
    }

    private function tagihanData(?string $start, ?string $end, ?int $kosId, ?array $kosIds): array
    {
        $query = Tagihan::with(['penghuni.user', 'kamar.kos']);
        $statsQuery = Tagihan::query();
        if ($start) { $query->whereDate('due_date', '>=', $start); $statsQuery->whereDate('due_date', '>=', $start); }
        if ($end) { $query->whereDate('due_date', '<=', $end); $statsQuery->whereDate('due_date', '<=', $end); }
        if ($kosId) { $query->whereHas('kamar.kos', fn ($q) => $q->where('id', $kosId)); $statsQuery->whereHas('kamar.kos', fn ($q) => $q->where('id', $kosId)); }
        elseif ($kosIds !== null) { $query->whereHas('kamar', fn ($q) => $q->whereIn('kos_id', $kosIds)); $statsQuery->whereHas('kamar', fn ($q) => $q->whereIn('kos_id', $kosIds)); }
        $items = $query->latest('due_date')->get();
        $stats = $statsQuery->selectRaw("COUNT(*) as total, COALESCE(SUM(total), 0) as sum_total, SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid, SUM(CASE WHEN status = 'unpaid' THEN 1 ELSE 0 END) as unpaid, SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue")->first();
        return ['items' => $items, 'summary' => ['Total Tagihan' => 'Rp '.number_format($stats->sum_total, 0, ',', '.'), 'Sudah Dibayar' => (int) $stats->paid, 'Belum Dibayar' => (int) $stats->unpaid, 'Overdue' => (int) $stats->overdue]];
    }

    private function getHeaders(string $type): array
    {
        return match ($type) {
            'pendapatan' => ['No', 'Tanggal', 'Penghuni', 'Kamar', 'Kos', 'Nominal', 'Metode', 'Status'],
            'penghuni' => ['No', 'Nama', 'Email', 'Kamar', 'Kos', 'Telepon', 'Tanggal Masuk', 'Status'],
            'booking' => ['No', 'Kode', 'User', 'Kamar', 'Kos', 'Periode', 'Harga', 'Status'],
            'kamar' => ['No', 'Nomor', 'Nama', 'Kos', 'Tipe', 'Harga/Bulan', 'Status'],
            'tagihan' => ['No', 'Nomor', 'Penghuni', 'Kamar', 'Jenis', 'Jatuh Tempo', 'Total', 'Status'],
            default => [],
        };
    }

    private function getRows(string $type, $items): array
    {
        $rows = [];
        $no = 1;

        foreach ($items as $item) {
            $row = match ($type) {
                'pendapatan' => [
                    $no++,
                    $item->payment_date?->format('d/m/Y') ?? '-',
                    $item->penghuni->user->name ?? '-',
                    $item->tagihan->kamar->room_number ?? '-',
                    $item->tagihan->kamar->kos->name ?? '-',
                    'Rp '.number_format($item->amount, 0, ',', '.'),
                    ucfirst(str_replace('_', ' ', $item->payment_method)),
                    ucfirst($item->verification_status),
                ],
                'penghuni' => [
                    $no++,
                    $item->user->name ?? '-',
                    $item->user->email ?? '-',
                    $item->kamar->room_number ?? '-',
                    $item->kos->name ?? '-',
                    $item->phone ?? '-',
                    $item->check_in_date?->format('d/m/Y') ?? '-',
                    ucfirst($item->status),
                ],
                'booking' => [
                    $no++,
                    $item->booking_code,
                    $item->user->name ?? '-',
                    $item->kamar->room_number ?? '-',
                    $item->kos->name ?? '-',
                    ($item->start_date?->format('d/m/Y') ?? '-').' s/d '.($item->end_date?->format('d/m/Y') ?? '-'),
                    'Rp '.number_format($item->price, 0, ',', '.'),
                    ucfirst($item->status),
                ],
                'kamar' => [
                    $no++,
                    $item->room_number,
                    $item->room_name,
                    $item->kos->name ?? '-',
                    $item->room_type,
                    'Rp '.number_format($item->monthly_price, 0, ',', '.'),
                    ucfirst($item->status),
                ],
                'tagihan' => [
                    $no++,
                    $item->bill_number,
                    $item->penghuni->user->name ?? '-',
                    $item->kamar->room_number ?? '-',
                    $item->bill_type,
                    $item->due_date?->format('d/m/Y') ?? '-',
                    'Rp '.number_format($item->total, 0, ',', '.'),
                    ucfirst(str_replace('_', ' ', $item->status)),
                ],
                default => [],
            };
            $rows[] = $row;
        }

        return $rows;
    }
}