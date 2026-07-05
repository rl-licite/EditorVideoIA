(() => {
    const data = window.EditorVideoIAData;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let pxPerSecond = 48;
    let selectedClip = null;
    let clips = Array.isArray(data.clips) ? data.clips : [];

    const mediaInput = document.getElementById('mediaInput');
    const chooseMediaBtn = document.getElementById('chooseMediaBtn');
    const uploadStatus = document.getElementById('uploadStatus');
    const mediaList = document.getElementById('mediaList');
    const mediaSearch = document.getElementById('mediaSearch');
    const mediaFilter = document.getElementById('mediaFilter');
    const tracks = document.getElementById('tracks');
    const timeRuler = document.getElementById('timeRuler');
    const previewCanvas = document.getElementById('previewCanvas');

    function api(url, options = {}) {
        return fetch(url, {
            ...options,
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                ...(options.headers || {})
            }
        }).then(async response => {
            const json = await response.json().catch(() => ({}));
            if (!response.ok) throw json;
            return json;
        });
    }

    function mediaTypeLabel(type) {
        if (type === 'video') return 'VÍDEO';
        if (type === 'audio') return 'ÁUDIO';
        if (type === 'image') return 'IMG';
        return 'ARQ';
    }

    function formatTime(seconds) {
        seconds = Math.max(0, Math.round(seconds || 0));
        const m = String(Math.floor(seconds / 60)).padStart(2, '0');
        const s = String(seconds % 60).padStart(2, '0');
        return `${m}:${s}`;
    }

    function renderRuler() {
        timeRuler.innerHTML = '';
        for (let i = 0; i <= 60; i += 5) {
            const tick = document.createElement('div');
            tick.className = 'tick';
            tick.style.left = `${i * pxPerSecond}px`;
            tick.textContent = formatTime(i);
            timeRuler.appendChild(tick);
        }
    }

    function renderClip(clip) {
        const lane = document.querySelector(`.track[data-track="${clip.track}"] .track-lane`);
        if (!lane) return;
        const el = document.createElement('div');
        el.className = `clip ${clip.media?.media_type || ''}`;
        el.dataset.clipId = clip.id;
        el.style.left = `${parseFloat(clip.start_time || 0) * pxPerSecond}px`;
        el.style.width = `${Math.max(70, parseFloat(clip.duration || 5) * pxPerSecond)}px`;
        el.textContent = clip.name || clip.media?.original_name || 'Clipe';
        el.addEventListener('click', () => selectClip(clip));
        lane.appendChild(el);
    }

    function renderTimeline() {
        document.querySelectorAll('.track-lane').forEach(lane => lane.innerHTML = '');
        clips.forEach(renderClip);
    }

    function selectClip(clip) {
        selectedClip = clip;
        document.querySelectorAll('.clip').forEach(el => el.classList.remove('selected'));
        const el = document.querySelector(`.clip[data-clip-id="${clip.id}"]`);
        if (el) el.classList.add('selected');
        document.getElementById('inspectorEmpty').classList.add('hidden');
        document.getElementById('inspectorForm').classList.remove('hidden');
        document.getElementById('clipId').value = clip.id;
        document.getElementById('clipName').value = clip.name || '';
        document.getElementById('clipStart').value = clip.start_time || 0;
        document.getElementById('clipDuration').value = clip.duration || 5;
        document.getElementById('clipVolume').value = clip.settings?.volume ?? 100;
        document.getElementById('clipSpeed').value = clip.settings?.speed ?? 1;
        updatePreview(clip);
    }

    function updatePreview(clip) {
        previewCanvas.innerHTML = '';
        const media = clip.media;
        if (!media) {
            previewCanvas.innerHTML = '<span>Clipe sem mídia vinculada</span>';
            return;
        }
        if (media.media_type === 'video') {
            const video = document.createElement('video');
            video.src = media.url;
            video.controls = true;
            previewCanvas.appendChild(video);
        } else if (media.media_type === 'image') {
            const img = document.createElement('img');
            img.src = media.url;
            previewCanvas.appendChild(img);
        } else if (media.media_type === 'audio') {
            const audio = document.createElement('audio');
            audio.src = media.url;
            audio.controls = true;
            previewCanvas.appendChild(audio);
        } else {
            previewCanvas.innerHTML = `<span>${media.original_name}</span>`;
        }
    }

    function attachMediaCardEvents(card) {
        card.addEventListener('dragstart', event => {
            event.dataTransfer.setData('application/json', JSON.stringify({
                media_id: card.dataset.mediaId,
                media_type: card.dataset.mediaType,
                name: card.dataset.mediaName,
                duration: card.dataset.duration || 5,
            }));
        });
    }

    function addMediaCard(media) {
        const empty = mediaList.querySelector('.empty-state');
        if (empty) empty.remove();
        const card = document.createElement('div');
        card.className = 'media-card';
        card.draggable = true;
        card.dataset.mediaId = media.id;
        card.dataset.mediaType = media.media_type;
        card.dataset.mediaName = media.original_name;
        card.dataset.duration = media.duration_seconds || 5;
        card.dataset.url = media.url;
        card.innerHTML = `
            <div class="media-thumb ${media.media_type}">
                ${media.media_type === 'image' ? `<img src="${media.url}" alt="${media.original_name}">` : `<span>${mediaTypeLabel(media.media_type)}</span>`}
            </div>
            <div class="media-info">
                <strong>${media.original_name}</strong>
                <small>${media.media_type} • ${media.size_label || ''}</small>
                <small>${media.width || '-'}x${media.height || '-'} • ${media.duration_seconds || 'auto'}s</small>
            </div>
            <button type="button" class="delete-media" data-media-id="${media.id}">×</button>`;
        mediaList.prepend(card);
        attachMediaCardEvents(card);
        document.getElementById('mediaCount').textContent = document.querySelectorAll('.media-card').length;
    }

    document.querySelectorAll('.media-card').forEach(attachMediaCardEvents);

    chooseMediaBtn.addEventListener('click', () => mediaInput.click());
    mediaInput.addEventListener('change', async () => {
        if (!mediaInput.files.length) return;
        uploadStatus.textContent = 'Enviando mídia...';
        const file = mediaInput.files[0];
        const form = new FormData();
        form.append('media', file);
        try {
            const result = await api(data.routes.upload, { method: 'POST', body: form });
            addMediaCard(result.media);
            uploadStatus.textContent = 'Mídia importada com sucesso.';
            mediaInput.value = '';
        } catch (error) {
            uploadStatus.textContent = error.message || 'Erro ao importar mídia.';
        }
    });

    document.querySelectorAll('.track-lane').forEach(lane => {
        lane.addEventListener('dragover', event => { event.preventDefault(); lane.classList.add('dragover'); });
        lane.addEventListener('dragleave', () => lane.classList.remove('dragover'));
        lane.addEventListener('drop', async event => {
            event.preventDefault();
            lane.classList.remove('dragover');
            const raw = event.dataTransfer.getData('application/json');
            if (!raw) return;
            const media = JSON.parse(raw);
            const rect = lane.getBoundingClientRect();
            const start = Math.max(0, (event.clientX - rect.left) / pxPerSecond);
            const track = lane.closest('.track').dataset.track;
            try {
                const result = await api(data.routes.storeClip, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        media_id: media.media_id,
                        name: media.name,
                        track,
                        start_time: Math.round(start * 10) / 10,
                        duration: parseFloat(media.duration || 5),
                    })
                });
                clips.push(result.clip);
                renderTimeline();
                selectClip(result.clip);
            } catch (error) {
                alert(error.message || 'Erro ao criar clipe.');
            }
        });
    });

    document.getElementById('inspectorForm').addEventListener('submit', async event => {
        event.preventDefault();
        if (!selectedClip) return;
        const payload = {
            name: document.getElementById('clipName').value,
            start_time: parseFloat(document.getElementById('clipStart').value || 0),
            duration: parseFloat(document.getElementById('clipDuration').value || 5),
            settings: {
                volume: parseInt(document.getElementById('clipVolume').value || 100, 10),
                speed: parseFloat(document.getElementById('clipSpeed').value || 1),
            }
        };
        const url = `/editor-video/timeline/clipes/${selectedClip.id}`;
        const result = await api(url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
        clips = clips.map(c => String(c.id) === String(result.clip.id) ? result.clip : c);
        selectedClip = result.clip;
        renderTimeline();
        selectClip(result.clip);
    });

    document.getElementById('deleteClipBtn').addEventListener('click', async () => {
        if (!selectedClip || !confirm('Remover este clipe da timeline?')) return;
        await api(`/editor-video/timeline/clipes/${selectedClip.id}`, { method: 'DELETE' });
        clips = clips.filter(c => String(c.id) !== String(selectedClip.id));
        selectedClip = null;
        document.getElementById('inspectorForm').classList.add('hidden');
        document.getElementById('inspectorEmpty').classList.remove('hidden');
        previewCanvas.innerHTML = '<span>Pré-visualização do vídeo</span>';
        renderTimeline();
    });

    mediaList.addEventListener('click', async event => {
        const button = event.target.closest('.delete-media');
        if (!button || !confirm('Excluir essa mídia da biblioteca?')) return;
        await api(`/editor-video/midias/${button.dataset.mediaId}`, { method: 'DELETE' });
        button.closest('.media-card').remove();
        document.getElementById('mediaCount').textContent = document.querySelectorAll('.media-card').length;
    });

    function filterMedia() {
        const q = mediaSearch.value.toLowerCase();
        const type = mediaFilter.value;
        document.querySelectorAll('.media-card').forEach(card => {
            const matchText = card.dataset.mediaName.toLowerCase().includes(q);
            const matchType = type === 'all' || card.dataset.mediaType === type;
            card.classList.toggle('hide', !(matchText && matchType));
        });
    }
    mediaSearch.addEventListener('input', filterMedia);
    mediaFilter.addEventListener('change', filterMedia);

    document.getElementById('zoomInBtn').addEventListener('click', () => { pxPerSecond += 12; renderRuler(); renderTimeline(); });
    document.getElementById('zoomOutBtn').addEventListener('click', () => { pxPerSecond = Math.max(24, pxPerSecond - 12); renderRuler(); renderTimeline(); });
    document.getElementById('saveProjectBtn').addEventListener('click', async () => {
        await api(data.routes.saveProject, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ settings: { saved_at: new Date().toISOString() } }) });
        alert('Projeto salvo com sucesso.');
    });

    renderRuler();
    renderTimeline();
})();
