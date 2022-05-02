<?php

namespace App\Services;

use WP_User;

class UserService
{
    public static function getCashier(int $id)
    {
        $user = get_user_by('id', $id);
        if ($user) {
            return sprintf('%s (%d)', $user->display_name, $user->user_login);
        }
        return '';
    }

    public static function isManager($roles = []): bool
    {
        if (
            in_array('framer-manager', $roles) ||
            in_array('customer-service-manager', $roles) ||
            in_array('store-manager', $roles)
        ) {
            return true;
        }
        return false;
    }

    public static function getSubordinates(WP_User $user): array
    {
        if (!self::isManager($user->roles)) {
            return [0];
        }

        $groups = wp_get_terms_for_user($user, 'user-group');
        // return $groups;
        $output = [];
        if ($groups && is_array($groups)) {
            foreach ($groups as $key => $group) {
                $users = wp_get_users_of_group(['term' => $group->slug]);
                $ids = collect($users)->pluck('ID');
                $output = $output + $ids->toArray();
            }
        }
        return $output;
    }
}
