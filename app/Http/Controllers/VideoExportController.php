<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessVideoExportJob;
use App\Models\Video;
use App\Models\VideoExport;
use App\Models\VideoFolder;
use App\Models\VideoProject;
use App\Models\VideoTemplate;
use App\Services\CreditService;
use App\Services\VideoActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VideoExportController extends Controller
{
    public function index(Request $request)
    {
        $exports = VideoExport::where('user_id', Auth::id())
            ->with(['video', 'template', 'project'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('exports.index', compact('exports'));
    }

    public function create()
    {
        $templates = VideoTemplate::where('user_id', Auth::id())->orderBy('name')->get();
        $folders = VideoFolder::where('user_id', Auth::id())->orderBy('name')->get();
        $projects = VideoProject::where('user_id', Auth::id())->orderBy('name')->get();
        $videos = Video::where('user_id', Auth::id())->latest()->limit(100)->get();

        return view('exports.create', compact('templates', 'folders', 'projects', 'videos'));
    }

    public function store(Request $request, VideoActivityService $activityService, CreditService $creditService)
    {
        $request->validate([
            'video_template_id' => ['required', 'exists:video_templates,id'],
            'mode' => ['required', 'in:selected,folder,project,all'],
            'video_ids' => ['nullable', 'array'],
            'video_folder_id' => ['nullable'],
            'video_project_id' => ['nullable'],
            'scheduled_at' => ['nullable', 'date'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:10'],
        ]);

        $user = Auth::user();
        $template = VideoTemplate::where('user_id', $user->id)->findOrFail($request->video_template_id);

        $videosQuery = Video::where('user_id', $user->id);

        if ($request->mode === 'selected') {
            $videosQuery->whereIn('id', $request->video_ids ?? []);
        }

        if ($request->mode === 'folder') {
            $request->video_folder_id === 'none'
                ? $videosQuery->whereNull('video_folder_id')
                : $videosQuery->where('video_folder_id', $request->video_folder_id);
        }

        if ($request->mode === 'project') {
            $videosQuery->where('video_project_id', $request->video_project_id);
        }

        $videos = $videosQuery->get();

        if ($videos->count() === 0) {
            return back()->withErrors(['Nenhum vídeo encontrado para processar.']);
        }

        $cost = $videos->count();

        if (($user->credits_balance ?? 0) < $cost) {
            return back()->withErrors(['Créditos insuficientes.']);
        }

        $creditService->charge($user, $cost, 'Processamento de ' . $cost . ' vídeo(s).');

        $scheduledAt = $request->scheduled_at ? now()->parse($request->scheduled_at) : null;

        foreach ($videos as $video) {
            $status = $scheduledAt && $scheduledAt->isFuture() ? 'agendado' : 'pendente';

            $export = VideoExport::create([
                'user_id' => $user->id,
                'video_id' => $video->id,
                'video_template_id' => $template->id,
                'video_project_id' => $video->video_project_id,
                'status' => $status,
                'progress' => 0,
                'scheduled_at' => $scheduledAt,
                'priority' => $request->priority ?? 0,
            ]);

            $activityService->log($video, 'fila', 'Vídeo enviado para fila', 'Processamento criado.');

            if ($status === 'pendente') {
                ProcessVideoExportJob::dispatch($export->id);
            }
        }

        return redirect()->route('exports.index')->with('success', $videos->count() . ' vídeo(s) enviados para a fila.');
    }

    public function show(VideoExport $export)
    {
        abort_unless($export->user_id === Auth::id(), 403);
        $export->load(['video', 'template', 'project']);
        return view('exports.show', compact('export'));
    }

    public function download(VideoExport $export)
    {
        abort_unless($export->user_id === Auth::id(), 403);
        abort_unless($export->output_path && Storage::disk('public')->exists($export->output_path), 404);
        return Storage::disk('public')->download($export->output_path);
    }

    public function destroy(VideoExport $export)
    {
        abort_unless($export->user_id === Auth::id(), 403);

        if ($export->output_path && Storage::disk('public')->exists($export->output_path)) {
            Storage::disk('public')->delete($export->output_path);
        }

        $export->delete();

        return redirect()->route('exports.index')->with('success', 'Exportação excluída.');
    }
}
