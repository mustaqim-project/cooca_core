@echo off
title Cooca Scheduler Worker (Local Development)
cd /d "%~dp0"
echo ========================================================
echo   Cooca Local Schedule Worker Running...
echo   Menjalankan scheduler otomatis setiap menit.
echo   Tekan Ctrl+C untuk menghentikan.
echo ========================================================
php artisan schedule:work
