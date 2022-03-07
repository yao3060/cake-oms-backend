<?php

require_once ABSPATH . 'wp-admin/includes/upgrade.php';
global $wpdb;
$tableName = $wpdb->prefix . 'order_logs';

$ddl = <<<EOF
CREATE TABLE $tableName (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `order_id` bigint unsigned NOT NULL,
    `user_id` bigint unsigned NOT NULL,
    `username` varchar(60) NOT NULL,
    `user_roles` json DEFAULT NULL,
    `ip` varchar(20) DEFAULT NULL,
    `event` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
    `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
    `data` json DEFAULT NULL,
    `created_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `order_id` (`order_id`),
    KEY `user_id` (`user_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
EOF;
if(maybe_create_table( $tableName, $ddl)){
	echo $tableName . ' Created. ' . PHP_EOL;
}
