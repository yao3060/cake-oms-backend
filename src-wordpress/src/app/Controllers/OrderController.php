<?php

namespace App\Controllers;

use App\Services\OrderLogService;
use App\Services\OrderService;
use WP_REST_Request;
use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

class OrderController extends \WP_REST_Controller
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
		$base      = 'orders';
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
		register_rest_route($namespace, '/' . $base . '/(?P<id>[\d]+)', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [$this, 'get_item'],
				'permission_callback' => [$this, 'get_item_permissions_check'],
				'args'                => ['context' => ['default' => 'view']],
			],
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array($this, 'update_item'),
				'permission_callback' => array($this, 'update_item_permissions_check'),
				'args'                => $this->get_endpoint_args_for_item_schema(false),
			],
		]);

		register_rest_route($namespace, '/' . $base . '/(?P<id>[\d]+)/items/(?P<item_id>[\d]+)', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [$this, 'get_order_item'],
				'permission_callback' => [$this, 'get_item_permissions_check'],
				'args'                => ['context' => ['default' => 'view']],
			]
		]);
		register_rest_route($namespace, '/' . $base . '/(?P<id>[\d]+)/items/(?P<item_id>[\d]+)/gallery/(?P<image_id>[\d]+)', [
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [$this, 'delete_order_item_gallery'],
				'permission_callback' => [$this, 'get_item_permissions_check'],
				'args'                => ['context' => ['default' => 'view']],
			]
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
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items($request)
	{
		$user = wp_get_current_user();

		//get parameters from request
		$perPage = $request->get_param('per_page') ? (int) $request->get_param('per_page') : 10;
		$orderBy = $request->get_param('orderby') ?? 'id';
		$order   = $request->get_param('order') ?? 'desc';

		$query = $this->db->table('orders');

		// if it's not admin user,  filter by store id
		if (!current_user_can('administrator')) {
			$groups = wp_get_terms_for_user($user, 'user-group');
			if (!$groups) {
				return new WP_Error(
					'user_not_bind_store',
					'用户没有绑定店铺',
					['status' => 403]
				);
			}
			$storeIds = collect($groups)->pluck('term_id');
			$query->whereIn('store_id', $storeIds);
		}

		// by status and status is not all
		if ($request->get_param('status') && $request->get_param('status') !== 'all') {
			$query->where('order_status', $request->get_param('status'));
		}
		// search by keyword
		if ($keyword = $request->get_param('keyword')) {
			$query->where('order_number', 'like', '%' . $keyword . '%')
				->orWhere('billing_phone', 'like', '%' . $keyword . '%')
				->orWhere('shipping_phone', 'like', '%' . $keyword . '%');
		} else {
			// filter
			if ($orderNumber = $request->get_param('order_number')) {
				$query->where('order_number', $orderNumber);
			}
			if ($billingPhone = $request->get_param('billing_phone')) {
				$query->where("billing_phone", 'like', "%" . $billingPhone . "%");
			}
			if ($pickupNumber = $request->get_param('pickup_number')) {
				$query->where('pickup_number', $pickupNumber);
			}
		}

		/**@var \Illuminate\Pagination\LengthAwarePaginator $orders */
		$orders = $query->orderBy($orderBy, $order)
			->paginate(
				$perPage,
				['*'],
				'page',
				$request->get_param('page') ?? 1
			);

		if (!$orders->count()) {
			return new WP_Error(
				'no_orders_found',
				__('No Orders', 'cake'),
				['status' => 404]
			);
		}

		$orderIds = $orders->map(function ($order) {
			return (int) $order->id;
		});
		$items    = $this->db->table('order_items')->whereIn('order_id', $orderIds)->get();
		//format items
		foreach ($items as $key => $item) {
			$item = OrderService::formatOrderItem($item);
		}

		// append order items
		foreach ($orders as $key => $order) {
			// format order value
			$order = OrderService::formatOrder($order);

			// append order items
			foreach ($items as $key2 => $item) {
				if ($order->id === $item->order_id) {
					$order->items[] = $item;
				}
			}
		}

		return new WP_REST_Response($orders, 200, [
			'X-WP-Total' => $orders->total()
		]);
	}


	/**
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item($request): WP_Error|WP_REST_Response
	{
		$id    = $request->get_param('id');
		$order = $this->db->table('orders')->where('id', $id)->first();

		if ($order) {
			$order = OrderService::formatOrder($order);
			$items = $this->db->table('order_items')->where('order_id', $id)->get();
			if ($items->count()) {
				foreach ($items as $key => $item) {
					$item = OrderService::formatOrderItem($item);
					$item->images = (new OrderService)->getItemImages($item->id);
				}
			}
			$order->items = $items->toArray();

			$order->creator = OrderService::getCreator((int) $order->creator);

			return new WP_REST_Response($order, 200);
		} else {
			return new WP_Error(
				'order_not_found',
				__('Order Not Found', 'cake'),
				['status' => 404]
			);
		}
	}

	/**
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_order_item($request)
	{
		$itemId = $request->get_param('item_id');
		$item   = $this->db->table('order_items')->where('id', $itemId)->first();
		if ($item) {
			$item = OrderService::formatOrderItem($item);

			$item->gallery = $this->db->table('order_item_gallery')
				->select(['id', 'media_id', 'media_url'])
				->where('item_id', $item->id)->get();

			if ($item->gallery->count()) {
				foreach ($item->gallery as $key => $image) {
					$image->id       = (int) $image->id;
					$image->media_id = (int) $image->media_id;
				}
			}

			return new WP_REST_Response($item, 200);
		} else {
			return new WP_Error(
				'order_item_not_found',
				__('Order Item Not Found', 'cake'),
				['status' => 404]
			);
		}
	}

	/**
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 */
	public function delete_order_item_gallery($request)
	{
		$imageId = $request->get_param('image_id');

		(new OrderLogService)->add(
			(int)$request->get_param('id'),
			'delete',
			'delete order item image',
			$request->get_params()
		);

		return $this->db->table('order_item_gallery')->delete($imageId);
	}

	/**
	 * Create one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item($request)
	{
		$data = $this->prepare_item_for_database($request);

		return new WP_REST_Response($data, 200);

		return new WP_Error('cant-create', __('message', 'text-domain'), array('status' => 500));
	}

	/**
	 * Update one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item($request)
	{
		$id   = (int) $request['id'];
		$data = $this->prepare_item_for_database($request);
		if (is_wp_error($data)) {
			return $data;
		}

		try {
			$this->db->table('orders')
				->where('id', $id)
				->update($data);

			// add order log
			(new OrderLogService)->add($id, 'update', 'update order', $data);

			$updatedOrder = $this->db->table('orders')->where('id', $id)->first();

			return new WP_REST_Response($updatedOrder, 200);
		} catch (\Throwable $th) {
			error_log($th->getMessage());
			return new WP_Error('cant-update', $th->getMessage(), ['status' => 500]);
		}
	}

	/**
	 * Delete one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
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
	 *
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check($request)
	{
		if (getenv('ENVIRONMENT') === 'local') return true;

		return current_user_can('read');
	}

	/**
	 * Check if a given request has access to get a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function get_item_permissions_check($request)
	{
		if (getenv('ENVIRONMENT') === 'local') return true;

		return $this->get_items_permissions_check($request);
	}

	/**
	 * Check if a given request has access to create items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function create_item_permissions_check($request)
	{
		return true; //current_user_can('edit_something');
	}

	/**
	 * Check if a given request has access to update a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
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
	 *
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
	 *
	 * @return WP_Error|array $prepared
	 */
	protected function prepare_item_for_database($request)
	{
		$prepared = [];

		// ID.
		if (isset($request['id'])) {
			$existing = $this->db->table('orders')->where('id', $request['id'])->first();
			if (is_wp_error($existing)) {
				return $existing;
			}

			$prepared['creator'] = $existing->creator;
		}

		if (isset($request['framer'])) {
			$prepared['framer'] = (int)$request['framer'];
		}

		// order status
		if (is_string($request['status'])) {
			$prepared['order_status'] = $request['status'];
		}

		$shippingKeys = ['shipping_name', 'shipping_phone', 'shipping_address'];
		foreach ($shippingKeys as $key) {
			if (empty($request[$key])) {
				continue;
			}
			$prepared[$key] = $request[$key];
		}

		// order note
		$prepared['note'] = $request['note'];

		// Post date.
		$prepared['updated_at'] = date('Y-m-d H:i:s');

		// creator.
		if ($prepared['creator'] < 1) {
			$prepared['creator'] = wp_get_current_user()->ID;
		}

		return $prepared;
	}

	/**
	 * Prepare the item for the REST response
	 *
	 * @param mixed $item WordPress representation of the item.
	 * @param WP_REST_Request $request Request object.
	 *
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
