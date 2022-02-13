<?php

$initStores = [
  [
    'title' => '裱花间',
    'slug' => 'workshop'
  ],
  [
    'title' => '徐汇店',
    'slug' => 'xuhui-store'
  ],
  [
    'title' => '龙华店',
    'slug' => 'longhua-store'
  ]
];

$taxonomy = 'user-group';
foreach ($initStores as $store) {
  if (term_exists($store['title'], $taxonomy)) {
    echo  $store['title'] . ' exist.' . PHP_EOL;
    continue;
  }
  try {
    wp_insert_term($store['title'], $taxonomy, [
      'slug' => $store['slug'],
    ]);
    echo  $store['title'] . ' created.' . PHP_EOL;
  } catch (\Throwable $th) {
    echo $th->getMessage() . PHP_EOL;
  }
}
