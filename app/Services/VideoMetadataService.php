<?php

namespace App\Services;

use App\Models\Video;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class VideoMetadataService
{
    public function __construct(
        private VideoActivityService $activityService
    ) {}

    public function analyze(Video $video): void
    {
        $absolutePath = Storage::disk('public')->path($video->path);

        if (!file_exists($absolutePath)) {
            $video->update([
                'status' => 'erro',
                'error_message' => 'Arquivo não encontrado no storage.',
            ]);

            $this->activityService->log(
                $video,
                'erro_analise',
                'Erro ao analisar vídeo',
                'O arquivo não foi encontrado no storage.'
            );

            return;
        }

        $metadata = $this->readMetadata($absolutePath);
        $thumbnailPath = $this->createThumbnail($video, $absolutePath);

        $video->update([
            'thumbnail_path' => $thumbnailPath,
            'duration' => $metadata['duration'] ?? null,
            'width' => $metadata['width'] ?? null,
            'height' => $metadata['height'] ?? null,
            'format' => $metadata['format'] ?? null,
            'codec' => $metadata['codec'] ?? null,
            'fps' => $metadata['fps'] ?? null,
            'status' => 'analisado',
            'error_message' => null,
        ]);

        $this->activityService->log(
            $video,
            'analise',
            'Vídeo analisado',
            'Dados técnicos extraídos com FFprobe e thumbnail gerada com FFmpeg.',
            [
                'duration' => $metadata['duration'] ?? null,
                'width' => $metadata['width'] ?? null,
                'height' => $metadata['height'] ?? null,
                'codec' => $metadata['codec'] ?? null,
                'fps' => $metadata['fps'] ?? null,
            ]
        );
    }

    private function readMetadata(string $absolutePath): array
    {
        $process = new Process([
            'ffprobe',
            '-v', 'quiet',
            '-print_format', 'json',
            '-show_format',
            '-show_streams',
            $absolutePath,
        ]);

        $process->setTimeout(60);
        $process->run();

        if (!$process->isSuccessful()) {
            return [];
        }

        $json = json_decode($process->getOutput(), true);

        if (!is_array($json)) {
            return [];
        }

        $videoStream = collect($json['streams'] ?? [])
            ->firstWhere('codec_type', 'video');

        $format = $json['format'] ?? [];

        return [
            'duration' => isset($format['duration']) ? (float) $format['duration'] : null,
            'width' => $videoStream['width'] ?? null,
            'height' => $videoStream['height'] ?? null,
            'format' => $format['format_name'] ?? null,
            'codec' => $videoStream['codec_name'] ?? null,
            'fps' => $this->parseFps($videoStream['r_frame_rate'] ?? null),
        ];
    }

    private function parseFps(?string $rate): ?float
    {
        if (!$rate || !str_contains($rate, '/')) {
            return null;
        }

        [$num, $den] = explode('/', $rate);

        if ((float) $den == 0.0) {
            return null;
        }

        return round(((float) $num) / ((float) $den), 2);
    }

    private function createThumbnail(Video $video, string $absolutePath): ?string
    {
        $folder = 'videos/thumbs';
        Storage::disk('public')->makeDirectory($folder);

        $thumbnailName = pathinfo($video->stored_name, PATHINFO_FILENAME) . '.jpg';
        $relativePath = $folder . '/' . $thumbnailName;
        $thumbnailAbsolutePath = Storage::disk('public')->path($relativePath);

        $process = new Process([
            'ffmpeg',
            '-y',
            '-i', $absolutePath,
            '-ss', '00:00:01',
            '-vframes', '1',
            '-vf', 'scale=360:-1',
            $thumbnailAbsolutePath,
        ]);

        $process->setTimeout(60);
        $process->run();

        if (!$process->isSuccessful() || !file_exists($thumbnailAbsolutePath)) {
            return null;
        }

        return $relativePath;
    }
}
