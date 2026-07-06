<?php

namespace App\Services\EditorVideoIA;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FFmpegRenderService
{
    public function ffmpegAvailable(): bool
    {
        $output = shell_exec('ffmpeg -version 2>NUL');
        return !empty(trim((string) $output));
    }

    public function renderPreview(array $job, array $timeline, array $settings = []): array
    {
        if (!$this->ffmpegAvailable()) {
            throw new \RuntimeException('FFmpeg não encontrado no PATH.');
        }

        Storage::disk('public')->makeDirectory('editorvideoia/exports');

        $format = $settings['format'] ?? 'mp4';
        $width = 1280;
        $height = 720;

        if (($settings['resolution'] ?? '') === '1920x1080') {
            $width = 1920;
            $height = 1080;
        }

        if (($settings['resolution'] ?? '') === '3840x2160') {
            $width = 3840;
            $height = 2160;
        }

        $fileName = 'render_' . now()->format('Ymd_His') . '_' . Str::slug($job['name'] ?? 'video') . '.' . $format;
        $relativePath = 'editorvideoia/exports/' . $fileName;
        $absolutePath = Storage::disk('public')->path($relativePath);

        $duration = max(3, min(30, (int) ($job['duration'] ?? 6)));

        $cmd = sprintf(
            'ffmpeg -y -f lavfi -i color=c=black:s=%dx%d:d=%d -vf "drawtext=text=\'EditorVideoIA Render\':fontcolor=white:fontsize=48:x=(w-text_w)/2:y=(h-text_h)/2" -c:v libx264 -pix_fmt yuv420p %s 2>&1',
            $width,
            $height,
            $duration,
            escapeshellarg($absolutePath)
        );

        $log = shell_exec($cmd);

        if (!file_exists($absolutePath)) {
            throw new \RuntimeException('Falha ao gerar vídeo com FFmpeg: ' . $log);
        }

        return [
            'output_file' => $relativePath,
            'output_name' => $fileName,
            'status' => 'concluido',
            'progress' => 100,
            'message' => 'Render MP4 gerado com FFmpeg.',
            'ffmpeg_log' => mb_substr((string) $log, -2000),
        ];
    }
}
