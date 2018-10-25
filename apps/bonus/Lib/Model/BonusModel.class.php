<?php



/**
 */
class BonusModel extends Model
{
    protected $tableName = 'bonus';
    protected $error = '';
    protected $fields = array(
             0 =>'id',1=>'bonus_code',2=>'bonus_type',3=>'bonus_many',4=>'bonus_fromuid',5=>'total_amount',6=>'send_time',7=>'bonus_msg',8=>'status'
    );
    /**
     * 获取资源列表
     * @param array $map 查询条件
     * @return array 获取资源列表
     */
    public function getBonusList()
    {
        // 获取资源分页结构
        $data = $this->field('*')->order(array('send_time'=>'DESC'))->findPage(15);
       
        foreach ($data ['data'] as $k => $v)
        {
        	$data['data'][$k]['bonus_code']='<a title="查看抢红包详细内容" href="'.U('bonus/Admin/viewBonus',array('bonus_code'=>$data['data'][$k]['bonus_code'])).'" >'.$data['data'][$k]['bonus_code'].'</a>';
        	$data['data'][$k]['bonus_fromuid']=getUserName($data['data'][$k]['bonus_fromuid']);
        	$data['data'][$k]['bonus_type']=$this->retBonusType($data['data'][$k]['bonus_type'],1,$data['data'][$k]['money_type']);
        	$data['data'][$k]['send_time']=date("Y-m-d H:i:s",$data['data'][$k]['send_time']) ;
        	// $data['data'][$k]['status']=$data['data'][$k]['status']=='1'?"<font color=green>末抢完</font>":"<font color=red>已抢完</font>" ;
            switch ($data['data'][$k]['status']) {
                case '0':
                    $data['data'][$k]['status'] = "<font color=red>已抢完</font>";
                    break;
                case '1':
                    $data['data'][$k]['status'] = "<font color=green>末抢完</font>";
                    break;
                case '2':
                    $data['data'][$k]['status'] = "<font color=red>已过期</font>";
                    break;    
            }
        }
        return $data;
    }

    public function retBonusType($type,$color=true,$money_type)
    {

        if ($money_type == 1)
        {
            $money_type_cn = '现金-';
        }elseif ($money_type == 2)
        {
            $money_type_cn = '财源币-';
        }
        switch ($type) {
            case '1':
                $ret = $money_type_cn.'普通红包';
                break;
            case '2':
                $ret = $color?$money_type_cn.'<font color=red>拼</font>运气红包':$money_type_cn.'拼运气红包';
                break;
            case '3':
                $ret = '指定用户红包';
                break;
            case '4':
                $ret = '转账红包';
                break;

        }
        return $ret;
    }

}
