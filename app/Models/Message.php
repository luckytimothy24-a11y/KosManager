<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'kamar' => 'Kamar',
        'fasilitas' => 'Fasilitas',
        'tagihan' => 'Tagihan',
        'pembayaran' => 'Pembayaran',
        'booking' => 'Booking',
        'kontrak' => 'Kontrak',
        'lainnya' => 'Lainnya',
    ];

    protected $fillable = [
        'kos_id',
        'sender_id',
        'receiver_id',
        'category',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function kos()
    {
        return $this->belongsTo(Kos::class);
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

    public function scopeForKos(Builder $query, int $kosId): Builder
    {
        return $query->where('kos_id', $kosId);
    }

    public function scopeUnread(Builder $query, int $userId): Builder
    {
        return $query->where('receiver_id', $userId)->whereNull('read_at');
    }

    public function markRead(int $userId = 0): void
    {
        if ($this->read_at === null && $this->receiver_id === (int) auth()->id()) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }
}
