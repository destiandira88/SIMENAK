@echo off
REM Perbaikan cepat MySQL XAMPP jika gagal start (tabel mysql.db rusak).
REM Jalankan sebagai Administrator jika Start dari XAMPP tetap gagal.

set "MYSQL_BIN=C:\xampp\mysql\bin"
set "MYSQL_DATA=C:\xampp\mysql\data\mysql"

if not exist "%MYSQL_BIN%\mysqld.exe" (
    echo MySQL XAMPP tidak ditemukan di %MYSQL_BIN%
    pause
    exit /b 1
)

echo [1/3] Mematikan proses mysqld lama (jika ada)...
taskkill /F /IM mysqld.exe >nul 2>&1
timeout /t 2 /nobreak >nul

echo [2/3] Repair tabel sistem mysql (db, global_priv, proxies_priv)...
cd /d "%MYSQL_BIN%"
aria_chk.exe -r "%MYSQL_DATA%\db"
aria_chk.exe -r "%MYSQL_DATA%\global_priv"
aria_chk.exe -r "%MYSQL_DATA%\proxies_priv"

echo [3/3] Menjalankan MySQL...
start "" /B mysqld.exe --defaults-file="%MYSQL_BIN%\my.ini" --standalone
timeout /t 4 /nobreak >nul

mysql.exe -u root -e "SELECT 'MySQL OK' AS status;" 2>nul
if errorlevel 1 (
    echo.
    echo MySQL masih gagal. Buka C:\xampp\mysql\data\mysql_error.log untuk detail.
    pause
    exit /b 1
)

echo.
echo MySQL berjalan. Buka XAMPP Control Panel lalu pastikan Apache juga Start.
echo SIMENAK: http://localhost/SIMENAK/public/
pause
