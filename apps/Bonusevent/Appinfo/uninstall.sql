-- 活动分类
DROP TABLE IF EXISTS `ts_bonusevent_cate`;

-- 活动列表
DROP TABLE IF EXISTS `ts_bonusevent_list`;

--
-- 活动报名
-- ts_bonusevent_enrollment
--
DROP TABLE IF EXISTS `ts_bonusevent_enrollment`;

--
-- 关注
-- ts_bonusevent_star
--
DROP TABLE IF EXISTS `ts_bonusevent_star`;

--
-- 清理一些系统配置数据
--
-- 分类管理配置
--
DELETE FROM `ts_system_config` WHERE `list` = 'pageKey' AND `key` = 'Event_Admin_index';
--
-- 分类添加/编辑
--
DELETE FROM `ts_system_config` WHERE `list` = 'pageKey' AND `key` = 'Event_Admin_AddCate';
DELETE FROM `ts_system_config` WHERE `list` = 'pageKey' AND `key` = 'Event_Admin_event';
DELETE FROM `ts_system_config` WHERE `list` = 'searchPageKey' AND `key` = 'S_Event_Admin_event';
