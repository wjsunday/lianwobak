<?php

namespace Apps\Bonusevent\Controller;

defined('SITE_PATH') || exit('Forbidden');

use Apps\Bonusevent\Common;
use AdministratorAction   as Controller;
use Apps\Bonusevent\Model\Cate as CateModel;
use Apps\Bonusevent\Model\Event;
use Apps\Bonusevent\Model\Bonusevent;

Common::import(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');

/**
 * 活动后台管理控制器
 *
 * @package Apps\Bonusevent\Controller\Admin
 * @author Seven Du <lovevipdsw@vip.qq.com>
 **/
class Admin extends Controller
{
    /*=================================非Action区域========================================*/
    /**
     * 初始化操作
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function _initialize()
    {
        parent::_initialize();
        Common::setHeader('text/html', 'utf-8');

        $this->pageTitle['index'] = '分类管理';
        $this->pageTitle['event'] = '活动管理';
        // $this->pageTitle['config']       = '常规配置';
    }

    /**
     * 添加tab菜单
     *
     * @param string $hash  方法的名称
     * @param string $url   点击跳转地址，非绝对地址，而是U函数需要构造的内容
     * @param string $name  描述名称
     * @param array  $param url拓展参数
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    private function _addTab($hash, $url, $name = '', array $param = array())
    {
        if (!$name) {
            $name = $this->pageTitle[$hash];
        }
        array_push($this->pageTab, array(
            'title' => $name,
            'tabHash' => $hash,
            'url' => U($url, $param),
        ));
    }

    /**
     * 公用TAB切换菜单
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    private function _commonTab()
    {
        /* 分类管理 */
        $this->_addTab('index', 'Bonusevent/Admin/index');
        $this->_addTab('event', 'Bonusevent/Admin/event');
    }

/*=================================Action区域===========================================*/

    /**
     * 删除活动 - 管理员删除
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function delEvent()
    {
        $ids = Common::getInput('ids');
        $ids = explode(',', $ids);
        if (Bonusevent::getInstance()->adminDel($ids)) {
            $this->success('删除成功');
        }
        $this->error('删除失败');
    }

    /**
     * 活动管理
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function event()
    {
        $this->_commonTab();
        array_push($this->appJsList, '/js/Bonusevent.admin.js?v='.time());
        $this->pageKeyList = array('eid', 'name', 'time', 'location', 'manNumber', 'price', 'cate', 'user', 'action');
        $this->searchKey = array('eid', 'name', 'stime', 'etime', 'cid', 'uid', 'audit');
        $this->savePostUrl = U('Bonusevent/Admin/Bonusevent');

        /*搜索*/
        array_push($this->pageButton, array(
            'title' => '搜索',
            'id' => 'search',
        ));
        /* 删除按钮 */
        array_push($this->pageButton, array(
            'title' => '删除',
            'id' => 'delete',
            'data' => array(
                'uri' => U('Bonusevent/Admin/delEvent', array('ids' => '__IDS__')),
            ),
        ));

        $this->displayList(Bonusevent::getInstance()->getAdminList());
    }

    /**
     * 分类管理
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function index()
    {
        $this->_commonTab();
        array_push($this->appJsList, '/js/cate.admin.js');
        $this->pageKeyList = array('cid', 'name', 'leval', 'action');
        $this->_listpk = 'cid';
        array_push($this->pageButton, array(
            'title' => '删除',
            'id' => 'delete',
            'data' => array(
                'uri' => U('Bonusevent/Admin/delCate', array('ids' => '__IDS__')),
            ),
        ));
        array_push($this->pageButton, array(
            'title' => '添加',
            'id' => 'add',
            'data' => array(
                'uri' => U('Bonusevent/Admin/AddCate', array('tabHash' => 'index')),
            ),
        ));
        $this->displayList(CateModel::getInstance()->getAdminList());
    }

    /**
     * 添加分类
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function AddCate()
    {
        $id = intval(Common::getInput('id'));
        $this->_commonTab();
        $this->notEmpty = $this->pageKeyList = array('name', 'leval');
        $this->savePostUrl = U('Bonusevent/Admin/DoAddCate', array('id' => $id));
        $this->displayConfig(CateModel::getInstance()->getById($id));
    }

    /**
     * 处理添加/编辑分类
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function DoAddCate()
    {
        $id = Common::getInput('id');
        list($name, $leval) = Common::getInput(array('name', 'leval'), 'post');
        if (!$id || !CateModel::getInstance()->hasById($id)) {
            if (!CateModel::getInstance()->add($name, $leval)) {
                $this->error(CateModel::getInstance()->getError());
            }
        } elseif (!CateModel::getInstance()->update($id, $name, $leval)) {
            $this->error(CateModel::getInstance()->getError());
        }
        $this->success('操作成功');
    }

    /**
     * 删除分类
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function delCate()
    {
        $ids = Common::getInput('ids');
        $ids = explode(',', $ids);
        $ids = array_filter($ids);
        CateModel::getInstance()->delCate($ids);
        $this->success('操作成功');
    }
} // END class Admin extends Controller
class_alias('\Apps\Bonusevent\Controller\Admin', 'AdminAction');
