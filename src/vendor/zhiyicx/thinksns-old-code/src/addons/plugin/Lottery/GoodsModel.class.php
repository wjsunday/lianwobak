<?php
/**
 * 抽奖模型.
 *
 * @author wj <wangalways@163.com>
 *
 * @version TS4.0
 */
class GoodsModel extends Model
{
    protected $tableName = 'lottery_goods';
    protected $_error;

    /**
     * 获取抽奖列表数据.
     *
     * @return array 商品列表数据
     */
    public function getGoodsList()
    {
        /* $data = D()->table($this->tablePrefix . 'lottery_goods as a LEFT JOIN ' .
            $this->tablePrefix . 'user_group b ON a.group_id = b.user_group_id ')
            ->field('a.*, b.user_group_name')
            ->order('update_time DESC')
            ->findPage(100); */
    	
    	$data = $this->order('update_time DESC')->findPage(100);

    	return $data;
    }
    
    public function doAddGoods($data)
    {
    	//$data['display_order'] = $this->count();
    	return $this->add($data);
    }
    
    public function getGoods($id)
    {
    	if (empty($id)) {
    		return array();
    	}
    	$map['goods_id'] = $id;
    	$data = $this->where($map)->find();
    	
    	return $data;
    }
    
    public function doEditGoods($id, $data)
    {
    	if (empty($id)) {
    		return false;
    	}
    	$map['goods_id'] = $id;
    	$res = $this->where($map)->save($data);

    	return (bool) $res;
    }
    
    public function doDelGoods($ids)
    {
    	$ids = is_array($ids) ? $ids : explode(',', $ids);
    	if (empty($ids)) {
    		return false;
    	}
    	$map['goods_id'] = array('IN', $ids);
    	$res = $this->where($map)->delete();
    	
    	return (bool) $res;
    }
    
    public function updateGoods($where, $data) {
    	$res = $this->where($where)->save($data);
    	
    	return $res;
    }
}
