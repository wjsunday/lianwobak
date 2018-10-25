<?php

namespace Apps\Bonusevent;

defined('SITE_PATH') || exit('Forbidden');
/**
 * 公用库
 *
 * @package Apps\Bonusevent\Common
 * @author Seven Du <lovevipdsw@vip.qq.com>
 **/
class Common
{
    /**
     * 文件加载列表
     *
     * @var array
     **/
    protected static $_includes = array();

    /**
     * 加载的文件对象列表
     *
     * @var array
     **/
    protected static $_loads = array();

    /**
     * 加载文件
     *
     * @param  string $filePath 文件地址
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public static function import($filePath)
    {
        $filePath = str_replace('\\', DIRECTORY_SEPARATOR, $filePath);
        $filePath = str_replace('/', DIRECTORY_SEPARATOR, $filePath);

        if (in_array($filePath, self::$_includes)) {
            return true;
        } elseif (file_exists($filePath)) {
            array_push(self::$_includes, $filePath);

            return include $filePath;
        }

        return false;
    }

    /**
     * 自适应加载
     * 目前只有模型有需求
     *
     * @param  string $namespace 命名空间
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public static function autoLoader($namespace)
    {
        if (strpos($namespace, '\\')) {
            $name = explode('\\', $namespace);
            $name = array_pop($name);
        }
        // var_dump($name);exit;
        return self::import(APPS_PATH.'/Bonusevent/Lib/Model/'.$name.'Model.class.php');
    }

    /**
     * 根据命名空间加载并返回实例
     *
     * @param  string $namespace 命名空间
     * @return object
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public static function load($namespace)
    {
        if (self::$_loads[$namespace]) {
            return self::$_loads[$namespace];
        } elseif (self::autoLoader($namespace)) {
            self::$_loads[$namespace] = new $namespace;

            return self::$_loads[$namespace];
        }

        return false;
    }

    /**
     * 设置头部文本输出类型
     *
     * @param string $type    设置文本类型
     * @param string $charset 设置字符集
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function setHeader($type = 'text/html', $charset = 'utf-8')
    {
        header('Content-type:'.$type.';charset='.$charset);
        header('Cache-control: private');
    }

    /**
     * 获取表单数据
     *
     * @param string|array $name 数组或者键名，不传则返回所有数据
     * @param string 获取的类型 默认只有request|get|post三种
     * @return data 返回的数据
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public static function getInput($name = null, $method = 'request')
    {
        $method = strtolower($method);
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $name[$key] = self::getInput($value, $method);
            }

            return $name;
        } elseif (!$name && $method == 'get') {
            return $_GET;
        } elseif (!$name && $method == 'post') {
            return $_POST;
        } elseif (!$name && $method == 'request') {
            return $_REQUEST;
        } elseif ($method == 'get' && isset($_GET[$name])) {
            return $_GET[$name];
        } elseif ($method == 'post' && isset($_POST[$name])) {
            return $_POST[$name];
        } elseif (isset($_REQUEST[$name])) {
            return $_REQUEST[$name];
        }

        return null;
    }

    /**
     * 求取字符串位数（非字节），以UTF-8编码长度计算
     *
     * @param  string $string 需要被计算位数的字符串
     * @return int
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public static function strlen($string)
    {
        $length = strlen($string);
        $index = $num = 0;
        while ($index < $length) {
            $str = $string[$index];
            if ($str < "\xC0") {
                $index += 1;
            } elseif ($str < "\xE0") {
                $index += 2;
            } elseif ($str < "\xF0") {
                $index += 3;
            } elseif ($str < "\xF8") {
                $index += 4;
            } elseif ($str < "\xFC") {
                $index += 5;
            } else {
                $index += 6;
            }
            $num += 1;
        }

        return $num;
    }

    /**
     * 更具表达式返回内容
     *
     * @param  PHP condition $condition PHP条件
     * @param  unknow        $yes       表达式成立返回内容
     * @param  unknow        $no        表达式不成立返回内容
     * @return unkonw
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public static function hasEcho($condition, $yes = '', $no = '')
    {
        return $condition ? $yes : $no;
    }

    /**
     * 发送通知
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public static function sendMessage($uid, $content, $formUid = 0)
    {
        $message = D('notify_message');

        $messageData = array();
        $messageData['uid'] = $uid;
        $messageData['from_uid'] = $formUid;
        $messageData['appname'] = 'Event';
        $messageData['title'] = '活动通知';
        $messageData['ctime'] = time();
        $messageData['is_read'] = '0';
        $messageData['body'] = $content;

        return $message->add($messageData);
    }
} // END class Common
