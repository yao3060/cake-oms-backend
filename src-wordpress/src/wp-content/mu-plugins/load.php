<?php

/*
  Plugin Name: Cake MU-Plugins
  Plugin URI: https://www.yaoin.net
  Description: Boilerplate MU-plugin for custom actions and filters to run for a site instead of setting in WP-config.php
  Version: 0.1
  Author: YAO
  Author URI: https://www.yaoin.net
*/

use App\Controllers\HomeIconMenuController;
use App\Controllers\OrderController;
use App\Services\OrderProductMediaService;

add_filter('jwt_auth_token_before_dispatch', function ($data, $user) {
  return array_merge($data, ['id' => $user->ID, 'roles' => $user->roles]);
}, 10, 2);

/**
 * Filters an attachment returned from the REST API.
 *
 * Allows modification of the attachment right before it is returned.
 *
 * @since 4.7.0
 *
 * @param WP_REST_Response $response The response object.
 * @param WP_Post          $post     The original attachment post.
 * @param WP_REST_Request  $request  Request used to generate the response.
 */
add_filter('rest_prepare_attachment', function ($response, $post, $request) {

  $data = $response->get_data();
  if ($productId = $request->get_param('product_id') && isset($data['source_url'])) {

    $data['product_id'] = (int)$productId;

    $action = $request->get_param('action');
    if ($action === 'add_featured_image') {
      (new OrderProductMediaService)->addFeaturedImage(
        $data['product_id'],
        (int)$data['id'],
        $data['source_url']
      );
    }
    if ($action === 'add_gallery_image') {
      (new OrderProductMediaService)->addGalleryImage(
        $data['product_id'],
        (int)$data['id'],
        $data['source_url']
      );
    }

    $response->set_data($data);
  }

  return $response;
}, 10, 3);

add_action('plugins_loaded', function () {
  load_muplugin_textdomain('cake', 'languages');
});

if (is_admin()) {
  new \App\Admin\OrdersPage;
}


add_action('rest_api_init', function () {
  (new OrderController)->register_routes();
  (new HomeIconMenuController)->register_routes();
});
