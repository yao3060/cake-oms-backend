<?php

require_once ABSPATH . 'wp-admin/includes/upgrade.php';
global $wpdb;
$tableName = $wpdb->prefix . 'order_logs';

$ddl = <<<EOF
CREATE TABLE $tableName (
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
if(maybe_create_table( $tableName, $ddl)){
	echo $tableName . ' Created. ' . PHP_EOL;
}