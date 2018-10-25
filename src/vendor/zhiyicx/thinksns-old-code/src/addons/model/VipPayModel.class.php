<?php
/**
 * vip模型 .
 *
 * @author jason <yangjs17@yeah.net>
 *
 * @version TS3.0
 */
class VipPayModel extends Model
{
    protected $tableName = 'vip';
    protected $fields = array(0 => 'vid', 1 => 'u_id', 2 => 'serial_number', 3 => 'vip_name', 4 => 'charge_value', 5 => 'Registration_time', 6 => 'due_time', 7 => 'u_time', 8 => 'remarks', 9 => 'charge_type', 10 => 'status', 11 => 'p_id', 12 => 'number', 13 => 'is_personal');


    /**
     * 是否为VIP.
     *
     * @param int $uid 用户uid
     *
     */
    public function isVip($uid){
       
        $map['u_id'] = $uid;
        $map['status'] = 1;
        $res = $this->field('p_id')->where($map)->find();
        $res1 = M('user_group_link')->where("user_group_id={$res['p_id']}")->find();
        if($res && $res1){
            $personal = M('user_group')->where("user_group_id={$res['p_id']} and is_vip=1 or user_group_id={$res['p_id']} and is_business_vip=1")->find();
            if(!$personal){
                return false;
            }
            return true;
        }
        return false;

    }

    /**
     * 是否为个人VIP.
     *
     * @param int $uid 用户uid
     *
     */
    public function isPersonalVip($uid){
        
        $map['u_id'] = $uid;
        $map['status'] = 1;
        $res = $this->field('p_id')->where($map)->find();
        $res1 = M('user_group_link')->where("user_group_id={$res['p_id']}")->find();
        if($res && $res1){
            $personal = M('user_group')->where("user_group_id={$res['p_id']} and is_vip=1")->find();
            if(!$personal){
                return false;
            }
            return true;
        }
        return false;

    }

    /**
     * 是否为商家VIP.
     *
     * @param int $uid 用户uid
     *
     */
    public function isBusinessVip($uid){
        
        $map['u_id'] = $uid;
        $map['status'] = 1;
        $res = $this->field('p_id')->where($map)->find();
        $res1 = M('user_group_link')->where("user_group_id={$res['p_id']}")->find();
        if($res && $res1){
            $business = M('user_group')->where("user_group_id={$res['p_id']} and is_business_vip=1")->find();
            if(!$business){
                return false;
            }
            return true;
        }
        return false;

    }

    /**
     * 是否为商家高级VIP.
     *
     * @param int $uid 用户uid
     *
     */
    public function isBusinessSeniorVip($uid){
        
        $map['u_id'] = $uid;
        $map['status'] = 1;
        $map['p_id'] = 12;
        $res = $this->field('p_id')->where($map)->find();
        $res1 = M('user_group_link')->where("user_group_id={$res['p_id']}")->find();
        if($res && $res1){
            $business = M('user_group')->where("user_group_id={$res['p_id']} and is_business_vip=1")->find();
            if(!$business){
                return false;
            }
            return true;
        }
        return false;

    }

    /**
     * 判断会员是否过期.
     *
     * @param int $uid 用户uid
     *
     */
    function is_VipOverdue($uid){
        $vip = $this->field('vip_name,serial_number,charge_value,Registration_time,due_time,p_id,status')->where("u_id={$uid} and status = 1 and is_upgrade = 0")->find();//查看会员是否过期
        if(!$vip){
            return false;
        }
        $now_time = time();
        $due_time = $vip['due_time'] - (24*3600*3);
        $due_time1 = $vip['due_time'];
         //判断会员是否过期 状态2为过期 1为有效期
        if($due_time == $now_time){

            $data_list = array(
                array('title'=>'商品名称','content'=>'链我'.$vip['vip_name']),
                array('title'=>'到期日期','content'=>'3天后过期'),
                array('title'=>'交易商品','content'=>'链我'.$vip['vip_name'].'服务'),
                array('title'=>'商品说明','content'=>'会员可抢300、400元类别红包'),
            );
            model('Jpush')->noticeMessage($uid,'会员过期通知','会员3天后过期',$vip['serial_number'],3,6,floatval($vip['charge_value']),$data_list);

            return true;
        }elseif ($due_time1 <= time()) {
            $pri['user_group_id'] = $vip['p_id'];
            $this->where("serial_number={$vip['serial_number']}")->setField('status', 2);
            M('user_group_link')->where($pri)->delete();
            $data_list = array(
                array('title'=>'商品名称','content'=>'链我'.$vip['vip_name']),
                array('title'=>'到期日期','content'=>'会员已过期'),
                array('title'=>'交易商品','content'=>'链我'.$vip['vip_name'].'服务'),
                array('title'=>'商品说明','content'=>'会员可抢300、400元类别红包'),
            );
            model('Jpush')->noticeMessage($uid,'会员过期通知','会员已过期',$vip['serial_number'],3,6,floatval($vip['charge_value']),$data_list);

            return true;
        }
        return false;

    }

