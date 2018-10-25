<?php
namespace Apps\BaseBonus;
include_once 'BonusCoupon.class.php';
class Bonus{
    private $redis ;
    public function __construct()
    {

    }
 
    private function connectRedis(){
        $this->redis = new \Redis();
        if (!$this->redis->connect('127.0.0.1','2830'))
        {
            die('redis disconnect!');
        }else{
            $this->redis->auth('ufdauif$%^&TYUIFGH$%^&%^&TY');
        }
    }
    
    public function setRedis() {
    	self::connectRedis();
    	return true;
    }
    
    public function getRedis() {
    	return $this->redis;
    }
    
    
    //redis红包生成
    public function makeRedisBonus($bonus_code)
    {
        self::connectRedis();
        $bonus_code = isset($bonus_code)?t($bonus_code):0;
        if (!$bonus_code) return 0;
        
        if (!$this->redis->keys("BINFO:$bonus_code"))
       
        {
          
            $bonus_info = M('bonus')->where(array("bonus_code"=>$bonus_code))->select();
            if ($bonus_info)
            {
                $this->redis->HSET("BINFO:$bonus_code","bonus_code",$bonus_code);
                $this->redis->HSET("BINFO:$bonus_code","bonus_many",$bonus_info[0]['bonus_many']);
                $this->redis->HSET("BINFO:$bonus_code","id",$bonus_info[0]['id']);
                $this->redis->HSET("BINFO:$bonus_code","bonus_type",$bonus_info[0]['bonus_type']);
                $this->redis->HSET("BINFO:$bonus_code","bonus_fromuid",$bonus_info[0]['bonus_fromuid']);
                $this->redis->HSET("BINFO:$bonus_code","total_amount",$bonus_info[0]['total_amount']);
                $this->redis->HSET("BINFO:$bonus_code","send_time",$bonus_info[0]['send_time']);
                $this->redis->HSET("BINFO:$bonus_code","bonus_msg",$bonus_info[0]['bonus_msg']);
                $this->redis->HSET("BINFO:$bonus_code","status",$bonus_info[0]['status']);
            }
    
        }
        if ( $this->redis->keys("BINFO:$bonus_code") && !$this->redis->keys("BLIST:$bonus_code"))
        {
            $bonus_list = M('bonus_list')->field('id,bonus_money')->where(array("bonus_code"=>$bonus_code,'to_uid'=>''))->order('bonus_money DESC')->select();
            $bonus_list_count = count($bonus_list);
            for ($i=0;$i<$bonus_list_count;$i++)
            {
                $this->redis->SADD("BLISTO:".$bonus_code,$bonus_list[$i]['id'].'#'.$bonus_list[$i]['bonus_money']);
                $this->redis->SADD("BLISTN:".$bonus_code,$bonus_list[$i]['id'].'#'.$bonus_list[$i]['bonus_money']);
            }
           
        }
    
        return true;
    
    }
    
    
    //随机红包生成
    public function sendRandBonus($total=0, $count=3, $type=2){
        if($total / $count == 0.01){
            $type = 2;
        }
        if($type==1){
            /* $input     = range(0.01, $total, 0.01);
            if($count>1){
                $rand_keys = (array) array_rand($input, $count-1);
                $last    = 0;
                foreach($rand_keys as $i=>$key){
                    $current  = $input[$key]-$last;
                    $items[]  = floatval(number_format($current,2));
                    $last    = number_format($input[$key],2);
                }
            }
            if(floatval(number_format($total-array_sum($items),2)) == '0.00' || floatval(number_format($total-array_sum($items),2)) < '0.01'){
                Bonus::sendRandBonus($total, $count, 1);
                $items[]    = floatval(number_format($total-array_sum($items),2));
                return $items;
            }
            $items[]    = floatval(number_format($total-array_sum($items),2)); */
        	$coupon = new BonusCoupon($total, $count);
        	$items  = $coupon->handle();
        }else{
            $avg      = floatval(number_format($total/$count, 2));
            $i       = 0;
            while($i<$count){
                $items[]  = $i<$count-1?$avg:floatval(number_format($total-array_sum($items),2));
                $i++;
            }
        }
        return $items;
    }
    
    //更新红包状态
    public function updateBonusInfoStatus($bonus_code,$update=true)
    {
        self::connectRedis();
        if ($bonus_code)
        {
            $bonus_list_orginal = $this->redis->SCARD("BLISTO:".$bonus_code);
            if ($bonus_list_orginal == 0)
            {
                $this->redis->HSET("BINFO:$bonus_code","status",0);
                if ($update)
                {
                    $sql ="UPDATE ".C('DB_PREFIX')."bonus
					set status =0
					where bonus_code='".$bonus_code."'";
                    M()->execute($sql);
                }
            }
            	
        }
    }
    
    //我拿到的红包
    public function showMyGets($bonus_code='',$mid='',$update=true)
    {
        self::connectRedis();
        if (!$bonus_code) return 0;
        $bonus_list_gets = $this->redis->SMEMBERS("BGETS:MIM:$bonus_code");
        $gets_count = count($bonus_list_gets);
        for($i=0;$i<$gets_count;$i++)
        {
            $bonus_get_info[$i] = $bonus_list_gets[$i]?explode('#', $bonus_list_gets[$i]):0;
            if ($bonus_get_info[$i][0] == $mid)
            {
                $myGets= $bonus_get_info[$i];
            }
        }
        if ($myGets && $update)
        {
            //update
            // $bonus = M('bonus')->field('money_type')->where("bonus_code='{$bonus_code}'")->find();
            // if($bonus['money_type'] == 1){
            //     $res = X('Credit')->setUserCredit($myGets[0],array('name'=>'bonus_getBonus','ubank'=>floatval($myGets[2]),''),1);
            // }elseif ($bonus['money_type'] == 2) {
            //     $res = X('Credit')->setUserCredit($myGets[0],array('name'=>'bonus_sendcaiyuanbi ','caiyuanbi'=>floatval($myGets[2]),''),1);
            // }
            // $res = X('Credit')->setUserCredit($myGets[0],array('name'=>'bonus_getBonus','ubank'=>floatval($myGets[2]),''),1);
            $res = Bonus::getUserBonus($bonus_code,$myGets[2],$myGets[0]);
            if ($res){
                $data = array();
    
                $data['to_uid'] = $this->mid;
                $data['get_time'] = time();
                	
                $sql ="UPDATE ".C('DB_PREFIX')."bonus_list
				set to_uid ='".$this->mid."',get_time = ".time()."
				where id='".$myGets[1]."'";
    
                M()->execute($sql);
            }
        }
        if ($myGets)
        {
            return $myGets;
        }else{
            return array(0,0,0);
        }
    
    }

