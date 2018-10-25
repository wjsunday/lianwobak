<?php

defined('SITE_PATH') || exit('Forbidden');
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Common.class.php';
use Apps\Information\Common;

spl_autoload_register(function ($namespace) {
    return Common::autoLoader($namespace);
});
