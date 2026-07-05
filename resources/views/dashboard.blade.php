<x-app-layout>
<x-slot name="header"><h2 style="font-size:24px;font-weight:800;">EditorVideoIA Pro — Versão 1.0</h2></x-slot>
<div style="padding:30px;background:#f3f4f6;min-height:calc(100vh - 80px);">
<div style="display:grid;grid-template-columns:260px 1fr;gap:24px;">
<aside style="background:#111827;color:white;border-radius:18px;padding:22px;height:max-content;">
<h2 style="font-size:22px;font-weight:800;margin-bottom:20px;">Menu Pro</h2>
<a href="{{ route('dashboard') }}" style="display:block;color:white;text-decoration:none;padding:10px 0;">🏠 Dashboard</a>
<a href="{{ route('projects.index') }}" style="display:block;color:white;text-decoration:none;padding:10px 0;">📁 Projetos</a>
<a href="{{ route('videos.index') }}" style="display:block;color:white;text-decoration:none;padding:10px 0;">🎬 Biblioteca</a>
<a href="{{ route('templates.index') }}" style="display:block;color:white;text-decoration:none;padding:10px 0;">🎨 Templates</a>
<a href="{{ route('exports.create') }}" style="display:block;color:white;text-decoration:none;padding:10px 0;">⚙️ Processar</a>
<a href="{{ route('exports.index') }}" style="display:block;color:white;text-decoration:none;padding:10px 0;">📊 Fila</a>
<a href="{{ route('ai.index') }}" style="display:block;color:white;text-decoration:none;padding:10px 0;">🤖 IA</a>
<a href="{{ route('plans.index') }}" style="display:block;color:white;text-decoration:none;padding:10px 0;">💳 Planos</a>
<a href="{{ route('credits.index') }}" style="display:block;color:white;text-decoration:none;padding:10px 0;">🪙 Créditos</a>
<a href="{{ route('settings.index') }}" style="display:block;color:white;text-decoration:none;padding:10px 0;">🔧 Configurações</a>
<a href="{{ route('logs.index') }}" style="display:block;color:white;text-decoration:none;padding:10px 0;">🧾 Logs</a>
<a href="{{ route('backups.index') }}" style="display:block;color:white;text-decoration:none;padding:10px 0;">💾 Backups</a>
<a href="{{ route('production.checklist') }}" style="display:block;color:white;text-decoration:none;padding:10px 0;">✅ Checklist</a>
</aside>
<main>
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:24px;">
@foreach(['videos'=>'Vídeos','projetos'=>'Projetos','exports'=>'Exportações','pendentes'=>'Pendentes'] as $key=>$label)
<div style="background:white;padding:24px;border-radius:18px;box-shadow:0 2px 8px #ddd;"><p style="color:#666;">{{ $label }}</p><h1 style="font-size:36px;font-weight:800;">{{ $stats[$key] ?? 0 }}</h1></div>
@endforeach
</div>
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:24px;">
@foreach(['concluidos'=>'Concluídos','erros'=>'Erros','logs'=>'Logs','backups'=>'Backups'] as $key=>$label)
<div style="background:white;padding:24px;border-radius:18px;box-shadow:0 2px 8px #ddd;"><p style="color:#666;">{{ $label }}</p><h1 style="font-size:36px;font-weight:800;">{{ $stats[$key] ?? 0 }}</h1></div>
@endforeach
</div>
<div style="background:white;padding:28px;border-radius:18px;box-shadow:0 2px 8px #ddd;margin-bottom:24px;">
<h3 style="font-size:26px;font-weight:800;">EditorVideoIA Pro finalizado</h3>
<p style="color:#555;margin:10px 0 20px;">Sistema com upload, biblioteca, templates, IA, legendas, fila, créditos, planos, projetos, logs, backup e checklist de produção.</p>
<a href="{{ route('exports.create') }}" style="background:#16a34a;color:white;padding:12px 18px;border-radius:10px;text-decoration:none;">Processar vídeos</a>
<a href="{{ route('backups.index') }}" style="margin-left:10px;background:#111827;color:white;padding:12px 18px;border-radius:10px;text-decoration:none;">Criar backup</a>
<a href="{{ route('production.checklist') }}" style="margin-left:10px;background:#2563eb;color:white;padding:12px 18px;border-radius:10px;text-decoration:none;">Checklist final</a>
</div>
<div style="background:white;padding:24px;border-radius:18px;box-shadow:0 2px 8px #ddd;">
<h3 style="font-size:22px;font-weight:800;margin-bottom:15px;">Últimas exportações</h3>
@forelse($recentExports as $export)
<div style="border-bottom:1px solid #eee;padding:12px 0;display:flex;justify-content:space-between;">
<div><strong>{{ $export->video->original_name ?? '-' }}</strong><div style="color:#666;font-size:13px;">{{ $export->template->name ?? '-' }}</div></div>
<span>{{ $export->status_label }} — {{ $export->progress }}%</span>
</div>
@empty
<p>Nenhuma exportação.</p>
@endforelse
</div>
</main>
</div>
</div>
</x-app-layout>
