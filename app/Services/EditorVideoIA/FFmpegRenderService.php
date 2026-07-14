<?php

namespace App\Services\EditorVideoIA;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FFmpegRenderService
{
    public function render(array $data): array
    {
        $input = $data['input'] ?? null;
        $output = $data['output'] ?? ('render_'.now()->format('Ymd_His').'.mp4');

        if (!$input) {
            throw new \InvalidArgumentException('Arquivo de entrada não informado para renderização.');
        }

        $inputPath = $this->resolveInputPath($input);

        if (!file_exists($inputPath)) {
            throw new \RuntimeException('Arquivo de entrada não encontrado: '.$inputPath);
        }

        $projectId = $data['project_id'] ?? 'geral';

        $renderDir = storage_path('app/public/renders/projeto_'.$projectId);

        if (!File::exists($renderDir)) {
            File::makeDirectory($renderDir, 0755, true);
        }

        $safeOutput = Str::slug(pathinfo($output, PATHINFO_FILENAME) ?: 'video');
        $outputName = $safeOutput.'_'.now()->format('Ymd_His').'.mp4';
        $outputPath = $renderDir.DIRECTORY_SEPARATOR.$outputName;

        $ffmpeg = env('FFMPEG_PATH', 'ffmpeg');

        $command = '"'.$ffmpeg.'" -y -i "'.$inputPath.'" -c:v libx264 -preset veryfast -crf 23 -c:a aac -b:a 128k "'.$outputPath.'" 2>&1';

        exec($command, $logs, $exitCode);

        if ($exitCode !== 0 || !file_exists($outputPath)) {
            throw new \RuntimeException('Erro ao renderizar com FFmpeg: '.implode("\n", $logs));
        }

        $relativePath = 'renders/projeto_'.$projectId.'/'.$outputName;

        return [
            'ok' => true,
            'output_name' => $outputName,
            'output_path' => $outputPath,
            'relative_path' => $relativePath,
            'download_url' => asset('storage/'.$relativePath),
            'size_bytes' => filesize($outputPath),
            'logs' => array_slice($logs, -20),
        ];
    }

    protected function resolveInputPath(string $input): string
    {
        if (file_exists($input)) {
            return $input;
        }

        $publicPath = public_path(ltrim($input, '/'));

        if (file_exists($publicPath)) {
            return $publicPath;
        }

        $storagePublic = storage_path('app/public/'.ltrim(str_replace('/storage/', '', $input), '/'));

        if (file_exists($storagePublic)) {
            return $storagePublic;
        }

        return $input;
    }
}
