<?php
/**
 * Created by PhpStorm.
 * User: 伟
 * Date: 2015/9/2
 * Time: 10:52
 */

namespace Lib;

require_once 'vendor/autoload.php';

use JPush\Model as M;
use JPush\JPushClient;

class Jpush
{
    protected static $configs = null;

    public static function pushMessage($uids, $alert, array $extras = null)
    {   
        $sound = '123456'; //ios 极光推送 需要声音文件 该字符串可以为任意不存在字符串
        $config = self::getConfig();
        $app_key = $config['app_key'];
        $master_secret = $config['master_secret'];
        if (!$app_key || !$master_secret) {
            return;
        }
        $client = new JPushClient($app_key, $master_secret);
        try {
            $uids = array_values(array_map('strval', $uids));
            $audience = M\audience(M\alias($uids));
            $notification = M\notification($alert,
                M\android($alert, null, null, $extras),
                M\ios($alert, $sound, null, null, $extras)
            );
            $options = M\options(1, null, null, true, null);
            $result = $client->push()
                ->setPlatform(M\all) //全部平台
                ->setAudience($audience) // 指定用户
                ->setNotification($notification)
                ->setOptions($options)
                ->send();
            echo 'Push Success.'.PHP_EOL;
            echo 'sendno : '.$result->sendno.PHP_EOL;
            echo 'msg_id : '.$result->msg_id.PHP_EOL;
            echo 'Response JSON : '.$result->json.PHP_EOL;
        } catch (\Exception $e) {
            echo 'Push Fail: '.$e->getMessage().PHP_EOL;
        }
    }

    public static function getConfig()
    {
        $time = time();
        if (empty(self::$configs) || self::$configs['mtime'] + 1800 < $time) {
            self::$configs = array();
            $db = \Lib\Message::db();
            $table = \Lib\Message::table('system_data');
            $sql = "SELECT `value` FROM `{$table}` WHERE `key`='jpush'";
            $value = @unserialize($db->single($sql));
            if ($value) {
                self::$configs['app_key'] = $value['key'];
                self::$configs['master_secret'] = $value['secret'];
                self::$configs['mtime'] = $time;
            }
        }

        return self::$configs;
    }
}
