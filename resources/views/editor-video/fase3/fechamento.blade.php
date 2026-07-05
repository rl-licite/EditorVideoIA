<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EditorVideoIA - Fechamento da Fase 3</title>
    <style>
        body { margin:0; font-family: Arial, sans-serif; background:#0f172a; color:#e5e7eb; }
        .wrap { max-width:1180px; margin:0 auto; padding:32px 20px; }
        .hero { background:linear-gradient(135deg,#111827,#1f2937); border:1px solid #334155; border-radius:18px; padding:28px; margin-bottom:22px; }
        h1 { margin:0 0 8px; font-size:32px; }
        p { color:#cbd5e1; }
        .success { background:#064e3b; border:1px solid #10b981; padding:14px 16px; border-radius:12px; margin-bottom:20px; color:#d1fae5; }
        .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px; margin-bottom:22px; }
        .card { background:#111827; border:1px solid #334155; border-radius:16px; padding:18px; }
        .card strong { display:block; color:#fff; font-size:24px; margin-top:6px; }
        .modules { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:16px; }
        .module { background:#111827; border:1px solid #334155; border-radius:16px; padding:18px; }
        .ok { color:#86efac; font-weight:bold; }
        .pending { color:#fbbf24; font-weight:bold; }
        .btn { border:0; border-radius:10px; background:#2563eb; color:white; padding:11px 16px; cursor:pointer; margin-top:12px; font-weight:bold; }
        .section-title { margin:30px 0 14px; }
        .footer { margin-top:24px; padding:18px; border:1px dashed #475569; border-radius:16px; color:#cbd5e1; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="hero">
        <h1>Fase 3 — Fechamento e Homologação Final</h1>
        <p>Auditoria geral, diagnóstico, performance, checklist, relatório e encerramento da Fase 3 do EditorVideoIA.</p>
    </div>

    @if(session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    <div class="grid">
        <div class="card">Controllers encontrados<strong>{{ $summary['controllers'] }}</strong></div>
        <div class="card">Views encontradas<strong>{{ $summary['views'] }}</strong></div>
        <div class="card">Rotas registradas<strong>{{ $summary['routes'] }}</strong></div>
        <div class="card">Memória atual<strong>{{ $summary['memory'] }}</strong></div>
        <div class="card">PHP<strong>{{ $summary['php'] }}</strong></div>
        <div class="card">Laravel<strong>{{ $summary['laravel'] }}</strong></div>
    </div>

    <h2 class="section-title">Blocos da Entrega 6</h2>
    <div class="modules">
        @foreach([
            'Auditoria geral do projeto',
            'Diagnóstico inteligente',
            'Painel de performance',
            'Verificação de dependências',
            'Checklist automático',
            'Relatório final da Fase 3',
            'Dashboard executivo',
            'Homologação final'
        ] as $module)
            <div class="module">
                <h3>{{ $module }}</h3>
                <p>Recurso integrado ao fechamento da Fase 3.</p>
                <form method="POST" action="{{ route('editor-video.fase3.fechamento.executar') }}">
                    @csrf
                    <input type="hidden" name="acao" value="{{ Str::slug($module) }}">
                    <button class="btn" type="submit">Testar recurso</button>
                </form>
            </div>
        @endforeach
    </div>

    <h2 class="section-title">Checklist automático</h2>
    <div class="modules">
        @foreach($checks as $check)
            <div class="module">
                <h3>{{ $check['nome'] }}</h3>
                <p>Tipo: {{ $check['tipo'] }}</p>
                <p class="{{ $check['status'] === 'OK' ? 'ok' : 'pending' }}">{{ $check['status'] }}</p>
                <p>{{ $check['mensagem'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="footer">
        <strong>Status:</strong> se esta tela abriu, os botões funcionaram e não apareceu erro 404/500, a Entrega 6 pode ser homologada e a Fase 3 encerrada.
    </div>
</div>
</body>
</html>
