<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'id_card_number',
        'id_card_image',
        'verification_status',
        'rejection_reason',
    ];

    protected $casts = [
        'verification_status' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
