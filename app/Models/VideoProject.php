<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoProject extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'status',
        'description',
    ];

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function exports()
    {
        return $this->hasMany(VideoExport::class);
    }
}
