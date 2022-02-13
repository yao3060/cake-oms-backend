<?php

namespace App\Filters;

use Illuminate\Database\Query\Builder;
use WP_REST_Request;

class StoreUserFilter extends Filter
{
  public static function handle(Builder $query, ?WP_REST_Request $request): Builder
  {
    if (\is_store_user()) {
      $groups = wp_get_terms_for_user(wp_get_current_user(), 'user-group');
      if ($groups) {
        $storeIds = collect($groups)->pluck('term_id');
        $query->whereIn('store_id', $storeIds);
      } else {
        write_log(sprintf('user(%d) do not have store.', get_current_user_id()));
        $query->where('store_id', 0);
      }
    }

    return $query;
  }
}
