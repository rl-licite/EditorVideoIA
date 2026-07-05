@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

echo ==================================================
echo EditorVideoIA - Limpeza e revisao pre-Fase 5
echo ==================================================
echo.

cd /d "%~dp0"

set BACKUP=_limpeza_pre_fase5_backup
if not exist "%BACKUP%" mkdir "%BACKUP%"

echo [1/6] Fazendo backup do routes\web.php...
if exist "routes\web.php" (
    copy "routes\web.php" "%BACKUP%\web.php.backup" >nul
)

echo [2/6] Aplicando routes\web.php revisado...
if exist "limpeza_payload\routes_web_limpo.php" (
    copy "limpeza_payload\routes_web_limpo.php" "routes\web.php" >nul
) else (
    echo ERRO: arquivo limpeza_payload\routes_web_limpo.php nao encontrado.
    pause
    exit /b 1
)

echo [3/6] Movendo pacotes antigos de entrega para backup...
for /d %%D in (EditorVideoIA_Fase*_Entrega* fase3_payload) do (
    if exist "%%D" (
        echo Movendo pasta %%D
        move "%%D" "%BACKUP%\" >nul
    )
)

if exist "_limpeza_pre_fase4_backup" (
    echo Movendo backup antigo da pre-Fase 4
    move "_limpeza_pre_fase4_backup" "%BACKUP%\" >nul
)

echo [4/6] Movendo arquivos temporarios de instalacao, rotas e readmes antigos...
for %%F in (
    "INSTALAR_CORRECAO*.bat"
    "INSTALAR_CORRECAO*.cmd"
    "INSTALAR_ETAPA*.bat"
    "INSTALAR_ETAPA*.cmd"
    "INSTALAR_FASE2*.bat"
    "INSTALAR_FASE2*.cmd"
    "INSTALAR_FASE_3*.bat"
    "INSTALAR_FASE_3*.cmd"
    "INSTALAR_FASE_4*.bat"
    "INSTALAR_FASE_4*.cmd"
    "LIMPAR_PRE_FASE4*.bat"
    "ROTAS_FASE_*.txt"
    "LEIA_ME*.txt"
    "README-CORRECAO*.txt"
    "README_CORRECAO*.txt"
    "README-ETAPA*.txt"
    "README_ETAPA*.txt"
    "README-FASE*.txt"
    "README_FASE*.txt"
    "README-PACOTE*.txt"
    "README_LIMPEZA_PRE_FASE4.txt"
) do (
    for %%G in (%%F) do (
        if exist "%%G" (
            echo Movendo arquivo %%G
            move "%%G" "%BACKUP%\" >nul
        )
    )
)

echo [5/6] Limpando caches do Laravel...
php artisan route:clear
php artisan cache:clear
php artisan config:clear
php artisan view:clear

echo [6/6] Gerando checklist da limpeza...
(
echo EditorVideoIA - Checklist limpeza pre-Fase 5
echo.
echo Feito:
echo - Backup do routes\web.php criado em %BACKUP%
echo - routes\web.php revisado aplicado
echo - Duplicidade da rota enterprise-ia removida
echo - Pacotes antigos de entrega movidos para %BACKUP%
echo - Readmes, arquivos ROTAS e instaladores antigos movidos para %BACKUP%
echo - Cache do Laravel limpo
echo.
echo Testes recomendados:
echo 1. php artisan route:list
echo 2. php artisan serve
echo 3. Abrir http://127.0.0.1:8000/editor-video
echo 4. Abrir http://127.0.0.1:8000/editor-video/fase-4/fase-final
echo 5. Confirmar sem erro 404 e sem erro 500
) > CHECKLIST_LIMPEZA_PRE_FASE5.txt

echo.
echo ==================================================
echo Limpeza pre-Fase 5 concluida.
echo Backup criado/mantido em: %BACKUP%
echo Confira: CHECKLIST_LIMPEZA_PRE_FASE5.txt
echo ==================================================
pause
