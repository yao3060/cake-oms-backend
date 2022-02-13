<?php

namespace App\Filters;

use Illuminate\Database\Query\Builder;
use WP_REST_Request;

class BillingPhoneFilter extends Filter
{

  static function handle(Builder $query, WP_REST_Request $request): Builder
  {
    if ($request->get_param('order_number')) {
      $query->where('order_number', $request->get_param('order_number'));
      return $query;
    }

    return $query;
  }
}

// 