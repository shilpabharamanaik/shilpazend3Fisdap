@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../codegyre/robo/robo
php "%BIN_TARGET%" %*
