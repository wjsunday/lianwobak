<?php

namespace Apps\Gift\Controller;

defined('SITE_PATH') || exit('Forbidden');

use Apps\Gift\Common;
use Action                   as Controller;
use Apps\Gift\Model\Gift     as GiftModel;
use Apps\Gift\Model\GiftLog  as LogModel;

/**
 * 礼品商城首页控制器
 *
 * @package Apps\Gift\Controller\Index
 * @author Seven Du <lovevipdsw@vip.qq.com>
 **/
class Index extends Controller
{
    /**
     * 初始化控制器
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function _initialize()
    {
        Common::setHeader('text/html', 'utf-8');
        array_push($this->appCssList, 'css/style.css');
    }

    /**
     * 首页方法
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function index()
    {
        /* # SEO */
        $this->setTitle('礼物商城');
        $this->setKeywords('ThinkSNS,T4,礼物,礼物商城');

        $type = intval(Common::getInput('cate'));
        $this->assign('cate', $type);

        $where = '`cate` IN (1, 2)';
        if ($type >= 1 && $type <= 2) {
            $where = '`cate` = '.$type;
        }
        $where .= ' AND `isDel` != 1';

        $num = 20; /* 设置每页显示的数量 */

        $list = GiftModel::getInstance()->where($where)->order('`id` DESC')->field('`id`, `name`, `image`, `score`, `stock`')->findPage($num);
        $this->assign('gifts', $list);

        /* # 右侧排行 */
        $this->_rightTops();

