<?php
include_once SITE_PATH.'/apps/bonus/Common/Bonus.class.php';
use Api;
use Apps\BaseBonus\Bonus;
/**
 * 零用钱模型 .
 *
 * @author jason <yangjs17@yeah.net>
 *
 * @version TS3.0
 */
class UbankModel extends Model
{
    
    /**
     * 零用钱充值成功
     *
     * @param string $serial_number 订单号
     */
    public function charge_success($serial_number)
    {
        $map['serial_number'] = $serial_number;
         // @file_put_contents('./log.txt',$serial_number);
        if ($GLOBALS['ts']['mid']) {
            $map['uid'] = $GLOBALS['ts']['mid'];
        }
        $detail = D('credit_charge')->where($map)->find();
        if ($detail && $detail['status'] != 1) {
            $res = D('credit_charge')->where($map)->setField('status', 1);
            
            if ($res !== false) {
                $ubank = model('Credit')->getUserUbank(intval($detail['uid']),1);
                if($detail['charge_type'] == 0){

                    $type = '支付宝';
                }elseif ($detail['charge_type'] == 1) {

                    $type = '微信';
                }
                $add['type'] = 2;
                $add['charge_type'] = intval($detail['charge_type']);
                $add['uid'] = intval($detail['uid']);
                $add['serial_number'] = t($detail['serial_number']);
                $add['action'] = '充值零用钱';
                $add['des'] = '';
                $add['change'] = -floatval($detail['charge_ubank']);
                $add['ctime'] = time();
                $add['detail'] = '{"ubank":"'.$add['change'].'"}';
                M('credit_user')->where("uid={$add['uid']}")->save(array('ubank' => $ubank - $add['change']));
                D('credit_record')->add($add);

                $data_list = array(
                    array('title'=>'支付方式','content'=>$type),
                    array('title'=>'收款人','content'=>'广州链沃网络科技有限公司'),
                    array('title'=>'交易商品','content'=>-floatval($add['change']).'元零用钱'),
                    array('title'=>'商品说明','content'=>'零用钱可用于发现金红包，商家消费、提现'),
                );
                model('Jpush')->noticeMessage($add['uid'],'零用钱转账通知','零用钱转账通知',$detail['serial_number'],1,1,-floatval($add['change']),$data_list);
                model('Credit')->cleanCache($add['uid']);
                return true;
            }
        }

        return false;
    }

    /**
     * 财源币使用成功
     *
     * @param string $serial_number 订单号
     */
    public function cyb_use_success($serial_number)
    {
        $map['serial_number'] = $serial_number;
         // @file_put_contents('./log.txt',$serial_number);
        $detail = D('credit_charge')->where($map)->find();
        if ($detail && $detail['status'] != 1) {
            $res = D('credit_charge')->where($map)->setField('status', 1);
            
            if ($res !== false) {
                $ubank = model('Credit')->getUserUbank(intval($detail['uid']),1);
                if($detail['charge_type'] == 0){

                    $type = '支付宝';
                }elseif ($detail['charge_type'] == 1) {

                    $type = '微信';
                }
                $add['type'] = 5;
                $add['charge_type'] = intval($detail['charge_type']);
                $add['uid'] = intval($detail['uid']);
                $add['serial_number'] = t($detail['serial_number']);
                $add['action'] = '零用钱转入';
                $add['des'] = '';
                $add['change'] = floatval($detail['charge_ubank']);
                $add['ctime'] = time();
                $add['detail'] = '{"ubank":"'.$add['change'].'"}';

                $add2 = $add;
                $add2['uid'] = $GLOBALS['ts']['mid'];
                $add2['change'] = -floatval($detail['charge_ubank']);
                $add2['action'] = '零用钱转出';
                $add2['detail'] = '{"ubank":"'.$add2['change'].'"}';

                M('credit_user')->where("uid={$add['uid']}")->save(array('ubank' => $ubank + $add['change']));
                D('credit_record')->add($add) && D('credit_record')->add($add2);

                $data_list = array(
                    array('title'=>'支付方式','content'=>$type),
                    array('title'=>'收款人','content'=>getUserName($add['uid'])),
                    array('title'=>'商品说明','content'=>'使用财源币'),
                );
                model('Jpush')->noticeMessage($add['uid'],'财源使用通知','财源使用通知',$add['serial_number'],1,1,floatval($add['change']),$data_list);

                $data_list = array(
                    array('title'=>'支付方式','content'=>$type),
                    array('title'=>'转账人','content'=>getUserName($add2['uid'])),
                    array('title'=>'商品说明','content'=>'使用财源币'),
                );
                model('Jpush')->noticeMessage($add['uid'],'财源使用通知','财源使用通知',$add['serial_number'],1,1,floatval($add['change']),$data_list);
                model('Credit')->cleanCache($add['uid']);
                return true;
            }
        }

        return false;
    }


