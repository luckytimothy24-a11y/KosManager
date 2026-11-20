<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'booking_id',
        'pembayaran_id',
        'type',
        'amount',
        'description',
        'reference_number',
        'status',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'integer',
        'metadata' => 'array',
    ];

    // Append-only: no updates or deletes allowed
    public function save(array $options = [])
    {
        if ($this->exists) {
            throw new \RuntimeException('WalletTransaction is append-only and cannot be updated.');
        }

        return parent::save($options);
    }

    public function delete()
    {
        throw new \RuntimeException('WalletTransaction is append-only and cannot be deleted.');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class);
    }
}
