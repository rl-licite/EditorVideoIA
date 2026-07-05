<x-app-layout>
<x-slot name="header"><h2 style="font-size:22px;font-weight:700;">Legendas inteligentes</h2></x-slot>
<div style="padding:30px;">
@if(session('success'))<div style="background:#dcfce7;color:#166534;padding:14px;border-radius:10px;margin-bottom:20px;">{{ session('success') }}</div>@endif
<div style="display:grid;grid-template-columns:420px 1fr;gap:25px;">
<div style="background:white;padding:25px;border-radius:14px;box-shadow:0 2px 8px #ddd;">
<h3 style="font-size:20px;font-weight:700;">Adicionar legenda</h3>
<p style="color:#555;margin-bottom:15px;">Vídeo: {{ $video->original_name }}</p>
<form method="POST" action="{{ route('ai.subtitles.store', $video) }}">@csrf
<label>Texto</label>
<input name="text" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:6px 0 14px;">
<label>Início (segundos)</label>
<input type="number" name="start_second" value="0" min="0" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:6px 0 14px;">
<label>Fim (segundos)</label>
<input type="number" name="end_second" value="3" min="1" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:6px 0 18px;">
<button style="background:#111827;color:white;border:none;border-radius:8px;padding:11px 15px;">Adicionar</button>
</form>
</div>
<div style="background:white;padding:25px;border-radius:14px;box-shadow:0 2px 8px #ddd;">
<h3 style="font-size:20px;font-weight:700;">Legendas cadastradas</h3>
@forelse($subtitles as $subtitle)
<div style="border-bottom:1px solid #eee;padding:12px 0;display:flex;justify-content:space-between;">
<div><strong>{{ $subtitle->text }}</strong><div style="color:#666;font-size:13px;">{{ $subtitle->start_second }}s até {{ $subtitle->end_second }}s</div></div>
<form method="POST" action="{{ route('ai.subtitles.delete', $subtitle) }}">@csrf @method('DELETE')<button style="background:#dc2626;color:white;border:none;border-radius:8px;padding:7px 10px;">Excluir</button></form>
</div>
@empty
<p>Nenhuma legenda.</p>
@endforelse
</div>
</div>
</div>
</x-app-layout>
