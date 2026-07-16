@echo off
REM Task Scheduler SIMENAK — reminder verifikasi DP/pelunasan + cek deadline upload DP
REM Sesuaikan path PHP/XAMPP jika instalasi berbeda.

set "PHP=C:\xampp\php\php.exe"
set "ROOT=C:\xampp\htdocs\SIMENAK"
set "LOG=%ROOT%\writable\logs\scheduler.log"

if not exist "%PHP%" (
    echo PHP tidak ditemukan: %PHP%
    exit /b 1
)

cd /d "%ROOT%"

echo.>> "%LOG%"
echo [%date% %time%] === SIMENAK scheduler start ===>> "%LOG%"

"%PHP%" spark reminder-verifikasi-dp >> "%LOG%" 2>&1
if errorlevel 1 (
    echo [%date% %time%] reminder-verifikasi-dp FAILED>> "%LOG%"
)

"%PHP%" spark reminder-verifikasi-pelunasan >> "%LOG%" 2>&1
if errorlevel 1 (
    echo [%date% %time%] reminder-verifikasi-pelunasan FAILED>> "%LOG%"
)

"%PHP%" spark cek-dp-deadline >> "%LOG%" 2>&1
if errorlevel 1 (
    echo [%date% %time%] cek-dp-deadline FAILED>> "%LOG%"
)

"%PHP%" spark reminder-konfirmasi-harga-custom >> "%LOG%" 2>&1
if errorlevel 1 (
    echo [%date% %time%] reminder-konfirmasi-harga-custom FAILED>> "%LOG%"
)

echo [%date% %time%] === SIMENAK scheduler end ===>> "%LOG%"

exit /b 0
