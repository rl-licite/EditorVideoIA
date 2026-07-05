<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $fillable = [
        'user_id',
        'level',
        'area',
        'message',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];
}
