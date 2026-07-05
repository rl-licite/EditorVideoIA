# EditorVideoIA — Etapa 13 — Template + CTA/TCA editável

## O que entra nesta etapa
- Tela de templates com prévia visual.
- Criação e edição de template.
- CTA/TCA editável: texto, posição, estilo, início e fim.
- Cores editáveis, fonte, texto principal, marca d’água e legenda automática.
- Salvamento do layout em `visual_layout` para uso posterior no editor e exportação.

## Instalação
1. Extraia o ZIP dentro da raiz do projeto:
   `C:\Projetos\EditorVideoIAackend`
2. No CMD, rode:
   `cd C:\Projetos\EditorVideoIAackend`
3. Rode:
   `INSTALAR_ETAPA_13_TEMPLATE_TCA.bat`
4. Inicie o servidor, se ainda não estiver aberto:
   `php artisan serve`

## Teste
1. Acesse: `http://127.0.0.1:8000/templates`
2. Clique em `+ Novo template`.
3. Preencha:
   - Nome: `Template Dark Teste`
   - Texto principal: `Esse erro acaba com qualquer canal dark`
   - CTA/TCA: `Siga para parte 2`
   - Altere cor, fonte e posição.
4. Confira se a prévia muda na hora.
5. Clique em `Salvar template`.
6. Volte em `Templates editáveis`, clique em `Editar`, altere o CTA/TCA e salve.
7. Resultado esperado: o card muda e os dados continuam salvos ao atualizar a página.
