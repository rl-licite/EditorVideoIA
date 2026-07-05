@php
    $layout = $template->visual_layout ?? [];
    $ctaText = old('cta_text', $template->cta_text ?? ($layout['cta_text'] ?? 'SAIBA MAIS'));
    $primaryColor = old('primary_color', $template->primary_color ?? ($layout['primary_color'] ?? '#facc15'));
    $backgroundColor = old('background_color', $template->background_color ?? ($layout['background_color'] ?? '#020617'));
    $fontFamily = old('font_family', $template->font_family ?? ($layout['font_family'] ?? 'Arial'));
@endphp
<div class="grid">
    <section class="card form-card">
        @csrf
        <label>Nome do template</label>
        <input name="name" value="{{ old('name', $template->name ?? '') }}" required>

        <label>Formato</label>
        <select name="format" id="format">
            @foreach(['vertical'=>'Vertical/Reels','horizontal'=>'Horizontal/Youtube','quadrado'=>'Quadrado'] as $value=>$label)
                <option value="{{ $value }}" @selected(old('format', $template->format ?? 'vertical') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <label>Resolução</label>
        <select name="resolution" id="resolution">
            @foreach(['1080x1920','1920x1080','1080x1080','720x1280','1280x720'] as $res)
                <option value="{{ $res }}" @selected(old('resolution', $template->resolution ?? '1080x1920') === $res)>{{ $res }}</option>
            @endforeach
        </select>

        <label>Texto principal editável</label>
        <input name="overlay_text" id="overlay_text" value="{{ old('overlay_text', $template->overlay_text ?? 'Texto principal editável') }}">

        <label>Posição do texto</label>
        <select name="overlay_position" id="overlay_position">
            @foreach(['top'=>'Topo','center'=>'Centro','bottom'=>'Rodapé'] as $value=>$label)
                <option value="{{ $value }}" @selected(old('overlay_position', $template->overlay_position ?? 'bottom') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <label>CTA/TCA editável</label>
        <input name="cta_text" id="cta_text" value="{{ $ctaText }}" placeholder="Ex: COMPRE AGORA / SAIBA MAIS">

        <label>Posição do CTA/TCA</label>
        <select name="cta_position" id="cta_position">
            @foreach(['top'=>'Topo','center'=>'Centro','bottom'=>'Rodapé'] as $value=>$label)
                <option value="{{ $value }}" @selected(old('cta_position', $template->cta_position ?? ($layout['cta_position'] ?? 'bottom')) === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <label>Marca d’água</label>
        <input name="watermark_text" id="watermark_text" value="{{ old('watermark_text', $template->watermark_text ?? '@sua_marca') }}">

        <label>Posição da marca d’água</label>
        <select name="watermark_position" id="watermark_position">
            @foreach(['top-left'=>'Topo esquerdo','top-right'=>'Topo direito','bottom-left'=>'Rodapé esquerdo','bottom-right'=>'Rodapé direito'] as $value=>$label)
                <option value="{{ $value }}" @selected(old('watermark_position', $template->watermark_position ?? 'bottom-right') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <label>Fonte</label>
        <select name="font_family" id="font_family">
            @foreach(['Arial','Impact','Verdana','Georgia','Tahoma'] as $font)
                <option value="{{ $font }}" @selected($fontFamily === $font)>{{ $font }}</option>
            @endforeach
        </select>

        <label>Cor principal</label>
        <input type="color" name="primary_color" id="primary_color" value="{{ $primaryColor }}">

        <label>Cor de fundo</label>
        <input type="color" name="background_color" id="background_color" value="{{ $backgroundColor }}">

        <label>Legenda automática</label>
        <select name="auto_subtitle" id="auto_subtitle">
            <option value="0" @selected(!old('auto_subtitle', $template->auto_subtitle ?? false))>Desligada</option>
            <option value="1" @selected(old('auto_subtitle', $template->auto_subtitle ?? false))>Ligada</option>
        </select>

        <label>Posição da legenda</label>
        <select name="subtitle_position" id="subtitle_position">
            @foreach(['top'=>'Topo','center'=>'Centro','bottom'=>'Rodapé'] as $value=>$label)
                <option value="{{ $value }}" @selected(old('subtitle_position', $template->subtitle_position ?? 'bottom') === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <label>Cor da legenda</label>
        <input type="color" name="subtitle_color" id="subtitle_color" value="{{ old('subtitle_color', $template->subtitle_color ?? '#ffffff') }}">

        <input type="hidden" name="canvas_width" value="{{ old('canvas_width', $template->canvas_width ?? 1080) }}">
        <input type="hidden" name="canvas_height" value="{{ old('canvas_height', $template->canvas_height ?? 1920) }}">

        <div class="actions">
            <button type="submit" class="btn primary">Salvar template</button>
            <a href="{{ route('templates.index') }}" class="btn">Voltar</a>
        </div>
    </section>

    <section class="card preview-card">
        <h2>Prévia visual</h2>
        <div id="canvas" class="canvas">
            <div id="previewText" class="preview-text"></div>
            <div id="previewCta" class="preview-cta"></div>
            <div id="previewSubtitle" class="preview-subtitle">Legenda automática editável</div>
            <div id="previewWatermark" class="preview-watermark"></div>
        </div>
        <p class="muted">Essa prévia serve para validar o layout antes de aplicar no editor.</p>
    </section>
</div>
<script>
function applyPreview(){
 const canvas=document.getElementById('canvas');
 const txt=document.getElementById('previewText');
 const cta=document.getElementById('previewCta');
 const sub=document.getElementById('previewSubtitle');
 const wm=document.getElementById('previewWatermark');
 const q=id=>document.getElementById(id);
 canvas.style.background=q('background_color').value;
 canvas.style.fontFamily=q('font_family').value;
 txt.innerText=q('overlay_text').value||'Texto principal';
 cta.innerText=q('cta_text').value||'SAIBA MAIS';
 wm.innerText=q('watermark_text').value||'';
 cta.style.background=q('primary_color').value; cta.style.color='#111827';
 sub.style.color=q('subtitle_color').value;
 txt.className='preview-text pos-'+q('overlay_position').value;
 cta.className='preview-cta pos-'+q('cta_position').value;
 sub.style.display=q('auto_subtitle').value==='1'?'block':'none';
 wm.className='preview-watermark wm-'+q('watermark_position').value;
}
document.querySelectorAll('input,select').forEach(el=>el.addEventListener('input',applyPreview));
applyPreview();
</script>
