<?php
/**
 * 抽奖模型.
 *
 * @author wj <wangalways@163.com>
 *
 * @version TS4.0
 */
class OrderModel extends Model
{
    protected $tableName = 'lottery_order';
    protected $_error;

    /**
     * 获取抽奖列表数据.
     *
     * @return array 商品列表数据
     */
    public function getOrderList()
    {
    	$data = $this->order('add_time DESC')->findPage(100);
    	
    	return $data;
    }
    
    /* public function add($data) {
    	$res = $this->add($data);echo $this->getLastSql();exit;
    	return (bool) $res;
    } */
}
