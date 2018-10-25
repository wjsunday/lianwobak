<?php
return array (
  'announcement' => 
  array (
    'is_open' => '1',
    'content' => '欢迎使用SociaxTeam',
  ),
  'nav' => 
  array (
    'navi_name' => '123',
    'attach' => '',
    'app_name' => '',
    'url' => '123',
    'target' => 'appoint',
    'status' => 'appoint',
    'position' => '',
    'guest' => 'appoint',
    'is_app_navi' => 'appoint',
    'parent_id' => '123',
    'order_sort' => '123',
  ),
  'invite' => 
  array (
    'send_email_num' => '3',
    'send_link_num' => '3',
  ),
  'audit' => 
  array (
    'open' => '1',
    'replace' => '**',
  ),
  'attachimage' => 
  array (
    'attach_max_size' => '2',
    'attach_allow_extension' => 'png,gif,jpg,jpeg',
    'auto_thumb' => '1',
  ),
  'attach' => 
  array (
    'attach_path_rule' => 'Y/md/H/',
    'attach_max_size' => '100',
    'attach_allow_extension' => 'png,jpeg,zip,rar,doc,xls,ppt,docx,xlsx,pptx,pdf,jpg,gif,mp3',
  ),
  'email' => 
  array (
    'email_sendtype' => 'smtp',
    'email_host' => 'smtp.admin.com',
    'email_ssl' => '0',
    'email_port' => '25',
    'email_account' => 'admin@admin.com',
    'email_password' => 'admin',
    'email_sender_name' => 'ThinkSNS官方社区',
    'email_sender_email' => 'admin@admin.com',
    'email_test' => '',
  ),
  'sms' => 
  array (
    'sms_server' => 'http://106.ihuyi.cn/webservice/sms.php?method=Submit',
    'sms_param' => 'account=admin&password=admin&mobile={tel}&content={message}',
    'success_code' => '<code>2</code>',
    'send_type' => 'post',
    'service' => 'ihuyi',
  ),
  'cloudimage' => 
  array (
    'cloud_image_open' => '0',
    'cloud_image_api_url' => 'http://v0.api.upyun.com',
    'cloud_image_bucket' => 'thinksns_v4',
    'cloud_image_form_api_key' => 'asdPKsdjfshnjfsPQ7cVBRasfd',
    'cloud_image_prefix_urls' => 'http://www.thinksns.com',
    'cloud_image_admin' => 'admin',
    'cloud_image_password' => 'admin',
  ),
  'cloudattach' => 
  array (
    'cloud_attach_open' => '0',
    'cloud_attach_api_url' => 'http://v0.api.upyun.com',
    'cloud_attach_bucket' => 'thinksns',
    'cloud_attach_form_api_key' => 'ajskdhnajkshbfdajjkdhnakjsndjkans',
    'cloud_attach_prefix_urls' => 'http://www.thinksns.com',
    'cloud_attach_admin' => 'admin',
    'cloud_attach_password' => 'admin',
  ),
  'seo_login' => 
  array (
    'name' => '登录页',
    'title' => '链我-享乐生活财源',
    'keywords' => '链 链我 红包  财源  财源币 币',
    'des' => 'welink（链我）是一款全球生活连接社交移动应用（包括iOS、Android）。welink在美国加利福尼亚州洛杉矶创建，以一种快速、美妙的方式链接各国吃、喝、玩、乐的金钱福利（红包）与金钱分享，即可有趣的分享图片和视频，还可以即刻接收亲友和同事的信息、图片、音频文件和视频信息。',
    'node' => '',
    'sub' => '提交',
  ),
  'seo_user_profile' => 
  array (
    'name' => '个人主页',
    'title' => '{uname}的链我主页',
    'keywords' => '{uname}',
    'des' => '{lastFeed}{uname}正在链我一起玩抢红包，朋友们不要错过哦！',
    'node' => '',
    'sub' => '提交',
  ),
  'seo_feed_topic' => 
  array (
    'name' => '话题页',
    'title' => '{topicName}',
    'keywords' => '{topicName}',
    'des' => '{topicDes}我也想说几句。',
    'node' => '',
    'sub' => '提交',
  ),
  'feed' => 
  array (
    'weibo_nums' => '500',
    'weibo_type' => 
    array (
      0 => 'face',
      1 => 'at',
      2 => 'image',
      3 => 'video',
    ),
    'weibo_uploadvideo_open' => '0',
    'weibo_premission' => 
    array (
      0 => 'repost',
      1 => 'comment',
    ),
    'weibo_send_info' => '享乐生活财源，我正链我抢红包，快来一起抢红包》》》',
    'weibo_default_topic' => '',
    'weibo_at_me' => '1',
  ),
  'seo_feed_detail' => 
  array (
    'name' => '分享详情页',
    'title' => '{uname}的分享',
    'keywords' => '{uname}',
    'des' => '我们享乐生活财源，{uname}正在链我-红包社交发红包，朋友们不要错过哦！',
    'node' => '',
    'sub' => '提交',
  ),
  'register' => 
  array (
    'register_type' => 'open',
    'account_type' => 'all',
    'email_suffix' => '',
    'captcha' => '',
    'register_audit' => '0',
    'need_active' => '0',
    'personal_open' => '0',
    'personal_required' => 
    array (
      0 => 'face',
      1 => 'tag',
      2 => 'intro',
    ),
    'tag_num' => '5',
    'interester_rule' => 
    array (
      0 => 'tag',
    ),
    'avoidSubmitByReturn' => '',
    'interester_recommend' => '',
    'default_follow' => '',
    'each_follow' => '',
    'default_user_group' => 
    array (
      0 => '3',
    ),
    'welcome_notify' => '',
  ),
  'site' => 
  array (
    'site_closed' => '1',
    'site_name' => '链我',
    'site_slogan' => '乐享生活财源',
    'site_header_keywords' => '链我、财源、财源币、红包',
    'site_header_description' => '链我-乐享生活财源',
    'site_company' => '',
    'site_footer' => '©2017  lian wo All  Rights Reserved.',
    'site_footer_des' => '链我-红包社交（享乐生活财源）',
    'attach' => '',
    'site_logo' => '47635',
    'site_qr_code' => '47634',
    'sina_weibo_link' => 'http://weibo.com/ithinksns',
    'login_bg' => '45095',
    'site_closed_reason' => '大伙儿不要害怕，我是来测试功能的，一会儿就给你们恢复，这个页面太丑了我要换一个。',
    'sys_domain' => 'admin,thinksns,kefu,liuxiaoqing,hujintao,liaosunan,xijinping,zhishisoft',
    'sys_nickname' => '管理员,超级管理员,法轮功,胡锦涛,江泽民,邓小平,小秘书,刘晓庆,廖素南,共产党,党,习近平,李宇春,政府,小胡祖宗,国民党,admin,智士软件,thinksns',
    'sys_email' => 'lianwocaiyuan@163.com',
    'home_page' => '0',
    'sys_version' => '2017010101',
    'site_online_count' => '1',
    'site_rewrite_on' => '0',
    'web_closed' => '1',
    'site_analytics_code' => '',
  ),
  'charge' => 
  array (
    'charge_ratio' => '100',
    'description' => '充值描述',
    'charge_platform' => 
    array (
      0 => 'alipay',
      1 => 'weixin',
    ),
    'alipay_pid' => '2088102169145681',
    'alipay_key' => 'PpBwxp4SYPTBkQe0wd4eWA==',
    'alipay_email' => 'lianwocaiyuan@163.com',
    'alipay_app_pid' => '2017032406389125',
    'private_key_path' => '/alidata/www/lianwo/data/certs/private_k.pem',
    'alipay_public_key' => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkrIvYISRcc73s3vF1ZT7XN8RNPwJxo8pWaJMmvyTn9N4HQ632qJBVHf8sxHi/fEsraprwCtzvzQETrNRwVxLO5jVmRGi60j8Ue1efIlzPXV9je9mkjzOmdssymZkh2QhUrCmZYI/FCEa3/cNMW0QIDAQAB',
    'weixin_pid' => 'wxcd22331f05aea75d',
    'weixin_mid' => '1438809002',
    'weixin_key' => '92b1f16fbf038443bba69461a8e63a80',
  ),
);
?>