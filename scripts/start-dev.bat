@echo off
title SIMENAK Dev (Mailpit + Server)
cd /d "%~dp0.."
start "SIMENAK Mailpit" cmd /k "%~dp0start-mailpit.bat"
timeout /t 3 /nobreak >nul
start "SIMENAK Server" cmd /k "%~dp0start-dev-server.bat"
echo.
echo  Dua jendela dibuka: Mailpit + PHP server.
echo  Inbox email: http://localhost:8025
echo.
