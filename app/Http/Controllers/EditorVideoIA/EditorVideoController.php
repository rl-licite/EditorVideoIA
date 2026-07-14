<?php

namespace App\Http\Controllers\EditorVideoIA;

use App\Http\Controllers\Controller;
use App\Models\EditorVideoProject;
use App\Models\MediaAsset;
use App\Models\VideoTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\EditorVideoIA\FFmpegRenderService;
use ZipArchive;

class EditorVideoController extends Controller
{
    protected FFmpegRenderService $ffmpeg;

    public function __construct(FFmpegRenderService $ffmpeg)
    {
        $this->ffmpeg = $ffmpeg;
    }
    public function index(Request $request)
    {
        $project = $this->resolveProject($request);

        $timeline = $this->normalizeTimeline($project->timeline_data);

        if ($project->timeline_data !== $timeline) {
            $project->timeline_data = $timeline;
            $project->save();
        }

        return view('editorvideoia.index', [
            'project' => $project,
            'timeline' => $timeline,
            'assets' => MediaAsset::latest()->get(),
            'templates' => VideoTemplate::latest()->get(),
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'media' => ['required'],
            'media.*' => ['file', 'max:512000', 'mimes:mp4,mov,avi,mkv,webm,mp3,wav,aac,flac,jpg,jpeg,png,webp,svg'],
        ]);

        $files = $request->file('media');
        if (!is_array($files)) {
            $files = [$files];
        }

        $assets = [];

        foreach ($files as $file) {
            if (!$file) {
                continue;
            }

            $extension = strtolower($file->getClientOriginalExtension());
            $storedName = Str::uuid()->toString().'.'.$extension;
            $path = $file->storeAs('editorvideoia/media', $storedName, 'public');
            $mime = $file->getMimeType() ?: $this->mimeFromExtension($extension);
            $mediaType = $this->detectMediaType($mime, $extension);
            [$width, $height] = $this->detectImageSize($file->getRealPath(), $mediaType);

            $asset = MediaAsset::create([
                'original_name' => $file->getClientOriginalName(),
                'stored_name' => $storedName,
                'mime_type' => $mime,
                'media_type' => $mediaType,
                'extension' => $extension,
                'size_bytes' => $file->getSize(),
                'duration_seconds' => null,
                'width' => $width,
                'height' => $height,
                'storage_path' => $path,
                'public_url' => route('editor-video.media.stream', $path),
                'metadata' => ['fase' => '6.5', 'bloco' => '1', 'modulo' => 'upload-multiplo-estavel'],
            ]);

            $assets[] = $this->assetPayload($asset);
        }

        return response()->json([
            'ok' => true,
            'message' => count($assets) === 1 ? 'Mídia importada com sucesso.' : count($assets).' mídias importadas com sucesso.',
            'asset' => $assets[0] ?? null,
            'assets' => $assets,
        ]);
    }

    public function stream(string $path)
    {
        $asset = MediaAsset::where('storage_path', $path)->firstOrFail();
        $fullPath = Storage::disk('public')->path($asset->storage_path);

        abort_unless(is_file($fullPath), 404, 'Arquivo de midia nao encontrado.');

        return response()->file($fullPath, [
            'Content-Type' => $asset->mime_type ?: 'application/octet-stream',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=3600',
            'Content-Disposition' => 'inline; filename="'.addslashes($asset->original_name).'"',
        ]);
    }

