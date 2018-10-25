<?php

namespace Apps\Bonusevent\Controller;

defined('SITE_PATH') || exit('Forbidden');

use Apps\Bonusevent\Common\BaseController as Controller;
use Apps\Bonusevent\Common;
use Apps\Bonusevent\Model\Cate;
use Apps\Bonusevent\Model\Event;
use Apps\Bonusevent\Model\Bonusevent;

/**
 * 活动前台入口控制器
 *
 * @package Apps\Bonusevent\Controller\Index
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
        
        /* 数据库不必重复的地区 */
        $this->assign('areas', Bonusevent::getInstance()->getArea());
        
        /* 列表数据 */
        $this->assign('list', Bonusevent::getInstance()->getList($cid, $area, $wd, $time));
        // var_dump(Bonusevent::getInstance());exit;

        /* 右侧 */
        $this->__RIGHT__();
        
        $this->display();
    }
} // END class Index extends Controller
class_alias('Apps\Bonusevent\Controller\Index', 'IndexAction');
