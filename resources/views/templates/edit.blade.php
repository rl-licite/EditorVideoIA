@include('templates._style')
<header class="page-head"><div><h1>Editar Template</h1><p>Altere o template e teste se ele permanece salvo após F5.</p></div><a class="btn" href="/editor-video">Abrir Editor</a></header>
<form method="POST" action="{{ route('templates.update', $template) }}">
@method('PUT')
@include('templates._form')
</form>
