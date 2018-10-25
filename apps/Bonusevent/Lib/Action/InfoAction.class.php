<?php

namespace Apps\Bonusevent\Controller;

defined('SITE_PATH') || exit('Forbidden');

use Apps\Bonusevent\Common\BaseController as Controller;
use Apps\Bonusevent\Common;
use Apps\Bonusevent\Model\Event;
use Apps\Bonusevent\Model\Cate;
use Apps\Bonusevent\Model\Enrollment;
use Apps\Bonusevent\Model\Star;

/**
 * 活动详情页面
 *
 * @package Apps\Bonusevent\Controller\Info
 * @author Seven Du <lovevipdsw@vip.qq.com>
 **/
class Info extends Controller
{
    /**
     * 活动详情页面
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function index()
    {
        array_push($this->appJsList, '/js/info.index.js');
        $id = Common::getInput('id', 'get');
        $id = intval($id);
        if (!$id or !($data = Bonusevent::getInstance()->get($id)) or $data['del']) {
            $this->error('您访问的活动不存在，或者已经被删除！');
        }
        /* 地区 */
        $data['area'] = model('Area')->getAreaById($data['area']);
        $data['area'] = $data['area']['title'];
        $data['city'] = model('Area')->getAreaById($data['city']);
        $data['city'] = $data['city']['title'];

        /* 分类 */
        $data['cate'] = Cate::getInstance()->getById($data['cid']);
        $data['cate'] = $data['cate']['name'];

        /* 用户 */
        $data['user'] = model('User')->getUserInfo($data['uid']);

        /* 是否报名 */
        $this->assign('enrollment', Enrollment::getInstance()->hasUser($id, $this->mid));

        /* 报名用户 */
        $this->assign('eventUsers', Enrollment::getInstance()->getEventUsers($id));

        /* 是否关注了活动 */
        $this->assign('star', Star::getInstance()->has($id, $this->mid));

        /* 右侧 */
        $this->__RIGHT__();

        $this->assign('data', $data);
        $data['etime'] < time() && $this->assign('ended', 1);
        $this->display();
    }

    /**
     * 报名
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function enrollment()
    {
        list($eid, $name, $sex, $num, $phone, $note) = Common::getInput(array('eid', 'name', 'sex', 'num', 'phone', 'note'), 'post');

        if (Enrollment::getInstance()->add($this->mid, $eid, $name, $sex, $num, $phone, $note, time())) {
            $this->__JSON__(1, '报名成功');
        }
        $this->__JSON__(0, Enrollment::getInstance()->getError());
    }

    /**
     * 取消报名
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function unEnrollment()
    {
        $eid = Common::getInput('eid', 'post');
        if (Enrollment::getInstance()->un($eid, $this->mid)) {
            $this->__JSON__(1, '取消报名成功');
        }
        $this->__JSON__(0, Enrollment::getInstance()->getError());
    }
} // END class Info extends Controller
class_alias('Apps\Bonusevent\Controller\Info', 'InfoAction');
