<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EditorVideoIA - Editor</title>
    <style>
        body { margin:0; font-family: Arial, sans-serif; background:#0b1120; color:#f8fafc; }
        .header { height:64px; background:#111827; border-bottom:1px solid #334155; display:flex; align-items:center; justify-content:space-between; padding:0 20px; }
        .btn { background:#2563eb; color:white; border:0; padding:10px 16px; border-radius:10px; text-decoration:none; cursor:pointer; font-weight:bold; }
        .layout { display:grid; grid-template-columns:280px 1fr 300px; min-height:calc(100vh - 64px); }
        .panel { border-right:1px solid #334155; padding:18px; background:#111827; }
        .right { border-left:1px solid #334155; border-right:0; }
        .preview { padding:22px; }
        .video-box { height:360px; background:#020617; border:1px solid #334155; border-radius:16px; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:20px; }
        .timeline { margin-top:24px; background:#111827; border:1px solid #334155; border-radius:16px; padding:18px; }
        .track { height:54px; background:#1e293b; border-radius:10px; margin:12px 0; display:flex; align-items:center; padding:0 12px; color:#cbd5e1; }
        .clip { background:#2563eb; padding:10px 18px; border-radius:8px; color:white; margin-right:10px; }
        .media { background:#1e293b; border:1px dashed #64748b; padding:14px; border-radius:12px; margin-bottom:12px; }
        input, select { width:100%; padding:10px; margin:8px 0 14px; border-radius:8px; border:1px solid #334155; background:#020617; color:white; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <strong>EditorVideoIA</strong> — {{ $project['name'] }}
        </div>
        <div>
            <a class="btn" href="/editor-video">Voltar</a>
            <button class="btn" onclick="alert('Projeto salvo em modo teste.')">Salvar</button>
        </div>
    </div>

    <div class="layout">
        <aside class="panel">
            <h3>Biblioteca de mídia</h3>
            <div class="media">Imagem exemplo.jpg</div>
            <div class="media">Vídeo produto.mp4</div>
            <div class="media">Áudio narracao.mp3</div>
            <button class="btn" onclick="alert('Upload será ativado nas próximas etapas.')">Adicionar mídia</button>
        </aside>

        <main class="preview">
            <div class="video-box">Pré-visualização do vídeo</div>

            <section class="timeline">
                <h3>Timeline</h3>
                <div class="track"><span class="clip">Clipe 1</span><span class="clip">Clipe 2</span></div>
                <div class="track"><span class="clip">Texto / Legenda</span></div>
                <div class="track"><span class="clip">Áudio</span></div>
            </section>
        </main>

        <aside class="panel right">
            <h3>Inspector</h3>
            <label>Nome do clipe</label>
            <input value="Clipe 1">
            <label>Duração</label>
            <input value="00:00:10">
            <label>Transição</label>
            <select>
                <option>Fade</option>
                <option>Corte seco</option>
                <option>Zoom</option>
            </select>
            <button class="btn" onclick="alert('Configuração aplicada em modo teste.')">Aplicar</button>
        </aside>
    </div>
</body>
</html>
