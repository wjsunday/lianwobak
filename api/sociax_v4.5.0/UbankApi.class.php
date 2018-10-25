<?php
/**
 * @author jason
 */
use apps\Common\Extend\Pay\Alipay\aop\AopClient;
use apps\Common\Extend\Pay\Alipay\aop\request\AlipayTradeAppPayRequest;

class UbankApi extends Api
{
    
    /*
     * 充值，创建一个订单
    */
    public function createCharge()
    {
        $orderinfo = $this->setOrder();
        if ($orderinfo['status'] == 0) {
            return array('status' => 0, 'message' => $orderinfo['message']);
        }

        $data = $orderinfo['data'];
        $chargeConfigs = $orderinfo['config'];

        if ($data['result']) {
            $data['charge_id'] = $data['result'];
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
                    '"subject":"零用钱充值:'.$data['charge_ubank'].'元",'.
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
                    'body'             => '零用钱充值:'.$data['charge_ubank'].'零用钱',
                    'appid'            => $chargeConfigs['weixin_pid'],
                    'device_info'      => 'APP',
                    'mch_id'           => $chargeConfigs['weixin_mid'],
                    'nonce_str'        => mt_rand(),
                    'notify_url'       => SITE_URL.'/weixin_ubank_notify_api.php',
                    'out_trade_no'     => $data['serial_number'],
                    'spbill_create_ip' => $ip,
                    'total_fee'        => $data['charge_value'] * 100, //这里的最小单位是分，跟支付宝不一样。1就是1分钱。只能是整形。
                    'trade_type'       => 'APP',
                    ); //预支付订单
                $weixinpay = new WeChatPay(); 

                $input = $weixinpay->getPayParam($order, $chargeConfigs['weixin_pid'], $chargeConfigs['weixin_mid'], $chargeConfigs['weixin_key'], 2);

                $input['out_trade_no'] = $data['serial_number'];
                $input['charge_type'] = $data['charge_type'];
                $input['charge_value'] = $data['charge_value'];
                $input['packagevalue'] = 'Sign=WXPay';
                return array(
                    'status' => 1,
                    'message' => '充值创建成功',
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
        ios 充值 直接返回一个url
     */
    public function createChargeIOS()
    {
        $orderinfo = $this->setOrder();
        if ($orderinfo['status'] == 0) {
            return array('status' => 0, 'message' => $orderinfo['message']);
        }

        $data = $orderinfo['data'];
        $chargeConfigs = $orderinfo['config'];

        if ($data['result']) {
            $data['charge_id'] = $data['result'];
            if ($data['charge_type'] == 0) {//支付宝支付
                $configs = $parameter = array();
                $configs['partner'] = $chargeConfigs['alipay_pid'];
                $configs['seller_id'] = $chargeConfigs['alipay_pid'];
                $configs['seller_email'] = $chargeConfigs['alipay_email'];
                $configs['key'] = $chargeConfigs['alipay_key'];
                $parameter = array(
                    'notify_url'   => SITE_URL.'/alipay_notify_api.php',
                    'out_trade_no' => $data['serial_number'],
                    'subject'      => '零用钱充值:'.$data['charge_ubank'].'零用钱',
                    'total_fee'    => $data['charge_value'],
                    'body'         => '',
                    'payment_type' => 1,
                    'service'      => 'mobile.securitypay.pay',
                    'it_b_pay'     => '1c',
                );
               // $url = createAlipayUrl($configs, $parameter, 2); //直接返回支付宝支付url
                $aliPayObj = $this->getAliPayObj();
                $request = new AlipayTradeAppPayRequest();
                //SDK已经封装掉了公共参数，这里只需要传入业务参数
                $bizcontent = "{\"body\":\"零用钱充值\","
                		. "\"subject\": \"零用钱充值:{$data['charge_value']}\","
                		. "\"out_trade_no\": \"{$data['serial_number']}\","
                		. "\"timeout_express\": \"30m\","
                		. "\"total_amount\": \"{$data['charge_value']}\","
                		. "\"product_code\":\"QUICK_MSECURITY_PAY\""
                		. "}";
                $request->setNotifyUrl($parameter['notify_url']);
                $request->setBizContent($bizcontent);
                //这里和普通的接口调用不同，使用的是sdkExecute
                $response = $aliPayObj->sdkExecute($request);
                //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
                $url = 'alipay://alipayclient/?'.urlencode(json_encode(array('requestType' => 'SafePay', 'fromAppUrlScheme' => 'com.zhiyiThinkSNS4', 'dataString' => $response))); //直接返回支付宝支付url
            } elseif ($data['charge_type'] == 1) {
                $ip = get_client_ip(); //微信支付需要终端ip
                $order = array(
                    'body'             => '零用钱充值:'.$data['charge_ubank'].'零用钱',
                    'appid'            => $chargeConfigs['weixin_pid'],
                    'device_info'      => 'APP',
                    'mch_id'           => $chargeConfigs['weixin_mid'],
                    'nonce_str'        => mt_rand(),
                    'notify_url'       => SITE_URL.'/weixin_ubank_notify_api.php',
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
    
    /**
     * 初始化支付对象-支付宝
     * @return Alipay
     */
    private function getAliPayObj(){
    	$aop = new AopClient;
    	$aop->gatewayUrl = ALI_PAY_GATE_WAY_URL;
    	$aop->appId = ALI_PAY_APP_ID;
    	$aop->rsaPrivateKey = ALI_PAY_MERCHANT_PRIVATE_KEY;
    	$aop->format = "json";
    	$aop->charset = ALI_PAY_CHARSET;
    	$aop->signType = ALI_PAY_SIGN_TYPE;
    	$aop->alipayrsaPublicKey = ALI_PAY_ALIPAY_PUBLIC_KEY;
    	
    	return $aop;
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
            model('Ubank')->charge_success(t($_POST['out_trade_no']));
        }
        exit;
    }

    //微信验证方法
    public function weixinNotify()
    {
        //@file_put_contents('./log.txt',json_encode($_REQUEST));
        //return array("status"=>1);

        unset($_GET['app'], $_GET['mod'], $_GET['act']);
        unset($_REQUEST['app'], $_REQUEST['mod'], $_REQUEST['act']);
        $chargeConfigs = model('Xdata')->get('admin_Config:charge');
        $weixinpay = new WeChatPay();
        $result = $weixinpay->notifyReturn($chargeConfigs['weixin_key']);
        if ($result) {
            model('Ubank')->charge_success(t($result->out_trade_no));
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
                if (model('Ubank')->charge_success(t($number))) {
                    return array('status' => 1, 'message' => '保存成功');
                }
            } else {
                $map = array(
                    'uid'           => $this->mid,
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

    //?? 啥用的 -> 谢伟20150925
    public function save_charge()
    {
        $data['charge_value'] = floatval($_REQUEST['charge_value']);
        $data['charge_ubank'] = floatval($_REQUEST['charge_ubank']);

// 		dump(WxPayConf_pub::APPID);
// 		dump(WxPayConf_pub::MCHID);
// 		dump(WxPayConf_pub::KEY);
// 		dump(WxPayConf_pub::APPSECRET);
// 		dump(WxPayConf_pub::NOTIFY_URL);

        $out_trade_no = $_REQUEST['out_trade_no'];
        empty($out_trade_no) && $out_trade_no = 'e2e5096d574976e8f115a8f1e0ffb52b';

        // 使用订单查询接口
        $orderQuery = new OrderQuery_pub();
        $orderQuery->setParameter('out_trade_no', "$out_trade_no"); // 商户订单号

        // 获取订单查询结果
        $orderQueryResult = $orderQuery->getResult();

        // 商户根据实际情况设置相应的处理流程,此处仅作举例
        if ($orderQueryResult['return_code'] == 'FAIL') {
            return array(
                    'status' => 0,
                    'msg'    => '通信出错：'.$orderQueryResult['return_msg'],
            );
        } elseif ($orderQueryResult['result_code'] == 'FAIL') {
            return array(
                    'status' => 0,
                    'msg'    => '错误代码：'.$orderQueryResult['err_code'].' '.'错误代码描述：'.$orderQueryResult['err_code_des'],
            );
        } elseif ($data['charge_value'] != $orderQueryResult['total_fee']) {
            return array(
                    'status' => 0,
                    'msg'    => '对账失败',
            );
        }

        $data['serial_number'] = t($_REQUEST['serial_number']);
        $data['uid'] = $this->mid;

        // TODO 以下信息海全需要从积分通接口取
        $data['charge_order'] = t($_REQUEST['charge_order']);
        $data['charge_type'] = intval($_REQUEST['charge_type']);

        $data['ctime'] = intval($_REQUEST['ctime']);
        $data['status'] = intval($_REQUEST['status']);

        M('credit_charge')->add($data);

        $des['content'] = '充值了'.$data['charge_ubank'].'零用钱';
        model('Credit')->setUserCredit($data['uid'], array(
                'name'  => 'credit_charge',
                'ubank' => $data['charge_value'],
        ), 1, $des);

        return array(
                'status' => 1,
                'msg'    => '充值成功',
        );
    }

    //购买零用钱
    public function get_chargeUbank()
    {
        $arr['data'] = array(
                array(
                        'value' => 10,
                        'ubank' => 10,

                ),
                array(
                        'value' => 30,
                        'ubank' => 30,
                ),
                array(
                        'value' => 50,
                        'ubank' => 50,
                ),
                array(
                        'value' => 100,
                        'ubank' => 100,
                ),
                array(
                        'value' => 200,
                        'ubank' => 200,
                ),
        );
        $arr['status'] = 1;
        $arr['measge'] = '';
        return $arr;
    }


    /*
     * 转账
    */
    public function transfer()
    {
        $paw = $this->data['paw'];
        $data['fromUid'] = $this->mid;
        $data['toUid'] = $this->data['to_uid'];
        $data['num'] = $this->data['money'];
        $data['desc'] = t($this->data['desc']);
        $user['phone'] = $this->data['mobile'];
        $user1['uname'] = t($this->data['to_uname']);
        $data['order_num'] = 'UBANK'.time().rand(1000, 9999);
        $data['type'] = 4;
        $data['charge_type'] = 2;
        $user = M('user')->where("phone={$user['phone']} and uid={$data['toUid']}")->find();
        $toUname = M('user_verified')->field('realname')->where("uid={$data['toUid']} and verified=1")->find();
        // var_dump($user);exit;
        $user_paw = M('user')->field('ppasswd')->where("uid={$this->mid}")->find();
        $u_paw = $user_paw['ppasswd'];
        if(!$u_paw){
            return array('status' => 5, 'message' => '未设置支付密码');
        }
        if(!empty($paw) && $paw == $u_paw){
            if(!$toUname){
                return array('status' => 0, 'message' => '收款人没有实名认证');
            }
            if(t($toUname['realname']) != $user1['uname']){
                return array('status' => 0, 'message' => '收款人真实姓名不一致');
            }
            if(!$user){

                return array('status' => 0, 'message' => '收款人手机号错误，请核对后再转');
            }
            if ($data['toUid'] && $data['num'] > 0) {

                $result = model('Ubank')->startTransfer_ubank($data);
            } else {
                $result = false;
            }
            if($result){
                self::success(
                    array(
                            'status' => 1,
                            'message' =>'零用钱转账成功！'
                        )
                    );
            }else{
                self::error(
                    array(
                            'status' => 0,
                            'message' =>'零用钱转账失败！'
                        )
                    );
                 }
        }else{

            return array('status' => 4, 'message' => '密码不正确');
        }
            

    }

    //充值接口统一下单
    public function setOrder()
    {
        $price = intval($this->data['money']);
        if ($price < 1) {
            return array('status' => 0, 'message' => '充值金额不正确');
        }
        $type = intval($this->data['charge_type']);
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
        $data['uid'] = $this->mid;
        $data['serial_number'] = 'CZ'.date('YmdHis').rand(0, 9).rand(0, 9);
        $data['uid'] = $this->mid;
        $data['serial_number'] = 'CZ'.date('YmdHis').rand(0, 9).rand(0, 9);
        $data['charge_type'] = $type;
        $data['charge_value'] = $price;
        $data['ctime'] = time();
        $data['status'] = 0;
        $data['sta'] = 1;
        $data['charge_ubank'] = $price;
        $data['charge_order'] = '';
        $result = D('credit_charge')->add($data);
        if ($result) {
            $data['result'] = $result;

            return  array(
                'status' => 1,
                'data'   => $data,
                'config' => $chargeConfigs,
            );
        } else {
            return array(
                'status' => 0,
                'message' => '创建订单失败',
            );
        }
    }
}
