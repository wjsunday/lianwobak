<?php
/**
 * 系统接口.
 *
 * @author Seven Du <lovevipdsw@vip.qq.com>
 **/
class VipApi extends Api
{
    
   

    public function GetPersonalVipDetail()
    {
        $u_id = $this->mid;
        if(model('VipPay')->isPersonalVip($u_id)){
            $info = M('vip')->join('ts_vip_price ON ts_vip.p_id = ts_vip_price.u_g_id')->field('content,vip_price,p_id,vid,due_time')->where("u_id={$u_id}")->find();
            $vip['pid']['0']['user_group_id'] = $info['p_id'];
            $vip['pid']['0']['vip_price'] = $info['vip_price'];
            $vip['pid']['0']['content'] = explode(',',$info['content']);
            $vip['pid']['0']['is_open'] = 1;
            $vip['pid']['0']['vid'] = $info['vid'];
            $vip['pid']['0']['time_validity'] = date('Y年m月d日',$vip['due_time']).'止';
            $user_group_name = M('user_group')->field('user_group_name')->where("user_group_id={$info['p_id']}")->find();
            $vip['pid']['0']['user_group_name'] = $user_group_name['user_group_name'].'服务';
            $vip['stages']['data_list'] = M('vip_stages')->field('id,content,number,discount')->where('is_personal=1')->select();
            $vip['stages']['title'] = '选择续费类型';
            if($info){
                $vip['status'] = 1;
                $vip['message'] = '成功';
                $vip['renew'] = 1;
                $data['isVip'] = model('VipPay')->isPersonalVip($u_id);
                return $vip;
            }else{
                self::error(array(
                    'status'  => 0,
                    'message' => '暂无数据',
                ));
            }

        }else{
            $vip['pid'] = M('user_group')->join('ts_vip_price ON ts_user_group.user_group_id = ts_vip_price.u_g_id')->where('is_vip = 1')->field('user_group_name,content,user_group_id,vip_price')->select(); //查询会员权限的分类

            foreach ($vip['pid'] as $key => $value) {
                // $aa['pid'][$key]['content'] = $value['content'];
                $vip['pid'][$key]['content'] = explode(',',$value['content']);
                $vip['pid'][$key]['user_group_name'] = $value['user_group_name'].'服务';
            }
            $vip['stages']['data_list'] = M('vip_stages')->field('id,content,number,discount')->where('is_personal=1')->select();
            
            $vip['stages']['title'] = '勾选更久会员';
            if(!$vip['pid'] || !$vip['stages']['data_list']){
                self::error(array(
                    'status'  => 0,
                    'message' => '暂无数据',
                ));
            }else{

                $vip['status'] = 1;
                $vip['message'] = '成功';
                $vip['renew'] = 0;
                $data['isVip'] = model('VipPay')->isPersonalVip($u_id);
                return $vip;
            }
            
        }
            
    }

