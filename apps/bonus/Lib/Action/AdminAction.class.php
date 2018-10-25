 <?php
/**
 * 后台，红包管理控制器
 * @author singsin <singsin.com@qq.com>
 * @version TS3.0
 */
// 加载后台控制器
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');

class AdminAction extends AdministratorAction {
	public $redis;
	public $pageTitle = array();
	
	/**
	 * 初始化，初始化页面表头信息
	 */
	public function _initialize() {
		$this->pageTitle['redpacketConfig'] = '红包配置';
		$this->pageTitle['redpacketList'] = '红包列表';
		$this->pageTitle['sendRedpacket'] = '管理员发红包测试';
		$this->pageTitle['bonusCate'] = '红包分类';
		$this->startRedis(C('REDIS_IP'), C('REDIS_PORT'));
		
		parent::_initialize();
	}
	/**
	 * 微吧列表
	 * @return void
	 */
	public function index() {
		$this->redpacketConfig();	
	}

	//红包设置
		function redpacketConfig()
		{
			$this->_initBonusListAdminMenu();
			$this->setting = model('Xdata')->get('bonus_Admin:redpacketConfig');
			$this->vipList = model('VipPay')->vipList();
			$this->display('redpacketconfig');	
			
		}
		//红包设置操作
		function doRedpacketConfig()
		{
			$list = $_POST['systemdata_list'];
			$key = $_POST['systemdata_key'];
			$key = $list.":".$key;
				
			$value['redpacket_max_money'] = intval($_POST['redpacket_max_money']);
			$value['redpacket_limit_money'] = intval($_POST['redpacket_limit_money']);
			$value['redpacket_return_hours'] = intval($_POST['redpacket_return_hours']);
	        $value['app_index_bonus'] = intval($_POST['app_index_bonus']);

	        $Data = array();
	        $vipList = model('VipPay')->vipList();

	        for ($i = 0; $i < count($vipList); $i++) {
	            //$Data[] = array('user_group_id' => $_POST['user_group_id'][$i], 'number' => $_POST['get_bonus_number'][$i]);
	            //$value['get_bonus_number'.$i] = $Data[$i];
	            $value['get_bonus_number_'.$vipList[$i]['user_group_id']] = $_POST["get_bonus_number_{$vipList[$i]['user_group_id']}"];
	        }
			$res = model('Xdata')->put($key,$value);

			if ($res)
			{
				$this->assign('jumpUrl', U('bonus/Admin/redpacketConfig',array('tabHash'=>'redpacketConfig')));
				$this->success('保存成功');
			
			}else{
				$this->error('保存失败');
			}
		}	
	public function redpacketList(){
		
		// 管理菜单
		$this->_initBonusListAdminMenu();
	
			$this->pageKeyList = array('id','bonus_code','bonus_type','bonus_fromuid','total_amount','bonus_many','send_time','expire_time','status');
			$data = D('Bonus', 'bonus')->getBonusList();
			$this->displayList($data);
	}
	
	
			//管理员发红包
		function sendRedpacket()
		{
			
			$this->_initBonusListAdminMenu();

			$this->pageButton[] = array('title'=>'发红包','onclick'=>"document.location.href='".U('bonus/Admin/sendRedpacket',array('tabHash'=>'sendRedpacket','type'=>1))."'");
			$this->pageButton[] = array('title'=>'直接到账红包','onclick'=>"document.location.href='".U('bonus/Admin/sendRedpacket',array('tabHash'=>'sendRedpacket','type'=>2))."'");
			$this->pageButton[] = array('title'=>'指定用户红包','onclick'=>"document.location.href='".U('bonus/Admin/sendRedpacket',array('tabHash'=>'sendRedpacket','type'=>3))."'");

			$this->assign('pageButton',$this->pageButton);
			$this->setting = model('Xdata')->get('bonus_Admin:redpacketConfig');
			//用户组
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
	       	$this->assign('sysGroups',$sysGroups);

			switch ($_GET['type']) {
				case '1':
					$this->display('sendRedpacket');
					break;
				case '2':
					$this->display('sendRedpacket2');
					break;
				case '3':
					$this->display('sendRedpacket3');
					break;
				
				default:
					$this->display('sendRedpacket');
					break;
			}
			
		}
	/**
	 * 后台管理菜单
	 * @return void
	 */
	private function _initBonusListAdminMenu(){
		$this->pageTab[] = array('title'=>'红包配置','tabHash'=>'redpacketConfig','url'=>U('bonus/Admin/redpacketConfig'));
		$this->pageTab[] = array('title'=>'红包列表','tabHash'=>'redpacketList','url'=>U('bonus/Admin/redpacketList'));
		$this->pageTab[] = array('title'=>'管理员发红包测试','tabHash'=>'sendRedpacket','url'=>U('bonus/Admin/sendRedpacket',array('type'=>1)));
		//$this->pageTab[] = array('title'=>'红包分类','tabHash'=>'bonusCate','url'=>U('bonus/Admin/bonusCate'));


		
	}
	
