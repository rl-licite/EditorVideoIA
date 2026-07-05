<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EditorVideoIA - Fase 4 Entrega 2</title>
    <style>
        body { margin:0; font-family: Arial, sans-serif; background:#0f172a; color:#e5e7eb; }
        .wrap { max-width:1180px; margin:0 auto; padding:32px 20px; }
        .hero { background:linear-gradient(135deg,#111827,#1e293b); border:1px solid #334155; border-radius:18px; padding:28px; margin-bottom:22px; }
        h1 { margin:0 0 8px; font-size:32px; }
        p { color:#cbd5e1; line-height:1.5; }
        .success { background:#064e3b; border:1px solid #10b981; padding:14px 16px; border-radius:12px; margin-bottom:20px; color:#d1fae5; }
        .grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(210px,1fr)); gap:16px; margin-bottom:22px; }
        .card { background:#111827; border:1px solid #334155; border-radius:16px; padding:18px; }
        .card strong { display:block; color:#fff; font-size:22px; margin-top:6px; }
        .modules { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:16px; }
        .module { background:#111827; border:1px solid #334155; border-radius:16px; padding:18px; }
        .badge { display:inline-block; color:#ddd6fe; background:#4c1d95; border:1px solid #8b5cf6; padding:5px 9px; border-radius:999px; font-size:12px; font-weight:bold; }
        .btn { border:0; border-radius:10px; background:#7c3aed; color:white; padding:11px 16px; cursor:pointer; margin-top:12px; font-weight:bold; }
        .footer { margin-top:24px; padding:18px; border:1px dashed #475569; border-radius:16px; color:#cbd5e1; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="hero">
        <h1>Fase 4 — Motion, Templates e Exportação</h1>
        <p>Entrega 2 / Etapas 39 + 40. Base profissional para motion graphics, textos animados, templates, exportações, plugins e colaboração.</p>
    </div>

    @if(session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    <div class="grid">
        <div class="card">Fase<strong>{{ $resumo['fase'] }}</strong></div>
        <div class="card">Entrega<strong>{{ $resumo['entrega'] }}</strong></div>
        <div class="card">Etapas<strong>{{ $resumo['etapas'] }}</strong></div>
        <div class="card">Módulos<strong>{{ $resumo['modulos'] }}</strong></div>
    </div>

    <div class="modules">
        @foreach($modulos as $modulo)
            <div class="module">
                <span class="badge">{{ $modulo['status'] }}</span>
                <h3>{{ $modulo['titulo'] }}</h3>
                <p>{{ $modulo['descricao'] }}</p>
                <form method="POST" action="{{ route('editor-video.fase4.motion-templates.executar') }}">
                    @csrf
                    <input type="hidden" name="acao" value="{{ Str::slug($modulo['titulo']) }}">
                    <button class="btn" type="submit">Testar recurso</button>
                </form>
            </div>
        @endforeach
    </div>

    <div class="footer">
        <strong>Homologação:</strong> se esta página abrir, os 8 módulos aparecerem e o botão mostrar mensagem verde sem erro 404/500, a Entrega 2 da Fase 4 está validada.
    </div>
</div>
</body>
</html>
