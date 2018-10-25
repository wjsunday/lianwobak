<?php

defined('SITE_PATH') || exit('Forbidden');

$sqlFilePath = APPS_PATH.'/Event/Appinfo/uninstall.sql';
D()->executeSqlFile($sqlFilePath);
