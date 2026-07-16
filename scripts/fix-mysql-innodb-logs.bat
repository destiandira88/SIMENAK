@echo off
setlocal EnableExtensions EnableDelayedExpansion
title SIMENAK - Perbaiki MySQL InnoDB (WAJIB Run as Administrator)

echo.
echo ============================================================
echo  PERBAIKAN MySQL - Log InnoDB tidak sinkron
echo ============================================================
echo.

:: --- cek MySQL masih jalan ---
tasklist /FI "IMAGENAME eq mysqld.exe" 2>nul | find /I "mysqld.exe" >nul
if !ERRORLEVEL!==0 (
    echo [ERROR] MySQL masih berjalan!
    echo         Buka XAMPP - klik STOP pada MySQL - jalankan script ini lagi.
    echo.
    pause
    exit /b 1
)

set "DATA=C:\xampp\mysql\data"
for /f "tokens=1-4 delims=/ " %%a in ('date /t') do set "TGL=%%d%%c%%b"
for /f "tokens=1-2 delims=: " %%a in ('time /t') do set "JAM=%%a%%b"
set "JAM=!JAM: =0!"
set "BACKUP=%DATA%\backup_innodb_!TGL!_!JAM!"

echo [1/5] Backup file InnoDB...
if not exist "!BACKUP!" mkdir "!BACKUP!"
copy /Y "!DATA!\ib_logfile0" "!BACKUP!\" >nul
copy /Y "!DATA!\ib_logfile1" "!BACKUP!\" >nul
copy /Y "!DATA!\ibdata1" "!BACKUP!\" >nul
echo       OK - disimpan di:
echo       !BACKUP!
echo.

echo [2/5] Hapus ib_logfile0 dan ib_logfile1 (KEDUANYA)...
if exist "!DATA!\ib_logfile0" del /F /Q "!DATA!\ib_logfile0"
if exist "!DATA!\ib_logfile1" del /F /Q "!DATA!\ib_logfile1"
if exist "!DATA!\ib_logfile0" (
    echo [ERROR] Gagal hapus ib_logfile0. Jalankan script as Administrator.
    pause
    exit /b 1
)
if exist "!DATA!\ib_logfile1" (
    echo [ERROR] Gagal hapus ib_logfile1. Jalankan script as Administrator.
    pause
    exit /b 1
)
echo       OK - log lama sudah dihapus.
echo.

echo [3/5] Cek innodb_force_recovery=3 di my.ini...
findstr /C:"innodb_force_recovery=3" "C:\xampp\mysql\bin\my.ini" >nul
if !ERRORLEVEL! neq 0 (
    echo [WARN] Tambahkan baris ini di C:\xampp\mysql\bin\my.ini
    echo        innodb_force_recovery=3
) else (
    echo       OK
)
echo.

echo [4/5] Start MySQL...
start "" /B "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini"
timeout /t 10 /nobreak >nul
echo.

echo [5/5] Tes koneksi...
"C:\xampp\mysql\bin\mysqladmin.exe" -u root ping 2>nul
if !ERRORLEVEL!==0 (
    echo.
    echo ============================================================
    echo  BERHASIL! MySQL sudah jalan.
    echo ============================================================
    echo.
    echo Langkah berikutnya:
    echo   1. Buka http://localhost/phpmyadmin
    echo   2. Export database simenak_db SEGERA
    echo   3. Buka XAMPP - MySQL seharusnya hijau
    echo.
) else (
    echo.
    echo ============================================================
    echo  BELUM BERHASIL - coba start manual dari XAMPP
    echo ============================================================
    echo.
    echo Jika masih merah, buka file ini dan kirim 20 baris terakhir:
    echo   C:\xampp\mysql\data\mysql_error.log
    echo.
    echo Atau coba recovery level 4:
    echo   Ubah my.ini: innodb_force_recovery=4
    echo   Ulangi script ini
    echo.
)

pause
