@echo off
pushd .
cd %~dp0
cd "../vendor/doctrine/orm/bin"
set BIN_TARGET=%CD%\doctrine.php
popd
php "%BIN_TARGET%" %*
