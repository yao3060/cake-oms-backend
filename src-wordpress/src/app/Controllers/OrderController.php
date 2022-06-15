<?php

namespace App\Controllers;

use App\Filters\BillingPhoneFilter;
use App\Filters\FramerFilter;
use App\Filters\KeywordFilter;
use App\Filters\OrderNumberFilter;
use App\Filters\PickupNumberFilter;
use App\Filters\StatusFilter;
use App\Filters\StoreUserFilter;
use App\Permissions\OrderStatusPermission;
use App\Services\OrderLogService;
use App\Services\OrderService;
use RuntimeException;
use WP_REST_Request;
use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

class OrderController extends \WP_REST_Controller
{

    private $db = null;
    private OrderService $orderService;

    public function __construct()
    {
        $this->db       = \WeDevs\ORM\Eloquent\Database::instance();
        $this->dbPrefix = $this->db->db->prefix;
        $this->orderService = new OrderService;
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
        $query = $this->db->table('orders');
        $pre = $this->orderService->preGetOrders($request->get_params());
        if (is_wp_error($pre)) {
            return $pre;
        }

        // if it's store user,  filter by store id
        $query = StoreUserFilter::handle($query, $request);

        // if it's framers
        $query = FramerFilter::handle($query, $request);

        // by status and status is not all
        $query = StatusFilter::handle($query, $request);

        // search by keyword
        if ($request->get_param('keyword')) {
            $query = KeywordFilter::handle($query, $request);
        } else {
            // filter
            $query = OrderNumberFilter::handle($query, $request);

            $query = BillingPhoneFilter::handle($query, $request);

            $query = PickupNumberFilter::handle($query, $request);
        }

        if($request->get_param('pickup_method')){//pickup_method
            $query = $query->where('pickup_method', $request->get_param('pickup_method'));
        }

        write_log([$query->toSql(), $query->getBindings()]);


        /**@var \Illuminate\Pagination\LengthAwarePaginator $orders */

        $orders = $query->orderBy($request->get_param('orderby') ?? 'id', $request->get_param('order') ?? 'desc')
            ->paginate(
                $request->get_param('per_page') ? (int) $request->get_param('per_page') : 10,
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
            return (int) $order->ID;
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
            $order = OrderService::mask($order);

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
        $order = $this->orderService->getOrderById((int)$id);

        if ($order) {

            $order = new \App\Models\Order($order);

            $items = $this->db->table('order_items')->where('order_id', $id)->get();
            if ($items->count()) {
                foreach ($items as $key => $item) {
                    $item = OrderService::formatOrderItem($item);
                    $item->images = (new OrderService)->getItemImages($item->id);
                }
            }
            $order->items = $items->toArray();
            $order->creator = $order->getCreator();
            $order->framer = $order->getFramer();
            $order->deadline = $order->getDeadline();
            $order->produce_time = $order->getProduceTime();
            $order->updated_at = $order->getUpdatedAt();

            $order = OrderService::mask($order);

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

        if (is_wp_error($data)) {
            return $data;
        }

        try {
            $idUpdate = false;
            if (isset($data['id']) && $data['id']) {
                $orderId = (int)$data['id'];
                unset($data['id']);
                $this->orderService->update($orderId, $data);
                $idUpdate = true;
            } else {
                $orderId = $this->orderService->create($data);
                if (!$orderId) {
                    throw new RuntimeException('Create order failed. 原因请查看日志。');
                }
                $this->orderService->createOrderItems($orderId, $request['items']);
            }

            return new WP_REST_Response([
                'code' => $idUpdate ? 'order_updated' : 'order_created',
                'message' => $idUpdate ? 'Order Updated' : 'Order Created',
                'data' => ['order_id' => $orderId],
            ], 201);
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            return new WP_Error('cant-create-order', $th->getMessage(), array('status' => 500));
        }
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
            (new OrderLogService($id, $data))->addUpdateLog();

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
        if ($request->get_param('status')) {
            return (new OrderStatusPermission($request, wp_get_current_user()))->check();
        }
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
        return current_user_can('administrator');
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
        if ($request->get_param('status')) {
            return (new OrderStatusPermission($request, wp_get_current_user()))->check();
        }
        return true;
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

        if (!$request['id'] && !$request['order_number']) {
            return new WP_Error('`order_number` 不存在');
        }

        // ID.
        if (isset($request['id'])) {
            $existing =  $this->orderService->getOrderById((int) $request['id']);
            if (is_null($existing)) {
                return new WP_Error('Order Id 不存在');
            }
            // if there is order id, unset order number to prevent update order number
            $request['order_number'] = null;

            // 谁修改订单便是下单人
            if ((int) $existing->creator < 1) {
                $prepared['creator'] = wp_get_current_user()->ID;
            } else {
                $prepared['creator'] = $request['creator'] ?? (int)$existing->creator;
            }
        }

        if ($request['order_number']) {
            // order_number
            $existingOrder = $this->orderService->getOrderByOrderNumber($request['order_number'] ?? '');
            if ($existingOrder) {
                $prepared['id'] = $existingOrder->id;
            } else {
                // order not exist, it's create new orders.
                // it's the place to apply import rules
                $error = $this->orderService->importValidates($request);
                if (is_wp_error($error)) {
                    return $error;
                }
            }
            $prepared['order_number'] = $request['order_number'];
        }


        // store_name
        if ($request['store_name']) {
            $term = OrderService::createStore($request['store_name'], 'user-group');
            $prepared['store_name'] = $request['store_name'];
            $prepared['store_id'] = is_numeric($term) ? $term : $term['term_id'];
        }

        $fillable = [
            'order_type', 'payment_method', 'pickup_method', 'deposit', 'balance',
            'billing_name', 'billing_phone', 'billing_store', 'pickup_store',
            'shipping_name', 'shipping_phone', 'shipping_address',
            'pickup_number', 'pickup_time', 'sales',
            'membership_number', 'member_name',
            'note', 'total'
        ];
        foreach ($fillable as $key) {
            if ($request[$key]) {
                $prepared[$key] = $request[$key];
            }
        }

        // member_balance
        if (!empty($request['member_balance'])) {
            $prepared['member_balance'] = $request['member_balance'];
        }

        if ($request['framer']) {
            $prepared['framer'] = (int)$request['framer'];
        }

        // order status
        if ($request['order_status']) {
            $prepared['order_status'] = $this->getOrderStatus((int)$request['order_status']);
        }
        if ($request['status']) {
            $prepared['order_status'] = $request['status'];
        }

        // created_at
        if (!isset($request['id'])) {
            $prepared['created_at'] = $request['created_at'] ?? date('Y-m-d H:i:s');
        }

        $prepared['updated_at'] = isset($request['id']) ? date('Y-m-d H:i:s') : ($request['created_at'] ?? date('Y-m-d H:i:s'));

        if (is_array($request['items'])) {
            $prepared['items_count'] = count($request['items'] ?? []);
        }


        return $prepared;
    }

    /** 订单状态 (1:unverified, 2:verified, 3:processing, 4:completed, 5:trash) */
    public function getOrderStatus(int $status = 1)
    {
        switch ($status) {
            case 1:
                return 'unverified';
            case 2:
                return 'verified';
            case 3:
                return 'processing';
            case 4:
                return 'completed';
            case 5:
                return 'trash';
            default:
                return 'unverified';
        }
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

    public function get_item_schema()
    {
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'order',
            'type'       => 'object',
            'properties' => [
                'order_number' => [
                    'description' => '订单编号',
                    'type'        => 'string',
                    'context'     => ['view', 'edit', 'embed'],
                ],
                'store_name' => [
                    'description' => '店铺名',
                    'type'        => 'string',
                    'context'     => ['view', 'edit', 'embed'],
                ],
                'order_status' => [
                    'description' => '订单状态 (1:unverified, 2:verified, 3:processing, 4:completed, 5:trash)',
                    'type'        => 'number',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'order_type' => [
                    'description' => '订单类型:预约，小程序外卖',
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'payment_method' => [
                    'description' => '支付方式:现金，支付宝，微信，储值卡',
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'pickup_method' => [
                    'description' => '提货方式: 自提，配送',
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'deposit' => [
                    'description' => '预约金额',
                    'type'        => 'number',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'balance' => [
                    'description' => '待收款',
                    'type'        => 'number',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'billing_name' => [
                    'description' => '预定人姓名',
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'billing_phone' => [
                    'description' => '预定人电话',
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'billing_store' => [
                    'description' => '预定门店',
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'pickup_store' => [
                    'description' => '取货门店',
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'created_at' => [
                    'description' => '下单时间 Datetime',
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'pickup_time' => [
                    'description' => '取货时间',
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'sales' => [
                    'description' => '导购员编号',
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'pickup_number' => [
                    'description' => '派单编号',
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'shipping_name' => [
                    'description' => '收货人',
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'shipping_phone' => [
                    'description' => '收货人电话',
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'shipping_address' => [
                    'description' => '收货人地址',
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'membership_number' => [
                    'description' => '会员卡号',
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'member_name' => [
                    'description' => '会员姓名',
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'member_balance' => [
                    'description' => '会员余额',
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'total' => [
                    'description' => '订单金额',
                    'type'        => 'number',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'note' => [
                    'description' => '备注',
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'items' => [
                    'description' => '商品',
                    'type'        => 'array',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'items[].name' => [
                    'description' => '商品名称',
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'items[].price' => [
                    'description' => '商品单价',
                    'type'        => 'number',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'items[].quantity' => [
                    'description' => '商品数量',
                    'type'        => 'number',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'items[].total' => [
                    'description' => '商品小计',
                    'type'        => 'number',
                    'context'     => array('view', 'edit', 'embed'),
                ],
                'items[].note' => [
                    'description' => '商品备注 ',
                    'type'        => 'string',
                    'context'     => array('view', 'edit', 'embed'),
                ],
            ]
        ];

        return $schema;
    }
}
