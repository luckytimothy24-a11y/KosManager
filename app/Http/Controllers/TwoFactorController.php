<?php

namespace App\Http\Controllers;

use App\Services\AuditLogService;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function __construct(private readonly TwoFactorService $twoFactor) {}

    /**
     * Halaman kelola 2FA (status, aktifkan, nonaktifkan).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $pendingSecret = $user->two_factor_secret && ! $user->two_factor_confirmed_at
            ? $user->two_factor_secret
            : null;

        $recoveryCodes = [];
        if (session()->has('two_factor_recovery_codes')) {
            $recoveryCodes = session()->pull('two_factor_recovery_codes');
        }

        return view('two-factor.index', [
            'enabled' => $user->hasTwoFactorEnabled(),
            'pendingSecret' => $pendingSecret,
            'recoveryCodes' => $recoveryCodes,
            'required' => $user->twoFactorRequired(),
        ]);
    }

    /**
     * Mulai setup: generate secret (belum terkonfirmasi).
     */
    public function enable(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.index')->with('info', '2FA sudah aktif.');
        }

        $secret = $this->twoFactor->generateSecret();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return redirect()->route('two-factor.confirm');
    }

    /**
     * Tampilkan QR + secret untuk dipindai.
     */
    public function showConfirm(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.index')->with('info', '2FA sudah aktif.');
        }

        if (! $user->two_factor_secret) {
            return redirect()->route('two-factor.index')->with('warning', 'Mulai setup 2FA terlebih dahulu.');
        }

        $uri = $this->twoFactor->provisioningUri($user->email, $user->two_factor_secret);

        return view('two-factor.confirm', [
            'secret' => $user->two_factor_secret,
            'qrCode' => $this->twoFactor->qrCodeDataUri($uri),
            'uri' => $uri,
        ]);
    }

    /**
     * Konfirmasi kode TOTP dan aktifkan 2FA.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:10'],
        ]);

        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.index')->with('info', '2FA sudah aktif.');
        }

        if (! $user->two_factor_secret || ! $this->twoFactor->verify($user->two_factor_secret, $request->code)) {
            return back()->withErrors(['code' => 'Kode verifikasi tidak valid. Coba lagi.']);
        }

        $recoveryCodes = $this->twoFactor->generateRecoveryCodes(
            (int) config('two-factor.recovery_code_count', 8)
        );

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $recoveryCodes,
        ])->save();

        session(['two_factor_verified_at' => now()->timestamp]);
        session()->flash('two_factor_recovery_codes', $recoveryCodes);

        AuditLogService::create('Keamanan', "2FA diaktifkan untuk akun {$user->email}", ['user_id' => $user->id]);

        return redirect()->route('two-factor.index')->with('success', '2FA berhasil diaktifkan. Simpan recovery codes Anda.');
    }

    /**
     * Nonaktifkan 2FA (wajib konfirmasi password).
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        session()->forget('two_factor_verified_at');

        AuditLogService::create('Keamanan', "2FA dinonaktifkan untuk akun {$user->email}", ['user_id' => $user->id]);

        return redirect()->route('two-factor.index')->with('success', '2FA dinonaktifkan.');
    }

    /**
     * Generate ulang recovery codes (wajib password).
     */
    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.index')->with('warning', 'Aktifkan 2FA terlebih dahulu.');
        }

        $codes = $this->twoFactor->generateRecoveryCodes(
            (int) config('two-factor.recovery_code_count', 8)
        );

        $user->forceFill(['two_factor_recovery_codes' => $codes])->save();

        session()->flash('two_factor_recovery_codes', $codes);

        AuditLogService::create('Keamanan', "Recovery codes 2FA dibuat ulang untuk akun {$user->email}", ['user_id' => $user->id]);

        return redirect()->route('two-factor.index')->with('success', 'Recovery codes baru dibuat. Kode lama tidak berlaku lagi.');
    }

    /**
     * Halaman tantangan setelah login.
     */
    public function challenge(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            return redirect()->route('dashboard');
        }

        if ($this->sessionVerified($request)) {
            return redirect()->intended(route('dashboard'));
        }

        return view('two-factor.challenge');
    }

    /**
     * Verifikasi kode TOTP atau recovery code.
     */
    public function verifyChallenge(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:20'],
        ]);

        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            return redirect()->route('dashboard');
        }

        $code = trim($request->code);
        $isTotp = $this->twoFactor->verify((string) $user->two_factor_secret, $code);

        if (! $isTotp && ! $this->consumeRecoveryCode($user, $code)) {
            AuditLogService::create('Keamanan', "Percobaan 2FA gagal untuk akun {$user->email}", ['user_id' => $user->id]);

            return back()->withErrors(['code' => 'Kode tidak valid. Periksa authenticator atau gunakan recovery code.']);
        }

        $request->session()->put('two_factor_verified_at', now()->timestamp);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))->with('success', 'Verifikasi 2FA berhasil.');
    }

    private function sessionVerified(Request $request): bool
    {
        $verifiedAt = (int) $request->session()->get('two_factor_verified_at', 0);
        $lifetime = (int) config('two-factor.session_minutes', 720) * 60;

        return $verifiedAt > 0 && (now()->timestamp - $verifiedAt) < $lifetime;
    }

    private function consumeRecoveryCode($user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];
        $normalized = strtoupper($code);

        $index = array_search($normalized, array_map('strtoupper', $codes), true);

        if ($index === false) {
            return false;
        }

        unset($codes[$index]);
        $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

        AuditLogService::create('Keamanan', "Recovery code 2FA digunakan untuk akun {$user->email}", [
            'user_id' => $user->id,
            'remaining' => count($codes),
        ]);

        return true;
    }
}
