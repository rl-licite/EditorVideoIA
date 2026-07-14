(() => {
    'use strict';

    const boot = window.EditorVideoIAFase6 || {};
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const routes = boot.routes || {};
    const activeProjectId = boot.activeProjectId || boot.project?.id || null;

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
        future: [],
        lastSelectedId: null,
        suppressNextClipClick: false
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
                batchSummary: byId('batchSummary'),
        batchProgressBar: byId('batchProgressBar'),
        batchWaiting: byId('batchWaiting'),
        batchProcessing: byId('batchProcessing'),
        batchFinished: byId('batchFinished'),
        batchFailed: byId('batchFailed'),
        batchList: byId('batchList'),
        btnBatchCreate: byId('btnBatchCreate'),
        btnBatchStart: byId('btnBatchStart'),
        btnBatchPause: byId('btnBatchPause'),
        btnBatchResume: byId('btnBatchResume'),
        btnBatchCancel: byId('btnBatchCancel'),
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
            clipRotation: byId('clipRotation'),
            clipBrightness: byId('clipBrightness'),
            clipContrast: byId('clipContrast'),
            clipSaturation: byId('clipSaturation'),
            clipExposure: byId('clipExposure'),
            clipTemperature: byId('clipTemperature'),
            clipHue: byId('clipHue'),
            clipSharpen: byId('clipSharpen'),
            clipBlur: byId('clipBlur'),
            clipVignette: byId('clipVignette'),
            clipGrain: byId('clipGrain'),
            clipScaleX: byId('clipScaleX'),
            clipScaleY: byId('clipScaleY'),
            clipAnchorX: byId('clipAnchorX'),
            clipAnchorY: byId('clipAnchorY'),
            clipSkewX: byId('clipSkewX'),
            clipSkewY: byId('clipSkewY'),
            clipCropZoom: byId('clipCropZoom'),
            clipRadius: byId('clipRadius'),
            clipLockRatio: byId('clipLockRatio'),
            clipTransformMode: byId('clipTransformMode'),
            clipFlipX: byId('clipFlipX'),
            clipFlipY: byId('clipFlipY'),
            clipGain: byId('clipGain'),
            clipBalance: byId('clipBalance'),
            clipFadeIn: byId('clipFadeIn'),
            clipFadeOut: byId('clipFadeOut'),
            clipMute: byId('clipMute'),
            btnResetInspector: byId('btnResetInspector'),
            btnResetTransform: byId('btnResetTransform')
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
            meta: { ...(t.meta || {}), version: '6.5-bloco-1-base-estavel' }
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
                brightness: 100,
                contrast: 100,
                saturation: 100,
                exposure: 0,
                temperature: 0,
                hue: 0,
                sharpen: 0,
                blur: 0,
                vignette: 0,
                grain: 0,
effectPreset: 'none',
effectIntensity: 100,
                scaleX: 100,
                scaleY: 100,
                anchorX: 50,
                anchorY: 50,
                skewX: 0,
                skewY: 0,
                cropZoom: 100,
                radius: 0,
                lockRatio: true,
                transformMode: 'fit',
                flipX: false,
                flipY: false,
                gain: 100,
                balance: 0,
                fadeIn: 0,
                fadeOut: 0,
                mute: false,
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

    function withProjectId(url) {
        if (!activeProjectId || !url) return url;
        try {
            const next = new URL(url, window.location.origin);
            if (!next.searchParams.has('project_id')) next.searchParams.set('project_id', activeProjectId);
            return next.pathname + next.search + next.hash;
        } catch (e) {
            return url + (url.includes('?') ? '&' : '?') + 'project_id=' + encodeURIComponent(activeProjectId);
        }
    }

    async function api(url, options = {}) {
        const res = await fetch(withProjectId(url), {
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

        snapToGrid(value) {
            return els.snapToggle?.checked ? Math.round(value * 2) / 2 : value;
        },

        getSnapTargets(excludedIds = []) {
            const excluded = new Set(excludedIds);
            const targets = [0, state.currentTime];

            state.timeline.clips.forEach(candidate => {
                if (excluded.has(candidate.id)) return;
                targets.push(candidate.start);
                targets.push(candidate.start + candidate.duration);
            });

            return Array.from(new Set(
                targets
                    .map(value => Number(value))
                    .filter(value => Number.isFinite(value) && value >= 0)
                    .map(value => Math.round(value * 1000) / 1000)
            ));
        },

        getSmartDelta(items, rawDelta, activeClipId = null) {
            if (!els.snapToggle?.checked || !items.length) return rawDelta;

            const threshold = Math.max(0.06, 10 / state.pxPerSecond);
            const targets = this.getSnapTargets(items.map(item => item.id));
            let best = null;

            const activeItem = items.find(item => item.id === activeClipId) || items[0];
            const points = [];

            items.forEach(item => {
                points.push({ item, value: item.start + rawDelta, kind: 'start' });
                points.push({ item, value: item.start + item.duration + rawDelta, kind: 'end' });
            });

            if (activeItem) {
                points.push({ item: activeItem, value: activeItem.start + rawDelta, kind: 'active-start' });
                points.push({ item: activeItem, value: activeItem.start + activeItem.duration + rawDelta, kind: 'active-end' });
            }

            points.forEach(point => {
                targets.forEach(target => {
                    const diff = target - point.value;
                    const abs = Math.abs(diff);
                    if (abs <= threshold && (!best || abs < best.abs)) {
                        best = { abs, diff, target };
                    }
                });
            });

            if (best) return rawDelta + best.diff;
            return this.snapToGrid(rawDelta);
        },

        getSmartStart(rawStart, clip = null) {
            if (!els.snapToggle?.checked) return rawStart;

            const start = Number(rawStart) || 0;
            const duration = Number(clip?.duration || 0);
            const threshold = Math.max(0.06, 10 / state.pxPerSecond);
            const targets = this.getSnapTargets(clip?.id ? [clip.id] : []);
            let best = null;

            const points = [start];
            if (duration > 0) points.push(start + duration);

            points.forEach(point => {
                targets.forEach(target => {
                    const diff = target - point;
                    const abs = Math.abs(diff);
                    if (abs <= threshold && (!best || abs < best.abs)) best = { abs, diff };
                });
            });

            return Math.max(0, best ? start + best.diff : this.snapToGrid(start));
        },

        getClip(id) {
            return state.timeline.clips.find(c => c.id === id);
        },

        getSelectedClips() {
            return state.timeline.clips.filter(c => state.selectedIds.includes(c.id));
        },

        selectOnly(id) {
            state.selectedIds = [id];
            state.lastSelectedId = id;
        },

        toggleSelect(id) {
            state.selectedIds = state.selectedIds.includes(id)
                ? state.selectedIds.filter(current => current !== id)
                : [...state.selectedIds, id];
            state.lastSelectedId = id;
        },

        selectRange(toId) {
            const fromId = state.lastSelectedId || state.selectedIds[state.selectedIds.length - 1] || toId;
            const fromClip = this.getClip(fromId);
            const toClip = this.getClip(toId);

            if (!fromClip || !toClip || fromClip.track !== toClip.track) {
                this.selectOnly(toId);
                state.lastSelectedId = toId;
                return;
            }

            const start = Math.min(fromClip.start, toClip.start);
            const end = Math.max(fromClip.start + fromClip.duration, toClip.start + toClip.duration);

            state.selectedIds = state.timeline.clips
                .filter(clip => clip.track === toClip.track && clip.start < end && (clip.start + clip.duration) > start)
                .sort((a, b) => a.start - b.start)
                .map(clip => clip.id);

            state.lastSelectedId = toId;
        },

        clearSelection() {
            state.selectedIds = [];
            state.lastSelectedId = null;
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
                            ${KeyframeManager.markersHtml(clip)}
                <span class="handle left"></span>
                <span>${escapeHtml(clip.name)}</span>
                <span class="handle right"></span>
            `;

            this.bindClip(el, clip);
            lane.appendChild(el);
        },

        selectClipsInBox(box, additive = false) {
            const ids = [];

            document.querySelectorAll('.ev-clip').forEach(el => {
                const rect = el.getBoundingClientRect();
                const intersects = !(rect.right < box.left || rect.left > box.right || rect.bottom < box.top || rect.top > box.bottom);
                if (intersects && el.dataset.id) ids.push(el.dataset.id);
            });

            const nextIds = additive
                ? Array.from(new Set([...state.selectedIds, ...ids]))
                : ids;

            state.selectedIds = nextIds;
            state.lastSelectedId = nextIds[nextIds.length - 1] || null;
            EditorEngine.renderAll();
            setMsg(state.selectedIds.length ? `${state.selectedIds.length} clipe(s) selecionado(s).` : 'Nenhum clipe selecionado.');
        },

        startBoxSelection(event) {
            if (!els.tracks) return false;

            event.preventDefault();
            event.stopPropagation();

            const startX = event.clientX;
            const startY = event.clientY;
            const additive = event.ctrlKey || event.metaKey;
            let moved = false;

            const box = document.createElement('div');
            box.className = 'ev-selection-box';
            box.style.position = 'fixed';
            box.style.left = startX + 'px';
            box.style.top = startY + 'px';
            box.style.width = '0px';
            box.style.height = '0px';
            box.style.border = '1px solid #60a5fa';
            box.style.background = 'rgba(37, 99, 235, 0.18)';
            box.style.boxShadow = '0 0 0 1px rgba(96,165,250,.25), 0 10px 30px rgba(0,0,0,.25)';
            box.style.zIndex = '9999';
            box.style.pointerEvents = 'none';
            box.style.display = 'none';
            document.body.appendChild(box);

            const move = ev => {
                const width = Math.abs(ev.clientX - startX);
                const height = Math.abs(ev.clientY - startY);

                if (width > 4 || height > 4) {
                    moved = true;
                    box.style.display = 'block';
                }

                const left = Math.min(startX, ev.clientX);
                const top = Math.min(startY, ev.clientY);

                box.style.left = left + 'px';
                box.style.top = top + 'px';
                box.style.width = width + 'px';
                box.style.height = height + 'px';
            };

            const up = ev => {
                document.removeEventListener('mousemove', move);
                document.removeEventListener('mouseup', up);

                const rect = box.getBoundingClientRect();
                box.remove();

                if (!moved) {
                    const lane = event.currentTarget;
                    const time = (event.offsetX || 0) / state.pxPerSecond;
                    this.setCurrentTime(time);
                    return;
                }

                this.selectClipsInBox(rect, additive);
            };

            document.addEventListener('mousemove', move);
            document.addEventListener('mouseup', up);
            return true;
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

            lane.addEventListener('mousedown', event => {
                if (event.target === lane) this.startBoxSelection(event);
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
            const start = this.getSmartStart(rawStart);
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

                if (state.suppressNextClipClick) {
                    state.suppressNextClipClick = false;
                    return;
                }

                if (state.tool === 'razor') {
                    this.cutClipAtTime(clip.id, state.currentTime);
                    return;
                }

                if (event.shiftKey) this.selectRange(clip.id);
                else if (event.ctrlKey || event.metaKey) this.toggleSelect(clip.id);
                else this.selectOnly(clip.id);

                EditorEngine.renderAll();
                if (state.selectedIds.length > 1) setMsg(`${state.selectedIds.length} clipe(s) selecionado(s).`);
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

                const groupIds = state.selectedIds.includes(clip.id)
                    ? [...state.selectedIds]
                    : [clip.id];

                state.selectedIds = groupIds;
                state.lastSelectedId = clip.id;

                const sx = event.clientX;
                let moved = false;
                let historySaved = false;

                const selectedAtStart = state.timeline.clips
                    .filter(selectedClip => groupIds.includes(selectedClip.id))
                    .map(selectedClip => ({
                        id: selectedClip.id,
                        start: selectedClip.start,
                        duration: selectedClip.duration,
                        track: selectedClip.track
                    }));
                const original = { start: clip.start, duration: clip.duration };

                const saveHistoryOnce = () => {
                    if (historySaved) return;
                    HistoryManager.push();
                    historySaved = true;
                };

                const move = ev => {
                    const rawDeltaPx = ev.clientX - sx;
                    const delta = rawDeltaPx / state.pxPerSecond;

                    if (Math.abs(rawDeltaPx) > 3) moved = true;
                    if (!moved) return;

                    saveHistoryOnce();

                    if (side === 'move') {
                        const minStart = Math.min(...selectedAtStart.map(item => item.start));
                        const limitedDelta = Math.max(delta, -minStart);
                        const smartDelta = this.getSmartDelta(selectedAtStart, limitedDelta, clip.id);
                        const finalDelta = Math.max(smartDelta, -minStart);

                        selectedAtStart.forEach(item => {
                            const movingClip = this.getClip(item.id);
                            if (!movingClip) return;
                            movingClip.start = Math.max(0, item.start + finalDelta);
                            movingClip.track = item.track;
                        });
                    }

                    if (side === 'left') {
                        const ns = Math.max(0, this.getSmartStart(original.start + delta, clip));
                        const end = original.start + original.duration;
                        clip.start = Math.min(ns, end - 0.2);
                        clip.duration = end - clip.start;
                    }

                    if (side === 'right') {
                        const rawEnd = original.start + original.duration + delta;
                        const snappedEnd = this.getSmartStart(rawEnd, { id: clip.id, duration: 0 });
                        clip.duration = Math.max(0.2, snappedEnd - original.start);
                    }

                    EditorEngine.renderAll();
                };

                const up = () => {
                    document.removeEventListener('mousemove', move);
                    document.removeEventListener('mouseup', up);

                    if (moved) {
                        state.suppressNextClipClick = true;
                        const count = state.selectedIds.length;
                        setMsg(count > 1 ? `${count} clipe(s) movido(s) em grupo.` : 'Clipe movido.');
                    }
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
    const KeyframeManager = {
        ensure(clip) {
            if (!clip) return [];
            if (!Array.isArray(clip.keyframes)) clip.keyframes = [];
            clip.keyframes = clip.keyframes
                .filter(k => k && typeof k === 'object')
                .map(k => ({
                    time: Math.max(0, Number(k.time || 0)),
                    settings: k.settings && typeof k.settings === 'object' ? k.settings : {}
                }))
                .sort((a, b) => a.time - b.time);
            return clip.keyframes;
        },

        add() {
            const clip = TimelineManager.getClip(state.selectedIds[0]);
            if (!clip) {
                setMsg('Selecione um clipe para criar keyframe.');
                return;
            }

            HistoryManager.push();

            const localTime = Math.max(0, Number(state.currentTime || 0) - Number(clip.start || 0));
            const time = Number(localTime.toFixed(2));
            const keyframes = this.ensure(clip);

            const frame = {
                time,
                settings: JSON.parse(JSON.stringify(clip.settings || {}))
            };

            const existingIndex = keyframes.findIndex(k => Math.abs(Number(k.time) - time) < 0.08);

            if (existingIndex >= 0) {
                keyframes[existingIndex] = frame;
                setMsg('Keyframe atualizado.');
            } else {
                keyframes.push(frame);
                setMsg('Keyframe criado.');
            }

            clip.keyframes = keyframes.sort((a, b) => a.time - b.time);
            EditorEngine.renderAll();
        },

        removeNearest() {
            const clip = TimelineManager.getClip(state.selectedIds[0]);
            if (!clip) return;

            const keyframes = this.ensure(clip);
            if (!keyframes.length) return;

            HistoryManager.push();

            const localTime = Math.max(0, Number(state.currentTime || 0) - Number(clip.start || 0));
            let bestIndex = 0;
            let bestDistance = Infinity;

            keyframes.forEach((k, index) => {
                const distance = Math.abs(Number(k.time || 0) - localTime);
                if (distance < bestDistance) {
                    bestDistance = distance;
                    bestIndex = index;
                }
            });

            keyframes.splice(bestIndex, 1);
            clip.keyframes = keyframes;
            EditorEngine.renderAll();
            setMsg('Keyframe removido.');
        },

        markersHtml(clip) {
            const keyframes = this.ensure(clip);
            if (!keyframes.length) return '';
            const duration = Math.max(0.2, Number(clip.duration || 1));

            return keyframes.map(k => {
                const left = Math.max(0, Math.min(100, (Number(k.time || 0) / duration) * 100));
                return '<span class="ev-keyframe-marker" style="left:' + left + '%" title="Keyframe"></span>';
            }).join('');
        }
    };
    const InspectorManager = {
        update() {
            if (!els.selectedCounter || !els.inspectorEmpty || !els.inspectorForm) return;

            els.selectedCounter.textContent = state.selectedIds.length;

            if (state.selectedIds.length > 1) {
                els.inspectorEmpty.textContent = `${state.selectedIds.length} clipes selecionados. Arraste um deles para mover o grupo, use Delete para remover todos ou Ctrl+D para duplicar.`;
                els.inspectorEmpty.classList.remove('hidden');
                els.inspectorForm.classList.add('hidden');
                return;
            }

            const clip = TimelineManager.getClip(state.selectedIds[0]);

            if (!clip) {
                els.inspectorEmpty.textContent = 'Selecione um clipe.';
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
            els.fields.clipBrightness.value = clip.settings.brightness ?? 100;
            els.fields.clipContrast.value = clip.settings.contrast ?? 100;
            els.fields.clipSaturation.value = clip.settings.saturation ?? 100;
            els.fields.clipExposure.value = clip.settings.exposure ?? 0;
            els.fields.clipTemperature.value = clip.settings.temperature ?? 0;
            els.fields.clipHue.value = clip.settings.hue ?? 0;
            els.fields.clipSharpen.value = clip.settings.sharpen ?? 0;
            els.fields.clipBlur.value = clip.settings.blur ?? 0;
            els.fields.clipVignette.value = clip.settings.vignette ?? 0;
            els.fields.clipGrain.value = clip.settings.grain ?? 0;
            els.fields.clipScaleX.value = clip.settings.scaleX ?? 100;
            els.fields.clipScaleY.value = clip.settings.scaleY ?? 100;
            els.fields.clipAnchorX.value = clip.settings.anchorX ?? 50;
            els.fields.clipAnchorY.value = clip.settings.anchorY ?? 50;
            els.fields.clipSkewX.value = clip.settings.skewX ?? 0;
            els.fields.clipSkewY.value = clip.settings.skewY ?? 0;
            els.fields.clipCropZoom.value = clip.settings.cropZoom ?? 100;
            els.fields.clipRadius.value = clip.settings.radius ?? 0;
            els.fields.clipLockRatio.checked = clip.settings.lockRatio !== false;
            els.fields.clipTransformMode.value = clip.settings.transformMode || 'fit';
            els.fields.clipFlipX.checked = !!clip.settings.flipX;
            els.fields.clipFlipY.checked = !!clip.settings.flipY;
            els.fields.clipGain.value = clip.settings.gain ?? 100;
            els.fields.clipBalance.value = clip.settings.balance ?? 0;
            els.fields.clipFadeIn.value = clip.settings.fadeIn ?? 0;
            els.fields.clipFadeOut.value = clip.settings.fadeOut ?? 0;
            els.fields.clipMute.checked = !!clip.settings.mute;
            if (els.fields.clipEffectPreset)
    els.fields.clipEffectPreset.value = clip.settings.effectPreset || 'none';

if (els.fields.clipEffectIntensity)
    els.fields.clipEffectIntensity.value = clip.settings.effectIntensity ?? 100;
        },

        readFormIntoClip({ saveHistory = true } = {}) {
            const clip = TimelineManager.getClip(state.selectedIds[0]);
            if (!clip) return null;

            if (saveHistory) HistoryManager.push();

            clip.name = els.fields.clipName.value || clip.name;
            clip.start = Math.max(0, Number(els.fields.clipStart.value || 0));
            clip.duration = Math.max(0.2, Number(els.fields.clipDuration.value || 1));
            clip.settings = {
                ...clip.settings,
                volume: Number(els.fields.clipVolume.value || 100),
                speed: Number(els.fields.clipSpeed.value || 1),
                opacity: Number(els.fields.clipOpacity.value || 100),
                scale: Number(els.fields.clipScale.value || 100),
                x: Number(els.fields.clipX.value || 0),
                y: Number(els.fields.clipY.value || 0),
                rotation: Number(els.fields.clipRotation.value || 0),
                brightness: Number(els.fields.clipBrightness.value || 100),
                contrast: Number(els.fields.clipContrast.value || 100),
                saturation: Number(els.fields.clipSaturation.value || 100),
                exposure: Number(els.fields.clipExposure.value || 0),
                temperature: Number(els.fields.clipTemperature.value || 0),
                hue: Number(els.fields.clipHue.value || 0),
                sharpen: Number(els.fields.clipSharpen.value || 0),
                blur: Number(els.fields.clipBlur.value || 0),
                vignette: Number(els.fields.clipVignette.value || 0),
                grain: Number(els.fields.clipGrain.value || 0),
                effectPreset: els.fields.clipEffectPreset?.value || 'none',
effectIntensity: Number(els.fields.clipEffectIntensity?.value || 100),
                scaleX: Number(els.fields.clipScaleX.value || 100),
                scaleY: Number(els.fields.clipScaleY.value || 100),
                anchorX: Number(els.fields.clipAnchorX.value || 50),
                anchorY: Number(els.fields.clipAnchorY.value || 50),
                skewX: Number(els.fields.clipSkewX.value || 0),
                skewY: Number(els.fields.clipSkewY.value || 0),
                cropZoom: Number(els.fields.clipCropZoom.value || 100),
                radius: Number(els.fields.clipRadius.value || 0),
                lockRatio: !!els.fields.clipLockRatio.checked,
                transformMode: els.fields.clipTransformMode.value || 'fit',
                flipX: !!els.fields.clipFlipX.checked,
                flipY: !!els.fields.clipFlipY.checked,
                gain: Number(els.fields.clipGain.value || 100),
                balance: Number(els.fields.clipBalance.value || 0),
                fadeIn: Number(els.fields.clipFadeIn.value || 0),
                fadeOut: Number(els.fields.clipFadeOut.value || 0),
                mute: !!els.fields.clipMute.checked
            };

            return clip;
        },

        resetSelected() {
            const clip = TimelineManager.getClip(state.selectedIds[0]);
            if (!clip) return;
            HistoryManager.push();
            clip.settings = {
                ...clip.settings,
                brightness: 100,
                contrast: 100,
                saturation: 100,
                exposure: 0,
                temperature: 0,
                hue: 0,
                sharpen: 0,
                blur: 0,
                vignette: 0,
                grain: 0,
                scaleX: 100,
                scaleY: 100,
                anchorX: 50,
                anchorY: 50,
                skewX: 0,
                skewY: 0,
                cropZoom: 100,
                radius: 0,
                lockRatio: true,
                transformMode: 'fit',
                flipX: false,
                flipY: false,
                gain: 100,
                balance: 0,
                fadeIn: 0,
                fadeOut: 0,
                mute: false
            };
            this.update();
            EditorEngine.renderAll();
            setMsg('Ajustes profissionais resetados.');
        },

        resetTransformSelected() {
            const clip = TimelineManager.getClip(state.selectedIds[0]);
            if (!clip) return;
            HistoryManager.push();
            clip.settings = {
                ...clip.settings,
                scale: 100,
                x: 0,
                y: 0,
                rotation: 0,
                scaleX: 100,
                scaleY: 100,
                anchorX: 50,
                anchorY: 50,
                skewX: 0,
                skewY: 0,
                cropZoom: 100,
                radius: 0,
                lockRatio: true,
                transformMode: 'fit',
                flipX: false,
                flipY: false
            };
            this.update();
            EditorEngine.renderAll();
            setMsg('Transformação resetada.');
        },

        apply(event) {
            event.preventDefault();

            const clip = this.readFormIntoClip({ saveHistory: true });
            if (!clip) return;

            EditorEngine.renderAll();
            setMsg('Inspector aplicado no clipe selecionado.');
        },

        liveApply() {
            const clip = this.readFormIntoClip({ saveHistory: false });
            if (!clip) return;
            TimelineManager.renderRuler();
            TimelineManager.renderTracks();
            PreviewManager.update();
        }
    };


function inspectorEffectFilter(settings){

    const preset = settings.effectPreset || 'none';
    const i = Math.max(0, Math.min(100, Number(settings.effectIntensity ?? 100))) / 100;

    switch(preset){

        case 'bw':
            return 'grayscale(' + i + ')';

        case 'sepia':
            return 'sepia(' + i + ')';

        case 'vintage':
            return 'sepia('+(0.6*i)+') contrast('+(1+0.2*i)+') saturate('+(1-0.25*i)+')';

        case 'cinema':
            return 'contrast('+(1+0.25*i)+') saturate('+(1-0.10*i)+') brightness('+(1-0.05*i)+')';

        case 'hdr':
            return 'contrast('+(1+0.35*i)+') saturate('+(1+0.25*i)+') brightness('+(1+0.08*i)+')';

        case 'vhs':
            return 'contrast('+(1+0.18*i)+') saturate('+(1-0.35*i)+') hue-rotate('+(8*i)+'deg)';

        case 'cold':
            return 'hue-rotate(-12deg)';

        case 'warm':
            return 'hue-rotate(12deg)';

        default:
            return '';
    }
}

function inspectorFilter(clip) {
    const s = clip.settings || {};
    const exposure = 100 + Number(s.exposure || 0);
    const temperature = Number(s.temperature || 0);
    const sepia = Math.max(0, temperature) / 220;
    const cool = Math.max(0, -temperature) / 240;
    const blur = Number(s.blur || 0);
    const hue = Number(s.hue || 0);
    const contrast = Number(s.contrast ?? 100);
    const brightness = Math.max(0, Number(s.brightness ?? 100) * exposure / 100);
    const saturation = Number(s.saturation ?? 100);

    const baseFilter = `brightness(${brightness}%) contrast(${contrast}%) saturate(${saturation}%) hue-rotate(${hue}deg) sepia(${sepia}) opacity(${1 - cool * 0.06}) blur(${blur}px)`;
    const effectFilter = inspectorEffectFilter(s);

    return [baseFilter, effectFilter].filter(Boolean).join(' ');
}

    function inspectorTransform(clip) {
        const s = clip.settings || {};
        const scale = Number(s.scale ?? 100) / 100;
        const cropZoom = Number(s.cropZoom ?? 100) / 100;
        const scaleX = Number(s.scaleX ?? 100) / 100 * (s.flipX ? -1 : 1);
        const scaleY = Number(s.scaleY ?? 100) / 100 * (s.flipY ? -1 : 1);
        const skewX = Number(s.skewX || 0);
        const skewY = Number(s.skewY || 0);
        return `translate(${Number(s.x || 0)}px,${Number(s.y || 0)}px) rotate(${Number(s.rotation || 0)}deg) skew(${skewX}deg,${skewY}deg) scale(${scale * cropZoom * scaleX},${scale * cropZoom * scaleY})`;
    }

    function inspectorTransformOrigin(clip) {
        const s = clip.settings || {};
        return `${Number(s.anchorX ?? 50)}% ${Number(s.anchorY ?? 50)}%`;
    }

    function inspectorObjectFit(clip) {
        const mode = clip.settings?.transformMode || 'fit';
        if (mode === 'fill') return 'cover';
        if (mode === 'free') return 'contain';
        return 'contain';
    }

    function inspectorExtraOverlay(clip) {
        const s = clip.settings || {};
        const vignette = Math.max(0, Number(s.vignette || 0));
        const grain = Math.max(0, Number(s.grain || 0));
        if (!vignette && !grain) return '';
        const vignetteHtml = vignette ? `<div class="ev-preview-vignette" style="opacity:${Math.min(.75, vignette / 100)}"></div>` : '';
        const grainHtml = grain ? `<div class="ev-preview-grain" style="opacity:${Math.min(.45, grain / 100)}"></div>` : '';
        return vignetteHtml + grainHtml;
    }

    const PreviewManager = {
        activeClipId: null,
        mediaEl: null,
        audioCtx: null,
        audioNodes: new WeakMap(),

        ensureAudioGraph(mediaEl) {
            if (!mediaEl || (mediaEl.tagName !== 'VIDEO' && mediaEl.tagName !== 'AUDIO')) return null;
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx || !window.StereoPannerNode) return null;

            if (!this.audioCtx) this.audioCtx = new AudioCtx();

            if (this.audioNodes.has(mediaEl)) return this.audioNodes.get(mediaEl);

            try {
                const source = this.audioCtx.createMediaElementSource(mediaEl);
                const pan = this.audioCtx.createStereoPanner();
                const gain = this.audioCtx.createGain();

                source.connect(pan);
                pan.connect(gain);
                gain.connect(this.audioCtx.destination);

                const graph = { source, pan, gain };
                this.audioNodes.set(mediaEl, graph);
                return graph;
            } catch (e) {
                return null;
            }
        },

        findActiveClip() {
            const selected = TimelineManager.getClip(state.selectedIds[0]);
            if (selected) return selected;

            return state.timeline.clips
                .filter(c => state.currentTime >= c.start && state.currentTime <= c.start + c.duration)
                .sort((a, b) => {
                    const ta = state.timeline.tracks.findIndex(t => t.id === a.track);
                    const tb = state.timeline.tracks.findIndex(t => t.id === b.track);
                    return tb - ta;
                })[0] || null;
        },

        build(clip) {
            this.activeClipId = clip?.id || null;
            this.mediaEl = null;

            if (!clip) {
                els.preview.innerHTML = '<div class="ev-preview-empty">Arraste uma mídia para a timeline.</div>';
                els.previewInfo.textContent = 'Nenhum clipe selecionado';
                return;
            }

            els.previewInfo.textContent = clip.name;
            const filters = [
    inspectorFilter(clip),
    inspectorEffectFilter(clip.settings || {})
].filter(Boolean).join(' ');

const common =
    `opacity:${clip.settings.opacity / 100};` +
    `transform:${inspectorTransform(clip)};` +
    `transform-origin:${inspectorTransformOrigin(clip)};` +
    `object-fit:${inspectorObjectFit(clip)};` +
    `border-radius:${Number(clip.settings.radius || 0)}px;` +
    `filter:${filters}`;

            if (clip.type === 'video') {
                els.preview.innerHTML = `<video class="ev-preview-media" src="${clip.url}" playsinline style="${common}"></video>`;
                this.mediaEl = els.preview.querySelector('video');
            } else if (clip.type === 'image') {
                els.preview.innerHTML = `<img class="ev-preview-media" src="${clip.url}" style="${common}">`;
                this.mediaEl = els.preview.querySelector('img');
            } else if (clip.type === 'audio') {
                els.preview.innerHTML = `<audio class="ev-preview-media" src="${clip.url}"></audio><div class="ev-preview-empty">Preview de áudio: ${escapeHtml(clip.name)}</div>`;
                this.mediaEl = els.preview.querySelector('audio');
            } else {
                els.preview.innerHTML = `<div class="ev-preview-text" style="font-size:42px;font-weight:900;${common}">${escapeHtml(clip.name)}</div>`;
            }

            els.preview.insertAdjacentHTML('beforeend', inspectorExtraOverlay(clip));
        },

        syncMedia(clip) {
            if (!clip || !this.mediaEl) return;

            const localTime = Math.max(0, state.currentTime - clip.start);

            if (this.mediaEl.tagName === 'VIDEO' || this.mediaEl.tagName === 'AUDIO') {
                const volume = Number(clip.settings.volume || 100);
                const gain = Number(clip.settings.gain || 100);
                const fadeIn = Math.max(0, Number(clip.settings.fadeIn || 0));
                const fadeOut = Math.max(0, Number(clip.settings.fadeOut || 0));
                const balance = Math.max(-100, Math.min(100, Number(clip.settings.balance || 0)));

                let fadeFactor = 1;
                if (fadeIn > 0 && localTime < fadeIn) fadeFactor = Math.min(fadeFactor, localTime / fadeIn);

                const clipDuration = Number(clip.duration || 0);
                const remaining = clipDuration - localTime;
                if (fadeOut > 0 && remaining < fadeOut) fadeFactor = Math.min(fadeFactor, Math.max(0, remaining / fadeOut));

                const finalVolume = clip.settings.mute ? 0 : Math.min(1, Math.max(0, (volume * gain * fadeFactor) / 10000));

                this.mediaEl.muted = false;
                this.mediaEl.volume = finalVolume;
                this.mediaEl.playbackRate = Math.max(0.25, Number(clip.settings.speed || 1));

                const graph = this.ensureAudioGraph(this.mediaEl);
                if (graph) {
                    graph.pan.pan.value = balance / 100;
                    graph.gain.gain.value = 1;
                    if (state.playing && this.audioCtx && this.audioCtx.state === 'suspended') {
                        this.audioCtx.resume().catch(() => {});
                    }
                }

                if (Number.isFinite(this.mediaEl.duration) && Math.abs((this.mediaEl.currentTime || 0) - localTime) > 0.28) {
                    try { this.mediaEl.currentTime = Math.min(localTime, this.mediaEl.duration || localTime); } catch (e) {}
                }

                if (state.playing) {
                    const promise = this.mediaEl.play();
                    if (promise && promise.catch) promise.catch(() => {});
                } else {
                    this.mediaEl.pause();
                }
            }
        },

        update() {
            if (!els.preview || !els.previewInfo) return;

            const clip = this.findActiveClip();

            if (!clip) {
                if (this.activeClipId !== null) this.build(null);
                return;
            }

            if (this.activeClipId !== clip.id) {
                this.build(clip);
            } else {
                const media = els.preview.querySelector('.ev-preview-media, .ev-preview-text');
                if (media) {
                    media.style.opacity = clip.settings.opacity / 100;
                    media.style.transform = inspectorTransform(clip);
                    media.style.transformOrigin = inspectorTransformOrigin(clip);
                    media.style.objectFit = inspectorObjectFit(clip);
                    media.style.borderRadius = `${Number(clip.settings.radius || 0)}px`;
                    media.style.filter =
    [
        inspectorFilter(clip),
        inspectorEffectFilter(clip.settings || {})
    ].filter(Boolean).join(' ');
                    els.preview.querySelectorAll('.ev-preview-vignette,.ev-preview-grain').forEach(node => node.remove());
                    els.preview.insertAdjacentHTML('beforeend', inspectorExtraOverlay(clip));
                }
                els.previewInfo.textContent = clip.name;
            }

            this.syncMedia(clip);
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
            els.assetList?.querySelector('.ev-empty')?.remove();

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
            const files = Array.from(els.mediaInput?.files || []);
            if (!files.length) return alert('Escolha um ou mais arquivos primeiro.');

            const form = new FormData();
            files.forEach(file => form.append('media[]', file));
            els.uploadStatus.textContent = files.length === 1 ? 'Enviando mídia...' : `Enviando ${files.length} mídias...`;

            try {
                const result = await api(routes.upload, { method: 'POST', body: form });
                const uploaded = Array.isArray(result.assets) && result.assets.length ? result.assets : (result.asset ? [result.asset] : []);

                uploaded.forEach(a => {
                    const asset = {
                        id: a.id,
                        name: a.original_name || a.name,
                        original_name: a.original_name || a.name,
                        type: a.media_type || a.type,
                        media_type: a.media_type || a.type,
                        url: a.public_url || a.stream_url || a.url,
                        duration: a.duration_seconds || a.duration || 6,
                        extension: a.extension
                    };

                    state.assets.unshift(asset);
                    this.addCard(asset);
                });

                els.mediaInput.value = '';
                els.uploadStatus.textContent = result.message || `${uploaded.length} mídia(s) importada(s) com sucesso.`;
            } catch (error) {
                els.uploadStatus.textContent = error.message || 'Erro ao importar mídia.';
            }
        }
    };

    const PlaybackManager = {
        tick() {
            if (!state.playing) return;
            state.currentTime = Math.round((state.currentTime + 0.1) * 10) / 10;
            if (state.currentTime > TimelineManager.duration()) state.currentTime = 0;
            TimelineManager.updatePlayhead();
            PreviewManager.update();
        },

        playPause() {
            state.playing = !state.playing;
            if (els.btnPlay) els.btnPlay.textContent = state.playing ? '⏸ Pause' : '▶ Play';

            if (state.playing) {
                PreviewManager.update();
                state.playTimer = setInterval(() => this.tick(), 100);
            } else {
                clearInterval(state.playTimer);
                PreviewManager.update();
            }
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
                        project_id: activeProjectId,
                        name: boot.project?.name || 'Projeto EditorVideoIA',
                        duration_seconds: Math.ceil(TimelineManager.duration()),
                        settings: { fase: '6.5', bloco: '1', saved_at: new Date().toISOString() },
                        timeline_data: state.timeline
                    })
                }).then(result => {
                    if (result.project) boot.project = result.project;
                    return result;
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

                if (event.key === 'Escape') {
                    event.preventDefault();
                    TimelineManager.clearSelection();
                    EditorEngine.renderAll();
                    setMsg('Seleção limpa.');
                }

                if (event.key === 'Delete') TimelineManager.deleteSelected();
                                if (event.key.toLowerCase() === 'k' && !event.shiftKey) {
                    event.preventDefault();
                    KeyframeManager.add();
                }

                if (event.key.toLowerCase() === 'k' && event.shiftKey) {
                    event.preventDefault();
                    KeyframeManager.removeNearest();
                }

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

  const BatchManager = {
    async status() {
        if (!routes.batchStatus) return;

        try {
            const result = await api(routes.batchStatus, { method: 'GET' });
            this.render(result.timeline || {});
        } catch (e) {}
    },

    async create() {
        const videos = (state.assets || [])
            .filter(asset => asset.type === 'video' || asset.media_type === 'video')
            .slice(0, 100);

        if (!videos.length) {
            setMsg('Nenhum vídeo encontrado para criar fila.');
            return;
        }

        const jobs = videos.map(asset => ({
            asset_id: asset.id,
            name: asset.name || asset.original_name,
            type: 'video',
            url: asset.url || asset.public_url,
            stream_url: asset.url || asset.public_url
        }));

        const result = await api(routes.batchCreate, {
            method: 'POST',
            body: JSON.stringify({ jobs })
        });

        this.render(result.timeline || {});
        setMsg('Fila criada com ' + jobs.length + ' vídeo(s).');
    },

    running: false,

    async start() {
        if (this.running) return;

        this.running = true;
        setMsg('Processamento iniciado. A fila continuara automaticamente.');

        try {
            while (this.running) {
                const result = await api(routes.batchProcess, { method: 'POST' });
                const timeline = result.timeline || {};
                this.render(timeline);

                if (result.paused || timeline.batch_queue?.paused) {
                    setMsg('Fila pausada apos concluir o video atual.');
                    break;
                }

                if (result.completed || timeline.batch_queue?.completed) {
                    setMsg('Fila processada ate o fim.');
                    break;
                }

                setMsg(result.message || 'Continuando para o proximo video...');
                await new Promise(resolve => setTimeout(resolve, 500));
            }
        } catch (error) {
            setMsg('O processamento foi interrompido: ' + (error.message || error));
        } finally {
            this.running = false;
            await this.status();
        }
    },

    async pause() {
        this.running = false;
        await api(routes.batchPause, { method: 'POST' });
        await this.status();
        setMsg('Fila pausada.');
    },

    async resume() {
        await api(routes.batchResume, { method: 'POST' });
        await this.start();
        setMsg('Fila retomada.');
    },

    async cancel() {
        this.running = false;
        await api(routes.batchCancel, { method: 'POST' });
        await this.status();
        setMsg('Fila cancelada.');
    },

    render(timeline) {
        const jobs = timeline.batch_jobs || [];
        const queue = timeline.batch_queue || {};
        const total = jobs.length || queue.total || 0;
        const waiting = queue.waiting ?? jobs.filter(j => j.status === 'aguardando').length;
        const processing = queue.processing ?? jobs.filter(j => j.status === 'processando').length;
        const finished = queue.finished ?? jobs.filter(j => j.status === 'concluido').length;
        const failed = queue.failed ?? jobs.filter(j => j.render_status === 'erro').length;
        const percent = total ? Math.round((finished / total) * 100) : 0;

        if (els.batchSummary) els.batchSummary.textContent = total + ' vídeo(s) na fila • ' + percent + '%';
        if (els.batchProgressBar) els.batchProgressBar.style.width = percent + '%';
        if (els.batchWaiting) els.batchWaiting.textContent = waiting;
        if (els.batchProcessing) els.batchProcessing.textContent = processing;
        if (els.batchFinished) els.batchFinished.textContent = finished;
        if (els.batchFailed) els.batchFailed.textContent = failed;

        if (els.batchList) {
            els.batchList.innerHTML = jobs.slice(0, 100).map(job => `
                <div class="ev-batch-job">
                    <span>${escapeHtml(job.name || 'Vídeo')}</span>
                    <span>${escapeHtml(job.status || 'aguardando')} • ${Number(job.progress || 0)}%</span>
                </div>
            `).join('');
        }
    },

    bind() {
        els.btnBatchCreate?.addEventListener('click', () => this.create());
        els.btnBatchStart?.addEventListener('click', () => this.start());
        els.btnBatchPause?.addEventListener('click', () => this.pause());
        els.btnBatchResume?.addEventListener('click', () => this.resume());
        els.btnBatchCancel?.addEventListener('click', () => this.cancel());

        this.status();
        setInterval(() => this.status(), 3000);
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

            els.fields.btnResetInspector?.addEventListener('click', () => InspectorManager.resetSelected());
            els.fields.btnResetTransform?.addEventListener('click', () => InspectorManager.resetTransformSelected());
            els.inspectorForm?.addEventListener('submit', event => InspectorManager.apply(event));
            Object.values(els.fields).forEach(field => {
                field?.addEventListener('input', () => {
                    if (field === els.fields.clipScaleX && els.fields.clipLockRatio?.checked) els.fields.clipScaleY.value = field.value;
                    if (field === els.fields.clipScaleY && els.fields.clipLockRatio?.checked) els.fields.clipScaleX.value = field.value;
                    InspectorManager.liveApply();
                });
                field?.addEventListener('change', () => InspectorManager.liveApply());
            });
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
                        BatchManager.bind();
            this.renderAll();
            setMsg('Fase 6.5 Bloco 1 ativa: timeline, preview, play/pause, seleção múltipla e salvamento estabilizados.');
        }
    };

    EditorEngine.init();
})();
