<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Pembayaran;
use App\Models\Penghuni;
use App\Models\Tagihan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

/**
 * Single Laporan controller untuk semua role (owner / admin / super_admin).
 * Satu file — hanya ekspor gabungan (1 file berisi 5 laporan).
 */
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
     * Ekspor GABUNGAN 1 file berisi 5 laporan sekaligus (Pendapatan, Penghuni, Booking, Kamar, Tagihan).
     */
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
        $rows = []; $no = 1;
        foreach ($items as $item) {
            $row = match ($type) {
                'pendapatan' => [$no++, $item->payment_date?->format('d/m/Y') ?? '-', $item->penghuni->user->name ?? '-', $item->tagihan->kamar->room_number ?? '-', $item->tagihan->kamar->kos->name ?? '-', 'Rp '.number_format($item->amount, 0, ',', '.'), ucfirst(str_replace('_', ' ', $item->payment_method)), ucfirst($item->verification_status)],
                'penghuni' => [$no++, $item->user->name ?? '-', $item->user->email ?? '-', $item->kamar->room_number ?? '-', $item->kos->name ?? '-', $item->phone ?? '-', $item->check_in_date?->format('d/m/Y') ?? '-', ucfirst($item->status)],
                'booking' => [$no++, $item->booking_code, $item->user->name ?? '-', $item->kamar->room_number ?? '-', $item->kos->name ?? '-', ($item->start_date?->format('d/m/Y') ?? '-').' s/d '.($item->end_date?->format('d/m/Y') ?? '-'), 'Rp '.number_format($item->price, 0, ',', '.'), ucfirst($item->status)],
                'kamar' => [$no++, $item->room_number, $item->room_name, $item->kos->name ?? '-', $item->room_type, 'Rp '.number_format($item->monthly_price, 0, ',', '.'), ucfirst($item->status)],
                'tagihan' => [$no++, $item->bill_number, $item->penghuni->user->name ?? '-', $item->kamar->room_number ?? '-', $item->bill_type, $item->due_date?->format('d/m/Y') ?? '-', 'Rp '.number_format($item->total, 0, ',', '.'), ucfirst(str_replace('_', ' ', $item->status))],
                default => [],
            };
            $rows[] = $row;
        }
        return $rows;
    }
}