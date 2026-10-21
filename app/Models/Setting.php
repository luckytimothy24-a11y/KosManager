<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }
}
