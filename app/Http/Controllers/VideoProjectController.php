<?php

namespace App\Http\Controllers;

use App\Models\VideoProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoProjectController extends Controller
{
    public function index()
    {
        $projects = VideoProject::where(function ($query) {
                $query->where('user_id', Auth::id())
                    ->orWhereNull('user_id');
            })
            ->withCount(['videos', 'exports'])
            ->latest()
            ->paginate(12);

        return view('projects.index', compact('projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        VideoProject::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'description' => $request->description,
            'status' => 'ativo',
        ]);

        return redirect()
            ->route('projects.index')
            ->with('success', 'Projeto criado com sucesso.');
    }

    public function show(VideoProject $project)
    {
        $this->ensureProjectBelongsToCurrentUser($project);

        $videos = $project->videos()
            ->latest()
            ->paginate(12);

        $exports = $project->exports()
            ->with(['video', 'template'])
            ->latest()
            ->limit(10)
            ->get();

        return view('projects.show', compact('project', 'videos', 'exports'));
    }

    public function destroy(VideoProject $project)
    {
        $this->ensureProjectBelongsToCurrentUser($project);

        $project->videos()->update(['video_project_id' => null]);
        $project->exports()->update(['video_project_id' => null]);
        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Projeto excluído. Os vídeos não foram apagados.');
    }

    private function ensureProjectBelongsToCurrentUser(VideoProject $project): void
    {
        if ($project->user_id === null) {
            $project->update([
                'user_id' => Auth::id(),
            ]);

            return;
        }

        abort_unless((int) $project->user_id === (int) Auth::id(), 403);
    }
}
