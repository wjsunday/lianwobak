<?php

namespace Apps\Bonusevent\Model;

use Model;

/**
 * 关注活动
 *
 * @package Apps\Bonusevent\Model\Star
 * @author Seven Du <lovevipdsw@vip.qq.com>
 **/
class Star extends Model
{
    /**
     * 储存单例
     *
     * @var object
     **/
    private static $_instance;

    /**
     * 获取单例对象
     *
     * @return object self
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * 主键名称
     *
     * @var string
     **/
    protected $pk = 'uid';

    protected $tableName = 'bonusevent_star';

    protected $fields = array('uid', 'eid', 'time');

    /**
     * 验证用户是否关注活动了
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function has($eid, $uid)
    {
        list($eid, $uid) = array(intval($eid), intval($uid));

        return $this->where(array(
            'eid' => array('eq', $eid),
            'uid' => array('eq', $uid),
        ))->field('uid')->count() > 0;
    }

    /**
     * 关注活动
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function add($eid, $uid)
    {
        list($eid, $uid) = array(intval($eid), intval($uid));
        if ($this->has($eid, $uid)) {
            $this->error = '你已经关注过该活动了';

            return false;
        }

        return parent::add(array(
            'eid' => $eid,
            'uid' => $uid,
            'time' => time(),
        ));
    }

    /**
     * 取消关注活动
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function un($eid, $uid)
    {
        list($eid, $uid) = array(intval($eid), intval($uid));

        return $this->where(array(
            'eid' => array('eq', $eid),
            'uid' => array('eq', $uid),
        ))->delete();
    }

    /**
     * 获取我关注的活动
     *
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getBonusevent($uid)
    {
        $uid = intval($uid);
        $events = $this->where(array(
            'uid' => array('eq', $uid),
        ))->field('eid')
          ->order('`time` DESC')
          ->findPage(20); // 每页 20条
        foreach ($events['data'] as $key => $value) {
            $value = Bonusevent::getInstance()->get($value['eid']);
            $events['data'][$key] = $value;
        }

        return $events;
    }
} // END class Star extends Model
