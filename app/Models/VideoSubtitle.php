<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoSubtitle extends Model
{
    protected $fillable = [
        'user_id',
        'video_id',
        'text',
        'start_second',
        'end_second',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
