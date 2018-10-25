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
class CybAction extends AdministratorAction
{
    public $pageTitle = array();

    public function _list(){
        array_push($this->pageTab, array(
            'title'   => '用户余额',
            'tabHash' => 'index',
            'url'     => U('admin/Cyb/index'),
        ));
        array_push($this->pageTab, array(
            'title'   => '充值记录',
            'tabHash' => 'recharge_record',
            'url'     => U('admin/Cyb/recharge_record'),
        ));
        array_push($this->pageTab, array(
            'title'   => '消费记录',
            'tabHash' => 'records_consumption',
            'url'     => U('admin/Cyb/records_consumption'),
        ));
    }

    public function index(){

        $this->_list();
        
        $this->pageButton[] = array('title' => '搜索记录', 'onclick' => "admin.fold('search_form')");
        $this->pageKeyList = array('id','uid','email','phone','caiyuanbi');
        $this->searchKey = array('email', 'phone', 'uname');
        $this->$searchPostUrl = U('admin/Cyb/index');
        if ($_POST) {
            $_POST['email'] && $map['email'] = $_POST['email'];
            $_POST['phone'] && $map['phone'] = $_POST['phone'];
            $_POST['uname'] && $map['ts_credit_user.uid'] = array('in',$_POST['uname']);
        }
        $list = M('credit_user')->join('join ts_user ON ts_credit_user.uid = ts_user.uid')->field('id,ts_credit_user.uid,caiyuanbi,email,phone')->where($map)->findPage(20);
        foreach ($list['data'] as $key => &$value) {
            $value['uid'] = getUserName($value['uid']);
            if(!$value['phone']){

                $value['phone'] = '<font color="red">未绑定</font>';
            }
            if(!$value['email']){

                $value['email'] = '<font color="red">未绑定</font>';
            }
        }
        $money = M('credit_user')->join('join ts_user ON ts_credit_user.uid = ts_user.uid')->field('caiyuanbi')->where($map)->select();
        foreach ($money as $k => $v) {
            $sum[$k] = floatval($v['caiyuanbi']);
        }
        $list['total'] = array_sum($sum)?array_sum($sum):'0.00';
        $this->displayList($list);
    }

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
        $map['action'] = '充值财源币';
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
        $this->pageKeyList = array('id','bonus_code','bonus_fromuid','to_uid','bonus_money','get_time');
        $this->searchKey = array('bonus_fromuid', 'bonus_code', 'start_ctime', 'end_time');
        $this->$searchPostUrl = U('admin/Cyb/records_consumption');
        if ($_POST) {
            $_POST['bonus_fromuid'] && $map['ts_bonus.bonus_fromuid'] = array('in',$_POST['bonus_fromuid']);
            $_POST['bonus_code'] && $map['bonus_code'] = array('like', '%'.$_POST['bonus_code'].'%');

            $_POST['start_ctime'] && $start = strtotime($_POST['start_ctime']);
            $_POST['end_time'] && $end = strtotime($_POST['end_time']);
            if($start && $end){
                $map['get_time'] = array('between',array("$start","$end"));
            }
        }
        $map['money_type'] = 2;
        $map['to_uid'] = array('gt','0');
        $list = M('bonus')->join('ts_bonus_list ON ts_bonus_list.bonus_code=ts_bonus.bonus_code')->field('ts_bonus_list.id,ts_bonus.bonus_code,ts_bonus.bonus_fromuid,to_uid,get_time,bonus_money')->where($map)->order('get_time desc')->findPage(20);
        // var_dump(M()->getLastSql());
        foreach ($list['data'] as $key => &$value) {
            $value['bonus_fromuid'] = getUserName($value['bonus_fromuid']);
            $value['to_uid'] = getUserName($value['to_uid']);
            $value['get_time'] = date('Y-m-d H:i:s', $value['get_time']);
        }
        $money = M('bonus')->join('ts_bonus_list ON ts_bonus_list.bonus_code=ts_bonus.bonus_code')->field('bonus_money')->where($map)->select();
        foreach ($money as $k => $v) {
            $sum[$k] = $v['bonus_money'];
        }
        $list['total'] = array_sum($sum)?array_sum($sum):'0.00';
        $this->displayList($list);
    }    

}