    public function GetBusinessVipDetail()
    {
        $u_id = $this->mid;
        if(model('VipPay')->isBusinessVip($u_id)){
            $vip = M('vip')->join('ts_vip_price ON ts_vip.p_id = ts_vip_price.u_g_id')->field('vip_price,p_id,vid,Registration_time,due_time')->where("u_id={$u_id} and status=1")->find();
            $user_group_name = M('user_group')->field('user_group_name')->where("user_group_id={$vip['p_id']}")->find();
            $data['pid']['0']['vip_type'] = '您已成为链我商家'.$user_group_name['user_group_name'];
            $data['pid']['0']['vip_price'] = $vip['vip_price'];
            $data['pid']['0']['user_group_id'] = $vip['p_id'];
            $data['pid']['0']['vid'] = $vip['vid'];
            $data['pid']['0']['time_validity'] = date('Y年m月d日',$vip['due_time']).'止';
            $data['pid']['0']['is_open'] = 1;
            $data['stages']['data_list'] = M('vip_stages')->field('id,content,number,discount')->where('is_personal=2')->select();
            $img = M('ad_user')->field('img,ad_id')->where("uid={$u_id} and place=3")->find();
            if($img){
                $attachInfo = model('Attach')->getAttachById($img['img']);
                $data['ad_img'] = getImageUrl($attachInfo['save_path'].$attachInfo['save_name']);
            }else{
                $data['ad_img'] = null;
            }
            if($vip){
                $data['status'] = 1;
                $data['message'] = '成功';
                $data['renew'] = 1;
                $data['isVip'] = model('VipPay')->isBusinessVip($u_id);
                return $data;
            }else{
                self::error(array(
                    'status'  => 0,
                    'message' => '暂无数据',
                ));
            }
        }else{
            $vip['pid'] = M('user_group')->join('ts_vip_price ON ts_user_group.user_group_id = ts_vip_price.u_g_id')->where('is_business_vip = 1')->field('user_group_name,content,user_group_id,vip_price')->select(); //查询会员权限的分类

            foreach ($vip['pid'] as $key => $value) {
                // $aa['pid'][$key]['content'] = $value['content'];
                $vip['pid'][$key]['content'] = explode(',',$value['content']);
                $vip['pid'][$key]['user_group_name'] = $value['user_group_name'].'服务';
            }
            // dump($vip);exit;
            $vip['stages']['data_list'] = M('vip_stages')->field('id,content,number,discount')->where('is_personal=2')->select();
            $vip['stages']['title'] = '勾选更久会员';
            if(!$vip['pid'] || !$vip['stages']['data_list']){
                self::error(array(
                        'status'  => 0,
                        'message' => '暂无数据',
                    ));
            }else{

                $vip['status'] = 1;
                $vip['message'] = '成功';
                $vip['renew'] = 0;
                $data['isVip'] = model('VipPay')->isBusinessVip($u_id);
                return $vip;
            }
        }

    }

    //上传红包广告
    public function upload(){
        $d['attach_type'] = 'vipad_image';
        $d['upload_type'] = 'image';
        $GLOBALS['fromMobile'] = true;
        $info = model('Attach')->upload($d, $d);
        if ($info['status']){
            $data['attach_id'] = implode(',',getSubByKey($info['info'], 'attach_id'));

            $data['status'] = 1;
            $data['message']="上传成功";
        }else{
            $data['status'] = 0;
            $data['message']="上传失败，请重新上传"  ;
        }

        return $data;
    }

    //添加红包广告
    public function addBonusAd(){
        if(!model('VipPay')->isBusinessVip($this->mid)){
            $data['status'] = 0;
            $data['message']="非法操作";
        }
        $attach_id['img'] = $this->data['attach_id'];
        $attach_id['mtime'] = time();
        $res = M('ad_user')->where("uid={$this->mid} and place=3")->save($attach_id);
        if($res){
            $data['status'] = 1;
            $data['message']="操作成功";
        }else{
            unset($attach_id['mtime']);
            $attach_id['uid'] = $this->mid;
            $attach_id['place'] = 3;
            $attach_id['is_active'] = 1;
            $attach_id['ctime'] = time();
            $res = M('ad_user')->add($attach_id);
            if($res){
                $data['status'] = 1;
                $data['message']="操作成功";
            }else{
                $data['status'] = 0;
                $data['message']="操作失败";
            }
        }
        return $data;
    }

