<?php
/**
 * app 提现充值模块
 * bs.
 */
use Ts\Models as Model;

class ApplicationUbankApi extends Api
{
    //加密key
    protected $key = 'ThinkSNS';

    //数据统一返回格式
    private function rd($data = '', $message = 'ok', $status = 1)
    {
        return array(
            'data'   => $data,
            'message'    => $message,
            'status' => $status,
        );
    }

    //获取版本号 用于app获取更新配置
    public function getVersion()
    {
        $info = model('Xdata')->get('admin_Application:ZB_config');
        if (!empty($info['version'])) {
            $version = $info['version'];
        } else {
            $version = 1; //未配置  初始版本
        }

        return $this->rd($version);
    }

    //获取支付相关配置
    public function getZBConfig()
    {
        //获取配置简单加密
        $key = $this->data['key'];
        // if (md5($this->key) != $key) {
        //     return $this->rd('', '认证失败', 1);
        // }
        $chongzhi_info = model('Xdata')->get('admin_Config:charge');
        $info['cash_exchange_ratio_list'] = getExchangeConfig('cash');
        $info['charge_ratio'] = $chongzhi_info['charge_ratio'] ?: '100'; //1人民币等于多少零用钱
        $info['charge_description'] = $chongzhi_info['description'] ?: '充值描述'; //充值描述
        $field = $this->data['field']; //关键字  不传为全部
        if ($field) {
            $field = explode(',', $field);
            foreach ($info as $key => $value) {
                if (!in_array($key, $field)) {
                    unset($info[$key]);
                }
            }
        }

        return $this->rd($info);
    }

    //生成提现订单号
    private function getOrderId()
    {
        //暂用这种简单的订单号生成办法。。。。请求密集时可能出现订单号重复？
        $number = date('YmdHis').rand(1000, 9999);

        return $number;
    }

    /**
     * 发布提现申请.
     */
    public function createOrder()
    {
        $data['order_number'] = $this->getOrderId();
        $data['uid'] = $map['uid'] = $this->mid;

        if ($accountinfo['status'] == 1) {
            return $this->rd('', '请先绑定提现账户', 0);
        }
        $data['account'] = $map['account'] = $this->data['account'];
        $data['type'] = $map['type'] = intval($this->data['type']); //绑定获取
        $info = M('user_account')->where($map)->find();
        // var_dump($info);exit;
        if (!$data['account']) {

             return $this->rd('','请填写提现账户',0);
         }
        if (!$info) {
            return $this->rd('', '未绑定账户', 0);
        }
        $data['gold'] = floatval($this->data['gold']);
        $data['amount'] = floatval($data['gold'] - $data['gold']*0.01);
        $data['ctime'] = time();
         
         if (!$data['gold']) {
             return $this->rd('', '请填写提现金额', 0);
         }
         if($data['gold'] < 100){
            return $this->rd('', '提现金额不能小于100元', 0);
         }
        $ubank = D('credit_user')->where(array('uid' => $this->mid))->getField('ubank');
        if ($ubank < $data['gold']) {
            return $this->rd('', '零用钱不足', 0);
        }
        $paw = $this->data['paw'];
        $user_paw = M('user')->field('ppasswd')->where("uid={$this->mid}")->find();
        $u_paw = $user_paw['ppasswd'];
        if(!$u_paw){
            return $this->rd('', '未设置支付密码', 5);
        }
        if(!empty($paw) && $paw == $u_paw){
            $info = Model\CreditOrder::insert($data);
            if ($info) {
                $record['serial_number'] = $data['order_number'];
                $record['cid'] = 0; //没有对应的零用钱规则
                $record['type'] = 3; //3-提现
                $record['charge_type'] = 2; 
                $record['uid'] = $this->mid;
                $record['action'] = '用户提现';
                $record['des'] = '';
                $record['change'] = $data['gold']; //提现申请扣零用钱   如果驳回再加回来
                $record['ctime'] = time();
                $record['detail'] = json_encode(array('ubank' => '-'.$data['gold']));
                D('credit_record')->add($record);
                D('credit_user')->setDec('ubank', 'uid='.$this->mid, $data['gold']);
                D('Credit')->cleanCache($this->mid);

                return $this->rd('', '提交成功请等待审核', 1);
            } else {
                return $this->rd('', '保存失败，请稍后再试', 0);
            }
        }else{

            return $this->rd('', '密码不正确', 4);
        }    
    }

    /**
     * 绑定/解绑账户
     * bs.
     */
    public function setUserAccount()
    {
        $status = intval($this->data['status']) ?: 1; //type 1-绑定 2-解绑
        if ($status == 1) {
            $data['account'] = $this->data['account'];
            if (!$data['account']) {
                return $this->rd('', '请输入需要绑定的账户', 0);
            }
            $data['type'] = intval($this->data['type']) ?: 1; //1-支付宝 2-微信 3-银行卡
            
            $data['uid'] = $this->mid;
            $isType = M('user_account')->where("uid={$this->mid} and type={$data['type']}")->find();
            if($isType){
                return $this->rd('', '已有绑定账号', 0);
            }
            $data['ctime'] = time();
            if($data['type'] == 3)
            {
                $data['name'] = $this->data['name'];//开户名
                $data['bank'] = $this->data['bank'];//开户分行  
                $data['phone'] = $this->data['phone'];//预留手机号  
                $data['address'] = $this->data['address'];//地址
                if(!$data['name']) return $this->rd('', '开户名不能为空', 0);
                if(!$data['bank']) return $this->rd('', '开户分行不能为空', 0);
                if(!$data['phone']) return $this->rd('', '预留手机号不能为空', 0);
                if(!$data['address']) return $this->rd('', '地址不能为空', 0);
            }
            $info = Model\UserAccount::insert($data);
            if ($info) {
                return $this->rd('', '绑定成功', 1);
            } else {
                return $this->rd('', '绑定失败，请稍后再试', 0);
            }
        } else {
            if (!Model\UserAccount::find($this->mid)) {
                return $this->rd('', '未绑定账户', 0);
            }
            $info = M('user_account')->where("uid={$this->mid} and type={$this->data['type']}")->delete();
            if ($info) {
                return $this->rd('', '解绑成功', 1);
            } else {
                return $this->rd('', '操作失败，请稍后再试', 0);
            }
        }
    }

    /**
     * 查看提现账户.
     */
    public function getUserAccount()
    {
        // $info = Model\UserAccount::find($this->mid);
        $info = M('user_account')->field('account,type')->where("uid={$this->mid}")->select();
        if(!$info){
            return array('status'=>0,'message'=>'没有绑定账号');
        }
        foreach ($info as $key => $value) {
            switch ($value['type']) {
                case '1':
                    $data['alipay']['account'] = $value['account'];
                    $data['alipay']['type'] = $value['type'];
                    break;

                case '2':
                    $data['weChat']['account'] = $value['account'];
                    $data['weChat']['type'] = $value['type'];
                    break;  
            }
        }
        return $this->rd($data,'操作成功',1);
    }

    public function test()
    {
        $order = $this->getOrderId();

        return $order;
    }
}
