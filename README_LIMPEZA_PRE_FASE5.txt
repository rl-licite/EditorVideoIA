# EditorVideoIA — Revisão e limpeza pré-Fase 5

Este pacote faz uma limpeza segura antes de iniciar a Fase 5.

## O que ele faz

- Faz backup do `routes/web.php`.
- Aplica um `routes/web.php` revisado.
- Remove a duplicidade encontrada nas rotas da Fase 3 Entrega 5 (`enterprise-ia`).
- Move pacotes antigos de entrega, instaladores antigos, arquivos `ROTAS_*.txt` e READMEs temporários para `_limpeza_pre_fase5_backup`.
- Limpa cache do Laravel.

## O que ele NÃO faz

- Não apaga `app`, `resources`, `routes`, `database`, `public`, `config`, `storage`, `vendor` nem `node_modules`.
- Não exclui arquivos definitivamente; move para backup.

## Teste depois de rodar

1. `php artisan serve`
2. Abrir `http://127.0.0.1:8000/editor-video`
3. Abrir `http://127.0.0.1:8000/editor-video/fase-4/fase-final`
4. Confirmar que não aparece 404 ou 500.
