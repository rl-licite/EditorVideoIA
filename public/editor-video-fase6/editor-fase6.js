(() => {
    const boot = window.EditorVideoIAFase6 || {};
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const routes = boot.routes || {};
    const baseTracks = [
        {id:'video_1', name:'Vídeo 1', type:'video'},
        {id:'video_2', name:'Vídeo 2', type:'video'},
        {id:'text_1', name:'Texto / Legenda', type:'text'},
        {id:'audio_1', name:'Áudio 1', type:'audio'},
        {id:'audio_2', name:'Áudio 2', type:'audio'}
    ];

    let timeline = normalizeTimeline(boot.timeline || {});
    let assets = Array.isArray(boot.assets) ? boot.assets : [];
    let selectedIds = [];
    let clipboard = [];
    let tool = 'select';
    let pxPerSecond = 48;
    let currentTime = 0;
    let playing = false;
    let playTimer = null;
    let history = [];
    let future = [];

    const els = {
        tracks: document.getElementById('tracks'), ruler: document.getElementById('timeRuler'), playhead: document.getElementById('playhead'),
        preview: document.getElementById('previewCanvas'), previewInfo: document.getElementById('previewInfo'), timeReadout: document.getElementById('timeReadout'),
        msg: document.getElementById('systemMessage'), save: document.getElementById('btnSaveProject'), upload: document.getElementById('btnUpload'), mediaInput: document.getElementById('mediaInput'), uploadStatus: document.getElementById('uploadStatus'),
        assetList: document.getElementById('assetList'), assetSearch: document.getElementById('assetSearch'), assetFilter: document.getElementById('assetFilter'),
        inspectorEmpty: document.getElementById('inspectorEmpty'), inspectorForm: document.getElementById('inspectorForm'), selectedCounter: document.getElementById('selectedCounter')
    };

    function normalizeTimeline(raw){
        const t = raw && typeof raw === 'object' ? raw : {};
        return {
            clips: Array.isArray(t.clips) ? t.clips.map(normalizeClip) : [],
            tracks: Array.isArray(t.tracks) && t.tracks.length ? t.tracks : baseTracks,
            overlays: t.overlays || {},
            canvas: t.canvas || {background:'#111827'},
            batch_jobs: Array.isArray(t.batch_jobs) ? t.batch_jobs : [],
            export_jobs: Array.isArray(t.export_jobs) ? t.export_jobs : [],
            export_settings: t.export_settings || {resolution:'1920x1080', fps:30, quality:'alta', format:'mp4', bitrate:'12M'},
            meta: {...(t.meta || {}), version:'6.1-motor-profissional'}
        };
    }
    function normalizeClip(c){
        return {
            id: c.id || uid('clip'), asset_id: c.asset_id || c.media_id || null, track: c.track || defaultTrack(c.type || c.media_type),
            name: c.name || c.original_name || 'Clipe', type: c.type || c.media_type || 'video', url: c.url || c.public_url || c.stream_url || '',
            start: Number(c.start ?? c.start_time ?? 0), duration: Math.max(.2, Number(c.duration ?? 6)),
            settings: {volume:100, speed:1, opacity:100, scale:100, x:0, y:0, rotation:0, ...(c.settings || {})}
        };
    }
    function uid(prefix){ return prefix + '_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2,8); }
    function defaultTrack(type){ if(type === 'audio') return 'audio_1'; if(type === 'text') return 'text_1'; return 'video_1'; }
    function fmt(s){ s = Math.max(0, Number(s)||0); const m = Math.floor(s/60); const sec = (s%60).toFixed(1).padStart(4,'0'); return String(m).padStart(2,'0') + ':' + sec; }
    function duration(){ return Math.max(60, ...timeline.clips.map(c => c.start + c.duration + 5)); }
    function pushHistory(){ history.push(JSON.stringify(timeline)); if(history.length>60) history.shift(); future = []; }
    function restore(json){ timeline = normalizeTimeline(JSON.parse(json)); selectedIds = []; renderAll(); }
    function setMsg(text){ els.msg.textContent = text; }
    async function api(url, options={}){
        const res = await fetch(url, { ...options, headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json',...(options.headers||{})} });
        const json = await res.json().catch(()=>({}));
        if(!res.ok) throw json; return json;
    }

    function renderRuler(){
        els.ruler.innerHTML = '';
        const total = duration();
        els.ruler.style.width = (total * pxPerSecond) + 'px';
        for(let i=0;i<=total;i+=5){ const tick=document.createElement('div'); tick.className='tick'; tick.style.left=(i*pxPerSecond)+'px'; tick.textContent=fmt(i); els.ruler.appendChild(tick); }
        updatePlayhead();
    }
    function renderTracks(){
        els.tracks.innerHTML = '';
        timeline.tracks.forEach(track => {
            const row = document.createElement('div'); row.className='ev-track'; row.dataset.track=track.id;
            row.innerHTML = `<div class="ev-track-head"><strong>${track.name}</strong><small>${track.type}</small></div><div class="ev-lane" data-track="${track.id}"></div>`;
            els.tracks.appendChild(row);
        });
        document.querySelectorAll('.ev-lane').forEach(bindLane);
        timeline.clips.forEach(renderClip);
    }
    function renderClip(clip){
        const lane = document.querySelector(`.ev-lane[data-track="${clip.track}"]`); if(!lane) return;
        const el = document.createElement('div'); el.className = `ev-clip ${clip.type || 'video'} ${selectedIds.includes(clip.id)?'selected':''}`;
        el.dataset.id = clip.id; el.style.left = (clip.start*pxPerSecond)+'px'; el.style.width = Math.max(44, clip.duration*pxPerSecond)+'px';
        el.innerHTML = `<span class="handle left"></span><span>${escapeHtml(clip.name)}</span><span class="handle right"></span>`;
        bindClip(el, clip); lane.appendChild(el);
    }
    function renderAll(){ renderRuler(); renderTracks(); updateInspector(); updatePreview(); }
    function updatePlayhead(){ els.playhead.style.left = (140 + currentTime*pxPerSecond) + 'px'; els.timeReadout.textContent = fmt(currentTime); }
    function escapeHtml(v){ return String(v||'').replace(/[&<>"]/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[s])); }

    function bindLane(lane){
        lane.addEventListener('dragover', e => { e.preventDefault(); lane.classList.add('dragover'); });
        lane.addEventListener('dragleave', () => lane.classList.remove('dragover'));
        lane.addEventListener('drop', e => { e.preventDefault(); lane.classList.remove('dragover'); const raw=e.dataTransfer.getData('application/json'); if(!raw) return; addAssetToLane(JSON.parse(raw), lane, e); });
        lane.addEventListener('click', e => { if(e.target === lane){ currentTime = Math.max(0, (e.offsetX/pxPerSecond)); updatePlayhead(); }});
    }
    function addAssetToLane(asset, lane, e){
        pushHistory();
        const rect = lane.getBoundingClientRect(); let start = Math.max(0, (e.clientX - rect.left)/pxPerSecond); if(document.getElementById('snapToggle').checked) start = Math.round(start*2)/2;
        const type = asset.media_type || asset.type || lane.closest('.ev-track')?.querySelector('small')?.textContent || 'video';
        const clip = normalizeClip({id:uid('clip'), asset_id:asset.id, track:lane.dataset.track, name:asset.name || asset.original_name, type, url:asset.url || asset.public_url, start, duration:Number(asset.duration || 6)});
        timeline.clips.push(clip); selectedIds = [clip.id]; renderAll(); setMsg('Clipe adicionado na timeline.');
    }
    function bindClip(el, clip){
        el.addEventListener('click', e => {
            e.stopPropagation();
            if(tool === 'razor'){ cutClipAtTime(clip.id, currentTime); return; }
            if(e.ctrlKey || e.metaKey){ selectedIds = selectedIds.includes(clip.id) ? selectedIds.filter(id=>id!==clip.id) : [...selectedIds, clip.id]; }
            else selectedIds = [clip.id];
            renderAll();
        });
        el.addEventListener('mousedown', e => {
            if(tool !== 'select' && tool !== 'hand') return; const side = e.target.classList.contains('left') ? 'left' : e.target.classList.contains('right') ? 'right' : 'move';
            e.preventDefault(); e.stopPropagation(); pushHistory(); const sx=e.clientX; const original={start:clip.start,duration:clip.duration};
            const move = ev => { const delta=(ev.clientX-sx)/pxPerSecond; if(side==='move') clip.start=Math.max(0, snap(original.start+delta)); if(side==='left'){ const ns=Math.max(0, snap(original.start+delta)); const end=original.start+original.duration; clip.start=Math.min(ns,end-.2); clip.duration=end-clip.start; } if(side==='right') clip.duration=Math.max(.2, snap(original.duration+delta)); renderAll(); };
            const up = () => { document.removeEventListener('mousemove',move); document.removeEventListener('mouseup',up); };
            document.addEventListener('mousemove',move); document.addEventListener('mouseup',up);
        });
    }
    function snap(v){ return document.getElementById('snapToggle').checked ? Math.round(v*2)/2 : v; }

    function updateInspector(){
        els.selectedCounter.textContent = selectedIds.length;
        const clip = timeline.clips.find(c=>c.id===selectedIds[0]);
        if(!clip){ els.inspectorEmpty.classList.remove('hidden'); els.inspectorForm.classList.add('hidden'); return; }
        els.inspectorEmpty.classList.add('hidden'); els.inspectorForm.classList.remove('hidden');
        clipName.value=clip.name; clipStart.value=clip.start; clipDuration.value=clip.duration; clipVolume.value=clip.settings.volume; clipSpeed.value=clip.settings.speed; clipOpacity.value=clip.settings.opacity; clipScale.value=clip.settings.scale; clipX.value=clip.settings.x; clipY.value=clip.settings.y; clipRotation.value=clip.settings.rotation;
    }
    function applyInspector(e){
        e.preventDefault(); const clip = timeline.clips.find(c=>c.id===selectedIds[0]); if(!clip) return; pushHistory();
        clip.name=clipName.value; clip.start=Number(clipStart.value||0); clip.duration=Math.max(.2, Number(clipDuration.value||1));
        clip.settings={...clip.settings, volume:Number(clipVolume.value||100), speed:Number(clipSpeed.value||1), opacity:Number(clipOpacity.value||100), scale:Number(clipScale.value||100), x:Number(clipX.value||0), y:Number(clipY.value||0), rotation:Number(clipRotation.value||0)};
        renderAll(); setMsg('Inspector aplicado no clipe selecionado.');
    }

    function updatePreview(){
        const clip = timeline.clips.find(c=>c.id===selectedIds[0]) || timeline.clips.find(c=>currentTime>=c.start && currentTime<=c.start+c.duration);
        if(!clip){ els.preview.innerHTML='<div class="ev-preview-empty">Arraste uma mídia para a timeline.</div>'; els.previewInfo.textContent='Nenhum clipe selecionado'; return; }
        els.previewInfo.textContent = clip.name;
        const transform = `translate(${clip.settings.x}px,${clip.settings.y}px) scale(${clip.settings.scale/100}) rotate(${clip.settings.rotation}deg)`;
        if(clip.type==='video'){ els.preview.innerHTML=`<video src="${clip.url}" controls style="opacity:${clip.settings.opacity/100};transform:${transform}"></video>`; }
        else if(clip.type==='image'){ els.preview.innerHTML=`<img src="${clip.url}" style="opacity:${clip.settings.opacity/100};transform:${transform}">`; }
        else if(clip.type==='audio'){ els.preview.innerHTML=`<audio src="${clip.url}" controls></audio><div class="ev-preview-empty">Preview de áudio</div>`; }
        else { els.preview.innerHTML=`<div style="font-size:42px;font-weight:900;opacity:${clip.settings.opacity/100};transform:${transform}">${escapeHtml(clip.name)}</div>`; }
    }

    function deleteSelected(){ if(!selectedIds.length) return; pushHistory(); timeline.clips = timeline.clips.filter(c=>!selectedIds.includes(c.id)); selectedIds=[]; renderAll(); setMsg('Clipe removido.'); }
    function duplicateSelected(){ const copies = timeline.clips.filter(c=>selectedIds.includes(c.id)).map(c=>({...JSON.parse(JSON.stringify(c)), id:uid('clip'), start:c.start+c.duration+.2, name:c.name+' cópia'})); if(!copies.length) return; pushHistory(); timeline.clips.push(...copies); selectedIds=copies.map(c=>c.id); renderAll(); setMsg('Clipe duplicado.'); }
    function copySelected(){ clipboard = timeline.clips.filter(c=>selectedIds.includes(c.id)).map(c=>JSON.parse(JSON.stringify(c))); setMsg('Clipe copiado.'); }
    function pasteClipboard(){ if(!clipboard.length) return; pushHistory(); const copies=clipboard.map(c=>({...c,id:uid('clip'),start:currentTime,name:c.name+' cópia'})); timeline.clips.push(...copies); selectedIds=copies.map(c=>c.id); renderAll(); setMsg('Clipe colado no playhead.'); }
    function cutClipAtTime(id, time){ const clip=timeline.clips.find(c=>c.id===id); if(!clip || time<=clip.start+.2 || time>=clip.start+clip.duration-.2) return; pushHistory(); const right={...JSON.parse(JSON.stringify(clip)), id:uid('clip'), start:time, duration:(clip.start+clip.duration)-time, name:clip.name+' parte 2'}; clip.duration=time-clip.start; timeline.clips.push(right); selectedIds=[right.id]; renderAll(); setMsg('Clipe cortado com Razor.'); }
    function undo(){ if(!history.length) return; future.push(JSON.stringify(timeline)); restore(history.pop()); setMsg('Desfeito.'); }
    function redo(){ if(!future.length) return; history.push(JSON.stringify(timeline)); restore(future.pop()); setMsg('Refeito.'); }
    function setTool(next){ tool=next; document.querySelectorAll('.tool').forEach(b=>b.classList.toggle('active', b.dataset.tool===tool)); setMsg('Ferramenta ativa: '+next); }
    function setZoom(v){ pxPerSecond=Math.min(140, Math.max(18, v)); renderAll(); }

    async function saveProject(){
        try{ await api(routes.save, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({name: boot.project?.name || 'Projeto EditorVideoIA', duration_seconds: Math.ceil(duration()), settings:{fase:'6.1', saved_at:new Date().toISOString()}, timeline_data: timeline})}); setMsg('Projeto salvo com sucesso.'); }
        catch(e){ alert(e.message || 'Erro ao salvar projeto.'); }
    }
    async function uploadMedia(){
        const file = els.mediaInput.files?.[0]; if(!file) return alert('Escolha um arquivo primeiro.');
        const form = new FormData(); form.append('media', file); els.uploadStatus.textContent='Enviando...';
        try{ const result = await api(routes.upload, {method:'POST', body:form}); const a=result.asset; const asset={id:a.id,name:a.original_name,original_name:a.original_name,type:a.media_type,media_type:a.media_type,url:a.public_url || a.stream_url,duration:a.duration_seconds || 6,extension:a.extension}; assets.unshift(asset); addAssetCard(asset); els.mediaInput.value=''; els.uploadStatus.textContent='Mídia importada com sucesso.'; }
        catch(e){ els.uploadStatus.textContent=e.message || 'Erro ao importar mídia.'; }
    }
    function addAssetCard(asset){
        document.querySelector('.ev-empty')?.remove(); const card=document.createElement('div'); card.className='ev-asset'; card.draggable=true; card.dataset.id=asset.id; card.dataset.name=asset.name; card.dataset.type=asset.type; card.dataset.url=asset.url; card.dataset.duration=asset.duration || 6;
        card.innerHTML=`<div class="ev-thumb ${asset.type}"><span>${String(asset.type).toUpperCase()}</span></div><div><strong>${escapeHtml(asset.name)}</strong><small>${asset.type} • ${asset.extension||''}</small></div>`; bindAsset(card); els.assetList.prepend(card); document.getElementById('assetCounter').textContent=document.querySelectorAll('.ev-asset').length;
    }
    function bindAsset(card){ card.addEventListener('dragstart', e => e.dataTransfer.setData('application/json', JSON.stringify({id:card.dataset.id,name:card.dataset.name,type:card.dataset.type,media_type:card.dataset.type,url:card.dataset.url,duration:card.dataset.duration}))); }
    function filterAssets(){ const q=els.assetSearch.value.toLowerCase(); const t=els.assetFilter.value; document.querySelectorAll('.ev-asset').forEach(card=>card.classList.toggle('hide', !(card.dataset.name.toLowerCase().includes(q) && (t==='all'||card.dataset.type===t)))); }
    function tick(){ if(!playing) return; currentTime += .1; if(currentTime > duration()) currentTime=0; updatePlayhead(); updatePreview(); }
    function playPause(){ playing=!playing; document.getElementById('btnPlay').textContent = playing ? '⏸ Pause' : '▶ Play'; if(playing) playTimer=setInterval(tick,100); else clearInterval(playTimer); }
    function stop(){ playing=false; clearInterval(playTimer); currentTime=0; document.getElementById('btnPlay').textContent='▶ Play'; updatePlayhead(); updatePreview(); }

    document.querySelectorAll('.ev-asset').forEach(bindAsset);
    document.querySelectorAll('.tool').forEach(b=>b.addEventListener('click',()=>setTool(b.dataset.tool)));
    els.inspectorForm.addEventListener('submit', applyInspector); els.save.addEventListener('click', saveProject); els.upload.addEventListener('click', uploadMedia);
    els.assetSearch.addEventListener('input', filterAssets); els.assetFilter.addEventListener('change', filterAssets);
    btnDeleteClip.addEventListener('click', deleteSelected); btnDuplicate.addEventListener('click', duplicateSelected); btnUndo.addEventListener('click', undo); btnRedo.addEventListener('click', redo); btnCutSelected.addEventListener('click',()=>selectedIds[0]&&cutClipAtTime(selectedIds[0], currentTime));
    btnZoomIn.addEventListener('click',()=>setZoom(pxPerSecond+10)); btnZoomOut.addEventListener('click',()=>setZoom(pxPerSecond-10)); btnFit.addEventListener('click',()=>setZoom(48)); btnPlay.addEventListener('click',playPause); btnStop.addEventListener('click',stop);
    document.addEventListener('keydown', e => { if(e.target.matches('input,select,textarea')) return; if(e.code==='Space'){e.preventDefault();playPause();} if(e.key==='Delete') deleteSelected(); if(e.ctrlKey&&e.key.toLowerCase()==='z'){e.preventDefault();undo();} if(e.ctrlKey&&e.key.toLowerCase()==='y'){e.preventDefault();redo();} if(e.ctrlKey&&e.key.toLowerCase()==='c'){e.preventDefault();copySelected();} if(e.ctrlKey&&e.key.toLowerCase()==='v'){e.preventDefault();pasteClipboard();} if(e.ctrlKey&&e.key.toLowerCase()==='d'){e.preventDefault();duplicateSelected();} });
    document.addEventListener('wheel', e => { if(e.shiftKey){ e.preventDefault(); setZoom(pxPerSecond + (e.deltaY<0?8:-8)); } }, {passive:false});
    renderAll();
})();
