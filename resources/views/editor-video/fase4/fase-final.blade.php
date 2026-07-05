<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EditorVideoIA - Fase 4 Final</title>
    <style>
        body{margin:0;font-family:Arial,Helvetica,sans-serif;background:#0f172a;color:#e5e7eb}
        .wrap{max-width:1180px;margin:0 auto;padding:32px 20px}
        .header{background:linear-gradient(135deg,#111827,#1f2937);border:1px solid #334155;border-radius:18px;padding:28px;margin-bottom:22px}
        .badge{display:inline-block;background:#2563eb;color:white;padding:7px 12px;border-radius:999px;font-size:13px;margin-bottom:12px}
        h1{margin:0;font-size:34px}.lead{color:#cbd5e1;font-size:16px;line-height:1.6}
        .success{background:#064e3b;border:1px solid #10b981;color:#d1fae5;padding:14px 16px;border-radius:12px;margin-bottom:20px}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:16px}.card{background:#111827;border:1px solid #334155;border-radius:16px;padding:18px}
        .card h3{margin:0 0 8px;font-size:18px}.card p{margin:0;color:#cbd5e1;line-height:1.45}.ind{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:20px}.stat{background:#020617;border:1px solid #1e293b;border-radius:14px;padding:15px}.stat small{display:block;color:#94a3b8}.stat strong{font-size:20px;color:#fff}.btn{background:#22c55e;color:#052e16;border:0;padding:11px 16px;border-radius:10px;font-weight:700;cursor:pointer;margin-top:14px}.footer{margin-top:22px;color:#94a3b8;font-size:13px}
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <span class="badge">Fase 4 — Entrega 6 / Etapas 47 + 48</span>
        <h1>Fechamento da Produção Profissional</h1>
        <p class="lead">Painel final da Fase 4 com sistema de plugins, marketplace, colaboração, biblioteca profissional e homologação antes da próxima fase.</p>
        @if(session('success'))<div class="success">{{ session('success') }}</div>@endif
        <form method="POST" action="{{ route('editor-video.fase4.final.executar') }}">@csrf<button class="btn" type="submit">Testar recurso</button></form>
    </div>
    <div class="ind">
        <div class="stat"><small>Fase</small><strong>{{ $indicadores['fase'] }}</strong></div>
        <div class="stat"><small>Status</small><strong>{{ $indicadores['status'] }}</strong></div>
        <div class="stat"><small>Módulos</small><strong>{{ $indicadores['modulos'] }}</strong></div>
        <div class="stat"><small>PHP</small><strong>{{ $indicadores['php'] }}</strong></div>
        <div class="stat"><small>Laravel</small><strong>{{ $indicadores['laravel'] }}</strong></div>
        <div class="stat"><small>Memória</small><strong>{{ $indicadores['memoria'] }}</strong></div>
    </div>
    <div class="grid">
        @foreach($modulos as $modulo)
            <div class="card"><h3>{{ $modulo['titulo'] }}</h3><p>{{ $modulo['descricao'] }}</p></div>
        @endforeach
    </div>
    <div class="footer">Critério de homologação: abrir sem 404/500, exibir os 8 módulos e responder ao botão de teste.</div>
</div>
</body>
</html>
