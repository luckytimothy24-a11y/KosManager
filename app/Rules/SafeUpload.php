<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;
use App\Rules\SafeImage;

class SafeUpload implements Rule
{
    /**
     * Validasi upload file:
     * - Jika gambar (jpg, jpeg, png, webp): terapkan SafeImage (content check)
     * - Jika PDF: cukup mimes + max (sudah divalidasi di rules())
     */
    public function passes($attribute, $value): bool
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            return false;
        }

        $realMime = (new \finfo(FILEINFO_MIME_TYPE))->file($value->getRealPath());

        // Gambar: terapkan SafeImage
        if (in_array($realMime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            $safeImage = new SafeImage();
            return $safeImage->passes($attribute, $value);
        }

        // PDF: cukup mimes + max (sudah divalidasi di rules())
        if ($realMime === 'application/pdf') {
            return true;
        }

        // Tipe lain: tolak
        return false;
    }

    public function message(): string
    {
        return ':attribute harus berupa file gambar (JPEG, PNG, WebP) atau PDF yang valid.';
    }
}
