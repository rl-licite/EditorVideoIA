<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EditorVideoIA - Fase 3 Entrega 2</title>
    <style>
        body{margin:0;background:#0f172a;color:#fff;font-family:Arial,Helvetica,sans-serif}.wrap{max-width:1100px;margin:40px auto;padding:0 20px}.hero,.check{border:1px solid #334155;border-radius:14px;padding:24px;background:#111827}.badge{display:inline-block;background:#2563eb;padding:10px 16px;border-radius:18px;font-weight:bold}.grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-top:22px}.card{border:1px solid #334155;border-radius:12px;padding:18px;background:#111827;min-height:150px}.card h3{color:#93c5fd;margin-top:0}.btn{background:#2563eb;border:0;color:#fff;border-radius:9px;padding:11px 16px;font-weight:bold;cursor:pointer}.ok{background:#065f46;border:1px solid #10b981;color:#d1fae5;border-radius:10px;padding:14px;margin:22px 0}.check{margin-top:22px}.check li{margin:9px 0}@media(max-width:900px){.grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:600px){.grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="wrap">
    <div class="hero">
        <span class="badge">Fase 3 - Entrega 2</span>
        <h1>IA de Mídia - Etapas 27 + 28</h1>
        <p>Esta tela valida a segunda entrega da Fase 3 do EditorVideoIA. Os botões abaixo executam simulações seguras de IA para legenda, tradução, voz, áudio e exportação.</p>
    </div>

    @if(session('sucesso'))
        <div class="ok">{{ session('sucesso') }}</div>
    @endif

    <div class="grid">
        @foreach($modulos as $modulo)
            <div class="card">
                <h3>{{ $modulo['nome'] }}</h3>
                <p>{{ $modulo['descricao'] }}</p>
                <form method="POST" action="{{ route('editor-video.fase3.midia-ia.executar') }}">
                    @csrf
                    <input type="hidden" name="acao" value="{{ $modulo['nome'] }}">
                    <button class="btn" type="submit">Testar recurso</button>
                </form>
            </div>
        @endforeach
    </div>

    <div class="check">
        <h2>Checklist de teste simples</h2>
        <ol>
            <li>Confirmar que esta página abriu sem erro 404 ou 500.</li>
            <li>Clicar em qualquer botão "Testar recurso".</li>
            <li>Confirmar que aparece uma mensagem verde no topo.</li>
            <li>Confirmar que nenhum vídeo real foi alterado nesta entrega de teste.</li>
        </ol>
    </div>
</div>
</body>
</html>
