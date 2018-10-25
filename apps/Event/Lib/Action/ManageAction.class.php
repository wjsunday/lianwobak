<?php

namespace Apps\Event\Controller;

defined('SITE_PATH') || exit('Forbidden');

use Apps\Event\Common\BaseController as Controller;
use Apps\Event\Common;
use Apps\Event\Model\Enrollment;
use Apps\Event\Model\Event;
use Apps\Event\Model\Star;

/**
 * 管理控制器
 *
 * @package Apps\Event\Controller\Manage
 * @author Seven Du <lovevipdsw@vip.qq.com>
 **/
class Manage extends Controller
{
    /**
     * 报名审核
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function aduit()
    {
        array_push($this->appJsList, '/js/manage.aduit.js');
        $eid = Common::getInput('eid', 'get');
        /* 是否存在 */
        if (!($event = Event::getInstance()->get($eid))) {
            $this->error('活动不存在或者已经被删除');

        /* 是否有权限 */
        } elseif ($event['uid'] != $this->mid) {
            $this->error('你没有权限执行该操作');
        }
        $this->assign('name', $event['name']);
        /* 成员列表 */
        $this->assign('list', Enrollment::getInstance()->get2event($eid));
        $this->display();
    }

    /**
     * 处理通过
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function aduitYes()
    {
        list($eid, $uid) = Common::getInput(array('eid', 'uid'));
        if (Enrollment::getInstance()->aduitYes($eid, $uid, $this->mid)) {
            $this->success('操作成功');
        }
        $this->error(Enrollment::getInstance()->getError());
    }

    /**
     * 处理审核驳回
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function doAduit()
    {
        list($eid, $uid, $content) = Common::getInput(array('eid', 'uid', 'content'), 'post');
        if (Enrollment::getInstance()->aduitNo($eid, $uid, $this->mid, $content)) {
            $this->__JSON__(1, '驳回成功');
        }
        $this->__JSON__(0, Enrollment::getInstance()->getError());
    }

    /**
     * 提前结束活动
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function over()
    {
        $eid = Common::getInput('eid', 'get');
        if (Event::getInstance()->setId($eid)->setUid($this->mid)->over()) {
            $this->success('操作成功！');
        }
        $this->error(Event::getInstance()->getError());
    }

    /**
     * 删除活动
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function delEvent()
    {
        $eid = Common::getInput('eid', 'get');
        if (Event::getInstance()->setId($eid)->setUid($this->mid)->delete()) {
            $this->assign('jumpUrl', U('Event/Index/index'));
            $this->success('删除成功！');
        }
        $this->error(Event::getInstance()->getError());
    }

    /**
     * 我的活动 我发起的/我参与的/无关注的
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function index()
    {
        /* 右侧 */
        $this->__RIGHT__();
        /* 类型 */
        $type = Common::getInput('type', 'get');
        $type = intval($type);
        if (!in_array($type, array(0, 1, 2))) {
            $type = 0;
        }

        /* 取得数据 */
        switch ($type) {
            case 2:
                $list = Star::getInstance()->getEvent($this->mid);
                break;

            case 1:
                $list = Event::getInstance()->getMyEvent($this->mid);
                break;

            case 0:
            default:
                $list = Enrollment::getInstance()->getUserEvent($this->mid);
                break;
        }
        $this->assign('list', $list);
        $this->assign('type', $type);

        $this->display();
    }

    /**
     * 关注活动
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function star()
    {
        $eid = Common::getInput('eid', 'post');
        if (Star::getInstance()->add($eid, $this->mid)) {
            $this->__JSON__(1, '关注成功');
        }
        $this->__JSON__(0, Star::getInstance()->getError());
    }

    /**
     * 取消关注活动
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function unStar()
    {
        $eid = Common::getInput('eid', 'post');
        if (Star::getInstance()->un($eid, $this->mid)) {
            $this->__JSON__(1, '取消关注成功');
        }
        $this->__JSON__(0, Star::getInstance()->getError());
    }
} // END class Manage extends Controller
class_alias('Apps\Event\Controller\Manage', 'ManageAction');
