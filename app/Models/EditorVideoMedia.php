<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EditorVideoMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'original_name',
        'file_name',
        'path',
        'mime_type',
        'media_type',
        'size_bytes',
        'duration_seconds',
        'width',
        'height',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'duration_seconds' => 'decimal:2',
    ];

    protected $appends = ['url', 'size_label'];

    public function project()
    {
        return $this->belongsTo(EditorVideoProject::class, 'project_id');
    }

    public function clips()
    {
        return $this->hasMany(EditorVideoClip::class, 'media_id');
    }

    public function getUrlAttribute()
    {
        return Storage::url($this->path);
    }

    public function getSizeLabelAttribute()
    {
        $bytes = (int) $this->size_bytes;
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }
}
