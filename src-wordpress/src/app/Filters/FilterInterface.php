<?php

namespace App\Filters;

use Illuminate\Database\Query\Builder;
use WP_REST_Request;

interface FilterInterface
{
  static function handle(Builder $query, WP_REST_Request $request): Builder;
}
