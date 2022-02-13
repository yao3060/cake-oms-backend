<?php

namespace App\Controllers;

use App\Services\CakePrintService;
use App\Services\OrderService;
use WP_REST_Request;
use WP_REST_Server;
use WP_REST_Response;
use WP_Error;
use WP_REST_Controller;

class PrintController extends WP_REST_Controller
{

	public function register_routes()
	{
		$version   = '1';
		$namespace = 'oms/v' . $version;
		$base      = 'print';

		// 打印订单
		register_rest_route($namespace, '/' . $base . '/orders/(?P<id>[\d]+)', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [$this, 'printItem'],
				'permission_callback' => [$this, 'get_item_permissions_check'],
				'args'                => ['context' => ['default' => 'view']],
			],
		]);

		register_rest_route($namespace, '/' . $base . '/schema', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [$this, 'get_public_item_schema'],
			'permission_callback' => '__return_true'
		]);
	}

	/**
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function printItem(WP_REST_Request $request): WP_Error|WP_REST_Response
	{
		$id = (int) $request->get_param('id');

		$orderService = new OrderService;
		$order = $orderService->getOrderById($id);

		if (!$order) {
			return new WP_Error(
				'order_not_found',
				__('Order Not Found', 'cake'),
				['status' => 404]
			);
		}

		try {
			$order->items = $orderService->getOrderItems($id);
			$response = (new CakePrintService($order))
				->printComplexReceiptWithoutBroadcast($order);
			return new WP_REST_Response($response, 200);
		} catch (\Throwable $th) {
			return new WP_Error(
				'failed_to_print',
				$th->getMessage(),
				['status' => 500]
			);
		}
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
		return true; // current_user_can( 'read' );
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
}
