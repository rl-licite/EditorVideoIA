<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EditorVideoIA - Health</title>
    <style>body{margin:0;background:#0f172a;color:#e5e7eb;font-family:Arial;padding:30px}a{color:#38bdf8}.card{border:1px solid #334155;border-radius:16px;padding:18px;margin:16px 0;background:#111827}.row{display:flex;justify-content:space-between;border-bottom:1px solid #334155;padding:10px}.ok{color:#4ade80;font-weight:900}.bad{color:#f87171;font-weight:900}h1{margin-top:0}</style>
</head>
<body>
<h1>EditorVideoIA - {{ $etapa }}</h1>
<p><a href="/editor-video">Abrir editor</a> | <a href="/templates">Abrir templates</a></p>
<div class="card"><h2>Rotas</h2>@foreach($routeStatus as $name=>$ok)<div class="row"><span>{{ $name }}</span><span class="{{ $ok ? 'ok':'bad' }}">{{ $ok ? 'OK':'ERRO' }}</span></div>@endforeach</div>
<div class="card"><h2>Tabelas</h2>@foreach($tables as $name=>$ok)<div class="row"><span>{{ $name }}</span><span class="{{ $ok ? 'ok':'bad' }}">{{ $ok ? 'OK':'ERRO' }}</span></div>@endforeach</div>
<div class="card"><h2>Verificações extras</h2>
    <div class="row"><span>Banco de dados responde</span><span class="{{ $databaseOk ? 'ok':'bad' }}">{{ $databaseOk ? 'OK':'ERRO' }}</span></div>
    <div class="row"><span>Conflito public/editor-video removido</span><span class="{{ ! $publicConflict ? 'ok':'bad' }}">{{ ! $publicConflict ? 'OK':'ERRO' }}</span></div>
    <div class="row"><span>Link public/storage</span><span class="{{ $storageLinked ? 'ok':'bad' }}">{{ $storageLinked ? 'OK':'ATENÇÃO' }}</span></div>
    <div class="row"><span>Projetos salvos</span><span class="ok">{{ $projectCount }}</span></div>
    <div class="row"><span>Mídias salvas</span><span class="ok">{{ $assetCount }}</span></div>
</div>
</body>
</html>
