<x-app-layout>
<x-slot name="header"><h2 style="font-size:24px;font-weight:800;">Editor Visual — Entrega 6.1</h2></x-slot>
@php
$layout = $template?->visual_layout ?? ['background'=>['type'=>'solid','color'=>'#111827'],'video'=>['x'=>120,'y'=>260,'width'=>840,'height'=>900,'rotation'=>0,'opacity'=>1,'borderRadius'=>28]];
$canvasWidth = $template?->canvas_width ?? 1080;
$canvasHeight = $template?->canvas_height ?? 1920;
@endphp
<div id="visualEditorApp" data-save-url="{{ route('visual-editor.save') }}" data-csrf="{{ csrf_token() }}" data-template-id="{{ $template?->id }}" data-layout='@json($layout)' data-canvas-width="{{ $canvasWidth }}" data-canvas-height="{{ $canvasHeight }}" style="background:#0f172a;min-height:calc(100vh - 80px);color:white;">
@if(session('success'))<div style="background:#dcfce7;color:#166534;padding:12px 18px;">{{ session('success') }}</div>@endif
<div style="display:grid;grid-template-columns:270px 1fr 320px;gap:0;min-height:calc(100vh - 80px);">
<aside style="background:#111827;border-right:1px solid #334155;padding:18px;">
<a href="{{ route('dashboard') }}" style="display:block;background:#2563eb;color:white;text-decoration:none;border-radius:10px;padding:10px 14px;margin-bottom:14px;">← Dashboard</a>
<h3 style="font-size:18px;font-weight:800;margin-bottom:12px;">Template</h3>
@if($templates->count() > 0)
<form method="GET" action="{{ route('visual-editor.index') }}">
<select name="template" onchange="this.form.submit()" style="width:100%;padding:10px;border-radius:8px;color:#111827;">
@foreach($templates as $item)<option value="{{ $item->id }}" @selected($template && $template->id === $item->id)>{{ $item->name }}</option>@endforeach
</select>
</form>
@else
<p style="color:#cbd5e1;margin-bottom:12px;">Nenhum template criado.</p>
@endif
<form method="POST" action="{{ route('visual-editor.quick-template') }}" style="margin-top:18px;background:#1f2937;padding:14px;border-radius:12px;">
@csrf
<label style="font-size:13px;color:#cbd5e1;">Novo template rápido</label>
<input name="name" value="Template Visual" style="width:100%;padding:9px;border-radius:8px;color:#111827;margin:6px 0 10px;">
<label style="font-size:13px;color:#cbd5e1;">Resolução</label>
<select name="resolution" style="width:100%;padding:9px;border-radius:8px;color:#111827;margin:6px 0 12px;">
<option value="1080x1920">Reels/Shorts 1080x1920</option><option value="1920x1080">YouTube 1920x1080</option><option value="1080x1080">Quadrado 1080x1080</option><option value="720x1280">Vertical leve 720x1280</option>
</select>
<button style="width:100%;background:#16a34a;color:white;border:none;border-radius:8px;padding:10px;cursor:pointer;">Criar</button>
</form>
<hr style="border-color:#334155;margin:18px 0;">
<h3 style="font-size:18px;font-weight:800;margin-bottom:12px;">Ferramentas</h3>
<button class="tool-btn" data-action="center">Centralizar vídeo</button>
<button class="tool-btn" data-action="fit">Ajustar à tela</button>
<button class="tool-btn" data-action="reset">Resetar layout</button>
<button class="tool-btn" data-action="save">Salvar layout</button>
</aside>
<main style="background:#020617;padding:22px;overflow:auto;">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
<div><h3 style="font-size:22px;font-weight:800;">Canvas de edição</h3><p style="color:#94a3b8;">Arraste o vídeo, ajuste posição, zoom e preview.</p></div>
<div style="display:flex;gap:10px;align-items:center;"><span style="color:#cbd5e1;">Zoom</span><input id="editorZoom" type="range" min="25" max="100" value="42"><span id="editorZoomLabel">42%</span></div>
</div>
<div style="height:calc(100vh - 190px);display:flex;align-items:flex-start;justify-content:center;overflow:auto;background:#0f172a;border-radius:16px;border:1px solid #334155;padding:30px;">
<div id="canvasStage" style="position:relative;width:{{ $canvasWidth }}px;height:{{ $canvasHeight }}px;background:{{ $layout['background']['color'] ?? '#111827' }};transform:scale(.42);transform-origin:top center;box-shadow:0 20px 70px rgba(0,0,0,.55);overflow:hidden;border-radius:10px;">
<div style="position:absolute;inset:70px;border:3px dashed rgba(255,255,255,.22);pointer-events:none;z-index:10;"></div>
<div style="position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.05) 1px,transparent 1px);background-size:60px 60px;pointer-events:none;z-index:2;"></div>
<div id="videoBox" style="position:absolute;left:{{ $layout['video']['x'] ?? 120 }}px;top:{{ $layout['video']['y'] ?? 260 }}px;width:{{ $layout['video']['width'] ?? 840 }}px;height:{{ $layout['video']['height'] ?? 900 }}px;background:#111827;border:4px solid rgba(255,255,255,.7);border-radius:{{ $layout['video']['borderRadius'] ?? 28 }}px;overflow:hidden;cursor:move;z-index:5;transform:rotate({{ $layout['video']['rotation'] ?? 0 }}deg);opacity:{{ $layout['video']['opacity'] ?? 1 }};">
<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#334155,#020617);">
<div style="text-align:center;color:white;"><div style="font-size:78px;">🎬</div><div style="font-size:32px;font-weight:800;margin-top:10px;">Área do vídeo</div><div style="font-size:22px;margin-top:6px;color:#cbd5e1;">Preview visual</div></div>
</div>
<div class="resize-handle"></div>
</div>
<div id="centerGuideV" class="guide guide-v"></div><div id="centerGuideH" class="guide guide-h"></div>
</div>
</div>
</main>
<aside style="background:#111827;border-left:1px solid #334155;padding:18px;overflow:auto;">
<h3 style="font-size:20px;font-weight:800;margin-bottom:15px;">Propriedades</h3>
<div class="prop-group"><label>X</label><input id="propX" type="number"></div>
<div class="prop-group"><label>Y</label><input id="propY" type="number"></div>
<div class="prop-group"><label>Largura</label><input id="propW" type="number"></div>
<div class="prop-group"><label>Altura</label><input id="propH" type="number"></div>
<div class="prop-group"><label>Rotação</label><input id="propRotation" type="range" min="-30" max="30"><span id="rotationLabel"></span></div>
<div class="prop-group"><label>Opacidade</label><input id="propOpacity" type="range" min="20" max="100"><span id="opacityLabel"></span></div>
<div class="prop-group"><label>Cantos arredondados</label><input id="propRadius" type="range" min="0" max="120"><span id="radiusLabel"></span></div>
<hr style="border-color:#334155;margin:20px 0;">
<h3 style="font-size:20px;font-weight:800;margin-bottom:15px;">Fundo</h3>
<div class="prop-group"><label>Cor sólida</label><input id="bgColor" type="color"></div>
<button id="saveLayoutBtn" style="width:100%;background:#16a34a;color:white;border:none;border-radius:10px;padding:12px;margin-top:20px;cursor:pointer;font-weight:800;">Salvar layout visual</button>
<div id="saveMessage" style="margin-top:12px;color:#86efac;"></div>
</aside>
</div>
</div>
<style>
.tool-btn{display:block;width:100%;background:#1f2937;color:white;border:1px solid #334155;border-radius:10px;padding:10px 12px;margin-bottom:8px;text-align:left;cursor:pointer}.tool-btn:hover{background:#334155}.prop-group{margin-bottom:14px}.prop-group label{display:block;color:#cbd5e1;font-size:13px;margin-bottom:6px}.prop-group input{width:100%;color:#111827;padding:9px;border-radius:8px;border:1px solid #475569}.resize-handle{position:absolute;width:28px;height:28px;right:0;bottom:0;background:#22c55e;border-radius:10px 0 0 0;cursor:nwse-resize;z-index:20}.guide{position:absolute;background:#22c55e;opacity:0;pointer-events:none;z-index:30}.guide-v{width:3px;height:100%;left:50%;top:0}.guide-h{height:3px;width:100%;top:50%;left:0}
</style>
<script>
(function(){const app=document.getElementById('visualEditorApp'),saveUrl=app.dataset.saveUrl,csrf=app.dataset.csrf,templateId=app.dataset.templateId,canvasWidth=parseInt(app.dataset.canvasWidth||'1080'),canvasHeight=parseInt(app.dataset.canvasHeight||'1920');let layout=JSON.parse(app.dataset.layout||'{}');const canvasStage=document.getElementById('canvasStage'),videoBox=document.getElementById('videoBox'),editorZoom=document.getElementById('editorZoom'),editorZoomLabel=document.getElementById('editorZoomLabel'),saveLayoutBtn=document.getElementById('saveLayoutBtn'),saveMessage=document.getElementById('saveMessage'),propX=document.getElementById('propX'),propY=document.getElementById('propY'),propW=document.getElementById('propW'),propH=document.getElementById('propH'),propRotation=document.getElementById('propRotation'),propOpacity=document.getElementById('propOpacity'),propRadius=document.getElementById('propRadius'),rotationLabel=document.getElementById('rotationLabel'),opacityLabel=document.getElementById('opacityLabel'),radiusLabel=document.getElementById('radiusLabel'),bgColor=document.getElementById('bgColor'),guideV=document.getElementById('centerGuideV'),guideH=document.getElementById('centerGuideH');let current=layout.video||{x:120,y:260,width:840,height:900,rotation:0,opacity:1,borderRadius:28},background=layout.background||{type:'solid',color:'#111827'},dragging=false,resizing=false,startMouse={x:0,y:0},startBox={...current};function scale(){return parseInt(editorZoom.value)/100}function applyZoom(){const z=scale();canvasStage.style.transform=`scale(${z})`;editorZoomLabel.innerText=Math.round(z*100)+'%'}function applyBox(){videoBox.style.left=current.x+'px';videoBox.style.top=current.y+'px';videoBox.style.width=current.width+'px';videoBox.style.height=current.height+'px';videoBox.style.transform=`rotate(${current.rotation||0}deg)`;videoBox.style.opacity=current.opacity||1;videoBox.style.borderRadius=(current.borderRadius||0)+'px';canvasStage.style.background=background.color||'#111827';propX.value=Math.round(current.x);propY.value=Math.round(current.y);propW.value=Math.round(current.width);propH.value=Math.round(current.height);propRotation.value=current.rotation||0;propOpacity.value=Math.round((current.opacity||1)*100);propRadius.value=current.borderRadius||0;bgColor.value=background.color||'#111827';rotationLabel.innerText=(current.rotation||0)+'°';opacityLabel.innerText=Math.round((current.opacity||1)*100)+'%';radiusLabel.innerText=(current.borderRadius||0)+'px';checkGuides()}function checkGuides(){const cx=current.x+current.width/2,cy=current.y+current.height/2;guideV.style.opacity=Math.abs(cx-canvasWidth/2)<10?1:0;guideH.style.opacity=Math.abs(cy-canvasHeight/2)<10?1:0}function updateFromInputs(){current.x=parseInt(propX.value||0);current.y=parseInt(propY.value||0);current.width=parseInt(propW.value||100);current.height=parseInt(propH.value||100);current.rotation=parseInt(propRotation.value||0);current.opacity=parseInt(propOpacity.value||100)/100;current.borderRadius=parseInt(propRadius.value||0);background.color=bgColor.value;applyBox()}function mouseOnCanvas(e){const rect=canvasStage.getBoundingClientRect(),s=scale();return{x:(e.clientX-rect.left)/s,y:(e.clientY-rect.top)/s}}videoBox.addEventListener('mousedown',e=>{if(e.target.classList.contains('resize-handle'))resizing=true;else dragging=true;startMouse=mouseOnCanvas(e);startBox={...current};e.preventDefault()});window.addEventListener('mousemove',e=>{if(!dragging&&!resizing)return;const m=mouseOnCanvas(e),dx=m.x-startMouse.x,dy=m.y-startMouse.y;if(dragging){current.x=Math.max(-canvasWidth,Math.min(canvasWidth,startBox.x+dx));current.y=Math.max(-canvasHeight,Math.min(canvasHeight,startBox.y+dy))}if(resizing){current.width=Math.max(120,startBox.width+dx);current.height=Math.max(120,startBox.height+dy)}applyBox()});window.addEventListener('mouseup',()=>{dragging=false;resizing=false;guideV.style.opacity=0;guideH.style.opacity=0});[propX,propY,propW,propH,propRotation,propOpacity,propRadius,bgColor].forEach(i=>i.addEventListener('input',updateFromInputs));editorZoom.addEventListener('input',applyZoom);document.querySelectorAll('.tool-btn').forEach(btn=>btn.addEventListener('click',function(){const a=this.dataset.action;if(a==='center'){current.x=(canvasWidth-current.width)/2;current.y=(canvasHeight-current.height)/2}if(a==='fit'){current.x=80;current.y=160;current.width=canvasWidth-160;current.height=canvasHeight-320;current.rotation=0}if(a==='reset'){current={x:120,y:260,width:canvasWidth-240,height:Math.round(canvasHeight*.55),rotation:0,opacity:1,borderRadius:28};background={type:'solid',color:'#111827'}}if(a==='save'){saveLayout();return}applyBox()}));saveLayoutBtn.addEventListener('click',saveLayout);async function saveLayout(){if(!templateId){alert('Crie ou selecione um template antes de salvar.');return}saveMessage.innerText='Salvando...';const payload={template_id:templateId,canvas_width:canvasWidth,canvas_height:canvasHeight,layout:{background:background,video:current}};const r=await fetch(saveUrl,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},body:JSON.stringify(payload)});const d=await r.json();saveMessage.innerText=d.ok?d.message:'Erro ao salvar layout.'}applyZoom();applyBox()})();
</script>
</x-app-layout>
