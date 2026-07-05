(function(){
    const state = { clips: [], selectedClipId: null, zoom: 1 };
    const els = {
        mediaInput: document.getElementById('mediaInput'), mediaList: document.getElementById('mediaList'), mediaSearch: document.getElementById('mediaSearch'),
        timelineTrack: document.getElementById('timelineTrack'), previewBox: document.getElementById('previewBox'), saveBtn: document.getElementById('saveProjectBtn'),
        clipName: document.getElementById('clipName'), clipDuration: document.getElementById('clipDuration'), clipStart: document.getElementById('clipStart'), clipTransition: document.getElementById('clipTransition'),
        applyBtn: document.getElementById('applyInspectorBtn'), removeBtn: document.getElementById('removeClipBtn'), status: document.getElementById('statusText'), zoomIn: document.getElementById('zoomInBtn'), zoomOut: document.getElementById('zoomOutBtn'), ruler: document.getElementById('timeRuler')
    };

    function init(){
        const saved = window.EditorVideoIA.initialProject && window.EditorVideoIA.initialProject.timeline_data;
        if(saved && Array.isArray(saved.clips)) state.clips = saved.clips;
        bindMediaCards(); bindEvents(); renderTimeline(); renderRuler();
    }
    function bindEvents(){
        els.mediaInput.addEventListener('change', uploadMedia);
        els.timelineTrack.addEventListener('dragover', e => { e.preventDefault(); els.timelineTrack.classList.add('drag'); });
        els.timelineTrack.addEventListener('drop', onDropToTimeline);
        els.saveBtn.addEventListener('click', saveProject);
        els.applyBtn.addEventListener('click', applyInspector);
        els.removeBtn.addEventListener('click', removeSelectedClip);
        els.zoomIn.addEventListener('click', () => { state.zoom = Math.min(3, state.zoom + .25); renderTimeline(); renderRuler(); });
        els.zoomOut.addEventListener('click', () => { state.zoom = Math.max(.5, state.zoom - .25); renderTimeline(); renderRuler(); });
        els.mediaSearch.addEventListener('input', () => {
            const q = els.mediaSearch.value.toLowerCase();
            document.querySelectorAll('.media-card').forEach(card => card.style.display = card.innerText.toLowerCase().includes(q) ? 'grid' : 'none');
        });
    }
    function bindMediaCards(){
        document.querySelectorAll('.media-card').forEach(card => {
            card.addEventListener('dragstart', e => e.dataTransfer.setData('application/json', card.dataset.asset));
            card.addEventListener('dblclick', () => addClip(JSON.parse(card.dataset.asset)));
        });
    }
    async function uploadMedia(){
        const file = els.mediaInput.files[0]; if(!file) return;
        setStatus('Enviando mídia...');
        const form = new FormData(); form.append('media', file);
        const res = await fetch(window.EditorVideoIA.uploadUrl, { method:'POST', headers:{'X-CSRF-TOKEN': window.EditorVideoIA.csrf}, body: form });
        if(!res.ok){ setStatus('Erro no upload. Verifique tamanho/formato.'); return; }
        const data = await res.json(); appendMediaCard(data.asset); els.mediaInput.value=''; setStatus('Mídia adicionada.');
    }
    function appendMediaCard(asset){
        const card = document.createElement('div'); card.className='media-card'; card.draggable=true; card.dataset.asset=JSON.stringify(asset);
        const thumb = asset.media_type === 'image' ? `<img src="${asset.public_url}">` : `<span>${asset.media_type.toUpperCase()}</span>`;
        card.innerHTML = `<div class="media-thumb ${asset.media_type}">${thumb}</div><div><strong>${asset.original_name}</strong><small>${(asset.extension||'').toUpperCase()} • ${(asset.size_bytes/1024/1024).toFixed(2)} MB</small></div>`;
        els.mediaList.prepend(card); bindMediaCards();
    }
    function onDropToTimeline(e){ e.preventDefault(); const raw=e.dataTransfer.getData('application/json'); if(raw) addClip(JSON.parse(raw)); }
    function addClip(asset){
        const clip = { id: 'clip_'+Date.now(), asset_id: asset.id, name: asset.original_name, media_type: asset.media_type, url: asset.public_url, start: state.clips.length * 5, duration: asset.media_type === 'image' ? 5 : 10, transition:'none' };
        state.clips.push(clip); state.selectedClipId=clip.id; renderTimeline(); loadInspector(clip); previewClip(clip); setStatus('Clipe adicionado na timeline.');
    }
    function renderTimeline(){
        els.timelineTrack.innerHTML = '';
        if(state.clips.length === 0){ els.timelineTrack.innerHTML='<span class="drop-help">Arraste mídias da biblioteca para cá</span>'; return; }
        state.clips.sort((a,b)=>a.start-b.start).forEach(clip => {
            const el = document.createElement('div'); el.className='clip'+(clip.id===state.selectedClipId?' selected':''); el.style.width = Math.max(110, clip.duration*28*state.zoom)+'px'; el.textContent = clip.name;
            el.onclick = () => { state.selectedClipId=clip.id; renderTimeline(); loadInspector(clip); previewClip(clip); };
            els.timelineTrack.appendChild(el);
        });
    }
    function renderRuler(){ els.ruler.innerHTML = Array.from({length:13},(_,i)=>`<span style="display:inline-block;width:${80*state.zoom}px">${i*5}s</span>`).join(''); }
    function selectedClip(){ return state.clips.find(c=>c.id===state.selectedClipId); }
    function loadInspector(clip){ els.clipName.value=clip.name; els.clipDuration.value=clip.duration; els.clipStart.value=clip.start; els.clipTransition.value=clip.transition || 'none'; }
    function applyInspector(){ const clip=selectedClip(); if(!clip) return setStatus('Selecione um clipe.'); clip.name=els.clipName.value; clip.duration=Number(els.clipDuration.value||5); clip.start=Number(els.clipStart.value||0); clip.transition=els.clipTransition.value; renderTimeline(); setStatus('Inspector aplicado.'); }
    function removeSelectedClip(){ const clip=selectedClip(); if(!clip) return; state.clips=state.clips.filter(c=>c.id!==clip.id); state.selectedClipId=null; renderTimeline(); setStatus('Clipe removido.'); }
    function previewClip(clip){
        if(clip.media_type==='image') els.previewBox.innerHTML = `<img src="${clip.url}">`;
        else if(clip.media_type==='video') els.previewBox.innerHTML = `<video src="${clip.url}" controls></video>`;
        else if(clip.media_type==='audio') els.previewBox.innerHTML = `<audio src="${clip.url}" controls></audio>`;
        else els.previewBox.innerHTML = '<span>Pré-visualização do vídeo</span>';
    }
    async function saveProject(){
        setStatus('Salvando projeto...');
        const duration = state.clips.reduce((max,c)=>Math.max(max, Number(c.start)+Number(c.duration)),0);
        const res = await fetch(window.EditorVideoIA.saveUrl, { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':window.EditorVideoIA.csrf}, body: JSON.stringify({name:'Projeto de teste', duration_seconds: duration, timeline_data:{clips:state.clips}}) });
        setStatus(res.ok ? 'Projeto salvo com sucesso.' : 'Erro ao salvar.');
    }
    function setStatus(msg){ els.status.textContent=msg; }
    init();
})();
