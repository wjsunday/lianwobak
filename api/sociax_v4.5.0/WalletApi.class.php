<?php

include_once SITE_PATH.'/apps/bonus/Common/Bonus.class.php';
use Api;
use Apps\BaseBonus\Bonus;
/**
 * @author jason
 */
class WalletApi extends Api
{
    // C:\wamp\www\thinksns.com\src\vendor\zhiyicx\thinksns-old-code\src\addons\model
    // /alidata/www/lianwo/src/vendor/zhiyicx/thinksns-old-code/src/addons/model
    public function aa(){
        $uids = array(5);
        $extras = array(
            'title' => '通知',
            'content_type' => 'text',
            'extras' => array('key'=>'测试')
        );
        return model('Jpush')->message($uids,'cc',$extras);
    }

    public function index(){
        $money = M('credit_user')->field('caiyuanbi,ubank')->where("uid={$this->mid}")->find();
        $t = time();
        $start = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
        $end = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t));
        $earningsToday = M('bonus_list')
            ->join('ts_bonus ON ts_bonus_list.bonus_code=ts_bonus.bonus_code')
            ->field('ts_bonus.money_type,ts_bonus_list.bonus_money')
            ->where("to_uid={$this->mid} and {$start} <= get_time and get_time <= {$end}")
            ->select();
        foreach ($earningsToday as $key => $value) {
            switch ($value['money_type']) {
                case '2':
                    $sum[$key] = $value['bonus_money'];
                    break;
                case '1':
                    $sum1[$key] = $value['bonus_money'];
                    break;
            }
        }
        $alreadyUse = M('bonus')
            ->join('ts_bonus_list ON ts_bonus.bonus_code=ts_bonus_list.bonus_code')
            ->field('ts_bonus_list.bonus_money,ts_bonus.money_type')
            ->where("ts_bonus_list.to_uid>0 and ts_bonus.bonus_fromuid={$this->mid}")
            ->select();

        foreach ($alreadyUse as $key => $value) {
            switch ($value['money_type']) {
                case '1':
                    $ubankSum[$key] = $value['bonus_money'];
                    break;
                case '2':
                    $cybSum[$key] = $value['bonus_money'];
                    break;
            }
        }

        $transferUse = M('credit_record_ubank')->field('ubank_change')->where("oid={$this->mid}")->select();
        foreach ($transferUse as $key => $value) {
            $transferUbank[$key] = $value['ubank_change'];
        }

        $data = array();
        $data['status'] = 1;
        $data['message'] = '操作成功';
        $data['avatar'] = model('Avatar')->init($this->mid)->getUserAvatar()['avatar_middle'];
        $data['caiyuanbi'] = floatval($money['caiyuanbi']);
        $data['ubank'] = floatval($money['ubank']);
        $cybTotal_money = array_sum($sum)?array_sum($sum):0;
        $ubankTotal_money = array_sum($sum1)?array_sum($sum1):0;
        $data['total_amount'] = $cybTotal_money + $ubankTotal_money;

        $data['alreadyUseCyb'] = array_sum($cybSum)?array_sum($cybSum):0;
        $ubankSum = array_sum($ubankSum)?array_sum($ubankSum):0;
        $transferUbank = array_sum($transferUbank)?array_sum($transferUbank):0;
        $data['alreadyUseUbank'] = floatval($ubankSum + $transferUbank);
        if(model('UserGroupLink')->userGroupIcon($this->mid,1))
        {
            $data['icon'] = model('UserGroupLink')->userGroupIcon($this->mid,1);
        }
        if(model('UserGroupLink')->userGroupIcon($this->mid,2))
        {
            $data['vip_icon'] = model('UserGroupLink')->userGroupIcon($this->mid,2);
        }
        return $data;


    }

    //账单
    public function bill(){

        if($this->data['type'] == 1){

            $bill = M('credit_record')->field('*')->where("uid={$this->mid} and type!=1 and type!=6")->order('ctime desc')->findPage(10);
            foreach ($bill['data'] as $key => $value) {
                
                switch ($value['type']) {
                    case '3':
                        $Total[$key]['transactionType'] = $value['action'];
                        $Total[$key]['avatar'] = model('Avatar')->init($value['uid'])->getUserAvatar()['avatar_middle'];
                        $Total[$key]['toUname'] = getUserName($value['uid']);
                        $Total[$key]['des'] = $value['des'];
                        $Total[$key]['money'] = $value['change'];
                        $Total[$key]['serial_number'] = $value['serial_number'];
                        $tixian = M('credit_order')->field('status')->where("order_number={$value['serial_number']}")->find();
                        $Total[$key]['transaction_status'] = $tixian['status'];
                        $Total[$key]['ctime'] = date('Y-m-d H:i',$value['ctime']);
                        $Total[$key]['payment_type'] = 0;
                        $Total[$key]['is_withdrawals'] = 1;
                        break;
                    case '4':
                        $Total[$key]['transactionType'] = '转账-'.getUserName($value['oid']);
                        $Total[$key]['avatar'] = model('Avatar')->init($value['oid'])->getUserAvatar()['avatar_middle'];
                        $Total[$key]['toUname'] = getUserName($value['oid']);
                        $Total[$key]['des'] = $value['des'];
                        $Total[$key]['money'] = $value['change'];
                        $Total[$key]['serial_number'] = $value['serial_number'];
                        $Total[$key]['transaction_status'] = 1;
                        $Total[$key]['ctime'] = date('Y-m-d H:i',$value['ctime']);
                        $Total[$key]['is_withdrawals'] = 0;
                        if($value['action'] == '零用钱转出'){
                            $Total[$key]['payment_type'] = 1;
                        }elseif ($value['action'] == '零用钱转入') {
                            $Total[$key]['payment_type'] = 0;
                        }
                        break; 
                    case '5':
                        $Total[$key]['transactionType'] = '付款-'.getUserName($value['oid']);
                        $Total[$key]['avatar'] = model('Avatar')->init($value['oid'])->getUserAvatar()['avatar_middle'];
                        $Total[$key]['toUname'] = getUserName($value['oid']);
                        $Total[$key]['des'] = $value['des'];
                        $Total[$key]['serial_number'] = $value['serial_number'];
                        $Total[$key]['money'] = $value['change'];
                        $Total[$key]['transaction_status'] = 1;
                        $Total[$key]['ctime'] = date('Y-m-d H:i',$value['ctime']);
                        $Total[$key]['is_withdrawals'] = 0;
                        if($value['action'] == '零用钱转出'){
                            $Total[$key]['payment_type'] = 1;
                        }elseif ($value['action'] == '零用钱转入') {
                            $Total[$key]['payment_type'] = 0;
                        }
                        break; 
                    default:
                        $Total[$key]['ctime'] = date('Y-m-d H:i',$value['ctime']);
                        $Total[$key]['transactionType'] = $value['action'];
                        $Total[$key]['money'] = $value['change'];
                        $Total[$key]['serial_number'] = $value['serial_number'];
                        $Total[$key]['avatar'] = model('Avatar')->init($value['uid'])->getUserAvatar()['avatar_middle'];
                        $Total[$key]['toUname'] = getUserName($value['uid']);
                        $Total[$key]['payment_type'] = 1;
                        $Total[$key]['transaction_status'] = 1;
                        $Total[$key]['is_withdrawals'] = 0;
                        break;            
                }
            }

        }elseif ($this->data['type'] == 2) {
            $bill = M('bonus_list')
            ->join('ts_bonus ON ts_bonus_list.bonus_code=ts_bonus.bonus_code')
            ->field('ts_bonus_list.id,
                ts_bonus.bonus_type,
                ts_bonus.money_type,
                ts_bonus.bonus_msg,
                ts_bonus_list.bonus_fromuid,
                ts_bonus_list.bonus_money,
                ts_bonus_list.total_amount,
                ts_bonus.send_time,
                ts_bonus.status,
                ts_bonus.charge_type,
                ts_bonus_list.bonus_code,
                get_time,
                to_uid')
            ->where("to_uid={$this->mid} and ts_bonus.money_type=1 or ts_bonus_list.bonus_fromuid={$this->mid} and ts_bonus.money_type=1 and to_uid!=0")
            ->order('ts_bonus_list.get_time desc, ts_bonus.send_time desc')
            ->findPage(10);
            foreach ($bill['data'] as $key => $value) {
                   $fromUid[$key] = $value['bonus_fromuid'];
                   $toUid[$key] = $value['to_uid'];
                   if($fromUid[$key] == $this->mid){

                        switch ($value['bonus_type']) {
                            case '1':
                                $Total[$key]['bonus_type'] = '发出-普通红包';
                                break;
                            case '2':
                                $Total[$key]['bonus_type'] = '发出-手气红包';
                                break;
                        }
                        $Total[$key]['bonus_msg'] = $value['bonus_msg'];
                        $Total[$key]['bonus_fromuid'] = $value['bonus_fromuid'];
                        $Total[$key]['uname'] = getUserName($value['to_uid']);
                        $Total[$key]['to_uid'] = $value['to_uid'];
                        $Total[$key]['avatar'] = model('Avatar')->init($value['bonus_fromuid'])->getUserAvatar()['avatar_middle'];
                        if(model('UserGroupLink')->userGroupIcon($value['bonus_fromuid'],1))
                        {
                            $Total[$key]['icon'] = model('UserGroupLink')->userGroupIcon($value['bonus_fromuid'],1);
                        }
                        if(model('UserGroupLink')->userGroupIcon($value['bonus_fromuid'],2))
                        {
                            $Total[$key]['vip_icon'] = model('UserGroupLink')->userGroupIcon($value['bonus_fromuid'],2);
                        }
                        $Total[$key]['bonus_money'] = '-'.$value['total_amount'];
                        $Total[$key]['bonus_code'] = $value['bonus_code'];
                        $Total[$key]['type'] = $value['status'];
                        $Total[$key]['ctime'] = date('Y-m-d H:i',$value['send_time']);
                        $Total[$key]['is_pay'] = 1;
                        $Total[$key]['pay_time'] = date('Y-m-d H:i',$value['send_time']);
                        switch ($value['charge_type']) {
                            case '0':
                                $Total[$key]['payment_method'] = '支付宝';
                                break;
                            case '1':
                                $Total[$key]['payment_method'] = '微信';
                                break;
                            case '2':
                                $Total[$key]['payment_method'] = '零用钱';
                                break;
                        }
                        
                        $Total[$key]['transaction_type'] = 0;

                   }elseif ($toUid[$key] == $this->mid) {

                       switch ($value['bonus_type']) {
                            case '1':
                                $Total[$key]['bonus_type'] = '收到-普通红包';
                                break;
                            case '2':
                                $Total[$key]['bonus_type'] = '收到-手气红包';
                                break;
                        }
                        $Total[$key]['bonus_msg'] = $value['bonus_msg'];
                        $Total[$key]['to_uid'] = $value['bonus_fromuid'];
                        $Total[$key]['uname'] = getUserName($value['bonus_fromuid']);
                        $Total[$key]['bonus_fromuid'] = $value['to_uid'];
                        $Total[$key]['avatar'] = model('Avatar')->init($value['bonus_fromuid'])->getUserAvatar()['avatar_middle'];
                        if(model('UserGroupLink')->userGroupIcon($value['bonus_fromuid'],1))
                        {
                            $Total[$key]['icon'] = model('UserGroupLink')->userGroupIcon($value['bonus_fromuid'],1);
                        }
                        if(model('UserGroupLink')->userGroupIcon($value['bonus_fromuid'],2))
                        {
                            $Total[$key]['vip_icon'] = model('UserGroupLink')->userGroupIcon($value['bonus_fromuid'],2);
                        }
                        $Total[$key]['bonus_money'] = '+'.$value['bonus_money'];
                        $Total[$key]['bonus_code'] = $value['bonus_code'];
                        $Total[$key]['type'] = 1;
                        $Total[$key]['ctime'] = date('Y-m-d H:i',$value['get_time']);
                        $Total[$key]['is_pay'] = 1;
                        $Total[$key]['pay_time'] = date('Y-m-d H:i',$value['send_time']);
                        switch ($value['charge_type']) {
                            case '0':
                                $Total[$key]['payment_method'] = '支付宝';
                                break;
                            case '1':
                                $Total[$key]['payment_method'] = '微信';
                                break;
                            case '2':
                                $Total[$key]['payment_method'] = '零用钱';
                                break;
                        }
                        $Total[$key]['transaction_type'] = 1;
                   }
               }   
        }
        if($bill['data']){
            self::success(
                array(
                    'status' => 1,
                    'message' => '操作成功',
                    'bills' => $Total,
                    'totalPages' => $bill['totalPages'],
                    'totalRows' => $bill['totalRows'],
                    'nowPage' => $bill['nowPage'],
                ));
        }else{
            self::error(array(
                'status'  => 0,
                'message' => '暂无数据',
            ));
        }
    }

//账单详情
    public function transferDetails(){

        $map['serial_number'] = $this->data['serial_number'];
        $map['uid'] = $this->mid;
        $bill = M('credit_record')->field('*')->where($map)->find();
        if($bill['oid'] == ''){
            $data['avatar'] = model('Avatar')->init($bill['uid'])->getUserAvatar()['avatar_middle'];
            $data['toUname'] = getUserName($bill['uid']);
        }else{
            $data['avatar'] = model('Avatar')->init($bill['oid'])->getUserAvatar()['avatar_middle'];
            $data['toUname'] = getUserName($bill['oid']);
        }
        
        $data['money'] = $bill['change'];
        $data['transactionType'] = $bill['action'];
        if($bill['type'] == 3){
            
            $tixian = M('credit_order')->field('status')->where("order_number={$bill['serial_number']}")->find();
            $data['transaction_status'] = $tixian['status'];
        }else{

            $data['transaction_status'] = 1;
        }
        switch ($bill['charge_type']) {
            case '0':
                $data['payment_method'] = '支付宝';
                break;
            case '1':
                $data['payment_method'] = '微信';
                break;
            case '2':
                $data['payment_method'] = '零用钱';
                break;
        }
        $data['ctime'] = date('Y-m-d H:i:s',$bill['ctime']);
        $data['serial_number'] = $bill['serial_number'];
        $data['status'] = 1;
        $data['message'] = '操作成功';
        if($bill){
            return $data;
        }else{
            self::error(array(
                'status'  => 0,
                'message' => '暂无数据',
            ));
        }
                
    }

//转账查询记录

    public function query(){

        $map = $this->data['text'];
        $bill = M('credit_record_ubank')->field('oid,uid')->where("oid={$this->mid} or uid={$this->mid}")->select();
        foreach ($bill as $key => $value) {
            $uid[$key] = $value['oid'];
            $oid[$key] = $value['uid'];
            if($uid[$key] == $this->mid){
                $data[$key] = getUserName($value['uid']);
            }elseif ($oid[$key] == $this->mid) {
                $data[$key] = getUserName($value['oid']);
            }
        }
        $temp = array_unique($data);
        unset($data);
        $i = 0;
        foreach ($temp as $key => $value) {
            $pos = strstr($value,$map);
            if($pos){
                $data['data'][$i]['name'] = $value;
                $data['data'][$i]['toUid'] = $this->getUid("'".$value."'");
                $i++;
            }

        }
       
        if($data){
            $data['status'] = 1;
            $data['message'] = '操作成功';
        }else{
            $data['status'] = 0;
            $data['message'] = '无该用户记录';
        }

        return $data;
    }

    public function transferQueryLog(){
        $tt = $this->data['time'];
        $t = strtotime($tt);
        if($t){
            $start = mktime(0,0,0,date("m",$t),1,date("Y",$t));
            $end = mktime(23,59,59,date("m",$t),date('t'),date("Y",$t));
            $map['ctime'] = array('between',array("$start","$end"));
        }
        $map['type'] = array('between',array(2,5));
        $map['uid'] = $this->mid;
        $this->data['toUname'] && $map['oid'] = array("in",$this->getUid($this->data['toUname']));
        $info = M('credit_record')->field('*')->where($map)->order('ctime desc')->findPage(10);
        foreach ($info['data'] as $key => $value) {

            switch ($value['type']) {
                    case '3':
                        $Total[$key]['transactionType'] = $value['action'];
                        $Total[$key]['avatar'] = model('Avatar')->init($value['uid'])->getUserAvatar()['avatar_middle'];
                        $Total[$key]['toUname'] = getUserName($value['uid']);
                        $Total[$key]['des'] = $value['des'];
                        $Total[$key]['money'] = $value['change'];
                        $Total[$key]['serial_number'] = $value['serial_number'];
                        $tixian = M('credit_order')->field('status')->where("order_number={$value['serial_number']}")->find();
                        $Total[$key]['transaction_status'] = $tixian['status'];
                        $Total[$key]['ctime'] = date('Y-m-d H:i',$value['ctime']);
                        $Total[$key]['payment_type'] = 0;
                        $Total[$key]['is_withdrawals'] = 1;
                        break;
                    case '4':
                        $Total[$key]['transactionType'] = '转账-'.getUserName($value['oid']);
                        $Total[$key]['avatar'] = model('Avatar')->init($value['oid'])->getUserAvatar()['avatar_middle'];
                        $Total[$key]['toUname'] = getUserName($value['oid']);
                        $Total[$key]['des'] = $value['des'];
                        $Total[$key]['money'] = $value['change'];
                        $Total[$key]['serial_number'] = $value['serial_number'];
                        $Total[$key]['transaction_status'] = 1;
                        $Total[$key]['ctime'] = date('Y-m-d H:i',$value['ctime']);
                        $Total[$key]['is_withdrawals'] = 0;
                        if($value['action'] == '零用钱转出'){
                            $Total[$key]['payment_type'] = 1;
                        }elseif ($value['action'] == '零用钱转入') {
                            $Total[$key]['payment_type'] = 0;
                        }
                        break; 
                    case '5':
                        $Total[$key]['transactionType'] = '付款-'.getUserName($value['oid']);
                        $Total[$key]['avatar'] = model('Avatar')->init($value['oid'])->getUserAvatar()['avatar_middle'];
                        $Total[$key]['toUname'] = getUserName($value['oid']);
                        $Total[$key]['des'] = $value['des'];
                        $Total[$key]['serial_number'] = $value['serial_number'];
                        $Total[$key]['money'] = $value['change'];
                        $Total[$key]['transaction_status'] = 1;
                        $Total[$key]['ctime'] = date('Y-m-d H:i',$value['ctime']);
                        $Total[$key]['is_withdrawals'] = 0;
                        if($value['action'] == '零用钱转出'){
                            $Total[$key]['payment_type'] = 1;
                        }elseif ($value['action'] == '零用钱转入') {
                            $Total[$key]['payment_type'] = 0;
                        }
                        break; 
                    default:
                        $Total[$key]['ctime'] = date('Y-m-d H:i',$value['ctime']);
                        $Total[$key]['transactionType'] = $value['action'];
                        $Total[$key]['money'] = $value['change'];
                        $Total[$key]['serial_number'] = $value['serial_number'];
                        $Total[$key]['avatar'] = model('Avatar')->init($value['uid'])->getUserAvatar()['avatar_middle'];
                        $Total[$key]['toUname'] = getUserName($value['uid']);
                        $Total[$key]['payment_type'] = 1;
                        $Total[$key]['transaction_status'] = 1;
                        $Total[$key]['is_withdrawals'] = 0;
                        break;            
                }
        }
        // var_dump(M()->getLastSql());exit;
        // var_dump($data);exit;
        if($info['data']){
            self::success(array(
                    'status'=>1,
                    'message'=>'操作成功',
                    'bills'=>$Total,
                    'totalPages' => $info['totalPages'],
                    'totalRows' => $info['totalRows'],
                    'nowPage' => $info['nowPage'],
                ));
        }else{
            self::error(array(
                'status'  => 0,
                'message' => '暂无数据',
            ));
        }
    }

    public function getUid($uname){
        $where['uname'] = array('like','%'.$uname.'%');
        $data = M('user')->field('uid')->where($where)->select();
        foreach ($data as $key => $value) {
            $uid[$key] = $value['uid'];
        }
        $uid = implode(',', $uid);
        return $uid;
    }

//今日收益
    public function earningsToday(){
        
        $t = time();
        $start = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
        $end = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t));
        $list = M('bonus_list')
            ->join('ts_bonus ON ts_bonus_list.bonus_code=ts_bonus.bonus_code')
            ->field('ts_bonus_list.id,
                ts_bonus_list.bonus_type,
                ts_bonus.money_type,
                ts_bonus.bonus_msg,
                ts_bonus_list.bonus_fromuid,
                ts_bonus_list.bonus_money,
                ts_bonus_list.id,
                ts_bonus_list.status,
                get_time,
                eid,
                ts_bonus_list.expire_time')
            ->where("to_uid={$this->mid} and {$start} <= get_time and get_time <= {$end}")
            ->order('get_time desc')
            ->findPage(10);
        foreach ($list['data'] as $key => $value) {

            switch ($value['money_type']) {
                case '2':
                    $data[$key]['expire_time'] = strtotime($value['expire_time']);
                    $data[$key]['status'] = $value['status'];
                    break;
                case '1':
                    $data[$key]['get_time'] = $value['get_time'];
                    $data[$key]['bonus_msg'] = $value['bonus_msg'];
                    break;
            }
            if($value['eid'] != 0){
                $data[$key]['event_name'] = $this->eventName($value['eid']);
            }
            $data[$key]['fromUname'] = '红包-'.getUserName($value['bonus_fromuid']);
            $data[$key]['avatar'] = model('Avatar')->init($value['bonus_fromuid'])->getUserAvatar()['avatar_middle'];
            $data[$key]['bonus_money'] = $value['bonus_money'];
            $data[$key]['bonus_id'] = $value['id'];
            $data[$key]['money_type'] = $value['money_type'];
            $data[$key]['get_time'] = date('H:i',$value['get_time']);

        }

        $earningsToday = M('bonus_list')
            ->join('ts_bonus ON ts_bonus_list.bonus_code=ts_bonus.bonus_code')
            ->field('ts_bonus.money_type,ts_bonus_list.bonus_money')
            ->where("to_uid={$this->mid} and {$start} <= get_time and get_time <= {$end}")
            ->select();
        foreach ($earningsToday as $key => $value) {
            switch ($value['money_type']) {
                case '2':
                    $sum[$key] = $value['bonus_money'];
                    break;
                case '1':
                    $sum1[$key] = $value['bonus_money'];
                    break;
            }
        }

        if($list['data']){
            self::success(array(
                    'message'=>'操作成功',
                    'data'=>$data,
                    'cybTotal_money'=>array_sum($sum)?array_sum($sum):0,
                    'ubankTotal_money'=>array_sum($sum1)?array_sum($sum1):0,
                    'totalPages' => $list['totalPages'],
                    'totalRows' => $list['totalRows'],
                    'nowPage' => $list['nowPage'],
                ));
        }else{
            self::error(array(
                'status'  => 0,
                'message' => '暂无数据',
            ));
        }

    }

 //发出财源列表   
    public function envelopesCybList(){

        if($this->data['time']){

            $tt = $this->data['time'];
            $t = strtotime($tt);
            $start = mktime(0,0,0,date("m",$t),1,date("Y",$t));
            $end = mktime(23,59,59,date("m",$t),date('t'),date("Y",$t));

            $data = M('bonus')->field('bonus_fromuid,bonus_code,eid,send_time,id,bonus_many,total_amount,bonus_msg')->where("bonus_fromuid={$this->mid} and money_type=2 and {$start}<=send_time and send_time<={$end}")->order('send_time desc')->findPage(10);

            foreach ($data['data'] as $key => $value) {
                if($value['eid'] != 0){
                    $data['data'][$key]['event_name'] = $this->eventName($value['eid']);
                }
                $bonus_code[$key] = $value['bonus_code'];
                $bonusDetail = Bonus::getBonusDetail($value['bonus_code'],1,$this->page);
                $data['data'][$key]['has_get'] = $bonusDetail['bonus_gets']['has_get'].'/'.$value['bonus_many'];
                $data['data'][$key]['uname'] = getUserName($value['bonus_fromuid']);
                $data['data'][$key]['send_time'] = date('m-d',$value['send_time']);
                $data['data'][$key]['avatar'] = model('Avatar')->init($value['bonus_fromuid'])->getUserAvatar()['avatar_middle'];
            }
            
            $sum = M('bonus')
            ->join('ts_bonus_list ON ts_bonus.bonus_code=ts_bonus_list.bonus_code')
            ->field('ts_bonus_list.bonus_money')
            ->where("ts_bonus_list.to_uid>0 and ts_bonus.bonus_fromuid={$this->mid} and ts_bonus.money_type=2 and {$start}<=ts_bonus.send_time and ts_bonus.send_time<={$end}")
            ->select();
            foreach ($sum as $key => $value) {
                $sum[$key] = $value['bonus_money'];
            }
        }else{

            $data = M('bonus')->field('bonus_fromuid,bonus_code,send_time,eid,id,bonus_many,total_amount,bonus_msg')->where("bonus_fromuid={$this->mid} and money_type=2")->order('send_time desc')->findPage(10);

            foreach ($data['data'] as $key => $value) {
                if($value['eid'] != 0){
                    $data['data'][$key]['event_name'] = $this->eventName($value['eid']);
                }
                $bonus_code[$key] = $value['bonus_code'];
                $bonusDetail = Bonus::getBonusDetail($value['bonus_code'],1,$this->page);
                $data['data'][$key]['has_get'] = $bonusDetail['bonus_gets']['has_get'].'/'.$value['bonus_many'];
                $data['data'][$key]['uname'] = getUserName($value['bonus_fromuid']);
                $data['data'][$key]['send_time'] = date('m-d',$value['send_time']);
                $data['data'][$key]['avatar'] = model('Avatar')->init($value['bonus_fromuid'])->getUserAvatar()['avatar_middle'];
            }
            
            $sum = M('bonus')
            ->join('ts_bonus_list ON ts_bonus.bonus_code=ts_bonus_list.bonus_code')
            ->field('ts_bonus_list.bonus_money')
            ->where("ts_bonus_list.to_uid>0 and ts_bonus.bonus_fromuid={$this->mid} and ts_bonus.money_type=2")
            ->select();
            foreach ($sum as $key => $value) {
                $sum[$key] = $value['bonus_money'];
            }
        }
            
        if($data['data']){
            self::success(
                array(
                    'status' => 1,
                    'message' => '操作成功',
                    'data' => $data['data'],
                    'totalPages' => $data['totalPages'],
                    'totalRows' => $data['totalRows'],
                    'nowPage' => $data['nowPage'],
                    'sum' => array_sum($sum),
                    'uname' => getUserName($this->mid),
                    'avatar' =>  model('Avatar')->init($this->mid)->getUserAvatar()['avatar_middle'],
                ));
        }else{
            self::error(array(
                'status'  => 0,
                'message' => '暂无数据',
                'sum' => 0,
                'uname' => getUserName($this->mid),
                'avatar' =>  model('Avatar')->init($this->mid)->getUserAvatar()['avatar_middle'],
            ));
        }
        
    }

