/*
 Navicat Premium Data Transfer

 Source Server         : Docker Mysql
 Source Server Type    : MySQL
 Source Server Version : 50722
 Source Host           : 127.0.0.1:3306
 Source Schema         : sheba_testing

 Target Server Type    : MySQL
 Target Server Version : 50722
 File Encoding         : 65001

 Date: 24/12/2020 14:37:16
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for access_token_requests
-- ----------------------------
DROP TABLE IF EXISTS `access_token_requests`;
CREATE TABLE `access_token_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` int(10) unsigned DEFAULT NULL,
  `method` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('successful','failed') COLLATE utf8_unicode_ci NOT NULL,
  `failed_reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `portal` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `portal_version` int(11) DEFAULT NULL,
  `imsi` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `imei` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `geo` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `access_token_requests_profile_id_foreign` (`profile_id`),
  KEY `access_token_requests_method_index` (`method`),
  KEY `access_token_requests_status_index` (`status`),
  KEY `access_token_requests_failed_reason_index` (`failed_reason`),
  KEY `access_token_requests_portal_index` (`portal`),
  CONSTRAINT `access_token_requests_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of access_token_requests
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for access_tokens
-- ----------------------------
DROP TABLE IF EXISTS `access_tokens`;
CREATE TABLE `access_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `access_token_request_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `token` varchar(2000) COLLATE utf8_unicode_ci NOT NULL,
  `valid_till` datetime NOT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `access_tokens_access_token_request_id_foreign` (`access_token_request_id`),
  KEY `access_tokens_parent_id_foreign` (`parent_id`),
  KEY `access_tokens_valid_till_index` (`valid_till`),
  KEY `access_tokens_is_active_index` (`is_active`),
  KEY `access_tokens_token_index` (`token`(1024)),
  CONSTRAINT `access_tokens_access_token_request_id_foreign` FOREIGN KEY (`access_token_request_id`) REFERENCES `access_token_requests` (`id`),
  CONSTRAINT `access_tokens_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `access_tokens` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of access_tokens
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of accessor_comment
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for accessor_complain_category
-- ----------------------------
DROP TABLE IF EXISTS `accessor_complain_category`;
CREATE TABLE `accessor_complain_category` (
  `complain_category_id` int(10) unsigned NOT NULL,
  `accessor_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`complain_category_id`,`accessor_id`) USING BTREE,
  KEY `accessor_complain_category_accessor_id_foreign` (`accessor_id`) USING BTREE,
  CONSTRAINT `accessor_complain_category_accessor_id_foreign` FOREIGN KEY (`accessor_id`) REFERENCES `accessors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `accessor_complain_category_complain_category_id_foreign` FOREIGN KEY (`complain_category_id`) REFERENCES `complain_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of accessor_complain_category
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for accessor_complain_preset
-- ----------------------------
DROP TABLE IF EXISTS `accessor_complain_preset`;
CREATE TABLE `accessor_complain_preset` (
  `complain_preset_id` int(10) unsigned NOT NULL,
  `accessor_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`complain_preset_id`,`accessor_id`) USING BTREE,
  KEY `accessor_complain_preset_accessor_id_foreign` (`accessor_id`) USING BTREE,
  CONSTRAINT `accessor_complain_preset_accessor_id_foreign` FOREIGN KEY (`accessor_id`) REFERENCES `accessors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `accessor_complain_preset_complain_preset_id_foreign` FOREIGN KEY (`complain_preset_id`) REFERENCES `complain_presets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of accessor_complain_preset
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of accessors
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of action_business
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of action_business_department
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of action_business_member
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of action_business_role
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of actions
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of active_users
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of affiliate_badge
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of affiliate_notification_logs
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of affiliate_report
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of affiliate_status_change_logs
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of affiliate_suspensions
-- ----------------------------
BEGIN;
COMMIT;

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
  CONSTRAINT `affiliate_transactions_affiliate_id_foreign` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=832215 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of affiliate_transactions
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of affiliate_withdrawal_requests
-- ----------------------------
BEGIN;
COMMIT;

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
  `verification_status` enum('pending','verified','unverified','rejected') COLLATE utf8_unicode_ci DEFAULT NULL,
  `reject_reason` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_suspended` tinyint(1) NOT NULL DEFAULT '0',
  `fake_referral_counter` smallint(6) NOT NULL DEFAULT '0',
  `last_suspended_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=41713 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of affiliates
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=2513 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of affiliation_logs
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of affiliation_milestones
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=27595 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of affiliation_status_change_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=68464 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of affiliations
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for affiliations_report
-- ----------------------------
DROP TABLE IF EXISTS `affiliations_report`;
CREATE TABLE `affiliations_report` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` int(10) unsigned DEFAULT NULL,
  `agent_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ambassador_id` int(10) unsigned DEFAULT NULL,
  `order_code` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_mobile` varchar(14) COLLATE utf8_unicode_ci DEFAULT NULL,
  `service_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=68462 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of affiliations_report
-- ----------------------------
BEGIN;
COMMIT;

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
  `type` enum('event','holiday','financial','others') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'event',
  `is_published` tinyint(4) NOT NULL DEFAULT '1',
  `end_date` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `announcements_business_id_foreign` (`business_id`),
  CONSTRAINT `announcements_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of announcements
-- ----------------------------
BEGIN;
COMMIT;

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
  `lat` double(8,2) DEFAULT NULL,
  `lng` double(8,2) DEFAULT NULL,
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
  KEY `api_requests_portal_version_index` (`portal_version`)
) ENGINE=InnoDB AUTO_INCREMENT=133 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of api_requests
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for app_versions
-- ----------------------------
DROP TABLE IF EXISTS `app_versions`;
CREATE TABLE `app_versions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8_unicode_ci,
  `tag` enum('customer_app_android','manager_app_android','resource_app_android','customer_app_ios','bondhu_app_android','rider_app_android','employee_app_android','employee_app_ios','resource_app_ios') COLLATE utf8_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of app_versions
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of approval_flow_approvers
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of approval_flows
-- ----------------------------
BEGIN;
COMMIT;

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
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approval_requests_approver_id_foreign` (`approver_id`),
  CONSTRAINT `approval_requests_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2086 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of approval_requests
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `article_like_dislikes_article_id_foreign` (`article_id`) USING BTREE,
  KEY `article_like_dislikes_user_type_index` (`user_type`) USING BTREE,
  KEY `article_like_dislikes_user_id_index` (`user_id`) USING BTREE,
  CONSTRAINT `article_like_dislikes_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of article_like_dislikes
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for article_type_article
-- ----------------------------
DROP TABLE IF EXISTS `article_type_article`;
CREATE TABLE `article_type_article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `article_type_id` int(10) unsigned DEFAULT NULL,
  `article_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `article_type_article_article_type_id_foreign` (`article_type_id`) USING BTREE,
  KEY `article_type_article_article_id_foreign` (`article_id`) USING BTREE,
  CONSTRAINT `article_type_article_article_id_foreign` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `article_type_article_article_type_id_foreign` FOREIGN KEY (`article_type_id`) REFERENCES `article_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1253 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of article_type_article
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for article_types
-- ----------------------------
DROP TABLE IF EXISTS `article_types`;
CREATE TABLE `article_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_published` tinyint(4) NOT NULL DEFAULT '1',
  `portal_name` enum('smanager-faq-for-sheba-user','sbusiness-faq-for-sheba-user','sbondhu-faq-for-sheba-user','hr-and-admin-faq-for-sheba-user','sheba-faq-for-sheba-user','business-app','business-portal','admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of article_types
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=254 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of articles
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of artisan_leaves
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=4823 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of attachments
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for attendance_action_logs
-- ----------------------------
DROP TABLE IF EXISTS `attendance_action_logs`;
CREATE TABLE `attendance_action_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attendance_id` int(10) unsigned NOT NULL,
  `action` enum('checkin','checkout') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'checkin',
  `status` enum('on_time','late','absent','left_early','left_timely') COLLATE utf8_unicode_ci DEFAULT NULL,
  `note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `device_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_remote` tinyint(3) unsigned DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `location` json DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_action_logs_attendance_id_foreign` (`attendance_id`),
  CONSTRAINT `attendance_action_logs_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of attendance_action_logs
-- ----------------------------
BEGIN;
COMMIT;

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
  `status` enum('on_time','late','absent','left_early') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'on_time',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendances_business_member_id_date_unique` (`business_member_id`,`date`),
  CONSTRAINT `attendances_business_member_id_foreign` FOREIGN KEY (`business_member_id`) REFERENCES `business_member` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of attendances
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=251 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of authentication_requests
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of authorization_requests
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for authorization_tokens
-- ----------------------------
DROP TABLE IF EXISTS `authorization_tokens`;
CREATE TABLE `authorization_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(3000) COLLATE utf8_unicode_ci NOT NULL,
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
  KEY `authorization_tokens_token_index` (`token`),
  KEY `authorization_tokens_valid_till_index` (`valid_till`),
  KEY `authorization_tokens_refresh_valid_till_index` (`refresh_valid_till`),
  KEY `authorization_tokens_is_blacklisted_index` (`is_blacklisted`),
  KEY `authorization_tokens_blacklisted_reason_index` (`blacklisted_reason`),
  CONSTRAINT `authorization_tokens_authorization_request_id_foreign` FOREIGN KEY (`authorization_request_id`) REFERENCES `authorization_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of authorization_tokens
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of automatic_subscription_upgradation_logs
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of badge_customer
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of badge_partner
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of badges
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of bank_users
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of banks
-- ----------------------------
BEGIN;
COMMIT;

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
  `input_type` enum('text','radio','number','select','textarea','checkbox') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'text',
  `variables` longtext COLLATE utf8_unicode_ci NOT NULL,
  `result` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bidder_result` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `bid_item_fields_bid_item_id_foreign` (`bid_item_id`) USING BTREE,
  CONSTRAINT `bid_item_fields_bid_item_id_foreign` FOREIGN KEY (`bid_item_id`) REFERENCES `bid_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=983 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of bid_item_fields
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `bid_items_bid_id_foreign` (`bid_id`) USING BTREE,
  KEY `bid_items_type_index` (`type`) USING BTREE,
  CONSTRAINT `bid_items_bid_id_foreign` FOREIGN KEY (`bid_id`) REFERENCES `bids` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=375 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of bid_items
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `bid_status_change_logs_bid_id_foreign` (`bid_id`) USING BTREE,
  KEY `bid_status_change_logs_from_status_index` (`from_status`) USING BTREE,
  KEY `bid_status_change_logs_to_status_index` (`to_status`) USING BTREE,
  CONSTRAINT `bid_status_change_logs_bid_id_foreign` FOREIGN KEY (`bid_id`) REFERENCES `bids` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=295 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of bid_status_change_logs
-- ----------------------------
BEGIN;
COMMIT;

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
  `price` decimal(8,2) NOT NULL,
  `bidder_price` decimal(8,2) NOT NULL,
  `commission_percentage` decimal(8,2) DEFAULT NULL,
  `proposal` longtext COLLATE utf8_unicode_ci,
  `is_favourite` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `bids_procurement_id_foreign` (`procurement_id`) USING BTREE,
  KEY `bids_bidder_id_index` (`bidder_id`) USING BTREE,
  KEY `bids_bidder_type_index` (`bidder_type`) USING BTREE,
  CONSTRAINT `bids_procurement_id_foreign` FOREIGN KEY (`procurement_id`) REFERENCES `procurements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=286 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of bids
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of block_grid
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of blocks
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of blog_posts
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of bondhu_bulk_point_distribute_logs
-- ----------------------------
BEGIN;
COMMIT;

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
  `spent_on_type` enum('App\\Models\\PartnerOrder','App\\Models\\TopUpOrder','App\\Models\\PartnerSubscriptionPackage','App\\Models\\Transport\\TransportTicketOrder','Sheba\\Utility\\UtilityOrder','App\\Models\\MovieTicketOrder') COLLATE utf8_unicode_ci DEFAULT NULL,
  `spent_on_id` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bonuses_user_index` (`user_type`,`user_id`),
  KEY `bonuses_user_type_index` (`user_type`,`user_id`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=8813 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of bonus_logs
-- ----------------------------
BEGIN;
COMMIT;

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
  `valid_till` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
) ENGINE=InnoDB AUTO_INCREMENT=7946 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of bonuses
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for bug_issues
-- ----------------------------
DROP TABLE IF EXISTS `bug_issues`;
CREATE TABLE `bug_issues` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bug_id` int(10) unsigned NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bug_issues_bug_id_foreign` (`bug_id`),
  CONSTRAINT `bug_issues_bug_id_foreign` FOREIGN KEY (`bug_id`) REFERENCES `bugs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of bug_issues
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of bugs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=221 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of business_attendance_types
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of business_bank_informations
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of business_categories
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of business_delivery_addresses
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=9140 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of business_departments
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=2283 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of business_holidays
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of business_join_requests
-- ----------------------------
BEGIN;
COMMIT;

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
  `business_role_id` int(10) unsigned DEFAULT NULL,
  `grade` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('active','inactive','invited') COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_super` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_member_business_id_foreign` (`business_id`),
  KEY `business_member_member_id_foreign` (`member_id`),
  KEY `business_member_business_role_id_foreign` (`business_role_id`),
  KEY `business_member_manager_id_foreign` (`manager_id`),
  CONSTRAINT `business_member_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `business_member_business_role_id_foreign` FOREIGN KEY (`business_role_id`) REFERENCES `business_roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `business_member_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `business_member` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `business_member_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2495 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of business_member
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of business_member_leave_types
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of business_member_status_change_logs
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for business_office_hours
-- ----------------------------
DROP TABLE IF EXISTS `business_office_hours`;
CREATE TABLE `business_office_hours` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` int(10) unsigned NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `business_office_hours_business_id_unique` (`business_id`),
  CONSTRAINT `business_office_hours_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=130 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of business_office_hours
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of business_offices
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of business_partners
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=54241 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of business_roles
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of business_sms_templates
-- ----------------------------
BEGIN;
COMMIT;

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
  CONSTRAINT `business_transactions_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of business_transactions
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of business_trip_requests
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of business_trips
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=218 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of business_weekends
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=894 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of businesses
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for cache
-- ----------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL,
  UNIQUE KEY `cache_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of cache
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=6990 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of car_rental_job_details
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of car_rental_prices
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of careers
-- ----------------------------
BEGIN;
COMMIT;

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
  `icon_hover` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/category_images/icons_hover/default_v3.png',
  `icon_png` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `icon_png_hover` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/category_images/icons_png_hover/default_hover_v3.png',
  `icon_png_active` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/category_images/icons_active/active_v3.png',
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
) ENGINE=InnoDB AUTO_INCREMENT=523 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of categories
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for category_complain_preset
-- ----------------------------
DROP TABLE IF EXISTS `category_complain_preset`;
CREATE TABLE `category_complain_preset` (
  `category_id` int(10) unsigned NOT NULL,
  `complain_preset_id` int(10) unsigned NOT NULL,
  KEY `category_complain_preset_category_id_foreign` (`category_id`) USING BTREE,
  KEY `category_complain_preset_complain_preset_id_foreign` (`complain_preset_id`) USING BTREE,
  CONSTRAINT `category_complain_preset_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `category_complain_preset_complain_preset_id_foreign` FOREIGN KEY (`complain_preset_id`) REFERENCES `complain_presets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of category_complain_preset
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of category_group_category
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of category_group_location
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of category_groups
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of category_location
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=137954 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of category_partner
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of category_partner_resource
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of category_question_answers
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of category_question_attributes
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of category_questions
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=10010 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of category_requests
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of category_resource
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of category_schedule_slot
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=169 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of category_usp
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of cities
-- ----------------------------
BEGIN;
INSERT INTO `cities` VALUES (1, 1, 'Dhaka', 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2016-12-16 02:41:24', '2016-12-16 02:41:24');
INSERT INTO `cities` VALUES (2, 1, 'Chittagong', 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2018-12-13 02:41:24', '2018-12-13 02:41:24');
INSERT INTO `cities` VALUES (3, 1, 'Other', 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2018-12-24 02:41:24', '2018-12-24 02:41:24');
INSERT INTO `cities` VALUES (4, 1, 'Gazipur', 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2018-12-24 02:41:24', '2018-12-24 02:41:24');
INSERT INTO `cities` VALUES (5, 1, 'Jessore', 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2019-03-18 02:41:24', '2019-03-18 02:41:24');
INSERT INTO `cities` VALUES (6, 1, 'Bogura', 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:50', '2020-12-15 16:03:50');
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of combo_services
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=528520 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of comments
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of complain_categories
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=22804 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of complain_logs
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `complain_presets_type_id_foreign` (`type_id`) USING BTREE,
  KEY `complain_presets_category_id_foreign` (`category_id`) USING BTREE,
  CONSTRAINT `complain_presets_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `complain_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `complain_presets_type_id_foreign` FOREIGN KEY (`type_id`) REFERENCES `complain_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of complain_presets
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of complain_types
-- ----------------------------
BEGIN;
COMMIT;

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
  `resolved_category` enum('service_provided','sp_compensated','promo_provided','development') COLLATE utf8_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=10359 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of complains
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for complains_report
-- ----------------------------
DROP TABLE IF EXISTS `complains_report`;
CREATE TABLE `complains_report` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `complain_code` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `complain_status` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `complain` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `complain_category` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `complain_preset` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `complain _source` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `job_code` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `job_status` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `order_code` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `complain_applicable_for` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resource` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `om_name` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `service_group` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `service_category` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `service_name` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lifetime_sla` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_mobile` varchar(14) COLLATE utf8_unicode_ci DEFAULT NULL,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10358 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of complains_report
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of countries
-- ----------------------------
BEGIN;
INSERT INTO `countries` VALUES (1, 'Bangladesh', 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2016-12-16 02:41:24', '2016-12-16 02:41:24');
COMMIT;

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
  `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'https://s3.ap-south-1.amazonaws.com/cdn-shebadev/marketplace/default_images/png/cross_sell.png',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of crosssale_services
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of custom_order_cancel_logs
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of custom_order_discussions
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of custom_order_status_logs
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of custom_order_update_logs
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of custom_orders
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=97217 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of customer_delivery_addresses
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=3855 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of customer_favourite_service
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=3362 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of customer_favourites
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of customer_mobiles
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for customer_report
-- ----------------------------
DROP TABLE IF EXISTS `customer_report`;
CREATE TABLE `customer_report` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile` varchar(14) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile_verified` tinyint(1) NOT NULL DEFAULT '0',
  `email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `address` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `report_updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of customer_report
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=11053 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of customer_reviews
-- ----------------------------
BEGIN;
COMMIT;

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
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_transactions_customer_id_foreign` (`customer_id`),
  KEY `customer_transactions_partner_order_id_foreign` (`partner_order_id`),
  CONSTRAINT `customer_transactions_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `customer_transactions_partner_order_id_foreign` FOREIGN KEY (`partner_order_id`) REFERENCES `partner_orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2182 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of customer_transactions
-- ----------------------------
BEGIN;
COMMIT;

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
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_profile_id_unique` (`profile_id`),
  UNIQUE KEY `customers_remember_token_unique` (`remember_token`),
  KEY `customers_portal_name_index` (`portal_name`),
  CONSTRAINT `customers_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=224055 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of customers
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=867 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of daily_stats
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=337 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of dashboard_settings
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for data_migrations
-- ----------------------------
DROP TABLE IF EXISTS `data_migrations`;
CREATE TABLE `data_migrations` (
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of data_migrations
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=654 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of delivery_charge_update_requests
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of department_feature
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of departments
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for discount_applicables
-- ----------------------------
DROP TABLE IF EXISTS `discount_applicables`;
CREATE TABLE `discount_applicables` (
  `discount_id` int(10) unsigned NOT NULL,
  `applicable_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `applicable_id` int(11) DEFAULT NULL,
  UNIQUE KEY `discount_applicable_unique` (`discount_id`,`applicable_type`,`applicable_id`) USING BTREE,
  CONSTRAINT `discount_applicables_discount_id_foreign` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of discount_applicables
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `discounts_partner_id_foreign` (`partner_id`) USING BTREE,
  CONSTRAINT `discounts_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of discounts
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of districts
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of divisions
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of documents
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of driver_vehicles
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of drivers
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=287922 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of events
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for expenses
-- ----------------------------
DROP TABLE IF EXISTS `expenses`;
CREATE TABLE `expenses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `remarks` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `comment` longtext COLLATE utf8_unicode_ci,
  `type` enum('transport','food','other') COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('pending','accepted','rejected') COLLATE utf8_unicode_ci NOT NULL,
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
  CONSTRAINT `expenses_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of expenses
-- ----------------------------
BEGIN;
COMMIT;

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
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic','business-portal','digigo-portal') COLLATE utf8_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of external_payments
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of external_projects
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of features
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of feedbacks
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of flag_attachments
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=38714 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of flag_status_change_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=12108 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of flag_time_change_logs
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of flag_user_change_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=17472 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of flags
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `form_template_items_form_template_id_foreign` (`form_template_id`) USING BTREE,
  CONSTRAINT `form_template_items_form_template_id_foreign` FOREIGN KEY (`form_template_id`) REFERENCES `form_templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of form_template_items
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of form_template_questions
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of form_templates
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of fuel_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of galleries
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=477 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of gift_card_purchases
-- ----------------------------
BEGIN;
COMMIT;

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
  `start_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `validity_in_months` int(11) NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of gift_cards
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of government_holidays
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of grid_portal
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of grids
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of hired_drivers
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of hired_vehicles
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for home_grids_copy
-- ----------------------------
DROP TABLE IF EXISTS `home_grids_copy`;
CREATE TABLE `home_grids_copy` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `item_id` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  `is_published_for_app` tinyint(1) NOT NULL DEFAULT '0',
  `is_published_for_web` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of home_grids_copy
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of home_menu_elements
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of home_menu_location
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of home_menus
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for homepage_settings_copy
-- ----------------------------
DROP TABLE IF EXISTS `homepage_settings_copy`;
CREATE TABLE `homepage_settings_copy` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order` int(11) NOT NULL,
  `item_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `is_published_for_app` tinyint(1) NOT NULL DEFAULT '0',
  `is_published_for_web` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=200 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of homepage_settings_copy
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of impression_deduction_partner
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=1791216 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of impression_deductions
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of incomplete_orders
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for info_calls
-- ----------------------------
DROP TABLE IF EXISTS `info_calls`;
CREATE TABLE `info_calls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `affiliation_id` int(10) unsigned DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `customer_mobile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `customer_email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
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
  `status` enum('Open','Closed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Open',
  `additional_informations` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `customer_id` int(10) unsigned DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=21968 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of info_calls
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of inspection_item_issues
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of inspection_item_status_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of inspection_items
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of inspection_schedules
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of inspections
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of ipdc_sms_logs
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for job_cancel_logs
-- ----------------------------
DROP TABLE IF EXISTS `job_cancel_logs`;
CREATE TABLE `job_cancel_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `from_status` enum('Pending','Accepted','Declined','Not Responded','Schedule Due','Process','Serve Due','Served','Cancelled') COLLATE utf8_unicode_ci DEFAULT 'Pending',
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cancel_reason` enum('Wrongly Create Order/ Test Order','Wrongly Create Order','Test Order','SP Unreachable','Price Shock','Resource Capacity','Service Disputes','Schedule Missed','Automatically Resolved','Customer Unreachable','Future Schedule','Duplicate Order','Service Change','SP Requested to Cancel','Resource Behaviour','Resource Skill','Customer Dependency','Customer Management','Push Sales Attempt','Insufficient Partner','Service Limitation','Urgent Support','Others','Fake','I Selected Wrong Schedule','I Want To Select Other Service','I Want To Select Other Service Provider','I Want Service AT Different Address') COLLATE utf8_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=45095 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of job_cancel_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of job_cancel_reasons
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for job_cancel_requests
-- ----------------------------
DROP TABLE IF EXISTS `job_cancel_requests`;
CREATE TABLE `job_cancel_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `job_id` int(10) unsigned NOT NULL,
  `from_status` enum('Pending','Accepted','Declined','Not Responded','Schedule Due','Process','Serve Due','Served','Cancelled') COLLATE utf8_unicode_ci DEFAULT 'Pending',
  `cancel_reason` enum('Wrongly Create Order/ Test Order','Wrongly Create Order','Test Order','SP Unreachable','Price Shock','Resource Capacity','Service Disputes','Schedule Missed','Automatically Resolved','Customer Unreachable','Future Schedule','Duplicate Order','Service Change','SP Requested to Cancel','Resource Behaviour','Resource Skill','Customer Dependency','Customer Management','Push Sales Attempt','Insufficient Partner','Service Limitation','Urgent Support','Others','Fake','I Selected Wrong Schedule','I Want To Select Other Service','I Want To Select Other Service Provider','I Want Service AT Different Address') COLLATE utf8_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=46775 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of job_cancel_requests
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=2144329 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of job_crm_change_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=2315 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of job_decline_logs
-- ----------------------------
BEGIN;
COMMIT;

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
  UNIQUE KEY `job_discounts_job_id_discount_id_unique` (`job_id`,`discount_id`),
  KEY `job_discounts_discount_id_foreign` (`discount_id`),
  CONSTRAINT `job_discounts_discount_id_foreign` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `job_discounts_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of job_discounts
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=26304 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of job_material
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=8098 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of job_material_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=52781 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of job_no_response_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=26585 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of job_partner_change_logs
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of job_partner_change_reasons
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=145605 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of job_schedule_due_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=281547 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of job_service
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=948516 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of job_status_change_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=694156 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of job_update_logs
-- ----------------------------
BEGIN;
COMMIT;

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
  `commission_rate` int(10) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=304347 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of jobs
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of join_requests
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of lafs_order_customer_action_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of lafs_order_partner_action_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of lafs_order_status_change_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of lafs_orders
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for leave_logs
-- ----------------------------
DROP TABLE IF EXISTS `leave_logs`;
CREATE TABLE `leave_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `leave_id` int(10) unsigned NOT NULL,
  `type` enum('status','leave_type','leave_date','substitute','leave_adjustment','leave_update') COLLATE utf8_unicode_ci DEFAULT NULL,
  `from` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `to` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `log` text COLLATE utf8_unicode_ci,
  `is_changed_by_super` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_logs_leave_id_foreign` (`leave_id`),
  CONSTRAINT `leave_logs_leave_id_foreign` FOREIGN KEY (`leave_id`) REFERENCES `leaves` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of leave_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of leave_status_change_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of leave_types
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=2071 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of leaves
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of loan_claim_requests
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of loan_payments
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of location_offer_group
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of location_offer_showcase
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of location_partner
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of location_partner_service
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of location_resource
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of location_screen_setting
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for location_service
-- ----------------------------
DROP TABLE IF EXISTS `location_service`;
CREATE TABLE `location_service` (
  `location_id` int(10) unsigned NOT NULL,
  `service_id` int(10) unsigned NOT NULL,
  `base_quantity` longtext COLLATE utf8_unicode_ci,
  `base_prices` longtext COLLATE utf8_unicode_ci,
  `prices` longtext COLLATE utf8_unicode_ci NOT NULL,
  `upsell_price` json DEFAULT NULL,
  `min_prices` longtext COLLATE utf8_unicode_ci,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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
) ENGINE=InnoDB AUTO_INCREMENT=79733 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of location_service
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of location_service_service_discount
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=191 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of locations
-- ----------------------------
BEGIN;
INSERT INTO `locations` VALUES (1, 1, 'Mohammadpur', '{\"lat\":23.765181318668,\"lng\":90.357595,\"radius\":\"2\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.335983731894,23.783471102709],[90.33646,23.78181],[90.33726,23.78102],[90.33858,23.77942],[90.33595,23.77648],[90.33487,23.7748],[90.34146,23.77332],[90.34513,23.76949],[90.34062,23.76544],[90.33648,23.75831],[90.33131,23.75165],[90.33285,23.7496],[90.33877,23.75063],[90.34126,23.74929],[90.346720772693,23.744149717141],[90.348972973628,23.745761227568],[90.352195393848,23.746826409471],[90.354362245369,23.752736645105],[90.3568,23.75639],[90.36188,23.75721],[90.36704,23.7601],[90.37273,23.76023],[90.374788017197,23.758484188148],[90.374807780886,23.758444475074],[90.38388,23.7588],[90.38359475215,23.765526726841],[90.383740837326,23.76550693978],[90.377528746033,23.786212920194],[90.351934927578,23.781581917512],[90.350258819609,23.782181646598],[90.348732915344,23.782764191848],[90.344326607132,23.783095182322],[90.342967288361,23.783072734119],[90.339137899475,23.782895650282],[90.335960262566,23.783580017782],[90.335983731894,23.783471102709]]]},\"center\":{\"lat\":23.765181318668,\"lng\":90.357595}}', 1, 1, 1, 'IT - Shafiqul Islam', 17, 'IT - Firoze Ahmed', '2016-12-16 02:41:24', '2019-07-01 17:43:02');
INSERT INTO `locations` VALUES (2, 1, 'Farmgate', '{\"lat\":23.75655,\"lng\":90.387215,\"radius\":\"1.1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.3898,23.75835],[90.38458,23.75791],[90.38449,23.75685],[90.38445,23.75499],[90.3855,23.75495],[90.38664,23.755],[90.38877,23.75475],[90.38967,23.7566],[90.38998,23.758],[90.3898,23.75835],[90.3898,23.75835]]]},\"center\":{\"lat\":23.75655,\"lng\":90.387215}}', 1, 1, 1, 'IT - Shafiqul Islam', 171, 'PM - Zakir Hossain', '2016-12-16 02:41:24', '2020-05-16 14:47:32');
INSERT INTO `locations` VALUES (3, 1, 'Dhanmondi', '{\"lat\":23.740765185361,\"lng\":90.371251122684,\"radius\":\"1.6\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.37273,23.76023],[90.374784780426,23.758489097942],[90.378465408275,23.751416646423],[90.379098834324,23.75158465549],[90.380057813492,23.752023023539],[90.381709897859,23.752731280587],[90.384391764095,23.752711723592],[90.386394402447,23.751242243641],[90.390220189278,23.750622656414],[90.393420714409,23.749780127126],[90.394840196491,23.745271838073],[90.395782245369,23.742543423143],[90.394927699521,23.742592673128],[90.394277001558,23.742632102313],[90.394005296756,23.742602712987],[90.393669218939,23.742534040502],[90.393000547204,23.742033252286],[90.392853965967,23.740741843577],[90.392330064234,23.738780117318],[90.392321146632,23.73868438008],[90.392303009405,23.738664141159],[90.392307670955,23.738677048386],[90.39113078712,23.738848644391],[90.389828965606,23.736677541355],[90.388354812165,23.735094149917],[90.387291036701,23.733478819458],[90.387107025794,23.732863426189],[90.386503440476,23.730839879993],[90.39087475215,23.726280196154],[90.392125364418,23.725444911039],[90.389274489584,23.72361453886],[90.38629160367,23.722834377965],[90.381587901206,23.72268770594],[90.378768483467,23.725272651059],[90.377235626984,23.725835426981],[90.376010670552,23.721909305178],[90.375321545386,23.721300370722],[90.36486,23.72348],[90.35907,23.72588],[90.35554,23.73441],[90.35356,23.73965],[90.35181,23.7412],[90.34967,23.74127],[90.34672,23.74415],[90.34897,23.74576],[90.352194825437,23.746826618088],[90.35436035028,23.752735692168],[90.356797434359,23.756391047213],[90.361878484044,23.757211553346],[90.367039562583,23.760101606406],[90.37273,23.76023]]]},\"center\":{\"lat\":23.740765185361,\"lng\":90.371251122684}}', 1, 1, 1, 'IT - Shafiqul Islam', 154, 'IT - S. A. T. M. Marzan ', '2016-12-16 02:41:24', '2020-05-16 18:34:01');
INSERT INTO `locations` VALUES (4, 1, 'Gulshan', '{\"lat\":23.788099544655,\"lng\":90.412001016086,\"radius\":\"3\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.402575364418,23.804897115104],[90.402258184287,23.800389505327],[90.407605829596,23.800279574726],[90.407228961401,23.797860810336],[90.409135976352,23.796321609682],[90.409345117339,23.79473532345],[90.409677568624,23.79344756045],[90.409666554524,23.790911264727],[90.411586445648,23.790275969034],[90.411456558555,23.789780931366],[90.412527160039,23.7884276098],[90.405538123523,23.786908840217],[90.405024739744,23.786404182168],[90.403772666629,23.786327524631],[90.403446474119,23.785064827553],[90.404156651279,23.785592687605],[90.404890360091,23.784596535752],[90.406218302848,23.784440118487],[90.407986696004,23.782925011077],[90.412060112534,23.780314883587],[90.412065512152,23.778934102016],[90.411056763229,23.778708004874],[90.410727743089,23.777954390047],[90.412168980899,23.776415314535],[90.411877743089,23.775177360059],[90.410771893357,23.773689449417],[90.407745971779,23.773480594621],[90.406066714031,23.770754611044],[90.410865088507,23.770029815916],[90.412824393466,23.76991288518],[90.41311,23.77066],[90.4136,23.77064],[90.41385,23.77102],[90.41374,23.77162],[90.41528,23.77234],[90.41658,23.7724],[90.41715,23.7728],[90.4179,23.77279],[90.418234898148,23.771844077009],[90.418712915344,23.773284909235],[90.41815,23.77331],[90.41739,23.77379],[90.41884,23.77763],[90.418943746033,23.77816473201],[90.41879,23.77921],[90.41877,23.78124],[90.41891,23.78233],[90.41927,23.78374],[90.420062157078,23.785060812654],[90.42031342576,23.786269133768],[90.420822186508,23.787320365181],[90.421039788361,23.787828690622],[90.421150101852,23.788376283402],[90.42136,23.78955],[90.421743847885,23.791194672895],[90.42166635582,23.79173],[90.421396195107,23.792526890922],[90.42089,23.79299],[90.421455189566,23.79426877289],[90.421214919497,23.794513653671],[90.420011108545,23.795825391656],[90.418635636215,23.797176383067],[90.418022080615,23.800151484924],[90.418228275144,23.802944918525],[90.417690156672,23.804443798144],[90.416980376823,23.805667812086],[90.416375538113,23.806212592276],[90.416071106813,23.80628620413],[90.415617084656,23.806214048561],[90.402671923943,23.804902023145],[90.402575364418,23.804897115104]]]},\"center\":{\"lat\":23.788099544655,\"lng\":90.412001016086}}', 1, 1, 1, 'IT - Shafiqul Islam', 15, 'IT - Quaviur Rahman', '2016-12-16 02:41:24', '2020-05-16 18:23:16');
INSERT INTO `locations` VALUES (5, 1, 'Mirpur', '{\"lat\":23.814800953807,\"lng\":90.362328935888,\"radius\":\"2.7\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.34255521842,23.843429281039],[90.347784679151,23.844575019597],[90.352988994172,23.845711548347],[90.35733,23.84757],[90.359870189855,23.848021907614],[90.363138802729,23.84691438882],[90.362455680577,23.840315710319],[90.363487410517,23.836623698407],[90.36383161411,23.82936030989],[90.369926033411,23.829575692858],[90.372973243062,23.829526356786],[90.376385123034,23.830208310779],[90.37559129927,23.829712561847],[90.376894020574,23.829883651615],[90.377394090831,23.830140615201],[90.377914749265,23.830574401275],[90.380407884183,23.831634433236],[90.38238603497,23.830339079923],[90.376062011657,23.811132147165],[90.388697871776,23.806139815989],[90.387576180391,23.804730551719],[90.388581778521,23.798089139734],[90.387301618097,23.798198469414],[90.387953031912,23.791230312723],[90.377492303076,23.78625958463],[90.377507635472,23.786207616451],[90.35193,23.78158],[90.350326479601,23.782155614884],[90.348732346933,23.782762521316],[90.34433,23.78309],[90.342980827804,23.783070473865],[90.339137536499,23.782894170714],[90.33596,23.78358],[90.337022507935,23.785297181735],[90.3386,23.78671],[90.33968,23.79079],[90.339736007271,23.797775415741],[90.342910846558,23.798590278658],[90.343565830688,23.800562214658],[90.34362,23.80222],[90.34262,23.80539],[90.34007,23.8084],[90.33995,23.81285],[90.34126,23.81747],[90.34137,23.82209],[90.34469,23.82709],[90.33959,23.83099],[90.33878,23.83756],[90.34251,23.84139],[90.34243317791,23.843402294825],[90.34255521842,23.843429281039]]]},\"center\":{\"lat\":23.814800953807,\"lng\":90.362328935888}}', 1, 1, 1, 'IT - Shafiqul Islam', 426, 'IT - Sakib Uddin Ahmad', '2016-12-16 02:41:24', '2020-04-08 13:35:30');
INSERT INTO `locations` VALUES (6, 1, 'Motijheel', '{\"lat\":23.735523654455,\"lng\":90.418698170408,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.416662278841,23.745254735734],[90.416493214264,23.747242635174],[90.416642394539,23.747729447355],[90.416641882824,23.749436081114],[90.417888982088,23.749325076965],[90.419189725531,23.749223893007],[90.42282118068,23.74847158686],[90.424693585896,23.747663023556],[90.426154285879,23.745488980348],[90.426962958336,23.740292230901],[90.427048775463,23.740293120228],[90.42848,23.73885],[90.42855,23.72489],[90.4272,23.72496],[90.42697,23.72338],[90.42198,23.7233],[90.42142,23.72168],[90.420316705523,23.721611227796],[90.415756734953,23.722333683367],[90.413655706042,23.722917091863],[90.41215390992,23.72299669583],[90.411050971926,23.723047854783],[90.410657478216,23.723057269127],[90.41059201887,23.725492172332],[90.41043,23.72666],[90.40992,23.73042],[90.410166927405,23.736406031285],[90.410314613076,23.73642796997],[90.41098817589,23.737139690399],[90.408846340816,23.737668443579],[90.41062886866,23.740149554558],[90.410735586445,23.740167387527],[90.411376063824,23.74105928361],[90.414042711639,23.743953765826],[90.417452161407,23.741497205533],[90.417322255325,23.742621006828],[90.417203078079,23.743499280734],[90.416079400717,23.743723936275],[90.416145198946,23.744548262094],[90.415910589765,23.745087785688],[90.416104379366,23.745235094563],[90.416662278841,23.745254735734]]]},\"center\":{\"lat\":23.735523654455,\"lng\":90.418698170408}}', 1, 1, 1, 'IT - Shafiqul Islam', 154, 'IT - S. A. T. M. Marzan ', '2016-12-16 02:41:24', '2020-05-16 18:03:59');
INSERT INTO `locations` VALUES (7, 1, 'Uttara', '{\"lat\":23.864875801165,\"lng\":90.377921836233,\"radius\":\"3.1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.405941458827,23.881369444529],[90.40209851059,23.880834037301],[90.395602679901,23.879918722219],[90.392688574066,23.879494029871],[90.392842915344,23.880148483114],[90.391942507935,23.885580380053],[90.391585219574,23.888064565349],[90.389110846558,23.891294571227],[90.387698574066,23.893113724901],[90.386387899475,23.894845106577],[90.384315015869,23.897537255967],[90.380041253967,23.89834337137],[90.374951865082,23.896002671496],[90.371554169312,23.895573434222],[90.367981190491,23.894452669851],[90.361836441803,23.89262571713],[90.359563730164,23.888543699948],[90.359404169311,23.884456961049],[90.359349357147,23.88197582563],[90.357179465637,23.879700355617],[90.352472679901,23.874731135232],[90.352123322754,23.867078868274],[90.349542261238,23.862840874135],[90.347304522476,23.858543869197],[90.345876034393,23.856818121577],[90.342163488083,23.852595350983],[90.341433558197,23.850943369176],[90.341814105835,23.847891016693],[90.342433352761,23.843400919985],[90.35299,23.84571],[90.35729,23.84755],[90.35987,23.84802],[90.37482,23.84612],[90.374850435972,23.846139239772],[90.38545682209,23.856665281753],[90.386066299267,23.85709307228],[90.406550945244,23.831408230959],[90.410489917488,23.833973595383],[90.414410114269,23.836534002223],[90.411941760063,23.841428248092],[90.40746032547,23.850237500955],[90.405122967282,23.854702902731],[90.40647763864,23.854565107199],[90.406210262566,23.855716458204],[90.406148338623,23.865531131049],[90.406012537365,23.875779909005],[90.405941458827,23.879544717041],[90.405941458827,23.881369444529]]]},\"center\":{\"lat\":23.864875801165,\"lng\":90.377921836233}}', 1, 1, 1, 'IT - Shafiqul Islam', 154, 'IT - S. A. T. M. Marzan ', '2016-12-16 02:41:24', '2020-05-16 17:03:24');
INSERT INTO `locations` VALUES (8, 1, 'Malibag', '{\"lat\":23.745705,\"lng\":90.41451,\"radius\":\"0.5\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.41294,23.74973],[90.41659,23.74906],[90.41656,23.74771],[90.4164,23.7473],[90.4166,23.74532],[90.41606,23.74526],[90.41583,23.74513],[90.41608,23.74459],[90.41603,23.74368],[90.41717,23.74347],[90.41727,23.74256],[90.4174,23.74156],[90.41162,23.7458],[90.41179,23.7467],[90.41199,23.74985],[90.41294,23.74973]]]},\"center\":{\"lat\":23.745705,\"lng\":90.41451}}', 1, 1, 1, 'IT - Shafiqul Islam', 191, 'SD - Ruhul Amin', '2016-12-16 02:41:24', '2020-05-16 15:05:51');
INSERT INTO `locations` VALUES (9, 1, 'Banani ', '{\"lat\":23.79228047513,\"lng\":90.405715294182,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.399649143219,23.78491283261],[90.402052402496,23.784196146419],[90.402717590332,23.784662483773],[90.40340423584,23.785001190909],[90.403752923012,23.786341284399],[90.404380559922,23.78638546307],[90.405002832413,23.786434550465],[90.405523180961,23.786935240832],[90.412491559982,23.788437300361],[90.41143476963,23.789767541201],[90.411558151245,23.790253492947],[90.40963768959,23.790896517212],[90.409653782844,23.793434223141],[90.409358739853,23.794587709452],[90.409224629402,23.795451590367],[90.409101247787,23.796300740442],[90.408537983894,23.796776851062],[90.408028364181,23.797194059965],[90.407556295394,23.797557276035],[90.407191514968,23.797851774807],[90.407325625419,23.798759808485],[90.407481193542,23.799677652293],[90.407583117484,23.800251914907],[90.401569604874,23.80036480384],[90.400799810887,23.796838205396],[90.399085879326,23.786753618078],[90.398939028383,23.785751620421],[90.399371534586,23.785652831501],[90.399635732174,23.78551415873],[90.399574041367,23.785256447876],[90.399649143219,23.78491283261]]]},\"center\":{\"lat\":23.79228047513,\"lng\":90.405715294182}}', 1, 1, 4, 'IT - Abu Naser Md. Shoaib', 15, 'IT - Quaviur Rahman', '2016-12-16 02:41:24', '2020-12-15 20:32:44');
INSERT INTO `locations` VALUES (10, 1, 'Rest of Dhaka', '{\"lat\":\"\",\"lng\":\"\",\"radius\":\"1\"}', 0, 0, 4, 'IT - Abu Naser Md. Shoaib', 5, 'IT - Arnab Rahman', '2016-12-25 06:09:59', '2019-03-05 12:53:49');
INSERT INTO `locations` VALUES (11, 1, 'Mirpur DOHS', '{\"lat\":23.838647790682,\"lng\":90.368693768978,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.363879203796,23.829359284959],[90.372333526611,23.829477055695],[90.376710891724,23.830262191202],[90.377140045166,23.831125834771],[90.376882553101,23.834423330052],[90.377140045166,23.837877758998],[90.377054214478,23.839918969213],[90.375080108643,23.841724628387],[90.375680923462,23.844393817623],[90.373674631119,23.845551757626],[90.372456908226,23.845939370825],[90.370166301727,23.846631183907],[90.363321304322,23.847484905599],[90.36024749279,23.847936296405],[90.363205969334,23.846964822938],[90.362838506699,23.843147548605],[90.362505912781,23.839840461722],[90.36340713501,23.837328196911],[90.363879203796,23.829359284959]]]},\"center\":{\"lat\":23.838647790682,\"lng\":90.368693768978}}', 1, 1, 4, 'IT - Abu Naser Md. Shoaib', 6, 'IT - Hasan Hafiz Pasha', '2016-12-25 06:10:28', '2020-05-16 16:14:38');
INSERT INTO `locations` VALUES (12, 1, 'Mohakhali DOHS', '{\"lat\":23.78108299941,\"lng\":90.395481034393,\"radius\":\".7\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.39882,23.78569],[90.39688,23.7857],[90.39406,23.7848],[90.3921,23.78453],[90.39167,23.78359],[90.39217,23.78348],[90.39218,23.78294],[90.39226,23.78237],[90.39328,23.78153],[90.3931,23.78062],[90.39267,23.77959],[90.39263,23.77842],[90.3931,23.77815],[90.39391,23.77791],[90.394438017197,23.77646599882],[90.396751543655,23.777691818665],[90.397306496048,23.777989122448],[90.397427021179,23.778489892786],[90.398396238098,23.781400364287],[90.399292068787,23.785631094787],[90.39882,23.78569]]]},\"center\":{\"lat\":23.78108299941,\"lng\":90.395481034393}}', 1, 1, 4, 'IT - Abu Naser Md. Shoaib', 426, 'IT - Sakib Uddin Ahmad', '2016-12-25 06:10:56', '2020-05-16 15:33:47');
INSERT INTO `locations` VALUES (13, 1, 'Baridhara DOHS', '{\"lat\":23.81263,\"lng\":90.413355,\"radius\":\".8\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.41269,23.81618],[90.41066,23.8149],[90.41051,23.81334],[90.41061,23.81232],[90.41172,23.81124],[90.4119,23.81009],[90.41271,23.80934],[90.41318,23.80927],[90.41338,23.80908],[90.41401,23.8093],[90.4162,23.81149],[90.41579,23.81483],[90.41517,23.81581],[90.41409,23.81614],[90.41269,23.81618]]]},\"center\":{\"lat\":23.81263,\"lng\":90.413355}}', 1, 1, 4, 'IT - Abu Naser Md. Shoaib', 154, 'IT - S. A. T. M. Marzan ', '2016-12-25 06:11:27', '2020-05-16 15:40:03');
INSERT INTO `locations` VALUES (14, 3, 'Rest Of Dhaka', '{\"lat\":22.363405563823,\"lng\":91.705799102783,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.689834594727,22.347535530433],[91.69017791748,22.328799567553],[91.721763610839,22.322765416581],[91.695327758789,22.331975331658],[91.693267822266,22.404045711065],[91.689834594727,22.347535530433]]]},\"center\":{\"lat\":22.363405563823,\"lng\":91.705799102783}}', 0, 0, 1, 'IT - Shafiqul Islam', 65, 'SD - Mehedi Hasan', '2016-12-25 06:11:45', '2020-03-26 23:30:22');
INSERT INTO `locations` VALUES (15, 1, 'Bashundhara R/A', '{\"lat\":23.822148706625,\"lng\":90.45567167638,\"radius\":\"2.1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.438305480986,23.828435270154],[90.448796360581,23.83064445145],[90.45745260921,23.83262787366],[90.476527988343,23.83701109468],[90.47787,23.83357],[90.48093,23.83142],[90.48178,23.83079],[90.48289,23.83052],[90.48423,23.83092],[90.48509,23.83206],[90.48613,23.8317],[90.48614,23.83006],[90.48687,23.82806],[90.48629,23.82677],[90.48716,23.82589],[90.48742,23.82501],[90.48638,23.82365],[90.48594,23.82166],[90.48565,23.81713],[90.48444,23.81524],[90.48383,23.81303],[90.47898,23.81094],[90.47356,23.8099],[90.47182,23.81097],[90.4632,23.81057],[90.45689,23.81457],[90.45398,23.8111],[90.439521720238,23.809348957287],[90.439377958913,23.810285639619],[90.435720889261,23.808698404617],[90.431670014427,23.807357790808],[90.428330204282,23.80728631857],[90.423923352761,23.825422824573],[90.431096647239,23.826926142452],[90.434688658896,23.827678714955],[90.438280670552,23.8284306697],[90.438305480986,23.828435270154]]]},\"center\":{\"lat\":23.822148706625,\"lng\":90.45567167638}}', 1, 1, 7, 'IT - Shah Newaz', 15, 'IT - Quaviur Rahman', '2017-01-01 23:10:59', '2019-07-01 17:47:12');
INSERT INTO `locations` VALUES (16, 3, 'CTG', '{\"lat\":22.313380976779,\"lng\":91.647434234619,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.626663208008,22.331340184622],[91.671981811523,22.248428704384],[91.687431335449,22.281154434764],[91.680564880371,22.332292904092],[91.607437133789,22.378333249173],[91.626663208008,22.331340184622]]]},\"center\":{\"lat\":22.313380976779,\"lng\":91.647434234619}}', 0, 0, 7, 'IT - Shah Newaz', 6, 'IT - Hasan Hafiz Pasha', '2017-01-02 01:55:29', '2020-04-08 13:30:14');
INSERT INTO `locations` VALUES (17, 1, 'Khilkhet', '{\"lat\":23.831340110168,\"lng\":90.426432931213,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.43024,23.83753],[90.427994576721,23.837717390473],[90.423445,23.837013138275],[90.419045626984,23.836348137074],[90.419011082001,23.833939904005],[90.42015148941,23.827160394832],[90.42128094841,23.824962829863],[90.42211,23.82516],[90.42405,23.82553],[90.433854780426,23.82760443129],[90.433644373016,23.82894334337],[90.433621865082,23.829970743261],[90.432626034393,23.830880371767],[90.433156034393,23.832589627978],[90.43188040741,23.835010768498],[90.430497492065,23.837490745537],[90.43024,23.83753]]]},\"center\":{\"lat\":23.831340110168,\"lng\":90.426432931213}}', 1, 1, 152, 'PM - Muhammad Shamsul Alam (Tutul)', 154, 'IT - S. A. T. M. Marzan ', '2018-01-23 16:49:50', '2020-05-16 16:11:40');
INSERT INTO `locations` VALUES (18, 1, 'Nikunjo', '{\"lat\":23.832533272401,\"lng\":90.41664,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.418883730164,23.833933631528],[90.42003,23.8271],[90.41767,23.82671],[90.41688,23.82794],[90.41325,23.82816],[90.41392,23.83108],[90.41358,23.83385],[90.41426,23.83517],[90.415190626984,23.835576118275],[90.414597759247,23.836629939928],[90.413994162674,23.837676334007],[90.418262848549,23.838356544802],[90.418926645508,23.836426334706],[90.418883730164,23.833933631528]]]},\"center\":{\"lat\":23.832533272401,\"lng\":90.41664}}', 1, 1, 152, 'PM - Muhammad Shamsul Alam (Tutul)', 154, 'IT - S. A. T. M. Marzan ', '2018-01-23 16:50:09', '2020-05-16 16:09:24');
INSERT INTO `locations` VALUES (19, 1, 'Azimpur', '{\"lat\":\"23.728022131998518\",\"lng\":\"90.38345589416508\",\"radius\":\"1\"}', 1, 1, 152, 'PM - Muhammad Shamsul Alam (Tutul)', 6, 'IT - Hasan Hafiz Pasha', '2018-01-27 17:55:41', '2018-07-14 10:19:24');
INSERT INTO `locations` VALUES (20, 1, 'Badda', '{\"lat\":23.786347040464,\"lng\":90.430788070431,\"radius\":\"2.8\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.421649737434,23.808145704443],[90.421953381615,23.806653497076],[90.422397521496,23.804527977256],[90.422649591436,23.803312025673],[90.422923119049,23.802029802705],[90.424522299614,23.80229295198],[90.424509167004,23.801152343246],[90.42545210269,23.79894023046],[90.426173015971,23.796864765502],[90.426853696117,23.794911977907],[90.423027288361,23.794353388045],[90.421421749668,23.794131024895],[90.421329302902,23.793935927285],[90.420896301575,23.792988445944],[90.421425163021,23.792522072458],[90.42168557043,23.791739129971],[90.421752858791,23.79116970529],[90.421431226845,23.789778048687],[90.421050002308,23.787833039421],[90.420757699232,23.787168482881],[90.420419798603,23.78652846662],[90.420313778348,23.786248015573],[90.420234580183,23.785800666104],[90.420089594898,23.785065498327],[90.41925245369,23.783695781601],[90.418927089272,23.78245193176],[90.418781419296,23.781235410108],[90.418793999653,23.780448412183],[90.418777286196,23.779260212082],[90.418929551907,23.778182454799],[90.418842480812,23.777622371378],[90.418039565468,23.775569648783],[90.417386140861,23.773778452222],[90.417799459648,23.773538064166],[90.418153769836,23.773312403369],[90.41871192791,23.773285566348],[90.41868,23.77319],[90.418233688037,23.771845397541],[90.421669855733,23.768096539704],[90.422955398753,23.767705531771],[90.425384521467,23.766606684894],[90.427792186508,23.765436640554],[90.431454316463,23.763656948042],[90.431520884354,23.763807976349],[90.438533703041,23.762431807147],[90.438827870045,23.762405921367],[90.43762,23.76564],[90.43784,23.76643],[90.43861,23.76667],[90.43883,23.76701],[90.4388,23.76759],[90.43794,23.76808],[90.4376,23.76881],[90.43783,23.7706],[90.43924,23.77361],[90.43915,23.77561],[90.44008,23.77757],[90.4393,23.77791],[90.43928,23.77844],[90.44052,23.77933],[90.44001,23.7804],[90.44009,23.782],[90.43932,23.78301],[90.44005,23.78503],[90.44099,23.78592],[90.44298,23.78736],[90.4438,23.78752],[90.44419,23.78828],[90.4439,23.78896],[90.44354,23.79061],[90.44329,23.79115],[90.44288,23.79194],[90.44291,23.79274],[90.4419,23.79379],[90.44093,23.7953],[90.44079,23.79597],[90.44018,23.80531],[90.439378994172,23.810288159561],[90.43572,23.8087],[90.43167,23.80736],[90.428331440938,23.80728809807],[90.428348211958,23.807221659751],[90.422274021583,23.808067791216],[90.421848220901,23.808126072768],[90.421649737434,23.808145704443]]]},\"center\":{\"lat\":23.786347040464,\"lng\":90.430788070431}}', 1, 1, 152, 'PM - Muhammad Shamsul Alam (Tutul)', 154, 'IT - S. A. T. M. Marzan ', '2018-01-27 23:56:09', '2020-05-16 17:25:33');
INSERT INTO `locations` VALUES (21, 1, 'Banani DOHS', '{\"lat\":23.79409,\"lng\":90.39876,\"radius\":\".4\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.40072,23.79683],[90.39799,23.79614],[90.3974,23.79551],[90.3968,23.79164],[90.39983,23.79135],[90.40072,23.79683]]]},\"center\":{\"lat\":23.79409,\"lng\":90.39876}}', 1, 1, 152, 'PM - Muhammad Shamsul Alam (Tutul)', 154, 'IT - S. A. T. M. Marzan ', '2018-01-27 23:56:46', '2020-05-16 17:03:25');
INSERT INTO `locations` VALUES (22, 1, 'Baridhara', '{\"lat\":23.80169,\"lng\":90.421405,\"radius\":\".8\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.42104,23.8092],[90.41598,23.80657],[90.41699,23.80586],[90.41779,23.80449],[90.4183,23.80305],[90.4181,23.80014],[90.41868,23.79729],[90.42168,23.79418],[90.42683,23.79492],[90.42544,23.79894],[90.42449,23.80115],[90.4245,23.80227],[90.4229,23.80201],[90.42154,23.80857],[90.42104,23.8092]]]},\"center\":{\"lat\":23.80169,\"lng\":90.421405}}', 1, 1, 152, 'PM - Muhammad Shamsul Alam (Tutul)', 154, 'IT - S. A. T. M. Marzan ', '2018-01-27 23:57:29', '2020-05-16 17:16:29');
INSERT INTO `locations` VALUES (23, 1, 'Bashabo', '{\"lat\":23.742285,\"lng\":90.430759431877,\"radius\":\"0.6\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.42684,23.74355],[90.43082,23.74438],[90.43167,23.74396],[90.43388,23.74463],[90.4341,23.74348],[90.43499,23.74089],[90.43356,23.74024],[90.42761,23.73994],[90.427221457672,23.74053],[90.427057550926,23.740953941756],[90.426850728836,23.741358240376],[90.426528863754,23.743471434271],[90.42684,23.74355]]]},\"center\":{\"lat\":23.742285,\"lng\":90.430759431877}}', 1, 1, 152, 'PM - Muhammad Shamsul Alam (Tutul)', 154, 'IT - S. A. T. M. Marzan ', '2018-01-27 23:57:59', '2020-05-16 17:38:11');
INSERT INTO `locations` VALUES (24, 1, 'Jatrabari', '{\"lat\":23.71165,\"lng\":90.43389,\"radius\":\"2.1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.42644,23.71011],[90.4289,23.70912],[90.43034,23.70894],[90.43146,23.7091],[90.43208,23.708],[90.43324,23.70809],[90.43384,23.70783],[90.43779,23.7082],[90.43846,23.70756],[90.43962,23.70816],[90.44089,23.70948],[90.44134,23.71066],[90.44094,23.71307],[90.44049,23.71447],[90.44046,23.71574],[90.43527,23.71408],[90.43367,23.71451],[90.43207,23.71459],[90.43124,23.71415],[90.43061,23.71258],[90.42644,23.71011]]]},\"center\":{\"lat\":23.71165,\"lng\":90.43389}}', 1, 1, 152, 'PM - Muhammad Shamsul Alam (Tutul)', 154, 'IT - S. A. T. M. Marzan ', '2018-01-27 23:58:36', '2020-05-16 17:41:57');
INSERT INTO `locations` VALUES (25, 1, 'Kakrail', '{\"lat\":23.738345,\"lng\":90.407135,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.4106,23.74015],[90.40776,23.74028],[90.40582,23.73995],[90.40555,23.73885],[90.40413,23.73844],[90.40404,23.73757],[90.40341,23.73765],[90.40332,23.73641],[90.40368,23.73641],[90.40431,23.73727],[90.40622,23.73743],[90.40785,23.73745],[90.40886,23.73736],[90.40982,23.73705],[90.40982,23.73647],[90.4103,23.73644],[90.41095,23.73712],[90.40882,23.73767],[90.4106,23.74015]]]},\"center\":{\"lat\":23.738345,\"lng\":90.407135}}', 1, 1, 152, 'PM - Muhammad Shamsul Alam (Tutul)', 154, 'IT - S. A. T. M. Marzan ', '2018-01-27 23:59:06', '2020-05-16 17:56:14');
INSERT INTO `locations` VALUES (26, 1, 'Kafrul', '{\"lat\":23.790956012592,\"lng\":90.391135,\"radius\":\"2.5\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.383736647239,23.765515955967],[90.37749,23.78626],[90.38795,23.79123],[90.3873,23.7982],[90.38858,23.79809],[90.38757,23.80473],[90.393384023314,23.811997852849],[90.395731676381,23.812256319178],[90.394592011657,23.814723680752],[90.395066982515,23.815963373991],[90.396451341104,23.816153865481],[90.396982682209,23.81608447893],[90.398047376075,23.817132025185],[90.40009,23.81637],[90.40248,23.81634],[90.40478,23.81617],[90.400767860964,23.796895745685],[90.397957128742,23.79621072004],[90.397324238827,23.795554069021],[90.396718115296,23.791593470687],[90.399794636889,23.79130168005],[90.398858573796,23.785747799756],[90.396917545771,23.785776012505],[90.393947440784,23.784870317214],[90.392084188514,23.784619917722],[90.391587063628,23.783539874534],[90.39205193556,23.783441495243],[90.392187745555,23.782321867672],[90.392672242722,23.781923842064],[90.393188926398,23.781486543841],[90.393056249707,23.78066022335],[90.392608506113,23.779626101532],[90.392611373633,23.779048510911],[90.392560596973,23.77841200924],[90.393129966528,23.77805542832],[90.393859656644,23.777814179694],[90.394326825552,23.776424816076],[90.39324971262,23.776130791314],[90.391786361589,23.775738583077],[90.391022449074,23.775547989342],[90.38868,23.76478],[90.383740335276,23.765507364293],[90.383736647239,23.765515955967]]]},\"center\":{\"lat\":23.790956012592,\"lng\":90.391135}}', 1, 1, 152, 'PM - Muhammad Shamsul Alam (Tutul)', 154, 'IT - S. A. T. M. Marzan ', '2018-01-27 23:59:24', '2020-05-16 17:05:39');
INSERT INTO `locations` VALUES (27, 1, 'Kamalapur', '{\"lat\":\"23.72750453566442\",\"lng\":\"90.42999585399775\",\"radius\":\"1.1\"}', 1, 1, 152, 'PM - Muhammad Shamsul Alam (Tutul)', 154, 'IT - S. A. T. M. Marzan ', '2018-01-28 00:00:27', '2018-04-04 21:32:19');
INSERT INTO `locations` VALUES (28, 3, 'Kamrangirchor', '{\"lat\":\"23.720618097719033\",\"lng\":\"90.37004851324468\",\"radius\":\"1.2\"}', 0, 0, 152, 'PM - Muhammad Shamsul Alam (Tutul)', 129, 'PM - Zinnatun Nesha', '2018-01-28 00:01:06', '2018-05-13 11:01:40');
INSERT INTO `locations` VALUES (29, 1, 'Khilgaon', '{\"lat\":23.752350958063,\"lng\":90.450325,\"radius\":\"3\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.43152,23.76381],[90.438534687953,23.762434115661],[90.438825748458,23.76240714885],[90.438786267528,23.762516460887],[90.44568,23.76245],[90.44615,23.7607],[90.45457,23.76017],[90.45707,23.75962],[90.46035,23.76001],[90.46326,23.76019],[90.46514,23.76178],[90.46919,23.76097],[90.47375,23.76046],[90.47978,23.75989],[90.48112,23.76097],[90.48237,23.76127],[90.4825,23.75942],[90.48306,23.75765],[90.48615,23.75638],[90.48769,23.75512],[90.48789,23.75416],[90.48707,23.75296],[90.4856,23.75144],[90.48464,23.75009],[90.48532,23.74877],[90.48642,23.74761],[90.48649,23.74701],[90.47206,23.74631],[90.46183,23.74491],[90.4577,23.74574],[90.45383,23.74524],[90.4494,23.74449],[90.444106311963,23.744988465531],[90.435046134226,23.740891916126],[90.434139267705,23.743491900172],[90.433931912981,23.744677545986],[90.431671739572,23.743993682739],[90.430809873768,23.744417762546],[90.426505831049,23.743549527958],[90.426182186508,23.745499820567],[90.42469,23.74766],[90.42282,23.74847],[90.41924,23.74928],[90.416606124992,23.749483652209],[90.416590085983,23.749107908037],[90.41276,23.7498],[90.416967969589,23.757023718938],[90.417905193462,23.758557375783],[90.417940628643,23.758623327054],[90.41796600554,23.758669639147],[90.419693381615,23.759122093964],[90.425490874546,23.756080996919],[90.431336647239,23.753003003833],[90.428653090773,23.75848357288],[90.429187726355,23.758796208519],[90.431388525305,23.763690441854],[90.431454095014,23.763659239981],[90.43152,23.76381]]]},\"center\":{\"lat\":23.752350958063,\"lng\":90.450325}}', 1, 1, 152, 'PM - Muhammad Shamsul Alam (Tutul)', 15, 'IT - Quaviur Rahman', '2018-01-28 00:01:27', '2020-05-16 17:34:56');
INSERT INTO `locations` VALUES (30, 1, 'Lalbagh', '{\"lat\":\"23.719934000307973\",\"lng\":\"90.39599512333984\",\"radius\":\"1.5\"}', 1, 1, 152, 'PM - Muhammad Shamsul Alam (Tutul)', 154, 'IT - S. A. T. M. Marzan ', '2018-01-27 18:01:44', '2018-04-04 15:29:28');
INSERT INTO `locations` VALUES (31, 1, 'Mogbazar', '{\"lat\":23.754008071259,\"lng\":90.409585627995,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.403447320099,23.756173332767],[90.40297648941,23.755193747497],[90.402441285706,23.753546399335],[90.40135230423,23.752967761915],[90.40371,23.74875],[90.40556,23.74839],[90.40705,23.74778],[90.40628,23.74633],[90.40873,23.74634],[90.409463279762,23.746855809124],[90.410121457672,23.747376526417],[90.410666473541,23.747173211187],[90.410879972877,23.746884964814],[90.411238850193,23.746568983004],[90.411683558197,23.746287372207],[90.411758609123,23.746732539163],[90.411962406082,23.749878297495],[90.41272405159,23.749811135354],[90.414377025795,23.752651439809],[90.415476876335,23.754566085087],[90.417818951759,23.758514380701],[90.414131421318,23.758235284977],[90.413557375679,23.760169171073],[90.412817033081,23.761121093348],[90.410671160049,23.761728770311],[90.408664656067,23.761137361118],[90.405874735413,23.759168974223],[90.404241253967,23.757548092073],[90.403447320099,23.756173332767]]]},\"center\":{\"lat\":23.754008071259,\"lng\":90.409585627995}}', 1, 1, 152, 'PM - Muhammad Shamsul Alam (Tutul)', 154, 'IT - S. A. T. M. Marzan ', '2018-01-28 00:02:18', '2020-05-16 18:17:46');
INSERT INTO `locations` VALUES (32, 1, 'Mohakhali', '{\"lat\":23.7783,\"lng\":90.404795431214,\"radius\":\"1.5\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.39919,23.78564],[90.398888597221,23.783516224157],[90.39845844841,23.78139241362],[90.397490862427,23.778381773088],[90.398044780426,23.77872254106],[90.39911,23.77425],[90.40102,23.77158],[90.40614,23.77096],[90.40772,23.77352],[90.41072,23.7737],[90.41182,23.77517],[90.4121,23.7764],[90.41069,23.77793],[90.41102,23.77873],[90.41201,23.77898],[90.41199,23.7803],[90.40801,23.78286],[90.4062,23.78441],[90.40486,23.78458],[90.40415,23.78552],[90.403555,23.785046003989],[90.402917084656,23.784748723249],[90.402066210976,23.784151398382],[90.399627469559,23.784879452787],[90.399528970222,23.785252300369],[90.399548488083,23.785517153918],[90.399286559525,23.785581094764],[90.39919,23.78564]]]},\"center\":{\"lat\":23.7783,\"lng\":90.404795431214}}', 1, 1, 152, 'PM - Muhammad Shamsul Alam (Tutul)', 154, 'IT - S. A. T. M. Marzan ', '2018-01-28 00:02:44', '2020-05-16 18:30:55');
INSERT INTO `locations` VALUES (33, 1, 'Rampura', '{\"lat\":23.76290404747,\"lng\":90.421060437417,\"radius\":\".8\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.410811005828,23.761735397184],[90.412848017197,23.761168520099],[90.413618498471,23.760219386284],[90.414158134341,23.758278013775],[90.415649329448,23.758389278382],[90.417907578049,23.758558882067],[90.417967974782,23.758668429276],[90.41969,23.75911],[90.43134,23.753],[90.428656647239,23.758482761759],[90.42919,23.75879],[90.43139,23.76369],[90.425888548098,23.766368368191],[90.425016588955,23.766776066284],[90.422955918403,23.767707419547],[90.421672346933,23.768097852124],[90.419599238271,23.770421337757],[90.418522450804,23.771558518513],[90.418253619077,23.771871822422],[90.41790163887,23.772784055928],[90.417145808182,23.772808094941],[90.416572367706,23.772422210758],[90.41529350626,23.772354201056],[90.413746423912,23.77162589247],[90.413854812307,23.771010750175],[90.413762551105,23.770860032476],[90.413595858603,23.770641197601],[90.413342664859,23.770651260273],[90.41321237995,23.770657825776],[90.413110021846,23.770659574361],[90.413104223266,23.770639844607],[90.413791586429,23.770206162595],[90.413870088146,23.769865757087],[90.413584527092,23.769096574354],[90.413692426567,23.768970750654],[90.413711813145,23.768781104584],[90.413247030411,23.768231105476],[90.412592555542,23.76800375189],[90.41200245369,23.767584929495],[90.411310194759,23.766251961646],[90.411293852501,23.765419753347],[90.412218716602,23.76537056737],[90.411990771828,23.765142701105],[90.411403411045,23.763810180815],[90.410730874834,23.761757183846],[90.410811005828,23.761735397184]]]},\"center\":{\"lat\":23.76290404747,\"lng\":90.421060437417}}', 1, 1, 152, 'PM - Muhammad Shamsul Alam (Tutul)', 154, 'IT - S. A. T. M. Marzan ', '2018-01-28 00:03:24', '2019-07-01 17:47:14');
INSERT INTO `locations` VALUES (34, 1, 'Shahbag', '{\"lat\":23.740370178881,\"lng\":90.394190568123,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.39559,23.74251],[90.39415,23.74259],[90.39368,23.74248],[90.39303,23.742],[90.39291,23.74076],[90.392381457672,23.738641789205],[90.394614357147,23.738184919857],[90.395999678574,23.738150357761],[90.395982915344,23.741122328412],[90.395984941139,23.742007672369],[90.395793847885,23.742470716828],[90.39559,23.74251]]]},\"center\":{\"lat\":23.740370178881,\"lng\":90.394190568123}}', 1, 1, 152, 'PM - Muhammad Shamsul Alam (Tutul)', 154, 'IT - S. A. T. M. Marzan ', '2018-01-28 00:03:45', '2020-05-18 11:00:57');
INSERT INTO `locations` VALUES (35, 1, 'Laxmibazar', '{\"lat\":23.70983,\"lng\":90.41482,\"radius\":\"1.2\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.41789,23.71036],[90.41442,23.71266],[90.41349,23.71371],[90.41316,23.71381],[90.41158,23.71383],[90.41162,23.71254],[90.41182,23.7106],[90.41206,23.70997],[90.41263,23.7096],[90.41266,23.7061],[90.41339,23.70583],[90.41436,23.70587],[90.41503,23.7061],[90.41806,23.70584],[90.41794,23.70753],[90.41772,23.70886],[90.41748,23.70949],[90.41789,23.71036]]]},\"center\":{\"lat\":23.70983,\"lng\":90.41482}}', 1, 1, 152, 'PM - Muhammad Shamsul Alam (Tutul)', 154, 'IT - S. A. T. M. Marzan ', '2018-01-28 00:05:24', '2020-05-16 18:34:31');
INSERT INTO `locations` VALUES (36, 1, 'Wari', '{\"lat\":23.718305,\"lng\":90.417855,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.41638,23.7217],[90.41596,23.71548],[90.419,23.71491],[90.41975,23.71721],[90.41899,23.71867],[90.41803,23.72032],[90.41723,23.72109],[90.41638,23.7217]]]},\"center\":{\"lat\":23.718305,\"lng\":90.417855}}', 1, 1, 152, 'PM - Muhammad Shamsul Alam (Tutul)', 154, 'IT - S. A. T. M. Marzan ', '2018-01-28 00:05:40', '2020-05-16 18:34:31');
INSERT INTO `locations` VALUES (37, 3, 'Gazipur Sadar', '{\"lat\":\"24.0245111\",\"lng\":\"90.39367219999997\",\"radius\":\"4\"}', 0, 0, 129, 'PM - Zinnatun Nesha', 17, 'IT - Firoze Ahmed', '2018-04-16 17:15:52', '2018-04-16 17:33:21');
INSERT INTO `locations` VALUES (38, 1, 'Kaliganj', '{\"lat\":\"\",\"lng\":\"\",\"radius\":\"1\"}', 1, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-05-14 10:06:06', '2018-05-14 10:06:06');
INSERT INTO `locations` VALUES (39, 3, 'Tongi', '{\"lat\":\"NaN\",\"lng\":\"NaN\",\"radius\":\"1\"}', 0, 0, 129, 'PM - Zinnatun Nesha', 129, 'PM - Zinnatun Nesha', '2018-06-11 15:13:13', '2018-06-11 15:13:35');
INSERT INTO `locations` VALUES (40, 1, 'Joydebpur', '{\"lat\":23.991155510557,\"lng\":90.396561920643,\"radius\":\"2\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.343666076661,24.025220748483],[90.341654419899,24.024762627698],[90.34238934517,24.021168650243],[90.345919132233,24.017900419921],[90.344057679177,24.015893871542],[90.343140363694,24.013730488212],[90.345425605774,24.010579674575],[90.344685316086,24.00088174051],[90.345060825348,23.999494861498],[90.348123908043,23.999938370369],[90.349727869034,23.999048900759],[90.348756909371,23.997100867329],[90.346584320069,23.995858521178],[90.362505912781,23.986634889997],[90.372934341431,23.977410598087],[90.382804870605,23.976528325142],[90.396795272827,23.971626698756],[90.402889251709,23.952292866692],[90.432028770447,23.945880296445],[90.440225601196,23.956724609669],[90.447692871094,23.961313068017],[90.451469421387,23.995662489131],[90.442070960999,24.006953448266],[90.432715415955,24.007129861641],[90.430279970169,24.008085429886],[90.42828977108,24.007171514764],[90.426299571991,24.007198466777],[90.426417589188,24.005782253336],[90.425017476082,24.005348565479],[90.423488616944,24.005463725674],[90.422201156616,24.006120381838],[90.420763492584,24.006149784274],[90.420162677765,24.007218068238],[90.419411659241,24.008031526225],[90.418311953545,24.008874380881],[90.41712641716,24.009717230015],[90.416461229325,24.009310506995],[90.414165258408,24.010765883437],[90.414527356625,24.011334921347],[90.414138436318,24.011727549912],[90.414390563965,24.012689209197],[90.414036512375,24.013416880361],[90.413280129433,24.014512054452],[90.412395000458,24.015862021315],[90.412486195564,24.017358973443],[90.412920713425,24.018855908144],[90.412861704827,24.020117631713],[90.412244796753,24.022124114178],[90.386281013489,24.028728870632],[90.363686084748,24.034774767022],[90.357023477555,24.03450530444],[90.344352722168,24.036430724668],[90.344502925873,24.033765504261],[90.345253944397,24.030865054529],[90.345060825348,24.026631848234],[90.343666076661,24.025220748483]]]},\"center\":{\"lat\":23.991155510557,\"lng\":90.396561920643}}', 1, 1, 177, 'PM - Syed Mohammad Toaha', 15, 'IT - Quaviur Rahman', '2018-06-27 08:09:23', '2019-12-10 11:20:51');
INSERT INTO `locations` VALUES (41, 1, 'Tongi', '{\"lat\":23.928945656537,\"lng\":90.430912971496,\"radius\":\"2\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.397481918335,23.905769992322],[90.392847061157,23.879637659818],[90.412759780884,23.882463031363],[90.416160821915,23.885111761162],[90.422995090485,23.887289565043],[90.438723564148,23.899178550574],[90.451823472977,23.894254367371],[90.457370281219,23.894195511458],[90.458169579506,23.883689301899],[90.460513830185,23.883071263002],[90.469754040241,23.888216592058],[90.472829192877,23.89275108086],[90.474702715874,23.897285410693],[90.48168182373,23.899727850222],[90.478464514017,23.90749013657],[90.466320812702,23.920587248119],[90.455378741026,23.925367001451],[90.453363060951,23.930146577881],[90.432082414627,23.946439202863],[90.403254032135,23.952351696136],[90.396580696106,23.972332544448],[90.38031578064,23.978253653256],[90.380144119263,23.949233698631],[90.397481918335,23.905769992322]]]},\"center\":{\"lat\":23.928945656537,\"lng\":90.430912971496}}', 1, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-06-27 08:09:57', '2019-07-01 17:47:14');
INSERT INTO `locations` VALUES (43, 3, 'Chittagong', '{\"lat\":\"22.35585575222634\",\"lng\":\"91.85625492089844\",\"radius\":\"10\"}', 0, 0, 3, 'IT - Fahim Razzaq Ishraq', 3, 'IT - Fahim Razzaq Ishraq', '2018-10-18 09:57:20', '2018-10-18 09:59:25');
INSERT INTO `locations` VALUES (44, 3, 'Barishal', '{\"lat\":\"22.7010021\",\"lng\":\"90.35345110000003\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 17:24:37', '2018-10-25 17:24:37');
INSERT INTO `locations` VALUES (45, 3, 'Barguna', '{\"lat\":\"22.0952915\",\"lng\":\"90.11206960000004\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 17:37:54', '2018-10-25 17:37:54');
INSERT INTO `locations` VALUES (46, 3, 'Bhola', '{\"lat\":\"22.1785315\",\"lng\":\"90.71010230000002\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 17:39:59', '2018-10-25 17:39:59');
INSERT INTO `locations` VALUES (47, 3, 'Jhalokati', '{\"lat\":\"22.57208\",\"lng\":\"90.18696439999997\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 17:42:17', '2018-10-25 17:42:17');
INSERT INTO `locations` VALUES (48, 3, 'Pirojpur', '{\"lat\":\"22.5790744\",\"lng\":\"89.97592639999993\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 17:45:05', '2018-10-25 17:45:05');
INSERT INTO `locations` VALUES (49, 3, 'Brahmanbaria', '{\"lat\":\"23.9608181\",\"lng\":\"91.11150139999995\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 17:46:46', '2018-10-25 17:46:46');
INSERT INTO `locations` VALUES (50, 3, 'Chittagong', '{\"lat\":\"22.356851\",\"lng\":\"91.78318190000005\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 237, 'PM - Rakebul Hasan', '2018-10-25 17:47:05', '2018-11-04 11:42:08');
INSERT INTO `locations` VALUES (51, 3, 'Bandarban', '{\"lat\":\"21.8311002\",\"lng\":\"92.36863210000001\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 17:47:52', '2018-10-25 17:47:52');
INSERT INTO `locations` VALUES (52, 3, 'Chandpur', '{\"lat\":\"23.2320991\",\"lng\":\"90.66307499999994\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 17:47:54', '2018-10-25 17:47:54');
INSERT INTO `locations` VALUES (53, 3, 'Coxs Bazar', '{\"lat\":\"21.4272283\",\"lng\":\"92.00580739999998\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 17:49:35', '2018-10-25 17:49:35');
INSERT INTO `locations` VALUES (54, 3, 'Comilla', '{\"lat\":23.42271,\"lng\":91.154865,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.14342,23.48711],[91.15148,23.48137],[91.1611,23.48554],[91.16745,23.48484],[91.17329,23.48587],[91.1896,23.47817],[91.20003,23.47149],[91.21596,23.46953],[91.24987,23.47033],[91.24748,23.45734],[91.25575,23.44466],[91.24566,23.42923],[91.23924,23.42041],[91.22098,23.416],[91.21742,23.40308],[91.14482,23.35624],[91.05398,23.37592],[91.06121,23.48259],[91.12197,23.48918],[91.14342,23.48711]]]},\"center\":{\"lat\":23.42271,\"lng\":91.154865}}', 1, 1, 16, 'PM - Md. Masud Reza', 17, 'IT - Firoze Ahmed', '2018-10-25 11:49:47', '2019-07-01 17:47:15');
INSERT INTO `locations` VALUES (55, 3, 'Feni', '{\"lat\":\"23.0159132\",\"lng\":\"91.39758310000002\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 17:50:59', '2018-10-25 17:50:59');
INSERT INTO `locations` VALUES (56, 3, 'Khagrachhari', '{\"lat\":\"23.1321751\",\"lng\":\"91.94902100000002\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 17:51:00', '2018-10-25 17:51:00');
INSERT INTO `locations` VALUES (57, 3, 'Lakshmipur', '{\"lat\":\"22.9446744\",\"lng\":\"90.82819070000005\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 17:52:09', '2018-10-25 17:52:09');
INSERT INTO `locations` VALUES (58, 3, 'Noakhali', '{\"lat\":\"22.8723789\",\"lng\":\"91.09731839999995\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 17:52:20', '2018-10-25 17:52:20');
INSERT INTO `locations` VALUES (59, 3, 'Rangamati', '{\"lat\":\"22.7324173\",\"lng\":\"92.29851340000005\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 17:53:47', '2018-10-25 17:53:47');
INSERT INTO `locations` VALUES (60, 3, 'Faridpur', '{\"lat\":\"23.5423919\",\"lng\":\"89.63089209999998\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 17:54:13', '2018-10-25 17:54:13');
INSERT INTO `locations` VALUES (61, 3, 'Gopalganj', '{\"lat\":\"23.0488146\",\"lng\":\"89.88793039999996\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 17:55:30', '2018-10-25 17:55:30');
INSERT INTO `locations` VALUES (62, 3, 'Gazipur', '{\"lat\":24.073721857472,\"lng\":90.443795433807,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.339838137207,23.858320845675],[90.340009989013,23.867004945301],[90.33778401001,23.876628455965],[90.337611983337,23.880428683973],[90.337203065338,23.885353236831],[90.338715541534,23.892702942428],[90.341253612976,23.89922813441],[90.34220652832,23.903431833716],[90.339262485962,23.916086639445],[90.334548507079,23.923136347567],[90.330311644592,23.929127085623],[90.324543184204,23.939371135014],[90.322588422241,23.942090617747],[90.31777807167,23.9479710678],[90.299363556976,23.963067424082],[90.278499035644,23.975688821435],[90.264110485229,23.978837660063],[90.234155262756,23.987274478948],[90.208548188476,23.996917231864],[90.192881392059,24.002760232703],[90.176012966003,24.008132537406],[90.187182336426,24.055794908951],[90.196368569336,24.084791963276],[90.253890146484,24.122476151712],[90.272346315918,24.162887296067],[90.309685046387,24.190452183902],[90.388828676758,24.282494715557],[90.510751228027,24.295952229208],[90.634047070312,24.279368061934],[90.711577901611,24.198129218623],[90.657959440918,24.017779966281],[90.61497730835,23.938730342875],[90.56135217041,23.919588963596],[90.452499770126,23.894590353017],[90.445452110024,23.898020082503],[90.438919434052,23.899723365832],[90.430412652245,23.894167931282],[90.422506685257,23.887592040632],[90.416188586006,23.885214564762],[90.412102084656,23.882366155592],[90.392799661255,23.879983863176],[90.391838806763,23.888230046811],[90.38438345089,23.897677941423],[90.380039457474,23.89849351825],[90.3616096875,23.892728555966],[90.35925408432,23.888633859335],[90.359065706024,23.882027635055],[90.352187784042,23.874837943325],[90.351747163696,23.867176907382],[90.351187803955,23.865455773539],[90.350542613526,23.864676529885],[90.349343020935,23.862957002587],[90.347793523559,23.859748728448],[90.347063491822,23.858537702308],[90.346076158447,23.857243936047],[90.344644655762,23.855195230009],[90.3413830896,23.851491485736],[90.339838137207,23.858320845675]]],\"center\":{\"lat\":23.9999405,\"lng\":90.4202724}},\"center\":{\"lat\":24.073721857472,\"lng\":90.443795433807}}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 15, 'IT - Quaviur Rahman', '2018-10-25 11:56:21', '2020-03-09 14:36:19');
INSERT INTO `locations` VALUES (63, 3, 'Jamalpur', '{\"lat\":\"25.0830926\",\"lng\":\"89.78532180000002\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 17:59:10', '2018-10-25 17:59:10');
INSERT INTO `locations` VALUES (64, 3, 'Kishoreganj', '{\"lat\":\"24.4260457\",\"lng\":\"90.98206679999998\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 18:01:13', '2018-10-25 18:01:13');
INSERT INTO `locations` VALUES (65, 3, 'Manikganj', '{\"lat\":\"23.8616512\",\"lng\":\"90.00032280000005\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 18:03:02', '2018-10-25 18:03:02');
INSERT INTO `locations` VALUES (66, 3, 'Madaripur', '{\"lat\":\"23.2393346\",\"lng\":\"90.18696439999997\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 18:03:06', '2018-10-25 18:03:06');
INSERT INTO `locations` VALUES (67, 3, 'Munshiganj', '{\"lat\":\"23.4980931\",\"lng\":\"90.41266210000003\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 18:05:05', '2018-10-25 18:05:05');
INSERT INTO `locations` VALUES (68, 3, 'Mymenshingh', '{\"lat\":\"24.7851062\",\"lng\":\"90.3560076\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 18:15:19', '2018-10-25 18:15:19');
INSERT INTO `locations` VALUES (69, 1, 'Narayanganj', '{\"lat\":\"23.6237764\",\"lng\":\"90.50004039999999\",\"radius\":\"1\"}', 1, 1, 16, 'PM - Md. Masud Reza', 237, 'PM - Rakebul Hasan', '2018-10-25 12:16:24', '2018-10-29 10:27:15');
INSERT INTO `locations` VALUES (70, 3, 'Narsingdi', '{\"lat\":\"24.134378\",\"lng\":\"90.78600570000003\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 18:17:37', '2018-10-25 18:17:37');
INSERT INTO `locations` VALUES (71, 3, 'Netrokona', '{\"lat\":\"24.8103284\",\"lng\":\"90.86564150000004\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 18:18:18', '2018-10-25 18:18:18');
INSERT INTO `locations` VALUES (72, 3, 'Rajbari', '{\"lat\":\"23.715134\",\"lng\":\"89.58748190000006\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 18:19:36', '2018-10-25 18:19:36');
INSERT INTO `locations` VALUES (73, 3, 'Shariatpur', '{\"lat\":\"23.2423214\",\"lng\":\"90.43477110000003\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 18:19:49', '2018-10-25 18:19:49');
INSERT INTO `locations` VALUES (74, 3, 'Sherpur', '{\"lat\":\"25.0746235\",\"lng\":\"90.14949039999999\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 18:20:28', '2018-10-25 18:20:28');
INSERT INTO `locations` VALUES (75, 3, 'Tangail', '{\"lat\":\"24.3917427\",\"lng\":\"89.99482569999998\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 18:20:46', '2018-10-25 18:20:46');
INSERT INTO `locations` VALUES (76, 3, 'Bagerhat', '{\"lat\":\"22.6602436\",\"lng\":\"89.78954780000004\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 18:21:38', '2018-10-25 18:21:38');
INSERT INTO `locations` VALUES (77, 3, 'Chuadanga', '{\"lat\":\"23.6160512\",\"lng\":\"88.82630059999997\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 18:21:49', '2018-10-25 18:21:49');
INSERT INTO `locations` VALUES (78, 5, 'Jessore', '{\"lat\":\"23.1634014\",\"lng\":\"89.21816639999997\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 12:22:42', '2018-10-25 12:22:42');
INSERT INTO `locations` VALUES (79, 3, 'Jhenaidah', '{\"lat\":\"23.5449873\",\"lng\":\"89.17260310000006\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 18:23:02', '2018-10-25 18:23:02');
INSERT INTO `locations` VALUES (80, 3, 'Kushtia', '{\"lat\":\"23.8906995\",\"lng\":\"89.10993680000001\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 18:24:08', '2018-10-25 18:24:08');
INSERT INTO `locations` VALUES (81, 3, 'Khulna', '{\"lat\":\"22.845641\",\"lng\":\"89.54032789999997\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 18:24:11', '2018-10-25 18:24:11');
INSERT INTO `locations` VALUES (82, 3, 'Magura', '{\"lat\":\"23.4289726\",\"lng\":\"89.43639099999996\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 18:25:14', '2018-10-25 18:25:14');
INSERT INTO `locations` VALUES (83, 3, 'Meherpur', '{\"lat\":\"23.8051991\",\"lng\":\"88.67235779999999\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 18:25:28', '2018-10-25 18:25:28');
INSERT INTO `locations` VALUES (84, 3, 'Narail', '{\"lat\":\"23.1162929\",\"lng\":\"89.58404040000005\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 18:26:13', '2018-10-25 18:26:13');
INSERT INTO `locations` VALUES (85, 3, 'Satkhira', '{\"lat\":\"22.3154812\",\"lng\":\"89.11145250000004\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 18:26:16', '2018-10-25 18:26:16');
INSERT INTO `locations` VALUES (86, 3, 'Bogra', '{\"lat\":\"24.848078\",\"lng\":\"89.37296330000004\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 18:27:06', '2018-10-25 18:27:06');
INSERT INTO `locations` VALUES (87, 3, 'Joypurhat', '{\"lat\":\"25.0947349\",\"lng\":\"89.09449370000004\",\"radius\":\"1\"}', 1, 1, 16, 'PM - Md. Masud Reza', 154, 'IT - S. A. T. M. Marzan ', '2018-10-25 18:28:23', '2020-02-20 11:04:04');
INSERT INTO `locations` VALUES (88, 3, 'Natore', '{\"lat\":\"24.410243\",\"lng\":\"89.00761769999997\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 18:30:08', '2018-10-25 18:30:08');
INSERT INTO `locations` VALUES (89, 3, 'Naogaon', '{\"lat\":\"24.9131597\",\"lng\":\"88.75309519999996\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 18:31:02', '2018-10-25 18:31:02');
INSERT INTO `locations` VALUES (90, 3, 'Pabna', '{\"lat\":\"24.0128563\",\"lng\":\"89.25905720000003\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 18:33:42', '2018-10-25 18:33:42');
INSERT INTO `locations` VALUES (91, 3, 'Nawabganj', '{\"lat\":\"24.7413111\",\"lng\":\"88.29120690000002\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 18:33:53', '2018-10-25 18:33:53');
INSERT INTO `locations` VALUES (92, 3, 'Rajshahi', '{\"lat\":\"24.3635886\",\"lng\":\"88.62413509999999\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 18:34:53', '2018-10-25 18:34:53');
INSERT INTO `locations` VALUES (93, 3, 'Sirajganj', '{\"lat\":\"24.3141115\",\"lng\":\"89.56996149999998\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 18:35:44', '2018-10-25 18:35:44');
INSERT INTO `locations` VALUES (94, 3, 'Dinajpur', '{\"lat\":\"25.6279123\",\"lng\":\"88.6331758\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 18:36:36', '2018-10-25 18:36:36');
INSERT INTO `locations` VALUES (95, 3, 'Gaibandha', '{\"lat\":\"25.3296928\",\"lng\":\"89.54296520000003\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 18:37:03', '2018-10-25 18:37:03');
INSERT INTO `locations` VALUES (96, 3, 'Kurigram', '{\"lat\":\"25.8072414\",\"lng\":\"89.62947459999998\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 18:38:13', '2018-10-25 18:38:13');
INSERT INTO `locations` VALUES (97, 3, 'Lalmonirhat', '{\"lat\":\"25.9923398\",\"lng\":\"89.28472510000006\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 18:38:14', '2018-10-25 18:38:14');
INSERT INTO `locations` VALUES (98, 3, 'Panchagarh', '{\"lat\":\"26.2708705\",\"lng\":\"88.5951751\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 18:39:33', '2018-10-25 18:39:33');
INSERT INTO `locations` VALUES (99, 3, 'Nilphamari', '{\"lat\":\"25.8482798\",\"lng\":\"88.94141339999999\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 18:39:51', '2018-10-25 18:39:51');
INSERT INTO `locations` VALUES (100, 3, 'Rangpur', '{\"lat\":\"25.7438916\",\"lng\":\"89.27522699999997\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 18:41:10', '2018-10-25 18:41:10');
INSERT INTO `locations` VALUES (101, 3, 'Thakurgaon', '{\"lat\":\"26.0418392\",\"lng\":\"88.42826160000004\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 18:41:16', '2018-10-25 18:41:16');
INSERT INTO `locations` VALUES (103, 3, 'Habiganj', '{\"lat\":\"24.4771236\",\"lng\":\"91.45065649999992\",\"radius\":\"1\"}', 0, 1, 177, 'PM - Syed Mohammad Toaha', 177, 'PM - Syed Mohammad Toaha', '2018-10-25 18:42:31', '2018-10-25 18:42:31');
INSERT INTO `locations` VALUES (104, 3, 'Sylhet', '{\"lat\":24.917365,\"lng\":91.863265,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.9033,24.87382],[91.91415,24.88198],[91.92604,24.90229],[91.94291,24.91264],[91.94915,24.91818],[91.93482,24.91985],[91.9114,24.92807],[91.89385,24.94427],[91.88345,24.96318],[91.86718,24.97162],[91.84987,24.97633],[91.83045,24.96361],[91.81858,24.93719],[91.81624,24.92598],[91.80292,24.91851],[91.77738,24.91361],[91.78899,24.90896],[91.80175,24.90733],[91.81408,24.90329],[91.82603,24.88548],[91.83385,24.87307],[91.84304,24.8584],[91.85411,24.86031],[91.85798,24.86347],[91.87143,24.86802],[91.87922,24.8679],[91.8919,24.87024],[91.89876,24.87067],[91.9033,24.87382]]]},\"center\":{\"lat\":24.917365,\"lng\":91.863265}}', 1, 1, 177, 'PM - Syed Mohammad Toaha', 17, 'IT - Firoze Ahmed', '2018-10-25 12:43:59', '2019-01-22 12:47:05');
INSERT INTO `locations` VALUES (105, 3, 'Sunamganj', '{\"lat\":\"25.0714535\",\"lng\":\"91.39916270000003\",\"radius\":\"1\"}', 0, 1, 16, 'PM - Md. Masud Reza', 16, 'PM - Md. Masud Reza', '2018-10-25 18:44:01', '2018-10-25 18:44:01');
INSERT INTO `locations` VALUES (106, 3, 'Keraniganj', '{\"lat\":\"23.7033938\",\"lng\":\"90.34659710000005\",\"radius\":\"1\"}', 0, 0, 237, 'PM - Rakebul Hasan', 6, 'IT - Hasan Hafiz Pasha', '2018-10-29 16:31:22', '2018-11-28 20:55:54');
INSERT INTO `locations` VALUES (107, 3, 'Chittagong', '{\"lat\":\"\",\"lng\":\"\",\"radius\":\"1\"}', 0, 0, 237, 'PM - Rakebul Hasan', 237, 'PM - Rakebul Hasan', '2018-11-04 11:40:26', '2018-11-04 11:40:26');
INSERT INTO `locations` VALUES (108, 1, 'Uttarkhan', '{\"lat\":23.87879091008,\"lng\":90.436752536499,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.406141311674,23.881694003555],[90.4122,23.88254],[90.41596,23.88553],[90.4222,23.88758],[90.42433,23.89018],[90.43236,23.89598],[90.43911,23.90004],[90.4455,23.89815],[90.44874,23.8961],[90.45224,23.89453],[90.45469,23.89475],[90.45713,23.89467],[90.45937,23.89143],[90.45902,23.8895],[90.45928,23.88789],[90.45937,23.88718],[90.45808,23.88584],[90.45851,23.88466],[90.45637,23.87895],[90.45588,23.87545],[90.45669,23.87251],[90.45994,23.87026],[90.46246,23.87036],[90.46327,23.86983],[90.4635,23.86928],[90.46338,23.86779],[90.46512,23.86682],[90.46609,23.86571],[90.46694,23.86427],[90.467479431589,23.862658843499],[90.451004256449,23.861071700292],[90.445559329448,23.861918961762],[90.441789329448,23.86307735876],[90.43672396503,23.862446083916],[90.432591122396,23.861617595664],[90.43178078712,23.860838229961],[90.424920218709,23.860727902474],[90.421142682209,23.860075380079],[90.415532186508,23.859116034122],[90.410936800162,23.858326476522],[90.406282405217,23.857541820159],[90.406271858445,23.863606733776],[90.40621839633,23.869612496309],[90.40602564141,23.881682047289],[90.406141311674,23.881694003555]]]},\"center\":{\"lat\":23.87879091008,\"lng\":90.436752536499}}', 1, 0, 6, 'IT - Hasan Hafiz Pasha', 6, 'IT - Hasan Hafiz Pasha', '2018-12-09 18:47:22', '2019-07-01 17:47:15');
INSERT INTO `locations` VALUES (109, 1, 'Sabujbagh', '{\"lat\":23.728754087811,\"lng\":90.457916238963,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.42736451786,23.7400584911],[90.428478163195,23.7388479026],[90.428550379133,23.724891583527],[90.427201661377,23.724962099759],[90.426970276992,23.723380193226],[90.421974343586,23.723302988845],[90.421420262566,23.721681034506],[90.420316093254,23.72161279523],[90.418028892031,23.721989548463],[90.416885291419,23.722168102347],[90.415856509752,23.72231876867],[90.415762477926,23.722334071151],[90.416418543625,23.721730986286],[90.41727765386,23.721075836639],[90.418072391078,23.720322459101],[90.418944912358,23.71882010788],[90.419795975965,23.717185132969],[90.419842376868,23.717264208926],[90.420757813492,23.71614],[90.42352,23.71524],[90.4259,23.71418],[90.429496032374,23.711991509851],[90.430517144093,23.712553465636],[90.431139396386,23.714106282873],[90.432004879391,23.714606505385],[90.433671437041,23.714542283533],[90.435277192062,23.714097610083],[90.440500350029,23.715780911018],[90.440541480179,23.714527176158],[90.441003151798,23.7131000631],[90.441389721565,23.710631680076],[90.441666154928,23.710534511495],[90.442264453373,23.710496282078],[90.44374,23.710518055267],[90.444500724792,23.710627670379],[90.46526,23.71669],[90.4749,23.71767],[90.48437,23.71849],[90.49833,23.72155],[90.49791,23.72413],[90.50007,23.72765],[90.49889,23.72981],[90.49581,23.73221],[90.49282,23.7327],[90.49137,23.73414],[90.4919,23.73901],[90.49364,23.74373],[90.49125,23.74365],[90.486488119914,23.747011893543],[90.472062215939,23.746323474311],[90.46696660367,23.745614550825],[90.461830758266,23.744910533785],[90.457699839575,23.745739929837],[90.453834926424,23.745241111579],[90.449401049686,23.744490330801],[90.44410591898,23.744989943691],[90.433596093254,23.74018652181],[90.429183055573,23.739957826054],[90.427579094581,23.739901355383],[90.427164693289,23.740513938085],[90.426900495701,23.741165801484],[90.427048017196,23.740436598143],[90.42736451786,23.7400584911]]]},\"center\":{\"lat\":23.728754087811,\"lng\":90.457916238963}}', 1, 0, 6, 'IT - Hasan Hafiz Pasha', 6, 'IT - Hasan Hafiz Pasha', '2018-11-28 21:34:08', '2020-05-16 19:00:55');
INSERT INTO `locations` VALUES (110, 1, 'House Building', '{\"lat\":\"23.8748924\",\"lng\":\"90.39251450000006\",\"radius\":\"1\"}', 0, 0, 6, 'IT - Hasan Hafiz Pasha', 6, 'IT - Hasan Hafiz Pasha', '2018-11-28 21:36:10', '2018-11-28 21:36:10');
INSERT INTO `locations` VALUES (111, 1, 'Mirpur 1', '{\"lat\":\"23.7956037\",\"lng\":\"90.35365479999996\",\"radius\":\"1\"}', 0, 0, 6, 'IT - Hasan Hafiz Pasha', 6, 'IT - Hasan Hafiz Pasha', '2018-11-28 21:36:52', '2018-11-28 21:36:52');
INSERT INTO `locations` VALUES (112, 1, 'Mirpur 2', '{\"lat\":23.79505,\"lng\":90.374135,\"radius\":\"5\"}', 0, 0, 6, 'IT - Hasan Hafiz Pasha', 6, 'IT - Hasan Hafiz Pasha', '2018-11-28 21:37:39', '2018-12-09 18:50:42');
INSERT INTO `locations` VALUES (113, 1, 'Kalabagan', '{\"lat\":23.74317,\"lng\":90.37275,\"radius\":\"1\"}', 0, 0, 6, 'IT - Hasan Hafiz Pasha', 6, 'IT - Hasan Hafiz Pasha', '2018-11-28 21:35:09', '2018-12-09 18:50:42');
INSERT INTO `locations` VALUES (114, 1, 'Tejgaon', '{\"lat\":23.763724675952,\"lng\":90.394427837549,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.379102476196,23.751582625028],[90.379747651624,23.751878062294],[90.380396850366,23.752164906334],[90.38171,23.75273],[90.38439,23.75271],[90.38639,23.75124],[90.39022,23.75062],[90.393419474869,23.749779640488],[90.393376618963,23.74993219666],[90.394130379133,23.74940961075],[90.394910961394,23.748880885048],[90.395136326389,23.748823533198],[90.39574201108,23.749358897145],[90.396334284725,23.750078388163],[90.397618687172,23.750791384534],[90.401047025795,23.753026120477],[90.402199153442,23.753539951385],[90.402576026459,23.754160407736],[90.402845611114,23.755502619296],[90.403298949738,23.756173965434],[90.403480932541,23.756482442128],[90.403791865081,23.756950911061],[90.40405451786,23.757650140282],[90.405819402447,23.759332790246],[90.407007616567,23.760095551371],[90.408378220901,23.761192167463],[90.410642652779,23.763993255038],[90.411075801258,23.764660944645],[90.411251457673,23.76534335943],[90.411308924275,23.765527769356],[90.411285924606,23.765839826498],[90.411320391541,23.766071178555],[90.411335681229,23.766281042466],[90.41152929367,23.766643217075],[90.412017061863,23.76758588728],[90.412279524498,23.767782317516],[90.412637738619,23.768006915622],[90.413242715969,23.768231513342],[90.413728474236,23.768788714565],[90.413699685211,23.76898065152],[90.413574336663,23.769094037925],[90.413874089966,23.76985426698],[90.413803698425,23.770207016086],[90.413679376912,23.770291660853],[90.413088351031,23.770651228581],[90.412855801258,23.76981901072],[90.410872861967,23.76994920646],[90.40122953373,23.771375465583],[90.400314169312,23.772822359499],[90.398819067459,23.775164368526],[90.397992974205,23.778625818707],[90.39778524107,23.778481660087],[90.397443397484,23.778209866095],[90.39739271164,23.778000092709],[90.396513499336,23.777501999733],[90.394413730164,23.776391273982],[90.39166,23.77566],[90.391052711639,23.775480373048],[90.39108864418,23.774950072783],[90.390995830688,23.773894485737],[90.390896034394,23.77208275239],[90.39077708004,23.770376437507],[90.390611592419,23.768311929123],[90.390489020142,23.766316120967],[90.390279268858,23.763735340397],[90.390195850888,23.762518575557],[90.390112432919,23.761125050483],[90.389991940394,23.759736251261],[90.389903634377,23.758376896008],[90.390099944644,23.75794489974],[90.38992074565,23.757345967709],[90.389734009037,23.756324850995],[90.388867009354,23.754675389098],[90.386654695926,23.75495532184],[90.384420924826,23.754940658688],[90.384542133241,23.757965205262],[90.387365412734,23.758186179973],[90.38978556307,23.758394863161],[90.391033189164,23.775152156397],[90.388649475628,23.764677113812],[90.383690841437,23.765454358258],[90.383815763607,23.758603546014],[90.374981585131,23.758289452397],[90.378464110451,23.751415683094],[90.379102476196,23.751582625028]]]},\"center\":{\"lat\":23.763724675952,\"lng\":90.394427837549}}', 1, 0, 6, 'IT - Hasan Hafiz Pasha', 6, 'IT - Hasan Hafiz Pasha', '2018-11-28 21:34:08', '2020-05-16 15:40:05');
INSERT INTO `locations` VALUES (115, 1, 'Dakshinkhan', '{\"lat\":23.845373272832,\"lng\":90.440855,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.40632,23.85755],[90.42492,23.86073],[90.43178,23.86084],[90.43259,23.86162],[90.43672,23.86245],[90.44179,23.86308],[90.44556,23.86192],[90.451,23.86108],[90.46748,23.86266],[90.47278,23.84976],[90.47217,23.84618],[90.47208,23.84245],[90.47431,23.83934],[90.47653,23.83701],[90.44874,23.83062],[90.433911453061,23.827666545664],[90.433707790129,23.829215402869],[90.433648874007,23.830068337991],[90.432718193718,23.830848112244],[90.43323990649,23.832651218918],[90.430582519468,23.837556778437],[90.427923450286,23.837811262429],[90.419040394569,23.83644209157],[90.418418332347,23.838544575299],[90.413976804488,23.837801113999],[90.410737116539,23.844027522278],[90.40518,23.85463],[90.40656,23.85447],[90.40627,23.85568],[90.40632,23.85755]]]},\"center\":{\"lat\":23.845373272832,\"lng\":90.440855}}', 1, 0, 6, 'IT - Hasan Hafiz Pasha', 6, 'IT - Hasan Hafiz Pasha', '2018-11-28 21:34:08', '2020-05-16 16:12:34');
INSERT INTO `locations` VALUES (116, 1, 'Cantonment', '{\"lat\":23.830987706482,\"lng\":90.39923959836,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.375230758266,23.846493519022],[90.385455979862,23.856639572619],[90.386060117722,23.857078541572],[90.406547236424,23.83138912479],[90.41457242426,23.836624765216],[90.415161874314,23.835579258192],[90.41426033412,23.835181627426],[90.413562800794,23.833838271299],[90.413904702953,23.831072520729],[90.413225393774,23.828145103237],[90.416875525094,23.827924116602],[90.417662575865,23.82668864847],[90.420147464944,23.82710690599],[90.421271154423,23.824946213418],[90.423891516533,23.825479600203],[90.428331224537,23.807234723859],[90.421648871398,23.808172005735],[90.421551341391,23.808591498274],[90.421039780712,23.809214532836],[90.415963841533,23.806575986786],[90.416277942753,23.806317247963],[90.415775357577,23.806277088667],[90.413365721791,23.809049177444],[90.414036932073,23.809287872807],[90.414619300443,23.809889417719],[90.415193622186,23.810454151117],[90.416218884058,23.811478093051],[90.4158138204,23.814822909317],[90.415178036671,23.815824467001],[90.414070083962,23.816161434478],[90.412674934502,23.816190017137],[90.410647107016,23.814900936626],[90.410495523472,23.813332516141],[90.410598098599,23.812302910964],[90.411202428976,23.811720368164],[90.41170483541,23.811218801511],[90.411885376763,23.810074306761],[90.412710572709,23.809318010458],[90.413171450445,23.809251511188],[90.413447486866,23.808916426706],[90.415721769003,23.806296692947],[90.403056635841,23.804937267912],[90.40259957701,23.804896871392],[90.404779825149,23.816168713958],[90.402486865947,23.816340609754],[90.400102711639,23.816365277178],[90.398048017197,23.817129815158],[90.396983381615,23.816083060025],[90.396452403774,23.816152716435],[90.395068483467,23.815962269058],[90.394594139881,23.814725199071],[90.395734139881,23.812254538817],[90.393386326389,23.811995701605],[90.388698309193,23.806138036292],[90.37606040741,23.811131475136],[90.382384431877,23.830338884963],[90.380426980495,23.831750729356],[90.377914031249,23.830591972747],[90.377383234463,23.830149642368],[90.376893059035,23.829896628178],[90.376427192424,23.82983644149],[90.37564214294,23.829729637311],[90.37638461149,23.830193282892],[90.376719091228,23.830246628739],[90.3768255832,23.830471722273],[90.377158721834,23.831127409712],[90.377022065352,23.832798397922],[90.376901502125,23.834439923064],[90.377157789265,23.837907993915],[90.377074038323,23.83991799767],[90.375100972361,23.841739947294],[90.375699624908,23.844401334989],[90.373685805875,23.845563740025],[90.370147972183,23.846672604976],[90.374847202377,23.846098547756],[90.375230758266,23.846493519022]]]},\"center\":{\"lat\":23.830987706482,\"lng\":90.39923959836}}', 1, 0, 6, 'IT - Hasan Hafiz Pasha', 227, 'IT - Md. Zubayer Alam', '2018-11-28 21:34:08', '2020-09-10 15:04:31');
INSERT INTO `locations` VALUES (117, 1, 'Ramna', '{\"lat\":23.738067921636,\"lng\":90.400248968204,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.410659664724,23.723056962678],[90.403920422413,23.723208740163],[90.39652642853,23.724433941502],[90.39413642853,23.724830306941],[90.393208258267,23.725240998106],[90.392291152115,23.725648311642],[90.392141891266,23.725460313218],[90.392120035346,23.725446656517],[90.39087,23.72628],[90.3865,23.73084],[90.38729,23.73349],[90.38834,23.73511],[90.38982,23.73672],[90.39113,23.73885],[90.39463,23.73816],[90.39607,23.7381],[90.39603,23.74206],[90.39483,23.74526],[90.39337,23.74994],[90.394260028853,23.749324163521],[90.394918278897,23.748888580213],[90.395133964453,23.748828623045],[90.395264169312,23.748926945338],[90.395804343586,23.749463707702],[90.39640084425,23.750150226225],[90.397576367073,23.750779318056],[90.398749207687,23.751531158296],[90.399299207687,23.751898577515],[90.40026649374,23.752536110717],[90.400629875355,23.75279329133],[90.40119510849,23.753078880594],[90.401819350223,23.752033856083],[90.402973109818,23.749996182562],[90.403654157772,23.748763891188],[90.404335205727,23.7485823681],[90.405203775177,23.748386267197],[90.405702466965,23.748258369765],[90.406915613136,23.747739713972],[90.406235208035,23.746303768362],[90.407406592131,23.746285672459],[90.408669171334,23.746257756045],[90.409194943162,23.746607273086],[90.409544535751,23.746878379536],[90.409894128341,23.747208408059],[90.410148415374,23.747334832748],[90.410619669342,23.74716095192],[90.410864320793,23.746860977024],[90.411211952076,23.746544041137],[90.411672236137,23.746236924991],[90.411581919327,23.745761351128],[90.411980549946,23.74546308392],[90.412323376999,23.745239052832],[90.412802996711,23.744892263768],[90.412919341423,23.744769144041],[90.413121516822,23.744636203567],[90.413382195958,23.744438995992],[90.413669697184,23.744227057127],[90.413997936408,23.743970130055],[90.413665391541,23.743572474141],[90.413426880951,23.743368723113],[90.413242684517,23.743196217194],[90.412996193085,23.742921377841],[90.412730583992,23.742625284293],[90.412502186509,23.742361134078],[90.412214300595,23.742075936433],[90.411969330025,23.741771096472],[90.41171525362,23.741468392059],[90.41138,23.74105],[90.41072390213,23.740183834114],[90.40784474205,23.740350604653],[90.405845346527,23.740006685004],[90.405485223179,23.73885677825],[90.40403915885,23.738497885315],[90.404002772193,23.737630959133],[90.403383764045,23.737727837171],[90.403256650184,23.736352201857],[90.403734899475,23.736346957846],[90.404012712129,23.736794968543],[90.4043548978,23.737223335321],[90.406156205175,23.737357065622],[90.407904436912,23.737384646016],[90.408773188362,23.737285492627],[90.409298830261,23.737167402955],[90.409781556816,23.736990385943],[90.409759957008,23.736449747612],[90.410118665676,23.736389973102],[90.409919474869,23.730417667453],[90.41061,23.72551],[90.410658994172,23.723074765519],[90.410659664724,23.723056962678]]]},\"center\":{\"lat\":23.738067921636,\"lng\":90.400248968204}}', 1, 0, 6, 'IT - Hasan Hafiz Pasha', 6, 'IT - Hasan Hafiz Pasha', '2018-11-28 21:34:08', '2020-05-16 18:14:50');
INSERT INTO `locations` VALUES (119, 1, 'Old Town (Dhaka)', '{\"lat\":23.707035,\"lng\":90.40565,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.37723,23.72584],[90.37601,23.72191],[90.37532,23.7213],[90.37822,23.71987],[90.3789,23.71714],[90.379299169311,23.71600535919],[90.37987,23.71491],[90.384765626984,23.712778141318],[90.385972406082,23.712898807996],[90.38778,23.71188],[90.39029,23.71202],[90.391199169312,23.711271367846],[90.39228,23.711151414789],[90.39865,23.71135],[90.40312459259,23.708082508654],[90.412062380981,23.702830546844],[90.412624609489,23.706108745958],[90.41260748085,23.709588239948],[90.412044828395,23.709950807456],[90.411799147424,23.71059043307],[90.411604428424,23.712511970119],[90.411558313179,23.713847675078],[90.41315370985,23.713827805918],[90.41349919712,23.713734264577],[90.414441263923,23.712672487566],[90.417911805935,23.710375215413],[90.41750387371,23.709485095227],[90.417728143883,23.708924720547],[90.417963142891,23.707499884547],[90.418079089317,23.705816718358],[90.415032711315,23.70608597279],[90.414345030422,23.70584976118],[90.413368188648,23.70581024369],[90.412642392263,23.706091331057],[90.41210435051,23.702845728578],[90.41885,23.6982],[90.42812,23.68823],[90.43174,23.69127],[90.43501,23.69399],[90.43584,23.69545],[90.43598,23.69668],[90.43535,23.70311],[90.434882711639,23.707858618538],[90.433847669802,23.707770510085],[90.433220323734,23.708036044052],[90.432033505792,23.707918184115],[90.431426044996,23.709038067439],[90.430339869437,23.708880935895],[90.428897079172,23.709038192135],[90.426354821396,23.710099271936],[90.429475244966,23.711973468246],[90.425900874834,23.714181617547],[90.423515480986,23.715244536092],[90.420750063621,23.716119585132],[90.419840734895,23.717220550535],[90.418998401523,23.714874542104],[90.415907079461,23.715469970837],[90.416388459807,23.721674086067],[90.415763352761,23.72233447495],[90.414713688037,23.722625178044],[90.41366,23.72293],[90.41104,23.72305],[90.40392,23.72321],[90.394136677246,23.724832065145],[90.392290862427,23.725650482184],[90.392140203705,23.725462135136],[90.38927,23.72362],[90.38629,23.72284],[90.38159,23.72269],[90.37877,23.72528],[90.37723,23.72584]]]},\"center\":{\"lat\":23.707035,\"lng\":90.40565}}', 1, 0, 6, 'IT - Hasan Hafiz Pasha', 15, 'IT - Quaviur Rahman', '2018-11-28 21:34:08', '2020-05-18 11:00:58');
INSERT INTO `locations` VALUES (120, 2, 'Nasirabad', '{\"lat\":22.362954216518,\"lng\":91.819090800104,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.828570320849,22.360745428452],[91.828467186797,22.360795272661],[91.828220218709,22.360911628237],[91.827550830977,22.360971084433],[91.827121618097,22.361901782398],[91.826901559813,22.362839921953],[91.825054242022,22.36439108649],[91.82461,22.36476],[91.824122507358,22.365169611731],[91.823582536211,22.365957983137],[91.8232,22.36638],[91.822135101852,22.367129529893],[91.821929576721,22.367178433035],[91.82167040741,22.367147963019],[91.820569882279,22.367087884799],[91.819799284725,22.367030776554],[91.819146704368,22.366963746628],[91.817766441802,22.366889216674],[91.816586720237,22.366804804177],[91.816587170639,22.366611253152],[91.815616661377,22.366710117493],[91.815118220902,22.366551018116],[91.814324780426,22.366379922046],[91.813241661377,22.366020313103],[91.812355814819,22.36588511749],[91.811534341278,22.365749921745],[91.810897460327,22.365689061043],[91.810282765884,22.365655853835],[91.809581104507,22.365462258251],[91.812128444805,22.365361380224],[91.813176430264,22.365093071988],[91.813825349417,22.364566375794],[91.814562168045,22.364593022437],[91.81520091754,22.364043919515],[91.815150132728,22.363573441386],[91.815110076752,22.363169934703],[91.815216001501,22.362784275677],[91.815215988517,22.362542895482],[91.815222681055,22.362301514869],[91.815226977971,22.362141513821],[91.815231274886,22.361981512589],[91.815238527612,22.361659649191],[91.81565,22.361696511335],[91.816096005828,22.361736627385],[91.816531953373,22.361774883043],[91.816972580357,22.361412471657],[91.81750268899,22.360649583219],[91.817509766865,22.360608526073],[91.817773899388,22.360456781414],[91.818031326389,22.360259146718],[91.818438221478,22.359930230942],[91.818904139881,22.359721394807],[91.819382128224,22.359575812301],[91.819605991113,22.359426393685],[91.819796996942,22.359240386768],[91.820398221478,22.359102556242],[91.821597930059,22.358894031908],[91.82178,22.35913],[91.822992011657,22.359098217366],[91.824145364418,22.359065503472],[91.82493,22.35889],[91.82573,22.35873],[91.82619,22.35884],[91.82662,22.359],[91.827938076057,22.359919766484],[91.828179169312,22.360224533116],[91.82849,22.36044],[91.828600495701,22.360730855365],[91.828570320849,22.360745428452]]]},\"center\":{\"lat\":22.362954216518,\"lng\":91.819090800104}}', 1, 0, 1, 'IT - Shafiqul Islam', 17, 'IT - Firoze Ahmed', '2018-12-25 12:23:12', '2020-12-07 12:46:38');
INSERT INTO `locations` VALUES (121, 2, 'Sugandha', '{\"lat\":22.364014020545,\"lng\":91.82933,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.833358134918,22.366095587117],[91.8327,22.367298041089],[91.8311,22.36705],[91.828011398811,22.366622768248],[91.825872449074,22.366373316726],[91.82396,22.36608],[91.82351,22.36594],[91.82412,22.36517],[91.82553,22.36399],[91.8269,22.36284],[91.82712,22.3619],[91.82755,22.36097],[91.82822,22.36091],[91.8286,22.36073],[91.83515,22.36297],[91.833921398811,22.365073646166],[91.833358134918,22.366095587117]]]},\"center\":{\"lat\":22.364014020545,\"lng\":91.82933}}', 1, 0, 1, 'IT - Shafiqul Islam', 175, 'IT - Gazi Fakhrul Islam', '2018-12-25 12:23:12', '2019-07-01 17:47:19');
INSERT INTO `locations` VALUES (122, 2, 'Fatikchari', '{\"lat\":22.773335488186,\"lng\":91.756117343903,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.597137451172,22.843273988571],[91.629753112793,22.74863871372],[91.667346954346,22.679440677779],[91.712236404419,22.561787279692],[91.763348579407,22.573477892092],[91.801071166992,22.562025064735],[91.883811950683,22.591031762653],[91.911792755127,22.62898485045],[91.921577453613,22.639996416309],[91.912822723389,22.651799201026],[91.893424987793,22.655680432052],[91.867675781251,22.689418753538],[91.853009462357,22.727928497184],[91.872455477715,22.762692760999],[91.852309405804,22.784819635568],[91.830563396216,22.781796475761],[91.806757450105,22.787952770159],[91.796650886536,22.811749383734],[91.736322641373,22.88344046069],[91.727492809296,22.924108889864],[91.727514266969,22.924365808351],[91.727728843689,22.924919169591],[91.728801727295,22.929464551376],[91.726619750261,22.949310519267],[91.719287931919,22.958563730509],[91.718307584524,22.960763019392],[91.717731580138,22.962060224954],[91.717400662601,22.962214888566],[91.717112660408,22.96292275789],[91.704831495881,22.962026884272],[91.699073463678,22.961131004718],[91.695139333606,22.962704809858],[91.692878901959,22.963962482477],[91.692413538694,22.963889627927],[91.691776514053,22.964607058313],[91.671059131622,22.976510166932],[91.667793542147,22.977958471579],[91.666246578097,22.980223500026],[91.66401296854,22.981619291779],[91.661393120885,22.979755557768],[91.656713336706,22.981605710504],[91.655982770026,22.98071335605],[91.65759159252,22.979476983194],[91.65834210813,22.978398639524],[91.656538657844,22.977150670156],[91.654477715492,22.976060732496],[91.651171222329,22.976370027508],[91.650462113321,22.978263132154],[91.65043964982,22.980156210276],[91.650460101664,22.981654170955],[91.649536415934,22.983310149779],[91.64631575346,22.984883696681],[91.64547689259,22.98444231523],[91.645925492048,22.977837471444],[91.645720638335,22.976095613107],[91.641310080886,22.975301999816],[91.640557050705,22.969921604791],[91.638031750917,22.966529654194],[91.636983677745,22.962936958458],[91.635678112507,22.961003815273],[91.631540134549,22.958991612928],[91.628312431276,22.9598822585],[91.626998987049,22.961513035405],[91.625256389379,22.962116410159],[91.62490401417,22.960359530974],[91.626525744796,22.957970373284],[91.627289168537,22.956331989947],[91.626936793327,22.954535518553],[91.623639017343,22.949246301522],[91.625294610858,22.946562096072],[91.625576913357,22.943877837381],[91.62184998393,22.940129542596],[91.620244011283,22.938294878107],[91.618123054504,22.93725063353],[91.615553498268,22.939424332867],[91.6153845191,22.941756080626],[91.615773439407,22.94408778822],[91.615007668733,22.945115307629],[91.613812744617,22.946024260604],[91.612195372581,22.94800022427],[91.612461581826,22.949205547939],[91.613242775202,22.949778566901],[91.615740582347,22.949442659528],[91.616478860378,22.949897122245],[91.615869998932,22.950924597545],[91.614286825061,22.952192876609],[91.612808592617,22.951799550829],[91.611587852239,22.951169115758],[91.608931794762,22.950026792034],[91.607048213482,22.950149052007],[91.606752499938,22.95161492813],[91.607658416033,22.953238858283],[91.609212756157,22.956486660109],[91.608785949647,22.957021984218],[91.607844159007,22.956925045638],[91.60600349307,22.955980353929],[91.604012623429,22.954107004582],[91.602279245853,22.95326108685],[91.601141318679,22.953417921631],[91.600217968225,22.954286066781],[91.599927619099,22.955737079309],[91.600667238235,22.957267108852],[91.603293120861,22.96096676978],[91.605163961649,22.962401657762],[91.606991887092,22.962176917276],[91.612385809421,22.958388378593],[91.61300137639,22.95839084834],[91.613402366638,22.958788476999],[91.613075137138,22.960616071952],[91.611975431442,22.962285583052],[91.606600284576,22.9649132925],[91.605457663536,22.965575151114],[91.605430841446,22.967975596217],[91.606149673462,22.971828073334],[91.601407527923,22.971511976783],[91.600581407547,22.973961705723],[91.599240303039,22.975779217857],[91.596354246139,22.974830953706],[91.593382358551,22.973092452149],[91.591740846634,22.9755026415],[91.590657234192,22.976529922257],[91.597137451172,22.843273988571]]]},\"center\":{\"lat\":22.773335488186,\"lng\":91.756117343903}}', 0, 0, 1, 'IT - Shafiqul Islam', 15, 'IT - Quaviur Rahman', '2018-12-25 12:23:12', '2020-03-20 11:45:33');
INSERT INTO `locations` VALUES (123, 2, 'Chittagong', '{\"lat\":22.326907649733,\"lng\":91.823913149711,\"radius\":\"7\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.760215759278,22.340867086456],[91.764163970947,22.273370852824],[91.772832870483,22.25375112813],[91.781802177429,22.242828025187],[91.792488098144,22.231268468785],[91.80004119873,22.223641022958],[91.824417114258,22.240802192463],[91.833515167236,22.251288539302],[91.836347579956,22.261932967081],[91.828837394715,22.268843468182],[91.806886196137,22.275475639649],[91.795910596848,22.282921565218],[91.796087622643,22.295221365652],[91.810767352581,22.313440750769],[91.829093545676,22.323184738962],[91.849242970348,22.326150974621],[91.855653617531,22.328321646013],[91.866184137762,22.335573409827],[91.868705749512,22.347535530432],[91.875057220459,22.368015184616],[91.879949569702,22.382698353907],[91.889605522156,22.398927405547],[91.893403530121,22.402597527461],[91.890152692795,22.411097951152],[91.882690787316,22.417252250733],[91.888572871685,22.425724504707],[91.889110654593,22.429960437834],[91.879423186183,22.430174276509],[91.868399642408,22.427425029826],[91.864261161536,22.417164072852],[91.862878566608,22.406320168885],[91.837811353617,22.401850143198],[91.810858191457,22.397075704698],[91.767512530787,22.380403348573],[91.7544227693,22.378733371713],[91.757469177246,22.362458986942],[91.760215759278,22.340867086456]]]},\"center\":{\"lat\":22.326907649733,\"lng\":91.823913149711}}', 0, 0, 1, 'IT - Shafiqul Islam', 15, 'IT - Quaviur Rahman', '2018-12-26 18:23:12', '2020-04-28 23:48:54');
INSERT INTO `locations` VALUES (124, 1, 'Vatara', '{\"lat\":23.787125,\"lng\":90.460935,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.43854,23.76245],[90.44564,23.76244],[90.44595,23.76153],[90.44615,23.76072],[90.44908,23.76048],[90.45211,23.76032],[90.45454,23.76015],[90.4571,23.75961],[90.45948,23.75991],[90.46063,23.76002],[90.46157,23.76008],[90.46325,23.76019],[90.46513,23.76177],[90.46625,23.76156],[90.46722,23.76139],[90.46917,23.76097],[90.47506,23.76034],[90.47674,23.76019],[90.47981,23.7599],[90.48112,23.76097],[90.48236,23.76128],[90.48242,23.76226],[90.48132,23.76675],[90.48234,23.77073],[90.48164,23.77298],[90.4797,23.77518],[90.47708,23.77738],[90.47389,23.77802],[90.47148,23.78086],[90.46975,23.78401],[90.47479,23.78812],[90.48052,23.79238],[90.48154,23.80205],[90.48427,23.80669],[90.48323,23.8089],[90.48391,23.81315],[90.47912,23.81101],[90.47355,23.80988],[90.47186,23.81104],[90.46325,23.8107],[90.45692,23.81464],[90.45396,23.81115],[90.43963,23.80934],[90.44072,23.79552],[90.44295,23.79285],[90.44298,23.79179],[90.44422,23.78822],[90.4399,23.78498],[90.43923,23.78291],[90.43992,23.78207],[90.44019,23.78022],[90.44052,23.77931],[90.43919,23.77839],[90.43923,23.77786],[90.44003,23.77754],[90.43919,23.77561],[90.43923,23.77355],[90.43784,23.77061],[90.43761,23.76882],[90.43793,23.76806],[90.43882,23.76758],[90.4388,23.76706],[90.43857,23.76669],[90.43789,23.76646],[90.4376,23.7656],[90.43854,23.76245]]]},\"center\":{\"lat\":23.787125,\"lng\":90.460935}}', 0, 0, 5, 'IT - Arnab Rahman', 5, 'IT - Arnab Rahman', '2019-01-03 19:56:37', '2019-01-03 20:03:37');
INSERT INTO `locations` VALUES (125, 1, 'Savar', '{\"lat\":23.879014428131,\"lng\":90.256108563385,\"radius\":\"15\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.243731323242,23.845321853383],[90.236586169434,23.771368478065],[90.241800634766,23.753948106659],[90.29645357666,23.749095988145],[90.331365460205,23.751785737989],[90.336242624206,23.757859889957],[90.341148838501,23.765610088624],[90.344853423157,23.769432380692],[90.340941813335,23.773960320179],[90.335141928368,23.774953546242],[90.336383316603,23.777178166891],[90.338397181034,23.779402749487],[90.336614615874,23.78186989298],[90.33620534173,23.783551576447],[90.337232430486,23.785293334242],[90.338860334061,23.786642370839],[90.339780134457,23.790936567915],[90.340013289344,23.797743702841],[90.342791268697,23.798427379559],[90.343723888247,23.800563889434],[90.343270397627,23.80500486764],[90.340241986352,23.8089745377],[90.341903516188,23.82179532381],[90.344509183598,23.827313144823],[90.33980386911,23.831367432431],[90.338960935602,23.837070296836],[90.342510740585,23.841880913518],[90.340717126808,23.855209806551],[90.338236867523,23.877955801537],[90.337739544754,23.886320395018],[90.342306232605,23.903316329574],[90.33963029602,23.915425081124],[90.331411457519,23.927972542319],[90.320349752502,23.946718802483],[90.304138206177,23.960599508911],[90.2800615979,23.97509743204],[90.23143741272,23.988339052627],[90.185152663879,24.006563998497],[90.1766015065,24.008932868117],[90.167363703613,23.978054380121],[90.223475280762,23.877661545546],[90.243731323242,23.845321853383]]]},\"center\":{\"lat\":23.879014428131,\"lng\":90.256108563385}}', 1, 0, 5, 'IT - Arnab Rahman', 237, 'PM - Rakebul Hasan', '2019-01-15 13:56:37', '2019-01-23 09:28:39');
INSERT INTO `locations` VALUES (126, 2, 'Anwara', '{\"lat\":22.192866146315,\"lng\":91.889777183533,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.837120056153,22.236988780818],[91.816520690918,22.192809203546],[91.83162689209,22.131761317728],[91.848106384277,22.11331456124],[91.855745315552,22.11633617806],[91.846518516541,22.140109271497],[91.84671163559,22.150404399615],[91.854457855225,22.153067495507],[91.857290267944,22.134782539077],[91.900248527527,22.120232377844],[91.907501220703,22.124764556378],[91.901149749756,22.13429557028],[91.873512268066,22.145733923209],[91.879348754883,22.155253581337],[91.905097961426,22.153623956941],[91.918144226074,22.179616202315],[91.935997009277,22.164593747285],[91.937026977539,22.180927610294],[91.944236755371,22.180093079359],[91.946047246456,22.191247069836],[91.95747077465,22.192228062033],[91.951478719711,22.203090540346],[91.956660747528,22.208921263428],[91.957411766052,22.21676802112],[91.963033676147,22.223561568213],[91.940631866455,22.240007740262],[91.895141601562,22.257008033931],[91.876945495605,22.27241773139],[91.848106384278,22.260503165629],[91.837120056153,22.236988780818]]]},\"center\":{\"lat\":22.192866146315,\"lng\":91.889777183533}}', 0, 1, 17, 'IT - Firoze Ahmed', 15, 'IT - Quaviur Rahman', '2019-02-17 16:14:25', '2020-03-20 11:52:03');
INSERT INTO `locations` VALUES (127, 2, 'Banshakhali', '{\"lat\":22.059414701986,\"lng\":91.949000358582,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.87780380249,22.015474667467],[91.871109008789,21.996694397952],[91.891021728515,21.946230007802],[91.908445358276,21.938268688195],[91.926898956299,21.937950226142],[92.05135345459,21.953235600364],[91.945266723633,22.179139323653],[91.936721205711,22.180879177831],[91.935728788376,22.164496871087],[91.918085217476,22.179749082279],[91.90524816513,22.153986649369],[91.879059076309,22.155559133811],[91.873469352722,22.145684236457],[91.901943683624,22.134201157767],[91.907415390014,22.124943455172],[91.900334358215,22.119467061385],[91.857204437256,22.134981301368],[91.85471534729,22.153763071955],[91.846733093261,22.150285155337],[91.846647262573,22.139513005494],[91.85583114624,22.116336178061],[91.875915527344,22.022954251361],[91.87780380249,22.015474667467]]]},\"center\":{\"lat\":22.059414701986,\"lng\":91.949000358582}}', 0, 1, 17, 'IT - Firoze Ahmed', 15, 'IT - Quaviur Rahman', '2019-02-17 16:15:26', '2020-03-20 11:52:54');
INSERT INTO `locations` VALUES (128, 2, 'Boalkhali', '{\"lat\":22.381131143289,\"lng\":91.986293792725,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.88175201416,22.38753949171],[91.863555908203,22.329236239405],[91.870594024659,22.332193662784],[91.88012123108,22.339279314683],[91.882663965226,22.34007320283],[91.887583136559,22.337453354791],[91.893017292023,22.337215184347],[91.896504163743,22.33792969446],[91.898188591004,22.33983503686],[91.902920007706,22.33908084194],[91.907994747162,22.339596870484],[91.916856765748,22.341025862639],[91.999897956849,22.351742836884],[92.109031677247,22.354203655363],[92.094290256501,22.380138831828],[92.08846449852,22.388660468859],[92.074913978577,22.398609985045],[92.063423395157,22.4212144637],[92.023093700409,22.433026047173],[92.012718915939,22.419766459052],[91.994619369507,22.414123063986],[91.975779533386,22.417098520542],[91.951103210449,22.424358367016],[91.929216384888,22.422930230485],[91.923637390137,22.421343394889],[91.908359527588,22.417068766292],[91.891536712646,22.402319791894],[91.88175201416,22.38753949171]]]},\"center\":{\"lat\":22.381131143289,\"lng\":91.986293792725}}', 0, 1, 17, 'IT - Firoze Ahmed', 15, 'IT - Quaviur Rahman', '2019-02-17 16:17:48', '2020-03-20 11:49:48');
INSERT INTO `locations` VALUES (129, 2, 'Chandanaish', '{\"lat\":22.245071431899,\"lng\":92.044658660889,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.956253051758,22.192809203546],[91.945781707764,22.192650259098],[91.93977355957,22.181364743568],[91.953678131104,22.168170667877],[91.97925567627,22.14988767368],[91.999876499176,22.138459596052],[92.010197639465,22.142295558574],[92.006807327271,22.156326738316],[92.032835483551,22.139652134454],[92.037577629089,22.142693061669],[92.041150331497,22.133957672574],[92.050489783287,22.137540340808],[92.055022716522,22.15193469222],[92.070713639259,22.15996845538],[92.081254720688,22.154965417421],[92.103297114372,22.160614316231],[92.118249088526,22.128318884992],[92.127021253109,22.130367459392],[92.11589762941,22.15271489283],[92.122970111668,22.178555952307],[92.138831689954,22.171417762314],[92.127639502287,22.201013224651],[92.140274047851,22.207113467188],[92.136754989624,22.275197650751],[92.149543762207,22.361823978805],[92.106971740723,22.350552102662],[92.08293914795,22.276309603026],[92.050838470459,22.250752324703],[92.017364501954,22.237902420145],[91.969470977784,22.227593839651],[91.957025527955,22.223869455096],[91.952133178711,22.204252731054],[91.956253051758,22.192809203546]]]},\"center\":{\"lat\":22.245071431899,\"lng\":92.044658660889}}', 0, 1, 17, 'IT - Firoze Ahmed', 15, 'IT - Quaviur Rahman', '2019-02-17 16:19:24', '2020-03-20 11:52:43');
INSERT INTO `locations` VALUES (130, 2, 'Hathazari', '{\"lat\":22.484091583097,\"lng\":91.802337169647,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.745796203614,22.493208836665],[91.771502494812,22.407933910203],[91.781394481659,22.410235038038],[91.803646087646,22.41396437118],[91.806639432907,22.413458536649],[91.807701587677,22.412873353227],[91.808814704418,22.413609791285],[91.811515688896,22.414068513354],[91.812583208084,22.414827261128],[91.815066933632,22.415119849032],[91.819353103638,22.415452109124],[91.818923950195,22.413081639134],[91.817636489868,22.409327485706],[91.818838119507,22.406208039319],[91.819310188293,22.400405385214],[91.821413040161,22.393650195574],[91.892395019531,22.401823834145],[91.889691352844,22.411514527977],[91.88286781311,22.418189505309],[91.889562606812,22.426619553147],[91.887245178223,22.430313736337],[91.879348754883,22.429882340149],[91.870937347412,22.435207757823],[91.864156723022,22.444102832097],[91.858835220337,22.452997335978],[91.855573654175,22.465648958118],[91.868362426758,22.47179588108],[91.875829696655,22.468008615637],[91.879348754883,22.476118392586],[91.881837844849,22.485100060716],[91.876602172852,22.48805416858],[91.866474151611,22.500702588514],[91.862182617188,22.503666275168],[91.852741241455,22.50123784097],[91.851110458374,22.508800538514],[91.845016479492,22.513825588485],[91.847155541182,22.531938287074],[91.84506257996,22.536501238543],[91.841253004968,22.539637106058],[91.845478489995,22.547652575577],[91.839853227139,22.549414461074],[91.845597177744,22.560230520609],[91.837654486299,22.565638232323],[91.836515553296,22.572542617666],[91.834003329277,22.57453297062],[91.801017522812,22.562946477905],[91.763198375702,22.574012862496],[91.712279319763,22.561906172265],[91.745796203614,22.493208836665]]]},\"center\":{\"lat\":22.484091583097,\"lng\":91.802337169647}}', 0, 1, 17, 'IT - Firoze Ahmed', 15, 'IT - Quaviur Rahman', '2019-02-17 16:27:55', '2020-03-20 11:46:36');
INSERT INTO `locations` VALUES (131, 2, 'Kanchana', '{\"lat\":22.134914002753,\"lng\":92.076759338379,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.978225708008,22.109815766135],[92.010498046875,22.046822621765],[92.060279846191,22.047936380752],[92.116241455078,22.056687039096],[92.15950012207,22.061141711721],[92.205505371093,22.07068696611],[92.162418365478,22.216807750422],[92.140617370605,22.223005383742],[92.137699127197,22.205842036105],[92.122507095337,22.195988054739],[92.138257026672,22.173257687113],[92.123472690582,22.177788158655],[92.117110490799,22.152391789381],[92.128692269325,22.129833267604],[92.113883793354,22.13636269154],[92.105106264353,22.158706534082],[92.068788483739,22.160656545492],[92.054678639397,22.15171887477],[92.041942086071,22.133240258397],[92.038784958422,22.143029696224],[92.032674173824,22.140786434071],[92.005964023992,22.15571470448],[92.003355491906,22.138508664238],[91.948013305664,22.170873169762],[91.978225708008,22.109815766135]]]},\"center\":{\"lat\":22.134914002753,\"lng\":92.076759338379}}', 0, 1, 17, 'IT - Firoze Ahmed', 15, 'IT - Quaviur Rahman', '2019-02-17 16:29:13', '2020-03-20 11:53:05');
INSERT INTO `locations` VALUES (132, 2, 'Lohagara', '{\"lat\":22.004955665314,\"lng\":92.10765838623,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[92.009124755859,22.046186184122],[92.051010131836,21.953872455297],[92.129631042481,21.939542529279],[92.177867889405,21.942567856133],[92.198638916016,21.972340006857],[92.206192016602,22.070368801349],[92.161560058594,22.061459897252],[92.056503295898,22.048413703348],[92.009124755859,22.046186184122]]]},\"center\":{\"lat\":22.004955665314,\"lng\":92.10765838623}}', 0, 1, 17, 'IT - Firoze Ahmed', 15, 'IT - Quaviur Rahman', '2019-02-17 16:30:57', '2020-03-20 11:53:18');
INSERT INTO `locations` VALUES (133, 2, 'Mirsharai', '{\"lat\":22.84301058979,\"lng\":91.528730392456,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.426677703857,22.800079078255],[91.438779830933,22.744126865064],[91.476631164551,22.70937272543],[91.630783081055,22.747688863225],[91.59782409668,22.84485595948],[91.590785980225,22.97664845415],[91.578168869019,22.966296276961],[91.568598747253,22.955232005225],[91.563835144043,22.951280260254],[91.559071540833,22.952347242772],[91.554479598999,22.956417506206],[91.551990509033,22.957840093669],[91.548728942871,22.956970736442],[91.543900966644,22.952762178145],[91.540017127991,22.947209842266],[91.538075208664,22.945065907836],[91.533928513527,22.943361603933],[91.531940996647,22.942311840849],[91.530640125275,22.94070877516],[91.53046309948,22.938549917102],[91.531487703323,22.936865292279],[91.533198952675,22.936326800744],[91.534910202026,22.936104487101],[91.537806987762,22.937497646576],[91.540017127991,22.939286007585],[91.541841030121,22.939295887967],[91.542720794678,22.937962029882],[91.541937589645,22.935704321622],[91.536862850189,22.932814202501],[91.532120704651,22.926559477128],[91.527850627899,22.919474391965],[91.523580551147,22.911914582986],[91.510469913483,22.902585296404],[91.502884626389,22.899185481562],[91.497530937195,22.900213341548],[91.496954262257,22.90373172638],[91.499810814857,22.911677405687],[91.490994393826,22.917725297187],[91.484581232071,22.919029708992],[91.478971391916,22.922013997866],[91.475078165531,22.915827948512],[91.473416537046,22.91153905207],[91.477220579982,22.905757721175],[91.478964686394,22.899659879446],[91.480822116137,22.894421644161],[91.480619609356,22.888550626899],[91.476297229528,22.883786416118],[91.471631526947,22.880287283439],[91.467664539814,22.878508028845],[91.464384198189,22.874830828802],[91.469686925411,22.866882991793],[91.469839811325,22.858934689797],[91.450366973877,22.83251609847],[91.453628540039,22.825950203484],[91.45231962204,22.824259236471],[91.450152397156,22.823359356573],[91.441869735718,22.825831539817],[91.426677703857,22.800079078255]]]},\"center\":{\"lat\":22.84301058979,\"lng\":91.528730392456}}', 0, 1, 17, 'IT - Firoze Ahmed', 15, 'IT - Quaviur Rahman', '2019-02-17 16:32:05', '2020-03-20 11:45:02');
INSERT INTO `locations` VALUES (134, 2, 'Patiya', '{\"lat\":22.288823002109,\"lng\":91.991229057312,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.882610321045,22.261297501572],[91.883554458619,22.259947127787],[91.895377635956,22.256690290359],[91.896573901177,22.25321492296],[91.899057626725,22.250136668373],[91.902485489846,22.248408844215],[91.905913352967,22.247395971867],[91.939837932587,22.240484412123],[91.96346282959,22.22340265859],[92.049293518066,22.249699749335],[92.086168527603,22.275842981273],[92.098324298858,22.3007107577],[92.108902931213,22.354243345627],[91.999619007111,22.352318354776],[91.955882906914,22.346359644951],[91.951714754105,22.345545946238],[91.949275955558,22.345198014165],[91.947722285987,22.345003891258],[91.947096660734,22.344943731708],[91.946122348309,22.344804186259],[91.944538503886,22.344594557725],[91.931818127632,22.343174285084],[91.916715949774,22.341317365316],[91.922728121281,22.316197522312],[91.910204887391,22.303827604013],[91.891965866089,22.29926149974],[91.877117156983,22.294337880961],[91.873555183412,22.283219393994],[91.877374649049,22.271941168239],[91.882781982422,22.265269113666],[91.882610321045,22.261297501572]]]},\"center\":{\"lat\":22.288823002109,\"lng\":91.991229057312}}', 0, 1, 17, 'IT - Firoze Ahmed', 15, 'IT - Quaviur Rahman', '2019-02-17 16:33:29', '2020-03-20 11:50:01');
INSERT INTO `locations` VALUES (135, 2, 'Rangunia', '{\"lat\":22.495684528236,\"lng\":92.078125672415,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[92.013416290284,22.549976773396],[92.004661560059,22.514777056669],[92.007064819336,22.469416459383],[92.007343769073,22.448634439917],[92.011399269104,22.430070766234],[92.015315294266,22.425548469569],[92.018818259239,22.424239356198],[92.025754451751,22.43149882931],[92.063112258911,22.421184710331],[92.074547857046,22.39909107521],[92.088730037212,22.387787496282],[92.109197974205,22.354699782856],[92.143696211278,22.356705360241],[92.151589784771,22.361121373656],[92.146780416369,22.374744336066],[92.13612601161,22.387961099219],[92.123056948185,22.408677813456],[92.140520811081,22.443755766312],[92.131412029266,22.462833179752],[92.112507820129,22.52762123604],[92.087059020996,22.611792368871],[92.071266174317,22.636669273617],[92.043027877808,22.630331636079],[92.032642364502,22.618447778022],[92.042770385743,22.603868842895],[92.038650512696,22.577559465615],[92.013416290284,22.549976773396]]]},\"center\":{\"lat\":22.495684528236,\"lng\":92.078125672415}}', 0, 1, 17, 'IT - Firoze Ahmed', 15, 'IT - Quaviur Rahman', '2019-02-17 16:34:59', '2020-03-20 11:47:53');
INSERT INTO `locations` VALUES (136, 2, 'Raozan', '{\"lat\":22.5221185142,\"lng\":91.935074329376,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.928615570068,22.42221615671],[91.950969099998,22.42414513923],[91.975382566452,22.416870404462],[91.994817852973,22.413721372468],[92.012289762497,22.419617690596],[92.012729644775,22.419746623267],[92.012954950332,22.420029282936],[92.013282179831,22.420217722394],[92.01356112957,22.420495422184],[92.013620138168,22.421055778996],[92.014505267143,22.422057473152],[92.014971971512,22.422657494407],[92.015224099159,22.422979818805],[92.015645205974,22.423522809752],[92.016142755746,22.424114146793],[92.016345262527,22.424427789945],[92.01644450426,22.424510849475],[92.016518265008,22.42462614098],[92.016701996326,22.424831929879],[92.016865611076,22.424988130888],[92.015261650086,22.42550879965],[92.014129757881,22.426922033587],[92.010925859212,22.432238887467],[92.009824812412,22.435125944292],[92.008309364319,22.450895230467],[92.00345993042,22.515807806477],[92.013866901398,22.551562135008],[92.035603523254,22.573041988747],[91.979234218597,22.605295111245],[91.92169547081,22.642016427916],[91.883382797241,22.591903454129],[91.834545135498,22.574310067378],[91.845874786377,22.531704166388],[91.844501495361,22.51375621034],[91.850509643554,22.508270271108],[91.852397918701,22.50119819271],[91.862096786499,22.502992265053],[91.865916252136,22.500400269081],[91.876237392425,22.487684908544],[91.881408691406,22.484961275574],[91.875636577606,22.468910827008],[91.868147850036,22.472212275674],[91.855187416076,22.465767933408],[91.858566999435,22.452863476435],[91.866281032562,22.441028790651],[91.870690584182,22.434746626385],[91.879118084907,22.429741020416],[91.886636316776,22.429816638887],[91.888318061828,22.426877405366],[91.882374286651,22.418596142892],[91.889187097549,22.411519487217],[91.891536712646,22.402220600485],[91.908445358276,22.416582779305],[91.928615570068,22.42221615671]]]},\"center\":{\"lat\":22.5221185142,\"lng\":91.935074329376}}', 0, 1, 17, 'IT - Firoze Ahmed', 15, 'IT - Quaviur Rahman', '2019-02-17 16:36:25', '2020-03-20 11:47:28');
INSERT INTO `locations` VALUES (137, 2, 'Shitakund', '{\"lat\":22.559986520048,\"lng\":91.624263972045,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.624603271484,22.596895762637],[91.681938171387,22.517155698475],[91.71275138855,22.471042401965],[91.738071441651,22.412853516457],[91.740528345109,22.409679596877],[91.742086708546,22.407051139843],[91.744496673346,22.406629590221],[91.74462877214,22.404157299929],[91.746262907983,22.402795909668],[91.746554262937,22.400118965885],[91.74733914435,22.397799071566],[91.74935951829,22.395381182198],[91.750248000026,22.393052528999],[91.751436889173,22.390981755644],[91.752098221332,22.38612027704],[91.751708127558,22.382845921815],[91.752172484995,22.380642307429],[91.752368621529,22.378855332829],[91.752629131079,22.377961216868],[91.752280779183,22.376541284432],[91.752554699779,22.374893152547],[91.754400394857,22.3724066563],[91.757576297969,22.372165445198],[91.762640476227,22.376428433285],[91.765360236169,22.377033611991],[91.76767230034,22.376646694761],[91.771481037141,22.375158541542],[91.77191823721,22.407981024194],[91.746885180474,22.493303006699],[91.712440252305,22.563005923704],[91.668441295624,22.679024908861],[91.630568504334,22.747807594898],[91.476609706879,22.709471696219],[91.624603271484,22.596895762637]]]},\"center\":{\"lat\":22.559986520048,\"lng\":91.624263972045}}', 0, 1, 17, 'IT - Firoze Ahmed', 15, 'IT - Quaviur Rahman', '2019-02-17 16:38:18', '2020-03-20 11:46:08');
INSERT INTO `locations` VALUES (138, 4, 'Kaliakor', '{\"lat\":24.020253456443,\"lng\":90.285902023315,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.186252593995,24.058063996676],[90.17406463623,24.008207938121],[90.233577489853,23.986960815962],[90.279014110565,23.975121577448],[90.298835635185,23.96290747004],[90.316854715347,23.948417417967],[90.324084609747,23.939401524152],[90.338953435421,23.915635070396],[90.341954827308,23.903463761584],[90.341117307544,23.899381013132],[90.338606089353,23.892865421985],[90.337059795856,23.885366208045],[90.337509065866,23.876895310414],[90.339106991887,23.868715590169],[90.339691378176,23.858444104326],[90.341219902038,23.850840860509],[90.342043340206,23.8526862307],[90.344772487879,23.85561061947],[90.347158312797,23.858534942235],[90.349440872669,23.862892020843],[90.35191655159,23.866974234405],[90.352308154106,23.874765465822],[90.359155833721,23.881976811878],[90.359403938055,23.888623390506],[90.361754894256,23.892660343594],[90.371472537517,23.895638086697],[90.374709963798,23.896085018133],[90.380028784275,23.898405480933],[90.384382009506,23.897626276007],[90.391618609428,23.888210460948],[90.393018722534,23.879892730616],[90.3977394104,23.905691524754],[90.380401611328,23.949586683263],[90.380508899689,23.978077200305],[90.373020172119,23.977616460905],[90.36280632019,23.986860342632],[90.346949100494,23.995819314792],[90.34912571311,23.9970016271],[90.34992903471,23.999360093305],[90.348243266344,24.000267938126],[90.345270037651,23.999842807813],[90.345846712589,24.010753632861],[90.343590974808,24.013901992182],[90.344512313604,24.016015147332],[90.346206128597,24.017893069984],[90.342621356249,24.021142926115],[90.342040657997,24.024471095439],[90.34373447299,24.02493779172],[90.34559994936,24.026502008104],[90.345577150583,24.031985801946],[90.344986394048,24.033963315487],[90.344653129578,24.036175963342],[90.343301296234,24.039659290768],[90.339460372925,24.04094775504],[90.337175130844,24.044609764477],[90.335919857025,24.048428430987],[90.335737466812,24.053853730432],[90.33383846283,24.057868059484],[90.335837379098,24.059130012217],[90.338351279497,24.05937309436],[90.341251417994,24.058479749913],[90.343293249607,24.060251125269],[90.341451242566,24.06417769905],[90.336948484182,24.066771878116],[90.337938889861,24.07069825233],[90.337041020393,24.074232682254],[90.337367244065,24.074763175064],[90.338508859277,24.074901843625],[90.341135412455,24.073768614038],[90.342011153698,24.074597568485],[90.341708734632,24.076060164113],[90.339646786451,24.077366017105],[90.337134227157,24.078182091945],[90.336209535599,24.08009522528],[90.337724983692,24.083633681461],[90.341128706932,24.087015325811],[90.340854115784,24.087925305929],[90.339292064309,24.089089936376],[90.335610061884,24.089989189123],[90.33266633749,24.094216633127],[90.332683771849,24.096857337533],[90.335059203207,24.097703587918],[90.336919650435,24.098980757388],[90.337421894074,24.101730928593],[90.335816591978,24.103125272826],[90.33043473959,24.104441255434],[90.323765426874,24.104503687761],[90.320100188256,24.102920835245],[90.314829647541,24.10288900661],[90.313018485904,24.105074758519],[90.311722308398,24.109140733173],[90.309782400727,24.111600575969],[90.307756662369,24.112885257362],[90.307717435062,24.114078426555],[90.308364853263,24.115506603935],[90.307188369334,24.116856426531],[90.30656978488,24.118911275358],[90.305933430791,24.122296272321],[90.306241214276,24.123448656387],[90.305145196617,24.125175377077],[90.303705856204,24.126392910759],[90.303017534316,24.130998259436],[90.302114635706,24.132196155327],[90.302435662598,24.133181848474],[90.30215587467,24.134128369882],[90.301339644939,24.134859482883],[90.300523415208,24.135081462588],[90.299405939877,24.137033215178],[90.298846364022,24.139219913724],[90.300349406898,24.142941223348],[90.300050005317,24.147210667785],[90.299514569342,24.150363938008],[90.301864687354,24.153193615912],[90.301897376776,24.156023231148],[90.303845163435,24.157085831159],[90.304762981832,24.159244807701],[90.306770280004,24.162779546335],[90.308262594044,24.168154425265],[90.307523310185,24.170788403466],[90.30602529645,24.175921002437],[90.306895337999,24.180668931518],[90.306048765779,24.18244136894],[90.307219214738,24.186640983896],[90.306259486825,24.187996936667],[90.306415557862,24.189666052378],[90.27216911316,24.162886611751],[90.25384426117,24.123490271762],[90.19552230835,24.085335533904],[90.186252593995,24.058063996676]]]},\"center\":{\"lat\":24.020253456443,\"lng\":90.285902023315}}', 1, 0, 17, 'IT - Firoze Ahmed', 17, 'IT - Firoze Ahmed', '2019-02-17 16:44:07', '2019-07-01 17:47:26');
INSERT INTO `locations` VALUES (139, 4, 'Kalihang', '{\"lat\":24.010233761426,\"lng\":90.545883178712,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.447177886963,23.961940536808],[90.439796447754,23.957312882685],[90.43104171753,23.945782242438],[90.45241355896,23.930092641611],[90.455203056336,23.925228478131],[90.466125011444,23.920364131434],[90.4782807827,23.907339333677],[90.480995178223,23.900277147536],[90.521335601807,23.9101641001],[90.561504364014,23.919265705213],[90.615921020508,23.938251472072],[90.660724639893,24.018713810345],[90.642056465149,24.050187085899],[90.627508163452,24.091368969701],[90.60227394104,24.10848827627],[90.593358278275,24.110828781663],[90.581524372101,24.10662759292],[90.578343272209,24.111098083932],[90.578509569168,24.120190375316],[90.568284988404,24.114207260009],[90.566568374634,24.115597796207],[90.554165840149,24.07249394837],[90.532150268555,24.023104069811],[90.501937866211,24.003738762175],[90.450782775879,23.996289790629],[90.447177886963,23.961940536808]]]},\"center\":{\"lat\":24.010233761426,\"lng\":90.545883178712}}', 1, 0, 17, 'IT - Firoze Ahmed', 17, 'IT - Firoze Ahmed', '2019-02-17 16:45:45', '2019-07-01 17:47:27');
INSERT INTO `locations` VALUES (140, 4, 'Kapashia', '{\"lat\":24.144987348773,\"lng\":90.618538856506,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.540733337403,24.179957196915],[90.551633834839,24.174417337486],[90.554809570313,24.162925767081],[90.542449951172,24.142759183673],[90.553092956543,24.135670754878],[90.559701919556,24.121316462304],[90.568199157715,24.114011408626],[90.578327178955,24.119945572486],[90.57806968689,24.111005052303],[90.58141708374,24.106451316254],[90.593347549439,24.110583960927],[90.602188110352,24.108292416138],[90.627164840698,24.090036936881],[90.641627311707,24.049991136683],[90.658922195435,24.018321815602],[90.712051391601,24.197182078145],[90.64338684082,24.269344667817],[90.643762350082,24.266635396904],[90.641906261444,24.262830583207],[90.639175772667,24.263001753365],[90.632325410843,24.269745674128],[90.621886253357,24.271652881943],[90.61773955822,24.268420392354],[90.610159635544,24.268004710721],[90.598776340485,24.261382963512],[90.58601975441,24.251317638438],[90.575494766235,24.247199331179],[90.562062263489,24.260072266595],[90.554455518723,24.261177556679],[90.549080371857,24.266038762697],[90.541763305664,24.266997288418],[90.531506538391,24.257881220679],[90.525026321411,24.245321104025],[90.525809526444,24.240889551944],[90.530712604523,24.237397027982],[90.542922019959,24.23792531507],[90.549831390381,24.218944736734],[90.549134016037,24.215490749945],[90.544488430024,24.21250635167],[90.541977882386,24.202779663657],[90.540733337403,24.179957196915]]]},\"center\":{\"lat\":24.144987348773,\"lng\":90.618538856506}}', 1, 0, 17, 'IT - Firoze Ahmed', 17, 'IT - Firoze Ahmed', '2019-02-17 16:47:15', '2019-07-01 17:47:27');
INSERT INTO `locations` VALUES (141, 4, 'Sreepur', '{\"lat\":24.146038011914,\"lng\":90.47144651413,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[90.307574272156,24.112679611279],[90.309725403786,24.111382074802],[90.311275720596,24.109418607805],[90.312826037407,24.105143923401],[90.314719676971,24.102671102667],[90.320266485214,24.102739656757],[90.323876738548,24.104380047241],[90.330424010754,24.104377598913],[90.335779041052,24.103024890343],[90.337271690369,24.101691754524],[90.336527377367,24.099040743947],[90.33269315958,24.097173190049],[90.331891179085,24.094064824636],[90.33555239439,24.089545991145],[90.340586900711,24.087534440723],[90.335793793201,24.079920136504],[90.337002128362,24.077836806601],[90.340696200728,24.076324947851],[90.341643691063,24.074969800349],[90.341264158487,24.074179418573],[90.33905737102,24.074998880918],[90.337108075619,24.075034695925],[90.336918309331,24.074188908081],[90.337930172682,24.070913761672],[90.336692333221,24.066792695013],[90.341426432132,24.06393829966],[90.343070626259,24.060300108686],[90.341189056635,24.058696504381],[90.338445827365,24.059618931375],[90.335702598095,24.059287372751],[90.333484411239,24.057961129688],[90.335407555103,24.053793722731],[90.335528254509,24.04852885622],[90.337279736995,24.044361143061],[90.339442938566,24.040631151662],[90.34314237535,24.039589171976],[90.34478187561,24.036117172196],[90.357559919357,24.033961478233],[90.36381483078,24.034627787502],[90.411515235901,24.021379342901],[90.41189879179,24.016067822641],[90.413998961449,24.010442467873],[90.417729914189,24.008345147208],[90.42025923729,24.005777352917],[90.424459576607,24.004797265246],[90.427183359862,24.005493128261],[90.430765450001,24.007600296126],[90.442221164704,24.006639823891],[90.45129776001,23.995192011002],[90.501422882081,24.003111496988],[90.53300857544,24.022320105902],[90.554294586182,24.071622123634],[90.568456649781,24.114403111092],[90.560050606728,24.121497614503],[90.553447008133,24.136033018365],[90.543158054352,24.142857087397],[90.555270910264,24.162876822918],[90.551800131798,24.174764809236],[90.541119575501,24.17999634701],[90.542301088572,24.202824922854],[90.544684231282,24.212342452364],[90.549278855324,24.214937907672],[90.550228357316,24.219032797496],[90.543372631073,24.238101410279],[90.530766248703,24.237563340821],[90.526265501976,24.240855311979],[90.525197982788,24.245086323682],[90.531603097916,24.257773623703],[90.541956424713,24.266782109805],[90.548723638058,24.266006974731],[90.554460883141,24.261162884751],[90.562072992325,24.259690792567],[90.575408935547,24.247042813309],[90.585874915123,24.250950811254],[90.598872900009,24.26123624438],[90.610191822052,24.26779931459],[90.617698989808,24.268281933844],[90.621987506747,24.271385752317],[90.632109493017,24.269612413025],[90.639049708843,24.262857481391],[90.642029643059,24.262571382245],[90.644127130508,24.26653758821],[90.644373893738,24.26867958139],[90.631499290466,24.281941529752],[90.517644882202,24.296884012825],[90.386066436767,24.280455008569],[90.306115150452,24.188100157925],[90.307040512562,24.186727537758],[90.305948853493,24.182301287638],[90.306597948074,24.180573809504],[90.305578708649,24.175787643121],[90.308003425598,24.168015857726],[90.301694869995,24.155916771815],[90.299184322357,24.150493346777],[90.298519134522,24.138960461312],[90.303411483765,24.126251544036],[90.305868387222,24.123534335085],[90.306453108788,24.118572219925],[90.307981967926,24.115255059814],[90.307574272156,24.112679611279]]]},\"center\":{\"lat\":24.146038011914,\"lng\":90.47144651413}}', 1, 0, 17, 'IT - Firoze Ahmed', 17, 'IT - Firoze Ahmed', '2019-02-17 16:48:21', '2019-07-01 17:47:27');
INSERT INTO `locations` VALUES (142, 2, 'Bayejid bostami', '{\"lat\":22.392686092842,\"lng\":91.804163083434,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.771373748779,22.408171959678],[91.77137374878,22.375158541543],[91.791286468506,22.369801058212],[91.815050840378,22.372767542052],[91.829365789891,22.381887347646],[91.836952418089,22.395632892642],[91.821450591088,22.393694834468],[91.81945770979,22.400462421028],[91.81896686554,22.406148526148],[91.817722320557,22.40931260775],[91.819052696228,22.413255210485],[91.819438934326,22.415571127471],[91.815002560615,22.415256224539],[91.812505424022,22.414945040231],[91.81158542633,22.414157778013],[91.808801293373,22.413670541051],[91.807605028152,22.412965098248],[91.806556284427,22.413633967215],[91.805657744407,22.413687898122],[91.80368900299,22.414152818867],[91.781158447265,22.410393735105],[91.771373748779,22.408171959678]]]},\"center\":{\"lat\":22.392686092842,\"lng\":91.804163083434}}', 1, 1, 17, 'IT - Firoze Ahmed', 3, 'IT - Fahim Razzaq Ishraq', '2019-02-17 14:43:26', '2019-07-01 17:47:28');
INSERT INTO `locations` VALUES (143, 2, 'Chadgaon', '{\"lat\":22.371752340437,\"lng\":91.863384246826,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.857032775879,22.398054497423],[91.845531463623,22.396546734118],[91.836647987365,22.374523591327],[91.835489273071,22.344995208438],[91.851754188537,22.341760200132],[91.869564056396,22.349639199535],[91.881902217865,22.38753949171],[91.891279220581,22.401744480741],[91.868190765381,22.399244825323],[91.86119556427,22.398530629806],[91.857032775879,22.398054497423]]]},\"center\":{\"lat\":22.371752340437,\"lng\":91.863384246826}}', 1, 1, 17, 'IT - Firoze Ahmed', 3, 'IT - Fahim Razzaq Ishraq', '2019-02-17 14:46:25', '2019-07-01 17:47:28');
INSERT INTO `locations` VALUES (144, 2, 'Pachlaish', '{\"lat\":22.371683894741,\"lng\":91.830383499618,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.817293167114,22.360712707601],[91.819846630096,22.346940146619],[91.824105978012,22.346781377173],[91.826196759939,22.347820817608],[91.828287541866,22.348349217531],[91.831053569913,22.348620858965],[91.832017153502,22.347999431891],[91.835644841194,22.348150757701],[91.836133003235,22.357061325505],[91.83679819107,22.374602960262],[91.845799684524,22.396586412309],[91.836720407009,22.395574614895],[91.829014420509,22.381865026306],[91.814967314712,22.372736228119],[91.816369732841,22.36678180229],[91.822006981819,22.367175568071],[91.823582611978,22.365978764687],[91.832656189799,22.367236338124],[91.83509632945,22.363004694744],[91.828475296497,22.360653174965],[91.827163696289,22.359283917311],[91.825570464134,22.358708428192],[91.822518110275,22.359164850792],[91.82190656662,22.359283917311],[91.820039749146,22.359363294934],[91.817293167114,22.360712707601]]]},\"center\":{\"lat\":22.371683894741,\"lng\":91.830383499618}}', 1, 1, 17, 'IT - Firoze Ahmed', 3, 'IT - Fahim Razzaq Ishraq', '2019-02-17 14:48:35', '2019-07-01 17:47:29');
INSERT INTO `locations` VALUES (145, 2, 'Pahartali', '{\"lat\":22.360879144627,\"lng\":91.775775924325,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.756036877633,22.359234306273],[91.757213361561,22.346981389433],[91.786309629678,22.349045375168],[91.787339933216,22.34420258873],[91.796963363886,22.344823412529],[91.794785410166,22.351198325309],[91.793668940663,22.354457300764],[91.792626902461,22.357516823283],[91.790728024207,22.359126499103],[91.788830151782,22.36073553619],[91.788304512156,22.361181395413],[91.787774849218,22.361621051935],[91.786706806161,22.362494159579],[91.784571725875,22.364240358446],[91.783594144508,22.365039879185],[91.783105018549,22.365442118276],[91.782858443912,22.365641687111],[91.782533414662,22.365907297217],[91.781153585762,22.367033952042],[91.780463671312,22.367601306691],[91.78011670243,22.367883742949],[91.779942882713,22.368026201061],[91.779769733548,22.36816772888],[91.778508089483,22.369199878669],[91.777876261622,22.369716260739],[91.777242422104,22.370232020805],[91.775977090001,22.371276556979],[91.774772107601,22.372436420109],[91.773406863213,22.374158989698],[91.770375967025,22.376274657949],[91.769388914108,22.376215131966],[91.769260838628,22.376650415125],[91.768885664642,22.376565466809],[91.768510490656,22.376624372581],[91.767481863499,22.376726062487],[91.767244990915,22.377416187748],[91.766742579639,22.377555700525],[91.766134724021,22.377135301599],[91.765455454588,22.377204748117],[91.765254288912,22.377113599555],[91.764965951443,22.37709933821],[91.763944029808,22.377095617859],[91.763317734003,22.376732650626],[91.761554181576,22.376215907045],[91.761359721422,22.375887661101],[91.760950684547,22.375772716707],[91.760623455047,22.375707299895],[91.760119199753,22.375587782371],[91.759995818138,22.375106145859],[91.760065555572,22.374738135377],[91.760076284408,22.374248279982],[91.759735308587,22.373755012459],[91.75927631557,22.37331382953],[91.758462935686,22.373046576787],[91.758085414768,22.372908299644],[91.757852733136,22.372655928002],[91.757653746754,22.372242567689],[91.757511086763,22.372094600985],[91.757204309106,22.372185597813],[91.756975315512,22.37215505885],[91.756762415171,22.372223732753],[91.756382212043,22.372423088404],[91.755964457989,22.372483546127],[91.755316704512,22.372477965415],[91.754588484764,22.372551754808],[91.755216121674,22.366044496125],[91.755972504616,22.359536933325],[91.756036877633,22.359234306273]]]},\"center\":{\"lat\":22.360879144627,\"lng\":91.775775924325}}', 1, 1, 17, 'IT - Firoze Ahmed', 3, 'IT - Fahim Razzaq Ishraq', '2019-02-17 20:49:54', '2020-12-07 12:23:14');
INSERT INTO `locations` VALUES (146, 2, 'Khulshi', '{\"lat\":22.357643192363,\"lng\":91.796103715896,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.788110733032,22.361347720804],[91.792627573013,22.357517753501],[91.796951293945,22.34484636001],[91.797272488475,22.344874889304],[91.797336190939,22.345022497299],[91.797785460949,22.345039862935],[91.801473498345,22.345292904816],[91.80660456419,22.342757503642],[91.811628341675,22.34037090972],[91.812728047371,22.341234256104],[91.819846630096,22.346989762034],[91.817502379417,22.36064821391],[91.816968619823,22.361417175197],[91.816531419754,22.361774368673],[91.815237253904,22.361661505556],[91.81520909071,22.362798814324],[91.815109848977,22.363168406668],[91.815199702978,22.364047734846],[91.814559996128,22.364589715738],[91.813823729753,22.364566151396],[91.813178323209,22.365091077192],[91.812125220895,22.365358035151],[91.809586174786,22.365466244445],[91.810288494453,22.365653905207],[91.81153262034,22.365749789604],[91.813249181723,22.366024313554],[91.814330059569,22.366381931225],[91.815113159828,22.366548991625],[91.815619887784,22.366711963007],[91.816582591273,22.366616350569],[91.816579141596,22.366790747782],[91.816370502929,22.366785314601],[91.816055149538,22.36810518614],[91.815692663658,22.36967089717],[91.814967691899,22.372752660184],[91.812089011073,22.372396735029],[91.809116452932,22.372025927019],[91.803171336651,22.371284308034],[91.791281104088,22.369801058212],[91.786560416221,22.371069758661],[91.781823635101,22.372358290098],[91.772360801696,22.374915475006],[91.773508787155,22.374055747738],[91.774592399597,22.372650347298],[91.774779483676,22.372438512877],[91.774935722351,22.372286205822],[91.775312572717,22.371913382252],[91.775866448879,22.37139049745],[91.776063591241,22.371212378224],[91.780031919479,22.367938292322],[91.788110733032,22.361347720804]]]},\"center\":{\"lat\":22.357643192363,\"lng\":91.796103715896}}', 1, 1, 17, 'IT - Firoze Ahmed', 426, 'IT - Sakib Uddin Ahmad', '2019-02-17 20:51:23', '2020-12-07 12:41:07');
INSERT INTO `locations` VALUES (147, 2, 'Kotwali (Chittagong)', '{\"lat\":22.331868879907,\"lng\":91.829455643892,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.814675331115,22.342931162727],[91.811148226261,22.340008699586],[91.813672184944,22.338912139886],[91.812797784805,22.337195336791],[91.812250614166,22.315103256775],[91.828665733337,22.323003611698],[91.846389770508,22.326576489663],[91.847355365753,22.33613348846],[91.846432685852,22.337949541911],[91.847763061523,22.342732695183],[91.835542917251,22.345024978104],[91.835644841194,22.348190449689],[91.832029223442,22.348135873202],[91.831060945987,22.34863450304],[91.828247308731,22.34837898648],[91.82617932558,22.347825779119],[91.82411134243,22.346855800373],[91.819868087768,22.347019531274],[91.814675331115,22.342931162727]]]},\"center\":{\"lat\":22.331868879907,\"lng\":91.829455643892}}', 1, 1, 17, 'IT - Firoze Ahmed', 15, 'IT - Quaviur Rahman', '2019-02-17 20:54:07', '2020-01-21 16:44:30');
INSERT INTO `locations` VALUES (148, 2, 'Bakalia', '{\"lat\":22.338070629232,\"lng\":91.857858896255,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.847548484802,22.342693001641],[91.846336126328,22.338108321416],[91.847221255302,22.336163260019],[91.84711933136,22.333900603441],[91.846218109131,22.326636036853],[91.850445270538,22.326521904715],[91.855273246765,22.328551457938],[91.862182617188,22.333721971095],[91.866543889046,22.336188069646],[91.868244409562,22.340956398081],[91.86949968338,22.349619353749],[91.851840019226,22.34181974084],[91.847548484802,22.342693001641]]]},\"center\":{\"lat\":22.338070629232,\"lng\":91.857858896255}}', 1, 1, 17, 'IT - Firoze Ahmed', 3, 'IT - Fahim Razzaq Ishraq', '2019-02-17 20:55:32', '2019-07-01 17:47:30');
INSERT INTO `locations` VALUES (149, 2, 'Halishahar', '{\"lat\":22.322909360059,\"lng\":91.785600185394,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.75721168518,22.346979838952],[91.767897605896,22.30108795936],[91.782982349396,22.299857087023],[91.791576147079,22.300512230101],[91.792439818382,22.297941272213],[91.795897185802,22.296774895409],[91.798067092896,22.297117364502],[91.812229156495,22.31085514239],[91.812508106232,22.324998479842],[91.812905073166,22.337388850336],[91.813988685608,22.338981605463],[91.801500320435,22.345114287065],[91.787338256836,22.34420134832],[91.786308288574,22.349043824709],[91.75721168518,22.346979838952]]]},\"center\":{\"lat\":22.322909360059,\"lng\":91.785600185394}}', 1, 1, 17, 'IT - Firoze Ahmed', 3, 'IT - Fahim Razzaq Ishraq', '2019-02-17 20:57:09', '2019-04-10 13:57:29');
INSERT INTO `locations` VALUES (150, 2, 'Double Mooring', '{\"lat\":22.320971856777,\"lng\":91.799671053886,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.791887283325,22.32276541658],[91.785192489624,22.299658558211],[91.791114807129,22.300254143801],[91.792144775391,22.297673254576],[91.795749664307,22.296640885536],[91.812443733215,22.315262062283],[91.8129748106,22.337299536426],[91.814149618149,22.339209849255],[91.811606884003,22.340331215504],[91.801468133926,22.345302828018],[91.797337532044,22.345034901325],[91.791887283325,22.32276541658]]]},\"center\":{\"lat\":22.320971856777,\"lng\":91.799671053886}}', 1, 1, 17, 'IT - Firoze Ahmed', 3, 'IT - Fahim Razzaq Ishraq', '2019-02-17 20:58:23', '2019-07-01 17:47:30');
INSERT INTO `locations` VALUES (151, 2, 'Bandar', '{\"lat\":22.28834134654,\"lng\":91.784377098083,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.78231716156,22.291478925927],[91.783969402313,22.286872708693],[91.784634590149,22.281055157109],[91.786308288574,22.275356501618],[91.801028251648,22.277739242962],[91.8039894104,22.277342122223],[91.79535806179,22.283638835111],[91.794561445713,22.291591845833],[91.795996427536,22.29684438196],[91.792466640472,22.298000831619],[91.791586875916,22.300631346694],[91.783304214478,22.30001590987],[91.767768859863,22.301326191463],[91.764764785767,22.29076417803],[91.78231716156,22.291478925927]]]},\"center\":{\"lat\":22.28834134654,\"lng\":91.784377098083}}', 1, 1, 17, 'IT - Firoze Ahmed', 3, 'IT - Fahim Razzaq Ishraq', '2019-02-17 21:01:01', '2019-07-01 17:47:31');
INSERT INTO `locations` VALUES (152, 2, 'Patenga', '{\"lat\":22.25779829989,\"lng\":91.799869537354,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.76459312439,22.290843594644],[91.76317691803,22.277858378964],[91.763498783112,22.273867267664],[91.773691177368,22.253274501414],[91.80004119873,22.223958841484],[91.823644638062,22.241239139254],[91.832485198975,22.251367978328],[91.836562156677,22.262528713375],[91.827968358994,22.268734253147],[91.815941333771,22.272080165992],[91.803570985794,22.277332194191],[91.80107653141,22.27797255087],[91.78626537323,22.275515352303],[91.784698963165,22.281075012646],[91.784119606018,22.286713870908],[91.782360076904,22.291637758297],[91.76459312439,22.290843594644]]]},\"center\":{\"lat\":22.25779829989,\"lng\":91.799869537354}}', 1, 1, 17, 'IT - Firoze Ahmed', 3, 'IT - Fahim Razzaq Ishraq', '2019-02-17 21:02:35', '2019-07-01 17:47:32');
INSERT INTO `locations` VALUES (153, 2, 'Karnafuli', '{\"lat\":22.266777358098,\"lng\":91.858577728271,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[91.797552108764,22.297911492501],[91.794676780701,22.291399509675],[91.795041561127,22.283775339346],[91.798667907715,22.281233856836],[91.809310913086,22.274482819621],[91.821498870849,22.270432040882],[91.840051794424,22.26329216475],[91.832505441271,22.242801569764],[91.8204925186,22.233111294721],[91.815344364149,22.227948097182],[91.813800255186,22.213606779972],[91.815774782735,22.192767017899],[91.846931744367,22.260600285852],[91.868059588596,22.270458568652],[91.873394586146,22.271102835109],[91.875165179372,22.271932480875],[91.876131445169,22.27184436615],[91.876862347126,22.271985846103],[91.873860955238,22.283705846298],[91.87644124031,22.294119490626],[91.911127567291,22.304462876258],[91.922478675842,22.316254592615],[91.916341781616,22.340787698297],[91.902810037136,22.338996490939],[91.898075938224,22.33982511327],[91.896547079086,22.337949541911],[91.893017292022,22.337175489234],[91.88793182373,22.337334269621],[91.882653236389,22.34003350853],[91.877160072326,22.337691524829],[91.863899230957,22.329514120781],[91.865272521972,22.335359425702],[91.852741241455,22.326278753327],[91.840596199035,22.325574108134],[91.825017929077,22.321058339676],[91.800727844238,22.300373260614],[91.797552108764,22.297911492501]]]},\"center\":{\"lat\":22.266777358098,\"lng\":91.858577728271}}', 0, 1, 17, 'IT - Firoze Ahmed', 15, 'IT - Quaviur Rahman', '2019-02-17 21:03:49', '2020-03-20 11:50:59');
INSERT INTO `locations` VALUES (154, 5, 'monirampur', '{\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.17911529541,23.047195970691],[89.168815612793,22.973882682902],[89.30477142334,22.973250498669],[89.315071105957,23.04150929687],[89.17911529541,23.047195970691]]]},\"center\":{\"lat\":23.01022323468,\"lng\":89.241943359375},\"lat\":23.01022323468,\"lng\":89.241943359375}', 0, 1, 17, 'IT - Firoze Ahmed', 17, 'IT - Firoze Ahmed', '2019-02-17 21:03:49', '2019-07-01 17:47:32');
INSERT INTO `locations` VALUES (155, 5, 'abhoynagar', '{\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.398927688599,23.017496250135],[89.396481513977,23.007344712448],[89.417638778687,23.007976737093],[89.410772323608,23.017456751469],[89.398927688599,23.017496250135]]]},\"center\":{\"lat\":23.012420481291,\"lng\":89.407060146332},\"lat\":23.012420481291,\"lng\":89.407060146332}', 0, 1, 17, 'IT - Firoze Ahmed', 17, 'IT - Firoze Ahmed', '2019-02-17 21:03:49', '2019-07-01 17:47:33');
INSERT INTO `locations` VALUES (156, 5, 'bagherpara', '{\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.303741455078,23.23976878109],[89.284515380859,23.19812075764],[89.372406005859,23.198751885174],[89.376525878906,23.235036705325],[89.303741455078,23.23976878109]]]},\"center\":{\"lat\":23.218944769365,\"lng\":89.330520629882},\"lat\":23.218944769365,\"lng\":89.330520629882}', 0, 1, 17, 'IT - Firoze Ahmed', 17, 'IT - Firoze Ahmed', '2019-02-17 21:03:49', '2019-07-01 17:47:33');
INSERT INTO `locations` VALUES (157, 5, 'chowgacha', '{\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[88.993377685547,23.284242086524],[88.98307800293,23.245762502805],[89.042816162109,23.241346102386],[89.057922363281,23.284242086524],[88.993377685547,23.284242086524]]]},\"center\":{\"lat\":23.262794094455,\"lng\":89.020500183105},\"lat\":23.262794094455,\"lng\":89.020500183105}', 0, 1, 17, 'IT - Firoze Ahmed', 17, 'IT - Firoze Ahmed', '2019-02-17 21:03:49', '2019-07-01 17:47:34');
INSERT INTO `locations` VALUES (158, 5, 'keshabpur', '{\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.180660247803,22.933891211367],[89.171562194824,22.893255368021],[89.240226745605,22.889459959328],[89.245548248291,22.93231027798],[89.180660247803,22.933891211367]]]},\"center\":{\"lat\":22.911675585348,\"lng\":89.208555221558},\"lat\":22.911675585348,\"lng\":89.208555221558}', 0, 1, 17, 'IT - Firoze Ahmed', 17, 'IT - Firoze Ahmed', '2019-02-17 21:03:49', '2019-07-01 17:47:34');
INSERT INTO `locations` VALUES (159, 5, 'jhikargacha', '{\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.07886505127,23.111470263925],[89.059295654297,23.071835448649],[89.132766723633,23.065833940119],[89.137573242188,23.109101977956],[89.07886505127,23.111470263925]]]},\"center\":{\"lat\":23.088652102022,\"lng\":89.098434448243},\"lat\":23.088652102022,\"lng\":89.098434448243}', 0, 1, 17, 'IT - Firoze Ahmed', 17, 'IT - Firoze Ahmed', '2019-02-17 21:03:49', '2019-07-01 17:47:34');
INSERT INTO `locations` VALUES (160, 5, 'sarsha', '{\"lat\":23.123620529154,\"lng\":88.988914489746,\"radius\":\"1\",\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[88.94359588623,23.159932001374],[88.902397155762,23.082574321912],[89.050025939941,23.098049424484],[89.07543182373,23.164666736396],[88.94359588623,23.159932001374]]]},\"center\":{\"lat\":23.123620529154,\"lng\":88.988914489746}}', 0, 1, 17, 'IT - Firoze Ahmed', 6, 'IT - Hasan Hafiz Pasha', '2019-02-17 21:03:49', '2019-07-01 17:47:35');
INSERT INTO `locations` VALUES (161, 1, 'Oxyzen', '{\"lat\":\"22.3933767\",\"lng\":\"91.82234449999999\",\"radius\":\"10\"}', 0, 0, 237, 'PM - Rakebul Hasan', 237, 'PM - Rakebul Hasan', '2019-03-21 11:37:32', '2019-03-21 11:37:32');
INSERT INTO `locations` VALUES (162, 1, 'Bhaddarhat', '{\"lat\":\"22.3702767\",\"lng\":\"91.84341619999998\",\"radius\":\"3\"}', 0, 0, 237, 'PM - Rakebul Hasan', 237, 'PM - Rakebul Hasan', '2019-03-21 11:46:23', '2019-03-21 11:46:23');
INSERT INTO `locations` VALUES (163, 1, 'Purbachal', '{\"lat\":\"\",\"lng\":\"\",\"radius\":\"3\"}', 0, 0, 463, 'IT - Md Sharif Newaz Chowdhury', 463, 'IT - Md Sharif Newaz Chowdhury', '2020-03-06 11:04:23', '2020-03-06 11:04:23');
INSERT INTO `locations` VALUES (164, 1, 'akbar shah', '{\"lat\":\"23.8191441\",\"lng\":\"90.45259539999999\",\"radius\":\"1\"}', 0, 0, 479, 'IT - Abdur Rakib', 479, 'IT - Abdur Rakib', '2020-06-30 12:09:59', '2020-06-30 12:09:59');
INSERT INTO `locations` VALUES (165, 1, 'Ashkona', '{\"lat\":\"23.853447\",\"lng\":\"90.4184093\",\"radius\":\"3\"}', 1, 0, 551, 'IT - Hasibur Rashid Mahi', 551, 'IT - Hasibur Rashid Mahi', '2020-09-10 13:06:43', '2020-09-10 13:06:43');
INSERT INTO `locations` VALUES (166, 1, 'Koshaibari', '{\"lat\":\"23.8586738\",\"lng\":\"90.4065585\",\"radius\":\"2.5\"}', 0, 0, 509, 'PM - Syed Mohaiminur Rahman Rahat', 509, 'PM - Syed Mohaiminur Rahman Rahat', '2020-09-10 13:10:43', '2020-09-10 13:10:43');
INSERT INTO `locations` VALUES (167, 1, 'chiitagong', '{\"lat\":\"\",\"lng\":\"\",\"radius\":\"1\"}', 0, 0, 72, 'IT - Tabassum Saima Tisha', 72, 'IT - Tabassum Saima Tisha', '2020-11-19 13:32:16', '2020-11-19 13:32:16');
INSERT INTO `locations` VALUES (168, 6, 'Matidali', '{\"lat\":24.88551165,\"lng\":89.3597427,\"radius\":0.436,\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.3603435,24.8887623],[89.3601289,24.8887721],[89.3563846,24.8846455],[89.3571249,24.8833023],[89.3588307,24.8836332],[89.359217,24.8823388],[89.3593457,24.8822512],[89.3609658,24.8826794],[89.3612125,24.8823582],[89.3631008,24.8829714],[89.3614808,24.8853754],[89.3610838,24.8860372],[89.3609229,24.8868256],[89.3607619,24.8878864],[89.360601,24.8885969],[89.3603435,24.8887623]]]},\"center\":{\"lat\":24.88551165,\"lng\":89.3597427}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:50', '2020-12-15 20:32:49');
INSERT INTO `locations` VALUES (169, 6, 'Jhopgari', '{\"lat\":24.8788731,\"lng\":89.35959485,\"radius\":0.988,\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.3631008,24.8829714],[89.3612125,24.8823582],[89.3609658,24.8826794],[89.3593457,24.8822512],[89.359217,24.8823388],[89.3588307,24.8836332],[89.3571249,24.8833023],[89.3563846,24.8846455],[89.3527472,24.8805127],[89.3520283,24.8791062],[89.3517977,24.877802],[89.3518835,24.8763322],[89.352141,24.8731007],[89.353069,24.8732029],[89.354555,24.8733538],[89.3555742,24.8733684],[89.3557834,24.8732954],[89.3559765,24.8737967],[89.3560785,24.8739816],[89.3564808,24.8744342],[89.3583422,24.8743077],[89.3589591,24.8744196],[89.3610888,24.8745121],[89.3614161,24.8743272],[89.3631649,24.8743564],[89.3644738,24.8745559],[89.3644845,24.8747846],[89.3645757,24.8748041],[89.3648278,24.874843],[89.3653267,24.8749355],[89.3658685,24.8750718],[89.3666893,24.8753589],[89.367392,24.8756071],[89.3668878,24.8761716],[89.3655252,24.8784103],[89.3647849,24.8799968],[89.364463,24.8808241],[89.3641734,24.8814957],[89.36383,24.8820699],[89.3631008,24.8829714]]]},\"center\":{\"lat\":24.8788731,\"lng\":89.35959485}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:50', '2020-12-15 20:32:49');
INSERT INTO `locations` VALUES (170, 6, 'Uposhohor', '{\"lat\":24.8681342,\"lng\":89.3561901,\"radius\":0.878,\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.3513187,24.8621377],[89.3520107,24.8620841],[89.3527993,24.8620939],[89.3529173,24.8620257],[89.3530353,24.8618116],[89.354017,24.8619624],[89.3546822,24.8620306],[89.3550041,24.8619722],[89.3569299,24.8619868],[89.3569299,24.8643133],[89.3609371,24.8667809],[89.3610615,24.8668467],[89.3607638,24.8669343],[89.3604795,24.8672141],[89.3602837,24.8681486],[89.3599592,24.869122],[89.3599672,24.8692753],[89.3604205,24.8699834],[89.3604715,24.8707037],[89.3600378,24.8717662],[89.3599573,24.8725449],[89.3598554,24.8727347],[89.3596784,24.8735913],[89.3596998,24.874039],[89.3598211,24.8743571],[89.3598131,24.8744568],[89.3589591,24.8744196],[89.3583422,24.8743077],[89.3564808,24.8744342],[89.3560785,24.8739816],[89.3559765,24.8737967],[89.3557834,24.8732954],[89.3555742,24.8733684],[89.354555,24.8733538],[89.352141,24.8731007],[89.3523805,24.8695744],[89.3524717,24.8684306],[89.3524073,24.8674718],[89.3518441,24.8646927],[89.3514793,24.86296],[89.3513187,24.8621377]]]},\"center\":{\"lat\":24.8681342,\"lng\":89.3561901}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:50', '2020-12-15 20:32:49');
INSERT INTO `locations` VALUES (171, 6, 'Atapara', '{\"lat\":24.8700101,\"lng\":89.365527,\"radius\":0.698,\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.3676217,24.8753536],[89.367392,24.8756071],[89.3658685,24.8750718],[89.3653267,24.8749355],[89.3645757,24.8748041],[89.3644845,24.8747846],[89.3644738,24.8745559],[89.3631649,24.8743564],[89.3614161,24.8743272],[89.3610888,24.8745121],[89.3598131,24.8744568],[89.3598211,24.8743571],[89.3596998,24.874039],[89.3596784,24.8735913],[89.3598554,24.8727347],[89.3599573,24.8725449],[89.3600378,24.8717662],[89.3602435,24.871244],[89.3604715,24.8707037],[89.3604205,24.8699834],[89.3599672,24.8692753],[89.3599592,24.869122],[89.3601469,24.8685453],[89.3602837,24.8681486],[89.3603588,24.8677422],[89.3604795,24.8672141],[89.3607638,24.8669343],[89.3610615,24.8668467],[89.3614505,24.8665984],[89.3617509,24.8662091],[89.361767,24.8659584],[89.3619279,24.8656055],[89.3622873,24.8653281],[89.3626977,24.8649874],[89.3631429,24.8644131],[89.3632878,24.8645275],[89.3633843,24.8647319],[89.3633709,24.8649144],[89.3634353,24.8650945],[89.3638028,24.8653817],[89.3638752,24.8654449],[89.363851,24.865552],[89.3641246,24.8656688],[89.3641246,24.8657467],[89.3643875,24.8659268],[89.3646101,24.866012],[89.3653128,24.8662066],[89.3673352,24.8665595],[89.3677429,24.8665692],[89.3681623,24.8666124],[89.3690555,24.866829],[89.3697288,24.8669921],[89.3705415,24.8671113],[89.3713756,24.8672865],[89.370674,24.8686957],[89.370041,24.8696788],[89.3697191,24.8707301],[89.3691505,24.8729591],[89.368319,24.8745359],[89.3676217,24.8753536]]]},\"center\":{\"lat\":24.8700101,\"lng\":89.365527}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:50', '2020-12-15 20:32:49');
INSERT INTO `locations` VALUES (172, 6, 'Fulbari', '{\"lat\":24.86806,\"lng\":89.37501,\"radius\":2.09,\"geometry\":null,\"center\":{\"lat\":24.86806,\"lng\":89.37501}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:50', '2020-12-15 16:03:50');
INSERT INTO `locations` VALUES (173, 6, 'Brindabon Para', '{\"lat\":24.8636,\"lng\":89.3678577,\"radius\":0.619,\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.3713932,24.8672439],[89.3713756,24.8672865],[89.3705415,24.8671113],[89.3697288,24.8669921],[89.3681623,24.8666124],[89.3677429,24.8665692],[89.3673352,24.8665595],[89.3653128,24.8662066],[89.3646101,24.866012],[89.3643875,24.8659268],[89.3641246,24.8657467],[89.3641246,24.8656688],[89.363851,24.865552],[89.3638752,24.8654449],[89.3634353,24.8650945],[89.3633709,24.8649144],[89.3633843,24.8647319],[89.3632878,24.8645275],[89.3631429,24.8644131],[89.363748,24.8632208],[89.3644669,24.8625832],[89.3654834,24.8619577],[89.3656792,24.8617776],[89.3657034,24.8614905],[89.3657704,24.8614077],[89.3664705,24.8611546],[89.3680691,24.860225],[89.3689247,24.8599135],[89.3697481,24.8599354],[89.3700968,24.8601495],[89.3702819,24.8603953],[89.3712475,24.8602444],[89.3712529,24.8599719],[89.3725725,24.8600108],[89.3724983,24.8614654],[89.3721469,24.8629645],[89.3720933,24.863697],[89.3722381,24.8650914],[89.371982,24.8659748],[89.3713932,24.8672439]]]},\"center\":{\"lat\":24.8636,\"lng\":89.3678577}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:51', '2020-12-15 20:32:49');
INSERT INTO `locations` VALUES (174, 6, 'Snigdha R/A', '{\"lat\":24.86207,\"lng\":89.36123,\"radius\":0.719,\"geometry\":null,\"center\":{\"lat\":24.86207,\"lng\":89.36123}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:51', '2020-12-15 16:03:51');
INSERT INTO `locations` VALUES (175, 6, 'Nisindara', '{\"lat\":24.8587048,\"lng\":89.354498,\"radius\":0.566,\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.3569401,24.8618419],[89.3569299,24.8619868],[89.3550041,24.8619722],[89.3546822,24.8620306],[89.354017,24.8619624],[89.3530353,24.8618116],[89.3529173,24.8620257],[89.3527993,24.8620939],[89.3520107,24.8620841],[89.3513187,24.8621377],[89.3505569,24.8583335],[89.3512588,24.8579247],[89.3526536,24.8570023],[89.3533188,24.8565302],[89.3550166,24.8557222],[89.3563765,24.8555567],[89.3573769,24.8554544],[89.3583533,24.8552719],[89.3583962,24.8559826],[89.3584391,24.8565472],[89.3583929,24.8571446],[89.3584036,24.8575924],[89.358307,24.8580791],[89.3581407,24.8582057],[89.3580254,24.8587557],[89.3580254,24.8589918],[89.3579262,24.8591378],[89.3578457,24.8596148],[89.3578511,24.8598387],[89.3578618,24.8601867],[89.3577867,24.8603887],[89.3573951,24.8600674],[89.3569874,24.8600772],[89.3569401,24.8618419]]]},\"center\":{\"lat\":24.8587048,\"lng\":89.354498}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:51', '2020-12-15 20:32:49');
INSERT INTO `locations` VALUES (176, 6, 'Gowlgari', '{\"lat\":24.85655705,\"lng\":89.36549165,\"radius\":0.888,\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.3725824,24.8598926],[89.3725725,24.8600108],[89.3712529,24.8599719],[89.3712475,24.8602444],[89.3702819,24.8603953],[89.3700968,24.8601495],[89.3697481,24.8599354],[89.3689247,24.8599135],[89.3680691,24.860225],[89.3675101,24.8605617],[89.3670327,24.8592597],[89.3668932,24.8586756],[89.3647447,24.8589749],[89.36438,24.8592524],[89.3638972,24.8593448],[89.3635807,24.859301],[89.3624515,24.8595201],[89.3614295,24.8594763],[89.3610299,24.8593521],[89.3606249,24.8593327],[89.3596512,24.8592378],[89.3595332,24.8595736],[89.3591309,24.859469],[89.3589083,24.8594617],[89.358919,24.859228],[89.358809,24.859228],[89.3588144,24.8591185],[89.3582672,24.8591015],[89.3580254,24.8589918],[89.3580254,24.8587557],[89.3581407,24.8582057],[89.358305,24.8580941],[89.3584036,24.8575924],[89.3583929,24.8571446],[89.3584418,24.8566899],[89.3584096,24.8563783],[89.3583533,24.8552719],[89.3618214,24.8545579],[89.3646431,24.854811],[89.3658447,24.8547331],[89.3664348,24.8543827],[89.3675506,24.8544216],[89.3680119,24.8540809],[89.369235,24.8539933],[89.3699002,24.8538083],[89.3703937,24.8535746],[89.3709838,24.8529224],[89.3724537,24.8527277],[89.3729579,24.8525524],[89.3729472,24.8532242],[89.3724859,24.8542074],[89.3725824,24.8598926]]]},\"center\":{\"lat\":24.85655705,\"lng\":89.36549165}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:51', '2020-12-15 20:32:49');
INSERT INTO `locations` VALUES (177, 6, 'Jahurul Nagar', '{\"lat\":24.85238705,\"lng\":89.35512775,\"radius\":0.89,\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.3481137,24.8464406],[89.3489854,24.8465331],[89.3494414,24.8465453],[89.3500959,24.8467327],[89.3511741,24.8467887],[89.3516247,24.8468374],[89.3520941,24.8469566],[89.352655,24.8470597],[89.3531941,24.8472569],[89.3539693,24.8473226],[89.3553211,24.8476049],[89.3559,24.8478777],[89.356305,24.8478777],[89.3575697,24.8481223],[89.3594137,24.8484424],[89.3604077,24.8485018],[89.3621418,24.8489885],[89.3610555,24.8539852],[89.3610447,24.8547251],[89.3583533,24.8552719],[89.3573769,24.8554544],[89.3550166,24.8557222],[89.3533188,24.8565302],[89.3526536,24.8570023],[89.3518592,24.8575281],[89.3512588,24.8579247],[89.3505569,24.8583335],[89.3481137,24.8464406]]]},\"center\":{\"lat\":24.85238705,\"lng\":89.35512775}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:51', '2020-12-15 20:32:49');
INSERT INTO `locations` VALUES (178, 6, 'Badurdola', '{\"lat\":24.8507772,\"lng\":89.36701675,\"radius\":0.75,\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.3729888,24.8509784],[89.3729579,24.8525524],[89.3724537,24.8527277],[89.3709838,24.8529224],[89.3703937,24.8535746],[89.3699002,24.8538083],[89.369235,24.8539933],[89.3680119,24.8540809],[89.3675506,24.8544216],[89.3664348,24.8543827],[89.3658447,24.8547331],[89.3646431,24.854811],[89.3618214,24.8545579],[89.3610447,24.8547251],[89.3610555,24.8539852],[89.3621418,24.8489885],[89.3626945,24.8467434],[89.3692069,24.8490459],[89.3693624,24.8495911],[89.3729888,24.8509784]]]},\"center\":{\"lat\":24.8507772,\"lng\":89.36701675}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:51', '2020-12-15 20:32:49');
INSERT INTO `locations` VALUES (179, 6, 'Sath Matha', '{\"lat\":24.8514191,\"lng\":89.37473655,\"radius\":0.444,\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.3725719,24.8548632],[89.3724976,24.8548701],[89.3724859,24.8542074],[89.3729472,24.8532242],[89.3729579,24.8525524],[89.3729888,24.8509784],[89.3728927,24.8481208],[89.3728205,24.8480245],[89.3729317,24.8479681],[89.3754981,24.8482675],[89.3769872,24.8486983],[89.3756989,24.8508006],[89.374127,24.8531967],[89.3737869,24.8545103],[89.3738613,24.8548302],[89.3725719,24.8548632]]]},\"center\":{\"lat\":24.8514191,\"lng\":89.37473655}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:51', '2020-12-15 20:32:50');
INSERT INTO `locations` VALUES (180, 6, 'Naruli', '{\"lat\":24.8529785,\"lng\":89.3775991,\"radius\":0.464,\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.3738613,24.8548302],[89.3737891,24.8545304],[89.374127,24.8531967],[89.3756989,24.8508006],[89.3759403,24.8508444],[89.3765974,24.8507714],[89.3769475,24.8509101],[89.377162,24.8511924],[89.3774383,24.851908],[89.3776315,24.8520667],[89.3787876,24.8521032],[89.3794457,24.8524034],[89.3803201,24.8527856],[89.3806339,24.8529121],[89.3814091,24.853209],[89.3788932,24.8539687],[89.3778364,24.8542802],[89.3757121,24.8551418],[89.3754546,24.8551856],[89.3750844,24.8550834],[89.3743656,24.8547572],[89.3738613,24.8548302]]]},\"center\":{\"lat\":24.8529785,\"lng\":89.3775991}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:51', '2020-12-15 20:32:50');
INSERT INTO `locations` VALUES (181, 6, 'Bou Bazar', '{\"lat\":24.84857795,\"lng\":89.38253865,\"radius\":0.748,\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.3843606,24.8431058],[89.3846074,24.8432129],[89.385007,24.8435951],[89.3851867,24.8436535],[89.3854228,24.8435829],[89.385235,24.8443374],[89.3854603,24.8444518],[89.3859619,24.8445687],[89.3865567,24.8446258],[89.3875438,24.8449495],[89.3880641,24.8450347],[89.3893784,24.8458282],[89.3892336,24.8462468],[89.3890512,24.8481964],[89.388901,24.8484592],[89.3886864,24.8485858],[89.3886972,24.849351],[89.3881339,24.8494094],[89.3882465,24.850122],[89.3881312,24.8507925],[89.3881392,24.85153],[89.3879273,24.8519097],[89.387753,24.8526715],[89.3876269,24.8535136],[89.3874982,24.8539005],[89.3874365,24.8543289],[89.3836907,24.8540892],[89.3802172,24.8527482],[89.3787876,24.8521032],[89.3776315,24.8520667],[89.3774383,24.851908],[89.377162,24.8511924],[89.3769475,24.8509101],[89.3765974,24.8507714],[89.3759403,24.8508444],[89.3756989,24.8508006],[89.3769872,24.8486983],[89.378931,24.8464342],[89.3808657,24.8441906],[89.3819156,24.843557],[89.3842712,24.842827],[89.3843606,24.8431058]]]},\"center\":{\"lat\":24.84857795,\"lng\":89.38253865}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:51', '2020-12-15 20:32:50');
INSERT INTO `locations` VALUES (182, 6, 'Jowelasory Tola', '{\"lat\":24.8447887,\"lng\":89.3773516,\"radius\":0.578,\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.3729317,24.8479681],[89.3728205,24.8480245],[89.3727876,24.8454535],[89.3731048,24.8439647],[89.3736237,24.8430448],[89.37399,24.8421173],[89.3740152,24.8409404],[89.3741647,24.8408937],[89.3766457,24.8408791],[89.3791393,24.8410971],[89.3794826,24.8409979],[89.3798629,24.8410104],[89.3799335,24.8410937],[89.379735,24.8417022],[89.379676,24.8420625],[89.3794346,24.8425882],[89.3804672,24.8428876],[89.3813711,24.8428803],[89.3817681,24.8431578],[89.3819156,24.843557],[89.3808657,24.8441906],[89.3769872,24.8486983],[89.3755174,24.8482796],[89.3729317,24.8479681]]]},\"center\":{\"lat\":24.8447887,\"lng\":89.3773516}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:51', '2020-12-15 20:32:50');
INSERT INTO `locations` VALUES (183, 6, 'Sheujgari', '{\"lat\":24.8447305,\"lng\":89.3687087,\"radius\":0.89,\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.3685153,24.8385552],[89.3690786,24.8398112],[89.3695667,24.8413398],[89.3701407,24.8428197],[89.3706664,24.8443969],[89.3711975,24.8460179],[89.3723991,24.8474393],[89.3728927,24.8481208],[89.3729888,24.8509784],[89.3693624,24.8495911],[89.3692069,24.8490459],[89.3644286,24.8473716],[89.3647076,24.8467193],[89.3649865,24.8455778],[89.3683651,24.8386574],[89.3684492,24.8384826],[89.3685153,24.8385552]]]},\"center\":{\"lat\":24.8447305,\"lng\":89.3687087}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:51', '2020-12-15 20:32:50');
INSERT INTO `locations` VALUES (184, 6, 'Kamargari', '{\"lat\":24.8453464,\"lng\":89.3548877,\"radius\":0.9,\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.3480912,24.8463794],[89.3477354,24.8448198],[89.3470809,24.8417043],[89.3484069,24.8419789],[89.3516871,24.8426898],[89.3580386,24.8449777],[89.3626945,24.8467434],[89.3621418,24.8489885],[89.3604077,24.8485018],[89.3594137,24.8484424],[89.3575697,24.8481223],[89.356305,24.8478777],[89.3559,24.8478777],[89.3553211,24.8476049],[89.3539693,24.8473226],[89.3531941,24.8472569],[89.352655,24.8470597],[89.3520941,24.8469566],[89.3516247,24.8468374],[89.3500959,24.8467327],[89.3494414,24.8465453],[89.3489854,24.8465331],[89.3481137,24.8464406],[89.3480912,24.8463794]]]},\"center\":{\"lat\":24.8453464,\"lng\":89.3548877}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:52', '2020-12-15 20:32:50');
INSERT INTO `locations` VALUES (185, 6, 'Khandar', '{\"lat\":24.8345174,\"lng\":89.3575281,\"radius\":1.74,\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.3470362,24.8415387],[89.346607,24.8393091],[89.3467412,24.83743],[89.3471703,24.8356628],[89.3484541,24.8334619],[89.3497738,24.8319673],[89.3591079,24.8237588],[89.3606687,24.8227149],[89.3634475,24.8216632],[89.3638927,24.8234598],[89.3648798,24.8251347],[89.3653381,24.8261512],[89.3653488,24.8273684],[89.3657726,24.8284687],[89.3664324,24.8303285],[89.3670118,24.8311707],[89.3673604,24.8333664],[89.3681088,24.8349392],[89.3681732,24.8360005],[89.3681971,24.8375674],[89.3684492,24.8384826],[89.3655632,24.8442512],[89.3649865,24.8455778],[89.3647076,24.8467193],[89.3644286,24.8473716],[89.3626945,24.8467434],[89.3589757,24.8453368],[89.3524525,24.8429612],[89.3516871,24.8426898],[89.3470809,24.8417043],[89.3470362,24.8415387]]]},\"center\":{\"lat\":24.8345174,\"lng\":89.3575281}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:52', '2020-12-15 20:32:50');
INSERT INTO `locations` VALUES (186, 6, 'Kanusgari', '{\"lat\":24.84155675,\"lng\":89.37135715,\"radius\":0.878,\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.3682262,24.8352862],[89.3695217,24.8352083],[89.3701333,24.835089],[89.3710103,24.8352278],[89.3710479,24.835644],[89.3732071,24.8359556],[89.3731418,24.836126],[89.3745848,24.8363718],[89.3740329,24.8401311],[89.37399,24.8421173],[89.3736252,24.8430422],[89.3731048,24.8439647],[89.372791,24.8454446],[89.3728205,24.8480245],[89.3723991,24.8474393],[89.3711975,24.8460179],[89.370884,24.8450527],[89.3705702,24.8441156],[89.3701946,24.8429887],[89.3695667,24.8413398],[89.3692961,24.8404865],[89.3690786,24.8398112],[89.3685153,24.8385552],[89.3684492,24.8384826],[89.3681971,24.8375674],[89.3681872,24.8368952],[89.3681751,24.8360566],[89.3681295,24.8352935],[89.3682262,24.8352862]]]},\"center\":{\"lat\":24.84155675,\"lng\":89.37135715}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:52', '2020-12-15 20:32:50');
INSERT INTO `locations` VALUES (187, 6, 'Rahman Nagar', '{\"lat\":24.83727865,\"lng\":89.3779809,\"radius\":0.625,\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.3741647,24.8408937],[89.3740152,24.8409404],[89.3740329,24.8401311],[89.3742272,24.8388749],[89.3745813,24.8364019],[89.3747241,24.8357466],[89.3750211,24.8351507],[89.3756434,24.8338679],[89.3757473,24.8334617],[89.3819466,24.8340578],[89.38126,24.8357276],[89.3811875,24.8363361],[89.381054,24.8363051],[89.3806033,24.8362564],[89.380539,24.836899],[89.380402,24.8369893],[89.3801874,24.8375054],[89.380166,24.8378072],[89.3800372,24.8382161],[89.3799943,24.838479],[89.3800372,24.8388831],[89.3799514,24.8400514],[89.3798817,24.8403143],[89.3798629,24.8410104],[89.3794874,24.8409983],[89.3791441,24.8410956],[89.3784723,24.8410349],[89.3766457,24.8408791],[89.3741647,24.8408937]]]},\"center\":{\"lat\":24.83727865,\"lng\":89.3779809}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:52', '2020-12-15 20:32:50');
INSERT INTO `locations` VALUES (188, 6, 'Maloti Nagor', '{\"lat\":24.838811,\"lng\":89.3835491,\"radius\":0.6,\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.3799335,24.8410937],[89.3798629,24.8410104],[89.3798798,24.840321],[89.37995,24.840059],[89.3800372,24.8388943],[89.3799929,24.8384939],[89.3800318,24.8382285],[89.380166,24.8378072],[89.3801874,24.8375054],[89.380402,24.8369893],[89.380539,24.836899],[89.3806033,24.8362564],[89.381054,24.8363051],[89.3811875,24.8363361],[89.3812588,24.8357302],[89.3814788,24.8351963],[89.381572,24.8349681],[89.3819433,24.834065],[89.3857975,24.8341169],[89.3863366,24.8341238],[89.3860543,24.8350985],[89.3876636,24.8387449],[89.3857056,24.8423789],[89.3819156,24.843557],[89.3817681,24.8431578],[89.3813711,24.8428803],[89.3804672,24.8428876],[89.3794346,24.8425882],[89.379676,24.8420625],[89.379735,24.8417022],[89.3799335,24.8410937]]]},\"center\":{\"lat\":24.838811,\"lng\":89.3835491}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:52', '2020-12-15 20:32:50');
INSERT INTO `locations` VALUES (189, 6, 'Koigari', '{\"lat\":24.82579115,\"lng\":89.37154,\"radius\":1.58,\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.3635285,24.8216521],[89.3714142,24.8194124],[89.3741823,24.8186383],[89.3796325,24.8152105],[89.3788171,24.8200503],[89.3788386,24.8220952],[89.3777764,24.82748],[89.3775833,24.8290769],[89.3768698,24.8303719],[89.3756682,24.833809],[89.3747241,24.8357466],[89.3745848,24.8363718],[89.3731418,24.836126],[89.3732071,24.8359556],[89.3710479,24.835644],[89.3710103,24.8352278],[89.3701333,24.835089],[89.3695217,24.8352083],[89.3681295,24.8352935],[89.3681088,24.8349392],[89.3673604,24.8333664],[89.3670118,24.8311707],[89.3664324,24.8303285],[89.3653488,24.8273684],[89.3653381,24.8261512],[89.3648798,24.8251347],[89.3638927,24.8234598],[89.3634475,24.8216632],[89.3635285,24.8216521]]]},\"center\":{\"lat\":24.82579115,\"lng\":89.37154}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:52', '2020-12-15 20:32:51');
INSERT INTO `locations` VALUES (190, 6, 'Colony', '{\"lat\":24.82466715,\"lng\":89.3832521,\"radius\":1.36,\"geometry\":{\"type\":\"Polygon\",\"coordinates\":[[[89.3796738,24.8152233],[89.3827637,24.8153401],[89.3835308,24.8164308],[89.3842445,24.8166452],[89.385296,24.8179501],[89.3863152,24.8218452],[89.3872271,24.8234617],[89.3877421,24.8253313],[89.3898235,24.8297714],[89.3907569,24.8320791],[89.3907569,24.8327412],[89.3898021,24.833228],[89.3876241,24.8334033],[89.3863366,24.8341238],[89.3818842,24.8340654],[89.3757473,24.8334617],[89.3765841,24.8312125],[89.3768698,24.8303719],[89.3775833,24.8290769],[89.3777965,24.8274053],[89.3788386,24.8220952],[89.3788171,24.8200503],[89.3796325,24.8152105],[89.3796738,24.8152233]]]},\"center\":{\"lat\":24.82466715,\"lng\":89.3832521}}', 1, 1, 1, 'IT - Shafiqul Islam', 1, 'IT - Shafiqul Islam', '2020-12-15 16:03:52', '2020-12-15 20:32:51');
COMMIT;

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
-- Records of materials
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of meals
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=2531 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of members
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=52175 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of mentions
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of meta_tags
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of migrations
-- ----------------------------
BEGIN;
INSERT INTO `migrations` VALUES ('2016_09_24_000000_create_departments_table', 1);
INSERT INTO `migrations` VALUES ('2016_09_24_000000_create_password_resets_table', 1);
INSERT INTO `migrations` VALUES ('2016_09_24_000000_create_users_table', 1);
INSERT INTO `migrations` VALUES ('2016_09_26_165428_entrust_setup_tables', 1);
INSERT INTO `migrations` VALUES ('2016_09_27_164953_create_countries_table', 1);
INSERT INTO `migrations` VALUES ('2016_09_27_165018_create_cities_table', 1);
INSERT INTO `migrations` VALUES ('2016_09_27_165059_create_locations_table', 1);
INSERT INTO `migrations` VALUES ('2016_09_27_165141_create_categories_table', 1);
INSERT INTO `migrations` VALUES ('2016_09_29_133748_create_services_table', 1);
INSERT INTO `migrations` VALUES ('2016_09_29_165225_create_tags_table', 1);
INSERT INTO `migrations` VALUES ('2016_10_17_013714_create_badges_table', 1);
INSERT INTO `migrations` VALUES ('2016_10_17_225950_create_partners_table', 1);
INSERT INTO `migrations` VALUES ('2016_10_18_111526_create_partner_basic_informations_table', 1);
INSERT INTO `migrations` VALUES ('2016_10_18_111548_create_partner_bank_informations_table', 1);
INSERT INTO `migrations` VALUES ('2016_10_18_112521_create_partner_references_table', 1);
INSERT INTO `migrations` VALUES ('2016_10_18_112614_create_resource_table', 1);
INSERT INTO `migrations` VALUES ('2016_10_18_112854_create_partner_resource_table', 1);
INSERT INTO `migrations` VALUES ('2016_10_18_113352_create_partner_transactions_table', 1);
INSERT INTO `migrations` VALUES ('2016_11_02_165807_create_partner_service_table', 1);
INSERT INTO `migrations` VALUES ('2016_11_08_170050_create_customers_table', 1);
INSERT INTO `migrations` VALUES ('2016_11_19_165710_create_reviews_table', 1);
INSERT INTO `migrations` VALUES ('2016_11_21_121633_create_orders_table', 1);
INSERT INTO `migrations` VALUES ('2016_11_21_121744_create_partner_orders_table', 1);
INSERT INTO `migrations` VALUES ('2016_11_21_121833_create_materials_table', 1);
INSERT INTO `migrations` VALUES ('2016_11_21_121912_create_jobs_table', 1);
INSERT INTO `migrations` VALUES ('2016_11_21_122351_create_partner_order_payments_table', 1);
INSERT INTO `migrations` VALUES ('2016_11_21_122405_create_customer_reviews_table', 1);
INSERT INTO `migrations` VALUES ('2016_12_07_124710_create_complains_table', 1);
INSERT INTO `migrations` VALUES ('2016_12_07_124745_create_comments_table', 1);
INSERT INTO `migrations` VALUES ('2016_12_09_000658_create_flags_table', 1);
INSERT INTO `migrations` VALUES ('2016_12_11_120224_create_documents_table', 1);
INSERT INTO `migrations` VALUES ('2016_12_25_123712_create_job_cancel_logs_table', 2);
INSERT INTO `migrations` VALUES ('2016_12_25_123732_create_job_decline_logs_table', 2);
INSERT INTO `migrations` VALUES ('2016_12_25_123751_create_job_status_change_logs_table', 2);
INSERT INTO `migrations` VALUES ('2016_12_25_123812_create_job_no_response_logs_table', 2);
INSERT INTO `migrations` VALUES ('2016_12_25_123824_create_job_schedule_due_logs_table', 2);
INSERT INTO `migrations` VALUES ('2016_12_25_123845_create_info_calls_table', 2);
INSERT INTO `migrations` VALUES ('2017_01_04_154529_create_job_update_logs_table', 3);
INSERT INTO `migrations` VALUES ('2017_01_05_163856_add_vat_commission_to_jobs_table', 4);
INSERT INTO `migrations` VALUES ('2017_01_05_194534_add_group_category_to_complains_table', 4);
INSERT INTO `migrations` VALUES ('2017_01_10_172021_add_service_variable_columns_to_job', 5);
INSERT INTO `migrations` VALUES ('2017_01_10_210037_add_previous_status_column_to_job_cancel_log', 6);
INSERT INTO `migrations` VALUES ('2017_01_11_010749_add_resolve_time_column_to_complains_table', 6);
INSERT INTO `migrations` VALUES ('2017_01_16_180650_add_closed_date_column_to_partner_orders_table', 7);
INSERT INTO `migrations` VALUES ('2017_01_19_152349_create_job_partner_change_logs_table', 7);
INSERT INTO `migrations` VALUES ('2017_01_19_211737_add_collected_by_column_to_partner_order_payments_table', 7);
INSERT INTO `migrations` VALUES ('2017_02_28_000826_create_sms_templates_table', 8);
INSERT INTO `migrations` VALUES ('2017_02_28_120114_create_profiles_table', 8);
INSERT INTO `migrations` VALUES ('2017_02_28_120807_add_profile_id_to_profilable_tables', 8);
INSERT INTO `migrations` VALUES ('2017_02_28_175020_add__remember_token_profiles_table', 8);
INSERT INTO `migrations` VALUES ('2017_03_01_021637_add_is_cm_and_is_active_to_users_table', 8);
INSERT INTO `migrations` VALUES ('2017_03_01_202639_add_logo_original_to_partners_table', 8);
INSERT INTO `migrations` VALUES ('2017_03_02_182500_add_flag_type_to_flags_table', 8);
INSERT INTO `migrations` VALUES ('2017_03_06_164232_add_remember_token_resources', 8);
INSERT INTO `migrations` VALUES ('2017_03_14_213946_add_cm_sp_notified_to_job_table', 9);
INSERT INTO `migrations` VALUES ('2017_03_14_180036_create_custom_orders_table', 10);
INSERT INTO `migrations` VALUES ('2017_03_14_180125_create_quotations_table', 10);
INSERT INTO `migrations` VALUES ('2017_03_14_180440_create_custom_order_status_logs_table', 10);
INSERT INTO `migrations` VALUES ('2017_03_14_180450_create_custom_order_cancel_logs_table', 10);
INSERT INTO `migrations` VALUES ('2017_03_14_180504_create_custom_order_update_logs_table', 10);
INSERT INTO `migrations` VALUES ('2017_03_14_184137_create_custom_order_discussions_table', 10);
INSERT INTO `migrations` VALUES ('2017_03_15_193139_create_notifications_table', 10);
INSERT INTO `migrations` VALUES ('2017_03_20_123530_create_partner_service_discounts_table', 11);
INSERT INTO `migrations` VALUES ('2017_03_21_152929_create_vouchers_table', 11);
INSERT INTO `migrations` VALUES ('2017_03_21_171537_create_service_units_table', 11);
INSERT INTO `migrations` VALUES ('2017_03_21_171629_add_unit_to_services_table', 11);
INSERT INTO `migrations` VALUES ('2017_03_21_175643_add_voucher_id_to_orders_table', 11);
INSERT INTO `migrations` VALUES ('2017_03_27_132736_add_sheba_and_partner_contribution_to_jobs_table', 12);
INSERT INTO `migrations` VALUES ('2017_03_27_132818_add_min_quantity_to_services_table', 12);
INSERT INTO `migrations` VALUES ('2017_03_28_140258_create_sliders_table', 12);
INSERT INTO `migrations` VALUES ('2017_04_10_153614_create_offer_showcases_table', 13);
INSERT INTO `migrations` VALUES ('2017_04_10_184515_add_is_amount_percentage_to_vouchers_table', 13);
INSERT INTO `migrations` VALUES ('2017_04_10_184632_add_is_amount_percentage_to_partner_service_discounts_table', 13);
INSERT INTO `migrations` VALUES ('2017_04_13_161816_create_feedbacks_table', 13);
INSERT INTO `migrations` VALUES ('2017_04_20_232224_create_resource_requests_table', 14);
INSERT INTO `migrations` VALUES ('2017_04_26_172300_add_verification_date_to_resources_table', 15);
INSERT INTO `migrations` VALUES ('2017_04_26_193445_add_verification_date_to_partners_table', 15);
INSERT INTO `migrations` VALUES ('2017_04_27_204610_create_flag_status_change_logs_table', 16);
INSERT INTO `migrations` VALUES ('2017_04_30_125147_create_business_categories_table', 17);
INSERT INTO `migrations` VALUES ('2017_04_30_135935_create_businesses_table', 17);
INSERT INTO `migrations` VALUES ('2017_04_30_140015_create_members_table', 17);
INSERT INTO `migrations` VALUES ('2017_04_30_140048_create_business_member_table', 17);
INSERT INTO `migrations` VALUES ('2017_04_30_140118_create_member_requests_table', 17);
INSERT INTO `migrations` VALUES ('2017_04_30_140257_create_business_bank_informations_table', 17);
INSERT INTO `migrations` VALUES ('2017_04_30_142851_create_business_delivery_addresses_table', 17);
INSERT INTO `migrations` VALUES ('2017_05_04_113720_drop_service_tag_table', 17);
INSERT INTO `migrations` VALUES ('2017_05_04_124852_create_taggables_table', 17);
INSERT INTO `migrations` VALUES ('2017_05_04_160843_add_source_and_severity_to_complain_table', 17);
INSERT INTO `migrations` VALUES ('2017_05_06_012607_add_taggable_type_to_tags_table', 17);
INSERT INTO `migrations` VALUES ('2017_05_07_124050_add_remember_token_to_members_table', 18);
INSERT INTO `migrations` VALUES ('2017_05_07_161043_drop_member_and_resource_requests_table', 18);
INSERT INTO `migrations` VALUES ('2017_05_07_161157_create_join_requests_table', 18);
INSERT INTO `migrations` VALUES ('2017_05_07_165846_add_complainable_to_complains_table', 18);
INSERT INTO `migrations` VALUES ('2017_05_08_162114_drop_job_id_from_complains_table', 19);
INSERT INTO `migrations` VALUES ('2017_05_15_011136_create_promotions_table', 20);
INSERT INTO `migrations` VALUES ('2017_05_15_014522_add_referral_columns_to_vouchers_table', 20);
INSERT INTO `migrations` VALUES ('2017_05_15_110605_add_max_customer_to_vouchers_table', 20);
INSERT INTO `migrations` VALUES ('2017_05_16_182214_add_finance_collection_to_partner_orders_table', 20);
INSERT INTO `migrations` VALUES ('2017_05_16_182456_create_partner_order_finance_collections_table', 20);
INSERT INTO `migrations` VALUES ('2017_05_16_184418_add_log_to_partner_transactions_table', 20);
INSERT INTO `migrations` VALUES ('2017_05_17_202829_add_referred_from_to_vouchers_table', 20);
INSERT INTO `migrations` VALUES ('2017_05_19_121630_add_closed_and_paid_date_to_partner_order_table', 20);
INSERT INTO `migrations` VALUES ('2017_06_04_111250_add_is_recurring_column_to_job_table', 21);
INSERT INTO `migrations` VALUES ('2017_06_05_092607_create_to_do_lists_table', 21);
INSERT INTO `migrations` VALUES ('2017_06_05_092621_create_to_do_tasks_table', 21);
INSERT INTO `migrations` VALUES ('2017_06_05_095707_create_to_do_task_attachments_table', 21);
INSERT INTO `migrations` VALUES ('2017_06_05_095721_create_to_do_settings_table', 21);
INSERT INTO `migrations` VALUES ('2017_06_05_095835_create_to_do_list_shared_users_table', 21);
INSERT INTO `migrations` VALUES ('2017_06_05_103423_add_estimated_dates_to_job_table', 21);
INSERT INTO `migrations` VALUES ('2017_06_05_113949_add_cap_to_voucher_table', 21);
INSERT INTO `migrations` VALUES ('2017_06_05_114022_add_cap_to_partner_service_discount_table', 21);
INSERT INTO `migrations` VALUES ('2017_06_13_140829_add_min_commission_to_categories_table', 22);
INSERT INTO `migrations` VALUES ('2017_06_15_112743_add_service_name_to_info_calls_table', 22);
INSERT INTO `migrations` VALUES ('2017_06_18_142615_add_jobs_foreign_to_customer_reviews', 23);
INSERT INTO `migrations` VALUES ('2017_06_18_152114_create_job_cancel_requests_table', 23);
INSERT INTO `migrations` VALUES ('2017_06_19_150203_add_status_and_reason_to_job_cancel_request', 23);
INSERT INTO `migrations` VALUES ('2017_06_20_141403_change_complain_status_structure_and_data', 23);
INSERT INTO `migrations` VALUES ('2017_06_22_115120_add_is_trained_to_resource_table', 23);
INSERT INTO `migrations` VALUES ('2017_07_10_011258_add_assignee_to_flags', 24);
INSERT INTO `migrations` VALUES ('2017_07_10_011359_add_backend_publication_to_services', 24);
INSERT INTO `migrations` VALUES ('2017_07_10_012214_create_unfollowed_notifications_table', 24);
INSERT INTO `migrations` VALUES ('2017_07_10_012232_create_notification_settings_table', 24);
INSERT INTO `migrations` VALUES ('2017_07_10_012735_create_partner_leaves_table', 24);
INSERT INTO `migrations` VALUES ('2017_07_10_013047_add_spouse_name_to_resources', 24);
INSERT INTO `migrations` VALUES ('2017_07_10_013216_add_info_category_to_info_calls', 24);
INSERT INTO `migrations` VALUES ('2017_07_10_015523_create_daily_stats_table', 24);
INSERT INTO `migrations` VALUES ('2017_07_10_194719_create_affiliates_table', 24);
INSERT INTO `migrations` VALUES ('2017_07_10_194738_create_affiliations_table', 24);
INSERT INTO `migrations` VALUES ('2017_07_10_194859_add_affiliation_id_to_order', 24);
INSERT INTO `migrations` VALUES ('2017_07_10_194927_create_affiliate_transactions_table', 24);
INSERT INTO `migrations` VALUES ('2017_07_10_195110_create_affiliation_milestones_table', 24);
INSERT INTO `migrations` VALUES ('2017_07_10_202422_create_affiliation_status_change_logs_table', 24);
INSERT INTO `migrations` VALUES ('2017_07_10_202458_create_affiliate_suspensions_table', 24);
INSERT INTO `migrations` VALUES ('2017_07_11_020238_add_info_call_id_to_order', 24);
INSERT INTO `migrations` VALUES ('2017_07_14_011945_add_store_name_to_affiliates', 24);
INSERT INTO `migrations` VALUES ('2017_07_14_144132_change_sound_structure_in_notifiaction_settings', 24);
INSERT INTO `migrations` VALUES ('2017_07_15_071949_change_status_enum_values_in_affiliations', 25);
INSERT INTO `migrations` VALUES ('2017_07_16_024131_make_profile_id_unique_for_affiliates', 26);
INSERT INTO `migrations` VALUES ('2017_07_16_043338_make_no_of_order_unique_for_affiliation_milestone', 26);
INSERT INTO `migrations` VALUES ('2017_07_18_191715_add_remember_token_to_affiliates', 26);
INSERT INTO `migrations` VALUES ('2017_07_24_184609_create_partner_service_prices_update', 27);
INSERT INTO `migrations` VALUES ('2017_08_01_182021_add_note_to_partner_service_prices_update_table', 28);
INSERT INTO `migrations` VALUES ('2017_08_07_185703_add_commentator_to_comments_table', 29);
INSERT INTO `migrations` VALUES ('2017_08_09_015924_create_sale_targets_table', 29);
INSERT INTO `migrations` VALUES ('2017_08_10_183616_add_attachment_to_jobs_table', 29);
INSERT INTO `migrations` VALUES ('2017_08_10_023850_create_meals_table', 30);
INSERT INTO `migrations` VALUES ('2017_08_17_182502_create_careers_table', 30);
INSERT INTO `migrations` VALUES ('2017_08_17_183357_profiling_clean_up', 30);
INSERT INTO `migrations` VALUES ('2017_08_27_023558_foreign_key_constraints_clean_up', 31);
INSERT INTO `migrations` VALUES ('2017_08_27_105509_add_ambassador_to_affiliates_table', 32);
INSERT INTO `migrations` VALUES ('2017_08_27_111435_create_affiliate_badge_table', 32);
INSERT INTO `migrations` VALUES ('2017_08_28_113509_create_partner_withdrawal_requests_table', 32);
INSERT INTO `migrations` VALUES ('2017_08_28_114155_create_partner_order_advance_requests_table', 32);
INSERT INTO `migrations` VALUES ('2017_08_28_114314_add_is_reconciled_to_partner_orders', 32);
INSERT INTO `migrations` VALUES ('2017_08_28_120203_create_partner_wallet_settings_table', 32);
INSERT INTO `migrations` VALUES ('2017_08_30_140103_add_total_gifted_to_affiliates', 32);
INSERT INTO `migrations` VALUES ('2017_08_30_221139_add_partner_order_id_to_partner_transactions_table', 32);
INSERT INTO `migrations` VALUES ('2017_08_30_221918_create_partner_order_reconcile_logs_table', 32);
INSERT INTO `migrations` VALUES ('2017_08_30_222629_add_affiliation_id_to_info_calls', 32);
INSERT INTO `migrations` VALUES ('2017_08_30_222646_add_affiliation_id_to_custom_orders', 32);
INSERT INTO `migrations` VALUES ('2017_08_30_223143_add_custom_order_id_to_orders', 32);
INSERT INTO `migrations` VALUES ('2017_09_02_232152_add_payment_amount_to_affiliates', 33);
INSERT INTO `migrations` VALUES ('2017_09_03_150416_add_new_option_to_affiliations_status', 33);
INSERT INTO `migrations` VALUES ('2017_09_05_110229_add_affiliation_id_to_affiliate_transaction', 34);
INSERT INTO `migrations` VALUES ('2017_09_06_235818_add_total_online_hours_to_user', 35);
INSERT INTO `migrations` VALUES ('2017_09_07_000821_create_user_login_logs_table', 35);
INSERT INTO `migrations` VALUES ('2017_09_07_001442_create_partner_order_stage_logs_table', 35);
INSERT INTO `migrations` VALUES ('2017_09_07_185237_add_publication_status_to_careers_table', 35);
INSERT INTO `migrations` VALUES ('2017_09_08_005339_add_user_agent_to_user_login_log', 35);
INSERT INTO `migrations` VALUES ('2017_09_25_221211_add_event_type_and_event_id_to_notifications_table', 36);
INSERT INTO `migrations` VALUES ('2017_09_30_132113_add_branch_and_routing_to_partner_bank_information', 37);
INSERT INTO `migrations` VALUES ('2017_10_09_144320_add_created_by_to_affiliate_transactions_table', 38);
INSERT INTO `migrations` VALUES ('2017_10_12_223645_change_daily_stats', 38);
INSERT INTO `migrations` VALUES ('2017_10_17_112854_add_target_and_app_image_to_sliders', 39);
INSERT INTO `migrations` VALUES ('2017_10_18_124752_add_likes_to_flags', 40);
INSERT INTO `migrations` VALUES ('2017_10_18_124848_create_flag_user_change_logs_table', 40);
INSERT INTO `migrations` VALUES ('2017_10_18_124901_create_flag_time_change_logs_table', 40);
INSERT INTO `migrations` VALUES ('2017_10_19_110138_add_completed_at_to_flags', 40);
INSERT INTO `migrations` VALUES ('2017_10_23_123346_create_partner_location_update_requests_table', 40);
INSERT INTO `migrations` VALUES ('2017_10_25_142224_add_is_approved_to_flag_time_change_logs_table', 40);
INSERT INTO `migrations` VALUES ('2017_10_30_203649_create_mentions_table', 40);
INSERT INTO `migrations` VALUES ('2017_11_02_114641_add_security_money_received_to_partner_wallet_settings', 40);
INSERT INTO `migrations` VALUES ('2017_11_26_171413_add_cancelled_at_to_partner_orders_table', 41);
INSERT INTO `migrations` VALUES ('2017_11_29_104154_add_start_end_date_to_offers', 41);
INSERT INTO `migrations` VALUES ('2017_12_04_133029_create_partner_order_status_logs_table', 42);
INSERT INTO `migrations` VALUES ('2017_12_04_133054_create_complain_status_logs_table', 42);
INSERT INTO `migrations` VALUES ('2017_12_04_133133_create_job_material_logs_table', 42);
INSERT INTO `migrations` VALUES ('2017_12_04_133211_create_order_update_logs_table', 42);
INSERT INTO `migrations` VALUES ('2017_12_04_133325_create_partner_daily_stats_table', 42);
INSERT INTO `migrations` VALUES ('2017_12_14_103313_create_push_subscriptions_table', 42);
INSERT INTO `migrations` VALUES ('2017_12_17_115001_add_refund_amount_to_partner_orders', 42);
INSERT INTO `migrations` VALUES ('2017_12_18_120123_create_system_updates_table', 43);
INSERT INTO `migrations` VALUES ('2017_12_18_121624_create_features_table', 43);
INSERT INTO `migrations` VALUES ('2017_12_18_122413_create_department_feature_table', 43);
INSERT INTO `migrations` VALUES ('2017_12_18_135531_create_attachments_table', 43);
INSERT INTO `migrations` VALUES ('2017_12_18_204731_add_foreign_key_to_category_partner_resource', 43);
INSERT INTO `migrations` VALUES ('2017_12_21_172644_create_data_migrations_table', 43);
INSERT INTO `migrations` VALUES ('2018_01_09_000205_add_cancellation_and_unreachable_sms_sent_to_orders', 44);
INSERT INTO `migrations` VALUES ('2018_01_07_172800_add_user_agent_to_logs_and_partner_order_payments_table', 45);
INSERT INTO `migrations` VALUES ('2018_01_09_173352_add_resolve_category_to_complains', 45);
INSERT INTO `migrations` VALUES ('2018_02_11_180446_create_partner_status_change_logs_table', 46);
INSERT INTO `migrations` VALUES ('2018_02_12_153100_add_unreachable_and_resolved_sms_sent_to_complains', 46);
INSERT INTO `migrations` VALUES ('2018_02_12_170507_change_resolved_categories_in_complains_table', 46);
INSERT INTO `migrations` VALUES ('2018_02_13_133601_add_transaction_details_to_partner_order_payments', 46);
INSERT INTO `migrations` VALUES ('2018_02_14_173020_change_cancel_reasons_on_cancel_request_and_log_table', 46);
INSERT INTO `migrations` VALUES ('2018_02_05_152516_add_google_id_to_profiles_table', 47);
INSERT INTO `migrations` VALUES ('2018_02_19_173920_add_is_sensitive_to_flags', 47);
INSERT INTO `migrations` VALUES ('2018_02_25_125453_add_pap_visitor_id_pap_affiliate_id_in_orders_table', 48);
INSERT INTO `migrations` VALUES ('2018_02_26_055149_create_complains_v2_table', 49);
INSERT INTO `migrations` VALUES ('2018_03_15_055538_change_complain_status_logs_structure', 49);
INSERT INTO `migrations` VALUES ('2018_03_15_100327_add_is_satisfied_to_complains_table', 49);
INSERT INTO `migrations` VALUES ('2018_03_15_161711_create_accessor_comment_table', 49);
INSERT INTO `migrations` VALUES ('2017_12_18_172056_create_job_service_table', 50);
INSERT INTO `migrations` VALUES ('2017_12_18_173418_add_category_id_to_jobs', 50);
INSERT INTO `migrations` VALUES ('2017_12_18_173938_add_description_to_categories', 50);
INSERT INTO `migrations` VALUES ('2017_12_24_113208_add_category_id_to_reviews', 50);
INSERT INTO `migrations` VALUES ('2018_01_16_142317_add_name_column_to_customer_delivery_addresses_table', 50);
INSERT INTO `migrations` VALUES ('2018_01_16_173115_add_lat_long_to_customer_delivery_addresses', 50);
INSERT INTO `migrations` VALUES ('2018_01_16_173136_add_geo_informations_to_locations', 50);
INSERT INTO `migrations` VALUES ('2018_01_16_173237_create_customer_favourites_table', 50);
INSERT INTO `migrations` VALUES ('2018_01_16_173316_create_category_groups_table', 50);
INSERT INTO `migrations` VALUES ('2018_01_21_173315_create_schedule_slots_table', 50);
INSERT INTO `migrations` VALUES ('2018_01_21_182022_create_resource_schedules_table', 50);
INSERT INTO `migrations` VALUES ('2018_01_21_200717_create_resource_schedule_logs_table', 50);
INSERT INTO `migrations` VALUES ('2018_01_23_094939_add_resource_blocking_time_to_categories', 50);
INSERT INTO `migrations` VALUES ('2018_01_25_105151_add_preferred_time_fields_to_jobs', 50);
INSERT INTO `migrations` VALUES ('2018_01_28_123119_create_homepage_settings_table', 50);
INSERT INTO `migrations` VALUES ('2018_01_31_152137_create_home_grids_table', 50);
INSERT INTO `migrations` VALUES ('2018_01_31_174718_create_external_projects_table', 50);
INSERT INTO `migrations` VALUES ('2018_02_01_120159_create_category_questions_table', 50);
INSERT INTO `migrations` VALUES ('2018_02_01_172927_add_questions_to_category_table', 50);
INSERT INTO `migrations` VALUES ('2018_02_01_181244_add_icon_to_category_table', 50);
INSERT INTO `migrations` VALUES ('2018_02_11_114839_add_category_answers_to_jobs', 50);
INSERT INTO `migrations` VALUES ('2018_02_14_122810_create_rating_questionaries_table', 50);
INSERT INTO `migrations` VALUES ('2018_03_03_102311_add_short_description_to_services_table', 50);
INSERT INTO `migrations` VALUES ('2018_03_07_112351_create_usps_table', 50);
INSERT INTO `migrations` VALUES ('2018_03_08_112923_add_meta_description_to_category_and_groups', 50);
INSERT INTO `migrations` VALUES ('2018_03_08_115313_create_onboarding_tables', 50);
INSERT INTO `migrations` VALUES ('2018_03_08_174715_add_slug_to_categories_table', 50);
INSERT INTO `migrations` VALUES ('2018_03_11_151641_add_icon_png_to_grid_item_tables', 50);
INSERT INTO `migrations` VALUES ('2018_03_11_160904_add_app_banner_to_offer_showcases', 50);
INSERT INTO `migrations` VALUES ('2018_03_15_020207_add_cancelled_status_to_join_requests', 50);
INSERT INTO `migrations` VALUES ('2018_03_17_134508_add_video_link_to_categories', 50);
INSERT INTO `migrations` VALUES ('2018_03_17_134550_add_minimum_order_amount_to_category_partner', 50);
INSERT INTO `migrations` VALUES ('2018_03_18_160347_create_partner_working_hours_table', 50);
INSERT INTO `migrations` VALUES ('2018_03_22_131929_add_resource_scheduler_notification_time_column', 50);
INSERT INTO `migrations` VALUES ('2018_03_24_111224_add_icon_off_to_rates', 50);
INSERT INTO `migrations` VALUES ('2018_03_25_141025_add_app_images_to_services', 50);
INSERT INTO `migrations` VALUES ('2018_03_31_063041_create_service_request_table', 51);
INSERT INTO `migrations` VALUES ('2018_03_31_063104_create_incomplete_order_table', 51);
INSERT INTO `migrations` VALUES ('2018_03_31_063152_add_device_to_push_subscriptions_table', 51);
INSERT INTO `migrations` VALUES ('2018_04_08_132059_add_lifetime_sla_to_complain_types_table', 52);
INSERT INTO `migrations` VALUES ('2018_04_09_055935_unreachable_sms_restructure_on_complains_table', 52);
INSERT INTO `migrations` VALUES ('2018_04_22_095457_add_preparation_time_minutes_to_categories_table', 53);
INSERT INTO `migrations` VALUES ('2018_04_23_130309_add_transaction_detail_to_partner_transactions', 53);
INSERT INTO `migrations` VALUES ('2018_04_25_094851_introduce_serve_due_job_status', 53);
INSERT INTO `migrations` VALUES ('2018_04_28_090822_add_onboarded_enum_type_on_status_on_partner_table', 53);
INSERT INTO `migrations` VALUES ('2018_05_03_080049_add_order_to_category_and_service_table', 53);
INSERT INTO `migrations` VALUES ('2018_05_16_081255_add_preparation_time_minutes_to_category_partner_table', 54);
INSERT INTO `migrations` VALUES ('2018_05_16_081534_add_meta_title_to_categories_table', 54);
INSERT INTO `migrations` VALUES ('2018_05_20_044539_create_car_rental_job_details_table', 55);
INSERT INTO `migrations` VALUES ('2018_05_20_095734_create_divisions_districts_upazilas_thanas_table', 55);
INSERT INTO `migrations` VALUES ('2018_05_21_075255_add_pricing_helper_text_for_service', 55);
INSERT INTO `migrations` VALUES ('2018_05_24_065257_create_app_versions_table', 55);
INSERT INTO `migrations` VALUES ('2018_06_11_084138_drop_unique_key_on_affiliate_transactions_table', 56);
INSERT INTO `migrations` VALUES ('2018_05_28_052041_add_trade_license_attachment_to_partner_basic_informations_table', 57);
INSERT INTO `migrations` VALUES ('2018_05_28_094142_add_is_flash_to_offer_showcase_table', 57);
INSERT INTO `migrations` VALUES ('2018_06_04_084944_modify_table_unique_nid_no_to_resource_table', 57);
INSERT INTO `migrations` VALUES ('2018_06_04_122012_add_geo_informations_to_partners_table', 57);
INSERT INTO `migrations` VALUES ('2018_06_05_202036_add_registration_channel_to_partners', 57);
INSERT INTO `migrations` VALUES ('2018_06_07_075816_create_push_notifications_table', 57);
INSERT INTO `migrations` VALUES ('2018_06_10_173830_create_partner_affiliations_table', 57);
INSERT INTO `migrations` VALUES ('2018_06_10_175933_add_acquisition_cost_to_affiliates', 57);
INSERT INTO `migrations` VALUES ('2018_06_10_210148_make_affiliate_transactions_table_morphable', 58);
INSERT INTO `migrations` VALUES ('2018_06_11_043734_add_acquisition_cost_to_affiliations', 58);
INSERT INTO `migrations` VALUES ('2018_06_11_182950_create_dashboard_settings_table', 58);
INSERT INTO `migrations` VALUES ('2018_06_12_062440_create_job_cancel_reasons_table', 58);
INSERT INTO `migrations` VALUES ('2018_06_14_052508_change_cancel_reasons_on_cancel_request_and_log_table_v2', 58);
INSERT INTO `migrations` VALUES ('2018_06_23_145629_change_quantity_decimal_to_job_service_table', 58);
INSERT INTO `migrations` VALUES ('2018_06_26_090020_add_new_columns_to_customer_delivery_addresses', 59);
INSERT INTO `migrations` VALUES ('2018_06_26_111544_add_customer_delivery_address_id_to_orders', 59);
INSERT INTO `migrations` VALUES ('2018_06_27_053938_add_unreachable_sms_partner_to_orders', 59);
INSERT INTO `migrations` VALUES ('2018_06_28_090525_add_unreachable_sms_to_affiliation', 59);
INSERT INTO `migrations` VALUES ('2018_07_01_071252_add_is_min_price_applicable_to_services', 59);
INSERT INTO `migrations` VALUES ('2018_07_01_071956_add_min_prices_to_partner_service', 59);
INSERT INTO `migrations` VALUES ('2018_07_01_083703_create_user_workload_logs', 59);
INSERT INTO `migrations` VALUES ('2018_07_01_085128_add_critical_noncritical_workload_cap_to_users_table', 59);
INSERT INTO `migrations` VALUES ('2018_07_01_085724_add_min_price_to_job_service', 59);
INSERT INTO `migrations` VALUES ('2018_07_02_091201_create_job_cm_change_logs_table', 59);
INSERT INTO `migrations` VALUES ('2018_07_04_102516_add_new_and_old_min_prices_column_on_partner_service_prices_update_table', 59);
INSERT INTO `migrations` VALUES ('2018_07_02_062926_setup_partner_subscription_tables', 60);
INSERT INTO `migrations` VALUES ('2018_07_09_102815_add_user_agent_to_job_cancel_requests_table', 60);
INSERT INTO `migrations` VALUES ('2018_07_12_082430_add_billing_type_to_partner_subscription_discounts', 60);
INSERT INTO `migrations` VALUES ('2018_07_12_142335_add_is_escalate_to_job_cancel_requests_table', 60);
INSERT INTO `migrations` VALUES ('2018_07_14_051745_add_distribution_data_to_daily_stats', 60);
INSERT INTO `migrations` VALUES ('2018_07_14_052012_add_index_to_notifications_and_comments', 60);
INSERT INTO `migrations` VALUES ('2018_07_17_065055_add_bangla_column_to_partner_subcription_package_table', 60);
INSERT INTO `migrations` VALUES ('2018_07_17_083249_setup_topup_tables', 60);
INSERT INTO `migrations` VALUES ('2018_07_22_091407_add_index_in_affiliate_transaction_table', 61);
INSERT INTO `migrations` VALUES ('2018_07_22_092957_add_index_in_affiliations_table', 61);
INSERT INTO `migrations` VALUES ('2018_07_22_093021_add_index_to_attachments_table', 61);
INSERT INTO `migrations` VALUES ('2018_07_30_150044_add_location_id_to_thanas_table', 61);
INSERT INTO `migrations` VALUES ('2018_08_01_083556_create_events_table', 61);
INSERT INTO `migrations` VALUES ('2018_08_06_065551_create_partner_service_surcharges_table', 62);
INSERT INTO `migrations` VALUES ('2018_08_07_091946_create_updates_table', 63);
INSERT INTO `migrations` VALUES ('2018_08_13_054941_add_asset_to_rates_and_rate_answers_table', 64);
INSERT INTO `migrations` VALUES ('2018_08_16_123843_add_title_and_body_and_image_link_and_height_and_width_to_app_versions_table', 64);
INSERT INTO `migrations` VALUES ('2018_09_03_042200_add_requested_billing_type_to_partners_table', 65);
INSERT INTO `migrations` VALUES ('2018_09_08_023935_add_wallet_to_customers_table', 65);
INSERT INTO `migrations` VALUES ('2018_09_08_024234_create_customer_transactions_table', 65);
INSERT INTO `migrations` VALUES ('2018_09_09_064136_create_rewards_table', 66);
INSERT INTO `migrations` VALUES ('2018_09_17_072152_create_reward_shop_and_reward_order_table', 66);
INSERT INTO `migrations` VALUES ('2018_10_02_094108_create_partner_package_update_requests_table', 67);
INSERT INTO `migrations` VALUES ('2018_10_02_124226_add_reject_status_to_partners_table', 67);
INSERT INTO `migrations` VALUES ('2018_10_08_050245_add_transaction_details_to_affiliate_transactions_table', 68);
INSERT INTO `migrations` VALUES ('2018_10_08_060049_add_bn_name_and_publication_for_bondhu_to_services', 68);
INSERT INTO `migrations` VALUES ('2018_10_08_060540_create_bonuses_table', 68);
INSERT INTO `migrations` VALUES ('2018_10_09_071722_add_percentage_related_column_to_rewards_table', 68);
INSERT INTO `migrations` VALUES ('2018_10_11_063811_add_validity_to_reward_table', 68);
INSERT INTO `migrations` VALUES ('2018_10_14_095637_add_on_premise_column_on_various_table', 69);
INSERT INTO `migrations` VALUES ('2018_10_14_110540_create_payment_tables', 69);
INSERT INTO `migrations` VALUES ('2018_10_14_124615_create_delivery_charge_update_requests_table', 69);
INSERT INTO `migrations` VALUES ('2018_10_21_084818_change_status_to_payment_and_logs_table', 70);
INSERT INTO `migrations` VALUES ('2018_10_27_044245_add_is_published_for_partner_to_locations_table', 71);
INSERT INTO `migrations` VALUES ('2018_10_22_062034_add_eshop_related_column_on_various_table', 72);
INSERT INTO `migrations` VALUES ('2018_10_22_084319_add_is_published_for_customer_to_job_cancel_reasons_table', 72);
INSERT INTO `migrations` VALUES ('2018_10_22_104946_add_impression_limit_and_current_impression_to_partner_table', 72);
INSERT INTO `migrations` VALUES ('2018_10_23_074210_create_partner_impression_deduction_logs_table', 72);
INSERT INTO `migrations` VALUES ('2018_10_24_123320_add_partner_wallet_enum_to_payment_details_table', 72);
INSERT INTO `migrations` VALUES ('2018_10_29_104707_add_is_gifted_to_affiliate_transactions_table', 73);
INSERT INTO `migrations` VALUES ('2018_11_03_135520_make_order_id_partner_id_unique_for_partner_orders_table', 74);
INSERT INTO `migrations` VALUES ('2018_10_30_053417_create_bonus_logs_tables', 75);
INSERT INTO `migrations` VALUES ('2018_10_31_111053_add_request_identification_data_to_partner_transactions_tables', 75);
INSERT INTO `migrations` VALUES ('2018_10_31_115702_update_spent_on_type_enum_to_bonuses_and_bonus_logs_tables', 75);
INSERT INTO `migrations` VALUES ('2018_11_01_112430_add_created_by_column_to_affiliation_status_logs', 75);
INSERT INTO `migrations` VALUES ('2018_11_01_112629_create_affiliation_logs_table', 75);
INSERT INTO `migrations` VALUES ('2018_11_03_103837_make_voucher_dates_nullable', 75);
INSERT INTO `migrations` VALUES ('2018_11_06_045820_add_customer_favourites_data_on_customer_favourites_related_table', 76);
INSERT INTO `migrations` VALUES ('2018_11_06_065720_change_geo_informations_column_to_locations_table', 76);
INSERT INTO `migrations` VALUES ('2018_11_06_072559_change_name_and_additional_info_column_nullable_to_customer_favourites_table', 76);
INSERT INTO `migrations` VALUES ('2018_11_06_091401_create_topup_vendor_commissions_table', 76);
INSERT INTO `migrations` VALUES ('2018_11_07_074910_add_min_prices_quantity_to_partner_service_table', 76);
INSERT INTO `migrations` VALUES ('2018_11_08_124221_add_old_new_base_quantity_base_prices_data_to_partner_service_prices_update_table', 76);
INSERT INTO `migrations` VALUES ('2018_11_10_093842_add_is_base_price_applicable_to_services_table', 76);
INSERT INTO `migrations` VALUES ('2018_11_11_045115_add_favourite_id_to_orders_table', 77);
INSERT INTO `migrations` VALUES ('2018_11_12_133252_add_bkash_no_to_partners_table', 78);
INSERT INTO `migrations` VALUES ('2018_11_12_140242_add_payment_method_and_payment_info_to_partner_withdrawal_requests_table', 78);
INSERT INTO `migrations` VALUES ('2018_11_13_070357_add_soft_delete_to_delivery_addresses', 78);
INSERT INTO `migrations` VALUES ('2018_11_14_080207_modify_status_enum_to_partner_withdrawal_requests_table', 78);
INSERT INTO `migrations` VALUES ('2018_11_15_063119_create_job_partner_change_reasons_table', 79);
INSERT INTO `migrations` VALUES ('2018_11_15_085418_add_home_banner_to_categories', 79);
INSERT INTO `migrations` VALUES ('2018_11_15_150937_add_cancel_reason_to_job_partner_change_logs_table', 79);
INSERT INTO `migrations` VALUES ('2018_11_25_124752_add_extra_column_to_offer_showcases_table', 80);
INSERT INTO `migrations` VALUES ('2018_12_06_090507_add_request_identification_column_to_orders_table', 81);
INSERT INTO `migrations` VALUES ('2018_12_04_054906_add_badge_thumb_to_partner_subscription_packages_table', 82);
INSERT INTO `migrations` VALUES ('2018_12_04_054953_add_in_progress_enum_status_to_affiliations_table', 82);
INSERT INTO `migrations` VALUES ('2018_12_04_062827_add_is_fake_for_affiliation_to_job_cancel_reasons_table', 82);
INSERT INTO `migrations` VALUES ('2018_12_04_124159_add_ambassador_commission_to_topup_vendor_commissions_and_topup_orders_table', 82);
INSERT INTO `migrations` VALUES ('2018_12_04_130408_add_new_enum_to_job_cancel_logs_and_job_cancel_requests_table', 82);
INSERT INTO `migrations` VALUES ('2018_12_10_090749_create_location_tagging_on_category_service_and_offer_table', 83);
INSERT INTO `migrations` VALUES ('2018_12_10_091715_create_location_tagging_on_category_groups_table', 83);
INSERT INTO `migrations` VALUES ('2018_12_12_121545_create_slider_location_related_table', 83);
INSERT INTO `migrations` VALUES ('2018_12_12_132107_create_grid_location_related_table', 83);
INSERT INTO `migrations` VALUES ('2018_12_12_134036_create_page_settings_location_related_table', 83);
INSERT INTO `migrations` VALUES ('2018_12_20_121209_add_event_morph_column_on_customer_transactions_table', 83);
INSERT INTO `migrations` VALUES ('2018_12_23_131933_add_log_column_to_order_update_logs_table', 83);
INSERT INTO `migrations` VALUES ('2019_01_02_101355_update_image_and_small_link_nullable_to_slides_table', 84);
INSERT INTO `migrations` VALUES ('2019_01_08_134047_create_partner_geo_change_logs_table', 85);
INSERT INTO `migrations` VALUES ('2019_01_09_141923_create_vendors_table', 86);
INSERT INTO `migrations` VALUES ('2019_01_09_144022_add_is_moderator_to_affiliates_table', 86);
INSERT INTO `migrations` VALUES ('2019_01_10_041416_add_lite_onboarding_info_to_partner_table', 86);
INSERT INTO `migrations` VALUES ('2019_01_10_053954_add_badge_to_partner_table', 86);
INSERT INTO `migrations` VALUES ('2019_01_13_094142_add_enum_on_affiliation_type_to_affiliate_transactions_table', 86);
INSERT INTO `migrations` VALUES ('2019_01_24_062305_add_banner_only_flag_to_offers', 87);
INSERT INTO `migrations` VALUES ('2019_01_17_110548_add_order_primary_field_customer_table', 88);
INSERT INTO `migrations` VALUES ('2019_01_30_101351_add_is_published_for_partner_to_categories', 89);
INSERT INTO `migrations` VALUES ('2019_01_29_033002_add_order_limit_to_partner', 90);
INSERT INTO `migrations` VALUES ('2019_01_29_033105_add_logistics_columns_to_different_tables', 90);
INSERT INTO `migrations` VALUES ('2019_02_05_043419_create_sms_marketing_related_table', 91);
INSERT INTO `migrations` VALUES ('2019_02_05_061126_add_is_published_sheba_and_partner_to_sms_templates_table', 91);
INSERT INTO `migrations` VALUES ('2019_02_07_093446_add_bitly_url_to_partners_table', 91);
INSERT INTO `migrations` VALUES ('2019_02_12_093446_add_moderation_log_to_partners_table', 91);
INSERT INTO `migrations` VALUES ('2019_02_18_080215_add_type_id_to_topup_vendor_commissions', 92);
INSERT INTO `migrations` VALUES ('2019_02_19_085004_add_material_commission_rate_to_jobs', 92);
INSERT INTO `migrations` VALUES ('2019_02_20_071945_create_customer_subscription_and_related_table', 93);
INSERT INTO `migrations` VALUES ('2019_02_23_110944_create_movie_ticket_related_db', 93);
INSERT INTO `migrations` VALUES ('2019_02_24_092843_create_offer_group_table', 93);
INSERT INTO `migrations` VALUES ('2019_02_24_104028_create_app_menu_table', 93);
INSERT INTO `migrations` VALUES ('2019_03_03_122850_add_offer_banner_size_fields', 93);
INSERT INTO `migrations` VALUES ('2019_03_07_105428_make_partner_service_id_unique_to_partner_service_table', 94);
INSERT INTO `migrations` VALUES ('2019_03_07_115052_add_review_id_unique_to_review_question_answer_table', 95);
INSERT INTO `migrations` VALUES ('2019_03_05_130418_add_and_create_sp_loan_related_table', 96);
INSERT INTO `migrations` VALUES ('2019_03_06_120423_add_complain_categories_order_status_field', 96);
INSERT INTO `migrations` VALUES ('2019_03_06_133650_create_complain_presets_sub_category_table', 96);
INSERT INTO `migrations` VALUES ('2019_03_07_071749_create_partner_order_report_table', 96);
INSERT INTO `migrations` VALUES ('2019_03_07_103623_change_status_column_complains_table', 96);
INSERT INTO `migrations` VALUES ('2019_03_07_124638_add_bkash_agreement_id_to_profiles_table', 96);
INSERT INTO `migrations` VALUES ('2019_03_10_092833_change_status_column_complains_table_again', 96);
INSERT INTO `migrations` VALUES ('2019_03_10_124009_change_reg_and_est_year_to_partner_basic_information_table', 96);
INSERT INTO `migrations` VALUES ('2019_03_11_060011_create_partner_bank_loans_table', 96);
INSERT INTO `migrations` VALUES ('2019_03_11_063119_add_gateway_transaction_id_to_payments_table', 96);
INSERT INTO `migrations` VALUES ('2019_03_12_122800_create_gift_cards_table', 96);
INSERT INTO `migrations` VALUES ('2019_03_12_130707_add_payment_failed_reason_column_partner_withdraw_requests_table', 96);
INSERT INTO `migrations` VALUES ('2019_03_12_140953_create_gift_card_purchases_table', 96);
INSERT INTO `migrations` VALUES ('2019_03_13_080410_create_affiliations_report_table', 96);
INSERT INTO `migrations` VALUES ('2019_03_14_043436_create_complains_report_table', 96);
INSERT INTO `migrations` VALUES ('2019_03_16_085520_add_common_columns_to_affiliates', 96);
INSERT INTO `migrations` VALUES ('2019_03_23_085800_create_vendor_transactions_table', 97);
INSERT INTO `migrations` VALUES ('2019_03_23_091925_add_new_types_to_topup_vendor_commissions_table', 97);
INSERT INTO `migrations` VALUES ('2019_03_27_071121_add_original_discount_amount_to_jobs_table', 97);
INSERT INTO `migrations` VALUES ('2019_04_02_075137_add_order_for_bondhu_to_services', 98);
INSERT INTO `migrations` VALUES ('2019_04_03_121629_add_new_types_to_payables_table_for_movie_ticket_purchases', 99);
INSERT INTO `migrations` VALUES ('2019_04_09_063014_add_order_to_category_group_category_table', 100);
INSERT INTO `migrations` VALUES ('2019_04_09_063449_add_is_popup_to_offer_showcase_table', 100);
INSERT INTO `migrations` VALUES ('2019_04_11_055405_add_affects_partner_performance_column_to_job_cancel_reasons_table', 101);
INSERT INTO `migrations` VALUES ('2019_04_11_102349_create_affiliate_withdrawal_requests_table', 101);
INSERT INTO `migrations` VALUES ('2019_04_16_050831_create_service_groups_tables', 102);
INSERT INTO `migrations` VALUES ('2019_04_16_101520_create_pos_related_table', 102);
INSERT INTO `migrations` VALUES ('2019_04_17_055542_add_stock_to_services_table', 102);
INSERT INTO `migrations` VALUES ('2019_04_17_071053_add_is_flash_in_offer_group_table', 102);
INSERT INTO `migrations` VALUES ('2019_04_17_120348_add_online_discount_to_jobs_table', 102);
INSERT INTO `migrations` VALUES ('2019_04_17_123618_add_with_children_column_to_screen_setting_elements', 102);
INSERT INTO `migrations` VALUES ('2019_04_18_095108_update_topup_order_agent_type_columns', 102);
INSERT INTO `migrations` VALUES ('2019_04_18_154521_add_partner_id_to_pos_orders_table', 102);
INSERT INTO `migrations` VALUES ('2019_04_18_164051_add_method_to_pos_order_payments_table', 102);
INSERT INTO `migrations` VALUES ('2019_04_21_064404_add_note_on_partner_pos_customers_table', 102);
INSERT INTO `migrations` VALUES ('2019_04_21_064753_add_vat_percentage_on_pos_order_items_table', 102);
INSERT INTO `migrations` VALUES ('2019_04_23_045148_add_business_id_to_orders_table', 102);
INSERT INTO `migrations` VALUES ('2019_04_23_045731_create_business_partners_table', 102);
INSERT INTO `migrations` VALUES ('2019_04_23_050123_create_business_join_requests_table', 102);
INSERT INTO `migrations` VALUES ('2019_04_23_052416_add_geo_informations_to_business', 102);
INSERT INTO `migrations` VALUES ('2019_04_24_070229_create_partner_pos_settings_table', 102);
INSERT INTO `migrations` VALUES ('2019_04_24_073803_add_transaction_type_to_pos_order_payments_table', 102);
INSERT INTO `migrations` VALUES ('2019_04_25_142213_add_wallet_to_businesses_table', 102);
INSERT INTO `migrations` VALUES ('2019_04_27_050247_create_business_transactions_table', 102);
INSERT INTO `migrations` VALUES ('2019_04_27_083244_add_user_type_enum_to_payables_table', 102);
INSERT INTO `migrations` VALUES ('2019_04_28_054508_add_soft_deletes_on_partner_pos_services_table', 102);
INSERT INTO `migrations` VALUES ('2019_04_29_071450_add_is_published_for_b2b_in_services_table', 102);
INSERT INTO `migrations` VALUES ('2019_04_29_100655_add_is_active_on_service_subscriptions_table', 102);
INSERT INTO `migrations` VALUES ('2019_05_02_134407_create_drivers_table', 103);
INSERT INTO `migrations` VALUES ('2019_05_04_100017_create_transport_ticket_related_table', 103);
INSERT INTO `migrations` VALUES ('2019_05_04_130334_add_voucher_id_to_other_order', 103);
INSERT INTO `migrations` VALUES ('2019_05_05_030205_create_vehicles_table', 103);
INSERT INTO `migrations` VALUES ('2019_05_05_031850_add_driver_id_to_profiles_table', 103);
INSERT INTO `migrations` VALUES ('2019_05_05_060409_create_routes_table', 103);
INSERT INTO `migrations` VALUES ('2019_05_06_062339_create_trip_related_tables', 103);
INSERT INTO `migrations` VALUES ('2019_05_06_150022_add_new_types_to_payables_table_for_transport_ticket_purchases', 103);
INSERT INTO `migrations` VALUES ('2019_05_07_051117_create_employee_department_related_tables', 103);
INSERT INTO `migrations` VALUES ('2019_05_08_045727_create_affiliate_report_table', 103);
INSERT INTO `migrations` VALUES ('2019_05_08_065947_add_wallet_id_to_movie_and_transport_vendor', 103);
INSERT INTO `migrations` VALUES ('2019_05_08_092006_add_commission_related_data_to_transport_ticket_orders', 103);
INSERT INTO `migrations` VALUES ('2019_05_09_054427_add_logistic_enable_manually_to_jobs', 103);
INSERT INTO `migrations` VALUES ('2019_05_09_111547_add_delivery_charge_to_partner_order_report', 103);
INSERT INTO `migrations` VALUES ('2019_05_11_103131_create_or_change_logistic_discount_tables', 103);
INSERT INTO `migrations` VALUES ('2019_05_12_070902_add_tags_in_business_transacrions_table', 103);
INSERT INTO `migrations` VALUES ('2019_05_12_080405_create_business_sms_templates_table', 103);
INSERT INTO `migrations` VALUES ('2019_05_12_101129_add_department_id_in_vehicles', 103);
INSERT INTO `migrations` VALUES ('2019_05_12_110149_add_discount_field_on_movie_ticket_order_table', 103);
INSERT INTO `migrations` VALUES ('2019_05_13_055213_add_business_id_in_trip_tables', 104);
INSERT INTO `migrations` VALUES ('2019_05_14_055904_rename_department_id_in_vehicles_table', 104);
INSERT INTO `migrations` VALUES ('2019_05_14_100120_add_is_super_field_in_business_members_table', 104);
INSERT INTO `migrations` VALUES ('2019_05_14_100923_remove_unique_column_from_business_sms_template_table', 104);
INSERT INTO `migrations` VALUES ('2019_05_15_131614_add_is_blacklisted_to_profile_table', 105);
INSERT INTO `migrations` VALUES ('2019_05_16_043211_create_inspection_related_tables', 106);
INSERT INTO `migrations` VALUES ('2019_05_19_115344_add_video_link_to_slides_table', 106);
INSERT INTO `migrations` VALUES ('2019_05_20_063829_create_customer_report_table', 106);
INSERT INTO `migrations` VALUES ('2019_05_21_042343_add_inspection_related_columns', 106);
INSERT INTO `migrations` VALUES ('2019_05_22_063607_add_logistics_paid_column_to_jobs_table', 106);
INSERT INTO `migrations` VALUES ('2019_05_22_073657_create_inspection_schedules', 106);
INSERT INTO `migrations` VALUES ('2019_05_27_084332_create_queue_failed_jobs_table', 107);
INSERT INTO `migrations` VALUES ('2019_06_10_042744_create_fuel_logs_table', 108);
INSERT INTO `migrations` VALUES ('2019_06_11_063835_add_mobile_type_in_topup_orders', 108);
INSERT INTO `migrations` VALUES ('2019_06_18_125735_add_rider_app_enum_on_tag_on_app_versions_table', 109);
INSERT INTO `migrations` VALUES ('2019_06_23_123237_add_pr_related_table', 109);
INSERT INTO `migrations` VALUES ('2019_06_24_135745_add_enum_to_type_and_completion_type_table', 109);
INSERT INTO `migrations` VALUES ('2019_06_26_063145_add_enum_to_bonuses_logs_table', 109);
INSERT INTO `migrations` VALUES ('2019_07_03_110445_add_vehicle_image_in_vehicle_general_inforamtions_table', 110);
INSERT INTO `migrations` VALUES ('2019_07_07_051406_add_hire_driver_and_vehicle_table', 110);
INSERT INTO `migrations` VALUES ('2019_07_07_072749_create_category_requests_table', 110);
INSERT INTO `migrations` VALUES ('2019_07_08_100654_add_partner_information_fields', 110);
INSERT INTO `migrations` VALUES ('2019_07_10_054138_change_enum_type_to_partner_registration_channel', 111);
INSERT INTO `migrations` VALUES ('2019_07_10_061835_add_nid_verified_field_to_profiles', 111);
INSERT INTO `migrations` VALUES ('2019_07_10_072520_add_onboarding_publication_status_for_Category', 112);
INSERT INTO `migrations` VALUES ('2019_07_10_114235_create_partner_subscription_package_charges_table', 112);
INSERT INTO `migrations` VALUES ('2019_07_25_112455_add_customer_id_index_orders_table', 113);
INSERT INTO `migrations` VALUES ('2019_07_18_104130_add_enum_type_in_payments_table', 114);
INSERT INTO `migrations` VALUES ('2019_07_18_105207_add_enum_type_in_topup_orders_table', 114);
INSERT INTO `migrations` VALUES ('2019_07_22_095927_add_business_enum_in_topup_vendor_commissions', 114);
INSERT INTO `migrations` VALUES ('2019_07_22_111850_add_logistic_setting_columns_to_job', 114);
INSERT INTO `migrations` VALUES ('2019_07_24_065008_add_description_in_payables_table', 114);
INSERT INTO `migrations` VALUES ('2019_07_25_070826_add_index_in_service_tables', 114);
INSERT INTO `migrations` VALUES ('2019_08_07_092320_add_enum_to_bonuses_and_bonuses_logs_table', 115);
INSERT INTO `migrations` VALUES ('2019_08_18_053036_make_partner_id_and_customer_id_unique_on_partner_pos_customer_table', 116);
INSERT INTO `migrations` VALUES ('2019_07_31_074221_add_faq_to_service_subscriptions', 117);
INSERT INTO `migrations` VALUES ('2019_07_31_091850_add_stock_on_partner_pos_services_table', 117);
INSERT INTO `migrations` VALUES ('2019_07_31_113229_add_platform_field_in_screen_settings_table', 117);
INSERT INTO `migrations` VALUES ('2019_08_01_055714_add_fields_for_product_link_pos', 117);
INSERT INTO `migrations` VALUES ('2019_08_01_091045_add_index_and_unique_for_tables', 117);
INSERT INTO `migrations` VALUES ('2019_08_06_034952_update_payment_tables_for_subscription_order', 117);
INSERT INTO `migrations` VALUES ('2019_08_07_060747_add_description_column_to_partner_pos_services_table', 117);
INSERT INTO `migrations` VALUES ('2019_08_07_062216_add_invoice_link_in_payments_table', 117);
INSERT INTO `migrations` VALUES ('2019_08_20_075603_add_warranty_field_in_pos_inventory', 118);
INSERT INTO `migrations` VALUES ('2019_08_20_084921_add_has_rated_customer_app_in_customers_table', 118);
INSERT INTO `migrations` VALUES ('2019_08_20_085359_add_emi_months_in_payables_table', 118);
INSERT INTO `migrations` VALUES ('2019_08_25_044716_add_is_campaign_in_offer_showcases_table', 118);
INSERT INTO `migrations` VALUES ('2019_08_25_044813_add_index_for_review_type_column_in_review_question_answer_table', 118);
INSERT INTO `migrations` VALUES ('2019_08_26_051520_add_created_by_type_column_to_voucher_table', 118);
INSERT INTO `migrations` VALUES ('2019_08_26_095040_update_transport_ticket_order_agent_type_column', 118);
INSERT INTO `migrations` VALUES ('2019_08_26_100025_update_movie_ticket_order_agent_type_column', 118);
INSERT INTO `migrations` VALUES ('2019_08_26_103358_add_valid_till_to_gift_card_purchases', 118);
INSERT INTO `migrations` VALUES ('2019_08_26_111009_update_resource_type_enum_partner_resource', 118);
INSERT INTO `migrations` VALUES ('2019_08_27_052418_update_movie_ticket_vendor_comission_type_column', 118);
INSERT INTO `migrations` VALUES ('2019_08_31_114651_add_pos_warranty_to_pos_orders_table', 118);
INSERT INTO `migrations` VALUES ('2019_09_01_045131_update_transport_ticket_vendor_comission_type_column', 118);
INSERT INTO `migrations` VALUES ('2019_09_01_132214_add_note_column_in_pos_order_table', 119);
INSERT INTO `migrations` VALUES ('2019_09_05_072054_add_wholesale_price_in_pos_service', 120);
INSERT INTO `migrations` VALUES ('2019_09_08_063549_add_nid_verification_fields_in_profile_table', 120);
INSERT INTO `migrations` VALUES ('2019_09_08_093225_create_lafs_orders_table', 120);
INSERT INTO `migrations` VALUES ('2019_09_08_113527_update_partner_status_enum_on_partners_table', 120);
INSERT INTO `migrations` VALUES ('2019_09_08_115335_add_features_column_to_partner_subscription_packages_table', 120);
INSERT INTO `migrations` VALUES ('2019_09_08_123854_add_auto_billing_activation_field_in_profile_table', 120);
INSERT INTO `migrations` VALUES ('2019_09_09_034739_add_is_published_for_business_to_service_subscriptions', 120);
INSERT INTO `migrations` VALUES ('2019_09_09_140656_add_half_yearly_enum_on_various_partner_related_table', 120);
INSERT INTO `migrations` VALUES ('2019_09_12_142154_add_pos_order_discounts_table', 120);
INSERT INTO `migrations` VALUES ('2019_09_22_063624_add_logistic_discount_to_job', 121);
INSERT INTO `migrations` VALUES ('2019_09_22_094741_create_subscription_order_payments_table', 122);
INSERT INTO `migrations` VALUES ('2019_09_23_044454_add_user_morph_to_subscription_orders_table', 122);
INSERT INTO `migrations` VALUES ('2019_09_24_110138_add_is_valid_in_voucher_table', 122);
INSERT INTO `migrations` VALUES ('2019_10_02_070923_add_labels_and_number_of_participate_and_last_date_of_submission_and_payment_options_and_type_to_procurements', 123);
INSERT INTO `migrations` VALUES ('2019_10_06_051147_add_expense_account_id_to_partner', 123);
INSERT INTO `migrations` VALUES ('2019_10_07_052604_add_request_identification_column_to_pos_related_table', 123);
INSERT INTO `migrations` VALUES ('2019_10_09_123715_update_procurement_item_type_to_procurement_item_fields', 123);
INSERT INTO `migrations` VALUES ('2019_10_15_032545_create_procurement_bids_tables', 124);
INSERT INTO `migrations` VALUES ('2019_10_15_120544_create_partner_pos_service_logs_table', 124);
INSERT INTO `migrations` VALUES ('2019_10_29_054738_create_procurement_advanced_requests_table', 125);
INSERT INTO `migrations` VALUES ('2019_10_29_055730_update_status_of_procurement_related_tables', 125);
INSERT INTO `migrations` VALUES ('2019_10_30_142643_add_bondhu_related_details_in_profile', 125);
INSERT INTO `migrations` VALUES ('2019_10_31_061605_create_profile_general_banking', 125);
INSERT INTO `migrations` VALUES ('2019_10_31_094503_create_profile_mobile_banking', 125);
INSERT INTO `migrations` VALUES ('2019_11_03_045050_create_procurement_order_related_tables', 125);
INSERT INTO `migrations` VALUES ('2019_11_03_093832_add_home_page_setting_to_partner_table', 125);
INSERT INTO `migrations` VALUES ('2019_11_05_135732_add_request_identification_column_to_missing_transaction_table', 125);
INSERT INTO `migrations` VALUES ('2019_11_06_095155_change_enum_type_to_affiliate_and_bkash_info_nullable', 125);
INSERT INTO `migrations` VALUES ('2019_11_06_135909_create_procurement_inivations_table', 125);
INSERT INTO `migrations` VALUES ('2019_11_17_042636_add_license_number_end_date_to_drivers', 126);
INSERT INTO `migrations` VALUES ('2019_11_17_071126_create_topup_bulk_requests_table', 126);
INSERT INTO `migrations` VALUES ('2019_11_17_071131_create_numbers_table', 126);
INSERT INTO `migrations` VALUES ('2019_11_17_080217_add_bulk_request_id_to_topup_orders_table', 126);
INSERT INTO `migrations` VALUES ('2019_11_18_105033_add_mobile_column_to_password_reset_table', 127);
INSERT INTO `migrations` VALUES ('2019_11_17_100908_create_supports_table', 128);
INSERT INTO `migrations` VALUES ('2019_11_17_114502_create_partner_order_request_table', 128);
INSERT INTO `migrations` VALUES ('2019_11_17_120602_create_subscription_order_request_table', 128);
INSERT INTO `migrations` VALUES ('2019_11_17_135226_change_price_field_nullable_partner_pos_services', 128);
INSERT INTO `migrations` VALUES ('2019_11_17_152301_add_shape_and_color_field_partner_pos_services', 128);
INSERT INTO `migrations` VALUES ('2019_11_18_090723_add_show_image_field_partner_pos_services', 128);
INSERT INTO `migrations` VALUES ('2019_11_19_044646_add_license_number_end_date_to_vehicle_registration_informations', 128);
INSERT INTO `migrations` VALUES ('2019_11_19_054315_add_prices_to_location_service_table', 128);
INSERT INTO `migrations` VALUES ('2019_11_19_081749_add_partner_wise_order_id_field_in_pos_orders_table', 128);
INSERT INTO `migrations` VALUES ('2019_11_20_133653_update_order_related_tables_for_order_requests', 128);
INSERT INTO `migrations` VALUES ('2019_11_21_064344_create_location_service_discounts_table', 128);
INSERT INTO `migrations` VALUES ('2019_11_21_071849_add_previous_ambassador_id_in_affiliate_table', 128);
INSERT INTO `migrations` VALUES ('2019_11_21_072324_update_wholesale_price_nullable_in_partner_pos_services', 128);
INSERT INTO `migrations` VALUES ('2019_11_21_074622_alter_subscription_order_status_column', 128);
INSERT INTO `migrations` VALUES ('2019_11_21_125633_update_job_service_table', 128);
INSERT INTO `migrations` VALUES ('2019_11_21_130311_add_services_to_subscription_orders', 128);
INSERT INTO `migrations` VALUES ('2019_11_23_045907_change_bulk_request_id_to_int', 128);
INSERT INTO `migrations` VALUES ('2019_12_03_141902_add_subscription_rules_to_partner_table', 129);
INSERT INTO `migrations` VALUES ('2019_11_26_071301_update_location_service_discount_table', 130);
INSERT INTO `migrations` VALUES ('2019_11_26_073858_create_301_redirect_urls_table', 130);
INSERT INTO `migrations` VALUES ('2019_11_26_073910_create_universal_slugs_table', 130);
INSERT INTO `migrations` VALUES ('2019_11_26_073931_create_meta_tags_table', 130);
INSERT INTO `migrations` VALUES ('2019_11_27_100901_add_order_for_b2b_to_categories', 130);
INSERT INTO `migrations` VALUES ('2019_11_27_113733_create_banks_and_bank_users_table_add_enum_on_status_interest_rate_bank_id_columns_in_partner_bank_loans_table', 130);
INSERT INTO `migrations` VALUES ('2019_11_28_091015_add_additional_fields_for_partner_loan', 130);
INSERT INTO `migrations` VALUES ('2019_11_28_114351_update_location_service_table', 130);
INSERT INTO `migrations` VALUES ('2019_11_28_140913_add_business_fields_for_partner_loan', 130);
INSERT INTO `migrations` VALUES ('2019_12_01_144812_add_remember_token_in_bank_users', 130);
INSERT INTO `migrations` VALUES ('2019_12_02_131146_add_description_field_in_partner_bank_loan_change_logs_table', 130);
INSERT INTO `migrations` VALUES ('2019_12_02_133654_add_delivery_charge_to_categoried_table', 130);
INSERT INTO `migrations` VALUES ('2019_12_02_134938_add_yearly_income_field_in_partners_table', 130);
INSERT INTO `migrations` VALUES ('2019_12_02_141600_update_parters_orders_table_for_order_request', 130);
INSERT INTO `migrations` VALUES ('2019_12_04_151953_create_service_price_update_table', 130);
INSERT INTO `migrations` VALUES ('2019_12_05_062506_create_slider_posts_and_gallery_tables', 130);
INSERT INTO `migrations` VALUES ('2019_12_08_121527_add_note_on_partner_pos_order_items_table', 130);
INSERT INTO `migrations` VALUES ('2019_12_09_135802_add_lowest_upgradable_version_code_to_app_version_code', 131);
INSERT INTO `migrations` VALUES ('2019_12_10_051341_add_common_column_to_location_service', 132);
INSERT INTO `migrations` VALUES ('2019_12_11_121627_update_customer_delivery_address_table', 132);
INSERT INTO `migrations` VALUES ('2019_12_11_143615_create_announcements_tables', 132);
INSERT INTO `migrations` VALUES ('2019_12_12_052234_add_icon_svg_column_to_categories_table', 132);
INSERT INTO `migrations` VALUES ('2019_12_13_062759_create_partnerships_table', 132);
INSERT INTO `migrations` VALUES ('2019_12_13_084233_add_frequency_for_categories', 132);
INSERT INTO `migrations` VALUES ('2019_12_18_071429_create_cross_sale_services_table', 133);
INSERT INTO `migrations` VALUES ('2019_12_24_095802_create_partner_usages_history_and_columns_for_referral_in_partners_table', 134);
INSERT INTO `migrations` VALUES ('2019_12_24_122344_create_newsletters_table', 134);
INSERT INTO `migrations` VALUES ('2019_12_26_053200_create_expense_related_tables', 134);
INSERT INTO `migrations` VALUES ('2019_12_26_090508_add_structured_contents_to_services', 134);
INSERT INTO `migrations` VALUES ('2019_12_28_081524_add_options_content_to_services', 134);
INSERT INTO `migrations` VALUES ('2019_12_29_063907_create_service_combo_table', 134);
INSERT INTO `migrations` VALUES ('2019_12_30_083131_add_service_title_to_categories', 134);
INSERT INTO `migrations` VALUES ('2019_12_31_091430_add_button_text_to_offer', 134);
INSERT INTO `migrations` VALUES ('2020_01_01_114906_add_faqs_in_categories_table', 134);
INSERT INTO `migrations` VALUES ('2020_01_02_103240_create_partner_referral_table', 134);
INSERT INTO `migrations` VALUES ('2020_01_07_091742_create_articles_table', 135);
INSERT INTO `migrations` VALUES ('2020_01_09_071845_add_terms_and_conditions_to_service_table', 135);
INSERT INTO `migrations` VALUES ('2020_01_12_081123_add_popular_service_other_service_to_categories_table', 135);
INSERT INTO `migrations` VALUES ('2020_01_12_113640_add_features_to_services_table', 135);
INSERT INTO `migrations` VALUES ('2020_01_13_052822_create_attendance_related_table', 135);
INSERT INTO `migrations` VALUES ('2020_01_15_093726_add_reject_reason_column_affiliate_table', 135);
INSERT INTO `migrations` VALUES ('2020_01_16_071721_add_due_date_reminder_column_in_partner_pos_customer', 136);
INSERT INTO `migrations` VALUES ('2020_01_21_122315_add_new_screens_in_slider_portal_table', 136);
INSERT INTO `migrations` VALUES ('2020_01_21_132040_add_is_auto_sp_enabled_in_categories_table', 136);
INSERT INTO `migrations` VALUES ('2020_01_22_100342_change_column_type_app_versions_table', 136);
INSERT INTO `migrations` VALUES ('2020_01_23_122536_add_nid_adderss_field_in_profiles', 137);
INSERT INTO `migrations` VALUES ('2020_01_26_054526_add_deleted_at_field_in_pos_orders_table', 137);
INSERT INTO `migrations` VALUES ('2020_01_26_055311_add_sub_domain_index_in_partners_table', 137);
INSERT INTO `migrations` VALUES ('2020_01_28_035020_create_trip_request_approval_tables', 138);
INSERT INTO `migrations` VALUES ('2020_01_28_065415_add_manager_id_in_business_member_table', 138);
INSERT INTO `migrations` VALUES ('2020_01_30_051910_add_name_in_business_offices_table', 138);
INSERT INTO `migrations` VALUES ('2020_02_03_093652_crate_leave_related_tables', 139);
INSERT INTO `migrations` VALUES ('2020_02_04_054449_create_service_usp_table', 139);
INSERT INTO `migrations` VALUES ('2020_02_04_055041_add_qr_code_aacount_type_and_image_column_in_partner_table', 139);
INSERT INTO `migrations` VALUES ('2020_02_04_102132_add_loan_related_fields_in_partner_basic_info_table', 139);
INSERT INTO `migrations` VALUES ('2020_02_04_102630_update_value_to_nullable_category_usp_table', 139);
INSERT INTO `migrations` VALUES ('2020_02_05_083929_add_icon_and_icon_png_hover_to_categories_table', 139);
INSERT INTO `migrations` VALUES ('2020_02_05_104537_add_is_published_for_ddn_column_in_categories_and_services', 139);
INSERT INTO `migrations` VALUES ('2020_02_10_091128_add_bidder_commission_percentage_in_bids_table', 140);
INSERT INTO `migrations` VALUES ('2020_02_09_115117_add_icon_png_active_to_categories_table', 141);
INSERT INTO `migrations` VALUES ('2020_02_10_072834_update_leaves_table_drop_unique_index_add_start_end_date', 142);
INSERT INTO `migrations` VALUES ('2020_02_10_150954_make_unique_taggable_to_meta_tags_table', 142);
INSERT INTO `migrations` VALUES ('2020_02_11_104758_add_total_days_in_leaves_table', 142);
INSERT INTO `migrations` VALUES ('2020_02_16_101548_create_leave_status_change_logs_table', 142);
INSERT INTO `migrations` VALUES ('2020_02_24_064947_update_sluggable_id_to_int_universal_slugs_table', 143);
INSERT INTO `migrations` VALUES ('2020_02_18_115507_add_user_id_type_remove_member_id_from_article_like_dislikes_table', 144);
INSERT INTO `migrations` VALUES ('2020_02_22_132817_add_indexes_in_universal_slugs_table', 144);
INSERT INTO `migrations` VALUES ('2020_02_23_074858_add_portal_name_in_article_types_table', 144);
INSERT INTO `migrations` VALUES ('2020_02_23_094415_create_article_type_article_table', 144);
INSERT INTO `migrations` VALUES ('2020_02_23_104522_remove_article_type_id_from_articles_table', 144);
INSERT INTO `migrations` VALUES ('2020_02_23_120909_create_business_office_hours_table', 144);
INSERT INTO `migrations` VALUES ('2020_02_24_122256_remove_column_from_business_office_hours_table', 144);
INSERT INTO `migrations` VALUES ('2020_03_05_120817_create_training_videos_table', 145);
INSERT INTO `migrations` VALUES ('2020_03_08_122216_add_min_order_amount_to_categories_table', 147);
INSERT INTO `migrations` VALUES ('2020_03_23_181324_add_customer_notified_to_jobs_table', 148);
INSERT INTO `migrations` VALUES ('2020_03_22_023753_update_enum_payment_method_to_procurement_payments_table', 149);
INSERT INTO `migrations` VALUES ('2020_03_22_055226_create_approval_requests_table', 149);
INSERT INTO `migrations` VALUES ('2020_03_22_075340_create_bugs_table', 149);
INSERT INTO `migrations` VALUES ('2020_03_22_140559_create_approval_flows_related_tables', 150);
INSERT INTO `migrations` VALUES ('2020_03_24_130020_add_left_days_to_leaves', 150);
INSERT INTO `migrations` VALUES ('2020_03_24_161956_add_deleted_at_leave_types_tables', 150);
INSERT INTO `migrations` VALUES ('2020_04_02_190530_update_method_column_of_payment_details_table', 150);
INSERT INTO `migrations` VALUES ('2020_04_01_071515_add_note_to_leaves', 151);
INSERT INTO `migrations` VALUES ('2020_04_02_100900_pos_order_item_quantity_change_to_decimal', 151);
INSERT INTO `migrations` VALUES ('2020_04_05_131738_create_affiliate_status_change_log', 151);
INSERT INTO `migrations` VALUES ('2020_04_09_051249_add_validity_in_months_to_gift_cards_table', 151);
INSERT INTO `migrations` VALUES ('2020_04_13_102508_add_thumb_to_articles', 152);
INSERT INTO `migrations` VALUES ('2020_04_15_114051_create_vendor_bkash_payout_table', 152);
INSERT INTO `migrations` VALUES ('2020_04_20_095705_add_user_agent_informations_in_vendor_bkash_payout_table', 153);
INSERT INTO `migrations` VALUES ('2020_03_02_071325_add_gateway_intopup_orders_table', 154);
INSERT INTO `migrations` VALUES ('2020_04_27_072452_create_businesses_attendance_type_table', 156);
INSERT INTO `migrations` VALUES ('2020_04_28_043705_create_government_holidays_table', 156);
INSERT INTO `migrations` VALUES ('2020_04_28_072837_create_resource_transactions_table', 156);
INSERT INTO `migrations` VALUES ('2020_04_29_094548_add_soft_delete_business_attendance_type_and_office_table', 156);
INSERT INTO `migrations` VALUES ('2020_04_29_142657_add_wallet_to_resources_table', 156);
INSERT INTO `migrations` VALUES ('2020_04_30_112929_add_new_methods_to_payment_details', 156);
INSERT INTO `migrations` VALUES ('2020_04_30_180004_drop_payments_payable_id_unique_index', 156);
INSERT INTO `migrations` VALUES ('2020_05_04_062850_add_category_and_shared_to_to_procurements_table', 156);
INSERT INTO `migrations` VALUES ('2020_05_08_111710_add_logistic_enable_to_category_location', 156);
INSERT INTO `migrations` VALUES ('2020_05_09_155735_add_index_to_tags_table', 156);
INSERT INTO `migrations` VALUES ('2020_05_11_060221_add_new_enum_to_app_version_table', 157);
INSERT INTO `migrations` VALUES ('2020_05_11_082933_add_is_vat_and_maximum_amount_in_category_table', 157);
INSERT INTO `migrations` VALUES ('2020_05_14_142245_add_min_response_time_to_categories', 158);
INSERT INTO `migrations` VALUES ('2020_05_14_152817_add_index_to_old_url_to_redirect_url_table', 159);
INSERT INTO `migrations` VALUES ('2020_05_14_153240_create_partner_wallet_setting_update_log_table', 159);
INSERT INTO `migrations` VALUES ('2020_05_14_154045_add_reset_date_to_partner_wallet_settings', 159);
INSERT INTO `migrations` VALUES ('2020_05_17_043106_add_is_remote_and_left_timely_enum_add_to_attendance_table', 160);
INSERT INTO `migrations` VALUES ('2020_04_20_093518_add_emi_month_in_pos_order_payment', 162);
INSERT INTO `migrations` VALUES ('2020_05_18_050533_add_enum_to_rewards_table', 162);
INSERT INTO `migrations` VALUES ('2020_05_19_091522_add_resource_related_fields_in_reward_tables', 162);
INSERT INTO `migrations` VALUES ('2020_05_19_095734_create_automatic_subscription_upgradation_log_table', 162);
INSERT INTO `migrations` VALUES ('2020_05_19_130450_create_reward_campaign_logs', 162);
INSERT INTO `migrations` VALUES ('2020_05_19_154617_create_topup_gateways_table', 162);
INSERT INTO `migrations` VALUES ('2020_05_20_141436_create_topup_gateway_sms_receiver_table', 162);
INSERT INTO `migrations` VALUES ('2020_05_21_055600_add_terms_to_rewards_table', 162);
INSERT INTO `migrations` VALUES ('2020_05_28_042042_modify_estimated_price_to_procurements_table', 162);
INSERT INTO `migrations` VALUES ('2020_05_19_210934_add_advance_subscription_fee_in_subscrition_package_charges_1', 163);
INSERT INTO `migrations` VALUES ('2020_06_01_062456_add_log_to_partner_wallet_setting_update_logs_table', 163);
INSERT INTO `migrations` VALUES ('2020_06_01_080559_create_bondhu_bulk_point_distribute_logs_table', 163);
INSERT INTO `migrations` VALUES ('2020_06_01_160808_add_left_timely_enum_to_status_column', 163);
INSERT INTO `migrations` VALUES ('2020_06_06_084122_create_home_seeting_new_column_in_partners', 164);
INSERT INTO `migrations` VALUES ('2020_05_04_045247_create_withdrawal_requests_table', 165);
INSERT INTO `migrations` VALUES ('2020_06_10_063455_create_profile_nid_submission_logs_table', 166);
INSERT INTO `migrations` VALUES ('2020_06_10_095307_add_log_to_vouchers_table', 166);
INSERT INTO `migrations` VALUES ('2020_06_13_072604_add_index_to_job_service_table_and_orders_table', 167);
INSERT INTO `migrations` VALUES ('2020_06_14_073650_create_resource_status_change_log_table', 167);
INSERT INTO `migrations` VALUES ('2020_06_15_054405_add_status_column_to_resource_table', 167);
INSERT INTO `migrations` VALUES ('2020_06_15_082819_add_gateway_account_name_to_payments', 167);
INSERT INTO `migrations` VALUES ('2020_06_15_114839_create_partner_helps_table', 167);
INSERT INTO `migrations` VALUES ('2020_06_17_105454_index_transaction_id_of_topup_orders_table', 167);
INSERT INTO `migrations` VALUES ('2020_06_17_111226_add_is_first_time_column_to_resource_table', 167);
INSERT INTO `migrations` VALUES ('2020_06_18_093133_add_bidder_price_and_bidder_result_on_bid_table', 167);
INSERT INTO `migrations` VALUES ('2020_06_19_123332_add_columns_in_partner_order_report_table', 167);
INSERT INTO `migrations` VALUES ('2020_06_22_125040_add_material_commission_rate_to_categories', 168);
INSERT INTO `migrations` VALUES ('2020_05_17_072311_create_balance_column_to_all_transactions_table', 169);
INSERT INTO `migrations` VALUES ('2020_06_16_101556_add_verification_message_seen_column_in_resource', 170);
INSERT INTO `migrations` VALUES ('2020_07_06_111106_create_car_rental_thana_wise_prices', 171);
INSERT INTO `migrations` VALUES ('2020_07_07_033639_create_service_surchage_discount_related_table', 171);
INSERT INTO `migrations` VALUES ('2020_07_07_064556_update_enum_to_newsletters_table', 171);
INSERT INTO `migrations` VALUES ('2020_07_07_081020_add_status_and_others_to_business_member_and_member_table', 171);
INSERT INTO `migrations` VALUES ('2020_07_07_101808_create_loan_claim_request_table', 171);
INSERT INTO `migrations` VALUES ('2020_07_08_053140_create_loan_payments_table', 171);
INSERT INTO `migrations` VALUES ('2020_07_08_074451_add_loan_id_column_to_loan_claim_requests_table', 171);
INSERT INTO `migrations` VALUES ('2020_07_09_121836_create_repayment_with_bank_requests_table', 171);
INSERT INTO `migrations` VALUES ('2020_07_12_064819_add_loan_claim_id_to_loan_payments', 171);
INSERT INTO `migrations` VALUES ('2020_07_12_100834_add_apple_id_in_profiles_table', 171);
INSERT INTO `migrations` VALUES ('2020_07_13_045151_add_robi_topup_wallet_column_to_affiliate_table', 171);
INSERT INTO `migrations` VALUES ('2020_07_13_055301_create_ipdc_sms_log_table', 171);
INSERT INTO `migrations` VALUES ('2020_07_13_064835_create_robi_topup_wallet_transactions_table', 171);
INSERT INTO `migrations` VALUES ('2020_07_16_105703_add_resource_id_to_loan_claim_requests_table', 172);
INSERT INTO `migrations` VALUES ('2020_07_20_092744_add_last_annual_fee_payment_at', 172);
INSERT INTO `migrations` VALUES ('2020_07_21_064256_add_work_order_fy_year_sandwich_leave_substitute', 172);
INSERT INTO `migrations` VALUES ('2020_06_17_123102_add_log_field_in_automatic_subscription_upgradation_log_table', 173);
INSERT INTO `migrations` VALUES ('2020_07_22_040020_add_group_column_in_loan_table', 173);
INSERT INTO `migrations` VALUES ('2020_07_27_183135_add_support_for_nagad_payment_method', 173);
INSERT INTO `migrations` VALUES ('2020_08_11_142407_add_is_robi_topup_wallet_column_to_topup_orders_table', 174);
INSERT INTO `migrations` VALUES ('2020_06_02_105800_create_retailer_table_for_dls', 175);
INSERT INTO `migrations` VALUES ('2020_08_19_050227_add_is_inspection_service_to_services_table', 176);
INSERT INTO `migrations` VALUES ('2020_08_24_071931_add_is_flash_to_service_groups_table', 177);
INSERT INTO `migrations` VALUES ('2020_08_26_054629_CreatePartnerNeoBankingInformationTable', 177);
INSERT INTO `migrations` VALUES ('2020_08_26_055515_CreatePartnerNeoBankingAccounts', 177);
INSERT INTO `migrations` VALUES ('2020_08_26_062715_create_category_schedule_slot_table', 177);
INSERT INTO `migrations` VALUES ('2020_08_27_075631_add_status_in_pos_orders', 177);
INSERT INTO `migrations` VALUES ('2020_09_01_064131_add_eligible_partners_auto_sp_assign_in_partner_orders', 177);
INSERT INTO `migrations` VALUES ('2020_08_19_064655_create_leave_info_super_admin_changes_logs_table', 178);
INSERT INTO `migrations` VALUES ('2020_08_19_052222_add_employee_id_to_business_member_table', 179);
INSERT INTO `migrations` VALUES ('2020_09_02_054907_add_service_upsale_feature', 180);
INSERT INTO `migrations` VALUES ('2020_09_03_051019_add_subscription_yearly_feature', 181);
INSERT INTO `migrations` VALUES ('2020_09_03_053042_add_abbreviation_to_business_departments_table', 181);
INSERT INTO `migrations` VALUES ('2020_09_03_061056_add_is_active_for_b2b_to_partners_table', 181);
INSERT INTO `migrations` VALUES ('2020_09_03_044745_add_publication_status_on_procurement_table', 182);
INSERT INTO `migrations` VALUES ('2020_09_03_104701_add_terms_and_conditions_to_categories_table', 183);
INSERT INTO `migrations` VALUES ('2020_09_07_101808_create_artisan_leaves', 183);
INSERT INTO `migrations` VALUES ('2020_09_08_065742_create_neo_banks_table', 183);
INSERT INTO `migrations` VALUES ('2020_09_09_092602_add_bank_identifier_columns_neo_banks_table', 183);
INSERT INTO `migrations` VALUES ('2020_09_12_144125_create_procurement_logs_table', 183);
INSERT INTO `migrations` VALUES ('2020_09_14_102133_modify_to_partner_neo_banking_information_table', 185);
INSERT INTO `migrations` VALUES ('2020_09_14_105505_modify_to_partner_neo_banking_accounts_table', 185);
INSERT INTO `migrations` VALUES ('2020_09_20_114053_add_enum_to_rates', 185);
INSERT INTO `migrations` VALUES ('2020_09_21_065004_add_request_identification_columns_to_customers_and_profiles_table', 185);
INSERT INTO `migrations` VALUES ('2020_09_21_100646_add_catalog_thumb_column_to_categories_and_services_table', 185);
INSERT INTO `migrations` VALUES ('2020_09_22_092357_CreateAffiliateNotificationLogs', 185);
INSERT INTO `migrations` VALUES ('2020_09_22_191856_alter_partner_bank_information_for_withdrawal', 185);
INSERT INTO `migrations` VALUES ('2020_09_23_115512_client_authentication', 185);
INSERT INTO `migrations` VALUES ('2020_09_24_112508_create_external_payments_table', 185);
INSERT INTO `migrations` VALUES ('2020_09_26_151950_add_portal_enum_to_article_type_table', 186);
INSERT INTO `migrations` VALUES ('2020_09_28_075806_add_nick_name_column_to_partner_pos_customers_table', 187);
INSERT INTO `migrations` VALUES ('2020_09_30_093744_add_field_in_affiliate_notification_logs', 187);
INSERT INTO `migrations` VALUES ('2020_10_01_091839_add_half_day_to_businesses_table', 187);
INSERT INTO `migrations` VALUES ('2020_10_04_044911_add_is_half_day_leaves_table', 187);
INSERT INTO `migrations` VALUES ('2020_10_04_112412_modify_left_days_to_leaves_table', 187);
INSERT INTO `migrations` VALUES ('2020_10_04_141033_add_is_half_day_enable_to_leave_types_table', 187);
INSERT INTO `migrations` VALUES ('2020_10_05_205546_update_external_payments_table', 187);
INSERT INTO `migrations` VALUES ('2020_10_07_131033_add_default_columns_to_payment_client_auth_table', 187);
INSERT INTO `migrations` VALUES ('2020_10_12_081745_add_google_product_category_column_in_services_and_categories_table', 187);
INSERT INTO `migrations` VALUES ('2020_10_13_102327_add_new_enum_leave_balance_to_leave_logs_table', 187);
INSERT INTO `migrations` VALUES ('2020_10_14_061845_modify_certain_categories_is_published_for_ddn', 187);
INSERT INTO `migrations` VALUES ('2020_10_14_062803_create_topup_blacklist_numbers_table', 187);
INSERT INTO `migrations` VALUES ('2020_10_14_064414_add_field_notification_type_in_affiliate_notification_logs', 187);
INSERT INTO `migrations` VALUES ('2020_10_15_051840_CreateWrongPINCountTable', 187);
INSERT INTO `migrations` VALUES ('2020_10_15_053037_create_access_token_tables', 187);
INSERT INTO `migrations` VALUES ('2020_10_15_122937_update_information_for_bank_account_column_to_long_text', 188);
INSERT INTO `migrations` VALUES ('2020_10_18_110830_add_commom_fields_in_wrong_pin_count_table', 188);
INSERT INTO `migrations` VALUES ('2020_10_18_140528_create_topup_blacklist_number_update_logs_table', 188);
INSERT INTO `migrations` VALUES ('2020_10_19_053453_create_business_member_leave_types_table', 188);
INSERT INTO `migrations` VALUES ('2020_10_19_064339_create_partner_pos_categories', 188);
INSERT INTO `migrations` VALUES ('2020_10_19_121806_create_topup_transaction_block_notification_receivers_table', 188);
INSERT INTO `migrations` VALUES ('2020_10_20_135059_add_has_webstore_is_webstore_published_delivery_charge_column_to_partners_table', 188);
INSERT INTO `migrations` VALUES ('2020_10_21_062758_add_status_to_pos_orders_table', 188);
INSERT INTO `migrations` VALUES ('2020_10_21_094346_update_topup_blacklist_numbers_table_remove_is_active_mobile_unique_index', 188);
INSERT INTO `migrations` VALUES ('2020_10_29_023941_add_user_id_to_businesses_table', 189);
INSERT INTO `migrations` VALUES ('2020_10_29_134059_add_enum_option_to_payment_details_method_column', 189);
INSERT INTO `migrations` VALUES ('2020_11_01_055753_add_fields_in_access_token_requests', 189);
INSERT INTO `migrations` VALUES ('2020_11_01_082539_add_login_blocked_in_profiles', 189);
INSERT INTO `migrations` VALUES ('2020_11_03_071248_add_payer_id_and_payer_type_column_in_orders_table', 189);
INSERT INTO `migrations` VALUES ('2020_11_04_092140_update_status_enum_to_pos_orders_table', 189);
INSERT INTO `migrations` VALUES ('2020_11_05_064339_create_report_download_logs_table', 189);
INSERT INTO `migrations` VALUES ('2020_11_05_064854_create_profile_password_update_logs', 189);
INSERT INTO `migrations` VALUES ('2020_11_05_080310_add_ip_user_agent_columns_to_topup_orders_table', 189);
INSERT INTO `migrations` VALUES ('2020_11_08_021020_add_index_to_token_for_access_tokens_table', 189);
INSERT INTO `migrations` VALUES ('2020_11_09_061736_add_topup_limit_to_businesses_table', 189);
INSERT INTO `migrations` VALUES ('2020_11_10_100822_add_enable_partial_payment_column_to_categories_table', 189);
INSERT INTO `migrations` VALUES ('2020_11_10_115011_add_email_verified_date_to_profiles_table', 189);
INSERT INTO `migrations` VALUES ('2020_11_11_120659_CreateOTFSettingsTable', 189);
INSERT INTO `migrations` VALUES ('2020_11_11_131527_add_nullable_to_old_password_to_password_update_logs', 189);
INSERT INTO `migrations` VALUES ('2020_11_15_095611_add_new_columns_inside_partner_subscription_package_table', 189);
INSERT INTO `migrations` VALUES ('2020_11_15_125106_update_wrong_pin_count_table', 189);
INSERT INTO `migrations` VALUES ('2020_11_16_073118_add_disclaimer_column_in_categories_table', 189);
INSERT INTO `migrations` VALUES ('2020_11_17_080807_add_advance_subscription_rules_column_in_partner_subscription_charges_table', 189);
INSERT INTO `migrations` VALUES ('2020_11_17_113408_CreateTopUpVendorOTFTable', 189);
INSERT INTO `migrations` VALUES ('2020_11_19_112307_CreateTopUpVendorOTFChangeLogTable', 189);
INSERT INTO `migrations` VALUES ('2020_11_23_043943_enum_modify_to_leaves_table', 190);
INSERT INTO `migrations` VALUES ('2020_11_23_044221_enum_modify_to_leave_status_change_log_table', 190);
INSERT INTO `migrations` VALUES ('2020_11_23_085905_create_active_user_table', 190);
INSERT INTO `migrations` VALUES ('2020_11_24_053808_enum_modify_to_leave_logs_table', 190);
INSERT INTO `migrations` VALUES ('2020_11_24_150348_add_can_topup_for_partners', 190);
INSERT INTO `migrations` VALUES ('2020_11_25_062021_add_next_billing_date_to_partners_table', 190);
INSERT INTO `migrations` VALUES ('2020_11_26_074506_add_resource_app_ios_enum_to_app_version', 190);
INSERT INTO `migrations` VALUES ('2020_11_29_120240_modify_topup_vendor_otf_column_sim_type', 190);
INSERT INTO `migrations` VALUES ('2020_11_30_064801_change_partners_billing_type_column', 190);
INSERT INTO `migrations` VALUES ('2020_11_30_070934_modify_partners_requested_billing_type_column', 190);
INSERT INTO `migrations` VALUES ('2020_11_30_083943_modify_columns_inside_partner_package_update_request_table', 190);
INSERT INTO `migrations` VALUES ('2020_11_30_091708_modify_columns_inside_partner_subscription_discounts_table', 190);
INSERT INTO `migrations` VALUES ('2020_11_30_093914_add_default_status_to_leaves', 190);
INSERT INTO `migrations` VALUES ('2020_11_30_110710_add_lat_long_to_topup_table', 190);
INSERT INTO `migrations` VALUES ('2020_11_30_123844_topup_orders_add_otf_transaction_fields', 190);
INSERT INTO `migrations` VALUES ('2020_12_01_061438_create_authentication_request_tables', 190);
INSERT INTO `migrations` VALUES ('2020_05_19_210934_add_advance_subscription_fee_in_subscrition_package_charges', 191);
INSERT INTO `migrations` VALUES ('2020_12_01_093048_add_adjusted_days_from_last_subscription_to_partner_subscription_package_charges_table', 191);
INSERT INTO `migrations` VALUES ('2020_12_02_120503_change_portal_name_enum_to_article_types_table', 192);
INSERT INTO `migrations` VALUES ('2020_12_05_074506_add_delivered_status_to_sms_campaign_order_receiver', 193);
INSERT INTO `migrations` VALUES ('2020_12_07_085054_create_webstore_banners', 193);
INSERT INTO `migrations` VALUES ('2020_12_08_050050_add_image_gallery_filed_in_pos_service', 194);
INSERT INTO `migrations` VALUES ('2020_12_08_090247_add_purpose_in_authentication_requests_table', 194);
INSERT INTO `migrations` VALUES ('2020_12_10_062406_add_delivery_charge_column_in_pos_orders_table', 194);
INSERT INTO `migrations` VALUES ('2020_12_10_100541_create_pos_service_image_gallery', 194);
INSERT INTO `migrations` VALUES ('2020_12_13_062430_add_blacklisted_reason_in_authorization_tokens_tables', 194);
INSERT INTO `migrations` VALUES ('2020_12_14_090829_add_new_columns_to_partner_subscription_package_charges_table', 194);
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=163 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of movie_ticket_orders
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of movie_ticket_recharge_history
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of movie_ticket_vendor_commissions
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of movie_ticket_vendors
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of neo_banks
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of newsletters
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of noc_requests
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=338 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of notification_settings
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=5749724 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of notifications
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of offer_group_offer
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of offer_groups
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=338 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of offer_showcases
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=3515 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of order_update_logs
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for orders
-- ----------------------------
DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `partner_id` int(10) unsigned DEFAULT NULL,
  `business_id` int(11) DEFAULT NULL,
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
  KEY `orders_customer_id_index` (`customer_id`),
  FULLTEXT KEY `orders_delivery_name_index` (`delivery_name`),
  CONSTRAINT `orders_affiliation_id_foreign` FOREIGN KEY (`affiliation_id`) REFERENCES `affiliations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_custom_order_id_foreign` FOREIGN KEY (`custom_order_id`) REFERENCES `custom_orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_delivery_address_id_foreign` FOREIGN KEY (`delivery_address_id`) REFERENCES `customer_delivery_addresses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_favourite_id_foreign` FOREIGN KEY (`favourite_id`) REFERENCES `customer_favourites` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_info_call_id_foreign` FOREIGN KEY (`info_call_id`) REFERENCES `info_calls` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_subscription_order_id_foreign` FOREIGN KEY (`subscription_order_id`) REFERENCES `subscription_orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `orders_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=254828 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of orders
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=11522 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_affiliations
-- ----------------------------
BEGIN;
COMMIT;

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
  `acc_type` enum('0','1') COLLATE utf8_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=32376 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_bank_informations
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_bank_loan_change_logs
-- ----------------------------
BEGIN;
COMMIT;

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
  `type` enum('general','micro') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'general',
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_bank_loans
-- ----------------------------
BEGIN;
COMMIT;

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
  `vat_registration_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `vat_registration_attachment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tin_no` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
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
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_basic_informations_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_basic_informations_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=68476 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_basic_informations
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_closing_requests
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=40202 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_daily_stats
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=77386 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_geo_change_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_helps
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=43768 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_leaves
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=5626 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_location_update_requests
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for partner_neo_banking_accounts
-- ----------------------------
DROP TABLE IF EXISTS `partner_neo_banking_accounts`;
CREATE TABLE `partner_neo_banking_accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `account_no` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `bank_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_neo_banking_accounts_bank_id_foreign` (`bank_id`),
  KEY `partner_neo_banking_accounts_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_neo_banking_accounts_bank_id_foreign` FOREIGN KEY (`bank_id`) REFERENCES `neo_banks` (`id`),
  CONSTRAINT `partner_neo_banking_accounts_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_neo_banking_accounts
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_neo_banking_information
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of partner_order_advance_requests
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_order_finance_collections
-- ----------------------------
BEGIN;
COMMIT;

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
  `transaction_detail` text COLLATE utf8_unicode_ci NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=125128 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_order_payments
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=104287 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_order_reconcile_logs
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for partner_order_report
-- ----------------------------
DROP TABLE IF EXISTS `partner_order_report`;
CREATE TABLE `partner_order_report` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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
  `delivery_mobile` varchar(14) COLLATE utf8_unicode_ci DEFAULT NULL,
  `delivery_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_vip` tinyint(1) NOT NULL DEFAULT '0',
  `sp_id` int(10) unsigned DEFAULT NULL,
  `sp_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
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
  `service_id` int(10) unsigned DEFAULT NULL,
  `services` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
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
  UNIQUE KEY `partner_order_report_order_code_unique` (`order_code`)
) ENGINE=InnoDB AUTO_INCREMENT=221180 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_order_report
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_order_requests
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of partner_order_stage_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=362784 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_order_status_logs
-- ----------------------------
BEGIN;
COMMIT;

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
  CONSTRAINT `partner_orders_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=286648 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_orders
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of partner_package_change_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=719 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_package_update_requests
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for partner_pos_categories
-- ----------------------------
DROP TABLE IF EXISTS `partner_pos_categories`;
CREATE TABLE `partner_pos_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_pos_categories_partner_id_foreign` (`partner_id`),
  KEY `partner_pos_categories_category_id_foreign` (`category_id`),
  CONSTRAINT `partner_pos_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `pos_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `partner_pos_categories_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_pos_categories
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `partner_pos_customers_partner_id_customer_id_unique` (`partner_id`,`customer_id`),
  KEY `partner_pos_customers_partner_id_foreign` (`partner_id`) USING BTREE,
  KEY `partner_pos_customers_customer_id_foreign` (`customer_id`) USING BTREE,
  CONSTRAINT `partner_pos_customers_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `pos_customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `partner_pos_customers_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=275 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_pos_customers
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `partner_pos_service_discounts_partner_pos_service_id_foreign` (`partner_pos_service_id`) USING BTREE,
  CONSTRAINT `partner_pos_service_discounts_partner_pos_service_id_foreign` FOREIGN KEY (`partner_pos_service_id`) REFERENCES `partner_pos_services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_pos_service_discounts
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_pos_service_image_gallery
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_pos_service_logs
-- ----------------------------
BEGIN;
COMMIT;

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
  `unit` enum('ft','sft','sq.m','kg','piece','km','litre','meter','dozon','inch','bosta','unit','set') COLLATE utf8_unicode_ci DEFAULT NULL,
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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `partner_pos_services_partner_id_foreign` (`partner_id`) USING BTREE,
  KEY `partner_pos_services_pos_category_id_foreign` (`pos_category_id`) USING BTREE,
  CONSTRAINT `partner_pos_services_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `partner_pos_services_pos_category_id_foreign` FOREIGN KEY (`pos_category_id`) REFERENCES `pos_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=493 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_pos_services
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for partner_pos_settings
-- ----------------------------
DROP TABLE IF EXISTS `partner_pos_settings`;
CREATE TABLE `partner_pos_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_id` int(10) unsigned NOT NULL,
  `vat_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `partner_pos_settings_partner_id_foreign` (`partner_id`) USING BTREE,
  CONSTRAINT `partner_pos_settings_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=881 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_pos_settings
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of partner_references
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_referrals
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=141032 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_resource
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for partner_retailer
-- ----------------------------
DROP TABLE IF EXISTS `partner_retailer`;
CREATE TABLE `partner_retailer` (
  `partner_id` int(10) unsigned NOT NULL,
  `retailer_id` int(10) unsigned NOT NULL,
  KEY `partner_retailer_partner_id_foreign` (`partner_id`),
  KEY `partner_retailer_retailer_id_foreign` (`retailer_id`),
  CONSTRAINT `partner_retailer_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `partner_retailer_retailer_id_foreign` FOREIGN KEY (`retailer_id`) REFERENCES `retailers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_retailer
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=592550 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_service
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=4693 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_service_discounts
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=19960 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_service_prices_update
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for partner_service_surcharges
-- ----------------------------
DROP TABLE IF EXISTS `partner_service_surcharges`;
CREATE TABLE `partner_service_surcharges` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `partner_service_id` int(10) unsigned DEFAULT NULL,
  `amount` decimal(8,2) NOT NULL,
  `is_amount_percentage` tinyint(1) NOT NULL DEFAULT '1',
  `start_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `end_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
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
) ENGINE=InnoDB AUTO_INCREMENT=9048 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_service_surcharges
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=67340 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_status_change_logs
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of partner_subscription_discounts
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=307 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_subscription_package_charges
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of partner_subscription_package_logs
-- ----------------------------
BEGIN;
COMMIT;

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
  `status` enum('unpublished','published') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'published',
  `rules` longtext COLLATE utf8_unicode_ci NOT NULL,
  `activate_from` date DEFAULT NULL,
  `new_rules` longtext COLLATE utf8_unicode_ci,
  `usps` longtext COLLATE utf8_unicode_ci,
  `features` longtext COLLATE utf8_unicode_ci,
  `badge` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `badge_thumb` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_subscription_packages
-- ----------------------------
BEGIN;
COMMIT;

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
  CONSTRAINT `partner_transactions_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `partner_transactions_partner_order_id_foreign` FOREIGN KEY (`partner_order_id`) REFERENCES `partner_orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=278728 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_transactions
-- ----------------------------
BEGIN;
COMMIT;

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
  CONSTRAINT `partner_usages_history_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_usages_history
-- ----------------------------
BEGIN;
COMMIT;

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
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_wallet_setting_id_foreign` (`partner_wallet_setting_id`),
  KEY `partner_wallet_setting_update_logs_portal_name_index` (`portal_name`),
  CONSTRAINT `partner_wallet_setting_id_foreign` FOREIGN KEY (`partner_wallet_setting_id`) REFERENCES `partner_wallet_settings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_wallet_setting_update_logs
-- ----------------------------
BEGIN;
COMMIT;

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
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_wallet_settings_partner_id_foreign` (`partner_id`),
  CONSTRAINT `partner_wallet_settings_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=68437 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_wallet_settings
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_webstore_banner
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=1032 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_withdrawal_requests
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=414882 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partner_working_hours
-- ----------------------------
BEGIN;
COMMIT;

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
  `bkash_account_type` enum('0','1','2') COLLATE utf8_unicode_ci DEFAULT NULL,
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
  `can_topup` tinyint(4) NOT NULL DEFAULT '1',
  `verified_at` timestamp NULL DEFAULT NULL,
  `verification_note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `prm_id` int(11) DEFAULT NULL,
  `status` enum('Verified','Unverified','Paused','Closed','Blacklisted','Waiting','Onboarded','Rejected','Inactive') COLLATE utf8_unicode_ci DEFAULT 'Onboarded',
  `package_id` int(10) unsigned DEFAULT '1',
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
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `partners_affiliation_id_unique` (`affiliation_id`),
  UNIQUE KEY `partners_expense_account_id_unique` (`expense_account_id`),
  KEY `partners_top_badge_id_foreign` (`top_badge_id`),
  KEY `partners_package_id_foreign` (`package_id`),
  KEY `partners_discount_id_foreign` (`discount_id`),
  KEY `partners_affiliate_id_foreign` (`affiliate_id`),
  KEY `partners_moderator_id_foreign` (`moderator_id`),
  KEY `partners_sub_domain_index` (`sub_domain`),
  KEY `partners_can_topup_index` (`can_topup`),
  CONSTRAINT `partners_affiliate_id_foreign` FOREIGN KEY (`affiliate_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `partners_affiliation_id_foreign` FOREIGN KEY (`affiliation_id`) REFERENCES `partner_affiliations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `partners_discount_id_foreign` FOREIGN KEY (`discount_id`) REFERENCES `partner_subscription_discounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `partners_moderator_id_foreign` FOREIGN KEY (`moderator_id`) REFERENCES `affiliates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `partners_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `partner_subscription_packages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `partners_top_badge_id_foreign` FOREIGN KEY (`top_badge_id`) REFERENCES `badges` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=68559 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partners
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partnership_slides
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of partnerships
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of password_resets
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for payables
-- ----------------------------
DROP TABLE IF EXISTS `payables`;
CREATE TABLE `payables` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('partner_order,wallet_recharge,subscription_order,gift_card_purchase,movie_ticket_purchase,transport_ticket_purchase,utility_order,payment_link,procurement,partner_bank_loan') COLLATE utf8_unicode_ci DEFAULT NULL,
  `type_id` int(10) unsigned DEFAULT NULL,
  `user_type` enum('App\\Models\\Customer','App\\Models\\Partner','App\\Models\\Affiliate','App\\Models\\Business') COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `amount` decimal(11,2) unsigned NOT NULL,
  `emi_month` int(11) DEFAULT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `completion_type` enum('order,advanced_order,wallet_recharge,gift_card_purchase,movie_ticket_purchase,transport_ticket_purchase,utility_order,payment_link,subscription_order,procurement,partner_bank_loan') COLLATE utf8_unicode_ci DEFAULT NULL,
  `success_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fail_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18014 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of payables
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of payment_client_authentications
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for payment_details
-- ----------------------------
DROP TABLE IF EXISTS `payment_details`;
CREATE TABLE `payment_details` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` int(10) unsigned NOT NULL,
  `method` enum('wallet','partner_wallet','bonus','ssl','bkash','cbl','dbbl','bkash_old','ebl','ssl_donation','ok_wallet','port_wallet','bbl','nagad','bondhu_balance') COLLATE utf8_unicode_ci DEFAULT NULL,
  `amount` decimal(11,2) NOT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_details_payment_id_foreign` (`payment_id`),
  CONSTRAINT `payment_details_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18059 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of payment_details
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=11163 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of payment_status_change_logs
-- ----------------------------
BEGIN;
COMMIT;

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
  CONSTRAINT `payments_payable_id_foreign` FOREIGN KEY (`payable_id`) REFERENCES `payables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18011 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of payments
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for permission_role
-- ----------------------------
DROP TABLE IF EXISTS `permission_role`;
CREATE TABLE `permission_role` (
  `permission_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `permission_role_role_id_foreign` (`role_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of permission_role
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for permissions
-- ----------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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
  UNIQUE KEY `permissions_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of permissions
-- ----------------------------
BEGIN;
COMMIT;

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
  `order` smallint(5) unsigned DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `icon_png` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `pos_categories_parent_id_foreign` (`parent_id`) USING BTREE,
  CONSTRAINT `pos_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `pos_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=96 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of pos_categories
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `pos_customers_profile_id_foreign` (`profile_id`) USING BTREE,
  CONSTRAINT `pos_customers_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=252 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of pos_customers
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=190 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of pos_order_discounts
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `pos_order_items_pos_order_id_foreign` (`pos_order_id`) USING BTREE,
  KEY `pos_order_items_service_id_foreign` (`service_id`) USING BTREE,
  CONSTRAINT `pos_order_items_pos_order_id_foreign` FOREIGN KEY (`pos_order_id`) REFERENCES `pos_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pos_order_items_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `partner_pos_services` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7261 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of pos_order_items
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `pos_order_logs_pos_order_id_foreign` (`pos_order_id`) USING BTREE,
  CONSTRAINT `pos_order_logs_pos_order_id_foreign` FOREIGN KEY (`pos_order_id`) REFERENCES `pos_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of pos_order_logs
-- ----------------------------
BEGIN;
COMMIT;

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
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `pos_order_payments_pos_order_id_foreign` (`pos_order_id`) USING BTREE,
  CONSTRAINT `pos_order_payments_pos_order_id_foreign` FOREIGN KEY (`pos_order_id`) REFERENCES `pos_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6933 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of pos_order_payments
-- ----------------------------
BEGIN;
COMMIT;

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
  `delivery_charge` decimal(8,2) DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `voucher_id` int(10) unsigned DEFAULT NULL,
  `note` longtext COLLATE utf8_unicode_ci,
  `status` enum('Pending','Processing','Declined','Shipped','Completed','Cancelled') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Pending',
  `sales_channel` enum('pos','webstore') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pos',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `pos_orders_previous_order_id_foreign` (`previous_order_id`) USING BTREE,
  KEY `pos_orders_customer_id_foreign` (`customer_id`) USING BTREE,
  KEY `pos_orders_partner_id_foreign` (`partner_id`) USING BTREE,
  KEY `pos_orders_voucher_id_foreign` (`voucher_id`),
  CONSTRAINT `pos_orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `pos_customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pos_orders_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pos_orders_previous_order_id_foreign` FOREIGN KEY (`previous_order_id`) REFERENCES `pos_orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pos_orders_voucher_id_foreign` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6977 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of pos_orders
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `procurement_invitations_procurement_id_partner_id_unique` (`procurement_id`,`partner_id`) USING BTREE,
  KEY `procurement_invitations_partner_id_foreign` (`partner_id`) USING BTREE,
  CONSTRAINT `procurement_invitations_partner_id_foreign` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `procurement_invitations_procurement_id_foreign` FOREIGN KEY (`procurement_id`) REFERENCES `procurements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of procurement_invitations
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `procurement_item_fields_procurement_item_id_foreign` (`procurement_item_id`) USING BTREE,
  CONSTRAINT `procurement_item_fields_procurement_item_id_foreign` FOREIGN KEY (`procurement_item_id`) REFERENCES `procurement_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=640 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of procurement_item_fields
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `procurement_items_procurement_id_foreign` (`procurement_id`) USING BTREE,
  CONSTRAINT `procurement_items_procurement_id_foreign` FOREIGN KEY (`procurement_id`) REFERENCES `procurements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=314 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of procurement_items
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of procurement_logs
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `payment_status_change_logs_payment_request_id_foreign` (`payment_request_id`) USING BTREE,
  KEY `procurement_payment_request_status_change_logs_from_status_index` (`from_status`) USING BTREE,
  KEY `procurement_payment_request_status_change_logs_to_status_index` (`to_status`) USING BTREE,
  CONSTRAINT `payment_status_change_logs_payment_request_id_foreign` FOREIGN KEY (`payment_request_id`) REFERENCES `procurement_payment_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=181 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of procurement_payment_request_status_change_logs
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `procurement_payment_requests_procurement_id_foreign` (`procurement_id`) USING BTREE,
  KEY `procurement_payment_requests_bid_id_foreign` (`bid_id`) USING BTREE,
  KEY `procurement_payment_requests_status_index` (`status`) USING BTREE,
  CONSTRAINT `procurement_payment_requests_bid_id_foreign` FOREIGN KEY (`bid_id`) REFERENCES `bids` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `procurement_payment_requests_procurement_id_foreign` FOREIGN KEY (`procurement_id`) REFERENCES `procurements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of procurement_payment_requests
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `procurement_payments_procurement_id_foreign` (`procurement_id`) USING BTREE,
  CONSTRAINT `procurement_payments_procurement_id_foreign` FOREIGN KEY (`procurement_id`) REFERENCES `procurements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=225 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of procurement_payments
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `procurement_questions_procurement_id_foreign` (`procurement_id`) USING BTREE,
  CONSTRAINT `procurement_questions_procurement_id_foreign` FOREIGN KEY (`procurement_id`) REFERENCES `procurements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of procurement_questions
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `procurement_status_change_logs_procurement_id_foreign` (`procurement_id`) USING BTREE,
  KEY `procurement_status_change_logs_from_status_index` (`from_status`) USING BTREE,
  KEY `procurement_status_change_logs_to_status_index` (`to_status`) USING BTREE,
  CONSTRAINT `procurement_status_change_logs_procurement_id_foreign` FOREIGN KEY (`procurement_id`) REFERENCES `procurements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=215 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of procurement_status_change_logs
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `procurements_purchase_request_id_foreign` (`purchase_request_id`) USING BTREE,
  KEY `procurements_category_id_foreign` (`category_id`),
  CONSTRAINT `procurements_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `procurements_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=513 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of procurements
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of profile_bank_informations
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of profile_mobile_bank_informations
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for profile_nid_submission_logs
-- ----------------------------
DROP TABLE IF EXISTS `profile_nid_submission_logs`;
CREATE TABLE `profile_nid_submission_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` int(10) unsigned NOT NULL,
  `submitted_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log` text COLLATE utf8_unicode_ci,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profile_nid_submission_logs_profile_id_foreign` (`profile_id`),
  CONSTRAINT `profile_nid_submission_logs_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of profile_nid_submission_logs
-- ----------------------------
BEGIN;
COMMIT;

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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `profile_password_update_logs_profile_id_foreign` (`profile_id`),
  CONSTRAINT `profile_password_update_logs_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of profile_password_update_logs
-- ----------------------------
BEGIN;
COMMIT;

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
  `is_blacklisted` tinyint(4) NOT NULL DEFAULT '0',
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
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
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
  CONSTRAINT `profiles_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `profiles_grantor_id_foreign` FOREIGN KEY (`grantor_id`) REFERENCES `profiles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `profiles_nominee_id_foreign` FOREIGN KEY (`nominee_id`) REFERENCES `profiles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1143315 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of profiles
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=83738 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of promotions
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for purchase_request_approvals
-- ----------------------------
DROP TABLE IF EXISTS `purchase_request_approvals`;
CREATE TABLE `purchase_request_approvals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_request_id` int(10) unsigned DEFAULT NULL,
  `member_id` int(10) unsigned DEFAULT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of purchase_request_approvals
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of purchase_request_item_fields
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of purchase_request_items
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of purchase_request_questions
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of purchase_requests
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of push_notifications
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3097 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of push_subscriptions
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for queue_failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS `queue_failed_jobs`;
CREATE TABLE `queue_failed_jobs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `connection` text COLLATE utf8_unicode_ci NOT NULL,
  `queue` text COLLATE utf8_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of queue_failed_jobs
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of quotations
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of rate_answer_rate_question
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of rate_answers
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of rate_question_rate
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rate_questions
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of rates
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of redirect_urls
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of repayment_with_bank_requests
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of report_download_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of resource_employments
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of resource_schedule_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=84964 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of resource_schedules
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of resource_status_change_logs
-- ----------------------------
BEGIN;
COMMIT;

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
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `resource_transactions_resource_id_foreign` (`resource_id`),
  CONSTRAINT `resource_transactions_resource_id_foreign` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of resource_transactions
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for resources
-- ----------------------------
DROP TABLE IF EXISTS `resources`;
CREATE TABLE `resources` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` int(10) unsigned NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=76277 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of resources
-- ----------------------------
BEGIN;
COMMIT;

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
  CONSTRAINT `retailer_members_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `retailer_members_retailer_id_foreign` FOREIGN KEY (`retailer_id`) REFERENCES `retailers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of retailer_members
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `retailers_strategic_partner_id_mobile_unique` (`strategic_partner_id`,`mobile`) USING BTREE,
  CONSTRAINT `retailers_strategic_partner_id_foreign` FOREIGN KEY (`strategic_partner_id`) REFERENCES `strategic_partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of retailers
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=23173 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of review_question_answer
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=75044 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of reviews
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of reward_actions
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for reward_campaign_logs
-- ----------------------------
DROP TABLE IF EXISTS `reward_campaign_logs`;
CREATE TABLE `reward_campaign_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reward_campaign_id` int(10) unsigned NOT NULL,
  `target_type` enum('partner','resource','customer') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'partner',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of reward_campaign_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of reward_campaigns
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=639 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of reward_constraints
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for reward_logs
-- ----------------------------
DROP TABLE IF EXISTS `reward_logs`;
CREATE TABLE `reward_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reward_id` int(10) unsigned DEFAULT NULL,
  `target_type` enum('App\\Models\\Partner','App\\Models\\Customer','App\\Models\\Resource') COLLATE utf8_unicode_ci DEFAULT 'App\\Models\\Partner',
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
) ENGINE=InnoDB AUTO_INCREMENT=54363 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of reward_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of reward_no_constraints
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of reward_orders
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of reward_point_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of reward_products
-- ----------------------------
BEGIN;
COMMIT;

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
  `target_type` enum('App\\Models\\Partner','App\\Models\\Customer','App\\Models\\Resource') COLLATE utf8_unicode_ci DEFAULT 'App\\Models\\Partner',
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
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rewards
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of robi_topup_wallet_transactions
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for role_user
-- ----------------------------
DROP TABLE IF EXISTS `role_user`;
CREATE TABLE `role_user` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_user_role_id_foreign` (`role_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of role_user
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `department_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`),
  KEY `roles_department_id_foreign` (`department_id`),
  CONSTRAINT `roles_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of roles
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of sale_targets
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of schedule_slots
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=166 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of screen_setting_elements
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of screen_settings
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of service_discounts
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of service_group_location
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of service_group_service
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of service_groups
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of service_price_update
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of service_requests
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of service_service_discount
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of service_subscription_discounts
-- ----------------------------
BEGIN;
COMMIT;

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
  `is_published_for_b2b` tinyint(4) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_subscriptions_service_id_unique` (`service_id`),
  CONSTRAINT `service_subscriptions_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of service_subscriptions
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of service_surcharges
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of service_units
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of service_usp
-- ----------------------------
BEGIN;
COMMIT;

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
  `short_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `google_product_category` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
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
  CONSTRAINT `services_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2092 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of services
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of slider_portal
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of slider_slide
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of sliders
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for sliders_copy
-- ----------------------------
DROP TABLE IF EXISTS `sliders_copy`;
CREATE TABLE `sliders_copy` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `image_link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `small_image_link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `target_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `target_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `target_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `order` smallint(6) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_active_for_web` tinyint(1) NOT NULL DEFAULT '1',
  `is_active_for_app` tinyint(1) NOT NULL DEFAULT '1',
  `is_active_for_business` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of sliders_copy
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of slides
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for sms_campaign_order_receivers
-- ----------------------------
DROP TABLE IF EXISTS `sms_campaign_order_receivers`;
CREATE TABLE `sms_campaign_order_receivers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sms_campaign_order_id` int(10) unsigned NOT NULL,
  `receiver_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `receiver_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` enum('pending','successful','failed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
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
) ENGINE=InnoDB AUTO_INCREMENT=13627 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of sms_campaign_order_receivers
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=183 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of sms_campaign_orders
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `sms_templates_event_name_unique` (`event_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of sms_templates
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `strategic_partner_members_strategic_partner_id_foreign` (`strategic_partner_id`) USING BTREE,
  KEY `strategic_partner_members_profile_id_foreign` (`profile_id`) USING BTREE,
  CONSTRAINT `strategic_partner_members_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `strategic_partner_members_strategic_partner_id_foreign` FOREIGN KEY (`strategic_partner_id`) REFERENCES `strategic_partners` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of strategic_partner_members
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of strategic_partners
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of subscription_order_payments
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of subscription_order_requests
-- ----------------------------
BEGIN;
COMMIT;

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
  `billing_cycle_start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `billing_cycle_end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of subscription_orders
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of supports
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of system_updates
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of taggables
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=3266 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of tags
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of thanas
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of to_do_list_shared_users
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=400 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of to_do_lists
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of to_do_settings
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of to_do_task_attachments
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=419 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of to_do_tasks
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of topup_blacklist_number_update_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of topup_blacklist_numbers
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of topup_bulk_request_numbers
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for topup_bulk_requests
-- ----------------------------
DROP TABLE IF EXISTS `topup_bulk_requests`;
CREATE TABLE `topup_bulk_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` int(11) NOT NULL,
  `agent_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('pending','completed','successful','failed') COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=471 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of topup_bulk_requests
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of topup_gateway_sms_receiver
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of topup_gateways
-- ----------------------------
BEGIN;
COMMIT;

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
  `vendor_id` int(10) unsigned DEFAULT NULL,
  `gateway` enum('ssl','robi','airtel','banglalink') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ssl',
  `otf_sheba_commission` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `otf_agent_commission` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `otf_id` int(10) unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
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
  PRIMARY KEY (`id`) USING BTREE,
  KEY `topup_orders_vendor_id_foreign` (`vendor_id`) USING BTREE,
  KEY `topup_orders_transaction_id_index` (`transaction_id`) USING BTREE,
  KEY `topup_orders_gateway_index` (`gateway`) USING BTREE,
  KEY `topup_orders_created_at_index` (`created_at`) USING BTREE,
  CONSTRAINT `topup_orders_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `topup_vendors` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3621155 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of topup_orders
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of topup_otf_settings
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of topup_recharge_history
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of topup_transaction_block_notification_receivers
-- ----------------------------
BEGIN;
COMMIT;

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
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `topup_vendor_commissions_topup_vendor_id_foreign` (`topup_vendor_id`),
  CONSTRAINT `topup_vendor_commissions_topup_vendor_id_foreign` FOREIGN KEY (`topup_vendor_id`) REFERENCES `topup_vendors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of topup_vendor_commissions
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of topup_vendor_otf
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of topup_vendor_otf_status_change_logs
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for topup_vendors
-- ----------------------------
DROP TABLE IF EXISTS `topup_vendors`;
CREATE TABLE `topup_vendors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `amount` decimal(11,2) DEFAULT '0.00',
  `gateway` enum('ssl','robi','airtel','banglalink','paywell') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ssl',
  `sheba_commission` decimal(5,2) NOT NULL DEFAULT '0.00',
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of topup_vendors
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of training_videos
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for transactions
-- ----------------------------
DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `gateway` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `transaction_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `amount` decimal(11,2) DEFAULT NULL,
  `used_on_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `used_on_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `details` longtext COLLATE utf8_unicode_ci,
  `from_account` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `to_account` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `time` datetime DEFAULT NULL,
  `portal` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8_unicode_ci,
  `request_details` longtext COLLATE utf8_unicode_ci,
  `created_by` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`,`transaction_id`) USING BTREE,
  UNIQUE KEY `transaction_id_unique` (`transaction_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of transactions
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of transport_routes
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of transport_ticket_orders
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of transport_ticket_recharge_history
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of transport_ticket_vendor_commissions
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of transport_ticket_vendors
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of treats
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of trip_request_approval_flow_approvers
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of trip_request_approval_flows
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of trip_request_approvals
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of unfollowed_notifications
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for universal_slugs
-- ----------------------------
DROP TABLE IF EXISTS `universal_slugs`;
CREATE TABLE `universal_slugs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sluggable_type` enum('master_category','secondary_category','service') COLLATE utf8_unicode_ci NOT NULL,
  `sluggable_id` int(10) unsigned NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=901 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of universal_slugs
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of upazilas
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of updates
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=206231 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of user_login_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=476592 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of user_workload_logs
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=346 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of users
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of usps
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of vehicle_basic_informations
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of vehicle_registration_informations
-- ----------------------------
BEGIN;
COMMIT;

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
  `business_department_id` int(10) unsigned DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of vehicles
-- ----------------------------
BEGIN;
COMMIT;

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
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of vendor_bkash_payout
-- ----------------------------
BEGIN;
COMMIT;

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
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vendor_transactions_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `vendor_transactions_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of vendor_transactions
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for vendors
-- ----------------------------
DROP TABLE IF EXISTS `vendors`;
CREATE TABLE `vendors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `app_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `app_secret` longtext COLLATE utf8_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `wallet` decimal(11,2) NOT NULL DEFAULT '0.00',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of vendors
-- ----------------------------
BEGIN;
COMMIT;

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
-- Records of votes
-- ----------------------------
BEGIN;
COMMIT;

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
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `vouchers_code_unique` (`code`) USING BTREE,
  KEY `vouchers_owner_id_index` (`owner_id`) USING BTREE,
  KEY `vouchers_owner_type_index` (`owner_type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2736502 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of vouchers
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for webstore_banners
-- ----------------------------
DROP TABLE IF EXISTS `webstore_banners`;
CREATE TABLE `webstore_banners` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `image_link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_published` tinyint(4) NOT NULL DEFAULT '1',
  `created_by` int(10) unsigned NOT NULL,
  `created_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_by` int(10) unsigned NOT NULL,
  `updated_by_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of webstore_banners
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for withdrawal_requests
-- ----------------------------
DROP TABLE IF EXISTS `withdrawal_requests`;
CREATE TABLE `withdrawal_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `requester_id` int(11) NOT NULL,
  `requester_type` enum('resource','partner') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'partner',
  `amount` decimal(11,2) unsigned NOT NULL,
  `status` enum('pending','approval_pending','approved','rejected','completed','failed','expired','cancelled') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_method` enum('bank','bkash') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'bank',
  `payment_info` text COLLATE utf8_unicode_ci,
  `last_fail_reason` longtext COLLATE utf8_unicode_ci,
  `portal_name` enum('admin-portal','partner-portal','manager-app','customer-app','customer-portal','resource-portal','resource-app','bondhu-app','bondhu-portal','automatic') COLLATE utf8_unicode_ci DEFAULT NULL,
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
  KEY `withdrawal_requests_requester_id_index` (`requester_id`),
  KEY `withdrawal_requests_requester_type_index` (`requester_type`),
  KEY `withdrawal_requests_status_index` (`status`),
  KEY `withdrawal_requests_payment_method_index` (`payment_method`),
  KEY `withdrawal_requests_portal_name_index` (`portal_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of withdrawal_requests
-- ----------------------------
BEGIN;
COMMIT;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of wrong_pin_count
-- ----------------------------
BEGIN;
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
