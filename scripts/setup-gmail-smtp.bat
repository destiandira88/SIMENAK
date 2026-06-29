@echo off
setlocal EnableDelayedExpansion
title Setup Gmail SMTP - SIMENAK
cd /d "%~dp0.."

echo.
echo  ========================================
echo   Setup Gmail SMTP untuk SIMENAK
echo  ========================================
echo.
echo  Butuh Gmail App Password (bukan password login biasa):
echo  Google Account -^> Keamanan -^> Verifikasi 2 langkah -^> Kata sandi aplikasi
echo.

set /p GMAIL_USER=Email Gmail: 
set /p GMAIL_PASS=App Password (16 karakter): 

if "!GMAIL_USER!"=="" (
    echo Email wajib diisi.
    pause
    exit /b 1
)
if "!GMAIL_PASS!"=="" (
    echo App Password wajib diisi.
    pause
    exit /b 1
)

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0setup-gmail-smtp.ps1" -GmailUser "!GMAIL_USER!" -GmailPass "!GMAIL_PASS!"
if errorlevel 1 (
    echo.
    echo  Gagal menyimpan ke .env. Coba isi manual di file .env:
    echo    email.SMTPUser = email-kamu@gmail.com
    echo    email.SMTPPass = app-password-16-karakter
    echo    email.fromEmail = email-kamu@gmail.com
    pause
    exit /b 1
)

echo.
echo  Selesai. Restart Apache / spark serve lalu uji: php spark test-email !GMAIL_USER!
echo.
pause
