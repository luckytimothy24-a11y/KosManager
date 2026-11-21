<?php

namespace App\Support;

use App\Models\CsConversation;
use App\Models\User;

class CustomerServiceTeam
{
    /**
     * Seluruh Admin & Super Admin aktif yang menangani inbox CS.
     *
     * @return array<int, int>
     */
    public static function adminIds(): array
    {
        return User::whereIn('role', ['admin', 'super_admin'])
            ->where('is_active', true)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Penerima notifikasi untuk pesan tenant: admin yang sudah menangani
     * percakapan, atau seluruh tim CS bila belum ada yang ditugaskan.
     *
     * @return array<int, int>
     */
    public static function recipientIds(CsConversation $conversation): array
    {
        if ($conversation->assigned_admin_id) {
            return [(int) $conversation->assigned_admin_id];
        }

        return self::adminIds();
    }
}
