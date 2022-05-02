<?php

/**
 * Filters user data returned from the REST API.
 *
 * @since 4.7.0
 *
 * @param WP_REST_Response $response The response object.
 * @param WP_User          $user     User object used to create response.
 * @param WP_REST_Request  $request  Request object.
 */

use App\Services\UserService;

add_filter('rest_prepare_user', function ($response, $user, $request) {
    $groups = wp_get_terms_for_user($user, 'user-group');
    if ($groups && is_array($groups)) {
        foreach ($groups as $key => $group) {
            $response->data['stores'][] = [
                'id' => $group->term_id,
                'name' => $group->name,
                'slug' => $group->slug
            ];
        }
        $response->data['subordinates'] = UserService::getSubordinates($user);
    }

    return $response;
}, 10, 3);

add_filter('user_contactmethods', function ($methods, $user) {
    return [
        'mobile_phone'    => __('Mobile Phone', 'cake'),
        'wechat'    => __('WeChat', 'cake'),
    ];
}, 10, 2);
