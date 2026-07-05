<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EditorVideoIA - Fase 4 Entrega 4</title>
    <style>
        body{font-family:Arial, sans-serif;background:#0f172a;color:#e5e7eb;margin:0;padding:32px}
        .wrap{max-width:1100px;margin:auto}
        .hero{background:#111827;border:1px solid #334155;border-radius:18px;padding:28px;margin-bottom:22px}
        h1{margin:0 0 8px;font-size:32px} p{color:#cbd5e1;line-height:1.5}
        .ok{background:#064e3b;border:1px solid #10b981;color:#d1fae5;padding:14px 18px;border-radius:12px;margin-bottom:20px}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px}
        .card{background:#1e293b;border:1px solid #334155;border-radius:16px;padding:20px}
        .card h3{margin-top:0;color:#fff}
        button{background:#2563eb;color:white;border:0;border-radius:10px;padding:10px 14px;cursor:pointer;font-weight:bold}
        .tag{display:inline-block;background:#312e81;color:#c7d2fe;padding:5px 10px;border-radius:999px;font-size:12px;margin-bottom:12px}
    </style>
</head>
<body>
<div class="wrap">
    <div class="hero">
        <span class="tag">Fase 4 • Entrega 4 • Etapas 43 + 44</span>
        <h1>Exportação Profissional</h1>
        <p>Área para consolidar recursos profissionais de exportação, renderização, presets, fila e controle de qualidade do EditorVideoIA.</p>
    </div>

    @if(session('success'))
        <div class="ok">{{ session('success') }}</div>
    @endif

    <div class="grid">
        @foreach($modulos as $modulo)
            <div class="card">
                <h3>{{ $modulo['titulo'] }}</h3>
                <p>{{ $modulo['descricao'] }}</p>
                <form method="POST" action="{{ route('editor-video.fase4.exportacao-profissional.executar') }}">
                    @csrf
                    <button type="submit">Testar recurso</button>
                </form>
            </div>
        @endforeach
    </div>
</div>
</body>
</html>
