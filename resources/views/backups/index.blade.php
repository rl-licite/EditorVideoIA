<x-app-layout>
<x-slot name="header"><h2 style="font-size:22px;font-weight:700;">Backups</h2></x-slot>
<div style="padding:30px;">
@if(session('success'))<div style="background:#dcfce7;color:#166534;padding:14px;border-radius:10px;margin-bottom:20px;">{{ session('success') }}</div>@endif
@if(session('error'))<div style="background:#fee2e2;color:#991b1b;padding:14px;border-radius:10px;margin-bottom:20px;">{{ session('error') }}</div>@endif
<div style="background:white;padding:24px;border-radius:14px;box-shadow:0 2px 8px #ddd;margin-bottom:20px;">
<h3 style="font-size:22px;font-weight:700;">Backup local</h3>
<p style="color:#555;margin:10px 0;">Cria um ZIP com banco SQLite e arquivo .env de backup.</p>
<form method="POST" action="{{ route('backups.store') }}">@csrf
<button style="background:#111827;color:white;border:none;border-radius:8px;padding:11px 15px;">Criar backup agora</button>
</form>
</div>
<div style="background:white;padding:24px;border-radius:14px;box-shadow:0 2px 8px #ddd;">
<table style="width:100%;border-collapse:collapse;">
<thead><tr style="text-align:left;border-bottom:1px solid #ddd;"><th>Data</th><th>Nome</th><th>Status</th><th>Tamanho</th><th>Notas</th></tr></thead>
<tbody>
@forelse($backups as $backup)
<tr style="border-bottom:1px solid #eee;">
<td style="padding:10px;">{{ $backup->created_at->format('d/m/Y H:i') }}</td>
<td>{{ $backup->name }}</td>
<td>{{ $backup->status }}</td>
<td>{{ $backup->size_formatted }}</td>
<td>{{ $backup->notes }}</td>
</tr>
@empty
<tr><td colspan="5" style="padding:15px;">Nenhum backup criado.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
</x-app-layout>
