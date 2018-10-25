--
-- 活动分类
-- ts_event_cate 结构
--
DROP TABLE IF EXISTS `ts_event_cate`;
CREATE TABLE IF NOT EXISTS `ts_event_cate` (
  `cid`   int(10)      unsigned NOT NULL AUTO_INCREMENT COMMENT '分类id'  ,
  `name`  varchar(255)          NOT NULL                COMMENT '分类名称' ,
  `leval` int(10)      unsigned     NULL DEFAULT 0      COMMENT '排序等级'     ,
  `del`   enum('0', '1')            NULL DEFAULT '0'    COMMENT '删除标记'     ,
  PRIMARY KEY (`cid`)                                                     ,
  INDEX `idx_name` (`name`)                                               ,
  KEY `idx_del` (`del`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='活动分类' AUTO_INCREMENT=1 ;

--
-- 活动列表
-- ts_event_list
--
DROP TABLE IF EXISTS `ts_event_list`;
CREATE TABLE IF NOT EXISTS `ts_event_list` (
  `eid`       int(10)       unsigned NOT NULL AUTO_INCREMENT COMMENT '互动id'    ,
  `name`      varchar(255)           NOT NULL                COMMENT '活动名称'  ,
  `stime`     bigint(15)             NOT NULL                COMMENT '开始时间'  , 
  `etime`     bigint(15)             NOT NULL                COMMENT '结束时间'  ,
  `area`      int(10)       unsigned NOT NULL                COMMENT '地区（省）',
  `city`      int(10)       unsigned NOT NULL                COMMENT '城市（市）',
  `location`  varchar(255)           NOT NULL                COMMENT '详细地址'  ,
  `longitude` varchar(100)               NULL DEFAULT 0      COMMENT '经度'      ,
  `latitude`  varchar(100)               NULL DEFAULT 0      COMMENT '纬度'      ,
  `place`     varchar(255)           NOT NULL                COMMENT '场所'      ,
  `image`     int(10)       unsigned     NULL DEFAULT 0      COMMENT '图片信息'  ,
  `manNumber` int(10)       unsigned NOT NULL                COMMENT '活动人数'  ,
  `remainder` int(10)       unsigned NOT NULL                COMMENT '剩余名额'  ,
  `price`     decimal(8, 2)              NULL DEFAULT '0.00' COMMENT '活动费用'  ,
  `tips`      varchar(500)           NOT NULL                COMMENT '费用说明'  ,
  `cid`       int(10)       unsigned NOT NULL                COMMENT '活动分类'  ,
  `audit`     enum('0', '1')             NULL DEFAULT '1'    COMMENT '是否审核'  ,
  `content`   text                       NULL                COMMENT '活动内容'  ,
  `uid`       int(10)       unsigned NOT NULL                COMMENT '发布者'    ,
  `del`       enum('0', '1')             NULL DEFAULT '0'    COMMENT '删除标记'  ,
  PRIMARY KEY (`eid`)                                                            ,
  KEY `idx_del` (`del`)                                                          ,
  KEY `idx_uid` (`uid`)                                                          ,
  KEY `idx_area` (`area`)                                                        ,
  KEY `idx_city` (`city`)                                                        ,
  KEY `idx_time` (`stime`, `etime`)                                              ,
  KEY `idx_cid` (`cid`)                                                          ,
  INDEX `idx_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='活动列表' AUTO_INCREMENT=1 ;

--
-- 活动报名
-- ts_event_enrollment
--
DROP TABLE IF EXISTS `ts_event_enrollment`;
CREATE TABLE IF NOT EXISTS `ts_event_enrollment` (
  `id`  int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '报名ID',
  `uid` int(10) unsigned NOT NULL                COMMENT '报名用户',
  `name` varchar(255)    NOT NULL                COMMENT '称呼',
  `num`  int(10)         NOT NULL                COMMENT '报名人数',
  `sex` tinyint(1)           NULL DEFAULT 1      COMMENT '性别，默认男',
  `phone` varchar(20)    NOT NULL                COMMENT '联系方式',
  `note`  text                                   COMMENT '备注',
  `time`  int(10)                                COMMENT '报名时间',
  `aduit` tinyint(1)         NULL DEFAULT 0      COMMENT '是否审核通过',
  `eid`   int(10)        NOT NULL                COMMENT '活动id',
  PRIMARY KEY (`id`),
  KEY `idx_uid` (`uid`),
  KEY `idx_event` (`eid`),
  KEY `idx_aduit` (`aduit`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='活动报名' AUTO_INCREMENT=1 ;

--
-- 关注
-- ts_event_star
--
DROP TABLE IF EXISTS `ts_event_star`;
CREATE TABLE IF NOT EXISTS `ts_event_star` (
  `uid` int(10) unsigned NOT NULL COMMENT '用户id',
  `eid` int(10) unsigned NOT NULL COMMENT '活动id',
  `time` int(10)         NOT NULL COMMENT '关注时间',
  KEY `idx_uid` (`uid`),
  KEY `idx_eid` (`eid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT="活动关注" ;

--
-- 一些系统配置的SQL
--
-- 分类管理配置
--
DELETE FROM `ts_system_config` WHERE `list` = 'pageKey' AND `key` = 'Event_Admin_index';
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES ('pageKey', 'Event_Admin_index', 'a:4:{s:3:"key";a:4:{s:3:"cid";s:3:"cid";s:4:"name";s:4:"name";s:5:"leval";s:5:"leval";s:6:"action";s:6:"action";}s:8:"key_name";a:4:{s:3:"cid";s:8:"分类ID";s:4:"name";s:12:"分类名称";s:5:"leval";s:12:"排序等级";s:6:"action";s:6:"操作";}s:10:"key_hidden";a:4:{s:3:"cid";s:1:"0";s:4:"name";s:1:"0";s:5:"leval";s:1:"0";s:6:"action";s:1:"0";}s:14:"key_javascript";a:4:{s:3:"cid";s:0:"";s:4:"name";s:0:"";s:5:"leval";s:0:"";s:6:"action";s:0:"";}}', '2015-12-16 09:55:44');
--
-- 分类添加/编辑
--
DELETE FROM `ts_system_config` WHERE `list` = 'pageKey' AND `key` = 'Event_Admin_AddCate';
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES ('pageKey', 'Event_Admin_AddCate', 'a:6:{s:3:"key";a:2:{s:4:"name";s:4:"name";s:5:"leval";s:5:"leval";}s:8:"key_name";a:2:{s:4:"name";s:12:"分类名称";s:5:"leval";s:12:"排序等级";}s:8:"key_type";a:2:{s:4:"name";s:4:"text";s:5:"leval";s:4:"text";}s:11:"key_default";a:2:{s:4:"name";s:0:"";s:5:"leval";s:3:"100";}s:9:"key_tishi";a:2:{s:4:"name";s:0:"";s:5:"leval";s:78:"排序等级最小值为0，最大值为99999999，排序越小，越靠前！";}s:14:"key_javascript";a:2:{s:4:"name";s:0:"";s:5:"leval";s:0:"";}}', '2015-12-16 10:00:00');

DELETE FROM `ts_system_config` WHERE `list` = 'pageKey' AND `key` = 'Event_Admin_event';
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES
('pageKey', 'Event_Admin_event', 'a:4:{s:3:"key";a:9:{s:3:"eid";s:3:"eid";s:4:"name";s:4:"name";s:4:"time";s:4:"time";s:8:"location";s:8:"location";s:9:"manNumber";s:9:"manNumber";s:5:"price";s:5:"price";s:4:"cate";s:4:"cate";s:4:"user";s:4:"user";s:6:"action";s:6:"action";}s:8:"key_name";a:9:{s:3:"eid";s:8:"活动ID";s:4:"name";s:6:"标题";s:4:"time";s:6:"时间";s:8:"location";s:6:"地址";s:9:"manNumber";s:12:"需要人数";s:5:"price";s:6:"价格";s:4:"cate";s:6:"分类";s:4:"user";s:9:"发布人";s:6:"action";s:6:"操作";}s:10:"key_hidden";a:9:{s:3:"eid";s:1:"0";s:4:"name";s:1:"0";s:4:"time";s:1:"0";s:8:"location";s:1:"0";s:9:"manNumber";s:1:"0";s:5:"price";s:1:"0";s:4:"cate";s:1:"0";s:4:"user";s:1:"0";s:6:"action";s:1:"0";}s:14:"key_javascript";a:9:{s:3:"eid";s:0:"";s:4:"name";s:0:"";s:4:"time";s:0:"";s:8:"location";s:0:"";s:9:"manNumber";s:0:"";s:5:"price";s:0:"";s:4:"cate";s:0:"";s:4:"user";s:0:"";s:6:"action";s:0:"";}}', '2015-12-27 17:30:58');

DELETE FROM `ts_system_config` WHERE `list` = 'searchPageKey' AND `key` = 'S_Event_Admin_event';
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES
('searchPageKey', 'S_Event_Admin_event', 'a:5:{s:3:"key";a:7:{s:3:"eid";s:3:"eid";s:4:"name";s:4:"name";s:5:"stime";s:5:"stime";s:5:"etime";s:5:"etime";s:3:"cid";s:3:"cid";s:3:"uid";s:3:"uid";s:5:"audit";s:5:"audit";}s:8:"key_name";a:7:{s:3:"eid";s:8:"活动ID";s:4:"name";s:12:"活动名称";s:5:"stime";s:12:"开始时间";s:5:"etime";s:12:"结束时间";s:3:"cid";s:6:"分类";s:3:"uid";s:6:"用户";s:5:"audit";s:18:"是否需要审核";}s:8:"key_type";a:7:{s:3:"eid";s:4:"text";s:4:"name";s:4:"text";s:5:"stime";s:4:"date";s:5:"etime";s:4:"date";s:3:"cid";s:6:"select";s:3:"uid";s:4:"user";s:5:"audit";s:5:"radio";}s:9:"key_tishi";a:7:{s:3:"eid";s:0:"";s:4:"name";s:0:"";s:5:"stime";s:0:"";s:5:"etime";s:0:"";s:3:"cid";s:0:"";s:3:"uid";s:0:"";s:5:"audit";s:0:"";}s:14:"key_javascript";a:7:{s:3:"eid";s:0:"";s:4:"name";s:0:"";s:5:"stime";s:0:"";s:5:"etime";s:0:"";s:3:"cid";s:0:"";s:3:"uid";s:0:"";s:5:"audit";s:0:"";}}', '2015-12-27 17:36:10');
