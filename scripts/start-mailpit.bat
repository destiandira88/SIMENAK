@echo off
setlocal
title SIMENAK Mailpit
set "ROOT=%~dp0.."
set "TOOLS=%ROOT%\tools\mailpit"
set "EXE=%TOOLS%\mailpit.exe"

if not exist "%TOOLS%" mkdir "%TOOLS%"

if not exist "%EXE%" (
    echo.
    echo  Mengunduh Mailpit ^(SMTP lokal^)...
    echo.
    powershell -NoProfile -ExecutionPolicy Bypass -Command ^
        "$zip = Join-Path $env:TEMP 'mailpit.zip';" ^
        "Invoke-WebRequest -Uri 'https://github.com/axllent/mailpit/releases/latest/download/mailpit-windows-amd64.zip' -OutFile $zip -UseBasicParsing;" ^
        "Expand-Archive -Path $zip -DestinationPath '%TOOLS%' -Force;" ^
        "Remove-Item $zip -Force;" ^
        "Get-ChildItem '%TOOLS%' -Filter 'mailpit*.exe' | ForEach-Object { if ($_.Name -ne 'mailpit.exe') { Move-Item $_.FullName '%EXE%' -Force } elseif (-not (Test-Path '%EXE%')) { Copy-Item $_.FullName '%EXE%' } }"
    if errorlevel 1 (
        echo Gagal mengunduh Mailpit. Cek koneksi internet lalu coba lagi.
        pause
        exit /b 1
    )
)

echo.
echo  Mailpit aktif:
echo    SMTP  : 127.0.0.1:1025  ^(sudah di .env^)
echo    Inbox : http://localhost:8025
echo.
echo  Biarkan jendela ini terbuka. Tekan Ctrl+C untuk berhenti.
echo.

"%EXE%"
