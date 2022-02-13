<?php

/**
 * Removes some menus by page.
 */
add_action('admin_menu', function () {
	//	remove_menu_page( 'index.php' );                  //Dashboard
	//	remove_menu_page( 'jetpack' );                    //Jetpack*
	//	remove_menu_page( 'edit.php' );                   //Posts
	//	remove_menu_page( 'upload.php' );                 //Media

	remove_menu_page('edit-comments.php');          //Comments
	// remove_menu_page( 'themes.php' );                 //Appearance
	// remove_menu_page( 'plugins.php' );                //Plugins
	//	remove_menu_page( 'users.php' );                  //Users
	//	remove_menu_page( 'tools.php' );                  //Tools
	//	remove_menu_page( 'options-general.php' );        //Settings

	//WordPress更新
	remove_submenu_page('index.php', 'update-core.php');

	//wsal-emailnotifications
	remove_submenu_page('wsal-auditlog', 'wsal-loginusers');
	remove_submenu_page('wsal-auditlog', 'wsal-emailnotifications');
	remove_submenu_page('wsal-auditlog', 'wsal-reports');
	remove_submenu_page('wsal-auditlog', 'wsal-externaldb');
	remove_submenu_page('wsal-auditlog', 'wsal-search');
	remove_submenu_page('wsal-auditlog', 'wsal-help');
}, 999);
