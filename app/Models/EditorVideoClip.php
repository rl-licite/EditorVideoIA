<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EditorVideoClip extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'media_id',
        'name',
        'track',
        'start_time',
        'duration',
        'end_time',
        'position',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'start_time' => 'decimal:2',
        'duration' => 'decimal:2',
        'end_time' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(EditorVideoProject::class, 'project_id');
    }

    public function media()
    {
        return $this->belongsTo(EditorVideoMedia::class, 'media_id');
    }
}
