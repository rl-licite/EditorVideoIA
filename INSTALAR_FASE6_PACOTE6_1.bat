@echo off
chcp 65001 >nul
echo ===============================================
echo EditorVideoIA - Fase 6 Pacote 6.1
echo Instalacao/atualizacao do Motor Profissional
echo ===============================================
echo.
echo Limpando caches do Laravel...
php artisan optimize:clear
echo.
echo Garantindo link de storage...
php artisan storage:link
echo.
echo Rodando migrations pendentes...
php artisan migrate

echo.
echo Instalacao concluida.
echo Agora rode separado: php artisan serve
echo Depois abra: http://127.0.0.1:8000/editor-video
echo.
pause
