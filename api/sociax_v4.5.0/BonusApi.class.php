<?php

defined('SITE_PATH') || exit('Forbidden');
include_once SITE_PATH.'/apps/bonus/Common/Bonus.class.php';
include_once SITE_PATH.'/apps/Bonusevent/Common/common.php';

use Api;
use Apps\BaseBonus\Bonus;
use Apps\Bonusevent\Common;
use Apps\Bonusevent\Model\Bonusevent;

use apps\Common\Extend\Pay\Alipay\aop\AopClient;
use apps\Common\Extend\Pay\Alipay\aop\request\AlipayTradeAppPayRequest;
/**
 * 红包API.
 *
 * @author singsin <singsin@vip.qq.com>
 **/
class BonusApi extends Api
{ 
    //发财源币
    private function sendCaiyuanCoin($eid){
        // self::verifyppwd(0);
        $this->verifyppwd();

        $send_staus = false;
        $value = array();
        $value['bonus_type'] = isset($this->data['bonus_type'])?intval($this->data['bonus_type']):1;
        $value['bonusmany'] = intval($this->data['bonusmany']);
        $value['bonusmoney'] = (intval($this->data['bonusmoney']));
        $value['bonusmsg'] = t($this->data['bonusmsg']);
        $value['address_title'] = t($this->data['address_title']);
        $value['address'] = t($this->data['address']);
        $value['latitude'] = (floatval($this->data['latitude']));
        $value['longitude'] = (floatval($this->data['longitude']));
        $value['expire_time'] = (($this->data['expire_time']));
        $value['money_type'] = (intval($this->data['money_type']));
        
        $value['user_group'] = (intval($this->data['user_group']));
        $value['user_group_verify'] = (intval($this->data['user_group_verify']));   
             if (!$value['bonusmany'])
            {
                self::error(array(
                    'status'  => 0,
                    'message' => '请填写红包个数',
                ));
    
            }   
            elseif (!$value['bonusmoney'])
            {
                self::error(array(
                    'status'  => 0,
                    'message' => '请填写红包金额',
                ));
            }elseif ($value['bonusmoney']<0 || !is_int($value['bonusmoney']))
            {
                self::error(array(
                    'status'  => 0,
                    'message' => '金额必需大于０且不能带小数',
                ));
            }elseif(!$value['expire_time'] && $value['money_type'] == 2)
            {
                self::error(array(
                    'status'  => 0,
                    'message' => '请填写过期时间',
                ));
            }
            
            $bonusConfig = model('Xdata')->get('bonus_Admin:redpacketConfig');
            
            if ($value['bonusmoney'] > $bonusConfig['redpacket_max_money'])
            {
                self::error(array(
                    'status'  => 0,
                    'message' => '单次发红包最大额度不能超过'. $bonusConfig['redpacket_max_money'],
                ));
            
            }
            
            $data = array();
            $data['bonus_fromuid'] = $this->mid;
            $data['send_time'] = time();
            $data['bonus_type'] = $value['bonus_type'] ;
            $data['bonus_code'] = md5(rand(1000,9999).$data['send_time'].'as'.rand(1000,9999).$data['send_time'].$data['bonus_fromuid'].$data['bonus_type']);
            $data['bonus_msg'] = $value['bonusmsg'];
            $data['bonus_many'] =($value['bonusmany']);
            $data['latitude'] = $value['latitude'] ;
            $data['longitude'] = $value['longitude'] ;
            $data['money_type'] = 2 ;//财源币
            $data['expire_time'] = $value['expire_time'] ;
            $data['address_title'] = $value['address_title'] ;
            $data['address'] = $value['address'] ;
            $data['status'] =1;
            //
            $data['eid'] = $eid;
            if(!$data['eid']){
                $data['eid'] = 0;
            }
            //
            $data['user_group'] = $value['user_group'];
            $data['user_group_verify'] = $value['user_group_verify'];
            $my_bank = model('Credit')->getUserUbank($this->mid,2);
            if ($value['bonus_type'] == 1)//普通
            {
                $data['total_amount'] =  (floatval($value['bonusmoney']) * intval($value['bonusmany']) );
            }elseif ($value['bonus_type'] == 2)//随机
            {
                $data['total_amount'] =  (floatval($value['bonusmoney']));
            }
            //rangetype
            $data['range_type'] = Bonus::getRangeType($data['total_amount']);
            $data['certification_group'] = model('UserGroupLink')->certificationGroup($this->mid);
            if (floatval($my_bank)<floatval($data['total_amount']))
            {
                Bonus::rmBonusNotUseEvent($eid);
                self::error(array(
                    'status'  => 3,
                    'message' => '财源币不足,请充值！',
                    'pay_type' => 3,
                ));
            }
                
            
            if(!model('Cyb')->setUserCyb($data['bonus_code'],$data['total_amount']))
            {
                Bonus::rmBonusNotUseEvent($eid);
                self::error(array(
                    'status'  => 0,
                    'message' => '财源币扣款失败，如有问题请联系客服！',
                ));
            }else{
            	$data['expire_time'] = date("Y-m-d H:i:s", $data['expire_time']);
                $bid = M('bonus')->add($data);
                if (!$bid) {
                    Bonus::rmBonusNotUseEvent($eid);
                    $this->_error('系统出错');
                }
                unset($data['bonus_msg']);
                unset($data['bonus_many']);
                unset($data['status']);
                unset($data['latitude']);
                unset($data['longitude']);
                unset($data['user_group']);
                unset($data['user_group_verify']);
                unset($data['address_title']);
                unset($data['address']);
                unset($data['certification_group']);
                $data['to_uid']='0';
                $data['get_time']='0';
                //$data['bonus_money'] = number_format(floatval($value['bonusmoney'])/intval($value['bonusmany']),2);
                if ($value['bonus_type'] == 1)
                {
                    $data['bonus_money'] = $value['bonusmoney'];
                    if ($value['bonusmany']>=1)
                    {
                         
            
                        for($i=0;$i<intval($value['bonusmany']);$i++)
                        {
                            $res_ = M('bonus_list')->add($data);
                            // var_dump(M()->getLastSql());exit;

                        }
                        $send_staus = true;
                    }
                     
                }
                if ($value['bonus_type'] == 2)
                {
                    $data['total_amount'] =  $value['bonusmoney'];
                    $data['status'] = 0;
                     
            
                    $bonus_array = Bonus::sendRandBonus($value['bonusmoney'],$value['bonusmany'],1);
                     
            
                    if ($value['bonusmany']>=1)
                    {
                        for($i=0;$i<intval($value['bonusmany']);$i++)
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
            
                    self::success(array(
                        'status'  => 1,
                        'message' => '红包发送成功！',
                        'bonusdata'=>Bonus::getBonusInfoById($bid),
                        'msg' => '红包发送成功！',
                        'pay_type' => 3,
                    ));
                }else{
                    self::error(array(
                        'status'  => 0,
                        'message' => '红包发送失败！',
                        'msg' => '红包发送失败！',
                    ));
                     
                }
                 
            }
    }
    
    
    
    //普通现金红包
    //总金额　＝　人数　*　金额　
    //红包类型=1;
    public function sendCashBonus($eid){
        if (!$this->data['money_type']|| !in_array($this->data['money_type'],array(1,2)) ) self::error(array(
                'status'  => 0,
                'msg' => '红包类型出错，请选择零用钱或财源币',
                'message' => '红包类型出错，请选择零用钱或财源币',
            ));
        
        //财源币
        if ($this->data['money_type'] == 2)
        {
            self::sendCaiyuanCoin($eid);
        }
        
        // self::verifyppwd(0);
        
        $send_staus = false;
        $value = array();
        $value['bonus_type'] = isset($this->data['bonus_type'])?intval($this->data['bonus_type']):1;
        $value['bonusmany'] = intval($this->data['bonusmany']);
        $value['bonusmoney'] = abs(floatval($this->data['bonusmoney']));
        $value['bonusmsg'] = t($this->data['bonusmsg']);
        $value['address_title'] = t($this->data['address_title']);
        $value['address'] = t($this->data['address']);
        $value['latitude'] = (floatval($this->data['latitude']));
        $value['longitude'] = (floatval($this->data['longitude']));
        
        $value['user_group'] = (intval($this->data['user_group']));
        $value['user_group_verify'] = (intval($this->data['user_group_verify'])); 
        $value['is_follow'] = intval($this->data['is_follow']);
        
        if (!$value['bonusmany'])
        {
            self::error(array(
                'status'  => 0,
                'message' => '请填写红包个数',
            ));

        }   
        elseif (!$value['bonusmoney'])
        {
            self::error(array(
                'status'  => 0,
                'message' => '请填写红包金额',
            ));
        }
        elseif ($value['bonusmoney'] < 0.01)
        {
            self::error(array(
                'status'  => 0,
                'message' => '请填写红包金额',
            ));
        }
        $bonusConfig = model('Xdata')->get('bonus_Admin:redpacketConfig');
        
        if ($value['bonusmoney'] > $bonusConfig['redpacket_max_money'])
        {
            self::error(array(
                'status'  => 0,
                'message' => '单次发红包最大额度不能超过'. $bonusConfig['redpacket_max_money'],
            ));

        }
        

         $data = array();
         $data['bonus_fromuid'] = $this->mid;
         $data['send_time'] = time();
         $data['bonus_type'] = $value['bonus_type'] ;
         $data['bonus_code'] = md5(rand(1000,9999).$data['send_time'].'as'.rand(1000,9999).$data['send_time'].$data['bonus_fromuid'].$data['bonus_type']);
         $data['bonus_msg'] = $value['bonusmsg'];
         $data['bonus_many'] =($value['bonusmany']);
         $data['latitude'] = $value['latitude'] ;
         $data['longitude'] = $value['longitude'] ;
         $data['address_title'] = $value['address_title'] ;
         $data['address'] = $value['address'] ;
         $data['status'] =1;
         $data['is_follow'] =$value['is_follow'];
         //eid
         $data['eid'] = $eid ;
         if(!$data['eid']){
            $data['eid'] = 0;
         }
         //
         $data['user_group'] = $value['user_group']; 
         $data['user_group_verify'] = $value['user_group_verify'];

         if ($value['bonus_type'] == 1)
         {
             $data['total_amount'] =  (floatval($value['bonusmoney']) * intval($value['bonusmany']) );
         }elseif ($value['bonus_type'] == 2)
         {
             $data['total_amount'] =  (floatval($value['bonusmoney']));
         }
         //rangetype
         $data['range_type'] = Bonus::getRangeType($data['total_amount']);
         $data['certification_group'] = model('UserGroupLink')->certificationGroup($this->mid);

         $data['charge_type'] = intval($this->data['pay_type']);
         
         if($data['charge_type'] == 0 || $data['charge_type'] == 1)
         {
            $res = $this->createCharge($data);
            if(!$res)
            {
                Bonus::rmBonusNotUseEvent($eid);
                self::error(array(
                    'status'  => 0,
                    'message' => '充值失败！',
                    'data' => $res,
                ));
            }
        }
         elseif ($data['charge_type'] == 2) {
             $this->verifyppwd();
             $Credit = model('Credit')->getUserUbank(intval($this->mid),1);
             // $my_bank = floatval($Credit['credit']['ubank']['value']);
             // @file_put_contents('./log.txt',$Credit);exit;
             // @file_put_contents('./log1.txt',$my_bank);exit;
             if (floatval($Credit)<floatval($data['total_amount']))
             {

                 self::error(array(
                     'status'  => 3,
                     'message' => '钱包余额不足,请充值！',
                     'msg' => '钱包余额不足,请充值！',
                     'pay_type' => $data['charge_type'],
                 ));
             }
        }
         if(!model('Ubank')->setUserUbank($data['charge_type'],$data['bonus_code'],$data['total_amount']))
         {
            self::error(array(
                'status'  => 0,
                'message' => '钱包扣款失败，如有问题请联系客服！',
                'msg' => '钱包扣款失败，如有问题请联系客服！',
            ));
         }else{

                     $bid = M('bonus')->add($data);
                     if (!$bid) $this->_error('Data insert error ');
                     unset($data['bonus_msg']);
                     unset($data['bonus_many']);
                     unset($data['status']);
                     unset($data['latitude']);
                     unset($data['longitude']);
                     unset($data['user_group']);
                     unset($data['user_group_verify']);
                     unset($data['address_title']);
                     unset($data['address']);
                     unset($data['certification_group']);
                     $data['to_uid']='0';
                     $data['get_time']='0';
                     //$data['bonus_money'] = number_format(floatval($value['bonusmoney'])/intval($value['bonusmany']),2);
                     
                     if ($value['bonus_type'] == 1)
                     {
                         $data['bonus_money'] = $value['bonusmoney'];
                         if ($value['bonusmany']>=1)
                         {
                         
                              
                             for($i=0;$i<intval($value['bonusmany']);$i++)
                             {
                                 $res_ = M('bonus_list')->add($data);
                                 // var_dump(M()->getLastSql());exit;
                                 
                         
                             }
                             $send_staus = true;
                         }
       
                     }
                     if ($value['bonus_type'] == 2)
                     {
                         $data['total_amount'] =  $value['bonusmoney'];
                         $data['status'] = 0;
                         
                         	
                         $bonus_array = Bonus::sendRandBonus($value['bonusmoney'],$value['bonusmany'],1);
                         	
                         if ($value['bonusmany']>=1)
                         {
                             for($i=0;$i<intval($value['bonusmany']);$i++)
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

                         self::success(array(
                             'status'  => 1,
                             'message' => '红包发送成功！',
                             'msg' => '红包发送成功！',
                             'pay_type' => $data['charge_type'],
                             'bonusdata'=>Bonus::getBonusInfoById($bid),
                         ));
                     }else{
                         self::error(array(
                             'status'  => 0,
                             'message' => '红包发送失败！',
                             'msg' => '红包发送失败！',
                         ));
                     
                     }
                     
           }

    }
    
    
    ///////end
    
    
    //领取红包
    
    public function getCashBonus(){
        
        $bonus_code = isset($this->data['bonus_code'])?t($this->data['bonus_code']):$this->_error("红包参数错误！");
        
        if (!$this->mid) $this->_error("请登入系统！");
        //if (!$bonus_code) $this->_error("红包代码有误！");
            //start check
            	
            $bonusInfo = M("bonus")->where("bonus_code = '{$bonus_code}'")->limit(1)->find();
            $bonus = Bonus::getBonusCodeById($bonusInfo['id']);
            $bonusDetail = Bonus::getBonusDetail($bonus['bonus_code'],1,$this->page);
            if ($bonusInfo){
                /*if ($bonusInfo['bonus_type'] != '1')
                {
                    $this->_error("红包类型错误!"); 
                }*/
                
                // if ($bonusInfo['expire_time'] !='0000-00-00 00:00:00' && $bonusInfo['expire_time'] != '' && date('Y-m-d H:i:s') > $bonusInfo['expire_time'])
                // {
                //     $this->_errorBonus("红包已过期！",$bonus_code);
                // }
                if($bonusInfo['status'] == 2)
                {
                    $this->_errorBonus("红包已过期！",$bonus_code);
                }
                if($bonusInfo['money_type'] == 2 && $bonusInfo['bonus_fromuid'] == $this->mid)
                {
                    $this->_errorBonus("自己不能抢自己的财源红包",$bonus_code);
                }
                if ($bonusInfo['user_group'] || $bonusInfo['user_group_verify']){
                    $sys_user_groups = Bonus::getBonusUserGroups(1);        
                    $checkStatus = Bonus::checkUserGroup($this->mid,$bonusInfo['user_group'],$bonusInfo['user_group_verify']);
                    foreach ($sys_user_groups[$bonusInfo['user_group']]['subCategory'] as $key => $value) 
                    {
                        if($value['id'] == $bonusInfo['user_group_verify']){
                            $user_group_verify = $value['name'];
                        }
                        
                    }
                    if ($checkStatus == 2 || $checkStatus == 4)  $this->_errorBonus("这个红包只能是".$sys_user_groups[$bonusInfo['user_group']]['groupName']."才能抢",$bonus_code,$bonusInfo['user_group']);
                    if ($checkStatus == 3)  $this->_errorBonus("这个红包只能是".$sys_user_groups[$bonusInfo['user_group']]['groupName'].'-'.$user_group_verify."才能抢！",$bonus_code,$bonusInfo['user_group']);
                        
                }
                //关注
                if ($bonusInfo['is_follow'] == '1')
                {
                    $isFollow = M('Follow')->getFollowState($this->mid,$bonusInfo['bonus_fromuid'])['following'];
                    if (!$isFollow) $this->_errorBonus("请右上角先关注我才能抢红包哦!",$bonus_code);
                }
                if($bonusInfo['money_type']==1)
                {
                    if(Bonus::todayGetBonus($this->mid) >= Bonus::userBonusNumber($this->mid))
                    {
                        $this->_errorBonus('今天领取现金红包超过次数,请充值会员',$bonus_code,7);
                    }
                    if(intval($bonusInfo['total_amount']) > Bonus::userBonusNumber($this->mid,1))
                    {
                        $this->_errorBonus('现金红包超出额度,请充值会员',$bonus_code,7);
                    }
                }
                
                
                
        
            }else{
                $this->_error('红包不存在');
            }
        
            if (!Bonus::checkBonusExist($bonus_code))
            {
                $this->_error('红包不存在！');
            }else{
                
                    $update = $bonusInfo['money_type']==1?true:false;
                    $getStatus = Bonus::getRedisBonus($bonus_code,$update);
                    if ($getStatus['status'] == 0)
                    {
                        $this->_errorBonus("红包已抢完了!",$bonus_code);
                    }else if ($getStatus['status'] == 1)
                    {
                        //$this->_error("您已抢过了");
                        self::success(array(
                            'status'  => 0,
                            'message' => '您已抢过了！',
                            'bonus_state' => $bonusInfo['status'],
                            'bonus_money'=>$getStatus['bonusMondy'],
                            'avatar'=>model('Avatar')->init($getStatus['uid'])->getUserAvatar()['avatar_middle'],
                            'data_list' =>$bonusDetail['listdata']['data'],
                            'has_get'=>$bonusDetail['bonus_gets']['has_get'],
                            'not_get'=>$bonusDetail['bonus_gets']['not_get'],
                            'bonus_code'=>$bonus['bonus_code'],
                            'totalPages'=>$bonusDetail['listdata']['totalPages'],
                            'totalRows'=>$bonusDetail['listdata']['totalRows'],
                            'nowPage'=>$bonusDetail['listdata']['nowPage'],
                        ));
                    }else if ($getStatus['status'] == 2)
                    {
                        self::success(array(
                                'status'  => 1,
                                'message' => '成功抢到红包！',
                                'bonus_state' => $bonusInfo['status'],
                                'bonus_money'=>$getStatus['bonusMondy'],
                                'bonusListId'=>$getStatus['bonusListId'],
                                'uid'=>$getStatus['uid'],
                                'avatar'=>model('Avatar')->init($getStatus['uid'])->getUserAvatar()['avatar_middle'],
                                'advertisement'=>Bonus::getBonusAd($bonusInfo['bonus_fromuid'],2),
                                'bonus_code'=>$bonus['bonus_code'],
                                
                        ));
                            
                        
                    }
            }
    }

    //领取二维码红包
    
    public function getQrCodeBonus(){
        
        $bonus_code = isset($this->data['bonus_code'])?t($this->data['bonus_code']):$this->_error("红包参数错误！");
        
        if (!$this->mid) $this->_error("请登入系统！");
        //if (!$bonus_code) $this->_error("红包代码有误！");
            //start check
                
            $bonusInfo = M("bonus")->where("bonus_code = '{$bonus_code}' and is_qrcode=1")->limit(1)->find();
            $bonus = Bonus::getBonusCodeById($bonusInfo['id']);
            
        
            if (!Bonus::checkBonusExist($bonus_code))
            {
                $this->_error('红包不存在！');
            }else{
                
                    $update = $bonusInfo['money_type']==1?true:false;
                    $getStatus = Bonus::getRedisBonus($bonus_code,$update);
                    if ($getStatus['status'] == 0)
                    {
                        $this->_errorBonus("红包已抢完了!",$bonus_code);
                    }else if ($getStatus['status'] == 1)
                    {
                        //$this->_error("您已抢过了");
                        self::success(array(
                            'status'  => 0,
                            'message' => '您已抢过了！',
                            'bonus_state' => $bonusInfo['status'],
                            'bonus_money'=>$getStatus['bonusMondy'],
                            'avatar'=>model('Avatar')->init($getStatus['uid'])->getUserAvatar()['avatar_middle'],
                        ));
                    }else if ($getStatus['status'] == 2)
                    {
                        self::success(array(
                                'status'  => 1,
                                'message' => '成功抢到红包！',
                                'bonus_state' => $bonusInfo['status'],
                                'bonus_money'=>$getStatus['bonusMondy'],
                                'bonusListId'=>$getStatus['bonusListId'],
                                'uid'=>$getStatus['uid'],
                                'avatar'=>model('Avatar')->init($getStatus['uid'])->getUserAvatar()['avatar_middle'],
                                'advertisement'=>Bonus::getBonusAd($bonusInfo['bonus_fromuid'],2),
                                'bonus_code'=>$bonus['bonus_code'],
                                
                        ));
                            
                        
                    }
            }
    }
    
    //大厅红包列表
    public function getIndexBonusList()
    {
        
//         if ($this->data['radar'])
//         {
// //             $radardata=model("Cache")->get('app_radar_data');
// //             if ($radardata)
// //             {
// //                 return $radardata;
// //             }else{
//                  $radardata = Bonus::getBonusData('', '');
//                 // model("Cache")->set('app_radar_data',$radardata);
//                  return $radardata;
// //             }
            

//         }else{
//             $data=model("Cache")->get('app_index_data');
//             if ($data)
//             {
//                 return $data;
//             }else{
                $data['range_type'] = Bonus::getRangeTypeArr(); 
                // $data['user_groups'] = Bonus::getBonusUserGroups();
                // $this->data['bonus_type']=isset($this->data['bonus_type'])?$this->data['bonus_type']:1;
                // foreach ($data['range_type'] as $k => $v)
                // {
                //     $data['data_list'][$k] = Bonus::getBonusData(intval($k+1),$this->data['bonus_type']);
                //     $data['data_list'][$k]['rangeType'] =intval($k+1);
                // }
               
                // $bonusConfig = model('Xdata')->get('bonus_Admin:redpacketConfig');
                // $data['appBonus'] = Bonus::getAppIndexBonus($bonusConfig['app_index_bonus'],$this->mid);
                //model("Cache")->set('app_index_data',$data);
                $data['status'] = 1;
                $data['message'] = '操作成功';
                return $data;
                
            //}
      //  }
      
    }
    
    public function getBonusData()
    {

        $data = Bonus::getBonusData();
        if ($data['data']){
            $data['msg'] = '操作成功';
            $data['status'] = '1';
        }else{
            $data['msg'] = '没有数据';
            $data['status'] = '0';
        }
      
        return $data;
    }
    
//     public function testCache()
//     {
//         if ($this->data['key']) return model("Cache")->get($this->data['key']);
//     }
   
    
    //
    public function getBonusDetail(){
         if (!$this->data['bonus_id']){
             $this->error("bonus_id　error");
         }
         $bonus = Bonus::getBonusCodeById($this->data['bonus_id']);
         $bonusInfo = M("bonus")->where("bonus_code = '{$bonus['bonus_code']}'")->limit(1)->find();
         if (!$bonus){
             return  $this->error("bonus_code　error");
         }
         if($bonusInfo['eid'] > 0){
            $name = M('bonusevent_list')->field('name')->where("eid={$bonusInfo['eid']}")->find();
         }
         $bonusDetail = Bonus::getBonusDetail($bonus['bonus_code'],1,$this->page);
         foreach ($bonusDetail['listdata']['data'] as $key => $value) {
            $sum[$key] = $value['bonus_money'];
         }
         $userInfo = D("User")->getUserInfo($bonusInfo['bonus_fromuid']);
         // 用户相关状态
         $userGets = Bonus::showMyGets($bonusInfo['bonus_code'],$this->mid,0);
        if ($bonusDetail['listdata']['data']){
           self::success(array(
                'status'  => $bonusInfo['status'],
                'message' => '操作成功',
                'bonus_state' => $bonusInfo['status'],
                'bonus_fromuid' => $bonusInfo['bonus_fromuid'],
                'bonus_username' => isset($userInfo['remark'])?$userInfo['remark']:$userInfo['uname'],
                'bonus_avatar'=>model('Avatar')->init($bonusInfo['bonus_fromuid'])->getUserAvatar()['avatar_middle'],
                'is_followed' => M('Follow')->getFollowState($this->mid,$bonusInfo['bonus_fromuid'])['following'],
                'is_follow' => $bonusInfo['is_follow'],
                'bonus_cn_type' => Bonus::getBonusType($bonusInfo['bonus_type']),
                'amount_range' => Bonus::getAmountRange($bonusInfo['total_amount']),
                'user_get' => $userGets[1]?1:0,
                'user_money' => floatval($userGets[2])>0?$userGets[2]:'0',
                'expire_time' => $bonusInfo['expire_time'],
                'total_amount'=>$bonusInfo['total_amount'],
                'bonus_many'=>$bonusInfo['bonus_many'],
                'surplus_many'=>floatval($bonusInfo['total_amount'] - array_sum($sum)),
                'data_list' =>$bonusDetail['listdata']['data'],
                'name' => $name['eid'],
                'money_type' => intval($bonusInfo['money_type']),
                'has_get'=>$bonusDetail['bonus_gets']['has_get'],
                'not_get'=>$bonusDetail['bonus_gets']['not_get'],
                'bonus_code'=>$bonusInfo['bonus_code'],
                'share'=>Bonus::share($this->data['share_type']),
                'totalPages'=>$bonusDetail['listdata']['totalPages'],
                'totalRows'=>$bonusDetail['listdata']['totalRows'],
                'nowPage'=>$bonusDetail['listdata']['nowPage'],
            )); 
        }else{
            self::error(array(
                'status'  => 0,
                'message' => '暂无人领取红包',
                'bonus_state' => $bonusInfo['status'],
                'bonus_fromuid' => $bonusInfo['bonus_fromuid'],
                'bonus_username' => isset($userInfo['remark'])?$userInfo['remark']:$userInfo['uname'],
                'bonus_avatar'=>model('Avatar')->init($bonusInfo['bonus_fromuid'])->getUserAvatar()['avatar_middle'],
                'is_follow' => $bonusInfo['is_follow'],
                'is_followed' => M('Follow')->getFollowState($this->mid,$bonusInfo['bonus_fromuid'])['following'],
                'bonus_cn_type' => Bonus::getBonusType($bonusInfo['bonus_type']),
                'amount_range' => Bonus::getAmountRange($bonusInfo['total_amount']),
                'user_get' => $userGets[1]?1:0,
                'user_money' => floatval($userGets[2])>0?$userGets[2]:'0',
                'expire_time' => $bonusInfo['expire_time'],
                'total_amount'=>$bonusInfo['total_amount'],
                'bonus_many'=>$bonusInfo['bonus_many'],
                'name' => $name['eid'],
                'surplus_many'=>floatval($bonusInfo['total_amount'] - array_sum($sum)),
                'share'=>Bonus::share($this->data['share_type']),
                'has_get'=>$bonusDetail['bonus_gets']['has_get'],
                'not_get'=>$bonusDetail['bonus_gets']['not_get'],
            ));
        }      
    }
    

    public function _errorBonus($msg,$bonus_code,$status=0){
            $bonusInfo = M("bonus")->where("bonus_code = '$bonus_code'")->limit(1)->find();
            $bonus = Bonus::getBonusCodeById($bonusInfo['id']);
            $bonusDetail = Bonus::getBonusDetail($bonus['bonus_code'],1,$this->page);
            if($bonusDetail['listdata']['data']){
                self::error(array(
                    'status'  => $status,
                    'message' => $msg,
                    'bonus_state' => $bonusInfo['status'],
                    'bonus_money'=>$getStatus['bonusMondy'],
                    'avatar'=>model('Avatar')->init($getStatus['uid'])->getUserAvatar()['avatar_middle'],
                    'data_list' =>$bonusDetail['listdata']['data'],
                    'has_get'=>$bonusDetail['bonus_gets']['has_get'],
                    'not_get'=>$bonusDetail['bonus_gets']['not_get'],
                    'bonus_code'=>$bonus['bonus_code'],
                    'totalPages'=>$bonusDetail['listdata']['totalPages'],
                    'totalRows'=>$bonusDetail['listdata']['totalRows'],
                    'nowPage'=>$bonusDetail['listdata']['nowPage'],
                ));
            }else{
                self::error(array(
                    'status'  => $status,
                    'message' => $msg,
                    'bonus_state' => $bonusInfo['status'],
                    'bonus_money'=>$getStatus['bonusMondy'],
                    'avatar'=>model('Avatar')->init($getStatus['uid'])->getUserAvatar()['avatar_middle'],
                    'has_get'=>$bonusDetail['bonus_gets']['has_get'],
                    'not_get'=>$bonusDetail['bonus_gets']['not_get'],
                    'bonus_code'=>$bonus['bonus_code'],
                    'totalPages'=>$bonusDetail['listdata']['totalPages'],
                    'totalRows'=>$bonusDetail['listdata']['totalRows'],
                    'nowPage'=>$bonusDetail['listdata']['nowPage'],
                ));
            }
                
    }

    public function _error($msg){
      
            self::error(array(
                'status'  => 0,
                'message' => $msg,
            ));
    }
    
    public function _success($msg){
            self::success(array(
                'status'  => 1,
                'message' => $msg,
            ));    
    }
    
    /**
     * 获取用户信息 --using.
     *
     * @param int $uid
     *                 用户UID
     *
     * @return array 用户信息
     */
    public function get_user_info($uid)
    {
        $user_info = model('Cache')->get('user_info_api_'.$uid);
        if (!$user_info) {
            $user_info = model('User')->where('uid='.$uid)->field('uid,uname,sex,location,province,city,area,intro')->find();
            // 头像
            $avatar = model('Avatar')->init($uid)->getUserAvatar();
            // $user_info ['avatar'] ['avatar_middle'] = $avatar ["avatar_big"];
            // $user_info ['avatar'] ['avatar_big'] = $avatar ["avatar_big"];
            $user_info['avatar'] = $avatar;
            // 用户组
            $user_group = model('UserGroupLink')->where('uid='.$uid)->field('user_group_id')->findAll();
            foreach ($user_group as $v) {
                $user_group_icon = D('user_group')->where('user_group_id='.$v['user_group_id'])->getField('user_group_icon');
                if ($user_group_icon != -1) {
                    $user_info['user_group'][] = THEME_PUBLIC_URL.'/image/usergroup/'.$user_group_icon;
                }
            }
            model('Cache')->set('user_info_api_'.$uid, $user_info);
        }
        // 积分、经验
        $user_info['user_credit'] = model('Credit')->getUserCredit($uid);
        $user_info['intro'] && $user_info['intro'] = formatEmoji(false, $user_info['intro']);
        // 用户统计
        $user_info['user_data'] = model('UserData')->getUserData($uid);
        // 用户备注
        $user_info['remark'] = model('UserRemark')->getRemark($this->mid, $uid);
    
        return $user_info;
    }

    public function verifyFgtPPwdPhone()
    {
        $paw = M('user')->field('ppasswd')->where("uid={$this->mid}")->find();

        if (!$this->data['phone']){
            return array('status'=>0,'message'=>'手机号不能为空');
        }
        if(model('User')->isPhoneNull($this->mid,$this->data['phone']))
        {
            return array('status'=>0,'message'=>'没有绑定手机号码');
        }
        if (!model('User')->isPhone($this->mid,$this->data['phone']))
        {
            return array('status'=>0,'message'=>'预留手机号不一致');
        }
        // if($paw['ppasswd'] == 'null')
        // {
        //     return array('status'=>5,'message'=>'您还没有设置支付密码');
        // }
        return array('status'=>1, 'message'=>'操作成功');    

    }
    
//     public function setViewInc()
//     {
//         if (! intval($this->data['bonus_id']))
//             return self::error('参数不正确');
//         if (M('bonus')->where(array(
//             "id" => $this->data['bonus_id']
//             ))->setInc('views')) {
//             return self::success();
//         } else {
//             return self::error();
//         }
       
//     }
    //短信验证
    
    /*
     * 错误码 	描述 	说明
        400 	无效请求 	客户端请求不能被识别。
        405 	AppKey为空 	请求的AppKey为空。
        406 	AppKey错误 	请求的AppKey不存在。
        407 	缺少数据 	请求提交的数据缺少必要的数据。
        408 	无效的参数 	无效的请求参数。
        418 	内部接口调用失败 	内部接口调用失败。
        450 	权限不足 	无权执行该操作。
        454 	数据格式错误 	请求传递的数据格式错误，服务器无法转换为JSON格式的数据。
        455 	签名无效 	签名检验。
        456 	手机号码为空 	提交的手机号码或者区号为空。
        457 	手机号码格式错误 	提交的手机号格式不正确（包括手机的区号）。
        458 	手机号码在黑名单中 	手机号码在发送黑名单中。
        459 	无appKey的控制数据 	获取appKey控制发送短信的数据失败。
        460 	无权限发送短信 	没有打开客户端发送短信的开关。
        461 	不支持该地区发送短信 	没有开通给当前地区发送短信的功能。
        462 	每分钟发送次数超限 	每分钟发送短信的数量超过限制。
        463 	手机号码每天发送次数超限 	手机号码在当前APP内每天发送短信的次数超出限制。
        464 	每台手机每天发送次数超限 	每台手机每天发送短信的次数超限。
        465 	号码在App中每天发送短信的次数超限 	手机号码在APP中每天发送短信的数量超限。
        466 	校验的验证码为空 	提交的校验验证码为空。
        467 	校验验证码请求频繁 	5分钟内校验错误超过3次，验证码失效。
        468 	需要校验的验证码错误 	用户提交校验的验证码错误。
        469 	未开启web发送短信 	没有打开通过网页端发送短信的开关。
        470 	账户余额不足 	账户的短信余额不足。
        471 	请求IP错误 	通过服务端发送或验证短信的IP错误
        472 	客户端请求发送短信验证过于频繁 	客户端请求发送短信验证过于频繁
        473 	服务端根据duid获取平台错误 	服务端根据duid获取平台错误
        474 	没有打开服务端验证开关 	没有打开服务端验证开关
        475 	appKey的应用信息不存在 	appKey的应用信息不存在
        476 	当前appkey发送短信的数量超过限额 	如果当前appkey对应的包名没有通过审核，每天次appkey+包名最多可以发送20条短信
        477 	当前手机号发送短信的数量超过限额 	当前手机号码在SMSSDK平台内每天最多可发送短信10条，包括客户端发送和WebApi发送
        478 	当前手机号在当前应用内发送超过限额 	当前手机号码在当前应用下12小时内最多可发送文本验证码5条
        479 	SDK使用的公共库版本错误 	当前SDK使用的公共库版本为非IDFA版本，需要更换为IDFA版本
        480 	SDK没有提交AES-KEY 	客户端在获取令牌的接口中没有传递aesKey
        500 	服务器内部错误 	服务端程序报错。
        说明：469 ，471，473，474这几个错误码是http-api端才会发生的错误
        本地错误码
        252 	发送短信条数超过限制 	发送短信条数超过规则限制
        253 	无权限进行此操作 	无权限进行此操作
        254 	无权限发送验证码 	无权限发送验证码
        255 	无权限发送国内验证码 	无权限发送国内验证码
        256 	无权限发送港澳台验证码 	无权限发送港澳台验证码
        257 	无权限发送国外验证码 	无权限发送国外验证码
        258 	操作过于频繁 	操作过于频繁
        259 	未知错误 	未知错误
        260 	未知错误 	未知错误
     */
    //检验手机号
    public function verifyPhone()
    {
        if (!$this->data['phone']){
            // return self::error('手机号不能为空');
            return array('status'=>0,'message'=>'手机号不能为空');
        }
        if ($this->data['type'] == 'register')
        {
            if(!model('User')->isChangePhone($this->data['phone'])){
                // return self::error('此号码已被注册');
                return array('status'=>0,'message'=>'此号码已被注册');
            }
            
        }elseif ($this->data['type'] == 'forgot_password') 
        {
            if(model('User')->isPhoneNull($this->mid,$this->data['phone'])){
                // return self::error('用户不存在或者没有绑定手机号码');
                return array('status'=>0,'message'=>'用户不存在或者没有绑定手机号码');
            }
        }
        return array('status'=>1, 'message'=>'操作成功');
    }   
    //短信验证 
    public function verifyCode(){
        
        if (!$this->data['phone']){
            // return self::error('手机号不能为空');
            return array('status'=>0,'message'=>'手机号不能为空');
        }
        if (!$this->data['code']){
            // return self::error('验证码不能为空');
            return array('status'=>0,'message'=>'验证码不能为空');
        }
        if ($this->data['type'] == 'register')
        {
            if(!model('User')->isChangePhone($this->data['phone'])){
                // return self::error('此号码已被注册');
                return array('status'=>0,'message'=>'此号码已被注册');
            }
            
        }elseif ($this->data['type'] == 'forgot_password') 
        {
            if(model('User')->isPhoneNull($this->mid,$this->data['phone'])){
                // return self::error('用户不存在或者没有绑定手机号码');
                return array('status'=>0,'message'=>'用户不存在或者没有绑定手机号码');
            }
        }
        $api = 'https://webapi.sms.mob.com';
        $appkey = '1ae235383c094';
        $response = self::postRequest( $api . '/sms/verify', array(
    	'appkey' => $appkey,
        'phone' => trim($this->data['phone']),
        'zone' => '86',
    	'code' => trim($this->data['code']),
       ));
        
        $retArr = (json_decode($response,true));
        
        $errorMsg = array(
            '400'=>'无效请求,客户端请求不能被识别。',
            '405'=>'AppKey为空,请求的AppKey为空。',
            '406'=>'AppKey错误,请求的AppKey不存在。',
            '407'=>'缺少数据,请求提交的数据缺少必要的数据。',
            '408'=>'无效的参数 	无效的请求参数。',
            '418'=>'内部接口调用失败,内部接口调用失败。',
            '450'=>'权限不足,无权执行该操作。',
            '454'=>'数据格式错误,请求传递的数据格式错误，服务器无法转换为JSON格式的数据。',
            '455'=>'签名无效,签名检验。',
            '456'=>'手机号码为空 ,提交的手机号码或者区号为空。',
            '457'=>'手机号码格式错误,提交的手机号格式不正确（包括手机的区号）。',
            '458'=>'手机号码在黑名单中,手机号码在发送黑名单中。',
            '459'=>'无appKey的控制数据,获取appKey控制发送短信的数据失败。',
            '460'=>'无权限发送短信,没有打开客户端发送短信的开关。',
            '461'=>'不支持该地区发送短信,没有开通给当前地区发送短信的功能。',
            '462'=>'每分钟发送次数超限,每分钟发送短信的数量超过限制。',
            '463'=>'手机号码每天发送次数超限,手机号码在当前APP内每天发送短信的次数超出限制。',
            '464'=>'每台手机每天发送次数超限 ,每台手机每天发送短信的次数超限。',
            '465'=>'号码在App中每天发送短信的次数超限,手机号码在APP中每天发送短信的数量超限。',
            '466'=>'校验的验证码为空,提交的校验验证码为空。',
            '467'=>'校验验证码请求频繁,5分钟内校验错误超过3次，验证码失效。',
            '468'=>'需要校验的验证码错误,用户提交校验的验证码错误。',
            '469'=>'未开启web发送短信,没有打开通过网页端发送短信的开关。',
            '470'=>'账户余额不足,账户的短信余额不足。',
            '471'=>'请求IP错误,通过服务端发送或验证短信的IP错误',
            '472'=>'客户端请求发送短信验证过于频繁,客户端请求发送短信验证过于频繁',
            '473'=>'服务端根据duid获取平台错误,服务端根据duid获取平台错误',
            '474'=>'没有打开服务端验证开关,没有打开服务端验证开关',
            '475'=>'appKey的应用信息不存在 ,appKey的应用信息不存在',
            '476'=>'当前appkey发送短信的数量超过限额 ,如果当前appkey对应的包名没有通过审核，每天次appkey+包名最多可以发送20条短信',
            '477'=>'当前手机号发送短信的数量超过限额 ,当前手机号码在SMSSDK平台内每天最多可发送短信10条，包括客户端发送和WebApi发送',
            '478'=>'当前手机号在当前应用内发送超过限额 ,当前手机号码在当前应用下12小时内最多可发送文本验证码5条',
            '479'=>'SDK使用的公共库版本错误,当前SDK使用的公共库版本为非IDFA版本，需要更换为IDFA版本',
            '480'=>'SDK没有提交AES-KEY,客户端在获取令牌的接口中没有传递aesKey',
            '500'=>'服务器内部错误,服务端程序报错。',
            '252'=>'发送短信条数超过限制,发送短信条数超过规则限制',
            '253'=>'无权限进行此操作,无权限进行此操作',
            '254'=>'无权限发送验证码 ,无权限发送验证码',
            '255'=>'无权限发送国内验证码,无权限发送国内验证码',
            '256'=>'无权限发送港澳台验证码 ,无权限发送港澳台验证码',
            '257'=>'无权限发送国外验证码,无权限发送国外验证码',
            '258'=>'操作过于频繁,操作过于频繁',
            '259'=>'未知错误',
            '260'=>'未知错误',
            '200'=>'验证码正确',
            
        );
        

        
        
        if (is_array($retArr)){
            
            if ($retArr['status'] == '200')
                {
                        M('sms')->add(array(
                            'phone'   => $this->data['phone'],
                            'code'    => $this->data['code'],
                            'message'    => '',
                            'time'    => time(),
                        ));
                        $retArr['status'] = 1;
        
                }
            $retArr['msg']=$errorMsg[$retArr['status']];
            return $retArr;
        }else{
            return $this->error();
        }
        
        
    }

        /**
         * 发起一个post请求到指定接口
         *
         * @param string $api 请求的接口
         * @param array $params post参数
         * @param int $timeout 超时时间
         * @return string 请求结果
         */
    private function postRequest( $api, array $params = array(), $timeout = 30 ) {
    	$ch = curl_init();
    	curl_setopt( $ch, CURLOPT_URL, $api );
    	// 以返回的形式接收信息
    	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    	// 设置为POST方式
    	curl_setopt( $ch, CURLOPT_POST, 1 );
    	curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params ) );
    	// 不验证https证书
    	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
    	curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
    	curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
    	curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
    		'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
    		'Accept: application/json',
    	) ); 
    	// 发送数据
    	$response = curl_exec( $ch );
    	// 不要忘记释放资源
    	curl_close( $ch );
    	return $response;
    }
    
    /**
     * 关注一个用户 --using.
     *
     * @param
     *        	integer user_id 要关注的用户ID
     *
     * @return array 状态+提示+关注状态
     */
    public function follow()
    {
        
        if (empty($this->mid) || empty($this->user_id)) {
          
            return array(
                'status' => 0,
                'msg'    => '参数错误',
            );
        }
        
        
        $uids = explode(',', $this->user_id);
        foreach ($uids as $key => $value) {
            $r = model('Follow')->doFollow($this->mid, $value);
        }
    
        if ($r) {
            $r['status'] = 1;
            $r['msg'] = '关注成功';
            //as
            $data['mid'] = $this->mid;
            $data['user_id']=$this->user_id;
            $data['ltime'] = time();
            $data['message'] = t($this->data['message']);
            if (M('message_add_friends')->add($data))
            {
                //申请加好友的消息
                
            }
            
    
            return $r;
        } else {
            return array(
                'status' => 0,
                'msg'    => model('Follow')->getLastError(),
            );
        }
    }

    
    public function configMyppwd(){
        
        if (empty($this->mid)) {
            return array(
                'status' => 0,
                'msg'    => '参数错误',
            );
        }
        $paw = M('user')->field('ppasswd')->where("uid={$this->mid}")->find();
        if(!$this->data['type'] == 'forgot_ppwd')
        {
            if($paw['ppasswd'] != '')
            {
                if (!t($this->data['used_password']))
                {
                    $this->error('旧密码不能为空');
                }
                if(t($this->data['used_password']) != $paw['ppasswd'])
                {
                    $this->error('旧密码错误');
                }
            }
        }
            
        if (!t($this->data['password']))
        {
            $this->error('密码不能为空');
        }
        if (!t($this->data['confirm_password']))
        {
            $this->error('确认密码不能为空');
        }
        if(t($this->data['password']) != t($this->data['confirm_password']))
        {
            $this->error('密码与确认密码不一致');
        }
        
        $status = M()->execute("UPDATE ".C('DB_PREFIX')."user set ppasswd = '{$this->data['password']}' where uid ='".$this->mid."'");
        
        if ( $status)
        {
            //echo M()->getLastSql();
            $this->success('设置成功');
        }else{
            //echo M()->getLastSql();
            $this->error("你输入的密码和原来一致，请更换新的密码！");
        }
    }

    
    // public function verifyppwd($isReturn=1)
    // {
    //     if (!$this->data['ppwd'])
    //     {
    //         $this->error("支付密码不能为空");
    //     }else{
    //         $uPpwd = M("user")->field("ppasswd")->where(array("uid"=>$this->mid))->find();
    //         if(!$uPpwd['ppasswd']){
    //             $this->error("未设置支付密码");
    //         }

    //         if ($uPpwd['ppasswd'] == (t($this->data['ppwd'])))
    //         {
    //              if ($isReturn){
    //                  $this->success("支付密码正确");
    //              }
                 
    //         }else{
    //              $this->error("支付密码错误");
    //         }
    //     }
        
    // }
    public function verifyppwd()
    {
        $paw = $this->data['ppwd'];
        if(!$paw){
            self::error(array(
                'status'  => 0,
                'message' => '密码不能为空',
            ));
        }
        $u_id = $this->mid;
        $user_paw = M('user')->field('ppasswd')->where("uid = $u_id")->find();
        $u_paw = $user_paw['ppasswd'];
        if(!$u_paw){
            self::error(array(
                'status'  => 5,
                'message' => '未设置支付密码',
            ));
        }
        if($paw == $u_paw){
            return true;
        }else{
            self::error(array(
                'status'  => 4,
                'message' => '密码不正确',
            ));
        }
    }

    
    
    /**
     * 创建活动.
     *
     * @return array
     *
     * @author Seven Du <lovevipdsw@vip.qq.com>
     **/
    public function createBonusEvent()
    {
        $addMiniType=isset($this->data['addMiniType'])?1:0;
        if (!intval($this->data['cid']))
        {
            self::error(array(
                'status'  => 0,
                'message' => '请选择活动类型',
            ));
        
        }
        
        
        if (!intval($this->data['bonusmany']))
        {
            self::error(array(
                'status'  => 0,
                'message' => '请填写红包个数',
            ));
        
        }
        elseif (!intval($this->data['bonusmoney']))
        {
            self::error(array(
                'status'  => 0,
                'message' => '请填写红包金额',
            ));
        }elseif (intval($this->data['bonusmoney'])<0 || !is_int(intval($this->data['bonusmoney'])))
        {
            self::error(array(
                'status'  => 0,
                'message' => '金额必需大于０且不能带小数',
            ));
        }elseif(!$this->data['expire_time'] && $this->data['money_type'] == 2)
        {
            self::error(array(
                'status'  => 0,
                'message' => '请填写过期时间',
            ));
        }
        
        
        list($title, $stime, $etime, $area, $city, $address, $place, $image, $mainNumber, $price, $cid, $tips, $cate, $audit, $content) = Common::getInput(array('title', 'stime', 'etime', 'area', 'city', 'address', 'place', 'image', 'mainNumber', 'price', 'cid', 'tips', 'cate', 'audit', 'content'));
        $audit != 1 and
        $audit = 0;
        /* 有大写参数，APP可能穿错，避免错误，还是多写一下 */
        $mainNumber or $mainNumber = Common::getInput('mainnumber');
        if (($id = Bonusevent::getInstance()->setName($title) //活动标题
            ->setStime($stime) // 开始时间
            ->setEtime($etime) // 结束时间
            ->setArea($area) // 地区
            ->setCity($city) // 城市
            ->setLocation($address) // 详细地址
            ->setPlace($place)  // 场所
            ->setImage($image) // 封面图片
            ->setManNumber($mainNumber)  // 活动人数
            ->setPrice($price)  // 价格
            ->setCid($cid) // 分类
            ->setAudit($audit)  // 是否需要权限审核
            ->setContent($content)  // 活动详情
            ->setUid($this->mid) // 发布活动的用户
            ->setTips($tips) // 费用说明
            ->add($addMiniType))) {
                /*self::success(array(
                    'status'  => 1,
                    'message' => '发布成功',
                    'data'    => $id,
                ));*/
            }
            
            if ($id)
            {
                self::sendCaiyuanCoin($id);
            }
            
            
            self::error(array(
                'status'  => 0,
                'message' => Bonusevent::getInstance()->getError(),
            ));
    }
    //活动红包列表
    public function getBonusEventList()
    {
        
        $data['range_type'] = Bonus::getRangeTypeArr();
       
        // for ($i=0;$i<count($data['range_type']);$i++)
        // {
        //     $data['data_list'][$i] = Bonus::getBonusEventList($data['range_type'][$i]['tid']);
        //     $data['data_list'][$i]['rangeType'] =$data['range_type'][$i]['tid'];
        // }
        $data['status'] = 1;
        $data['message'] = '操作成功';
        return $data;
         
    }
    
    //活动红包列表
    public function getBonusEventData()
    {
       if (!intval($this->data['tid'])) $this->error("tid参数不能为空！");
       $data= Bonus::getBonusEventList($this->data['tid']);
       if ($data['data']){
           $data['msg'] = '操作成功';
           $data['status'] = '1';
       }else{
           $data['msg'] = '没有数据';
           $data['status'] = '0';
       }
       $data['rangeType'] =$this->data['tid'];

       return $data;
         
    }
    //发现
    public function getDiscoverList()
    {
        $data['range_type'] = Bonus::getEventType();
        // foreach ($data['range_type'] as $k => $v)
        // {
        //     $data['data_list'][] = Bonus::getEventData($data['range_type'][$k]['cid']);

        // }
        $data['status'] = 1;
        $data['message'] = '操作成功';
        return $data;
    }
    
    public function getDiscoverData()
    {
        if (!intval($this->data['cid'])) $this->error("cid参数不能为空！");
        $data =  Bonus::getEventData($this->data['cid']);

        if ($data['data']){
            $data['msg'] = '操作成功';
            $data['status'] = '1';
        }else{
            $data['msg'] = '没有数据';
            $data['status'] = '0';
        }
        
        $data['cid'] =  $this->data['cid'];

        return $data;
    }
    
    
    public function getMyassets()
    {
        $userInfo = D('User')->getUserInfo($this->mid);
        $userInfo_['caiyuanbi'] = $userInfo['credit_info']['credit']['caiyuanbi'];
        $userInfo_['ubank'] = $userInfo['credit_info']['credit']['ubank'];
        $userInfo_['totalAmt'] = floatval($userInfo['credit_info']['credit']['caiyuanbi']['value'])
                               + floatval($userInfo['credit_info']['credit']['ubank']['value']);
        $caiyuanbiTodayIncome =  Bonus::getCaiyuanbiTodayIncome($this->mid);
        $userInfo_['todayIncome'] = $caiyuanbiTodayIncome;
        
        return array(
                'status' => 1,
                'msg'    => '操作成功',
                'data'=>$userInfo_
            );
    }

    public function appIndexBonusH5(){
        $bonusConfig = model('Xdata')->get('bonus_Admin:redpacketConfig');
        return Bonus::getAppIndexBonus($bonusConfig['app_index_bonus'],$this->mid);
    }
    
    public function globalData()
    {
        $now_time = time() - 24*3600 + 60;
        // $data['range_type_presonal'] = Bonus::getRangeTypeArr();
        // $data['range_type_company'] = Bonus::getRangeTypeArr();
        $data['bonus_global_data']['event_type'] = Bonus::getEventType();
        $data['bonus_global_data']['user_groups'] = Bonus::getBonusUserGroups();
        $data['boot_ad'] = Bonus::bootAd(4);
        $bonusConfig = model('Xdata')->get('bonus_Admin:redpacketConfig');
        $data['appBonus'] = Bonus::getAppIndexBonus($bonusConfig['app_index_bonus'],$this->mid);
        // $userGroup = model('UserGroupLink')->getUserGroupData($this->mid);
        // foreach ($userGroup[$this->mid] as $g) {
        //     $g['is_authenticate'] == 1 && $groupArr[] = $g['user_group_name'];
        //     switch ($g['user_group_id']) {
        //         case '5':
        //             $groupArrType[] = 1;
        //             break;
        //         case '6':
        //             $groupArrType[] = 2;
        //             break;    
        //     }
            
        // }
        // $data['authenticate'] = empty($groupArr) ? '无' : implode(' , ', $groupArr);
        // $data['authenticate_type'] = empty($groupArrType) ? 0 : intval(implode(' , ', $groupArrType));
        $data['status'] = 1;
        $data['message'] = '操作成功';
        return $data;
    }
    
    public function getLianwoUsersByLastSendBonus(){
         $count = M()->query("Select count(distinct(bonus_fromuid)) as count from ".C('DB_PREFIX')."bonus  GROUP BY bonus_fromuid ORDER BY send_time desc")[0]['count'];
         //$sql ="select distinct(`bonus_fromuid`) from ".C('DB_PREFIX')."bonus ORDER BY send_time desc";
         $sql ="Select bonus_fromuid,eid from ".C('DB_PREFIX')."bonus  GROUP BY bonus_fromuid ORDER BY send_time desc";
         $list = M()->findPageBySql($sql,$count,10);
         unset($list['html']);

         foreach ($list['data'] as $k => $v)
         {
             $user_info = D("User")->getUserInfo($list['data'][$k]['bonus_fromuid']);
             $list['data'][$k]['uid']=$list['data'][$k]['bonus_fromuid'];
             $list['data'][$k]['user_name']=$user_info['uname'];
             $list['data'][$k]['avatar']=$user_info['avatar_middle'];
             $list['data'][$k]['title']=Bonus::getTitleByEid($list['data'][$k]['eid']);
             $add['uid'] = $this->mid;
             $add['fid'] = $list['data'][$k]['uid'];
             $cz = M('user_follow')->where($add)->find();
             $cz1 = M('user_follow')->where("uid={$add['fid']} and fid={$add['uid']}")->find();
             if(!empty($cz1) && !empty($cz)) {
             	$list['data'][$k]['is_friend'] = true;
             }else {
             	$list['data'][$k]['is_friend'] = false;
             }
             unset($list['data'][$k]['bonus_fromuid']);
         }
         
         if ($list['data'])
         {
             $list['msg']="请求数据成功";
             $list['status'] = 1;
         }else{
             $list['msg']="请求成功但没有数据";
             $list['status'] = 0;
         }
         
         return $list;
    }

     /*
     * 充值，创建一个订单
    */
    public function createCharge(array $data = array())
    {
        if ($GLOBALS['ts']['mid']) {
            $add['uid'] = $GLOBALS['ts']['mid'];
        }
        $data = count($data) ? $data : $_POST;
        $order = $data['bonus_code'];
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
        $add['serial_number'] = $order;
        $price = $data['total_amount'];
        $add['charge_value'] = floatval($price);
        $add['ctime'] = time();
        $add['status'] = 0;
        $add['sta'] = 2;
        $add['charge_ubank'] = $price;
        $add['charge_order'] = '';
        $add['charge_type'] = $type;
        $data['status'] = -1;
        $result = D('credit_charge')->add($add);
        $bonus = M('bonus')->add($data);
        if(!$result && !$bonus){
            self::error(array(
                'status'  => 0,
                'message' => '支付创建失败',
            ));
        }
        $data = D('credit_charge')->where("serial_number='{$order}'")->find();
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
                
                /* $url['url'] = createAlipayUrl($configs, $parameter, 2); //直接返回支付宝支付url
                echo $url['url'];exit; */

                $aliPayObj = $this->getAliPayObj();
                //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
                $request = new AlipayTradeAppPayRequest();
                //SDK已经封装掉了公共参数，这里只需要传入业务参数
                $bizcontent = "{\"body\":\"零用钱支付\","
                		. "\"subject\": \"零用钱支付:{$data['charge_ubank']}\","
                		. "\"out_trade_no\": \"{$data['serial_number']}\","
                		. "\"timeout_express\": \"30m\","
                		. "\"total_amount\": \"0.01\","
                		. "\"product_code\":\"QUICK_MSECURITY_PAY\""
                		. "}";
                $request->setNotifyUrl($parameter['notify_url']);
                $request->setBizContent($bizcontent);
                //这里和普通的接口调用不同，使用的是sdkExecute
                $response = $aliPayObj->sdkExecute($request);
                //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
                
                $url['url'] = 'alipay://alipayclient/?'.urlencode(json_encode(array('requestType' => 'SafePay', 'fromAppUrlScheme' => 'com.boonchat', 'dataString' => $response))); //直接返回支付宝支付url
                $url['charge_type'] = $data['charge_type'];
                $url['charge_value'] = $data['charge_value'];
                $url['out_trade_no'] = $data['serial_number'];
                
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
                    'notify_url'       => SITE_URL.'/weixin_ubank_pay_notify_api.php',
                    'out_trade_no'     => $data['serial_number'],
                    'spbill_create_ip' => $ip,
                	'total_fee'        => $data['charge_ubank']*100, //这里的最小单位是分，跟支付宝不一样。1就是1分钱。只能是整形。
                    'trade_type'       => 'APP',
                    ); //预支付订单
                $weixinpay = new WeChatPay();

                $input = $weixinpay->getPayParam($order, $chargeConfigs['weixin_pid'], $chargeConfigs['weixin_mid'], $chargeConfigs['weixin_key'], 1);

                /* $input['out_trade_no'] = $data['serial_number'];
                $input['charge_type'] = $data['charge_type'];
                $input['charge_value'] = $data['charge_value'];
                $input['packagevalue'] = 'Sign=WXPay'; */
                
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
    
    /**
     * 初始化支付对象-支付宝
     * @return Alipay
     */
    private function getAliPayObj(){
    	$aop = new AopClient;
    	$aop->gatewayUrl = ALI_PAY_GATE_WAY_URL;
    	$aop->appId = ALI_PAY_APP_ID;
    	$aop->rsaPrivateKey = ALI_PAY_MERCHANT_PRIVATE_KEY;
    	$aop->format = "json";
    	$aop->charset = ALI_PAY_CHARSET;
    	$aop->signType = ALI_PAY_SIGN_TYPE;
    	$aop->alipayrsaPublicKey = ALI_PAY_ALIPAY_PUBLIC_KEY;
    	
    	return $aop;
    }
    
    public function cybUseInfo()
    {
        $info = M('user_cyb_use_info')->where("uid={$this->mid}")->find();

        if($info){
            if($info['address_pic'] != '')
            {
                $attachInfo = model('Attach')->getAttachById($info['address_pic']);
                $info['address_img']['img'] = getImageUrl($attachInfo['save_path'].$attachInfo['save_name']);
                $info['address_img']['attach_id'] = $info['address_pic'];
            }
            if($info['img'] != '')
            {
                $img = explode(',',$info['img']);
                foreach ($img as $key => $value) {
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
                    
                    $info['photo'][$key]['attach_id'] = $value;
                }
            }
            $info['latitude'] = floatval($info['latitude']);
            $info['longitude'] = floatval($info['longitude']);
            unset($info['id']);
            unset($info['img']);
            $feedbackList = M('user_cyb_use_feedback')->field('ts_user_cyb_use_feedback.uid,content,ctime')->join('ts_user_cyb_use_info ON ts_user_cyb_use_feedback.cid=ts_user_cyb_use_info.id')->where("ts_user_cyb_use_info.uid={$this->mid}")->findPage();
            if($feedbackList)
            {
                foreach ($feedbackList['data'] as $key => $value) {
                    $info['feedback_info']['data'][$key]['content'] = $value['content'];
                    $info['feedback_info']['data'][$key]['uname'] = getUserName($value['uid']);
                    $info['feedback_info']['data'][$key]['avatar'] = model('Avatar')->init($value['uid'])->getUserAvatar()['avatar_middle'];
                    $info['feedback_info']['data'][$key]['ctime'] = date('Y-m-d H:i:s',$value['ctime']);
                }
            }  
            $info['feedback_info']['totalPages'] = $feedbackList['totalPages'];
            $info['feedback_info']['totalRows'] = $feedbackList['totalRows'];
            $info['feedback_info']['nowPage'] = $feedbackList['nowPage'];
            return array('status'=>1,'message'=>'操作成功','data'=>$info);
        }else{
            return array('status'=>0,'message'=>'暂无设置');
        }
        
    }


    public function doCybUseInfo()
    {
        $data['uid'] = $this->mid;
        $data['address'] = trim(t($this->data['address']));
        if(!$data['address']){
            return array('status'=>0,'message'=>'位置不能为空');
        }
        $data['is_display_wifi'] = intval($this->data['is_display_wifi']);
        $data['phone'] = $this->data['phone'];
        if(!$data['phone']){
            return array('status'=>0,'message'=>'联系电话不能为空');
        }
        $data['explain'] = t($this->data['explain']); //说明
        $data['about'] = t($this->data['about']); //关于我们
        $data['img'] = implode(',',$this->data['attach_id']);
        $data['wifi_name'] = trim(t($this->data['wifi_name']));
        $data['wifi_paw'] = trim(t($this->data['wifi_paw']));
        $data['operation_time'] = trim(t($this->data['operation_time']));
        $data['latitude'] = trim(t($this->data['latitude']));
        $data['longitude'] = trim(t($this->data['longitude']));
        if(!$data['latitude'] && !$data['longitude']){
            return array('status'=>0,'message'=>'非法操作');
        }
        if(!$data['operation_time']){
            return array('status'=>0,'message'=>'运营时间不能为空');
        }
        $data['address_pic'] = $this->data['address_img'];
        $info = M('user_cyb_use_info')->where("uid={$this->mid}")->find();
        if($info){
            $resSave = M('user_cyb_use_info')->where("uid={$this->mid}")->save($data);
            if($resSave){
                return array('status'=>1,'message'=>'修改成功');
            }else{
                return array('status'=>0,'message'=>'请修改');
            }
        }
        $res = M('user_cyb_use_info')->add($data);
        if($res){
            return array('status'=>1,'message'=>'添加成功');
        }else{
            return array('status'=>0,'message'=>'添加失败');
        }

    }

    public function cybUseInfoUploadImg(){
        $d['attach_type'] = 'business_image';
        
        $d['upload_type'] = 'image';
        $GLOBALS['fromMobile'] = true;
        $info = model('Attach')->upload($d, $d);
        if ($info['status']){
            $data['attach_id'] = implode(',',getSubByKey($info['info'], 'attach_id'));

            $data['status'] = 1;
            $data['message']="上传成功";
        }else{
            $data['status'] = 0;
            $data['message']="上传失败，请重新上传"  ;
        }

        return $data;
    }

    public function delAttach()
    {
        $attachInfo = model('Attach')->getAttachById($this->data['attach_id']);
        $filename = 'data/upload/'.$attachInfo['save_path'].$attachInfo['save_name'];

        $cloud = unlink($filename);
        if($cloud){
            return model('Attach')->doEditAttach($this->data['attach_id'],'deleteAttach');
        }else{
            return array('status'=>0,'message'=>'操作失败');
        }
        
    }
    
    /**
     * 改变红包是否关注状态
     */
    public function changeFollow() {
    	$bonus_id = intval($this->data['bonus_id']);
    	if (empty($bonus_id)){
    		$this->error("bonus_id　error");
    	}
    	$is_follow = intval($this->data['is_follow']);
    	$bonusMob = M('bonus');
    	$bonus = $bonusMob->where(array('id' => intval($this->data['bonus_id']), 'bonus_fromuid' => $this->mid))->find();
    	if(empty($bonus)) {
    		self::error(array(
    				'status'  => 0,
    				'message' => '找不到该红包',
    		));
    	}
    	
    	$status = $bonusMob->execute("UPDATE ".C('DB_PREFIX')."bonus set is_follow = '{$is_follow}' where id ='".$bonus_id."' limit 1");
    	if($status) {
    		self::success(array(
    				'status'  => 1,
    				'message' => '变更成功！',
    		));
    	}else {
    		self::error(array(
    				'status'  => 0,
    				'message' => '变更失败',
    		));
    	}
    }
}