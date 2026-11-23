<?php

namespace App\Traits;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\Penghuni;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

trait HasRoleScope
{
    /**
     * Daftarkan global scope berbasis role (defense-in-depth).
     * Hanya aktif saat ada user terautentikasi; di luar konteks auth (console,
     * seeder, factory tanpa actingAs) tidak ada filter agar tidak merusak
     * operasi non-request.
     */
    public static function bootHasRoleScope(): void
    {
        static::addGlobalScope('role', function (Builder $builder) {
            $user = Auth::user();

            if (! $user) {
                return;
            }

            if ($user->isSuperAdmin()) {
                return;
            }

            $model = $builder->getModel();
            $table = $model->getTable();

            // Teknisi: belum ada task model di 1B; akses data umum dibatasi.
            if ($user->isTechnician()) {
                $builder->whereRaw('1 = 0');

                return;
            }

            if ($user->isPencari()) {
                self::scopeForPencari($builder, $table, $user);

                return;
            }

            // Admin / Owner: batasi ke kos yang di-assign / dimiliki.
            $kosIds = $user->isAdmin()
                ? $user->assignedKos()->pluck('kos.id')->all()
                : Kos::where('owner_id', $user->id)->pluck('id')->all();

            self::scopeForManager($builder, $table, $kosIds);

        });
    }

    private static function scopeForPencari(Builder $builder, string $table, $user): void
    {
        if ($table === 'penghunis') {
            $builder->where($table.'.user_id', $user->id);
        } elseif ($table === 'tagihans' || $table === 'pembayarans') {
            $penghuniIds = Penghuni::where('user_id', $user->id)->pluck('id')->all();
            $builder->whereIn($table.'.penghuni_id', $penghuniIds ?: [0]);
        } elseif ($table === 'kontraks') {
            $penghuniIds = Penghuni::where('user_id', $user->id)->pluck('id')->all();
            $builder->whereIn($table.'.penghuni_id', $penghuniIds ?: [0]);
        } elseif ($table === 'check_outs') {
            $penghuniIds = Penghuni::where('user_id', $user->id)->pluck('id')->all();
            $builder->whereIn($table.'.penghuni_id', $penghuniIds ?: [0]);
        } elseif ($table === 'damage_reports') {
            $builder->where($table.'.user_id', $user->id);
        } elseif ($table === 'disputes') {
            $builder->where($table.'.user_id', $user->id);
        }
    }

    private static function scopeForManager(Builder $builder, string $table, array $kosIds): void
    {
        if (empty($kosIds)) {
            $builder->whereRaw('1 = 0');

            return;
        }

        if (Schema::hasColumn($table, 'kos_id')) {
            $builder->whereIn($table.'.kos_id', $kosIds);

            return;
        }

        if ($table === 'tagihans' || $table === 'pembayarans') {
            $kamarIds = Kamar::whereIn('kos_id', $kosIds)->pluck('id')->all();
            $builder->whereIn($table.'.kamar_id', $kamarIds ?: [0]);
        } elseif ($table === 'kontraks') {
            $builder->whereIn($table.'.kos_id', $kosIds);
        } elseif ($table === 'penghunis') {
            $builder->whereIn($table.'.kos_id', $kosIds);
        } elseif ($table === 'check_outs') {
            $kamarIds = Kamar::whereIn('kos_id', $kosIds)->pluck('id')->all();
            // check_outs has kamar_id
            if (Schema::hasColumn($table, 'kamar_id')) {
                $builder->whereIn($table.'.kamar_id', $kamarIds ?: [0]);
            }
        } elseif ($table === 'damage_reports') {
            $builder->whereIn($table.'.kos_id', $kosIds);
        } elseif ($table === 'disputes') {
            // disputes has no kos_id; scope via user_id of penghuni in kos
            $penghuniIds = Penghuni::whereIn('kos_id', $kosIds)->pluck('user_id')->all();
            $builder->whereIn($table.'.user_id', $penghuniIds ?: [0]);
        }
    }
}
