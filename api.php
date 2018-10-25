<?php

//默认应用设置为API
$_GET['app'] = 'api';

define('APP_NAME', 'api');
$api_version = !empty($_REQUEST['api_version']) ? $_REQUEST['api_version'] : '4.5.0';
$api_type = !empty($_REQUEST['api_type']) ? $_REQUEST['api_type'] : 'sociax';
define('API_VERSION', $api_type.'_v'.$api_version);
$lanArr = array("en","zh-cn");
$lang = empty($_REQUEST['lang'])?'cn':$_REQUEST['lang'];
if(in_array($lang,$lanArr))
{
	$_lang = require dirname(__FILE__)."/data/lang/public_{$lang}.php";
	$GLOBALS['_lang'] = $_lang;
}

require dirname(__FILE__).'/src/bootstrap.php';
Api::run();
