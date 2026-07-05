<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EditorVideoIA — {{ $project->title }}</title>
    <link rel="stylesheet" href="{{ asset('editor-video/editor-video.css') }}">
</head>
<body>
<div class="app-shell" data-project-id="{{ $project->id }}">
    <header class="topbar">
        <div>
            <strong>EditorVideoIA</strong>
            <span>— {{ $project->title }}</span>
        </div>
        <div class="top-actions">
            <button type="button" onclick="window.location.href='/'">Voltar</button>
            <button type="button" id="saveProjectBtn" class="primary">Salvar</button>
        </div>
    </header>

    <main class="editor-grid">
        <aside class="panel media-panel">
            <div class="panel-title-row">
                <h2>Biblioteca de mídia</h2>
                <span id="mediaCount">{{ $mediaItems->count() }}</span>
            </div>

            <div class="filters">
                <input id="mediaSearch" type="search" placeholder="Pesquisar mídia...">
                <select id="mediaFilter">
                    <option value="all">Todos</option>
                    <option value="video">Vídeos</option>
                    <option value="image">Imagens</option>
                    <option value="audio">Áudios</option>
                </select>
            </div>

            <form id="uploadForm" class="upload-box" enctype="multipart/form-data">
                <input id="mediaInput" name="media" type="file" accept="video/*,audio/*,image/*" hidden>
                <button type="button" id="chooseMediaBtn" class="primary full">Adicionar mídia</button>
                <small>MP4, MOV, MKV, AVI, WEBM, MP3, WAV, JPG, PNG, WEBP</small>
            </form>

            <div id="uploadStatus" class="status"></div>

            <div id="mediaList" class="media-list">
                @forelse($mediaItems as $media)
                    <div class="media-card" draggable="true" data-media-id="{{ $media->id }}" data-media-type="{{ $media->media_type }}" data-media-name="{{ $media->original_name }}" data-duration="{{ $media->duration_seconds ?? 5 }}" data-url="{{ $media->url }}">
                        <div class="media-thumb {{ $media->media_type }}">
                            @if($media->media_type === 'image')
                                <img src="{{ $media->url }}" alt="{{ $media->original_name }}">
                            @else
                                <span>{{ strtoupper($media->media_type) }}</span>
                            @endif
                        </div>
                        <div class="media-info">
                            <strong>{{ $media->original_name }}</strong>
                            <small>{{ $media->media_type }} • {{ $media->size_label }}</small>
                            <small>{{ $media->width ?: '-' }}x{{ $media->height ?: '-' }} • {{ $media->duration_seconds ?: 'auto' }}s</small>
                        </div>
                        <button type="button" class="delete-media" data-media-id="{{ $media->id }}">×</button>
                    </div>
                @empty
                    <div class="empty-state">Nenhuma mídia importada ainda.</div>
                @endforelse
            </div>
        </aside>

        <section class="stage-panel">
            <div class="preview-wrap">
                <div id="previewCanvas" class="preview-canvas">
                    <span>Pré-visualização do vídeo</span>
                </div>
                <div class="player-controls">
                    <button id="playBtn" type="button">▶</button>
                    <button id="stopBtn" type="button">■</button>
                    <input id="playheadSlider" type="range" min="0" max="60" value="0">
                    <span id="timeLabel">00:00 / 01:00</span>
                </div>
            </div>

            <div class="timeline-wrap">
                <div class="timeline-header">
                    <h2>Timeline</h2>
                    <div>
                        <button id="zoomOutBtn" type="button">- Zoom</button>
                        <button id="zoomInBtn" type="button">+ Zoom</button>
                    </div>
                </div>

                <div class="time-ruler" id="timeRuler"></div>
                <div class="tracks" id="tracks">
                    <div class="track" data-track="video_1"><span>Vídeo 1</span><div class="track-lane"></div></div>
                    <div class="track" data-track="image_1"><span>Imagem</span><div class="track-lane"></div></div>
                    <div class="track" data-track="text_1"><span>Texto</span><div class="track-lane"></div></div>
                    <div class="track" data-track="audio_1"><span>Áudio 1</span><div class="track-lane"></div></div>
                </div>
            </div>
        </section>

        <aside class="panel inspector-panel">
            <h2>Inspector</h2>
            <div id="inspectorEmpty">Selecione um clipe na timeline.</div>
            <form id="inspectorForm" class="inspector-form hidden">
                <input type="hidden" id="clipId">
                <label>Nome do clipe</label>
                <input type="text" id="clipName">
                <label>Início</label>
                <input type="number" id="clipStart" min="0" step="0.1">
                <label>Duração</label>
                <input type="number" id="clipDuration" min="0.1" step="0.1">
                <label>Volume</label>
                <input type="number" id="clipVolume" min="0" max="200" step="1">
                <label>Velocidade</label>
                <select id="clipSpeed">
                    <option value="0.25">0.25x</option>
                    <option value="0.5">0.5x</option>
                    <option value="1">1x</option>
                    <option value="2">2x</option>
                    <option value="4">4x</option>
                </select>
                <button type="submit" class="primary">Aplicar</button>
                <button type="button" id="deleteClipBtn" class="danger">Remover clipe</button>
            </form>
        </aside>
    </main>
</div>

<script>
window.EditorVideoIAData = {
    projectId: {{ $project->id }},
    clips: @json($clips),
    media: @json($mediaItems),
    routes: {
        upload: "{{ route('editor-video.media.upload', $project) }}",
        storeClip: "{{ route('editor-video.clips.store', $project) }}",
        saveProject: "{{ route('editor-video.projects.save', $project) }}"
    }
};
</script>
<script src="{{ asset('editor-video/editor-video.js') }}"></script>
</body>
</html>
