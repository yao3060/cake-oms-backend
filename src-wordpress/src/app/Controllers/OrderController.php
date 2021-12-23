<?php

namespace App\Controllers;

use WP_REST_Server;
use WP_REST_Response;
use WP_Query;
use WP_Error;

class OrderController extends \WP_REST_Controller
{
  private $db = null;
  private $dbPrefix = '';

  public function __construct()
  {
    $this->db = \WeDevs\ORM\Eloquent\Database::instance();
    $this->dbPrefix = $this->db->db->prefix;
  }

  public function register_routes()
  {
    $version = '1';
    $namespace = 'oms/v' . $version;
    $base = 'orders';
    register_rest_route($namespace, '/' . $base, array(
      array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => array($this, 'get_items'),
        'permission_callback' => array($this, 'get_items_permissions_check'),
        'args'                => array(),
      ),
      array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => array($this, 'create_item'),
        'permission_callback' => array($this, 'create_item_permissions_check'),
        'args'                => $this->get_endpoint_args_for_item_schema(true),
      ),
    ));
    register_rest_route($namespace, '/' . $base . '/(?P<id>[\d]+)', array(
      array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => array($this, 'get_item'),
        'permission_callback' => array($this, 'get_item_permissions_check'),
        'args'                => array(
          'context' => array(
            'default' => 'view',
          ),
        ),
      ),
      array(
        'methods'             => WP_REST_Server::EDITABLE,
        'callback'            => array($this, 'update_item'),
        'permission_callback' => array($this, 'update_item_permissions_check'),
        'args'                => $this->get_endpoint_args_for_item_schema(false),
      ),
      array(
        'methods'             => WP_REST_Server::DELETABLE,
        'callback'            => array($this, 'delete_item'),
        'permission_callback' => array($this, 'delete_item_permissions_check'),
        'args'                => array(
          'force' => array(
            'default' => false,
          ),
        ),
      ),
    ));
    register_rest_route($namespace, '/' . $base . '/schema', array(
      'methods'  => WP_REST_Server::READABLE,
      'callback' => array($this, 'get_public_item_schema'),
    ));
  }

  /**
   * Get a collection of items
   *
   * @param \WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_items($request)
  {
    //get parameters from request
    $perPage = $request->get_param('per_page') ? (int)$request->get_param('per_page') : 10;
    $orderBy = $request->get_param('orderby') ?? 'id';
    $order = $request->get_param('order') ?? 'desc';

    $query =  $this->db->table('orders');
    // filter
    if ($orderNumber = $request->get_param('order_number')) {
      $query->where('order_number', $orderNumber);
    }
    if ($billingPhone = $request->get_param('billing_phone')) {
      $query->where("billing_phone", 'like',  "%" . $billingPhone . "%");
    }
    if ($pickupNumber = $request->get_param('pickup_number')) {
      $query->where('pickup_number', $pickupNumber);
    }

    $query->orderBy($orderBy, $order);
    $orders = $query->paginate($perPage, ['*'], 'page', $request->get_param('page') ?? 1);

    // append order items
    foreach ($orders as $key => $order) {
      $order->items = $this->db->table('order_items')->where('order_id', $order->id)->get();
    }

    return new WP_REST_Response($orders, 200, [
      'X-WP-Total' => $orders->total()
    ]);
  }

  /**
   * Get one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function get_item($request)
  {

    $id = $request->get_param('id');
    $order = $this->db->table('orders')->where('id', $id)->first();

    if ($order) {
      $items = $this->db->table('order_items')->where('order_id', $id)->get();
      $order->items = $items->toArray();
    }

    if ($order) {
      return new WP_REST_Response($order, 200);
    } else {
      return new WP_Error('code', __('message', 'text-domain'));
    }
  }

  /**
   * Create one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function create_item($request)
  {
    $item = $this->prepare_item_for_database($request);

    $data = $item;
    if (is_array($data)) {
      return new WP_REST_Response($data, 200);
    }


    return new WP_Error('cant-create', __('message', 'text-domain'), array('status' => 500));
  }

  /**
   * Update one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function update_item($request)
  {
    $item = $this->prepare_item_for_database($request);

    $data = $item;
    if (is_array($data)) {
      return new WP_REST_Response($data, 200);
    }


    return new WP_Error('cant-update', __('message', 'text-domain'), array('status' => 500));
  }

  /**
   * Delete one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Response
   */
  public function delete_item($request)
  {
    $item = $this->prepare_item_for_database($request);

    $deleted = true;
    if ($deleted) {
      return new WP_REST_Response(true, 200);
    }


    return new WP_Error('cant-delete', __('message', 'text-domain'), array('status' => 500));
  }

  /**
   * Check if a given request has access to get items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_items_permissions_check($request)
  {
    return true; //<--use to make readable by all
    // return current_user_can('read');
  }

  /**
   * Check if a given request has access to get a specific item
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function get_item_permissions_check($request)
  {
    return $this->get_items_permissions_check($request);
  }

  /**
   * Check if a given request has access to create items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function create_item_permissions_check($request)
  {
    return current_user_can('edit_something');
  }

  /**
   * Check if a given request has access to update a specific item
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function update_item_permissions_check($request)
  {
    return $this->create_item_permissions_check($request);
  }

  /**
   * Check if a given request has access to delete a specific item
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function delete_item_permissions_check($request)
  {
    return $this->create_item_permissions_check($request);
  }

  /**
   * Prepare the item for create or update operation
   *
   * @param WP_REST_Request $request Request object
   * @return WP_Error|object $prepared_item
   */
  protected function prepare_item_for_database($request)
  {
    return array();
  }

  /**
   * Prepare the item for the REST response
   *
   * @param mixed $item WordPress representation of the item.
   * @param WP_REST_Request $request Request object.
   * @return mixed
   */
  public function prepare_item_for_response($item, $request)
  {
    return array();
  }

  /**
   * Get the query params for collections
   *
   * @return array
   */
  public function get_collection_params()
  {
    return array(
      'page'     => array(
        'description'       => 'Current page of the collection.',
        'type'              => 'integer',
        'default'           => 1,
        'sanitize_callback' => 'absint',
      ),
      'per_page' => array(
        'description'       => 'Maximum number of items to be returned in result set.',
        'type'              => 'integer',
        'default'           => 10,
        'sanitize_callback' => 'absint',
      ),
      'search'   => array(
        'description'       => 'Limit results to those matching a string.',
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
      ),
    );
  }
}
