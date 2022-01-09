<?php

/**
 * 当前在使用插件 “User Role Editor”, 没有使用下面的代码
 * 以后可能会使用
 */
add_action('plugins_loaded', function(){
	$option = 'cake_roles_are_set';
	$rolesSet = get_option($option);
	if(!$rolesSet){
		add_role( 'store-manager', '店长', [
				'read' => true,
				'edit_posts' => true,
				'delete_posts' => true,
				'delete_others_posts' => true,
				'manage_categories' => true,
				'upload_files' => true
			]);

		//	裱花管理员 framer-manager
		add_role( 'framer-manager', '裱花管理员', [
			'read' => true,
			'edit_posts' => true,
			'delete_posts' => true,
			'delete_others_posts' => true,
			'manage_categories' => true,
			'upload_files' => true
		]);

		// 裱画师 framer

		// 门店员工 employee

		// 客服 customer-service

		update_option($option,true);
	}
});