        $this->display('index');
    }

    /**
     * 礼物阅读页面
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function read()
    {
        $id = intval(Common::getInput('id', 'get'));
        $data = GiftModel::getInstance()->getById($id);

        if (!$data) {
            $this->error('该礼物不存在或者已经被删除');
        }

        /* # SEO */
        $this->setTitle($gift['name']);
        $this->setKeywords('ThinkSNS,T4,礼物,礼物商城');

        $this->_rightTops();

        /* # 人数统计 */
        $this->assign('getCount', LogModel::getInstance()->getUserCount($id));

        $this->assign('gift', $data);
        $this->display('read');
    }

    /**
     * 礼物购买
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function buy()
    {
        $id = intval(Common::getInput('id', 'get'));
        $data = GiftModel::getInstance()->getById($id);

        if (!$data) {
            $this->error('该礼物不存在或者已经被删除');
        }
        $this->assign('gift', $data);
        $this->_rightTops();

        $this->display('buy');
    }

    /**
     * 处理礼物购买
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function doBuy()
    {
        list($id, $uid, $num, $addres, $say, $type) = Common::getInput(array('id', 'uid', 'num', 'addres', 'say', 'type'), 'post');
        list($name, $phone) = Common::getInput(array('name', 'phone'), 'post');

        /* # 参数过滤处理 */
        $id = intval($id);
        $uid = intval($uid);
        $num = intval($num);
        $type = intval($type);
        $addres = t($addres);
        $say = t($say);
        $name = t($name);
        $phone = t($phone);

        /* # 获取当前用户积分 */
        $score = model('Credit')->getUserCredit($this->mid);
        $score = $score['credit']['score']['value'];

        /* # 判断是否登陆 */
        if (!$this->mid) {
            $this->echoJson(0, '提示', '请先登录后再兑换', false);

        /* # 判断物品是否不存在 */
        } elseif (!$id || !($gift = GiftModel::getInstance()->getById($id))) {
            $this->echoJson(0, '提示', '兑换的该物品不存在', false);

        /* # 判断赠送的用户是否不存在 */
        } elseif (!$uid || !model('User')->hasUser($uid, true)) {
            $this->echoJson(0, '提示', '对不起，您赠送的用户不存在', false);

        /* # 判断是否赠送过了 */
        /*} elseif (LogModel::getInstance()->hasGive($id, $uid, $this->mid)) {*/
            /*$this->echoJson(0, '提示', '您已经赠送过给该用户，请勿重复赠送', false);*/

        /* # 判断积分是否充足 */
        } elseif ($gift['score'] > $score) {
            $this->echoJson(0, '积分获取', '您的积分余额不足，请先充值积分，或者做任务获得积分。', true, array($gift['score'], $score));

        /* # 判断数量是否少于1 */
        } elseif ($num < 1) {
            $this->echoJson(0, '提示', '赠送数量不得少于1份', false);

        /* # 判断是否超出库存 */
        } elseif ($gift['stock'] < $num) {
            $this->echoJson(0, '提示', '数量超出库存数量：'.$gift['stock'], false);

        /* # 判断是否超出限购 */
        } elseif ($gift['max'] < $num && $gift['max']) {
            $this->echoJson(0, '提示', '数量超出限购数量：'.$gift['max'], false);

        /* # 判断是否缺少祝福语 */
        } elseif (!$say) {
            $this->echoJson(0, '提示', '请输入祝福语', false);

        /* # 判断真实姓名是否为空 */
        } elseif (!$name && $gift['cate'] == 2) {
            $this->echoJson(0, '提示', '用户真实姓名不能为空', false);

        /* # 判断是否输入了联系方式 */
        } elseif (!$phone && $gift['cate'] == 2) {
            $this->echoJson(0, '提示', '用户联系方式不能为空', false);

        /* # 判断是否输入了地址 */
        } elseif (!$addres && $gift['cate'] == 2) {
            $this->echoJson(0, '提示', '请输入正确的收货地址', false);

        /* # 判断是否是不允许的赠送类型 */
        } elseif (!in_array($type, array(1, 2, 3))) {
            $this->echoJson(0, '提示', '不允许的赠送类型，请在页面上正确的选择赠送类型', false);

        /* # 判断是否兑换失败 */
        } elseif (!LogModel::getInstance()->add($uid, $this->mid, $id, $type, $say, $num, $addres, $name, $phone)) {
            $this->echoJson(0, '提示', LogModel::getInstance()->getError(), false);
        }

        /* # 发送系统消息 */
        if ($gift['cate'] == 1) {
            $message = '礼物提示：'.getUserName($this->mid).'送给了您'.$num.'份“'.$gift['name'].'”,快<a href="'.U('Gift/Index/my').'">去看看</a>！';
            if ($type == 1) {
                $message = '礼物提示：您收到了'.$num.'份你们赠送的礼物“'.$gift['name'].'”,快<a href="'.U('Gift/Index/my').'">去看看</a>！';
            }
        } else {
            $message = '礼物提示：'.getUserName($this->mid).'送给了您'.$num.'份“'.$gift['name'].'”,请等待快递发货通知！';
            if ($type == 1) {
                $message = '礼物提示：您收到了'.$num.'份你们赠送的礼物“'.$gift['name'].'”,请等待快递发货通知！';
            }
        }
        LogModel::getInstance()->sendMessage($uid, $message);

        $this->echoJson(1, '提示', '恭喜您，成功的为您的好友送出了礼物！您可以去充值或者完成任务获得更多积分哦！', true);
    }

    /**
     * 返回需要的json数据
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    protected function echoJson($status = 0, $title, $content, $button = false, $data = null)
    {
        ob_end_clean();
        Common::setHeader('application/json', 'utf-8');
        echo json_encode(array(
            'status' => $status,
            'title' => $title,
            'content' => $content,
            'button' => $button,
            'data' => $data,
        ));
        ob_end_flush();
        exit;
    }

    /**
     * 我的礼物
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function my()
    {
        /* # SEO */
        $this->setTitle('我的礼物');
        $this->setKeywords('ThinkSNS,T4,礼物,礼物商城');

        $type = Common::getInput('type', 'get');
        if ($type) {
            $data = LogModel::getInstance()->getOutLog($this->mid, 0, 20);
        } else {
            $data = LogModel::getInstance()->getInLog($this->mid, 0, 20);
        }

        $this->assign('type', $type);
        $this->assign('gifts', $data);

        $this->_rightTops();

        $this->display('my');
    }

    /**
     * 右侧兑换排行榜
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    protected function _rightTops($num = 6)
    {
        $data = LogModel::getInstance()->getTops($num);
        $this->assign('giftTops', $data['data']);
    }

    /**
     * 转增虚拟礼物
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function transfer()
    {
        list($id, $uid, $say) = Common::getInput(array('id', 'uid', 'say'), 'post');
        $data = LogModel::getInstance()->getById($id);
        $data = array_merge($data, GiftModel::getInstance()->getById($data['gid']));
        $uid = intval($uid);
        $say = t($say);
        list($num, $type) = Common::getInput(array('num', 'type'), 'post');
        list($num, $type) = array(intval($num), intval($type));

        /* # 判断是否存在礼物 */
        if (!$data) {
            $this->echoJson(0, '提示', '转增的礼物不存在', false);

        /* # 判断赠送的用户是否不存在 */
        } elseif (!$uid || !model('User')->hasUser($uid, true)) {
            $this->echoJson(0, '提示', '对不起，您赠送的用户不存在', false);

        /* # 判断祝福语是否为空 */
        } elseif (!$say) {
            $this->echoJson(0, '提示', '请输入祝福语', false);

        /* # 判断是否不是虚拟礼物 */
        } elseif ($data['cate'] != 1) {
            $this->echoJson(0, '提示', '您转账的礼物不是虚拟物品！', false);

        /* # 判断礼物是否属于自己 */
        } elseif ($data['inUid'] != $this->mid || $data['notIn'] == 1) {
            $this->echoJson(0, '提示', '该礼物不属于您！', false);

        /* # 判断该礼物是否已经赠送过了 */
        /*} elseif (LogModel::getInstance()->hasGive($data['gid'], $uid, $this->mid)) {*/
            /*$this->echoJson(0, '提示', '您已经赠送过给该用户，请勿重复赠送', false);*/

        /* # 判断转赠的数量是否不合法 */
        } elseif ($num <= 0 || $num > $data['num']) {
            $this->echoJson(0, '提示', '您转赠的数量不合法，必须大于0且小于或等于您收到的数量！', false);

        /* # 判断转赠类似是否不合法 */
        } elseif (!in_array($type, array(1, 2, 3))) {
            $this->echoJson(0, '提示', '不允许的赠送类型，请在页面上正确的选择赠送类型', false);

        /* # 检查是否转增失败 */
        } elseif (!LogModel::getInstance()->transfer($id, $uid, $say, $num, $type)) {
            $this->echoJson(0, '提示', '转增失败！', false);
        }

        /* # 发送提示 */
        $message = '礼物提示：'.getUserName($this->mid).'送给了您'.$data['num'].'份“'.$data['name'].'”,快<a href="'.U('Gift/Index/my').'">去看看</a>！';
        LogModel::getInstance()->sendMessage($uid, $message);

        /* # 转增成功 */
        $this->echoJson(1, '提示', '礼物转赠成功！', true);
    }
} // END class Index extends Controller
class_alias('Apps\Gift\Controller\Index', 'IndexAction');
