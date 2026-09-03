<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembayaran extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    /**
     * Status verifikasi yang berarti pembayaran aktif / sudah terverifikasi
     * (menghalangi upload ulang).
     */
    public const ACTIVE_VERIFICATIONS = [self::STATUS_PENDING, self::STATUS_APPROVED];

    protected $fillable = [
        'payment_number',
        'tagihan_id',
        'penghuni_id',
        'amount',
        'payment_date',
        'payment_method',
        'proof_file',
        'verification_status',
        'admin_notes',
        'verified_by',
        'verified_at',
        'gateway_provider',
        'gateway_reference',
        'gateway_instructions',
        'gateway_expires_at',
        'active_payment_key',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'verified_at' => 'datetime',
        'gateway_expires_at' => 'datetime',
    ];

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class);
    }

    public function penghuni()
    {
        return $this->belongsTo(Penghuni::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Apakah pembayaran ini dibuat lewat gateway online (verifikasi otomatis).
     */
    public function isFromGateway(): bool
    {
        return $this->gateway_provider !== null;
    }
}
