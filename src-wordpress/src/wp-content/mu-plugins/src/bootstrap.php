<?php

require_once __DIR__ . '/hooks/language.php';
require_once __DIR__ . '/hooks/jwt.php';
require_once __DIR__ . '/hooks/user.php';
require_once __DIR__ . '/hooks/attachment.php';
require_once __DIR__ . '/hooks/taxonomy.php';
require_once __DIR__ . '/hooks/apis.php';

require_once __DIR__ . '/hooks/remove.php';

if (is_admin()) {
    new \App\Admin\OrdersPage;
}

add_action('admin_menu', [App\Admin\Settings\Order::getInstance(), 'add_admin_menu']);
add_action('admin_init', [App\Admin\Settings\Order::getInstance(), 'settings_init']);