	function viewBonus(){
			$this->_initBonusListAdminMenu();
			$bonus_code = t($_GET['bonus_code']);
			if ($bonus_code)
			{	
				$data = array();
				$data["count"] = count($data['data']);
				$data["totalPages"] = 1;
				$data["totalRows"] = count($data['data']);
				$data["nowPage"] = 1;
				$data["html"] = NULL;
				$data['data'] = M('bonus_list')->where(array('bonus_code'=>$bonus_code))->findAll();
				$this->pageKeyList = array('id','bonus_code','total_amount','bonus_type','bonus_fromuid','bonus_money','status','to_uid','send_time','get_time','expire_time');
				$this->allSelected = false;
				foreach ($data['data'] as $k => $v)
				{
					$data['data'][$k]['bonus_type'] = D('bonus')->retBonusType($data['data'][$k]['bonus_type']);
					$data['data'][$k]['bonus_fromuid']=getUserName($data['data'][$k]['bonus_fromuid']);
					$data['data'][$k]['status']=$data['data'][$k]['to_uid']=='0'?'<font color=green>末领取</font>':'<font color=red>已领取</font>';
					$data['data'][$k]['to_uid']=$data['data'][$k]['to_uid']?getUserName($data['data'][$k]['to_uid']):'0';
					$data['data'][$k]['send_time']=date("Y-m-d H:i:s",$data['data'][$k]['send_time']) ;
					
					$data['data'][$k]['get_time']=$data['data'][$k]['get_time']?date("Y-m-d H:i:s",$data['data'][$k]['get_time']):'0' ;
					
				}
				
				$this->displayList($data);
			}else{
				$this->assign('jumpUrl', U('bonus/Admin/redpacketList',array('tabHash'=>'redpacketList')));
				$this->error('红包生成码错误!');
			}
		}	
	
	
	
	public function startRedis($host,$port)
	{
	    $this->redis = new \Redis();
        if (!$this->redis->connect('127.0.0.1','2830'))
        {
            die('redis server must be start!');
        }else{
            $this->redis->auth('ufdauif$%^&TYUIFGH$%^&%^&TY');
        }
		
	}
	
	
		function doSendRedpacket()
		{
			$send_staus = false;
			$value['redpacket_many'] = abs(intval($_POST['redpacket_many']));
			$value['redpacket_pre_money'] = abs(floatval($_POST['redpacket_pre_money']));
			$value['editredpacketType'] = intval($_POST['editredpacketType']);
			$value['redpacket_msg'] = strval($_POST['redpacket_msg']);
			$value['uid'] = intval($_POST['uid']);
			$value['sysGroups'] = intval($_POST['sysGroups']);
			if (!$value['uid'])
			{
					$this->assign('jumpUrl', U('bonus/Admin/sendRedpacket',array('tabHash'=>'sendRedpacket','type'=>$value['editredpacketType'])));
					$this->error('请填写用户id');						
			}

			if (!$value['redpacket_many'])
			{
					$this->assign('jumpUrl', U('bonus/Admin/sendRedpacket',array('tabHash'=>'sendRedpacket','type'=>$value['editredpacketType'])));
					$this->error('请填写红包个数');						
			}

			if (!$value['redpacket_pre_money'])
			{
					$this->assign('jumpUrl', U('bonus/Admin/sendRedpacket',array('tabHash'=>'sendRedpacket','type'=>$value['editredpacketType'])));
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
			$data['bonus_fromuid'] = $value['uid'];
			$data['send_time'] = time();		
			$data['bonus_code'] = md5(rand(1000,9999).$data['send_time'].'asin'.rand(1000,9999).$data['send_time'].$data['bonus_fromuid'].$data['bonus_type']);
			$data['bonus_type'] = $value['editredpacketType'];
			$data['bonus_msg'] = $value['redpacket_msg'];
			$data['bonus_many'] =$value['redpacket_many'];
			$data['user_group'] = $value['sysGroups'];
			$data['status'] =1;			

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
			unset($data['user_group']);
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
		    	$this->assign('jumpUrl', U('bonus/Admin/redpacketList',array('tabHash'=>'redpacketList')));
		    	$this->success('红包发送成功!');
		    }else{
		    	$this->error('红包发送失败');
		    }
		
			if ($res)
			{
				$this->assign('jumpUrl', U('bonus/Admin/sendRedpacket',array('tabHash'=>'redpacketList')));
				$this->success('红包发出去了哦!');
					
			}else{
				$this->error('红包发出失败');
			}
			
			
		}

		
		function sendRandBonus($total=0, $count=3, $type=2){
			if($type==1){
				$input     = range(0.01, $total, 0.01);
				if($count>1){
					$rand_keys = (array) array_rand($input, $count-1);
					$last    = 0;
					foreach($rand_keys as $i=>$key){
						$current  = $input[$key]-$last;
						$items[]  = $current;
						$last    = number_format($input[$key],2);
					}
				}
				$items[]    = number_format($total-array_sum($items),2);
			}else{
				$avg      = number_format($total/$count, 2);
				$i       = 0;
				while($i<$count){
					$items[]  = $i<$count-1?$avg:number_format($total-array_sum($items),2);
					$i++;
				}
			}
			return $items;
		}
		


