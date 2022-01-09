<?php

namespace App\Services;

class OrderService {

	private \WeDevs\ORM\Eloquent\Database|null|false $db = null;
	private string $dbPrefix = '';

	public function __construct() {
		$this->db       = \WeDevs\ORM\Eloquent\Database::instance();
		$this->dbPrefix = $this->db->db->prefix;
	}


	public function getOrderById(int $id): object|null
	{
		$order = $this->db->table( 'orders' )
		                  ->where( 'id', $id )
		                  ->first();
		if($order) {
			$order = self::formatOrder($order);
		}
		return $order;
	}

	public function getOrderItems(int $orderId):array
	{
		$items = $this->db->table( 'order_items' )
		                  ->where( 'order_id', $orderId )
		                  ->get();
		if ( $items->count() ) {
			foreach ( $items as $key => $item ) {
				$item = self::formatOrderItem( $item );
			}
			return $items->toArray();
		}
		return [];
	}

	public static function formatOrder( $order ) {
		$order->id          = (int) $order->id;
		$order->creator     = (int) $order->creator;
		$order->sales       = (int) $order->sales;
		$order->items_count = (int) $order->items_count;

		return $order;
	}

	public static function formatOrderItem( $item ) {
		$item->id        = (int) $item->id;
		$item->order_id  = (int) $item->order_id;
		$item->media_id  = $item->media_id ? (int) $item->media_id : 0;
		$item->media_url = $item->media_url ?? "";
		$item->price     = number_format( $item->price, 2 );
		$item->quantity  = (int) $item->quantity;
		$item->total     = number_format( $item->total, 2 );
		$item->note     = is_null( $item->note ) ? '' : $item->note;
		return $item;
	}
}