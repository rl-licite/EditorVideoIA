<x-app-layout>
<x-slot name="header"><h2 style="font-size:22px;font-weight:700;">Planos</h2></x-slot>
<div style="padding:30px;">
@if(session('success'))<div style="background:#dcfce7;color:#166534;padding:14px;border-radius:10px;margin-bottom:20px;">{{ session('success') }}</div>@endif
<p style="margin-bottom:20px;">Créditos atuais: <strong>{{ auth()->user()->credits_balance ?? 0 }}</strong></p>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px;">
@foreach($plans as $plan)
<div style="background:white;padding:25px;border-radius:14px;box-shadow:0 2px 8px #ddd;">
<h3 style="font-size:24px;font-weight:700;">{{ $plan->name }}</h3>
<p style="font-size:28px;font-weight:700;">R$ {{ number_format($plan->price,2,',','.') }}</p>
<p>{{ $plan->monthly_credits }} créditos</p>
<p>Até {{ $plan->max_videos }} vídeos/mês</p>
<form method="POST" action="{{ route('plans.subscribe', $plan) }}">@csrf
<button style="background:#111827;color:white;border:none;border-radius:8px;padding:10px 14px;margin-top:12px;">Ativar teste</button>
</form>
</div>
@endforeach
</div>
</div>
</x-app-layout>
