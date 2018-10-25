<?php
/*
 * OpenSociax 核心入口文件
 * @author  liuxiaoqing <liuxiaoqing@zhishisoft.com>
 * @version ThinkSNS v4.
 */

@ini_set('magic_quotes_runtime', 0);

$time_include_start = microtime(true);
$mem_include_start = memory_get_usage();

//设置全局变量ts
$ts['_debug'] = false;        //调试模式
$ts['_define'] = array();    //全局常量
$ts['_config'] = array();    //全局配置
$ts['_access'] = array();    //访问配置
$ts['_router'] = array();    //路由配置

tsdefine('IS_CGI', substr(PHP_SAPI, 0, 3) == 'cgi' ? 1 : 0);
tsdefine('IS_WIN', strstr(PHP_OS, 'WIN') ? 1 : 0);
tsdefine('IS_HTTPS', 0);

// # 设置API版本常量
if (isset($_REQUEST['api_version'])) {
    $apiVersion = $_REQUEST['api_version'];
    $apiVersion = preg_replace('/[^a-zA-Z0-9_\.\-]/is', '', $apiVersion);
    tsdefine('API_VERSION', $apiVersion);
    unset($apiVersion);
} else {
    tsdefine('API_VERSION', 'thinksns');
}

// 当前文件名
if (!defined('_PHP_FILE_')) {
    if (IS_CGI) {
        // CGI/FASTCGI模式下
        $_temp = explode('.php', $_SERVER['PHP_SELF']);
        define('_PHP_FILE_', rtrim(str_replace($_SERVER['HTTP_HOST'], '', $_temp[0].'.php'), '/'));
    } else {
        define('_PHP_FILE_', rtrim($_SERVER['SCRIPT_NAME'], '/'));
    }
}

// 网站URL根目录
if (!defined('__ROOT__')) {
    $_root = dirname(_PHP_FILE_);
    define('__ROOT__', (($_root == '/' || $_root == '\\') ? '' : rtrim($_root, '/')));
}

//基本常量定义
tsdefine('ROOT_FILE', basename(_PHP_FILE_) == 'api.php' ? 'index.php' : basename(_PHP_FILE_));
tsdefine('CORE_PATH', dirname(__FILE__));

tsdefine('SITE_URL', (IS_HTTPS ? 'https:' : 'http:').'//'.strip_tags($_SERVER['HTTP_HOST']).__ROOT__);

tsdefine('CONF_PATH', SITE_PATH.'/config');

tsdefine('APPS_PATH', SITE_PATH.'/apps');
tsdefine('APPS_URL', SITE_URL.'/apps');    // 应用内部图标 等元素

tsdefine('ADDON_PATH', dirname(__FILE__).'/addons');

tsdefine('DATA_PATH', SITE_PATH.'/data');
tsdefine('DATA_URL', SITE_URL.'/data');

tsdefine('UPLOAD_PATH', SITE_PATH.'/data/upload');
tsdefine('UPLOAD_URL', SITE_URL.'/data/upload');

tsdefine('PUBLIC_PATH', SITE_PATH.'/public');
tsdefine('PUBLIC_URL', SITE_URL.'/public');

tsdefine('CORE_RUN_PATH', TS_ROOT.TS_STORAGE.'/temp');
tsdefine('LOG_PATH', TS_ROOT.TS_STORAGE.'/logs/');

/**
 * 支付宝信息
 */
