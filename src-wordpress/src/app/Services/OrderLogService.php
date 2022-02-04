<?php

namespace App\Services;

use App\Models\OrderLog;

class OrderLogService
{

  public function __construct()
  {
  }

  /**
   * create a event log
   *
   * @param integer $orderId
   * @param string $event
   * @param string $message
   * @return integer
   */
  public function add(int $orderId, string $event = 'default', string $message = '', array $data = []): int
  {
    $user = wp_get_current_user();

    $log = new OrderLog;
    $log->order_id = $orderId;
    $log->user_id = $user->ID;
    $log->username = $user->display_name;
    $log->user_roles = json_encode($user->roles);
    $log->ip = $this->getIP();
    $log->event = $event;
    $log->message = $message;
    $log->data = json_encode($data);
    $log->created_at = date('Y-m-d H:i:s');

    $log->save();

    return $log->id;
  }

  public function getById(int $id)
  {
    return OrderLog::find($id);
  }

  /**
   * @param integer $orderId
   * @return \Illuminate\Database\Eloquent\Collection|static[]
   */
  public function getOrderLogs(int $orderId)
  {
    return OrderLog::where('order_id', $orderId)
      ->orderBy('id', 'desc')
      ->get();
  }

  protected function getIP()
  {

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {

      //check ip from share internet

      $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

      //to check ip is pass from proxy

      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {

      $ip = $_SERVER['REMOTE_ADDR'];
    }

    return apply_filters('cake_get_ip', $ip);
  }
}
