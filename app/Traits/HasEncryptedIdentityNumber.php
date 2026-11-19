<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;

/**
 * Menyimpan nomor identitas (NIK/KTP) terenkripsi di kolom
 * `identity_number_encrypted` + hash SHA-256 untuk pencarian exact-match.
 *
 * Accessor/mutator mempertahankan nama atribut lama `identity_number`
 * sehingga kode pemanggil tidak perlu berubah.
 *
 * Fallback plaintext disediakan untuk data legacy yang belum dimigrasi.
 */
trait HasEncryptedIdentityNumber
{
    public function initializeHasEncryptedIdentityNumber(): void
    {
        $this->hidden = array_values(array_unique(array_merge(
            $this->hidden ?? [],
            ['identity_number_encrypted', 'identity_number_hash']
        )));
    }

    public function setIdentityNumberAttribute($value): void
    {
        $value = filled($value) ? trim((string) $value) : null;

        $this->attributes['identity_number_encrypted'] = $value === null
            ? null
            : Crypt::encryptString($value);

        $this->attributes['identity_number_hash'] = $value === null
            ? null
            : hash('sha256', $value);
    }

    public function getIdentityNumberAttribute(): ?string
    {
        $encrypted = $this->attributes['identity_number_encrypted'] ?? null;

        if (! filled($encrypted)) {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable $e) {
            // Data legacy plaintext (belum termigrasi) — kembalikan apa adanya.
            return (string) $encrypted;
        }
    }

    /**
     * Hash untuk query exact-match tanpa mendekripsi kolom.
     */
    public static function identityHash(?string $identityNumber): ?string
    {
        return filled($identityNumber) ? hash('sha256', trim((string) $identityNumber)) : null;
    }

    /**
     * Mask NIK: ************1234.
     */
    public function maskedIdentityNumber(): string
    {
        $num = (string) ($this->identity_number ?? '');

        if ($num === '') {
            return '-';
        }

        $len = mb_strlen($num);

        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        return str_repeat('*', $len - 4).mb_substr($num, -4);
    }
}
