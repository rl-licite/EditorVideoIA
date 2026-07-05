<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'user_id',
        'video_folder_id',
        'video_project_id',
        'original_name',
        'stored_name',
        'path',
        'thumbnail_path',
        'mime_type',
        'size',
        'duration',
        'width',
        'height',
        'format',
        'codec',
        'fps',
        'status',
        'error_message',
    ];

    protected $casts = [
        'size' => 'integer',
        'duration' => 'float',
        'width' => 'integer',
        'height' => 'integer',
        'fps' => 'float',
    ];

    public function folder()
    {
        return $this->belongsTo(VideoFolder::class, 'video_folder_id');
    }

    public function project()
    {
        return $this->belongsTo(VideoProject::class, 'video_project_id');
    }

    public function activities()
    {
        return $this->hasMany(VideoActivity::class);
    }

    public function aiContents()
    {
        return $this->hasMany(AiContent::class);
    }

    public function subtitles()
    {
        return $this->hasMany(VideoSubtitle::class);
    }

    public function getDurationFormattedAttribute(): string
    {
        if (!$this->duration) {
            return '-';
        }

        $seconds = (int) round($this->duration);
        $minutes = floor($seconds / 60);
        $remaining = $seconds % 60;

        return sprintf('%02d:%02d', $minutes, $remaining);
    }

    public function getResolutionAttribute(): string
    {
        if (!$this->width || !$this->height) {
            return '-';
        }

        return $this->width . 'x' . $this->height;
    }

    public function getAspectAttribute(): string
    {
        if (!$this->width || !$this->height) {
            return '-';
        }

        if ($this->height > $this->width) {
            return 'Vertical 9:16';
        }

        if ($this->width > $this->height) {
            return 'Horizontal 16:9';
        }

        return 'Quadrado 1:1';
    }

    public function getSizeFormattedAttribute(): string
    {
        return number_format(($this->size ?? 0) / 1024 / 1024, 2, ',', '.') . ' MB';
    }
}
