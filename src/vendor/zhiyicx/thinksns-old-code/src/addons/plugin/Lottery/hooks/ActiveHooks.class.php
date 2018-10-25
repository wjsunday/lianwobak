<?php
/**
 * 抽奖.
 *
 * @author wj <wangalways@163.com>
 *
 * @version TS4.0
 */
include_once __DIR__.'/../common/Predis.php';
include_once __DIR__.'/../common/function.inc.php';
class ActiveHooks extends Hooks
{
	protected $config;
	public function __construct() {
		$this->config = $this->_getConfig();
		parent::__construct();
	}
	
	public function home_index_middle_publish_type() {
		/*静态模板创造url <div class="right-wrap">
		<a href="{:Addons::createAddonUrl('Lottery','doLottery')}">去抽奖</a>
		</div> */
		//Predis::getInstance()->set("wj", "123");
		
		//查看有没有进行中的活动 且开启的活动  放在首页查询减轻 抽奖接口 数据查询
		$now = time();
		$actWhere = array();
		$actWhere['active_status'] = 2;
		$actWhere['start_time'] = array('elt', $now);
		$actWhere['end_time'] = array('egt', $now);
		$active = $this->model('Active')->getActiveOne($actWhere);
		if(empty($active)) {
			//有效的活动不存在
			//echo '';exit;
		}
		$html = "<a href=". Addons::createAddonUrl('Lottery', 'doLottery', array('active_id' => $active['active_id'])).">去抽奖</a>";
		echo $html;
	}
	
	/**
	 * 钩子
	 * @param unknown $param
	 */
	public function doLottery() {
		$active_id = intval($_GET['active_id']);
		if(empty($active_id)) return false;
		//判断用户是否登陆
		if(!$this->mid) {
			/* $jump = U("public/Passport/quickLogin", array('url' => urlencode(Addons::createAddonUrl('Lottery', 'doLottery', array('active_id' => $active_id)))));
			header("location:$jump"); */
			/** 去登陆页面*/
		}
		
		$key = $this->config['redis_key']['actinfoKey'].$active_id;
		$active_status = Predis::getInstance()->hGet($key, 'active_status');
		if($active_status <> 2) {
			echo '活动关闭';exit;
		}
		
		$stopNum = $this->actLottery($active_id);
		
		if(is_array($stopNum)) {
			$goods_id = $stopNum['goods_id'];
			$ag_id    = $stopNum['ag_id'];
			$position = $stopNum['position'];
		}else {
			$position = $stopNum;
			$goods_id = 0;
			$ag_id    = 0;
		}
		//生成验证用户信息
		$info = array(
				'now' => time(),
				'ip' => getClientIp(),
				'uid' => $this->mid,//用户id
		);
		$str = signQuestion($info);
		
		$this->assign('active_id', $active_id);
		$this->assign('stopNum', $position);
		$this->assign('goods_id', $goods_id);
		$this->assign('ag_id', $ag_id);
		$this->assign('user_sign', $str);
		$this->display('showActive');
	}
	
