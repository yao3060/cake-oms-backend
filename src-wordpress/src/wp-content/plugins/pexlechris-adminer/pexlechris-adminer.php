<?php
/**
 * Plugin Name: Database Management tool - Adminer
 * Description: Manage the database from your WordPress Dashboard using Adminer
 * Version: 1.0.0
 * Stable tag: 1.0.0
 * Adminer version: 4.8.1
 * Author: Pexle Chris
 * Author URI: https://www.pexlechris.dev
 * Contributors: pexlechris
 * Domain Path: /languages
 * Requires at least: 4.6.0
 * Tested up to: 5.9
 * Requires PHP: 5.6
 * License: GPLv2
 */

add_action( 'plugins_loaded', 'wp_adminer_load_plugin_textdomain' );
function wp_adminer_load_plugin_textdomain() {
	load_plugin_textdomain(
		'pexlechris-adminer',
		false,
		dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
	);
}

//INIT
add_action('plugins_loaded', 'determine_if_wp_adminer_will_be_included', 2);
function determine_if_wp_adminer_will_be_included()
{
	$REQUEST_URI = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
	$REQUEST_URI = rtrim($REQUEST_URI, '/');

	$expl = explode('/', $REQUEST_URI);
	if ( 'wp-adminer' == $expl[count($expl)-1] )
	{
		if( have_current_user_access_to_wp_adminer() ){
			include 'adminer.php';
			exit;
		}else{
			do_action('wp_adminer_current_user_has_not_access');
		}
	}

}



//POSITION 1
add_action("admin_bar_menu", 'wp_adminer_register_in_wp_admin_bar' , 50);
function wp_adminer_register_in_wp_admin_bar($wp_admin_bar) {

	if( have_current_user_access_to_wp_adminer() ){
		$args = array(
			'id' => 'wp_adminer',
			'title' => esc_html__('WP Adminer', 'pexlechris-adminer'),
			'href' => site_url().'/wp-adminer',
			"meta" => array(
				"target" => "_blank"
			)
		);
		$wp_admin_bar->add_node($args);
	}

}

//POSITION 2
add_action('admin_menu', 'register_wp_adminer_as_tool');
function register_wp_adminer_as_tool(){
	add_submenu_page(
		'tools.php',
		esc_html__('WP Adminer', 'pexlechris-adminer'),
		esc_html__('WP Adminer', 'pexlechris-adminer'),
		implode(',', wp_adminer_access_capabilities()),
		'wp-adminer',
	    'wp_adminer_tools_page_content',
		3
	);
}


//IN TOOLS
function wp_adminer_tools_page_content()
{
    ?>
    <style>
        #wp_adminer_current_url{
            display: block !important;
        }
        #wpcontent{
            padding-left: 0px;
        }
        #wp_adminer--iframe{
            width: 100%;
            min-width: 100%;
            min-height: calc(100vh - 32px);
            position: absolute;
            left: 0;
            top: 0;
            z-index: 999;
        }
        @media all and (max-width:782px) {
            #wp_adminer--iframe{
                min-height: calc(100vh - 46px);
            }
        }
        <?php do_action('print_css_inside_wp_adminer_tools_page'); ?>
    </style>
    <?php
	if( isset($_GET['server']) ){
		$iframe_src = (is_ssl() ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$iframe_src = str_replace('wp-admin/tools.php?page=wp-adminer&', 'iframe/wp-adminer?', $iframe_src);
	}else{
		$iframe_src = site_url() . '/iframe/wp-adminer';
	}
    ?>
    <iframe id="wp_adminer--iframe" src="<?php echo esc_url($iframe_src)?>"></iframe>

    <script>
        function setParentWindowURL(url) {
            history.replaceState(null, '', window.location.href.split('?')[0] + '?page=wp-adminer&' + url.split('?')[1]);
        }
        function setIframeDimensions(fullWindowHeight,fullWindowWidth) {
            document.getElementById('wp_adminer--iframe').style.height = 'calc(' + fullWindowHeight.toString() + 'px + 3rem)';
            document.getElementById('wp_adminer--iframe').style.width = fullWindowWidth.toString() + 'px';
            document.getElementById('adminmenuwrap').style.height = fullWindowHeight.toString() + 'px';
        }
        <?php do_action('print_js_inside_wp_adminer_tools_page'); ?>
    </script>
	<?php
}

function wp_adminer_access_capabilities()
{
    //only administrator of website has the capability `manage_options`
	$capabilities = array('manage_options');
	$capabilities = apply_filters('wp_adminer_access_capabilities', $capabilities);
    return $capabilities;
}

// can be overridden in a must-use plugin
if( !function_exists('have_current_user_access_to_wp_adminer') ){
	function have_current_user_access_to_wp_adminer()
	{
		foreach (wp_adminer_access_capabilities() as $capability) {
			if( current_user_can($capability) ) return true;
		}

		return false;
	}
}





// JS
// inside the window load event
function wp_adminer_print_js_inside_adminer($is_iframe)
{
    // boolean: $is_iframe
    do_action('print_js_inside_wp_adminer', $is_iframe);
}

add_action('print_js_inside_wp_adminer', 'print_js_inside_wp_adminer');
function print_js_inside_wp_adminer($is_iframe){
    ?>
    if ( null !== document.querySelector('.pexle_loginForm + p > input') ) {
        document.querySelector('.pexle_loginForm + p > input').click();
    } <?php if($is_iframe){ ?> else {
        window.parent.setParentWindowURL(window.location.href);
        window.parent.setIframeDimensions( Math.max(document.querySelector('body').clientHeight, document.querySelector('#menu').clientHeight), document.querySelector('body').clientWidth);
    }
    <?php } ?>
    <?php
}


//CSS
function wp_adminer_print_css_inside_adminer($is_iframe)
{
    echo '<style>';
	// boolean: $is_iframe
	do_action('print_css_inside_wp_adminer', $is_iframe);
	echo '</style>';
}

add_action('print_css_inside_wp_adminer', 'print_css_inside_wp_adminer');
function print_css_inside_wp_adminer($is_iframe){
	?>
    .pexle_loginForm *,
    .pexle_loginForm + p,
    #tables a.select,
    #version {
        display: none;
    }
    .pexle_loginForm::before {
        content: "<?php esc_html_e('You are connecting to the database...', 'pexlechris-adminer')?>";
    }
	<?php
}