    /*
     * 充值，创建一个订单
    */
    public function createCharge()
    {
        $type = intval($this->data['charge_type']);
        $order_id = intval($this->data['serial_number_id']);

        $types = array('alipay', 'weixin');
        if (!isset($types[$type])) {
            return array('status' => 0, 'message' => '充值方式不支持');
        }
        $version = intval($this->data['version']) ?: 1; //版本   1-系统版  2-直播版
        if ($version == 1) {
            $chargeConfigs = model('Xdata')->get('admin_Config:charge');
        } elseif ($version == 2) {
            $chargeConfigs = model('Xdata')->get('admin_Config:ZBcharge');
        } else {
            return array('status' => 0, 'message' => '参数错误');
        }
        if (!in_array($types[$type], $chargeConfigs['charge_platform'])) {
            return array('status' => 0, 'message' => '充值方式不支持');
        }

        $charge_type['charge_type'] = $type;
        $type = M('vip')->where("vid={$order_id}")->save($charge_type);
        $data = M('vip')->where("vid={$order_id}")->find();
        
        if ($data) {
            if ($data['charge_type'] == 0) {
                $configs = $parameter = array();
                $configs['partner'] = $chargeConfigs['alipay_pid'];
                $configs['seller_id'] = $chargeConfigs['alipay_pid'];
                $configs['seller_email'] = $chargeConfigs['alipay_email'];
                $configs['sign_type'] = 'RSA';
                $configs['private_key_path'] = $chargeConfigs['private_key_path'];
                $parameter = array(
                    'app_id'     => $chargeConfigs['alipay_app_pid'],
                    'method'     => 'alipay.trade.app.pay',
                    'charset'    => 'utf-8',
                    'sign_type'  => 'RSA',
                    'timestamp'  => date('Y-m-d H:i:s'),
                    'version'    => '1.0',
                    'notify_url' => SITE_URL.'/alipay_notify_api.php',
                );
                $parameter['biz_content'] = '{'.
                    '"subject":"充值:'.$data['vip_name'].'",'.
                    '"out_trade_no":"'.$data['serial_number'].'",'.
                    '"total_amount":"'.$data['charge_value'].'",'.
                    '"seller_id":"'.$chargeConfigs['alipay_pid'].'",'.
                    '"product_code":"QUICK_MSECURITY_PAY"'.
                    '}';

                $url['url'] = createAlipayUrl($configs, $parameter, 3); //直接返回支付宝支付url
                $url['charge_type'] = $data['charge_type'];
                $url['charge_value'] = $data['charge_value'];
                $url['out_trade_no'] = $data['serial_number'];

                return array(
                    'status' => 1,
                    'message' => '',
                    'data'   => $url,
                );
            } elseif ($data['charge_type'] == 1) {
                $ip = get_client_ip(); //微信支付需要终端ip
                $order = array(
                    'body'             => '充值:'.$data['vip_name'],
                    'appid'            => $chargeConfigs['weixin_pid'],
                    'device_info'      => 'APP',
                    'mch_id'           => $chargeConfigs['weixin_mid'],
                    'nonce_str'        => mt_rand(),
                    'notify_url'       => SITE_URL.'/weixin_vip_notify_api.php',
                    'out_trade_no'     => $data['serial_number'],
                    'spbill_create_ip' => $ip,
                    'total_fee'        => $data['charge_value'] * 100, //这里的最小单位是分，跟支付宝不一样。1就是1分钱。只能是整形。
                    'trade_type'       => 'APP',
                    ); //预支付订单
                $weixinpay = new WeChatPay();

                $input = $weixinpay->getPayParam($order, $chargeConfigs['weixin_pid'], $chargeConfigs['weixin_mid'], $chargeConfigs['weixin_key'], 2);

                $input['out_trade_no'] = $data['serial_number'];
                $input['charge_type'] = $data['charge_type'];
                $input['charge_value'] = intval($data['charge_value']);

                return array(
                    'status' => 1,
                    'message' => '',
                    'data'   => $input,
                    
                );
            }

        } else {
            $res = array();
            $res['status'] = 0;
            $res['message'] = '充值创建失败';

            return $res;
        }
    }


