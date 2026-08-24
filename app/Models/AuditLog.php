<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'description',
        'ip_address',
        'timestamp_data',
    ];

    protected $casts = [
        'timestamp_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
