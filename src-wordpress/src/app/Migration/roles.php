<?php
// customer-service-manager
if (add_role('customer-service-manager', '客服经理', get_role('editor')->capabilities)) {
    echo  'Role 客服经理 Added.' . PHP_EOL;
} else {
    echo  'Role 客服经理 exist.' . PHP_EOL;
}

if (add_role('customer-service', '客服', get_role('editor')->capabilities)) {
    echo  'Role 客服 Added.' . PHP_EOL;
} else {
    echo  'Role 客服 exist.' . PHP_EOL;
}
if (add_role('employee', '门店员工', get_role('editor')->capabilities)) {
    echo  'Role 门店员工 Added.' . PHP_EOL;
} else {
    echo  'Role 门店员工 exist.' . PHP_EOL;
}
if (add_role('framer', '裱画师', get_role('editor')->capabilities)) {
    echo  'Role 裱画师 Added.' . PHP_EOL;
} else {
    echo  'Role 裱画师 exist.' . PHP_EOL;
}
if (add_role('framer-manager', '裱花管理员', get_role('editor')->capabilities)) {
    echo  'Role 裱花管理员 Added.' . PHP_EOL;
} else {
    echo  'Role 裱花管理员 exist.' . PHP_EOL;
}
if (add_role('store-manager', '店长', get_role('editor')->capabilities)) {
    echo  'Role 店长 Added.' . PHP_EOL;
} else {
    echo  'Role 店长 exist.' . PHP_EOL;
}