    public function getUserBonus($bonus_code,$money,$mid)
    {
        $bonus = M('bonus')->field('money_type,bonus_code,bonus_fromuid,charge_type')->where("bonus_code='{$bonus_code}'")->find();
        if($bonus['money_type'] == 1){
            $add['uid'] = $bonus['bonus_fromuid'];
            $add['oid'] = $mid;
            $add['type'] = 6;
            $add['charge_type'] = $bonus['charge_type'];
            $add['serial_number'] = $bonus['bonus_code'];
            $add['action'] = '领取现金';
            $add['des'] = '';
            $add['change'] = floatval($money);
            $add['ctime'] = time();
            $add['detail'] = '';
            $ubank = model('Credit')->getUserUbank(intval($add['oid']),1);
            D('credit_record')->add($add);
            $res = M('credit_user')->where("uid={$add['oid']}")->save(array('ubank' => $ubank + $add['change']));
            if($res){
                return true;
            }
            return false;
        }elseif ($bonus['money_type'] == 2) {
            $add['uid'] = $bonus['bonus_fromuid'];
            $add['oid'] = $mid;
            $add['type'] = 6;
            $add['charge_type'] = $bonus['charge_type'];
            $add['serial_number'] = $bonus['bonus_code'];
            $add['action'] = '领取财源币';
            $add['des'] = '';
            $add['change'] = floatval($money);
            $add['ctime'] = time();
            $add['detail'] = '';
            $ubank = model('Credit')->getUserUbank(intval($add['oid']),2);
            D('credit_record')->add($add);
            $res = M('credit_user')->where("uid={$add['oid']}")->save(array('caiyuanbi' => $ubank + $add['change']));
            if($res){
                return true;
            }
            return false;
        }
    }
    
    //检查redis红包是否存在
    public function checkBonusExist($bonus_code)
    {
        self::connectRedis();
        if ($this->redis->keys("BINFO:$bonus_code"))
        {
            return 1;
        }else{
            return 0;
        }
    }
    
        public function getRedisBonus($bonus_code,$update=true){
            self::connectRedis();
            $bonus_info = $this->redis->HGETALL("BINFO:$bonus_code");
            
            if ($bonus_info['status'] == '0') return array('status'=>0);
            $bonus_list_count = $this->redis->SCARD("BGETS:MID:$bonus_code");
            if (intval($bonus_list_count) < intval($bonus_info['bonus_many']))
            {
                if ($this->redis->SISMEMBER("BGETS:MID:$bonus_code",$this->mid))
                {
                    $getMybonusInfo = self::showMyGets($bonus_code,$this->mid,$update);
                    return array('status'=>1,'uid'=>$this->mid,'bonusListId'=>$getMybonusInfo[1],'bonusMondy'=>$getMybonusInfo[2]);
                   
                }else{
                    $this->redis->SADD("BGETS:MID:$bonus_code",$this->mid);
                    $b_IM = $this->redis->SPOP("BLISTO:".$bonus_code);
                    $this->redis->SADD("BGETS:MIM:$bonus_code",$this->mid.'#'.$b_IM);
                    $getMybonusInfo = self::showMyGets($bonus_code,$this->mid,TRUE);
                    self::updateBonusInfoStatus($bonus_code,true);
                    return array('status'=>2,'uid'=>$this->mid,'bonusListId'=>$getMybonusInfo[1],'bonusMondy'=>$getMybonusInfo[2]);
                    //array('uid'=>$this->mid,'bonusListId'=>$getMybonusInfo[1],'bonusMondy'=>$getMybonusInfo[2]);
                }
            
            }else{
                //$this->redis->HSET("BINFO:$bonus_code","status",'0');
                return array('status'=>3);
            }
    }
    
    public function getBonusType($type)
    {
        if ($type){
             switch ($type) {
            case '1':
                $ret = '个人';
                break;
            case '2':
                $ret = '商家';
                break;
            case '2':
                $ret = '企业';
                break;

        }
        return $ret;
        }
    }
    //
    public function getAmountRange($total_amount)
    {
        
        
    }
    
    public function checkBonusExistById($bonus_id)
    {
        if (!$bonus_id)
        {
            return 0;
        }else{
            $bonusInfo = M("bonus")->where(array("id"=>$bonus_id,'status'=>1))->find();
            echo M()->getLastSql();
            if ($bonusInfo)
            {
                return 1;
            }else{
                return 0;
            }     
        }
    }
    
