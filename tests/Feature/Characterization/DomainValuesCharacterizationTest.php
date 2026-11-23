<?php

namespace Tests\Feature\Characterization;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\CheckOut;
use App\Models\DamageReport;
use App\Models\Dispute;
use App\Models\Kontrak;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * FASE 0 — KARAKTERISASI NILAI ROLE & STATUS.
 *
 * Sumber nilai diambil dari constraint database yang benar-benar aktif
 * (SQLite CHECK / MySQL ENUM), bukan hanya dari dokumen. Ini menjadi
 * dasar tabel pemetaan lama -> baru.
 */
class DomainValuesCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_user_roles_are_four_and_tenant_is_not_a_db_value_yet(): void
    {
        $this->assertSame(
            ['super_admin', 'admin', 'owner', 'pencari'],
            $this->enumValues('users', 'role')
        );

        $this->assertSame(
            ['super_admin', 'admin', 'owner', 'pencari'],
            UserRole::values()
        );

        $this->assertNull(UserRole::tryFrom('tenant'));
        $this->assertNull(UserRole::tryFrom('technician'));
    }

    public function test_current_role_labels(): void
    {
        $this->assertSame('Super Admin', UserRole::SUPER_ADMIN->label());
        $this->assertSame('Admin Kos', UserRole::ADMIN->label());
        $this->assertSame('Owner', UserRole::OWNER->label());
        $this->assertSame('Pencari Kos', UserRole::PENCARI->label());
    }

    public function test_current_kamar_statuses_have_no_needs_cleaning(): void
    {
        $this->assertSame(
            ['available', 'booked', 'occupied', 'maintenance'],
            $this->enumValues('kamar', 'status')
        );

        $this->assertNotContains('needs_cleaning', $this->enumValues('kamar', 'status'));
    }

    public function test_current_booking_statuses(): void
    {
        $this->assertSame([
            'pending',
            'approved',
            'rejected',
            'cancelled',
            'completed',
            'expired',
        ], $this->modelConstants(Booking::class, 'STATUS_'));

        $this->assertSame('approved', Booking::STATUS_CONFIRMED);
        $this->assertSame('pending', Booking::STATUS_PENDING);
        $this->assertSame('completed', Booking::STATUS_COMPLETED);

        $dbValues = $this->enumValues('bookings', 'status');

        if ($dbValues === []) {
            // karakterisasi, bukan perilaku yang diinginkan.
            // Rebuild tabel SQLite menghapus CHECK constraint: DB menerima status apa pun.
            $this->assertSame('sqlite', DB::connection()->getDriverName());
            Booking::factory()->create(['status' => 'status_tidak_dikenal']);
            $this->assertDatabaseHas('bookings', ['status' => 'status_tidak_dikenal']);
        } else {
            $this->assertEqualsCanonicalizing([
                'pending',
                'approved',
                'rejected',
                'cancelled',
                'completed',
                'expired',
            ], $dbValues);
        }
    }

    public function test_current_tagihan_statuses(): void
    {
        $this->assertEqualsCanonicalizing(
            ['unpaid', 'pending_verification', 'paid', 'overdue', 'cancelled'],
            $this->enumValues('tagihans', 'status')
        );

        $this->assertSame(['unpaid', 'overdue'], Tagihan::PAYABLE);
        $this->assertSame(['unpaid', 'overdue', 'pending_verification'], Tagihan::OUTSTANDING);
        $this->assertNotContains('draft', $this->enumValues('tagihans', 'status'));
        $this->assertNotContains('partial', $this->enumValues('tagihans', 'status'));
    }

    public function test_current_payment_verification_statuses(): void
    {
        $this->assertEqualsCanonicalizing(
            ['pending', 'approved', 'rejected'],
            $this->enumValues('pembayarans', 'verification_status')
        );

        $this->assertSame('pending', Pembayaran::STATUS_PENDING);
        $this->assertSame('approved', Pembayaran::STATUS_APPROVED);
        $this->assertSame('rejected', Pembayaran::STATUS_REJECTED);
        $this->assertSame([Pembayaran::STATUS_PENDING, Pembayaran::STATUS_APPROVED], Pembayaran::ACTIVE_VERIFICATIONS);
    }

    public function test_current_kontrak_statuses(): void
    {
        // Tidak ada konstanta status di model Kontrak; nilai berikut adalah
        // literal yang ditulis oleh command/service yang ada.
        $this->assertSame('active', Kontrak::factory()->make()->status);

        $dbValues = $this->enumValues('kontraks', 'status');

        if ($dbValues === []) {
            // karakterisasi, bukan perilaku yang diinginkan.
            // Rebuild SQLite mengubah kolom menjadi varchar tanpa CHECK.
            $this->assertSame('sqlite', DB::connection()->getDriverName());
            Kontrak::factory()->create(['status' => 'status_tidak_dikenal']);
            $this->assertDatabaseHas('kontraks', ['status' => 'status_tidak_dikenal']);
        } else {
            $this->assertEqualsCanonicalizing(['active', 'expired', 'terminated'], $dbValues);
        }

        $this->assertNotContains('renewed', $dbValues);
        $this->assertNotContains('transferred', $dbValues);
    }

    public function test_current_checkout_statuses(): void
    {
        $this->assertEqualsCanonicalizing(
            ['pending', 'approved', 'rejected', 'completed', 'cancelled'],
            $this->enumValues('check_outs', 'status')
        );

        $this->assertSame(['pending', 'approved'], CheckOut::ACTIVE);

        // karakterisasi, bukan perilaku yang diinginkan:
        // DB mengizinkan 'completed' tetapi model tidak punya konstantanya.
        $this->assertFalse(defined(CheckOut::class.'::STATUS_COMPLETED'));
    }

    public function test_current_damage_report_and_dispute_statuses_are_unconstrained_strings(): void
    {
        // karakterisasi, bukan perilaku yang diinginkan:
        // kedua kolom dibuat string tanpa CHECK/ENUM, validasi hanya di aplikasi.
        $this->assertSame([], $this->enumValues('damage_reports', 'status'));
        $this->assertSame([], $this->enumValues('disputes', 'status'));
        $this->assertContains(
            strtolower((string) DB::getSchemaBuilder()->getColumnType('damage_reports', 'status')),
            ['string', 'varchar']
        );
        $this->assertContains(
            strtolower((string) DB::getSchemaBuilder()->getColumnType('disputes', 'status')),
            ['string', 'varchar']
        );

        $this->assertSame([
            'pending',
            'reviewing',
            'approved',
            'rejected',
            'resolved',
        ], $this->modelConstants(DamageReport::class, 'STATUS_'));

        $this->assertSame([
            'open',
            'in_progress',
            'resolved',
            'closed',
        ], $this->modelConstants(Dispute::class, 'STATUS_'));

        $this->assertSame('open', Dispute::STATUS_OPEN);
        $this->assertSame('pending', DamageReport::STATUS_PENDING);
    }

    public function test_current_money_columns_are_decimal_not_integer(): void
    {
        foreach ([
            ['kamar', 'monthly_price'],
            ['tagihans', 'subtotal'],
            ['tagihans', 'total'],
            ['pembayarans', 'amount'],
            ['kontraks', 'rental_price'],
        ] as [$table, $column]) {
            $type = strtolower((string) DB::getSchemaBuilder()->getColumnType($table, $column));
            $this->assertContains($type, ['decimal', 'numeric'], "{$table}.{$column} should be decimal/numeric in baseline");
        }
    }

    /**
     * Ambil semua nilai konstanta model dengan prefix tertentu, tanpa duplikat.
     *
     * @return array<int, string>
     */
    private function modelConstants(string $class, string $prefix): array
    {
        $constants = (new \ReflectionClass($class))->getConstants();
        $values = [];

        foreach ($constants as $name => $value) {
            if (str_starts_with($name, $prefix) && is_string($value)) {
                $values[] = $value;
            }
        }

        return array_values(array_unique($values));
    }

    /**
     * Baca nilai enum dari constraint DB aktif.
     *
     * @return array<int, string>
     */
    private function enumValues(string $table, string $column): array
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $sql = (string) DB::table('sqlite_master')
                ->where('type', 'table')
                ->where('name', $table)
                ->value('sql');

            if (preg_match('/"'.preg_quote($column, '/').'"\s+in\s*\(([^)]*)\)/i', $sql, $matches)) {
                preg_match_all("/'([^']+)'/", $matches[1], $values);

                return $values[1];
            }

            return [];
        }

        if ($driver === 'mysql') {
            $row = DB::selectOne("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
            $type = (string) ($row->Type ?? '');
            if (preg_match('/^enum\((.*)\)$/i', $type, $matches)) {
                preg_match_all("/'([^']+)'/", $matches[1], $values);

                return $values[1];
            }

            return [];
        }

        $this->fail("Unsupported database driver: {$driver}");
    }
}
