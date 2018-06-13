@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../vendor/robmorgan/phinx/bin/phinx
SET CONFIG_TARGET=%~dp0/../resources/database/config.php
php "%BIN_TARGET%" migrate -e environment -c "%CONFIG_TARGET%" %*
