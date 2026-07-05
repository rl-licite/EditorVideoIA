<?php

namespace App\Http\Controllers;

use App\Models\AiContent;
use App\Models\Video;
use App\Models\VideoSubtitle;
use App\Services\AiContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiController extends Controller
{
    public function index()
    {
        $videos = Video::where('user_id', Auth::id())
            ->latest()
            ->limit(100)
            ->get();

        $contents = AiContent::where('user_id', Auth::id())
            ->with('video')
            ->latest()
            ->paginate(20);

        return view('ai.index', compact('videos', 'contents'));
    }

    public function generate(Request $request, AiContentService $service)
    {
        $request->validate([
            'video_id' => ['required', 'exists:videos,id'],
            'theme' => ['nullable', 'string', 'max:120'],
        ]);

        $video = Video::where('user_id', Auth::id())->findOrFail($request->video_id);

        $service->generateForVideo($video, $request->theme ?? '');

        return redirect()
            ->route('ai.index')
            ->with('success', 'Conteúdo inteligente gerado com sucesso.');
    }

    public function subtitles(Video $video)
    {
        abort_unless($video->user_id === Auth::id(), 403);

        $subtitles = $video->subtitles()->orderBy('start_second')->get();

        return view('ai.subtitles', compact('video', 'subtitles'));
    }

    public function storeSubtitle(Request $request, Video $video)
    {
        abort_unless($video->user_id === Auth::id(), 403);

        $request->validate([
            'text' => ['required', 'string', 'max:160'],
            'start_second' => ['required', 'integer', 'min:0'],
            'end_second' => ['required', 'integer', 'min:1'],
        ]);

        VideoSubtitle::create([
            'user_id' => Auth::id(),
            'video_id' => $video->id,
            'text' => $request->text,
            'start_second' => $request->start_second,
            'end_second' => $request->end_second,
        ]);

        return redirect()
            ->route('ai.subtitles', $video)
            ->with('success', 'Legenda adicionada.');
    }

    public function deleteSubtitle(VideoSubtitle $subtitle)
    {
        abort_unless($subtitle->user_id === Auth::id(), 403);

        $video = $subtitle->video;
        $subtitle->delete();

        return redirect()
            ->route('ai.subtitles', $video)
            ->with('success', 'Legenda excluída.');
    }
}
