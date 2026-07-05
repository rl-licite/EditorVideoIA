@echo off
chcp 65001 >nul
cls

echo ================================================
echo EditorVideoIA - Fase 6.5 - Bloco 1
echo Atualizacao de base do editor
echo ================================================
echo.

cd /d %~dp0

echo Limpando cache do Laravel...
php artisan optimize:clear

echo.
echo Recriando link de storage, se necessario...
php artisan storage:link

echo.
echo Validando rotas principais...
php artisan route:list | findstr editor-video

echo.
echo ================================================
echo Instalacao do Bloco 1 concluida.
echo Agora rode: php artisan serve
echo Abra: http://127.0.0.1:8000/editor-video
echo ================================================
pause
