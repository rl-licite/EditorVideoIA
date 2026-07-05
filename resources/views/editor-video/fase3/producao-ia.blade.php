<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EditorVideoIA - Fase 3 Entrega 3</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #0f172a; color: #ffffff; }
        .wrap { max-width: 1280px; margin: 0 auto; padding: 32px 24px; }
        .hero, .card, .checklist { background: #111827; border: 1px solid #334155; border-radius: 14px; }
        .hero { padding: 28px; margin-bottom: 24px; }
        .tag { display: inline-block; background: #2563eb; padding: 10px 18px; border-radius: 999px; font-weight: bold; margin-bottom: 16px; }
        h1 { margin: 0 0 18px; font-size: 34px; }
        .alert { background: #065f46; border: 1px solid #10b981; padding: 16px; border-radius: 10px; margin-bottom: 24px; }
        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
        .card { padding: 20px; min-height: 170px; }
        .card h3 { color: #60a5fa; margin-top: 0; font-size: 20px; }
        .btn { border: 0; background: #2563eb; color: #fff; padding: 12px 18px; border-radius: 9px; font-weight: bold; cursor: pointer; }
        .btn:hover { background: #1d4ed8; }
        .checklist { margin-top: 26px; padding: 24px; }
        li { margin-bottom: 10px; }
        @media (max-width: 1000px) { .grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="wrap">
    <div class="hero">
        <div class="tag">Fase 3 - Entrega 3</div>
        <h1>Produção Inteligente - Etapas 29 + 30</h1>
        <p>Esta tela valida a terceira entrega da Fase 3 do EditorVideoIA. Os botões abaixo executam simulações seguras para geração automática, templates, lotes, filas e dashboard de IA.</p>
    </div>

    @if (session('status'))
        <div class="alert">{{ session('status') }}</div>
    @endif

    <div class="grid">
        @foreach($modulos as $modulo)
            <div class="card">
                <h3>{{ $modulo['nome'] }}</h3>
                <p>{{ $modulo['descricao'] }}</p>
                <form method="POST" action="{{ route('editor-video.fase3.producao-ia.executar') }}">
                    @csrf
                    <input type="hidden" name="acao" value="{{ $modulo['nome'] }}">
                    <button class="btn" type="submit">Testar recurso</button>
                </form>
            </div>
        @endforeach
    </div>

    <div class="checklist">
        <h2>Checklist de teste simples</h2>
        <ol>
            <li>Confirmar que esta página abriu sem erro 404 ou 500.</li>
            <li>Clicar em qualquer botão "Testar recurso".</li>
            <li>Confirmar que aparece uma mensagem verde no topo.</li>
            <li>Confirmar que nenhum vídeo real foi alterado nesta entrega de teste.</li>
            <li>Confirmar que os 8 blocos da Entrega 3 aparecem na tela.</li>
        </ol>
    </div>
</div>
</body>
</html>
