@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../vendor/robmorgan/phinx/bin/phinx
SET CONFIG_TARGET=%~dp0/cli-bootstrap-phinx.php
php "%BIN_TARGET%" rollback -e environment -c "%CONFIG_TARGET%" %*