<x-app-layout>
<x-slot name="header"><h2 style="font-size:22px;font-weight:700;">Créditos</h2></x-slot>
<div style="padding:30px;">
<div style="background:white;padding:25px;border-radius:14px;box-shadow:0 2px 8px #ddd;margin-bottom:20px;">
<h3 style="font-size:24px;font-weight:700;">Saldo: {{ auth()->user()->credits_balance ?? 0 }} créditos</h3>
<a href="{{ route('plans.index') }}" style="color:#2563eb;">Ver planos</a>
</div>
<div style="background:white;padding:25px;border-radius:14px;box-shadow:0 2px 8px #ddd;">
<table style="width:100%;border-collapse:collapse;">
<thead><tr style="text-align:left;border-bottom:1px solid #ddd;"><th>Data</th><th>Tipo</th><th>Qtd</th><th>Descrição</th></tr></thead>
<tbody>
@forelse($transactions as $t)
<tr style="border-bottom:1px solid #eee;"><td style="padding:10px;">{{ $t->created_at->format('d/m/Y H:i') }}</td><td>{{ $t->type }}</td><td>{{ $t->amount }}</td><td>{{ $t->description }}</td></tr>
@empty
<tr><td colspan="4" style="padding:15px;">Nenhuma movimentação.</td></tr>
@endforelse
</tbody>
</table>
<div style="margin-top:20px;">{{ $transactions->links() }}</div>
</div>
</div>
</x-app-layout>