		function uBanktransfer(array $data=array())
		{
			$data = count($data) ? $data : $_POST;
			if(!$data['toUid'] || $data['num']<=0 || !$data['fromUid']){
				return false;
			}

			$toUser = model('User')->getUserInfo($data['toUid']);
			if (!$toUser) return false;
	        $uBank =model('Credit')->getUserCredit($data['toUid']);
	        $uBank = intval($uBank['credit']['ubank']['value']);
	        $uBank2 =model('Credit')->getUserCredit($data['fromUid']);
	        $uBank2 = intval($uBank2['credit']['ubank']['value']);
	        if($uBank2 < intval($data['num'])){
	            return false;
	        }
			$add['type'] = 3;
			$add['uid'] = intval($data['toUid']);
			$add['action'] = '红包收入';
			$add['des'] = t($data['bonus_msg']);
			$add['change'] = intval($data['num']); 
			$add['ctime'] = time();
	        $add['detail'] = '{"ubank":"钱包' . $add["change"] . '"}';

			$add2 = $add;
			$add2['uid'] = intval($data['fromUid']);
			$add2['change'] = -1 * intval($data['num']);
			$add2['action'] = '发红包转出';
	        $add2['detail'] = '{"ubank":"钱包' . $add2["change"] . '"}';
	        M('credit_user')->where("uid={$add2['uid']}")->save(array('ubank'=>$uBank2-$add['change']));
	        M('credit_user')->where("uid={$add['uid']}")->save(array('ubank'=>$uBank+$add['change']));
			//转账对象积分变动记录
			//当前用户积分变动记录
			D('credit_record')->add($add) && D('credit_record')->add($add2);
			model('Credit')->cleanCache($data['toUid']);
			model('Credit')->cleanCache($data['fromUid']);
			//bonus ,bonus_list add record

			$bonus_['bonus_fromuid'] = $_SESSION['mid'];
			$bonus_['send_time'] = time();		
			$bonus_['bonus_code'] = md5(rand(1000,9999).$bonus_['send_time'].'asin'.rand(1000,9999).$bonus_['send_time'].$bonus_['bonus_fromuid'].$bonus_['bonus_type']);
			$bonus_['bonus_type'] = 4;
			$bonus_['bonus_msg'] = $data['bonus_msg'];
			$bonus_['bonus_many'] = 1;
			$bonus_['total_amount'] = $data['num'];
			$bonus_['status'] =0;

			M('bonus')->add($bonus_);
			unset($bonus_['bonus_msg']);
			unset($bonus_['bonus_many']);
			$bonus_['to_uid']=$data['toUid'];
			$bonus_['get_time']=$bonus_['send_time'];	
			$bonus_['bonus_money'] = $data['num'];

			$res_ = D('bonus_list')->add($bonus_);
			return true;
		}

		public function doSendRedpacket2()
		{
			
		if ($_POST) {
            $_POST ['fromUid'] = $this->mid;
            $result = $this->uBanktransfer();
            if ($result) {
                $this->success('红包已发！');
                return;
            }
            $this->error('发红包失败！');
        }
        $map ['uid'] = $this->mid;
        $map ['action'] = '发红包转出';
        //$credit_record = D('credit_record')->where($map)->order('ctime DESC')->findPage(100);

		}
	public function makeRedisBonus($bonus_code)
	{
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
}