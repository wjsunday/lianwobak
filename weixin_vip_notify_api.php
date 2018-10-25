<?php
error_reporting(E_ALL);
require dirname(__FILE__).'/src/bootstrap.php';

define('ROOT_FILE', 'index.php');
$raw_post_data = file_get_contents('php://input', 'r'); 
 // @file_put_contents('./log.txt',$raw_post_data);
 // exit;

// $raw_post_data = "<xml><appid><![CDATA[wxcd22331f05aea75d]]></appid>
// <bank_type><![CDATA[CFT]]></bank_type>
// <cash_fee><![CDATA[1]]></cash_fee>
// <device_info><![CDATA[APP]]></device_info>
// <fee_type><![CDATA[CNY]]></fee_type>
// <is_subscribe><![CDATA[N]]></is_subscribe>
// <mch_id><![CDATA[1438809002]]></mch_id>
// <nonce_str><![CDATA[214381868]]></nonce_str>
// <openid><![CDATA[o2KEmwPUI6zEPwZNC_-MX09gpEi8]]></openid>
// <out_trade_no><![CDATA[CZ2017042510381935]]></out_trade_no>
// <result_code><![CDATA[SUCCESS]]></result_code>
// <return_code><![CDATA[SUCCESS]]></return_code>
// <sign><![CDATA[5101ED7D127C0E64A7502384B1E217D6]]></sign>
// <time_end><![CDATA[20170425103857]]></time_end>
// <total_fee>1</total_fee>
// <trade_type><![CDATA[APP]]></trade_type>
// <transaction_id><![CDATA[4006082001201704258270792737]]></transaction_id>
// </xml>";

 // $result = simplexml_load_string($raw_post_data, null, LIBXML_NOCDATA);

 // print_r($result);

 // die();

$chargeConfigs = model('Xdata')->get('admin_Config:charge');
$weixinpay = new WeChatPay();
$result = simplexml_load_string($raw_post_data, null, LIBXML_NOCDATA);
$sign = $weixinpay->setWXsign($result,$chargeConfigs['weixin_key']);
$result = $weixinpay->notifyReturn1($raw_post_data,$chargeConfigs['weixin_key'],$sign);//
if ($result) {
	model('VipPay')->charge_success(t($result->out_trade_no));
}

