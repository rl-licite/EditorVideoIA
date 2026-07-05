@echo off
cd /d %~dp0

echo === EditorVideoIA - Instalando Fase 6.5 Bloco 1 ===
echo.

echo Limpando caches do Laravel...
php artisan optimize:clear

echo.
echo Rodando migrations pendentes, se existirem...
php artisan migrate --force

echo.
echo Instalacao concluida.
echo Agora rode: php artisan serve
echo Abra: http://127.0.0.1:8000/editor-video
pause
