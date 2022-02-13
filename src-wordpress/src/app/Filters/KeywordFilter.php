<?php

namespace App\Filters;

use Illuminate\Database\Query\Builder;
use WP_REST_Request;

class KeywordFilter extends Filter
{
  static function handle(Builder $query, WP_REST_Request $request): Builder
  {
    if ($request->get_param('keyword')) {
      $keyword = $request->get_param('keyword');
      $query->where('order_number', 'like', '%' . $keyword . '%')
        ->orWhere('billing_phone', 'like', '%' . $keyword . '%')
        ->orWhere('shipping_phone', 'like', '%' . $keyword . '%');
      return $query;
    }
    return $query;
  }
}
