<?php

add_action('plugins_loaded', function () {
	load_muplugin_textdomain('cake', 'languages');
});

add_filter('translations_api', '__return_true');