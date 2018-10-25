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
class UbankAction extends AdministratorAction
{
    public $pageTitle = array();

    public function _list(){
        array_push($this->pageTab, array(
            'title'   => '用户余额',
            'tabHash' => 'index',
            'url'     => U('admin/Ubank/index'),
        ));
        array_push($this->pageTab, array(
            'title'   => '充值记录',
            'tabHash' => 'recharge_record',
            'url'     => U('admin/Ubank/recharge_record'),
        ));
        array_push($this->pageTab, array(
            'title'   => '消费记录',
            'tabHash' => 'records_consumption',
            'url'     => U('admin/Ubank/records_consumption'),
        ));
    }

//用户余额记录 
    public function index(){

        $this->_list();
        
        $this->pageButton[] = array('title' => '搜索记录', 'onclick' => "admin.fold('search_form')");
        $this->pageKeyList = array('id','uid','email','phone','ubank');
        $this->searchKey = array('email', 'phone', 'uname');
        $this->$searchPostUrl = U('admin/Ubank/index');
        if ($_POST) {
            $_POST['email'] && $map['email'] = $_POST['email'];
            $_POST['phone'] && $map['phone'] = $_POST['phone'];
            $_POST['uname'] && $map['ts_credit_user.uid'] = array('in',$_POST['uname']);
        }
        $list = M('credit_user')->join('join ts_user ON ts_credit_user.uid = ts_user.uid')->field('id,ts_credit_user.uid,ubank,email,phone')->where($map)->findPage(20);
        foreach ($list['data'] as $key => &$value) {
            $value['uid'] = getUserName($value['uid']);
            if(!$value['phone']){

                $value['phone'] = '<font color="red">未绑定</font>';
            }
            if(!$value['email']){

                $value['email'] = '<font color="red">未绑定</font>';
            }
        }
        $money = M('credit_user')->join('join ts_user ON ts_credit_user.uid = ts_user.uid')->field('ubank')->where($map)->select();
        foreach ($money as $k => $v) {
            $sum[$k] = $v['ubank'];
        }
        $list['total'] = array_sum($sum)?array_sum($sum):'0.00';
        $this->displayList($list);
    }

//充值记录 
    public function recharge_record(){

        $this->_list();
        $_REQUEST['tabHash'] = 'recharge_record';
        $this->pageButton[] = array('title' => '搜索记录', 'onclick' => "admin.fold('search_form')");
        $this->pageKeyList = array('rid','serial_number','uid','email','phone','charge_type','change','ctime');
        $this->searchKey = array('uid', 'email', 'phone', 'serial_number', 'start_ctime', 'end_time');
        $this->$searchPostUrl = U('admin/Cyb/recharge_record');
        if ($_POST) {
            $_POST['email'] && $map['email'] = $_POST['email'];
            $_POST['phone'] && $map['phone'] = $_POST['phone'];
            $_POST['serial_number'] && $map['serial_number'] = array('like', '%'.$_POST['serial_number'].'%');
            $_POST['uid'] && $map['ts_credit_record.uid'] = array('in',$_POST['uid']);
            $_POST['start_ctime'] && $start = strtotime($_POST['start_ctime']);
            $_POST['end_time'] && $end = strtotime($_POST['end_time']);
            if($start && $end){
                $map['ts_credit_record.ctime'] = array('between',array("$start","$end"));
            }
        }

        $map['type'] = 2;
        $map['action'] = '充值零用钱';
        $list = M('credit_record')->join('join ts_user ON ts_credit_record.uid = ts_user.uid')->field('rid,serial_number,charge_type,ts_credit_record.change,ts_credit_record.ctime,ts_credit_record.uid,email,phone')->where($map)->order('ts_credit_record.ctime DESC')->findPage(20);
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
            if(!$value['phone']){

                $value['phone'] = '<font color="red">未绑定</font>';
            }
            if(!$value['email']){

                $value['email'] = '<font color="red">未绑定</font>';
            }
            $value['change'] = -$value['change'];
            $value['ctime'] = date('Y-m-d H:i:s', $value['ctime']);
            $value['uid'] = getUserName($value['uid']);
        }
        $money = M('credit_record')->join('join ts_user ON ts_credit_record.uid = ts_user.uid')->field('change')->where($map)->select();
        foreach ($money as $k => $v) {
            $sum[$k] = -floatval($v['change']);
        }
        $list['total'] = array_sum($sum)?array_sum($sum):'0.00';
        $this->displayList($list);
    }

//消费记录 
    public function records_consumption(){

        $this->_list();
        $_REQUEST['tabHash'] = 'records_consumption';
        $this->pageButton[] = array('title' => '搜索记录', 'onclick' => "admin.fold('search_form')");
        $this->pageKeyList = array('rid','serial_number','type','charge_type','uid','oid','change','ctime');
        $this->searchKey = array('uid', 'serial_number', 'type', 'start_ctime', 'end_time');
        $this->$searchPostUrl = U('admin/Cyb/records_consumption');
        if ($_POST) {
            $_POST['uid'] && $map['uid'] = array('in',$_POST['uid']);
            $_POST['serial_number'] && $map['serial_number'] = array('like', '%'.$_POST['serial_number'].'%');
            $_POST['type'] && $map['type'] = $_POST['type'];
            $_POST['start_ctime'] && $start = strtotime($_POST['start_ctime']);
            $_POST['end_time'] && $end = strtotime($_POST['end_time']);
            if($start && $end){
                $map['ctime'] = array('between',array("$start","$end"));
            }
        }
        $map['type'] = array('in','2,4,5,6');
        $map['action'] = array('in',"'领取现金','零用钱转出','充值会员','充值财源币','会员续费'");
        $list = M('credit_record')->where($map)->order('ctime DESC')->findPage(20);

        foreach ($list['data'] as $key => &$value) {
            $value['uid'] = getUserName($value['uid']);
            $value['oid'] = getUserName($value['oid']);
            $value['ctime'] = date('Y-m-d H:i:s', $value['ctime']);
            switch ($value['type']) {
                case '2':
                    if($value['action'] == '充值会员'){
                        $value['type'] = '开通会员';
                    }elseif ($value['action'] == '充值财源币') {
                        $value['type'] = '充值财源币';
                    }elseif ($value['action'] == '会员续费') {
                        $value['type'] = '会员续费';
                    }
                    $value['oid'] = '链我';
                    $value['change'] = -$value['change'];
                    break;
                case '4':
                    $value['type'] = '转账';
                    $value['change'] = -$value['change'];
                    break;
                case '5':
                    $value['type'] = '付款';
                    $value['change'] = -$value['change'];
                    break;
                case '6':
                    $value['type'] = '发红包';
                    break;
            }
            switch ($value['charge_type']) {
                case '0':
                    $value['charge_type'] = '支付宝';
                    break;
                case '1':
                    $value['charge_type'] = '微信';
                    break;
                case '2':
                    $value['charge_type'] = '零用钱';
                    break;
            }
        }
        $money = M('credit_record')->field('change')->where($map)->select();
        foreach ($money as $k => $v) {
            $sum[$k] = -floatval($v['change']);
        }
        $list['total'] = array_sum($sum)?array_sum($sum):'0.00';
        $this->displayList($list);
    } 
}