    public function getAppIndexBonus($bonus_id,$user_id)
    {
        
        if ($bonus_id)
        {
            $bonusInfo = M("bonus")->where(array("id"=>$bonus_id,'status'=>1))->find();
            
            if ($bonusInfo)
            {
                unset($bonusInfo['id']);
		         $hasGet = M("bonus_list")->where(array("bonus_code"=>$bonusInfo['bonus_code'],'to_uid'=>$user_id))->find();
                 
		         if ($hasGet) return array("status"=>0);
                 $ad = M('ad_user')->field('img')->where("uid={$bonusInfo['bonus_fromuid']} and place=5")->find();
                 $info['img'] = unserialize($ad['img']);
                 foreach ($info['img'] as $key => $value) {
                     $attachInfo = model('Attach')->getAttachById($value['banner']);
                     $bonusInfo['bonus_ad'] = getImageUrl($attachInfo['save_path'].$attachInfo['save_name']);
                 }
                $bonusInfo['send_time']=date("m-d H:i",$bonusInfo['send_time']) ;
                $bonusInfo['avatar']=model('Avatar')->init($bonusInfo['bonus_fromuid'])->getUserAvatar()['avatar_middle'];
                return $bonusInfo;
            }else{
                return array("status"=>0);
            }
        }else{
            return array("status"=>0);
        }
    }
    
    //红包详情
    public function getBonusDetail($bonus_code,$hasGet = 0,$thisPage)
    {
        $map['bonus_code'] = $bonus_code;
        if ($hasGet)
        {
            $map['to_uid'] = array('neq','');
        }else{
            $map['to_uid'] = array('eq','');
        }
        
        //增加浏览量
        M('bonus')->where(array(
            "bonus_code" => $bonus_code
        ))->setInc('views');
        
       
        $bonus_list = M('bonus_list')->field('to_uid,get_time,bonus_money')->where($map)->order('get_time DESC')->findPage(10);
        $bonus_list_count = count($bonus_list['data']);
        
        
        $bonusInfo['bonus_gets']['has_get']  = Bonus::countGetBonus($bonus_code,1);
        $bonusInfo['bonus_gets']['not_get']  = Bonus::countGetBonus($bonus_code,0);
        

        if ($bonus_list['data']){
            for ($i=0;$i<$bonus_list_count;$i++)
            {
                $bonus_list['data'][$i]['username'] =D("user")->getUserInfo($bonus_list['data'][$i]['to_uid'])['uname'];
                $bonus_list['data'][$i]['get_time'] = date("H:i:s", $bonus_list['data'][$i]['get_time']);
                $bonus_list['data'][$i]['bonus_avatar'] = self::getUserAvator($bonus_list['data'][$i]['to_uid'], 'avatar_middle');
              
            }
        }
         $bonusInfo['listdata']  = $bonus_list;
        if (intval($thisPage) >intval($bonus_list['nowPage'])) $bonusInfo['listdata']=array();
       
        
        return $bonusInfo;
    }
    
    public function getBonusCodeById($bonus_id)
    {
        return M("bonus")->field('bonus_code')->where(array("id"=>trim($bonus_id)))->find();     
    }
    
    public function getBonusInfoById($bonus_id)
    {
        
        $bonusInfo = M("bonus")->where(array("id"=>trim($bonus_id)))->find();
        if (!$bonusInfo) return array();
        $bonusInfo['bonus_id']=$bonusInfo['id'];
        unset($bonusInfo['id']);
        $userInfo = D("User")->getUserInfo($bonusInfo['bonus_fromuid']);
        $bonusInfo['bonus_avatar'] = self::getUserAvator($bonusInfo['bonus_fromuid'], 'avatar_middle');
        $bonusInfo['bonus_username'] =isset($userInfo['remark '])?$userInfo['remark ']:$userInfo['uname'];
        $bonusInfo['send_time'] = date("m-d H:i:s",$bonusInfo['send_time']);
        return $bonusInfo;
    }
    
    public function countGetBonus($bonus_code,$hasGet = 1){
        $map['bonus_code'] = $bonus_code;
        if ($hasGet)
        {
            $map['to_uid'] = array('neq','');
        }else{
            $map['to_uid'] = array('eq','');
        }
        return M('bonus_list')->where($map)->count();

    }
    
    public function getUserAvator($uid,$avatarType){
        if (!intval($uid)) return "";
        $user_info = model('Cache')->get('user_info_api_'.$uid);
        if (!$user_info) {
            $user_info = model('User')->where('uid='.$uid)->field('uid,uname,sex,location,province,city,area,intro')->find();
            $avatar = model('Avatar')->init($uid)->getUserAvatar();
            $user_info['avatar'] = $avatar;
        }
       
        if ($user_info['avatar']['avatar_middle'])
        {
            return $user_info['avatar']['avatar_middle'];
        }
        
    }
    
    public function makeSavePwd($string,$salt='')
    {
        return md5(md5($string).md5($string.$salt.'as'));
    }
    
    public function getRangeType($money_range)
    {
        //50以下为即抢50；50-100为即抢100；100-200为即抢200；
        //200-300为即抢300；300-500为即抢500；
        
        $money_range = floatval($money_range);
        if ($money_range <= 50 ){ 
            return 1;
        }else if ($money_range >50 && $money_range <= 100){        
            return 2;
        }else if ($money_range >100 && $money_range <= 200){
            return 3;
        }else if ($money_range >200  && $money_range <= 300){
            return 4;
        }else if ($money_range >300){
            return 5;
        }
        
    }
    
    public function getTotalAmtWhere($rangType)
    {
        $rangType = intval($rangType);
        if ($rangType == 1 ){
            return array('elt',50);
        }else if ($rangType == 2 ){
            return array(array('gt',50),array('elt',100));
        }else if ($rangType == 3 ){
           return array(array('gt',100),array('elt',200));
        }else if ($rangType == 4 ){
            return array(array('gt',200),array('elt',300));
        }else if ($rangType == 5){
            return array('gt','300');
        }
        
    }
    
