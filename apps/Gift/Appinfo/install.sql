--
-- 转存表中的数据 `ts_system_config`
--
DELETE FROM `ts_system_config` WHERE `key` = 'Gift_Admin_addGift';
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES
('pageKey', 'Gift_Admin_addGift', 'a:6:{s:3:"key";a:8:{s:4:"name";s:4:"name";s:4:"cate";s:4:"cate";s:5:"brief";s:5:"brief";s:5:"image";s:5:"image";s:5:"score";s:5:"score";s:5:"stock";s:5:"stock";s:3:"max";s:3:"max";s:4:"info";s:4:"info";}s:8:"key_name";a:8:{s:4:"name";s:6:"名称";s:4:"cate";s:6:"分类";s:5:"brief";s:6:"简介";s:5:"image";s:6:"图片";s:5:"score";s:12:"兑换积分";s:5:"stock";s:6:"库存";s:3:"max";s:6:"限购";s:4:"info";s:6:"详情";}s:8:"key_type";a:8:{s:4:"name";s:4:"text";s:4:"cate";s:6:"select";s:5:"brief";s:8:"textarea";s:5:"image";s:5:"image";s:5:"score";s:4:"text";s:5:"stock";s:4:"text";s:3:"max";s:4:"text";s:4:"info";s:6:"editor";}s:11:"key_default";a:8:{s:4:"name";s:0:"";s:4:"cate";s:0:"";s:5:"brief";s:0:"";s:5:"image";s:0:"";s:5:"score";s:0:"";s:5:"stock";s:0:"";s:3:"max";s:0:"";s:4:"info";s:0:"";}s:9:"key_tishi";a:8:{s:4:"name";s:27:"输入礼物的商品标题";s:4:"cate";s:18:"选择礼物类型";s:5:"brief";s:45:"输入展示的礼物简介，200字以内。";s:5:"image";s:18:"上传礼物图片";s:5:"score";s:39:"输入兑换礼物所需的积分数量";s:5:"stock";s:24:"礼物的库存量情况";s:3:"max";s:69:"限制用户兑换的数量，不填写或者为零则表示不限制";s:4:"info";s:21:"礼物的详细信息";}s:14:"key_javascript";a:8:{s:4:"name";s:0:"";s:4:"cate";s:0:"";s:5:"brief";s:0:"";s:5:"image";s:0:"";s:5:"score";s:0:"";s:5:"stock";s:0:"";s:3:"max";s:0:"";s:4:"info";s:0:"";}}', '2015-09-18 03:23:37');

DELETE FROM `ts_system_config` WHERE `key` = 'S_Gift_Admin_index';
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES ('searchPageKey', 'S_Gift_Admin_index', 'a:5:{s:3:"key";a:2:{s:2:"id";s:2:"id";s:4:"name";s:4:"name";}s:8:"key_name";a:2:{s:2:"id";s:8:"礼物ID";s:4:"name";s:12:"礼物标题";}s:8:"key_type";a:2:{s:2:"id";s:4:"text";s:4:"name";s:4:"text";}s:9:"key_tishi";a:2:{s:2:"id";s:35:"输入需要精确搜索的礼物ID";s:4:"name";s:36:"输入搜索的礼物名称关键字";}s:14:"key_javascript";a:2:{s:2:"id";s:0:"";s:4:"name";s:0:"";}}', '2015-09-21 08:59:59');

