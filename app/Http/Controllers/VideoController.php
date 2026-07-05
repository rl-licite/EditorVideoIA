<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoFolder;
use App\Models\VideoProject;
use App\Services\VideoActivityService;
use App\Services\VideoMetadataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        $folders = VideoFolder::where('user_id', Auth::id())->orderBy('name')->get();
        $projects = VideoProject::where('user_id', Auth::id())->orderBy('name')->get();

        $videos = Video::where('user_id', Auth::id())
            ->with(['folder', 'project'])
            ->when($request->filled('folder'), function ($query) use ($request) {
                if ($request->folder === 'none') {
                    $query->whereNull('video_folder_id');
                } else {
                    $query->where('video_folder_id', $request->folder);
                }
            })
            ->when($request->filled('project'), function ($query) use ($request) {
                if ($request->project === 'none') {
                    $query->whereNull('video_project_id');
                } else {
                    $query->where('video_project_id', $request->project);
                }
            })
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where('original_name', 'like', '%' . $request->q . '%');
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('videos.index', compact('videos', 'folders', 'projects'));
    }

    public function create()
    {
        $folders = VideoFolder::where('user_id', Auth::id())->orderBy('name')->get();
        $projects = VideoProject::where('user_id', Auth::id())->orderBy('name')->get();

        return view('videos.create', compact('folders', 'projects'));
    }

    public function store(Request $request, VideoMetadataService $metadataService, VideoActivityService $activityService)
    {
        $request->validate([
            'video_folder_id' => ['nullable'],
            'video_project_id' => ['nullable'],
            'videos' => ['required', 'array'],
            'videos.*' => ['required', 'file', 'mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-matroska,video/webm', 'max:512000'],
        ]);

        $folderId = $request->video_folder_id ?: null;
        $projectId = $request->video_project_id ?: null;

        if ($folderId) {
            VideoFolder::where('user_id', Auth::id())->findOrFail($folderId);
        }

        if ($projectId) {
            VideoProject::where('user_id', Auth::id())->findOrFail($projectId);
        }

        $total = 0;

        foreach ($request->file('videos') as $file) {
            $extension = strtolower($file->getClientOriginalExtension());
            $storedName = now()->format('YmdHis') . '_' . Str::random(16) . '.' . $extension;
            $path = $file->storeAs('videos/originais', $storedName, 'public');

            $video = Video::create([
                'user_id' => Auth::id(),
                'video_folder_id' => $folderId,
                'video_project_id' => $projectId,
                'original_name' => $file->getClientOriginalName(),
                'stored_name' => $storedName,
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'status' => 'enviado',
            ]);

            $activityService->log($video, 'upload', 'Vídeo enviado', 'Arquivo recebido e salvo na biblioteca.');
            $metadataService->analyze($video);

            $total++;
        }

        return redirect()->route('videos.index')->with('success', $total . ' vídeo(s) enviado(s).');
    }

    public function show(Video $video)
    {
        abort_unless($video->user_id === Auth::id(), 403);

        $folders = VideoFolder::where('user_id', Auth::id())->orderBy('name')->get();
        $projects = VideoProject::where('user_id', Auth::id())->orderBy('name')->get();
        $activities = $video->activities()->latest()->limit(10)->get();

        return view('videos.show', compact('video', 'folders', 'projects', 'activities'));
    }

    public function updateFolder(Request $request, Video $video, VideoActivityService $activityService)
    {
        abort_unless($video->user_id === Auth::id(), 403);

        $folderId = $request->video_folder_id ?: null;
        $projectId = $request->video_project_id ?: null;

        if ($folderId) {
            VideoFolder::where('user_id', Auth::id())->findOrFail($folderId);
        }

        if ($projectId) {
            VideoProject::where('user_id', Auth::id())->findOrFail($projectId);
        }

        $video->update([
            'video_folder_id' => $folderId,
            'video_project_id' => $projectId,
        ]);

        $activityService->log($video, 'organizacao', 'Organização atualizada', 'Pasta/projeto do vídeo foram atualizados.');

        return redirect()->route('videos.show', $video)->with('success', 'Organização atualizada.');
    }

    public function analyze(Video $video, VideoMetadataService $metadataService)
    {
        abort_unless($video->user_id === Auth::id(), 403);

        $metadataService->analyze($video);

        return redirect()->route('videos.show', $video)->with('success', 'Vídeo analisado novamente.');
    }

    public function destroy(Video $video, VideoActivityService $activityService)
    {
        abort_unless($video->user_id === Auth::id(), 403);

        $activityService->log($video, 'exclusao', 'Vídeo excluído', 'Vídeo removido da biblioteca.');

        if ($video->path && Storage::disk('public')->exists($video->path)) {
            Storage::disk('public')->delete($video->path);
        }

        if ($video->thumbnail_path && Storage::disk('public')->exists($video->thumbnail_path)) {
            Storage::disk('public')->delete($video->thumbnail_path);
        }

        $video->delete();

        return redirect()->route('videos.index')->with('success', 'Vídeo excluído.');
    }
}
