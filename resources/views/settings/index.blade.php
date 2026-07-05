<x-app-layout>
<x-slot name="header"><h2 style="font-size:22px;font-weight:700;">Configurações</h2></x-slot>
<div style="padding:30px;">
@if(session('success'))<div style="background:#dcfce7;color:#166534;padding:14px;border-radius:10px;margin-bottom:20px;">{{ session('success') }}</div>@endif
<div style="background:white;padding:24px;border-radius:14px;box-shadow:0 2px 8px #ddd;max-width:760px;">
<form method="POST" action="{{ route('settings.update') }}">@csrf
<label>Nome da plataforma/marca</label>
<input name="brand_name" value="{{ $settings['brand_name'] ?? 'EditorVideoIA' }}" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:6px 0 14px;">
<label>Marca d'água padrão</label>
<input name="default_watermark" value="{{ $settings['default_watermark'] ?? '' }}" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:6px 0 14px;">
<label>Jobs paralelos máximos</label>
<input type="number" name="max_parallel_jobs" value="{{ $settings['max_parallel_jobs'] ?? 1 }}" min="1" max="4" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:6px 0 14px;">
<label>Resolução padrão</label>
<select name="default_resolution" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;margin:6px 0 18px;">
@foreach(['1080x1920','1920x1080','1080x1080','720x1280'] as $res)
<option value="{{ $res }}" @selected(($settings['default_resolution'] ?? '1080x1920')==$res)>{{ $res }}</option>
@endforeach
</select>
<button style="background:#111827;color:white;border:none;border-radius:8px;padding:11px 15px;">Salvar configurações</button>
</form>
</div>
</div>
</x-app-layout>
