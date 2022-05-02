<?php

use App\Services\UserService;

add_filter('jwt_auth_token_before_dispatch', function ($data, WP_User $user) {
    return array_merge($data, [
        'id' => $user->ID,
        'roles' => $user->roles,
        'subordinates' => UserService::getSubordinates($user)
    ]);
}, 10, 2);
