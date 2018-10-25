--
-- 资讯列表
--
DROP TABLE IF EXISTS `ts_information_list`;
CREATE TABLE IF NOT EXISTS `ts_information_list` (
  `id`       int(10)      NOT NULL AUTO_INCREMENT COMMENT '资讯ID'  ,
  `cid`      int(10)      NOT NULL                COMMENT '资讯分类',
  `subject`  varchar(255) NOT NULL DEFAULT ''     COMMENT '资讯标题',
  `abstract` varchar(500) NOT NULL DEFAULT ''     COMMENT '资讯摘要',
  `content`  longtext     NOT NULL                COMMENT '资讯内容',
  `author`   int(10)      NOT NULL                COMMENT '作者'    ,
  `ctime`    varchar(15)  NOT NULL DEFAULT ''     COMMENT '发表时间',
  `rtime`    varchar(15)  NOT NULL DEFAULT ''     COMMENT '更新时间',
  `hits`     int(10)      NOT NULL DEFAULT 0      COMMENT '访问数量',
  `isPre`    tinyint(1)   NOT NULL DEFAULT 1      COMMENT '是否是预发布',
  `isDel`    tinyint(1)   NOT NULL DEFAULT 0      COMMENT '是否删除',
  `isTop`    tinyint(1)   NOT NULL DEFAULT 0      COMMENT '是否推荐',
  PRIMARY KEY (`id`),
  KEY `idx_user` (`author`),
  KEY `idx_cate` (`cid`),
  KEY `idx_time` (`ctime`, `rtime`),
  KEY `idx_hits` (`hits`),
  INDEX `idx_subject` (`subject`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='资讯列表' AUTO_INCREMENT=1 ;

--
-- 资讯分类
--
DROP TABLE IF EXISTS `ts_information_cate`;
CREATE TABLE IF NOT EXISTS `ts_information_cate` (
  `id`    int(10)      NOT NULL AUTO_INCREMENT COMMENT '分类ID'  ,
  `name`  varchar(100) NOT NULL                COMMENT '分类名称',
  `isDel` tinyint(1)   NOT NULL DEFAULT 0      COMMENT '是否删除',
  `rank`  int(10)      NOT NULL DEFAULT 0      COMMENT '排序等级',
  PRIMARY KEY (`id`),
  KEY `idx_is` (`isDel`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='资讯分类' AUTO_INCREMENT=1 ;

--
-- 推荐记录表
-- 
DROP TABLE IF EXISTS `ts_information_top`;
CREATE TABLE IF NOT EXISTS `ts_information_top` (
  `id`    int(10)      NOT NULL AUTO_INCREMENT COMMENT '推荐ID'  ,
  `title` varchar(500) NOT NULL                COMMENT '推荐标题',
  `image` int(10)      NOT NULL                COMMENT '推荐图片',
  `sid`   int(10)      NOT NULL                COMMENT '主题ID'  ,
  PRIMARY KEY (`id`),
  KEY `idx_sid` (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='推荐记录' AUTO_INCREMENT=1 ;


--
-- 转存表中的数据 `ts_system_config`
--
DELETE FROM `ts_system_config` WHERE `list` = 'pageKey' AND `key` = 'Information_Admin_addSubject';
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES('pageKey', 'Information_Admin_addSubject', 'a:6:{s:3:"key";a:4:{s:7:"subject";s:7:"subject";s:3:"cid";s:3:"cid";s:8:"abstract";s:8:"abstract";s:7:"content";s:7:"content";}s:8:"key_name";a:4:{s:7:"subject";s:12:"主题名称";s:3:"cid";s:12:"选择分类";s:8:"abstract";s:12:"主题摘要";s:7:"content";s:12:"主题内容";}s:8:"key_type";a:4:{s:7:"subject";s:4:"text";s:3:"cid";s:5:"radio";s:8:"abstract";s:8:"textarea";s:7:"content";s:6:"editor";}s:11:"key_default";a:4:{s:7:"subject";s:0:"";s:3:"cid";s:0:"";s:8:"abstract";s:0:"";s:7:"content";s:0:"";}s:9:"key_tishi";a:4:{s:7:"subject";s:0:"";s:3:"cid";s:0:"";s:8:"abstract";s:0:"";s:7:"content";s:0:"";}s:14:"key_javascript";a:4:{s:7:"subject";s:0:"";s:3:"cid";s:0:"";s:8:"abstract";s:0:"";s:7:"content";s:0:"";}}', '2015-10-16 02:30:51');

DELETE FROM `ts_system_config` WHERE `list` = 'pageKey' AND `key` = 'Information_Admin_subjectList';
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES('pageKey', 'Information_Admin_subjectList', 'a:4:{s:3:"key";a:7:{s:2:"id";s:2:"id";s:4:"cate";s:4:"cate";s:7:"subject";s:7:"subject";s:6:"author";s:6:"author";s:5:"rtime";s:5:"rtime";s:4:"hits";s:4:"hits";s:6:"action";s:6:"action";}s:8:"key_name";a:7:{s:2:"id";s:8:"主题ID";s:4:"cate";s:12:"主题分类";s:7:"subject";s:12:"主题名称";s:6:"author";s:6:"作者";s:5:"rtime";s:12:"更新时间";s:4:"hits";s:9:"访问量";s:6:"action";s:6:"操作";}s:10:"key_hidden";a:7:{s:2:"id";s:1:"0";s:4:"cate";s:1:"0";s:7:"subject";s:1:"0";s:6:"author";s:1:"0";s:5:"rtime";s:1:"0";s:4:"hits";s:1:"0";s:6:"action";s:1:"0";}s:14:"key_javascript";a:7:{s:2:"id";s:0:"";s:4:"cate";s:0:"";s:7:"subject";s:0:"";s:6:"author";s:0:"";s:5:"rtime";s:0:"";s:4:"hits";s:0:"";s:6:"action";s:0:"";}}', '2015-10-16 02:29:56');

DELETE FROM `ts_system_config` WHERE `list` = 'searchPageKey' AND `key` = 'S_Information_Admin_subjectList';
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES('searchPageKey', 'S_Information_Admin_subjectList', 'a:5:{s:3:"key";a:6:{s:2:"id";s:2:"id";s:3:"cid";s:3:"cid";s:7:"subject";s:7:"subject";s:6:"author";s:6:"author";s:5:"isTop";s:5:"isTop";s:5:"isPre";s:5:"isPre";}s:8:"key_name";a:6:{s:2:"id";s:8:"主题ID";s:3:"cid";s:12:"主题分类";s:7:"subject";s:12:"主题名称";s:6:"author";s:6:"作者";s:5:"isTop";s:12:"是否推荐";s:5:"isPre";s:15:"是否是投稿";}s:8:"key_type";a:6:{s:2:"id";s:4:"text";s:3:"cid";s:6:"select";s:7:"subject";s:4:"text";s:6:"author";s:4:"user";s:5:"isTop";s:5:"radio";s:5:"isPre";s:5:"radio";}s:9:"key_tishi";a:6:{s:2:"id";s:0:"";s:3:"cid";s:0:"";s:7:"subject";s:0:"";s:6:"author";s:0:"";s:5:"isTop";s:0:"";s:5:"isPre";s:0:"";}s:14:"key_javascript";a:6:{s:2:"id";s:0:"";s:3:"cid";s:0:"";s:7:"subject";s:0:"";s:6:"author";s:0:"";s:5:"isTop";s:0:"";s:5:"isPre";s:0:"";}}', '2015-10-16 02:29:23');

DELETE FROM `ts_system_config` WHERE `list` = 'pageKey' AND `key` = 'Information_Admin_cateAdd';
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES('pageKey', 'Information_Admin_cateAdd', 'a:6:{s:3:"key";a:1:{s:4:"name";s:4:"name";}s:8:"key_name";a:1:{s:4:"name";s:12:"分类名称";}s:8:"key_type";a:1:{s:4:"name";s:4:"text";}s:11:"key_default";a:1:{s:4:"name";s:0:"";}s:9:"key_tishi";a:1:{s:4:"name";s:0:"";}s:14:"key_javascript";a:1:{s:4:"name";s:0:"";}}', '2015-10-16 02:28:54');

DELETE FROM `ts_system_config` WHERE `list` = 'pageKey' AND `key` = 'Information_Admin_index';
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES('pageKey', 'Information_Admin_index', 'a:4:{s:3:"key";a:4:{s:2:"id";s:2:"id";s:4:"name";s:4:"name";s:4:"rank";s:4:"rank";s:6:"action";s:6:"action";}s:8:"key_name";a:4:{s:2:"id";s:8:"分类ID";s:4:"name";s:12:"分类名称";s:4:"rank";s:12:"分类排序";s:6:"action";s:6:"操作";}s:10:"key_hidden";a:4:{s:2:"id";s:1:"0";s:4:"name";s:1:"0";s:4:"rank";s:1:"0";s:6:"action";s:1:"0";}s:14:"key_javascript";a:4:{s:2:"id";s:0:"";s:4:"name";s:0:"";s:4:"rank";s:0:"";s:6:"action";s:0:"";}}', '2015-10-16 02:28:39');

DELETE FROM `ts_system_config` WHERE `list` = 'searchPageKey' AND `key` = 'S_Information_Admin_index';
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES('searchPageKey', 'S_Information_Admin_index', 'a:5:{s:3:"key";a:2:{s:2:"id";s:2:"id";s:4:"name";s:4:"name";}s:8:"key_name";a:2:{s:2:"id";s:8:"分类ID";s:4:"name";s:12:"分类名称";}s:8:"key_type";a:2:{s:2:"id";s:4:"text";s:4:"name";s:4:"text";}s:9:"key_tishi";a:2:{s:2:"id";s:0:"";s:4:"name";s:0:"";}s:14:"key_javascript";a:2:{s:2:"id";s:0:"";s:4:"name";s:0:"";}}', '2015-10-16 02:27:38');

DELETE FROM `ts_system_config` WHERE `list` = 'pageKey' AND `key` = 'Information_Admin_subjectTop';
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES
('pageKey', 'Information_Admin_subjectTop', 'a:6:{s:3:"key";a:2:{s:5:"title";s:5:"title";s:5:"image";s:5:"image";}s:8:"key_name";a:2:{s:5:"title";s:12:"推荐标题";s:5:"image";s:12:"推荐图片";}s:8:"key_type";a:2:{s:5:"title";s:4:"text";s:5:"image";s:5:"image";}s:11:"key_default";a:2:{s:5:"title";s:0:"";s:5:"image";s:0:"";}s:9:"key_tishi";a:2:{s:5:"title";s:0:"";s:5:"image";s:0:"";}s:14:"key_javascript";a:2:{s:5:"title";s:0:"";s:5:"image";s:0:"";}}', '2015-10-19 02:58:37');

DELETE FROM `ts_system_config` WHERE `list` = 'pageKey' AND `key` = 'Information_Admin_config';
INSERT INTO `ts_system_config` (`list`, `key`, `value`, `mtime`) VALUES
('pageKey', 'Information_Admin_config', 'a:6:{s:3:"key";a:3:{s:7:"hotTime";s:7:"hotTime";s:14:"commentHotTime";s:14:"commentHotTime";s:5:"guide";s:5:"guide";}s:8:"key_name";a:3:{s:7:"hotTime";s:12:"热门资讯";s:14:"commentHotTime";s:12:"热门评论";s:5:"guide";s:12:"投稿指南";}s:8:"key_type";a:3:{s:7:"hotTime";s:4:"text";s:14:"commentHotTime";s:4:"text";s:5:"guide";s:6:"editor";}s:11:"key_default";a:3:{s:7:"hotTime";s:0:"";s:14:"commentHotTime";s:0:"";s:5:"guide";s:0:"";}s:9:"key_tishi";a:3:{s:7:"hotTime";s:244:"设置热门资讯推荐时间范围，例如，填写1，表示就只推荐一天内的9篇热门资讯，如果输入7，就表示推荐一周内的热门阅读资讯，如果为空或输入0，则表示所有时间内的阅读靠前资讯。";s:14:"commentHotTime";s:90:"设置被热门评论推荐时间范围设置，设置方法和热门资讯方法一致！";s:5:"guide";s:45:"设置用户投稿界面右侧的投稿指南";}s:14:"key_javascript";a:3:{s:7:"hotTime";s:0:"";s:14:"commentHotTime";s:0:"";s:5:"guide";s:0:"";}}', '2015-10-22 23:52:28');

