<?php

namespace App\Services;

use WP_User;

class OrderService
{
    private \WeDevs\ORM\Eloquent\Database|null|false $db = null;
    private string $dbPrefix = '';

    public function __construct()
    {
        $this->db       = \WeDevs\ORM\Eloquent\Database::instance();
        $this->dbPrefix = $this->db->db->prefix;
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
        (new OrderLogService)->add($id, 'update', 'update order', $data);
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
            'username' => $user->user_login,
            'display_name' => $user->display_name,
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
            if ($currentUserId !== $order->creator) {
                $order->billing_phone = mask_mobile_phone($order->billing_phone);
                //shipping_phone
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
        $item->price     = number_format($item->price, 2);
        $item->quantity  = (int) $item->quantity;
        $item->total     = number_format($item->total, 2);
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
