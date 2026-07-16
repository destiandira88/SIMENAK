@echo off
set MYSQL=c:\xampp\mysql\bin\mysql.exe
set DUMP=c:\xampp\htdocs\SIMENAK\docs\backups\recovered_20260705

"%MYSQL%" -u root -e "CREATE DATABASE IF NOT EXISTS laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
"%MYSQL%" -u root laravel < "%DUMP%\laravel.sql"
if errorlevel 1 exit /b 1

"%MYSQL%" -u root -e "CREATE DATABASE IF NOT EXISTS simenak_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
"%MYSQL%" -u root simenak_test < "%DUMP%\simenak_test.sql"
if errorlevel 1 exit /b 1

"%MYSQL%" -u root -e "CREATE DATABASE IF NOT EXISTS `e-commerce-10522088` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
"%MYSQL%" -u root "e-commerce-10522088" < "%DUMP%\e-commerce-10522088.sql"
if errorlevel 1 exit /b 1

echo Import selesai.
exit /b 0
