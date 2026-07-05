<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoFolder extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'color',
    ];

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
