<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;

class SafeImage implements Rule
{
    /**
     * Raster image types yang diizinkan. SVG dan HTML sengaja ditolak.
     *
     * @var list<string>
     */
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * Validasi konten file (bukan sekadar ekstensi):
     * - MIME aktual dari isi file harus jpeg/png/webp;
     * - file harus benar-benar dapat di-decode sebagai image (getimagesize);
     *   HTML/SVG/malformed/trailing-payload ditolak.
     */
    public function passes($attribute, $value): bool
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            return false;
        }

        $realMime = (new \finfo(FILEINFO_MIME_TYPE))->file($value->getRealPath());

        if (! is_string($realMime) || ! in_array($realMime, self::ALLOWED_MIMES, true)) {
            return false;
        }

        $info = @getimagesize($value->getRealPath());

        return $info !== false
            && isset($info[0], $info[1])
            && (int) $info[0] > 0
            && (int) $info[1] > 0;
    }

    public function message(): string
    {
        return ':attribute harus berupa file gambar JPEG, PNG, atau WebP yang valid.';
    }
}
