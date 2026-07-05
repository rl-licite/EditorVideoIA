<?php

namespace App\Http\Controllers;

use App\Models\VideoTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoTemplateController extends Controller
{
    public function index()
    {
        $templates = VideoTemplate::latest()->paginate(12);
        return view('templates.index', compact('templates'));
    }

    public function create()
    {
        $template = new VideoTemplate([
            'name' => 'Template Automático',
            'format' => 'vertical',
            'resolution' => '1080x1920',
            'watermark_position' => 'bottom-right',
            'overlay_position' => 'bottom',
            'canvas_width' => 1080,
            'canvas_height' => 1920,
            'visual_layout' => $this->defaultLayout(),
        ]);
        return view('templates.create', compact('template'));
    }

    public function store(Request $request)
    {
        $data = $this->templateData($request);
        $data['user_id'] = Auth::id();
        VideoTemplate::create($data);
        return redirect()->route('templates.index')->with('success', 'Template criado com sucesso.');
    }

    public function edit(VideoTemplate $template)
    {
        return view('templates.edit', compact('template'));
    }

    public function update(Request $request, VideoTemplate $template)
    {
        $template->update($this->templateData($request));
        return redirect()->route('templates.index')->with('success', 'Template atualizado com sucesso.');
    }

    public function destroy(VideoTemplate $template)
    {
        $template->delete();
        return redirect()->route('templates.index')->with('success', 'Template excluído com sucesso.');
    }

    private function templateData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'format' => ['required', 'in:vertical,horizontal,quadrado'],
            'resolution' => ['required', 'in:1080x1920,1920x1080,1080x1080,720x1280,1280x720'],
            'watermark_text' => ['nullable', 'string', 'max:80'],
            'watermark_position' => ['required', 'string', 'max:30'],
            'overlay_text' => ['nullable', 'string', 'max:160'],
            'overlay_position' => ['required', 'string', 'max:30'],
            'clip_start' => ['nullable', 'integer', 'min:0'],
            'clip_duration' => ['nullable', 'integer', 'min:1', 'max:600'],
            'subtitle_position' => ['nullable', 'string', 'max:30'],
            'subtitle_color' => ['nullable', 'string', 'max:30'],
            'cta_text' => ['nullable', 'string', 'max:120'],
            'cta_position' => ['nullable', 'string', 'max:30'],
            'font_family' => ['nullable', 'string', 'max:60'],
            'primary_color' => ['nullable', 'string', 'max:30'],
            'background_color' => ['nullable', 'string', 'max:30'],
            'canvas_width' => ['nullable', 'integer', 'min:200', 'max:4000'],
            'canvas_height' => ['nullable', 'integer', 'min:200', 'max:4000'],
        ]);

        $resolution = $data['resolution'] ?? '1080x1920';
        [$w, $h] = array_map('intval', explode('x', $resolution));
        $data['canvas_width'] = $data['canvas_width'] ?? $w;
        $data['canvas_height'] = $data['canvas_height'] ?? $h;
        $data['clip_start'] = $data['clip_start'] ?? 0;
        $data['auto_subtitle'] = $request->boolean('auto_subtitle');
        $data['visual_layout'] = [
            'cta_text' => $data['cta_text'] ?? 'SAIBA MAIS',
            'cta_position' => $data['cta_position'] ?? 'bottom',
            'font_family' => $data['font_family'] ?? 'Arial',
            'primary_color' => $data['primary_color'] ?? '#facc15',
            'background_color' => $data['background_color'] ?? '#020617',
            'overlay_text' => $data['overlay_text'] ?? '',
            'watermark_text' => $data['watermark_text'] ?? '',
            'subtitle_color' => $data['subtitle_color'] ?? '#ffffff',
        ];

        return $data;
    }

    private function defaultLayout(): array
    {
        return [
            'cta_text' => 'SAIBA MAIS',
            'cta_position' => 'bottom',
            'font_family' => 'Arial',
            'primary_color' => '#facc15',
            'background_color' => '#020617',
            'overlay_text' => 'Texto principal editável',
            'watermark_text' => '@sua_marca',
            'subtitle_color' => '#ffffff',
        ];
    }
}
