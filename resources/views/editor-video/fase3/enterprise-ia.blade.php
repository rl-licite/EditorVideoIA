<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EditorVideoIA - Fase 3 Entrega 5</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #0f172a; color: #e5e7eb; }
        .wrap { max-width: 1180px; margin: 0 auto; padding: 32px 20px; }
        .top { background: linear-gradient(135deg, #1e293b, #111827); border: 1px solid #334155; border-radius: 18px; padding: 26px; margin-bottom: 22px; }
        h1 { margin: 0 0 8px; font-size: 30px; }
        .muted { color: #94a3b8; }
        .success { background: #14532d; border: 1px solid #22c55e; color: #dcfce7; padding: 14px 16px; border-radius: 12px; margin-bottom: 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; }
        .card { background: #111827; border: 1px solid #334155; border-radius: 16px; padding: 20px; min-height: 185px; display:flex; flex-direction:column; justify-content:space-between; }
        .card h2 { font-size: 18px; margin: 0 0 8px; }
        .card p { line-height: 1.45; }
        button { cursor: pointer; background: #2563eb; color: white; border: 0; border-radius: 10px; padding: 11px 14px; font-weight: bold; }
        button:hover { background: #1d4ed8; }
        .footer { margin-top: 24px; color: #94a3b8; font-size: 14px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="top">
        <h1>Fase 3 - Entrega 5: IA Enterprise, Performance e Diagnóstico</h1>
        <p class="muted">Etapas 33 + 34 do EditorVideoIA. Tela de validação para recursos Enterprise, otimização, cache, monitoramento e logs avançados.</p>
    </div>

    @if(session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    <div class="grid">
        @foreach($modulos as $modulo)
            <div class="card">
                <div>
                    <h2>{{ $modulo['titulo'] }}</h2>
                    <p class="muted">{{ $modulo['descricao'] }}</p>
                </div>
                <form method="POST" action="{{ route('editor-video.fase3.enterprise-ia.executar') }}">
                    @csrf
                    <input type="hidden" name="modulo" value="{{ $modulo['titulo'] }}">
                    <button type="submit">Testar recurso</button>
                </form>
            </div>
        @endforeach
    </div>

    <div class="footer">
        Checklist: rota aberta, controller carregado, 8 módulos exibidos, botão POST funcionando, mensagem verde exibida, sem erro 404/500.
    </div>
</div>
</body>
</html>
