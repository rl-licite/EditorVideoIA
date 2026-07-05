@include('templates._style')
<header class="page-head">
    <div><h1>Templates Editáveis</h1><p>Área de templates com CTA/TCA, texto, legenda, fonte, cor e marca d’água editáveis.</p></div>
    <div><a class="btn" href="/editor-video">Editor</a> <a class="btn primary" href="{{ route('templates.create') }}">+ Novo Template</a></div>
</header>
@if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
<div class="cards">
@forelse($templates as $template)
    @php $layout=$template->visual_layout ?? []; @endphp
    <article class="template-card">
        <div class="mini" style="background:{{ $template->background_color ?? ($layout['background_color'] ?? '#020617') }};font-family:{{ $template->font_family ?? ($layout['font_family'] ?? 'Arial') }}">
            <strong>{{ $template->overlay_text ?: 'Texto principal' }}</strong>
            <span style="background:{{ $template->primary_color ?? ($layout['primary_color'] ?? '#facc15') }}">{{ $template->cta_text ?? ($layout['cta_text'] ?? 'SAIBA MAIS') }}</span>
            <em>{{ $template->watermark_text }}</em>
        </div>
        <h2>{{ $template->name }}</h2>
        <p>{{ $template->format }} • {{ $template->resolution }}</p>
        <div class="actions">
            <a class="btn primary" href="{{ route('templates.edit', $template) }}">Editar</a>
            <form method="POST" action="{{ route('templates.destroy', $template) }}" onsubmit="return confirm('Excluir template?')">
                @csrf @method('DELETE')
                <button class="btn danger">Excluir</button>
            </form>
        </div>
    </article>
@empty
    <div class="empty">Nenhum template ainda. Clique em <b>Novo Template</b> para testar.</div>
@endforelse
</div>
<div class="pagination">{{ $templates->links() }}</div>
