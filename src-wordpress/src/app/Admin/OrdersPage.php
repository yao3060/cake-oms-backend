<?php

namespace App\Admin;

class OrdersPage
{

  public function __construct()
  {
    add_action('admin_menu', array($this, 'adminMenu'));
  }

  public function adminMenu()
  {
    $hook = add_menu_page(
      __('Orders', 'cake'),
      __('Orders', 'cake'),
      'manage_options',
      'cake-oms-orders',
      array($this, 'menuPage'),
      'dashicons-cart',
      4
    );
    add_action("admin_print_scripts-$hook", array($this, 'scripts'));
    add_action("admin_print_styles-$hook", array($this, 'styles'));
  }

  public function menuPage()
  {
    require_once __DIR__ . '/OrderList.php';
  }

  # Printing directly, could be wp_enqueue_script
  public function scripts()
  {
    wp_enqueue_script('vue', 'https://unpkg.com/vue@2.6.14/dist/vue.js');
    wp_enqueue_script('element-ui', 'https://unpkg.com/element-ui@2.15.6/lib/index.js');
    wp_localize_script('vue', 'wpApiSettings', array(
      'root' => esc_url_raw(rest_url()),
      'nonce' => wp_create_nonce('wp_rest')
    ));
  }

  # Enqueing from a CSS file on plugin directory
  public function styles()
  {
    wp_enqueue_style('element-ui', 'https://unpkg.com/element-ui/lib/theme-chalk/index.css');
  }
}
