<?php
/**
 * 抽奖模型.
 *
 * @author wj <wangalways@163.com>
 *
 * @version TS4.0
 */
class AgModel extends Model
{
    protected $tableName = 'lottery_ag';
    protected $_error;

    /**
     * 获取抽奖列表数据.
     *
     * @return array 抽奖列表数据
     */
    public function getAgList($id)
    {
    	if (empty($id)) {
    		return array();
    	}
    	$map['a.active_id'] = $id;
    	//$data = $this->where($map)->findPage(100);

    	$data = D()->table($this->tablePrefix . 'lottery_ag a LEFT JOIN ' .
            $this->tablePrefix . 'lottery_active b ON a.active_id = b.active_id' .
            ' LEFT JOIN ' . $this->tablePrefix . 'lottery_goods c ON a.goods_id = c.goods_id ')
            ->field('a.*, b.act_name, c.gift_name')
            ->where($map)
            ->findPage(100);


    	return $data;
    }
    
    public function doAddActGoods($data)
    {
    	//$data['display_order'] = $this->count();
    	$res = $this->add($data);
    	
    	return (bool) $res;
    }
    
    public function getActGoods($id)
    {
    	if (empty($id)) {
    		return array();
    	}
    	$map['ag_id'] = $id;
    	$data = $this->where($map)->find();
    	
    	return $data;
    }
    
    public function doEditActGoods($id, $data)
    {
    	if (empty($id)) {
    		return false;
    	}
    	$map['ag_id'] = $id;
    	$res = $this->where($map)->save($data);
    	
    	return (bool) $res;
    }
    
    public function doDelActGoods($ids)
    {
    	$ids = is_array($ids) ? $ids : explode(',', $ids);
    	if (empty($ids)) {
    		return false;
    	}
    	$map['ag_id'] = array('IN', $ids);
    	$res = $this->where($map)->delete();
    	
    	return (bool) $res;
    }
    
    public function getOne($where,$field) {
    	$data = $this->fields($field)->where($where)->find();
    	return $data;
    }
}
