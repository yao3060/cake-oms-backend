<?php

if (!function_exists('is_administrator')) {
  function is_administrator()
  {
    return current_user_can('administrator');
  }
}


if (!function_exists('is_framer')) {
  function is_framer()
  {
    return current_user_can('framer');
  }
}

if (!function_exists('is_framer_manager')) {
  function is_framer_manager()
  {
    return current_user_can('framer-manager');
  }
}

if (!function_exists('is_customer_service')) {
  function is_customer_service()
  {
    return current_user_can('customer-service');
  }
}

if (!function_exists('is_store_manager')) {
  function is_store_manager()
  {
    return current_user_can('store-manager');
  }
}

if (!function_exists('is_employee')) {
  function is_employee()
  {
    return current_user_can('employee');
  }
}

if (!function_exists('is_framer_user')) {
  function is_framer_user()
  {
    return is_framer_manager() || is_framer();
  }
}

if (!function_exists('is_store_user')) {
  function is_store_user()
  {
    return is_store_manager() || is_employee();
  }
}


if (!function_exists('write_log')) {
  function write_log($log)
  {
    if (is_array($log) || is_object($log)) {
      error_log(print_r($log, true));
    } else {
      error_log($log);
    }
  }
}
