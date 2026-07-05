<?php

namespace App\Services;

use App\Models\Video;
use App\Models\VideoActivity;
use Illuminate\Support\Facades\Auth;

class VideoActivityService
{
    public function log(?Video $video, string $action, string $title, ?string $description = null, array $payload = []): VideoActivity
    {
        return VideoActivity::create([
            'user_id' => Auth::id(),
            'video_id' => $video?->id,
            'action' => $action,
            'title' => $title,
            'description' => $description,
            'payload' => $payload,
        ]);
    }
}
