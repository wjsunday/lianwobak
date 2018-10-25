<?php
/**
 * 抽奖模型.
 *
 * @author wj <wangalways@163.com>
 *
 * @version TS4.0
 */
class ActiveModel extends Model
{
    protected $tableName = 'lottery_active';
    protected $_error;

    /**
     * 获取抽奖列表数据.
     *
     * @return array 抽奖列表数据
     */
    public function getActiveList()
    {
    	$data = $this->order('update_time DESC')->findPage(100);
    	
    	return $data;
    }
    
    public function doAddAct($data)
    {
    	$res = $this->add($data);
    	return (bool) $res;
    }
    
    public function getAct($id)
    {
    	if (empty($id)) {
    		return array();
    	}
    	$map['active_id'] = $id;
    	$data = $this->where($map)->find();
    	
    	return $data;
    }
    
    public function doEditAct($id, $data)
    {
    	if (empty($id)) {
    		return false;
    	}
    	$map['active_id'] = $id;
    	$res = $this->where($map)->save($data);
    	
    	return (bool) $res;
    }
    
    public function doDelAct($ids)
    {
    	$ids = is_array($ids) ? $ids : explode(',', $ids);
    	if (empty($ids)) {
    		return false;
    	}
    	$map['active_id'] = array('IN', $ids);
    	$res = $this->where($map)->delete();
    	
    	return (bool) $res;
    }
    
    public function getActiveOne($actWhere) {
    	$data = $this->where($actWhere)->order('add_time desc')->find();
    	return $data;
    }
}
