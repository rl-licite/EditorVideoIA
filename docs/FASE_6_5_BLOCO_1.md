# EditorVideoIA — Fase 6.5 — Bloco 1

Este pacote corrige a base do editor antes da renderização MP4 real.

## O que foi alterado

- Removida a dependência principal do projeto fixo `id = 1` no fluxo do editor.
- O editor agora resolve o projeto ativo por `project_id` quando informado e usa o projeto mais recente como fallback.
- Salvamento passa a enviar `project_id` do projeto aberto.
- Preview/player foi estabilizado para não recriar o vídeo a cada atualização do playhead.
- Play/Pause/Stop agora sincronizam melhor o tempo local do clipe com o tempo da timeline.
- Inspector continua salvando volume, velocidade, opacidade, escala, X, Y e rotação dentro do `timeline_data`.
- Timeline mantém seleção múltipla, arraste em grupo, duplicação, delete, undo/redo, razor e snap.

## Como instalar

Copie os arquivos deste pacote por cima do projeto atual ou execute:

```bat
INSTALAR_FASE_6_5_BLOCO_1.bat
```

## Depois rode

```bat
php artisan optimize:clear
php artisan serve
```

Abra:

```text
http://127.0.0.1:8000/editor-video
```

## Teste obrigatório

1. Abra o editor.
2. Importe pelo menos 2 vídeos ou imagens.
3. Arraste uma mídia para a timeline.
4. Arraste outra mídia para outra posição da timeline.
5. Selecione um clipe e altere no Inspector: volume, escala, posição X/Y, rotação e opacidade.
6. Clique em **Aplicar alterações**.
7. Clique em **Salvar projeto**.
8. Aperte F5.
9. Verifique se os clipes e alterações continuam na timeline.
10. Clique em Play/Pause/Stop e veja se o preview acompanha a timeline.

## Observação sincera

Este bloco ainda não é a exportação MP4 real. Ele prepara e estabiliza a base para a Fase 7.