DELETE FROM `ts_system_config` WHERE `key` = 'Gift_Admin_index';
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES
('pageKey', 'Gift_Admin_index', 'a:4:{s:3:"key";a:10:{s:2:"id";s:2:"id";s:4:"name";s:4:"name";s:5:"brief";s:5:"brief";s:5:"image";s:5:"image";s:5:"score";s:5:"score";s:5:"stock";s:5:"stock";s:3:"max";s:3:"max";s:4:"cate";s:4:"cate";s:4:"time";s:4:"time";s:6:"action";s:6:"action";}s:8:"key_name";a:10:{s:2:"id";s:8:"礼物ID";s:4:"name";s:6:"名称";s:5:"brief";s:6:"简介";s:5:"image";s:6:"图片";s:5:"score";s:6:"积分";s:5:"stock";s:6:"库存";s:3:"max";s:6:"限购";s:4:"cate";s:6:"分类";s:4:"time";s:6:"时间";s:6:"action";s:6:"操作";}s:10:"key_hidden";a:10:{s:2:"id";s:1:"0";s:4:"name";s:1:"0";s:5:"brief";s:1:"0";s:5:"image";s:1:"0";s:5:"score";s:1:"0";s:5:"stock";s:1:"0";s:3:"max";s:1:"0";s:4:"cate";s:1:"0";s:4:"time";s:1:"0";s:6:"action";s:1:"0";}s:14:"key_javascript";a:10:{s:2:"id";s:0:"";s:4:"name";s:0:"";s:5:"brief";s:0:"";s:5:"image";s:0:"";s:5:"score";s:0:"";s:5:"stock";s:0:"";s:3:"max";s:0:"";s:4:"cate";s:0:"";s:4:"time";s:0:"";s:6:"action";s:0:"";}}', '2015-09-21 02:40:37');

DELETE FROM `ts_system_config` WHERE `key` = 'Gift_Admin_editGift';
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES
('pageKey', 'Gift_Admin_editGift', 'a:6:{s:3:"key";a:8:{s:4:"name";s:4:"name";s:4:"cate";s:4:"cate";s:5:"brief";s:5:"brief";s:5:"image";s:5:"image";s:5:"score";s:5:"score";s:5:"stock";s:5:"stock";s:3:"max";s:3:"max";s:4:"info";s:4:"info";}s:8:"key_name";a:8:{s:4:"name";s:6:"名称";s:4:"cate";s:6:"分类";s:5:"brief";s:6:"简介";s:5:"image";s:6:"图片";s:5:"score";s:12:"兑换积分";s:5:"stock";s:6:"库存";s:3:"max";s:6:"限购";s:4:"info";s:6:"详情";}s:8:"key_type";a:8:{s:4:"name";s:4:"text";s:4:"cate";s:6:"select";s:5:"brief";s:8:"textarea";s:5:"image";s:5:"image";s:5:"score";s:4:"text";s:5:"stock";s:4:"text";s:3:"max";s:4:"text";s:4:"info";s:6:"editor";}s:11:"key_default";a:8:{s:4:"name";s:0:"";s:4:"cate";s:0:"";s:5:"brief";s:0:"";s:5:"image";s:0:"";s:5:"score";s:0:"";s:5:"stock";s:0:"";s:3:"max";s:0:"";s:4:"info";s:0:"";}s:9:"key_tishi";a:8:{s:4:"name";s:27:"输入礼物的商品标题";s:4:"cate";s:18:"选择礼物类型";s:5:"brief";s:45:"输入展示的礼物简介，200字以内。";s:5:"image";s:18:"上传礼物图片";s:5:"score";s:39:"输入兑换礼物所需的积分数量";s:5:"stock";s:24:"礼物的库存量情况";s:3:"max";s:69:"限制用户兑换的数量，不填写或者为零则表示不限制";s:4:"info";s:21:"礼物的详细信息";}s:14:"key_javascript";a:8:{s:4:"name";s:0:"";s:4:"cate";s:0:"";s:5:"brief";s:0:"";s:5:"image";s:0:"";s:5:"score";s:0:"";s:5:"stock";s:0:"";s:3:"max";s:0:"";s:4:"info";s:0:"";}}', '2015-09-18 03:23:37');

