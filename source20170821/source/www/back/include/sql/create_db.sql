/*
Navicat MySQL Data Transfer

Source Server         : local
Source Server Version : 50611
Source Host           : localhost:3306
Source Database       : handcrowd3

Target Server Type    : MYSQL
Target Server Version : 50611
File Encoding         : 65001

Date: 2016-04-29 21:19:34
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for m_skill
-- ----------------------------
DROP TABLE IF EXISTS `m_skill`;
CREATE TABLE `m_skill` (
  `skill_id` int(11) NOT NULL AUTO_INCREMENT,
  `skill_name` varchar(50) NOT NULL,
  `home_id` int(11) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL,
  PRIMARY KEY (`skill_id`)
) ENGINE=InnoDB AUTO_INCREMENT=130 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for m_user
-- ----------------------------
DROP TABLE IF EXISTS `m_user`;
CREATE TABLE `m_user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` int(3) NOT NULL DEFAULT '2',
  `user_name` varchar(50) NOT NULL,
  `avartar` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(32) DEFAULT NULL,
  `facebook_id` varchar(30) DEFAULT NULL,
  `google_id` varchar(30) DEFAULT NULL,
  `hourly_amount` int(11) DEFAULT NULL,
  `curr_type` varchar(3) DEFAULT NULL,
  `weekly_limit` int(11) DEFAULT NULL,
  `language` varchar(5) NOT NULL DEFAULT 'ja_jp',
  `time_zone` varchar(40) NOT NULL DEFAULT 'Asia/Tokyo',
  `alarm_mail_flag` int(1) DEFAULT NULL,
  `alarm_time` int(4) DEFAULT NULL,
  `activate_flag` int(1) NOT NULL DEFAULT '0',
  `activate_key` varchar(32) DEFAULT NULL,
  `activate_until` timestamp NULL DEFAULT NULL,
  `access_time` timestamp NULL DEFAULT NULL,
  `plan_type` decimal(2,0) NOT NULL DEFAULT '0',
  `plan_start_date` timestamp NULL DEFAULT NULL,
  `plan_end_date` timestamp NULL DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=100335 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_alarm
-- ----------------------------
DROP TABLE IF EXISTS `t_alarm`;
CREATE TABLE `t_alarm` (
  `alarm_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `alarm_time` timestamp NULL DEFAULT NULL,
  `alarm_flag` int(1) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL,
  PRIMARY KEY (`alarm_id`),
  KEY `alarm_user` (`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1840 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_cmsg
-- ----------------------------
DROP TABLE IF EXISTS `t_cmsg`;
CREATE TABLE `t_cmsg` (
  `cmsg_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `mission_id` int(11) NOT NULL,
  `from_id` int(11) NOT NULL,
  `to_id` int(11) DEFAULT NULL,
  `cmsg_type` int(2) NOT NULL,
  `content` longtext,
  `attach` varchar(256) DEFAULT NULL,
  `file_size` decimal(10,0) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmsg_id`),
  KEY `croom_cmsg_id` (`mission_id`) USING BTREE,
  KEY `cmsg_from` (`from_id`) USING BTREE,
  KEY `cmsg_to` (`to_id`) USING BTREE,
  KEY `del_flag` (`del_flag`)
) ENGINE=InnoDB AUTO_INCREMENT=10695 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_cmsg_star
-- ----------------------------
DROP TABLE IF EXISTS `t_cmsg_star`;
CREATE TABLE `t_cmsg_star` (
  `cmsg_star_id` int(11) NOT NULL AUTO_INCREMENT,
  `cmsg_id` bigint(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmsg_star_id`),
  KEY `cmsg_user_id` (`cmsg_id`,`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_cunread
-- ----------------------------
DROP TABLE IF EXISTS `t_cunread`;
CREATE TABLE `t_cunread` (
  `cunread_id` bigint(11) NOT NULL AUTO_INCREMENT,
  `mission_id` int(11) NOT NULL,
  `cmsg_id` bigint(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mail_flag` int(1) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cunread_id`),
  KEY `cunread_croom_user_id` (`cmsg_id`,`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=161884 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_google_batch
-- ----------------------------
DROP TABLE IF EXISTS `t_google_batch`;
CREATE TABLE `t_google_batch` (
  `google_batch_id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `update_type` int(1) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL,
  PRIMARY KEY (`google_batch_id`),
  KEY `google_batch_task` (`task_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_google_event
-- ----------------------------
DROP TABLE IF EXISTS `t_google_event`;
CREATE TABLE `t_google_event` (
  `google_event_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `calendar_id` varchar(70) DEFAULT NULL,
  `event_id` varchar(70) DEFAULT NULL,
  `task_id` int(11) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL,
  PRIMARY KEY (`google_event_id`),
  KEY `google_event_user` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_google_token
-- ----------------------------
DROP TABLE IF EXISTS `t_google_token`;
CREATE TABLE `t_google_token` (
  `user_id` int(11) NOT NULL,
  `token` varchar(300) NOT NULL,
  `calendar_id` varchar(70) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_home
-- ----------------------------
DROP TABLE IF EXISTS `t_home`;
CREATE TABLE `t_home` (
  `home_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `home_name` varchar(100) NOT NULL,
  `summary` longtext,
  `logo` varchar(100) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`home_id`),
  KEY `home_client` (`client_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=127 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_home_member
-- ----------------------------
DROP TABLE IF EXISTS `t_home_member`;
CREATE TABLE `t_home_member` (
  `home_member_id` int(11) NOT NULL AUTO_INCREMENT,
  `home_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `priv` int(11) NOT NULL,
  `last_date` timestamp NULL DEFAULT NULL,
  `accepted` int(1) NOT NULL DEFAULT '0',
  `create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`home_member_id`),
  KEY `home_member` (`home_id`) USING BTREE,
  KEY `user_id` (`user_id`),
  KEY `del_flag` (`del_flag`),
  KEY `priv` (`priv`),
  KEY `last_date` (`last_date`)
) ENGINE=InnoDB AUTO_INCREMENT=466 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_mission
-- ----------------------------
DROP TABLE IF EXISTS `t_mission`;
CREATE TABLE `t_mission` (
  `mission_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `home_id` int(1) DEFAULT NULL,
  `mission_name` varchar(100) NOT NULL,
  `complete_flag` int(1) NOT NULL DEFAULT '0',
  `complete_time` timestamp NULL DEFAULT NULL,
  `summary` longtext,
  `job_back` varchar(255) DEFAULT NULL,
  `job_back_pos` int(2) DEFAULT NULL,
  `prc_back` varchar(255) DEFAULT NULL,
  `prc_back_pos` int(2) DEFAULT NULL,
  `repeat_type` int(2) NOT NULL DEFAULT '0',
  `repeat_day` varchar(5) DEFAULT NULL,
  `private_flag` int(1) DEFAULT NULL,
  `last_date` timestamp NULL DEFAULT NULL,
  `last_cmsg_id` bigint(11) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`mission_id`),
  KEY `mission_last_date` (`last_date`) USING BTREE,
  KEY `mission_home` (`home_id`) USING BTREE,
  KEY `mission_client_id` (`client_id`) USING BTREE,
  KEY `mission_private_flag` (`private_flag`) USING BTREE,
  KEY `del_flag` (`del_flag`),
  KEY `last_date` (`last_date`)
) ENGINE=InnoDB AUTO_INCREMENT=1992 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_mission_attach
-- ----------------------------
DROP TABLE IF EXISTS `t_mission_attach`;
CREATE TABLE `t_mission_attach` (
  `mission_attach_id` int(11) NOT NULL AUTO_INCREMENT,
  `mission_id` int(11) NOT NULL,
  `attach_name` varchar(255) NOT NULL,
  `file_size` decimal(10,2) NOT NULL DEFAULT '0.00',
  `creator_id` int(11) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL,
  PRIMARY KEY (`mission_attach_id`),
  KEY `mission_attach` (`mission_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1660 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_mission_member
-- ----------------------------
DROP TABLE IF EXISTS `t_mission_member`;
CREATE TABLE `t_mission_member` (
  `mission_member_id` int(11) NOT NULL AUTO_INCREMENT,
  `mission_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pinned` int(1) DEFAULT NULL,
  `unreads` smallint(6) DEFAULT NULL,
  `opp_user_id` int(11) DEFAULT NULL,
  `last_date` timestamp NULL DEFAULT NULL,
  `push_flag` int(1) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`mission_member_id`),
  KEY `mission_member` (`mission_id`) USING BTREE,
  KEY `user_id` (`user_id`),
  KEY `opp_user_id` (`opp_user_id`),
  KEY `pinned` (`pinned`)
) ENGINE=InnoDB AUTO_INCREMENT=859015 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_patch
-- ----------------------------
DROP TABLE IF EXISTS `t_patch`;
CREATE TABLE `t_patch` (
  `patch_id` int(11) NOT NULL AUTO_INCREMENT,
  `version` varchar(10) NOT NULL,
  `description` mediumtext NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` decimal(1,0) NOT NULL,
  PRIMARY KEY (`patch_id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_proclink
-- ----------------------------
DROP TABLE IF EXISTS `t_proclink`;
CREATE TABLE `t_proclink` (
  `proclink_id` int(11) NOT NULL AUTO_INCREMENT,
  `mission_id` int(11) NOT NULL,
  `from_task_id` int(11) NOT NULL,
  `to_task_id` int(11) NOT NULL,
  `critical` int(1) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`proclink_id`),
  KEY `proclink_mission` (`mission_id`) USING BTREE,
  KEY `proclink_from` (`from_task_id`) USING BTREE,
  KEY `proclink_to` (`to_task_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6461 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_push_msg
-- ----------------------------
DROP TABLE IF EXISTS `t_push_msg`;
CREATE TABLE `t_push_msg` (
  `push_msg_id` int(11) NOT NULL AUTO_INCREMENT,
  `device_type` int(1) NOT NULL,
  `device_token` varchar(200) NOT NULL,
  `message` varchar(100) DEFAULT NULL,
  `fail_count` int(2) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`push_msg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_push_token
-- ----------------------------
DROP TABLE IF EXISTS `t_push_token`;
CREATE TABLE `t_push_token` (
  `push_token_id` int(11) NOT NULL AUTO_INCREMENT,
  `device_type` int(1) NOT NULL,
  `device_token` varchar(200) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `last_message` varchar(100) DEFAULT NULL,
  `push_flag` int(1) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `del_flag` int(1) NOT NULL,
  PRIMARY KEY (`push_token_id`),
  UNIQUE KEY `push_token` (`device_type`,`device_token`) USING BTREE,
  UNIQUE KEY `push_token_userid` (`device_type`,`device_token`,`user_id`) USING BTREE,
  KEY `push_flag` (`push_flag`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_session
-- ----------------------------
DROP TABLE IF EXISTS `t_session`;
CREATE TABLE `t_session` (
  `session_id` varchar(40) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `login_time` timestamp NULL DEFAULT NULL,
  `access_time` timestamp NULL DEFAULT NULL,
  `ip` varchar(15) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL,
  PRIMARY KEY (`session_id`,`user_id`),
  KEY `access_time` (`access_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_task
-- ----------------------------
DROP TABLE IF EXISTS `t_task`;
CREATE TABLE `t_task` (
  `task_id` int(11) NOT NULL AUTO_INCREMENT,
  `mission_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `performer_id` int(11) DEFAULT NULL,
  `task_name` tinytext NOT NULL,
  `plan_start_date` timestamp NULL DEFAULT NULL,
  `plan_start_time` timestamp NULL DEFAULT NULL,
  `plan_end_date` timestamp NULL DEFAULT NULL,
  `plan_end_time` timestamp NULL DEFAULT NULL,
  `plan_budget` decimal(10,0) DEFAULT NULL,
  `plan_hours` decimal(12,2) DEFAULT NULL,
  `level` int(2) DEFAULT NULL,
  `complete_flag` int(1) NOT NULL DEFAULT '0',
  `complete_time` timestamp NULL DEFAULT NULL,
  `progress` int(11) DEFAULT NULL,
  `summary` longtext,
  `x` int(11) DEFAULT NULL,
  `y` int(11) DEFAULT NULL,
  `processed` int(1) NOT NULL DEFAULT '0',
  `proclevel` int(4) DEFAULT NULL,
  `start_alarm` int(1) DEFAULT NULL,
  `end_alarm` int(1) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`task_id`),
  KEY `task_mission_id` (`mission_id`) USING BTREE,
  KEY `task_user_id` (`user_id`) USING BTREE,
  KEY `task_performer_id` (`performer_id`) USING BTREE,
  KEY `task_start_alarm` (`start_alarm`) USING BTREE,
  KEY `task_end_alarm` (`end_alarm`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9947 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_task_comment
-- ----------------------------
DROP TABLE IF EXISTS `t_task_comment`;
CREATE TABLE `t_task_comment` (
  `task_comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_type` int(11) DEFAULT '0',
  `content` longtext,
  `attach` varchar(255) DEFAULT NULL,
  `check_result` varchar(50) DEFAULT NULL,
  `file_size` decimal(10,2) DEFAULT '0.00',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`task_comment_id`),
  KEY `task_comment` (`task_id`) USING BTREE,
  KEY `task_comment_user` (`task_id`,`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=885 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_task_skill
-- ----------------------------
DROP TABLE IF EXISTS `t_task_skill`;
CREATE TABLE `t_task_skill` (
  `task_skill_id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `skill_name` varchar(50) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`task_skill_id`),
  KEY `task_skill` (`task_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=307 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_task_user
-- ----------------------------
DROP TABLE IF EXISTS `t_task_user`;
CREATE TABLE `t_task_user` (
  `task_user_id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sort` int(11) DEFAULT NULL,
  `priority` int(1) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`task_user_id`),
  KEY `task_user_id` (`task_id`,`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2375 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_template
-- ----------------------------
DROP TABLE IF EXISTS `t_template`;
CREATE TABLE `t_template` (
  `template_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `template_name` varchar(100) NOT NULL,
  `summary` longtext,
  `job_back` varchar(255) DEFAULT NULL,
  `job_back_pos` int(2) DEFAULT NULL,
  `prc_back` varchar(255) DEFAULT NULL,
  `prc_back_pos` int(2) DEFAULT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`template_id`),
  KEY `template_user` (`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_template_attach
-- ----------------------------
DROP TABLE IF EXISTS `t_template_attach`;
CREATE TABLE `t_template_attach` (
  `template_attach_id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `attach_name` varchar(255) NOT NULL,
  `file_size` decimal(10,2) NOT NULL DEFAULT '0.00',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL,
  PRIMARY KEY (`template_attach_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_template_proclink
-- ----------------------------
DROP TABLE IF EXISTS `t_template_proclink`;
CREATE TABLE `t_template_proclink` (
  `template_proclink_id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `from_task_id` int(11) NOT NULL,
  `to_task_id` int(11) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`template_proclink_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1358 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_template_skill
-- ----------------------------
DROP TABLE IF EXISTS `t_template_skill`;
CREATE TABLE `t_template_skill` (
  `template_skill_id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `template_task_id` int(11) NOT NULL,
  `skill_name` varchar(50) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`template_skill_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_template_task
-- ----------------------------
DROP TABLE IF EXISTS `t_template_task`;
CREATE TABLE `t_template_task` (
  `template_task_id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) DEFAULT NULL,
  `task_name` tinytext NOT NULL,
  `plan_budget` decimal(10,2) DEFAULT NULL,
  `plan_hours` decimal(12,2) DEFAULT NULL,
  `summary` longtext,
  `x` int(11) DEFAULT NULL,
  `y` int(11) DEFAULT NULL,
  `processed` int(1) NOT NULL DEFAULT '0',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`template_task_id`),
  KEY `template_task_template_id` (`template_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1299 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for t_user_skill
-- ----------------------------
DROP TABLE IF EXISTS `t_user_skill`;
CREATE TABLE `t_user_skill` (
  `user_skill_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `skill_name` varchar(50) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_time` timestamp NULL DEFAULT NULL,
  `del_flag` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_skill_id`),
  KEY `user_skill` (`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=197 DEFAULT CHARSET=utf8;
