<x-app-layout>
<x-slot name="header"><h2 style="font-size:22px;font-weight:700;">Processar vídeos em massa</h2></x-slot>
<div style="padding:30px;">
@if($errors->any())<div style="background:#fee2e2;color:#991b1b;padding:14px;border-radius:10px;margin-bottom:20px;">{{ $errors->first() }}</div>@endif
<div style="background:white;padding:25px;border-radius:14px;box-shadow:0 2px 8px #ddd;max-width:920px;">
<form method="POST" action="{{ route('exports.store') }}">@csrf
<label>Template</label>
<select name="video_template_id" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:6px 0 16px;">@foreach($templates as $template)<option value="{{ $template->id }}">{{ $template->name }} — {{ $template->resolution }}</option>@endforeach</select>
<label>Modo</label>
<select name="mode" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:6px 0 16px;">
<option value="all">Todos os vídeos</option><option value="project">Vídeos de um projeto</option><option value="folder">Vídeos de uma pasta</option><option value="selected">Vídeos selecionados</option>
</select>
<label>Projeto</label>
<select name="video_project_id" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:6px 0 16px;"><option value="">Selecione</option>@foreach($projects as $project)<option value="{{ $project->id }}">{{ $project->name }}</option>@endforeach</select>
<label>Pasta</label>
<select name="video_folder_id" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:6px 0 16px;"><option value="none">Sem pasta</option>@foreach($folders as $folder)<option value="{{ $folder->id }}">{{ $folder->name }}</option>@endforeach</select>
<label>Agendar para (opcional)</label>
<input type="datetime-local" name="scheduled_at" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:6px 0 16px;">
<label>Prioridade 0 a 10</label>
<input type="number" name="priority" value="0" min="0" max="10" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:6px 0 16px;">
<h3 style="font-size:18px;font-weight:700;margin-top:15px;">Selecionar vídeos manualmente</h3>
<div style="max-height:260px;overflow:auto;border:1px solid #eee;border-radius:10px;padding:12px;margin:10px 0 20px;">
@foreach($videos as $video)<label style="display:block;margin-bottom:8px;"><input type="checkbox" name="video_ids[]" value="{{ $video->id }}"> {{ $video->original_name }}</label>@endforeach
</div>
<button style="background:#111827;color:white;border:none;border-radius:8px;padding:12px 16px;">Enviar para fila</button>
</form>
</div>
</div>
</x-app-layout>