DELETE FROM `ts_system_config` WHERE `key` = 'Gift_Admin_order';
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES
('pageKey', 'Gift_Admin_order', 'a:4:{s:3:"key";a:10:{s:3:"gid";s:3:"gid";s:8:"giftName";s:8:"giftName";s:4:"type";s:4:"type";s:3:"say";s:3:"say";s:3:"num";s:3:"num";s:4:"name";s:4:"name";s:5:"phone";s:5:"phone";s:6:"addres";s:6:"addres";s:4:"time";s:4:"time";s:6:"action";s:6:"action";}s:8:"key_name";a:10:{s:3:"gid";s:8:"礼物ID";s:8:"giftName";s:12:"礼物名称";s:4:"type";s:12:"赠送方式";s:3:"say";s:9:"祝福语";s:3:"num";s:6:"数量";s:4:"name";s:9:"联系人";s:5:"phone";s:12:"联系方式";s:6:"addres";s:12:"联系地址";s:4:"time";s:12:"赠送时间";s:6:"action";s:6:"操作";}s:10:"key_hidden";a:10:{s:3:"gid";s:1:"0";s:8:"giftName";s:1:"0";s:4:"type";s:1:"0";s:3:"say";s:1:"0";s:3:"num";s:1:"0";s:4:"name";s:1:"0";s:5:"phone";s:1:"0";s:6:"addres";s:1:"0";s:4:"time";s:1:"0";s:6:"action";s:1:"0";}s:14:"key_javascript";a:10:{s:3:"gid";s:0:"";s:8:"giftName";s:0:"";s:4:"type";s:0:"";s:3:"say";s:0:"";s:3:"num";s:0:"";s:4:"name";s:0:"";s:5:"phone";s:0:"";s:6:"addres";s:0:"";s:4:"time";s:0:"";s:6:"action";s:0:"";}}', '2015-09-28 05:16:16');

DELETE FROM `ts_system_config` WHERE `list` = 'searchPageKey' AND `key` = 'S_Gift_Admin_order';
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES('searchPageKey', 'S_Gift_Admin_order', 'a:5:{s:3:"key";a:9:{s:3:"gid";s:3:"gid";s:8:"giftName";s:8:"giftName";s:5:"inUid";s:5:"inUid";s:6:"outUid";s:6:"outUid";s:4:"name";s:4:"name";s:5:"phone";s:5:"phone";s:6:"addres";s:6:"addres";s:9:"startTime";s:9:"startTime";s:7:"endTime";s:7:"endTime";}s:8:"key_name";a:9:{s:3:"gid";s:8:"礼物ID";s:8:"giftName";s:12:"礼物名称";s:5:"inUid";s:12:"被赠送人";s:6:"outUid";s:9:"赠送人";s:4:"name";s:15:"联系人姓名";s:5:"phone";s:12:"联系方式";s:6:"addres";s:12:"联系地址";s:9:"startTime";s:12:"开始时间";s:7:"endTime";s:12:"结束时间";}s:8:"key_type";a:9:{s:3:"gid";s:4:"text";s:8:"giftName";s:4:"text";s:5:"inUid";s:4:"user";s:6:"outUid";s:4:"user";s:4:"name";s:4:"text";s:5:"phone";s:4:"text";s:6:"addres";s:4:"text";s:9:"startTime";s:4:"date";s:7:"endTime";s:4:"date";}s:9:"key_tishi";a:9:{s:3:"gid";s:0:"";s:8:"giftName";s:0:"";s:5:"inUid";s:0:"";s:6:"outUid";s:0:"";s:4:"name";s:0:"";s:5:"phone";s:0:"";s:6:"addres";s:0:"";s:9:"startTime";s:0:"";s:7:"endTime";s:0:"";}s:14:"key_javascript";a:9:{s:3:"gid";s:0:"";s:8:"giftName";s:0:"";s:5:"inUid";s:0:"";s:6:"outUid";s:0:"";s:4:"name";s:0:"";s:5:"phone";s:0:"";s:6:"addres";s:0:"";s:9:"startTime";s:0:"";s:7:"endTime";s:0:"";}}', '2015-09-27 23:41:46');

