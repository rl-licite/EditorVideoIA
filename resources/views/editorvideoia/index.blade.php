<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>EditorVideoIA - Fase 6 Pacote 6.1</title>
<link rel="stylesheet" href="{{ asset('editor-video-fase6/editor-fase6.css') }}">
</head>
<body>
<header class="ev-topbar">
    <div>
        <strong>EditorVideoIA</strong>
        <span class="ev-badge">Fase 6 • Pacote 6.1 • Motor Profissional</span>
    </div>
    <div class="ev-top-actions">
        <a href="/editor-video/health">Health check</a>
        <a href="/templates">Templates</a>
        <button id="btnSaveProject" type="button">Salvar projeto</button>
    </div>
</header>

<main class="ev-shell">
    <aside class="ev-panel ev-library">
        <div class="ev-panel-head">
            <h2>Biblioteca</h2>
            <span id="assetCounter">{{ count($assets ?? []) }}</span>
        </div>
        <p class="ev-muted">Envie mídia e arraste para uma trilha da timeline.</p>
        <input id="mediaInput" type="file" accept="video/*,image/*,audio/*">
        <button id="btnUpload" class="ev-primary" type="button">Enviar mídia</button>
        <div id="uploadStatus" class="ev-status small">Pronto para importar.</div>
        <input id="assetSearch" type="search" placeholder="Pesquisar mídia...">
        <select id="assetFilter">
            <option value="all">Todos os tipos</option>
            <option value="video">Vídeo</option>
            <option value="image">Imagem</option>
            <option value="audio">Áudio</option>
        </select>
        <div id="assetList" class="ev-assets">
            @forelse($assets ?? [] as $asset)
                <div class="ev-asset" draggable="true"
                    data-id="{{ $asset->id }}"
                    data-name="{{ $asset->original_name }}"
                    data-type="{{ $asset->media_type }}"
                    data-url="{{ $asset->public_url }}"
                    data-duration="{{ $asset->duration_seconds ?? 6 }}">
                    <div class="ev-thumb {{ $asset->media_type }}">
                        @if($asset->media_type === 'image')
                            <img src="{{ $asset->public_url }}" alt="{{ $asset->original_name }}">
                        @else
                            <span>{{ strtoupper($asset->media_type) }}</span>
                        @endif
                    </div>
                    <div>
                        <strong>{{ $asset->original_name }}</strong>
                        <small>{{ $asset->media_type }} • {{ $asset->extension ?? '' }}</small>
                    </div>
                </div>
            @empty
                <div class="ev-empty">Nenhuma mídia importada.</div>
            @endforelse
        </div>
    </aside>

    <section class="ev-workspace">
        <div class="ev-message" id="systemMessage">Pacote 6.1 ativo: timeline multicamada, ferramentas, zoom, snap, razor, ripple, atalhos e salvamento.</div>
        <section class="ev-preview-card">
            <div class="ev-preview-head">
                <div>
                    <strong>Preview</strong>
                    <span id="previewInfo">Nenhum clipe selecionado</span>
                </div>
                <div class="ev-play-controls">
                    <button id="btnPlay" type="button">▶ Play</button>
                    <button id="btnStop" type="button">■ Stop</button>
                    <span id="timeReadout">00:00.0</span>
                </div>
            </div>
            <div id="previewCanvas" class="ev-preview">
                <div class="ev-preview-empty">Arraste uma mídia para a timeline.</div>
            </div>
        </section>

        <section class="ev-timeline-card">
            <div class="ev-tools">
                <button class="tool active" data-tool="select" type="button">Cursor</button>
                <button class="tool" data-tool="razor" type="button">Razor</button>
                <button class="tool" data-tool="ripple" type="button">Ripple</button>
                <button class="tool" data-tool="hand" type="button">Hand</button>
                <button class="tool" data-tool="zoom" type="button">Zoom</button>
                <span class="divider"></span>
                <button id="btnUndo" type="button">Ctrl+Z</button>
                <button id="btnRedo" type="button">Ctrl+Y</button>
                <button id="btnDuplicate" type="button">Duplicar</button>
                <button id="btnDeleteClip" class="danger" type="button">Delete</button>
                <span class="divider"></span>
                <button id="btnZoomOut" type="button">- Zoom</button>
                <button id="btnZoomIn" type="button">+ Zoom</button>
                <button id="btnFit" type="button">Ajustar</button>
                <label class="snap"><input id="snapToggle" type="checkbox" checked> Snap</label>
            </div>
            <div class="ev-ruler-wrap" id="rulerWrap">
                <div id="timeRuler" class="ev-ruler"></div>
                <div id="playhead" class="ev-playhead"></div>
            </div>
            <div id="tracks" class="ev-tracks"></div>
        </section>
    </section>

    <aside class="ev-panel ev-inspector">
        <div class="ev-panel-head"><h2>Inspector</h2><span id="selectedCounter">0</span></div>
        <div id="inspectorEmpty" class="ev-empty">Selecione um clipe.</div>
        <form id="inspectorForm" class="ev-form hidden">
            <label>Nome</label>
            <input id="clipName" type="text">
            <div class="ev-grid2">
                <div><label>Início</label><input id="clipStart" type="number" min="0" step="0.1"></div>
                <div><label>Duração</label><input id="clipDuration" type="number" min="0.2" step="0.1"></div>
            </div>
            <div class="ev-grid2">
                <div><label>Volume</label><input id="clipVolume" type="number" min="0" max="200" step="1"></div>
                <div><label>Velocidade</label><select id="clipSpeed"><option value="0.25">0.25x</option><option value="0.5">0.5x</option><option value="1">1x</option><option value="2">2x</option><option value="4">4x</option></select></div>
            </div>
            <div class="ev-grid2">
                <div><label>Opacidade</label><input id="clipOpacity" type="number" min="0" max="100" step="1"></div>
                <div><label>Escala</label><input id="clipScale" type="number" min="10" max="300" step="1"></div>
            </div>
            <div class="ev-grid2">
                <div><label>X</label><input id="clipX" type="number" step="1"></div>
                <div><label>Y</label><input id="clipY" type="number" step="1"></div>
            </div>
            <label>Rotação</label>
            <input id="clipRotation" type="number" step="1">
            <button class="ev-primary" type="submit">Aplicar alterações</button>
            <button id="btnCutSelected" type="button">Cortar no playhead</button>
        </form>
        <div class="ev-help">
            <strong>Atalhos</strong>
            <p>Space: Play/Pause<br>Delete: remover<br>Ctrl+C/Ctrl+V: copiar/colar<br>Ctrl+Z/Ctrl+Y: desfazer/refazer<br>Ctrl+D: duplicar<br>Shift + Scroll: zoom</p>
        </div>
    </aside>
</main>

<script>
window.EditorVideoIAFase6 = {
    project: {!! json_encode($project ?? null) !!},
    timeline: {!! json_encode($timeline ?? []) !!},
    assets: {!! json_encode(
        ($assets ?? collect())->map(function ($asset) {
            return [
                'id' => $asset->id,
                'name' => $asset->original_name,
                'original_name' => $asset->original_name,
                'type' => $asset->media_type,
                'media_type' => $asset->media_type,
                'url' => $asset->public_url,
                'public_url' => $asset->public_url,
                'duration' => $asset->duration_seconds ?? 6,
                'extension' => $asset->extension,
            ];
        })->values()
    ) !!},
    routes: {
        upload: "{{ route('editor-video.media.upload') }}",
        save: "{{ route('editor-video.project.save') }}",
        load: "{{ route('editor-video.project.load') }}"
    }
};
</script>

<script src="{{ asset('editor-video-fase6/editor-fase6.js') }}"></script>
</body>
</html>


