<?php

add_action('user-group_edit_form_fields', function ($term, $taxonomy) {
    $value = get_term_meta($term->term_id, 'user_group_phone', true);
    echo '<tr class="form-field">
      <th>
        <label for="user_group_phone">电话</label>
      </th>
      <td>
        <input name="user_group_phone" id="user_group_phone" type="text" value="' . esc_attr($value) . '" />
        <p class="description">店铺电话</p>
      </td>
    </tr>';

    $value = get_term_meta($term->term_id, 'user_group_address', true);
    echo '<tr class="form-field">
      <th>
        <label for="user_group_address">地址</label>
      </th>
      <td>
        <input name="user_group_address" id="user_group_address" type="text" value="' . esc_attr($value) . '" />
        <p class="description">店铺地址</p>
      </td>
    </tr>';

    $value = get_term_meta($term->term_id, 'user_group_deadline', true);
    echo '<tr class="form-field">
      <th>
        <label for="user_group_deadline">出品时间</label>
      </th>
      <td>
        <input name="user_group_deadline" id="user_group_deadline" type="text" value="' . esc_attr($value) . '" />
        <p class="description">取货时间前 (' . $value . ') 小时</p>
      </td>
    </tr>';

    // printer_sn
    $value = get_term_meta($term->term_id, 'printer_sn', true);
    echo '<tr class="form-field">
      <th>
        <label for="user_group_printer_sn">打印机编号</label>
      </th>
      <td>
        <input name="printer_sn" id="user_group_printer_sn" type="text" value="' . esc_attr($value) . '" />
      </td>
    </tr>';
}, 10, 2);


function user_group_save_term_fields($term_id)
{
    $keys = ['user_group_phone', 'user_group_address', 'user_group_deadline', 'printer_sn'];
    foreach ($keys as $key) {
        update_term_meta($term_id, $key, sanitize_text_field($_POST[$key]));
    }
}
add_action('created_user-group', 'user_group_save_term_fields');
add_action('edited_user-group', 'user_group_save_term_fields');
