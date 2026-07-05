<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VisualEditorController extends Controller
{
    public function index(Request $request)
    {
        $templates = VideoTemplate::where('user_id', Auth::id())->latest()->get();
        $videos = Video::where('user_id', Auth::id())->latest()->limit(50)->get();

        $template = null;
        if ($request->filled('template')) {
            $template = VideoTemplate::where('user_id', Auth::id())->find($request->template);
        }
        if (!$template) {
            $template = $templates->first();
        }

        return view('editor.visual', compact('templates', 'videos', 'template'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'template_id' => ['required', 'exists:video_templates,id'],
            'layout' => ['required', 'array'],
            'canvas_width' => ['required', 'integer', 'min:300', 'max:4000'],
            'canvas_height' => ['required', 'integer', 'min:300', 'max:4000'],
        ]);

        $template = VideoTemplate::where('user_id', Auth::id())->findOrFail($request->template_id);

        $template->update([
            'visual_layout' => $request->layout,
            'canvas_width' => $request->canvas_width,
            'canvas_height' => $request->canvas_height,
            'resolution' => $request->canvas_width . 'x' . $request->canvas_height,
        ]);

        return response()->json(['ok' => true, 'message' => 'Layout visual salvo com sucesso.']);
    }

    public function createQuickTemplate(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'resolution' => ['required', 'in:1080x1920,1920x1080,1080x1080,720x1280,1280x720'],
        ]);

        [$width, $height] = explode('x', $request->resolution);

        $template = VideoTemplate::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'format' => $height > $width ? 'vertical' : ($height == $width ? 'quadrado' : 'horizontal'),
            'resolution' => $request->resolution,
            'watermark_position' => 'bottom-right',
            'overlay_position' => 'bottom',
            'clip_start' => 0,
            'canvas_width' => (int) $width,
            'canvas_height' => (int) $height,
            'visual_layout' => [
                'background' => ['type' => 'solid', 'color' => '#111827'],
                'video' => [
                    'x' => 120, 'y' => 260, 'width' => (int) $width - 240,
                    'height' => (int) ($height * 0.55), 'rotation' => 0,
                    'opacity' => 1, 'borderRadius' => 28,
                ],
            ],
        ]);

        return redirect()->route('visual-editor.index', ['template' => $template->id])
            ->with('success', 'Template visual criado.');
    }
}
