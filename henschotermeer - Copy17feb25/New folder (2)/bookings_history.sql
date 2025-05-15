/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 80040
 Source Host           : localhost:3306
 Source Schema         : parkingware-hanscatameer

 Target Server Type    : MySQL
 Target Server Version : 80040
 File Encoding         : 65001

 Date: 03/02/2025 19:49:13
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for bookings_history
-- ----------------------------
DROP TABLE IF EXISTS `bookings_history`;
CREATE TABLE `bookings_history`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `live_id` int NOT NULL DEFAULT 0,
  `customer_id` int UNSIGNED NULL DEFAULT NULL,
  `customer_vehicle_info_id` int UNSIGNED NULL DEFAULT NULL,
  `checkin_time` timestamp NULL DEFAULT NULL,
  `checkout_time` timestamp NULL DEFAULT NULL,
  `type` tinyint NOT NULL DEFAULT 0,
  `is_user_logged_in` tinyint NOT NULL DEFAULT 0,
  `vehicle_num` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `phone_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `first_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `last_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `sender_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `rating_id` bigint NULL DEFAULT NULL,
  `is_cancelled` tinyint NOT NULL DEFAULT 0,
  `is_customer_left` tinyint NOT NULL DEFAULT 0,
  `customer_left_status` tinyint NOT NULL DEFAULT 0,
  `is_user_ballance_adjustment` tinyint NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `image_path` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ticket_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `barcode` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `tommy_parent_id` int NULL DEFAULT NULL,
  `tommy_children_id` int NULL DEFAULT NULL,
  `tommy_children_dob` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `is_paid` tinyint NOT NULL DEFAULT 0,
  `confidence` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `country_code` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `low_confidence` tinyint NOT NULL DEFAULT 0,
  `promo_code` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `is_local_updated` tinyint NOT NULL DEFAULT 1,
  `is_live_updated` tinyint NOT NULL DEFAULT 0,
  `user_arrival_notification` tinyint NULL DEFAULT 0,
  `is_tommy_online` tinyint NOT NULL DEFAULT 0,
  `group_invitation_id` int NULL DEFAULT NULL,
  `ref_booking_id` int NULL DEFAULT NULL,
  `pos_barcode` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `pos_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `product_id` int NULL DEFAULT NULL,
  `is_blocked` tinyint(1) NOT NULL DEFAULT 0,
  `booking_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal_booking',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 380 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
