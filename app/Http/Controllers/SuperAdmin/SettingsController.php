<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    protected const ALLOWED_SETTINGS = [
        'application_name' => ['type' => 'string', 'default' => 'KosManager', 'group' => 'general'],
        'application_description' => ['type' => 'string', 'default' => 'Platform manajemen kos terpercaya', 'group' => 'general'],
        'contact_email' => ['type' => 'string', 'default' => '', 'group' => 'general'],
        'contact_phone' => ['type' => 'string', 'default' => '', 'group' => 'general'],
        'items_per_page' => ['type' => 'integer', 'default' => 15, 'group' => 'general'],
        'booking_auto_expire_days' => ['type' => 'integer', 'default' => 24, 'group' => 'booking'],
        'late_fee_amount' => ['type' => 'integer', 'default' => 50000, 'group' => 'booking'],
        'commission.default_percent' => ['type' => 'json', 'default' => null, 'group' => 'commission'],
        'commission.default_fixed' => ['type' => 'json', 'default' => null, 'group' => 'commission'],
        'advertising_default_placement' => ['type' => 'string', 'default' => 'marketplace', 'group' => 'advertising'],
        'maintenance_mode' => ['type' => 'boolean', 'default' => false, 'group' => 'maintenance'],
        'maintenance_message' => ['type' => 'string', 'default' => '', 'group' => 'maintenance'],
    ];

    private function authorizeSuperAdmin(): void
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat mengakses pengaturan sistem.');
    }

    public function index(Request $request)
    {
        $this->authorizeSuperAdmin();

        $settings = [];
        foreach (self::ALLOWED_SETTINGS as $key => $config) {
            $dbSetting = Setting::where('key', $key)->first();
            $settings[$key] = [
                'key' => $key,
                'type' => $config['type'],
                'group' => $config['group'],
                'value' => $dbSetting ? $dbSetting->value : $config['default'],
                'label' => self::getLabel($key),
            ];
        }

        $grouped = collect($settings)->groupBy('group');

        return view('super-admin.settings.index', compact('settings', 'grouped'));
    }

    public function update(Request $request)
    {
        $this->authorizeSuperAdmin();

        $data = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'required|array',
            'settings.*.key' => 'required|string|in:'.implode(',', array_keys(self::ALLOWED_SETTINGS)),
            'settings.*.value' => 'required',
        ]);

        $changed = [];

        foreach ($data['settings'] as $setting) {
            $key = $setting['key'];
            $value = $setting['value'];
            $config = self::ALLOWED_SETTINGS[$key];

            if ($config['type'] === 'integer') {
                $value = filter_var($value, FILTER_VALIDATE_INT);
                if ($value === false) {
                    return back()->with('error', "Nilai untuk {$key} harus berupa angka.");
                }
            } elseif ($config['type'] === 'boolean') {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            } elseif ($config['type'] === 'string') {
                $value = (string) $value;
                if (strlen($value) > 500) {
                    return back()->with('error', "Nilai untuk {$key} terlalu panjang.");
                }
            }

            $existing = Setting::where('key', $key)->first();
            $oldValue = $existing?->value;

            if ($existing) {
                $existing->update(['value' => $value, 'type' => $config['type']]);
            } else {
                Setting::create([
                    'key' => $key,
                    'value' => $value,
                    'type' => $config['type'],
                ]);
            }

            if ($oldValue != $value) {
                $changed[] = "{$key}: {$oldValue} → {$value}";
            }
        }

        if (! empty($changed)) {
            AuditLogService::update('Settings', 'Pengaturan sistem diperbarui: '.implode(', ', $changed));
        }

        return redirect()->route('super-admin.settings.index')->with('success', 'Pengaturan berhasil diperbarui.');
    }

    private static function getLabel(string $key): string
    {
        $labels = [
            'application_name' => 'Nama Aplikasi',
            'application_description' => 'Deskripsi Aplikasi',
            'contact_email' => 'Email Kontak',
            'contact_phone' => 'Telepon Kontak',
            'items_per_page' => 'Item Per Halaman',
            'booking_auto_expire_days' => 'Hari Expiry Booking Otomatis',
            'maintenance_mode' => 'Mode Maintenance',
            'maintenance_message' => 'Pesan Maintenance',
            'advertising_default_placement' => 'Placement Iklan Default',
            'late_fee_amount' => 'Nominal Denda Keterlambatan',
            'commission.default_percent' => 'Komisi Default (%)',
            'commission.default_fixed' => 'Komisi Default Fixed (Rp)',
        ];

        return $labels[$key] ?? $key;
    }
}
