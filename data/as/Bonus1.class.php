<?php
namespace Apps\BaseBonus;

class Bonus{
    private $redis ;
    public function __construct()
    {

    }
 
    private function connectRedis(){
        $this->redis = new \Redis();
        if (!$this->redis->connect('127.0.0.1','6379'))
        {
            die('redis server must be start!');
        }
    }
    
    
    
    //redis红包生成
    public function makeRedisBonus($bonus_code)
    {
        self::connectRedis();
        $bonus_code = isset($bonus_code)?t($bonus_code):0;
        if (!$bonus_code) return 0;
        
        if (!$this->redis->keys("BINFO:$bonus_code"))
       
        {
          
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
            $bonus_list = M('bonus_list')->field('id,bonus_money')->where(array("bonus_code"=>$bonus_code,'to_uid'=>''))->order('bonus_money DESC')->select();
            $bonus_list_count = count($bonus_list);
            for ($i=0;$i<$bonus_list_count;$i++)
            {
                $this->redis->SADD("BLISTO:".$bonus_code,$bonus_list[$i]['id'].'#'.$bonus_list[$i]['bonus_money']);
                $this->redis->SADD("BLISTN:".$bonus_code,$bonus_list[$i]['id'].'#'.$bonus_list[$i]['bonus_money']);
            }
           
        }
    
        return true;
    
    }
    
    
    //随机红包生成
    public function sendRandBonus($total=0, $count=3, $type=2){
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
    
    //更新红包状态
    public function updateBonusInfoStatus($bonus_code,$update=true)
    {
        self::connectRedis();
        if ($bonus_code)
        {
            $bonus_list_orginal = $this->redis->SCARD("BLISTO:".$bonus_code);
            if ($bonus_list_orginal == 0)
            {
                $this->redis->HSET("BINFO:$bonus_code","status",0);
                if ($update)
                {
                    $sql ="UPDATE ".C('DB_PREFIX')."bonus
					set status =0
					where bonus_code='".$bonus_code."'";
                    M()->execute($sql);
                }
            }
            	
        }
    }
    
    //我拿到的红包
    public function showMyGets($bonus_code='',$mid='',$update=true)
    {
        self::connectRedis();
        if (!$bonus_code) return 0;
        $bonus_list_gets = $this->redis->SMEMBERS("BGETS:MIM:$bonus_code");
        $gets_count = count($bonus_list_gets);
        for($i=0;$i<$gets_count;$i++)
        {
            $bonus_get_info[$i] = $bonus_list_gets[$i]?explode('#', $bonus_list_gets[$i]):0;
            if ($bonus_get_info[$i][0] == $mid)
            {
                $myGets= $bonus_get_info[$i];
            }
        }
        if ($myGets && $update)
        {
            //update
            $res = X('Credit')->setUserCredit($myGets[0],array('name'=>'bonus_getBonus','ubank'=>floatval($myGets[2]),''),1);
            if ($res){
                $data = array();
    
                $data['to_uid'] = $this->mid;
                $data['get_time'] = time();
                	
                $sql ="UPDATE ".C('DB_PREFIX')."bonus_list
				set to_uid ='".$this->mid."',get_time = ".time()."
				where id='".$myGets[1]."'";
    
                M()->execute($sql);
            }
        }
        if ($myGets)
        {
            return $myGets;
        }else{
            return array(0,0,0);
        }
    
    }
    
    //检查redis红包是否存在
    public function checkBonusExist($bonus_code)
    {
        self::connectRedis();
        if ($this->redis->keys("BINFO:$bonus_code"))
        {
            return 1;
        }else{
            return 0;
        }
    }
    
    public function getRedisBonus($bonus_code){
        self::connectRedis();
        $bonus_info = $this->redis->HGETALL("BINFO:$bonus_code");
        
        if ($bonus_info['status'] == '0') return 0;
        $bonus_list_count = $this->redis->SCARD("BGETS:MID:$bonus_code");
        
        if (intval($bonus_list_count) < intval($bonus_info['bonus_many']))
        {
            if ($this->redis->SISMEMBER("BGETS:MID:$bonus_code",$this->mid))
            {
                return 1;
               
            }else{
                $this->redis->SADD("BGETS:MID:$bonus_code",$this->mid);
                $b_IM = $this->redis->SPOP("BLISTO:".$bonus_code);
                $this->redis->SADD("BGETS:MIM:$bonus_code",$this->mid.'#'.$b_IM);
                $getMybonusInfo = self::showMyGets($bonus_code,$this->mid,TRUE);
                self::updateBonusInfoStatus($bonus_code,true);
                return 2;
                //array('uid'=>$this->mid,'bonusListId'=>$getMybonusInfo[1],'bonusMondy'=>$getMybonusInfo[2]);
            }
        
        }else{
            //$this->redis->HSET("BINFO:$bonus_code","status",'0');
            return 3;
        }
    }
    
    public function getBonusType($type)
    {
        if ($type){
             switch ($type) {
            case '1':
                $ret = '个人';
                break;
            case '2':
                $ret = '企业';
                break;

        }
        return $ret;
        }
    }
    //
    public function getAmountRange($total_amount)
    {
        
        
    }
    
    public function checkBonusExistById($bonus_id)
    {
        if (!$bonus_id)
        {
            return 0;
        }else{
            $bonusInfo = M("bonus")->where(array("id"=>$bonus_id,'status'=>1))->find();
            echo M()->getLastSql();
            if ($bonusInfo)
            {
                return 1;
            }else{
                return 0;
            }     
        }
    }
    
    public function getAppIndexBonus($bonus_id)
    {
        
        if ($bonus_id)
        {
            $bonusInfo = M("bonus")->where(array("id"=>$bonus_id,'status'=>1))->find();
            if ($bonusInfo)
            {
                unset($bonusInfo['id']);
                $bonusInfo['send_time']=date("m-d H:i",$bonusInfo['send_time']) ;
                return $bonusInfo;
            }else{
                return array("status"=>0);
            }
        }else{
            return array("status"=>0);
        }
    }
    
}