	/**
	 * 流水账入库和一些基本的数据操作
	 */
	public function insertLottery() {
		//入库 ts_lottery_goods_record  ts_lottery_user ts_lottery_goods ts_lottery_ag
		//随机抽奖 未中奖 然后减去nums 次数(redis)， 如果到了1000  和 同时满足 时间    是否必中,  若不中 按照什么概率来 中奖
		//根据用户组别来判断
		//添加用户校验，防刷ts_lottery_active 活动已抽奖次数 nums   和   ts_lottery_ag  缩小范围的 nums 都记录到 redis
		//中奖人数列表
		$now = time();
		$client_ip = getClientIp();
		$sign_data = $_POST['user_sign'];
		//验证活动状态信息
		$status_check = false;
		$str_sign_data = unsignQuestion($sign_data);
		$str_sign_data =  preg_replace('/[\x00-\x1F\x80-\x9F]/u', '', trim($str_sign_data));
		$sign_data_info = json_decode($str_sign_data, true);
		// 时间不能超过当前时间5分钟，IP和用户保持不变
		if ($sign_data_info && $sign_data_info['now'] < $now && $sign_data_info['now'] > $now - 300 && $sign_data_info['ip'] == $client_ip
			&& $sign_data_info['uid'] == $this->mid) {
			$status_check = true;
		}
		if (!$status_check) {
			echo '用户校验值验证没有通过';exit;
		}
		
		$active_id = intval($_POST['active_id']); 
		$stopNum   = intval($_POST['stopNum']);
		$goods_id  = intval($_POST['goods_id']);
		$ag_id     = intval($_POST['ag_id']);
		/* $phone     = addslashes($_POST['phone']);
		$address   = addslashes($_POST['address']); */
		
		//增加活动抽奖数
		$key = $this->config['redis_key']['activenumsKey'].$active_id;
		Predis::getInstance()->hincrby($key, "nums", 1);
		
		if($stopNum == 10) {
			//return '没中奖';
			//扣减概率  所有商品
			$agIdsKey = $this->config['redis_key']['actagidsKey'].$active_id;
			$ag = Predis::getInstance()->sMembers($agIdsKey);
			foreach($ag as $id) {
				$agKey = $this->config['redis_key']['agKey'].$id;
				Predis::getInstance()->hincrby($agKey, "probability", -1);
			}
		}else {
			//扣减库存
			$goodsKey = $this->config['redis_key']['goodsKey'].$goods_id;
			$num_left = Predis::getInstance()->hincrby($goodsKey, "goods_number", -1);
			
			if($num_left > 0) {
				//重置概率
				$agKey = $this->config['redis_key']['agKey'].$ag_id;
				$probability_old = Predis::getInstance()->hget($agKey, 'probability_old');
				Predis::getInstance()->hset($agKey, "probability", $probability_old);
				
				//插入订单 orderModel.class.php & 减商品库存
				/* $this->model('Goods')->updateGoods(array('goods_id' => $goods_id), array(
						'goods_number' => 'goods_number-1'
				));
				 */
				$this->model('Goods')->query('Update ts_lottery_goods set goods_number=goods_number-1 where goods_number>0 and goods_id='.$goods_id);
				
				$this->model('Order')->add(array(
						'order_sn' => $this->createOrderSn(),
						'user_id'  => 1,
						'user_name' => 'xxx',
						//'phone' => $phone,
						'group_id' => 1,
						//'address' => $address,
						'add_time' => $now,
						/* 'has_num' => 1,
						'last_num' => 10,
						'invitees_num' => 10,
						'friend_num' => 100,
						'user_amount' => 100,
						'user_month' =>100,
						'supplier_id' => 10, */
						'remark' => 123,
						'goods_id' => $goods_id,
						'active_id' => $active_id,
				));
			}else {
				//没有库存了
				echo '库存不足';
			}
		}
	}
	
