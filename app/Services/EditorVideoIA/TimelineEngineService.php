<?php

namespace App\Services\EditorVideoIA;

class TimelineEngineService
{
    public function normalize(array $timeline): array
    {
        $timeline['meta'] = array_merge($timeline['meta'] ?? [], [
            'version' => '6.1-motor-profissional',
            'engine' => 'timeline-profissional',
        ]);

        $timeline['clips'] = array_values(array_filter($timeline['clips'] ?? [], 'is_array'));
        foreach ($timeline['clips'] as &$clip) {
            $clip['start'] = max(0, (float) ($clip['start'] ?? 0));
            $clip['duration'] = max(0.5, (float) ($clip['duration'] ?? 1));
            $clip['track_id'] = $clip['track_id'] ?? 'video_1';
            $clip['props'] = is_array($clip['props'] ?? null) ? $clip['props'] : [];
        }
        unset($clip);

        return $timeline;
    }

    public function duration(array $timeline): float
    {
        $max = 0;
        foreach (($timeline['clips'] ?? []) as $clip) {
            $max = max($max, (float) ($clip['start'] ?? 0) + (float) ($clip['duration'] ?? 0));
        }
        return $max;
    }
}
