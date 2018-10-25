 <?php
/**
 * 后台，红包管理控制器
 * @author singsin <singsin.com@qq.com>
 * @version TS3.0
 */

class BonusAction extends Action 
{
	private $redis ;
    public function __construct()
    {
    	if(!$_SESSION['mid'])
    	{
    		$this->assign('jumpUrl',U());
    		$this->error('请先登录');
    	}	
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

//红包信息
	public function index()
	{
		
		$setting = model('Xdata')->get('bonus_Admin:redpacketConfig');
		$this->assign('setting',$setting);
		$bonusCount = $this->sendBonusCount();
		$this->assign('bonusCount',$bonusCount);
		$getBonus = $this->getBonusCount();
		$this->assign('getBonus',$getBonus);
		$user['uName'] = getUserName($_SESSION['mid']);
		$user['avatar'] = model('Avatar')->init($_SESSION['mid'])->getUserAvatar()['avatar_middle'];
		$this->assign('user',$user);
		$this->display();
		
		
	}

	public function doSendRedpacket()
	{
		$send_staus = false;
		$value['redpacket_many'] = abs(intval($_POST['redpacket_many']));
		$value['redpacket_pre_money'] = abs(floatval($_POST['redpacket_pre_money']));
		$value['editredpacketType'] = intval($_POST['editredpacketType']);
		$value['redpacket_msg'] = strval($_POST['redpacket_msg']);
		$value['money_type'] = intval($_POST['money_type']);

		if (!$value['money_type'])
		{
				$this->error('请选择红包类型');						
		}

		if (!$value['redpacket_many'])
		{
				$this->error('请填写红包个数');						
		}

		if (!$value['redpacket_pre_money'])
		{
				if ($value['editredpacketType'] == '1'){
					$this->error('请填写红包单个金额！');				
				}else{
					$this->error('请填写红包总金额！');				
				}
						
		}

		$bnnus_config = model('Xdata')->get('bonus_Admin:redpacketConfig');

		if ($value['redpacket_pre_money'] > $bnnus_config['redpacket_max_money'])
		{
			$this->error('每次红包最大额度不能超过'. $bnnus_config['redpacket_max_money']);			
		}
		$data = array();
		$code = M('bonus')->field('bonus_code')->where("bonus_fromuid={$_SESSION['mid']} and is_qrcode=1")->find();
		if($code)
		{
			$data['bonus_code'] = $code['bonus_code'];
		}else{
			$data['bonus_code'] = md5(rand(10000,99999).$data['send_time'].'qrcode'.rand(1000,9999).$data['send_time'].$data['bonus_fromuid'].$data['bonus_type']);
		}

		$data['bonus_fromuid'] = $_SESSION['mid'];
		$data['send_time'] = time();		
		$data['bonus_type'] = $value['editredpacketType'];
		$data['bonus_msg'] = $value['redpacket_msg'];
		$data['bonus_many'] =$value['redpacket_many'];
		$data['money_type'] =$value['money_type'];
		$data['status'] =1;			
		$data['is_qrcode'] =1;			

		 if ($value['editredpacketType'] == '1' || $value['editredpacketType'] == '3')
		 {
			$data['total_amount'] =  floatval($value['redpacket_many']) * ($value['redpacket_pre_money']);
		 }else{
		 	$data['total_amount'] =  ($value['redpacket_pre_money']);
		 }
	    $res = M('bonus')->add($data);
	    unset($data['bonus_msg']);
	    unset($data['bonus_many']);
		unset($data['status']);
	    $data['to_uid']='0';
	    $data['get_time']='0';	
	    if ($value['editredpacketType'] == '1' || $value['editredpacketType'] == '3')
	    {

	    	
	    	$data['bonus_money'] = $value['redpacket_pre_money'];

	    	if ($value['redpacket_many']>=1)
	    	{
	    		for($i=0;$i<intval($value['redpacket_many']);$i++)
	    		{
	    			$res_ = D('bonus_list')->add($data);
	    		}
	    		$send_staus = true;
	    	}

	    }
	    if ($value['editredpacketType'] == '2')
	    {
	    	$data['total_amount'] =  $value['redpacket_pre_money'];
	    	$data['status'] = 0;

	    	
	    	$bonus_array = $this->sendRandBonus($value['redpacket_pre_money'],$value['redpacket_many'],1);

	    	
	    	if ($value['redpacket_many']>=1)
	    	{
	    		for($i=0;$i<intval($value['redpacket_many']);$i++)
	    		{
	    			$data['bonus_money'] = floatval($bonus_array[$i]);

	    			$res_ = D('bonus_list')->add($data);
	    		}
	    		$send_staus = true;
	    	}
	    }
		
		//make bonus
		$make_status =  $this->makeRedisBonus($data['bonus_code']);
	    if ($send_staus && $make_status)
	    {
	    	$this->success('红包发送成功!');
	    }else{
	    	$this->error('红包发送失败');
	    }
	
		if ($res)
		{
			$this->assign('jumpUrl', U('public/Bouns/index'));
			$this->success('红包发出去了哦!');
				
		}else{
			$this->error('红包发出失败');
		}
		
		
	}

	public function makeRedisBonus($bonus_code)
	{
		self::connectRedis();
		$bonus_code = isset($bonus_code)?t($bonus_code):$this->returnMsg('10001');;
		//红包详细
		if (!$this->redis->keys("BINFO:$bonus_code"))
		{
			//echo "红包{$bonus_code}信息已生成";
		//}else{
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
			$bonus_list = M('bonus_list')->field('id,bonus_money')->where(array("bonus_code"=>$bonus_code,'to_uid'=>'0'))->order('bonus_money DESC')->select();
			$bonus_list_count = count($bonus_list);
			for ($i=0;$i<$bonus_list_count;$i++)
			{	
				$this->redis->SADD("BLISTO:".$bonus_code,$bonus_list[$i]['id'].'#'.$bonus_list[$i]['bonus_money']);
				$this->redis->SADD("BLISTN:".$bonus_code,$bonus_list[$i]['id'].'#'.$bonus_list[$i]['bonus_money']);
			}
			//echo "红包{$bonus_code}列表信息已生成";
		}
		
		return 1;
	
	}

	//随机红包生成
    public function sendRandBonus($total=0, $count=3, $type=2)
    {
        if($total / $count == 0.01){
            $type = 2;
        }
        if($type==1){
            $input     = range(0.01, $total, 0.01);
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
            $items[]    = floatval(number_format($total-array_sum($items),2));
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

    //红包发出统计
    public function sendBonusCount()
    {
    	$count = M('bonus')->field('bonus_many,money_type,total_amount,bonus_code')->where("bonus_fromuid={$_SESSION['mid']} and is_qrcode=1")->select();

    	foreach ($count as $key => $value) {
    		switch ($value['money_type']) {
    			case '1':
    				$ubank_bonus_many[$key] = $value['bonus_many'];
    				$ubank_total_amount[$key] = $value['total_amount'];
    				break;
    			case '2':
    				$cyb_bonus_many[$key] = $value['bonus_many'];
    				$cyb_total_amount[$key] = $value['total_amount'];
    				break;
    		}
    	}
    	$data['ubank']['bonus_many'] = array_sum($ubank_bonus_many)?array_sum($ubank_bonus_many):0;
    	$data['ubank']['total_amount'] = array_sum($ubank_total_amount)?array_sum($ubank_total_amount):0;
    	$data['cyb']['bonus_many'] = array_sum($cyb_bonus_many)?array_sum($cyb_bonus_many):0;
    	$data['cyb']['total_amount'] = array_sum($cyb_total_amount)?array_sum($cyb_total_amount):0;
    	return $data;
    }

    public function getBonusCount($bonus_code,$hasGet = 1){
    	$code = M('bonus')->field('bonus_code')->where("bonus_fromuid={$_SESSION['mid']} and is_qrcode=1")->find();
        $getBonus = M('bonus_list')->field('money_type,bonus_money')->where("bonus_code={$code['bonus_code']} and bonus_fromuid={$_SESSION['mid']} and to_uid != 0")->select();
        foreach ($getBonus as $key => $value) {
        	switch ($value['money_type']) {
    			case '1':
    				$ubank_bonus_money[$key] = $value['bonus_money'];
    				break;
    			case '2':
    				$cyb_bonus_money[$key] = $value['bonus_money'];
    				break;
    		}
        }
        $data['code'] = $code['bonus_code'];
        $data['ubank']['bonus_money'] = array_sum($ubank_bonus_money)?array_sum($ubank_bonus_money):0;
        $data['ubank']['has_get'] = $this->countGetBonus($code['bonus_code'],1);
		$data['cyb']['bonus_money'] = array_sum($cyb_bonus_money)?array_sum($cyb_bonus_money):0;
		$data['cyb']['has_get'] = $this->countGetBonus($code['bonus_code'],2);
		return $data;

    }

    public function countGetBonus($bonus_code,$bonus_type){
        $map['bonus_code'] = $bonus_code;
        $map['bonus_type'] = $bonus_type;
        $map['to_uid'] = array('neq',0);

        return M('bonus_list')->where($map)->count();

    }

}