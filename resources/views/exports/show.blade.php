<x-app-layout>
<x-slot name="header"><h2 style="font-size:22px;font-weight:700;">Detalhes da exportação</h2></x-slot>
<div style="padding:30px;">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:25px;">
<div style="background:white;padding:22px;border-radius:14px;box-shadow:0 2px 8px #ddd;">
<h3 style="font-size:22px;font-weight:700;">Resultado</h3>
@if($export->status === 'concluido' && $export->output_path)
<video controls style="width:100%;border-radius:12px;background:#111827;margin-top:12px;"><source src="{{ asset('storage/'.$export->output_path) }}" type="video/mp4"></video>
<a href="{{ route('exports.download', $export) }}" style="display:inline-block;margin-top:15px;background:#16a34a;color:white;padding:10px 14px;border-radius:8px;text-decoration:none;">Baixar vídeo</a>
@else
<p style="margin-top:12px;">Status: {{ $export->status_label }}</p>
@if($export->error_message)<pre style="white-space:pre-wrap;background:#fee2e2;color:#991b1b;padding:12px;border-radius:8px;">{{ $export->error_message }}</pre>@endif
@endif
</div>
<div style="background:white;padding:22px;border-radius:14px;box-shadow:0 2px 8px #ddd;">
<h3 style="font-size:22px;font-weight:700;">Informações</h3>
<p><strong>Vídeo:</strong> {{ $export->video->original_name ?? '-' }}</p>
<p><strong>Template:</strong> {{ $export->template->name ?? '-' }}</p>
<p><strong>Status:</strong> {{ $export->status_label }}</p>
<p><strong>Progresso:</strong> {{ $export->progress }}%</p>
<p><strong>Início:</strong> {{ optional($export->started_at)->format('d/m/Y H:i') ?: '-' }}</p>
<p><strong>Fim:</strong> {{ optional($export->finished_at)->format('d/m/Y H:i') ?: '-' }}</p>
<h4 style="font-weight:700;margin-top:15px;">Comando FFmpeg</h4>
<pre style="white-space:pre-wrap;background:#f3f4f6;padding:12px;border-radius:8px;">{{ $export->ffmpeg_command }}</pre>
<a href="{{ route('exports.index') }}" style="color:#2563eb;">Voltar</a>
</div>
</div>
</div>
</x-app-layout>
