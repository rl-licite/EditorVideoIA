<x-app-layout>
<x-slot name="header"><h2 style="font-size:22px;font-weight:700;">Checklist de produção</h2></x-slot>
<div style="padding:30px;">
<a href="{{ route('dashboard') }}" style="display:inline-block;background:#2563eb;color:white;padding:10px 14px;border-radius:8px;text-decoration:none;margin-bottom:20px;">← Dashboard</a>
<div style="background:white;padding:24px;border-radius:14px;box-shadow:0 2px 8px #ddd;">
<h3 style="font-size:24px;font-weight:800;margin-bottom:15px;">Homologação da versão 1.0</h3>
<table style="width:100%;border-collapse:collapse;">
<thead><tr style="text-align:left;border-bottom:1px solid #ddd;"><th>Item</th><th>Status</th><th>Observação</th></tr></thead>
<tbody>
@foreach($checks as $check)
<tr style="border-bottom:1px solid #eee;">
<td style="padding:12px;">{{ $check['item'] }}</td>
<td>
@if($check['status']==='ok') ✅ OK
@elseif($check['status']==='atenção') ⚠️ Atenção
@else ❌ Erro
@endif
</td>
<td>{{ $check['note'] }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>
</div>
</x-app-layout>
