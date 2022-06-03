<?php

namespace App\Models;

use ArrayObject;
use DateTime;
use WP_User;

class Order extends ArrayObject
{
    public function __construct($data = array())
    {
        parent::__construct($data, ArrayObject::ARRAY_AS_PROPS);
    }

    public function getUpdatedAt()
    {
        return get_date_from_gmt($this->updated_at);
    }

    public function getProduceTime()
    {
        $orderStatus = $this->order_status;
        if ($orderStatus === 'completed') {
            $logs = OrderLog::where('order_id', $this->id)
                ->whereIn('message', ['processing', 'completed'])
                ->get(['message', 'created_at']);

            $interval  = date_diff(
                date_create($logs[0]['created_at']),
                date_create($logs[1]['created_at'])
            );
        } else {
            $interval  = date_diff(
                date_create($this->updated_at),
                date_create(date('Y-m-d H:i:s'))
            );
        }

        return $interval;
    }

    public function getDeadline()
    {
        $diffHours = get_term_meta($this->store_id, 'user_group_deadline', true);
        if (!$diffHours) {
            $this->pickup_time;
        }

        $date = new DateTime($this->pickup_time);
        $date->modify('-' . $diffHours . ' hours');
        return $date->format('Y-m-d H:i:s');
    }

    public function getFramer(): array
    {
        $user = new WP_User((int)$this->framer);
        return [
            'id' => $user->ID,
            'username' => $user->user_login ?? '',
            'display_name' => $user->display_name ?? '',
        ];
    }

    public function getCreator(): array
    {
        $user = new WP_User((int)$this->creator);
        return [
            'id' => $user->ID,
            'username' => $user->user_login ?? '',
            'display_name' => $user->display_name ?? '',
        ];
    }
}
