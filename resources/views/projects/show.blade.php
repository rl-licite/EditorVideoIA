<x-app-layout>
<x-slot name="header"><h2 style="font-size:22px;font-weight:700;">Projeto: {{ $project->name }}</h2></x-slot>
<div style="padding:30px;">
<a href="{{ route('projects.index') }}" style="display:inline-block;background:#2563eb;color:white;padding:10px 14px;border-radius:8px;text-decoration:none;margin-bottom:20px;">← Voltar</a>
<div style="background:white;padding:24px;border-radius:14px;box-shadow:0 2px 8px #ddd;margin-bottom:24px;">
<h3 style="font-size:24px;font-weight:800;">{{ $project->name }}</h3>
<p style="color:#555;">{{ $project->description }}</p>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
<div style="background:white;padding:24px;border-radius:14px;box-shadow:0 2px 8px #ddd;">
<h3 style="font-size:20px;font-weight:700;">Vídeos do projeto</h3>
@forelse($videos as $video)
<div style="border-bottom:1px solid #eee;padding:10px 0;">{{ $video->original_name }}</div>
@empty<p>Nenhum vídeo neste projeto.</p>@endforelse
</div>
<div style="background:white;padding:24px;border-radius:14px;box-shadow:0 2px 8px #ddd;">
<h3 style="font-size:20px;font-weight:700;">Exportações recentes</h3>
@forelse($exports as $export)
<div style="border-bottom:1px solid #eee;padding:10px 0;">{{ $export->video->original_name ?? '-' }} — {{ $export->status_label }}</div>
@empty<p>Nenhuma exportação.</p>@endforelse
</div>
</div>
</div>
</x-app-layout>