tsdefine('ALI_PAY_APP_ID',               '2017032406389125');
tsdefine('ALI_PAY_MERCHANT_PRIVATE_KEY', 'MIIEpAIBAAKCAQEA3gC/UbiSzUkzTSv2O6jqTCgOgrt2oqTmr9a3wP9HFq2dz30Jjs8g71TEvC0q3sYS48r0it2EzEnF7rZul9yV6RT9jHsZl1gTJxskealyYP1ls3dUaztmRyQxUKM59iXNJkedmZHk1oRtMl1JVAhL7QFUfVkz6m6bCvrwJMiC4u8xXygQI20ij1Zn5PHv011r2adRbZvZZywBE2huG5QLmaAIlAfvPi890kI0J4zR0zCeJsM0kTflz86m4g+oSbUGFf/tc6YxntNy2AGj0zj72seROtLxsjrfpLpEFHluBY0LSfFjwMlM4PX0NTCMjtdTDPTgR8uFuCaYGyLFMaH+gQIDAQABAoIBAEVfGGmx8nWhnR1NUnlcFDLOBZLGf5DdVJS31N3DJ3SntfuDR/xPLyx6YauntZC9dmOmJPpLtFad6vLoIPDlZhbaj0xZnyqSxBFhQumdkHdMV35YGJIVkqVCYwKertsC/vMqR84YL9j0sqadTe+rVAZwdvVgbX+hJmOs/GOSIIxGuy0JvB6ZKWo+YbJu4G9t5GIxDv7TQ3iVeVLCrr8a3rPA20EHyrPSm9HkB4NaqwqMjWewDn45+FodOTvuTlmIo++t/c7t6pzjWm7x4hmsh7xo3ujk30djmJOGHWovUDCkOWrI3nCVorsPqTUxmr+XBv2VD92ZWs/9Ggj/LDzUfi0CgYEA9MMedAZQbgL1gDkfXj4GCX1Ci9n/OP80UrPKLdm9wBgtqnS/TLQa0F3lbRD0D0i1qqh7XLH6ED/+CoR/fAn6Qp93nsVSZbMXF6pW68ga45OfwZIyZaaWc7+zUzBH6nwVG31zA2JNbn3vukZF2s7b7vE5F9zY9+BW4xohSENnbnMCgYEA6DIe+K00AZjYHI0TjGqymoAHAJsfF30k7Ak9JwVmxFQpnTBvGkZo/HSNQzO4i0Xk/pDZzFFkLco/f9ZP9CXw3291QBTMW7FbvCRTMGejojxFu8+IIZZGjghFK3cqnw0oQdCwtq4cn2M0+agtnF8XsprDmkhBak/eSE9VAGypzjsCgYAawWmGzVnlbLVhet0O9EC8sNChcQcRN/SERdsIABFZ+kqjo3d17wovVNRLg9rqAMy7BwfVLi9jr1sZ0zoMWRreRYhR9sf3DgisMZam14z4654/DrgrnHY92OrFYTW7UC0OgDweVpSYS3P1LNFeBdbM29md3ippjkBxOt4AhD2+OQKBgQCWFl/CqmJlbeCzg9VQ/F76UyszjxFn43KMR+SJZopo+WX0vyvxtfWCyevNto7hJ4q/qe2+jhXNK2AkR+lijWvHYYpOo6L82gXL9Ub4kOVKpt8EaT87RqHQiTP8xoh8Ssrsz/KKAr1poXzKq5Tf8O+meTdacL4n8/xgjTcKkTKppQKBgQC17bfzuYIT9lLovP982iHWtMbB6PKuNNKRjoy22vG9y7cZt7pVy46W4Hji/GWAdLG34QVqmjKH3Y/szQhuOSwFqdltJO+G7tJe5YeusY3T1qBjmGph0q0Yak37rQb47NiVxA7dq1dlPr2UdetHeN1FEoUdX1dSy6NJb6gDdVnUwA=='); //商户应用私钥
tsdefine('ALI_PAY_ALIPAY_PUBLIC_KEY',    'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsviZpx1OZ7pXoMBxl9z73Byi8fKiuAQBahhWXBhAMp1oNurPeKjik/rp38NFAjW6VzH4hoOQsPGfd4asigrPSwQ0WNcSLpog3bqlVatAP64gKRXKJj3dMmc+uGElabZZhBUi2t98WiNeW+VK/Mx5QO0d5WE9ArBEDzGJx9Mp2qNBDNZpciCVWaRvmDPjvzzw0l2iLrGusOjLkiONJD/fHJKmf4TAHhx06gMexbjxIKhp2/JJsV0zR3Fs185GulMg+FULTMamYeD8bZ7h22irDBSS3D3/K+UGCaL8YKY0dr3AGNlKFbXg7l7Om9Z9Bp8Djf6RAihYvqkWl41hWiJ7owIDAQAB'); //支付宝公钥(RSA1withRSA)
tsdefine('ALI_PAY_GATE_WAY_URL',         'https://openapi.alipay.com/gateway.do');
tsdefine('ALI_PAY_CHARSET',              'UTF-8');
tsdefine('ALI_PAY_SIGN_TYPE',            'RSA2');
tsdefine('JG_APP_KEY',                   'c39c471a5eccc7cea90482ce');
tsdefine('JG_MASTER_SECRET',             'a67a6a668c49847f36c41d34');

//注册AUTOLOAD方法
if (function_exists('spl_autoload_register')) {
    spl_autoload_register(function ($classname) {
        tsautoload($classname);
    });
}