    //会员组
    public function getBonusUserGroups($type)
    {
        $sysGroups = D('UserGroup')->getAllGroup();
        
        //过滤
        $filteGroups = array('管理员','巡逻员','禁言用户','频道管理','正常用户');
        
        foreach($sysGroups as $gid => $groupName)
        {
            if (in_array($groupName, $filteGroups))
            {
                unset($sysGroups[$gid]);
            }
        }
        if($type){
            foreach($sysGroups as $k => $v)
            {
                $sysGroups_[$k] = array(
                        'groupId' => $k,
                        'groupName'=> $v,
                        'subCategory' => self::getVerifyCategroy($k),
                );
            }
        }else{
            $sysGroups_[] = array(
                'groupId' => 0,
                'groupName'=> '所有',
                'subCategory' => array(),
            );
            foreach($sysGroups as $k => $v)
            {
                $sysGroups_[] = array(
                        'groupId' => $k,
                        'groupName'=> $v,
                        'subCategory' => self::getVerifyCategroy($k),
                );
            }
        }
        return $sysGroups_;
    }
    
    private function getVerifyCategroy($pid){
        $map['pid']=$pid;
        $category = D('user_verified_category')->field('user_verified_category_id,title')->where($map)->findAll();
        

        for($i=0;$i<count($category);$i++)
        {
            $category_[$i]['id'] = $category[$i]['user_verified_category_id'];
            $category_[$i]['name'] = $category[$i]['title'];
            
        }
        $arr = array('id'=>0,'name'=>'所有');
        array_unshift($category_,$arr);
        
        return $category_;
    }
    
    //会员组别ＣＨＥＣＫ
    public function checkUserGroup($mid,$user_group,$user_group_verify)
    {
           // if ($mid) $userGroups = D('User')->getUserInfo($mid)['user_group'];
           if ($mid) $userGroups = M('user_group_link')->field('user_group_id')->where("user_group_id={$user_group} and uid={$mid}")->find();
           if(!$userGroups) return 4;

           $checkGroupStatus = false;
           if ($user_group && $userGroups)
           {
                 // for ($i=0;$i<count($userGroups);$i++)
                 // {
                 //       if ($userGroups[$i]['user_group_id'] == $user_group) $checkGroupStatus=true;
                 // }
                 if($userGroups['user_group_id'] == $user_group) $checkGroupStatus=true;
                 if ($checkGroupStatus == false) return 2;
                 
                 if ($checkGroupStatus && $user_group_verify != -1)
                 {
                     $map['uid'] = $mid;
                     $map['verified'] = 1;
                     $map['user_verified_category_id'] = $user_group_verify;
                     $data = M('user_verified')->where($map)->find();
                     
                     if (!$data) return 3;
   
                 }
       
           }
           return 1;
    }
    public function rmBonusNotUseEvent($eid)
    {
        if ($eid) M("bonusevent_list")->where("edi  = '$eid'")->delete();
            
    }
    
    public function getBonusData($range_type,$bonus_type){
    
        /*if ($this->data['radar']){
         $inTimes = intval(time()) - 10800;
         $map['send_time'] = array('egt',$inTimes);
         //$map['range_type']=isset($range_type)?intval($range_type):'';
         // $map['bonus_type']=isset($bonus_type)?intval($bonus_type):'';
         }else{*/
         
        $range_type_ = isset($this->data['range_type']) ? $this->data['range_type'] : $range_type;
        //$bonus_type_ = isset($this->data['bonus_type']) ? $this->data['bonus_type'] : $bonus_type;
        if (!$range_type_)  $range_type_ =1;
        //if (!$bonus_type_)  $bonus_type_ =1;
        $calcScope = Bonus::calcScope($this->data['latitude'],$this->data['longitude'],3000);
        $map['total_amount'] = self::getTotalAmtWhere($range_type_);
        $map['status'] = array('neq','-1');
        $map['is_qrcode'] = array('neq','1');
        $bonusConfig = model('Xdata')->get('bonus_Admin:redpacketConfig');
        $stime = time() - (intval($bonusConfig['redpacket_return_hours'] * 3600));
        $map['send_time'] = array('egt',$stime);
        $map['eid'] = 0;
        if(!$this->data['h5']){
            $map['latitude'] = array('between ',array($calcScope['minLat'],$calcScope['maxLat']));
            $map['longitude'] = array('between ',array($calcScope['minLng'],$calcScope['maxLng']));
        }
        $data = M("bonus")->where($map)->order('send_time desc')->findPage(10);
        
        unset($data['html']);
        $data['rangeType'] = $range_type_;
    
       foreach ($data ['data'] as $k => $v)
        {
            $data['data'][$k]['bonus_id']= $data['data'][$k]['id'];
            unset($data['data'][$k]['id']);
            
            $userInfo = D("User")->getUserInfo($data['data'][$k]['bonus_fromuid']);
            $data['data'][$k]['bonus_avatar']= $userInfo['avatar_middle'];
            $data['data'][$k]['bonus_username']=isset($userInfo['remark '])?$userInfo['remark ']:$userInfo['uname'];
            $data['data'][$k]['send_time']=date("m-d H:i",$data['data'][$k]['send_time']) ;
            // $data['data'][$k]['status']=$data['data'][$k]['status'] ;
            $data['data'][$k]['status_msg']=$data['data'][$k]['status']=='1'?"末抢完":"已抢完" ;
            $data['data'][$k]['bonus_state']=$v['status'];
            $data['data'][$k]['uid']=$v['bonus_fromuid'];
            $data['data'][$k]['bonus_msg']=$v['bonus_msg'];
            $data['data'][$k]['content']=$v['bonus_msg'];
            $r = model('Follow')->doFollow($this->mid, $v['bonus_fromuid']);
            if($r) {
            	$data['data'][$k]['is_followed'] = true;
            }else {
            	$data['data'][$k]['is_followed'] = false;
            }
            if(model('UserGroupLink')->userGroupIcon($v['bonus_fromuid'],1))
            {
                $data['data'][$k]['icon'] = model('UserGroupLink')->userGroupIcon($v['bonus_fromuid'],1);
            }
            if(model('UserGroupLink')->userGroupIcon($v['bonus_fromuid'],2))
            {
                $data['data'][$k]['vip_icon'] = model('UserGroupLink')->userGroupIcon($v['bonus_fromuid'],2);
            }
            // $data['data'][$k]['bonus_cn_type']=Bonus::getBonusType($data['data'][$k]['bonus_type']);
            //$data['data'][$k]['amount_range']=Bonus::getAmountRange($data['data'][$k]['total_amount']);
            //unset($data['data'][$k]['bonus_code']);
            // $data['data'][$k]['is_followed'] = M('Follow')->getFollowState($this->mid,$data['data'][$k]['bonus_fromuid'])['following'];
    
            //用户相关状态
            // $userGets = Bonus::showMyGets($data['data'][$k]['bonus_code'],$this->mid,0);
            // $data['data'][$k]['user_get'] = $userGets[1]?1:0;
            // $data['data'][$k]['user_money'] = floatval($userGets[2])>0?$userGets[2]:'0';
            // $data['data'][$k]['expire_time'] = $data['data'][$k]['expire_time'];
            if(Bonus::coverBonus($v['bonus_fromuid'])){
                $data['data'][$k]['bonus_ad'] = Bonus::coverBonus($v['bonus_fromuid']);
            }
            unset($data['data'][$k]['bonus_fromuid']);
        }
        if (intval($this->page) >intval($data['nowPage'])) $data['data']= array();
        if (!$data['data']) $data['data']= array();
        return $data;
    
    }
    