    /**
     * 零用钱转账.
     *
     * @param array $data 转账数据
     *
     * @return bool
     */
    public function startTransfer_ubank(array $data = array())
    {
        $data = count($data) ? $data : $_POST;
        if (!$data['toUid'] || $data['num'] <= 0 || !$data['fromUid']) {
            return false;
        }
        $ubank = model('Credit')->getUserUbank($data['toUid'],1);
        $ubank2 = model('Credit')->getUserUbank($data['fromUid'],1);
        if ($ubank2 < intval($data['num'])) {
            return false;
        }

        $add['type'] = intval($data['type']);
        $add['charge_type'] = intval($data['charge_type']);
        $add['serial_number'] = $data['order_num'];
        $add['uid'] = intval($data['toUid']);
        $add['oid'] = intval($data['fromUid']);
        $add['action'] = '零用钱转入';
        $add['des'] = t($data['desc']);
        $add['change'] = floatval(number_format($data['num'],2));
        $add['ctime'] = time();
        $add['status'] = 1;
        $add['detail'] = '{"ubank":"'.$add['change'].'"}';
        $add2 = $add;
        $add2['uid'] = intval($data['fromUid']);
        $add2['oid'] = intval($data['toUid']);
        $add2['change'] = -1 * floatval(number_format($data['num'],2));
        $add2['action'] = '零用钱转出';
        $add2['detail'] = '{"ubank":"'.$add2['change'].'"}';

        $add3['uid'] = intval($data['toUid']);
        $add3['state'] = 1;
        $add3['oid'] = intval($data['fromUid']);
        $add3['des'] = t($data['desc']);
        $add3['ubank_change'] = floatval(number_format($data['num'],2));
        $add3['ctime'] = time();
        $add3['order_num'] = $data['order_num'];
        $add3['code'] = rand(1000, 9999).rand(1000, 9999).rand(1000, 9999).rand(1000, 9999).rand(1000, 9999);

        // var_dump($add,$add2);exit;
        $res = M('credit_user')->where("uid={$add3['oid']}")->save(array('ubank' => $ubank2 - $add3['ubank_change']));
        $res1 = M('credit_user')->where("uid={$add3['uid']}")->save(array('ubank' => $ubank + $add3['ubank_change']));
        D('credit_record_ubank')->add($add3);
        D('credit_record')->add($add) && D('credit_record')->add($add2);

        if($res && $res1){
            $data_list = array(
                array('title'=>'支付方式','content'=>'零用钱'),
                array('title'=>'收款人','content'=>getUserName($data['toUid'])),
                array('title'=>'转账时间','content'=>date('Y-m-d',time())),
                array('title'=>'备注','content'=>$data['desc']),
            );
            model('Jpush')->noticeMessage($data['fromUid'],'零用钱转账通知',getUserName($data['fromUid']).'向你转了'.floatval($data['num']).'元零用钱',$data['order_num'],1,2,$add['change'],$data_list);

            $data_list1 = array(
                array('title'=>'支付方式','content'=>'零用钱'),
                array('title'=>'转账人','content'=>getUserName($data['fromUid'])),
                array('title'=>'转账时间','content'=>date('Y-m-d H:i:s',time())),
                array('title'=>'备注','content'=>$data['desc']),
            );
            model('Jpush')->noticeMessage($data['toUid'],'零用钱转账通知','你向'.getUserName($data['toUid']).'转了'.$add['change'].'元零用钱',$data['order_num'],1,2,floatval($data['num']),$data_list1);
        }

        model('Credit')->cleanCache($data['toUid']);
        model('Credit')->cleanCache($data['fromUid']);

        return true;
    } 

    /**
     * 零用钱付款.
     *
     * @param array $data 付款数据
     *
     * @return bool
     */
    public function cybUseUbank(array $data = array())
    {
        $data = count($data) ? $data : $_POST;
        if (!$data['toUid'] || $data['num'] <= 0 || !$data['fromUid']) {
            return false;
        }
        $ubank = model('Credit')->getUserUbank($data['toUid'],1);
        $ubank2 = model('Credit')->getUserUbank($data['fromUid'],1);
        if ($ubank2 < intval($data['num'])) {
            return false;
        }
        M('bonus_list')->where("id='{$data['bonus_id']}' and to_uid={$data['fromUid']}")->setField('pay_money', floatval($data['num']));
        $add['type'] = intval($data['type']);
        $add['charge_type'] = intval($data['charge_type']);
        $add['serial_number'] = $data['order_num'];
        $add['uid'] = intval($data['toUid']);
        $add['oid'] = intval($data['fromUid']);
        $add['action'] = '零用钱转入';
        $add['des'] = t($data['desc']);
        $add['change'] = floatval(number_format($data['num'],2));
        $add['ctime'] = time();
        $add['status'] = 1;
        $add['detail'] = '{"ubank":"'.$add['change'].'"}';
        $add2 = $add;
        $add2['uid'] = intval($data['fromUid']);
        $add2['oid'] = intval($data['toUid']);
        $add2['change'] = -1 * floatval(number_format($data['num'],2));
        $add2['action'] = '零用钱转出';
        $add2['detail'] = '{"ubank":"'.$add2['change'].'"}';

        $add3['uid'] = intval($data['toUid']);
        $add3['state'] = 1;
        $add3['oid'] = intval($data['fromUid']);
        $add3['des'] = t($data['desc']);
        $add3['ubank_change'] = floatval(number_format($data['num'],2));
        $add3['ctime'] = time();
        $add3['order_num'] = $data['order_num'];
        $add3['code'] = rand(1000, 9999).rand(1000, 9999).rand(1000, 9999).rand(1000, 9999).rand(1000, 9999);

        // var_dump($add,$add2);exit;
        $res = M('credit_user')->where("uid={$add3['oid']}")->save(array('ubank' => $ubank2 - $add3['ubank_change']));
        $res1 = M('credit_user')->where("uid={$add3['uid']}")->save(array('ubank' => $ubank + $add3['ubank_change']));
        D('credit_record_ubank')->add($add3);
        D('credit_record')->add($add) && D('credit_record')->add($add2);

        model('Credit')->cleanCache($data['toUid']);
        model('Credit')->cleanCache($data['fromUid']);

        return true;
    }    

