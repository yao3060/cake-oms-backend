<?php

require_once ABSPATH . 'wp-admin/includes/upgrade.php';
global $wpdb;
$tableName = $wpdb->prefix . 'orders';

$ddl = <<<EOF
CREATE TABLE $tableName (
  `ID` bigint unsigned NOT NULL AUTO_INCREMENT COMMENT '自增 ID ',
  `order_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '订单编号',
  `store_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '门店名',
  `store_id` mediumint unsigned NOT NULL DEFAULT '0',
  `creator` bigint unsigned NOT NULL DEFAULT '0' COMMENT 'order creator',
  `framer` bigint unsigned DEFAULT NULL,
  `order_status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT '订单状态',
  `order_type` varchar(20) NOT NULL COMMENT '订单类型:预约，小程序外卖',
  `payment_method` varchar(20) NOT NULL COMMENT '支付方式:现金，支付宝，微信，储值卡',
  `pickup_method` varchar(20) NOT NULL COMMENT '提货方式: 自提，配送',
  `deposit` decimal(10,0) DEFAULT NULL COMMENT '预约金额',
  `balance` decimal(10,0) DEFAULT NULL COMMENT '待收款',
  `billing_name` varchar(40) DEFAULT NULL COMMENT '预定人姓名',
  `billing_phone` varchar(40) DEFAULT NULL COMMENT '预定人电话',
  `billing_store` varchar(40) DEFAULT NULL COMMENT '预定门店',
  `pickup_store` varchar(40) DEFAULT NULL COMMENT '取货门店',
  `created_at` datetime DEFAULT NULL COMMENT '下单时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `pickup_time` varchar(20) DEFAULT NULL COMMENT '取货时间',
  `sales` varchar(40) DEFAULT NULL COMMENT '导购员编号',
  `pickup_number` varchar(40) DEFAULT NULL COMMENT '派单编号',
  `shipping_name` varchar(40) DEFAULT NULL COMMENT '收货人',
  `shipping_phone` varchar(40) DEFAULT NULL COMMENT '收货人电话 ',
  `shipping_address` varchar(40) DEFAULT NULL COMMENT '收货人地址 ',
  `membership_number` varchar(60) DEFAULT NULL COMMENT '会员卡号',
  `member_name` varchar(60) DEFAULT NULL COMMENT '会员姓名  ',
  `member_balance` decimal(10,0) DEFAULT NULL COMMENT '会员余额    ',
  `items_count` tinyint DEFAULT NULL COMMENT '商品数量',
  `total` decimal(10,0) DEFAULT NULL COMMENT '订单金额 ',
  `note` text COMMENT '备注',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `store_name` (`store_name`),
  KEY `store_id` (`store_id`),
  KEY `order_status` (`order_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
EOF;
if (maybe_create_table($tableName, $ddl)) {
    echo $tableName . ' Created.' . PHP_EOL;
}

$wp_order_items = $wpdb->prefix . 'order_items';
$wp_order_items_ddl = <<<EOF
CREATE TABLE $wp_order_items (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `media_id` bigint DEFAULT NULL COMMENT '媒体ID',
  `media_url` varchar(255) DEFAULT NULL COMMENT '媒体URL',
  `product_name` varchar(100) DEFAULT NULL COMMENT '商品名称',
  `price` decimal(10,0) DEFAULT NULL COMMENT '单价',
  `quantity` tinyint DEFAULT NULL COMMENT '数量',
  `total` decimal(10,0) DEFAULT NULL COMMENT '小计',
  `note` text COMMENT '备注 ',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `media_id` (`media_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
EOF;
if (maybe_create_table($wp_order_items, $wp_order_items_ddl)) {
    echo $wp_order_items . ' Created.' . PHP_EOL;
}


$wp_order_item_gallery = $wpdb->prefix . 'order_item_gallery';
$wp_order_item_gallery_ddl = <<<EOF
CREATE TABLE $wp_order_item_gallery (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `item_id` bigint unsigned NOT NULL,
  `media_id` bigint unsigned NOT NULL,
  `media_url` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
EOF;
if (maybe_create_table($wp_order_item_gallery, $wp_order_item_gallery_ddl)) {
    echo $wp_order_item_gallery . ' Created.' . PHP_EOL;
}


// ALTER TABLE `wp_orders` CHANGE `pickup_time` `pickup_time` VARCHAR(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '取货时间';
