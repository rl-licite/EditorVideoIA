<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoExport extends Model
{
    protected $fillable = [
        'user_id',
        'video_id',
        'video_template_id',
        'video_project_id',
        'status',
        'progress',
        'output_path',
        'ffmpeg_command',
        'error_message',
        'started_at',
        'finished_at',
        'scheduled_at',
        'priority',
        'retry_count',
        'render_seconds',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function template()
    {
        return $this->belongsTo(VideoTemplate::class, 'video_template_id');
    }

    public function project()
    {
        return $this->belongsTo(VideoProject::class, 'video_project_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pendente' => 'Pendente',
            'agendado' => 'Agendado',
            'processando' => 'Processando',
            'concluido' => 'Concluído',
            'erro' => 'Erro',
            default => ucfirst($this->status),
        };
    }
}