DELETE FROM `ts_system_config` WHERE `list` = 'pageKey' AND `key` = 'Gift_Admin_send';
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES
('pageKey', 'Gift_Admin_send', 'a:6:{s:3:"key";a:1:{s:7:"content";s:7:"content";}s:8:"key_name";a:1:{s:7:"content";s:12:"发货信息";}s:8:"key_type";a:1:{s:7:"content";s:8:"textarea";}s:11:"key_default";a:1:{s:7:"content";s:0:"";}s:9:"key_tishi";a:1:{s:7:"content";s:105:"可以将快递单号或者其他信息，一并写在输入框里面，用此信息停止收货用户！";}s:14:"key_javascript";a:1:{s:7:"content";s:0:"";}}', '2015-09-28 01:50:44');



--
-- 礼物信息表
--
DROP TABLE IF EXISTS `ts_gift`;
CREATE TABLE IF NOT EXISTS `ts_gift` (
  `id`    int(10)      NOT NULL AUTO_INCREMENT COMMENT '礼物ID',
  `name`  varchar(255) NOT NULL                COMMENT '礼物名称',
  `brief` varchar(500) NOT NULL                COMMENT '简介',
  `info`  text         NOT NULL                COMMENT '详情',
  `image` int(10)      NOT NULL                COMMENT '图片的ID',
  `score` int(10)      NOT NULL DEFAULT 0      COMMENT '所需积分',
  `stock` int(10)      NOT NULL DEFAULT 1      COMMENT '库存',
  `max`   smallint(5)  NOT NULL DEFAULT 0      COMMENT '限购数量',
  `time`  bigint(12)   NOT NULL DEFAULT 0      COMMENT '礼物发布时间',
  `cate`  tinyint(1)   NOT NULL DEFAULT 1      COMMENT '礼物分类，1：虚拟，2：实体',
  PRIMARY KEY (`id`),
  KEY `idx_score_stock_time` (`score`, `stock`, `time`),
  KEY `idx_cate` (`cate`),
  INDEX `idx_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='礼物信息储存表' AUTO_INCREMENT=1;
ALTER TABLE `ts_gift` ADD `isDel` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否删除' ;

--
-- 礼物赠送记录表
--
DROP TABLE IF EXISTS `ts_gift_log`;
CREATE TABLE IF NOT EXISTS `ts_gift_log` (
  `id`     int(10)      NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `inUid`  int(10)      NOT NULL                COMMENT '被赠送用户UID',
  `outUid` int(10)      NOT NULL                COMMENT '赠送人UID',
  `gid`    int(10)      NOT NULL                COMMENT '礼物ID',
  `type`   tinyint(1)   NOT NULL DEFAULT 2      COMMENT '赠送类型，1：匿名赠送，2：公开赠送，3：私下赠送',
  `say`    varchar(500) NOT NULL                COMMENT '祝福语',
  `addres` varchar(500) NOT NULL DEFAULT ''     COMMENT '发货地址，只有实体礼物需要',
  `time`   bigint(12)   NOT NULL DEFAULT 0      COMMENT '赠送时间',
  `num`    smallint(5)  NOT NULL DEFAULT 1      COMMENT '赠送数量',
  PRIMARY KEY (`id`),
  KEY `idx_gid` (`gid`),
  KEY `idx_uid` (`inUid`, `outUid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='礼物赠送记录表' AUTO_INCREMENT=1;
ALTER TABLE `ts_gift_log` ADD `name` VARCHAR(100) NULL DEFAULT NULL COMMENT '真实姓名' AFTER `say`, ADD `phone` VARCHAR(50) NULL DEFAULT NULL COMMENT '联系方式（手机号码）' AFTER `name`;
ALTER TABLE `ts_gift_log` ADD `status` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '实体礼物是否已经发货' , ADD `content` TEXT NULL DEFAULT NULL COMMENT '实体礼物拓展信息' ;
ALTER TABLE `ts_gift_log` ADD `notIn` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '标识为转增的收入礼物，不显示在我收到的里面' ;
ALTER TABLE `ts_gift_log` ADD `notOut` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '标识为转增的送出礼物，不显示在我送出到的里面' ;
