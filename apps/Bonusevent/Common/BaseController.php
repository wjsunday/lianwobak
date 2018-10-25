<?php

namespace apps\Bonusevent\Common;

use Action as Controller;
use Apps\Bonusevent\Common;
use Apps\Bonusevent\Model\Cate;
use Apps\Bonusevent\Model\Bonusevent;

/**
 * 共同初始化的控制器
 *
 * @package default
 * @author
 **/
class BaseController extends Controller
{
    /**
     * 初始化方法
     *
     * @author Seven Du <lovevipdsw2vip.qq.com>
     **/
    protected function _initialize()
    {
        Common::setHeader('text/html');
        array_push($this->appCssList, '/css/style.css?');
    }

    /**
     * 请求分类
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    protected function __cate__()
    {
        $this->assign('cates', Cate::getInstance()->getAll());
    }

    /**
     * 输出JSON数据
     *
     * @param int    $sttaus  状态码
     * @param string $message 消息
     * @param unknow $data    附带数据
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    protected function __JSON__($status = 0, $message, $data = null)
    {
        ob_end_clean();
        ob_start();
        echo json_encode(array(
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ));
        ob_end_flush();
        unset($status, $message, $data);
        exit;
    }

    /**
     * 右侧公用 - 兼容首页和搜索
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    protected function __RIGHT__()
    {
        array_push($this->appJsList, '/js/date.js');
        array_push($this->appJsList, '/js/right.js');
        /* 常用搜索参数传递 */
        list($cid, $area, $time, $wd) = Common::getInput(array('cid', 'area', 'time', 'wd'));
        list($cid, $area, $wd) = array(intval($cid), intval($area), t($wd));

        /* 转换time */
        $time = $time ? is_numeric($time) ? $time : strtotime($time) : null;

        $this->assign('cid', $cid);
        $this->assign('area', $area);
        $this->assign('time', $time);
        $this->assign('wd', $wd);

        /* 推荐的活动 */
        $this->assign('rightTopEvent', Bonusevent::getInstance()->getRightEvent(5));
    }

    /**
     * 获取推荐的活动
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function ajaxGetRightTopEvent()
    {
        foreach (Bonusevent::getInstance()->getRightEvent(5) as $event) {
            echo '<li>',
                        '<a href="', U('Bonusevent/Info/index', array('id' => $event['eid'])), '">',
                            '<img src="', getImageUrlByAttachId($event['image'], 46, 65), '">',
                            '<span>', $event['name'], '</span>',
                        '</a>',
                    '</li>';
        }
    }

    /**
     * 异步请求，需求月是否有活动
     *
     * @request string $time 请求的时间
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function ajaxGetMonthEventDay()
    {
        /* 获取初始化时间戳 */
        list($cid, $area, $time, $wd) = Common::getInput(array('cid', 'area', 'time', 'wd'));

        $this->__JSON__(1, '', Bonusevent::getInstance()->getMonthEventDay($cid, $area, $wd, $time));
    }
} // END class BaseController extends Controller
