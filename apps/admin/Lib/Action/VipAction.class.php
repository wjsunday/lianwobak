<?php
/**
 * 后台，用户管理控制器.
 *
 * @author liuxiaoqing <liuxiaoqing@zhishisoft.com>
 *
 * @version TS3.0
 */
// 加载后台控制器
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class VipAction extends AdministratorAction
{
    public $pageTitle = array();


    public function index(){

        array_push($this->pageTab, array(
            'title'   => '会员列表',
            'tabHash' => 'index',
            'url'     => U('admin/Vip/index'),
        ));
        array_push($this->pageTab, array(
            'title'   => '充值记录',
            'tabHash' => 'addSlide',
            'url'     => U('admin/Vip/rechargeRecord'),
        ));
        $this->pageButton[] = array('title' => '搜索记录', 'onclick' => "admin.fold('search_form')");
        $this->pageKeyList = array('vid','vip_name','is_personal','serial_number','email','phone','uname','charge_type','Registration_time','due_time','charge_value','status');
        $this->searchKey = array('email', 'phone', 'serial_number', 'p_id','is_personal' ,'status');
        $this->$searchPostUrl = U('admin/Vip/index');
        $this->_listpk = 'serial_number';
        if ($_POST) {
            $_POST['email'] && $map['email'] = $_POST['email'];
            $_POST['phone'] && $map['phone'] = $_POST['phone'];
            $_POST['is_personal'] && $map['is_personal'] = $_POST['is_personal'];
            $_POST['serial_number'] && $map['serial_number'] = array('like', '%'.$_POST['serial_number'].'%');
            $_POST['p_id'] && $map['p_id'] = $_POST['p_id'];
            $_POST['status'] && $map['status'] = $_POST['status'];
        }
        M('vip')->where('status=2')->delete();
        $list = D('vip')->join('join ts_user ON ts_vip.u_id = ts_user.uid')->field('vid,serial_number,vip_name,charge_value,Registration_time,due_time,charge_type,p_id,number,is_personal,phone,uname,email,status')->where($map)->findPage(20);
        // var_dump($list);exit;
        $uid = $this->mid;
        foreach ($list['data'] as $key => &$value) {
        
            switch ($value['charge_type']) {
                case '0':
                    $value['charge_type'] = '<font color="">支付宝</font>';
                    break;
                case '1':
                    $value['charge_type'] = '<font color="">微信</font>';
                    break;
                case '2':
                    $value['charge_type'] = '<font color="">零用钱</font>';
                    break;
            }
            switch ($value['is_personal']) {
                case '1':
                    $value['is_personal'] = '<font color="">个人会员</font>';
                    break;
                case '2':
                    $value['is_personal'] = '<font color="">商家会员</font>';
                    break;
            }
            switch ($value['status']) {
                case '0':
                    $value['status'] = '<font color="red">支付失败</font>';
                    break;
                case '1':
                    $value['status'] = '<font color="">正在使用</font>';
                    break;
                case '2':
                    $value['status'] = '<font color="red">已过期</font>';
                    break;    
            }
            if(!$value['phone']){

                $value['phone'] = '<font color="red">未绑定</font>';
            }
            if(!$value['email']){

                $value['email'] = '<font color="red">未绑定</font>';
            }
            if($value['vip_Price'] == 0){

                $value['vip_Price'] = '<font color="">赠送</font>';
            }
            $value['Registration_time'] = date('Y-m-d H:i:s', $value['Registration_time']);
            $value['due_time'] = date('Y-m-d H:i:s', $value['due_time']);
        }
        $this->displayList($list);
    }



    public function rechargeRecord()
    {
        array_push($this->pageTab, array(
            'title'   => '会员列表',
            'tabHash' => 'index',
            'url'     => U('admin/Vip/index'),
        ));
        array_push($this->pageTab, array(
            'title'   => '充值记录',
            'tabHash' => 'addSlide',
            'url'     => U('admin/Vip/rechargeRecord'),
        ));
        $this->pageButton[] = array('title' => '搜索记录', 'onclick' => "admin.fold('search_form')");
        $this->pageKeyList = array('rid','serial_number','charge_type','uid','action','change','ctime');
        $this->searchKey = array('serial_number', 'uid' ,'action');
        $this->$searchPostUrl = U('admin/Vip/rechargeRecord');
        if ($_POST) {
            $_POST['serial_number'] && $map['serial_number'] = array('like', '%'.$_POST['serial_number'].'%');
            $_POST['uid'] && $map['uid'] = array('in',$_POST['uid']);
            $_POST['action'] && $map['action'] = $_POST['action'];
        }    
        $map['type'] = 2;
        $map['action'] = array("in","'充值会员','会员续费'");

        $list = M('credit_record')->field('rid,serial_number,charge_type,uid,action,ts_credit_record.change,ctime')->where($map)->order('ctime desc')->findPage(20);
        foreach ($list['data'] as $key => &$value) {
            switch ($value['charge_type']) {
                case '0':
                    $value['charge_type'] = '<font color="">支付宝</font>';
                    break;
                case '1':
                    $value['charge_type'] = '<font color="">微信</font>';
                    break;
                case '2':
                    $value['charge_type'] = '<font color="">零用钱</font>';
                    break;
            }
            $value['change'] = -$value['change'];
            $value['ctime'] = date('Y-m-d H:i:s', $value['ctime']);
        }
        $money = M('credit_record')->field('rid,ts_credit_record.change')->where($map)->select();
        foreach ($money as $k => $v) {
            $sum[$k] = -floatval($v['change']);
        }
        $list['total'] = array_sum($sum)?array_sum($sum):'0.00';
        $this->displayList($list);
    }

    public function del(){

        $id = $_GET['id'];
        // var_dump($id);
        $del = M('vip')->where("vid = $id")->delete();
        if($del){

            $this->success('删除成功');
        }
    }

    
    public function personalSet(){

        $list[] = array('vip' => 个人会员,'url' => U('admin/Vip/personalSet'));
        $list[] = array('vip' => 商家会员,'url' => U('admin/Vip/businessSet'));
        $personal_pri = M('user_group')->field('user_group_name,user_group_id')->where('is_vip = 1')->select();
        $pid['u_g_id'] = $_POST['pid'];
        $price = M('vip_price')->field('vip_price,content')->where($pid)->find();
        if($price){
            $this->ajaxReturn($price);
        }
        $this->assign('personal_pri',$personal_pri);
        $this->assign('list',$list);
        $this->display();

    }

    public function businessSet(){

        $list[] = array('vip' => 个人会员,'url' => U('admin/Vip/personalSet'));
        $list[] = array('vip' => 商家会员,'url' => U('admin/Vip/businessSet'));
        $personal_pri = M('user_group')->field('user_group_name,user_group_id')->where('is_business_vip = 1')->select();
        $pid['u_g_id'] = $_POST['pid'];
        $price = M('vip_price')->field('vip_price,content')->where($pid)->find();
        if($price){
            $this->ajaxReturn($price);
        }
        $this->assign('personal_pri',$personal_pri);
        $this->assign('list',$list);
        $this->display();

    }

    public function set_price(){

        $pid = $_POST['pid'];
        if(!$pid){
            $this->error('请选择会员类型编辑！');
        }
        // var_dump($pid);
        $data['vip_price'] = intval($_POST['num']);
        $data['content'] = $_POST['content'];
        if(!$data['vip_price']){

            $this->error('金额不能为空');
        }

        if(!is_numeric($data['vip_price'])){

            $this->error('非法操作');
        }

        if(!$data['content']){

            $this->error('内容不能为空');
        }

        $vip_price = M('vip_price')->where("u_g_id = $pid")->save(array('vip_price' => $data['vip_price'] ,'content' => $data['content']));

        if($vip_price){

            $this->success('编辑成功');
        }else{

            $this->error('请编辑');
        }
    }

    public function discountSet(){

        array_push($this->pageTab, array(
            'title'   => '折扣列表',
            'tabHash' => 'index',
            'url'     => U('admin/Vip/discountSet'),
        ));
        array_push($this->pageTab, array(
            'title'   => '添加折扣',
            'tabHash' => 'addSlide',
            'url'     => U('admin/Vip/discountEdit'),
        ));
        $this->pageKeyList = array('id','is_personal','number','content','discount','DOACTION');
        $list = D('vip_stages')->findPage(20);
        foreach ($list['data'] as $key => &$value) {

            switch ($value['is_personal']) {
                case '1':
                    $value['is_personal'] = '<font color="">个人会员</font>';
                    break;
                case '2':
                    $value['is_personal'] = '<font color="">商家会员</font>';
                    break;
            }
            $value['DOACTION'] = '<a href="'.U('admin/Vip/discountEdit', array('id' => $value['id'])).'">编辑</a> '.'|<a href="'.U('admin/Vip/discountDel', array('id' => $value['id'])).'">删除</a> ';
        }
        $this->displayList($list);
    }

    public function discountEdit(){

        array_push($this->pageTab, array(
            'title'   => '折扣列表',
            'tabHash' => 'index',
            'url'     => U('admin/Vip/discountSet'),
        ));
        array_push($this->pageTab, array(
            'title'   => '添加折扣',
            'tabHash' => 'addSlide',
            'url'     => U('admin/Vip/discountEdit'),
        ));
        
        $this->pageKeyList = array('number','content','discount');

        $data = D('vip_stages')->where('`id` = '.intval($_GET['id']))->find();
        $this->opt['number'] = $data['number'];
        $this->opt['number'] = $data['content'];
        $this->opt['number'] = $data['discount'];

        $this->savePostUrl = U('admin/Vip/discountAdd', array('id' => intval($_GET['id'])));
        $this->displayConfig($data);
    }

    public function discountAdd(){
        list($id,$number, $content, $discount) = array($_GET['id'],$_POST['number'], $_POST['content'], $_POST['discount']);
        list($id,$number, $content, $discount) = array(intval($id),intval($number), t($content),intval($discount));
        $data = array(
            'number' => $number,
            'content' => $content,
            'discount'  => $discount,
        );
        if ($id) {
            D('vip_stages')->where('`id` = '.$id)->save($data);
            $this->assign('jumpUrl', U('admin/Vip/discountSet'));
            $this->success('修改成功');
        }
        M('vip_stages')->data($data)->add() or $this->error('添加失败');

        $this->assign('jumpUrl', U('admin/Vip/discountSet'));
        $this->success('添加成功');

    }

    public function discountDel(){

        M('vip_stages')->where('`id` = '.intval($_GET['id']))->delete();
        $this->success('删除成功');
    }

}
