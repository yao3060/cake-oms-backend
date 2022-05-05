<?php


namespace App\Controllers;

use WP_REST_Request;
use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

class MemberController extends \WP_REST_Controller
{
    private $db = null;
    private $dbPrefix = '';

    public function __construct()
    {
        $this->db       = \WeDevs\ORM\Eloquent\Database::instance();
        $this->dbPrefix = $this->db->db->prefix;
    }

    public function register_routes()
    {
        $version   = '1';
        $namespace = 'oms/v' . $version;
        $base      = 'members';

        register_rest_route($namespace, '/' . $base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_items'],
                'permission_callback' => [
                    $this,
                    'get_items_permissions_check',
                ],
                'args'                => [],
            ],
        ]);

        register_rest_route($namespace, '/' . $base . '/schema', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [$this, 'get_public_item_schema'],
            'permission_callback' => '__return_true'
        ]);
    }

    /**
     * Get a collection of items
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_Error|WP_REST_Response
     */
    public function get_items($request)
    {
        if (!function_exists('wp_get_users_of_group')) {
            return new WP_Error(
                'need_plugins',
                'Need Enable `WP User Groups`',
                array('status' => 500)
            );
        }

        $userGroup = $request->get_param('user-group');
        if ($userGroup) {
            $users = wp_get_users_of_group([
                'taxonomy' => 'user-group',
                'term'     => $userGroup,
                'term_by'  => is_numeric($userGroup) ? 'id' : 'slug'
            ]);
        } else {

            $currentUser = wp_get_current_user();

            $userStores = wp_get_terms_for_user($currentUser, 'user-group');

            $storeIds = collect($userStores)->pluck('term_id');

            $users = wp_get_users_of_group([
                'taxonomy' => 'user-group',
                'term'     => $storeIds[0],
                'term_by'  => 'id'
            ]);
        }


        $collection  = array_map(function ($user) {
            return [
                'id' => $user->ID,
                'display_name' => $user->display_name,
                'user_email' => $user->user_email,
                'mobile_phone' => get_user_meta($user->ID, 'mobile_phone', true),
                'wechat' => get_user_meta($user->ID, 'wechat', true),
                'roles' => $user->roles
            ];
        }, $users);

        // $sorted =  collect($collection)->sortBy('user_email');

        return new WP_REST_Response($collection, 200);
    }

    public function get_items_permissions_check($request)
    {
        return current_user_can('read');
    }
}
