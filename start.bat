@echo off
chcp 65001 > nul
title Kur'an-i Kerim Meali (Yasar Nuri Ozturk)
echo ========================================================
echo   Kur'an-i Kerim Arama & Okuma Uygulamasi
echo   Meal: Prof. Dr. Yasar Nuri Ozturk
echo ========================================================
echo.
echo Sunucu baslatiliyor: http://localhost:8000
echo Tarayiciniz otomatik olarak acilacaktir...
echo.

start "" "http://localhost:8000"
php -S localhost:8000
pause
