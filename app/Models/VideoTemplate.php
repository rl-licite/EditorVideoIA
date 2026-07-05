<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoTemplate extends Model
{
    protected $fillable = [
        'user_id','name','format','resolution','watermark_text','watermark_position',
        'overlay_text','overlay_position','clip_start','clip_duration','music_path',
        'intro_path','outro_path','auto_subtitle','subtitle_position','subtitle_color',
        'visual_layout','canvas_width','canvas_height',
        'cta_text','cta_position','font_family','primary_color','background_color',
    ];

    protected $casts = [
        'auto_subtitle' => 'boolean',
        'visual_layout' => 'array',
        'canvas_width' => 'integer',
        'canvas_height' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
