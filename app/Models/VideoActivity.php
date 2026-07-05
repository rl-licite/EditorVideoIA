<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoActivity extends Model
{
    protected $fillable = [
        'user_id',
        'video_id',
        'action',
        'title',
        'description',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
