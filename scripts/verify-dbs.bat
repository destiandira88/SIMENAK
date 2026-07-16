@echo off
c:\xampp\mysql\bin\mysql.exe -u root -e "USE laravel; SELECT COUNT(*) AS produk FROM produk;"
c:\xampp\mysql\bin\mysql.exe -u root -e "USE simenak_test; SELECT COUNT(*) AS orders FROM orders; SELECT COUNT(*) AS katalog FROM katalog;"
c:\xampp\mysql\bin\mysql.exe -u root -e "USE `e-commerce-10522088`; SELECT COUNT(*) AS produk FROM produk; SELECT COUNT(*) AS pelanggan FROM pelanggan; SELECT COUNT(*) AS kategori FROM kategori;"
