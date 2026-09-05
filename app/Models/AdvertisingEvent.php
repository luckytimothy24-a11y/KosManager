<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertisingEvent extends Model
{
    use HasFactory;

    public const TYPE_IMPRESSION = 'impression';

    public const TYPE_CLICK = 'click';

    public const TYPE_CONVERSION = 'conversion';

    public const UPDATED_AT = null;

    protected $table = 'advertising_events';

    protected $fillable = [
        'campaign_id',
        'type',
        'user_id',
        'session_key',
        'placement',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(AdvertisingCampaign::class, 'campaign_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
