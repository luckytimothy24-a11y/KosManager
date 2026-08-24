<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        $legacyDir = storage_path('app/public/bukti-pembayaran');

        if (! is_dir($legacyDir)) {
            return;
        }

        foreach (File::files($legacyDir) as $file) {
            $target = 'bukti-pembayaran/'.$file->getFilename();

            if (! Storage::exists($target)) {
                Storage::put($target, file_get_contents($file->getPathname()));
            }

            File::delete($file->getPathname());
        }
    }

    public function down(): void
    {
        foreach (Storage::files('bukti-pembayaran') as $path) {
            File::put(
                storage_path('app/public/'.$path),
                Storage::get($path)
            );
        }
    }
};
