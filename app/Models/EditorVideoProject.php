<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EditorVideoProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'timeline_data',
        'duration_seconds',
        'settings',
    ];

    protected $casts = [
        'timeline_data' => 'array',
        'settings' => 'array',
        'duration_seconds' => 'integer',
    ];
}
