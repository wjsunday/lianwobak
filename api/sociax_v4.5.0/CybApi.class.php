<?php
/**
 * @author jason
 */
class CybApi extends Api
{
    
    /*
     * 充值，创建一个订单
    */
    public function createCharge()
    {
        $order_id = intval($this->data['serial_number_id']);
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
        $charge_type['charge_type'] = $type;
        D('credit_charge')->where("charge_id={$order_id}")->save($charge_type);
        $data = D('credit_charge')->where("charge_id={$order_id}")->find();

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
                    '"subject":"财源币充值:'.$data['charge_cyb'].'个",'.
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
                    'body'             => '财源币充值:'.$data['charge_cyb'].'财源币',
                    'appid'            => $chargeConfigs['weixin_pid'],
                    'device_info'      => 'APP',
                    'mch_id'           => $chargeConfigs['weixin_mid'],
                    'nonce_str'        => mt_rand(),
                    'notify_url'       => SITE_URL.'/weixin_cyb_notify_api.php',
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
        $type = M('credit_charge')->where("charge_id={$order_id}")->save($charge_type);
        $data = M('credit_charge')->where("charge_id={$order_id}")->find();
        if ($data) {

            $paw = $this->data['paw'];
            $u_id = $this->mid;
            $user_paw = M('user')->field('ppasswd')->where("uid = $u_id")->find();
            // var_dump($paw,$user_paw);exit;
            $u_paw = $user_paw['ppasswd'];
            if(!$u_paw){
                return array('status' => 5, 'message' => '未设置支付密码');
            }
            if(!empty($paw) && $paw == $u_paw){

                $price = floatval($data['charge_value']);
                $type = $data['charge_type'];
                $ubank = M('credit_user')->field('ubank,caiyuanbi')->where("uid = $u_id")->find();
                $uu = floatval($ubank['ubank']);
                $cc = $ubank['caiyuanbi'];
                if($uu < $price){
                    return array('status' => 3, 'message' => '余额不足，请选择其他方式充值');
                }
                $credit = M('credit_user')->where("uid={$data['uid']}")->save(array('ubank' => $uu - $data['charge_value'],'caiyuanbi' => $cc + $data['charge_cyb']));
                if(!credit){
                    return array('status' => 0, 'message' => '充值失败');
                }
                $add['type'] = 2;
                $add['charge_type'] = $data['charge_type'];
                $add['serial_number'] = t($data['serial_number']);
                $add['uid'] = intval($data['uid']);
                $add['change'] = -intval($data['charge_value']);
                $add['action'] = '充值财源币';
                $add['des'] = '';
                $add['ctime'] = time();
                $res = D('credit_record')->add($add);
                $res1 = D('credit_charge')->where("charge_id={$order_id}")->setField('status', 1);
                if($res && $res1){
                    $data_list = array(
                        array('title'=>'支付方式','content'=>'零用钱'),
                        array('title'=>'收款人','content'=>'广州链沃网络科技有限公司'),
                        array('title'=>'交易商品','content'=>$data['charge_cyb'].'元财源币'),
                        array('title'=>'商品优惠','content'=>'赠送'.model('Cyb')->giveCyb($data['charge_value']).'元财源币'),
                        array('title'=>'商品说明','content'=>'财源币可用于发财源红包'),
                    );
                    model('Jpush')->noticeMessage($this->mid,'财源币充值通知','财源币充值通知',$data['serial_number'],1,1,floatval($data['charge_value']),$data_list);
                     return array('status' => 1, 'message' => '充值成功');
                }else{
                    return array('status' => 0, 'message' => '充值失败');
                }

            }else{
                return array('status' => 4, 'message' => '密码不正确');
            }

        }
    }

    /*
        ios 充值 直接返回一个url
     */
    public function createChargeIOS()
    {
        $order_id = intval($this->data['serial_number_id']);
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
        $charge_type['charge_type'] = $type;
        D('credit_charge')->where("charge_id={$order_id}")->save($charge_type);
        $data = D('credit_charge')->where("charge_id={$order_id}")->find();

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
                    'subject'      => '财源币充值:'.$data['charge_cyb'].'财源币',
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
                    'body'             => '财源币充值:'.$data['charge_cyb'].'财源币',
                    'appid'            => $chargeConfigs['weixin_pid'],
                    'device_info'      => 'APP',
                    'mch_id'           => $chargeConfigs['weixin_mid'],
                    'nonce_str'        => mt_rand(),
                    'notify_url'       => SITE_URL.'/weixin_cyb_notify_api.php',
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

//购买财源币
    public function get_chargeCyb()
    {
        $data = M('get_chargecyb')->select();
        foreach ($data as $key => $value) {
           $arr['data'][$key]['value'] = $value['charge_value'];
           $arr['data'][$key]['cyb'] = $value['charge_cyb'];
           $arr['data'][$key]['charge_cyb'] = $value['charge_cyb'] + $value['give_quantity'];
           $arr['data'][$key]['title'] = $value['title'];
           if($value['give_quantity'] == 0)
           {
              $arr['data'][$key]['sta'] = 0; 
           }
           elseif ($value['charge_value'] >= 50 && $value['charge_value'] < 200) 
           {
               $arr['data'][$key]['sta'] = 1; 
           }
           elseif ($value['charge_value'] >= 200) 
           {
               $arr['data'][$key]['sta'] = 2; 
           }
        }
        $arr['status'] = 1;
        $arr['measge'] = '';
        return $arr;
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
            model('Cyb')->charge_success(t($_POST['out_trade_no']));
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
            model('Cyb')->charge_success(t($result->out_trade_no));
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
                if (model('Cyb')->charge_success(t($number))) {
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
        $data['charge_cyb'] = floatval($_REQUEST['charge_cyb']);

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

        $des['content'] = '充值了'.$data['charge_cyb'].'财源币';
        model('Credit')->setUserCredit($data['uid'], array(
                'name'  => 'credit_charge',
                'score' => $data['charge_value'],
        ), 1, $des);

        return array(
                'status' => 1,
                'msg'    => '充值成功',
        );
    }
    

    //充值接口统一下单
    public function setOrder()
    {
        $price = intval($this->data['money']);
        $give = model('Cyb')->giveCyb($price);
        if ($price < 1) {
            return array('status' => 0, 'message' => '充值金额不正确');
        }
        
        $data['serial_number'] = 'CZ'.date('YmdHis').rand(0, 9).rand(0, 9);
        $data['charge_value'] = $price;
        $data['uid'] = $this->mid;
        $data['ctime'] = time();
        $data['status'] = 0;
        $data['sta'] = 3;
        $data['charge_cyb'] = intval($price*100) + intval($give);
        $data['charge_order'] = '';
        $result = D('credit_charge')->add($data);

        if ($result) {
            $data['result'] = $result;

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