	/**
	 * 活动抽奖
	 */
	public function actLottery($active_id) {
		//查找该活动下面的所有奖品  10个
		$now = time();
		$stopNumArr = $agWhere = array();
		$stopNum = 10; //中奖奖品 1-10  10默认谢谢惠顾
		/* $agWhere['active_id'] = $active_id;
		$ag = $this->model('ag')->getAgList($agWhere); */
		
		/**
		 * 还需要判断用户组别是否能中奖，不能中奖直接返回 $stopNum=10, 能中奖才执行下面部分
		 */
		
		$agIdsKey = $this->config['redis_key']['actagidsKey'].$active_id;
		$ag = Predis::getInstance()->sMembers($agIdsKey);
		if(!empty($ag)) {
			$giftArr = array();
			$userGroup = model('UserGroup')->getUserGroup($this->mid);
			foreach($ag as $id) {
				$keyTmp = $this->config['redis_key']['agKey'].$id;
				$val = Predis::getInstance()->hGetAll($keyTmp);
				if(empty($val['position'])) continue; //位置不填写的奖品直接 忽略
				
				//看下商品是否还有库存
				$keyGoods = $this->config['redis_key']['goodsKey'].$val['goods_id'];
				$goods_number = Predis::getInstance()->hGet($keyGoods, 'goods_number');
				if($goods_number <= 0) continue;
				
				//判断抽奖用户是否属于活动商品的组别类。
				if($val['group_id'] <> $userGroup['user_group_id']) continue;
				
				if($val['type'] == 2) { //平均
					$vOld = mt_rand(1, $val['probability']);
					$vNew = mt_rand(1, $val['probability']);
					if($vOld == $vNew) {
						$stopNumArr[$val['position']] = $val['position']; //定义数组是为了 同时满足中奖的多个商品
						$giftArr[$val['position']] = array(
								'ag_id'    => $id,
								'goods_id' => $val['goods_id'],
								'position' => $val['position'],
						);
					}
				}else { //随机的前提条件是 满足时间范围内 才可以中奖
					if($now > $val['random_start'] && $now < $val['random_end']) {
						$vOld = mt_rand(1, $val['probability']);
						$vNew = mt_rand(1, $val['probability']);
						if($vOld == $vNew) {
							$stopNumArr[$val['position']] = $val['position'];
							$giftArr[$val['position']] = array(
									'ag_id'    => $id,
									'goods_id' => $val['goods_id'],
									'position' => $val['position'],
							);
						}
					}
				}
			}
			
			if(!empty($stopNumArr)) {
				$stopNumTmp = array_rand($stopNumArr, 1);
				$stopNum = $giftArr[$stopNumTmp];
			}
		}
		
		return $stopNum;
	}
	
	
	public function activeList() {
		// 列表数据
		$list = $this->model('Active')->getActiveList();
		
		if(!empty($list['data'])) {
			foreach($list['data'] as $key => $val) {
				$actkey = $this->config['redis_key']['activenumsKey'].$val['active_id'];
				$list['data'][$key]['nums'] = Predis::getInstance()->hget($actkey, "nums");
			}
		}
		
		$this->assign('list', $list);
		
		$this->display('act_list');
	}
	
	/**
	 * 添加奖品.
	 */
	public function addAct()
	{
		// 是否可编辑
		$this->assign('editPage', false);
		
		$previewUrl = Addons::createAddonUrl('Lottery', 'previewPic');
		$this->assign('previewUrl', $previewUrl);
		
		$this->display('add_act');
	}
	
	public function doAddAct()
	{
		// 组装数据
		$data['act_name'] = t($_POST['act_name']);
		$data['active_status'] = intval($_POST['active_status']);
		$data['start_time'] = strtotime(t($_POST['start_time']));
		$data['end_time'] = strtotime(t($_POST['end_time']));
		$data['add_time'] = time();
		$data['update_time'] = time();

		$res = $this->model('Active')->doAddAct($data);
		if($res) {
			$key = $this->config['redis_key']['actinfoKey'].$res;
			Predis::getInstance()->hMset($key, $data);
		}
		
		return false;
	}
	
	/**
	 * 删除奖品操作.
	 *
	 * @return json 是否删除成功
	 */
	public function doDelAct()
	{
		$result = array();
		$ids = t($_POST['ids']);
		if (empty($ids)) {
			$result['status'] = 0;
			$result['info'] = '参数不能为空';
			exit(json_encode($result));
		}
		$res = $this->model('Active')->doDelAct($ids);
		if ($res) {
			$idsarr = explode(",", $ids);
			foreach($idsarr as $v) {
				$agIdsKey = $this->config['redis_key']['actinfoKey'].$v;
				Predis::getInstance()->del($agIdsKey, $v);
			}
			$result['status'] = 1;
			$result['info'] = '删除成功';
		} else {
			$result['status'] = 0;
			$result['info'] = '删除失败';
		}
		exit(json_encode($result));
	}
	
