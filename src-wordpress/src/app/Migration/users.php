<?php

// 初始化 裱花师 用户
$user_id = wp_insert_user([
    'user_login' => 'framer1',
    'user_email' => 'framer1@app.com',
    'display_name' => '裱花师1',
    'user_pass' => 'login_password',
    'show_admin_bar_front' => false,
    'role' => 'framer'
]);

if (!is_wp_error($user_id)) {
    wp_set_terms_for_user($user_id, 'user-group', 'workshop');
    echo "裱花师1 created, User ID : " . $user_id . PHP_EOL;
}


$user_id = wp_insert_user([
    'user_login' => 'framer2',
    'user_email' => 'framer2@app.com',
    'display_name' => '裱花师2',
    'user_pass' => 'login_password',
    'show_admin_bar_front' => false,
    'role' => 'framer'
]);

if (!is_wp_error($user_id)) {
    wp_set_terms_for_user($user_id, 'user-group', 'workshop');
    echo "裱花师2 created, User ID : " . $user_id . PHP_EOL;
}

$user_id = wp_insert_user([
    'user_login' => 'framer_manager_1',
    'user_email' => 'framer_manager_1@app.com',
    'display_name' => '裱花经理',
    'user_pass' => 'login_password',
    'show_admin_bar_front' => false,
    'role' => 'framer-manager',
]);

if (!is_wp_error($user_id)) {
    wp_set_terms_for_user($user_id, 'user-group', 'workshop');
    echo "裱花经理 created, User ID : " . $user_id . PHP_EOL;
}


// 初始化 员工 用户
$user_id = wp_insert_user([
    'user_login' => 'yuangong01',
    'user_email' => 'yuangong01@app.com',
    'display_name' => '员工01',
    'user_pass' => 'login_password',
    'show_admin_bar_front' => false,
    'role' => 'employee',
]);

if (!is_wp_error($user_id)) {
    wp_set_terms_for_user($user_id, 'user-group', 'xuhui-store');
    echo "员工 1 created, User ID : " . $user_id . PHP_EOL;
}

$user_id = wp_insert_user([
    'user_login' => 'yuangong02',
    'user_email' => 'yuangong02@app.com',
    'display_name' => '员工02',
    'user_pass' => 'login_password',
    'show_admin_bar_front' => false,
    'role' => 'employee',
]);

if (!is_wp_error($user_id)) {
    wp_set_terms_for_user($user_id, 'user-group', 'longhua-store');
    echo "员工02 created, User ID : " . $user_id . PHP_EOL;
}

