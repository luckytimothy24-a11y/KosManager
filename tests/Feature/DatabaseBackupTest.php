<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DatabaseBackup\DatabaseBackupContract;
use App\Services\DatabaseBackup\DatabaseBackupManager;
use App\Services\DatabaseBackup\SqliteBackupDriver;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('backups');
        config(['backup.disk' => 'backups']);
    }

    private function backupFile(): string
    {
        return collect(Storage::disk('backups')->files())
            ->first(fn ($f) => str_ends_with($f, '.sql')) ?? '';
    }

    public function test_resolves_sqlite_driver_for_default_connection(): void
    {
        $driver = DatabaseBackupManager::driver();

        $this->assertInstanceOf(DatabaseBackupContract::class, $driver);
        $this->assertInstanceOf(SqliteBackupDriver::class, $driver);
        $this->assertSame('sqlite', $driver->driverName());
        $this->assertTrue($driver->supported());
    }

    public function test_backup_run_creates_backup_file_on_disk(): void
    {
        User::factory()->count(2)->create();

        $this->artisan('backup:run')
            ->expectsOutputToContain('Backup berhasil')
            ->assertSuccessful();

        $file = $this->backupFile();
        $this->assertNotEmpty($file);

        $content = Storage::disk('backups')->get($file);

        $this->assertStringContainsString('CREATE TABLE', $content);
        $this->assertStringContainsString('INSERT INTO "users"', $content);
    }

    public function test_backup_run_cleans_up_old_backups_beyond_retention(): void
    {
        $old = [
            'backup-2020-01-01_000000.sql',
            'backup-2020-01-02_000000.sql',
        ];

        foreach ($old as $file) {
            Storage::disk('backups')->put($file, 'SELECT 1;');
            touch(Storage::disk('backups')->path($file), Carbon::parse('2020-01-02')->timestamp);
        }

        config(['backup.retention_days' => 30]);

        $this->artisan('backup:run')
            ->expectsOutputToContain('2 file lama dihapus')
            ->assertSuccessful();

        $existing = Storage::disk('backups')->files();
        $this->assertCount(1, $existing);
        $this->assertStringStartsWith('backup-'.Carbon::now()->format('Y-m-d'), $existing[0]);
    }

    public function test_backup_list_lists_backup_files(): void
    {
        $name = 'backup-2026-01-01_000000.sql';
        Storage::disk('backups')->put($name, 'SELECT 1;');

        $size = Storage::disk('backups')->size($name);
        $modified = Carbon::createFromTimestamp(Storage::disk('backups')->lastModified($name))->format('Y-m-d H:i:s');

        $this->artisan('backup:list')
            ->expectsTable(['File', 'Ukuran (bytes)', 'Terakhir diubah'], [
                [$name, (string) $size, $modified],
            ])
            ->assertSuccessful();
    }

    public function test_backup_verify_rejects_invalid_content(): void
    {
        Storage::disk('backups')->put('backup-2026-01-01_000000.sql', 'INVALID SQL ---');

        $this->artisan('backup:verify', ['file' => 'backup-2026-01-01_000000.sql'])
            ->assertFailed()
            ->expectsOutputToContain('INVALID');
    }

    public function test_backup_restore_recovers_database(): void
    {
        User::factory()->create(['name' => 'Penghuni Sebelum Backup']);

        $this->artisan('backup:run')->assertSuccessful();
        $file = $this->backupFile();

        User::query()->delete();
        $this->assertSame(0, User::count());

        $this->artisan('backup:restore', ['file' => $file, '--force' => true])
            ->assertSuccessful();

        $this->assertSame(1, User::count());
        $this->assertSame('Penghuni Sebelum Backup', User::first()->name);
    }

    public function test_backup_restore_requires_confirmation_without_force(): void
    {
        User::factory()->create(['name' => 'Data Saat Ini']);

        $this->artisan('backup:run')->assertSuccessful();
        $file = $this->backupFile();

        $this->artisan('backup:restore', ['file' => $file])
            ->expectsQuestion('Semua data saat ini akan DIGANTI dengan isi backup. Lanjutkan?', false)
            ->expectsOutputToContain('Restore dibatalkan')
            ->assertSuccessful();

        $this->assertSame(1, User::count());
        $this->assertSame('Data Saat Ini', User::first()->name);
    }

    public function test_backup_restore_rejects_missing_file(): void
    {
        $this->artisan('backup:restore', ['file' => 'backup-2026-01-01_000000.sql', '--force' => true])
            ->assertFailed()
            ->expectsOutputToContain('tidak ditemukan');
    }

    public function test_backup_restore_rejects_invalid_backup(): void
    {
        Storage::disk('backups')->put('bad.sql', 'INVALID SQL ---');

        $this->artisan('backup:restore', ['file' => 'bad.sql', '--force' => true])
            ->assertFailed()
            ->expectsOutputToContain('verifikasi');
    }

    public function test_backup_manager_rejects_unsupported_driver(): void
    {
        config(['backup.disk' => 'backups']);

        $this->artisan('backup:run', ['--connection' => 'sqlsrv'])
            ->expectsOutputToContain('tidak didukung')
            ->assertFailed();
    }

    public function test_backup_does_not_overwrite_existing_backup_in_same_second(): void
    {
        User::factory()->create();

        $this->artisan('backup:run')->assertSuccessful();
        $this->artisan('backup:run')->assertSuccessful();

        $this->assertCount(2, Storage::disk('backups')->files());
    }
}