    public function getBonusEventList($range_type,$bonus_type)
    {
        $range_type_ = isset($this->data['range_type']) ? $this->data['range_type'] : $range_type;
        $bonus_type_ = isset($this->data['bonus_type']) ? $this->data['bonus_type'] : $bonus_type;
        
        $map['money_type']=2;
        $map['eid'] = array('gt',0);
        $calcScope = Bonus::calcScope($this->data['latitude'],$this->data['longitude'],3000);
        $map['total_amount'] = self::getTotalAmtWhere($range_type_);
        $map['status'] = array('neq','-1');
        $bonusConfig = model('Xdata')->get('bonus_Admin:redpacketConfig');
        $stime = time() - (intval($bonusConfig['redpacket_return_hours'] * 3600));
        $map['send_time'] = array('egt',$stime);
        $map['latitude'] = array('between ',array($calcScope['minLat'],$calcScope['maxLat']));
        $map['longitude'] = array('between ',array($calcScope['minLng'],$calcScope['maxLng']));
        $data = D("bonus")->field('*')->where($map)->order('send_time desc')->findPage(10);
        unset($data['html']);

        foreach ($data ['data'] as $k => $v)
        {

            $data['data'][$k]['bonus_id']= $data['data'][$k]['id'];
            unset($data['data'][$k]['id']);
            $userInfo = D("User")->getUserInfo($data['data'][$k]['bonus_fromuid']);
            $data['data'][$k]['bonus_avatar']= $userInfo['avatar_middle'];
            $data['data'][$k]['bonus_username']=isset($userInfo['remark '])?$userInfo['remark ']:$userInfo['uname'];
            $data['data'][$k]['send_time']=date("m-d H:i",$data['data'][$k]['send_time']) ;
            // $data['data'][$k]['status']=$data['data'][$k]['status'] ;
            $data['data'][$k]['status_msg']=$data['data'][$k]['status']=='1'?"末抢完":"已抢完" ;
            $data['data'][$k]['bonus_state']=$v['status'];
            $data['data'][$k]['bonus_msg']=$v['bonus_msg'];
            if(Bonus::coverBonus($v['bonus_fromuid'])){
                $data['data'][$k]['bonus_ad'] = Bonus::coverBonus($v['bonus_fromuid']);
            }
            if(model('UserGroupLink')->userGroupIcon($v['bonus_fromuid'],1))
            {
                $data['data'][$k]['icon'] = model('UserGroupLink')->userGroupIcon($v['bonus_fromuid'],1);
            }
            if(model('UserGroupLink')->userGroupIcon($v['bonus_fromuid'],2))
            {
                $data['data'][$k]['vip_icon'] = model('UserGroupLink')->userGroupIcon($v['bonus_fromuid'],2);
            }
            // $data['data'][$k]['bonus_cn_type']=Bonus::getBonusType($data['data'][$k]['bonus_type']);
            //$data['data'][$k]['amount_range']=Bonus::getAmountRange($data['data'][$k]['total_amount']);
            //unset($data['data'][$k]['bonus_code']);
            // $data['data'][$k]['is_followed'] = M('Follow')->getFollowState($this->mid,$data['data'][$k]['bonus_fromuid'])['following'];
        
            //用户相关状态
            // $userGets = Bonus::showMyGets($data['data'][$k]['bonus_code'],$this->mid,0);
            // $data['data'][$k]['user_get'] = $userGets[1]?1:0;
            // $data['data'][$k]['user_money'] = floatval($userGets[2])>0?$userGets[2]:'0';
            
            $bonusEventData = self::getBonuseventInfoByEid($data['data'][$k]['eid']);
            $data['data'][$k] = array_merge($bonusEventData,$data['data'][$k]);
        }
        if (intval($this->page) >intval($data['nowPage'])) $data['data']=array();
        if (!$data['data']) $data['data']= array();
        return $data;
        
    }
    
    private function getBonuseventInfoByEid($eid)
    {
        if (!$eid) return array();
        $eventData =  M('bonusevent_list')->where(array('eid'=>$eid))->find();
        if ($eventData)
        {
            unset($eventData['longitude']);
            unset($eventData['latitude']);
            $eventData['event_name'] = $eventData['name'];
            unset($eventData['name']);
        }
        //$eventData['title']=$eventData['name'];
        return $eventData;

    }
    
    
    public function getRangeTypeSimpleArr(){
        return array('即抢50','即抢100','即抢200','即抢300','即抢400');    
    }
    public function getRangeTypeArr(){
        return array(array(
            'tid'=>'1',
            'name'=>'即抢50'
            ),
            array(
            'tid'=>'2',
            'name'=>'即抢100'
            ),
            array(
            'tid'=>'3',
            'name'=>'即抢200'
            ),
            array(
            'tid'=>'4',
            'name'=>'即抢300'
            ),
            array(
            'tid'=>'5',
            'name'=>'即抢400'
            ),
            
        );
    }
 
