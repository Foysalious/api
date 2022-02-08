/*
 Navicat Premium Data Transfer

 Source Server         : (PA) Production Mysql - Read Replica 01
 Source Server Type    : MySQL
 Source Server Version : 50733
 Source Host           : sheba-rr-admin.cifg1gfqorjf.ap-south-1.rds.amazonaws.com:3306
 Source Schema         : sheba

 Target Server Type    : MySQL
 Target Server Version : 50733
 File Encoding         : 65001

 Date: 08/02/2022 13:11:51
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for accessor_comment
-- ----------------------------
DROP TABLE IF EXISTS `accessor_comment`;
CREATE TABLE `accessor_comment` (
  `comment_id` int(10) unsigned NOT NULL,
  `accessor_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`comment_id`,`accessor_id`),
  KEY `accessor_comment_accessor_id_foreign` (`accessor_id`),
  KEY `accessor_comment_comment_id_foreign` (`comment_id`) USING BTREE,
  CONSTRAINT `accessor_comment_accessor_id_foreign` FOREIGN KEY (`accessor_id`) REFERENCES `accessors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `accessor_comment_comment_id_foreign` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for accessor_complain_category
-- ----------------------------
DROP TABLE IF EXISTS `accessor_complain_category`;
CREATE TABLE `accessor_complain_category` (
  `complain_category_id` int(10) unsigned NOT NULL,
  `accessor_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`complain_category_id`,`accessor_id`),
  KEY `accessor_complain_category_accessor_id_foreign` (`accessor_id`),
  CONSTRAINT `accessor_complain_category_accessor_id_foreign` FOREIGN KEY (`accessor_id`) REFERENCES `accessors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `accessor_complain_category_complain_category_id_foreign` FOREIGN KEY (`complain_category_id`) REFERENCES `complain_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for accessor_complain_preset
-- ----------------------------
DROP TABLE IF EXISTS `accessor_complain_preset`;
CREATE TABLE `accessor_complain_preset` (
  `complain_preset_id` int(10) unsigned NOT NULL,
  `accessor_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`complain_preset_id`,`accessor_id`),
  KEY `accessor_complain_preset_accessor_id_foreign` (`accessor_id`),
  CONSTRAINT `accessor_complain_preset_accessor_id_foreign` FOREIGN KEY (`accessor_id`) REFERENCES `accessors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `accessor_complain_preset_complain_preset_id_foreign` FOREIGN KEY (`complain_preset_id`) REFERENCES `complain_presets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for accessors
-- ----------------------------
DROP TABLE IF EXISTS `accessors`;
CREATE TABLE `accessors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `model_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for action_business
-- ----------------------------
DROP TABLE IF EXISTS `action_business`;
CREATE TABLE `action_business` (
  `action_id` int(10) unsigned NOT NULL,
  `business_id` int(10) unsigned NOT NULL,
  KEY `action_business_action_id_foreign` (`action_id`),
  KEY `action_business_business_id_foreign` (`business_id`),
  CONSTRAINT `action_business_action_id_foreign` FOREIGN KEY (`action_id`) REFERENCES `actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `action_business_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for action_business_department
-- ----------------------------
DROP TABLE IF EXISTS `action_business_department`;
CREATE TABLE `action_business_department` (
  `action_id` int(10) unsigned NOT NULL,
  `business_department_id` int(10) unsigned NOT NULL,
  KEY `action_business_department_action_id_foreign` (`action_id`),
  KEY `action_business_department_business_department_id_foreign` (`business_department_id`),
  CONSTRAINT `action_business_department_action_id_foreign` FOREIGN KEY (`action_id`) REFERENCES `actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `action_business_department_business_department_id_foreign` FOREIGN KEY (`business_department_id`) REFERENCES `business_departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for action_business_member
-- ----------------------------
DROP TABLE IF EXISTS `action_business_member`;
CREATE TABLE `action_business_member` (
  `business_member_id` int(10) unsigned NOT NULL,
  `action_id` int(10) unsigned NOT NULL,
  KEY `action_business_member_business_member_id_foreign` (`business_member_id`),
  KEY `action_business_member_action_id_foreign` (`action_id`),
  CONSTRAINT `action_business_member_action_id_foreign` FOREIGN KEY (`action_id`) REFERENCES `actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `action_business_member_business_member_id_foreign` FOREIGN KEY (`business_member_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for action_business_role
-- ----------------------------
DROP TABLE IF EXISTS `action_business_role`;
CREATE TABLE `action_business_role` (
  `action_id` int(10) unsigned NOT NULL,
  `business_role_id` int(10) unsigned NOT NULL,
  KEY `action_business_role_action_id_foreign` (`action_id`),
  KEY `action_business_role_business_role_id_foreign` (`business_role_id`),
  CONSTRAINT `action_business_role_action_id_foreign` FOREIGN KEY (`action_id`) REFERENCES `actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `action_business_role_business_role_id_foreign` FOREIGN KEY (`business_role_id`) REFERENCES `business_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for actions
-- ----------------------------
DROP TABLE IF EXISTS `actions`;
CREATE TABLE `actions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tag` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `details` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `actions_tag_unique` (`tag`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for active_users
-- ----------------------------
DROP TABLE IF EXISTS `active_users`;
CREATE TABLE `active_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `last_use` date NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1179952 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for affiliate_badge
-- ----------------------------
DROP TABLE IF EXISTS `affiliate_badge`;
CREATE TABLE `affiliate_badge` (
  `badge_id` int(10) unsigned NOT NULL,
  `affiliate_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`badge_id`,`affiliate_id`),
  KEY `affiliate_badge_affiliate_id_foreign` (`affiliate_id`),
  CONSTRAINT `affiliate_badge_affiliate_id_foreign` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `affiliate_badge_badge_id_foreign` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for affiliate_notification_logs
-- ----------------------------
DROP TABLE IF EXISTS `affiliate_notification_logs`;
CREATE TABLE `affiliate_notification_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `notification_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `notification_body` text COLLATE utf8_unicode_ci NOT NULL,
  `notification_type` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `internal_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `internal_description` text COLLATE utf8_unicode_ci,
  `notification_category` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `affiliate_info_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `filter_options` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `affiliate_info_file_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `delevery_channel` text COLLATE utf8_unicode_ci,
  `status` enum('active','inactive') COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1331 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for affiliate_report
-- ----------------------------
DROP TABLE IF EXISTS `affiliate_report`;
CREATE TABLE `affiliate_report` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile` varchar(14) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gender` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tags` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_ambassador` tinyint(1) NOT NULL DEFAULT '0',
  `has_ambassador` tinyint(1) NOT NULL DEFAULT '0',
  `ambassador_id` int(10) unsigned DEFAULT NULL,
  `ambassador_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ambassador_mobile` varchar(14) COLLATE utf8_unicode_ci DEFAULT NULL,
  `under_ambassador_since` datetime DEFAULT NULL,
  `store_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `location` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `wallet` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `payment_amount_this_week` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `total_referred` smallint(5) unsigned DEFAULT NULL,
  `successfully_referred` smallint(5) unsigned DEFAULT NULL,
  `total_earned` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `total_paid` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `last_paid` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `banking_info` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `banking_info_verification_status` tinyint(1) NOT NULL DEFAULT '0',
  `verification_status` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `suspension_status` tinyint(1) NOT NULL DEFAULT '0',
  `fake_referral_counter` smallint(5) unsigned DEFAULT NULL,
  `last_suspended_date` datetime DEFAULT NULL,
  `number_of_successful_lead` smallint(5) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `report_updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for affiliate_status_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `affiliate_status_change_logs`;
CREATE TABLE `affiliate_status_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `affiliate_id` int(10) unsigned NOT NULL,
  `from` enum('pending','verified','unverified','rejected') COLLATE utf8_unicode_ci NOT NULL,
  `to` enum('pending','verified','unverified','rejected') COLLATE utf8_unicode_ci NOT NULL,
  `reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `affiliate_status_change_logs_affiliate_id_foreign` (`affiliate_id`),
  CONSTRAINT `affiliate_status_change_logs_affiliate_id_foreign` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=111696 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for affiliate_suspensions
-- ----------------------------
DROP TABLE IF EXISTS `affiliate_suspensions`;
CREATE TABLE `affiliate_suspensions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `affiliate_id` int(10) unsigned NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `affiliate_suspensions_affiliate_id_foreign` (`affiliate_id`),
  CONSTRAINT `affiliate_suspensions_affiliate_id_foreign` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for affiliate_transactions
-- ----------------------------
DROP TABLE IF EXISTS `affiliate_transactions`;
CREATE TABLE `affiliate_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `affiliate_id` int(10) unsigned NOT NULL,
  `affiliation_type` enum('App\\Models\\Affiliation','App\\Models\\PartnerAffiliation','App\\Models\\Partner') COLLATE utf8_unicode_ci DEFAULT NULL,
  `affiliation_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_gifted` tinyint(1) NOT NULL DEFAULT '0',
  `log` text COLLATE utf8_unicode_ci NOT NULL,
  `transaction_details` text COLLATE utf8_unicode_ci,
  `third_party_transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `amount` decimal(11,2) unsigned NOT NULL,
  `balance` decimal(11,2) NOT NULL DEFAULT '0.00',
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `affiliate_transactions_affiliate_id_foreign` (`affiliate_id`),
  KEY `affiliate_transactions_affiliation_index` (`affiliation_type`,`affiliation_id`),
  KEY `affiliate_transactions_third_party_transaction_id_index` (`third_party_transaction_id`),
  CONSTRAINT `affiliate_transactions_affiliate_id_foreign` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12517745 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for affiliate_withdrawal_requests
-- ----------------------------
DROP TABLE IF EXISTS `affiliate_withdrawal_requests`;
CREATE TABLE `affiliate_withdrawal_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `affiliate_id` int(10) unsigned NOT NULL,
  `amount` decimal(11,2) unsigned NOT NULL,
  `status` enum('pending','approval_pending','approved','rejected','completed','failed','expired','cancelled') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_method` enum('bkash','bank') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'bkash',
  `payment_info` text COLLATE utf8_unicode_ci,
  `last_fail_reason` longtext COLLATE utf8_unicode_ci,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `affiliate_withdrawal_requests_affiliate_id_foreign` (`affiliate_id`),
  CONSTRAINT `affiliate_withdrawal_requests_affiliate_id_foreign` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for affiliates
-- ----------------------------
DROP TABLE IF EXISTS `affiliates`;
CREATE TABLE `affiliates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` int(10) unsigned NOT NULL,
  `is_ambassador` tinyint(1) NOT NULL DEFAULT '0',
  `is_moderator` tinyint(1) NOT NULL DEFAULT '0',
  `ambassador_id` int(10) unsigned DEFAULT NULL,
  `previous_ambassador_id` int(10) unsigned DEFAULT NULL,
  `under_ambassador_since` datetime DEFAULT NULL,
  `ambassador_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `acquisition_cost` decimal(8,2) NOT NULL,
  `store_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `geolocation` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `wallet` decimal(11,2) NOT NULL,
  `robi_topup_wallet` decimal(11,2) NOT NULL DEFAULT '0.00',
  `payment_amount` decimal(11,2) unsigned DEFAULT NULL,
  `total_earning` decimal(11,2) unsigned NOT NULL,
  `total_gifted_amount` decimal(11,2) unsigned NOT NULL,
  `total_gifted_number` int(10) unsigned NOT NULL,
  `banking_info` text COLLATE utf8_unicode_ci,
  `is_banking_info_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verification_status` enum('pending','verified','unverified','rejected') COLLATE utf8_unicode_ci DEFAULT 'pending',
  `reject_reason` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_suspended` tinyint(1) NOT NULL DEFAULT '0',
  `fake_referral_counter` smallint(6) NOT NULL DEFAULT '0',
  `last_suspended_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `affiliates_profile_id_unique` (`profile_id`),
  UNIQUE KEY `affiliates_remember_token_unique` (`remember_token`),
  UNIQUE KEY `affiliates_ambassador_code_unique` (`ambassador_code`) USING BTREE,
  KEY `affiliates_location_id_foreign` (`location_id`),
  KEY `affiliates_ambassador_id_foreign` (`ambassador_id`),
  CONSTRAINT `affiliates_ambassador_id_foreign` FOREIGN KEY (`ambassador_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `affiliates_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `affiliates_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=194415 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for affiliation_logs
-- ----------------------------
DROP TABLE IF EXISTS `affiliation_logs`;
CREATE TABLE `affiliation_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `affiliation_id` int(10) unsigned NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `affiliation_logs_affiliation_id_foreign` (`affiliation_id`),
  CONSTRAINT `affiliation_logs_affiliation_id_foreign` FOREIGN KEY (`affiliation_id`) REFERENCES `affiliations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3051 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for affiliation_milestones
-- ----------------------------
DROP TABLE IF EXISTS `affiliation_milestones`;
CREATE TABLE `affiliation_milestones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `no_of_order` smallint(5) unsigned NOT NULL,
  `amount` mediumint(8) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `affiliation_milestones_no_of_order_unique` (`no_of_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for affiliation_status_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `affiliation_status_change_logs`;
CREATE TABLE `affiliation_status_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `affiliation_id` int(10) unsigned NOT NULL,
  `from_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `to_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `log` text COLLATE utf8_unicode_ci NOT NULL,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `affiliation_status_change_logs_affiliation_id_foreign` (`affiliation_id`),
  CONSTRAINT `affiliation_status_change_logs_affiliation_id_foreign` FOREIGN KEY (`affiliation_id`) REFERENCES `affiliations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=60015 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for affiliations
-- ----------------------------
DROP TABLE IF EXISTS `affiliations`;
CREATE TABLE `affiliations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `affiliate_id` int(10) unsigned NOT NULL,
  `customer_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `service` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('pending','converted','in_progress','follow_up','cancelled','successful','rejected') COLLATE utf8_unicode_ci DEFAULT 'pending',
  `acquisition_cost` decimal(8,2) DEFAULT NULL,
  `is_fake` tinyint(1) NOT NULL DEFAULT '0',
  `reject_reason` enum('invalid','fake','lack_of_capacity','no_response','price_high','sp_not_available','service_unavailable') COLLATE utf8_unicode_ci DEFAULT NULL,
  `unreachable_sms_sent` tinyint(4) NOT NULL DEFAULT '0',
  `unreachable_sms_sent_agent` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `affiliations_affiliate_id_foreign` (`affiliate_id`),
  KEY `affiliations_status_index` (`status`),
  CONSTRAINT `affiliations_affiliate_id_foreign` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=87648 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for affiliations_report
-- ----------------------------
DROP TABLE IF EXISTS `affiliations_report`;
CREATE TABLE `affiliations_report` (
  `id` int(10) unsigned NOT NULL,
  `agent_id` int(10) unsigned DEFAULT NULL,
  `agent_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ambassador_id` int(10) unsigned DEFAULT NULL,
  `order_code` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_mobile` varchar(14) COLLATE utf8_unicode_ci DEFAULT NULL,
  `service_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `conversion_status` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fake_status` tinyint(1) NOT NULL DEFAULT '0',
  `tags` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reject_reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `total_sales` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `commission_received_by_agents` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `first_response_time` smallint(5) unsigned DEFAULT NULL,
  `converter_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `converter_time` smallint(5) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `report_updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for announcements
-- ----------------------------
DROP TABLE IF EXISTS `announcements`;
CREATE TABLE `announcements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `title` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `short_description` longtext COLLATE utf8_unicode_ci,
  `long_description` longtext COLLATE utf8_unicode_ci,
  `target_type` enum('all','department','employee','employee_type') COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('scheduled','published','expired') COLLATE utf8_unicode_ci DEFAULT NULL,
  `target_id` json DEFAULT NULL,
  `type` enum('event','holiday','financial','others') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'event',
  `is_published` tinyint(4) NOT NULL DEFAULT '1',
  `scheduled_for` enum('now','later') COLLATE utf8_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_date` datetime NOT NULL,
  `end_time` time DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `announcements_business_id_foreign` (`business_id`),
  CONSTRAINT `announcements_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=782 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for api_requests
-- ----------------------------
DROP TABLE IF EXISTS `api_requests`;
CREATE TABLE `api_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `route` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `google_advertising_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imei` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imsi` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `msisdn` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `device_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `uuid` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firebase_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lat` double(8,6) DEFAULT NULL,
  `lng` double(8,6) DEFAULT NULL,
  `portal` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `portal_version` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `api_requests_ip_index` (`ip`),
  KEY `api_requests_user_agent_index` (`user_agent`),
  KEY `api_requests_google_advertising_id_index` (`google_advertising_id`),
  KEY `api_requests_imei_index` (`imei`),
  KEY `api_requests_imsi_index` (`imsi`),
  KEY `api_requests_msisdn_index` (`msisdn`),
  KEY `api_requests_device_id_index` (`device_id`),
  KEY `api_requests_lat_index` (`lat`),
  KEY `api_requests_lng_index` (`lng`),
  KEY `api_requests_portal_index` (`portal`),
  KEY `api_requests_portal_version_index` (`portal_version`),
  KEY `api_requests_uuid_index` (`uuid`),
  KEY `api_requests_firebase_token_index` (`firebase_token`)
) ENGINE=InnoDB AUTO_INCREMENT=8364686 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for app_versions
-- ----------------------------
DROP TABLE IF EXISTS `app_versions`;
CREATE TABLE `app_versions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8_unicode_ci,
  `tag` enum('customer_app_android','manager_app_android','resource_app_android','customer_app_ios','bondhu_app_android','rider_app_android','employee_app_android','employee_app_ios','resource_app_ios','bondhu_app_ios') COLLATE utf8_unicode_ci DEFAULT NULL,
  `platform` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `package_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `version_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `version_code` int(10) unsigned NOT NULL,
  `lowest_upgradable_version_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_critical` tinyint(1) NOT NULL DEFAULT '0',
  `image_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `height` int(10) unsigned DEFAULT NULL,
  `width` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for appreciations
-- ----------------------------
DROP TABLE IF EXISTS `appreciations`;
CREATE TABLE `appreciations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `receiver_id` int(10) unsigned NOT NULL,
  `giver_id` int(10) unsigned NOT NULL,
  `sticker_id` int(10) unsigned NOT NULL,
  `note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `appreciations_receiver_id_foreign` (`receiver_id`),
  KEY `appreciations_giver_id_foreign` (`giver_id`),
  KEY `appreciations_sticker_id_foreign` (`sticker_id`),
  CONSTRAINT `appreciations_giver_id_foreign` FOREIGN KEY (`giver_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `appreciations_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `appreciations_sticker_id_foreign` FOREIGN KEY (`sticker_id`) REFERENCES `stickers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2260 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for approval_flow_approvers
-- ----------------------------
DROP TABLE IF EXISTS `approval_flow_approvers`;
CREATE TABLE `approval_flow_approvers` (
  `approval_flow_id` int(10) unsigned NOT NULL,
  `business_member_id` int(10) unsigned NOT NULL,
  KEY `approval_flow_approvers_approval_flow_id_foreign` (`approval_flow_id`),
  KEY `approval_flow_approvers_business_member_id_foreign` (`business_member_id`),
  CONSTRAINT `approval_flow_approvers_approval_flow_id_foreign` FOREIGN KEY (`approval_flow_id`) REFERENCES `approval_flows` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `approval_flow_approvers_business_member_id_foreign` FOREIGN KEY (`business_member_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for approval_flows
-- ----------------------------
DROP TABLE IF EXISTS `approval_flows`;
CREATE TABLE `approval_flows` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` enum('leave','expense','trip') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'leave',
  `business_department_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `approval_flows_type_business_department_id_unique` (`type`,`business_department_id`),
  KEY `approval_flows_business_department_id_foreign` (`business_department_id`),
  CONSTRAINT `approval_flows_business_department_id_foreign` FOREIGN KEY (`business_department_id`) REFERENCES `business_departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for approval_requests
-- ----------------------------
DROP TABLE IF EXISTS `approval_requests`;
CREATE TABLE `approval_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `requestable_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `requestable_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('pending','accepted','rejected') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `approver_id` int(10) unsigned NOT NULL,
  `order` int(11) DEFAULT NULL,
  `is_notified` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approval_requests_approver_id_foreign` (`approver_id`),
  CONSTRAINT `approval_requests_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18315 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for approval_setting_approvers
-- ----------------------------
DROP TABLE IF EXISTS `approval_setting_approvers`;
CREATE TABLE `approval_setting_approvers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `approval_setting_id` int(10) unsigned NOT NULL,
  `type` enum('lm','hod','employee') COLLATE utf8_unicode_ci NOT NULL,
  `type_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approval_setting_approvers_approval_setting_id_foreign` (`approval_setting_id`),
  CONSTRAINT `approval_setting_approvers_approval_setting_id_foreign` FOREIGN KEY (`approval_setting_id`) REFERENCES `approval_settings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1079 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for approval_setting_modules
-- ----------------------------
DROP TABLE IF EXISTS `approval_setting_modules`;
CREATE TABLE `approval_setting_modules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `approval_setting_id` int(10) unsigned NOT NULL,
  `modules` enum('leave','expense','support') COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approval_setting_modules_approval_setting_id_foreign` (`approval_setting_id`),
  CONSTRAINT `approval_setting_modules_approval_setting_id_foreign` FOREIGN KEY (`approval_setting_id`) REFERENCES `approval_settings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=536 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for approval_settings
-- ----------------------------
DROP TABLE IF EXISTS `approval_settings`;
CREATE TABLE `approval_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `target_type` enum('global','global_module','department','employee') COLLATE utf8_unicode_ci DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `note` longtext COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approval_settings_business_id_foreign` (`business_id`),
  CONSTRAINT `approval_settings_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=321 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for article_like_dislikes
-- ----------------------------
DROP TABLE IF EXISTS `article_like_dislikes`;
CREATE TABLE `article_like_dislikes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(10) unsigned DEFAULT NULL,
  `user_type` enum('App\\Models\\Customer','App\\Models\\Affiliate','App\\Models\\Member') COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `is_like` tinyint(4) NOT NULL DEFAULT '1',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `article_like_dislikes_article_id_foreign` (`article_id`),
  KEY `article_like_dislikes_user_type_index` (`user_type`),
  KEY `article_like_dislikes_user_id_index` (`user_id`),
  CONSTRAINT `article_like_dislikes_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for article_type_article
-- ----------------------------
DROP TABLE IF EXISTS `article_type_article`;
CREATE TABLE `article_type_article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `article_type_id` int(10) unsigned DEFAULT NULL,
  `article_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `article_type_article_article_type_id_foreign` (`article_type_id`),
  KEY `article_type_article_article_id_foreign` (`article_id`),
  CONSTRAINT `article_type_article_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `article_type_article_article_type_id_foreign` FOREIGN KEY (`article_type_id`) REFERENCES `article_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3463 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for article_types
-- ----------------------------
DROP TABLE IF EXISTS `article_types`;
CREATE TABLE `article_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_published` tinyint(4) NOT NULL DEFAULT '1',
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','business-portal','business-app','smanager-faq-for-sheba-user','sbusiness-faq-for-sheba-user','sbondhu-faq-for-sheba-user','hr-and-admin-faq-for-sheba-user','sheba-faq-for-sheba-user','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for articles
-- ----------------------------
DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `video_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/bulk/jpg/Sub-catagory/15/600.jpg',
  `is_published` tinyint(4) NOT NULL DEFAULT '1',
  `project_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=812 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for artisan_leaves
-- ----------------------------
DROP TABLE IF EXISTS `artisan_leaves`;
CREATE TABLE `artisan_leaves` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `artisan_id` int(11) NOT NULL,
  `artisan_type` enum('resource','partner') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'partner',
  `start` datetime NOT NULL,
  `end` datetime DEFAULT NULL,
  `portal_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `artisan_leaves_artisan_id_index` (`artisan_id`),
  KEY `artisan_leaves_artisan_type_index` (`artisan_type`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for attachments
-- ----------------------------
DROP TABLE IF EXISTS `attachments`;
CREATE TABLE `attachments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attachable_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attachable_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attachments_attachable_index` (`attachable_type`,`attachable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28752 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for attendance_action_logs
-- ----------------------------
DROP TABLE IF EXISTS `attendance_action_logs`;
CREATE TABLE `attendance_action_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attendance_id` int(10) unsigned NOT NULL,
  `action` enum('checkin','checkout') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'checkin',
  `status` enum('on_time','late','absent','left_early','left_timely') COLLATE utf8_unicode_ci DEFAULT NULL,
  `in_grace_period` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `device_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_remote` tinyint(3) unsigned DEFAULT NULL,
  `remote_mode` enum('home','field','no_location') COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `location` json DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_action_logs_attendance_id_foreign` (`attendance_id`),
  CONSTRAINT `attendance_action_logs_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=557950 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for attendance_override_logs
-- ----------------------------
DROP TABLE IF EXISTS `attendance_override_logs`;
CREATE TABLE `attendance_override_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attendance_id` int(10) unsigned NOT NULL,
  `action` enum('checkin','checkout') COLLATE utf8_unicode_ci NOT NULL,
  `previous_time` time DEFAULT NULL,
  `new_time` time NOT NULL,
  `previous_status` enum('on_time','late','absent','left_early','left_timely') COLLATE utf8_unicode_ci DEFAULT NULL,
  `new_status` enum('on_time','late','absent','left_early','left_timely') COLLATE utf8_unicode_ci NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_override_logs_attendance_id_foreign` (`attendance_id`),
  CONSTRAINT `attendance_override_logs_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=933 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for attendance_summary
-- ----------------------------
DROP TABLE IF EXISTS `attendance_summary`;
CREATE TABLE `attendance_summary` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_member_id` int(10) unsigned NOT NULL,
  `total_present` int(11) DEFAULT NULL,
  `total_late_checkin` int(11) DEFAULT NULL,
  `total_early_checkout` int(11) DEFAULT NULL,
  `total_checkin_grace` int(11) DEFAULT NULL,
  `total_checkout_grace` int(11) DEFAULT NULL,
  `total_leave` decimal(8,2) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `logs` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_summary_business_member_id_foreign` (`business_member_id`),
  CONSTRAINT `attendance_summary_business_member_id_foreign` FOREIGN KEY (`business_member_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for attendances
-- ----------------------------
DROP TABLE IF EXISTS `attendances`;
CREATE TABLE `attendances` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_member_id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `checkin_time` time NOT NULL,
  `checkout_time` time DEFAULT NULL,
  `staying_time_in_minutes` decimal(8,2) DEFAULT NULL,
  `overtime_in_minutes` decimal(8,2) DEFAULT NULL,
  `status` enum('on_time','late','absent','left_early','left_timely') COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_attendance_reconciled` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendances_business_member_id_date_unique` (`business_member_id`,`date`),
  CONSTRAINT `attendances_business_member_id_foreign` FOREIGN KEY (`business_member_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=313374 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for authentication_requests
-- ----------------------------
DROP TABLE IF EXISTS `authentication_requests`;
CREATE TABLE `authentication_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` int(10) unsigned DEFAULT NULL,
  `api_request_id` int(10) unsigned DEFAULT NULL,
  `method` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `purpose` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'authorization',
  `status` enum('success','fail') COLLATE utf8_unicode_ci NOT NULL,
  `failed_reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `authentication_requests_profile_id_foreign` (`profile_id`),
  KEY `authentication_requests_api_request_id_foreign` (`api_request_id`),
  KEY `authentication_requests_method_index` (`method`),
  KEY `authentication_requests_status_index` (`status`),
  KEY `authentication_requests_failed_reason_index` (`failed_reason`),
  KEY `authentication_requests_purpose_index` (`purpose`),
  CONSTRAINT `authentication_requests_api_request_id_foreign` FOREIGN KEY (`api_request_id`) REFERENCES `api_requests` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `authentication_requests_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19720251 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for authorization_requests
-- ----------------------------
DROP TABLE IF EXISTS `authorization_requests`;
CREATE TABLE `authorization_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` int(10) unsigned DEFAULT NULL,
  `authentication_request_id` int(10) unsigned DEFAULT NULL,
  `api_request_id` int(10) unsigned DEFAULT NULL,
  `purpose` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('success','fail') COLLATE utf8_unicode_ci NOT NULL,
  `failed_reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `authorization_requests_profile_id_foreign` (`profile_id`),
  KEY `authorization_requests_authentication_request_id_foreign` (`authentication_request_id`),
  KEY `authorization_requests_api_request_id_foreign` (`api_request_id`),
  KEY `authorization_requests_purpose_index` (`purpose`),
  KEY `authorization_requests_status_index` (`status`),
  KEY `authorization_requests_failed_reason_index` (`failed_reason`),
  CONSTRAINT `authorization_requests_api_request_id_foreign` FOREIGN KEY (`api_request_id`) REFERENCES `api_requests` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `authorization_requests_authentication_request_id_foreign` FOREIGN KEY (`authentication_request_id`) REFERENCES `authentication_requests` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `authorization_requests_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6937747 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for authorization_tokens
-- ----------------------------
DROP TABLE IF EXISTS `authorization_tokens`;
CREATE TABLE `authorization_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(2036) COLLATE utf8_unicode_ci NOT NULL,
  `authorization_request_id` int(10) unsigned NOT NULL,
  `valid_till` datetime NOT NULL,
  `refresh_valid_till` datetime NOT NULL,
  `is_blacklisted` tinyint(4) NOT NULL DEFAULT '0',
  `blacklisted_reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `authorization_tokens_authorization_request_id_foreign` (`authorization_request_id`),
  KEY `authorization_tokens_token_index` (`token`(1024)),
  KEY `authorization_tokens_valid_till_index` (`valid_till`),
  KEY `authorization_tokens_refresh_valid_till_index` (`refresh_valid_till`),
  KEY `authorization_tokens_is_blacklisted_index` (`is_blacklisted`),
  KEY `authorization_tokens_blacklisted_reason_index` (`blacklisted_reason`),
  CONSTRAINT `authorization_tokens_authorization_request_id_foreign` FOREIGN KEY (`authorization_request_id`) REFERENCES `authorization_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5588722 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for automatic_subscription_upgradation_logs
-- ----------------------------
DROP TABLE IF EXISTS `automatic_subscription_upgradation_logs`;
CREATE TABLE `automatic_subscription_upgradation_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `status` enum('successful','failed','migrated_to_light') COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10891819 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for badge_customer
-- ----------------------------
DROP TABLE IF EXISTS `badge_customer`;
CREATE TABLE `badge_customer` (
  `badge_id` int(10) unsigned NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`badge_id`,`customer_id`),
  KEY `badge_customer_customer_id_foreign` (`customer_id`),
  CONSTRAINT `badge_customer_badge_id_foreign` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `badge_customer_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for badge_partner
-- ----------------------------
DROP TABLE IF EXISTS `badge_partner`;
CREATE TABLE `badge_partner` (
  `badge_id` int(10) unsigned NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`badge_id`,`partner_id`),
  KEY `badge_partner_partner_id_foreign` (`partner_id`),
  CONSTRAINT `badge_partner_badge_id_foreign` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `badge_partner_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for badges
-- ----------------------------
DROP TABLE IF EXISTS `badges`;
CREATE TABLE `badges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `for` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for bank_users
-- ----------------------------
DROP TABLE IF EXISTS `bank_users`;
CREATE TABLE `bank_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bank_id` int(10) unsigned NOT NULL,
  `profile_id` int(10) unsigned NOT NULL,
  `remember_token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bank_users_bank_id_foreign` (`bank_id`),
  KEY `bank_users_profile_id_foreign` (`profile_id`),
  CONSTRAINT `bank_users_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bank_users_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for banks
-- ----------------------------
DROP TABLE IF EXISTS `banks`;
CREATE TABLE `banks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `interest_rate` decimal(11,2) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for bid_item_fields
-- ----------------------------
DROP TABLE IF EXISTS `bid_item_fields`;
CREATE TABLE `bid_item_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bid_item_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `short_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `long_description` longtext COLLATE utf8_unicode_ci,
  `input_type` enum('text','textarea','radio','checkbox','number','select') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'text',
  `variables` longtext COLLATE utf8_unicode_ci NOT NULL,
  `result` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bidder_result` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bid_item_fields_bid_item_id_foreign` (`bid_item_id`),
  KEY `bid_item_fields_input_type_index` (`input_type`),
  CONSTRAINT `bid_item_fields_bid_item_id_foreign` FOREIGN KEY (`bid_item_id`) REFERENCES `bid_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=168 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for bid_items
-- ----------------------------
DROP TABLE IF EXISTS `bid_items`;
CREATE TABLE `bid_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bid_id` int(10) unsigned NOT NULL,
  `type` enum('price_quotation','technical_evaluation','company_evaluation') COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bid_items_bid_id_foreign` (`bid_id`),
  KEY `bid_items_type_index` (`type`),
  CONSTRAINT `bid_items_bid_id_foreign` FOREIGN KEY (`bid_id`) REFERENCES `bids` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for bid_status_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `bid_status_change_logs`;
CREATE TABLE `bid_status_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bid_id` int(10) unsigned NOT NULL,
  `from_status` enum('pending','rejected','accepted','drafted','sent','awarded') COLLATE utf8_unicode_ci NOT NULL,
  `to_status` enum('pending','rejected','accepted','drafted','sent','awarded') COLLATE utf8_unicode_ci NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bid_status_change_logs_bid_id_foreign` (`bid_id`),
  KEY `bid_status_change_logs_from_status_index` (`from_status`),
  KEY `bid_status_change_logs_to_status_index` (`to_status`),
  CONSTRAINT `bid_status_change_logs_bid_id_foreign` FOREIGN KEY (`bid_id`) REFERENCES `bids` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3873 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for bids
-- ----------------------------
DROP TABLE IF EXISTS `bids`;
CREATE TABLE `bids` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `procurement_id` int(10) unsigned NOT NULL,
  `bidder_id` int(10) unsigned NOT NULL,
  `bidder_type` enum('App\\Models\\Affiliate','App\\Models\\Partner','App\\Models\\Resource') COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('pending','rejected','accepted','drafted','sent','awarded') COLLATE utf8_unicode_ci NOT NULL,
  `terms` longtext COLLATE utf8_unicode_ci NOT NULL,
  `policies` longtext COLLATE utf8_unicode_ci NOT NULL,
  `proposal` longtext COLLATE utf8_unicode_ci NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `bidder_price` decimal(8,2) NOT NULL,
  `commission_percentage` decimal(8,2) DEFAULT NULL,
  `is_favourite` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bids_procurement_id_foreign` (`procurement_id`),
  KEY `bids_bidder_id_index` (`bidder_id`),
  KEY `bids_bidder_type_index` (`bidder_type`),
  CONSTRAINT `bids_procurement_id_foreign` FOREIGN KEY (`procurement_id`) REFERENCES `procurements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3070 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for block_grid
-- ----------------------------
DROP TABLE IF EXISTS `block_grid`;
CREATE TABLE `block_grid` (
  `grid_id` int(10) unsigned NOT NULL,
  `block_id` int(10) unsigned NOT NULL,
  `location_id` int(10) unsigned NOT NULL,
  `order` smallint(6) DEFAULT NULL,
  UNIQUE KEY `grid_block_location_unique` (`grid_id`,`block_id`,`location_id`),
  KEY `block_grid_block_id_foreign` (`block_id`),
  KEY `block_grid_location_id_foreign` (`location_id`),
  CONSTRAINT `block_grid_block_id_foreign` FOREIGN KEY (`block_id`) REFERENCES `blocks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `block_grid_grid_id_foreign` FOREIGN KEY (`grid_id`) REFERENCES `grids` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `block_grid_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for blocks
-- ----------------------------
DROP TABLE IF EXISTS `blocks`;
CREATE TABLE `blocks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `item_id` int(11) NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for blog_posts
-- ----------------------------
DROP TABLE IF EXISTS `blog_posts`;
CREATE TABLE `blog_posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `short_description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `long_description` longtext COLLATE utf8_unicode_ci,
  `thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_published` tinyint(4) NOT NULL DEFAULT '1',
  `owner_type` enum('App\\Models\\Service','App\\Models\\Category','App\\Models\\CategoryGroup') COLLATE utf8_unicode_ci NOT NULL,
  `owner_id` int(11) NOT NULL,
  `target_link` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `blog_posts_owner_type_index` (`owner_type`),
  KEY `blog_posts_owner_id_index` (`owner_id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for bondhu_bulk_point_distribute_logs
-- ----------------------------
DROP TABLE IF EXISTS `bondhu_bulk_point_distribute_logs`;
CREATE TABLE `bondhu_bulk_point_distribute_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `bondhu_users_count` int(11) NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for bondhu_icons
-- ----------------------------
DROP TABLE IF EXISTS `bondhu_icons`;
CREATE TABLE `bondhu_icons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name_en` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name_bn` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `target_type` enum('dynamic_module','static_module') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'dynamic_module',
  `option` enum('single','multiple') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'multiple',
  `values` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('Published','Unpublished') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Unpublished',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for bondhucms_changelogs
-- ----------------------------
DROP TABLE IF EXISTS `bondhucms_changelogs`;
CREATE TABLE `bondhucms_changelogs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(10) unsigned NOT NULL,
  `item_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `change_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `from` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `to` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `log` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for bondhucms_icons
-- ----------------------------
DROP TABLE IF EXISTS `bondhucms_icons`;
CREATE TABLE `bondhucms_icons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name_en` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name_bn` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `target_type` enum('dynamic_module','static_module') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'dynamic_module',
  `target` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `option` enum('single','multiple') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'multiple',
  `values` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('Published','Unpublished') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Unpublished',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for bondhucms_static_modules
-- ----------------------------
DROP TABLE IF EXISTS `bondhucms_static_modules`;
CREATE TABLE `bondhucms_static_modules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name_en` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name_bn` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `module_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `agent_commission` double NOT NULL,
  `is_agent_commission_percentage` tinyint(1) NOT NULL DEFAULT '1',
  `agent_commission_cap` double DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bondhucms_static_modules_module_code_unique` (`module_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for bonus_logs
-- ----------------------------
DROP TABLE IF EXISTS `bonus_logs`;
CREATE TABLE `bonus_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_type` enum('App\\Models\\Customer','App\\Models\\Partner') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'App\\Models\\Customer',
  `user_id` int(10) unsigned NOT NULL,
  `type` enum('Credit','Debit') COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `valid_till` timestamp NULL DEFAULT NULL,
  `spent_on_type` enum('App\\Models\\PartnerOrder','App\\Models\\TopUpOrder','App\\Models\\PartnerSubscriptionPackage','Sheba\\Utility\\UtilityOrder','App\\Models\\Transport\\TransportTicketOrder','App\\Models\\MovieTicketOrder') COLLATE utf8_unicode_ci DEFAULT NULL,
  `spent_on_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bonuses_user_index` (`user_type`,`user_id`),
  KEY `bonuses_user_type_index` (`user_type`,`user_id`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=2027321 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for bonuses
-- ----------------------------
DROP TABLE IF EXISTS `bonuses`;
CREATE TABLE `bonuses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_type` enum('App\\Models\\Customer','App\\Models\\Partner') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'App\\Models\\Customer',
  `user_id` int(10) unsigned NOT NULL,
  `type` enum('cash','point') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'cash',
  `amount` decimal(11,2) NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('valid','used','invalid') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'valid',
  `valid_till` timestamp NOT NULL,
  `spent_on_type` enum('App\\Models\\PartnerOrder','App\\Models\\TopUpOrder','App\\Models\\PartnerSubscriptionPackage','Sheba\\Utility\\UtilityOrder','App\\Models\\Transport\\TransportTicketOrder','App\\Models\\MovieTicketOrder') COLLATE utf8_unicode_ci DEFAULT NULL,
  `spent_on_id` int(10) unsigned DEFAULT NULL,
  `used_date` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bonuses_user_index` (`user_type`,`user_id`),
  KEY `bonuses_user_type_status_index` (`user_type`,`user_id`,`type`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=1231986 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for bugs
-- ----------------------------
DROP TABLE IF EXISTS `bugs`;
CREATE TABLE `bugs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `issue` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `issue_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `assignee_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `assignee_email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `assignee_phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `project` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `project_owner_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `project_owner_email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `project_owner_phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('pending','solved') COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_action_logs
-- ----------------------------
DROP TABLE IF EXISTS `business_action_logs`;
CREATE TABLE `business_action_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `transaction_type` enum('Debit','Credit') COLLATE utf8_unicode_ci DEFAULT NULL,
  `action_type` enum('wallet_transaction','prepaid_max_limit','tagged_kam','topup_commission') COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(11,2) DEFAULT NULL,
  `from_amount` decimal(8,2) DEFAULT NULL,
  `to_amount` decimal(8,2) DEFAULT NULL,
  `tagged_kam` int(10) unsigned DEFAULT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_action_logs_business_id_foreign` (`business_id`),
  CONSTRAINT `business_action_logs_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1106 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_attendance_types
-- ----------------------------
DROP TABLE IF EXISTS `business_attendance_types`;
CREATE TABLE `business_attendance_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `attendance_type` enum('remote','ip_based') COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `business_attendance_types_business_id_attendance_type_unique` (`business_id`,`attendance_type`),
  CONSTRAINT `business_attendance_types_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3057 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_bank_informations
-- ----------------------------
DROP TABLE IF EXISTS `business_bank_informations`;
CREATE TABLE `business_bank_informations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `acc_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `acc_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `other_details` text COLLATE utf8_unicode_ci,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_bank_informations_business_id_foreign` (`business_id`),
  CONSTRAINT `business_bank_informations_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_categories
-- ----------------------------
DROP TABLE IF EXISTS `business_categories`;
CREATE TABLE `business_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `publication_status` tinyint(1) NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_categories_parent_id_foreign` (`parent_id`),
  CONSTRAINT `business_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_delivery_addresses
-- ----------------------------
DROP TABLE IF EXISTS `business_delivery_addresses`;
CREATE TABLE `business_delivery_addresses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_delivery_addresses_business_id_foreign` (`business_id`),
  CONSTRAINT `business_delivery_addresses_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_departments
-- ----------------------------
DROP TABLE IF EXISTS `business_departments`;
CREATE TABLE `business_departments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `abbreviation` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_published` int(11) NOT NULL DEFAULT '1',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_departments_business_id_foreign` (`business_id`),
  CONSTRAINT `business_departments_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21744 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_holidays
-- ----------------------------
DROP TABLE IF EXISTS `business_holidays`;
CREATE TABLE `business_holidays` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_holidays_business_id_foreign` (`business_id`),
  CONSTRAINT `business_holidays_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=133687 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_join_requests
-- ----------------------------
DROP TABLE IF EXISTS `business_join_requests`;
CREATE TABLE `business_join_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `mobile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `request_message` longtext COLLATE utf8_unicode_ci,
  `status` enum('pending','successful','failed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_join_requests_business_id_foreign` (`business_id`),
  CONSTRAINT `business_join_requests_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_member
-- ----------------------------
DROP TABLE IF EXISTS `business_member`;
CREATE TABLE `business_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned DEFAULT NULL,
  `member_id` int(10) unsigned NOT NULL,
  `employee_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `manager_id` int(10) unsigned DEFAULT NULL,
  `type` enum('Admin','Manager','Editor','Employee') COLLATE utf8_unicode_ci DEFAULT NULL,
  `join_date` date DEFAULT NULL,
  `previous_institution` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `designation` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `employee_type` enum('permanent','on_probation','contractual','intern') COLLATE utf8_unicode_ci DEFAULT NULL,
  `department` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `business_role_id` int(10) unsigned DEFAULT NULL,
  `grade` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('active','inactive','invited') COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_super` tinyint(4) NOT NULL DEFAULT '0',
  `is_payroll_enable` int(11) NOT NULL DEFAULT '0',
  `early_bird_counter` int(11) NOT NULL DEFAULT '0',
  `late_loteef_counter` int(11) NOT NULL DEFAULT '0',
  `deleted_at` datetime DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `business_member_mobile_unique` (`mobile`),
  KEY `business_member_business_id_foreign` (`business_id`),
  KEY `business_member_member_id_foreign` (`member_id`),
  KEY `business_member_business_role_id_foreign` (`business_role_id`),
  KEY `business_member_manager_id_foreign` (`manager_id`),
  CONSTRAINT `business_member_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `business_member_business_role_id_foreign` FOREIGN KEY (`business_role_id`) REFERENCES `business_roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `business_member_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `business_member` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `business_member_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10364 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_member_badges
-- ----------------------------
DROP TABLE IF EXISTS `business_member_badges`;
CREATE TABLE `business_member_badges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_member_id` int(10) unsigned NOT NULL,
  `badge` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_seen` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_member_badges_business_member_id_foreign` (`business_member_id`),
  CONSTRAINT `business_member_badges_business_member_id_foreign` FOREIGN KEY (`business_member_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4336 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_member_bkash_info
-- ----------------------------
DROP TABLE IF EXISTS `business_member_bkash_info`;
CREATE TABLE `business_member_bkash_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_member_id` int(10) unsigned NOT NULL,
  `account_no` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_member_bkash_info_business_member_id_foreign` (`business_member_id`),
  CONSTRAINT `business_member_bkash_info_business_member_id_foreign` FOREIGN KEY (`business_member_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_member_leave_types
-- ----------------------------
DROP TABLE IF EXISTS `business_member_leave_types`;
CREATE TABLE `business_member_leave_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_member_id` int(10) unsigned NOT NULL,
  `leave_type_id` int(10) unsigned NOT NULL,
  `total_days` double NOT NULL,
  `note` text COLLATE utf8_unicode_ci,
  `is_auto_prorated` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `business_member_leave_type_unique` (`business_member_id`,`leave_type_id`),
  KEY `le_ty_id` (`leave_type_id`),
  CONSTRAINT `bu_me_id` FOREIGN KEY (`business_member_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `le_ty_id` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1684 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_member_status_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `business_member_status_change_logs`;
CREATE TABLE `business_member_status_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_member_id` int(10) unsigned NOT NULL,
  `from_status` enum('active','inactive','invited') COLLATE utf8_unicode_ci NOT NULL,
  `to_status` enum('active','inactive','invited') COLLATE utf8_unicode_ci NOT NULL,
  `log` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_member_status_change_logs_business_member_id_foreign` (`business_member_id`),
  KEY `business_member_status_change_logs_from_status_index` (`from_status`),
  KEY `business_member_status_change_logs_to_status_index` (`to_status`),
  CONSTRAINT `business_member_status_change_logs_business_member_id_foreign` FOREIGN KEY (`business_member_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=362 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_notification_histories
-- ----------------------------
DROP TABLE IF EXISTS `business_notification_histories`;
CREATE TABLE `business_notification_histories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `business_member_id` int(10) unsigned NOT NULL,
  `action` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('pending','success') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `device_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_notification_histories_business_id_foreign` (`business_id`),
  KEY `business_notification_histories_business_member_id_foreign` (`business_member_id`),
  CONSTRAINT `business_notification_histories_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `business_notification_histories_business_member_id_foreign` FOREIGN KEY (`business_member_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_office_hours
-- ----------------------------
DROP TABLE IF EXISTS `business_office_hours`;
CREATE TABLE `business_office_hours` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `type` enum('as_per_calendar','fixed') COLLATE utf8_unicode_ci DEFAULT NULL,
  `number_of_days` int(10) unsigned DEFAULT NULL,
  `is_weekend_included` tinyint(1) NOT NULL DEFAULT '1',
  `is_start_grace_time_enable` tinyint(1) NOT NULL DEFAULT '0',
  `start_time` time NOT NULL,
  `start_grace_time` int(10) unsigned DEFAULT NULL,
  `is_end_grace_time_enable` tinyint(1) NOT NULL DEFAULT '0',
  `end_time` time NOT NULL,
  `end_grace_time` int(10) unsigned DEFAULT NULL,
  `is_grace_period_policy_enable` tinyint(1) NOT NULL DEFAULT '0',
  `is_late_checkin_early_checkout_policy_enable` tinyint(1) NOT NULL DEFAULT '0',
  `is_for_late_checkin` tinyint(1) NOT NULL DEFAULT '0',
  `is_for_early_checkout` tinyint(1) NOT NULL DEFAULT '0',
  `is_unpaid_leave_policy_enable` tinyint(1) NOT NULL DEFAULT '0',
  `unauthorised_leave_penalty_component` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'gross',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `business_office_hours_business_id_unique` (`business_id`),
  CONSTRAINT `business_office_hours_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2967 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_offices
-- ----------------------------
DROP TABLE IF EXISTS `business_offices`;
CREATE TABLE `business_offices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `location` json NOT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `business_offices_business_id_ip_unique` (`business_id`,`ip`),
  CONSTRAINT `business_offices_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=237 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_partners
-- ----------------------------
DROP TABLE IF EXISTS `business_partners`;
CREATE TABLE `business_partners` (
  `business_id` int(10) unsigned NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  KEY `business_partners_business_id_foreign` (`business_id`),
  KEY `business_partners_partner_id_foreign` (`partner_id`),
  CONSTRAINT `business_partners_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `business_partners_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_pre_tags
-- ----------------------------
DROP TABLE IF EXISTS `business_pre_tags`;
CREATE TABLE `business_pre_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_push_notification_logs
-- ----------------------------
DROP TABLE IF EXISTS `business_push_notification_logs`;
CREATE TABLE `business_push_notification_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `notification_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `notification_body` text COLLATE utf8_unicode_ci NOT NULL,
  `notification_target` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `client_info_type` enum('all','specific_business','specific_coworkers','file') COLLATE utf8_unicode_ci NOT NULL,
  `image_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `device` enum('mobile','web') COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_roles
-- ----------------------------
DROP TABLE IF EXISTS `business_roles`;
CREATE TABLE `business_roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_department_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_published` int(11) NOT NULL DEFAULT '1',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_roles_business_department_id_foreign` (`business_department_id`),
  CONSTRAINT `business_roles_business_department_id_foreign` FOREIGN KEY (`business_department_id`) REFERENCES `business_departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=131817 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_sms_templates
-- ----------------------------
DROP TABLE IF EXISTS `business_sms_templates`;
CREATE TABLE `business_sms_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `event_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `event_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `template` text COLLATE utf8_unicode_ci NOT NULL,
  `variables` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_sms_templates_business_id_foreign` (`business_id`),
  CONSTRAINT `business_sms_templates_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2987 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_tags
-- ----------------------------
DROP TABLE IF EXISTS `business_tags`;
CREATE TABLE `business_tags` (
  `business_id` int(10) unsigned NOT NULL,
  `business_pre_tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`business_id`,`business_pre_tag_id`),
  KEY `business_tags_business_pre_tag_id_foreign` (`business_pre_tag_id`),
  CONSTRAINT `business_tags_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `business_tags_business_pre_tag_id_foreign` FOREIGN KEY (`business_pre_tag_id`) REFERENCES `business_pre_tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_transactions
-- ----------------------------
DROP TABLE IF EXISTS `business_transactions`;
CREATE TABLE `business_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `balance` decimal(11,2) NOT NULL DEFAULT '0.00',
  `event_type` text COLLATE utf8_unicode_ci,
  `event_id` int(11) DEFAULT NULL,
  `tag` enum('sms','subscription','service_purchase') COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` text COLLATE utf8_unicode_ci NOT NULL,
  `log` text COLLATE utf8_unicode_ci NOT NULL,
  `transaction_details` text COLLATE utf8_unicode_ci,
  `third_party_transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_transactions_business_id_foreign` (`business_id`),
  KEY `business_transactions_third_party_transaction_id_index` (`third_party_transaction_id`),
  CONSTRAINT `business_transactions_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=222281 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_trip_requests
-- ----------------------------
DROP TABLE IF EXISTS `business_trip_requests`;
CREATE TABLE `business_trip_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned DEFAULT NULL,
  `member_id` int(10) unsigned NOT NULL,
  `vehicle_id` int(10) unsigned DEFAULT NULL,
  `driver_id` int(10) unsigned DEFAULT NULL,
  `trip_type` enum('one_way','round_trip') COLLATE utf8_unicode_ci NOT NULL,
  `vehicle_type` enum('sedan','suv','passenger_van','hatchback','others') COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('pending','accepted','rejected') CHARACTER SET utf8 NOT NULL DEFAULT 'pending',
  `pickup_geo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pickup_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dropoff_geo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dropoff_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `no_of_seats` int(11) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `details` longtext COLLATE utf8_unicode_ci,
  `reason` longtext COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_trip_requests_member_id_foreign` (`member_id`),
  KEY `business_trip_requests_vehicle_id_foreign` (`vehicle_id`),
  KEY `business_trip_requests_driver_id_foreign` (`driver_id`),
  KEY `business_trip_requests_business_id_foreign` (`business_id`),
  CONSTRAINT `business_trip_requests_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `business_trip_requests_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `business_trip_requests_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `business_trip_requests_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=140 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_trips
-- ----------------------------
DROP TABLE IF EXISTS `business_trips`;
CREATE TABLE `business_trips` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned DEFAULT NULL,
  `business_trip_request_id` int(10) unsigned NOT NULL,
  `member_id` int(10) unsigned NOT NULL,
  `vehicle_id` int(10) unsigned NOT NULL,
  `driver_id` int(10) unsigned NOT NULL,
  `trip_type` enum('one_way','round_trip') COLLATE utf8_unicode_ci NOT NULL,
  `vehicle_type` enum('sedan','suv','passenger_van','hatchback','others') COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('open','process','cancelled','closed') CHARACTER SET utf8 NOT NULL DEFAULT 'open',
  `pickup_geo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pickup_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dropoff_geo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dropoff_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `details` longtext COLLATE utf8_unicode_ci,
  `reason` longtext COLLATE utf8_unicode_ci,
  `no_of_seats` int(11) DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_trips_business_trip_request_id_foreign` (`business_trip_request_id`),
  KEY `business_trips_member_id_foreign` (`member_id`),
  KEY `business_trips_vehicle_id_foreign` (`vehicle_id`),
  KEY `business_trips_driver_id_foreign` (`driver_id`),
  KEY `business_trips_business_id_foreign` (`business_id`),
  CONSTRAINT `business_trips_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `business_trips_business_trip_request_id_foreign` FOREIGN KEY (`business_trip_request_id`) REFERENCES `business_trip_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `business_trips_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `business_trips_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `business_trips_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_weekend_settings
-- ----------------------------
DROP TABLE IF EXISTS `business_weekend_settings`;
CREATE TABLE `business_weekend_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `weekday_name` json NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_weekend_settings_business_id_foreign` (`business_id`),
  CONSTRAINT `business_weekend_settings_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3137 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_weekends
-- ----------------------------
DROP TABLE IF EXISTS `business_weekends`;
CREATE TABLE `business_weekends` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `weekday_name` enum('saturday','sunday','monday','tuesday','wednesday','thursday','friday') COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `business_weekends_business_id_weekday_name_unique` (`business_id`,`weekday_name`),
  CONSTRAINT `business_weekends_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7422 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for business_working_days
-- ----------------------------
DROP TABLE IF EXISTS `business_working_days`;
CREATE TABLE `business_working_days` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `total_working_days` int(11) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `logs` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_working_days_business_id_foreign` (`business_id`),
  CONSTRAINT `business_working_days_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for businesses
-- ----------------------------
DROP TABLE IF EXISTS `businesses`;
CREATE TABLE `businesses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sub_domain` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tagline` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` enum('Company','Organization','Institution') COLLATE utf8_unicode_ci NOT NULL,
  `is_half_day_enable` tinyint(4) NOT NULL DEFAULT '0',
  `half_day_configuration` json DEFAULT NULL,
  `business_category_id` int(10) unsigned DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_enable_employee_visit` tinyint(4) NOT NULL DEFAULT '0',
  `is_payroll_enable` int(11) NOT NULL DEFAULT '0',
  `website` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `about` longtext COLLATE utf8_unicode_ci,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `geo_informations` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/partners/logos/default.png',
  `logo_original` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/partners/logos/default.png',
  `logo_coordinates` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `employee_size` mediumint(8) unsigned DEFAULT '0',
  `fiscal_year` enum('1','2','3','4','5','6','7','8','9','10','11','12') COLLATE utf8_unicode_ci NOT NULL DEFAULT '7',
  `is_leave_prorate_enable` tinyint(4) NOT NULL DEFAULT '1',
  `registration_year` smallint(5) unsigned DEFAULT NULL,
  `registration_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `establishment_year` smallint(5) unsigned DEFAULT NULL,
  `trade_license` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tin_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `map_location` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `working_days` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `working_hours` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_sandwich_leave_enable` tinyint(4) NOT NULL DEFAULT '0',
  `xp` decimal(8,2) NOT NULL DEFAULT '0.00',
  `rating` int(11) NOT NULL DEFAULT '0',
  `wallet` decimal(11,2) NOT NULL DEFAULT '0.00',
  `topup_prepaid_max_limit` double(8,2) NOT NULL DEFAULT '1000.00',
  `account_completion` smallint(5) unsigned NOT NULL DEFAULT '0',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `businesses_business_category_id_foreign` (`business_category_id`),
  KEY `businesses_user_id_foreign` (`user_id`),
  CONSTRAINT `businesses_business_category_id_foreign` FOREIGN KEY (`business_category_id`) REFERENCES `business_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `businesses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3062 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for can_top_up_update_logs
-- ----------------------------
DROP TABLE IF EXISTS `can_top_up_update_logs`;
CREATE TABLE `can_top_up_update_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `from` enum('0','1') COLLATE utf8_unicode_ci NOT NULL,
  `to` enum('0','1') COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `can_top_up_update_logs_partner_id_foreign` (`partner_id`),
  KEY `can_top_up_update_logs_from_index` (`from`),
  KEY `can_top_up_update_logs_to_index` (`to`),
  CONSTRAINT `can_top_up_update_logs_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=454 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for car_rental_job_details
-- ----------------------------
DROP TABLE IF EXISTS `car_rental_job_details`;
CREATE TABLE `car_rental_job_details` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `pick_up_location_id` int(10) unsigned NOT NULL,
  `pick_up_location_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pick_up_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pick_up_address_geo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `destination_location_id` int(10) unsigned DEFAULT NULL,
  `destination_location_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `destination_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `destination_address_geo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `drop_off_date` date DEFAULT NULL,
  `drop_off_time` time DEFAULT NULL,
  `estimated_distance` decimal(8,2) DEFAULT '0.00',
  `estimated_time` mediumint(9) DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `car_rental_job_details_job_id_foreign` (`job_id`),
  CONSTRAINT `car_rental_job_details_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18532 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for car_rental_prices
-- ----------------------------
DROP TABLE IF EXISTS `car_rental_prices`;
CREATE TABLE `car_rental_prices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int(10) unsigned NOT NULL,
  `pickup_thana_id` int(10) unsigned NOT NULL,
  `destination_thana_id` int(10) unsigned DEFAULT NULL,
  `prices` json DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `car_rental_prices_service_id_foreign` (`service_id`),
  KEY `car_rental_prices_pickup_thana_id_foreign` (`pickup_thana_id`),
  KEY `car_rental_prices_destination_thana_id_foreign` (`destination_thana_id`),
  CONSTRAINT `car_rental_prices_destination_thana_id_foreign` FOREIGN KEY (`destination_thana_id`) REFERENCES `thanas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `car_rental_prices_pickup_thana_id_foreign` FOREIGN KEY (`pickup_thana_id`) REFERENCES `thanas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `car_rental_prices_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=98515 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for card_types
-- ----------------------------
DROP TABLE IF EXISTS `card_types`;
CREATE TABLE `card_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payment_gateway_id` int(10) unsigned DEFAULT NULL,
  `fallback_gateway_id` int(10) unsigned DEFAULT NULL,
  `regular_expression` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('Published','Unpublished') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Unpublished',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for careers
-- ----------------------------
DROP TABLE IF EXISTS `careers`;
CREATE TABLE `careers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `vacancy` smallint(5) unsigned NOT NULL,
  `requirements` text COLLATE utf8_unicode_ci NOT NULL,
  `educational_requirements` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `additional_requirements` text COLLATE utf8_unicode_ci NOT NULL,
  `job_nature` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `salary` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `experience` text COLLATE utf8_unicode_ci NOT NULL,
  `benefits` text COLLATE utf8_unicode_ci NOT NULL,
  `note` text COLLATE utf8_unicode_ci,
  `additional_info` text COLLATE utf8_unicode_ci,
  `deadline` date NOT NULL,
  `publication_status` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=136 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for categories
-- ----------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `bn_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `short_description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `service_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `meta_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `long_description` text COLLATE utf8_unicode_ci NOT NULL,
  `popular_service_description` longtext COLLATE utf8_unicode_ci,
  `other_service_description` longtext COLLATE utf8_unicode_ci,
  `structured_contents` json DEFAULT NULL,
  `terms_and_conditions` json DEFAULT NULL,
  `faqs` longtext COLLATE utf8_unicode_ci,
  `bn_faqs` longtext COLLATE utf8_unicode_ci,
  `google_product_category` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `facebook_product_category` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `catalog_price` double DEFAULT NULL,
  `thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/thumbs/default.jpg',
  `app_thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `catalog_thumb` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `banner` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/categories_images/banners/default.jpg',
  `app_banner` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `home_banner` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `video_link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `publication_status` tinyint(1) NOT NULL DEFAULT '0',
  `is_published_for_partner` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `is_published_for_partner_onboarding` tinyint(4) NOT NULL DEFAULT '0',
  `is_published_for_business` tinyint(1) NOT NULL DEFAULT '0',
  `is_published_for_b2b` tinyint(4) NOT NULL DEFAULT '0',
  `is_published_for_ddn` tinyint(4) NOT NULL DEFAULT '0',
  `is_trending` tinyint(4) NOT NULL DEFAULT '0',
  `disclaimer` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_partial_payment_enabled` tinyint(4) NOT NULL DEFAULT '0',
  `is_auto_sp_enabled` tinyint(4) NOT NULL DEFAULT '1',
  `is_vat_applicable` tinyint(4) NOT NULL DEFAULT '0',
  `is_home_delivery_applied` tinyint(1) NOT NULL DEFAULT '1',
  `is_partner_premise_applied` tinyint(1) NOT NULL DEFAULT '0',
  `frequency_in_days` int(11) DEFAULT NULL,
  `is_logistic_available` tinyint(1) NOT NULL DEFAULT '0',
  `logistic_parcel_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `logistic_nature` enum('one_way','two_way') COLLATE utf8_unicode_ci DEFAULT NULL,
  `one_way_logistic_init_event` enum('order_accept','ready_to_pick') COLLATE utf8_unicode_ci DEFAULT NULL,
  `order` tinyint(3) unsigned DEFAULT NULL,
  `order_for_b2b` tinyint(3) unsigned DEFAULT NULL,
  `min_commission` decimal(5,2) DEFAULT NULL,
  `use_partner_commission_for_material` tinyint(1) NOT NULL DEFAULT '1',
  `material_commission_rate` decimal(5,2) DEFAULT NULL,
  `book_resource_minutes` mediumint(8) unsigned NOT NULL DEFAULT '120',
  `preparation_time_minutes` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `notification_before_min` smallint(5) unsigned NOT NULL DEFAULT '30',
  `min_response_time` smallint(5) unsigned NOT NULL DEFAULT '15',
  `questions` longtext COLLATE utf8_unicode_ci,
  `structured_description` longtext COLLATE utf8_unicode_ci,
  `icon` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `icon_hover` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/images/category_images/default_icons/hover_v3.svg',
  `icon_png` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `icon_png_hover` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/images/category_images/default_icons/hover_v3.png',
  `icon_png_active` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/category_images/icons_active/active_v3.png',
  `icon_svg` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebadev/marketplace/default_images/svg/default_icon.svg',
  `icon_color` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `delivery_charge` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `min_order_amount` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `max_order_amount` decimal(8,2) DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categories_parent_id_foreign` (`parent_id`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=868 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for category_complain_preset
-- ----------------------------
DROP TABLE IF EXISTS `category_complain_preset`;
CREATE TABLE `category_complain_preset` (
  `category_id` int(10) unsigned NOT NULL,
  `complain_preset_id` int(10) unsigned NOT NULL,
  KEY `category_complain_preset_category_id_foreign` (`category_id`),
  KEY `category_complain_preset_complain_preset_id_foreign` (`complain_preset_id`),
  CONSTRAINT `category_complain_preset_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `category_complain_preset_complain_preset_id_foreign` FOREIGN KEY (`complain_preset_id`) REFERENCES `complain_presets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for category_group_category
-- ----------------------------
DROP TABLE IF EXISTS `category_group_category`;
CREATE TABLE `category_group_category` (
  `category_group_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `order` smallint(6) DEFAULT NULL,
  UNIQUE KEY `category_group_category_category_group_id_category_id_unique` (`category_group_id`,`category_id`),
  KEY `category_group_category_category_id_foreign` (`category_id`),
  KEY `category_group_category_category_group_id_index` (`category_group_id`),
  CONSTRAINT `category_group_category_category_group_id_foreign` FOREIGN KEY (`category_group_id`) REFERENCES `category_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `category_group_category_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for category_group_location
-- ----------------------------
DROP TABLE IF EXISTS `category_group_location`;
CREATE TABLE `category_group_location` (
  `category_group_id` int(10) unsigned NOT NULL,
  `location_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`category_group_id`,`location_id`),
  KEY `category_group_location_location_id_foreign` (`location_id`),
  KEY `category_group_location_category_group_id_index` (`category_group_id`),
  CONSTRAINT `category_group_location_category_group_id_foreign` FOREIGN KEY (`category_group_id`) REFERENCES `category_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `category_group_location_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for category_groups
-- ----------------------------
DROP TABLE IF EXISTS `category_groups`;
CREATE TABLE `category_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `short_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `long_description` text COLLATE utf8_unicode_ci,
  `meta_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `thumb` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `banner` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `app_thumb` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `app_banner` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `icon_png` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_published_for_app` tinyint(1) NOT NULL DEFAULT '0',
  `is_published_for_web` tinyint(1) NOT NULL DEFAULT '0',
  `order` smallint(5) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_groups_order_unique` (`order`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for category_location
-- ----------------------------
DROP TABLE IF EXISTS `category_location`;
CREATE TABLE `category_location` (
  `category_id` int(10) unsigned NOT NULL,
  `location_id` int(10) unsigned NOT NULL,
  `is_logistic_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`category_id`,`location_id`),
  KEY `category_location_location_id_foreign` (`location_id`),
  KEY `category_location_category_id_index` (`category_id`),
  CONSTRAINT `category_location_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `category_location_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for category_logs
-- ----------------------------
DROP TABLE IF EXISTS `category_logs`;
CREATE TABLE `category_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL,
  `fields` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_logs_category_id_foreign` (`category_id`),
  CONSTRAINT `category_logs_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for category_partner
-- ----------------------------
DROP TABLE IF EXISTS `category_partner`;
CREATE TABLE `category_partner` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  `experience` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `response_time_min` smallint(5) unsigned DEFAULT NULL,
  `response_time_max` smallint(5) unsigned DEFAULT NULL,
  `commission` decimal(5,2) DEFAULT NULL,
  `min_order_amount` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `is_home_delivery_applied` tinyint(1) NOT NULL DEFAULT '1',
  `is_partner_premise_applied` tinyint(1) NOT NULL DEFAULT '0',
  `uses_sheba_logistic` tinyint(1) NOT NULL DEFAULT '0',
  `delivery_charge` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `preparation_time_minutes` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verification_note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_partner_category_id_foreign` (`category_id`),
  KEY `category_partner_partner_id_foreign` (`partner_id`),
  CONSTRAINT `category_partner_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `category_partner_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=256624 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for category_partner_resource
-- ----------------------------
DROP TABLE IF EXISTS `category_partner_resource`;
CREATE TABLE `category_partner_resource` (
  `partner_resource_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`partner_resource_id`,`category_id`),
  KEY `cat_id_fk` (`category_id`),
  CONSTRAINT `cat_id_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pr_id_fk` FOREIGN KEY (`partner_resource_id`) REFERENCES `partner_resource` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for category_question_answers
-- ----------------------------
DROP TABLE IF EXISTS `category_question_answers`;
CREATE TABLE `category_question_answers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_question_id` int(10) unsigned NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_question_answers_category_question_id_foreign` (`category_question_id`),
  CONSTRAINT `category_question_answers_category_question_id_foreign` FOREIGN KEY (`category_question_id`) REFERENCES `category_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for category_question_attributes
-- ----------------------------
DROP TABLE IF EXISTS `category_question_attributes`;
CREATE TABLE `category_question_attributes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_question_id` int(10) unsigned NOT NULL,
  `name` enum('min','max','multiple','placeholder','required','step') COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_question_attributes_category_question_id_foreign` (`category_question_id`),
  CONSTRAINT `category_question_attributes_category_question_id_foreign` FOREIGN KEY (`category_question_id`) REFERENCES `category_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for category_questions
-- ----------------------------
DROP TABLE IF EXISTS `category_questions`;
CREATE TABLE `category_questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL,
  `question` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` enum('checkbox','date','datetime','email','number','radio','range','text','textarea') COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_questions_category_id_foreign` (`category_id`),
  CONSTRAINT `category_questions_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for category_requests
-- ----------------------------
DROP TABLE IF EXISTS `category_requests`;
CREATE TABLE `category_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `partner_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_requests_partner_id_foreign` (`partner_id`),
  CONSTRAINT `category_requests_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13997 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for category_resource
-- ----------------------------
DROP TABLE IF EXISTS `category_resource`;
CREATE TABLE `category_resource` (
  `category_id` int(10) unsigned NOT NULL,
  `resource_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`category_id`,`resource_id`),
  KEY `category_resource_resource_id_foreign` (`resource_id`),
  CONSTRAINT `category_resource_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `category_resource_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for category_schedule_slot
-- ----------------------------
DROP TABLE IF EXISTS `category_schedule_slot`;
CREATE TABLE `category_schedule_slot` (
  `category_id` int(10) unsigned NOT NULL,
  `schedule_slot_id` int(10) unsigned NOT NULL,
  `day` smallint(6) NOT NULL,
  UNIQUE KEY `category_schedule_slot_day_unique` (`category_id`,`schedule_slot_id`,`day`),
  KEY `category_schedule_slot_schedule_slot_id_foreign` (`schedule_slot_id`),
  CONSTRAINT `category_schedule_slot_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `category_schedule_slot_schedule_slot_id_foreign` FOREIGN KEY (`schedule_slot_id`) REFERENCES `schedule_slots` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for category_usp
-- ----------------------------
DROP TABLE IF EXISTS `category_usp`;
CREATE TABLE `category_usp` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL,
  `usp_id` int(10) unsigned NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_usp_category_id_usp_id_unique` (`category_id`,`usp_id`),
  KEY `category_usp_usp_id_foreign` (`usp_id`),
  CONSTRAINT `category_usp_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `category_usp_usp_id_foreign` FOREIGN KEY (`usp_id`) REFERENCES `usps` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=933 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for cities
-- ----------------------------
DROP TABLE IF EXISTS `cities`;
CREATE TABLE `cities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cities_country_id_foreign` (`country_id`),
  CONSTRAINT `cities_country_id_foreign` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for clo_user_leaves
-- ----------------------------
DROP TABLE IF EXISTS `clo_user_leaves`;
CREATE TABLE `clo_user_leaves` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clo_user_id` int(10) unsigned NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clo_user_leaves_clo_user_id_foreign` (`clo_user_id`),
  CONSTRAINT `clo_user_leaves_clo_user_id_foreign` FOREIGN KEY (`clo_user_id`) REFERENCES `clo_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for clo_users
-- ----------------------------
DROP TABLE IF EXISTS `clo_users`;
CREATE TABLE `clo_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `dialer_id` char(15) COLLATE utf8_unicode_ci NOT NULL,
  `date_of_join` datetime NOT NULL,
  `status` enum('active','inactive') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active',
  `team` enum('topup_kam','topup_sales','b_and_m_kam','b_and_m_sales','os_kam','os_sales','general_sales','sorting','ojt') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'general_sales',
  `work_type` enum('part_time','full_time') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'part_time',
  `gender` enum('male','female') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'female',
  `bkash_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `region` enum('dhaka','bogra') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'dhaka',
  `team_lead_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clo_users_user_id_unique` (`user_id`),
  UNIQUE KEY `clo_users_dialer_id_unique` (`dialer_id`),
  KEY `clo_users_team_lead_id_foreign` (`team_lead_id`),
  CONSTRAINT `clo_users_team_lead_id_foreign` FOREIGN KEY (`team_lead_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `clo_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for combo_services
-- ----------------------------
DROP TABLE IF EXISTS `combo_services`;
CREATE TABLE `combo_services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int(10) unsigned NOT NULL,
  `option` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '[]',
  `quantity` decimal(8,2) NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `combo_services_service_id_foreign` (`service_id`),
  CONSTRAINT `combo_services_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for comments
-- ----------------------------
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `commentable_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `commentable_id` int(10) unsigned NOT NULL,
  `commentator_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `commentator_id` int(10) unsigned NOT NULL,
  `is_visible` tinyint(1) DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comments_commentable_index` (`commentable_type`,`commentable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1511280 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for complain_categories
-- ----------------------------
DROP TABLE IF EXISTS `complain_categories`;
CREATE TABLE `complain_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL,
  `publication_status` tinyint(1) NOT NULL DEFAULT '1',
  `order_status` enum('open','closed','pending') COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for complain_logs
-- ----------------------------
DROP TABLE IF EXISTS `complain_logs`;
CREATE TABLE `complain_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `complain_id` int(10) unsigned NOT NULL,
  `field` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `from` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `to` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `complain_logs_complain_id_foreign` (`complain_id`),
  CONSTRAINT `complain_logs_complain_id_foreign` FOREIGN KEY (`complain_id`) REFERENCES `complains` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=78076 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for complain_presets
-- ----------------------------
DROP TABLE IF EXISTS `complain_presets`;
CREATE TABLE `complain_presets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `response` text COLLATE utf8_unicode_ci,
  `order` int(11) NOT NULL,
  `publication_status` tinyint(1) NOT NULL DEFAULT '1',
  `is_callable` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `complain_presets_type_id_foreign` (`type_id`),
  KEY `complain_presets_category_id_foreign` (`category_id`),
  CONSTRAINT `complain_presets_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `complain_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `complain_presets_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `complain_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for complain_types
-- ----------------------------
DROP TABLE IF EXISTS `complain_types`;
CREATE TABLE `complain_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sla` int(10) unsigned NOT NULL,
  `lifetime_sla` int(10) unsigned NOT NULL,
  `publication_status` tinyint(1) NOT NULL DEFAULT '1',
  `label_color` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'info',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for complains
-- ----------------------------
DROP TABLE IF EXISTS `complains`;
CREATE TABLE `complains` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` enum('Open','Observation','Resolved','Rejected') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Open',
  `complain` text COLLATE utf8_unicode_ci,
  `complain_preset_id` int(10) unsigned DEFAULT NULL,
  `source` enum('Direct','QA','FB') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Direct',
  `accessor_id` int(10) unsigned DEFAULT NULL,
  `job_id` int(10) unsigned DEFAULT NULL,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `partner_id` int(10) unsigned DEFAULT NULL,
  `assigned_to_id` int(10) unsigned DEFAULT NULL,
  `unreachable_sms_sent_to_customer` tinyint(4) NOT NULL DEFAULT '0',
  `unreachable_sms_sent_to_sp` tinyint(4) NOT NULL DEFAULT '0',
  `unreached_resolve_sms_sent` tinyint(4) NOT NULL DEFAULT '0',
  `follow_up_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved_time` timestamp NULL DEFAULT NULL,
  `resolved_category` enum('service_provided','service_category_line_item_Unpublished','spro_sp_unverified_pause','financial_penalty','spro_sp_warning','preparation_time_order_limit_radius_spro_info_fixation','skill_test_training_arranged_for_spro_sp','tech_issue_solved','customer_tag_changed_blocked','customer_refunded','partner_payment_settled','uniform_purchase_hygiene_issue_ensured','price_updated_verified_revised','sp_compensated','sheba_compensated','action_taken_against_sheba_agent','promo_provided','development') COLLATE utf8_unicode_ci NOT NULL,
  `is_satisfied` tinyint(4) DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `complains_complain_preset_id_foreign` (`complain_preset_id`),
  KEY `complains_accessor_id_foreign` (`accessor_id`),
  KEY `complains_job_id_foreign` (`job_id`),
  KEY `complains_customer_id_foreign` (`customer_id`),
  KEY `complains_partner_id_foreign` (`partner_id`),
  KEY `complains_assigned_to_id_foreign` (`assigned_to_id`),
  CONSTRAINT `complains_accessor_id_foreign` FOREIGN KEY (`accessor_id`) REFERENCES `accessors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `complains_assigned_to_id_foreign` FOREIGN KEY (`assigned_to_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `complains_complain_preset_id_foreign` FOREIGN KEY (`complain_preset_id`) REFERENCES `complain_presets` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `complains_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `complains_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `complains_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29291 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for complains_report
-- ----------------------------
DROP TABLE IF EXISTS `complains_report`;
CREATE TABLE `complains_report` (
  `id` int(10) unsigned NOT NULL,
  `partner_id` int(10) unsigned DEFAULT NULL,
  `complain_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `complain_status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `complain` text COLLATE utf8_unicode_ci,
  `complain_category` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `complain_preset` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `complain _source` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `job_code` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `job_status` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `order_code` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `complain_applicable_for` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resource` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `om_name` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `service_group` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `service_category` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `service_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lifetime_sla` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_mobile` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `new_returning_customer` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `partner_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `complain_assignee` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `unreachable_sms_sent_to_sp` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `unreachable_sms_sent_to_customer` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `unreached_resolve_sms_sent` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `follow_up_time` datetime DEFAULT NULL,
  `is_satisfied` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `severity` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `closed_by_assinge_completed` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resolved_date` date DEFAULT NULL,
  `resolved_time` time DEFAULT NULL,
  `hours_taken_to_resolve` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resolved_by_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resolved_category` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `report_updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `complains_report_partner_id_foreign` (`partner_id`),
  CONSTRAINT `complains_report_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for component_package_target
-- ----------------------------
DROP TABLE IF EXISTS `component_package_target`;
CREATE TABLE `component_package_target` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `package_id` int(10) unsigned NOT NULL,
  `effective_for` enum('global','department','employee') COLLATE utf8_unicode_ci DEFAULT NULL,
  `target_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `component_package_target_package_id_foreign` (`package_id`),
  CONSTRAINT `component_package_target_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `component_packages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for component_packages
-- ----------------------------
DROP TABLE IF EXISTS `component_packages`;
CREATE TABLE `component_packages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `payroll_component_id` int(10) unsigned NOT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `is_taxable` tinyint(1) NOT NULL DEFAULT '0',
  `calculation_type` enum('fix_pay_amount','variable_amount') COLLATE utf8_unicode_ci NOT NULL,
  `is_percentage` tinyint(1) NOT NULL DEFAULT '0',
  `on_what` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `amount` decimal(8,2) NOT NULL,
  `schedule_type` enum('periodically','fixed_date') COLLATE utf8_unicode_ci NOT NULL,
  `periodic_schedule` smallint(5) unsigned DEFAULT NULL,
  `schedule_date` smallint(6) DEFAULT NULL,
  `periodic_schedule_created_at` timestamp NULL DEFAULT NULL,
  `generated_at` date DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `component_packages_payroll_component_id_foreign` (`payroll_component_id`),
  CONSTRAINT `component_packages_payroll_component_id_foreign` FOREIGN KEY (`payroll_component_id`) REFERENCES `payroll_components` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for countries
-- ----------------------------
DROP TABLE IF EXISTS `countries`;
CREATE TABLE `countries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for crosssale_services
-- ----------------------------
DROP TABLE IF EXISTS `crosssale_services`;
CREATE TABLE `crosssale_services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int(10) unsigned NOT NULL,
  `add_on_service_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/marketplace/default_images/png/cross_sell.png',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `crosssale_services_add_on_service_id_service_id_unique` (`add_on_service_id`,`service_id`),
  KEY `crosssale_services_service_id_foreign` (`service_id`),
  KEY `crosssale_services_add_on_service_id_index` (`add_on_service_id`),
  CONSTRAINT `crosssale_services_add_on_service_id_foreign` FOREIGN KEY (`add_on_service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `crosssale_services_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for custom_order_cancel_logs
-- ----------------------------
DROP TABLE IF EXISTS `custom_order_cancel_logs`;
CREATE TABLE `custom_order_cancel_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `custom_order_id` int(10) unsigned NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cancel_reason` enum('Customer Dependency','Customer Management','Push Sales Attempt','Insufficient Partner','Price Shock','Service Limitation','Wrongly Create Order/ Test Order','Service Change') COLLATE utf8_unicode_ci NOT NULL,
  `cancel_reason_details` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `custom_order_cancel_logs_custom_order_id_foreign` (`custom_order_id`),
  CONSTRAINT `custom_order_cancel_logs_custom_order_id_foreign` FOREIGN KEY (`custom_order_id`) REFERENCES `custom_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for custom_order_discussions
-- ----------------------------
DROP TABLE IF EXISTS `custom_order_discussions`;
CREATE TABLE `custom_order_discussions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `custom_order_id` int(10) unsigned NOT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `custom_order_discussions_custom_order_id_foreign` (`custom_order_id`),
  CONSTRAINT `custom_order_discussions_custom_order_id_foreign` FOREIGN KEY (`custom_order_id`) REFERENCES `custom_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for custom_order_status_logs
-- ----------------------------
DROP TABLE IF EXISTS `custom_order_status_logs`;
CREATE TABLE `custom_order_status_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `custom_order_id` int(10) unsigned NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `from_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `to_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `custom_order_status_logs_custom_order_id_foreign` (`custom_order_id`),
  CONSTRAINT `custom_order_status_logs_custom_order_id_foreign` FOREIGN KEY (`custom_order_id`) REFERENCES `custom_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for custom_order_update_logs
-- ----------------------------
DROP TABLE IF EXISTS `custom_order_update_logs`;
CREATE TABLE `custom_order_update_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `custom_order_id` int(10) unsigned NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `custom_order_update_logs_custom_order_id_foreign` (`custom_order_id`),
  CONSTRAINT `custom_order_update_logs_custom_order_id_foreign` FOREIGN KEY (`custom_order_id`) REFERENCES `custom_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for custom_orders
-- ----------------------------
DROP TABLE IF EXISTS `custom_orders`;
CREATE TABLE `custom_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `voucher_id` int(10) unsigned DEFAULT NULL,
  `affiliation_id` int(10) unsigned DEFAULT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `delivery_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `job_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `service_id` int(10) unsigned DEFAULT NULL,
  `service_variables` text COLLATE utf8_unicode_ci,
  `additional_info` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `schedule_date` date DEFAULT NULL,
  `preferred_time` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `crm_id` int(10) unsigned DEFAULT NULL,
  `sales_channel` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('Open','Process','On Inspection','Quotation Sent','Converted To Order','Cancelled') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Open',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `custom_orders_voucher_id_unique` (`voucher_id`),
  UNIQUE KEY `custom_orders_affiliation_id_unique` (`affiliation_id`),
  KEY `custom_orders_customer_id_foreign` (`customer_id`),
  KEY `custom_orders_service_id_foreign` (`service_id`),
  KEY `custom_orders_location_id_foreign` (`location_id`),
  KEY `custom_orders_crm_id_foreign` (`crm_id`),
  CONSTRAINT `custom_orders_affiliation_id_foreign` FOREIGN KEY (`affiliation_id`) REFERENCES `affiliations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `custom_orders_crm_id_foreign` FOREIGN KEY (`crm_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `custom_orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `custom_orders_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `custom_orders_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `custom_orders_voucher_id_foreign` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for customer_delivery_addresses
-- ----------------------------
DROP TABLE IF EXISTS `customer_delivery_addresses`;
CREATE TABLE `customer_delivery_addresses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `flat_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `road_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `house_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `block_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sector_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `area` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `landmark` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `geo_informations` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `latitude` double(8,2) DEFAULT NULL,
  `longitude` double(8,2) DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_delivery_addresses_customer_id_foreign` (`customer_id`),
  KEY `customer_delivery_addresses_location_id_foreign` (`location_id`),
  CONSTRAINT `customer_delivery_addresses_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `customer_delivery_addresses_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=344575 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for customer_favourite_service
-- ----------------------------
DROP TABLE IF EXISTS `customer_favourite_service`;
CREATE TABLE `customer_favourite_service` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_favourite_id` int(10) unsigned NOT NULL,
  `service_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `additional_info` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `variable_type` enum('Fixed','Options','Custom') COLLATE utf8_unicode_ci NOT NULL,
  `variables` text COLLATE utf8_unicode_ci NOT NULL,
  `option` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_favourite_service_customer_favourite_id_foreign` (`customer_favourite_id`),
  KEY `customer_favourite_service_service_id_foreign` (`service_id`),
  CONSTRAINT `customer_favourite_service_customer_favourite_id_foreign` FOREIGN KEY (`customer_favourite_id`) REFERENCES `customer_favourites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `customer_favourite_service_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11350 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for customer_favourites
-- ----------------------------
DROP TABLE IF EXISTS `customer_favourites`;
CREATE TABLE `customer_favourites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_info` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `job_id` int(10) unsigned DEFAULT NULL,
  `partner_id` int(10) unsigned DEFAULT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `delivery_address_id` int(10) unsigned DEFAULT NULL,
  `schedule_date` date DEFAULT NULL,
  `preferred_time` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `total_price` decimal(11,2) NOT NULL DEFAULT '0.00',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_favourites_customer_id_foreign` (`customer_id`),
  KEY `customer_favourites_category_id_foreign` (`category_id`),
  KEY `customer_favourites_job_id_foreign` (`job_id`),
  KEY `customer_favourites_partner_id_foreign` (`partner_id`),
  KEY `customer_favourites_location_id_foreign` (`location_id`),
  KEY `customer_favourites_delivery_address_id_foreign` (`delivery_address_id`),
  CONSTRAINT `customer_favourites_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `customer_favourites_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `customer_favourites_delivery_address_id_foreign` FOREIGN KEY (`delivery_address_id`) REFERENCES `customer_delivery_addresses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `customer_favourites_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `customer_favourites_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `customer_favourites_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9504 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for customer_mobiles
-- ----------------------------
DROP TABLE IF EXISTS `customer_mobiles`;
CREATE TABLE `customer_mobiles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_mobiles_mobile_unique` (`mobile`),
  KEY `customer_mobiles_customer_id_foreign` (`customer_id`),
  CONSTRAINT `customer_mobiles_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6432 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for customer_report
-- ----------------------------
DROP TABLE IF EXISTS `customer_report`;
CREATE TABLE `customer_report` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile_verified` tinyint(1) NOT NULL DEFAULT '0',
  `email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gender` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `no_of_orders` smallint(5) unsigned NOT NULL DEFAULT '0',
  `closed_orders` smallint(5) unsigned NOT NULL DEFAULT '0',
  `cancelled_orders` smallint(5) unsigned NOT NULL DEFAULT '0',
  `last_order_date` date DEFAULT NULL,
  `primary_location` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `all_locations` text COLLATE utf8_unicode_ci,
  `primary_channel` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `all_channels` text COLLATE utf8_unicode_ci,
  `purchase_amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `most_purchased_service` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `service_tried` smallint(5) unsigned NOT NULL DEFAULT '0',
  `referral_code` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `all_purchased_service` text COLLATE utf8_unicode_ci,
  `created_by` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `report_updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for customer_reviews
-- ----------------------------
DROP TABLE IF EXISTS `customer_reviews`;
CREATE TABLE `customer_reviews` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `review` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `rating` int(11) NOT NULL,
  `reviewable_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `reviewable_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `job_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_reviews_customer_id_foreign` (`customer_id`),
  KEY `customer_reviews_job_id_foreign` (`job_id`),
  CONSTRAINT `customer_reviews_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `customer_reviews_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=91440 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for customer_transactions
-- ----------------------------
DROP TABLE IF EXISTS `customer_transactions`;
CREATE TABLE `customer_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_order_id` int(11) unsigned DEFAULT NULL,
  `event_type` text COLLATE utf8_unicode_ci,
  `event_id` int(11) DEFAULT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `type` text COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `balance` decimal(11,2) NOT NULL DEFAULT '0.00',
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `transaction_details` text COLLATE utf8_unicode_ci,
  `third_party_transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_transactions_customer_id_foreign` (`customer_id`),
  KEY `customer_transactions_partner_order_id_foreign` (`partner_order_id`),
  KEY `customer_transactions_third_party_transaction_id_index` (`third_party_transaction_id`),
  CONSTRAINT `customer_transactions_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `customer_transactions_partner_order_id_foreign` FOREIGN KEY (`partner_order_id`) REFERENCES `partner_orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5504 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for customers
-- ----------------------------
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` int(10) unsigned NOT NULL,
  `remember_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_vip` tinyint(1) NOT NULL DEFAULT '0',
  `office_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `xp` decimal(8,2) NOT NULL DEFAULT '0.00',
  `rating` int(11) NOT NULL DEFAULT '0',
  `has_rated_customer_app` tinyint(4) NOT NULL DEFAULT '0',
  `reference_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `wallet` decimal(11,2) NOT NULL DEFAULT '0.00',
  `reward_point` decimal(11,2) NOT NULL DEFAULT '0.00',
  `order_count` int(11) NOT NULL DEFAULT '0',
  `served_order_count` int(11) NOT NULL DEFAULT '0',
  `voucher_order_count` int(11) NOT NULL DEFAULT '0',
  `primary_location` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `primary_channel` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `referrer_id` int(10) unsigned DEFAULT NULL,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic','business-portal') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_completed` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_profile_id_unique` (`profile_id`),
  UNIQUE KEY `customers_remember_token_unique` (`remember_token`),
  KEY `customers_portal_name_index` (`portal_name`),
  CONSTRAINT `customers_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=737973 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for daily_stats
-- ----------------------------
DROP TABLE IF EXISTS `daily_stats`;
CREATE TABLE `daily_stats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  `distribution_data` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `daily_stats_date_unique` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=1865 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for dashboard_settings
-- ----------------------------
DROP TABLE IF EXISTS `dashboard_settings`;
CREATE TABLE `dashboard_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `rules` text COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dashboard_settings_user_id_unique` (`user_id`),
  CONSTRAINT `dashboard_settings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1317 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for data_migrations
-- ----------------------------
DROP TABLE IF EXISTS `data_migrations`;
CREATE TABLE `data_migrations` (
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for delivery_charge_update_requests
-- ----------------------------
DROP TABLE IF EXISTS `delivery_charge_update_requests`;
CREATE TABLE `delivery_charge_update_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_partner_id` int(10) unsigned NOT NULL,
  `old_category_partner_info` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `new_category_partner_info` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `log` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `delivery_charge_update_requests_category_partner_id_foreign` (`category_partner_id`),
  CONSTRAINT `delivery_charge_update_requests_category_partner_id_foreign` FOREIGN KEY (`category_partner_id`) REFERENCES `category_partner` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1105 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for department_feature
-- ----------------------------
DROP TABLE IF EXISTS `department_feature`;
CREATE TABLE `department_feature` (
  `department_id` int(10) unsigned NOT NULL,
  `feature_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`department_id`,`feature_id`),
  KEY `department_feature_feature_id_foreign` (`feature_id`),
  CONSTRAINT `department_feature_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `department_feature_feature_id_foreign` FOREIGN KEY (`feature_id`) REFERENCES `features` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for departments
-- ----------------------------
DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for digital_collection_settings
-- ----------------------------
DROP TABLE IF EXISTS `digital_collection_settings`;
CREATE TABLE `digital_collection_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `service_charge` double(8,2) NOT NULL DEFAULT '0.00',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `digital_collection_settings_partner_id_foreign` (`partner_id`),
  CONSTRAINT `digital_collection_settings_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for discount_applicables
-- ----------------------------
DROP TABLE IF EXISTS `discount_applicables`;
CREATE TABLE `discount_applicables` (
  `discount_id` int(10) unsigned NOT NULL,
  `applicable_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `applicable_id` int(11) DEFAULT NULL,
  UNIQUE KEY `discount_applicable_unique` (`discount_id`,`applicable_type`,`applicable_id`),
  CONSTRAINT `discount_applicables_discount_id_foreign` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for discounts
-- ----------------------------
DROP TABLE IF EXISTS `discounts`;
CREATE TABLE `discounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` enum('general','online_payment','delivery') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'general',
  `rules` longtext COLLATE utf8_unicode_ci,
  `amount` decimal(11,2) NOT NULL,
  `is_percentage` tinyint(1) NOT NULL DEFAULT '0',
  `cap` decimal(11,2) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `max_user` smallint(5) unsigned DEFAULT NULL,
  `max_usage_per_user` smallint(5) unsigned DEFAULT NULL,
  `sheba_contribution` decimal(5,2) NOT NULL DEFAULT '0.00',
  `partner_contribution` decimal(5,2) NOT NULL DEFAULT '0.00',
  `is_created_by_sheba` tinyint(1) NOT NULL DEFAULT '0',
  `partner_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `discounts_partner_id_foreign` (`partner_id`),
  CONSTRAINT `discounts_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for districts
-- ----------------------------
DROP TABLE IF EXISTS `districts`;
CREATE TABLE `districts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `division_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `bn_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lat` decimal(9,7) NOT NULL DEFAULT '0.0000000',
  `lng` decimal(9,7) NOT NULL DEFAULT '0.0000000',
  PRIMARY KEY (`id`),
  KEY `districts_division_id_foreign` (`division_id`),
  CONSTRAINT `districts_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for divisions
-- ----------------------------
DROP TABLE IF EXISTS `divisions`;
CREATE TABLE `divisions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `bn_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for documents
-- ----------------------------
DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for driver_vehicles
-- ----------------------------
DROP TABLE IF EXISTS `driver_vehicles`;
CREATE TABLE `driver_vehicles` (
  `driver_id` int(10) unsigned NOT NULL,
  `vehicle_id` int(10) unsigned NOT NULL,
  KEY `driver_vehicles_driver_id_foreign` (`driver_id`),
  KEY `driver_vehicles_vehicle_id_foreign` (`vehicle_id`),
  CONSTRAINT `driver_vehicles_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `driver_vehicles_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for drivers
-- ----------------------------
DROP TABLE IF EXISTS `drivers`;
CREATE TABLE `drivers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `license_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `license_number_end_date` datetime NOT NULL,
  `license_number_image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `license_class` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `years_of_experience` int(11) DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'unverified',
  `traffic_awareness` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `accident_history` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `basic_knowledge` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `license_age_in_years` decimal(11,2) DEFAULT NULL,
  `contract_type` enum('permanent','temporary') COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_info` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for emi_banks
-- ----------------------------
DROP TABLE IF EXISTS `emi_banks`;
CREATE TABLE `emi_banks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `payment_gateway_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name_bn` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `emi_banks_payment_gateway_id_foreign` (`payment_gateway_id`),
  CONSTRAINT `emi_banks_payment_gateway_id_foreign` FOREIGN KEY (`payment_gateway_id`) REFERENCES `payment_gateways` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for events
-- ----------------------------
DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8_unicode_ci NOT NULL,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1323804 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for expenses
-- ----------------------------
DROP TABLE IF EXISTS `expenses`;
CREATE TABLE `expenses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `business_member_id` int(10) unsigned DEFAULT NULL,
  `amount` decimal(8,2) NOT NULL,
  `remarks` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `comment` longtext COLLATE utf8_unicode_ci,
  `type` enum('transport','food','other') COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('pending','accepted','rejected') COLLATE utf8_unicode_ci NOT NULL,
  `is_updated_by_super_admin` tinyint(4) NOT NULL DEFAULT '0',
  `closed_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_member_id_foreign` (`member_id`),
  KEY `expenses_business_member_id_foreign` (`business_member_id`),
  CONSTRAINT `expenses_business_member_id_foreign` FOREIGN KEY (`business_member_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `expenses_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20331 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for external_payments
-- ----------------------------
DROP TABLE IF EXISTS `external_payments`;
CREATE TABLE `external_payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `amount` double(8,2) NOT NULL,
  `success_url` text COLLATE utf8_unicode_ci NOT NULL,
  `fail_url` text COLLATE utf8_unicode_ci NOT NULL,
  `customer_mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `customer_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `emi_month` int(11) NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  `payment_id` int(10) unsigned DEFAULT NULL,
  `client_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `transaction_id` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `purpose` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payment_details` text COLLATE utf8_unicode_ci,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic','business-portal') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `external_payments_partner_id_foreign` (`partner_id`),
  KEY `external_payments_client_id_foreign` (`client_id`),
  KEY `external_payments_payment_id_foreign` (`payment_id`),
  CONSTRAINT `external_payments_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `payment_client_authentications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `external_payments_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `external_payments_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=48624 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for external_projects
-- ----------------------------
DROP TABLE IF EXISTS `external_projects`;
CREATE TABLE `external_projects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `icon_png` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `banner` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `web_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `app_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for features
-- ----------------------------
DROP TABLE IF EXISTS `features`;
CREATE TABLE `features` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `system_update_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `module_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `send_mail_notification` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `features_system_update_id_foreign` (`system_update_id`),
  CONSTRAINT `features_system_update_id_foreign` FOREIGN KEY (`system_update_id`) REFERENCES `system_updates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for feedbacks
-- ----------------------------
DROP TABLE IF EXISTS `feedbacks`;
CREATE TABLE `feedbacks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `type` enum('Issue','Idea','Improvement') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Issue',
  `project` enum('Admin','Partner','Customer','Business','Account') COLLATE utf8_unicode_ci NOT NULL,
  `page` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('Open','Acknowledged','In Process','Closed','Declined','Halt') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Open',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23817 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for flag_attachments
-- ----------------------------
DROP TABLE IF EXISTS `flag_attachments`;
CREATE TABLE `flag_attachments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `flag_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `flag_attachments_flag_id_foreign` (`flag_id`),
  CONSTRAINT `flag_attachments_flag_id_foreign` FOREIGN KEY (`flag_id`) REFERENCES `flags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1039 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for flag_status_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `flag_status_change_logs`;
CREATE TABLE `flag_status_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `flag_id` int(10) unsigned NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `from` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `to` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `flag_status_change_logs_flag_id_foreign` (`flag_id`),
  CONSTRAINT `flag_status_change_logs_flag_id_foreign` FOREIGN KEY (`flag_id`) REFERENCES `flags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=84213 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for flag_time_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `flag_time_change_logs`;
CREATE TABLE `flag_time_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `flag_id` int(10) unsigned NOT NULL,
  `status` enum('Open','Acknowledged','In Process','Completed','Closed','Declined','Halt') COLLATE utf8_unicode_ci NOT NULL,
  `old_time` datetime NOT NULL,
  `new_time` datetime NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `flag_time_change_logs_flag_id_foreign` (`flag_id`),
  CONSTRAINT `flag_time_change_logs_flag_id_foreign` FOREIGN KEY (`flag_id`) REFERENCES `flags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32783 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for flag_user_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `flag_user_change_logs`;
CREATE TABLE `flag_user_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `flag_id` int(10) unsigned NOT NULL,
  `status` enum('Open','Acknowledged','In Process','Completed','Closed','Declined','Halt') COLLATE utf8_unicode_ci NOT NULL,
  `type` enum('assignee','reporter') COLLATE utf8_unicode_ci NOT NULL,
  `old_id` int(10) unsigned DEFAULT NULL,
  `new_id` int(10) unsigned DEFAULT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `flag_user_change_logs_flag_id_foreign` (`flag_id`),
  KEY `flag_user_change_logs_old_id_foreign` (`old_id`),
  KEY `flag_user_change_logs_new_id_foreign` (`new_id`),
  CONSTRAINT `flag_user_change_logs_flag_id_foreign` FOREIGN KEY (`flag_id`) REFERENCES `flags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `flag_user_change_logs_new_id_foreign` FOREIGN KEY (`new_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `flag_user_change_logs_old_id_foreign` FOREIGN KEY (`old_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for flags
-- ----------------------------
DROP TABLE IF EXISTS `flags`;
CREATE TABLE `flags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `department_id` int(10) unsigned NOT NULL,
  `by_department_id` int(10) unsigned NOT NULL,
  `assigned_to_id` int(10) unsigned DEFAULT NULL,
  `assigned_by_id` int(10) unsigned DEFAULT NULL,
  `is_sensitive` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `flag_type` enum('Idea','Assignment','Improvement','Risk','Issue') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Issue',
  `severity` enum('Critical','Major','Minor','Moderate') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Moderate',
  `preferred_solving_date` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `status` enum('Open','Acknowledged','In Process','Completed','Closed','Declined','Halt') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Open',
  `is_liked_by_reporter` tinyint(1) DEFAULT NULL,
  `is_liked_by_assignee` tinyint(1) DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `flags_department_id_foreign` (`department_id`),
  KEY `flags_by_department_id_foreign` (`by_department_id`),
  KEY `flags_assigned_to_id_foreign` (`assigned_to_id`),
  KEY `flags_assigned_by_id_foreign` (`assigned_by_id`),
  CONSTRAINT `flags_assigned_by_id_foreign` FOREIGN KEY (`assigned_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `flags_assigned_to_id_foreign` FOREIGN KEY (`assigned_to_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `flags_by_department_id_foreign` FOREIGN KEY (`by_department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `flags_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37192 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for form_template_items
-- ----------------------------
DROP TABLE IF EXISTS `form_template_items`;
CREATE TABLE `form_template_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `form_template_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `short_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `long_description` longtext COLLATE utf8_unicode_ci,
  `input_type` enum('text','radio','number','select') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'text',
  `variables` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `form_template_items_form_template_id_foreign` (`form_template_id`),
  CONSTRAINT `form_template_items_form_template_id_foreign` FOREIGN KEY (`form_template_id`) REFERENCES `form_templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for form_template_questions
-- ----------------------------
DROP TABLE IF EXISTS `form_template_questions`;
CREATE TABLE `form_template_questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `form_template_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `short_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `long_description` longtext COLLATE utf8_unicode_ci,
  `input_type` enum('text','radio','number','select') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'text',
  `variables` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `form_template_questions_form_template_id_foreign` (`form_template_id`),
  CONSTRAINT `form_template_questions_form_template_id_foreign` FOREIGN KEY (`form_template_id`) REFERENCES `form_templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for form_templates
-- ----------------------------
DROP TABLE IF EXISTS `form_templates`;
CREATE TABLE `form_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `short_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `long_description` longtext COLLATE utf8_unicode_ci,
  `owner_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `owner_id` int(11) NOT NULL,
  `type` enum('inspection','purchase_request','procurement') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'inspection',
  `is_published` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for fuel_logs
-- ----------------------------
DROP TABLE IF EXISTS `fuel_logs`;
CREATE TABLE `fuel_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` int(10) unsigned NOT NULL,
  `type` enum('petrol','diesel','octane','cng') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'petrol',
  `unit` enum('ltr','cubic_feet') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ltr',
  `volume` decimal(11,2) NOT NULL,
  `price` decimal(11,2) NOT NULL,
  `refilled_date` datetime NOT NULL,
  `station_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `station_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fuel_logs_vehicle_id_foreign` (`vehicle_id`),
  CONSTRAINT `fuel_logs_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for galleries
-- ----------------------------
DROP TABLE IF EXISTS `galleries`;
CREATE TABLE `galleries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `short_description` longtext COLLATE utf8_unicode_ci,
  `long_description` longtext COLLATE utf8_unicode_ci,
  `thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_published` tinyint(4) NOT NULL DEFAULT '1',
  `owner_type` enum('App\\Models\\Service','App\\Models\\Category','App\\Models\\CategoryGroup') COLLATE utf8_unicode_ci NOT NULL,
  `owner_id` int(11) NOT NULL,
  `target_link` longtext COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `galleries_owner_type_index` (`owner_type`),
  KEY `galleries_owner_id_index` (`owner_id`)
) ENGINE=InnoDB AUTO_INCREMENT=235 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for gift_card_purchases
-- ----------------------------
DROP TABLE IF EXISTS `gift_card_purchases`;
CREATE TABLE `gift_card_purchases` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `gift_card_id` int(10) unsigned DEFAULT NULL,
  `amount` decimal(11,2) unsigned NOT NULL,
  `credits_purchased` decimal(11,2) unsigned NOT NULL,
  `status` enum('initialized','successful','failed') COLLATE utf8_unicode_ci NOT NULL,
  `valid_till` timestamp NULL DEFAULT NULL,
  `transaction_details` json DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gift_card_purchases_customer_id_foreign` (`customer_id`),
  KEY `gift_card_purchases_gift_card_id_foreign` (`gift_card_id`),
  CONSTRAINT `gift_card_purchases_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `gift_card_purchases_gift_card_id_foreign` FOREIGN KEY (`gift_card_id`) REFERENCES `gift_cards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39602 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for gift_cards
-- ----------------------------
DROP TABLE IF EXISTS `gift_cards`;
CREATE TABLE `gift_cards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'sheba_credit',
  `credit` decimal(11,2) unsigned NOT NULL,
  `price` decimal(11,2) NOT NULL,
  `start_date` timestamp NOT NULL,
  `end_date` timestamp NOT NULL,
  `validity_in_months` int(11) NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for government_holidays
-- ----------------------------
DROP TABLE IF EXISTS `government_holidays`;
CREATE TABLE `government_holidays` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for grace_period_policy_history
-- ----------------------------
DROP TABLE IF EXISTS `grace_period_policy_history`;
CREATE TABLE `grace_period_policy_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `is_enable` tinyint(4) NOT NULL DEFAULT '0',
  `settings` json NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `logs` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `grace_period_policy_history_business_id_foreign` (`business_id`),
  CONSTRAINT `grace_period_policy_history_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for grid_portal
-- ----------------------------
DROP TABLE IF EXISTS `grid_portal`;
CREATE TABLE `grid_portal` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `grid_id` int(10) unsigned NOT NULL,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic') COLLATE utf8_unicode_ci NOT NULL,
  `screen` enum('home','eshop') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `grid_portal_grid_id_foreign` (`grid_id`),
  CONSTRAINT `grid_portal_grid_id_foreign` FOREIGN KEY (`grid_id`) REFERENCES `grids` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for grids
-- ----------------------------
DROP TABLE IF EXISTS `grids`;
CREATE TABLE `grids` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `attributes` longtext COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for gross_salary_breakdown_history
-- ----------------------------
DROP TABLE IF EXISTS `gross_salary_breakdown_history`;
CREATE TABLE `gross_salary_breakdown_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_member_id` int(10) unsigned NOT NULL,
  `setting_form_where` enum('breakdown_gross_salary','individual_salary') COLLATE utf8_unicode_ci NOT NULL,
  `settings` json NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `logs` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gross_salary_breakdown_history_business_member_id_foreign` (`business_member_id`),
  CONSTRAINT `gross_salary_breakdown_history_business_member_id_foreign` FOREIGN KEY (`business_member_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for hired_drivers
-- ----------------------------
DROP TABLE IF EXISTS `hired_drivers`;
CREATE TABLE `hired_drivers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hired_by_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hired_by_id` int(11) NOT NULL,
  `owner_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `owner_id` int(11) NOT NULL,
  `driver_id` int(10) unsigned DEFAULT NULL,
  `start` timestamp NULL DEFAULT NULL,
  `end` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hired_drivers_driver_id_foreign` (`driver_id`),
  CONSTRAINT `hired_drivers_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for hired_vehicles
-- ----------------------------
DROP TABLE IF EXISTS `hired_vehicles`;
CREATE TABLE `hired_vehicles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hired_by_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hired_by_id` int(11) NOT NULL,
  `owner_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `owner_id` int(11) NOT NULL,
  `vehicle_id` int(10) unsigned DEFAULT NULL,
  `start` timestamp NULL DEFAULT NULL,
  `end` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hired_vehicles_vehicle_id_foreign` (`vehicle_id`),
  CONSTRAINT `hired_vehicles_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for home_menu_elements
-- ----------------------------
DROP TABLE IF EXISTS `home_menu_elements`;
CREATE TABLE `home_menu_elements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `home_menu_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `item_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `item_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `home_menu_elements_home_menu_id_foreign` (`home_menu_id`),
  CONSTRAINT `home_menu_elements_home_menu_id_foreign` FOREIGN KEY (`home_menu_id`) REFERENCES `home_menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=560 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for home_menu_location
-- ----------------------------
DROP TABLE IF EXISTS `home_menu_location`;
CREATE TABLE `home_menu_location` (
  `home_menu_id` int(10) unsigned NOT NULL,
  `location_id` int(10) unsigned NOT NULL,
  KEY `home_menu_location_home_menu_id_foreign` (`home_menu_id`),
  KEY `home_menu_location_location_id_foreign` (`location_id`),
  CONSTRAINT `home_menu_location_home_menu_id_foreign` FOREIGN KEY (`home_menu_id`) REFERENCES `home_menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `home_menu_location_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for home_menus
-- ----------------------------
DROP TABLE IF EXISTS `home_menus`;
CREATE TABLE `home_menus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `portal_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for impression_deduction_partner
-- ----------------------------
DROP TABLE IF EXISTS `impression_deduction_partner`;
CREATE TABLE `impression_deduction_partner` (
  `impression_deduction_id` int(10) unsigned NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`impression_deduction_id`,`partner_id`),
  KEY `impression_deduction_partner_partner_id_foreign` (`partner_id`),
  CONSTRAINT `impression_deduction_partner_impression_deduction_id_foreign` FOREIGN KEY (`impression_deduction_id`) REFERENCES `impression_deductions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `impression_deduction_partner_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for impression_deductions
-- ----------------------------
DROP TABLE IF EXISTS `impression_deductions`;
CREATE TABLE `impression_deductions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `category_id` int(10) unsigned DEFAULT NULL,
  `order_details` longtext COLLATE utf8_unicode_ci,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `impression_deductions_customer_id_foreign` (`customer_id`),
  KEY `impression_deductions_location_id_foreign` (`location_id`),
  KEY `impression_deductions_category_id_foreign` (`category_id`),
  CONSTRAINT `impression_deductions_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `impression_deductions_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `impression_deductions_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4731527 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for incomplete_orders
-- ----------------------------
DROP TABLE IF EXISTS `incomplete_orders`;
CREATE TABLE `incomplete_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `category_id` int(10) unsigned DEFAULT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `partner_id` int(10) unsigned DEFAULT NULL,
  `voucher_id` int(10) unsigned DEFAULT NULL,
  `delivery_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `delivery_mobile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sales_channel` enum('Call-Center','Web','App','Facebook','B2B','Store','Alternative','Affiliation') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'App',
  `schedule_date` date DEFAULT NULL,
  `preferred_time` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `services` text COLLATE utf8_unicode_ci,
  `reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reason_detail` text COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('pending','rejected','processing','notified','cancelled','lost','converted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `order_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `incomplete_orders_order_id_unique` (`order_id`),
  KEY `incomplete_orders_customer_id_foreign` (`customer_id`),
  KEY `incomplete_orders_category_id_foreign` (`category_id`),
  KEY `incomplete_orders_location_id_foreign` (`location_id`),
  KEY `incomplete_orders_partner_id_foreign` (`partner_id`),
  KEY `incomplete_orders_voucher_id_foreign` (`voucher_id`),
  CONSTRAINT `incomplete_orders_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `incomplete_orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `incomplete_orders_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `incomplete_orders_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `incomplete_orders_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `incomplete_orders_voucher_id_foreign` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for info_call_reject_reasons
-- ----------------------------
DROP TABLE IF EXISTS `info_call_reject_reasons`;
CREATE TABLE `info_call_reject_reasons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for info_call_status_logs
-- ----------------------------
DROP TABLE IF EXISTS `info_call_status_logs`;
CREATE TABLE `info_call_status_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `info_call_id` int(10) unsigned NOT NULL,
  `from` enum('Open','Rejected','Converted') COLLATE utf8_unicode_ci NOT NULL,
  `to` enum('Open','Rejected','Converted') COLLATE utf8_unicode_ci NOT NULL,
  `reject_reason_id` int(10) unsigned DEFAULT NULL,
  `reject_reason_details` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `info_call_status_logs_info_call_id_foreign` (`info_call_id`),
  KEY `info_call_status_logs_reject_reason_id_foreign` (`reject_reason_id`),
  CONSTRAINT `info_call_status_logs_info_call_id_foreign` FOREIGN KEY (`info_call_id`) REFERENCES `info_calls` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `info_call_status_logs_reject_reason_id_foreign` FOREIGN KEY (`reject_reason_id`) REFERENCES `info_call_reject_reasons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2904 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for info_calls
-- ----------------------------
DROP TABLE IF EXISTS `info_calls`;
CREATE TABLE `info_calls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `affiliation_id` int(10) unsigned DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `customer_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_office_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_designation` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_customer_vip` tinyint(1) NOT NULL DEFAULT '0',
  `location_id` int(10) unsigned DEFAULT NULL,
  `sales_channel` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `priority` enum('Low','Medium','High') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Low',
  `flag` enum('Green','Amber','Red') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Green',
  `service_id` int(10) unsigned DEFAULT NULL,
  `service_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `category_id` int(10) unsigned DEFAULT NULL,
  `service_type` enum('Recurring','Regular') COLLATE utf8_unicode_ci DEFAULT NULL,
  `info_category` enum('not_available','price','service','sheba') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'not_available',
  `estimated_budget` int(10) unsigned DEFAULT NULL,
  `budget_type` enum('One Time','Monthly','Annual') COLLATE utf8_unicode_ci DEFAULT NULL,
  `department_id` int(10) unsigned DEFAULT NULL,
  `crm_id` int(10) unsigned DEFAULT NULL,
  `follow_up_date` timestamp NULL DEFAULT NULL,
  `intended_closing_date` timestamp NULL DEFAULT NULL,
  `status` enum('Open','Rejected','Converted') COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_informations` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic','business-portal') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `info_calls_affiliation_id_unique` (`affiliation_id`),
  KEY `info_calls_customer_id_foreign` (`customer_id`),
  KEY `info_calls_service_id_foreign` (`service_id`),
  KEY `info_calls_location_id_foreign` (`location_id`),
  KEY `info_calls_category_id_foreign` (`category_id`),
  KEY `info_calls_crm_id_foreign` (`crm_id`),
  CONSTRAINT `info_calls_affiliation_id_foreign` FOREIGN KEY (`affiliation_id`) REFERENCES `affiliations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `info_calls_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `info_calls_crm_id_foreign` FOREIGN KEY (`crm_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `info_calls_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `info_calls_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `info_calls_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49396 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for inspection_item_issues
-- ----------------------------
DROP TABLE IF EXISTS `inspection_item_issues`;
CREATE TABLE `inspection_item_issues` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `inspection_item_id` int(10) unsigned NOT NULL,
  `order_id` int(10) unsigned DEFAULT NULL,
  `status` enum('open','process','closed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'open',
  `comment` longtext COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inspection_item_issues_inspection_item_id_foreign` (`inspection_item_id`),
  KEY `inspection_item_issues_order_id_foreign` (`order_id`),
  CONSTRAINT `inspection_item_issues_inspection_item_id_foreign` FOREIGN KEY (`inspection_item_id`) REFERENCES `inspection_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inspection_item_issues_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for inspection_item_status_logs
-- ----------------------------
DROP TABLE IF EXISTS `inspection_item_status_logs`;
CREATE TABLE `inspection_item_status_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `inspection_item_id` int(10) unsigned NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `from_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `to_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inspection_item_status_logs_inspection_item_id_foreign` (`inspection_item_id`),
  CONSTRAINT `inspection_item_status_logs_inspection_item_id_foreign` FOREIGN KEY (`inspection_item_id`) REFERENCES `inspection_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for inspection_items
-- ----------------------------
DROP TABLE IF EXISTS `inspection_items`;
CREATE TABLE `inspection_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `inspection_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `short_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `long_description` longtext COLLATE utf8_unicode_ci,
  `input_type` enum('text','radio','number','select') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'text',
  `variables` longtext COLLATE utf8_unicode_ci NOT NULL,
  `result` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` longtext COLLATE utf8_unicode_ci,
  `status` enum('open','issue_created','acknowledged') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'open',
  `acknowledgment_note` longtext COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inspection_items_inspection_id_foreign` (`inspection_id`),
  CONSTRAINT `inspection_items_inspection_id_foreign` FOREIGN KEY (`inspection_id`) REFERENCES `inspections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=598 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for inspection_schedules
-- ----------------------------
DROP TABLE IF EXISTS `inspection_schedules`;
CREATE TABLE `inspection_schedules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('weekly','monthly') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'weekly',
  `date_values` longtext COLLATE utf8_unicode_ci,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_published` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for inspections
-- ----------------------------
DROP TABLE IF EXISTS `inspections`;
CREATE TABLE `inspections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `inspection_schedule_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `short_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `long_description` longtext COLLATE utf8_unicode_ci,
  `type` enum('daily','weekly','monthly','one_time') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'one_time',
  `status` enum('open','process','closed','cancelled') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'open',
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `next_start_date` datetime DEFAULT NULL,
  `date_values` longtext COLLATE utf8_unicode_ci,
  `is_published` tinyint(4) NOT NULL DEFAULT '0',
  `vehicle_id` int(10) unsigned NOT NULL,
  `business_id` int(10) unsigned NOT NULL,
  `member_id` int(10) unsigned NOT NULL,
  `form_template_id` int(10) unsigned NOT NULL,
  `submitted_date` datetime DEFAULT NULL,
  `submission_note` longtext COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inspections_vehicle_id_foreign` (`vehicle_id`),
  KEY `inspections_business_id_foreign` (`business_id`),
  KEY `inspections_member_id_foreign` (`member_id`),
  KEY `inspections_form_template_id_foreign` (`form_template_id`),
  KEY `inspections_inspection_schedule_id_foreign` (`inspection_schedule_id`),
  CONSTRAINT `inspections_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inspections_form_template_id_foreign` FOREIGN KEY (`form_template_id`) REFERENCES `form_templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inspections_inspection_schedule_id_foreign` FOREIGN KEY (`inspection_schedule_id`) REFERENCES `inspection_schedules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inspections_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `inspections_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=271 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for ipdc_sms_logs
-- ----------------------------
DROP TABLE IF EXISTS `ipdc_sms_logs`;
CREATE TABLE `ipdc_sms_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cost` decimal(11,2) NOT NULL,
  `content` text COLLATE utf8_unicode_ci,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `used_on_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `used_on_id` int(11) DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13379 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for job_cancel_logs
-- ----------------------------
DROP TABLE IF EXISTS `job_cancel_logs`;
CREATE TABLE `job_cancel_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `from_status` enum('Pending','Accepted','Declined','Not Responded','Schedule Due','Process','Serve Due','Served','Cancelled') COLLATE utf8_unicode_ci DEFAULT 'Pending',
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cancel_reason` enum('Wrongly Create Order/ Test Order','Wrongly Create Order','Test Order','SP Unreachable','Price Shock','Resource Capacity','Service Disputes','Schedule Missed','Automatically Resolved','Customer Unreachable','Future Schedule','Duplicate Order','Service Change','SP Requested to Cancel','Resource Behaviour','Resource Skill','Customer Dependency','Customer Management','Push Sales Attempt','Insufficient Partner','Service Limitation','Urgent Support','Fake','Accidentally Placed Order - Customer','Billing Issue - Customer','Customer Financial Concern','Customer Personal Issues','Customer picked LSP - SP Issue','Customer picked LSP - Unsure','Customer picked LSP - Urgent','Customer Relocated','Customer Rescheduled Order','Customer Unavailable','Customer unreachable','Duplicate Order - App','Health Issue - Customer','Issue Resolved','Mistake in order placement - Customer','Not Repairable','Not Serviceable','Not Willing to pay advance','Price Shock - Additional Charge','Price Shock - Market Price','Price Shock - Others','Problematic Customer','Product has warranty','Test - Customer','Will buy new product','Workshop required','Wrong Location - Customer','Duplicate Order - CC','Duplicate Order - DQM','Duplicate Order - sManager','Duplicate Order - Telesales','HLT order','Mistake in order placement - Agent','Out of service area','Service unavailable','Test','Wrong Location - Agent','Billing Issue - SP','COVID - 19','Duplicate Order - Bondhu','Health Issue - sPro','Price Shock - SP Issue','Quality Concern','Resource Quality Concern','SP Canceled','SP Missed Schedule','SP Not Found','SP Not Responding','SP unreachable','Spare parts unavailable / Supply Issue','Miscommunication','Other','Others','I Selected Wrong Schedule','I Want To Select Other Service','I Want To Select Other Service Provider','I Want Service AT Different Address') COLLATE utf8_unicode_ci DEFAULT NULL,
  `cancel_reason_details` text COLLATE utf8_unicode_ci,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_cancel_logs_job_id_foreign` (`job_id`),
  CONSTRAINT `job_cancel_logs_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=162398 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for job_cancel_reasons
-- ----------------------------
DROP TABLE IF EXISTS `job_cancel_reasons`;
CREATE TABLE `job_cancel_reasons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_published_for_cm` tinyint(1) NOT NULL DEFAULT '0',
  `is_published_for_sp` tinyint(1) NOT NULL DEFAULT '0',
  `is_published_for_customer` tinyint(1) NOT NULL DEFAULT '0',
  `is_fake_for_affiliation` tinyint(1) NOT NULL DEFAULT '0',
  `affects_partner_performance` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for job_cancel_requests
-- ----------------------------
DROP TABLE IF EXISTS `job_cancel_requests`;
CREATE TABLE `job_cancel_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `from_status` enum('Pending','Accepted','Declined','Not Responded','Schedule Due','Process','Serve Due','Served','Cancelled') COLLATE utf8_unicode_ci DEFAULT 'Pending',
  `cancel_reason` enum('Wrongly Create Order/ Test Order','Wrongly Create Order','Test Order','SP Unreachable','Price Shock','Resource Capacity','Service Disputes','Schedule Missed','Automatically Resolved','Customer Unreachable','Future Schedule','Duplicate Order','Service Change','SP Requested to Cancel','Resource Behaviour','Resource Skill','Customer Dependency','Customer Management','Push Sales Attempt','Insufficient Partner','Service Limitation','Urgent Support','Fake','Accidentally Placed Order - Customer','Billing Issue - Customer','Customer Financial Concern','Customer Personal Issues','Customer picked LSP - SP Issue','Customer picked LSP - Unsure','Customer picked LSP - Urgent','Customer Relocated','Customer Rescheduled Order','Customer Unavailable','Customer unreachable','Duplicate Order - App','Health Issue - Customer','Issue Resolved','Mistake in order placement - Customer','Not Repairable','Not Serviceable','Not Willing to pay advance','Price Shock - Additional Charge','Price Shock - Market Price','Price Shock - Others','Problematic Customer','Product has warranty','Test - Customer','Will buy new product','Workshop required','Wrong Location - Customer','Duplicate Order - CC','Duplicate Order - DQM','Duplicate Order - sManager','Duplicate Order - Telesales','HLT order','Mistake in order placement - Agent','Out of service area','Service unavailable','Test','Wrong Location - Agent','Billing Issue - SP','COVID - 19','Duplicate Order - Bondhu','Health Issue - sPro','Price Shock - SP Issue','Quality Concern','Resource Quality Concern','SP Canceled','SP Missed Schedule','SP Not Found','SP Not Responding','SP unreachable','Spare parts unavailable / Supply Issue','Miscommunication','Other') COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('Pending','Approved','Disapproved') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Pending',
  `is_escalated` tinyint(1) NOT NULL DEFAULT '0',
  `approved_at` datetime DEFAULT NULL,
  `approved_by` int(10) unsigned NOT NULL,
  `approved_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_cancel_requests_job_id_foreign` (`job_id`),
  CONSTRAINT `job_cancel_requests_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=163569 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for job_crm_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `job_crm_change_logs`;
CREATE TABLE `job_crm_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `new_crm_id` int(10) unsigned DEFAULT NULL,
  `old_crm_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_crm_change_logs_job_id_foreign` (`job_id`),
  KEY `job_crm_change_logs_new_crm_id_foreign` (`new_crm_id`),
  KEY `job_crm_change_logs_old_crm_id_foreign` (`old_crm_id`),
  CONSTRAINT `job_crm_change_logs_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `job_crm_change_logs_new_crm_id_foreign` FOREIGN KEY (`new_crm_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `job_crm_change_logs_old_crm_id_foreign` FOREIGN KEY (`old_crm_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4479838 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for job_decline_logs
-- ----------------------------
DROP TABLE IF EXISTS `job_decline_logs`;
CREATE TABLE `job_decline_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `partner_id` int(10) unsigned DEFAULT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reason` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_decline_logs_job_id_foreign` (`job_id`),
  KEY `job_decline_logs_partner_id_foreign` (`partner_id`),
  CONSTRAINT `job_decline_logs_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `job_decline_logs_ibfk_2` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11474 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for job_discounts
-- ----------------------------
DROP TABLE IF EXISTS `job_discounts`;
CREATE TABLE `job_discounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `discount_id` int(10) unsigned DEFAULT NULL,
  `type` enum('general','online_payment','delivery') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'general',
  `amount` decimal(11,2) NOT NULL,
  `original_amount` decimal(11,2) NOT NULL,
  `is_percentage` tinyint(1) NOT NULL DEFAULT '0',
  `cap` decimal(11,2) DEFAULT NULL,
  `sheba_contribution` decimal(5,2) NOT NULL DEFAULT '0.00',
  `partner_contribution` decimal(5,2) NOT NULL DEFAULT '0.00',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_discounts_discount_id_foreign` (`discount_id`),
  KEY `job_discounts_job_id_foreign` (`job_id`),
  CONSTRAINT `job_discounts_discount_id_foreign` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `job_discounts_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5133 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for job_material
-- ----------------------------
DROP TABLE IF EXISTS `job_material`;
CREATE TABLE `job_material` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `material_id` int(10) unsigned DEFAULT NULL,
  `material_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `material_price` decimal(11,2) NOT NULL,
  `is_verified` tinyint(1) NOT NULL,
  `verification_note` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_material_job_id_foreign` (`job_id`),
  CONSTRAINT `job_material_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=85850 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for job_material_logs
-- ----------------------------
DROP TABLE IF EXISTS `job_material_logs`;
CREATE TABLE `job_material_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `job_material_id` int(10) unsigned DEFAULT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `old_data` text COLLATE utf8_unicode_ci,
  `new_data` text COLLATE utf8_unicode_ci,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_material_logs_job_id_foreign` (`job_id`),
  CONSTRAINT `job_material_logs_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=54101 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for job_no_response_logs
-- ----------------------------
DROP TABLE IF EXISTS `job_no_response_logs`;
CREATE TABLE `job_no_response_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `partner_id` int(10) unsigned DEFAULT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_no_response_logs_job_id_foreign` (`job_id`),
  KEY `job_no_response_logs_partner_id_foreign` (`partner_id`),
  CONSTRAINT `job_no_response_logs_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `job_no_response_logs_ibfk_2` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=114549 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for job_partner_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `job_partner_change_logs`;
CREATE TABLE `job_partner_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `from_status` enum('Pending','Accepted','Declined','Not Responded','Schedule Due','Process','Serve Due','Served','Cancelled') COLLATE utf8_unicode_ci DEFAULT 'Pending',
  `cancel_reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `old_partner_id` int(10) unsigned NOT NULL,
  `new_partner_id` int(10) unsigned NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_partner_change_logs_job_id_foreign` (`job_id`),
  CONSTRAINT `job_partner_change_logs_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=58762 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for job_partner_change_reasons
-- ----------------------------
DROP TABLE IF EXISTS `job_partner_change_reasons`;
CREATE TABLE `job_partner_change_reasons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for job_rating_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `job_rating_change_logs`;
CREATE TABLE `job_rating_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `previous_rating` int(11) DEFAULT NULL,
  `review_id` int(11) NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5200 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for job_schedule_due_logs
-- ----------------------------
DROP TABLE IF EXISTS `job_schedule_due_logs`;
CREATE TABLE `job_schedule_due_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_schedule_due_logs_job_id_foreign` (`job_id`),
  CONSTRAINT `job_schedule_due_logs_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=338966 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for job_service
-- ----------------------------
DROP TABLE IF EXISTS `job_service`;
CREATE TABLE `job_service` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `service_id` int(10) unsigned DEFAULT NULL,
  `quantity` decimal(11,2) DEFAULT '1.00',
  `unit_price` decimal(11,2) NOT NULL,
  `min_price` double NOT NULL DEFAULT '0',
  `discount` decimal(11,2) NOT NULL,
  `sheba_contribution` decimal(5,2) NOT NULL,
  `partner_contribution` decimal(5,2) NOT NULL,
  `vendor_contribution` decimal(5,2) NOT NULL DEFAULT '0.00',
  `discount_percentage` decimal(5,2) NOT NULL,
  `surcharge_percentage` decimal(5,2) DEFAULT NULL,
  `discount_id` int(10) unsigned DEFAULT NULL,
  `location_service_discount_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `variable_type` enum('Fixed','Options','Custom') COLLATE utf8_unicode_ci NOT NULL,
  `variables` text COLLATE utf8_unicode_ci NOT NULL,
  `option` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_info` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` enum('Recurring','Regular') COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_service_job_id_foreign` (`job_id`),
  KEY `job_service_service_id_foreign` (`service_id`),
  KEY `job_service_discount_id_foreign` (`discount_id`),
  KEY `job_service_location_service_discount_id_foreign` (`location_service_discount_id`),
  FULLTEXT KEY `job_service_name_index` (`name`),
  CONSTRAINT `job_service_discount_id_foreign` FOREIGN KEY (`discount_id`) REFERENCES `partner_service_discounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `job_service_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `job_service_location_service_discount_id_foreign` FOREIGN KEY (`location_service_discount_id`) REFERENCES `service_discounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `job_service_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=722306 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for job_status_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `job_status_change_logs`;
CREATE TABLE `job_status_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `from_status` enum('Pending','Accepted','Declined','Not Responded','Schedule Due','Process','Serve Due','Served','Cancelled') COLLATE utf8_unicode_ci DEFAULT 'Pending',
  `to_status` enum('Pending','Accepted','Declined','Not Responded','Schedule Due','Process','Serve Due','Served','Cancelled') COLLATE utf8_unicode_ci DEFAULT 'Pending',
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_status_change_logs_job_id_foreign` (`job_id`),
  CONSTRAINT `job_status_change_logs_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2642986 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for job_update_logs
-- ----------------------------
DROP TABLE IF EXISTS `job_update_logs`;
CREATE TABLE `job_update_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `log` text COLLATE utf8_unicode_ci,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_update_logs_job_id_foreign` (`job_id`),
  CONSTRAINT `job_update_logs_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2090019 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for jobs
-- ----------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_order_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned DEFAULT NULL,
  `job_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `service_id` int(10) unsigned DEFAULT NULL,
  `service_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `service_variable_type` enum('Fixed','Options','Custom') COLLATE utf8_unicode_ci DEFAULT NULL,
  `service_variables` longtext COLLATE utf8_unicode_ci,
  `service_option` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `job_additional_info` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `category_answers` longtext COLLATE utf8_unicode_ci,
  `service_quantity` int(11) NOT NULL DEFAULT '1',
  `service_type` enum('Recurring','Regular') COLLATE utf8_unicode_ci DEFAULT NULL,
  `resource_id` int(10) unsigned DEFAULT NULL,
  `crm_id` int(10) unsigned DEFAULT NULL,
  `department_id` int(10) unsigned DEFAULT NULL,
  `needs_logistic` tinyint(1) NOT NULL DEFAULT '0',
  `logistic_parcel_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `logistic_nature` enum('one_way','two_way') COLLATE utf8_unicode_ci DEFAULT NULL,
  `one_way_logistic_init_event` enum('order_accept','ready_to_pick') COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_logistic_order_id` int(10) unsigned DEFAULT NULL,
  `last_logistic_order_id` int(10) DEFAULT NULL,
  `logistic_enabled_manually` tinyint(1) NOT NULL DEFAULT '0',
  `schedule_date` date DEFAULT NULL,
  `estimated_visiting_date` timestamp NULL DEFAULT NULL,
  `estimated_delivery_date` timestamp NULL DEFAULT NULL,
  `preferred_time` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `preferred_time_start` time DEFAULT NULL,
  `preferred_time_end` time DEFAULT NULL,
  `service_unit_price` decimal(11,2) NOT NULL,
  `commission_rate` double unsigned DEFAULT NULL,
  `material_commission_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `site` enum('customer','partner') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'customer',
  `delivery_charge` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `logistic_charge` decimal(11,2) NOT NULL DEFAULT '0.00',
  `logistic_discount` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `logistic_paid` decimal(11,2) NOT NULL DEFAULT '0.00',
  `delivery_commission_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `vat` decimal(8,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `online_discount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `sheba_contribution` decimal(5,2) NOT NULL DEFAULT '0.00',
  `partner_contribution` decimal(5,2) NOT NULL DEFAULT '0.00',
  `vendor_contribution` decimal(5,2) NOT NULL DEFAULT '0.00',
  `discount_percentage` decimal(5,2) DEFAULT NULL,
  `original_discount_amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `status` enum('Pending','Accepted','Declined','Not Responded','Schedule Due','Process','Serve Due','Served','Cancelled') COLLATE utf8_unicode_ci DEFAULT 'Pending',
  `delivered_date` timestamp NULL DEFAULT NULL,
  `warranty` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_recurring` tinyint(1) NOT NULL DEFAULT '0',
  `review_request` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `satisfaction_level` enum('High','Medium','Low') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ci_level` enum('High','Medium','Low') COLLATE utf8_unicode_ci DEFAULT NULL,
  `cm_notified` tinyint(1) NOT NULL DEFAULT '0',
  `sp_notified` tinyint(1) NOT NULL DEFAULT '0',
  `customer_notified` tinyint(1) NOT NULL DEFAULT '0',
  `attachment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_partner_order_id_foreign` (`partner_order_id`),
  KEY `jobs_service_id_foreign` (`service_id`),
  KEY `jobs_resource_id_foreign` (`resource_id`),
  KEY `jobs_crm_id_foreign` (`crm_id`),
  KEY `jobs_category_id_foreign` (`category_id`),
  CONSTRAINT `jobs_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `jobs_crm_id_foreign` FOREIGN KEY (`crm_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`partner_order_id`) REFERENCES `partner_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `jobs_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `jobs_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=655250 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for join_requests
-- ----------------------------
DROP TABLE IF EXISTS `join_requests`;
CREATE TABLE `join_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` int(10) unsigned DEFAULT NULL,
  `profile_mobile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `profile_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `organization_id` int(10) unsigned NOT NULL,
  `organization_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci,
  `file` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `requester_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `invitation_sent` tinyint(1) NOT NULL DEFAULT '0',
  `mail_sent` tinyint(1) NOT NULL DEFAULT '0',
  `message_sent` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('Open','Pending','Process','Accepted','Rejected','Cancelled') COLLATE utf8_unicode_ci DEFAULT 'Open',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `join_requests_profile_id_foreign` (`profile_id`),
  CONSTRAINT `join_requests_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=232 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for lafs_order_customer_action_logs
-- ----------------------------
DROP TABLE IF EXISTS `lafs_order_customer_action_logs`;
CREATE TABLE `lafs_order_customer_action_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lafs_order_id` int(10) unsigned NOT NULL,
  `from_status` enum('pending','called','unreachable') COLLATE utf8_unicode_ci NOT NULL,
  `to_status` enum('pending','called','unreachable') COLLATE utf8_unicode_ci NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lafs_order_customer_action_logs_lafs_order_id_foreign` (`lafs_order_id`),
  KEY `lafs_order_customer_action_logs_from_status_index` (`from_status`),
  KEY `lafs_order_customer_action_logs_to_status_index` (`to_status`),
  CONSTRAINT `lafs_order_customer_action_logs_lafs_order_id_foreign` FOREIGN KEY (`lafs_order_id`) REFERENCES `lafs_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for lafs_order_partner_action_logs
-- ----------------------------
DROP TABLE IF EXISTS `lafs_order_partner_action_logs`;
CREATE TABLE `lafs_order_partner_action_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lafs_order_id` int(10) unsigned NOT NULL,
  `from_status` enum('pending','called','unreachable') COLLATE utf8_unicode_ci NOT NULL,
  `to_status` enum('pending','called','unreachable') COLLATE utf8_unicode_ci NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lafs_order_partner_action_logs_lafs_order_id_foreign` (`lafs_order_id`),
  KEY `lafs_order_partner_action_logs_from_status_index` (`from_status`),
  KEY `lafs_order_partner_action_logs_to_status_index` (`to_status`),
  CONSTRAINT `lafs_order_partner_action_logs_lafs_order_id_foreign` FOREIGN KEY (`lafs_order_id`) REFERENCES `lafs_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for lafs_order_status_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `lafs_order_status_change_logs`;
CREATE TABLE `lafs_order_status_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lafs_order_id` int(10) unsigned NOT NULL,
  `from_status` enum('pending','ongoing','done','failed') COLLATE utf8_unicode_ci NOT NULL,
  `to_status` enum('pending','ongoing','done','failed') COLLATE utf8_unicode_ci NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lafs_order_status_change_logs_lafs_order_id_foreign` (`lafs_order_id`),
  KEY `lafs_order_status_change_logs_from_status_index` (`from_status`),
  KEY `lafs_order_status_change_logs_to_status_index` (`to_status`),
  CONSTRAINT `lafs_order_status_change_logs_lafs_order_id_foreign` FOREIGN KEY (`lafs_order_id`) REFERENCES `lafs_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=92887 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for lafs_orders
-- ----------------------------
DROP TABLE IF EXISTS `lafs_orders`;
CREATE TABLE `lafs_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `nth_order` int(11) NOT NULL,
  `status` enum('pending','ongoing','done','failed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `action_taken_for_customer` enum('pending','called','unreachable') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `action_taken_for_partner` enum('pending','called','unreachable') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lafs_orders_order_id_foreign` (`order_id`),
  KEY `lafs_orders_action_taken_for_customer_index` (`action_taken_for_customer`),
  KEY `lafs_orders_action_taken_for_partner_index` (`action_taken_for_partner`),
  KEY `lafs_orders_status_index` (`status`),
  CONSTRAINT `lafs_orders_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=108303 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for late_check_early_out_policy_history
-- ----------------------------
DROP TABLE IF EXISTS `late_check_early_out_policy_history`;
CREATE TABLE `late_check_early_out_policy_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `is_enable` tinyint(4) NOT NULL DEFAULT '0',
  `for_checkin` tinyint(4) NOT NULL DEFAULT '0',
  `for_checkout` tinyint(4) NOT NULL DEFAULT '0',
  `settings` json NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `logs` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `late_check_early_out_policy_history_business_id_foreign` (`business_id`),
  CONSTRAINT `late_check_early_out_policy_history_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for lead_distribution_logs
-- ----------------------------
DROP TABLE IF EXISTS `lead_distribution_logs`;
CREATE TABLE `lead_distribution_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lead_distribution_id` int(10) unsigned NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_distribution_logs_lead_distribution_id_foreign` (`lead_distribution_id`),
  CONSTRAINT `lead_distribution_logs_lead_distribution_id_foreign` FOREIGN KEY (`lead_distribution_id`) REFERENCES `lead_distributions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for lead_distribution_remarks
-- ----------------------------
DROP TABLE IF EXISTS `lead_distribution_remarks`;
CREATE TABLE `lead_distribution_remarks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lead_distribution_id` int(10) unsigned NOT NULL,
  `clo_user_id` int(10) unsigned NOT NULL,
  `remark` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_distribution_remarks_lead_distribution_id_foreign` (`lead_distribution_id`),
  KEY `lead_distribution_remarks_clo_user_id_foreign` (`clo_user_id`),
  CONSTRAINT `lead_distribution_remarks_clo_user_id_foreign` FOREIGN KEY (`clo_user_id`) REFERENCES `clo_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `lead_distribution_remarks_lead_distribution_id_foreign` FOREIGN KEY (`lead_distribution_id`) REFERENCES `lead_distributions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for lead_distributions
-- ----------------------------
DROP TABLE IF EXISTS `lead_distributions`;
CREATE TABLE `lead_distributions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clo_user_id` int(10) unsigned DEFAULT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lead_distributions_clo_user_id_foreign` (`clo_user_id`),
  KEY `lead_distributions_partner_id_foreign` (`partner_id`),
  CONSTRAINT `lead_distributions_clo_user_id_foreign` FOREIGN KEY (`clo_user_id`) REFERENCES `clo_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `lead_distributions_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for leave_logs
-- ----------------------------
DROP TABLE IF EXISTS `leave_logs`;
CREATE TABLE `leave_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `leave_id` int(10) unsigned NOT NULL,
  `type` enum('status','leave_type','leave_date','substitute','leave_adjustment','leave_update') COLLATE utf8_unicode_ci DEFAULT NULL,
  `from` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `to` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log` text COLLATE utf8_unicode_ci,
  `is_changed_by_super` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_logs_leave_id_foreign` (`leave_id`),
  CONSTRAINT `leave_logs_leave_id_foreign` FOREIGN KEY (`leave_id`) REFERENCES `leaves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1392 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for leave_prorate_logs
-- ----------------------------
DROP TABLE IF EXISTS `leave_prorate_logs`;
CREATE TABLE `leave_prorate_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_member_id` int(10) unsigned NOT NULL,
  `prorate_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `leave_type_target` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `leave_type_target_id` int(11) NOT NULL,
  `leave_type_total_days` int(11) NOT NULL,
  `prorated_leave_type_total_days` int(11) NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_prorate_logs_business_member_id_foreign` (`business_member_id`),
  CONSTRAINT `leave_prorate_logs_business_member_id_foreign` FOREIGN KEY (`business_member_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1082 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for leave_rejection_reasons
-- ----------------------------
DROP TABLE IF EXISTS `leave_rejection_reasons`;
CREATE TABLE `leave_rejection_reasons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `leave_rejection_id` int(10) unsigned NOT NULL,
  `reason` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_rejection_reasons_leave_rejection_id_foreign` (`leave_rejection_id`),
  CONSTRAINT `leave_rejection_reasons_leave_rejection_id_foreign` FOREIGN KEY (`leave_rejection_id`) REFERENCES `leave_rejections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=390 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for leave_rejections
-- ----------------------------
DROP TABLE IF EXISTS `leave_rejections`;
CREATE TABLE `leave_rejections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `leave_id` int(10) unsigned NOT NULL,
  `note` longtext COLLATE utf8_unicode_ci,
  `is_rejected_by_super_admin` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_rejections_leave_id_foreign` (`leave_id`),
  CONSTRAINT `leave_rejections_leave_id_foreign` FOREIGN KEY (`leave_id`) REFERENCES `leaves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=505 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for leave_status_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `leave_status_change_logs`;
CREATE TABLE `leave_status_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `leave_id` int(10) unsigned NOT NULL,
  `from_status` enum('pending','accepted','rejected','canceled') COLLATE utf8_unicode_ci DEFAULT NULL,
  `to_status` enum('pending','accepted','rejected','canceled') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_status_change_logs_leave_id_foreign` (`leave_id`),
  KEY `leave_status_change_logs_from_status_index` (`from_status`),
  KEY `leave_status_change_logs_to_status_index` (`to_status`),
  CONSTRAINT `leave_status_change_logs_leave_id_foreign` FOREIGN KEY (`leave_id`) REFERENCES `leaves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7475 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for leave_types
-- ----------------------------
DROP TABLE IF EXISTS `leave_types`;
CREATE TABLE `leave_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `total_days` int(11) NOT NULL,
  `is_half_day_enable` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_types_business_id_foreign` (`business_id`),
  CONSTRAINT `leave_types_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6184 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for leaves
-- ----------------------------
DROP TABLE IF EXISTS `leaves`;
CREATE TABLE `leaves` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `business_member_id` int(10) unsigned DEFAULT NULL,
  `substitute_id` int(10) unsigned DEFAULT NULL,
  `leave_type_id` int(10) unsigned DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `is_half_day` tinyint(4) NOT NULL DEFAULT '0',
  `half_day_configuration` enum('first_half','second_half') COLLATE utf8_unicode_ci DEFAULT NULL,
  `note` longtext COLLATE utf8_unicode_ci,
  `total_days` decimal(8,2) DEFAULT NULL,
  `left_days` decimal(11,2) DEFAULT NULL,
  `status` enum('pending','accepted','rejected','canceled') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leaves_business_member_id_foreign` (`business_member_id`),
  KEY `leaves_leave_type_id_foreign` (`leave_type_id`),
  KEY `leaves_substitute_id_foreign` (`substitute_id`),
  CONSTRAINT `leaves_business_member_id_foreign` FOREIGN KEY (`business_member_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `leaves_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `leaves_substitute_id_foreign` FOREIGN KEY (`substitute_id`) REFERENCES `business_member` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8951 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for loan_claim_requests
-- ----------------------------
DROP TABLE IF EXISTS `loan_claim_requests`;
CREATE TABLE `loan_claim_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` int(10) unsigned NOT NULL,
  `resource_id` int(10) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `status` enum('pending','approved','declined') COLLATE utf8_unicode_ci NOT NULL,
  `defaulter_date` date DEFAULT NULL,
  `approved_msg_seen` tinyint(4) NOT NULL DEFAULT '0',
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `loan_claim_requests_loan_id_index` (`loan_id`),
  KEY `loan_claim_requests_resource_id_index` (`resource_id`),
  CONSTRAINT `loan_claim_requests_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `partner_bank_loans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `loan_claim_requests_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=312 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for loan_payments
-- ----------------------------
DROP TABLE IF EXISTS `loan_payments`;
CREATE TABLE `loan_payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` int(10) unsigned NOT NULL,
  `loan_claim_request_id` int(10) unsigned NOT NULL,
  `debit` decimal(11,2) DEFAULT NULL,
  `credit` decimal(11,2) DEFAULT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `defaulter_date` date DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `loan_payments_loan_id_foreign` (`loan_id`),
  KEY `loan_payments_loan_claim_request_id_index` (`loan_claim_request_id`),
  CONSTRAINT `loan_payments_loan_claim_request_id_foreign` FOREIGN KEY (`loan_claim_request_id`) REFERENCES `loan_claim_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `loan_payments_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `partner_bank_loans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9613 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for location_offer_group
-- ----------------------------
DROP TABLE IF EXISTS `location_offer_group`;
CREATE TABLE `location_offer_group` (
  `offer_group_id` int(10) unsigned NOT NULL,
  `location_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`offer_group_id`,`location_id`),
  KEY `location_offer_group_location_id_foreign` (`location_id`),
  CONSTRAINT `location_offer_group_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `location_offer_group_offer_group_id_foreign` FOREIGN KEY (`offer_group_id`) REFERENCES `offer_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for location_offer_showcase
-- ----------------------------
DROP TABLE IF EXISTS `location_offer_showcase`;
CREATE TABLE `location_offer_showcase` (
  `location_id` int(10) unsigned NOT NULL,
  `offer_showcase_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`location_id`,`offer_showcase_id`),
  KEY `location_offer_showcase_offer_showcase_id_foreign` (`offer_showcase_id`),
  CONSTRAINT `location_offer_showcase_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `location_offer_showcase_offer_showcase_id_foreign` FOREIGN KEY (`offer_showcase_id`) REFERENCES `offer_showcases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for location_partner
-- ----------------------------
DROP TABLE IF EXISTS `location_partner`;
CREATE TABLE `location_partner` (
  `location_id` int(10) unsigned NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`location_id`,`partner_id`),
  KEY `location_partner_partner_id_foreign` (`partner_id`),
  CONSTRAINT `location_partner_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `location_partner_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for location_partner_service
-- ----------------------------
DROP TABLE IF EXISTS `location_partner_service`;
CREATE TABLE `location_partner_service` (
  `partner_service_id` int(10) unsigned NOT NULL,
  `location_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`partner_service_id`,`location_id`),
  KEY `location_partner_service_location_id_foreign` (`location_id`),
  CONSTRAINT `location_partner_service_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `location_partner_service_partner_service_id_foreign` FOREIGN KEY (`partner_service_id`) REFERENCES `partner_service` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for location_resource
-- ----------------------------
DROP TABLE IF EXISTS `location_resource`;
CREATE TABLE `location_resource` (
  `location_id` int(10) unsigned NOT NULL,
  `resource_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`location_id`,`resource_id`),
  KEY `location_resource_resource_id_foreign` (`resource_id`),
  CONSTRAINT `location_resource_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `location_resource_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for location_screen_setting
-- ----------------------------
DROP TABLE IF EXISTS `location_screen_setting`;
CREATE TABLE `location_screen_setting` (
  `location_id` int(10) unsigned NOT NULL,
  `screen_setting_id` int(10) unsigned NOT NULL,
  `screen_setting_element_id` int(10) unsigned NOT NULL,
  `order` smallint(6) DEFAULT NULL,
  UNIQUE KEY `loc_scr_elem_order_unique` (`location_id`,`screen_setting_id`,`screen_setting_element_id`,`order`),
  KEY `location_screen_setting_screen_setting_id_foreign` (`screen_setting_id`),
  KEY `location_screen_setting_screen_setting_element_id_foreign` (`screen_setting_element_id`),
  CONSTRAINT `location_screen_setting_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `location_screen_setting_screen_setting_element_id_foreign` FOREIGN KEY (`screen_setting_element_id`) REFERENCES `screen_setting_elements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `location_screen_setting_screen_setting_id_foreign` FOREIGN KEY (`screen_setting_id`) REFERENCES `screen_settings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for location_service
-- ----------------------------
DROP TABLE IF EXISTS `location_service`;
CREATE TABLE `location_service` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `location_id` int(10) unsigned NOT NULL,
  `service_id` int(10) unsigned NOT NULL,
  `base_quantity` longtext COLLATE utf8_unicode_ci,
  `base_prices` longtext COLLATE utf8_unicode_ci,
  `prices` longtext COLLATE utf8_unicode_ci NOT NULL,
  `min_prices` longtext COLLATE utf8_unicode_ci,
  `upsell_price` json DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `location_service_service_id_foreign` (`service_id`),
  KEY `location_service_location_id_index` (`location_id`),
  CONSTRAINT `location_service_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `location_service_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=180299 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for location_service_service_discount
-- ----------------------------
DROP TABLE IF EXISTS `location_service_service_discount`;
CREATE TABLE `location_service_service_discount` (
  `location_service_id` int(10) unsigned NOT NULL,
  `service_discount_id` int(10) unsigned NOT NULL,
  KEY `location_service_service_discount_location_service_id_foreign` (`location_service_id`),
  KEY `location_service_service_discount_service_discount_id_foreign` (`service_discount_id`),
  CONSTRAINT `location_service_service_discount_location_service_id_foreign` FOREIGN KEY (`location_service_id`) REFERENCES `location_service` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `location_service_service_discount_service_discount_id_foreign` FOREIGN KEY (`service_discount_id`) REFERENCES `service_discounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for locations
-- ----------------------------
DROP TABLE IF EXISTS `locations`;
CREATE TABLE `locations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `city_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `geo_informations` longtext COLLATE utf8_unicode_ci,
  `publication_status` tinyint(1) NOT NULL DEFAULT '0',
  `is_published_for_partner` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `locations_city_id_foreign` (`city_id`),
  CONSTRAINT `locations_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=192 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for materials
-- ----------------------------
DROP TABLE IF EXISTS `materials`;
CREATE TABLE `materials` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `material_sku` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `price` decimal(11,2) NOT NULL,
  `service_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for meals
-- ----------------------------
DROP TABLE IF EXISTS `meals`;
CREATE TABLE `meals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `no_of_meals` smallint(5) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `meals_user_id_foreign` (`user_id`),
  CONSTRAINT `meals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for members
-- ----------------------------
DROP TABLE IF EXISTS `members`;
CREATE TABLE `members` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `remember_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `profile_id` int(10) unsigned NOT NULL,
  `father_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `spouse_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mother_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nid_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nid_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `emergency_contract_person_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `alternate_contact` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `emergency_contract_person_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `education` text COLLATE utf8_unicode_ci,
  `emergency_contract_person_relationship` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `profession` text COLLATE utf8_unicode_ci,
  `references` text COLLATE utf8_unicode_ci,
  `bank_account` text COLLATE utf8_unicode_ci,
  `mfs_account` text COLLATE utf8_unicode_ci,
  `other_expertise` text COLLATE utf8_unicode_ci,
  `experience` text COLLATE utf8_unicode_ci,
  `present_income` mediumint(8) unsigned DEFAULT NULL,
  `ward_no` text COLLATE utf8_unicode_ci,
  `police_station` text COLLATE utf8_unicode_ci,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `social_links` json DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `members_profile_id_unique` (`profile_id`),
  UNIQUE KEY `members_remember_token_unique` (`remember_token`),
  KEY `members_profile_id_foreign` (`profile_id`),
  CONSTRAINT `members_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10329 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for mentions
-- ----------------------------
DROP TABLE IF EXISTS `mentions`;
CREATE TABLE `mentions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mentionable_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mentionable_id` int(10) unsigned NOT NULL,
  `mentioned_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mentioned_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=174763 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for meta_tags
-- ----------------------------
DROP TABLE IF EXISTS `meta_tags`;
CREATE TABLE `meta_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `taggable_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `taggable_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `meta_tag` json NOT NULL,
  `og_tag` json NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `meta_tags_taggable_type_taggable_id_unique` (`taggable_type`,`taggable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=374 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for movie_ticket_orders
-- ----------------------------
DROP TABLE IF EXISTS `movie_ticket_orders`;
CREATE TABLE `movie_ticket_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `agent_type` enum('App\\Models\\Partner','App\\Models\\Affiliate','App\\Models\\Customer','App\\Models\\Vendor','App\\Models\\Business') COLLATE utf8_unicode_ci DEFAULT NULL,
  `agent_id` int(10) unsigned NOT NULL,
  `reserver_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reserver_mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `reserver_email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(11,2) unsigned NOT NULL,
  `sheba_commission` decimal(5,2) NOT NULL DEFAULT '0.00',
  `agent_commission` decimal(5,2) NOT NULL DEFAULT '0.00',
  `ambassador_commission` decimal(5,2) NOT NULL DEFAULT '0.00',
  `vendor_id` int(10) unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reservation_details` text COLLATE utf8_unicode_ci,
  `voucher_id` int(10) unsigned DEFAULT NULL,
  `discount` decimal(8,2) unsigned NOT NULL,
  `discount_percent` decimal(5,2) unsigned NOT NULL,
  `sheba_contribution` decimal(5,2) unsigned NOT NULL,
  `vendor_contribution` decimal(5,2) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `movie_ticket_orders_vendor_id_foreign` (`vendor_id`),
  KEY `movie_ticket_orders_voucher_id_foreign` (`voucher_id`),
  CONSTRAINT `movie_ticket_orders_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `movie_ticket_vendors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `movie_ticket_orders_voucher_id_foreign` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=680 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for movie_ticket_recharge_history
-- ----------------------------
DROP TABLE IF EXISTS `movie_ticket_recharge_history`;
CREATE TABLE `movie_ticket_recharge_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int(10) unsigned DEFAULT NULL,
  `amount` decimal(11,2) unsigned NOT NULL,
  `recharge_date` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `movie_ticket_recharge_history_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `movie_ticket_recharge_history_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `movie_ticket_vendors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for movie_ticket_vendor_commissions
-- ----------------------------
DROP TABLE IF EXISTS `movie_ticket_vendor_commissions`;
CREATE TABLE `movie_ticket_vendor_commissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `movie_ticket_vendor_id` int(10) unsigned NOT NULL,
  `agent_commission` decimal(5,2) NOT NULL DEFAULT '0.00',
  `ambassador_commission` decimal(5,2) NOT NULL DEFAULT '0.00',
  `type` enum('App\\Models\\Partner','App\\Models\\Affiliate','App\\Models\\Customer','App\\Models\\Vendor','App\\Models\\Business') COLLATE utf8_unicode_ci DEFAULT NULL,
  `type_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `movie_ticket_vendor_commissions_movie_ticket_vendor_id_foreign` (`movie_ticket_vendor_id`),
  CONSTRAINT `movie_ticket_vendor_commissions_movie_ticket_vendor_id_foreign` FOREIGN KEY (`movie_ticket_vendor_id`) REFERENCES `movie_ticket_vendors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for movie_ticket_vendors
-- ----------------------------
DROP TABLE IF EXISTS `movie_ticket_vendors`;
CREATE TABLE `movie_ticket_vendors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(11,2) unsigned NOT NULL,
  `sheba_commission` decimal(5,2) NOT NULL DEFAULT '0.00',
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `wallet_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for neo_banking_third_party_logs
-- ----------------------------
DROP TABLE IF EXISTS `neo_banking_third_party_logs`;
CREATE TABLE `neo_banking_third_party_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `request` longtext COLLATE utf8_unicode_ci NOT NULL,
  `response` longtext COLLATE utf8_unicode_ci NOT NULL,
  `from` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `others` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `neo_banking_third_party_logs_partner_id_foreign` (`partner_id`),
  CONSTRAINT `neo_banking_third_party_logs_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=615 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for neo_banks
-- ----------------------------
DROP TABLE IF EXISTS `neo_banks`;
CREATE TABLE `neo_banks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name_bn` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `bank_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for newsletters
-- ----------------------------
DROP TABLE IF EXISTS `newsletters`;
CREATE TABLE `newsletters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic','business-portal') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for noc_requests
-- ----------------------------
DROP TABLE IF EXISTS `noc_requests`;
CREATE TABLE `noc_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resource_id` int(10) unsigned NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  `status` enum('pending','approved','rejected','cancelled') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `request` text COLLATE utf8_unicode_ci,
  `reviewed_at` datetime DEFAULT NULL,
  `note` text COLLATE utf8_unicode_ci,
  `noc_doc` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `noc_requests_resource_id_foreign` (`resource_id`),
  KEY `noc_requests_partner_id_foreign` (`partner_id`),
  CONSTRAINT `noc_requests_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `noc_requests_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for notification_settings
-- ----------------------------
DROP TABLE IF EXISTS `notification_settings`;
CREATE TABLE `notification_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `rules` text COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sound` enum('','aww','beep','bug','casual','chime','coins','hell-yeah','high','machine-gun','shoot-em','tweet') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_settings_user_id_unique` (`user_id`),
  CONSTRAINT `notification_settings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1318 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for notifications
-- ----------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `notifiable_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `notifiable_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `event_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `event_id` int(10) unsigned DEFAULT NULL,
  `type` enum('Info','Warning','Danger','Success') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Info',
  `is_seen` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_index` (`notifiable_type`,`notifiable_id`),
  KEY `notifications_event_index` (`event_type`,`event_id`)
) ENGINE=InnoDB AUTO_INCREMENT=96147507 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for offer_group_offer
-- ----------------------------
DROP TABLE IF EXISTS `offer_group_offer`;
CREATE TABLE `offer_group_offer` (
  `offer_group_id` int(10) unsigned NOT NULL,
  `offer_showcase_id` int(10) unsigned NOT NULL,
  UNIQUE KEY `offer_group_offer_offer_group_id_offer_showcase_id_unique` (`offer_group_id`,`offer_showcase_id`),
  KEY `offer_group_offer_offer_showcase_id_foreign` (`offer_showcase_id`),
  CONSTRAINT `offer_group_offer_offer_group_id_foreign` FOREIGN KEY (`offer_group_id`) REFERENCES `offer_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `offer_group_offer_offer_showcase_id_foreign` FOREIGN KEY (`offer_showcase_id`) REFERENCES `offer_showcases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for offer_groups
-- ----------------------------
DROP TABLE IF EXISTS `offer_groups`;
CREATE TABLE `offer_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `short_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `long_description` longtext COLLATE utf8_unicode_ci,
  `meta_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `thumb` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `banner` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `app_thumb` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `app_banner` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `banner_type` enum('mini','medium','jumbo') COLLATE utf8_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `icon_png` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_published_for_app` tinyint(1) NOT NULL DEFAULT '0',
  `is_published_for_web` tinyint(1) NOT NULL DEFAULT '0',
  `is_flash` tinyint(4) NOT NULL DEFAULT '0',
  `order` tinyint(1) NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for offer_showcases
-- ----------------------------
DROP TABLE IF EXISTS `offer_showcases`;
CREATE TABLE `offer_showcases` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `app_thumb` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `structured_title` longtext COLLATE utf8_unicode_ci,
  `short_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `structured_description` longtext COLLATE utf8_unicode_ci,
  `banner` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `app_banner` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `banner_type` enum('mini','medium','jumbo','popup') COLLATE utf8_unicode_ci DEFAULT NULL,
  `detail_description` longtext COLLATE utf8_unicode_ci,
  `offer_group` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `target_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `target_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `target_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `button_text` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `amount` decimal(8,2) DEFAULT NULL,
  `order` smallint(6) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_flash` tinyint(1) NOT NULL DEFAULT '0',
  `is_campaign` tinyint(4) NOT NULL DEFAULT '0',
  `is_pop_up` tinyint(1) NOT NULL DEFAULT '0',
  `is_banner_only` tinyint(1) NOT NULL DEFAULT '0',
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1324 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for office_policy_rules
-- ----------------------------
DROP TABLE IF EXISTS `office_policy_rules`;
CREATE TABLE `office_policy_rules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `policy_type` enum('grace_period','late_checkin_early_checkout','unpaid_leave') COLLATE utf8_unicode_ci NOT NULL,
  `from_days` smallint(5) unsigned NOT NULL,
  `to_days` smallint(5) unsigned NOT NULL,
  `action` enum('no_penalty','leave_adjustment','salary_adjustment','cash_penalty') COLLATE utf8_unicode_ci NOT NULL,
  `penalty_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `penalty_amount` decimal(8,2) DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `office_policy_rules_business_id_foreign` (`business_id`),
  CONSTRAINT `office_policy_rules_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=581 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for office_setting_changes_logs
-- ----------------------------
DROP TABLE IF EXISTS `office_setting_changes_logs`;
CREATE TABLE `office_setting_changes_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `from` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `to` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `logs` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `office_setting_changes_logs_business_id_foreign` (`business_id`),
  CONSTRAINT `office_setting_changes_logs_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=993 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for order_order_tags
-- ----------------------------
DROP TABLE IF EXISTS `order_order_tags`;
CREATE TABLE `order_order_tags` (
  `order_id` int(10) unsigned NOT NULL,
  `order_tags_id` int(10) unsigned NOT NULL,
  KEY `order_order_tags_order_id_foreign` (`order_id`),
  KEY `order_order_tags_order_tags_id_foreign` (`order_tags_id`),
  CONSTRAINT `order_order_tags_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_order_tags_order_tags_id_foreign` FOREIGN KEY (`order_tags_id`) REFERENCES `order_tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for order_tags
-- ----------------------------
DROP TABLE IF EXISTS `order_tags`;
CREATE TABLE `order_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_tags_tag_unique` (`tag`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for order_update_logs
-- ----------------------------
DROP TABLE IF EXISTS `order_update_logs`;
CREATE TABLE `order_update_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `old_data` text COLLATE utf8_unicode_ci NOT NULL,
  `new_data` text COLLATE utf8_unicode_ci NOT NULL,
  `log` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_update_logs_order_id_foreign` (`order_id`),
  CONSTRAINT `order_update_logs_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5329 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for orders
-- ----------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `partner_id` int(10) unsigned DEFAULT NULL,
  `business_id` int(10) unsigned DEFAULT NULL,
  `vendor_id` int(10) unsigned DEFAULT NULL,
  `payer_id` int(10) unsigned DEFAULT NULL,
  `payer_type` enum('customer','partner','affiliate','business') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'customer',
  `delivery_address_id` int(10) unsigned DEFAULT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `voucher_id` int(10) unsigned DEFAULT NULL,
  `info_call_id` int(10) unsigned DEFAULT NULL,
  `custom_order_id` int(10) unsigned DEFAULT NULL,
  `subscription_order_id` int(10) unsigned DEFAULT NULL,
  `affiliation_id` int(10) unsigned DEFAULT NULL,
  `pap_visitor_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pap_affiliate_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `delivery_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `delivery_mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `delivery_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sales_channel` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `affiliation_cost` decimal(6,2) unsigned DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `favourite_id` int(10) unsigned DEFAULT NULL,
  `unreachable_sms_sent` tinyint(4) NOT NULL DEFAULT '0',
  `unreachable_sms_sent_partner` tinyint(4) NOT NULL DEFAULT '0',
  `cancellation_sms_sent` tinyint(1) NOT NULL DEFAULT '0',
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cx_remark` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `api_request_id` int(11) DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_affiliation_id_unique` (`affiliation_id`),
  UNIQUE KEY `orders_info_call_id_unique` (`info_call_id`),
  UNIQUE KEY `orders_custom_order_id_unique` (`custom_order_id`),
  KEY `orders_delivery_address_id_foreign` (`delivery_address_id`),
  KEY `orders_partner_id_foreign` (`partner_id`),
  KEY `orders_favourite_id_foreign` (`favourite_id`),
  KEY `orders_vendor_id_foreign` (`vendor_id`),
  KEY `orders_subscription_order_id_foreign` (`subscription_order_id`),
  KEY `orders_customer_id_foreign` (`customer_id`),
  KEY `orders_location_id_foreign` (`location_id`),
  KEY `orders_voucher_id_foreign` (`voucher_id`),
  KEY `orders_business_id_foreign` (`business_id`),
  FULLTEXT KEY `orders_delivery_name_index` (`delivery_name`),
  CONSTRAINT `orders_affiliation_id_foreign` FOREIGN KEY (`affiliation_id`) REFERENCES `affiliations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_custom_order_id_foreign` FOREIGN KEY (`custom_order_id`) REFERENCES `custom_orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_delivery_address_id_foreign` FOREIGN KEY (`delivery_address_id`) REFERENCES `customer_delivery_addresses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_favourite_id_foreign` FOREIGN KEY (`favourite_id`) REFERENCES `customer_favourites` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_info_call_id_foreign` FOREIGN KEY (`info_call_id`) REFERENCES `info_calls` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_subscription_order_id_foreign` FOREIGN KEY (`subscription_order_id`) REFERENCES `subscription_orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_voucher_id_foreign` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=579610 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for otp_vendor_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `otp_vendor_change_logs`;
CREATE TABLE `otp_vendor_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `to_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for package_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `package_change_logs`;
CREATE TABLE `package_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `package_id` int(10) unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `from` longtext COLLATE utf8_unicode_ci NOT NULL,
  `to` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `package_change_logs_package_id_foreign` (`package_id`),
  CONSTRAINT `package_change_logs_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `partner_subscription_packages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=232 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for package_duration_update_logs
-- ----------------------------
DROP TABLE IF EXISTS `package_duration_update_logs`;
CREATE TABLE `package_duration_update_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(11) NOT NULL,
  `previous_billing_date` date DEFAULT NULL,
  `updated_billing_date` date DEFAULT NULL,
  `selling_price` decimal(11,2) DEFAULT NULL,
  `previous_price` decimal(11,2) DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=744 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for package_status_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `package_status_change_logs`;
CREATE TABLE `package_status_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `package_id` int(10) unsigned NOT NULL,
  `from_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `to_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `log` text COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `package_status_change_logs_package_id_foreign` (`package_id`),
  CONSTRAINT `package_status_change_logs_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `partner_subscription_packages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_affiliations
-- ----------------------------
DROP TABLE IF EXISTS `partner_affiliations`;
CREATE TABLE `partner_affiliations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `affiliate_id` int(10) unsigned NOT NULL,
  `company_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resource_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resource_mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('pending','rejected','successful') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `acquisition_cost` decimal(8,2) DEFAULT NULL,
  `reject_reason` enum('fake','no_response','not_interested','not_capable','service_unavailable','blacklisted','closed') COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_affiliations_affiliate_id_foreign` (`affiliate_id`),
  CONSTRAINT `partner_affiliations_affiliate_id_foreign` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16264 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_bank_informations
-- ----------------------------
DROP TABLE IF EXISTS `partner_bank_informations`;
CREATE TABLE `partner_bank_informations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `purpose` enum('partner_wallet_withdrawal','general') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'general',
  `acc_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `acc_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `acc_type` enum('savings','current') COLLATE utf8_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bank_address` text COLLATE utf8_unicode_ci,
  `branch_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `routing_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cheque_book_receipt` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `other_details` text COLLATE utf8_unicode_ci,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verification_note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `statement` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_bank_informations_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_bank_informations_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=79160 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_bank_loan_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `partner_bank_loan_change_logs`;
CREATE TABLE `partner_bank_loan_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `from` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `to` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_bank_loan_change_logs_loan_id_foreign` (`loan_id`),
  CONSTRAINT `partner_bank_loan_change_logs_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `partner_bank_loans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16326 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_bank_loans
-- ----------------------------
DROP TABLE IF EXISTS `partner_bank_loans`;
CREATE TABLE `partner_bank_loans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `bank_id` int(10) unsigned DEFAULT NULL,
  `loan_amount` decimal(11,2) unsigned NOT NULL,
  `status` enum('rejected','approved','closed','considerable','applied','submitted','withdrawal','verified','hold','sanction_issued','disbursed','declined') COLLATE utf8_unicode_ci DEFAULT NULL,
  `duration` int(11) NOT NULL,
  `monthly_installment` decimal(11,2) unsigned NOT NULL,
  `interest_rate` decimal(11,2) unsigned DEFAULT NULL,
  `credit_score` decimal(11,2) unsigned DEFAULT NULL,
  `purpose` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `final_information_for_loan` json NOT NULL,
  `type` enum('term','micro') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'term',
  `groups` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_annual_fee_payment_at` datetime DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_bank_loans_partner_id_foreign` (`partner_id`),
  KEY `partner_bank_loans_bank_id_foreign` (`bank_id`),
  CONSTRAINT `partner_bank_loans_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `partner_bank_loans_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14941 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_basic_informations
-- ----------------------------
DROP TABLE IF EXISTS `partner_basic_informations`;
CREATE TABLE `partner_basic_informations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `registration_year` date DEFAULT NULL,
  `registration_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `establishment_year` date DEFAULT NULL,
  `trade_license` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trade_license_issue_date` datetime DEFAULT NULL,
  `business_category` enum('Micro','Small','Medium') COLLATE utf8_unicode_ci DEFAULT NULL,
  `sector` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trade_license_attachment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shop_physical_image` text COLLATE utf8_unicode_ci,
  `vat_registration_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `show_vat_registration_number` tinyint(4) NOT NULL DEFAULT '0',
  `vat_registration_attachment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tin_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tin_licence_photo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shop_type` enum('physical','virtual','both') COLLATE utf8_unicode_ci DEFAULT NULL,
  `monthly_transaction_volume` int(11) DEFAULT NULL,
  `electricity_bill_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cpv_documents` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cpv_status` enum('pending','verified','unverified') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'unverified',
  `grantor` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `security_cheque` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `website_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `company_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `map_location` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `working_days` varchar(255) COLLATE utf8_unicode_ci DEFAULT '["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"]' COMMENT '["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"]',
  `working_hours` varchar(255) COLLATE utf8_unicode_ci DEFAULT '{"day_end":"9:00 PM","day_start":"7:00 AM"}' COMMENT '{"day_end":"7:00 PM","day_start":"9:00 AM"}',
  `other_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `other_id_issue_date` date DEFAULT NULL,
  `permanent_address` json DEFAULT NULL,
  `present_address` json DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verification_note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_information` json DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_basic_informations_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_basic_informations_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1181049 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_closing_requests
-- ----------------------------
DROP TABLE IF EXISTS `partner_closing_requests`;
CREATE TABLE `partner_closing_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `type` enum('closing','opening') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'closing',
  `status` enum('pending','approved','rejected','cancelled') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `request` text COLLATE utf8_unicode_ci,
  `reviewed_at` datetime DEFAULT NULL,
  `note` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_closing_requests_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_closing_requests_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_daily_stats
-- ----------------------------
DROP TABLE IF EXISTS `partner_daily_stats`;
CREATE TABLE `partner_daily_stats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `partner_daily_stats_partner_id_date_unique` (`partner_id`,`date`),
  CONSTRAINT `partner_daily_stats_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=685723 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_data_migrations
-- ----------------------------
DROP TABLE IF EXISTS `partner_data_migrations`;
CREATE TABLE `partner_data_migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `status` enum('initiated','failed','successful') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'initiated',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_data_migrations_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_data_migrations_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_delivery_information
-- ----------------------------
DROP TABLE IF EXISTS `partner_delivery_information`;
CREATE TABLE `partner_delivery_information` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  `merchant_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `business_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `district` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `thana` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `website` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `facebook` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `account_type` enum('bank','mobile') COLLATE utf8_unicode_ci NOT NULL,
  `mobile_banking_provider` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `agent_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `account_holder_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `branch_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `account_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `routing_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `delivery_vendor` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `partner_delivery_information_mobile_unique` (`mobile`),
  UNIQUE KEY `partner_delivery_information_email_unique` (`email`),
  KEY `partner_delivery_information_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_delivery_information_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3552 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_general_settings
-- ----------------------------
DROP TABLE IF EXISTS `partner_general_settings`;
CREATE TABLE `partner_general_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `payment_completion_sms` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_general_settings_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_general_settings_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_geo_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `partner_geo_change_logs`;
CREATE TABLE `partner_geo_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `old_geo_informations` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `new_geo_informations` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_geo_change_logs_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_geo_change_logs_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=168961 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_helps
-- ----------------------------
DROP TABLE IF EXISTS `partner_helps`;
CREATE TABLE `partner_helps` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `status` enum('open','closed') COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_helps_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_helps_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=137754 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_leaves
-- ----------------------------
DROP TABLE IF EXISTS `partner_leaves`;
CREATE TABLE `partner_leaves` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_leaves_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_leaves_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=214930 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_location_update_requests
-- ----------------------------
DROP TABLE IF EXISTS `partner_location_update_requests`;
CREATE TABLE `partner_location_update_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `old_locations` text COLLATE utf8_unicode_ci NOT NULL,
  `new_locations` text COLLATE utf8_unicode_ci NOT NULL,
  `old_locations_name` text COLLATE utf8_unicode_ci NOT NULL,
  `new_locations_name` text COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_location_update_requests_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_location_update_requests_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6336 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_neo_banking_accounts
-- ----------------------------
DROP TABLE IF EXISTS `partner_neo_banking_accounts`;
CREATE TABLE `partner_neo_banking_accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `account_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bank_id` int(10) unsigned NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `partner_neo_banking_accounts_transaction_id_unique` (`transaction_id`),
  KEY `partner_neo_banking_accounts_bank_id_foreign` (`bank_id`),
  KEY `partner_neo_banking_accounts_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_neo_banking_accounts_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `neo_banks` (`id`),
  CONSTRAINT `partner_neo_banking_accounts_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_neo_banking_information
-- ----------------------------
DROP TABLE IF EXISTS `partner_neo_banking_information`;
CREATE TABLE `partner_neo_banking_information` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `is_gigatech_verified` tinyint(1) NOT NULL DEFAULT '0',
  `information_for_bank_account` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_neo_banking_information_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_neo_banking_information_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_order_advance_requests
-- ----------------------------
DROP TABLE IF EXISTS `partner_order_advance_requests`;
CREATE TABLE `partner_order_advance_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_order_id` int(10) unsigned NOT NULL,
  `amount` decimal(11,2) unsigned NOT NULL,
  `status` enum('Pending','Approval Pending','Approved','Rejected','Completed','Cancelled') COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_order_advance_requests_partner_order_id_foreign` (`partner_order_id`),
  CONSTRAINT `partner_order_advance_requests_partner_order_id_foreign` FOREIGN KEY (`partner_order_id`) REFERENCES `partner_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_order_finance_collections
-- ----------------------------
DROP TABLE IF EXISTS `partner_order_finance_collections`;
CREATE TABLE `partner_order_finance_collections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_order_id` int(10) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `transaction_type` enum('Debit','Credit') COLLATE utf8_unicode_ci NOT NULL,
  `method` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_order_finance_collections_partner_order_id_foreign` (`partner_order_id`),
  CONSTRAINT `partner_order_finance_collections_partner_order_id_foreign` FOREIGN KEY (`partner_order_id`) REFERENCES `partner_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_order_limit_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `partner_order_limit_change_logs`;
CREATE TABLE `partner_order_limit_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `from` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `to` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_order_limit_change_logs_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_order_limit_change_logs_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2005 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_order_payments
-- ----------------------------
DROP TABLE IF EXISTS `partner_order_payments`;
CREATE TABLE `partner_order_payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_order_id` int(10) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `transaction_type` enum('Debit','Credit') COLLATE utf8_unicode_ci NOT NULL,
  `method` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `collected_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `refund_transaction_id` int(11) DEFAULT NULL,
  `transaction_detail` text COLLATE utf8_unicode_ci NOT NULL,
  `is_refund_transaction` tinyint(4) NOT NULL DEFAULT '0',
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_order_payments_partner_order_id_foreign` (`partner_order_id`),
  CONSTRAINT `partner_order_payments_ibfk_1` FOREIGN KEY (`partner_order_id`) REFERENCES `partner_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=364562 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_order_reconcile_logs
-- ----------------------------
DROP TABLE IF EXISTS `partner_order_reconcile_logs`;
CREATE TABLE `partner_order_reconcile_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_order_id` int(10) unsigned DEFAULT NULL,
  `partner_collection` decimal(11,2) NOT NULL,
  `sheba_collection` decimal(11,2) NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `to` enum('Sheba','SP') COLLATE utf8_unicode_ci NOT NULL,
  `log` text COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_order_reconcile_logs_partner_order_id_foreign` (`partner_order_id`),
  CONSTRAINT `partner_order_reconcile_logs_partner_order_id_foreign` FOREIGN KEY (`partner_order_id`) REFERENCES `partner_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=329471 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_order_report
-- ----------------------------
DROP TABLE IF EXISTS `partner_order_report`;
CREATE TABLE `partner_order_report` (
  `id` int(10) unsigned NOT NULL,
  `order_code` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `order_media` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `order_channel` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `order_portal` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `order_unique_id` int(10) unsigned DEFAULT NULL,
  `order_first_created` datetime DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `request_created_date` datetime DEFAULT NULL,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_mobile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_total_order` smallint(5) unsigned NOT NULL DEFAULT '0',
  `customer_registration_date` datetime DEFAULT NULL,
  `location` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `delivery_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `delivery_mobile` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `delivery_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_vip` tinyint(1) NOT NULL DEFAULT '0',
  `sp_id` int(10) unsigned DEFAULT NULL,
  `sp_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sp_mobile` varchar(14) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sp_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resource_id` int(10) unsigned DEFAULT NULL,
  `resource_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_sp_changed` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `job_status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `serving_life_time` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cancelled_date` datetime DEFAULT NULL,
  `cancel_reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `closed_date` datetime DEFAULT NULL,
  `closed_and_paid_date` datetime DEFAULT NULL,
  `payment_status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `promo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `promo_tags` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reference_id` int(10) unsigned DEFAULT NULL,
  `agent_tags` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `csat` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_acquisition_or_retention` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_complaint` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `sp_complaint` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `total_complaint` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `created_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `order_updated_at` datetime DEFAULT NULL,
  `om` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_info` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `quantity` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `schedule_date` date DEFAULT NULL,
  `schedule_time` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `csat_date` datetime DEFAULT NULL,
  `csat_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reschedule_counter` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `schedule_due_counter` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `price_change_counter` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `accept_date` datetime DEFAULT NULL,
  `accepted_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `accepted_from` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `declined_date` datetime DEFAULT NULL,
  `declined_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `declined_from` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `served_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `served_from` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cancel_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cancel_from` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cancel_requested_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cancel_reason_details` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `complain_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `csat_remarks` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status_changes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `master_category_id` int(10) unsigned DEFAULT NULL,
  `master_category_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `service_category_id` int(10) unsigned DEFAULT NULL,
  `service_category_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `service_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `services` text COLLATE utf8_unicode_ci,
  `gmv_service` decimal(11,2) NOT NULL DEFAULT '0.00',
  `gmv_material` decimal(11,2) NOT NULL DEFAULT '0.00',
  `gmv_delivery` decimal(11,2) NOT NULL DEFAULT '0.00',
  `gmv` decimal(11,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `discount_sheba` decimal(11,2) NOT NULL DEFAULT '0.00',
  `discount_partner` decimal(11,2) NOT NULL DEFAULT '0.00',
  `rounding_cut_off` decimal(11,2) NOT NULL DEFAULT '0.00',
  `billed_amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `service_charge` decimal(11,2) NOT NULL DEFAULT '0.00',
  `revenue` decimal(11,2) NOT NULL DEFAULT '0.00',
  `sp_cost_service` decimal(11,2) NOT NULL DEFAULT '0.00',
  `sp_cost_additional` decimal(11,2) NOT NULL DEFAULT '0.00',
  `sp_cost_delivery` decimal(11,2) NOT NULL DEFAULT '0.00',
  `sp_cost` decimal(11,2) NOT NULL DEFAULT '0.00',
  `collected_sheba` decimal(11,2) NOT NULL DEFAULT '0.00',
  `collected_sp` decimal(11,2) NOT NULL DEFAULT '0.00',
  `collection` decimal(11,2) NOT NULL DEFAULT '0.00',
  `contained_by_sheba` decimal(11,2) NOT NULL DEFAULT '0.00',
  `contained_by_sp` decimal(11,2) NOT NULL DEFAULT '0.00',
  `due` decimal(11,2) NOT NULL DEFAULT '0.00',
  `profit` decimal(11,2) NOT NULL DEFAULT '0.00',
  `sp_payable` decimal(11,2) NOT NULL DEFAULT '0.00',
  `sheba_receivable` decimal(11,2) NOT NULL DEFAULT '0.00',
  `collected_by_finance` decimal(11,2) NOT NULL DEFAULT '0.00',
  `revenue_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `service_charge_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `report_updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_order_report_closed_date_index` (`closed_date`),
  KEY `partner_order_report_created_date_index` (`created_date`),
  KEY `partner_order_report_sp_id_index` (`sp_id`),
  KEY `partner_order_report_order_code_unique` (`order_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_order_requests
-- ----------------------------
DROP TABLE IF EXISTS `partner_order_requests`;
CREATE TABLE `partner_order_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_order_id` int(10) unsigned NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  `status` enum('pending','accepted','declined','not_responded','missed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_order_requests_partner_order_id_foreign` (`partner_order_id`),
  KEY `partner_order_requests_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_order_requests_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `partner_order_requests_partner_order_id_foreign` FOREIGN KEY (`partner_order_id`) REFERENCES `partner_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=207402 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_order_stage_logs
-- ----------------------------
DROP TABLE IF EXISTS `partner_order_stage_logs`;
CREATE TABLE `partner_order_stage_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_order_id` int(10) unsigned NOT NULL,
  `from_stage` enum('unassigned','assigned','acknowledged','sp_confirmed','sp_reconfirmed','customer_reconfirmed','in_process','served','payment_due','completed') COLLATE utf8_unicode_ci NOT NULL,
  `to_stage` enum('unassigned','assigned','acknowledged','sp_confirmed','sp_reconfirmed','customer_reconfirmed','in_process','served','payment_due','completed') COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_order_stage_logs_partner_order_id_foreign` (`partner_order_id`),
  CONSTRAINT `partner_order_stage_logs_partner_order_id_foreign` FOREIGN KEY (`partner_order_id`) REFERENCES `partner_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_order_status_logs
-- ----------------------------
DROP TABLE IF EXISTS `partner_order_status_logs`;
CREATE TABLE `partner_order_status_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_order_id` int(10) unsigned NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `from_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `to_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_order_status_logs_partner_order_id_foreign` (`partner_order_id`),
  CONSTRAINT `partner_order_status_logs_partner_order_id_foreign` FOREIGN KEY (`partner_order_id`) REFERENCES `partner_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1169820 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_orders
-- ----------------------------
DROP TABLE IF EXISTS `partner_orders`;
CREATE TABLE `partner_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `partner_id` int(10) unsigned DEFAULT NULL,
  `discount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `discount_percent` decimal(5,2) DEFAULT NULL,
  `sheba_collection` decimal(11,2) NOT NULL DEFAULT '0.00',
  `partner_collection` decimal(11,2) NOT NULL DEFAULT '0.00',
  `refund_amount` decimal(11,2) NOT NULL DEFAULT '0.00',
  `is_reconciled` tinyint(1) NOT NULL DEFAULT '0',
  `invoice` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `partner_searched_count` int(11) NOT NULL DEFAULT '1',
  `partners_for_sp_assign` json DEFAULT NULL,
  `payment_method` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `collected_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `closed_and_paid_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `finance_collection` decimal(11,2) NOT NULL DEFAULT '0.00',
  `finance_cm_cleared_at` timestamp NULL DEFAULT NULL,
  `finance_closed_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `partner_orders_order_id_partner_id_unique` (`order_id`,`partner_id`) USING BTREE,
  KEY `partner_orders_order_id_foreign` (`order_id`),
  KEY `partner_orders_partner_id_foreign` (`partner_id`),
  KEY `partner_orders_closed_at_index` (`closed_at`) USING BTREE,
  KEY `partner_orders_cancelled_at_index` (`cancelled_at`) USING BTREE,
  KEY `partner_orders_closed_and_paid_at_index` (`closed_and_paid_at`) USING BTREE,
  KEY `partner_orders_due_index` (`closed_at`,`cancelled_at`) USING BTREE,
  CONSTRAINT `partner_order_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `partner_orders_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=636034 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_package_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `partner_package_change_logs`;
CREATE TABLE `partner_package_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `old_package_id` int(10) unsigned NOT NULL,
  `new_package_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_package_change_logs_partner_id_foreign` (`partner_id`),
  KEY `partner_package_change_logs_old_package_id_foreign` (`old_package_id`),
  KEY `partner_package_change_logs_new_package_id_foreign` (`new_package_id`),
  CONSTRAINT `partner_package_change_logs_new_package_id_foreign` FOREIGN KEY (`new_package_id`) REFERENCES `partner_subscription_packages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `partner_package_change_logs_old_package_id_foreign` FOREIGN KEY (`old_package_id`) REFERENCES `partner_subscription_packages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `partner_package_change_logs_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_package_update_requests
-- ----------------------------
DROP TABLE IF EXISTS `partner_package_update_requests`;
CREATE TABLE `partner_package_update_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `old_package_id` int(10) unsigned DEFAULT NULL,
  `old_billing_type` varchar(160) COLLATE utf8_unicode_ci DEFAULT NULL,
  `new_package_id` int(10) unsigned DEFAULT NULL,
  `discount_id` int(10) unsigned DEFAULT NULL,
  `new_billing_type` varchar(160) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Pending',
  `log` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_package_update_requests_partner_id_foreign` (`partner_id`),
  KEY `partner_package_update_requests_old_package_id_foreign` (`old_package_id`),
  KEY `partner_package_update_requests_new_package_id_foreign` (`new_package_id`),
  KEY `partner_package_update_requests_discount_id_foreign` (`discount_id`),
  CONSTRAINT `partner_package_update_requests_discount_id_foreign` FOREIGN KEY (`discount_id`) REFERENCES `partner_subscription_discounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `partner_package_update_requests_new_package_id_foreign` FOREIGN KEY (`new_package_id`) REFERENCES `partner_subscription_packages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `partner_package_update_requests_old_package_id_foreign` FOREIGN KEY (`old_package_id`) REFERENCES `partner_subscription_packages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `partner_package_update_requests_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=984589 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_pos_categories
-- ----------------------------
DROP TABLE IF EXISTS `partner_pos_categories`;
CREATE TABLE `partner_pos_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_migrated` tinyint(4) DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `partner_pos_categories_partner_id_category_id_unique` (`partner_id`,`category_id`),
  KEY `partner_pos_categories_category_id_foreign` (`category_id`),
  KEY `partner_pos_categories_is_migrated_index` (`is_migrated`),
  CONSTRAINT `partner_pos_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `pos_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `partner_pos_categories_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=372923 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_pos_customers
-- ----------------------------
DROP TABLE IF EXISTS `partner_pos_customers`;
CREATE TABLE `partner_pos_customers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `nick_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_supplier` tinyint(4) NOT NULL DEFAULT '0',
  `is_migrated` tinyint(4) DEFAULT NULL,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `due_date_reminder` datetime DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `partner_pos_customers_partner_id_customer_id_unique` (`partner_id`,`customer_id`) USING BTREE COMMENT 'Manually Create Index. Also apply a migration',
  KEY `partner_pos_customers_customer_id_foreign` (`customer_id`),
  KEY `partner_pos_customers_partner_id_foreign` (`partner_id`) USING BTREE,
  KEY `partner_pos_customers_is_supplier_index` (`is_supplier`),
  KEY `partner_pos_customers_is_migrated_index` (`is_migrated`),
  CONSTRAINT `partner_pos_customers_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `pos_customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `partner_pos_customers_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1158600 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_pos_service_batches
-- ----------------------------
DROP TABLE IF EXISTS `partner_pos_service_batches`;
CREATE TABLE `partner_pos_service_batches` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_pos_service_id` int(10) unsigned DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `from_account` varchar(191) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cost` double NOT NULL DEFAULT '0',
  `stock` double DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_pos_service_batches_partner_pos_service_id_foreign` (`partner_pos_service_id`),
  CONSTRAINT `partner_pos_service_batches_partner_pos_service_id_foreign` FOREIGN KEY (`partner_pos_service_id`) REFERENCES `partner_pos_services` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=832371 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_pos_service_discounts
-- ----------------------------
DROP TABLE IF EXISTS `partner_pos_service_discounts`;
CREATE TABLE `partner_pos_service_discounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_pos_service_id` int(10) unsigned NOT NULL,
  `amount` decimal(11,2) unsigned NOT NULL,
  `is_amount_percentage` tinyint(1) NOT NULL DEFAULT '0',
  `cap` decimal(8,2) DEFAULT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_pos_service_discounts_partner_pos_service_id_foreign` (`partner_pos_service_id`),
  CONSTRAINT `partner_pos_service_discounts_partner_pos_service_id_foreign` FOREIGN KEY (`partner_pos_service_id`) REFERENCES `partner_pos_services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=104767 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_pos_service_image_gallery
-- ----------------------------
DROP TABLE IF EXISTS `partner_pos_service_image_gallery`;
CREATE TABLE `partner_pos_service_image_gallery` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_pos_service_id` int(10) unsigned NOT NULL,
  `image_link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_pos_service_image_gallery_partner_pos_service_id_foreign` (`partner_pos_service_id`),
  CONSTRAINT `partner_pos_service_image_gallery_partner_pos_service_id_foreign` FOREIGN KEY (`partner_pos_service_id`) REFERENCES `partner_pos_services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=661528 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_pos_service_logs
-- ----------------------------
DROP TABLE IF EXISTS `partner_pos_service_logs`;
CREATE TABLE `partner_pos_service_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_pos_service_id` int(10) unsigned NOT NULL,
  `field_names` json NOT NULL,
  `old_value` json NOT NULL,
  `new_value` json NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_pos_service_logs_partner_pos_service_id_foreign` (`partner_pos_service_id`),
  CONSTRAINT `partner_pos_service_logs_partner_pos_service_id_foreign` FOREIGN KEY (`partner_pos_service_id`) REFERENCES `partner_pos_services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=762162 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_pos_services
-- ----------------------------
DROP TABLE IF EXISTS `partner_pos_services`;
CREATE TABLE `partner_pos_services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `pos_category_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `publication_status` tinyint(1) NOT NULL DEFAULT '1',
  `is_published_for_shop` tinyint(4) NOT NULL DEFAULT '0',
  `is_migrated` tinyint(4) DEFAULT NULL,
  `thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/pos/services/thumbs/default.jpg',
  `banner` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/pos/services/banners/default.jpg',
  `app_thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/pos/services/thumbs/default.jpg',
  `app_banner` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/pos/services/banners/default.jpg',
  `color` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shape` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `show_image` tinyint(4) NOT NULL DEFAULT '1',
  `description` text COLLATE utf8_unicode_ci,
  `cost` decimal(11,2) unsigned NOT NULL,
  `price` decimal(11,2) DEFAULT NULL,
  `wholesale_price` decimal(11,2) DEFAULT NULL,
  `vat_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `stock` decimal(11,2) DEFAULT NULL,
  `unit` enum('ft','sft','sq.m','kg','piece','km','litre','meter','dozen','dozon','inch','bosta','unit','set','carton','gauze') COLLATE utf8_unicode_ci DEFAULT NULL,
  `warranty` int(11) NOT NULL DEFAULT '0',
  `warranty_unit` enum('day','week','month','year') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'day',
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `weight` decimal(11,2) DEFAULT NULL,
  `weight_unit` enum('kg') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_pos_services_partner_id_foreign` (`partner_id`),
  KEY `partner_pos_services_pos_category_id_foreign` (`pos_category_id`),
  KEY `partner_pos_services_weight_unit_index` (`weight_unit`),
  KEY `partner_pos_services_is_migrated_index` (`is_migrated`),
  CONSTRAINT `partner_pos_services_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `partner_pos_services_pos_category_id_foreign` FOREIGN KEY (`pos_category_id`) REFERENCES `pos_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=814037 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_pos_settings
-- ----------------------------
DROP TABLE IF EXISTS `partner_pos_settings`;
CREATE TABLE `partner_pos_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `vat_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `printer_model` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `printer_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `auto_printing` tinyint(4) NOT NULL DEFAULT '0',
  `sms_invoice` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_pos_settings_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_pos_settings_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=593192 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_references
-- ----------------------------
DROP TABLE IF EXISTS `partner_references`;
CREATE TABLE `partner_references` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `company_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contact_person_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contact_person_mail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contact_person_mobile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verification_note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_references_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_references_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_referrals
-- ----------------------------
DROP TABLE IF EXISTS `partner_referrals`;
CREATE TABLE `partner_referrals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `referred_partner_id` int(11) DEFAULT NULL,
  `company_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N/A',
  `resource_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N/A',
  `resource_mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('pending','successful') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_referrals_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_referrals_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=134008 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_resource
-- ----------------------------
DROP TABLE IF EXISTS `partner_resource`;
CREATE TABLE `partner_resource` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned DEFAULT NULL,
  `resource_type` enum('Owner','Management','Admin','Operation','Finance','Handyman','Salesman') COLLATE utf8_unicode_ci DEFAULT NULL,
  `resource_id` int(10) unsigned NOT NULL,
  `join_date` date DEFAULT NULL,
  `designation` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `department` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verification_note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_resource_partner_id_foreign` (`partner_id`),
  KEY `partner_resource_resource_id_foreign` (`resource_id`),
  CONSTRAINT `partner_resource_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `partner_resource_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2379360 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_retailer
-- ----------------------------
DROP TABLE IF EXISTS `partner_retailer`;
CREATE TABLE `partner_retailer` (
  `partner_id` int(10) unsigned NOT NULL,
  `retailer_id` int(10) unsigned NOT NULL,
  KEY `partner_retailer_partner_id_foreign` (`partner_id`),
  KEY `partner_retailer_retailer_id_foreign` (`retailer_id`),
  CONSTRAINT `partner_retailer_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_service
-- ----------------------------
DROP TABLE IF EXISTS `partner_service`;
CREATE TABLE `partner_service` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `service_id` int(10) unsigned NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `min_quantity` decimal(8,2) NOT NULL DEFAULT '1.00',
  `options` text COLLATE utf8_unicode_ci,
  `base_quantity` mediumtext COLLATE utf8_unicode_ci,
  `base_prices` mediumtext COLLATE utf8_unicode_ci,
  `prices` text COLLATE utf8_unicode_ci,
  `min_prices` mediumtext COLLATE utf8_unicode_ci,
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `is_weekly_subscription_enable` tinyint(1) NOT NULL DEFAULT '0',
  `is_monthly_subscription_enable` tinyint(1) NOT NULL DEFAULT '0',
  `is_recurring` tinyint(1) NOT NULL DEFAULT '0',
  `discount` decimal(8,2) DEFAULT NULL,
  `discount_start_date` date DEFAULT NULL,
  `discount_end_date` date DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verification_note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `partner_service_partner_id_service_id_unique` (`partner_id`,`service_id`) USING BTREE,
  KEY `partner_service_partner_id_foreign` (`partner_id`),
  KEY `partner_service_service_id_foreign` (`service_id`),
  CONSTRAINT `partner_service_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `partner_service_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=811061 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_service_discounts
-- ----------------------------
DROP TABLE IF EXISTS `partner_service_discounts`;
CREATE TABLE `partner_service_discounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_service_id` int(10) unsigned NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `is_amount_percentage` tinyint(1) NOT NULL DEFAULT '0',
  `cap` decimal(8,2) DEFAULT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `sheba_contribution` decimal(5,2) NOT NULL,
  `partner_contribution` decimal(5,2) NOT NULL,
  `is_created_by_sheba` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_service_discounts_partner_service_id_foreign` (`partner_service_id`),
  CONSTRAINT `partner_service_discounts_partner_service_id_foreign` FOREIGN KEY (`partner_service_id`) REFERENCES `partner_service` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5944 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_service_prices_update
-- ----------------------------
DROP TABLE IF EXISTS `partner_service_prices_update`;
CREATE TABLE `partner_service_prices_update` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_service_id` int(10) unsigned NOT NULL,
  `old_options` text COLLATE utf8_unicode_ci,
  `new_options` text COLLATE utf8_unicode_ci,
  `old_prices` text COLLATE utf8_unicode_ci,
  `new_prices` text COLLATE utf8_unicode_ci,
  `old_min_prices` text COLLATE utf8_unicode_ci,
  `new_min_prices` text COLLATE utf8_unicode_ci,
  `old_base_quantity` mediumtext COLLATE utf8_unicode_ci,
  `new_base_quantity` mediumtext COLLATE utf8_unicode_ci,
  `old_base_prices` mediumtext COLLATE utf8_unicode_ci,
  `new_base_prices` mediumtext COLLATE utf8_unicode_ci,
  `status` enum('Pending','Approved','Rejected') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Pending',
  `note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_service_prices_update_partner_service_id_foreign` (`partner_service_id`),
  CONSTRAINT `partner_service_prices_update_partner_service_id_foreign` FOREIGN KEY (`partner_service_id`) REFERENCES `partner_service` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28092 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_service_surcharges
-- ----------------------------
DROP TABLE IF EXISTS `partner_service_surcharges`;
CREATE TABLE `partner_service_surcharges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_service_id` int(10) unsigned DEFAULT NULL,
  `amount` decimal(8,2) NOT NULL,
  `is_amount_percentage` tinyint(1) NOT NULL DEFAULT '1',
  `start_date` timestamp NOT NULL,
  `end_date` timestamp NOT NULL,
  `is_created_by_sheba` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_service_surcharges_partner_service_id_foreign` (`partner_service_id`),
  CONSTRAINT `partner_service_surcharges_partner_service_id_foreign` FOREIGN KEY (`partner_service_id`) REFERENCES `partner_service` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9365 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_status_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `partner_status_change_logs`;
CREATE TABLE `partner_status_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `from` enum('Verified','Unverified','Paused','Closed','Blacklisted','Waiting','Onboarded','Rejected','Inactive') COLLATE utf8_unicode_ci DEFAULT NULL,
  `to` enum('Verified','Unverified','Paused','Closed','Blacklisted','Waiting','Onboarded','Rejected','Inactive') COLLATE utf8_unicode_ci DEFAULT NULL,
  `reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_status_change_logs_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_status_change_logs_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=164606 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_strategic_partner
-- ----------------------------
DROP TABLE IF EXISTS `partner_strategic_partner`;
CREATE TABLE `partner_strategic_partner` (
  `partner_id` int(10) unsigned NOT NULL,
  `strategic_partner_id` int(10) unsigned NOT NULL,
  KEY `partner_strategic_partner_partner_id_foreign` (`partner_id`),
  KEY `partner_strategic_partner_strategic_partner_id_foreign` (`strategic_partner_id`),
  CONSTRAINT `partner_strategic_partner_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `partner_strategic_partner_strategic_partner_id_foreign` FOREIGN KEY (`strategic_partner_id`) REFERENCES `strategic_partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_subscription_discounts
-- ----------------------------
DROP TABLE IF EXISTS `partner_subscription_discounts`;
CREATE TABLE `partner_subscription_discounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `package_id` int(10) unsigned NOT NULL,
  `billing_type` varchar(160) COLLATE utf8_unicode_ci DEFAULT NULL,
  `amount` decimal(8,2) NOT NULL,
  `is_percentage` tinyint(1) NOT NULL DEFAULT '0',
  `applicable_billing_cycles` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_subscription_discounts_package_id_foreign` (`package_id`),
  CONSTRAINT `partner_subscription_discounts_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `partner_subscription_packages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_subscription_package_charges
-- ----------------------------
DROP TABLE IF EXISTS `partner_subscription_package_charges`;
CREATE TABLE `partner_subscription_package_charges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `action` enum('upgrade','downgrade','renewed') COLLATE utf8_unicode_ci NOT NULL,
  `activation_date` datetime NOT NULL,
  `package_from` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `package_to` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `package_price` decimal(8,2) NOT NULL DEFAULT '0.00',
  `cash_wallet_charge` decimal(8,2) NOT NULL DEFAULT '0.00',
  `bonus_wallet_charge` decimal(8,2) NOT NULL DEFAULT '0.00',
  `refunded` decimal(8,2) NOT NULL DEFAULT '0.00',
  `adjusted_amount_from_last_subscription` decimal(8,2) NOT NULL DEFAULT '0.00',
  `adjusted_days_from_last_subscription` int(11) NOT NULL DEFAULT '0',
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billing_date` datetime DEFAULT NULL,
  `advance_subscription_fee` tinyint(4) NOT NULL DEFAULT '0',
  `is_valid_advance_payment` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `advance_subscription_rules` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `partner_subscription_package_charges_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_subscription_package_charges_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12988495 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_subscription_package_logs
-- ----------------------------
DROP TABLE IF EXISTS `partner_subscription_package_logs`;
CREATE TABLE `partner_subscription_package_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `package_id` int(10) unsigned NOT NULL,
  `old_rules` longtext COLLATE utf8_unicode_ci NOT NULL,
  `new_rules` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_subscription_package_logs_package_id_foreign` (`package_id`),
  CONSTRAINT `partner_subscription_package_logs_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `partner_subscription_packages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_subscription_packages
-- ----------------------------
DROP TABLE IF EXISTS `partner_subscription_packages`;
CREATE TABLE `partner_subscription_packages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name_bn` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `show_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `show_name_bn` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tagline` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tagline_bn` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sort_order` int(11) NOT NULL,
  `status` enum('unpublished','published') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'published',
  `rules` longtext COLLATE utf8_unicode_ci NOT NULL,
  `activate_from` date DEFAULT NULL,
  `new_rules` longtext COLLATE utf8_unicode_ci,
  `usps` longtext COLLATE utf8_unicode_ci,
  `features` longtext COLLATE utf8_unicode_ci,
  `homepage_settings` json DEFAULT NULL,
  `homepage_last_updated_date_time` datetime DEFAULT NULL,
  `badge` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `badge_thumb` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_transactions
-- ----------------------------
DROP TABLE IF EXISTS `partner_transactions`;
CREATE TABLE `partner_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `type` text COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(11,2) unsigned NOT NULL,
  `balance` decimal(11,2) NOT NULL DEFAULT '0.00',
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `transaction_details` text COLLATE utf8_unicode_ci,
  `third_party_transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `partner_order_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_transactions_partner_id_foreign` (`partner_id`),
  KEY `partner_transactions_partner_order_id_foreign` (`partner_order_id`),
  KEY `partner_transactions_third_party_transaction_id_index` (`third_party_transaction_id`),
  CONSTRAINT `partner_transactions_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `partner_transactions_partner_order_id_foreign` FOREIGN KEY (`partner_order_id`) REFERENCES `partner_orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7851223 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_usages_history
-- ----------------------------
DROP TABLE IF EXISTS `partner_usages_history`;
CREATE TABLE `partner_usages_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_usages_history_partner_id_foreign` (`partner_id`),
  KEY `partner_usages_history_type_index` (`type`),
  CONSTRAINT `partner_usages_history_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14043239 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_wallet_setting_update_logs
-- ----------------------------
DROP TABLE IF EXISTS `partner_wallet_setting_update_logs`;
CREATE TABLE `partner_wallet_setting_update_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_wallet_setting_id` int(10) unsigned NOT NULL,
  `fields` text COLLATE utf8_unicode_ci NOT NULL,
  `old_values` text COLLATE utf8_unicode_ci NOT NULL,
  `new_values` text COLLATE utf8_unicode_ci NOT NULL,
  `log` text COLLATE utf8_unicode_ci,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic','business-portal') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_wallet_setting_id_foreign` (`partner_wallet_setting_id`),
  KEY `partner_wallet_setting_update_logs_portal_name_index` (`portal_name`),
  CONSTRAINT `partner_wallet_setting_id_foreign` FOREIGN KEY (`partner_wallet_setting_id`) REFERENCES `partner_wallet_settings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2116 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_wallet_settings
-- ----------------------------
DROP TABLE IF EXISTS `partner_wallet_settings`;
CREATE TABLE `partner_wallet_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `min_withdraw_amount` decimal(11,2) NOT NULL,
  `max_withdraw_amount` decimal(11,2) unsigned NOT NULL,
  `security_money` decimal(11,2) NOT NULL,
  `security_money_received` tinyint(1) NOT NULL DEFAULT '0',
  `min_wallet_threshold` decimal(11,2) NOT NULL,
  `reset_credit_limit_after` date DEFAULT NULL,
  `old_credit_limit` int(11) DEFAULT NULL,
  `pending_withdrawal_amount` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_wallet_settings_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_wallet_settings_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1181008 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_webstore_banner
-- ----------------------------
DROP TABLE IF EXISTS `partner_webstore_banner`;
CREATE TABLE `partner_webstore_banner` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `banner_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_published` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_webstore_banner_partner_id_foreign` (`partner_id`),
  KEY `partner_webstore_banner_banner_id_foreign` (`banner_id`),
  CONSTRAINT `partner_webstore_banner_banner_id_foreign` FOREIGN KEY (`banner_id`) REFERENCES `webstore_banners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `partner_webstore_banner_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=292606 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_webstore_domain_infos
-- ----------------------------
DROP TABLE IF EXISTS `partner_webstore_domain_infos`;
CREATE TABLE `partner_webstore_domain_infos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` bigint(20) unsigned NOT NULL,
  `domain_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name_servers` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `expired_on` datetime DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `partner_webstore_domain_infos_domain_name_unique` (`domain_name`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_withdrawal_requests
-- ----------------------------
DROP TABLE IF EXISTS `partner_withdrawal_requests`;
CREATE TABLE `partner_withdrawal_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `amount` decimal(11,2) unsigned NOT NULL,
  `status` enum('pending','approval_pending','approved','rejected','completed','failed','expired','cancelled') COLLATE utf8_unicode_ci DEFAULT 'pending',
  `payment_method` enum('bank','bkash') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'bank',
  `payment_info` text COLLATE utf8_unicode_ci,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_fail_reason` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `partner_withdrawal_requests_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_withdrawal_requests_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4104 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partner_working_hours
-- ----------------------------
DROP TABLE IF EXISTS `partner_working_hours`;
CREATE TABLE `partner_working_hours` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `day` enum('Saturday','Sunday','Monday','Tuesday','Wednesday','Thursday','Friday') COLLATE utf8_unicode_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_working_hours_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_working_hours_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8200614 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partners
-- ----------------------------
DROP TABLE IF EXISTS `partners`;
CREATE TABLE `partners` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sub_domain` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bkash_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bkash_account_type` enum('personal','agent','merchant') COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reset_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `simple` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `affiliation_id` int(10) unsigned DEFAULT NULL,
  `affiliation_cost` decimal(8,2) NOT NULL,
  `affiliate_id` int(10) unsigned DEFAULT NULL,
  `moderator_id` int(10) unsigned DEFAULT NULL,
  `moderation_status` enum('pending','approved','rejected') COLLATE utf8_unicode_ci DEFAULT NULL,
  `moderation_log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `geo_informations` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_active_for_b2b` tinyint(4) NOT NULL DEFAULT '1',
  `mobile_verified` tinyint(1) NOT NULL DEFAULT '0',
  `email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `prm_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verified_at` timestamp NULL DEFAULT NULL,
  `verification_note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `prm_id` int(11) DEFAULT NULL,
  `status` enum('Verified','Unverified','Paused','Closed','Blacklisted','Waiting','Onboarded','Rejected','Inactive') COLLATE utf8_unicode_ci DEFAULT 'Onboarded',
  `package_id` int(10) unsigned DEFAULT '1',
  `subscription_renewal_warning` tinyint(1) DEFAULT '1',
  `renewal_warning_days` tinyint(4) DEFAULT '7',
  `discount_id` int(10) unsigned DEFAULT NULL,
  `billing_type` varchar(160) COLLATE utf8_unicode_ci DEFAULT NULL,
  `requested_billing_type` varchar(160) COLLATE utf8_unicode_ci DEFAULT NULL,
  `billing_start_date` date DEFAULT NULL,
  `last_billed_date` date DEFAULT NULL,
  `last_billed_amount` double DEFAULT NULL,
  `next_billing_date` date DEFAULT NULL,
  `auto_billing_activated` tinyint(4) NOT NULL DEFAULT '1',
  `subscription_rules` longtext COLLATE utf8_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/partners/logos/default_v2.png',
  `logo_original` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/partners/logos/default_v2.png',
  `coordinates` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `xp` decimal(8,2) NOT NULL DEFAULT '0.00',
  `rating` int(11) NOT NULL DEFAULT '0',
  `badge` enum('silver','gold') COLLATE utf8_unicode_ci DEFAULT NULL,
  `top_badge_id` int(10) unsigned DEFAULT NULL,
  `level` enum('Starter','Intermediate','Advanced') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Starter',
  `type` enum('USP','NSP','ESP') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'USP',
  `can_topup` tinyint(1) DEFAULT '1',
  `wallet` decimal(11,2) NOT NULL DEFAULT '0.00',
  `reward_point` decimal(11,2) NOT NULL DEFAULT '0.00',
  `impression_limit` decimal(8,2) NOT NULL,
  `current_impression` decimal(8,2) NOT NULL,
  `order_limit` smallint(5) unsigned DEFAULT NULL,
  `registration_channel` enum('PM','Web','App','B2B') COLLATE utf8_unicode_ci DEFAULT NULL,
  `account_completion` decimal(5,2) NOT NULL DEFAULT '0.00',
  `bitly_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `business_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stock_price` double NOT NULL DEFAULT '0',
  `ownership_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `smanager_business_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `full_time_employee` int(10) unsigned NOT NULL DEFAULT '0',
  `part_time_employee` int(10) unsigned NOT NULL DEFAULT '0',
  `sales_information` json DEFAULT NULL,
  `business_additional_information` json DEFAULT NULL,
  `yearly_income` double(12,2) NOT NULL DEFAULT '0.00',
  `expense_account_id` int(11) DEFAULT NULL,
  `home_page_setting` json DEFAULT NULL,
  `home_page_setting_new` json DEFAULT NULL,
  `referrer_id` int(10) unsigned DEFAULT NULL,
  `referrer_income` double NOT NULL DEFAULT '0',
  `refer_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `refer_level` int(10) unsigned DEFAULT NULL,
  `qr_code_account_type` enum('bkash','rocket','nagad','mastercard') COLLATE utf8_unicode_ci DEFAULT NULL,
  `qr_code_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `has_webstore` tinyint(4) NOT NULL DEFAULT '0',
  `is_webstore_published` tinyint(4) NOT NULL DEFAULT '0',
  `delivery_charge` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `is_webstore_sms_active` tinyint(4) NOT NULL DEFAULT '1',
  `is_migration_completed` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `original_created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `partners_affiliation_id_unique` (`affiliation_id`),
  UNIQUE KEY `partners_expense_account_id_unique` (`expense_account_id`),
  UNIQUE KEY `partners_sub_domain_unique` (`sub_domain`),
  KEY `partners_top_badge_id_foreign` (`top_badge_id`),
  KEY `partners_package_id_foreign` (`package_id`),
  KEY `partners_discount_id_foreign` (`discount_id`),
  KEY `partners_affiliate_id_foreign` (`affiliate_id`),
  KEY `partners_moderator_id_foreign` (`moderator_id`),
  KEY `partners_status_index` (`status`) USING BTREE,
  KEY `partners_sub_domain_index` (`sub_domain`),
  KEY `partners_can_topup_index` (`can_topup`) USING BTREE,
  KEY `partners_original_created_at_index` (`original_created_at`) USING BTREE,
  CONSTRAINT `partners_affiliate_id_foreign` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `partners_affiliation_id_foreign` FOREIGN KEY (`affiliation_id`) REFERENCES `partner_affiliations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `partners_discount_id_foreign` FOREIGN KEY (`discount_id`) REFERENCES `partner_subscription_discounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `partners_moderator_id_foreign` FOREIGN KEY (`moderator_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `partners_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `partner_subscription_packages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `partners_top_badge_id_foreign` FOREIGN KEY (`top_badge_id`) REFERENCES `badges` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1181231 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partnership_slides
-- ----------------------------
DROP TABLE IF EXISTS `partnership_slides`;
CREATE TABLE `partnership_slides` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `banner` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `partnership_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partnership_slides_partnership_id_foreign` (`partnership_id`),
  CONSTRAINT `partnership_slides_partnership_id_foreign` FOREIGN KEY (`partnership_id`) REFERENCES `partnerships` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for partnerships
-- ----------------------------
DROP TABLE IF EXISTS `partnerships`;
CREATE TABLE `partnerships` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `short_description` longtext COLLATE utf8_unicode_ci,
  `owner_type` enum('App\\Models\\Service','App\\Models\\Category','App\\Models\\CategoryGroup') COLLATE utf8_unicode_ci NOT NULL,
  `owner_id` int(11) NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `partnerships_owner_type_owner_id_unique` (`owner_type`,`owner_id`),
  KEY `partnerships_owner_type_index` (`owner_type`),
  KEY `partnerships_owner_id_index` (`owner_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for password_resets
-- ----------------------------
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `password_resets_email_index` (`email`),
  KEY `password_resets_token_index` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for payables
-- ----------------------------
DROP TABLE IF EXISTS `payables`;
CREATE TABLE `payables` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('partner_order','wallet_recharge','subscription_order','gift_card_purchase','movie_ticket_purchase','transport_ticket_purchase','utility_order','payment_link','procurement','partner_bank_loan') CHARACTER SET utf8 DEFAULT NULL,
  `type_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_type` enum('App\\Models\\Customer','App\\Models\\Partner','App\\Models\\Affiliate','App\\Models\\Business') COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `amount` decimal(11,2) unsigned NOT NULL,
  `emi_month` int(11) DEFAULT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `completion_type` enum('order','advanced_order','wallet_recharge','gift_card_purchase','movie_ticket_purchase','transport_ticket_purchase','utility_order','payment_link','subscription_order','procurement','partner_bank_loan') CHARACTER SET utf8 DEFAULT NULL,
  `success_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fail_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payables_id_index` (`id`),
  KEY `payables_type_id_index` (`type_id`),
  KEY `payables_type_index` (`type`),
  KEY `payables_user_type_user_id_index` (`user_type`,`user_id`),
  KEY `payables_user_type_index` (`user_type`),
  KEY `payables_completion_type_index` (`completion_type`)
) ENGINE=InnoDB AUTO_INCREMENT=2804008 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for payment_client_authentications
-- ----------------------------
DROP TABLE IF EXISTS `payment_client_authentications`;
CREATE TABLE `payment_client_authentications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `client_secret` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `details` text COLLATE utf8_unicode_ci NOT NULL,
  `whitelisted_ips` text COLLATE utf8_unicode_ci NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  `status` enum('published','unpublished') COLLATE utf8_unicode_ci NOT NULL,
  `default_purpose` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `default_redirect_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_client_authentications_client_id_unique` (`client_id`),
  KEY `payment_client_authentications_partner_id_foreign` (`partner_id`),
  CONSTRAINT `payment_client_authentications_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=433 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for payment_details
-- ----------------------------
DROP TABLE IF EXISTS `payment_details`;
CREATE TABLE `payment_details` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` int(10) unsigned NOT NULL,
  `method` enum('wallet','partner_wallet','bonus','ssl','bkash','cbl','dbbl','bkash_old','ebl','ssl_donation','ok_wallet','port_wallet','bbl','nagad','bondhu_balance','upay') COLLATE utf8_unicode_ci DEFAULT NULL,
  `amount` decimal(11,2) NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_details_payment_id_foreign` (`payment_id`),
  KEY `payment_details_method_index` (`method`),
  CONSTRAINT `payment_details_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2160845 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for payment_gateways
-- ----------------------------
DROP TABLE IF EXISTS `payment_gateways`;
CREATE TABLE `payment_gateways` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payment_method` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `method_name` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `name_en` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name_bn` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `asset_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cash_in_charge` decimal(3,2) NOT NULL,
  `order` int(10) unsigned NOT NULL,
  `discount_message` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('Published','Unpublished') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Unpublished',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for payment_gateways_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `payment_gateways_change_logs`;
CREATE TABLE `payment_gateways_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `payment_gateway` int(10) unsigned NOT NULL,
  `from` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `to` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log` text COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `service_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_gateways_change_logs_payment_gateway_foreign` (`payment_gateway`),
  CONSTRAINT `payment_gateways_change_logs_payment_gateway_foreign` FOREIGN KEY (`payment_gateway`) REFERENCES `payment_gateways` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for payment_status_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `payment_status_change_logs`;
CREATE TABLE `payment_status_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` int(10) unsigned NOT NULL,
  `from` enum('initiated','initiation_failed','processed','validated','completed','validation_failed','failed','cancelled') COLLATE utf8_unicode_ci NOT NULL,
  `to` enum('initiated','initiation_failed','processed','validated','completed','validation_failed','failed','cancelled') COLLATE utf8_unicode_ci NOT NULL,
  `transaction_details` text COLLATE utf8_unicode_ci,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_status_change_logs_payment_id_foreign` (`payment_id`),
  CONSTRAINT `payment_status_change_logs_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13601633 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for payments
-- ----------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `payable_id` int(10) unsigned NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `gateway_transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gateway_account_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('initiated','initiation_failed','processed','validated','completed','validation_failed','failed','cancelled') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'initiated',
  `valid_till` datetime NOT NULL,
  `transaction_details` text COLLATE utf8_unicode_ci,
  `redirect_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `invoice_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `request_payload` text COLLATE utf8_unicode_ci,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_transaction_id_unique` (`transaction_id`),
  KEY `payments_payable_id_foreign` (`payable_id`),
  KEY `gateway_transaction_id_index` (`gateway_transaction_id`) USING BTREE,
  CONSTRAINT `payments_payable_id_foreign` FOREIGN KEY (`payable_id`) REFERENCES `payables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2160554 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for payroll_components
-- ----------------------------
DROP TABLE IF EXISTS `payroll_components`;
CREATE TABLE `payroll_components` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `payroll_setting_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `setting` json NOT NULL,
  `type` enum('gross','addition','deduction') COLLATE utf8_unicode_ci DEFAULT NULL,
  `target_type` enum('global','employee') COLLATE utf8_unicode_ci DEFAULT NULL,
  `target_id` int(10) unsigned DEFAULT NULL,
  `is_default` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `is_taxable` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_components_payroll_setting_id_foreign` (`payroll_setting_id`),
  CONSTRAINT `payroll_components_payroll_setting_id_foreign` FOREIGN KEY (`payroll_setting_id`) REFERENCES `payroll_settings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27218 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for payroll_payday_history
-- ----------------------------
DROP TABLE IF EXISTS `payroll_payday_history`;
CREATE TABLE `payroll_payday_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `pay_day_type` enum('last_working_day','fixed_date') COLLATE utf8_unicode_ci DEFAULT NULL,
  `pay_day` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `logs` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_payday_history_business_id_foreign` (`business_id`),
  CONSTRAINT `payroll_payday_history_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for payroll_settings
-- ----------------------------
DROP TABLE IF EXISTS `payroll_settings`;
CREATE TABLE `payroll_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `is_enable` tinyint(4) NOT NULL DEFAULT '0',
  `payment_schedule` enum('once_a_month') COLLATE utf8_unicode_ci NOT NULL,
  `pay_day_type` enum('last_working_day','fixed_date') COLLATE utf8_unicode_ci DEFAULT NULL,
  `pay_day` int(11) DEFAULT NULL,
  `last_pay_day` date DEFAULT NULL,
  `next_pay_day` date DEFAULT NULL,
  `show_tax_report_download_banner` int(11) NOT NULL DEFAULT '1',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_settings_business_id_foreign` (`business_id`),
  CONSTRAINT `payroll_settings_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3060 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for permission_group_permission
-- ----------------------------
DROP TABLE IF EXISTS `permission_group_permission`;
CREATE TABLE `permission_group_permission` (
  `permission_groups_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_groups_id`,`permission_id`),
  KEY `permission_group_permission_permission_id_foreign` (`permission_id`),
  CONSTRAINT `permission_group_permission_permission_groups_id_foreign` FOREIGN KEY (`permission_groups_id`) REFERENCES `permission_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permission_group_permission_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for permission_groups
-- ----------------------------
DROP TABLE IF EXISTS `permission_groups`;
CREATE TABLE `permission_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for permission_role
-- ----------------------------
DROP TABLE IF EXISTS `permission_role`;
CREATE TABLE `permission_role` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `permission_role_role_id_foreign` (`role_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for permission_role_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `permission_role_change_logs`;
CREATE TABLE `permission_role_change_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `attachable_by_type` enum('App\\Models\\User','Sheba\\Dal\\Role\\Model') COLLATE utf8_unicode_ci NOT NULL,
  `attachable_by_id` int(10) unsigned NOT NULL,
  `attachable_type` enum('Sheba\\Dal\\Role\\Model','Sheba\\Dal\\Permission\\Model','App\\Models\\User') COLLATE utf8_unicode_ci NOT NULL,
  `attachable_id` int(10) unsigned NOT NULL,
  `action` enum('attach','detach') COLLATE utf8_unicode_ci NOT NULL,
  `reason` longtext COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4029 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for permission_user
-- ----------------------------
DROP TABLE IF EXISTS `permission_user`;
CREATE TABLE `permission_user` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`permission_id`),
  KEY `permission_user_permission_id_foreign` (`permission_id`),
  CONSTRAINT `permission_user_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permission_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for permissions
-- ----------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(510) COLLATE utf8_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1226 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for pgw_store_accounts
-- ----------------------------
DROP TABLE IF EXISTS `pgw_store_accounts`;
CREATE TABLE `pgw_store_accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `user_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pgw_store_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `configuration` longtext COLLATE utf8_unicode_ci,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pgw_store_accounts_pgw_store_id_foreign` (`pgw_store_id`),
  CONSTRAINT `pgw_store_accounts_pgw_store_id_foreign` FOREIGN KEY (`pgw_store_id`) REFERENCES `pgw_stores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for pgw_stores
-- ----------------------------
DROP TABLE IF EXISTS `pgw_stores`;
CREATE TABLE `pgw_stores` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name_bn` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `asset` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `method_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_emi_enabled` tinyint(1) NOT NULL,
  `is_published_for_mef` tinyint(1) NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for pos_categories
-- ----------------------------
DROP TABLE IF EXISTS `pos_categories`;
CREATE TABLE `pos_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `thumb` varchar(255) COLLATE utf8_unicode_ci DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/pos/categories/thumbs/default.jpg',
  `banner` varchar(255) COLLATE utf8_unicode_ci DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/pos/categories/banners/default.jpg',
  `app_thumb` varchar(255) COLLATE utf8_unicode_ci DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/pos/categories/thumbs/default.jpg',
  `app_banner` varchar(255) COLLATE utf8_unicode_ci DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/pos/categories/banners/default.jpg',
  `publication_status` tinyint(1) NOT NULL DEFAULT '0',
  `is_published_for_sheba` tinyint(4) NOT NULL DEFAULT '1',
  `is_migrated` tinyint(4) DEFAULT NULL,
  `order` smallint(5) unsigned DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `icon_png` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pos_categories_parent_id_foreign` (`parent_id`),
  KEY `pos_categories_is_migrated_index` (`is_migrated`),
  CONSTRAINT `pos_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `pos_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26806 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for pos_customers
-- ----------------------------
DROP TABLE IF EXISTS `pos_customers`;
CREATE TABLE `pos_customers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pos_customers_profile_id_foreign` (`profile_id`),
  CONSTRAINT `pos_customers_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1039546 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for pos_order_discounts
-- ----------------------------
DROP TABLE IF EXISTS `pos_order_discounts`;
CREATE TABLE `pos_order_discounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pos_order_id` int(10) unsigned NOT NULL,
  `type` enum('order','service','voucher') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'order',
  `amount` decimal(11,2) NOT NULL,
  `original_amount` decimal(11,2) NOT NULL,
  `is_percentage` tinyint(1) NOT NULL DEFAULT '0',
  `cap` decimal(11,2) DEFAULT NULL,
  `sheba_contribution` decimal(5,2) NOT NULL DEFAULT '0.00',
  `partner_contribution` decimal(5,2) NOT NULL DEFAULT '0.00',
  `discount_id` int(10) unsigned DEFAULT NULL,
  `item_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pos_order_discounts_pos_order_id_foreign` (`pos_order_id`),
  KEY `pos_order_discounts_discount_id_foreign` (`discount_id`),
  KEY `pos_order_discounts_item_id_foreign` (`item_id`),
  CONSTRAINT `pos_order_discounts_discount_id_foreign` FOREIGN KEY (`discount_id`) REFERENCES `partner_pos_service_discounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pos_order_discounts_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `pos_order_items` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pos_order_discounts_pos_order_id_foreign` FOREIGN KEY (`pos_order_id`) REFERENCES `pos_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=185019 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for pos_order_items
-- ----------------------------
DROP TABLE IF EXISTS `pos_order_items`;
CREATE TABLE `pos_order_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pos_order_id` int(10) unsigned NOT NULL,
  `service_id` int(10) unsigned DEFAULT NULL,
  `service_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `quantity` decimal(10,2) unsigned DEFAULT '1.00',
  `unit_price` decimal(11,2) NOT NULL,
  `vat_percentage` decimal(5,2) DEFAULT '0.00',
  `warranty` int(11) NOT NULL DEFAULT '0',
  `warranty_unit` enum('day','week','month','year') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'day',
  `note` longtext COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pos_order_items_pos_order_id_foreign` (`pos_order_id`),
  KEY `pos_order_items_service_id_foreign` (`service_id`),
  CONSTRAINT `pos_order_items_pos_order_id_foreign` FOREIGN KEY (`pos_order_id`) REFERENCES `pos_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pos_order_items_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `partner_pos_services` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3875401 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for pos_order_logs
-- ----------------------------
DROP TABLE IF EXISTS `pos_order_logs`;
CREATE TABLE `pos_order_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pos_order_id` int(10) unsigned NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `details` longtext COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pos_order_logs_pos_order_id_foreign` (`pos_order_id`),
  CONSTRAINT `pos_order_logs_pos_order_id_foreign` FOREIGN KEY (`pos_order_id`) REFERENCES `pos_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16430 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for pos_order_payments
-- ----------------------------
DROP TABLE IF EXISTS `pos_order_payments`;
CREATE TABLE `pos_order_payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pos_order_id` int(10) unsigned NOT NULL,
  `amount` decimal(11,2) unsigned NOT NULL,
  `transaction_type` enum('Debit','Credit') COLLATE utf8_unicode_ci DEFAULT NULL,
  `method` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `method_details` json DEFAULT NULL,
  `emi_month` int(11) DEFAULT NULL,
  `interest` decimal(11,2) DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pos_order_payments_pos_order_id_foreign` (`pos_order_id`),
  CONSTRAINT `pos_order_payments_pos_order_id_foreign` FOREIGN KEY (`pos_order_id`) REFERENCES `pos_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2499099 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for pos_orders
-- ----------------------------
DROP TABLE IF EXISTS `pos_orders`;
CREATE TABLE `pos_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `previous_order_id` int(10) unsigned DEFAULT NULL,
  `partner_wise_order_id` int(10) unsigned NOT NULL,
  `partner_id` int(10) unsigned DEFAULT NULL,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `payment_status` enum('Paid','Due') COLLATE utf8_unicode_ci DEFAULT NULL,
  `emi_month` int(11) DEFAULT NULL,
  `bank_transaction_charge` decimal(8,2) DEFAULT NULL,
  `interest` decimal(8,2) DEFAULT NULL,
  `weight` double DEFAULT NULL,
  `delivery_charge` decimal(8,2) DEFAULT NULL,
  `delivery_vendor_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `delivery_request_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `delivery_thana` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `delivery_district` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `delivery_status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `voucher_id` int(10) unsigned DEFAULT NULL,
  `note` longtext COLLATE utf8_unicode_ci,
  `status` enum('Pending','Processing','Declined','Shipped','Completed','Cancelled') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Pending',
  `sales_channel` enum('pos','webstore') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pos',
  `invoice` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_migrated` tinyint(4) DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pos_orders_previous_order_id_foreign` (`previous_order_id`),
  KEY `pos_orders_customer_id_foreign` (`customer_id`),
  KEY `pos_orders_partner_id_foreign` (`partner_id`),
  KEY `pos_orders_voucher_id_foreign` (`voucher_id`),
  KEY `pos_orders_payment_status_index` (`payment_status`),
  KEY `pos_orders_is_migrated_index` (`is_migrated`),
  CONSTRAINT `pos_orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `pos_customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pos_orders_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pos_orders_previous_order_id_foreign` FOREIGN KEY (`previous_order_id`) REFERENCES `pos_orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pos_orders_voucher_id_foreign` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2517659 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for potential_customers
-- ----------------------------
DROP TABLE IF EXISTS `potential_customers`;
CREATE TABLE `potential_customers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `voucher_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Reference` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for procurement_invitations
-- ----------------------------
DROP TABLE IF EXISTS `procurement_invitations`;
CREATE TABLE `procurement_invitations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `procurement_id` int(10) unsigned NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `procurement_invitations_procurement_id_partner_id_unique` (`procurement_id`,`partner_id`),
  KEY `procurement_invitations_partner_id_foreign` (`partner_id`),
  CONSTRAINT `procurement_invitations_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `procurement_invitations_procurement_id_foreign` FOREIGN KEY (`procurement_id`) REFERENCES `procurements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=238 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for procurement_item_fields
-- ----------------------------
DROP TABLE IF EXISTS `procurement_item_fields`;
CREATE TABLE `procurement_item_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `procurement_item_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `short_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `long_description` longtext COLLATE utf8_unicode_ci,
  `input_type` enum('text','textarea','radio','checkbox','number','select') COLLATE utf8_unicode_ci DEFAULT NULL,
  `variables` longtext COLLATE utf8_unicode_ci NOT NULL,
  `result` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `procurement_item_fields_procurement_item_id_foreign` (`procurement_item_id`),
  CONSTRAINT `procurement_item_fields_procurement_item_id_foreign` FOREIGN KEY (`procurement_item_id`) REFERENCES `procurement_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=331 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for procurement_items
-- ----------------------------
DROP TABLE IF EXISTS `procurement_items`;
CREATE TABLE `procurement_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `procurement_id` int(10) unsigned NOT NULL,
  `type` enum('price_quotation','technical_evaluation','company_evaluation') COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `procurement_items_procurement_id_foreign` (`procurement_id`),
  CONSTRAINT `procurement_items_procurement_id_foreign` FOREIGN KEY (`procurement_id`) REFERENCES `procurements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=190 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for procurement_logs
-- ----------------------------
DROP TABLE IF EXISTS `procurement_logs`;
CREATE TABLE `procurement_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `procurement_id` int(10) unsigned NOT NULL,
  `field` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `from` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `to` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `procurement_logs_procurement_id_foreign` (`procurement_id`),
  CONSTRAINT `procurement_logs_procurement_id_foreign` FOREIGN KEY (`procurement_id`) REFERENCES `procurements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1442 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for procurement_payment_request_status_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `procurement_payment_request_status_change_logs`;
CREATE TABLE `procurement_payment_request_status_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `payment_request_id` int(10) unsigned NOT NULL,
  `from_status` enum('pending','approved','acknowledged','rejected','paid') COLLATE utf8_unicode_ci NOT NULL,
  `to_status` enum('pending','approved','acknowledged','rejected','paid') COLLATE utf8_unicode_ci NOT NULL,
  `log` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_status_change_logs_payment_request_id_foreign` (`payment_request_id`),
  KEY `procurement_payment_request_status_change_logs_from_status_index` (`from_status`),
  KEY `procurement_payment_request_status_change_logs_to_status_index` (`to_status`),
  CONSTRAINT `payment_status_change_logs_payment_request_id_foreign` FOREIGN KEY (`payment_request_id`) REFERENCES `procurement_payment_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=933 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for procurement_payment_requests
-- ----------------------------
DROP TABLE IF EXISTS `procurement_payment_requests`;
CREATE TABLE `procurement_payment_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `procurement_id` int(10) unsigned NOT NULL,
  `bid_id` int(10) unsigned NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `status` enum('pending','approved','acknowledged','rejected','paid') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `short_description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `note` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `procurement_payment_requests_procurement_id_foreign` (`procurement_id`),
  KEY `procurement_payment_requests_bid_id_foreign` (`bid_id`),
  KEY `procurement_payment_requests_status_index` (`status`),
  CONSTRAINT `procurement_payment_requests_bid_id_foreign` FOREIGN KEY (`bid_id`) REFERENCES `bids` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `procurement_payment_requests_procurement_id_foreign` FOREIGN KEY (`procurement_id`) REFERENCES `procurements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=956 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for procurement_payments
-- ----------------------------
DROP TABLE IF EXISTS `procurement_payments`;
CREATE TABLE `procurement_payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `procurement_id` int(10) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `transaction_type` enum('Debit','Credit') COLLATE utf8_unicode_ci NOT NULL,
  `method` enum('online','bkash','wallet','cbl','cod','cheque','deposit','beftn') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `transaction_detail` longtext COLLATE utf8_unicode_ci NOT NULL,
  `portal_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `procurement_payments_procurement_id_foreign` (`procurement_id`),
  CONSTRAINT `procurement_payments_procurement_id_foreign` FOREIGN KEY (`procurement_id`) REFERENCES `procurements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1737 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for procurement_questions
-- ----------------------------
DROP TABLE IF EXISTS `procurement_questions`;
CREATE TABLE `procurement_questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `procurement_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `short_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `long_description` longtext COLLATE utf8_unicode_ci,
  `input_type` enum('text','radio','number','select') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'text',
  `variables` longtext COLLATE utf8_unicode_ci NOT NULL,
  `result` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `procurement_questions_procurement_id_foreign` (`procurement_id`),
  CONSTRAINT `procurement_questions_procurement_id_foreign` FOREIGN KEY (`procurement_id`) REFERENCES `procurements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for procurement_status_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `procurement_status_change_logs`;
CREATE TABLE `procurement_status_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `procurement_id` int(10) unsigned NOT NULL,
  `from_status` enum('pending','approved','rejected','need_approval','accepted','started','served','cancelled') COLLATE utf8_unicode_ci NOT NULL,
  `to_status` enum('pending','approved','rejected','need_approval','accepted','started','served','cancelled') COLLATE utf8_unicode_ci NOT NULL,
  `log` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `procurement_status_change_logs_procurement_id_foreign` (`procurement_id`),
  KEY `procurement_status_change_logs_from_status_index` (`from_status`),
  KEY `procurement_status_change_logs_to_status_index` (`to_status`),
  CONSTRAINT `procurement_status_change_logs_procurement_id_foreign` FOREIGN KEY (`procurement_id`) REFERENCES `procurements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6324 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for procurements
-- ----------------------------
DROP TABLE IF EXISTS `procurements`;
CREATE TABLE `procurements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_request_id` int(10) unsigned DEFAULT NULL,
  `category_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `short_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `long_description` longtext COLLATE utf8_unicode_ci,
  `status` enum('pending','approved','rejected','need_approval','accepted','started','served','cancelled') COLLATE utf8_unicode_ci DEFAULT 'pending',
  `number_of_participants` int(11) NOT NULL,
  `type` enum('basic','advanced','product','service') COLLATE utf8_unicode_ci DEFAULT 'basic',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estimated_price` decimal(11,2) DEFAULT NULL,
  `sheba_collection` decimal(8,2) NOT NULL DEFAULT '0.00',
  `partner_collection` decimal(8,2) NOT NULL DEFAULT '0.00',
  `closed_at` datetime DEFAULT NULL,
  `closed_and_paid_at` datetime DEFAULT NULL,
  `payment_options` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estimated_date` date NOT NULL,
  `order_start_date` timestamp NULL DEFAULT NULL,
  `order_end_date` timestamp NULL DEFAULT NULL,
  `interview_date` timestamp NULL DEFAULT NULL,
  `last_date_of_submission` datetime NOT NULL,
  `procurement_start_date` timestamp NULL DEFAULT NULL,
  `procurement_end_date` timestamp NULL DEFAULT NULL,
  `owner_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `owner_id` int(11) NOT NULL,
  `is_published` tinyint(4) NOT NULL DEFAULT '1',
  `publication_status` enum('published','unpublished','draft') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'published',
  `published_at` datetime NOT NULL,
  `shared_to` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `work_order_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `procurements_purchase_request_id_foreign` (`purchase_request_id`),
  KEY `procurements_category_id_foreign` (`category_id`),
  CONSTRAINT `procurements_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `procurements_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4177 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for profile_bank_informations
-- ----------------------------
DROP TABLE IF EXISTS `profile_bank_informations`;
CREATE TABLE `profile_bank_informations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` int(10) unsigned NOT NULL,
  `bank_name` enum('city_bank','ab_bank','bank_asia','brac_bank','dhaka_bank','dutch_bangla_bank','eastern_bank','ific_bank','jamuna_bank','meghna_bank','shonali_bank','modhumoti_bank','mutual_trust_bank','national_bank','nrb_bank','nrb_commercial_bank','nrb_global_bank','one_bank','padma_bank','premier_bank','PRIME_BANK','pubali_bank','shimanto_bank','south_bangla_agriculture_and_commerce_bank','standard_bank','trust_bank','united_commercial_bank','uttara_bank','southeast_bank','community_bank_bangladesh','mercantile_bank','national_credit_and_commerce_bank','janata_bank','agrani_bank','rupali_bank','basic_bank','bangladesh_development_bank','bangladesh_krishi_bank','rajshahi_krishi_unnayan_bank','probashi_kallyan_bank','standard_chartered_bank','bank_al_falah','al_arafah_islami_bank','exim_bank','first_security_islami_bank','icb_islamic_bank','islami_bank_bangladesh','shahjalal_islami_bank','social_islami_bank','union_bank') COLLATE utf8_unicode_ci NOT NULL,
  `account_no` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `account_type` enum('savings','current') COLLATE utf8_unicode_ci DEFAULT NULL,
  `branch_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `routing_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profile_bank_informations_profile_id_foreign` (`profile_id`),
  CONSTRAINT `profile_bank_informations_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=232 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for profile_mobile_bank_informations
-- ----------------------------
DROP TABLE IF EXISTS `profile_mobile_bank_informations`;
CREATE TABLE `profile_mobile_bank_informations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` int(10) unsigned NOT NULL,
  `bank_name` enum('bKash','EasyCash','mCash','SureCash','Rocket','MyCash') COLLATE utf8_unicode_ci NOT NULL,
  `account_no` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profile_mobile_bank_informations_profile_id_foreign` (`profile_id`),
  CONSTRAINT `profile_mobile_bank_informations_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14261 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for profile_nid_submission_logs
-- ----------------------------
DROP TABLE IF EXISTS `profile_nid_submission_logs`;
CREATE TABLE `profile_nid_submission_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` int(10) unsigned NOT NULL,
  `nid_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `submitted_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nid_ocr_data` text COLLATE utf8_unicode_ci,
  `porichoy_request` text COLLATE utf8_unicode_ci,
  `porichy_data` text COLLATE utf8_unicode_ci,
  `user_agent` text COLLATE utf8_unicode_ci,
  `liveliness_complete` tinyint(1) NOT NULL DEFAULT '0',
  `log` text COLLATE utf8_unicode_ci,
  `business_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `feature_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `verification_status` enum('pending','approved','rejected','incomplete') COLLATE utf8_unicode_ci DEFAULT NULL,
  `rejection_reasons` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `force_verification_reason` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profile_nid_submission_logs_profile_id_foreign` (`profile_id`),
  CONSTRAINT `profile_nid_submission_logs_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=131423 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for profile_password_update_logs
-- ----------------------------
DROP TABLE IF EXISTS `profile_password_update_logs`;
CREATE TABLE `profile_password_update_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` int(10) unsigned NOT NULL,
  `old_password` text COLLATE utf8_unicode_ci,
  `new_password` text COLLATE utf8_unicode_ci NOT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `portal_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `device_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imei` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imsi` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `geo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `profile_password_update_logs_profile_id_foreign` (`profile_id`),
  CONSTRAINT `profile_password_update_logs_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=508229 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for profiles
-- ----------------------------
DROP TABLE IF EXISTS `profiles`;
CREATE TABLE `profiles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `driver_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bn_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `remember_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_blacklisted` tinyint(1) unsigned DEFAULT '0',
  `login_blocked_until` datetime DEFAULT NULL,
  `fb_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `google_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `apple_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile_verified` tinyint(1) NOT NULL DEFAULT '0',
  `email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `father_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mother_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `post_office` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `post_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nationality` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `permanent_address` longtext COLLATE utf8_unicode_ci,
  `bkash_agreement_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `occupation` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nid_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nid_issue_date` date DEFAULT NULL,
  `nid_verified` tinyint(1) DEFAULT NULL,
  `nid_verification_date` datetime DEFAULT NULL,
  `nid_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_nid_verification_request_date` datetime DEFAULT NULL,
  `nid_verification_request_count` int(11) NOT NULL DEFAULT '0',
  `nid_image_front` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nid_image_back` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `passport_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `passport_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tin_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tin_certificate` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gender` enum('Female','Male','Other') COLLATE utf8_unicode_ci DEFAULT NULL,
  `blood_group` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `dob` date DEFAULT NULL,
  `birth_place` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pro_pic` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/profiles/avatar/default.jpg',
  `total_asset_amount` decimal(11,2) NOT NULL,
  `monthly_living_cost` decimal(11,2) NOT NULL,
  `monthly_loan_installment_amount` decimal(11,2) NOT NULL,
  `utility_bill_attachment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nominee_id` int(10) unsigned DEFAULT NULL,
  `nominee_relation` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `grantor_id` int(10) unsigned DEFAULT NULL,
  `grantor_relation` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic','business-portal') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `profiles_mobile_unique` (`mobile`),
  UNIQUE KEY `profiles_email_unique` (`email`),
  UNIQUE KEY `profiles_fb_id_unique` (`fb_id`),
  UNIQUE KEY `profiles_apple_id_unique` (`apple_id`),
  KEY `profiles_nominee_id_foreign` (`nominee_id`),
  KEY `profiles_grantor_id_foreign` (`grantor_id`),
  KEY `profiles_driver_id_foreign` (`driver_id`),
  KEY `profiles_portal_name_index` (`portal_name`),
  KEY `profiles_nid_index` (`nid_no`) USING BTREE,
  CONSTRAINT `profiles_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `profiles_grantor_id_foreign` FOREIGN KEY (`grantor_id`) REFERENCES `profiles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `profiles_nominee_id_foreign` FOREIGN KEY (`nominee_id`) REFERENCES `profiles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2869409 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for promotions
-- ----------------------------
DROP TABLE IF EXISTS `promotions`;
CREATE TABLE `promotions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `voucher_id` int(10) unsigned NOT NULL,
  `is_valid` tinyint(1) NOT NULL DEFAULT '1',
  `valid_till` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `promotions_customer_id_foreign` (`customer_id`),
  KEY `promotions_voucher_id_foreign` (`voucher_id`),
  CONSTRAINT `promotions_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `promotions_voucher_id_foreign` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=197766 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for purchase_request_approvals
-- ----------------------------
DROP TABLE IF EXISTS `purchase_request_approvals`;
CREATE TABLE `purchase_request_approvals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_request_id` int(10) unsigned DEFAULT NULL,
  `member_id` int(10) unsigned DEFAULT NULL,
  `status` enum('pending','approved','rejected','need_approval') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `note` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_request_approvals_purchase_request_id_foreign` (`purchase_request_id`),
  KEY `purchase_request_approvals_member_id_foreign` (`member_id`),
  CONSTRAINT `purchase_request_approvals_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `purchase_request_approvals_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for purchase_request_item_fields
-- ----------------------------
DROP TABLE IF EXISTS `purchase_request_item_fields`;
CREATE TABLE `purchase_request_item_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_request_item_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `short_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `long_description` longtext COLLATE utf8_unicode_ci,
  `input_type` enum('text','radio','number','select') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'text',
  `variables` longtext COLLATE utf8_unicode_ci NOT NULL,
  `result` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_request_item_fields_purchase_request_item_id_foreign` (`purchase_request_item_id`),
  CONSTRAINT `purchase_request_item_fields_purchase_request_item_id_foreign` FOREIGN KEY (`purchase_request_item_id`) REFERENCES `purchase_request_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for purchase_request_items
-- ----------------------------
DROP TABLE IF EXISTS `purchase_request_items`;
CREATE TABLE `purchase_request_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_request_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_request_items_purchase_request_id_foreign` (`purchase_request_id`),
  CONSTRAINT `purchase_request_items_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for purchase_request_questions
-- ----------------------------
DROP TABLE IF EXISTS `purchase_request_questions`;
CREATE TABLE `purchase_request_questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_request_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `short_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `long_description` longtext COLLATE utf8_unicode_ci,
  `input_type` enum('text','radio','number','select') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'text',
  `variables` longtext COLLATE utf8_unicode_ci NOT NULL,
  `result` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_request_questions_purchase_request_id_foreign` (`purchase_request_id`),
  CONSTRAINT `purchase_request_questions_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for purchase_requests
-- ----------------------------
DROP TABLE IF EXISTS `purchase_requests`;
CREATE TABLE `purchase_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `form_template_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `short_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `long_description` longtext COLLATE utf8_unicode_ci,
  `status` enum('pending','approved','rejected','need_approval') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `type` enum('product','service') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'product',
  `estimated_price` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `estimated_date` date DEFAULT NULL,
  `business_id` int(10) unsigned DEFAULT NULL,
  `member_id` int(10) unsigned DEFAULT NULL,
  `rejection_note` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_requests_form_template_id_foreign` (`form_template_id`),
  KEY `purchase_requests_business_id_foreign` (`business_id`),
  KEY `purchase_requests_member_id_foreign` (`member_id`),
  CONSTRAINT `purchase_requests_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `purchase_requests_form_template_id_foreign` FOREIGN KEY (`form_template_id`) REFERENCES `form_templates` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `purchase_requests_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for push_notification_monitoring_item_receive_info
-- ----------------------------
DROP TABLE IF EXISTS `push_notification_monitoring_item_receive_info`;
CREATE TABLE `push_notification_monitoring_item_receive_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `push_notification_monitoring_item_id` int(10) unsigned NOT NULL,
  `message_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sent_time` timestamp NULL DEFAULT NULL,
  `receive_time` timestamp NULL DEFAULT NULL,
  `priority` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `original_priority` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `manufacturer` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `model` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=117496 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for push_notification_monitoring_items
-- ----------------------------
DROP TABLE IF EXISTS `push_notification_monitoring_items`;
CREATE TABLE `push_notification_monitoring_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `sent_payload` json DEFAULT NULL,
  `topic_message_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `push_notification_monitoring_items_partner_id_foreign` (`partner_id`),
  CONSTRAINT `push_notification_monitoring_items_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=83395 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for push_notifications
-- ----------------------------
DROP TABLE IF EXISTS `push_notifications`;
CREATE TABLE `push_notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `payload` longtext COLLATE utf8_unicode_ci NOT NULL,
  `target_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `target_id` int(10) unsigned DEFAULT NULL,
  `device_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `app_type` enum('customer_app_android','manager_app_android','resource_app_android','customer_app_ios','bondhu_app_android') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'customer_app_android',
  `number_of_receive` int(10) unsigned DEFAULT NULL,
  `number_of_success` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3317 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for push_subscriptions
-- ----------------------------
DROP TABLE IF EXISTS `push_subscriptions`;
CREATE TABLE `push_subscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subscriber_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subscriber_id` int(10) unsigned DEFAULT NULL,
  `device_type` enum('browser','android','ios','wp') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'browser',
  `device` text COLLATE utf8_unicode_ci,
  `is_subscribed` tinyint(1) NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `push_subscriber_index` (`subscriber_type`,`subscriber_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=22636 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for queue_failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS `queue_failed_jobs`;
CREATE TABLE `queue_failed_jobs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `connection` text COLLATE utf8_unicode_ci NOT NULL,
  `queue` text COLLATE utf8_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35058 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for queue_jobs
-- ----------------------------
DROP TABLE IF EXISTS `queue_jobs`;
CREATE TABLE `queue_jobs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `connection` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payload` text COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('on_queue','processing_started','successful','failed') COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `started_at` datetime NOT NULL,
  `completed_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for quotations
-- ----------------------------
DROP TABLE IF EXISTS `quotations`;
CREATE TABLE `quotations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `custom_order_id` int(10) unsigned NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  `proposal` longtext COLLATE utf8_unicode_ci,
  `attachment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `proposed_price` decimal(11,2) NOT NULL,
  `is_sent` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quotations_custom_order_id_foreign` (`custom_order_id`),
  KEY `quotations_partner_id_foreign` (`partner_id`),
  CONSTRAINT `quotations_custom_order_id_foreign` FOREIGN KEY (`custom_order_id`) REFERENCES `custom_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `quotations_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for rate_answer_rate_question
-- ----------------------------
DROP TABLE IF EXISTS `rate_answer_rate_question`;
CREATE TABLE `rate_answer_rate_question` (
  `rate_answer_id` int(10) unsigned NOT NULL,
  `rate_question_id` int(10) unsigned NOT NULL,
  UNIQUE KEY `rate_answer_rate_question_rate_answer_id_rate_question_id_unique` (`rate_answer_id`,`rate_question_id`),
  KEY `rate_answer_rate_question_rate_question_id_foreign` (`rate_question_id`),
  CONSTRAINT `rate_answer_rate_question_rate_answer_id_foreign` FOREIGN KEY (`rate_answer_id`) REFERENCES `rate_answers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `rate_answer_rate_question_rate_question_id_foreign` FOREIGN KEY (`rate_question_id`) REFERENCES `rate_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for rate_answers
-- ----------------------------
DROP TABLE IF EXISTS `rate_answers`;
CREATE TABLE `rate_answers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `answer` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `badge` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `asset` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for rate_question_rate
-- ----------------------------
DROP TABLE IF EXISTS `rate_question_rate`;
CREATE TABLE `rate_question_rate` (
  `rate_id` int(10) unsigned NOT NULL,
  `rate_question_id` int(10) unsigned NOT NULL,
  UNIQUE KEY `rate_question_rate_rate_id_rate_question_id_unique` (`rate_id`,`rate_question_id`),
  KEY `rate_question_rate_rate_question_id_foreign` (`rate_question_id`),
  CONSTRAINT `rate_question_rate_rate_id_foreign` FOREIGN KEY (`rate_id`) REFERENCES `rates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `rate_question_rate_rate_question_id_foreign` FOREIGN KEY (`rate_question_id`) REFERENCES `rate_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for rate_questions
-- ----------------------------
DROP TABLE IF EXISTS `rate_questions`;
CREATE TABLE `rate_questions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` enum('checkbox','date','datetime','email','number','radio','range','text','textarea') COLLATE utf8_unicode_ci NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for rates
-- ----------------------------
DROP TABLE IF EXISTS `rates`;
CREATE TABLE `rates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `value` tinyint(3) unsigned NOT NULL,
  `type` enum('review','customer_review','review_from_business') COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `icon_off` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `asset` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for redirect_urls
-- ----------------------------
DROP TABLE IF EXISTS `redirect_urls`;
CREATE TABLE `redirect_urls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `old_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `new_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `redirect_urls_old_url_index` (`old_url`)
) ENGINE=InnoDB AUTO_INCREMENT=154 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for repayment_with_bank_requests
-- ----------------------------
DROP TABLE IF EXISTS `repayment_with_bank_requests`;
CREATE TABLE `repayment_with_bank_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `loan_id` int(10) unsigned NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `bank_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `branch_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `receipt_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `receipt_image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8_unicode_ci NOT NULL,
  `reject_reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `repayment_with_bank_requests_loan_id_foreign` (`loan_id`),
  CONSTRAINT `repayment_with_bank_requests_loan_id_foreign` FOREIGN KEY (`loan_id`) REFERENCES `partner_bank_loans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for report_download_logs
-- ----------------------------
DROP TABLE IF EXISTS `report_download_logs`;
CREATE TABLE `report_download_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `report_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `request_details` text COLLATE utf8_unicode_ci,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic','business-portal') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=484 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for resource_employments
-- ----------------------------
DROP TABLE IF EXISTS `resource_employments`;
CREATE TABLE `resource_employments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resource_id` int(10) unsigned NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  `request` text COLLATE utf8_unicode_ci,
  `joined_at` datetime NOT NULL,
  `left_at` datetime NOT NULL,
  `worked_as` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `categories` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `jobs_served` smallint(5) unsigned NOT NULL,
  `got_complain` smallint(5) unsigned NOT NULL,
  `avg_rating` decimal(3,2) NOT NULL,
  `note` text COLLATE utf8_unicode_ci,
  `noc_doc` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `resource_employments_resource_id_foreign` (`resource_id`),
  KEY `resource_employments_partner_id_foreign` (`partner_id`),
  CONSTRAINT `resource_employments_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `resource_employments_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1304 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for resource_schedule_logs
-- ----------------------------
DROP TABLE IF EXISTS `resource_schedule_logs`;
CREATE TABLE `resource_schedule_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resource_schedule_id` int(10) unsigned NOT NULL,
  `old_time` time NOT NULL,
  `new_time` time NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `resource_schedule_logs_resource_schedule_id_foreign` (`resource_schedule_id`),
  CONSTRAINT `resource_schedule_logs_resource_schedule_id_foreign` FOREIGN KEY (`resource_schedule_id`) REFERENCES `resource_schedules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for resource_schedules
-- ----------------------------
DROP TABLE IF EXISTS `resource_schedules`;
CREATE TABLE `resource_schedules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resource_id` int(10) unsigned NOT NULL,
  `job_id` int(10) unsigned NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `notify_at` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `resource_schedules_resource_id_foreign` (`resource_id`),
  KEY `resource_schedules_job_id_foreign` (`job_id`),
  CONSTRAINT `resource_schedules_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `resource_schedules_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=383242 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for resource_status_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `resource_status_change_logs`;
CREATE TABLE `resource_status_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resource_id` int(10) unsigned NOT NULL,
  `from` enum('pending','verified','unverified','rejected') COLLATE utf8_unicode_ci NOT NULL,
  `to` enum('pending','verified','unverified','rejected') COLLATE utf8_unicode_ci NOT NULL,
  `reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `resource_status_change_logs_resource_id_foreign` (`resource_id`),
  CONSTRAINT `resource_status_change_logs_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=683356 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for resource_transactions
-- ----------------------------
DROP TABLE IF EXISTS `resource_transactions`;
CREATE TABLE `resource_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resource_id` int(10) unsigned NOT NULL,
  `type` text COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(11,2) NOT NULL,
  `balance` decimal(11,2) NOT NULL DEFAULT '0.00',
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `transaction_details` text COLLATE utf8_unicode_ci,
  `third_party_transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic','business-portal') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `job_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `resource_transactions_resource_id_foreign` (`resource_id`),
  KEY `resource_transactions_third_party_transaction_id_index` (`third_party_transaction_id`),
  CONSTRAINT `resource_transactions_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3551 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for resources
-- ----------------------------
DROP TABLE IF EXISTS `resources`;
CREATE TABLE `resources` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` int(10) unsigned NOT NULL,
  `name_bn` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `father_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mother_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `spouse_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nid_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nid_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `alternate_contact` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `remember_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_first_time` tinyint(4) NOT NULL DEFAULT '1',
  `education` text COLLATE utf8_unicode_ci,
  `profession` text COLLATE utf8_unicode_ci,
  `references` text COLLATE utf8_unicode_ci,
  `bank_account` text COLLATE utf8_unicode_ci,
  `mfs_account` text COLLATE utf8_unicode_ci,
  `basic_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `other_expertise` text COLLATE utf8_unicode_ci,
  `experience` text COLLATE utf8_unicode_ci,
  `is_trained` tinyint(1) NOT NULL DEFAULT '0',
  `present_income` mediumint(8) unsigned DEFAULT NULL,
  `ward_no` text COLLATE utf8_unicode_ci,
  `police_station` text COLLATE utf8_unicode_ci,
  `status` enum('pending','verified','unverified','rejected') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'unverified',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verified_at` timestamp NULL DEFAULT NULL,
  `verification_note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `verification_message_seen` tinyint(4) NOT NULL DEFAULT '0',
  `wallet` decimal(11,2) NOT NULL DEFAULT '0.00',
  `reward_point` decimal(8,2) NOT NULL DEFAULT '0.00',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `resources_profile_id_unique` (`profile_id`),
  UNIQUE KEY `resources_nid_no_unique` (`nid_no`),
  UNIQUE KEY `resources_remember_token_unique` (`remember_token`),
  CONSTRAINT `resources_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1190615 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for retailer_members
-- ----------------------------
DROP TABLE IF EXISTS `retailer_members`;
CREATE TABLE `retailer_members` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `retailer_id` int(10) unsigned NOT NULL,
  `profile_id` int(10) unsigned NOT NULL,
  `remember_token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `role` enum('admin','agent') COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `retailer_members_retailer_id_foreign` (`retailer_id`),
  KEY `retailer_members_profile_id_foreign` (`profile_id`),
  CONSTRAINT `retailer_members_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for retailers
-- ----------------------------
DROP TABLE IF EXISTS `retailers`;
CREATE TABLE `retailers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `strategic_partner_id` int(10) unsigned NOT NULL,
  `mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `retailers_strategic_partner_id_mobile_unique` (`strategic_partner_id`,`mobile`),
  CONSTRAINT `retailers_strategic_partner_id_foreign` FOREIGN KEY (`strategic_partner_id`) REFERENCES `strategic_partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=931 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for review_question_answer
-- ----------------------------
DROP TABLE IF EXISTS `review_question_answer`;
CREATE TABLE `review_question_answer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `review_type` enum('App\\Models\\Review','App\\Models\\CustomerReview') COLLATE utf8_unicode_ci NOT NULL,
  `review_id` int(10) unsigned NOT NULL,
  `rate_question_id` int(10) unsigned DEFAULT NULL,
  `rate_answer_id` int(10) unsigned DEFAULT NULL,
  `rate_answer_text` text COLLATE utf8_unicode_ci NOT NULL,
  `rate_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `review_question_answer_rate_question_id_foreign` (`rate_question_id`),
  KEY `review_question_answer_rate_answer_id_foreign` (`rate_answer_id`),
  KEY `review_question_answer_rate_id_foreign` (`rate_id`),
  KEY `review_question_answer_review_id_index` (`review_id`) USING BTREE,
  KEY `review_question_answer_review_type_index` (`review_type`),
  CONSTRAINT `review_question_answer_rate_answer_id_foreign` FOREIGN KEY (`rate_answer_id`) REFERENCES `rate_answers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `review_question_answer_rate_id_foreign` FOREIGN KEY (`rate_id`) REFERENCES `rates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `review_question_answer_rate_question_id_foreign` FOREIGN KEY (`rate_question_id`) REFERENCES `rate_questions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=93029 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for reviews
-- ----------------------------
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `job_id` int(10) unsigned NOT NULL,
  `review_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `review` text COLLATE utf8_unicode_ci,
  `rating` int(11) NOT NULL DEFAULT '0',
  `resource_id` int(10) unsigned DEFAULT NULL,
  `partner_id` int(10) unsigned DEFAULT NULL,
  `service_id` int(10) unsigned DEFAULT NULL,
  `category_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reviews_customer_id_foreign` (`customer_id`),
  KEY `reviews_job_id_foreign` (`job_id`),
  KEY `reviews_service_id_foreign` (`service_id`),
  KEY `reviews_resource_id_foreign` (`resource_id`),
  KEY `reviews_partner_id_foreign` (`partner_id`),
  KEY `reviews_category_id_foreign` (`category_id`),
  CONSTRAINT `reviews_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `reviews_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `reviews_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `reviews_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `reviews_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `reviews_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=211618 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for reward_actions
-- ----------------------------
DROP TABLE IF EXISTS `reward_actions`;
CREATE TABLE `reward_actions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `event_rules` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for reward_affiliates
-- ----------------------------
DROP TABLE IF EXISTS `reward_affiliates`;
CREATE TABLE `reward_affiliates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reward` int(10) unsigned NOT NULL,
  `affiliate` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_seen` tinyint(1) NOT NULL DEFAULT '0',
  `is_achieved` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `reward_affiliates_reward_foreign` (`reward`),
  KEY `reward_affiliates_affiliate_foreign` (`affiliate`),
  CONSTRAINT `reward_affiliates_affiliate_foreign` FOREIGN KEY (`affiliate`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `reward_affiliates_reward_foreign` FOREIGN KEY (`reward`) REFERENCES `rewards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for reward_campaign_logs
-- ----------------------------
DROP TABLE IF EXISTS `reward_campaign_logs`;
CREATE TABLE `reward_campaign_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reward_campaign_id` int(10) unsigned NOT NULL,
  `target_type` enum('partner','resource','customer','affiliate') COLLATE utf8_unicode_ci DEFAULT 'partner',
  `target_id` int(11) NOT NULL,
  `achieved` int(11) NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reward_campaign_logs_reward_campaign_id_foreign` (`reward_campaign_id`),
  KEY `reward_campaign_logs_target_type_index` (`target_type`),
  KEY `reward_campaign_logs_target_id_index` (`target_id`),
  CONSTRAINT `reward_campaign_logs_reward_campaign_id_foreign` FOREIGN KEY (`reward_campaign_id`) REFERENCES `reward_campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=246 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for reward_campaigns
-- ----------------------------
DROP TABLE IF EXISTS `reward_campaigns`;
CREATE TABLE `reward_campaigns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `events` longtext COLLATE utf8_unicode_ci NOT NULL,
  `timeline_type` enum('Onetime','Recurring') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Onetime',
  `timeline` longtext COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for reward_constraints
-- ----------------------------
DROP TABLE IF EXISTS `reward_constraints`;
CREATE TABLE `reward_constraints` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reward_id` int(10) unsigned NOT NULL,
  `constraint_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `constraint_id` int(11) NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reward_constraints_reward_id_foreign` (`reward_id`),
  CONSTRAINT `reward_constraints_reward_id_foreign` FOREIGN KEY (`reward_id`) REFERENCES `rewards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1353 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for reward_logs
-- ----------------------------
DROP TABLE IF EXISTS `reward_logs`;
CREATE TABLE `reward_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reward_id` int(10) unsigned DEFAULT NULL,
  `target_type` enum('App\\Models\\Partner','App\\Models\\Customer','App\\Models\\Resource','App\\Models\\Affiliate') COLLATE utf8_unicode_ci DEFAULT NULL,
  `target_id` int(10) unsigned NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reward_logs_reward_id_foreign` (`reward_id`),
  CONSTRAINT `reward_logs_reward_id_foreign` FOREIGN KEY (`reward_id`) REFERENCES `rewards` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1861174 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for reward_no_constraints
-- ----------------------------
DROP TABLE IF EXISTS `reward_no_constraints`;
CREATE TABLE `reward_no_constraints` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reward_id` int(10) unsigned NOT NULL,
  `constraint_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reward_no_constraints_reward_id_foreign` (`reward_id`),
  CONSTRAINT `reward_no_constraints_reward_id_foreign` FOREIGN KEY (`reward_id`) REFERENCES `rewards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for reward_orders
-- ----------------------------
DROP TABLE IF EXISTS `reward_orders`;
CREATE TABLE `reward_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reward_product_id` int(10) unsigned DEFAULT NULL,
  `order_creator_type` enum('App\\Models\\Partner','App\\Models\\Customer','App\\Models\\Resource') COLLATE utf8_unicode_ci DEFAULT 'App\\Models\\Partner',
  `order_creator_id` int(10) unsigned NOT NULL,
  `status` enum('Pending','Process','Served') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Pending',
  `reward_product_point` decimal(11,2) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reward_orders_reward_product_id_foreign` (`reward_product_id`),
  CONSTRAINT `reward_orders_reward_product_id_foreign` FOREIGN KEY (`reward_product_id`) REFERENCES `reward_products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=590 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for reward_point_logs
-- ----------------------------
DROP TABLE IF EXISTS `reward_point_logs`;
CREATE TABLE `reward_point_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `target_type` enum('App\\Models\\Partner','App\\Models\\Customer','App\\Models\\Resource') COLLATE utf8_unicode_ci DEFAULT 'App\\Models\\Partner',
  `target_id` int(10) unsigned NOT NULL,
  `transaction_type` enum('In','Out') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'In',
  `amount` decimal(11,2) NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7906 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for reward_products
-- ----------------------------
DROP TABLE IF EXISTS `reward_products`;
CREATE TABLE `reward_products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `publication_status` tinyint(1) NOT NULL DEFAULT '1',
  `thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/reward_product_images/thumbs/default.jpg',
  `banner` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/reward_product_images/banners/default.jpg',
  `point` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for reward_targets
-- ----------------------------
DROP TABLE IF EXISTS `reward_targets`;
CREATE TABLE `reward_targets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reward_id` int(10) unsigned NOT NULL,
  `target_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reward_targets_reward_id_foreign` (`reward_id`),
  CONSTRAINT `reward_targets_reward_id_foreign` FOREIGN KEY (`reward_id`) REFERENCES `rewards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for rewards
-- ----------------------------
DROP TABLE IF EXISTS `rewards`;
CREATE TABLE `rewards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `short_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `long_description` longtext COLLATE utf8_unicode_ci,
  `terms` longtext COLLATE utf8_unicode_ci,
  `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `target_type` enum('App\\Models\\Partner','App\\Models\\Customer','App\\Models\\Resource','App\\Models\\Affiliate') COLLATE utf8_unicode_ci DEFAULT NULL,
  `detail_type` enum('App\\Models\\RewardCampaign','App\\Models\\RewardAction') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'App\\Models\\RewardCampaign',
  `detail_id` int(10) unsigned NOT NULL,
  `type` enum('Cash','Point') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Cash',
  `amount` decimal(11,2) NOT NULL,
  `is_amount_percentage` tinyint(1) NOT NULL DEFAULT '0',
  `cap` decimal(8,2) DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `valid_till_date` timestamp NULL DEFAULT NULL,
  `valid_till_day` int(11) DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rewards_detail_type_detail_id_unique` (`detail_type`,`detail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for robi_topup_wallet_transactions
-- ----------------------------
DROP TABLE IF EXISTS `robi_topup_wallet_transactions`;
CREATE TABLE `robi_topup_wallet_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `affiliate_id` int(10) unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `log` text COLLATE utf8_unicode_ci NOT NULL,
  `transaction_details` text COLLATE utf8_unicode_ci,
  `amount` decimal(11,2) NOT NULL,
  `balance` decimal(11,2) NOT NULL DEFAULT '0.00',
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `robi_topup_wallet_transactions_affiliate_id_foreign` (`affiliate_id`),
  CONSTRAINT `robi_topup_wallet_transactions_affiliate_id_foreign` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for role_user
-- ----------------------------
DROP TABLE IF EXISTS `role_user`;
CREATE TABLE `role_user` (
  `role_id` bigint(20) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_user_role_id_foreign` (`role_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for sale_targets
-- ----------------------------
DROP TABLE IF EXISTS `sale_targets`;
CREATE TABLE `sale_targets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `month` smallint(6) NOT NULL,
  `year` mediumint(9) NOT NULL,
  `month_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `targets` text COLLATE utf8_unicode_ci NOT NULL,
  `achievements` text COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sale_targets_month_name_unique` (`month_name`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for salesman
-- ----------------------------
DROP TABLE IF EXISTS `salesman`;
CREATE TABLE `salesman` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `team` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `dialer_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=984719 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for schedule_slots
-- ----------------------------
DROP TABLE IF EXISTS `schedule_slots`;
CREATE TABLE `schedule_slots` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `start` time NOT NULL,
  `end` time NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for screen_setting_elements
-- ----------------------------
DROP TABLE IF EXISTS `screen_setting_elements`;
CREATE TABLE `screen_setting_elements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `item_id` int(11) NOT NULL,
  `with_children` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=566 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for screen_settings
-- ----------------------------
DROP TABLE IF EXISTS `screen_settings`;
CREATE TABLE `screen_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attributes` longtext COLLATE utf8_unicode_ci,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic') COLLATE utf8_unicode_ci NOT NULL,
  `platform` enum('android','ios','web','all') COLLATE utf8_unicode_ci DEFAULT 'all',
  `screen` enum('home','eshop') COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for service_discounts
-- ----------------------------
DROP TABLE IF EXISTS `service_discounts`;
CREATE TABLE `service_discounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `amount` decimal(11,2) NOT NULL,
  `is_percentage` tinyint(1) NOT NULL DEFAULT '0',
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `cap` decimal(11,2) DEFAULT NULL,
  `sheba_contribution` decimal(5,2) NOT NULL DEFAULT '0.00',
  `partner_contribution` decimal(5,2) NOT NULL DEFAULT '0.00',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=312 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for service_group_location
-- ----------------------------
DROP TABLE IF EXISTS `service_group_location`;
CREATE TABLE `service_group_location` (
  `service_group_id` int(10) unsigned NOT NULL,
  `location_id` int(10) unsigned NOT NULL,
  KEY `service_group_location_location_id_foreign` (`location_id`),
  KEY `service_group_location_service_group_id_foreign` (`service_group_id`),
  CONSTRAINT `service_group_location_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `service_group_location_service_group_id_foreign` FOREIGN KEY (`service_group_id`) REFERENCES `service_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for service_group_service
-- ----------------------------
DROP TABLE IF EXISTS `service_group_service`;
CREATE TABLE `service_group_service` (
  `service_group_id` int(10) unsigned NOT NULL,
  `service_id` int(10) unsigned NOT NULL,
  `order` tinyint(3) unsigned NOT NULL DEFAULT '0',
  KEY `service_group_service_service_group_id_foreign` (`service_group_id`),
  KEY `service_group_service_service_id_foreign` (`service_id`),
  CONSTRAINT `service_group_service_service_group_id_foreign` FOREIGN KEY (`service_group_id`) REFERENCES `service_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `service_group_service_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for service_groups
-- ----------------------------
DROP TABLE IF EXISTS `service_groups`;
CREATE TABLE `service_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `short_description` text COLLATE utf8_unicode_ci,
  `long_description` longtext COLLATE utf8_unicode_ci,
  `meta_description` text COLLATE utf8_unicode_ci,
  `thumb` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `banner` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `app_thumb` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `app_banner` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `icon_png` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_published_for_app` tinyint(4) NOT NULL DEFAULT '1',
  `is_published_for_web` tinyint(4) NOT NULL DEFAULT '1',
  `is_flash` tinyint(4) NOT NULL DEFAULT '0',
  `order` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=555 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for service_price_update
-- ----------------------------
DROP TABLE IF EXISTS `service_price_update`;
CREATE TABLE `service_price_update` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `location_service_id` int(10) unsigned NOT NULL,
  `type` enum('price','upsell_price') COLLATE utf8_unicode_ci NOT NULL,
  `old_value` json DEFAULT NULL,
  `new_value` json DEFAULT NULL,
  `log` longtext COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_price_update_location_service_id_foreign` (`location_service_id`),
  CONSTRAINT `service_price_update_location_service_id_foreign` FOREIGN KEY (`location_service_id`) REFERENCES `location_service` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3161 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for service_requests
-- ----------------------------
DROP TABLE IF EXISTS `service_requests`;
CREATE TABLE `service_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_mobile` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `category_id` int(10) unsigned DEFAULT NULL,
  `category_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `location_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `services` text COLLATE utf8_unicode_ci,
  `is_subscribed` tinyint(1) NOT NULL DEFAULT '1',
  `status` enum('pending','rejected','processing','notified','cancelled','lost','converted') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `order_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_requests_order_id_unique` (`order_id`),
  KEY `service_requests_customer_id_foreign` (`customer_id`),
  KEY `service_requests_category_id_foreign` (`category_id`),
  KEY `service_requests_location_id_foreign` (`location_id`),
  CONSTRAINT `service_requests_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `service_requests_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `service_requests_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `service_requests_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1569 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for service_service_discount
-- ----------------------------
DROP TABLE IF EXISTS `service_service_discount`;
CREATE TABLE `service_service_discount` (
  `service_id` int(10) unsigned NOT NULL,
  `service_discount_id` int(10) unsigned NOT NULL,
  KEY `service_service_discount_service_id_foreign` (`service_id`),
  KEY `service_service_discount_service_discount_id_foreign` (`service_discount_id`),
  CONSTRAINT `service_service_discount_service_discount_id_foreign` FOREIGN KEY (`service_discount_id`) REFERENCES `service_discounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `service_service_discount_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for service_subscription_discounts
-- ----------------------------
DROP TABLE IF EXISTS `service_subscription_discounts`;
CREATE TABLE `service_subscription_discounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_subscription_id` int(10) unsigned NOT NULL,
  `subscription_type` enum('monthly','weekly','yearly') COLLATE utf8_unicode_ci DEFAULT 'weekly',
  `sheba_contribution` decimal(5,2) NOT NULL DEFAULT '0.00',
  `partner_contribution` decimal(5,2) NOT NULL DEFAULT '0.00',
  `min_discount_qty` int(10) unsigned DEFAULT NULL,
  `discount_amount` decimal(8,2) DEFAULT NULL,
  `is_discount_amount_percentage` tinyint(1) NOT NULL DEFAULT '0',
  `cap` decimal(8,2) DEFAULT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_subscription_discounts_service_subscription_id_foreign` (`service_subscription_id`),
  CONSTRAINT `service_subscription_discounts_service_subscription_id_foreign` FOREIGN KEY (`service_subscription_id`) REFERENCES `service_subscriptions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for service_subscriptions
-- ----------------------------
DROP TABLE IF EXISTS `service_subscriptions`;
CREATE TABLE `service_subscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `short_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `faq` json DEFAULT NULL,
  `is_weekly` tinyint(1) NOT NULL DEFAULT '1',
  `is_monthly` tinyint(1) NOT NULL DEFAULT '0',
  `is_yearly` tinyint(4) NOT NULL DEFAULT '0',
  `min_weekly_qty` int(10) unsigned NOT NULL DEFAULT '0',
  `min_monthly_qty` int(10) unsigned NOT NULL DEFAULT '0',
  `min_yearly_qty` int(11) NOT NULL DEFAULT '0',
  `is_active` tinyint(4) NOT NULL DEFAULT '0',
  `is_published_for_business` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_subscriptions_service_id_unique` (`service_id`),
  CONSTRAINT `service_subscriptions_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for service_surcharges
-- ----------------------------
DROP TABLE IF EXISTS `service_surcharges`;
CREATE TABLE `service_surcharges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int(10) unsigned NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `is_amount_percentage` tinyint(1) NOT NULL DEFAULT '1',
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_surcharges_service_id_foreign` (`service_id`),
  CONSTRAINT `service_surcharges_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for service_units
-- ----------------------------
DROP TABLE IF EXISTS `service_units`;
CREATE TABLE `service_units` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for service_usp
-- ----------------------------
DROP TABLE IF EXISTS `service_usp`;
CREATE TABLE `service_usp` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int(10) unsigned NOT NULL,
  `usp_id` int(10) unsigned NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_usp_service_id_usp_id_unique` (`service_id`,`usp_id`),
  KEY `service_usp_usp_id_foreign` (`usp_id`),
  CONSTRAINT `service_usp_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `service_usp_usp_id_foreign` FOREIGN KEY (`usp_id`) REFERENCES `usps` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3041 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for services
-- ----------------------------
DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `bn_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `description_bn` longtext COLLATE utf8_unicode_ci,
  `short_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `google_product_category` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `facebook_product_category` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `catalog_price` double DEFAULT NULL,
  `unit` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `stock_left` int(11) DEFAULT NULL,
  `min_quantity` decimal(8,2) NOT NULL DEFAULT '1.00',
  `publication_status` tinyint(1) NOT NULL DEFAULT '0',
  `order` tinyint(3) unsigned DEFAULT NULL,
  `order_for_bondhu` smallint(5) unsigned DEFAULT NULL,
  `is_published_for_backend` tinyint(1) NOT NULL DEFAULT '0',
  `is_published_for_bondhu` tinyint(1) NOT NULL DEFAULT '0',
  `is_published_for_business` tinyint(1) NOT NULL DEFAULT '0',
  `is_published_for_b2b` tinyint(4) NOT NULL DEFAULT '0',
  `is_published_for_ddn` tinyint(4) NOT NULL DEFAULT '0',
  `is_min_price_applicable` tinyint(4) NOT NULL DEFAULT '0',
  `is_base_price_applicable` tinyint(4) NOT NULL DEFAULT '0',
  `is_surcharges_applicable` tinyint(1) NOT NULL DEFAULT '0',
  `is_inspection_service` tinyint(4) NOT NULL DEFAULT '0',
  `is_add_on` tinyint(4) NOT NULL DEFAULT '0',
  `recurring_possibility` tinyint(1) NOT NULL DEFAULT '0',
  `thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/services_images/thumbs/default.jpg',
  `app_thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `catalog_thumb` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `banner` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/services_images/banners/default.jpg',
  `app_banner` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `faqs` longtext COLLATE utf8_unicode_ci NOT NULL,
  `bn_faqs` longtext COLLATE utf8_unicode_ci,
  `variable_type` enum('Fixed','Options','Custom') COLLATE utf8_unicode_ci NOT NULL,
  `variables` longtext COLLATE utf8_unicode_ci NOT NULL,
  `options_content` json DEFAULT NULL,
  `structured_description` longtext COLLATE utf8_unicode_ci,
  `structured_description_bn` longtext COLLATE utf8_unicode_ci,
  `structured_contents` json DEFAULT NULL,
  `bn_structured_contents` json DEFAULT NULL,
  `terms_and_conditions` json DEFAULT NULL,
  `features` json DEFAULT NULL,
  `pricing_helper_text` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `services_category_id_foreign` (`category_id`),
  CONSTRAINT `services_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3923 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for services_backup
-- ----------------------------
DROP TABLE IF EXISTS `services_backup`;
CREATE TABLE `services_backup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `bn_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `short_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `unit` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `stock_left` int(11) DEFAULT NULL,
  `min_quantity` decimal(8,2) NOT NULL DEFAULT '1.00',
  `publication_status` tinyint(1) NOT NULL DEFAULT '0',
  `order` tinyint(3) unsigned DEFAULT NULL,
  `order_for_bondhu` smallint(5) unsigned DEFAULT NULL,
  `is_published_for_backend` tinyint(1) NOT NULL DEFAULT '0',
  `is_published_for_bondhu` tinyint(1) NOT NULL DEFAULT '0',
  `is_published_for_business` tinyint(1) NOT NULL DEFAULT '0',
  `is_published_for_b2b` tinyint(4) NOT NULL DEFAULT '0',
  `is_published_for_ddn` tinyint(4) NOT NULL DEFAULT '0',
  `is_min_price_applicable` tinyint(4) NOT NULL DEFAULT '0',
  `is_base_price_applicable` tinyint(4) NOT NULL DEFAULT '0',
  `is_surcharges_applicable` tinyint(1) NOT NULL DEFAULT '0',
  `recurring_possibility` tinyint(1) NOT NULL DEFAULT '0',
  `thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/services_images/thumbs/default.jpg',
  `app_thumb` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `banner` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/services_images/banners/default.jpg',
  `app_banner` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `faqs` longtext COLLATE utf8_unicode_ci NOT NULL,
  `bn_faqs` longtext COLLATE utf8_unicode_ci,
  `variable_type` enum('Fixed','Options','Custom') COLLATE utf8_unicode_ci NOT NULL,
  `variables` longtext COLLATE utf8_unicode_ci NOT NULL,
  `options_content` json DEFAULT NULL,
  `structured_description` longtext COLLATE utf8_unicode_ci,
  `structured_contents` json DEFAULT NULL,
  `terms_and_conditions` json DEFAULT NULL,
  `features` json DEFAULT NULL,
  `pricing_helper_text` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `services_category_id_foreign` (`category_id`),
  CONSTRAINT `services_backup_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2959 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for slider_portal
-- ----------------------------
DROP TABLE IF EXISTS `slider_portal`;
CREATE TABLE `slider_portal` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slider_id` int(10) unsigned NOT NULL,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic') COLLATE utf8_unicode_ci NOT NULL,
  `screen` enum('home','eshop','payment_link','pos','inventory','referral','due') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `slider_portal_slider_id_foreign` (`slider_id`),
  CONSTRAINT `slider_portal_slider_id_foreign` FOREIGN KEY (`slider_id`) REFERENCES `sliders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for slider_slide
-- ----------------------------
DROP TABLE IF EXISTS `slider_slide`;
CREATE TABLE `slider_slide` (
  `slider_id` int(10) unsigned NOT NULL,
  `slide_id` int(10) unsigned NOT NULL,
  `location_id` int(10) unsigned NOT NULL,
  `order` smallint(6) DEFAULT NULL,
  UNIQUE KEY `slider_slide_location_unique` (`slider_id`,`slide_id`,`location_id`),
  KEY `slider_slide_slide_id_foreign` (`slide_id`),
  KEY `slider_slide_location_id_foreign` (`location_id`),
  CONSTRAINT `slider_slide_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `slider_slide_slide_id_foreign` FOREIGN KEY (`slide_id`) REFERENCES `slides` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `slider_slide_slider_id_foreign` FOREIGN KEY (`slider_id`) REFERENCES `sliders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for sliders
-- ----------------------------
DROP TABLE IF EXISTS `sliders`;
CREATE TABLE `sliders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `attributes` longtext COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for slides
-- ----------------------------
DROP TABLE IF EXISTS `slides`;
CREATE TABLE `slides` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `image_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `small_image_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_video` tinyint(4) NOT NULL DEFAULT '0',
  `video_info` text COLLATE utf8_unicode_ci,
  `target_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `target_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `target_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=288 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for smanager_location_csv_logs
-- ----------------------------
DROP TABLE IF EXISTS `smanager_location_csv_logs`;
CREATE TABLE `smanager_location_csv_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for smanager_locations
-- ----------------------------
DROP TABLE IF EXISTS `smanager_locations`;
CREATE TABLE `smanager_locations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `division` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `district` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `thana` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `union` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `smanager_locations_partner_id_foreign` (`partner_id`),
  KEY `smanager_locations_id_index` (`id`),
  CONSTRAINT `smanager_locations_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=704382 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for sms_campaign_order_receivers
-- ----------------------------
DROP TABLE IF EXISTS `sms_campaign_order_receivers`;
CREATE TABLE `sms_campaign_order_receivers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sms_campaign_order_id` int(10) unsigned NOT NULL,
  `receiver_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `receiver_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('pending','delivered','successful','failed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `message_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sms_count` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sms_campaign_order_receivers_sms_campaign_order_id_foreign` (`sms_campaign_order_id`),
  CONSTRAINT `sms_campaign_order_receivers_sms_campaign_order_id_foreign` FOREIGN KEY (`sms_campaign_order_id`) REFERENCES `sms_campaign_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=365325 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for sms_campaign_orders
-- ----------------------------
DROP TABLE IF EXISTS `sms_campaign_orders`;
CREATE TABLE `sms_campaign_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `message` longtext COLLATE utf8_unicode_ci NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  `rate_per_sms` decimal(8,2) NOT NULL,
  `bulk_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sms_campaign_orders_partner_id_foreign` (`partner_id`),
  CONSTRAINT `sms_campaign_orders_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13265 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for sms_sending_logs
-- ----------------------------
DROP TABLE IF EXISTS `sms_sending_logs`;
CREATE TABLE `sms_sending_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sms_body` longtext COLLATE utf8_unicode_ci NOT NULL,
  `mobile_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sms_cost` double DEFAULT NULL,
  `feature_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `business_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sms_status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sms_template` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=640691 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for sms_templates
-- ----------------------------
DROP TABLE IF EXISTS `sms_templates`;
CREATE TABLE `sms_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `event_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `template` text COLLATE utf8_unicode_ci NOT NULL,
  `variables` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_on` tinyint(1) NOT NULL DEFAULT '0',
  `is_published_for_sheba` tinyint(1) NOT NULL DEFAULT '1',
  `is_published_for_partner` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sms_templates_event_name_unique` (`event_name`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for sticker_categories
-- ----------------------------
DROP TABLE IF EXISTS `sticker_categories`;
CREATE TABLE `sticker_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for stickers
-- ----------------------------
DROP TABLE IF EXISTS `stickers`;
CREATE TABLE `stickers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sticker_category_id` int(10) unsigned NOT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stickers_sticker_category_id_foreign` (`sticker_category_id`),
  CONSTRAINT `stickers_sticker_category_id_foreign` FOREIGN KEY (`sticker_category_id`) REFERENCES `sticker_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for strategic_partner_members
-- ----------------------------
DROP TABLE IF EXISTS `strategic_partner_members`;
CREATE TABLE `strategic_partner_members` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `strategic_partner_id` int(10) unsigned NOT NULL,
  `profile_id` int(10) unsigned NOT NULL,
  `remember_token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `role` enum('admin','agent') COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `strategic_partner_members_strategic_partner_id_foreign` (`strategic_partner_id`),
  KEY `strategic_partner_members_profile_id_foreign` (`profile_id`),
  CONSTRAINT `strategic_partner_members_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `strategic_partner_members_strategic_partner_id_foreign` FOREIGN KEY (`strategic_partner_id`) REFERENCES `strategic_partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for strategic_partners
-- ----------------------------
DROP TABLE IF EXISTS `strategic_partners`;
CREATE TABLE `strategic_partners` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for subscription_order_payments
-- ----------------------------
DROP TABLE IF EXISTS `subscription_order_payments`;
CREATE TABLE `subscription_order_payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subscription_order_id` int(10) unsigned NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `transaction_type` enum('credit','debit') COLLATE utf8_unicode_ci NOT NULL,
  `method` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `transaction_detail` longtext COLLATE utf8_unicode_ci NOT NULL,
  `portal_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscription_order_payments_subscription_order_id_foreign` (`subscription_order_id`),
  CONSTRAINT `subscription_order_payments_subscription_order_id_foreign` FOREIGN KEY (`subscription_order_id`) REFERENCES `subscription_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for subscription_order_requests
-- ----------------------------
DROP TABLE IF EXISTS `subscription_order_requests`;
CREATE TABLE `subscription_order_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subscription_order_id` int(10) unsigned NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  `status` enum('pending','accepted','declined','not_responded','missed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscription_order_requests_subscription_order_id_foreign` (`subscription_order_id`),
  KEY `subscription_order_requests_partner_id_foreign` (`partner_id`),
  CONSTRAINT `subscription_order_requests_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `subscription_order_requests_subscription_order_id_foreign` FOREIGN KEY (`subscription_order_id`) REFERENCES `subscription_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for subscription_orders
-- ----------------------------
DROP TABLE IF EXISTS `subscription_orders`;
CREATE TABLE `subscription_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `billing_cycle` enum('monthly','weekly','yearly') COLLATE utf8_unicode_ci DEFAULT 'weekly',
  `schedules` json NOT NULL,
  `status` enum('requested','declined','not_responded','accepted','completed','converted') COLLATE utf8_unicode_ci DEFAULT NULL,
  `delivery_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `delivery_mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `additional_info` longtext COLLATE utf8_unicode_ci,
  `customer_id` int(10) unsigned NOT NULL,
  `user_type` enum('App\\Models\\Affiliate','App\\Models\\Customer','App\\Models\\Partner','App\\Models\\Business','App\\Models\\Vendor') COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `location_id` int(10) unsigned NOT NULL,
  `delivery_address_id` int(10) unsigned NOT NULL,
  `sales_channel` enum('Call-Center','Web','App','App-iOS','Facebook','B2B','Store','Alternative','Affiliation','Othoba','Daraz','Pickaboo','E-Shop','Bondhu','Telesales') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'App',
  `partner_id` int(10) unsigned DEFAULT NULL,
  `partner_collection` decimal(11,2) NOT NULL,
  `sheba_collection` decimal(11,2) NOT NULL,
  `discount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `discount_percentage` decimal(5,2) DEFAULT NULL,
  `invoice` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payment_method` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `paid_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `category_id` int(10) unsigned NOT NULL,
  `service_details` json NOT NULL,
  `services` json DEFAULT NULL,
  `billing_cycle_start` timestamp NOT NULL,
  `billing_cycle_end` timestamp NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscription_orders_partner_id_foreign` (`partner_id`),
  KEY `subscription_orders_category_id_foreign` (`category_id`),
  KEY `subscription_orders_user_type_index` (`user_type`),
  KEY `subscription_orders_user_id_index` (`user_id`),
  CONSTRAINT `subscription_orders_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `subscription_orders_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=573 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for subscription_wise_payment_gateways
-- ----------------------------
DROP TABLE IF EXISTS `subscription_wise_payment_gateways`;
CREATE TABLE `subscription_wise_payment_gateways` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `package_id` int(10) unsigned NOT NULL,
  `gateway_charges` json NOT NULL,
  `topup_charges` json DEFAULT NULL,
  `expired` tinyint(1) NOT NULL DEFAULT '0',
  `updated_from` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `subscription_wise_payment_gateways_package_id_foreign` (`package_id`),
  CONSTRAINT `subscription_wise_payment_gateways_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `partner_subscription_packages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for supports
-- ----------------------------
DROP TABLE IF EXISTS `supports`;
CREATE TABLE `supports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `short_description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `long_description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('open','closed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'open',
  `is_satisfied` tinyint(1) DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supports_member_id_foreign` (`member_id`),
  CONSTRAINT `supports_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1262 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for survey
-- ----------------------------
DROP TABLE IF EXISTS `survey`;
CREATE TABLE `survey` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `user_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `result` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=215 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for system_updates
-- ----------------------------
DROP TABLE IF EXISTS `system_updates`;
CREATE TABLE `system_updates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `deployed_date` date NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `sprint_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version_number` int(11) DEFAULT NULL,
  `version_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for taggables
-- ----------------------------
DROP TABLE IF EXISTS `taggables`;
CREATE TABLE `taggables` (
  `tag_id` int(10) unsigned NOT NULL,
  `taggable_id` int(10) unsigned NOT NULL,
  `taggable_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  KEY `taggables_tag_id_foreign` (`tag_id`),
  CONSTRAINT `taggables_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for tags
-- ----------------------------
DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `taggable_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tags_taggable_type_index` (`taggable_type`)
) ENGINE=InnoDB AUTO_INCREMENT=4276 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for thanas
-- ----------------------------
DROP TABLE IF EXISTS `thanas`;
CREATE TABLE `thanas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `district_id` int(10) unsigned NOT NULL,
  `location_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `bn_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lat` decimal(9,7) NOT NULL DEFAULT '0.0000000',
  `lng` decimal(9,7) NOT NULL DEFAULT '0.0000000',
  PRIMARY KEY (`id`),
  KEY `thanas_district_id_foreign` (`district_id`),
  KEY `thanas_location_id_foreign` (`location_id`),
  CONSTRAINT `thanas_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `thanas_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=611 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for to_do_list_shared_users
-- ----------------------------
DROP TABLE IF EXISTS `to_do_list_shared_users`;
CREATE TABLE `to_do_list_shared_users` (
  `to_do_list_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  KEY `to_do_list_shared_users_to_do_list_id_foreign` (`to_do_list_id`),
  KEY `to_do_list_shared_users_user_id_foreign` (`user_id`),
  CONSTRAINT `to_do_list_shared_users_to_do_list_id_foreign` FOREIGN KEY (`to_do_list_id`) REFERENCES `to_do_lists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `to_do_list_shared_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for to_do_lists
-- ----------------------------
DROP TABLE IF EXISTS `to_do_lists`;
CREATE TABLE `to_do_lists` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `to_do_lists_user_id_foreign` (`user_id`),
  CONSTRAINT `to_do_lists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1396 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for to_do_settings
-- ----------------------------
DROP TABLE IF EXISTS `to_do_settings`;
CREATE TABLE `to_do_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `bg_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `to_do_settings_user_id_foreign` (`user_id`),
  CONSTRAINT `to_do_settings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for to_do_task_attachments
-- ----------------------------
DROP TABLE IF EXISTS `to_do_task_attachments`;
CREATE TABLE `to_do_task_attachments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `to_do_task_id` int(10) unsigned NOT NULL,
  `file_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file_link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `to_do_task_attachments_to_do_task_id_foreign` (`to_do_task_id`),
  CONSTRAINT `to_do_task_attachments_to_do_task_id_foreign` FOREIGN KEY (`to_do_task_id`) REFERENCES `to_do_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for to_do_tasks
-- ----------------------------
DROP TABLE IF EXISTS `to_do_tasks`;
CREATE TABLE `to_do_tasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `to_do_list_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `task` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `note` text COLLATE utf8_unicode_ci,
  `due_date` timestamp NULL DEFAULT NULL,
  `reminder_on` timestamp NULL DEFAULT NULL,
  `assignee_id` int(10) unsigned DEFAULT NULL,
  `is_starred` tinyint(1) NOT NULL DEFAULT '0',
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
  `completed_by` int(10) unsigned DEFAULT NULL,
  `completed_by_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `focused_to_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `focused_to_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `to_do_tasks_to_do_list_id_foreign` (`to_do_list_id`),
  KEY `to_do_tasks_parent_id_foreign` (`parent_id`),
  CONSTRAINT `to_do_tasks_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `to_do_tasks_to_do_list_id_foreign` FOREIGN KEY (`to_do_list_id`) REFERENCES `to_do_lists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=490 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for top_up_blocked_agent_logs
-- ----------------------------
DROP TABLE IF EXISTS `top_up_blocked_agent_logs`;
CREATE TABLE `top_up_blocked_agent_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `agent_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `agent_id` int(11) NOT NULL,
  `action` enum('block','unblock') COLLATE utf8_unicode_ci NOT NULL,
  `reason` enum('recurring_top_up') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=325 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for top_up_blocked_agents
-- ----------------------------
DROP TABLE IF EXISTS `top_up_blocked_agents`;
CREATE TABLE `top_up_blocked_agents` (
  `agent_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `agent_id` int(11) NOT NULL,
  `reason` enum('recurring_top_up') COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`agent_type`,`agent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for topup_blacklist_number_update_logs
-- ----------------------------
DROP TABLE IF EXISTS `topup_blacklist_number_update_logs`;
CREATE TABLE `topup_blacklist_number_update_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topup_blacklist_number_id` int(10) unsigned DEFAULT NULL,
  `old_data` text COLLATE utf8_unicode_ci,
  `new_data` text COLLATE utf8_unicode_ci,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic','business-portal') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `topup_bl_number_update_logs_topup_bl_number_id_foreign` (`topup_blacklist_number_id`),
  KEY `topup_blacklist_number_update_logs_portal_name_index` (`portal_name`),
  CONSTRAINT `topup_bl_number_update_logs_topup_bl_number_id_foreign` FOREIGN KEY (`topup_blacklist_number_id`) REFERENCES `topup_blacklist_numbers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for topup_blacklist_numbers
-- ----------------------------
DROP TABLE IF EXISTS `topup_blacklist_numbers`;
CREATE TABLE `topup_blacklist_numbers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `topup_blacklist_numbers_mobile_unique` (`mobile`),
  KEY `topup_blacklist_numbers_mobile_index` (`mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for topup_bulk_request_numbers
-- ----------------------------
DROP TABLE IF EXISTS `topup_bulk_request_numbers`;
CREATE TABLE `topup_bulk_request_numbers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `topup_bulk_request_id` int(11) NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=228003 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for topup_bulk_requests
-- ----------------------------
DROP TABLE IF EXISTS `topup_bulk_requests`;
CREATE TABLE `topup_bulk_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL,
  `agent_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('pending','completed','successful','failed') COLLATE utf8_unicode_ci NOT NULL,
  `file` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1427 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for topup_gateway_sms_receiver
-- ----------------------------
DROP TABLE IF EXISTS `topup_gateway_sms_receiver`;
CREATE TABLE `topup_gateway_sms_receiver` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topup_gateway_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `topup_gateway_sms_receiver_topup_gateway_id_foreign` (`topup_gateway_id`),
  KEY `topup_gateway_sms_receiver_user_id_foreign` (`user_id`),
  CONSTRAINT `topup_gateway_sms_receiver_topup_gateway_id_foreign` FOREIGN KEY (`topup_gateway_id`) REFERENCES `topup_gateways` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `topup_gateway_sms_receiver_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for topup_gateways
-- ----------------------------
DROP TABLE IF EXISTS `topup_gateways`;
CREATE TABLE `topup_gateways` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `threshold` decimal(11,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(11,2) NOT NULL DEFAULT '0.00',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for topup_order_status_logs
-- ----------------------------
DROP TABLE IF EXISTS `topup_order_status_logs`;
CREATE TABLE `topup_order_status_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topup_order_id` int(10) unsigned NOT NULL,
  `from` enum('Initiated','Attempted','Pending','Failed','Successful','System Error') COLLATE utf8_unicode_ci NOT NULL,
  `to` enum('Initiated','Attempted','Pending','Failed','Successful','System Error') COLLATE utf8_unicode_ci NOT NULL,
  `transaction_details` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `topup_order_status_logs_topup_order_id_foreign` (`topup_order_id`),
  CONSTRAINT `topup_order_status_logs_topup_order_id_foreign` FOREIGN KEY (`topup_order_id`) REFERENCES `topup_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21974912 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for topup_orders
-- ----------------------------
DROP TABLE IF EXISTS `topup_orders`;
CREATE TABLE `topup_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `agent_type` enum('App\\Models\\Partner','App\\Models\\Affiliate','App\\Models\\Customer','App\\Models\\Vendor','App\\Models\\Business') COLLATE utf8_unicode_ci DEFAULT NULL,
  `agent_id` int(10) unsigned NOT NULL,
  `payee_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payee_mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payee_mobile_type` enum('prepaid','postpaid') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'prepaid',
  `amount` decimal(11,2) unsigned NOT NULL,
  `sheba_commission` decimal(5,2) NOT NULL DEFAULT '0.00',
  `agent_commission` decimal(5,2) NOT NULL DEFAULT '0.00',
  `ambassador_commission` decimal(5,2) NOT NULL DEFAULT '0.00',
  `is_agent_debited` tinyint(1) NOT NULL DEFAULT '0',
  `vendor_id` int(10) unsigned DEFAULT NULL,
  `gateway` enum('ssl','robi','airtel','banglalink','paywell','bdrecharge','paystation') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ssl',
  `otf_sheba_commission` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `otf_agent_commission` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `otf_id` int(10) unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `failed_reason` enum('unsupported_operator','invalid_number','insufficient_balance','gateway_timeout','gateway_error','unknown') COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_robi_topup_wallet` tinyint(4) NOT NULL DEFAULT '0',
  `bulk_request_id` int(10) unsigned DEFAULT NULL,
  `transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `transaction_details` text COLLATE utf8_unicode_ci,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `long` decimal(10,7) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `topup_orders_vendor_id_foreign` (`vendor_id`),
  KEY `topup_orders_transaction_id_index` (`transaction_id`),
  KEY `topup_orders_gateway_index` (`gateway`) USING BTREE,
  KEY `topup_orders_created_at_index` (`created_at`) USING BTREE,
  KEY `topup_orders_agent_index` (`agent_type`,`agent_id`) USING BTREE,
  CONSTRAINT `topup_orders_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `topup_vendors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16554238 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for topup_otf_settings
-- ----------------------------
DROP TABLE IF EXISTS `topup_otf_settings`;
CREATE TABLE `topup_otf_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topup_vendor_id` int(10) unsigned NOT NULL,
  `applicable_gateways` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `agent_commission` decimal(5,2) NOT NULL DEFAULT '0.00',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `topup_otf_settings_topup_vendor_id_foreign` (`topup_vendor_id`),
  CONSTRAINT `topup_otf_settings_topup_vendor_id_foreign` FOREIGN KEY (`topup_vendor_id`) REFERENCES `topup_vendors` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for topup_recharge_history
-- ----------------------------
DROP TABLE IF EXISTS `topup_recharge_history`;
CREATE TABLE `topup_recharge_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int(10) unsigned DEFAULT NULL,
  `amount` decimal(11,2) unsigned NOT NULL,
  `recharge_date` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `topup_recharge_history_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `topup_recharge_history_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `topup_vendors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for topup_transaction_block_notification_receivers
-- ----------------------------
DROP TABLE IF EXISTS `topup_transaction_block_notification_receivers`;
CREATE TABLE `topup_transaction_block_notification_receivers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `topup_transaction_block_notification_receivers_user_id_unique` (`user_id`),
  CONSTRAINT `topup_transaction_block_notification_receivers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for topup_vendor_commissions
-- ----------------------------
DROP TABLE IF EXISTS `topup_vendor_commissions`;
CREATE TABLE `topup_vendor_commissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topup_vendor_id` int(10) unsigned NOT NULL,
  `agent_commission` decimal(5,2) NOT NULL DEFAULT '0.00',
  `ambassador_commission` decimal(5,2) NOT NULL DEFAULT '0.00',
  `type` enum('App\\Models\\Affiliate','App\\Models\\Customer','App\\Models\\Partner','App\\Models\\Vendor','App\\Models\\Business') COLLATE utf8_unicode_ci DEFAULT NULL,
  `type_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `topup_vendor_commissions_topup_vendor_id_foreign` (`topup_vendor_id`),
  CONSTRAINT `topup_vendor_commissions_topup_vendor_id_foreign` FOREIGN KEY (`topup_vendor_id`) REFERENCES `topup_vendors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=233 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for topup_vendor_otf
-- ----------------------------
DROP TABLE IF EXISTS `topup_vendor_otf`;
CREATE TABLE `topup_vendor_otf` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topup_vendor_id` int(10) unsigned NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  `name_en` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name_bn` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `type` enum('Internet','Minutes','Call_Rate','Bundle') COLLATE utf8_unicode_ci DEFAULT NULL,
  `sim_type` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cashback_amount` decimal(5,2) NOT NULL DEFAULT '0.00',
  `status` enum('Active','Deactive') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Deactive',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `topup_vendor_otf_topup_vendor_id_foreign` (`topup_vendor_id`),
  CONSTRAINT `topup_vendor_otf_topup_vendor_id_foreign` FOREIGN KEY (`topup_vendor_id`) REFERENCES `topup_vendors` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for topup_vendor_otf_data_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `topup_vendor_otf_data_change_logs`;
CREATE TABLE `topup_vendor_otf_data_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topup_vendor_id` int(10) unsigned NOT NULL,
  `gateway` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `from` text COLLATE utf8_unicode_ci,
  `to` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=562 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for topup_vendor_otf_status_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `topup_vendor_otf_status_change_logs`;
CREATE TABLE `topup_vendor_otf_status_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `otf_id` int(10) unsigned NOT NULL,
  `from_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `to_status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `log` text COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `topup_vendor_otf_status_change_logs_otf_id_foreign` (`otf_id`),
  CONSTRAINT `topup_vendor_otf_status_change_logs_otf_id_foreign` FOREIGN KEY (`otf_id`) REFERENCES `topup_vendor_otf` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1120 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for topup_vendors
-- ----------------------------
DROP TABLE IF EXISTS `topup_vendors`;
CREATE TABLE `topup_vendors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(11,2) DEFAULT '0.00',
  `gateway` enum('ssl','robi','airtel','banglalink','paywell','bdrecharge','paystation') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ssl',
  `sheba_commission` decimal(5,2) NOT NULL DEFAULT '0.00',
  `waiting_time` int(10) unsigned NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for trade_fair
-- ----------------------------
DROP TABLE IF EXISTS `trade_fair`;
CREATE TABLE `trade_fair` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stall_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `discount` decimal(11,2) unsigned NOT NULL DEFAULT '0.00',
  `is_published` tinyint(4) NOT NULL DEFAULT '1',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trade_fair_partner_id_foreign` (`partner_id`),
  CONSTRAINT `trade_fair_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for training_videos
-- ----------------------------
DROP TABLE IF EXISTS `training_videos`;
CREATE TABLE `training_videos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `screen` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `video_link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `video_info` longtext COLLATE utf8_unicode_ci,
  `banner` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title_bn` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description_bn` text COLLATE utf8_unicode_ci,
  `publication_status` tinyint(4) NOT NULL DEFAULT '1',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for transport_routes
-- ----------------------------
DROP TABLE IF EXISTS `transport_routes`;
CREATE TABLE `transport_routes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `is_published` int(11) NOT NULL DEFAULT '0',
  `stoppage_points` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transport_routes_business_id_foreign` (`business_id`),
  CONSTRAINT `transport_routes_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for transport_ticket_orders
-- ----------------------------
DROP TABLE IF EXISTS `transport_ticket_orders`;
CREATE TABLE `transport_ticket_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `agent_type` enum('App\\Models\\Partner','App\\Models\\Affiliate','App\\Models\\Customer','App\\Models\\Vendor','App\\Models\\Business') COLLATE utf8_unicode_ci DEFAULT NULL,
  `agent_id` int(10) unsigned NOT NULL,
  `reserver_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reserver_mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `reserver_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vendor_id` int(10) unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(8,2) unsigned NOT NULL,
  `discount` decimal(8,2) unsigned NOT NULL,
  `discount_percent` decimal(5,2) unsigned NOT NULL,
  `sheba_contribution` decimal(5,2) unsigned NOT NULL,
  `vendor_contribution` decimal(5,2) unsigned NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `journey_date` date NOT NULL,
  `departure_time` time NOT NULL,
  `arrival_time` time DEFAULT NULL,
  `departure_station_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `arrival_station_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `reservation_details` text COLLATE utf8_unicode_ci,
  `voucher_id` int(10) unsigned DEFAULT NULL,
  `sheba_amount` decimal(5,2) unsigned NOT NULL,
  `agent_amount` decimal(5,2) unsigned NOT NULL,
  `ambassador_amount` decimal(5,2) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transport_ticket_orders_vendor_id_foreign` (`vendor_id`),
  KEY `transport_ticket_orders_voucher_id_foreign` (`voucher_id`),
  CONSTRAINT `transport_ticket_orders_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `transport_ticket_vendors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `transport_ticket_orders_voucher_id_foreign` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2904 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for transport_ticket_recharge_history
-- ----------------------------
DROP TABLE IF EXISTS `transport_ticket_recharge_history`;
CREATE TABLE `transport_ticket_recharge_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int(10) unsigned DEFAULT NULL,
  `amount` decimal(11,2) unsigned NOT NULL,
  `recharge_date` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transport_ticket_recharge_history_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `transport_ticket_recharge_history_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `transport_ticket_vendors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for transport_ticket_vendor_commissions
-- ----------------------------
DROP TABLE IF EXISTS `transport_ticket_vendor_commissions`;
CREATE TABLE `transport_ticket_vendor_commissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int(10) unsigned NOT NULL,
  `agent_amount` decimal(5,2) NOT NULL DEFAULT '0.00',
  `ambassador_amount` decimal(5,2) NOT NULL DEFAULT '0.00',
  `type` enum('App\\Models\\Partner','App\\Models\\Affiliate','App\\Models\\Customer','App\\Models\\Vendor','App\\Models\\Business') COLLATE utf8_unicode_ci DEFAULT NULL,
  `type_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transport_ticket_vendor_commissions_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `transport_ticket_vendor_commissions_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `transport_ticket_vendors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for transport_ticket_vendors
-- ----------------------------
DROP TABLE IF EXISTS `transport_ticket_vendors`;
CREATE TABLE `transport_ticket_vendors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sheba_amount` decimal(5,2) NOT NULL DEFAULT '0.00',
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `wallet_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for treats
-- ----------------------------
DROP TABLE IF EXISTS `treats`;
CREATE TABLE `treats` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `treat` varchar(255) DEFAULT NULL,
  `msg` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for trip_request_approval_flow_approvers
-- ----------------------------
DROP TABLE IF EXISTS `trip_request_approval_flow_approvers`;
CREATE TABLE `trip_request_approval_flow_approvers` (
  `approval_flow_id` int(10) unsigned DEFAULT NULL,
  `business_member_id` int(10) unsigned DEFAULT NULL,
  KEY `trip_request_approval_flow_id_foreign` (`approval_flow_id`),
  KEY `trip_request_approval_flow_approvers_business_member_id_foreign` (`business_member_id`),
  CONSTRAINT `trip_request_approval_flow_approvers_business_member_id_foreign` FOREIGN KEY (`business_member_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `trip_request_approval_flow_id_foreign` FOREIGN KEY (`approval_flow_id`) REFERENCES `trip_request_approval_flows` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for trip_request_approval_flows
-- ----------------------------
DROP TABLE IF EXISTS `trip_request_approval_flows`;
CREATE TABLE `trip_request_approval_flows` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `business_department_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trip_request_approval_flows_business_department_id_unique` (`business_department_id`),
  CONSTRAINT `trip_request_approval_flows_business_department_id_foreign` FOREIGN KEY (`business_department_id`) REFERENCES `business_departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for trip_request_approvals
-- ----------------------------
DROP TABLE IF EXISTS `trip_request_approvals`;
CREATE TABLE `trip_request_approvals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_trip_request_id` int(10) unsigned DEFAULT NULL,
  `business_member_id` int(10) unsigned DEFAULT NULL,
  `status` enum('pending','accepted','rejected') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trip_request_approvals_business_trip_request_id_foreign` (`business_trip_request_id`),
  KEY `trip_request_approvals_business_member_id_foreign` (`business_member_id`),
  CONSTRAINT `trip_request_approvals_business_member_id_foreign` FOREIGN KEY (`business_member_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `trip_request_approvals_business_trip_request_id_foreign` FOREIGN KEY (`business_trip_request_id`) REFERENCES `business_trip_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for unfollowed_notifications
-- ----------------------------
DROP TABLE IF EXISTS `unfollowed_notifications`;
CREATE TABLE `unfollowed_notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `event_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `event_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `unfollowed_notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `unfollowed_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for universal_slugs
-- ----------------------------
DROP TABLE IF EXISTS `universal_slugs`;
CREATE TABLE `universal_slugs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sluggable_type` enum('master_category','secondary_category','service') COLLATE utf8_unicode_ci NOT NULL,
  `sluggable_id` int(11) NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `universal_slugs_slug_unique` (`slug`),
  KEY `universal_slugs_sluggable_type_index` (`sluggable_type`),
  KEY `universal_slugs_sluggable_id_index` (`sluggable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1937 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for unpaid_leave_policy_history
-- ----------------------------
DROP TABLE IF EXISTS `unpaid_leave_policy_history`;
CREATE TABLE `unpaid_leave_policy_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `is_enable` tinyint(4) NOT NULL DEFAULT '0',
  `settings` json NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `logs` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `unpaid_leave_policy_history_business_id_foreign` (`business_id`),
  CONSTRAINT `unpaid_leave_policy_history_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for upazilas
-- ----------------------------
DROP TABLE IF EXISTS `upazilas`;
CREATE TABLE `upazilas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `district_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `bn_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lat` decimal(9,7) NOT NULL DEFAULT '0.0000000',
  `lng` decimal(9,7) NOT NULL DEFAULT '0.0000000',
  PRIMARY KEY (`id`),
  KEY `upazilas_district_id_foreign` (`district_id`),
  CONSTRAINT `upazilas_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=492 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for updates
-- ----------------------------
DROP TABLE IF EXISTS `updates`;
CREATE TABLE `updates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `message` longtext COLLATE utf8_unicode_ci,
  `image_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `video_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `target_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `publication_status` tinyint(1) NOT NULL DEFAULT '0',
  `app_type` enum('customer_app_android','manager_app_android','resource_app_android','customer_app_ios','bondhu_app_android') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'customer_app_android',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for user_login_logs
-- ----------------------------
DROP TABLE IF EXISTS `user_login_logs`;
CREATE TABLE `user_login_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `session_start_event` enum('login','unlock') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'login',
  `session_start_time` datetime NOT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `browser` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `platform` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `session_end_event` enum('logout','lock') COLLATE utf8_unicode_ci DEFAULT NULL,
  `session_end_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_login_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `user_login_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=900054 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for user_migration_status_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `user_migration_status_change_logs`;
CREATE TABLE `user_migration_status_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_migration_id` int(10) unsigned NOT NULL,
  `from` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `to` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_migration_status_change_logs_user_migration_id_foreign` (`user_migration_id`),
  CONSTRAINT `user_migration_status_change_logs_user_migration_id_foreign` FOREIGN KEY (`user_migration_id`) REFERENCES `user_migrations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4708821 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for user_migrations
-- ----------------------------
DROP TABLE IF EXISTS `user_migrations`;
CREATE TABLE `user_migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `status` enum('pending','upgraded','upgrading','failed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_migrations_module_name_user_id_unique` (`module_name`,`user_id`),
  KEY `user_migrations_index` (`user_id`,`module_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2362586 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for user_workload_logs
-- ----------------------------
DROP TABLE IF EXISTS `user_workload_logs`;
CREATE TABLE `user_workload_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `current_critical_load` int(10) unsigned NOT NULL DEFAULT '0',
  `current_noncritical_load` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_workload_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `user_workload_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9244636 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `designation` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `simple` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `reference_no` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date_of_birth` date NOT NULL,
  `profile_pic` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/employees_avatar/default_user.jpg',
  `blood_group` enum('A+','B+','AB+','O+','A-','B-','AB-','O-') COLLATE utf8_unicode_ci NOT NULL,
  `nid_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `department_id` int(10) unsigned NOT NULL,
  `i_help_bd_user_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_cm` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `critical_cap` int(10) unsigned NOT NULL DEFAULT '0',
  `noncritical_cap` int(10) unsigned NOT NULL DEFAULT '0',
  `total_online_hours` decimal(8,2) unsigned DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `about` text COLLATE utf8_unicode_ci NOT NULL,
  `linkedin_profile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `facebook_profile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_department_id_foreign` (`department_id`),
  CONSTRAINT `users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1326 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for usps
-- ----------------------------
DROP TABLE IF EXISTS `usps`;
CREATE TABLE `usps` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=245 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for vehicle_basic_informations
-- ----------------------------
DROP TABLE IF EXISTS `vehicle_basic_informations`;
CREATE TABLE `vehicle_basic_informations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` int(10) unsigned NOT NULL,
  `type` enum('hatchback','sedan','suv','passenger_van','others') COLLATE utf8_unicode_ci NOT NULL,
  `company_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `model_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `vehicle_image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `model_year` date NOT NULL,
  `seat_capacity` int(11) NOT NULL,
  `color` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `trim_level` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mileage_reading_km` decimal(11,2) DEFAULT NULL,
  `height_inch` decimal(11,2) DEFAULT NULL,
  `width_inch` decimal(11,2) DEFAULT NULL,
  `length_inch` decimal(11,2) DEFAULT NULL,
  `volume_ft` decimal(11,2) DEFAULT NULL,
  `weight_kg` decimal(11,2) DEFAULT NULL,
  `max_payload_kg` decimal(11,2) DEFAULT NULL,
  `engine_summary` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `transmission_type` enum('auto','manual') COLLATE utf8_unicode_ci NOT NULL,
  `fuel_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fuel_quality` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fuel_tank_capacity_ltr` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `wheels_and_tires` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vehicle_basic_informations_vehicle_id_foreign` (`vehicle_id`),
  CONSTRAINT `vehicle_basic_informations_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for vehicle_registration_informations
-- ----------------------------
DROP TABLE IF EXISTS `vehicle_registration_informations`;
CREATE TABLE `vehicle_registration_informations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vehicle_id` int(10) unsigned NOT NULL,
  `license_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `license_number_end_date` datetime NOT NULL,
  `license_number_image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tax_token_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tax_token_image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `fitness_start_date` datetime NOT NULL,
  `fitness_end_date` datetime NOT NULL,
  `fitness_paper_image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `insurance_date` datetime NOT NULL,
  `insurance_paper_image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vehicle_registration_informations_license_number_unique` (`license_number`),
  UNIQUE KEY `vehicle_registration_informations_tax_token_number_unique` (`tax_token_number`),
  KEY `vehicle_registration_informations_vehicle_id_foreign` (`vehicle_id`),
  CONSTRAINT `vehicle_registration_informations_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for vehicles
-- ----------------------------
DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE `vehicles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `owner_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `owner_id` int(11) NOT NULL,
  `default_driver_id` int(10) unsigned DEFAULT NULL,
  `current_driver_id` int(10) unsigned DEFAULT NULL,
  `business_department_id` int(10) unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vehicles_default_driver_id_foreign` (`default_driver_id`),
  KEY `vehicles_current_driver_id_foreign` (`current_driver_id`),
  KEY `vehicles_business_department_id_foreign` (`business_department_id`),
  CONSTRAINT `vehicles_business_department_id_foreign` FOREIGN KEY (`business_department_id`) REFERENCES `business_departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `vehicles_current_driver_id_foreign` FOREIGN KEY (`current_driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `vehicles_default_driver_id_foreign` FOREIGN KEY (`default_driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for vendor_bkash_payout
-- ----------------------------
DROP TABLE IF EXISTS `vendor_bkash_payout`;
CREATE TABLE `vendor_bkash_payout` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `transaction_time` datetime NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bkash_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `transaction_details` json DEFAULT NULL,
  `payment_amount` decimal(11,2) NOT NULL,
  `status` enum('Successful','Failed','Pending') COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_bkash_payout_status_index` (`status`),
  KEY `vendor_bkash_payout_created_at_index` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4682 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for vendor_payout_request_data
-- ----------------------------
DROP TABLE IF EXISTS `vendor_payout_request_data`;
CREATE TABLE `vendor_payout_request_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_payout_request_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bkash_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payment_amount` decimal(11,2) NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_payout_request_data_vendor_payout_request_id_foreign` (`vendor_payout_request_id`),
  CONSTRAINT `vendor_payout_request_data_vendor_payout_request_id_foreign` FOREIGN KEY (`vendor_payout_request_id`) REFERENCES `vendor_payout_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=638 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for vendor_payout_requests
-- ----------------------------
DROP TABLE IF EXISTS `vendor_payout_requests`;
CREATE TABLE `vendor_payout_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('single','bulk') COLLATE utf8_unicode_ci NOT NULL,
  `upload_file_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','partially_approved','rejected') COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_payout_requests_status_index` (`status`),
  KEY `vendor_payout_requests_created_at_index` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=181 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for vendor_payout_status_logs
-- ----------------------------
DROP TABLE IF EXISTS `vendor_payout_status_logs`;
CREATE TABLE `vendor_payout_status_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_payout_request_id` int(10) unsigned NOT NULL,
  `user_type` enum('request_maker','approver') COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('pending','approved','partially_approved','rejected') COLLATE utf8_unicode_ci DEFAULT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_payout_status_logs_vendor_payout_request_id_foreign` (`vendor_payout_request_id`),
  CONSTRAINT `vendor_payout_status_logs_vendor_payout_request_id_foreign` FOREIGN KEY (`vendor_payout_request_id`) REFERENCES `vendor_payout_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=698 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for vendor_transactions
-- ----------------------------
DROP TABLE IF EXISTS `vendor_transactions`;
CREATE TABLE `vendor_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` int(10) unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `initiator_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `initiator_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `amount` decimal(11,2) unsigned NOT NULL,
  `balance` decimal(11,2) NOT NULL DEFAULT '0.00',
  `log` text COLLATE utf8_unicode_ci NOT NULL,
  `transaction_details` text COLLATE utf8_unicode_ci,
  `third_party_transaction_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_transactions_vendor_id_foreign` (`vendor_id`),
  KEY `vendor_transactions_third_party_transaction_id_index` (`third_party_transaction_id`),
  CONSTRAINT `vendor_transactions_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16610 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for vendors
-- ----------------------------
DROP TABLE IF EXISTS `vendors`;
CREATE TABLE `vendors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `app_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `app_secret` longtext COLLATE utf8_unicode_ci NOT NULL,
  `whitelisted_ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `wallet` decimal(11,2) NOT NULL DEFAULT '0.00',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for verified_resource_data_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `verified_resource_data_change_logs`;
CREATE TABLE `verified_resource_data_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `resource_id` int(10) unsigned NOT NULL,
  `from` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `to` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `verified_resource_data_change_logs_resource_id_foreign` (`resource_id`),
  CONSTRAINT `verified_resource_data_change_logs_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1109 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for visit_notes
-- ----------------------------
DROP TABLE IF EXISTS `visit_notes`;
CREATE TABLE `visit_notes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `visit_id` int(10) unsigned NOT NULL,
  `note` longtext COLLATE utf8_unicode_ci,
  `status` enum('created','started','reached','rescheduled','cancelled','completed') COLLATE utf8_unicode_ci DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `visit_notes_visit_id_foreign` (`visit_id`),
  CONSTRAINT `visit_notes_visit_id_foreign` FOREIGN KEY (`visit_id`) REFERENCES `visits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2507 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for visit_photos
-- ----------------------------
DROP TABLE IF EXISTS `visit_photos`;
CREATE TABLE `visit_photos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `visit_id` int(10) unsigned NOT NULL,
  `photo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `visit_photos_visit_id_foreign` (`visit_id`),
  CONSTRAINT `visit_photos_visit_id_foreign` FOREIGN KEY (`visit_id`) REFERENCES `visits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4398 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for visit_status_change_logs
-- ----------------------------
DROP TABLE IF EXISTS `visit_status_change_logs`;
CREATE TABLE `visit_status_change_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `visit_id` int(10) unsigned NOT NULL,
  `old_status` enum('created','started','reached','rescheduled','cancelled','completed') COLLATE utf8_unicode_ci NOT NULL,
  `old_location` json DEFAULT NULL,
  `new_status` enum('created','started','reached','rescheduled','cancelled','completed') COLLATE utf8_unicode_ci NOT NULL,
  `new_location` json DEFAULT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `visit_status_change_logs_visit_id_foreign` (`visit_id`),
  CONSTRAINT `visit_status_change_logs_visit_id_foreign` FOREIGN KEY (`visit_id`) REFERENCES `visits` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22410 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for visits
-- ----------------------------
DROP TABLE IF EXISTS `visits`;
CREATE TABLE `visits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `visitor_id` int(10) unsigned NOT NULL,
  `assignee_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `schedule_date` timestamp NULL DEFAULT NULL,
  `status` enum('created','started','reached','rescheduled','cancelled','completed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'started',
  `start_date_time` timestamp NULL DEFAULT NULL,
  `end_date_time` timestamp NULL DEFAULT NULL,
  `total_time_in_minutes` decimal(8,2) DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `visits_visitor_id_foreign` (`visitor_id`),
  KEY `visits_assignee_id_foreign` (`assignee_id`),
  CONSTRAINT `visits_assignee_id_foreign` FOREIGN KEY (`assignee_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `visits_visitor_id_foreign` FOREIGN KEY (`visitor_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7869 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for votes
-- ----------------------------
DROP TABLE IF EXISTS `votes`;
CREATE TABLE `votes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `review_id` int(10) unsigned NOT NULL,
  `vote_type` tinyint(1) NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `votes_customer_id_foreign` (`customer_id`),
  KEY `votes_review_id_foreign` (`review_id`),
  CONSTRAINT `votes_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `votes_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for vouchers
-- ----------------------------
DROP TABLE IF EXISTS `vouchers`;
CREATE TABLE `vouchers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `rules` longtext COLLATE utf8_unicode_ci NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `amount` decimal(8,2) NOT NULL,
  `is_amount_percentage` tinyint(1) NOT NULL DEFAULT '0',
  `cap` decimal(8,2) DEFAULT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `max_order` int(11) NOT NULL DEFAULT '1',
  `max_customer` int(10) unsigned DEFAULT NULL,
  `sheba_contribution` decimal(5,2) NOT NULL,
  `partner_contribution` decimal(5,2) NOT NULL,
  `vendor_contribution` decimal(5,2) NOT NULL DEFAULT '0.00',
  `is_created_by_sheba` tinyint(1) NOT NULL DEFAULT '0',
  `owner_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `owner_id` int(10) unsigned DEFAULT NULL,
  `is_referral` tinyint(1) NOT NULL DEFAULT '0',
  `referred_from` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT '1',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vouchers_code_unique` (`code`),
  KEY `vouchers_owner_id_index` (`owner_id`),
  KEY `vouchers_owner_type_index` (`owner_type`)
) ENGINE=InnoDB AUTO_INCREMENT=2087320 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for webstore_banners
-- ----------------------------
DROP TABLE IF EXISTS `webstore_banners`;
CREATE TABLE `webstore_banners` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `image_link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `small_image_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_published` tinyint(4) NOT NULL DEFAULT '1',
  `is_published_for_sheba` tinyint(4) NOT NULL DEFAULT '1',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for withdraw_reject_comments
-- ----------------------------
DROP TABLE IF EXISTS `withdraw_reject_comments`;
CREATE TABLE `withdraw_reject_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `comment` longtext COLLATE utf8_unicode_ci NOT NULL,
  `is_published` tinyint(1) NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for withdrawal_requests
-- ----------------------------
DROP TABLE IF EXISTS `withdrawal_requests`;
CREATE TABLE `withdrawal_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `requester_id` int(11) NOT NULL,
  `requester_type` enum('resource','partner') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'partner',
  `amount` decimal(11,2) unsigned NOT NULL,
  `wallet_balance` double DEFAULT NULL,
  `status` enum('pending','approval_pending','approved','rejected','completed','failed','expired','cancelled','hold') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_method` enum('bank','bkash') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'bank',
  `payment_info` text COLLATE utf8_unicode_ci,
  `last_fail_reason` longtext COLLATE utf8_unicode_ci,
  `reject_reason` longtext COLLATE utf8_unicode_ci,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic','business-portal') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `api_request_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `withdrawal_requests_requester_id_index` (`requester_id`),
  KEY `withdrawal_requests_requester_type_index` (`requester_type`),
  KEY `withdrawal_requests_status_index` (`status`),
  KEY `withdrawal_requests_payment_method_index` (`payment_method`),
  KEY `withdrawal_requests_portal_name_index` (`portal_name`),
  KEY `withdrawal_requests_api_request_id_foreign` (`api_request_id`),
  CONSTRAINT `withdrawal_requests_api_request_id_foreign` FOREIGN KEY (`api_request_id`) REFERENCES `api_requests` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25978 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Table structure for wrong_pin_count
-- ----------------------------
DROP TABLE IF EXISTS `wrong_pin_count`;
CREATE TABLE `wrong_pin_count` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('affiliate','partner','business','customer','resource') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'affiliate',
  `type_id` int(11) DEFAULT NULL,
  `topup_number` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `topup_amount` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `ip_address` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wrong_pin_count_affiliate_id_foreign` (`type_id`),
  KEY `wrong_pin_count_type_index` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=10337 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Procedure structure for proc
-- ----------------------------
DROP PROCEDURE IF EXISTS `proc`;
delimiter ;;
CREATE PROCEDURE `proc`()
BEGIN
	
	SET @arr = "6LV7M4SL6B,6LU9LCSXIH,6LU8LCSK02,6LU4LCSCVA,6LU8LB49IU,6LT0KUZ0AK,6LQ7JMOG4R,6LQ1JFMZ37,6LQ3JFKVAD,6LQ8JEZN1Q,6LQ7JEXJ4N,6LQ3JESPHF,6LQ9JES58Z,6LQ2JEP5YA,6LQ8JEFXKW,6LQ2JEDYPM,6LQ1JEDUG9,6LQ3JDEA89,6LN3HWW04D,6LN8HW3B5U,6LN8HPZL9G,6LL0H1BMTA,6LJ3FUFWA1,6LJ7FSPAZ9,6LJ8FNO6OG,6LG0EDUHS8,6LD9CQ6SM5,6LD4COTENC,6LD7CF96EV,6LD2CAX59I,6LC3BVBH2V,6LC2BN634K,6LB4BL85YA,6LB3BJQ5Y3,6LA1AQDJWL,6L98AEREQC,6L859JX6DP,6L899ISNEH,6L698B3KMP,6L427GK47S,6L4277HHCC,6L386NVCAQ,6L356GONR9,6L205UM31E,6LQ3JFKVEJ,6LQ0JF1NBQ,6LQ3JESENP,6LQ0JDUPEE,6LP9J5R3R3,6LP7J1YRID,6LP9J1TD11,6LP6IXHNEQ,6LP8IWXGPY,6LP0IW0ALY,6LP0IVIIZA,6LP3IVD23V,6LP4IUY0W0,6LP2IU8HYU,6LP2IU066M,6LP2IPC06G,6LP6IORUNO,6LP0IOMNRQ,6LO6IO7502,6LO2IM23SE,6LO8IIH8WU,6LO6ICUT1Q,6LO4IBUUTG,6LO5I9LV7T,6LO0I8MK0M,6LO5I5QIDP,6LO3I5JNMB,6LN2I43TMQ,6LN0I3QZJE,6LN5I3DRH7,6LN3HT7PWN,6LN0HNKSX4,6LJ1FUXDCD,6L96A0ZS9Y";
	WHILE
			LOCATE( ",", @arr ) > 0 DO
			
			SET @val = ELT( 1, @arr );
		
		SET @arr = SUBSTRING( @arr, LOCATE( ',', @arr ) + 1 );
		
		SET @trx = CONCAT( "%", @val, "%" );
		SELECT
			id 
		FROM
			affiliate_transactions 
		WHERE
			transaction_details LIKE @trx;
		
	END WHILE;

END
;;
delimiter ;

-- ----------------------------
-- Triggers structure for table user_migrations
-- ----------------------------
DROP TRIGGER IF EXISTS `user_migration_status_change_trigger`;
delimiter ;;
CREATE TRIGGER `user_migration_status_change_trigger` AFTER UPDATE ON `user_migrations` FOR EACH ROW BEGIN
                INSERT INTO `user_migration_status_change_logs` (user_migration_id,`from`,`to`, created_by, created_by_name, created_at) values(OLD.id, OLD.status, NEW.status, New.updated_by, NEW.updated_by_name, NOW());
            END
;;
delimiter ;

SET FOREIGN_KEY_CHECKS = 1;
