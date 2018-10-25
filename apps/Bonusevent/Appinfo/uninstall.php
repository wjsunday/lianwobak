<?php

defined('SITE_PATH') || exit('Forbidden');

$sqlFilePath = APPS_PATH.'/Bonusevent/Appinfo/uninstall.sql';
D()->executeSqlFile($sqlFilePath);
