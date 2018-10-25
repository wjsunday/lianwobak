<?php

namespace Apps\Event\Controller;

defined('SITE_PATH') || exit('Forbidden');

use Apps\Event\Common\BaseController as Controller;
use Apps\Event\Common;
use Apps\Event\Model\Cate;
use Apps\Event\Model\Event;

/**
 * 活动前台入口控制器
 *
 * @package Apps\Event\Controller\Index
 * @author Seven Du <lovevipdsw@vip.qq.com>
 **/
class Index extends Controller
{
    /**
     * 活动首页
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function index()
    {
        array_push($this->appJsList, '/js/index.js');
        list($cid, $area, $time, $wd) = Common::getInput(array('cid', 'area', 'time', 'wd'));
        list($cid, $area) = array(intval($cid), intval($area));

        /* 分类 */
        $this->assign('cates', Cate::getInstance()->getAll());
        // var_dump(Cate::getInstance());exit;
        /* 数据库不必重复的地区 */
        $this->assign('areas', Event::getInstance()->getArea());

        /* 列表数据 */
        $this->assign('list', Event::getInstance()->getList($cid, $area, $wd, $time));
        // lst_dump(Event::getInstance()->getList());exit;
        // lst_dump(Event::getInstance()->getList($cid, $area, $wd, $time));exit;

        /* 右侧 */
        $this->__RIGHT__();

        $this->display();
    }


} // END class Index extends Controller
class_alias('Apps\Event\Controller\Index', 'IndexAction');
