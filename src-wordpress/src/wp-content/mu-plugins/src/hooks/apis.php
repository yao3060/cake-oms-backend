<?php

add_action('rest_api_init', function () {
	(new \App\Controllers\OrderController)->register_routes();
	(new \App\Controllers\PrintController())->register_routes();
	(new \App\Controllers\OrderItemController)->register_routes();
	(new \App\Controllers\HomeIconMenuController)->register_routes();
    (new \App\Controllers\MemberController)->register_routes();
});
