<x-app-layout>
<x-slot name="header"><h2 style="font-size:22px;font-weight:700;">Enviar vídeos</h2></x-slot>
<div style="padding:30px;"><div style="background:white;padding:30px;border-radius:14px;box-shadow:0 2px 8px #ddd;max-width:850px;">
<h3 style="font-size:24px;font-weight:700;">Upload em massa</h3>
@if($errors->any())<div style="background:#fee2e2;color:#991b1b;padding:14px;border-radius:10px;margin:15px 0;">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('videos.store') }}" enctype="multipart/form-data">@csrf
<label>Pasta</label>
<select name="video_folder_id" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:6px 0 14px;"><option value="">Sem pasta</option>@foreach($folders as $folder)<option value="{{ $folder->id }}">{{ $folder->name }}</option>@endforeach</select>
<label>Projeto</label>
<select name="video_project_id" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:6px 0 14px;"><option value="">Sem projeto</option>@foreach($projects as $project)<option value="{{ $project->id }}">{{ $project->name }}</option>@endforeach</select>
<div style="border:2px dashed #cbd5e1;padding:35px;border-radius:14px;text-align:center;margin-bottom:20px;">
<input type="file" name="videos[]" multiple accept="video/mp4,video/quicktime,video/x-msvideo,video/x-matroska,video/webm">
<p style="margin-top:12px;color:#666;">Pode selecionar vários vídeos de uma vez.</p>
</div>
<button style="background:#111827;color:white;padding:12px 18px;border-radius:10px;border:none;">Enviar vídeos</button>
</form>
</div></div>
</x-app-layout>
