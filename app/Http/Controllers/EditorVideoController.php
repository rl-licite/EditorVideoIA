<?php

namespace App\Http\Controllers;

use App\Models\EditorVideoClip;
use App\Models\EditorVideoMedia;
use App\Models\EditorVideoProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditorVideoController extends Controller
{
    public function index()
    {
        $projects = EditorVideoProject::latest()->get();

        if ($projects->isEmpty()) {
            $project = EditorVideoProject::create([
                'title' => 'Projeto de teste',
                'settings' => ['fps' => 30, 'width' => 1920, 'height' => 1080],
            ]);
            return redirect()->route('editor-video.show', $project);
        }

        return redirect()->route('editor-video.show', $projects->first());
    }

    public function show(EditorVideoProject $project = null)
    {
        $project = $project ?: EditorVideoProject::firstOrCreate(
            ['title' => 'Projeto de teste'],
            ['settings' => ['fps' => 30, 'width' => 1920, 'height' => 1080]]
        );

        $project->load(['media', 'clips.media']);

        return view('editor-video.index', [
            'project' => $project,
            'mediaItems' => $project->media,
            'clips' => $project->clips,
        ]);
    }

    public function storeProject(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string'],
        ]);

        $project = EditorVideoProject::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'settings' => ['fps' => 30, 'width' => 1920, 'height' => 1080],
        ]);

        return redirect()->route('editor-video.show', $project);
    }

    public function saveProject(Request $request, EditorVideoProject $project)
    {
        $project->update([
            'title' => $request->input('title', $project->title),
            'settings' => array_merge($project->settings ?? [], $request->input('settings', [])),
        ]);

        return response()->json(['ok' => true, 'message' => 'Projeto salvo com sucesso.']);
    }

    public function uploadMedia(Request $request, EditorVideoProject $project)
    {
        $request->validate([
            'media' => ['required', 'file', 'max:512000', 'mimes:mp4,mov,avi,mkv,webm,mp3,wav,aac,flac,jpg,jpeg,png,webp,svg'],
            'duration_seconds' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'integer', 'min:0'],
            'height' => ['nullable', 'integer', 'min:0'],
        ]);

        $file = $request->file('media');
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();
        $mediaType = $this->detectMediaType($mime, $extension);
        $safeBaseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $fileName = now()->format('YmdHis') . '_' . Str::random(8) . '_' . $safeBaseName . '.' . $extension;
        $path = $file->storeAs('editor-video/projects/' . $project->id, $fileName, 'public');

        $media = EditorVideoMedia::create([
            'project_id' => $project->id,
            'original_name' => $file->getClientOriginalName(),
            'file_name' => $fileName,
            'path' => $path,
            'mime_type' => $mime,
            'media_type' => $mediaType,
            'size_bytes' => $file->getSize(),
            'duration_seconds' => $request->input('duration_seconds'),
            'width' => $request->input('width'),
            'height' => $request->input('height'),
            'metadata' => [
                'extension' => $extension,
                'uploaded_by' => 'local',
            ],
        ]);

        return response()->json(['ok' => true, 'media' => $media->fresh()]);
    }

    public function deleteMedia(EditorVideoMedia $media)
    {
        Storage::disk('public')->delete($media->path);
        $media->delete();

        return response()->json(['ok' => true]);
    }

    public function storeClip(Request $request, EditorVideoProject $project)
    {
        $data = $request->validate([
            'media_id' => ['nullable', 'exists:editor_video_media,id'],
            'name' => ['nullable', 'string', 'max:190'],
            'track' => ['required', 'string', 'max:50'],
            'start_time' => ['nullable', 'numeric', 'min:0'],
            'duration' => ['nullable', 'numeric', 'min:0.1'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $media = isset($data['media_id']) ? EditorVideoMedia::find($data['media_id']) : null;
        $duration = (float) ($data['duration'] ?? $media?->duration_seconds ?? 5);
        $start = (float) ($data['start_time'] ?? 0);

        $clip = EditorVideoClip::create([
            'project_id' => $project->id,
            'media_id' => $data['media_id'] ?? null,
            'name' => $data['name'] ?? ($media?->original_name ?? 'Clipe'),
            'track' => $data['track'],
            'start_time' => $start,
            'duration' => $duration,
            'end_time' => $start + $duration,
            'position' => $data['position'] ?? 0,
            'settings' => [
                'volume' => 100,
                'speed' => 1,
                'transition' => 'none',
            ],
        ]);

        return response()->json(['ok' => true, 'clip' => $clip->load('media')]);
    }

    public function updateClip(Request $request, EditorVideoClip $clip)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:190'],
            'track' => ['nullable', 'string', 'max:50'],
            'start_time' => ['nullable', 'numeric', 'min:0'],
            'duration' => ['nullable', 'numeric', 'min:0.1'],
            'position' => ['nullable', 'integer', 'min:0'],
            'settings' => ['nullable', 'array'],
        ]);

        $start = array_key_exists('start_time', $data) ? (float) $data['start_time'] : (float) $clip->start_time;
        $duration = array_key_exists('duration', $data) ? (float) $data['duration'] : (float) $clip->duration;

        $clip->update([
            'name' => $data['name'] ?? $clip->name,
            'track' => $data['track'] ?? $clip->track,
            'start_time' => $start,
            'duration' => $duration,
            'end_time' => $start + $duration,
            'position' => $data['position'] ?? $clip->position,
            'settings' => array_merge($clip->settings ?? [], $data['settings'] ?? []),
        ]);

        return response()->json(['ok' => true, 'clip' => $clip->fresh()->load('media')]);
    }

    public function deleteClip(EditorVideoClip $clip)
    {
        $clip->delete();

        return response()->json(['ok' => true]);
    }

    private function detectMediaType(?string $mime, string $extension): string
    {
        if ($mime && str_starts_with($mime, 'video/')) return 'video';
        if ($mime && str_starts_with($mime, 'audio/')) return 'audio';
        if ($mime && str_starts_with($mime, 'image/')) return 'image';

        return match ($extension) {
            'mp4', 'mov', 'avi', 'mkv', 'webm' => 'video',
            'mp3', 'wav', 'aac', 'flac' => 'audio',
            'jpg', 'jpeg', 'png', 'webp', 'svg' => 'image',
            default => 'file',
        };
    }
}
