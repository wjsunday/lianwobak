<?php

defined('SITE_PATH') || exit('Forbidden');

use Apps\Bonusevent\Common;

include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Common.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'BaseController.php';

spl_autoload_register(function ($namespace) {
    return Common::autoLoader($namespace);
});
