<?php

namespace App\Services;

use App\Models\OrderLog;

class OrderLogService
{
    public function __construct(protected int $orderId = 0, protected array $data = [])
    {
    }

    protected function getMessage()
    {
        // verified
        if (isset($this->data['order_status']) && $this->data['order_status'] == 'verified') {
            return 'verified';
        }

        if (isset($this->data['order_status']) && $this->data['order_status'] == 'processing') {
            return 'processing';
        }

        if (isset($this->data['order_status']) && $this->data['order_status'] == 'completed') {
            return 'completed';
        }

        if (isset($this->data['order_status']) && $this->data['order_status'] == 'trash') {
            return 'trash';
        }


        // assign framer
        if (isset($this->data['framer'])) {
            return 'assign framer';
        }

        return 'update order';
    }

    public function addUpdateLog()
    {
        $this->add($this->orderId, 'update', $this->getMessage(), $this->data);
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
