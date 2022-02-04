<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package cake
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <div id="page" class="site">
        <h1 style="text-align: center;">不要看了，什么都不会有的。</h1>
        <pre>
        <?php
        // print_r((new \App\Services\OrderLogService)->getOrderLogs(2)->toArray());
        ?>
        </pre>
    </div>
    <?php wp_footer(); ?>
</body>

</html>