    //VIP升级处理
    public function vipUpgrade($uid)
    {
        $vip = $this->field('p_id,u_id,number,due_time,Registration_time,vid,charge_type')->where("u_id={$uid} and is_upgrade=1")->find();
        if(!$vip){
            return false;
        }
        $now = time();
        if($vip['number'] == 0){
            
            if($vip['due_time'] <= $now){
                $save['Registration_time'] = $vip['due_time'];
                $save['due_time'] = $vip['due_time'] + 30*24*3600;
            }
        }else{
            $tt = $vip['Registration_time'] + 30*24*3600;
            if($tt <= $now){
                $save['Registration_time'] = $vip['Registration_time'] + 30*24*3600;
                $save['due_time'] = $vip['due_time'] + 30*24*3600*$vip['number'];
                
            }
        }
        switch ($vip['charge_type']) {
            case '0':
                $type = '支付宝充值';
                break;

            case '1':
                $type = '微信充值';
                break;    
            
            case '2':
                $type = '零用钱充值';
                break;
        }
        //升级权限
        $pid = $vip['p_id'] + 1;
        $pri = M('User_group_link')->where("uid={$vip['u_id']} and user_group_id={$vip['p_id']}")->setField('user_group_id',$pid);
        $save['is_upgrade'] = 0;
        $save['vip_name'] = $this->userGroupName($pid);
        $this->where("vid={$vip['vid']}")->save($save);
        $data_list = array(
            array('title'=>'支付方式','content'=>$type),
            array('title'=>'收款人','content'=>'广州链沃网络科技有限公司'),
            array('title'=>'交易商品','content'=>'链我'.$this->userGroupName($pid).'服务'),
            array('title'=>'商品说明','content'=>$save['Registration_time'].'至'.$save['due_time'].'止'),
        );
        model('Jpush')->noticeMessage($add['uid'],'VIP升级生效通知','升级已生效',$vip['serial_number'],1,1,floatval($vip['charge_value']),$data_list);
        return true;
            
    }

