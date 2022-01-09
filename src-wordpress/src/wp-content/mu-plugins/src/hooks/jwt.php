<?php

add_filter('jwt_auth_token_before_dispatch', function ($data, $user) {
	return array_merge($data, ['id' => $user->ID, 'roles' => $user->roles]);
}, 10, 2);