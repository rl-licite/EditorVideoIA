(() => {
    'use strict';

    const boot = window.EditorVideoIAFase6 || {};
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const routes = boot.routes || {};

    const baseTracks = [
        { id: 'video_1', name: 'Vídeo 1', type: 'video' },
        { id: 'video_2', name: 'Vídeo 2', type: 'video' },
        { id: 'text_1', name: 'Texto / Legenda', type: 'text' },
        { id: 'audio_1', name: 'Áudio 1', type: 'audio' },
        { id: 'audio_2', name: 'Áudio 2', type: 'audio' }
    ];

    const state = {
        timeline: normalizeTimeline(boot.timeline || {}),
        assets: Array.isArray(boot.assets) ? boot.assets : [],
        selectedIds: [],
        clipboard: [],
        tool: 'select',
        pxPerSecond: 48,
        currentTime: 0,
        playing: false,
        playTimer: null,
        history: [],
        future: []
    };

    const els = {
        tracks: byId('tracks'),
        ruler: byId('timeRuler'),
        playhead: byId('playhead'),
        preview: byId('previewCanvas'),
        previewInfo: byId('previewInfo'),
        timeReadout: byId('timeReadout'),
        msg: byId('systemMessage'),
        save: byId('btnSaveProject'),
        upload: byId('btnUpload'),
        mediaInput: byId('mediaInput'),
        uploadStatus: byId('uploadStatus'),
        assetList: byId('assetList'),
        assetSearch: byId('assetSearch'),
        assetFilter: byId('assetFilter'),
        inspectorEmpty: byId('inspectorEmpty'),
        inspectorForm: byId('inspectorForm'),
        selectedCounter: byId('selectedCounter'),
        btnDeleteClip: byId('btnDeleteClip'),
        btnDuplicate: byId('btnDuplicate'),
        btnUndo: byId('btnUndo'),
        btnRedo: byId('btnRedo'),
        btnCutSelected: byId('btnCutSelected'),
        btnZoomIn: byId('btnZoomIn'),
        btnZoomOut: byId('btnZoomOut'),
        btnFit: byId('btnFit'),
        btnPlay: byId('btnPlay'),
        btnStop: byId('btnStop'),
        snapToggle: byId('snapToggle'),
        assetCounter: byId('assetCounter'),
        fields: {
            clipName: byId('clipName'),
            clipStart: byId('clipStart'),
            clipDuration: byId('clipDuration'),
            clipVolume: byId('clipVolume'),
            clipSpeed: byId('clipSpeed'),
            clipOpacity: byId('clipOpacity'),
            clipScale: byId('clipScale'),
            clipX: byId('clipX'),
            clipY: byId('clipY'),
            clipRotation: byId('clipRotation')
        }
    };

    function byId(id) {
        return document.getElementById(id);
    }

    function uid(prefix) {
        return prefix + '_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2, 8);
    }

    function defaultTrack(type) {
        if (type === 'audio') return 'audio_1';
        if (type === 'text') return 'text_1';
        return 'video_1';
    }

    function normalizeTimeline(raw) {
        const t = raw && typeof raw === 'object' ? raw : {};

        return {
            clips: Array.isArray(t.clips) ? t.clips.map(normalizeClip) : [],
            tracks: Array.isArray(t.tracks) && t.tracks.length ? t.tracks : baseTracks,
            overlays: t.overlays || {},
            canvas: t.canvas || { background: '#111827' },
            batch_jobs: Array.isArray(t.batch_jobs) ? t.batch_jobs : [],
            export_jobs: Array.isArray(t.export_jobs) ? t.export_jobs : [],
            export_settings: t.export_settings || {
                resolution: '1920x1080',
                fps: 30,
                quality: 'alta',
                format: 'mp4',
                bitrate: '12M'
            },
            meta: { ...(t.meta || {}), version: '6.1-motor-profissional-refatorado' }
        };
    }

    function normalizeClip(c) {
        return {
            id: c.id || uid('clip'),
            asset_id: c.asset_id || c.media_id || null,
            track: c.track || defaultTrack(c.type || c.media_type),
            name: c.name || c.original_name || 'Clipe',
            type: c.type || c.media_type || 'video',
            url: c.url || c.public_url || c.stream_url || '',
            start: Number(c.start ?? c.start_time ?? 0),
            duration: Math.max(0.2, Number(c.duration ?? 6)),
            settings: {
                volume: 100,
                speed: 1,
                opacity: 100,
                scale: 100,
                x: 0,
                y: 0,
                rotation: 0,
                ...(c.settings || {})
            }
        };
    }

    function fmt(seconds) {
        const s = Math.max(0, Number(seconds) || 0);
        const minutes = Math.floor(s / 60);
        const sec = (s % 60).toFixed(1).padStart(4, '0');
        return String(minutes).padStart(2, '0') + ':' + sec;
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"]/g, s => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;'
        }[s]));
    }

    function setMsg(text) {
        if (els.msg) els.msg.textContent = text;
    }

    async function api(url, options = {}) {
        const res = await fetch(url, {
            ...options,
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                ...(options.headers || {})
            }
        });

        const json = await res.json().catch(() => ({}));
        if (!res.ok) throw json;
        return json;
    }

    const HistoryManager = {
        push() {
            state.history.push(JSON.stringify(state.timeline));
            if (state.history.length > 60) state.history.shift();
            state.future = [];
        },

        restore(json) {
            state.timeline = normalizeTimeline(JSON.parse(json));
            state.selectedIds = [];
            EditorEngine.renderAll();
        },

        undo() {
            if (!state.history.length) return;
            state.future.push(JSON.stringify(state.timeline));
            this.restore(state.history.pop());
            setMsg('Desfeito.');
        },

        redo() {
            if (!state.future.length) return;
            state.history.push(JSON.stringify(state.timeline));
            this.restore(state.future.pop());
            setMsg('Refeito.');
        }
    };

    const TimelineManager = {
        duration() {
            return Math.max(60, ...state.timeline.clips.map(c => c.start + c.duration + 5));
        },

        snap(value) {
            return els.snapToggle?.checked ? Math.round(value * 2) / 2 : value;
        },

        getClip(id) {
            return state.timeline.clips.find(c => c.id === id);
        },

        getSelectedClips() {
            return state.timeline.clips.filter(c => state.selectedIds.includes(c.id));
        },

        selectOnly(id) {
            state.selectedIds = [id];
        },

        toggleSelect(id) {
            state.selectedIds = state.selectedIds.includes(id)
                ? state.selectedIds.filter(current => current !== id)
                : [...state.selectedIds, id];
        },

        clearSelection() {
            state.selectedIds = [];
        },

        setTool(next) {
            state.tool = next;
            document.querySelectorAll('.tool').forEach(button => {
                button.classList.toggle('active', button.dataset.tool === state.tool);
            });
            setMsg('Ferramenta ativa: ' + next);
        },

        setZoom(value) {
            state.pxPerSecond = Math.min(140, Math.max(18, value));
            EditorEngine.renderAll();
        },

        setCurrentTime(value) {
            state.currentTime = Math.max(0, Number(value) || 0);
            this.updatePlayhead();
            PreviewManager.update();
        },

        updatePlayhead() {
            if (els.playhead) els.playhead.style.left = (140 + state.currentTime * state.pxPerSecond) + 'px';
            if (els.timeReadout) els.timeReadout.textContent = fmt(state.currentTime);
        },

        renderRuler() {
            if (!els.ruler) return;
            els.ruler.innerHTML = '';
            const total = this.duration();
            els.ruler.style.width = (total * state.pxPerSecond) + 'px';

            for (let i = 0; i <= total; i += 5) {
                const tick = document.createElement('div');
                tick.className = 'tick';
                tick.style.left = (i * state.pxPerSecond) + 'px';
                tick.textContent = fmt(i);
                els.ruler.appendChild(tick);
            }

            this.updatePlayhead();
        },

        renderTracks() {
            if (!els.tracks) return;
            els.tracks.innerHTML = '';

            state.timeline.tracks.forEach(track => {
                const row = document.createElement('div');
                row.className = 'ev-track';
                row.dataset.track = track.id;
                row.innerHTML = `
                    <div class="ev-track-head">
                        <strong>${escapeHtml(track.name)}</strong>
                        <small>${escapeHtml(track.type)}</small>
                    </div>
                    <div class="ev-lane" data-track="${escapeHtml(track.id)}"></div>
                `;
                els.tracks.appendChild(row);
            });

            document.querySelectorAll('.ev-lane').forEach(lane => this.bindLane(lane));
            state.timeline.clips.forEach(clip => this.renderClip(clip));
        },

        renderClip(clip) {
            const lane = document.querySelector(`.ev-lane[data-track="${clip.track}"]`);
            if (!lane) return;

            const el = document.createElement('div');
            el.className = `ev-clip ${clip.type || 'video'} ${state.selectedIds.includes(clip.id) ? 'selected' : ''}`;
            el.dataset.id = clip.id;
            el.style.left = (clip.start * state.pxPerSecond) + 'px';
            el.style.width = Math.max(44, clip.duration * state.pxPerSecond) + 'px';
            el.innerHTML = `
                <span class="handle left"></span>
                <span>${escapeHtml(clip.name)}</span>
                <span class="handle right"></span>
            `;

            this.bindClip(el, clip);
            lane.appendChild(el);
        },

        bindLane(lane) {
            lane.addEventListener('dragover', event => {
                event.preventDefault();
                lane.classList.add('dragover');
            });

            lane.addEventListener('dragleave', () => lane.classList.remove('dragover'));

            lane.addEventListener('drop', event => {
                event.preventDefault();
                lane.classList.remove('dragover');

                const raw = event.dataTransfer.getData('application/json');
                if (!raw) return;

                this.addAssetToLane(JSON.parse(raw), lane, event);
            });

            lane.addEventListener('click', event => {
                if (event.target === lane) {
                    const time = event.offsetX / state.pxPerSecond;
                    this.setCurrentTime(time);
                }
            });
        },

        addAssetToLane(asset, lane, event) {
            HistoryManager.push();

            const rect = lane.getBoundingClientRect();
            const rawStart = Math.max(0, (event.clientX - rect.left) / state.pxPerSecond);
            const start = this.snap(rawStart);
            const type = asset.media_type || asset.type || lane.closest('.ev-track')?.querySelector('small')?.textContent || 'video';

            const clip = normalizeClip({
                id: uid('clip'),
                asset_id: asset.id,
                track: lane.dataset.track,
                name: asset.name || asset.original_name,
                type,
                url: asset.url || asset.public_url,
                start,
                duration: Number(asset.duration || 6)
            });

            state.timeline.clips.push(clip);
            this.selectOnly(clip.id);
            EditorEngine.renderAll();
            setMsg('Clipe adicionado na timeline.');
        },

        bindClip(el, clip) {
            el.addEventListener('click', event => {
                event.stopPropagation();

                if (state.tool === 'razor') {
                    this.cutClipAtTime(clip.id, state.currentTime);
                    return;
                }

                if (event.ctrlKey || event.metaKey) this.toggleSelect(clip.id);
                else this.selectOnly(clip.id);

                EditorEngine.renderAll();
            });

            el.addEventListener('mousedown', event => {
                if (state.tool !== 'select' && state.tool !== 'hand') return;

                const side = event.target.classList.contains('left')
                    ? 'left'
                    : event.target.classList.contains('right')
                        ? 'right'
                        : 'move';

                event.preventDefault();
                event.stopPropagation();
                HistoryManager.push();

                if (!state.selectedIds.includes(clip.id)) this.selectOnly(clip.id);

                const sx = event.clientX;
                const original = { start: clip.start, duration: clip.duration };

                const move = ev => {
                    const delta = (ev.clientX - sx) / state.pxPerSecond;

                    if (side === 'move') clip.start = Math.max(0, this.snap(original.start + delta));

                    if (side === 'left') {
                        const ns = Math.max(0, this.snap(original.start + delta));
                        const end = original.start + original.duration;
                        clip.start = Math.min(ns, end - 0.2);
                        clip.duration = end - clip.start;
                    }

                    if (side === 'right') clip.duration = Math.max(0.2, this.snap(original.duration + delta));

                    EditorEngine.renderAll();
                };

                const up = () => {
                    document.removeEventListener('mousemove', move);
                    document.removeEventListener('mouseup', up);
                };

                document.addEventListener('mousemove', move);
                document.addEventListener('mouseup', up);
            });
        },

        deleteSelected() {
            if (!state.selectedIds.length) return;
            HistoryManager.push();
            state.timeline.clips = state.timeline.clips.filter(c => !state.selectedIds.includes(c.id));
            this.clearSelection();
            EditorEngine.renderAll();
            setMsg('Clipe removido.');
        },

        duplicateSelected() {
            const copies = this.getSelectedClips().map(c => ({
                ...JSON.parse(JSON.stringify(c)),
                id: uid('clip'),
                start: c.start + c.duration + 0.2,
                name: c.name + ' cópia'
            }));

            if (!copies.length) return;

            HistoryManager.push();
            state.timeline.clips.push(...copies);
            state.selectedIds = copies.map(c => c.id);
            EditorEngine.renderAll();
            setMsg('Clipe duplicado.');
        },

        copySelected() {
            state.clipboard = this.getSelectedClips().map(c => JSON.parse(JSON.stringify(c)));
            setMsg('Clipe copiado.');
        },

        pasteClipboard() {
            if (!state.clipboard.length) return;

            HistoryManager.push();
            const copies = state.clipboard.map(c => ({
                ...c,
                id: uid('clip'),
                start: state.currentTime,
                name: c.name + ' cópia'
            }));

            state.timeline.clips.push(...copies);
            state.selectedIds = copies.map(c => c.id);
            EditorEngine.renderAll();
            setMsg('Clipe colado no playhead.');
        },

        cutClipAtTime(id, time) {
            const clip = this.getClip(id);
            if (!clip || time <= clip.start + 0.2 || time >= clip.start + clip.duration - 0.2) return;

            HistoryManager.push();

            const right = {
                ...JSON.parse(JSON.stringify(clip)),
                id: uid('clip'),
                start: time,
                duration: (clip.start + clip.duration) - time,
                name: clip.name + ' parte 2'
            };

            clip.duration = time - clip.start;
            state.timeline.clips.push(right);
            this.selectOnly(right.id);
            EditorEngine.renderAll();
            setMsg('Clipe cortado com Razor.');
        }
    };

    const InspectorManager = {
        update() {
            if (!els.selectedCounter || !els.inspectorEmpty || !els.inspectorForm) return;

            els.selectedCounter.textContent = state.selectedIds.length;
            const clip = TimelineManager.getClip(state.selectedIds[0]);

            if (!clip) {
                els.inspectorEmpty.classList.remove('hidden');
                els.inspectorForm.classList.add('hidden');
                return;
            }

            els.inspectorEmpty.classList.add('hidden');
            els.inspectorForm.classList.remove('hidden');

            els.fields.clipName.value = clip.name;
            els.fields.clipStart.value = clip.start;
            els.fields.clipDuration.value = clip.duration;
            els.fields.clipVolume.value = clip.settings.volume;
            els.fields.clipSpeed.value = clip.settings.speed;
            els.fields.clipOpacity.value = clip.settings.opacity;
            els.fields.clipScale.value = clip.settings.scale;
            els.fields.clipX.value = clip.settings.x;
            els.fields.clipY.value = clip.settings.y;
            els.fields.clipRotation.value = clip.settings.rotation;
        },

        apply(event) {
            event.preventDefault();

            const clip = TimelineManager.getClip(state.selectedIds[0]);
            if (!clip) return;

            HistoryManager.push();

            clip.name = els.fields.clipName.value;
            clip.start = Number(els.fields.clipStart.value || 0);
            clip.duration = Math.max(0.2, Number(els.fields.clipDuration.value || 1));
            clip.settings = {
                ...clip.settings,
                volume: Number(els.fields.clipVolume.value || 100),
                speed: Number(els.fields.clipSpeed.value || 1),
                opacity: Number(els.fields.clipOpacity.value || 100),
                scale: Number(els.fields.clipScale.value || 100),
                x: Number(els.fields.clipX.value || 0),
                y: Number(els.fields.clipY.value || 0),
                rotation: Number(els.fields.clipRotation.value || 0)
            };

            EditorEngine.renderAll();
            setMsg('Inspector aplicado no clipe selecionado.');
        }
    };

    const PreviewManager = {
        update() {
            if (!els.preview || !els.previewInfo) return;

            const clip = TimelineManager.getClip(state.selectedIds[0])
                || state.timeline.clips.find(c => state.currentTime >= c.start && state.currentTime <= c.start + c.duration);

            if (!clip) {
                els.preview.innerHTML = '<div class="ev-preview-empty">Arraste uma mídia para a timeline.</div>';
                els.previewInfo.textContent = 'Nenhum clipe selecionado';
                return;
            }

            els.previewInfo.textContent = clip.name;
            const transform = `translate(${clip.settings.x}px,${clip.settings.y}px) scale(${clip.settings.scale / 100}) rotate(${clip.settings.rotation}deg)`;

            if (clip.type === 'video') {
                els.preview.innerHTML = `<video src="${clip.url}" controls style="opacity:${clip.settings.opacity / 100};transform:${transform}"></video>`;
            } else if (clip.type === 'image') {
                els.preview.innerHTML = `<img src="${clip.url}" style="opacity:${clip.settings.opacity / 100};transform:${transform}">`;
            } else if (clip.type === 'audio') {
                els.preview.innerHTML = `<audio src="${clip.url}" controls></audio><div class="ev-preview-empty">Preview de áudio</div>`;
            } else {
                els.preview.innerHTML = `<div style="font-size:42px;font-weight:900;opacity:${clip.settings.opacity / 100};transform:${transform}">${escapeHtml(clip.name)}</div>`;
            }
        }
    };

    const AssetManager = {
        bindExisting() {
            document.querySelectorAll('.ev-asset').forEach(card => this.bindCard(card));
        },

        bindCard(card) {
            card.addEventListener('dragstart', event => {
                event.dataTransfer.setData('application/json', JSON.stringify({
                    id: card.dataset.id,
                    name: card.dataset.name,
                    type: card.dataset.type,
                    media_type: card.dataset.type,
                    url: card.dataset.url,
                    duration: card.dataset.duration
                }));
            });
        },

        addCard(asset) {
            document.querySelector('.ev-empty')?.remove();

            const card = document.createElement('div');
            card.className = 'ev-asset';
            card.draggable = true;
            card.dataset.id = asset.id;
            card.dataset.name = asset.name;
            card.dataset.type = asset.type;
            card.dataset.url = asset.url;
            card.dataset.duration = asset.duration || 6;
            card.innerHTML = `
                <div class="ev-thumb ${asset.type}"><span>${String(asset.type).toUpperCase()}</span></div>
                <div><strong>${escapeHtml(asset.name)}</strong><small>${asset.type} • ${asset.extension || ''}</small></div>
            `;

            this.bindCard(card);
            els.assetList.prepend(card);
            if (els.assetCounter) els.assetCounter.textContent = document.querySelectorAll('.ev-asset').length;
        },

        filter() {
            const query = (els.assetSearch?.value || '').toLowerCase();
            const type = els.assetFilter?.value || 'all';

            document.querySelectorAll('.ev-asset').forEach(card => {
                const matchName = (card.dataset.name || '').toLowerCase().includes(query);
                const matchType = type === 'all' || card.dataset.type === type;
                card.classList.toggle('hide', !(matchName && matchType));
            });
        },

        async upload() {
            const file = els.mediaInput?.files?.[0];
            if (!file) return alert('Escolha um arquivo primeiro.');

            const form = new FormData();
            form.append('media', file);
            els.uploadStatus.textContent = 'Enviando...';

            try {
                const result = await api(routes.upload, { method: 'POST', body: form });
                const a = result.asset;
                const asset = {
                    id: a.id,
                    name: a.original_name,
                    original_name: a.original_name,
                    type: a.media_type,
                    media_type: a.media_type,
                    url: a.public_url || a.stream_url,
                    duration: a.duration_seconds || 6,
                    extension: a.extension
                };

                state.assets.unshift(asset);
                this.addCard(asset);
                els.mediaInput.value = '';
                els.uploadStatus.textContent = 'Mídia importada com sucesso.';
            } catch (error) {
                els.uploadStatus.textContent = error.message || 'Erro ao importar mídia.';
            }
        }
    };

    const PlaybackManager = {
        tick() {
            if (!state.playing) return;
            state.currentTime += 0.1;
            if (state.currentTime > TimelineManager.duration()) state.currentTime = 0;
            TimelineManager.updatePlayhead();
            PreviewManager.update();
        },

        playPause() {
            state.playing = !state.playing;
            if (els.btnPlay) els.btnPlay.textContent = state.playing ? '⏸ Pause' : '▶ Play';

            if (state.playing) state.playTimer = setInterval(() => this.tick(), 100);
            else clearInterval(state.playTimer);
        },

        stop() {
            state.playing = false;
            clearInterval(state.playTimer);
            state.currentTime = 0;
            if (els.btnPlay) els.btnPlay.textContent = '▶ Play';
            TimelineManager.updatePlayhead();
            PreviewManager.update();
        }
    };

    const ProjectManager = {
        async save() {
            try {
                await api(routes.save, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        name: boot.project?.name || 'Projeto EditorVideoIA',
                        duration_seconds: Math.ceil(TimelineManager.duration()),
                        settings: { fase: '6.1', saved_at: new Date().toISOString() },
                        timeline_data: state.timeline
                    })
                });

                setMsg('Projeto salvo com sucesso.');
            } catch (error) {
                alert(error.message || 'Erro ao salvar projeto.');
            }
        }
    };

    const ShortcutManager = {
        bind() {
            document.addEventListener('keydown', event => {
                if (event.target.matches('input,select,textarea')) return;

                if (event.code === 'Space') {
                    event.preventDefault();
                    PlaybackManager.playPause();
                }

                if (event.key === 'Delete') TimelineManager.deleteSelected();

                if (event.ctrlKey && event.key.toLowerCase() === 'z') {
                    event.preventDefault();
                    HistoryManager.undo();
                }

                if (event.ctrlKey && event.key.toLowerCase() === 'y') {
                    event.preventDefault();
                    HistoryManager.redo();
                }

                if (event.ctrlKey && event.key.toLowerCase() === 'c') {
                    event.preventDefault();
                    TimelineManager.copySelected();
                }

                if (event.ctrlKey && event.key.toLowerCase() === 'v') {
                    event.preventDefault();
                    TimelineManager.pasteClipboard();
                }

                if (event.ctrlKey && event.key.toLowerCase() === 'd') {
                    event.preventDefault();
                    TimelineManager.duplicateSelected();
                }
            });

            document.addEventListener('wheel', event => {
                if (event.shiftKey) {
                    event.preventDefault();
                    TimelineManager.setZoom(state.pxPerSecond + (event.deltaY < 0 ? 8 : -8));
                }
            }, { passive: false });
        }
    };

    const EditorEngine = {
        renderAll() {
            TimelineManager.renderRuler();
            TimelineManager.renderTracks();
            InspectorManager.update();
            PreviewManager.update();
        },

        bindUi() {
            AssetManager.bindExisting();

            document.querySelectorAll('.tool').forEach(button => {
                button.addEventListener('click', () => TimelineManager.setTool(button.dataset.tool));
            });

            els.inspectorForm?.addEventListener('submit', event => InspectorManager.apply(event));
            els.save?.addEventListener('click', () => ProjectManager.save());
            els.upload?.addEventListener('click', () => AssetManager.upload());
            els.assetSearch?.addEventListener('input', () => AssetManager.filter());
            els.assetFilter?.addEventListener('change', () => AssetManager.filter());

            els.btnDeleteClip?.addEventListener('click', () => TimelineManager.deleteSelected());
            els.btnDuplicate?.addEventListener('click', () => TimelineManager.duplicateSelected());
            els.btnUndo?.addEventListener('click', () => HistoryManager.undo());
            els.btnRedo?.addEventListener('click', () => HistoryManager.redo());
            els.btnCutSelected?.addEventListener('click', () => {
                if (state.selectedIds[0]) TimelineManager.cutClipAtTime(state.selectedIds[0], state.currentTime);
            });

            els.btnZoomIn?.addEventListener('click', () => TimelineManager.setZoom(state.pxPerSecond + 10));
            els.btnZoomOut?.addEventListener('click', () => TimelineManager.setZoom(state.pxPerSecond - 10));
            els.btnFit?.addEventListener('click', () => TimelineManager.setZoom(48));
            els.btnPlay?.addEventListener('click', () => PlaybackManager.playPause());
            els.btnStop?.addEventListener('click', () => PlaybackManager.stop());

            ShortcutManager.bind();
        },

        init() {
            this.bindUi();
            this.renderAll();
            setMsg('Sprint 6.1 ativa: motor reorganizado em módulos internos.');
        }
    };

    EditorEngine.init();
})();
