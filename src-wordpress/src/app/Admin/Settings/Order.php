<?php

namespace App\Admin\Settings;


class Order
{
    const PAGE_NAME = 'orderSettingsPage';
    const OPTION_NAME = 'cake_orders_settings';

    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function add_admin_menu()
    {
        add_options_page(
            'Orders',
            'Orders',
            'manage_options',
            'orders',
            [$this, 'options_page']
        );
    }

    public function settings_init()
    {
        register_setting(self::PAGE_NAME, self::OPTION_NAME);

        $sectionName = 'pluginPageSection';

        add_settings_section(
            $sectionName,
            __('同步设置', 'cake'),
            [$this, 'settings_section_callback'],
            self::PAGE_NAME
        );

        add_settings_field(
            'xpyunUserRender',
            __('XPYUN USER ID', 'cake'),
            [$this, 'xpyunUserRender'],
            self::PAGE_NAME,
            $sectionName
        );

        add_settings_field(
            'xpyunUserKeyRender',
            __('XPYUN USER SECRET', 'cake'),
            [$this, 'xpyunUserKeyRender'],
            self::PAGE_NAME,
            $sectionName
        );

        add_settings_field(
            'pickupTimeLimitRender',
            __('取货时间限制(小时)', 'cake'),
            [$this, 'pickupTimeLimitRender'],
            self::PAGE_NAME,
            $sectionName
        );

        add_settings_field(
            'noSaveOrderWithoutPickupTime',
            __('不保存没有取货时间的订单', 'cake'),
            [$this, 'noSaveOrderWithoutPickupTime'],
            self::PAGE_NAME,
            $sectionName
        );
    }

    function xpyunUserRender()
    {
        $options = get_option(self::OPTION_NAME);
?>
        <input type='text' name='cake_orders_settings[XPYUN_USER]' value='<?php echo $options['XPYUN_USER'] ?? ''; ?>'>
    <?php

    }

    function xpyunUserKeyRender()
    {
        $options = get_option(self::OPTION_NAME);
    ?>
        <input type='text' name='cake_orders_settings[XPYUN_USER_SECRET]' value='<?php echo $options['XPYUN_USER_SECRET'] ?? ''; ?>'>
    <?php

    }

    function pickupTimeLimitRender()
    {
        $options = get_option(self::OPTION_NAME);
    ?>
        <input type='text' name='cake_orders_settings[pickup_time_limit]' value='<?php echo $options['pickup_time_limit'] ?? ''; ?>'>
    <?php

    }

    function noSaveOrderWithoutPickupTime()
    {

        $options = get_option(self::OPTION_NAME);
    ?>
        <input type='checkbox' name='cake_orders_settings[do_not_save_order_without_pickup_time]' <?php checked($options['do_not_save_order_without_pickup_time'] ?? 0, 1); ?> value='1'>
    <?php

    }

    function settings_section_callback()
    {
    }

    function options_page()
    {

    ?>
        <form action='options.php' method='post'>

            <h2>Orders</h2>

            <?php
            settings_fields(self::PAGE_NAME);
            do_settings_sections(self::PAGE_NAME);
            submit_button();
            ?>

        </form>
<?php

    }
}
