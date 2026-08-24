<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'penghuni_id',
        'kamar_id',
        'check_in_date',
        'check_in_time',
        'officer_id',
        'notes',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_in_time' => 'datetime:H:i',
    ];

    public function penghuni()
    {
        return $this->belongsTo(Penghuni::class);
    }

    public function kamar()
    {
        return $this->belongsTo(Kamar::class);
    }

    public function officer()
    {
        return $this->belongsTo(User::class, 'officer_id');
    }
}