	/**
	 * 编辑奖品.
	 */
	public function editAct()
	{
		// 获取广告位信息
		$id = intval($_GET['id']);
		$data = $this->model('Active')->getAct($id);
		$key = $this->config['redis_key']['activenumsKey'].$id;
		$data['nums'] = Predis::getInstance()->hget($key, "nums");
		$this->assign('data', $data);
		$this->assign('editPage', true);
		
		$previewUrl = Addons::createAddonUrl('Lottery', 'previewPic');
		$this->assign('previewUrl', $previewUrl);
		
		$this->display('add_act');
	}
	
	public function doEditAct()
	{
		// 数据组装
		$id = intval($_POST['active_id']);
		$data['act_name'] = addslashes($_POST['act_name']);
		$data['active_status'] = intval($_POST['active_status']);
		$data['start_time'] = strtotime($_POST['start_time']);
		$data['end_time'] = strtotime($_POST['end_time']);
		$data['update_time'] = time();
		
		$res = $this->model('Active')->doEditAct($id, $data);
		if($res) {
			$key = $this->config['redis_key']['actinfoKey'].$id;
			Predis::getInstance()->hMset($key, $data);
		}
		
		return false;
	}
	
	/////////////////////////////////////////////活动商品操作
	public function agList() {
		$id = intval($_GET['id']);
		
		$list = $this->model('Ag')->getAgList($id);
		
		if(!empty($list['data'])) {
			$user_group = model('UserGroup')->getHashUsergroup();
			foreach($list['data'] as $key => $val) {
				$agKey = $this->config['redis_key']['agKey'].$val['ag_id'];
				$list['data'][$key]['has_lottery'] = Predis::getInstance()->hget($agKey, 'probability');
				$list['data'][$key]['user_group_name'] = $user_group[$val['group_id']];
			}
		}

		$this->assign('active_id', $id);
		$this->assign('list', $list);
		$this->display('act_goods_list');
	}
	
	public function addActGoods() {
		$user_group = model('UserGroup')->getHashUsergroup();
		$this->assign('user_group', $user_group);
		$active_id = intval($_GET['active_id']);
		
		$goods_list = $this->model('Goods')->getGoodsList();
		$goodsArr   = array();
		if(!empty($goods_list['data'])) {
			foreach($goods_list['data'] as $key => $val) {
				$goods_name = '';
				if($val['gift_type'] == 1) {
					$goods_name = '代金券'.$val['virtual_currency'];
				}elseif($val['gift_type'] == 2) {
					$goods_name = '零用钱'.$val['cash'];
				}else {
					$goods_name = '礼品名称'.$val['gift_name'];
				}
				$goodsArr[$key] = array(
						'goods_id' => $val['goods_id'],
						'goods_name' => $val['supplier_name']."-".$goods_name,
				);
			}
		}
		$this->assign('goodsArr', $goodsArr);
		
		// 是否可编辑
		$this->assign('editPage', false);
		
		$previewUrl = Addons::createAddonUrl('Lottery', 'previewPic');
		$this->assign('previewUrl', $previewUrl);
		$this->assign('active_id', $active_id);
		$this->display('add_act_goods');
	}
	
	public function doAddActGoods()
	{
		// 组装数据
		$data['goods_id'] = intval($_POST['goods_id']);
		$data['active_id'] = intval($_POST['active_id']);
		$data['type'] = intval($_POST['type']);
		$data['probability'] = intval($_POST['probability']);
		$data['random_start'] = strtotime($_POST['random_start']);
		$data['random_end'] = strtotime($_POST['random_end']);
		$data['position'] = intval($_POST['position']);
		$data['group_id'] = intval($_POST['group_id']);

		$res = $this->model('Ag')->doAddActGoods($data);
		if($res) {
			//将活动商品的概率存入redis 方便压缩中奖概率
			$data['probability_old'] = $data['probability'];
			$key = $this->config['redis_key']['agKey'].$res;
			Predis::getInstance()->hMset($key, $data);
			
			//将活动商品主键放入  该活动下 actagidsKey
			$agIdsKey = $this->config['redis_key']['actagidsKey'].$data['active_id'];
			Predis::getInstance()->sAdd($agIdsKey, $res);
		}
		
		return false;
	}
	
