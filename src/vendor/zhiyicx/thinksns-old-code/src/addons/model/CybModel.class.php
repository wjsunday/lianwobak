<?php
/**
 * 财源币模型 .
 *
 * @author jason <yangjs17@yeah.net>
 *
 * @version TS3.0
 */
class CybModel extends Model
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
                $cyb = model('Credit')->getUserUbank(intval($detail['uid']),2);
                switch ($detail['charge_type']) {
                    case '0':
                        $type = '支付宝';
                        break;
                    case '1':
                        $type = '微信';
                        break;       
                }
                $add['type'] = 2;
                $add['charge_type'] = intval($detail['charge_type']);
                $add['uid'] = intval($detail['uid']);
                $add['serial_number'] = t($detail['serial_number']);
                $add['action'] = '充值财源币';
                $add['des'] = '';
                $add['change'] = -floatval($detail['charge_cyb']);
                $add['ctime'] = time();
                $add['detail'] = '{"caiyuanbi":"'.$add['change'].'"}';
                M('credit_user')->where("uid={$add['uid']}")->save(array('caiyuanbi' => $cyb - $add['change']));
                D('credit_record')->add($add);
                $data_list = array(
                    array('title'=>'支付方式','content'=>$type),
                    array('title'=>'收款人','content'=>'广州链沃网络科技有限公司'),
                    array('title'=>'交易商品','content'=>$detail['charge_cyb'].'元财源币'),
                    array('title'=>'商品优惠','content'=>'赠送'.$this->giveCyb($detail['charge_value']).'元财源币'),
                    array('title'=>'商品说明','content'=>'财源币可用于发财源红包'),
                );
                model('Jpush')->noticeMessage($add['uid'],'财源币充值通知','财源币充值通知',$detail['serial_number'],1,1,-floatval($add['change']),$data_list);
                model('Credit')->cleanCache($add['uid']);
                return true;
            }
        }

        return false;
    }

    //财源币过期
    public function cybOverdue($uid){
        $data = M('bonus_list')->join('ts_bonus ON ts_bonus_list.bonus_code=ts_bonus.bonus_code')->field('ts_bonus_list.expire_time,ts_bonus_list.bonus_code,ts_bonus_list.bonus_money,ts_bonus_list.bonus_fromuid,ts_bonus.address')->where("to_uid={$uid} and money_type=2 and ts_bonus_list.status=0")->find();
        // var_dump($data);
        if(!$data){
            return false;
        }
        $now_time = time();
        $due_time = strtotime($data['expire_time']) - (24*3600*3);
        $due_time1 = strtotime($data['expire_time']);
        if($due_time == $now_time){
            // $res = M('bonus_list')->where("bonus_code='{$data['bonus_code']}' and to_uid={$uid} and expire_time='{$data['expire_time']}'")->setField('status', 2);
            // var_dump(M()->getLastSql());exit;
            $data_list = array(
                array('title'=>'商家名称','content'=>getUserName($data['bonus_fromuid'])),
                array('title'=>'可用日期','content'=>date('Y年m月d号',$due_time).'前'),
                array('title'=>'商品说明','content'=>'3天后过期'),
            );
            model('Jpush')->noticeMessage($uid,'财源过期通知','财源3天后过期',$data['bonus_code'],3,5,floatval($data['bonus_money']),$data_list);
         return true;
        }elseif ($due_time1 <= $now_time) {
            $res = M('bonus_list')->where("bonus_code='{$data['bonus_code']}' and to_uid={$uid} and expire_time='{$data['expire_time']}'")->setField('status', 2);
            // var_dump(M()->getLastSql());exit;
            $data_list = array(
                array('title'=>'商家名称','content'=>getUserName($data['bonus_fromuid'])),
                array('title'=>'商品说明','content'=>'财源过期'),
            );
            model('Jpush')->noticeMessage($uid,'财源已过期通知','财源过期',$data['bonus_code'],3,5,floatval($data['bonus_money']),$data_list);
        }
        return false;
            
    }

    public function setUserCyb($serial_number,$change)
    {
        if ($GLOBALS['ts']['mid']) {
            $add['uid'] = $GLOBALS['ts']['mid'];
        }
        $add['type'] = 6;
        $add['charge_type'] = 2;
        $add['serial_number'] = $serial_number;
        $add['action'] = '发出财源币';
        $add['des'] = '';
        $add['change'] = intval($change);
        $add['ctime'] = time();
        $add['detail'] = '';
        $cyb = model('Credit')->getUserUbank(intval($add['uid']),2);
        D('credit_record')->add($add);
        $res = M('credit_user')->where("uid={$add['uid']}")->save(array('caiyuanbi' => $cyb - $add['change']));
        if($res){
            return true;
        }
        return false;
    }

    //赠送财源币
    public function giveCyb($price)
    {
        return M('get_chargecyb')->field('give_quantity')->where("charge_value={$price}")->find()['give_quantity'];
    }

    //奖励财源
    public function rewardCyb($uid,$money)
    {
        $cyb = model('Credit')->getUserUbank(intval($uid),2);
        $res = M('credit_user')->where("uid={$uid}")->save(array('caiyuanbi' => $cyb + $money));
        if($res){
            return true;
        }
        return false;
    }
}
