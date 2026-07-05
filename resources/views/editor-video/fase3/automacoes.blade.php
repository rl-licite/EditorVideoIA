<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EditorVideoIA - Fase 3</title>
    <style>
        body{font-family:Arial, sans-serif;background:#0f172a;color:#e5e7eb;margin:0;padding:32px}.wrap{max-width:1100px;margin:auto}.top{background:#111827;border:1px solid #334155;border-radius:16px;padding:24px;margin-bottom:20px}.badge{display:inline-block;background:#1d4ed8;padding:8px 12px;border-radius:999px;font-weight:bold}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px}.card{background:#111827;border:1px solid #334155;border-radius:14px;padding:18px}.card h3{margin-top:0;color:#93c5fd}.btn{background:#2563eb;color:white;border:0;border-radius:10px;padding:10px 14px;cursor:pointer;font-weight:bold}.ok{background:#064e3b;border:1px solid #10b981;color:#d1fae5;padding:14px;border-radius:12px;margin-bottom:18px}.tests{background:#020617;border:1px solid #334155;border-radius:14px;padding:18px;margin-top:22px}li{margin-bottom:8px}
    </style>
</head>
<body>
<div class="wrap">
    <div class="top">
        <span class="badge">Fase 3 - Entrega 1</span>
        <h1>IA e Automação - Etapas 25 + 26</h1>
        <p>Esta tela valida o início da Fase 3 do EditorVideoIA. Os botões abaixo executam simulações seguras das automações, sem alterar vídeo real.</p>
    </div>

    @if(session('fase3_ok'))
        <div class="ok">{{ session('fase3_ok') }}</div>
    @endif

    <div class="grid">
        @foreach($modulos as $modulo)
            <div class="card">
                <h3>{{ $modulo['nome'] }}</h3>
                <p>{{ $modulo['descricao'] }}</p>
                <form method="POST" action="{{ route('editor-video.fase3.automacoes.executar') }}">
                    @csrf
                    <input type="hidden" name="acao" value="{{ $modulo['nome'] }}">
                    <button class="btn" type="submit">Testar automação</button>
                </form>
            </div>
        @endforeach
    </div>

    <div class="tests">
        <h2>Checklist de teste simples</h2>
        <ol>
            <li>Confirmar que esta página abriu sem erro 404 ou 500.</li>
            <li>Clicar em qualquer botão "Testar automação".</li>
            <li>Confirmar que aparece uma mensagem verde no topo.</li>
            <li>Confirmar que nenhum vídeo real foi alterado nesta entrega de teste.</li>
        </ol>
    </div>
</div>
</body>
</html>
