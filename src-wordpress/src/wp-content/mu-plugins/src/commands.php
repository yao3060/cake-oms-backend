<?php

if (class_exists('WP_CLI')) {
  WP_CLI::add_command('example', new \App\Commands\SyncOrders());
}
