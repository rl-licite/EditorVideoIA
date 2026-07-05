@include('templates._style')
<header class="page-head"><div><h1>Novo Template Editável</h1><p>Crie um template com texto, CTA/TCA, legenda e marca d’água editáveis.</p></div><a class="btn" href="/editor-video">Abrir Editor</a></header>
<form method="POST" action="{{ route('templates.store') }}">
@include('templates._form')
</form>
