<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fase 3 - Entrega 4 | EditorVideoIA</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #0f172a; color: #fff; }
        .wrap { max-width: 1280px; margin: 35px auto; padding: 0 24px; }
        .hero, .check { border: 1px solid #334155; border-radius: 14px; padding: 28px; background: #111827; margin-bottom: 26px; }
        .badge { display:inline-block; background:#2563eb; padding:12px 18px; border-radius:18px; font-weight:700; margin-bottom:18px; }
        h1 { font-size: 34px; margin: 0 0 18px; }
        .grid { display:grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap:16px; }
        .card { border:1px solid #334155; border-radius:12px; background:#111827; padding:22px; min-height:170px; }
        .card h3 { color:#60a5fa; margin:0 0 18px; font-size:20px; }
        .btn { background:#2563eb; border:0; border-radius:9px; color:#fff; padding:12px 18px; font-weight:700; cursor:pointer; }
        .alert { background:#065f46; border:1px solid #10b981; padding:16px; border-radius:10px; margin-bottom:22px; }
        li { margin-bottom: 10px; }
        @media (max-width: 900px) { .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 560px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="wrap">
    <div class="hero">
        <span class="badge">Fase 3 - Entrega 4</span>
        <h1>Publicação Inteligente - Etapas 31 + 32</h1>
        <p>Esta tela valida a quarta entrega da Fase 3 do EditorVideoIA. Os botões abaixo executam simulações seguras para vídeos de produtos, anúncios e redes sociais.</p>
    </div>

    @if(session('status'))
        <div class="alert">{{ session('status') }}</div>
    @endif

    <div class="grid">
        @foreach($modulos as $modulo)
            <div class="card">
                <h3>{{ $modulo['nome'] }}</h3>
                <p>{{ $modulo['descricao'] }}</p>
                <form method="POST" action="{{ route('editor-video.fase3.publicacao-ia.executar') }}">
                    @csrf
                    <input type="hidden" name="acao" value="{{ $modulo['nome'] }}">
                    <button class="btn" type="submit">Testar recurso</button>
                </form>
            </div>
        @endforeach
    </div>

    <div class="check" style="margin-top:26px;">
        <h2>Checklist de teste simples</h2>
        <ol>
            <li>Confirmar que esta página abriu sem erro 404 ou 500.</li>
            <li>Clicar em qualquer botão "Testar recurso".</li>
            <li>Confirmar que aparece uma mensagem verde no topo.</li>
            <li>Confirmar que nenhuma publicação real foi feita nesta entrega de teste.</li>
        </ol>
    </div>
</div>
</body>
</html>