    public function setUserUbank($charge_type,$serial_number,$change){
        if ($GLOBALS['ts']['mid']) {
            $add['uid'] = $GLOBALS['ts']['mid'];
        }
        $add['type'] = 6;
        $add['charge_type'] = $charge_type;
        $add['serial_number'] = $serial_number;
        $add['action'] = '发出现金';
        $add['des'] = '';
        if($charge_type == 1){
            $add['change'] = floatval($change)/100;
        }
        $add['change'] = floatval($change);
        $add['ctime'] = time();
        $add['detail'] = '';
        if($charge_type == 0 || $charge_type == 1){
            $map['serial_number'] = $mapa['bonus_code'] = $serial_number;
            if ($GLOBALS['ts']['mid']) {
                $map['uid'] = $mapa['bonus_fromuid'] = $GLOBALS['ts']['mid'];
            }
            $detail = D('credit_charge')->where($map)->find();
            if ($detail && $detail['status'] != 1){
                $res = D('credit_charge')->where($map)->setField('status', 1);
                $res1 = M('bonus')->where($mapa)->setField('status', 1);
                if($res !== false && $res1 !== false){
                     $bonus = M('bonus')->where($mapa)->find();
                     if(!$bonus){
                        return false;
                     }
                     $data['bonus_code'] = t($serial_number);
                     $data['bonus_type'] = $bonus['bonus_type'];
                     $data['bonus_fromuid'] = $bonus['bonus_fromuid'];
                     $data['total_amount'] = $bonus['total_amount'];
                     $data['send_time'] = $bonus['send_time'];
                     $data['status'] = 0;
                     $data['to_uid']=0;
                     $data['get_time']=0;
                     //$data['bonus_money'] = number_format(floatval($value['bonusmoney'])/intval($value['bonusmany']),2);
                     
                     if ($bonus['bonus_type'] == 1)
                     {
                         $data['bonus_money'] = (floatval($bonus['total_amount']) / intval($bonus['bonus_many']) );
                         if ($bonus['bonus_many']>=1)
                         {
                         
                              
                             for($i=0;$i<intval($bonus['bonus_many']);$i++)
                             {
                                 $res_ = M('bonus_list')->add($data);
                                 // var_dump(M()->getLastSql());exit;
                                 
                         
                             }
                             $send_staus = true;
                         }
       
                     }
                     if ($bonus['bonus_type'] == 2)
                     {
                         $data['total_amount'] =  $bonus['total_amount'];
                         
                            
                         $bonus_array = Bonus::sendRandBonus($bonus['total_amount'],$bonus['bonus_many'],1);
                         
                            
                         if ($bonus['bonus_many']>=1)
                         {
                             for($i=0;$i<intval($bonus['bonus_many']);$i++)
                             {
                                 $data['bonus_money'] = floatval($bonus_array[$i]);
                         
                                 $res_ = D('bonus_list')->add($data);
                             }
                             $send_staus = true;
                         }
                
                     }
    
                     $make_status =  Bonus::makeRedisBonus($data['bonus_code']);
                     
                     if ($send_staus && $make_status)
                     {
                         D('credit_record')->add($add);
                         self::success(array(
                             'status'  => 1,
                             'message' => '红包发送成功！',
                             'msg' => '红包发送成功！',
                             'pay_type' => $detail['charge_type'],
                             'bonusdata'=>Bonus::getBonusInfoById($bonus),
                         ));
                     }else{
                         self::error(array(
                             'status'  => 0,
                             'message' => '红包发送失败！',
                             'msg' => '红包发送失败！',
                         ));
                     
                     }
                }
                return false;
            }
            return false;
        }elseif ($charge_type == 2) {
            $ubank = model('Credit')->getUserUbank(intval($add['uid']),1);
            D('credit_record')->add($add);
            $res = M('credit_user')->where("uid={$add['uid']}")->save(array('ubank' => $ubank - $add['change']));
            if($res){
                return true;
            }
            return false;
        }
        return false;
    }

}
