<?php

add_action('plugins_loaded', function () {
	load_muplugin_textdomain('cake', 'languages');
});
