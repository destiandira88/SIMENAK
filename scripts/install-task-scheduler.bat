@echo off
REM Jalankan file ini sebagai Administrator (klik kanan > Run as administrator)
REM Mendaftarkan task jam-an: reminder DP/pelunasan, deadline DP, harga custom, cleanup activity logs

set "TASK=SIMENAK Spark Tasks"
set "BAT=C:\xampp\htdocs\SIMENAK\scripts\run-simenak-scheduler.bat"

echo Mendaftarkan Task Scheduler: %TASK%
echo Script: %BAT%
echo.

schtasks /Create /F /TN "%TASK%" /TR "\"%BAT%\"" /SC HOURLY /MO 1 /ST 00:05 /RL HIGHEST

if errorlevel 1 (
    echo.
    echo GAGAL — pastikan dijalankan sebagai Administrator.
    pause
    exit /b 1
)

echo.
echo Berhasil. Task akan jalan setiap jam (mulai 00:05).
echo Cek log: C:\xampp\htdocs\SIMENAK\writable\logs\scheduler.log
echo.
schtasks /Query /TN "%TASK%" /FO LIST /V
echo.
pause
