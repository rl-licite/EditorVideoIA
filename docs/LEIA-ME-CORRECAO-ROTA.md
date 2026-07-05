# Correção da rota /editor-video

Este pacote cria uma implementação Laravel inicial para a tela `/editor-video` parar de retornar 404.

## Caminho do seu projeto

Use este caminho:

```cmd
C:\Projetos\EditorVideoIA\backend
```

## Como instalar

1. Extraia o ZIP.
2. Copie as pastas `app`, `resources` e `routes` para dentro de:

```cmd
C:\Projetos\EditorVideoIA\backend
```

3. Abra o arquivo:

```cmd
C:\Projetos\EditorVideoIA\backend\routes\web.php
```

4. No final do arquivo, cole esta linha:

```php
require __DIR__.'/editor-video.php';
```

## Comandos para rodar

Copie e cole tudo no CMD:

```cmd
cd C:\Projetos\EditorVideoIA\backend
php artisan route:clear
php artisan optimize:clear
php artisan route:list | findstr editor-video
php artisan serve
```

## URLs para testar

Tela inicial:

```text
http://127.0.0.1:8000/editor-video
```

Editor:

```text
http://127.0.0.1:8000/editor-video/editor/1
```

## Resultado esperado

- A URL `/editor-video` deve abrir a tela `EditorVideoIA`.
- O botão `Criar projeto` deve levar para o editor.
- O botão `Abrir editor` deve abrir a tela com preview, biblioteca de mídia, timeline e inspector.
