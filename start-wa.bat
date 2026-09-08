@echo off
title COOCA WhatsApp Gateway Microservice
echo ===================================================
echo   Starting COOCA WhatsApp Gateway (Port 3000)...
echo ===================================================
cd /d "%~dp0wa-server"
if not exist node_modules (
    echo Installing dependencies...
    npm install
)
npm start
pause
