@echo off
title SIMENAK Dev Server
cd /d "%~dp0.."
echo.
echo  SIMENAK - Development Server
echo  URL: http://localhost:8080/
echo  Tekan Ctrl+C untuk berhenti.
echo.
php spark serve --host localhost --port 8080
