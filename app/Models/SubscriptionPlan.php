<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'monthly_credits',
        'price',
        'max_videos',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'price' => 'decimal:2',
    ];
}
