<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiContent extends Model
{
    protected $fillable = [
        'user_id',
        'video_id',
        'type',
        'title',
        'content',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