    public function saveProject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'timeline_data' => ['required', 'array'],
            'duration_seconds' => ['nullable', 'numeric'],
            'settings' => ['nullable', 'array'],
        ]);

        $timeline = $this->normalizeTimeline($validated['timeline_data']);

        $project = $this->resolveProject($request);
        $project->name = $validated['name'] ?? $project->name ?? 'Projeto EditorVideoIA';
        $project->timeline_data = $timeline;
        $project->duration_seconds = (int) ($validated['duration_seconds'] ?? 0);
        $project->settings = $validated['settings'] ?? ['fps' => 30, 'resolution' => '1920x1080'];
        $project->save();

        return response()->json([
            'ok' => true,
            'message' => 'Projeto salvo com sucesso.',
            'project' => $project->fresh(),
            'timeline' => $this->normalizeTimeline($project->fresh()->timeline_data),
        ]);
    }

    public function loadProject(Request $request): JsonResponse
    {
        $project = $this->resolveProject($request);

        return response()->json([
            'ok' => true,
            'project' => $project,
            'timeline' => $this->normalizeTimeline($project->timeline_data),
            'assets' => MediaAsset::latest()->get()->map(fn ($asset) => $this->assetPayload($asset))->values(),
            'templates' => VideoTemplate::latest()->get()->map(fn ($template) => $this->templatePayload($template))->values(),
        ]);
    }


    /**
     * Resolve o projeto ativo sem prender o editor no id fixo 1.
     * Fase 6.5 Bloco 1: permite abrir/salvar por ?project_id=ID e mantém um projeto padrão apenas como fallback.
     */
    private function resolveProject(Request $request): EditorVideoProject
    {
        $projectId = (int) $request->input('project_id', $request->query('project_id', 0));

        if ($projectId > 0) {
            $project = EditorVideoProject::find($projectId);
            if ($project) {
                return $project;
            }
        }

        $project = EditorVideoProject::latest('id')->first();

        if ($project) {
            return $project;
        }

        return EditorVideoProject::create([
            'name' => 'Projeto EditorVideoIA',
            'timeline_data' => $this->defaultTimeline(),
            'duration_seconds' => 0,
            'settings' => ['fps' => 30, 'resolution' => '1920x1080'],
        ]);
    }


    public function saveTemplateFromEditor(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'format' => ['required', 'string', 'max:30'],
            'resolution' => ['nullable', 'string', 'max:30'],
            'overlay_text' => ['nullable', 'string', 'max:160'],
            'overlay_position' => ['nullable', 'string', 'max:30'],
            'cta_text' => ['nullable', 'string', 'max:120'],
            'cta_position' => ['nullable', 'string', 'max:30'],
            'subtitle_position' => ['nullable', 'string', 'max:30'],
            'watermark_text' => ['nullable', 'string', 'max:80'],
            'watermark_position' => ['nullable', 'string', 'max:30'],
            'font_family' => ['nullable', 'string', 'max:60'],
            'primary_color' => ['nullable', 'string', 'max:30'],
            'background_color' => ['nullable', 'string', 'max:30'],
            'subtitle_color' => ['nullable', 'string', 'max:30'],
            'visual_layout' => ['nullable', 'array'],
        ]);

        $format = $validated['format'] === 'square' ? 'quadrado' : ($validated['format'] ?: 'horizontal');
        $resolution = $validated['resolution'] ?? match ($format) {
            'vertical' => '1080x1920',
            'quadrado' => '1080x1080',
            default => '1920x1080',
        };
        [$width, $height] = array_pad(array_map('intval', explode('x', $resolution)), 2, 1080);

        $template = VideoTemplate::create([
            'user_id' => null,
            'name' => $validated['name'],
            'format' => $format,
            'resolution' => $resolution,
            'overlay_text' => $validated['overlay_text'] ?? 'Texto principal editavel',
            'overlay_position' => $validated['overlay_position'] ?? 'top',
            'cta_text' => $validated['cta_text'] ?? 'SAIBA MAIS',
            'cta_position' => $validated['cta_position'] ?? 'bottom',
            'subtitle_position' => $validated['subtitle_position'] ?? 'bottom',
            'subtitle_color' => $validated['subtitle_color'] ?? '#ffffff',
            'watermark_text' => $validated['watermark_text'] ?? 'EditorVideoIA',
            'watermark_position' => $validated['watermark_position'] ?? 'top-right',
            'font_family' => $validated['font_family'] ?? 'Arial',
            'primary_color' => $validated['primary_color'] ?? '#22c55e',
            'background_color' => $validated['background_color'] ?? '#111827',
            'canvas_width' => $width,
            'canvas_height' => $height,
            'clip_start' => 0,
            'clip_duration' => 10,
            'auto_subtitle' => false,
            'visual_layout' => $validated['visual_layout'] ?? [],
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Template salvo na biblioteca.',
            'template' => $this->templatePayload($template),
        ]);
    }


    public function createBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'jobs' => ['required', 'array', 'min:1', 'max:100'],
            'jobs.*.asset_id' => ['nullable'],
            'jobs.*.name' => ['nullable', 'string', 'max:255'],
            'jobs.*.type' => ['nullable', 'string', 'max:30'],
            'jobs.*.url' => ['nullable', 'string'],
            'jobs.*.stream_url' => ['nullable', 'string'],
            'template_snapshot' => ['nullable', 'array'],
        ]);

        $project = $this->resolveProject($request);

        $timeline = $this->normalizeTimeline($project->timeline_data);
        $snapshot = $validated['template_snapshot'] ?? [];

        $jobs = [];
        foreach ($validated['jobs'] as $index => $job) {
            $asset = null;
            if (!empty($job['asset_id'])) {
                $asset = MediaAsset::find($job['asset_id']);
            }

            $jobs[] = [
                'id' => 'batch_'.now()->format('YmdHis').'_'.$index,
                'asset_id' => $asset?->id ?? ($job['asset_id'] ?? null),
                'name' => $asset?->original_name ?? ($job['name'] ?? 'Midia do lote'),
                'type' => $asset?->media_type ?? ($job['type'] ?? 'media'),
                'url' => $asset ? route('editor-video.media.stream', $asset->storage_path) : ($job['url'] ?? null),
                'stream_url' => $asset ? route('editor-video.media.stream', $asset->storage_path) : ($job['stream_url'] ?? ($job['url'] ?? null)),
                'status' => 'aguardando',
'progress' => 0,
'worker' => null,
'priority' => 1,
'retries' => 0,
                'template_snapshot' => $snapshot,
                'created_at' => now()->toDateTimeString(),
            ];
        }

        $timeline['batch_jobs'] = $jobs;
        $timeline['batch_queue'] = [
    'total' => count($jobs),
    'waiting' => count($jobs),
    'processing' => 0,
    'finished' => 0,
    'failed' => 0,
    'paused' => false,
    'workers' => 4,
    'max_parallel' => 4,
];
        $timeline['meta']['version'] = '4.2-processamento-em-massa-blocos-3-4';
        $timeline['meta']['batch_total'] = count($jobs);
        $timeline['meta']['batch_updated_at'] = now()->toDateTimeString();

        $project->timeline_data = $timeline;
        $project->settings = array_merge($project->settings ?? [], [
            'etapa' => '4.2',
            'blocos' => '3-4',
            'batch_total' => count($jobs),
        ]);
        $project->save();

        return response()->json([
            'ok' => true,
            'message' => 'Fila em massa criada.',
            'batch_jobs' => $jobs,
            'timeline' => $this->normalizeTimeline($project->fresh()->timeline_data),
        ]);
    }


   public function processBatch(Request $request): JsonResponse
{
    $project = $this->resolveProject($request);

    $timeline = $this->normalizeTimeline($project->timeline_data);

    if (($timeline['batch_queue']['paused'] ?? false) === true) {
        return response()->json([
            'ok' => true,
            'message' => 'Fila pausada.',
            'timeline' => $timeline,
        ]);
    }

    $jobs = $timeline['batch_jobs'] ?? [];

    $maxParallel = max(1, min(8, (int) ($timeline['batch_queue']['max_parallel'] ?? 4)));

    $processedNow = 0;

    foreach ($jobs as $index => $job) {
        if (!is_array($job)) {
            continue;
        }

        if (($job['status'] ?? '') !== 'aguardando') {
            continue;
        }

        $workerId = ($processedNow % $maxParallel) + 1;

        $jobs[$index]['status'] = 'processando';
        $jobs[$index]['worker'] = $workerId;
        $jobs[$index]['progress'] = 10;
        $jobs[$index]['started_at'] = $jobs[$index]['started_at'] ?? now()->toDateTimeString();

        $sourceUrl = $job['stream_url'] ?? ($job['url'] ?? null);

        $outputName = 'render_lote_'
            .($index + 1).'_'
            .Str::slug(pathinfo($job['name'] ?? 'video', PATHINFO_FILENAME) ?: 'video')
            .'.mp4';

        try {
            if (!$sourceUrl) {
                throw new \RuntimeException('Arquivo de origem não encontrado para este vídeo.');
            }

            $jobs[$index]['progress'] = 40;

            $result = $this->ffmpeg->render([
                'input' => $sourceUrl,
                'output' => $outputName,
                'project_id' => $project->id,
                'template' => $job['template_snapshot'] ?? [],
            ]);

            $jobs[$index]['progress'] = 100;
            $jobs[$index]['status'] = 'concluido';
            $jobs[$index]['render_status'] = 'ok';
            $jobs[$index]['processed_at'] = now()->toDateTimeString();
            $jobs[$index]['finished_at'] = now()->toDateTimeString();
            $jobs[$index]['worker'] = $workerId;

            $jobs[$index]['output_name'] = $result['output_name'] ?? $outputName;
            $jobs[$index]['output_path'] = $result['output_path'] ?? null;
            $jobs[$index]['relative_path'] = $result['relative_path'] ?? null;
            $jobs[$index]['download_url'] = $result['download_url'] ?? null;
            $jobs[$index]['size_bytes'] = $result['size_bytes'] ?? null;
            $jobs[$index]['render'] = $result;

            $jobs[$index]['message'] = 'MP4 renderizado e salvo pelo Worker '.$workerId.'.';

        } catch (\Throwable $e) {
            $jobs[$index]['retries'] = ($jobs[$index]['retries'] ?? 0) + 1;
            $jobs[$index]['render_status'] = 'erro';
            $jobs[$index]['render_error'] = $e->getMessage();

            if ($jobs[$index]['retries'] < 3) {
                $jobs[$index]['status'] = 'aguardando';
                $jobs[$index]['progress'] = 0;
                $jobs[$index]['worker'] = null;
                $jobs[$index]['message'] = 'Falhou. Reagendado automaticamente. Tentativa '.$jobs[$index]['retries'].' de 3.';
            } else {
                $jobs[$index]['status'] = 'erro';
                $jobs[$index]['progress'] = 0;
                $jobs[$index]['worker'] = null;
                $jobs[$index]['finished_at'] = now()->toDateTimeString();
                $jobs[$index]['message'] = 'Render cancelado após 3 tentativas.';
            }
        }

        $processedNow++;
    }

    $totalJobs = collect($jobs)->count();
    $finishedJobs = collect($jobs)->where('status', 'concluido')->count();
    $waitingJobs = collect($jobs)->where('status', 'aguardando')->count();
    $processingJobs = collect($jobs)->where('status', 'processando')->count();
    $failedJobs = collect($jobs)->where('status', 'erro')->count();
    $cancelledJobs = collect($jobs)->where('status', 'cancelado')->count();

    $startedAt = collect($jobs)->pluck('started_at')->filter()->min();
    $finishedAt = collect($jobs)->pluck('finished_at')->filter()->max();

    $elapsedSeconds = $startedAt && $finishedAt
        ? max(1, strtotime($finishedAt) - strtotime($startedAt))
        : 0;

    $videosPerMinute = $elapsedSeconds > 0
        ? round(($finishedJobs / $elapsedSeconds) * 60, 2)
        : 0;

    $remainingJobs = max(0, $totalJobs - $finishedJobs - $failedJobs - $cancelledJobs);

    $etaMinutes = $videosPerMinute > 0
        ? round($remainingJobs / $videosPerMinute, 2)
        : null;

    $timeline['batch_jobs'] = array_values($jobs);

    $timeline['batch_queue'] = array_merge($timeline['batch_queue'] ?? [], [
        'waiting' => $waitingJobs,
        'processing' => $processingJobs,
        'finished' => $finishedJobs,
        'failed' => $failedJobs,
        'cancelled' => $cancelledJobs,
        'completed' => $waitingJobs === 0 && $processingJobs === 0,
        'max_parallel' => $maxParallel,
        'stats' => [
            'total_jobs' => $totalJobs,
            'finished_jobs' => $finishedJobs,
            'remaining_jobs' => $remainingJobs,
            'elapsed_seconds' => $elapsedSeconds,
            'videos_per_minute' => $videosPerMinute,
            'eta_minutes' => $etaMinutes,
            'memory_limit' => ini_get('memory_limit'),
        ],
    ]);

    $timeline['meta']['version'] = 'final-100-videos-blocos-1-2';
    $timeline['meta']['batch_processed_total'] = $totalJobs;
    $timeline['meta']['batch_finished_total'] = $finishedJobs;
    $timeline['meta']['batch_processed_at'] = now()->toDateTimeString();

    $project->timeline_data = $timeline;
    $project->settings = array_merge($project->settings ?? [], [
        'etapa' => 'final-100-videos',
        'blocos' => '1-2',
        'batch_processed_total' => $totalJobs,
        'batch_finished_total' => $finishedJobs,
    ]);
    $project->save();

    return response()->json([
        'ok' => true,
        'message' => $waitingJobs === 0
            ? 'Fila processada até o fim.'
            : 'Fila processada parcialmente. Ainda existem vídeos aguardando.',
        'processed_now' => $processedNow,
        'batch_jobs' => $timeline['batch_jobs'],
        'timeline' => $this->normalizeTimeline($project->fresh()->timeline_data),
    ]);
}

    public function resetBatch(): JsonResponse
    {
        $project = $this->resolveProject($request);

        $timeline = $this->normalizeTimeline($project->timeline_data);
        foreach ($timeline['batch_jobs'] ?? [] as &$job) {

    if (($job['status'] ?? '') === 'processando') {

        $job['status'] = 'aguardando';
        $job['worker'] = null;

    }

}
unset($job);
        $timeline['batch_jobs'] = [];
        $timeline['meta']['batch_reset_at'] = now()->toDateTimeString();
        $project->timeline_data = $timeline;
        $project->save();

        return response()->json([
            'ok' => true,
            'message' => 'Fila limpa.',
            'timeline' => $this->normalizeTimeline($project->fresh()->timeline_data),
        ]);
    }
