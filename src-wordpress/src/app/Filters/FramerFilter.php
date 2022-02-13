<?php

namespace App\Filters;

use Illuminate\Database\Query\Builder;
use WP_REST_Request;

class FramerFilter extends Filter
{

  public static function handle(Builder $query, WP_REST_Request $request): Builder
  {
    if (!is_framer_user()) {
      return $query;
    }

    if (is_framer_manager()) {
      $query->whereNotNull('framer');
    } else {
      $query->where('framer', get_current_user_id());
    }

    return $query;
  }
}
