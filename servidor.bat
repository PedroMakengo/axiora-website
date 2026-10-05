@echo off
REM Arranca o site no servidor embutido do PHP (sem depender do Apache).
REM Basta fazer duplo-clique neste ficheiro e abrir http://localhost:8000
setlocal
set PHP_EXE=C:\xampp\php\php.exe
if not exist "%PHP_EXE%" set PHP_EXE=php
echo.
echo   Axiora - Site + Blog + Painel
echo   Servidor local em http://localhost:8000  (Ctrl+C para parar)
echo.
"%PHP_EXE%" -S localhost:8000 router.php
