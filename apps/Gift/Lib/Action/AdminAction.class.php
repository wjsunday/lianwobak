<?php

namespace Apps\Gift\Controller;

defined('SITE_PATH') || exit('Forbidden');

use Apps\Gift\Common;
use AdministratorAction     as Controller;
use Apps\Gift\Model\Gift    as GiftModel;
use Apps\Gift\Model\GiftLog as LogModel;

Common::import(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');

/**
 * 礼物商城后台管理控制器
 *
 * @package Apps\Gift\Controller\Admin
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

        $this->pageTitle['index'] = '礼物列表';
        $this->pageTitle['addGift'] = '添加礼物';
        $this->pageTitle['order'] = '礼物订单';
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
        /* index */
        $this->_addTab('index', 'Gift/Admin/index');
        /* 订单 */
        $this->_addTab('order', 'Gift/Admin/order');
    }

/*=================================Action区域===========================================*/

    /**
     * 商品列表
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function index()
    {
        $this->_commonTab();
        array_push($this->pageButton, array(
            'title' => '搜索礼物',
            'onclick' => '" id="gift-search',
        ));
        array_push($this->pageButton, array(
            'title' => '添加礼物',
            'onclick' => '" id="gift-add',
        ));
        array_push($this->pageButton, array(
            'title' => '批量删除',
            'onclick' => '" id="gift-dels',
        ));
        array_push($this->onload, '(function() {
			/* 添加礼物 */
			$("#gift-add").on("click", function() {
				window.location.href = \''.U('Gift/Admin/addGift', array('tabHash' => 'index')).'\';
			});
			/* 搜索礼物 */
			$(\'#gift-search\').on(\'click\', function() {
				admin.fold(\'search_form\');
			});
			/* 批量删除 */
			$(\'#gift-dels\').on(\'click\', function() {
				var ids = admin.getChecked();
				var str = \'\';
				for(var i in ids) {
					str += ids[i];
					if (ids.length > i + 1) {
						str += \'|\';
					};
				};
				var url = \''.U('Gift/Admin/deleteGift', array('ids' => '__IDS__')).'\';
				url = url.replace(/\_\_IDS\_\_/g, str);
				window.location.href = url;
			});
		})()');

        $this->searchKey = array('id', 'name');
        $this->searchPostUrl = U('Gift/Admin/index');
        $this->pageKeyList = array('id', 'name', 'brief', 'image', 'score', 'stock', 'max', 'cate', 'time', 'action');

        list($id, $name) = Common::getInput(array('id', 'name'));

        /*
         * 每页显示的条数
         *
         * @var string
         **/
        $num = 10;

        $data = GiftModel::getInstance()->findByPage(10, $id, $name);

        foreach ($data['data'] as $key => $value) {
            $value['image'] = getImageUrlByAttachId($value['image'], 100, 100);
            $value['image'] = '<img src="'.$value['image'].'">';
            if ($value['cate'] == 1) {
                $value['cate'] = '虚拟礼物';
            } elseif ($value['cate'] == 2) {
                $value['cate'] = '实体礼物';
            }
            $value['time'] = date('Y-m-d H:i:s', $value['time']);

            $value['action'] = '';
            $value['action'] .= '<a href="'.U('Gift/Admin/editGift', array('tabHash' => 'index', 'id' => $value['id'])).'">[编辑]</a>';
            $value['action'] .= '&nbsp;-&nbsp;';
            $value['action'] .= '<a href="'.U('Gift/Admin/deleteGift', array('ids' => $value['id'])).'">[删除]</a>';

            $data['data'][$key] = $value;
        }

        $this->displayList($data);
    }

    /**
     * 添加礼物
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function addGift()
    {
        $this->_commonTab();
        $this->pageKeyList = array('name', 'cate', 'brief', 'image', 'score', 'stock', 'max', 'info');
        $this->notEmpty = array('name', 'cate', 'brief', 'image', 'score', 'stock', 'info');
        $this->opt['cate'] = array(
            '1' => '虚拟礼物',
            '2' => '实体礼物',
        );
        $this->submitAlias = '发布礼物';
        $this->savePostUrl = U('Gift/Admin/doAddGift', array('tabHash' => 'index'));
        $this->displayConfig();
    }

    /**
     * 添加礼物处理
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function doAddGift()
    {
        /* # 接收参数 */
        list($name, $cate, $brief, $image, $score, $stock, $max, $info) = Common::getInput(array('name', 'cate', 'brief', 'image', 'score', 'stock', 'max', 'info'), 'post');

        /* # 判断商品名称是否超出限制 */
        if (Common::strlen($name) > 100) {
            $this->error('标题最多100字');

        /* # 判断类型是否不正确 */
        } elseif (!in_array($cate, array(1, 2))) {
            $this->error('请选择正确的分类');

        /* # 判断简介是否为空 */
        } elseif (Common::strlen($brief) <= 0) {
            $this->error('简介不能为空');

        /* # 判断简介是否超出限制 */
        } elseif (Common::strlen($brief) > 200) {
            $this->error('简介不得超出200字');

        /* # 判断图片是否存在 */
        } elseif (!$image) {
            $this->error('请选择图片');

        /* # 判断库存不能小于1 */
        } elseif ($stock < 1) {
            $this->error('库存不能小于1');

        /* # 判断是否添加失败 */
        } elseif (!GiftModel::getInstance()->add($name, $brief, $info, $image, $score, $stock, $max, $cate)) {
            $this->error(GiftModel::getInstance()->getError());
        }

        $this->assign('jumpUrl', U('Gift/Admin/index'));
        $this->success('添加成功');
    }

    /**
     * 修改礼物
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function editGift()
    {
        $id = Common::getInput('id');
        $id = intval($id);

        $this->_commonTab();
        $this->pageKeyList = array('name', 'cate', 'brief', 'image', 'score', 'stock', 'max', 'info');
        $this->notEmpty = array('name', 'cate', 'brief', 'image', 'score', 'stock', 'info');
        $this->opt['cate'] = array(
            '1' => '虚拟礼物',
            '2' => '实体礼物',
        );
        $this->submitAlias = '修改礼物';
        $this->savePostUrl = U('Gift/Admin/doEditGift', array('tabHash' => 'index', 'id' => $id));

        $data = GiftModel::getInstance()->getById($id);

        $this->displayConfig($data);
    }

    /**
     * 处理编辑礼物操作
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function doEditGift()
    {
        /* # 接收参数 */
        $id = intval(Common::getInput('id'));
        list($name, $cate, $brief, $image, $score, $stock, $max, $info) = Common::getInput(array('name', 'cate', 'brief', 'image', 'score', 'stock', 'max', 'info'), 'post');

        /* # 判断参数正确性 */
        if (!$id) {
            $this->error('传递的参数有误');

        /* # 判断编辑对象是否存在 */
        } elseif (!GiftModel::getInstance()->hasById($id)) {
            $this->error('不存在的礼物');

        /* # 判断商品名称是否超出限制 */
        } elseif (Common::strlen($name) > 100) {
            $this->error('标题最多100字');

        /* # 判断类型是否不正确 */
        } elseif (!in_array($cate, array(1, 2))) {
            $this->error('请选择正确的分类');

        /* # 判断简介是否为空 */
        } elseif (Common::strlen($brief) <= 0) {
            $this->error('简介不能为空');

        /* # 判断简介是否超出限制 */
        } elseif (Common::strlen($brief) > 200) {
            $this->error('简介不得超出200字');

        /* # 判断图片是否存在 */
        } elseif (!$image) {
            $this->error('请选择图片');

        /* # 判断库存不能小于1 */
        } elseif ($stock < 1) {
            $this->error('库存不能小于1');

        /* # 判断是否更新失败 */
        } elseif (!GiftModel::getInstance()->update($id, $name, $brief, $info, $image, $score, $stock, $max, $cate)) {
            $this->error(GiftModel::getInstance()->getError());
        }

        $this->assign('jumpUrl', U('Gift/Admin/index'));
        $this->success('修改成功！');
    }

    /**
     * 删除礼物数据
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function deleteGift()
    {
        $ids = Common::getInput('ids');
        is_numeric($ids) && $ids = array($ids);
        is_array($ids) || $ids = explode('|', $ids);

        $where = '`id` IN (%s)';
        $where = sprintf($where, implode(',', $ids));

        GiftModel::getInstance()->where($where)->delete();
        $this->success('删除成功');
    }

    /**
     * 订单记录
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function order()
    {
        $this->_commonTab();
        array_push($this->pageButton, array(
            'title' => '搜索礼物',
            'onclick' => '" id="gift-order-search',
        ));
        array_push($this->onload, '(function() {
			/* 搜索礼物订单 */
			$(\'#gift-order-search\').on(\'click\', function() {
				admin.fold(\'search_form\');
			});
		})()');

        /* # 搜索字段 */
        $this->searchKey = array('gid', 'giftName', 'inUid', 'outUid', 'name', 'phone', 'addres', 'startTime', 'endTime');
        $this->searchPostUrl = U('Gift/Admin/order', array('tabHash' => 'order'));

        /* # 页面字段 */
        $this->pageKeyList = array('gid', 'giftName', 'type', 'say', 'num', 'name', 'phone', 'addres', 'time', 'action');
        $this->allSelected = false;

        /* # 搜索参数 */
        list($gid, $giftName, $inUid, $outUid, $name, $phone, $addres, $startTime, $endTime) = Common::getInput(array('gid', 'giftName', 'inUid', 'outUid', 'name', 'phone', 'addres', 'startTime', 'endTime'));

        $data = LogModel::getInstance()->getOrderData($gid, $giftName, $inUid, $outUid, $name, $phone, $addres, $startTime, $endTime);

        foreach ($data['data'] as $key => $value) {
            if ($value['type'] == 1) {
                $value['type'] = '匿名赠送';
            } elseif ($value['type'] == 2) {
                $value['type'] = '公开赠送';
            } elseif ($value['type'] == 3) {
                $value['type'] = '私下赠送';
            }
            $value['time'] = date('Y年m月d日 H时i分s秒', $value['time']);
            $value['action'] = '<a style="display:block;width:40px;" href="'.U('Gift/Admin/send', array('tabHash' => 'order', 'oid' => $value['id'])).'">[发货]</a>';
            $data['data'][$key] = $value;
        }

        // var_dump($data);exit;

        $this->displayList($data);
    }

    /**
     * 礼物发货
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function send()
    {
        $this->_commonTab();
        $id = intval(Common::getInput('oid'));
        if (!LogModel::getInstance()->has($id)) {
            $this->error('该记录不存在');
        }
        $this->pageKeyList = array('content');
        $this->savePostUrl = U('Gift/Admin/doSend', array('id' => $id));

        $this->displayConfig();
    }

    /**
     * 处理发货步骤
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function doSend()
    {
        $id = intval(Common::getInput('id'));
        $content = t(Common::getInput('content', 'post'));
        $this->assign('jumpUrl', U('Gift/Admin/order', array('tabHash' => 'order')));

        /* # 判断是否是不存在的订单 */
        if (!($gift = LogModel::getInstance()->getById($id))) {
            $this->error('您所发货的订单不存在~~');

        /* # 判断是否发货失败 */
        } elseif (!LogModel::getInstance()->delivery($id, $content)) {
            $this->error('发货失败，Error：'.LogModel::getInstance()->getError());
        }

        /* # 礼物名称 */
        $giftName = GiftModel::getInstance()->getById($gift['gid']);
        $giftName = $giftName['name'];
        /* # 赠送人UserName */
        $outUserName = getUserName($gift['outUid']);
        /* # 被赠送人UserName */
        $inUserName = getUserName($gift['inUid']);

        $message = '礼物通知：__outUid__送给您的礼物“__GIFT__”已经发货，'.$content;
        if ($gift['type'] == 1) {
            /* # 如果是匿名赠送，不上报赠送人信息 */
            $message = '礼物通知：有好友匿名赠送给您的礼物“__GIFT__”已经发货,'.$content;
        }
        $message = str_replace('__outUid__', $outUserName, $message);
        $message = str_replace('__GIFT__', $giftName, $message);
        /* # 将消息添加到收礼人通知 */
        LogModel::getInstance()->sendMessage($gift['inUid'], $message);

        $message = '礼物通知：您送给__inUid__的礼物“__GIFT__”已经发货，'.$content;
        $message = str_replace('__inUid__', $inUserName, $message);
        $message = str_replace('__GIFT__', $giftName, $message);

        /* # 将消息发送给送礼人 */
        LogModel::getInstance()->sendMessage($gift['outUid'], $message);

        /* # 页面成功提醒 */
        $this->success('发货成功！');
    }
} // END class Admin extends Controller
class_alias('Apps\Gift\Controller\Admin', 'AdminAction');
