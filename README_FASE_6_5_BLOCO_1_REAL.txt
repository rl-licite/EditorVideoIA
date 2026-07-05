EditorVideoIA - Fase 6.5 - Bloco 1

ALTERACOES REAIS IMPLEMENTADAS NESTE PACOTE:

1) Upload multiplo estabilizado
- O campo de upload agora aceita selecionar varios arquivos de uma vez.
- O backend aceita media[] e salva todos os arquivos enviados.
- A biblioteca atualiza todos os cards importados sem precisar atualizar a pagina.

2) Timeline / base do editor
- Mantida a estrutura multicamada da Fase 6.
- Metadados da timeline atualizados para Fase 6.5 Bloco 1.
- Salvamento envia project_id do projeto ativo para reduzir risco de salvar no projeto errado.

3) Inspector com preview imediato
- Alteracoes de volume, velocidade, opacidade, escala, X, Y, rotacao, inicio e duracao atualizam o preview/timeline durante a digitacao.
- O botao Aplicar continua existindo para confirmar a alteracao e registrar no historico.

4) Base sem id fixo 1
- O controller ja usa resolveProject por project_id e fallback para ultimo projeto existente.
- O frontend agora preserva o activeProjectId nas requisicoes do editor.

COMANDOS:

cd C:\Projetos\EditorVideoIA\backend
INSTALAR_FASE_6_5_BLOCO_1_REAL.bat

Depois:

php artisan serve

Abrir:

http://127.0.0.1:8000/editor-video

TESTE PRINCIPAL:

1. Abrir /editor-video.
2. Selecionar 3 arquivos de uma vez no upload: 1 video, 1 imagem e 1 audio.
3. Clicar em Enviar mídia.
4. Confirmar que os 3 aparecem na biblioteca.
5. Arrastar 2 videos, 1 imagem e 1 audio para a timeline.
6. Selecionar um clip.
7. Alterar escala, X, Y, rotacao, volume e opacidade.
8. Confirmar se o preview muda na hora.
9. Clicar em Salvar projeto.
10. Atualizar a pagina com F5.
11. Confirmar se a timeline continua carregada.

OBSERVACAO:
Este pacote nao e a Fase 6.5 completa. Ele e o Bloco 1. O Bloco 2 ainda vai fechar persistencia final, inspector completo e preparacao direta da Fase 7.
