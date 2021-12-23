<?php
add_filter( 'upgrader_pre_install', 'wp_db_backup_upgrader_pre_install', 10, 2 );
function wp_db_backup_upgrader_pre_install( $response, $hook_extra ){
    $wp_db_backup_enable_auto_upgrade = get_option('wp_db_backup_enable_auto_upgrade');
    if ($wp_db_backup_enable_auto_upgrade == 1) {
        $beforeUpdateBackupObj = new WPDB_Admin();
        $beforeUpdateBackupObj->wp_db_backup_event_process();
    }
	return $response;
}