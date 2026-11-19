<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

/**
 * TOTP (RFC 6238) dua faktor tanpa dependensi eksternal tambahan.
 * Secret disimpan terenkripsi oleh cast `encrypted` pada model User.
 */
class TwoFactorService
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    private const PERIOD = 30;

    private const DIGITS = 6;

    /**
     * Generate Base32 secret acak (default 32 karakter = 160 bit).
     */
    public function generateSecret(int $length = 32): string
    {
        $bytes = random_bytes((int) ceil($length * 5 / 8));
        $secret = $this->base32Encode($bytes);

        return substr($secret, 0, $length);
    }

    /**
     * Verifikasi kode TOTP dengan toleransi window (default ±1 periode = 30 detik).
     */
    public function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\D/', '', $code) ?? '';

        if (strlen($code) !== self::DIGITS) {
            return false;
        }

        $timestamp = time();

        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals($this->code($secret, $timestamp + ($i * self::PERIOD)), $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Hitung kode TOTP untuk secret pada waktu tertentu (default sekarang).
     */
    public function code(string $secret, ?int $timestamp = null): string
    {
        $key = $this->base32Decode($secret);
        $counter = (int) floor(($timestamp ?? time()) / self::PERIOD);

        $hash = hash_hmac('sha1', pack('N2', 0, $counter), $key, true);
        $offset = ord(substr($hash, -1)) & 0x0F;

        $value = unpack('N', substr($hash, $offset, 4))[1] & 0x7FFFFFFF;

        return str_pad((string) ($value % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * URI otpauth:// untuk aplikasi authenticator.
     */
    public function provisioningUri(string $accountName, string $secret, ?string $issuer = null): string
    {
        $issuer = $issuer ?: config('app.name', 'KosManager');
        $label = rawurlencode($issuer).':'.rawurlencode($accountName);

        return sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s&algorithm=SHA1&digits=%d&period=%d',
            $label,
            $secret,
            rawurlencode($issuer),
            self::DIGITS,
            self::PERIOD
        );
    }

    /**
     * QR code sebagai data URI (PNG base64) untuk ditampilkan di halaman setup.
     */
    public function qrCodeDataUri(string $uri): string
    {
        $result = (new Builder(
            writer: new PngWriter,
            data: $uri,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 260,
            margin: 8,
        ))->build();

        return $result->getDataUri();
    }

    /**
     * Generate recovery codes sekali pakai.
     *
     * @return list<string>
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(5)));
        }

        return $codes;
    }

    public function base32Encode(string $bytes): string
    {
        $bits = '';

        foreach (str_split($bytes) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }

        $encoded = '';

        foreach (str_split($bits, 5) as $chunk) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            $encoded .= self::ALPHABET[bindec($chunk)];
        }

        return $encoded;
    }

    public function base32Decode(string $secret): string
    {
        $secret = strtoupper(preg_replace('/[^A-Z2-7]/i', '', $secret) ?? '');
        $bits = '';

        foreach (str_split($secret) as $char) {
            $position = strpos(self::ALPHABET, $char);
            if ($position === false) {
                continue;
            }
            $bits .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';

        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $bytes .= chr(bindec($chunk));
            }
        }

        return $bytes;
    }
}
