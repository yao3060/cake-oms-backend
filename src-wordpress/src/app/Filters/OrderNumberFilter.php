<?php

namespace App\Filters;

use Illuminate\Database\Query\Builder;
use WP_REST_Request;

class OrderNumberFilter extends Filter
{

  static function handle(Builder $query, WP_REST_Request $request): Builder
  {

    if ($request->get_param('pickup_number')) {
      $query->where('pickup_number', $request->get_param('pickup_number'));
      return $query;
    }

    return $query;
  }
}

// 