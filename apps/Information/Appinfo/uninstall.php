<?php

defined('SITE_PATH') || exit('Forbidden');

$sqlFilePath = APPS_PATH.'/Information/Appinfo/uninstall.sql';
D()->executeSqlFile($sqlFilePath);
