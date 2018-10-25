<?php
/**
 * 抽奖.
 *
 * @author wj <wangalways@163.com>
 *
 * @version TS4.0
 */
class OrderHooks extends Hooks
{	
	
	public function orderList() {
		// 列表数据
		$list = $this->model('Order')->getOrderList();
		$this->assign('list', $list);
		
		$this->display('order_list');
	}
	
}
