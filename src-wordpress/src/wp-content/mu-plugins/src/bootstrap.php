<?php

require_once __DIR__ . '/hooks/language.php';
require_once  __DIR__.'/acf/store.php';
require_once __DIR__.'/hooks/jwt.php';
require_once __DIR__.'/hooks/user.php';
require_once __DIR__ . '/hooks/attachment.php';
require_once __DIR__.'/hooks/taxonomy.php';
require_once __DIR__ . '/hooks/apis.php';

if (is_admin()) {
	new \App\Admin\OrdersPage;
}