# EditorVideoIA — Etapa 12.1, 12.2 e 12.3

## O que este pacote entrega

- 12.1 Biblioteca de mídia real com pesquisa, filtro, cards e metadados básicos.
- 12.2 Upload real de vídeos, imagens e áudios para `storage/app/public/editor-video`.
- 12.3 Timeline funcional com drag and drop da biblioteca para as trilhas, zoom e salvamento de clipes no banco.

## Onde copiar

Copie as pastas do pacote para dentro de:

```cmd
C:\Projetos\EditorVideoIA\backend
```

Aceite substituir os arquivos quando o Windows perguntar.

## Conferir web.php

Abra:

```txt
C:\Projetos\EditorVideoIA\backend\routes\web.php
```

O arquivo precisa ter esta linha no final:

```php
require __DIR__.'/editor-video.php';
```

## Rodar no CMD

Copie tudo de uma vez:

```cmd
cd C:\Projetos\EditorVideoIA\backend
php artisan route:clear
php artisan optimize:clear
php artisan storage:link
php artisan migrate
php artisan route:list | findstr editor-video
php artisan serve
```

## Testar no navegador

Acesse:

```txt
http://127.0.0.1:8000/editor-video
```

## Testes obrigatórios

### Teste 1 — A página abre
Resultado esperado:

- abre a tela do EditorVideoIA;
- aparece Biblioteca de mídia;
- aparece Timeline;
- aparece Inspector.

### Teste 2 — Upload de mídia
Clique em **Adicionar mídia** e envie uma imagem JPG ou PNG.

Resultado esperado:

- aparece mensagem de mídia importada;
- o arquivo aparece na biblioteca;
- se for imagem, aparece miniatura.

### Teste 3 — Filtro de mídia
Use a caixa de pesquisa e o filtro Todos/Vídeos/Imagens/Áudios.

Resultado esperado:

- os cards são filtrados sem recarregar a página.

### Teste 4 — Timeline
Arraste uma mídia da biblioteca para uma trilha da Timeline.

Resultado esperado:

- o clipe aparece na timeline;
- ao clicar nele, o Inspector preenche os campos.

### Teste 5 — Inspector
Altere nome, início e duração do clipe e clique em **Aplicar**.

Resultado esperado:

- o clipe muda de posição/tamanho na timeline;
- o nome atualizado aparece no clipe.

### Teste 6 — Salvar e recarregar
Clique em **Salvar**, depois aperte F5.

Resultado esperado:

- o clipe continua salvo na timeline;
- a mídia continua na biblioteca.
