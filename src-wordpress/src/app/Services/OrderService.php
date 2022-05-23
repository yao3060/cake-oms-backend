<?php

namespace App\Services;

use WP_User;
use WP_Error;
use WP_REST_Request;

class OrderService
{
    private \WeDevs\ORM\Eloquent\Database|null|false $db = null;
    private string $dbPrefix = '';

    public function __construct()
    {
        $this->db       = \WeDevs\ORM\Eloquent\Database::instance();
        $this->dbPrefix = $this->db->db->prefix;
    }

    public function importValidates(WP_REST_Request $request)
    {
        // check `do_not_save_order_without_pickup_time`
        $options = get_option(\App\Admin\Settings\Order::OPTION_NAME);

        if ($options['do_not_save_order_without_pickup_time'] && !$request['pickup_time']) {
            return new WP_Error('`pickup_time` is required.');
        }

        // check `pickup_time_limit`
        if (
            $options['pickup_time_limit'] &&
            hours_difference($request['created_at'], $request['pickup_time']) < (int)$options['pickup_time_limit']
        ) {
            return new WP_Error('`pickup_time` is too early.');
        }
    }

    public function validateUpdateStatus(WP_REST_Request $request)
    {
        if ($request['status'] == 'processing' && !is_framer()) {
            return new WP_Error('Only Framers can start processing order.');
        }
    }

    public function create(array $data): int
    {
        global $wpdb;

        error_log('create order from:' . json_encode($data));

        $keys = array_keys($data);
        $format = array_map(function ($key) {
            if (in_array($key, [
                'store_id', 'creator', 'framer',
                'deposit', 'balance', 'member_balance', 'items_count', 'total'
            ])) {
                return '%d';
            }
            return '%s';
        }, $keys);

        if (!$wpdb->insert($this->dbPrefix . 'orders', $data, $format)) {
            return false;
        }

        return (int) $wpdb->insert_id;
    }

    public function update(int $id, array $data)
    {
        $this->db->table('orders')->where('ID', $id)->update($data);
        // add order log
        (new OrderLogService($id, $data))->addUpdateLog();
    }

    public function createOrderItems(int $orderId, array $items)
    {
        foreach ($items as $item) {
            $this->db->table('order_items')->insert([
                'order_id' => $orderId,
                'product_name' => $item['name'] ?? '',
                'price' => $item['price'] ?? 0,
                'quantity' => $item['quantity'] ?? 0,
                'total' => $item['total'] ?? 0,
                'note' => $item['total'] ?? ''
            ]);
        }
    }


    public function getOrderById(int $id): object|null
    {
        $order = $this->db->table('orders')
            ->where('ID', $id)
            ->first();
        if ($order) {
            $order = self::formatOrder($order);
        }
        return $order;
    }

    public function getOrderByOrderNumber(string $sn): object|null
    {
        $order = $this->db->table('orders')
            ->where('order_number', $sn)
            ->first();
        if ($order) {
            $order = self::formatOrder($order);
        }
        return $order;
    }

    public function getOrderItems(int $orderId): array
    {
        $items = $this->db->table('order_items')
            ->where('order_id', $orderId)
            ->get();
        if ($items->count()) {
            foreach ($items as $key => $item) {
                $item = self::formatOrderItem($item);
            }
            return $items->toArray();
        }
        return [];
    }

    public function preGetOrders(array $params = [])
    {
        $status = $params['status'] ?? 'all';

        // if `framer` request `unverified` orders
        if (is_framer_user() && $status == 'unverified') {
            return new WP_Error(
                'no_unverified_orders_found',
                __('No Orders', 'cake'),
                ['status' => 404]
            );
        }
    }


    /**
     * get item images
     *
     * @param integer $itemId
     * @return array
     */
    public function getItemImages(int $itemId): array
    {
        $items = $this->db->table('order_item_gallery')
            ->where('item_id', $itemId)->get();
        if ($items->count() < 1) {
            return [];
        }

        $collection = [];
        foreach ($items as $item) {
            $collection[] = [
                'id' => (int)$item->id,
                'media_id' => (int)$item->media_id,
                'media_url' => $item->media_url,
                'created_at' => $item->created_at,
            ];
        }
        return $collection;
    }

    public static function getCreator(int $creator): array
    {
        $user = new WP_User($creator);
        return [
            'id' => $user->ID,
            'username' => $user->user_login ?? '',
            'display_name' => $user->display_name ?? '',
        ];
    }

    public static function formatOrder(object $order): object
    {
        $order->id          = (int) $order->ID;
        $order->creator     = (int) $order->creator;
        $order->sales       = $order->sales;
        $order->items_count = (int) $order->items_count;
        unset($order->ID);

        return $order;
    }

    public static function mask(object $order): object
    {
        if ($order->creator) {
            $currentUserId = wp_get_current_user()->ID;
            // 电话: 管理员、下单人本部门所有人及管理员，客服、客服管理员，可看到电话全部
            if (!is_administrator() && !is_store_manager() && !is_customer_service() && $currentUserId !== $order->creator) {
                $order->billing_phone = mask_mobile_phone($order->billing_phone);
                $order->shipping_phone = mask_mobile_phone($order->shipping_phone);
            }
        }
        return $order;
    }

    public static function formatOrderItem(object $item): object
    {
        $item->id        = (int) $item->id;
        $item->order_id  = (int) $item->order_id;
        $item->media_id  = $item->media_id ? (int) $item->media_id : 0;
        $item->media_url = $item->media_url ?? "";
        $item->images         = [];
        $item->price     = number_format($item->price, 2, '.', '');
        $item->quantity  = (int) $item->quantity;
        $item->total     = number_format($item->total, 2, '.', '');
        $item->note     = is_null($item->note) ? '' : $item->note;
        return $item;
    }

    public static function createStore($storeName, $taxonomy = 'user-group')
    {
        $id = term_exists($storeName, $taxonomy);
        if ($id) {
            return $id;
        }

        return wp_insert_term($storeName, $taxonomy);
    }
}
