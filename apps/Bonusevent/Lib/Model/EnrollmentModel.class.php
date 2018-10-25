<?php

namespace Apps\Bonusevent\Model;

use Model;
use Apps\Bonusevent\Common;

/**
 * 报名模型
 *
 * @package Apps\Bonusevent\Model\Enrollment
 * @author Seven Du <lovevipdsw@vip.qq.com>
 **/
class Enrollment extends Model
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

    /* =========================================== */
    /* 静态 End */

    /**
     * 主键名称
     *
     * @var string
     **/
    protected $pk = 'id';

    protected $tableName = 'bonusevent_enrollment';

    protected $fields = array('id', 'uid', 'name', 'num', 'sex', 'phone', 'note', 'time', 'aduit', 'eid');

    /**
     * 添加报名
     *
     * @param  int  $uid 报名用户UID
     * @param  int  $eid 活动ID
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function add($uid, $eid, $name, $sex = 0, $num = 1, $phone, $note = null, $time = null)
    {
        /* 备注信息 */
        $note = t($note);
        /* 时间 */
        $time = $time ? is_numeric($time) ? $time : strtotime($time) : time();
        /* ID */
        list($uid, $eid) = array(intval($uid), intval($eid));
        /* 数量 */
        $num = intval($num);
        $num = 1; // 特殊原因，固定为1 2015-12-32
        /* name */
        $name = t($name);
        /* 性别 */
        $sex = $sex ? 1 : 0;
        /* 审核信息默认 */
        $aduit = 0;

        /* 判断活动是否存在 */
        if (!($event = Bonusevent::getInstance()->get($eid))) {
            $this->error = '活动不存在或者已经被删除';

            return false;
        } elseif ($event['uid'] == $uid) {
            $this->error = '不能报名自己发起的活动哦！';

            return false;

        /* 判断是否已经报名 */
        } elseif ($this->hasUser($eid, $uid)) {
            $this->error = '你已经报过名了';

            return false;

        /* 检查活动是否没有开始 */
        } elseif ($event['stime'] >= time()) {
            $this->error = '该活动还没有开始，不能报名';

            return false;

        /* 检查活动是否已经结束 */
        } elseif ($event['etime'] <= time()) {
            $this->error = '该活动已经结束';

            return false;

        /* 检查name是否合法 */
        } elseif (Common::strlen($name) <= 0 or Common::strlen($name) >= 100) {
            $this->error = '称呼不合法';

            return false;

        /* 检查是否还有报名名额 */
        } elseif ($event['manNumber'] > 0 and $event['remainder'] <= 0) {
            $this->error = '没有报名名额';

            return false;

        /* 检查人数 */
        } elseif ($event['manNumber'] > 0 and $event['remainder'] < $num) {
            $this->error = '报名人数超出最大限制';

            return false;

        /* 检查联系方式 */
        } elseif (Common::strlen($phone) <= 6) {
            $this->error = '联系方式信息不正确';

            return false;

        /* 检查报名人数 */
        } elseif ($num <= 0) {
            $this->error = '报名人数，是纯数字，必须等于或者大于1，小于系统报名人数！';

            return false;

        /* 审核信息 */
        } elseif ($event['manNumber'] <= 0) {
            $aduit = 1;
        }

        /* 如果报名成功，则更新活动报名人数 */
        if (parent::add(array(
            'uid' => $uid,
            'name' => $name,
            'num' => $num,
            'sex' => $sex,
            'phone' => $phone,
            'note' => $note,
            'time' => $time,
            'aduit' => $aduit,
            'eid' => $eid,
        ))) {
            // var_dump($event['manNumber'] > 0);exit;
            $event['manNumber'] > 0 and
            Bonusevent::getInstance()->setId($eid)->changeMan($num);
            /* 私信 */
            Common::sendMessage($event['uid'], '你的活动《<a href="'.U('Bonusevent/Info/index', array('id', $eid)).'">'.$event['name'].'</a>》有人提交了新的报名申请【<a href="'.U('Bonusevent/Manage/aduit', array('eid' => $eid)).'">点击这里</a>】这里审核吧！', $uid);

            return true;
        }

        return false;
    }

    /**
     * 检查用户是否报名了该活动
     *
     * @param  int  $eid 活动id
     * @param  int  $uid 用户ID
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function hasUser($eid, $uid)
    {
        return $this->where(array(
            'eid' => array('eq', intval($eid)),
            'uid' => array('eq', intval($uid)),
        ))->field('id')->count() > 0;
    }

    /**
     * 取得报名信息
     *
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function get($eid, $uid)
    {
        list($eid, $uid) = array(intval($eid), intval($uid));

        return $this->where(array(
            'eid' => array('eq', $eid),
            'uid' => array('eq', $uid),
        ))->find();
    }

    /**
     * 取消报名
     *
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function un($eid, $uid)
    {
        list($eid, $uid) = array(intval($eid), intval($uid));
        /* 检查活动是否存在 */
        if (!($event = Bonusevent::getInstance()->get($eid))) {
            $this->error = '活动不存在';

            return false;

        /* 判断活动是否结束 */
        } elseif ($event['etime'] <= time()) {
            $this->error = '活动已经结束';

            return false;

        /* 读取报名信息 */
        } elseif (!($info = $this->get($eid, $uid))) {
            $this->error = '没有报名信息';

            return false;

        /* 取消报名 */
        } elseif ($this->where(array(
            'eid' => array('eq', $eid),
            'uid' => array('eq', $uid),
        ))->delete()) {
            Bonusevent::getInstance()->setId($eid)->change2Man($info['num']);

            return true;
        }

        return false;
    }

    /**
     * 根据活动ID获取所有的用户
     *
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getBonuseventUsers($eid)
    {
        $eid = intval($eid);
        $users = $this->where(array(
            'eid' => array('eq', $eid),
            'aduit' => array('eq', 1),
        ))->field('uid')
          ->select();
        foreach ($users as $key => $value) {
            $value = model('User')->getUserInfo($value['uid']);
            $users[$key] = $value;
        }
        // var_dump($users);exit;
        return $users;
    }

    /**
     * 取得用户参与的活动
     *
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getUserBonusevent($uid)
    {
        $uid = intval($uid);
        $events = $this->where(array('uid' => array('eq', $uid)))->field('eid')->order('`id` DESC')->findPage(20); // 每页20条
        // var_dump($events);exit;
        foreach ($events['data'] as $key => $value) {
            $value = Bonusevent::getInstance()->get($value['eid']);
            $events['data'][$key] = $value;
        }

        return $events;
    }

    /**
     * 获取活动待审核人数
     *
     * @return int
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function getNotAduit($eid)
    {
        $eid = intval($eid);

        return $this->where(array(
            'eid' => array('eq', $eid),
            'aduit' => array('neq', 1),
        ))->field('id')->count();
    }

    /**
     * 获取活动成员报名信息列表
     *
     * @return array
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function get2event($eid)
    {
        $eid = intval($eid);

        return $this->where(array('eid' => array('eq', $eid)))->order('`id` DESC')->findPage(20);
    }

    /**
     * 驳回申诉
     *
     * @param  int    $eid      活动ID
     * @param  int    $uid      被操作用户
     * @param  int    $adminUid 执行操作的用户ID
     * @param  string $tips     理由
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function aduitNo($eid, $uid, $adminUid, $tips)
    {
        list($eid, $uid, $adminUid) = array(intval($eid), intval($uid), intval($adminUid));
        $tips = t($tips);
        $event = Bonusevent::getInstance()->get($eid);
        if (Common::strlen($tips) <= 0) {
            $this->error = '驳回理由不能为空';

            return false;
        } elseif (!$event) {
            $this->error = '操作的活动不存在或者已经被删除';

            return false;

        /* 是否有权限 */
        } elseif ($event['uid'] != $adminUid) {
            $this->error = '你没有权限执行该操作';

            return false;

        /* 判断申请是否存在 */
        } elseif (!($data = $this->get($eid, $uid))) {
            $this->error = '不存在的用户申请，或者该用户撤销了申请';

            return false;

        /* 检查删除申请是否成功 */
        } elseif ($this->where(array(
            'eid' => array('eq', $eid),
            'uid' => array('eq', $uid),
        ))->delete()) {
            /* 成功后，活动应当增加相应人数 */
            Bonusevent::getInstance()->setId($eid)->change2Man($data['num']);
            /* 发送私信 */
            Common::sendMessage($uid, '你申请参与的活动《<a href="'.U('Bonusevent/Info/index', array('id' => $eid)).'">'.$event['name'].'</a>》被驳回。<br>理由：'.$tips, $adminUid);

            return true;
        }

        return false;
    }

    /**
     * 通过审核
     *
     * @param  int  $eid      活动ID
     * @param  int  $uid      被操作用户
     * @param  int  $adminUid 执行操作的用户ID
     * @return bool
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function aduitYes($eid, $uid, $adminUid)
    {
        list($eid, $uid, $adminUid) = array(intval($eid), intval($uid), intval($adminUid));
        $event = Bonusevent::getInstance()->get($eid);
        if (!$event) {
            $this->error = '操作的活动不存在或者已经被删除';

            return false;

        /* 是否有权限 */
        } elseif ($event['uid'] != $adminUid) {
            $this->error = '你没有权限执行该操作';

            return false;

        /* 判断申请是否存在 */
        } elseif (!($data = $this->get($eid, $uid))) {
            $this->error = '不存在的用户申请，或者该用户撤销了申请';

            return false;

        /* 检查申请是否成功 */
        } elseif ($this->where(array(
            'eid' => array('eq', $eid),
            'uid' => array('eq', $uid),
        ))->save(array(
            'aduit' => 1,
        ))) {
            /* 发送私信 */
            Common::sendMessage($uid, '你申请参与的活动《<a href="'.U('Bonusevent/Info/index', array('id' => $eid)).'">'.$event['name'].'</a>》已经被审核通过', $adminUid);

            return true;
        }

        return false;
    }
} // END class Enrollment extends Model