    public function getCaiyuanbiTodayIncome($mid)
    {
        if (!$mid) return 0;
        $todayTimeStar = strtotime(date("Y-m-d 00:00:00"));
        $todayTimeEnd  = strtotime(date("Y-m-d 23:00:00"));
        $map['to_uid'] = $mid;
        $map['get_time'] = array(
            array(
               'EGT',$todayTimeStar
            ),
            array(
                'ELT',$todayTimeEnd
            )
        );
        $total = M("bonus_list")->where($map)->sum('bonus_money');
        return $total=is_null($total)?0:$total;
    }
    //活动类型
    public function getEventType()
    {
        $map['del'] = array('neq','1');
        $result = M('bonusevent_cate')->field('`cid`,`name`')->where($map)->order('leval desc')->findall();
        return $result;
    }
    
    public function getEventData($cid){
        
        if (!$cid) return array();
        $bonusConfig = model('Xdata')->get('bonus_Admin:redpacketConfig');
        $stime = time() - (intval($bonusConfig['redpacket_return_hours'] * 3600));
        $data = M("bonusevent_list")->table('`'.C('DB_PREFIX').'bonusevent_list` AS a LEFT JOIN `'.C('DB_PREFIX').'bonus` AS b ON a.`eid` = b.`eid`')
            ->field('*')
            ->where("a.cid = '$cid' and a.del = '0' and b.eid > '0' and b.send_time >= '$stime'")
            ->order('b.send_time DESC')
            ->findPage(10);
        if ($data) unset($data['html']);
        //$data['sql']=M()->getLastSql();
        foreach ($data ['data'] as $k => $v)
        {
            $data['data'][$k]['bonus_id']= $data['data'][$k]['id'];
            unset($data['data'][$k]['id']);
            $data['data'][$k]['event_name']= $data['data'][$k]['name'];
            unset($data['data'][$k]['name']);
            $userInfo = D("User")->getUserInfo($data['data'][$k]['bonus_fromuid']);
            $data['data'][$k]['bonus_avatar']= $userInfo['avatar_middle'];
            $data['data'][$k]['bonus_username']=isset($userInfo['remark '])?$userInfo['remark ']:$userInfo['uname'];
            $data['data'][$k]['send_time']=date("m-d H:i",$data['data'][$k]['send_time']) ;
            // $data['data'][$k]['status']=$data['data'][$k]['status'] ;
            $data['data'][$k]['status_msg']=$data['data'][$k]['status']=='1'?"末抢完":"已抢完" ;
            $data['data'][$k]['bonus_state']=$v['status'];
            $data['data'][$k]['bonus_msg']=$v['bonus_msg'];
            if(Bonus::coverBonus($v['bonus_fromuid'])){
                $data['data'][$k]['bonus_ad'] = Bonus::coverBonus($v['bonus_fromuid']);
            }
            if(model('UserGroupLink')->userGroupIcon($v['bonus_fromuid'],1))
            {
                $data['data'][$k]['icon'] = model('UserGroupLink')->userGroupIcon($v['bonus_fromuid'],1);
            }
            if(model('UserGroupLink')->userGroupIcon($v['bonus_fromuid'],2))
            {
                $data['data'][$k]['vip_icon'] = model('UserGroupLink')->userGroupIcon($v['bonus_fromuid'],2);
            }
            // $data['data'][$k]['bonus_cn_type']=Bonus::getBonusType($data['data'][$k]['bonus_type']);
            //$data['data'][$k]['amount_range']=Bonus::getAmountRange($data['data'][$k]['total_amount']);
            //unset($data['data'][$k]['bonus_code']);
            // $data['data'][$k]['is_followed'] = M('Follow')->getFollowState($this->mid,$data['data'][$k]['bonus_fromuid'])['following'];
        
            //用户相关状态
            // $userGets = Bonus::showMyGets($data['data'][$k]['bonus_code'],$this->mid,0);
            // $data['data'][$k]['user_get'] = $userGets[1]?1:0;
            // $data['data'][$k]['user_money'] = floatval($userGets[2])>0?$userGets[2]:'0';
        }
        if (intval($this->page) >intval($data['nowPage'])) $data['data']= array();
        if (!$data['data']) $data['data']= array();
        return $data;
    }
    
    public function getTitleByEid($eid){
        if ($eid)
        {
            return M("bonusevent_list")->field('name')->where("eid = '{$eid}'")->find()['name'];
        }
    }
    
