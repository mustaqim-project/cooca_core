@echo off
rem ==============================================================================
rem COOCA ERP - Windows Scheduler Runner (cron-run.bat)
rem ==============================================================================
rem Executes Laravel's schedule:run on Windows / Laragon.
rem Can be triggered via Windows Task Scheduler (every 1 minute) or run manually.
rem Automates:
rem   - Subscriptions lifecycle & expiry checks
rem   - AI token allowances monthly reset
rem   - WhatsApp billing reminders (H-7, H-3, H-1, H-Day)
rem   - Social Media publishing scheduler (every minute)
rem ==============================================================================
cd /d "%~dp0"
if not exist "storage\logs" mkdir "storage\logs"
php artisan schedule:run >> "storage\logs\scheduler.log" 2>&1
