--
-- 礼物信息表
--
DROP TABLE IF EXISTS `ts_gift`;

--
-- 礼物赠送记录表
--
DROP TABLE IF EXISTS `ts_gift_log`;

--
-- 转存表中的数据 `ts_system_config`
--
DELETE FROM `ts_system_config` WHERE `key` = 'Gift_Admin_addGift';
DELETE FROM `ts_system_config` WHERE `key` = 'S_Gift_Admin_index';
DELETE FROM `ts_system_config` WHERE `key` = 'Gift_Admin_index';
DELETE FROM `ts_system_config` WHERE `key` = 'Gift_Admin_editGift';
DELETE FROM `ts_system_config` WHERE `key` = 'Gift_Admin_order';
DELETE FROM `ts_system_config` WHERE `list` = 'searchPageKey' AND `key` = 'S_Gift_Admin_order';
DELETE FROM `ts_system_config` WHERE `list` = 'pageKey' AND `key` = 'Gift_Admin_send';