    /*
     * 充值，创建一个订单
    */
    public function UbankPay(){
      
        $order_id = intval($this->data['serial_number_id']);
        $charge_type['charge_type'] = 2;
        $type = M('vip')->where("vid={$order_id}")->save($charge_type);
        $data = M('vip')->where("vid={$order_id}")->find();
        if ($data) {
            //零用钱充值
            // var_dump($_POST);
            $paw = $this->data['paw'];
            $u_id = $this->mid;
            $user_paw = M('user')->field('ppasswd')->where("uid = $u_id")->find();
            // var_dump($paw,$user_paw);exit;
            $u_paw = $user_paw['ppasswd'];
            if(!$u_paw){
                return array('status' => 5, 'message' => '未设置支付密码');
            }

            if(!empty($paw) && $paw == $u_paw){

                $price = $data['charge_value'];
                $ubank = M('credit_user')->field('ubank')->where("uid = $u_id")->find();
                $uu = $ubank['ubank'];

                if($uu < $price){
                    return array('status' => 3, 'message' => '余额不足，请选择其他方式充值');
                }
                $credit = M('credit_user')->where("uid={$data['u_id']}")->save(array('ubank' => $uu - $data['charge_value']));
                if($credit){
                    
                    $res = model('VipPay')->charge_success($data['serial_number']);
                    if($res){
                        return array('status' => 1, 'message' => '开通成功');
                    }else{
                        return array('status' => 0, 'message' => '开通失败');
                    }
                }else{
                    return array('status' => 0, 'message' => '开通失败');
                }

            }else{
                return array('status' => 4, 'message' => '密码不正确');
            }
        }else{

            $res = array();
            $res['status'] = 0;
            $res['message'] = '充值失败';

            return $res;
        }

    }

    /*
        ios 充值 直接返回一个url
     */
    public function createChargeIOS()
    {
        $type = intval($this->data['charge_type']);
        $order_id = intval($this->data['serial_number_id']);
        $types = array('alipay', 'weixin');
        if (!isset($types[$type])) {
            return array('status' => 0, 'message' => '充值方式不支持');
        }
        $version = intval($this->data['version']) ?: 1; //版本   1-系统版  2-直播版
        if ($version == 1) {
            $chargeConfigs = model('Xdata')->get('admin_Config:charge');
        } elseif ($version == 2) {
            $chargeConfigs = model('Xdata')->get('admin_Config:ZBcharge');
        } else {
            return array('status' => 0, 'message' => '参数错误');
        }
        if (!in_array($types[$type], $chargeConfigs['charge_platform'])) {
            return array('status' => 0, 'message' => '充值方式不支持');
        }

        $charge_type['charge_type'] = $type;
        $type = M('vip')->where("vid={$order_id}")->save($charge_type);
        $data = M('vip')->where("vid={$order_id}")->find();

        if ($data) {
            if ($data['charge_type'] == 0) {//支付宝支付
                $configs = $parameter = array();
                $configs['partner'] = $chargeConfigs['alipay_pid'];
                $configs['seller_id'] = $chargeConfigs['alipay_pid'];
                $configs['seller_email'] = $chargeConfigs['alipay_email'];
                $configs['key'] = $chargeConfigs['alipay_key'];
                $parameter = array(
                    'notify_url'   => SITE_URL.'/alipay_notify_api.php',
                    'out_trade_no' => $data['serial_number'],
                    'subject'      => '充值:'.$data['vip_name'],
                    'total_fee'    => $data['charge_value'],
                    'body'         => '',
                    'payment_type' => 1,
                    'service'      => 'mobile.securitypay.pay',
                    'it_b_pay'     => '1c',
                );
                $url = createAlipayUrl($configs, $parameter, 2); //直接返回支付宝支付url
            } elseif ($data['charge_type'] == 1) {
                $ip = get_client_ip(); //微信支付需要终端ip
                $order = array(
                    'body'             => '充值:'.$data['vip_name'],
                    'appid'            => $chargeConfigs['weixin_pid'],
                    'device_info'      => 'APP',
                    'mch_id'           => $chargeConfigs['weixin_mid'],
                    'nonce_str'        => mt_rand(),
                    'notify_url'       => SITE_URL.'/weixin_vip_notify_api.php',
                    'out_trade_no'     => $data['serial_number'],
                    'spbill_create_ip' => $ip,
                    'total_fee'        => $data['charge_value'] * 100, //这里的最小单位是分，跟支付宝不一样。1就是1分钱。只能是整形。
                    'trade_type'       => 'APP',
                    ); //预支付订单
                $weixinpay = new WeChatPay();

                $url['url'] = $weixinpay->getPayParam($order, $chargeConfigs['weixin_pid'], $chargeConfigs['weixin_mid'], $chargeConfigs['weixin_key'], 1);
                $url['out_trade_no'] = $data['serial_number'];
            }

            return array(
                'status' => 1,
                'message' => '',
                'data'   => $url,
            );
        } else {
            $res = array();
            $res['status'] = 0;
            $res['message'] = '充值创建失败';

            return $res;
        }
    }

