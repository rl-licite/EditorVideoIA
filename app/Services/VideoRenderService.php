<?php

namespace App\Services;

use App\Models\VideoExport;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class VideoRenderService
{
    public function __construct(
        private SystemLogService $logService
    ) {}

    public function render(VideoExport $export): void
    {
        $start = microtime(true);

        $export->load(['video.subtitles', 'template']);

        $video = $export->video;
        $template = $export->template;

        if (!$video || !$template) {
            $this->fail($export, 'Vídeo ou template não encontrado.');
            return;
        }

        $inputPath = Storage::disk('public')->path($video->path);

        if (!file_exists($inputPath)) {
            $this->fail($export, 'Arquivo original não encontrado.');
            return;
        }

        Storage::disk('public')->makeDirectory('videos/processados');

        $outputName = 'render_' . $export->id . '_' . pathinfo($video->stored_name, PATHINFO_FILENAME) . '.mp4';
        $outputRelativePath = 'videos/processados/' . $outputName;
        $outputPath = Storage::disk('public')->path($outputRelativePath);

        [$width, $height] = explode('x', $template->resolution);
        $vf = $this->buildVideoFilter((int) $width, (int) $height, $template, $video);

        $command = ['ffmpeg', '-y'];

        if ($template->clip_start > 0) {
            $command[] = '-ss';
            $command[] = (string) $template->clip_start;
        }

        $command[] = '-i';
        $command[] = $inputPath;

        if ($template->clip_duration) {
            $command[] = '-t';
            $command[] = (string) $template->clip_duration;
        }

        $command = array_merge($command, [
            '-vf', $vf,
            '-c:v', 'libx264',
            '-preset', 'veryfast',
            '-crf', '23',
            '-c:a', 'aac',
            '-b:a', '128k',
            '-movflags', '+faststart',
            $outputPath,
        ]);

        $export->update([
            'status' => 'processando',
            'progress' => 10,
            'started_at' => now(),
            'ffmpeg_command' => implode(' ', array_map(fn($part) => str_contains($part, ' ') ? '"' . $part . '"' : $part, $command)),
        ]);

        $this->logService->info('render', 'Renderização iniciada.', ['export_id' => $export->id]);

        $process = new Process($command);
        $process->setTimeout(1200);
        $process->run(function () use ($export) {
            if ($export->progress < 90) {
                $export->increment('progress', 4);
            }
        });

        if (!$process->isSuccessful() || !file_exists($outputPath)) {
            $this->fail($export, $process->getErrorOutput() ?: 'Erro desconhecido ao renderizar.');
            return;
        }

        $seconds = (int) round(microtime(true) - $start);

        $export->update([
            'status' => 'concluido',
            'progress' => 100,
            'output_path' => $outputRelativePath,
            'error_message' => null,
            'finished_at' => now(),
            'render_seconds' => $seconds,
        ]);

        $this->logService->info('render', 'Renderização concluída.', [
            'export_id' => $export->id,
            'seconds' => $seconds,
        ]);
    }

    private function fail(VideoExport $export, string $message): void
    {
        $export->update([
            'status' => 'erro',
            'progress' => 100,
            'error_message' => $message,
            'finished_at' => now(),
            'retry_count' => ($export->retry_count ?? 0) + 1,
        ]);

        $this->logService->error('render', 'Erro na renderização.', [
            'export_id' => $export->id,
            'message' => $message,
        ]);
    }

    private function buildVideoFilter(int $width, int $height, $template, $video): string
    {
        $filters = [
            "scale={$width}:{$height}:force_original_aspect_ratio=increase",
            "crop={$width}:{$height}"
        ];

        if ($template->auto_subtitle && $video->subtitles->count() > 0) {
            foreach ($video->subtitles as $subtitle) {
                $safe = $this->escapeDrawText($subtitle->text);
                $pos = $this->textPosition($template->subtitle_position ?: 'bottom');
                $color = $template->subtitle_color ?: 'white';
                $filters[] = "drawtext=text='{$safe}':fontcolor={$color}:fontsize=48:box=1:boxcolor=black@0.65:boxborderw=18:enable='between(t,{$subtitle->start_second},{$subtitle->end_second})':{$pos}";
            }
        }

        if ($template->overlay_text) {
            $safe = $this->escapeDrawText($template->overlay_text);
            $pos = $this->textPosition($template->overlay_position);
            $filters[] = "drawtext=text='{$safe}':fontcolor=white:fontsize=54:box=1:boxcolor=black@0.55:boxborderw=18:{$pos}";
        }

        if ($template->watermark_text) {
            $safe = $this->escapeDrawText($template->watermark_text);
            $pos = $this->textPosition($template->watermark_position);
            $filters[] = "drawtext=text='{$safe}':fontcolor=white@0.85:fontsize=34:box=1:boxcolor=black@0.35:boxborderw=10:{$pos}";
        }

        return implode(',', $filters);
    }

    private function textPosition(string $position): string
    {
        return match ($position) {
            'top' => "x=(w-text_w)/2:y=60",
            'center' => "x=(w-text_w)/2:y=(h-text_h)/2",
            'bottom' => "x=(w-text_w)/2:y=h-text_h-120",
            'top-left' => "x=40:y=40",
            'top-right' => "x=w-text_w-40:y=40",
            'bottom-left' => "x=40:y=h-text_h-40",
            'bottom-right' => "x=w-text_w-40:y=h-text_h-40",
            default => "x=(w-text_w)/2:y=h-text_h-120",
        };
    }

    private function escapeDrawText(string $text): string
    {
        return str_replace(["\\", ":", "'", "\n", "\r"], ["\\\\", "\\:", "\\'", " ", " "], $text);
    }
}
