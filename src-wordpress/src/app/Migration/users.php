<?php
$users = [
    [
        'user_login' => 'administrator',
        'user_email' => 'administrator@app.com',
        'display_name' => 'Administrator',
        'user_pass' => 'Yao123',
        'show_admin_bar_front' => false,
        'role' => 'administrator',
        'user-group' => '',
    ],
    [
        'user_login' => 'framer1',
        'user_email' => 'framer1@app.com',
        'display_name' => '裱花师1',
        'user_pass' => 'login_password',
        'show_admin_bar_front' => false,
        'role' => 'framer',
        'user-group' => 'workshop',
    ],
    [
        'user_login' => 'framer2',
        'user_email' => 'framer2@app.com',
        'display_name' => '裱花师2',
        'user_pass' => 'login_password',
        'show_admin_bar_front' => false,
        'role' => 'framer',
        'user-group' => 'workshop',
    ],
    [
        'user_login' => 'framer_manager_1',
        'user_email' => 'framer_manager_1@app.com',
        'display_name' => '裱花经理',
        'user_pass' => 'login_password',
        'show_admin_bar_front' => false,
        'role' => 'framer-manager',
        'user-group' => 'workshop',
    ],
    [
        'user_login' => 'yuangong01',
        'user_email' => 'yuangong01@app.com',
        'display_name' => '员工01',
        'user_pass' => 'login_password',
        'show_admin_bar_front' => false,
        'role' => 'employee',
        'user-group' => 'xuhui-store',
    ],
    [
        'user_login' => 'yuangong02',
        'user_email' => 'yuangong02@app.com',
        'display_name' => '员工02',
        'user_pass' => 'login_password',
        'show_admin_bar_front' => false,
        'role' => 'employee',
        'user-group' => 'longhua-store',
    ],

];

foreach ($users as $user) {
    $userGroup = $user['user-group'];
    unset($user['user-group']);

    $user_id = wp_insert_user($user);
    if (!is_wp_error($user_id)) {
        if (!empty($userGroup)) {
            wp_set_terms_for_user($user_id, 'user-group', $userGroup);
        }
        echo $user['display_name'] . " created, User ID : " . $user_id . PHP_EOL;
    }
}
