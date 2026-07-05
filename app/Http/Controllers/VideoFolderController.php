<?php

namespace App\Http\Controllers;

use App\Models\VideoFolder;
use App\Services\VideoActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoFolderController extends Controller
{
    public function index()
    {
        $folders = VideoFolder::where('user_id', Auth::id())
            ->withCount('videos')
            ->latest()
            ->get();

        return view('folders.index', compact('folders'));
    }

    public function store(Request $request, VideoActivityService $activityService)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'color' => ['required', 'string', 'max:20'],
        ]);

        $folder = VideoFolder::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'color' => $request->color,
        ]);

        $activityService->log(
            null,
            'pasta_criada',
            'Pasta criada',
            'A pasta "' . $folder->name . '" foi criada.'
        );

        return redirect()
            ->route('folders.index')
            ->with('success', 'Pasta criada com sucesso.');
    }

    public function destroy(VideoFolder $folder, VideoActivityService $activityService)
    {
        abort_unless($folder->user_id === Auth::id(), 403);

        $name = $folder->name;

        $folder->videos()->update(['video_folder_id' => null]);
        $folder->delete();

        $activityService->log(
            null,
            'pasta_excluida',
            'Pasta excluída',
            'A pasta "' . $name . '" foi excluída. Os vídeos ficaram sem pasta.'
        );

        return redirect()
            ->route('folders.index')
            ->with('success', 'Pasta excluída. Os vídeos ficaram sem pasta.');
    }
}
