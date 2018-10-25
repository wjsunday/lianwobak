--
-- 资讯列表
--
DROP TABLE IF EXISTS `ts_information_list`;

--
-- 资讯分类
--
DROP TABLE IF EXISTS `ts_information_cate`;


--
-- 推荐记录表
-- 
DROP TABLE IF EXISTS `ts_information_top`;

--
-- 转存表中的数据 `ts_system_config`
--
DELETE FROM `ts_system_config` WHERE `list` = 'pageKey' AND `key` = 'Information_Admin_addSubject';
DELETE FROM `ts_system_config` WHERE `list` = 'pageKey' AND `key` = 'Information_Admin_subjectList';
DELETE FROM `ts_system_config` WHERE `list` = 'pageKey' AND `key` = 'Information_Admin_cateAdd';
DELETE FROM `ts_system_config` WHERE `list` = 'pageKey' AND `key` = 'Information_Admin_index';
DELETE FROM `ts_system_config` WHERE `list` = 'pageKey' AND `key` = 'Information_Admin_subjectTop';
DELETE FROM `ts_system_config` WHERE `list` = 'pageKey' AND `key` = 'Information_Admin_config';

DELETE FROM `ts_system_config` WHERE `list` = 'searchPageKey' AND `key` = 'S_Information_Admin_index';
DELETE FROM `ts_system_config` WHERE `list` = 'searchPageKey' AND `key` = 'S_Information_Admin_subjectList';
