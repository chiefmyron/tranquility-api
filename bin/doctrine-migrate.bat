@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/cli-db-migration.php
php "%BIN_TARGET%" %*