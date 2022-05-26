<?php

namespace App\Permissions;

use App\Services\OrderService;
use WP_REST_Request;
use WP_User;
use WP_Error;

class OrderStatusPermission
{

    /** @var int $orderId */
    protected int $orderId = 0;

    /** @var string $status ["unverified", "verified", "processing", "completed", "trash"] */
    protected string $status = '';

    /** @var string $method ["get", "post", "put"] */
    protected string $method = 'get';

    protected OrderService $orderService;

    public function __construct(protected WP_REST_Request $request, protected WP_User $user)
    {
        $this->method = strtolower($request->get_method());
        $this->status = $request->get_param('status');
        $this->orderId = (int)$request->get_param('id');
        $this->orderService = new OrderService();
    }

    public function check(): WP_Error | bool
    {
        // if not logged in
        if (!$this->user->ID) {
            return new WP_Error('not_logged_in', __('Not Logged In', 'cake'), ['status' => 401]);
        }

        if (is_administrator()) {
            return true;
        }

        if ($this->method == 'put') {
            return $this->putStrategy();
        }

        if ($this->method == 'get') {
            return $this->getStrategy();
        }

        return true;
    }

    protected function getStrategy(): WP_Error | bool
    {
        // framer no unverified orders
        if ($this->status === 'unverified' && is_framer_user()) {
            return new WP_Error(
                'no_permission',
                "Only framer users can't view unverified orders.",
                ['status' => 404]
            );
        }

        return true;
    }

    protected function putStrategy(): WP_Error | bool
    {
        // 最高管理员、下单人、门店管理员，客服、客服管理员，可修改状态为已审核
        if (
            $this->status === 'verified' &&
            !is_customer_service() &&
            !is_store_manager() &&
            !$this->orderService->isCreator($this->orderId)
        ) {
            return new WP_Error(
                'no_permission',
                "Only `administrator`, `creator`, `store manager` and `customer service` can be update order `status` to `verified`.",
                ['status' => 401]
            );
        }

        // processing, 只有 裱花师 可以开始订单
        if ($this->status === 'processing' && !is_framer()) {
            return new WP_Error('no_permission', __('Only Framers can star processing.', 'cake'), ['status' => 401]);
        }

        // trash, 最高管理员，下单人，下单人所属部门的管理员，客服、客服管理员 可以
        if (
            $this->status === 'trash' &&
            !$this->orderService->isCreator($this->orderId) &&
            !is_store_manager() &&
            !is_customer_service()
        ) {
            return new WP_Error(
                'no_permission',
                "Only `administrator`, `creator`, `store manager` and `customer service` can `trash` orders.",
                ['status' => 401]
            );
        }

        return true;
    }
}
