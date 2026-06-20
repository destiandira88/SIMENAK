@echo off
setlocal
cd /d "%~dp0\.."

if not exist "vendor\bin\phpunit" (
    echo [SIMENAK] Menginstall dev dependencies...
    call composer install --no-interaction
    if errorlevel 1 exit /b 1
)

echo [SIMENAK] Memastikan database uji simenak_test ada...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS simenak_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
if errorlevel 1 (
    echo [PERINGATAN] Gagal membuat simenak_test otomatis. Pastikan MySQL/XAMPP aktif dan jalankan:
    echo   CREATE DATABASE simenak_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
)

echo [SIMENAK] Menjalankan automation tests...
php vendor\bin\phpunit %*
exit /b %ERRORLEVEL%