    public function getCaiyuanData($data)
    {
        if ($data['caiyuanType'] == 'Expire')
        {
            
            $map['bl.Expire_time'] = array(array('lt', date("Y-m-d H:i:s")),array('neq', date("0000-00-00 00:00:00")));

        }else if ($data['caiyuanType'] == 'Used')
        {
            
            $map['bl.status'] = array('eq', '2');
        }else if ($data['caiyuanType'] == 'unUsed')
        {
            $map['bl.status'] = array('eq', '0');
        }else if ($data['year'])
        {
            $map['bl.send_time'] = array(
                array('gt',$data['year']."-01-01 00:00:00"),
                array('lt',$data['year']."-12-31 23:59:59")
            );
        }
        if ($data['year'] && $data['month'])
        {
            $map['bl.send_time'] = array(
                array('gt',$data['year'].'-'.$data['month'].'-01 00:00:00'),
                array('lt',$data['year'].'-'.$data['month'].'-31 23:59:59')
                
            );
        }

       
        
        $map['money_type'] = $data['money_type'];
        $map['to_uid'] = $this->mid;
        
        $list = D()->table('`'.C('DB_PREFIX').'bonus_list` AS bl LEFT JOIN `'.C('DB_PREFIX').'bonus` AS b ON bl.`bonus_code` = b.`bonus_code`')
        ->field('bl.*, b.`money_type`,b.`status` as bonus_status')
        ->where($map)
        ->order('bl.send_time DESC')
        ->findPage(8);
        
        $list['sql']=M()->getLastSql();
        $list['data'] = self::formartBonusListData($list['data']);
        unset($list['html']);
        return $list;
    }
    
    
    public function formartBonusListData($data)
    {
        if (!is_array($data)) return array();
        foreach ($data as $k => $v)
        {
            $data[$k]['bonuslist_id']= $data[$k]['id'];
            unset($data[$k]['id']);
            $userInfo = D("User")->getUserInfo($data[$k]['bonus_fromuid']);
            $data[$k]['bonus_avatar']= $userInfo['avatar_middle'];
            $data[$k]['bonus_username']=isset($userInfo['remark '])?$userInfo['remark ']:$userInfo['uname'];
            $data[$k]['send_time']=date("m-d H:i",$data[$k]['send_time']) ;
            $data[$k]['status']=$data[$k]['status'];
            $data[$k]['eid']=$data[$k]['eid'] == null ?0:$data[$k]['eid'];
            if ($data[$k]['eid'])
            {
                $eventInfo = self::getEevntInfo($data[$k]['eid']);
            }
            array_merge($data[$k],$eventInfo);

        }
        return $data;
    }
    
    
    public function getEevntInfo($eid)
    {
        if (!$eid) return array();
        $eventInfo = M("bonusevent_list")->where("eid = '{$eid}")->limit(1)->find();
    }
    
    public function formatSendOutCaiyuan($data)
    {
        if ($data)
        {
            $data_ = array();

            foreach ($data as $k => $v)
            {
                $data_[$k]['caiyuanType'] = $data[$k]['eid'] != '' ? "商家财源":"个人财源";
                $data_[$k]['caiyuanStatus'] = $data[$k]['status'] == 1 ? "正在进行中":"已抢完";
                $data_[$k]['caiyuanNum'] = $data[$k]['total_amount'].'元';
                $data_[$k]['send_time'] = date("Y-m-d",$data[$k]['send_time']);
                
            }
            return $data_;
        }else{
            return array();
        }
        
        
    }
    
    public function getTotalSendoutCaiyuan($uid)
    {
        if (!$uid) return 0;
        $map['bonus_fromuid'] = array('eq',$uid);
        $total = M("bonus")->where($map)->sum("total_amount");

        return $total;
        
    }
    //封面红包
    public function coverBonus($uid)
    {
        $ad = M('ad_user')->field('img')->where("uid={$uid} and place=3")->find();
        if(!$ad){
            return false;
        }
        if(!model('VipPay')->isBusinessVip($uid)){
            M('ad_user')->where("uid={$uid} and place=3")->delete();
            return false;
        }
        $attachInfo = model('Attach')->getAttachById(intval($ad['img']));
        return getImageUrl($attachInfo['save_path'].$attachInfo['save_name']);
    }
    //领取红包广告
    public function getBonusAd($uid,$place){
        $ad = M('ad_user')->field('img,adUrl,clicks')->where("uid={$uid} and place={$place}")->find();
        
        $num['clicks'] = intval($ad['clicks'] + 1);
        M('ad_user')->where("uid={$uid} and place={$place}")->save($num);
        if(!$ad){
            $data['img'] = '';
            $data['imgUrl'] = '';
            return $data;
        }else{
            $info['img'] = unserialize($ad['img']);
            foreach ($info['img'] as $key => $value) {
                $attachInfo = model('Attach')->getAttachById($value['banner']);
                $data['img'] = getImageUrl($attachInfo['save_path'].$attachInfo['save_name']);
                $data['imgUrl'] = $value['bannerurl'];
            }
            return $data;
        }
        
    }

    //开机红包广告
    public function bootAd($place)
    {
        $ad = M('ad')->field('content')->where("place={$place}")->order('ad_id desc')->find();
        $info['content'] = unserialize($ad['content']);
        foreach ($info['content'] as $key => $value) {
            $attachInfo = model('Attach')->getAttachById($value['banner']);
            $data['img'] = getImageUrl($attachInfo['save_path'].$attachInfo['save_name']);
            $data['imgUrl'] = $value['bannerurl'];
        }
        return $data;
    }

    //查找用户会员限制次数/领取红包额度限制 $status 0-限制次数，1-限制额度
    public function userBonusNumber($uid,$status=0){
        $bb = M('user_group_link')->join('ts_user_group ON ts_user_group.user_group_id=ts_user_group_link.user_group_id')->field('ts_user_group_link.user_group_id')->where("uid={$uid} and is_vip=1 or uid={$uid} and is_business_vip=1")->find();
        $get_bonus_number = 'get_bonus_number_'.$bb['user_group_id'];
        $bonusConfig = model('Xdata')->get('bonus_Admin:redpacketConfig');
        if(array_key_exists($get_bonus_number, $bonusConfig)){
            $sum = explode(',', $bonusConfig[$get_bonus_number]);
            if($status == 0){
                return intval($sum[0]);
            }elseif ($status == 1) {
                return intval($sum[1]);
            }
            
        }else{
            if($status == 0){
                return 10;
            }elseif ($status == 1) {
                return 150;
            }
        }
    }