    //调用支付后的返回验证 验证通过则加积分
    //支付的回调不能跳转  输出success 给支付宝
    public function alipayNotify()
    {
        unset($_GET['app'], $_GET['mod'], $_GET['act']);
        unset($_REQUEST['app'], $_REQUEST['mod'], $_REQUEST['act']);
        header('Content-type:text/html;charset=utf-8');
        $chargeConfigs = model('Xdata')->get('admin_Config:charge');
        if ($_POST['sign_type'] == 'RSA') {
            $configs = array(
                'partner'           => $chargeConfigs['alipay_pid'],
                'seller_id'         => $chargeConfigs['alipay_pid'],
                'seller_email'      => $chargeConfigs['alipay_email'],
                'alipay_public_key' => $chargeConfigs['alipay_public_key'],
                'sign_type'         => 'RSA',
            );
        } else {
            $configs = array(
                'partner'      => $chargeConfigs['alipay_pid'],
                'seller_id'    => $chargeConfigs['alipay_pid'],
                'seller_email' => $chargeConfigs['alipay_email'],
                'key'          => $chargeConfigs['alipay_key'],
            );
        }

        if (verifyAlipayNotify($configs)) {
            model('VipPay')->charge_success(t($_POST['out_trade_no']));
        }
        exit;
    }

    //微信验证方法
    public function weixinNotify()
    {
        unset($_GET['app'], $_GET['mod'], $_GET['act']);
        unset($_REQUEST['app'], $_REQUEST['mod'], $_REQUEST['act']);
        $chargeConfigs = model('Xdata')->get('admin_Config:charge');
        $weixinpay = new WeChatPay();
        $result = $weixinpay->notifyReturn($chargeConfigs['weixin_key']);
        if ($result) {
            model('VipPay')->charge_success(t($result->out_trade_no));
        }
        exit;
    }

    //客户端拿到订单号 检查订单状态
    public function checkChage()
    {
        $map['serial_number'] = $this->data['out_trade_no'];
        if (!$map['serial_number']) {
            return array('status' => 0, 'message' => '参数错误');
        }

        $status = D('credit_charge')->where($map)->getField('status');
        if ($status == 1) {
            return array('status' => 1, 'message' => '充值成功');
        } else {
            return array('status' => 0, 'message' => '充值失败');
        }
    }

  //这个类里的参数返回跟其他接口不一致、、、message..

    public function saveCharge()
    {
        $number = (string) $this->data['serial_number'];
        $status = intval($this->data['status']);
        $sign = (string) $this->data['sign'];
        $verify = md5($number.'&'.$status.'&'.md5(C('SECURE_CODE')));
        if ($number && $sign && ($status == 1 || $status == 2) && $sign == $verify) {
            if ($status == 1) {
                if (model('VipPay')->charge_success(t($number))) {
                    return array('status' => 1, 'message' => '保存成功');
                }
            } else {
                $map = array(
                    'u_id'           => $this->mid,
                    'serial_number' => t($number),
                    'status'        => 0, // 这个条件不能删，删了就有充值漏洞
                );
                if (D('credit_charge')->where($map)->setField('status', 2)) {
                    return array('status' => 1, 'message' => '保存成功');
                }
            }

            return array('status' => 0, 'message' => '保存失败');
        } else {
            return array('status' => 0, 'message' => '参数错误');
        }
    }