tsdefine('NOW_TIME', $_SERVER['REQUEST_TIME']);
tsdefine('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
tsdefine('IS_GET', REQUEST_METHOD == 'GET' ? true : false);
tsdefine('IS_POST', REQUEST_METHOD == 'POST' ? true : false);

/* 核心方法 */

/**
 * 载入文件 去重\缓存.
 *
 * @param  string $filename 载入的文件名
 *
 * @return bool
 */
function tsload($filename)
{
    return Ts::import($filename, '');
}

/**
 * 系统自动加载函数.
 *
 * @param string $classname 对象类名
 */
function tsautoload($classname)
{
    // 检查是否存在别名定义
    if (tsload($classname)) {
        return;
    }

    // 自动加载当前项目的Actioon类和Model类
    if (substr($classname, -5) == 'Model') {
        tsload(APP_LIB_PATH.'/Model/'.$classname.'.class.php');
    } elseif (substr($classname, -6) == 'Action') {
        tsload(APP_LIB_PATH.'/Action/'.$classname.'.class.php');
    } elseif (substr($classname, -6) == 'Widget') {
        $filename = sprintf('%s/Widget/%s/%s.class.php', APP_LIB_PATH, $classname, $classname);
        tsload($filename);
    } elseif (substr($classname, -6) == 'Addons') {
        tsload(APP_LIB_PATH.'/Plugin/'.$classname.'.class.php');
    }
}

/**
 * 定义常量,判断是否未定义.
 *
 * @param string $name  常量名
 * @param string $value 常量值
 *
 * @return string $str 返回常量的值
 */
function tsdefine($name, $value)
{
    global $ts;
    //定义未定义的常量
    if (!defined($name)) {
        //定义新常量
        define($name, $value);
    } else {
        //返回已定义的值
        $value = constant($name);
    }
    //缓存已定义常量列表
    $ts['_define'][$name] = $value;

    return $value;
}

/**
 * 返回16位md5值
 *
 * @param string $str 字符串
 *
 * @return string $str 返回16位的字符串
 */
function tsmd5($str)
{
    return substr(md5($str), 8, 16);
}

/**
 * 载入配置 修改自ThinkPHP:C函数 为了不与tp冲突
 *
 * @param  string              $name  配置名/文件名.
 * @param  string|array|object $value 配置赋值
 *
 * @return void|null
 */
function tsconfig($name = null, $value = null)
{
    global $ts;
    // 无参数时获取所有
    if (empty($name)) {
        return $ts['_config'];
    }
    if (!isset($ts['_config'])) {
        $ts['_config'] = array();
    }
    // 优先执行设置获取或赋值
    if (is_string($name)) {
        if (!strpos($name, '.')) {
            $name = strtolower($name);
            if (is_null($value)) {
                return isset($ts['_config'][$name]) ? $ts['_config'][$name] : null;
            }
            $ts['_config'][$name] = $value;

            return;
        }
        // 二维数组设置和获取支持
        $name = explode('.', $name);
        $name[0] = strtolower($name[0]);
        if (is_null($value)) {
            return isset($ts['_config'][$name[0]][$name[1]]) ? $ts['_config'][$name[0]][$name[1]] : null;
        }
        $ts['_config'][$name[0]][$name[1]] = $value;

        return;
    }
    // 批量设置
    if (is_array($name)) {
        return $ts['_config'] = array_merge((array) $ts['_config'], array_change_key_case($name));
    }

     // 避免非法参数
}

/**
 * 执行钩子方法.
 *
 * @param string $name   钩子方法名.
 * @param array  $params 钩子参数数组.
 *
 * @return array|string Stripped array (or string in the callback).
 */
function tshook($name, $params = array())
{
    global $ts;
    $hooks = isset($ts['_config']['hooks'][$name]) ? $ts['_config']['hooks'][$name] : array();
    if ($hooks) {
        foreach ($hooks as $call) {
            if (is_callable($call)) {
                $result = call_user_func_array($call, $params);
            }
        }

        return $result;
    }

    return false;
}

/**
 * Navigates through an array and removes slashes from the values.
 *
 * If an array is passed, the array_map() function causes a callback to pass the
 * value back to the function. The slashes from this value will removed.
 *
 * @param array|string $value The array or string to be striped.
 *
 * @return array|string Stripped array (or string in the callback).
 */
function stripslashes_deep($value)
{
    if (is_array($value)) {
        $value = array_map('stripslashes_deep', $value);
    } elseif (is_object($value)) {
        $vars = get_object_vars($value);
        foreach ($vars as $key => $data) {
            $value->{$key} = stripslashes_deep($data);
        }
    } else {
        $value = stripslashes($value);
    }

    return $value;
}

/**
 * GPC参数过滤.
 *
 * @param array|string $value The array or string to be striped.
 *
 * @return array|string Stripped array (or string in the callback).
 */
function check_gpc($value = array())
{
    if (!is_array($value)) {
        return;
    }
    foreach ($value as $key => $data) {
        //对get、post的key值做限制,只允许数字、字母、及部分符号_-[]#@~
        // if(!preg_match('/^[a-zA-Z0-9,_\;\-\/\*\.#!@~\[\]]+$/i',$key)){
        // 	die('wrong_parameter: not safe get/post/cookie key.');
        // }
        //如果key值为app\mod\act,value只允许数字、字母下划线
        if (($key == 'app' || $key == 'mod' || $key == 'act') && !empty($data)) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/i', $data)) {
                die('wrong_parameter: not safe app/mod/act value.');
            }
        }
    }
}

//全站静态缓存,替换之前每个model类中使用的静态缓存
//类似于s和f函数的使用
function static_cache($cache_id, $value = null, $clean = false)
{
    static $cacheHash = array();
    if ($clean) { //清空缓存 其实是清不了的 程序执行结束才会自动清理
        unset($cacheHash);
        $cacheHash = array(0);

        return $cacheHash;
    }
    if (empty($cache_id)) {
        return false;
    }
    if ($value === null) {
        //获取缓存数据
        return isset($cacheHash[$cache_id]) ? $cacheHash[$cache_id] : false;
    } else {
        //设置缓存数据
        $cacheHash[$cache_id] = $value;

        return $cacheHash[$cache_id];
    }
}

// 载入核心运行时文件
include dirname(__FILE__).'/OpenSociax/OpenSociax.php';
