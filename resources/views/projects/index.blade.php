<x-app-layout>
<x-slot name="header"><h2 style="font-size:22px;font-weight:700;">Projetos</h2></x-slot>
<div style="padding:30px;">
@if(session('success'))<div style="background:#dcfce7;color:#166534;padding:14px;border-radius:10px;margin-bottom:20px;">{{ session('success') }}</div>@endif
<div style="display:grid;grid-template-columns:360px 1fr;gap:24px;">
<div style="background:white;padding:24px;border-radius:14px;box-shadow:0 2px 8px #ddd;">
<h3 style="font-size:22px;font-weight:700;margin-bottom:15px;">Novo projeto</h3>
<form method="POST" action="{{ route('projects.store') }}">@csrf
<label>Nome</label>
<input name="name" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:6px 0 14px;">
<label>Descrição</label>
<textarea name="description" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:6px 0 14px;"></textarea>
<button style="background:#111827;color:white;border:none;border-radius:8px;padding:11px 15px;">Criar projeto</button>
</form>
</div>
<div style="background:white;padding:24px;border-radius:14px;box-shadow:0 2px 8px #ddd;">
<h3 style="font-size:22px;font-weight:700;margin-bottom:15px;">Meus projetos</h3>
@forelse($projects as $project)
<div style="border-bottom:1px solid #eee;padding:14px 0;display:flex;justify-content:space-between;">
<div><strong>{{ $project->name }}</strong><div style="color:#666;">{{ $project->videos_count }} vídeos — {{ $project->exports_count }} exportações</div></div>
<div><a href="{{ route('projects.show', $project) }}" style="color:#2563eb;">Abrir</a></div>
</div>
@empty
<p>Nenhum projeto criado.</p>
@endforelse
<div style="margin-top:20px;">{{ $projects->links() }}</div>
</div>
</div>
</div>
</x-app-layout>
