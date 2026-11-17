<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Pembayaran;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Laporan read-only untuk data lama C3: booking cancelled dengan DP
 * yang sudah terlanjur berstatus refund requested/approved.
 *
 * Command ini tidak mengubah data apa pun.
 */
class ReportCancelledDownPaymentRefunds extends Command
{
    protected $signature = 'refunds:report-cancelled-dp
                            {--json : Tampilkan hasil sebagai JSON}';

    protected $description = 'Laporkan booking cancelled dengan DP yang masih berstatus refund requested/approved (read-only).';

    public function handle(): int
    {
        $rows = DB::table('bookings')
            ->join('pembayarans', 'pembayarans.booking_id', '=', 'bookings.id')
            ->leftJoin('payment_distributions', 'payment_distributions.pembayaran_id', '=', 'pembayarans.id')
            ->where('bookings.status', Booking::STATUS_CANCELLED)
            ->whereNotNull('bookings.down_payment_amount')
            ->where('pembayarans.verification_status', Pembayaran::STATUS_APPROVED)
            ->whereIn('pembayarans.refund_status', [
                Pembayaran::REFUND_REQUESTED,
                Pembayaran::REFUND_APPROVED,
            ])
            ->whereRaw('ABS(pembayarans.amount - bookings.down_payment_amount) < 0.01')
            ->orderBy('bookings.id')
            ->orderBy('pembayarans.id')
            ->get([
                'bookings.id as booking_id',
                'bookings.booking_code',
                'bookings.down_payment_amount',
                'pembayarans.id as pembayaran_id',
                'pembayarans.amount',
                'pembayarans.refund_status',
                'pembayarans.refund_amount',
                'payment_distributions.id as distribution_id',
                'payment_distributions.status as distribution_status',
            ]);

        if ($this->option('json')) {
            $this->line($rows->toJson(JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        if ($rows->isEmpty()) {
            $this->info('Tidak ada booking cancelled dengan DP berstatus refund requested/approved.');

            return self::SUCCESS;
        }

        $this->table(
            ['Booking', 'Pembayaran', 'Nominal DP', 'Refund', 'Distribusi'],
            $rows->map(fn ($row) => [
                $row->booking_code,
                $row->pembayaran_id,
                number_format((float) $row->amount, 0, ',', '.'),
                $row->refund_status,
                $row->distribution_status ?? '-',
            ])->all()
        );

        $this->line('Read-only: tidak ada data yang diubah.');

        return self::SUCCESS;
    }
}
