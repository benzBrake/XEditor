@echo off
pushd %cd%
cd ..
if exist Plugin.php (
for /F "tokens=3" %%i in ('type Plugin.php ^| findstr @version') do (SET VERSION=%%i)
for /F "tokens=3" %%i in ('type Plugin.php ^| findstr @package') do (SET THEME=%%i)
) else (
for /F "tokens=3" %%i in ('type index.php ^| findstr @version') do (SET VERSION=%%i)
for /F "tokens=3" %%i in ('type index.php ^| findstr @package') do (SET THEME=%%i)
)
if not exist pack (mkdir pack)
for %%I in (.) do set CurrDirName=%%~nxI
SET ARCHIVES=".\%CurrDirName%\pack\%THEME%-%VERSION%-%date:~0,4%%date:~5,2%%date:~8,2%.zip"
if exist %ARCHIVEPATH% (del /s /f /q %ARCHIVEPATH%)
del /s /f /q .\tools\p.tmp
for /f "tokens=* delims=" %%i in (.\tools\pack.exclude) do echo %CurrDirName%\%%i>>.\tools\p.tmp
cd ..
SET ZIP="C:\Program Files\7-Zip\7z.exe"
%ZIP% a -tzip -r -x@.\%CurrDirName%\tools\p.tmp -spf %ARCHIVES% %CurrDirName%
del /s /f /q .\%CurrDirName%\tools\p.tmp
popd