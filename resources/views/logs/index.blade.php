<x-app-layout>
<x-slot name="header"><h2 style="font-size:22px;font-weight:700;">Logs do sistema</h2></x-slot>
<div style="padding:30px;">
<a href="{{ route('dashboard') }}" style="display:inline-block;background:#2563eb;color:white;padding:10px 14px;border-radius:8px;text-decoration:none;margin-bottom:20px;">← Dashboard</a>
<div style="background:white;padding:24px;border-radius:14px;box-shadow:0 2px 8px #ddd;">
<h3 style="font-size:22px;font-weight:700;margin-bottom:15px;">Eventos técnicos</h3>
<table style="width:100%;border-collapse:collapse;">
<thead><tr style="text-align:left;border-bottom:1px solid #ddd;"><th>Data</th><th>Nível</th><th>Área</th><th>Mensagem</th></tr></thead>
<tbody>
@forelse($logs as $log)
<tr style="border-bottom:1px solid #eee;">
<td style="padding:10px;">{{ $log->created_at->format('d/m/Y H:i') }}</td>
<td>{{ strtoupper($log->level) }}</td>
<td>{{ $log->area }}</td>
<td>{{ $log->message }}</td>
</tr>
@empty
<tr><td colspan="4" style="padding:15px;">Nenhum log registrado.</td></tr>
@endforelse
</tbody>
</table>
<div style="margin-top:20px;">{{ $logs->links() }}</div>
</div>
</div>
</x-app-layout>
