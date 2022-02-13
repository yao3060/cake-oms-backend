<?php

namespace App\Filters;

use Illuminate\Database\Query\Builder;
use WP_REST_Request;

class StatusFilter extends Filter
{

  static function handle(Builder $query, WP_REST_Request $request): Builder
  {
    if ($request->get_param('status') && $request->get_param('status') !== 'all') {
      $query->where('order_status', $request->get_param('status'));
      return $query;
    }

    return $query;
  }
}
