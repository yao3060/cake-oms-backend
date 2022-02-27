<?php

namespace App\Permissions;

use WP_REST_Request;
use WP_User;
use WP_Error;

class OrderUpdatePermission
{

  // $status = ["unverified", "verified", "processing", "completed", "trash"]
  protected $status = '';
  protected $roles;

  public function __construct(protected WP_REST_Request $request, protected WP_User $user)
  {
    $this->status = $request->get_param('status');
  }

  public function check(): WP_Error | bool
  {
    // if not logged in
    if (!$this->user->ID) {
      return new WP_Error('not_logged_in', __('Not Logged In', 'cake'), ['status' => 401]);
    }
    if ($this->status) {
      return $this->statusStrategy();
    }

    return true;
  }

  protected function statusStrategy()
  {
    if ($this->status === 'verified' && is_framer_user()) {
      return new WP_Error('no_permission', __('Framer Can\'t Verify Order', 'cake'), ['status' => 401]);
    }

    if ($this->status === 'processing' && is_store_user()) {
      return new WP_Error('no_permission', __('Only Framers can star processing.', 'cake'), ['status' => 401]);
    }
    return true;
  }
}