    /**
     * VIP充值成功
     *
     * @param string $serial_number 订单号
     */
    public function charge_success($serial_number)
    {
        $map['serial_number'] = $serial_number;
        if ($GLOBALS['ts']['mid']) {
            $map['u_id'] = $GLOBALS['ts']['mid'];
        }
        $vip = $this->where($map)->find();
        // @file_put_contents('./log.txt',$vip);exit;
        if ($vip && $vip['status'] == 0){

            $res = $this->where($map)->setField('status', 1);
            if($res !== false){

                $num = $vip['number'];

                $dd = $this->vipExpireTime($num);
                $avip = $this->where($map)->save($dd);
                //赋予权限
                $pri = model('UserGroupLink')->addJurisdiction($vip['u_id'],$vip['p_id']);
                switch ($vip['charge_type']) {
                    case '0':
                        $type = '支付宝';
                        break;

                    case '1':
                        $type = '微信';
                        break;    
                    
                    case '2':
                        $type = '零用钱';
                        break;
                }
                $add['type'] = 2;
                $add['charge_type'] = intval($vip['charge_type']);
                $add['serial_number'] = t($vip['serial_number']);
                $add['uid'] = intval($vip['u_id']);
                $add['change'] = -intval($vip['charge_value']);
                $add['action'] = '充值会员';
                $add['des'] = $vip['number'].'个月';
                $add['ctime'] = time();
                D('credit_record')->add($add);

                $start = date("Y年m月d日",$dd['Registration_time']);
                $end = date("Y年m月d日",$dd['due_time']);

                $data_list = array(
                    array('title'=>'支付方式','content'=>$type),
                    array('title'=>'收款人','content'=>'广州链沃网络科技有限公司'),
                    array('title'=>'交易商品','content'=>'链我'.$vip['vip_name'].'服务'),
                    array('title'=>'商品说明','content'=>$start.'至'.$end.'止'),
                );
                model('Jpush')->noticeMessage($add['uid'],'VIP充值通知','VIP充值通知',$vip['serial_number'],1,1,floatval($vip['charge_value']),$data_list);
                return true;
            }
        }elseif ($vip && $vip['status'] == 1) {
            $num = $vip['number'];
            $tt = $vip['due_time'];
            $now = time();
            $mon = floor(round($tt - $now)/3600/24 + 30*$num);
            $mmo = date("Y-m-d H:i:s",strtotime("+$mon day"));
            $dd['due_time'] = strtotime($mmo);
            $avip = $this->where($map)->save($dd);
            switch ($vip['charge_type']) {
                case '0':
                    $type = '支付宝充值';
                    break;

                case '1':
                    $type = '微信充值';
                    break;    
                
                case '2':
                    $type = '零用钱充值';
                    break;
            }
            $add['type'] = 2;
            $add['charge_type'] = intval($vip['charge_type']);
            $add['serial_number'] = t($vip['serial_number']);
            $add['uid'] = intval($vip['u_id']);
            $add['change'] = -intval($vip['charge_value']);
            $add['action'] = '会员续费';
            $add['des'] = $vip['number'].'个月';
            $add['ctime'] = time();
            D('credit_record')->add($add);
            $start = date("Y年m月d日",$vip['Registration_time']);
            $end = date("Y年m月d日",$dd['due_time']);

            $data_list = array(
                array('title'=>'支付方式','content'=>$type),
                array('title'=>'收款人','content'=>'广州链沃网络科技有限公司'),
                array('title'=>'交易商品','content'=>'链我'.$vip['vip_name'].'服务'),
                array('title'=>'商品说明','content'=>$start.'至'.$end.'止'),
            );
            model('Jpush')->noticeMessage($add['uid'],'VIP续费通知','VIP续费通知',$vip['serial_number'],1,1,floatval($vip['charge_value']),$data_list);
             return true;
        }elseif ($vip && $vip['status'] == 1 && $vip['is_upgrade'] == 2) {
            $save['is_upgrade'] = 1;
            $res = $this->where($map)->save($save);
            if($res !== false){

                switch ($vip['charge_type']) {
                    case '0':
                        $type = '支付宝';
                        break;

                    case '1':
                        $type = '微信';
                        break;    
                    
                    case '2':
                        $type = '零用钱';
                        break;
                }
                if($vip['number'] == 0){
                    $add['des'] = '1个月';
                    $start = date("Y年m月d日",$vip['due_time']);
                    $end = date("Y年m月d日",$vip['due_time'] + 30*3600*24);
                }else{
                    $add['des'] = $vip['number'].'个月';
                    $start = date("Y年m月d日",$vip['Registration_time'] + 30*3600*24);
                    $end = date("Y年m月d日",$vip['due_time'] + 30*3600*24*$vip['number']);
                }
                $add['type'] = 2;
                $add['charge_type'] = intval($vip['charge_type']);
                $add['serial_number'] = t($vip['serial_number']);
                $add['uid'] = intval($vip['u_id']);
                $add['change'] = -intval($vip['charge_value']);
                $add['action'] = '升级会员';
                $add['ctime'] = time();
                D('credit_record')->add($add);
                $pid = $vip['u_g_id'] + 1;
                $data_list = array(
                    array('title'=>'支付方式','content'=>$type),
                    array('title'=>'收款人','content'=>'广州链沃网络科技有限公司'),
                    array('title'=>'交易商品','content'=>'链我'.$this->userGroupName($pid).'服务'),
                    array('title'=>'商品说明','content'=>$start.'生效至'.$end.'过期'),
                );
                model('Jpush')->noticeMessage($add['uid'],'VIP升级通知','VIP升级通知',$vip['serial_number'],1,1,floatval($vip['charge_value']),$data_list);
                return true;
            }
        }
        return false;
    }

    /**
     * VIP有效期
     *
     * @param int $num 几个月
     */
    public function vipExpireTime($num){

        $mon = date("Y-m-d H:i:s",strtotime("+$num month"));
        $data = array(
                'Registration_time' => time(),
                'due_time' => strtotime($mon)
            );
        if($data){

            return $data;
        }else{

            return false;
        }
    }

    /**
     * VIP列表
     *
     * 
     */
    public function vipList(){
        $data = M('user_group')->field('user_group_id,user_group_name')->where('is_vip=1 or is_business_vip=1')->select();
        return $data;
    }

    public function userGroupName($user_group_id)
    {
        return M('user_group')->field('user_group_name')->where("user_group_id={$user_group_id}")->find()['user_group_name'];
    }

}
