<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagementAgreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'kos_id',
        'property_owner_id',
        'commission_type',
        'commission_value',
        'effective_from',
        'effective_to',
        'approved_by',
        'notes',
    ];

    protected $casts = [
        'commission_value' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function kos()
    {
        return $this->belongsTo(Kos::class);
    }

    public function propertyOwner()
    {
        return $this->belongsTo(PropertyOwner::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function activeForKosOn(int $kosId, int $propertyOwnerId, string $date): ?self
    {
        return static::where('kos_id', $kosId)
            ->where('property_owner_id', $propertyOwnerId)
            ->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')->orWhere('effective_to', '>=', $date);
            })
            ->orderByDesc('effective_from')
            ->first();
    }
}
