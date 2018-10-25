<?php
/**
 * 抽奖插件.
 *
 * @author wj <wangalways@163.com>
 *
 * @version TS4.0
 */
class LotteryAddons extends NormalAddons
{
    protected $version = '1.0';
    protected $author = 'wj';
    protected $site = 'http://www.thinksns.com';
    protected $info = '抽奖';
    protected $pluginName = '抽奖';
    protected $tsVersion = '4.0';

    /**
     * 获取该插件使用钩子.
     *
     * @return array 钩子信息数组
     */
    public function getHooksInfo()
    {
        $hooks['list'] = array('ActiveHooks', 'GoodsHooks', 'OrderHooks');

        return $hooks;
    }

    /**
     * 插件后台管理入口.
     *
     * @return array 管理相关数据
     */
    public function adminMenu()
    {
        $menu = array();
        $menu['activeList'] = '活动管理';
        $menu['goodsList']  = '商品管理';
        $menu['orderList']  = '中奖结果记录';
 
        $page = isset($_GET['page']) ? t($_GET['page']) : 'addGift';
        if ($page === 'editGift') {
            unset($menu['addGift']);
            $menu['editGift'] = array('content' => '编辑奖品', 'param' => array('id' => intval($_GET['id'])));
        }

        return $menu;
    }

    public function start()
    {
    }

    /**
     * 插件安装入口.
     *
     * @return bool 是否安装成功
     */
    public function install()
    {
        // 插入数据表
        $dbPrefix = C('DB_PREFIX');
        $sql = "DROP TABLE IF EXISTS `{$dbPrefix}lottery_active`;
CREATE TABLE `{$dbPrefix}lottery_active` (
  `active_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '活动id',
  `act_name` varchar(255) NOT NULL DEFAULT '' COMMENT '活动名称',
  `start_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '活动开始时间',
  `end_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '活动结束时间',
  `active_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '活动状态 1关闭 2开启',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '活动创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '编辑活动时间',
  PRIMARY KEY (`active_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='抽奖活动表';

DROP TABLE IF EXISTS `{$dbPrefix}lottery_ag`;
CREATE TABLE `{$dbPrefix}lottery_ag` (
  `ag_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `active_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '活动id',
  `goods_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品id',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '形势 1，随即(还要考虑时间)2，平均',
  `probability` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '概率(抽多少次出一个)',
  `winners` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '中奖人数',
  `random_start` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '随机出奖开始时间',
  `random_end` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '随机出奖结束时间',
  `position` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '商品在转盘上所对应的位置 支持1-9',
  `group_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '分组',
  PRIMARY KEY (`ag_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='活动商品关系表';

DROP TABLE IF EXISTS `{$dbPrefix}lottery_goods`;
CREATE TABLE `{$dbPrefix}lottery_goods` (
  `goods_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `supplier_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '供应商id',
  `supplier_name` varchar(100) NOT NULL DEFAULT '' COMMENT '供应商名称',
  `supplier_phone` varchar(100) NOT NULL DEFAULT '' COMMENT '供应商电话',
  `area_id` varchar(60) NOT NULL DEFAULT '0' COMMENT '地区',
  `add_time` int(11) NOT NULL,
  `virtual_currency` decimal(10,0) unsigned NOT NULL DEFAULT '0' COMMENT '财源币',
  `cash` decimal(10,0) unsigned DEFAULT '0' COMMENT '现金',
  `gift_name` varchar(255) NOT NULL DEFAULT '' COMMENT '礼品名称',
  `goods_number` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '商品数量',
  `gift_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1，代金券2，零用钱3，实物',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `audit_kefu` varchar(60) NOT NULL DEFAULT '0' COMMENT '跟单',
  `audit_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '报名状态1，未审核2，审核通过',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '编辑时间',
  PRIMARY KEY (`goods_id`) USING BTREE,
  KEY `update_time` (`update_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='抽奖奖品';


DROP TABLE IF EXISTS `{$dbPrefix}lottery_order`;
CREATE TABLE `{$dbPrefix}lottery_order` (
  `order_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `order_sn` varchar(255) NOT NULL DEFAULT '' COMMENT '条码',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `user_name` varchar(100) DEFAULT '' COMMENT '中奖用户',
  `phone` varchar(100) DEFAULT '' COMMENT '电话',
  `group_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '用户组',
  `address` varchar(255) DEFAULT '' COMMENT '地址',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '中奖时间',
  `has_num` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '已抽奖',
  `last_num` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '剩余抽奖次数',
  `invitees_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '邀请人数',
  `friend_num` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '好友抽奖',
  `user_amount` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '会员/金额',
  `user_month` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '会员/月 ',
  `supplier_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '供应商',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `goods_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品id',
  `active_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '活动id',
  PRIMARY KEY (`order_id`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='中奖人记录';


DROP TABLE IF EXISTS `{$dbPrefix}lottery_user`;
CREATE TABLE `{$dbPrefix}lottery_user` (
  `user_lottery_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int(11) unsigned NOT NULL COMMENT '用户id',
  `last_nums` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '剩余抽奖次数',
  `used_nums` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '已抽奖次数',
  `winners` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '中奖次数',
  `losers` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '未中奖次数',
  PRIMARY KEY (`user_lottery_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='用户抽奖数';

";
        
        D()->execute($sql);

        return true;
    }

    /**
     * 插件卸载入口.
     *
     * @return bool 是否卸载成功
     */
    public function uninstall()
    {
        // 卸载数据表
        $dbPrefix = C('DB_PREFIX');
        $sql = "DROP TABLE IF EXISTS `{$dbPrefix}lottery_active`;
DROP TABLE IF EXISTS `{$dbPrefix}lottery_ag`;
DROP TABLE IF EXISTS `{$dbPrefix}lottery_goods`;
DROP TABLE IF EXISTS `{$dbPrefix}lottery_order`;
DROP TABLE IF EXISTS `{$dbPrefix}lottery_user`;";
        D()->execute($sql);

        return true;
    }
}
