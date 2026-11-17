<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'sender_id',
        'receiver_id',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function scopeBetween(Builder $query, int $a, int $b): Builder
    {
        return $query->where(function (Builder $q) use ($a, $b) {
            $q->where('sender_id', $a)->where('receiver_id', $b)
                ->orWhere(function (Builder $inner) use ($a, $b) {
                    $inner->where('sender_id', $b)->where('receiver_id', $a);
                });
        });
    }

    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeUnread(Builder $query, int $userId): Builder
    {
        return $query->where('receiver_id', $userId)->whereNull('read_at');
    }
}