	public function editActGoods() {
		$user_group = model('UserGroup')->getHashUsergroup();
		$this->assign('user_group', $user_group);
		$id = intval($_GET['id']);
		$goods_list = $this->model('Goods')->getGoodsList();
		$goodsArr   = array();
		if(!empty($goods_list['data'])) {
			foreach($goods_list['data'] as $key => $val) {
				$goods_name = '';
				if($val['gift_type'] == 1) {
					$goods_name = '代金券'.$val['virtual_currency'];
				}elseif($val['gift_type'] == 2) {
					$goods_name = '零用钱'.$val['cash'];
				}else {
					$goods_name = '礼品名称'.$val['gift_name'];
				}
				$goodsArr[$key] = array(
						'goods_id' => $val['goods_id'],
						'goods_name' => $val['supplier_name']."-".$goods_name,
				);
			}
		}
		$this->assign('goodsArr', $goodsArr);
		$data = $this->model('ag')->getActGoods($id);
		if(!empty($data)) {
			$data['random_start'] = date("Y-m-d H:i:s", $data['random_start']);
			$data['random_end']   = date("Y-m-d H:i:s", $data['random_end']);;
		}
		$agKey = $this->config['redis_key']['agKey'].$id;
		$data['has_lottery'] = Predis::getInstance()->hget($agKey, 'probability');
		
		$this->assign('data', $data);
		$this->assign('active_id', $data['active_id']);
		$this->assign('editPage', true);
		
		$previewUrl = Addons::createAddonUrl('Lottery', 'previewPic');
		$this->assign('previewUrl', $previewUrl);
		
		$this->display('add_act_goods');
	}
	
	public function doEditActGoods() {
		$id = intval($_POST['ag_id']);
		$data['goods_id'] = intval($_POST['goods_id']);
		$data['active_id'] = intval($_POST['active_id']);
		$data['type'] = intval($_POST['type']);
		$data['probability'] = intval($_POST['probability']);
		$data['random_start'] = strtotime($_POST['random_start']);
		$data['random_end'] = strtotime($_POST['random_end']);
		$data['position'] = intval($_POST['position']);
		$data['group_id'] = intval($_POST['group_id']);
		
		$res = $this->model('ag')->doEditActGoods($id, $data);
		if($res) {
			//将活动商品的概率存入redis 方便压缩中奖概率
			$data['probability_old'] = $data['probability'];
			$key = $this->config['redis_key']['agKey'].$id;
			Predis::getInstance()->hMset($key, $data);
		}
		
		return false;
	}
	
	public function doDelActGoods()
	{
		//先关闭删除功能，待redis这块功能完善再启用
		/* $agIdsKey = $this->config['redis_key']['actagidsKey'].$data['active_id'];
		Predis::getInstance()->sRem($agIdsKey, $res); */
		
		$result = array();
		$ids = t($_POST['ids']);
		if (empty($ids)) {
			$result['status'] = 0;
			$result['info'] = '参数不能为空';
			exit(json_encode($result));
		}
		$res = $this->model('ag')->doDelActGoods($ids);
		if ($res) {
			$result['status'] = 1;
			$result['info'] = '删除成功';
			$idsarr = explode(",", $ids);
			foreach($idsarr as $v) {
				$agIdsKey = $this->config['redis_key']['actagidsKey'].$v;
				Predis::getInstance()->sRem($agIdsKey, $v);
			}
		} else {
			$result['status'] = 0;
			$result['info'] = '删除失败';
		}
		exit(json_encode($result));
	}
	
	/**
	 * 创建订单号
	 */
	public function createOrderSn(){
		$orderSn = date('ymdHis').rand(100000,999999);
		$result = $this->model('Order')->where(array('order_sn'=>$orderSn))->count();
		if($result > 0){
			return $this->createOrderSn();
		}
		return $orderSn;
	}
	
	private function _getConfig()
	{
		$data = include ADDON_PATH.'/plugin/Lottery/config/config.php';
		
		return $data;
	}
}
