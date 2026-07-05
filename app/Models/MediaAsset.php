<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MediaAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_name',
        'stored_name',
        'mime_type',
        'media_type',
        'extension',
        'size_bytes',
        'duration_seconds',
        'width',
        'height',
        'storage_path',
        'public_url',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'duration_seconds' => 'decimal:2',
        'size_bytes' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    protected $appends = ['url', 'size_label'];

    public function getUrlAttribute(): string
    {
        if (!empty($this->public_url)) {
            return $this->public_url;
        }

        return $this->storage_path ? Storage::disk('public')->url($this->storage_path) : '';
    }

    public function getSizeLabelAttribute(): string
    {
        $bytes = (int) $this->size_bytes;

        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2).' GB';
        if ($bytes >= 1048576) return round($bytes / 1048576, 2).' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 2).' KB';

        return $bytes.' B';
    }
}