public function pauseBatch(Request $request): JsonResponse
{
    $project = $this->resolveProject($request);

    $timeline = $this->normalizeTimeline($project->timeline_data);
    $timeline['batch_queue']['paused'] = true;

    $project->timeline_data = $timeline;
    $project->save();

    return response()->json([
        'ok' => true,
        'paused' => true,
    ]);
}

public function resumeBatch(Request $request): JsonResponse
{
    $project = $this->resolveProject($request);

    $timeline = $this->normalizeTimeline($project->timeline_data);
    $timeline['batch_queue']['paused'] = false;

    $project->timeline_data = $timeline;
    $project->save();

    return response()->json([
        'ok' => true,
        'paused' => false,
    ]);
}

public function cancelBatch(Request $request): JsonResponse
{
    $project = $this->resolveProject($request);

    $timeline = $this->normalizeTimeline($project->timeline_data);

    $jobId = $request->input('job_id');

    foreach ($timeline['batch_jobs'] ?? [] as &$job) {

        if ($jobId) {

            if (($job['id'] ?? null) !== $jobId) {
                continue;
            }

        }

        if (in_array(($job['status'] ?? ''), ['aguardando', 'processando'])) {

            $job['status'] = 'cancelado';
            $job['worker'] = null;
            $job['progress'] = 0;
            $job['finished_at'] = now()->toDateTimeString();
            $job['message'] = 'Cancelado pelo usuário.';

        }

    }

    unset($job);

    $timeline['batch_queue']['waiting'] =
        collect($timeline['batch_jobs'])->where('status', 'aguardando')->count();

    $timeline['batch_queue']['processing'] =
        collect($timeline['batch_jobs'])->where('status', 'processando')->count();

    $timeline['batch_queue']['finished'] =
        collect($timeline['batch_jobs'])->where('status', 'concluido')->count();

    $timeline['batch_queue']['failed'] =
        collect($timeline['batch_jobs'])->where('status', 'erro')->count();

    $timeline['batch_queue']['cancelled'] =
        collect($timeline['batch_jobs'])->where('status', 'cancelado')->count();

    $project->timeline_data = $timeline;
    $project->save();

    return response()->json([
        'ok' => true,
        'message' => $jobId
            ? 'Vídeo cancelado.'
            : 'Fila cancelada.',
        'timeline' => $timeline,
    ]);
}





    public function prepareExport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'resolution' => ['required', 'string', 'max:20'],
            'fps' => ['required', 'integer', 'min:24', 'max:60'],
            'quality' => ['required', 'string', 'max:30'],
            'format' => ['required', 'string', 'max:20'],
            'bitrate' => ['nullable', 'string', 'max:20'],
        ]);

        $project = $this->resolveProject($request);

        $timeline = $this->normalizeTimeline($project->timeline_data);
        $clips = $timeline['clips'] ?? [];
        $batchJobs = $timeline['batch_jobs'] ?? [];
        $sourceJobs = count($batchJobs) > 0 ? $batchJobs : $clips;

        $exportJobs = [];
        foreach ($sourceJobs as $index => $item) {
            if (!is_array($item)) {
                continue;
            }
            $name = $item['name'] ?? ('video_'.$index);
            $exportJobs[] = [
                'id' => 'export_'.now()->format('YmdHis').'_'.$index,
                'name' => $name,
                'status' => 'preparado',
                'progress' => 0,
                'resolution' => $validated['resolution'],
                'fps' => $validated['fps'],
                'quality' => $validated['quality'],
                'format' => $validated['format'],
                'bitrate' => $validated['bitrate'] ?? $this->defaultBitrate($validated['resolution'], $validated['quality']),
                'output_name' => 'export_'.($index + 1).'_'.Str::slug(pathinfo($name, PATHINFO_FILENAME) ?: 'video').'.'.$validated['format'],
                'source_asset_id' => $item['asset_id'] ?? null,
                'source_type' => $item['type'] ?? null,
                'source_url' => $item['stream_url'] ?? ($item['url'] ?? null),
                'created_at' => now()->toDateTimeString(),
                'message' => 'Exportacao preparada com configuracao profissional.',
            ];
        }

        if (count($exportJobs) === 0) {
            $exportJobs[] = [
                'id' => 'export_'.now()->format('YmdHis').'_0',
                'name' => $project->name ?: 'Projeto principal',
                'status' => 'preparado',
                'progress' => 0,
                'resolution' => $validated['resolution'],
                'fps' => $validated['fps'],
                'quality' => $validated['quality'],
                'format' => $validated['format'],
                'bitrate' => $validated['bitrate'] ?? $this->defaultBitrate($validated['resolution'], $validated['quality']),
                'output_name' => 'export_projeto_principal.'.$validated['format'],
                'created_at' => now()->toDateTimeString(),
                'message' => 'Exportacao preparada sem midia ativa. Use para validar o fluxo.',
            ];
        }

        $timeline['export_jobs'] = $exportJobs;
        $timeline['export_settings'] = $validated;
        $timeline['meta']['version'] = '5.2-download-exportacao-blocos-3-4';
        $timeline['meta']['export_prepared_at'] = now()->toDateTimeString();

        $project->timeline_data = $timeline;
        $project->settings = array_merge($project->settings ?? [], [
            'etapa' => '5.2',
            'blocos' => '3-4',
            'export_resolution' => $validated['resolution'],
            'export_fps' => $validated['fps'],
            'export_quality' => $validated['quality'],
        ]);
        $project->save();

        return response()->json([
            'ok' => true,
            'message' => 'Exportacao preparada.',
            'timeline' => $this->normalizeTimeline($project->fresh()->timeline_data),
        ]);
    }

    public function processExport(Request $request): JsonResponse
{
    $project = $this->resolveProject($request);

    $timeline = $this->normalizeTimeline($project->timeline_data);
    $batchJobs = $timeline['batch_jobs'] ?? [];
    $exportJobs = $timeline['export_jobs'] ?? [];

    foreach ($batchJobs as $index => $job) {
        if (!is_array($job)) {
            continue;
        }

        if (($job['status'] ?? '') !== 'concluido') {
            continue;
        }

        $relativePath = $job['relative_path'] ?? null;

        if (!$relativePath) {
            continue;
        }

        $exportJobs[$index] = array_merge($exportJobs[$index] ?? [], [
            'id' => $job['id'] ?? ('export_'.$index),
            'name' => $job['name'] ?? ('video_'.$index),
            'status' => 'concluido',
            'progress' => 100,
            'format' => 'mp4',
            'output_name' => $job['output_name'] ?? basename($relativePath),
            'output_file' => $relativePath,
            'relative_path' => $relativePath,
            'download_url' => route('editor-video.export.download', ['index' => $index]),
            'processed_at' => now()->toDateTimeString(),
            'message' => 'MP4 pronto para download.',
        ]);
    }

    $timeline['export_jobs'] = array_values($exportJobs);
    $timeline['meta']['export_processed_at'] = now()->toDateTimeString();
    $timeline['meta']['export_processed_total'] = count($exportJobs);
    $timeline['meta']['version'] = 'final-100-videos-bloco-3-process-export';

    $project->timeline_data = $timeline;
    $project->save();

    return response()->json([
        'ok' => true,
        'message' => 'Downloads dos MP4 preparados.',
        'timeline' => $this->normalizeTimeline($project->fresh()->timeline_data),
    ]);
}

   public function downloadExport(Request $request, int $index)
{
    $project = $this->resolveProject($request);

    $timeline = $this->normalizeTimeline($project->timeline_data);
    $jobs = $timeline['export_jobs'] ?? [];
    $job = $jobs[$index] ?? null;

    abort_unless(is_array($job), 404, 'Exportacao nao encontrada.');

    $relativePath = $job['relative_path'] ?? $job['output_file'] ?? null;

    abort_unless($relativePath, 404, 'Arquivo de exportacao nao encontrado.');
    abort_unless(Storage::disk('public')->exists($relativePath), 404, 'Arquivo de exportacao ainda nao foi gerado.');

    $fullPath = Storage::disk('public')->path($relativePath);
    $downloadName = $job['output_name'] ?? basename($relativePath);

    return response()->download($fullPath, $downloadName, [
        'Content-Type' => 'video/mp4',
    ]);
}
public function downloadAllExports(Request $request)
{
    $project = $this->resolveProject($request);

    $timeline = $this->normalizeTimeline($project->timeline_data);
    $jobs = $timeline['export_jobs'] ?? [];

    Storage::disk('public')->makeDirectory('editorvideoia/zips');

    $zipRelativePath = 'editorvideoia/zips/videos_renderizados_projeto_'.$project->id.'_'.now()->format('Ymd_His').'.zip';
    $zipFullPath = Storage::disk('public')->path($zipRelativePath);

    $zip = new ZipArchive();

    if ($zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        abort(500, 'Nao foi possivel criar o ZIP.');
    }

    $added = 0;

    foreach ($jobs as $index => $job) {
        if (!is_array($job)) {
            continue;
        }

        $relativePath = $job['relative_path'] ?? $job['output_file'] ?? null;

        if (!$relativePath || !Storage::disk('public')->exists($relativePath)) {
            continue;
        }

        $fullPath = Storage::disk('public')->path($relativePath);
        $nameInZip = $job['output_name'] ?? ('video_'.($index + 1).'.mp4');

        $zip->addFile($fullPath, $nameInZip);
        $added++;
    }

    $zip->close();

    abort_unless($added > 0, 404, 'Nenhum MP4 renderizado encontrado para ZIP.');

    return response()->download($zipFullPath, basename($zipRelativePath), [
        'Content-Type' => 'application/zip',
    ]);
}
    public function resetExport(): JsonResponse
    {
        $project = $this->resolveProject($request);

        $timeline = $this->normalizeTimeline($project->timeline_data);
        $timeline['export_jobs'] = [];
        $timeline['meta']['export_reset_at'] = now()->toDateTimeString();
        $project->timeline_data = $timeline;
        $project->save();

        return response()->json([
            'ok' => true,
            'message' => 'Fila de exportacao limpa.',
            'timeline' => $this->normalizeTimeline($project->fresh()->timeline_data),
        ]);
    }

    public function exportStatus(): JsonResponse
    {
        $project = $this->resolveProject($request);

        $timeline = $this->normalizeTimeline($project->timeline_data);

        return response()->json([
            'ok' => true,
            'export_jobs' => $timeline['export_jobs'] ?? [],
            'timeline' => $timeline,
        ]);
    }

    private function defaultBitrate(string $resolution, string $quality): string
    {
        if (str_contains($resolution, '3840')) return $quality === 'maxima' ? '45M' : '35M';
        if (str_contains($resolution, '2560')) return $quality === 'maxima' ? '24M' : '18M';
        if (str_contains($resolution, '1920')) return $quality === 'maxima' ? '16M' : '12M';
        return $quality === 'maxima' ? '8M' : '5M';
    }

    public function deleteMedia(MediaAsset $mediaAsset): JsonResponse
    {
        if ($mediaAsset->storage_path) {
            Storage::disk('public')->delete($mediaAsset->storage_path);
        }

        $mediaAsset->delete();

        return response()->json(['ok' => true]);
    }


    private function templatePayload(VideoTemplate $template): array
    {
        $layout = is_array($template->visual_layout) ? $template->visual_layout : [];

        return [
            'id' => $template->id,
            'name' => $template->name,
            'format' => $template->format ?: 'horizontal',
            'resolution' => $template->resolution ?: '1920x1080',
            'overlay_text' => $template->overlay_text ?: ($layout['overlay_text'] ?? 'Texto principal editável'),
            'overlay_position' => $template->overlay_position ?: 'top',
            'cta_text' => $template->cta_text ?: ($layout['cta_text'] ?? 'SAIBA MAIS'),
            'cta_position' => $template->cta_position ?: ($layout['cta_position'] ?? 'bottom'),
            'subtitle_position' => $template->subtitle_position ?: 'bottom',
            'watermark_text' => $template->watermark_text ?: ($layout['watermark_text'] ?? 'EditorVideoIA'),
            'watermark_position' => $template->watermark_position ?: 'top-right',
            'font_family' => $template->font_family ?: ($layout['font_family'] ?? 'Arial'),
            'primary_color' => $template->primary_color ?: ($layout['primary_color'] ?? '#22c55e'),
            'background_color' => $template->background_color ?: ($layout['background_color'] ?? '#111827'),
            'subtitle_color' => $template->subtitle_color ?: ($layout['subtitle_color'] ?? '#ffffff'),
        ];
    }

    private function assetPayload(MediaAsset $asset): array
    {
        return [
            'id' => $asset->id,
            'original_name' => $asset->original_name,
            'mime_type' => $asset->mime_type,
            'media_type' => $asset->media_type,
            'extension' => $asset->extension,
            'size_bytes' => $asset->size_bytes,
            'size_label' => $asset->size_label,
            'storage_path' => $asset->storage_path,
            'public_url' => route('editor-video.media.stream', $asset->storage_path),
            'stream_url' => route('editor-video.media.stream', $asset->storage_path),
        ];
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
            default => 'other',
        };
    }

    private function mimeFromExtension(string $extension): string
    {
        return match ($extension) {
            'mp4' => 'video/mp4',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
            'mkv' => 'video/x-matroska',
            'webm' => 'video/webm',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'aac' => 'audio/aac',
            'flac' => 'audio/flac',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'application/octet-stream',
        };
    }

    private function detectImageSize(string $path, string $mediaType): array
    {
        if ($mediaType !== 'image') return [null, null];
        $size = @getimagesize($path);
        return $size ? [$size[0] ?? null, $size[1] ?? null] : [null, null];
    }

    private function normalizeTimeline($timeline): array
    {
        if (is_string($timeline)) {
            $decoded = json_decode($timeline, true);
            $timeline = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($timeline)) {
            $timeline = [];
        }

        $defaults = $this->defaultTimeline();

        if (!isset($timeline['clips']) || !is_array($timeline['clips'])) {
            $timeline['clips'] = [];
        }

        foreach ($timeline['clips'] as $key => $clip) {
            if (!is_array($clip)) {
                unset($timeline['clips'][$key]);
                continue;
            }

            if (!empty($clip['asset_id'])) {
                $asset = MediaAsset::find($clip['asset_id']);
                if ($asset) {
                    $clip['url'] = route('editor-video.media.stream', $asset->storage_path);
                    $clip['stream_url'] = route('editor-video.media.stream', $asset->storage_path);
                    $clip['name'] = $clip['name'] ?? $asset->original_name;
                    $clip['type'] = $clip['type'] ?? $asset->media_type;
                    $timeline['clips'][$key] = $clip;
                }
            }
        }

        $timeline['clips'] = array_values($timeline['clips']);

        if (!isset($timeline['tracks']) || !is_array($timeline['tracks']) || count($timeline['tracks']) === 0) {
            $timeline['tracks'] = $defaults['tracks'];
        }

        $timeline['overlays'] = isset($timeline['overlays']) && is_array($timeline['overlays'])
            ? array_merge($defaults['overlays'], $timeline['overlays'])
            : $defaults['overlays'];

        $timeline['canvas'] = isset($timeline['canvas']) && is_array($timeline['canvas'])
            ? array_merge($defaults['canvas'], $timeline['canvas'])
            : $defaults['canvas'];

        $timeline['meta'] = isset($timeline['meta']) && is_array($timeline['meta'])
            ? array_merge($defaults['meta'], $timeline['meta'])
            : $defaults['meta'];

        if (!isset($timeline['batch_jobs']) || !is_array($timeline['batch_jobs'])) {
            $timeline['batch_jobs'] = [];
        }

        if (!isset($timeline['export_jobs']) || !is_array($timeline['export_jobs'])) {
            $timeline['export_jobs'] = [];
        }

        if (!isset($timeline['export_settings']) || !is_array($timeline['export_settings'])) {
            $timeline['export_settings'] = ['resolution' => '1920x1080', 'fps' => 30, 'quality' => 'alta', 'format' => 'mp4', 'bitrate' => '12M'];
        }

        foreach ($timeline['batch_jobs'] as $key => $job) {
            if (!is_array($job)) {
                unset($timeline['batch_jobs'][$key]);
                continue;
            }

            if (!empty($job['asset_id'])) {
                $asset = MediaAsset::find($job['asset_id']);
                if ($asset) {
                    $job['name'] = $asset->original_name;
                    $job['type'] = $asset->media_type;
                    $job['url'] = route('editor-video.media.stream', $asset->storage_path);
                    $job['stream_url'] = route('editor-video.media.stream', $asset->storage_path);
                    $timeline['batch_jobs'][$key] = $job;
                }
            }
        }

        $timeline['batch_jobs'] = array_values($timeline['batch_jobs']);

        return $timeline;
    }

    private function defaultTimeline(): array
    {
        return [
            'clips' => [],
            'tracks' => [
                ['id' => 'video_1', 'name' => 'Video 1', 'type' => 'video', 'locked' => false, 'muted' => false, 'hidden' => false],
                ['id' => 'image_1', 'name' => 'Imagem', 'type' => 'image', 'locked' => false, 'muted' => false, 'hidden' => false],
                ['id' => 'text_1', 'name' => 'Texto / Legenda', 'type' => 'text', 'locked' => false, 'muted' => false, 'hidden' => false],
                ['id' => 'audio_1', 'name' => 'Audio 1', 'type' => 'audio', 'locked' => false, 'muted' => false, 'hidden' => false],
            ],
            'overlays' => [
                'title' => 'Texto principal editavel',
                'cta' => 'CTA EDITAVEL',
                'subtitle' => 'Legenda editavel',
                'watermark' => 'EditorVideoIA',
            ],
            'canvas' => ['background' => '#111827'],
            'batch_jobs' => [],
            'export_jobs' => [],
            'export_settings' => ['resolution' => '1920x1080', 'fps' => 30, 'quality' => 'alta', 'format' => 'mp4', 'bitrate' => '12M'],
            'meta' => ['version' => '5.2-download-exportacao-blocos-3-4'],
        ];
    }
}
