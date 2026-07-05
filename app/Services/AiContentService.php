<?php

namespace App\Services;

use App\Models\AiContent;
use App\Models\Video;
use App\Models\VideoSubtitle;
use Illuminate\Support\Facades\Auth;

class AiContentService
{
    public function generateForVideo(Video $video, string $theme = ''): array
    {
        $base = trim($theme) ?: pathinfo($video->original_name, PATHINFO_FILENAME);

        $title = $this->makeTitle($base);
        $description = $this->makeDescription($base, $video);
        $hashtags = $this->makeHashtags($base);
        $script = $this->makeScript($base);
        $subtitles = $this->makeSubtitles($base, (int) ($video->duration ?: 15));

        AiContent::create([
            'user_id' => Auth::id(),
            'video_id' => $video->id,
            'type' => 'title',
            'title' => 'Título sugerido',
            'content' => $title,
        ]);

        AiContent::create([
            'user_id' => Auth::id(),
            'video_id' => $video->id,
            'type' => 'description',
            'title' => 'Descrição sugerida',
            'content' => $description,
        ]);

        AiContent::create([
            'user_id' => Auth::id(),
            'video_id' => $video->id,
            'type' => 'hashtags',
            'title' => 'Hashtags sugeridas',
            'content' => implode(' ', $hashtags),
        ]);

        AiContent::create([
            'user_id' => Auth::id(),
            'video_id' => $video->id,
            'type' => 'script',
            'title' => 'Roteiro sugerido',
            'content' => $script,
        ]);

        $video->subtitles()->delete();

        foreach ($subtitles as $subtitle) {
            VideoSubtitle::create([
                'user_id' => Auth::id(),
                'video_id' => $video->id,
                'text' => $subtitle['text'],
                'start_second' => $subtitle['start'],
                'end_second' => $subtitle['end'],
            ]);
        }

        return compact('title', 'description', 'hashtags', 'script', 'subtitles');
    }

    private function makeTitle(string $base): string
    {
        return 'Veja isso: ' . ucfirst($base);
    }

    private function makeDescription(string $base, Video $video): string
    {
        return "Conteúdo gerado automaticamente para o vídeo \"{$video->original_name}\".\n\nTema: {$base}\n\nUse este texto como base para Reels, Shorts ou TikTok.";
    }

    private function makeHashtags(string $base): array
    {
        $clean = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($base));

        return array_filter([
            '#videos',
            '#reels',
            '#shorts',
            '#conteudo',
            $clean ? '#' . substr($clean, 0, 24) : '#viral',
        ]);
    }

    private function makeScript(string $base): string
    {
        return "Abertura: chame a atenção nos primeiros 3 segundos.\n"
            . "Meio: mostre o ponto principal sobre {$base}.\n"
            . "Fechamento: incentive o público a salvar, compartilhar ou comentar.";
    }

    private function makeSubtitles(string $base, int $duration): array
    {
        $duration = max(9, min($duration, 60));

        return [
            ['start' => 0, 'end' => min(3, $duration), 'text' => 'Olha esse detalhe'],
            ['start' => min(3, $duration), 'end' => min(7, $duration), 'text' => ucfirst($base)],
            ['start' => min(7, $duration), 'end' => min(12, $duration), 'text' => 'Resultado pronto em poucos segundos'],
        ];
    }
}
