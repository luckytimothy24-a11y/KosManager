<?php

namespace App\Traits;

/**
 * Sinkronisasi kolom uang decimal lama dengan kolom integer rupiah baru.
 *
 * Fase 1D: kolom `*_rp` adalah source of truth baru. Kolom decimal lama
 * tetap ditulis agar rollback dan query agregat legacy tetap aman sampai
 * cutover final (drop kolom decimal) dilakukan.
 */
trait HasIntegerRupiah
{
    public static function bootHasIntegerRupiah(): void
    {
        static::saving(function ($model) {
            foreach ($model->rupiahColumnMap() as $decimal => $integer) {
                $decimalDirty = $model->isDirty($decimal);
                $integerDirty = $model->isDirty($integer);

                if ($integerDirty && ! $decimalDirty) {
                    // Kolom integer diisi langsung (mis. forceFill): turunkan decimal.
                    $model->setAttribute($decimal, $model->getAttribute($integer));
                } elseif ($decimalDirty && ! $integerDirty) {
                    // Kolom decimal diisi tanpa mutator: turunkan integer.
                    $model->setAttribute($integer, self::toRupiah($model->getAttribute($decimal)));
                }
            }
        });
    }

    /**
     * @return array<string, string> decimal column => integer rupiah column
     */
    public function rupiahColumnMap(): array
    {
        return property_exists($this, 'rupiahColumns') ? $this->rupiahColumns : [];
    }

    protected function getRupiahValue(string $decimal, mixed $fallback): ?int
    {
        $integer = $this->attributes[$this->rupiahColumn($decimal)] ?? null;

        if ($integer !== null && $integer !== '') {
            return (int) $integer;
        }

        // Pertahankan null: beberapa aturan bisnis membedakan "tanpa DP"
        // (null) dari "DP nol" (0).
        if ($fallback === null || $fallback === '') {
            return null;
        }

        return self::toRupiah($fallback);
    }

    protected function setRupiahValue(string $decimal, mixed $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes[$decimal] = null;
            $this->attributes[$this->rupiahColumn($decimal)] = null;

            return;
        }

        $rupiah = self::toRupiah($value);

        // Kolom decimal dipertahankan sampai cutover final (rollback-able).
        $this->attributes[$decimal] = number_format($rupiah, 2, '.', '');
        $this->attributes[$this->rupiahColumn($decimal)] = $rupiah;
    }

    protected function rupiahColumn(string $decimal): string
    {
        return $this->rupiahColumnMap()[$decimal] ?? $decimal.'_rp';
    }

    public static function toRupiah(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return (int) round((float) $value);
    }
}