    //今日领取红包个数统计
    public function todayGetBonus($uid){
        $t = time();
        $start = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
        $end = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t));
        $get_bonus = M('bonus_list')->join('ts_bonus ON ts_bonus_list.bonus_code=ts_bonus.bonus_code')->where("to_uid={$uid} and {$start} <= get_time and get_time <= {$end} and ts_bonus.money_type=1")->count();
        return $get_bonus;
    }

    //未领取红包返回
    public function unclaimedBonusReturn($uid){
        $bonusConfig = model('Xdata')->get('bonus_Admin:redpacketConfig');
        $unclaimedBonus = M('bonus')->field('id,bonus_code,bonus_fromuid,send_time,money_type')->where("bonus_fromuid={$uid} and status=1")->find();
        // var_dump($unclaimedBonus);exit;
        // var_dump(M()->getLastSql());exit;
        $time = intval($unclaimedBonus['send_time'])+(intval($bonusConfig['redpacket_return_hours'] * 3600));
        if(time() >= $time){
            $res = M('bonus')->where("id={$unclaimedBonus['id']}")->setField('status', 2);
            if($res){
                $bonusList = M('bonus_list')
                            ->join('ts_bonus ON ts_bonus_list.bonus_code=ts_bonus.bonus_code')
                            ->field('ts_bonus_list.bonus_money,ts_bonus.money_type')
                            ->where("ts_bonus_list.bonus_code='{$unclaimedBonus['bonus_code']}' and to_uid=0")
                            ->select();
                foreach ($bonusList as $key => $value) {
                    switch ($value['money_type']) {
                    case '1':
                        $sum[$key] = $value['bonus_money'];
                        $type = '零用钱';
                        break;
                    case '2':
                        $sum1[$key] = $value['bonus_money'];
                        $type = '财源币';
                        break;
                    }
                }
                if($type == '零用钱'){
                    $money = array_sum($sum)?array_sum($sum):0;
                    $ubank = model('Credit')->getUserCredit(intval($unclaimedBonus['bonus_fromuid']));
                    $ubank = intval($ubank['credit']['ubank']['value']);
                    M('credit_user')->where("uid={$unclaimedBonus['bonus_fromuid']}")->save(array('ubank' => $ubank + intval($money)));
                }elseif ($type == '财源币') {
                    $money = array_sum($sum1)?array_sum($sum1):0;
                    $cyb = model('Credit')->getUserCredit(intval($unclaimedBonus['bonus_fromuid']));
                    $cyb = intval($cyb['credit']['caiyuanbi']['value']);
                    M('credit_user')->where("uid={$unclaimedBonus['bonus_fromuid']}")->save(array('caiyuanbi' => $cyb + intval($money)));
                }
                $data_list = array(
                    array('title'=>'退款方式','content'=>'退回'.$type),
                    array('title'=>'到账时间','content'=>date('Y-m-d H:i:s',time())),
                    array('title'=>'退款原因','content'=>'红包超过'.$bonusConfig['redpacket_return_hours'].'小时未被领取'),
                    array('title'=>'交易单号','content'=>$unclaimedBonus['bonus_code']),
                    array('title'=>'备注','content'=>'了解红包详情，可点击查看详情'),
                );
                model('Jpush')->noticeMessage($unclaimedBonus['bonus_fromuid'],'红包退还通知','红包退还通知',$unclaimedBonus['id'],3,7,floatval($money),$data_list);

            }

        }else{
            return false;
        }
        
        
    }

    //红包分享链接 1为微信朋友圈，2为qq跟空间
    public function share($share_type)
    {
        $data['weixin'] = 'http://m.lianwoapp.com/H5';
        $data['qq'] = 'http://m.lianwoapp.com/H5';
        $data['weibo'] = 'http://m.lianwoapp.com/H5';
        return $data;
    }
/**
 * 根据经纬度和半径计算出范围
 * @param string $lat 经度
 * @param String $lng 纬度
 * @param float $radius 半径
 * @return Array 范围数组
 */
    public function calcScope($lat, $lng, $radius) 
    {
        $degree = (24901*1609)/360.0;
        $dpmLat = 1/$degree;

        $radiusLat = $dpmLat*$radius;
        $minLat = $lat - $radiusLat;       // 最小经度
        $maxLat = $lat + $radiusLat;       // 最大经度

        $mpdLng = $degree*cos($lat * (PI/180));
        $dpmLng = 1 / $mpdLng;
        $radiusLng = $dpmLng*$radius;
        $minLng = $lng - $radiusLng;      // 最小纬度
        $maxLng = $lng + $radiusLng;      // 最大纬度

        /** 返回范围数组 */
        $scope = array(
            'minLat'    =>  $minLat,
            'maxLat'    =>  $maxLat,
            'minLng'    =>  $minLng,
            'maxLng'    =>  $maxLng
            );
        return $scope;
    }
/**
 * 根据经纬度和半径查询在此范围内的所有的电站
 * @param  String $lat    经度
 * @param  String $lng    纬度
 * @param  float $radius 半径
 * @return Array         计算出来的结果
 */
    public function searchByLatAndLng($lat, $lng, $radius) 
    {
        $scope = $this->calcScope($lat, $lng, $radius);        // 调用范围计算函数，获取最大最小经纬度
        /** 查询经纬度在 $radius 范围内的电站的详细地址 */
        $sql = 'SELECT `字段` FROM `表名` WHERE `Latitude` < '.$scope['maxLat'].' and `Latitude` > '.$scope['minLat'].' and `Longitude` < '.$scope['maxLng'].' and `Longitude` > '.$scope['minLng'];

        $stmt = self::$db->query($sql);
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);       // 获取查询结果并返回
        return $res;
    }

/**
 * 获取两个经纬度之间的距离
 * @param  string $lat1 经一
 * @param  String $lng1 纬一
 * @param  String $lat2 经二
 * @param  String $lng2 纬二
 * @return float  返回两点之间的距离
 */
    public function calcDistance($lat1, $lng1, $lat2, $lng2) {
        /** 转换数据类型为 double */
        $lat1 = doubleval($lat1);
        $lng1 = doubleval($lng1);
        $lat2 = doubleval($lat2);
        $lng2 = doubleval($lng2);
        /** 以下算法是 Google 出来的，与大多数经纬度计算工具结果一致 */
        $theta = $lng1 - $lng2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        return ($miles * 1.609344);
    }    
   
}