//财源币使用详情
    public function details_cyb(){

        if($this->data['time'] != 'null'){
            $tt = $this->data['time'];
            $t = strtotime($tt);
            $start = mktime(0,0,0,date("m",$t),1,date("Y",$t));
            $end = mktime(23,59,59,date("m",$t),date('t'),date("Y",$t));
            $info = M('bonus_list')
                ->join('ts_bonus ON ts_bonus_list.bonus_code=ts_bonus.bonus_code')
                ->field('
                    ts_bonus.bonus_msg,
                    ts_bonus.bonus_fromuid,
                    ts_bonus_list.bonus_money,
                    to_uid,
                    ts_bonus_list.get_time,
                    ts_bonus_list.use_time,
                    ts_bonus.expire_time,
                    eid
                    ')
                ->where("ts_bonus_list.status={$this->data['type']} and ts_bonus.money_type=2 and ts_bonus_list.to_uid > 0 and ts_bonus.bonus_fromuid={$this->mid} and {$start}<=ts_bonus_list.get_time and ts_bonus_list.get_time<={$end}")
                ->order('ts_bonus_list.get_time desc')
                ->findPage(10);

            foreach ($info['data'] as $key => $value) {
                if($value['eid'] != 0){
                    $info['data'][$key]['event_name'] = $this->eventName($value['eid']);
                }
                $info['data'][$key]['uname'] = getUserName($value['to_uid']);
                $info['data'][$key]['use_time'] = date('m月d日 H:i',$value['use_time']);
                $info['data'][$key]['avatar'] = model('Avatar')->init($value['to_uid'])->getUserAvatar()['avatar_middle'];
                $info['data'][$key]['total_amount'] = floatval($value['bonus_money']);
            }

            $sum = M('bonus_list')
                ->join('ts_bonus ON ts_bonus_list.bonus_code=ts_bonus.bonus_code')
                ->field('
                    ts_bonus_list.bonus_money
                    ')
                ->where("ts_bonus_list.status={$this->data['type']} and ts_bonus.money_type=2 and ts_bonus_list.to_uid > 0 and ts_bonus.bonus_fromuid={$this->mid} and {$start}<=ts_bonus_list.get_time and ts_bonus_list.get_time<={$end}")
                ->select();
            foreach ($sum as $key => $value) {
                    $sum[$key] = $value['bonus_money'];
                }    
        }else{

            $info = M('bonus_list')
                ->join('ts_bonus ON ts_bonus_list.bonus_code=ts_bonus.bonus_code')
                ->field('
                    ts_bonus.bonus_msg,
                    ts_bonus.bonus_fromuid,
                    ts_bonus_list.bonus_money,
                    to_uid,
                    ts_bonus_list.get_time,
                    ts_bonus_list.use_time,
                    ts_bonus.expire_time,
                    eid
                    ')
                ->where("ts_bonus_list.status={$this->data['type']} and ts_bonus.money_type=2 and ts_bonus_list.to_uid > 0 and ts_bonus.bonus_fromuid={$this->mid}")
                ->order('ts_bonus_list.get_time desc')
                ->findPage(10);

            foreach ($info['data'] as $key => $value) {
                if($value['eid'] != 0){
                    $info['data'][$key]['event_name'] = $this->eventName($value['eid']);
                }
                $info['data'][$key]['uname'] = getUserName($value['to_uid']);
                $info['data'][$key]['use_time'] = date('m月d日 H:i',$value['use_time']);
                $info['data'][$key]['avatar'] = model('Avatar')->init($value['to_uid'])->getUserAvatar()['avatar_middle'];
                $info['data'][$key]['total_amount'] = floatval($value['bonus_money']);
            }
            $sum = M('bonus_list')
                ->join('ts_bonus ON ts_bonus_list.bonus_code=ts_bonus.bonus_code')
                ->field('
                    ts_bonus_list.bonus_money
                    ')
                ->where("ts_bonus_list.status={$this->data['type']} and ts_bonus.money_type=2 and ts_bonus_list.to_uid > 0 and ts_bonus.bonus_fromuid={$this->mid}")
                ->select();
            foreach ($sum as $key => $value) {
                    $sum[$key] = $value['bonus_money'];
                }  
        }
            
        if($info){
            self::success(array(
                'status'  => 1,
                'message' => '操作成功',
                'sum' => array_sum($sum),
                'data'=>$info['data'],
                'totalPages' => $info['totalPages'],
                'totalRows' => $info['totalRows'],
                'nowPage' => $info['nowPage'],
                
                ));
        }else{
            self::error(array(
                'status'  => 0,
                'message' => '暂无数据',
                'sum' => 0,
                'uname' => getUserName($this->mid),
                'avatar' =>  model('Avatar')->init($this->mid)->getUserAvatar()['avatar_middle'],
            ));
        }
    }

//红包详情
    public function getBonusDetail(){
        
         if (!$this->data['bonus_id']){
             $this->error("bonus_id　error");
         }
         
         $bonus = M("bonus")->field('bonus_code,status,bonus_msg,total_amount,bonus_fromuid,bonus_many,is_follow')->where(array("id"=>trim($this->data['bonus_id'])))->find();

         if (!$bonus){
             return  $this->error("bonus_code　error");
         }
         
        $bonusDetail = Bonus::getBonusDetail($bonus['bonus_code'],1,$this->page);
        foreach ($bonusDetail['listdata']['data'] as $key => $value) {
            $sum[$key] = $value['bonus_money'];
        }
        if ($bonusDetail['listdata']['data']){
           self::success(array(
                'status'  => 1,
                'message' => '操作成功',
                'bonus_state' => $bonus['status'],
                'data_list' =>$bonusDetail['listdata']['data'],
                'has_get'=>$bonusDetail['bonus_gets']['has_get'],
                'not_get'=>$bonusDetail['bonus_gets']['not_get'],
                'bonus_code'=>$bonus['bonus_code'],
                'total_amount'=>$bonus['total_amount'],
                'bonus_many'=>$bonus['bonus_many'],
                'is_follow'=>$bonus['is_follow'],
                'uname' => getUserName($bonus['bonus_fromuid']),
                'avatar' =>  model('Avatar')->init($bonus['bonus_fromuid'])->getUserAvatar()['avatar_middle'],
                'surplus_many'=>floatval($bonus['total_amount'] - array_sum($sum)),
                'totalPages'=>$bonusDetail['listdata']['totalPages'],
                'totalRows'=>$bonusDetail['listdata']['totalRows'],
                'nowPage'=>$bonusDetail['listdata']['nowPage'],
            )); 
            
        }else{
            self::error(array(
                'status'  => 0,
                'message' => '暂无数据',
                'bonus_state' => $bonus['status'],
                'total_amount'=>$bonus['total_amount'],
                'bonus_many'=>$bonus['bonus_many'],
                'bonus_msg'=>$bonus['bonus_msg'],
                'uname' => getUserName($bonus['bonus_fromuid']),
                'avatar' =>  model('Avatar')->init($bonus['bonus_fromuid'])->getUserAvatar()['avatar_middle'],
                'has_get'=>0,
                'not_get'=>$bonus['bonus_many'],

            ));
        }      
    }

//我的财源
    public function myCyb()
    {
        $map = $this->data['user_group'];
        $page = $this->data['page'] ? intval($this->data['page']) : 1;
        if($map == 0 || !$map){
            $map = '0,1,2';
        }
    	$type = $this->data['type'];
    	if($type == 1) {
    		$calcScope = Bonus::calcScope($this->data['latitude'],$this->data['longitude'],3000);
    		$where['latitude'] = array('between ',array($calcScope['minLat'],$calcScope['maxLat']));
    		$where['longitude'] = array('between ',array($calcScope['minLng'],$calcScope['maxLng']));
    		
    		$order = "ts_bonus.latitude asc, ts_bonus.longitude asc";
    	}elseif($type == 2) {
    		$order = "ts_bonus.total_amount desc";
    	}elseif($type == 3) {
    		$order = "ts_bonus.certification_group desc";
    	}else {
    		$order = "ts_bonus_list.get_time desc";
    	}
    	$where['ts_bonus_list.status'] = 0; 
    	$where['ts_bonus.money_type'] = 2; 
    	$where['ts_bonus_list.to_uid'] = $this->mid;
    	$where['certification_group'] = array("in", $map);
        $list = M('bonus_list')
            ->join('ts_bonus ON ts_bonus_list.bonus_code=ts_bonus.bonus_code')
            ->field('
                ts_bonus.total_amount,
                ts_bonus.bonus_msg,
                ts_bonus.eid,
                ts_bonus_list.bonus_fromuid,
                ts_bonus_list.bonus_money,
                to_uid,
                ts_bonus_list.expire_time,
                ts_bonus_list.bonus_code,
                ts_bonus_list.id
            ')
            ->where($where)
            ->order($order)
            ->page($page.',10')
            ->select();
            foreach ($list as $key => $value) {
                if($value['eid'] != 0){
                	$list[$key]['event_name'] = $this->eventName($value['eid']);
                }
                $list[$key]['toUname'] = getUserName($value['bonus_fromuid']);
                $list[$key]['avatar'] = model('Avatar')->init($value['bonus_fromuid'])->getUserAvatar()['avatar_middle'];
                $list[$key]['expire_time'] = date('Y.m.d',strtotime($value['expire_time']));
                
            }    

        if($list){

            self::success(
                array(
                    'status' => 1,
                    'message' => '操作成功',
                	'data' => $list,
                    //'totalPages' => $list['totalPages'],
                    //'totalRows' => $list['totalRows'],
                	'nowPage' => $page,
                ));
        }else{
            self::error(array(
                'status'  => 0,
                'message' => '暂无收到财源',
            ));
        }    
            

    }

    //查看分类名称
    public function eventName($eid){
        $data = M('bonusevent_list')->field('name')->where("eid=$eid")->find();
        return $data['name'];
    }

//我的财源使用详情
    public function myCybDetails()
    {

        if($this->data['time'] != 'null'){
            $tt = $this->data['time'];
            $t = strtotime($tt);
            $start = mktime(0,0,0,date("m",$t),1,date("Y",$t));
            $end = mktime(23,59,59,date("m",$t),date('t'),date("Y",$t));
            $list = M('bonus_list')
                ->join('ts_bonus ON ts_bonus_list.bonus_code=ts_bonus.bonus_code')
                ->field('
                    ts_bonus.eid,
                    ts_bonus.bonus_fromuid,
                    ts_bonus_list.bonus_money,
                    ts_bonus_list.get_time,
                    ts_bonus_list.status
                ')
                ->where("ts_bonus_list.status={$this->data['type']} and ts_bonus.money_type=2 and to_uid={$this->mid} and {$start}<=ts_bonus_list.get_time and ts_bonus_list.get_time<={$end}")
                ->order('ts_bonus_list.get_time desc')
                ->findPage(10);
                foreach ($list['data'] as $key => $value) {
                    $list['data'][$key]['avatar'] = model('Avatar')->init($value['bonus_fromuid'])->getUserAvatar()['avatar_middle'];
                    $list['data'][$key]['get_time'] = date('m月d日 H:i',$value['get_time']);
                    if(empty($value['eid'])){

                        $list['data'][$key]['bonusType'] = '个人红包';
                    }else{

                        $list['data'][$key]['bonusType'] = '活动红包';
                    }
                }
                $sum = M('bonus_list')
                    ->join('ts_bonus ON ts_bonus_list.bonus_code=ts_bonus.bonus_code')
                    ->field('
                        ts_bonus_list.bonus_money
                    ')
                    ->where("ts_bonus_list.status={$this->data['type']} and ts_bonus.money_type=2 and to_uid={$this->mid} and {$start}<=ts_bonus_list.get_time and ts_bonus_list.get_time<={$end}")
                    ->select();
                foreach ($sum as $key => $value) {
                    $sum[$key] = $value['bonus_money'];
                }
        }else{

                $list = M('bonus_list')
                ->join('ts_bonus ON ts_bonus_list.bonus_code=ts_bonus.bonus_code')
                ->field('
                    ts_bonus.eid,
                    ts_bonus.bonus_fromuid,
                    ts_bonus_list.bonus_money,
                    ts_bonus_list.get_time,
                    ts_bonus_list.status
                ')
                ->where("ts_bonus_list.status={$this->data['type']} and ts_bonus.money_type=2 and to_uid={$this->mid}")
                ->order('ts_bonus_list.get_time desc')
                ->findPage(10);
                foreach ($list['data'] as $key => $value) {
                    $list['data'][$key]['avatar'] = model('Avatar')->init($value['bonus_fromuid'])->getUserAvatar()['avatar_middle'];
                    $list['data'][$key]['get_time'] = date('m月d日 H:i',$value['get_time']);
                    if(empty($value['eid'])){

                        $list['data'][$key]['bonusType'] = '个人红包';
                    }else{

                        $list['data'][$key]['bonusType'] = '活动红包';
                    }
                }
                $sum = M('bonus_list')
                    ->join('ts_bonus ON ts_bonus_list.bonus_code=ts_bonus.bonus_code')
                    ->field('
                        ts_bonus_list.bonus_money
                    ')
                    ->where("ts_bonus_list.status={$this->data['type']} and ts_bonus.money_type=2 and to_uid={$this->mid}")
                    ->select();
                foreach ($sum as $key => $value) {
                    $sum[$key] = $value['bonus_money'];
                }
        }    

        if($list['data']){

            self::success(
                array(
                    'status' => 1,
                    'message' => '操作成功',
                    'data' => $list['data'],
                    'totalPages' => $list['totalPages'],
                    'totalRows' => $list['totalRows'],
                    'nowPage' => $list['nowPage'],
                    'uname' => getUserName($this->mid),
                    'avatar' =>  model('Avatar')->init($this->mid)->getUserAvatar()['avatar_middle'],
                    'sum' => array_sum($sum),
                ));
        }else{
            self::error(array(
                'status'  => 0,
                'message' => '暂无收到财源',
                'uname' => getUserName($this->mid),
                'avatar' =>  model('Avatar')->init($this->mid)->getUserAvatar()['avatar_middle'],
                'sum' => 0,
            ));
        }      
    }

//财源币使用页
    public function cybUsePage(){

        $info = M('bonus_list')->field('bonus_fromuid,bonus_code,id,expire_time,bonus_money,status')->where("id={$this->data['bonus_id']} and to_uid={$this->mid}")->find();
        if(!$info){
            $info = M('bonus_list')->field('bonus_fromuid,bonus_code,id,expire_time,bonus_money,status')->where("bonus_code='{$this->data['serial_number']}' and to_uid={$this->mid}")->find();
            if(!info){
                return array('status'=> 0,'message'=>'无数据');
            }
        }
        if($info['status'] == 1)
        {
            return array('status'=> 0,'message'=>'财源已使用');
        }elseif ($info['status'] == 2) {
            return array('status'=> 0,'message'=>'财源已过期');
        }
        $msg = M('bonus')->field('bonus_msg,latitude,longitude,address_title,address')->where("bonus_code='{$info['bonus_code']}'")->find();
        $use_info = M('user_cyb_use_info')->where("uid={$info['bonus_fromuid']}")->find();
        if($use_info){
            $info['details_type'] = 1;
        }else{
            $info['details_type'] = 0;
        }
        $info['bonus_money'] = floatval($info['bonus_money']);

        $img = M('ad')->field('content')->where('place=1')->order('ad_id desc')->find();

        $userImg = M('ad_user')->where("uid={$info['bonus_fromuid']} and place=1 and is_active=1")->find();

        if($userImg){
            $data['img'] = unserialize($userImg['img']);
            foreach ($data['img'] as $key => $value) {

                $attachInfo = model('Attach')->getAttachById($value['banner']);
                $info['carouselFigure'][$key]['advertImg'] = getImageUrl($attachInfo['save_path'].$attachInfo['save_name']);
                $info['carouselFigure'][$key]['advertUrl'] = $value['bannerurl'];
            }
                
            $info['adUrl'] = unserialize($userImg['adUrl']);
        }else{
            $info['adUrl'] = array(array('adTitle'=>'下载APP','explain'=>'领万元红包','url'=>'http://www.baidu.com'));
            // $info['carouselFigure'] = array(array('advertImg'=>$match[1][0],'advertUrl'=>$matchUrl[1][0]),array('advertImg'=>$match[1][0],'advertUrl'=>$matchUrl[1][0]));
            $data['content'] = unserialize($img['content']);
            foreach ($data['content'] as $key => $value) {

                $attachInfo = model('Attach')->getAttachById($value['banner']);
                $info['carouselFigure'][$key]['advertImg'] = getImageUrl($attachInfo['save_path'].$attachInfo['save_name']);
                $info['carouselFigure'][$key]['advertUrl'] = $value['bannerurl'];
            }
        }
        $info['title'] = getUserName($info['bonus_fromuid']);
        $info['toUname'] = getUserName($info['bonus_fromuid']);
        $info['avatar'] = model('Avatar')->init($info['bonus_fromuid'])->getUserAvatar()['avatar_middle'];
        $info['condition'] = $msg['bonus_msg'];
        $info['latitude'] = $msg['latitude'];
        $info['longitude'] = $msg['longitude'];
        $info['address_title'] = $msg['address_title'];
        $info['address'] = $msg['address'];
        if($info){
            self::success(array(
                    'status' => 1,
                    'message' => '操作成功',
                    'data' => $info,
            ));
        }else{
            self::error(array(
                'status'  => 0,
                'message' => '无数据',
            ));
        }
    }

    public function cybUseDetails()
    {

        $useInfo = M('user_cyb_use_info')->where("uid={$this->data['business_uid']}")->find();
        if($useInfo)
        {
            
            $info['address'] = $useInfo['address'];
            $info['phone'] = $useInfo['phone'];
            $info['explain'] = $useInfo['explain'];
            $info['about'] = $useInfo['about'];
            $info['operation_time'] = $useInfo['operation_time'];
            $info['latitude'] = $useInfo['latitude'];
            $info['longitude'] = $useInfo['longitude'];
            if($useInfo['is_display_wifi'] == 1){
                $info['wifi_name'] = $useInfo['wifi_name'];
                $info['wifi_paw'] = $useInfo['wifi_paw'];
            }
            if($useInfo['address_pic'] != '')
            {
                $attachInfo = model('Attach')->getAttachById($useInfo['address_pic']);
                $info['address_img_url'] = getImageUrl($attachInfo['save_path'].$attachInfo['save_name']);
            }
            if($useInfo['img'] != '')
            {
                $photo = explode(',',$useInfo['img']);
                foreach ($photo as $key => $value) {
                    $attachInfo = model('Attach')->getAttachById($value);
                    $info['photo'][$key]['img'] = getImageUrl($attachInfo['save_path'].$attachInfo['save_name']);
                    if ($attachInfo['width'] > 384 && $attachInfo['height'] > 384) {
                        $info['photo'][$key]['img_middle'] = UPLOAD_URL.getThumbImage(UPLOAD_URL.$attachInfo['save_path'].$attachInfo['save_name'], 384)['src'];
                    } else {
                        $info['photo'][$key]['img_middle'] = $info['photo'][$key]['img'];
                    }
                    if ($attachInfo['width'] > 220 && $attachInfo['height'] > 220) {
                        $info['photo'][$key]['img_small'] = UPLOAD_URL.getThumbImage(UPLOAD_URL.$attachInfo['save_path'].$attachInfo['save_name'], 220)['src'];
                    } else {
                        $info['photo'][$key]['img_small'] = $info['photo'][$key]['img'];
                    }
                }
            }
                
            self::success(array(
                    'status' => 1,
                    'message' => '操作成功',
                    'data' => $info,
            ));
        }else{
            self::error(array(
                'status'  => 0,
                'message' => '暂无数据',
            ));
        }
    }

    //刪除財源使用
    public function delCybUse()
    {
        $map['status'] = '-1';
        $del = M('bonus_list')->where("id={$this->data['bonus_id']}")->save($map);
        if($del){
            return array('status'=>1,'message'=>'删除成功');
        }else{
            return array('status'=>0,'message'=>'删除失败');
        }
    }

    //举报
    public function cybUseReport()
    {
        $map['from'] = 'cyb_use';
        $map['aid'] = 0;
        $map['state'] = 0;
        $map['uid'] = $this->mid;
        $map['ctime'] = time();
        $map['fuid'] = $this->data['bonus_fromuid'];
        if(!$map['fuid']){
            return array('status'=>0,'message'=>'被举报用户id不能为空');
        } 
        // $map['reason'] = $this->data['reason'];//举报原因
        $map['content'] = $this->data['content'];//举报内容
        if(!$map['content']){
            return array('status'=>0,'message'=>'举报内容不能为空');
        } 
        $res = M('denounce')->add($map);
        if($res){
            return array('status'=>1,'message'=>'举报成功');
        }else{
            return array('status'=>0,'message'=>'举报失败');
        }
    }

    //商家扫码
    public function MerchantCode(){

        $map['id'] = $this->data['bonus_id'];
        $map['to_uid'] = $this->data['uid'];
        $map['bonus_fromuid'] = $this->mid;
        $remark = $this->data['remark'];
        $map['status'] = 0;
        $data = M('bonus_list')->field('bonus_money,bonus_fromuid,bonus_code')->where($map)->find();
        if(!$this->data['money']){

            return array('status'=>0,'message' =>'请输入总金额');
        }
        $pay_money['pay_money'] = $this->data['money']-$data['bonus_money'];
        $toUname = getUserName($data['bonus_fromuid']);
        $res = M('bonus_list')->where($map)->save($pay_money);
        if($data){
            // $data_list = array(
            //     array('title'=>'支付方式','content'=>'零用钱'),
            //     array('title'=>'收款人','content'=>$toUname),
            //     array('title'=>'商品说明','content'=>'总金额：'.$this->data['money'].',当前红包金额：'.$data['bonus_money'].'。'),
            // );
            // model('Jpush')->noticeMessage($this->data['uid'],'付款通知','付款通知',$data['bonus_code'],1,1,floatval($pay_money['pay_money']),$data_list);
            $uid = array($this->data['uid']);
            $extras = array(
                    'title' => '付款通知',
                    'content_type' => 'text',
                    'extras' => array(
                            'uid' => $this->data['uid'],
                            'time' => time(),
                            'type' => 4,//消费凭证
                            'money' => floatval($pay_money['pay_money']),
                            'bonus_money' => floatval($data['bonus_money']),
                            'toUname' => $toUname,
                            'pay_type' => '链我零用钱',
                            'detatls' => '总金额：'.$this->data['money'].',当前红包金额：'.$data['bonus_money'].'。',
                            'serial_number' => $data['bonus_code'],
                            'remark' => $remark,
                            'detatls_type' => 1,
                            'money_type' => 2, 
                            'transaction_status' => 4,//付款状态
                            'push_type' => 'qrCodePay'
                        )
                );
            $con = '付款通知';
            model('Jpush')->message($uid,$con,$extras);
            self::success(array(
                    'status' => 1,
                    'message' => '操作成功',
                ));
        }else{
            self::error(array(
                'status'  => 0,
                'message' => '参数错误',
            ));
        }

    }

//二维码支付
    public function qrCodePay(){

        $info = M('bonus_list')->field('pay_money,bonus_fromuid,bonus_money')->where("bonus_code='{$this->data['serial_number']}'")->find();
        if(!info){
            return array('status' => 0, 'message' => '该红包不存在');
        }
        $data['toUid'] = $info['bonus_fromuid'];
        $data['fromUid'] = $this->mid;
        $data['num'] = $info['pay_money'];
        $data['order_num'] = 'UBANK'.time().rand(1000, 9999);
        $data['type'] = 5;
        $data['charge_type'] = 2;
        $paw = $this->data['paw'];
        $user_paw = M('user')->field('ppasswd')->where("uid={$this->mid}")->find();
        $u_paw = $user_paw['ppasswd'];
        if(!$u_paw){
            return array('status' => 5, 'message' => '未设置支付密码');
        }

        if(!empty($paw) && $paw == $u_paw){
            if ($data['toUid'] && $data['num'] > 0) {

                $result = model('Ubank')->cybUseUbank($data);
            } else {
                $result = false;
            }
            if($result){
                $map['status'] = 1;
                $map['use_time'] = time();
                M("bonus_list")->where("bonus_code='{$this->data['serial_number']}' and to_uid={$this->mid}")->save($map);
                $reward_money = floatval($data['num']*0.1);
                $data_list = array(
                    array('title'=>'支付方式','content'=>'零用钱'),
                    array('title'=>'收款人','content'=>getUserName($data['toUid'])),
                    array('title'=>'总额消费','content'=>floatval($info['bonus_money'] + $data['num']).'元'),
                    array('title'=>'商品说明','content'=>'优惠:已使用财源币'.$info['bonus_money'].'元'),
                );
                model('Jpush')->noticeMessage($data['fromUid'],'财源使用通知','财源使用通知',$data['order_num'],1,1,floatval($data['num']),$data_list);

                $data_list1 = array(
                    array('title'=>'支付方式','content'=>'零用钱'),
                    array('title'=>'转账人','content'=>getUserName($data['fromUid'])),
                    array('title'=>'总额消费','content'=>floatval($info['bonus_money'] + $data['num']).'元'),
                    array('title'=>'商品说明','content'=>'优惠:已使用财源币'.$info['bonus_money'].'元'),
                    array('title'=>'活动奖励','content'=>'奖励财源币::'.$reward_money.'元'),
                );
                model('Jpush')->noticeMessage($data['toUid'],'财源使用通知','财源使用通知',$data['order_num'],1,1,floatval($data['num']),$data_list1);
                model('Cyb')->rewardCyb($data['toUid'],$reward_money);
                
                self::success(
                    array(
                            'status' => 1,
                            'message' =>'支付成功！'
                        )
                    );
            }else{
                self::error(
                    array(
                            'status' => 0,
                            'message' =>'支付失败！'
                        )
                    );
            }
        }else{

            return array('status' => 4, 'message' => '密码不正确');
        }   
    }

    public function payCyb(){

        $num = $this->data['money'];//支付金额
        if(!$num){
            return array('status' => 0, 'message' => '金额不为空');
        }
        $bonus_list = M('bonus_list')->field('bonus_fromuid,bonus_money,id,status')->where("id={$this->data['bonus_id']} and to_uid={$this->mid}")->find();
        if($bonus_list['status'] != 0)
        {
            return array('status'=>0,'message'=>'非法操作');
        }
        $data['toUid'] = $bonus_list['bonus_fromuid'];
        $data['fromUid'] = $this->mid;
        $data['desc'] = t($this->data['desc']);
        $data['num'] = $num;
        $data['order_num'] = 'UBANK'.time().rand(1000, 9999);
        $data['type'] = 5;
        $data['charge_type'] = 2;
        $data['bonus_id'] = $bonus_list['id'];
        $paw = $this->data['paw'];
        $user_paw = M('user')->field('ppasswd')->where("uid={$this->mid}")->find();
        $u_paw = $user_paw['ppasswd'];
        if(!$u_paw){
            return array('status' => 5, 'message' => '未设置支付密码');
        }
        if(!empty($paw) && $paw == $u_paw){
            if ($data['toUid'] && $data['num'] > 0) {

                $result = model('Ubank')->cybUseUbank($data);

            } else {
                $result = false;
            }
            if($result){
                
                $map['status'] = 1;
                $map['use_time'] = time();
                M("bonus_list")->where("id={$this->data['bonus_id']} and to_uid={$this->mid}")->save($map);
                $reward_money = floatval($data['num']*0.1);
                $data_list = array(
                    array('title'=>'支付方式','content'=>'零用钱'),
                    array('title'=>'收款人','content'=>getUserName($data['toUid'])),
                    array('title'=>'总额消费','content'=>floatval($bonus_list['bonus_money'] + $data['num']).'元'),
                    array('title'=>'商品说明','content'=>'优惠:已使用财源币'.$bonus_list['bonus_money'].'元'),
                    array('title'=>'备注','content'=>$data['desc']),
                );
                model('Jpush')->noticeMessage($data['fromUid'],'财源使用通知','财源使用通知',$data['order_num'],1,1,floatval($data['num']),$data_list);

                $data_list1 = array(
                    array('title'=>'支付方式','content'=>'零用钱'),
                    array('title'=>'转账人','content'=>getUserName($data['fromUid'])),
                    array('title'=>'总额消费','content'=>floatval($bonus_list['bonus_money'] + $data['num']).'元'),
                    array('title'=>'商品说明','content'=>'优惠:已使用财源币'.$bonus_list['bonus_money'].'元'),
                    array('title'=>'活动奖励','content'=>'奖励财源币:'.$reward_money.'个'),
                    array('title'=>'备注','content'=>$data['desc']),
                );
                model('Jpush')->noticeMessage($data['toUid'],'财源使用通知','财源使用通知',$data['order_num'],1,1,floatval($data['num']),$data_list1);
                model('Cyb')->rewardCyb($data['toUid'],$reward_money);
                self::success(
                    array(
                            'status' => 1,
                            'message' =>'支付成功！'
                        )
                    );
            }else{
                self::error(
                    array(
                            'status' => 0,
                            'message' =>'支付失败！'
                        )
                    );
            }
        }else{

            return array('status' => 4, 'message' => '密码不正确');
        }    
    }
//我的财源支付成功信息
    public function paySuccessInfo()
    {
        $data = M('bonus_list')->field('bonus_fromuid,pay_money')->where("bonus_code='{$this->data['bonus_code']}' and to_uid={$this->mid}")->find();
        $aa['money'] = floatval($data['pay_money']);
        $aa['time'] = time();
        $aa['advertisement'] = Bonus::bootAd(5);
        $aa['is_followed'] = M('Follow')->getFollowState($this->mid,$data['bonus_fromuid'])['following'];
        $aa['toUname'] = getUserName($data['bonus_fromuid']);
        $aa['bonus_fromuid'] = $data['bonus_fromuid'];
        $aa['avatar'] = model('Avatar')->init($data['bonus_fromuid'])->getUserAvatar()['avatar_middle'];
        if($data){
            return array('status'=>1,'message'=>'操作成功','data'=>$aa);
        }else{
            return array('status'=>0,'message'=>'操作失败');
        }
    }

    public function cybUseThirdPartyPayment()
    {
        $num = $this->data['money'];//支付金额
        if(!$num){
            return array('status' => 0, 'message' => '金额不为空');
        }
        $bonus_list = M('bonus_list')->field('bonus_fromuid,bonus_money')->where("id={$this->data['bonus_id']} and to_uid={$this->mid}")->find();

        $data['toUid'] = $bonus_list['bonus_fromuid'];
        $data['num'] = $num;
        $data['order_num'] = 'UBANK'.time().rand(1000, 9999);
        $data['type'] = 5;
        $data['charge_type'] = $this->data['pay_type'];
        $data = count($data) ? $data : $_POST;
        $type = intval($data['charge_type']);
        $types = array('alipay', 'weixin');
        if (!isset($types[$type])) {
            self::error(array(
                'status'  => 0,
                'message' => '充值方式不支持',
            ));
        }
        $version = intval($this->data['version']) ?: 1; //版本   1-系统版  2-直播版
        if ($version == 1) {
            $chargeConfigs = model('Xdata')->get('admin_Config:charge');
        } elseif ($version == 2) {
            $chargeConfigs = model('Xdata')->get('admin_Config:ZBcharge');
        } else {
            self::error(array(
                'status'  => 0,
                'message' => '参数错误',
            ));
        }
        if (!in_array($types[$type], $chargeConfigs['charge_platform'])) {
            self::error(array(
                'status'  => 0,
                'message' => '充值方式不支持',
            ));
        }
        $add['serial_number'] = $data['order_num'];
        $price = $data['num'];
        $add['charge_value'] = floatval($price);
        $add['ctime'] = time();
        $add['uid'] = $data['toUid'];
        $add['status'] = 0;
        $add['sta'] = 2;
        $add['charge_ubank'] = $price;
        $add['charge_order'] = '';
        $add['charge_type'] = $type;
        $data['status'] = 0;
        $result = D('credit_charge')->add($add);
        if(!$result){
            self::error(array(
                'status'  => 0,
                'message' => '支付创建失败',
            ));
        }
        $data = D('credit_charge')->where("serial_number='{$add['serial_number']}'")->find();
        if ($data) {
            if ($data['charge_type'] == 0) {
                $configs = $parameter = array();
                $configs['partner'] = $chargeConfigs['alipay_pid'];
                $configs['seller_id'] = $chargeConfigs['alipay_pid'];
                $configs['seller_email'] = $chargeConfigs['alipay_email'];
                $configs['sign_type'] = 'RSA';
                $configs['private_key_path'] = $chargeConfigs['private_key_path'];
                $parameter = array(
                    'app_id'     => $chargeConfigs['alipay_app_pid'],
                    'method'     => 'alipay.trade.app.pay',
                    'charset'    => 'utf-8',
                    'sign_type'  => 'RSA',
                    'timestamp'  => date('Y-m-d H:i:s'),
                    'version'    => '1.0',
                    'notify_url' => SITE_URL.'/alipay_notify_api.php',
                );
                $parameter['biz_content'] = '{'.
                    '"subject":"零用钱支付:'.$data['charge_ubank'].'元",'.
                    '"out_trade_no":"'.$data['serial_number'].'",'.
                    '"total_amount":"'.$data['charge_value'].'",'.
                    '"seller_id":"'.$chargeConfigs['alipay_pid'].'",'.
                    '"product_code":"QUICK_MSECURITY_PAY"'.
                    '}';

                $url['url'] = createAlipayUrl($configs, $parameter, 3); //直接返回支付宝支付url
                $url['charge_type'] = $data['charge_type'];
                $url['charge_value'] = $data['charge_value'];
                $url['out_trade_no'] = $data['serial_number'];
                $map['status'] = 1;
                $map['use_time'] = time();
                M("bonus_list")->where("id={$this->data['bonus_id']} and to_uid={$this->mid}")->save($map);
                self::success(array(
                    'status'  => 1,
                    'message' => '操作成功',
                    'pay_type' => $data['charge_type'],
                    'data'=>$url,
                ));
            } elseif ($data['charge_type'] == 1) {
                $ip = get_client_ip(); //微信支付需要终端ip
                $order = array(
                    'body'             => '零用钱支付:'.$data['charge_ubank'].'元',
                    'appid'            => $chargeConfigs['weixin_pid'],
                    'device_info'      => 'APP',
                    'mch_id'           => $chargeConfigs['weixin_mid'],
                    'nonce_str'        => mt_rand(),
                    'notify_url'       => SITE_URL.'/weixin_cyb_use_notify_api.php',
                    'out_trade_no'     => $data['serial_number'],
                    'spbill_create_ip' => $ip,
                    'total_fee'        => 1, //这里的最小单位是分，跟支付宝不一样。1就是1分钱。只能是整形。
                    'trade_type'       => 'APP',
                    ); //预支付订单
                $weixinpay = new WeChatPay();

                $input = $weixinpay->getPayParam($order, $chargeConfigs['weixin_pid'], $chargeConfigs['weixin_mid'], $chargeConfigs['weixin_key'], 2);

                $input['out_trade_no'] = $data['serial_number'];
                $input['charge_type'] = $data['charge_type'];
                $input['charge_value'] = $data['charge_value'];
                $input['packagevalue'] = 'Sign=WXPay';
                $map['status'] = 1;
                $map['use_time'] = time();
                M("bonus_list")->where("id={$this->data['bonus_id']} and to_uid={$this->mid}")->save($map);
                self::success(array(
                    'status'  => 1,
                    'message' => '操作成功',
                    'pay_type' => $data['charge_type'],
                    'data'=>$input,
                ));
            }

        } else {
            self::error(array(
                'status'  => 0,
                'message' => '支付创建失败',
            ));
        }
    }

    public function cybUseThirdPartyPaymentIos()
    {
        $num = $this->data['money'];//支付金额
        if(!$num){
            return array('status' => 0, 'message' => '金额不为空');
        }
        $bonus_list = M('bonus_list')->field('bonus_fromuid,bonus_money')->where("id={$this->data['bonus_id']} and to_uid={$this->mid}")->find();

        $data['toUid'] = $bonus_list['bonus_fromuid'];
        $data['num'] = $num;
        $data['order_num'] = 'UBANK'.time().rand(1000, 9999);
        $data['type'] = 5;
        $data['charge_type'] = $this->data['pay_type'];
        $data = count($data) ? $data : $_POST;
        $type = intval($data['charge_type']);
        $types = array('alipay', 'weixin');
        if (!isset($types[$type])) {
            self::error(array(
                'status'  => 0,
                'message' => '充值方式不支持',
            ));
        }
        $version = intval($this->data['version']) ?: 1; //版本   1-系统版  2-直播版
        if ($version == 1) {
            $chargeConfigs = model('Xdata')->get('admin_Config:charge');
        } elseif ($version == 2) {
            $chargeConfigs = model('Xdata')->get('admin_Config:ZBcharge');
        } else {
            self::error(array(
                'status'  => 0,
                'message' => '参数错误',
            ));
        }
        if (!in_array($types[$type], $chargeConfigs['charge_platform'])) {
            self::error(array(
                'status'  => 0,
                'message' => '充值方式不支持',
            ));
        }
        $add['serial_number'] = $data['order_num'];
        $price = $data['num'];
        $add['charge_value'] = floatval($price);
        $add['ctime'] = time();
        $add['uid'] = $data['toUid'];
        $add['status'] = 0;
        $add['sta'] = 2;
        $add['charge_ubank'] = $price;
        $add['charge_order'] = '';
        $add['charge_type'] = $type;
        $data['status'] = 0;
        $result = D('credit_charge')->add($add);
        if(!$result){
            self::error(array(
                'status'  => 0,
                'message' => '支付创建失败',
            ));
        }
        $data = D('credit_charge')->where("serial_number='{$add['serial_number']}'")->find();
        
        if ($data) {
            if ($data['charge_type'] == 0) {
                $configs = $parameter = array();
                $configs['partner'] = $chargeConfigs['alipay_pid'];
                $configs['seller_id'] = $chargeConfigs['alipay_pid'];
                $configs['seller_email'] = $chargeConfigs['alipay_email'];
                $configs['key'] = $chargeConfigs['alipay_key'];
                $parameter = array(
                    'notify_url'   => SITE_URL.'/alipay_notify_api.php',
                    'out_trade_no' => $data['serial_number'],
                    'subject'      => '零用钱支付:'.$data['charge_ubank'].'元',
                    'total_fee'    => $data['charge_value'],
                    'body'         => '',
                    'payment_type' => 1,
                    'service'      => 'mobile.securitypay.pay',
                    'it_b_pay'     => '1c',
                );
                $url = createAlipayUrl($configs, $parameter, 2); //直接返回支付宝支付url

                $info['money'] = floatval($data['num']);
                $info['time'] = time();
                $info['advertisement'] = Bonus::bootAd(5);
                $info['is_followed'] = M('Follow')->getFollowState($this->mid,$add['toUid'])['following'];
                $map['status'] = 1;
                $map['use_time'] = time();
                M("bonus_list")->where("id={$this->data['bonus_id']} and to_uid={$this->mid}")->save($map);
                self::success(array(
                    'status'  => 1,
                    'message' => '操作成功',
                    'pay_type' => $data['charge_type'],
                    'data'=>$url,
                    'info'=>$info,
                ));
            } elseif ($data['charge_type'] == 1) {
                $ip = get_client_ip(); //微信支付需要终端ip
                $order = array(
                    'body'             => '零用钱支付:'.$data['charge_ubank'].'元',
                    'appid'            => $chargeConfigs['weixin_pid'],
                    'device_info'      => 'APP',
                    'mch_id'           => $chargeConfigs['weixin_mid'],
                    'nonce_str'        => mt_rand(),
                    'notify_url'       => SITE_URL.'/weixin_cyb_use_notify_api.php',
                    'out_trade_no'     => $data['serial_number'],
                    'spbill_create_ip' => $ip,
                    'total_fee'        => 1, //这里的最小单位是分，跟支付宝不一样。1就是1分钱。只能是整形。
                    'trade_type'       => 'APP',
                    ); //预支付订单
                $weixinpay = new WeChatPay();

                $url['url'] = $weixinpay->getPayParam($order, $chargeConfigs['weixin_pid'], $chargeConfigs['weixin_mid'], $chargeConfigs['weixin_key'], 1);
                $url['out_trade_no'] = $data['serial_number'];

                $info['money'] = floatval($data['num']);
                $info['time'] = time();
                $info['advertisement'] = Bonus::bootAd(5);
                $info['is_followed'] = M('Follow')->getFollowState($this->mid,$add['toUid'])['following'];
                $map['status'] = 1;
                $map['use_time'] = time();
                M("bonus_list")->where("id={$this->data['bonus_id']} and to_uid={$this->mid}")->save($map);
                self::success(array(
                    'status'  => 1,
                    'message' => '操作成功',
                    'pay_type' => $data['charge_type'],
                    'data'=>$url,
                    'info'=>$info,
                ));
            }

        } else {
            self::error(array(
                'status'  => 0,
                'message' => '支付创建失败',
            ));
        }
    }

}
