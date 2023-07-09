@echo off
echo.
echo ===========================================================================
echo Compiling graphics
echo ===========================================================================
..\..\php5\php.exe -c ..\..\php5\ -f convert_spr.php
if %ERRORLEVEL% NEQ 0 ( exit /b )
..\..\php5\php.exe -c ..\..\php5\ -f convert_bgr.php
if %ERRORLEVEL% NEQ 0 ( exit /b )

echo.
echo ===========================================================================
echo Compiling PPU.MAC
echo ===========================================================================
..\..\php5\php.exe -c ..\..\php5\ -f preprocess.php ppu.mac
if %ERRORLEVEL% NEQ 0 ( exit /b )
..\..\macro11\macro11.exe -ysl 32 -yus -m ..\..\macro11\sysmac.sml -l _ppu.lst _ppu.mac
if %ERRORLEVEL% NEQ 0 ( exit /b )

echo.
echo ===========================================================================
echo Creating PPU data block
echo ===========================================================================
..\..\php5\php.exe -c ..\..\php5\ -f gen_overlay.php
if %ERRORLEVEL% NEQ 0 ( exit /b )

echo.
echo ===========================================================================
echo Compiling CPU.MAC
echo ===========================================================================
..\..\php5\php.exe -c ..\..\php5\ -f preprocess.php cpu.mac
if %ERRORLEVEL% NEQ 0 ( exit /b )
copy _cpu.mac+_ppu_bytes.mac _cpu.mac >NUL
..\..\macro11\macro11.exe -ysl 32 -yus -m ..\..\macro11\sysmac.sml -l _cpu.lst -o column.obj _cpu.mac
if %ERRORLEVEL% NEQ 0 ( exit /b )

echo.
echo ===========================================================================
echo Linking
echo ===========================================================================
.\rt11.exe link column
..\..\macro11\rt11dsk.exe d column.dsk column.sav >NUL
..\..\macro11\rt11dsk.exe a column.dsk column.sav >NUL
move /y column.sav release\column.sav >NUL

echo.