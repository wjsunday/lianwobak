<?php
/**
 * 极光推送模型.
 *
 * @version TS4.5
 * @name JpushModel
 *
 * @author Foreach
 */
class JpushModel extends Model
{
    protected static $client;

    public function __construct()
    {
        $config = D('system_data')->where(array('key' => 'jpush'))->getField('value');
        $config = unserialize($config);
        static::$client = new \JPush\Client(t($config['key']), t($config['secret']));
    }

    /**
     * 推送消息.
     *
     * @param array $uids 用户uid
     * @param string $alert 消息内容
     * @param array $extras
     * @param int   $type
     * @param int   $rose
     *
     * @return array|bool
     */
    public function pushMessage($uids = array(), $alert = '', $extras = array())
    {
        $audience = array('alias' => $uids);
        foreach ($audience['alias'] as $k => $v) {
            $audience['alias'][$k] = (string) $v;
        }
        $audience['alias'] = array_values($audience['alias']);
        // var_dump($audience);exit;
        try {
            $result = static::$client->push()
                ->setPlatform('all')
                ->setNotificationAlert($alert)
                ->addAlias($audience['alias'])
                ->androidNotification($alert, null, null, $extras)
                ->iosNotification($alert, null, null, null, 'iOS category', $extras)
                // ->message($alert, $title, '', $extras)
                ->message($alert,$extras)
                ->options(0, null, null, True, null)//True 表示推送生产环境，False 表示要推送开发环境
                ->send();
                // var_dump($result);exit;

            if ($result == null) {
                return false;
            }

            return $result;
        } catch (\JPush\Exceptions\APIConnectionException $e) {
            return $e;
        } catch (\JPush\Exceptions\APIRequestException $e) {
            return $e;
        }
            
    }

/***
例子
@$extras = array(
        'title' => '通知',
        'content_type' => 'text',
        'extras' => array('key'=>'测试')
    );

*/
    public function message($uids = array(), $alert = '', $extras = array())
    {
        $audience = array('alias' => $uids);
        foreach ($audience['alias'] as $k => $v) {
            $audience['alias'][$k] = (string) $v;
        }
        $audience['alias'] = array_values($audience['alias']);
        // var_dump($audience['alias']);exit;

        try {
            $result = static::$client->push()
            ->setPlatform('all')
            // ->setNotificationAlert($alert)
            ->addAlias($audience['alias'])
            // ->androidNotification($alert, null, null, $extras)
            // ->iosNotification($alert, null, null, null, 'iOS category', $extras)
            ->message($alert,$extras)
            ->options(0, null, null, True, null)//True 表示推送生产环境，False 表示要推送开发环境
            ->send();
            if ($result == null) {
                return false;
            }
            return result;
        } catch (\JPush\Exceptions\APIConnectionException $e) {
            return $e;
        } catch (\JPush\Exceptions\APIRequestException $e) {
            return $e;
        }
        
        
    }

    public function inhotnow()
    {
        $result = static::$client->push()
            ->setPlatform('all')
            ->addAllAudience()
            ->iosNotification('通知', [
              'sound' => 'inhotnews.wav'
            ])
            ->send();

        if ($result == null) {
            return false;
        }

        return $result;
    }

    //支付通知消息
    public function noticeMessage($uid,$title='',$alert='',$serial_number,$type,$money_type,$money,$data_list = array())
    {
        $user = array($uid);
        $extras = array(
                'title' => $title,
                'content_type' => 'text',
                'extras' => array(
                        'push_type' => 'lianwoPay',
                        'uid' => $uid,
                        'serial_number' => $serial_number,
                        'type' => $type,
                        'time' => time(),
                        'money_type' => $money_type,
                        'money' => $money,
                        'data_list' => $data_list,
                    )
            );
        $con = $alert;
        $this->message($user,$con,$extras);
    }

    //认证通知消息
    public function authenticationMessage($uid,$title='',$alert='',$type,$data_list = array())
    {
        $user = array($uid);
        $extras = array(
                'title' => $title,
                'content_type' => 'text',
                'extras' => array(
                        'push_type' => 'lianwoAuthentication',
                        'uid' => $uid,
                        'type' => $type,
                        'time' => time(),
                        'data_list' => $data_list,
                    )
            );
        $con = $alert;
        $this->message($user,$con,$extras);
    }
}