    //充值接口统一下单
    public function createOrder()
    {   
        $priceId['u_g_id'] = $this->data['priceId'];
        $stagesId = $this->data['stagesId'];
        $Price = M('vip_price')->field('vip_price,u_g_id')->where($priceId)->find();
        $price = $Price['vip_price'];
        $pid = $Price['u_g_id'];
        if($stagesId == '-1'){
            $discount = 1;
            $stages['is_personal'] = intval($this->data['userType']);
            $num = 1;
        }else{
            $stages = M('vip_stages')->field('number,discount,is_personal')->where("id={$stagesId}")->find();
            $num = $stages['number'];
            $discount = $stages['discount'];
        }
        if($stages['is_personal'] == 2){

            $user_group_link = M('user_group_link')->where("uid={$this->mid} and user_group_id=6")->find();
            if(!$user_group_link){
                return array('status'=>0,'message'=>'你不是商家用户，请先认证');
            }
        }
        // elseif ($stages['is_personal'] == 1) {
        //     $user_group_link = M('user_group_link')->where("uid={$this->mid} and user_group_id=5")->find();
        //     if(!$user_group_link){
        //         return array('status'=>0,'message'=>'你不是实名用户，请先认证');
        //     }
        // }
        //开通
        if($this->data['renew'] == 0){
            if(model('VipPay')->isBusinessVip($this->mid)){
                return array('status'=>0,'message'=>'您已是商家会员');
            }
            if(model('VipPay')->isPersonalVip($this->mid)){
                return array('status'=>0,'message'=>'您已是个人会员');
            }
            $userGroup = M('user_group')->field('user_group_name')->where("user_group_id={$pid}")->find();
            $vip_name = $userGroup['user_group_name'];
            $data['charge_value'] =$data['vip_Price'] = $price * $discount * $num;
            $data['p_id'] = $pid;
            $data['vip_name'] = $vip_name;
            $data['number'] = $num;
            $data['u_time'] = time();
            $data['status'] = 0;
            $vvvip = M('vip')->field('vid')->where("u_id={$this->mid} and status != 1")->find();

            if($vvvip){
                $result = D('vip')->where("u_id={$this->mid} and vid={$vvvip['vid']}")->save($data);
                $vid = $vvvip['vid'];
            }else{
                $data['serial_number'] = time().rand(1000,9999).rand(1,9).rand(1,9).rand(1,9);
                $data['is_personal'] = $stages['is_personal'];
                $data['u_id'] = $this->mid;
                $result = $vid = D('vip')->add($data);
            }

            if ($result) {
                $data['result'] = $vid;

                return  array(
                    'status' => 1,
                    'message' => '创建订单成功',
                    'data'   => $data,
                );
            } else {
                return array(
                    'status' => 0,
                    'message' => '创建订单失败',
                );
            }
        }elseif ($this->data['renew'] == 1) {
            $map['number'] = $num;
            $map['u_time'] = time();
            $map['charge_value'] =$data['vip_Price'] = $price * $discount * $num;
            $result = M('vip')->where("u_id={$this->mid} and status=1")->save($map);
            $res = M('vip')->field('vid')->where("u_id={$this->mid} and status=1")->find();
            if ($result) {
                $data['result'] = $res['vid'];
                return  array(
                    'status' => 1,
                    'message' => '创建订单成功',
                    'data'   => $data,
                );
            } else {
                return array(
                    'status' => 0,
                    'message' => '创建订单失败',
                );
            }
        }
            
    }  


} 
