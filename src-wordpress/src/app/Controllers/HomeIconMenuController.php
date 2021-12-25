<?php

namespace App\Controllers;

use WP_REST_Server;
use WP_REST_Response;
use WP_Query;
use WP_Error;

// HomeIconMenuController
class HomeIconMenuController extends \WP_REST_Controller
{
  public function register_routes()
  {
    $version = '1';
    $namespace = 'oms/v' . $version;
    $base = 'menus';
    register_rest_route($namespace, '/' . $base, [
      [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => array($this, 'get_items'),
        'permission_callback' => array($this, 'get_items_permissions_check'),
        'args'                => [],
      ]
    ]);

    register_rest_route($namespace, '/' . $base . '/schema', [
      'methods'  => WP_REST_Server::READABLE,
      'callback' => array($this, 'get_public_item_schema'),
      'permission_callback' => '__return_true'
    ]);
  }

  /**
   * Get a collection of items
   *
   * @param \WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items($request)
  {
    $data = [
      [
        "icon" => 'JD',
        "name" => 'Orders',
        "to" => "/orders"
      ],
      [
        "icon" => 'JD',
        "name" => 'About',
        "to" => "/about"
      ],
      [
        "icon" => 'JD',
        "name" => 'About',
        "to" => "/about"
      ],
      [
        "icon" => 'JD',
        "name" => 'Products',
        "to" => "/products"
      ]
    ];

    return new WP_REST_Response($data, 200);
  }

  public function get_items_permissions_check($request)
  {
    return true; //<--use to make readable by all
    // return current_user_can('read');
  }
}
