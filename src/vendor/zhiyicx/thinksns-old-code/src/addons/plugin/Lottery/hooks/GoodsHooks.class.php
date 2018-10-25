<?php
/**
 * 抽奖.
 *
 * @author wj <wangalways@163.com>
 *
 * @version TS4.0
 */
include_once __DIR__.'/../common/Predis.php';
class GoodsHooks extends Hooks
{	
	protected $config;
	public function __construct() {
		$this->config = $this->_getConfig();
	}
	
	public function goodsList() {
		// 列表数据
		$list = $this->model('Goods')->getGoodsList();

		$this->assign('list', $list);
		
		$this->display('goods_list');
	}
	
	/**
	 * 添加奖品.
	 */
	public function addGoods()
	{
        /* $user_group = model('UserGroup')->getHashUsergroup();
        $this->assign('user_group', $user_group); */

		// 是否可编辑
		$this->assign('editPage', false);
		
		$previewUrl = Addons::createAddonUrl('Lottery', 'previewPic');
		$this->assign('previewUrl', $previewUrl);
		
		$this->display('add_goods');
	}
	
	public function doAddGoods()
	{
		// 组装数据
		$data['supplier_id'] = t($_POST['supplier_id']);
		$data['supplier_name'] = addslashes($_POST['supplier_name']);
		$data['supplier_phone'] = addslashes($_POST['supplier_phone']);
		//$data['group_id'] = t($_POST['group_id']);
		$data['area_id']  = t($_POST['area_id']);
		$data['add_time']  = time();
		$data['virtual_currency'] = $_POST['virtual_currency'];
		$data['cash'] = $_POST['cash'];
		$data['gift_name'] = $_POST['gift_name'];
		$data['goods_number'] = $_POST['goods_number'];
		$data['gift_type'] = intval($_POST['gift_type']);
		$data['remark'] = addslashes($_POST['remark']);
		$data['status']  = intval($_POST['status']);

        if ($data['status'] == 1) {
            $data['audit_time'] = 0;
        } else {
            $data['audit_time'] = time();
        }

		$data['update_time']  = time();
		
		$res = $this->model('Goods')->doAddGoods($data);
		if($res > 0) {
			$key = $this->config['redis_key']['goodsKey'].$res;
			Predis::getInstance()->hMset($key, $data);
		}
		
		return false;
	}
	
	/**
	 * 删除奖品操作.
	 *
	 * @return json 是否删除成功
	 */
	public function doDelGoods()
	{
		$result = array();
		$ids = t($_POST['ids']);
		if (empty($ids)) {
			$result['status'] = 0;
			$result['info'] = '参数不能为空';
			exit(json_encode($result));
		}
		$res = $this->model('Goods')->doDelGoods($ids);
		if ($res) {
			$result['status'] = 1;
			$result['info'] = '删除成功';
			$idsarr = explode(",", $ids);
			foreach($idsarr as $v) {
				Predis::getInstance()->del($this->config['redis_key']['goodsKey'].$v);
			}
		} else {
			$result['status'] = 0;
			$result['info'] = '删除失败';
		}
		exit(json_encode($result));
	}
	
	/**
	 * 编辑奖品.
	 */
	public function editGoods()
	{
        /* $user_group = model('UserGroup')->getHashUsergroup();
        $this->assign('user_group', $user_group); */

		$id = intval($_GET['id']);
		$data = $this->model('Goods')->getGoods($id);
		$this->assign('data', $data);
		$this->assign('supplier_id', array($data['supplier_id']));
		$this->assign('editPage', true);
		
		$previewUrl = Addons::createAddonUrl('Lottery', 'previewPic');
		$this->assign('previewUrl', $previewUrl);
		
		$this->display('add_goods');
	}
	
	public function doEditGoods()
	{
		// 数据组装
		$id = intval($_POST['goods_id']);
		$data['supplier_id'] = t($_POST['supplier_id']);
		$data['supplier_name'] = t($_POST['supplier_name']);
		$data['supplier_phone'] = t($_POST['supplier_phone']);
		//$data['group_id'] = intval($_POST['group_id']);
		$data['area_id']  = t($_POST['area_id']);
		$data['virtual_currency'] = t($_POST['virtual_currency']);
		$data['cash'] = t($_POST['cash']);
		$data['gift_name'] = t($_POST['gift_name']);
		$data['goods_number'] = t($_POST['goods_number']);
		$data['gift_type'] = intval($_POST['gift_type']);
		$data['audit_kefu'] = t($_POST['audit_kefu']);
		$data['remark'] = t($_POST['remark']);
		$data['status']  = intval($_POST['status']);
		if ($data['status'] == 1) {
		    $data['audit_time'] = 0;
        } else {
		    $data['audit_time'] = time();
        }
		$data['update_time']  = time();
		
		$res = $this->model('Goods')->doEditGoods($id, $data);
		
		if($res) {
			$key = $this->config['redis_key']['goodsKey'].$id;
			Predis::getInstance()->hMset($key, $data);
		}
		
		return false;
	}
	
	private function _getConfig()
	{
		$data = include ADDON_PATH.'/plugin/Lottery/config/config.php';
		
		return $data;
	}
}
