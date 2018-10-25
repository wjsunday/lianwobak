<?php

defined('SITE_PATH') || exit('Forbidden');

$sqlFilePath = APPS_PATH.'/Gift/Appinfo/uninstall.sql';
D()->executeSqlFile($sqlFilePath);
