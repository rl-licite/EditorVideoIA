<x-app-layout>
<x-slot name="header"><h2 style="font-size:22px;font-weight:700;">Exportações / Fila</h2></x-slot>
<div style="padding:30px;">
@if(session('success'))<div style="background:#dcfce7;color:#166534;padding:14px;border-radius:10px;margin-bottom:20px;">{{ session('success') }}</div>@endif
<div style="display:flex;justify-content:space-between;margin-bottom:20px;">
<div><h3 style="font-size:24px;font-weight:700;">Fila de processamento</h3><p>Rode o worker para processar em segundo plano.</p></div>
<a href="{{ route('exports.create') }}" style="background:#111827;color:white;padding:12px 18px;border-radius:10px;text-decoration:none;">+ Processar vídeos</a>
</div>
<div style="background:white;padding:22px;border-radius:14px;box-shadow:0 2px 8px #ddd;">
<table style="width:100%;border-collapse:collapse;">
<thead><tr style="text-align:left;border-bottom:1px solid #ddd;"><th style="padding:10px;">Vídeo</th><th>Template</th><th>Status</th><th>Progresso</th><th>Ações</th></tr></thead>
<tbody>
@forelse($exports as $export)
<tr style="border-bottom:1px solid #eee;">
<td style="padding:10px;">{{ $export->video->original_name ?? '-' }}</td>
<td>{{ $export->template->name ?? '-' }}</td>
<td>{{ $export->status_label }}</td>
<td><div style="background:#e5e7eb;border-radius:999px;width:160px;"><div style="background:#2563eb;color:white;border-radius:999px;width:{{ $export->progress }}%;padding:2px 6px;font-size:12px;">{{ $export->progress }}%</div></div></td>
<td><a href="{{ route('exports.show', $export) }}" style="color:#2563eb;">Detalhes</a>@if($export->status==='concluido') | <a href="{{ route('exports.download', $export) }}" style="color:#16a34a;">Baixar</a>@endif</td>
</tr>
@empty
<tr><td colspan="5" style="padding:15px;">Nenhuma exportação ainda.</td></tr>
@endforelse
</tbody>
</table>
<div style="margin-top:20px;">{{ $exports->links() }}</div>
</div>
</div>
</x-app-layout>
