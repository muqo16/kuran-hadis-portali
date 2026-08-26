@echo off
chcp 65001 > nul
echo ========================================================
echo   Kur'an-ı Kerim ve Sahih Hadis Portali - GitHub Push
echo ========================================================
echo.
echo GitHub'a gonderiliyor...
git push -u origin main
echo.
if %ERRORLEVEL% EQU 0 (
    echo [BASARILI] Proje GitHub'a basariyla yuklendi!
) else (
    echo [BILGI] Eger hata aldiysaniz GitHub'da 'kuran-hadis-portali' reposunu actiginizdan emin olun.
)
echo.
pause
