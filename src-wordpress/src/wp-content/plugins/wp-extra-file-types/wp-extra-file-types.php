<?php
/**
 * Plugin Name: WP Extra File Types
 * Description: Plugin to let you extend the list of allowed file types supported by the Wordpress Media Library.
 * Plugin URI: http://www.airaghi.net/en/2015/01/02/wordpress-custom-mime-types/
 * Version: 0.5.2
 * Author: Davide Airaghi
 * Author URI: http://www.airaghi.net
 * License: GPLv2 or later
 */
 
defined('ABSPATH') or die("No script kiddies please!");


class WPEFT {
	
	private $lang         = array();
	private $is_multisite = false;
	private $types_list   = false;
	
	const NONCE_FIELD   = '_wpnonce';
	const NONCE_ACTION = 'wp-extra-file-types-page-options';
	
	public function __construct() {
		// language
		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'languages.php' );
		$lang = get_bloginfo('language','raw');
		if (!isset($wpeft_lang[$lang])) {
			$lang = 'en-US';
		}
		$this->lang = $wpeft_lang[$lang];
		// mime types' list
		$main_list = dirname(__FILE__).DIRECTORY_SEPARATOR.'mime-list.txt';
		if (file_exists($main_list)) {
			$wpeft_list = trim(file_get_contents($main_list));
			if ($wpeft_list) {
				$this->types_list = @unserialize($wpeft_list);
			} else {
				$this->types_list = false;
			}
		}
		// multisite
		$this->is_multisite = is_multisite();
	}

    private function clean_ext($the_ext) {
      $the_ext = trim($the_ext);
      return preg_replace('#([^a-zA-Z0-9_.\-]+)#','',$the_ext);
    }

    private function clean_name($name) {
      $name = trim($name);
      $name = str_replace(array('<','>',"\r","\n","\v",'"','`'),array('-','-',' ',' ',' ',"'","'"),$name);
      return $name;
    }

    private function clean_mime($mime) {
      $mime = trim($mime);
      $mime = strtolower($mime);
      if (function_exists('sanitize_mime_type')) {
          $mime = sanitize_mime_type($mime);
      } else {
          $mime = preg_replace('#([^a-zA-Z0-9.\-_/]+)#','',$mime);
      }
      return $mime;
    }

    private function getPost($name,$val='') {
        $val = isset($_POST[$name]) ? sanitize_text_field($_POST[$name]) : sanitize_text_field($val);
        return $val;
    }

    private function getCookie($name,$val='') {
        $val = isset($_COOKIE[$name]) ? sanitize_text_field($_COOKIE[$name]) : sanitize_text_field($val);
        return $val;
    }

    private function token($check=false) {
      if ($check) {
          $token = $this->getCookie('wp-extra-file-types-token','');
          $token = strval($token);
          $post  = $this->getPost('token','');
          $_COOKIE['wp-extra-file-types-token'] = '';
          unset($_COOKIE['wp-extra-file-types-token']);
          // echo $post.' * '.$token;die;
          return $post === $token;
      } else {
          $token = time() . rand(1111,9999) . rand(1111,9999) . md5(time());
          setcookie('wp-extra-file-types-token',$token);
          $_COOKIE['wp-extra-file-types-token'] = $token;
          return $token;
      }
    }

	private function defaults() {
		return array (
			// text
			'txt' => 'text/plain',
			// compressed
			'7z'  => 'application/x-7z-compressed',
			'bz2' => 'application/x-bzip2',
			'gz'  => 'application/x-gzip',
			'tgz' => 'application/x-gzip',
			'txz' => 'application/x-xz',
			'xz'  => 'application/x-xz',
			'zip' => 'application/zip'
		);
	}
	
	public function settings() {
		register_setting('wp-extra-file-types-page','wpeft_types');	
		register_setting('wp-extra-file-types-page','wpeft_custom_types');
		register_setting('wp-extra-file-types-page','wpeft_no_strict');
		register_setting('wp-extra-file-types-page','wpeft_no_wp');
        register_setting('wp-extra-file-types-page','wpeft_gf_hack');
	}

	public function admin() {
		add_submenu_page( 
			'options-general.php',
			$this->lang['ADMIN_PAGE_TITLE'] , $this->lang['ADMIN_MENU_TITLE'], 
			'manage_options', 
			'wp-extra-file-types-page', 
			array($this,'admin_page')
		);
		add_action( 'admin_init', array($this,'settings') );
		if (get_option('wpeft_types','')=='') {
			update_option('wpeft_types',$this->defaults());
		}
		if (get_option('wpeft_custom_types','')=='') {
			update_option('wpeft_custom_types','');
		}
	}
	
	public function admin_page() {
		if (!current_user_can('manage_options')) { wp_die('Unauthorized'); }
		$ok_do_save = isset($_POST['do_save']) && $_POST['do_save']=='1';
		$ok_token   = false;
		$ok_nonce   = false;
		if ($ok_do_save) {
		  $ok_token   = $this->token(true);
		  $nonce      = isset($_REQUEST[self::NONCE_FIELD]) ? $_REQUEST[self::NONCE_FIELD] : '';
		  $ok_nonce   = wp_verify_nonce( $nonce , self::NONCE_ACTION);
		  // echo '<pre>'; print_r($_REQUEST); echo ' | '.intval($ok_do_save).' * '.intval($ok_token).' * '.intval($ok_nonce).' * '.$nonce.' | ';die;
		}
		if ($ok_do_save && $ok_token && $ok_nonce) {
			// save !!!
			if (!isset($_POST['ext']) || !is_array($_POST['ext'])) {
					update_option('wpeft_types','none');
			} else {
				$info = array();
				foreach ($this->types_list as $t) {
					foreach ($t->extensions as $te) {
						$info[$te] = $t->mime_type;
					}
				}
				$array = array();
				foreach ($_POST['ext'] as $the_ext) {
				    $the_ext = sanitize_text_field($the_ext);
				    $the_ext = $this->clean_ext($the_ext);
					$array[ $the_ext ] = $info['.'.$the_ext];
				}
				$ok = update_option('wpeft_types',$array);
				if (!$ok) {
					$ok = add_option('wpeft_types',$array);
				}
			}
			if (isset($_POST['custom_d'])) {
				$custom = array();
				foreach ($_POST['custom_d'] as $k=>$description) {
				    $description = sanitize_text_field(trim($description));
					$description = $this->clean_name($description);
					if ($description != '') {
						$ext  = $this->clean_ext(sanitize_text_field(trim($_POST['custom_e'][$k])));
						$mime = $this->clean_mime(sanitize_text_field(trim($_POST['custom_m'][$k])));
						if ($ext=='' || $mime=='')  { continue; }
						if (strpos($mime,'/')===false) { $mime = 'application/octet-stream'; }
						if (!substr($ext,0,1)=='.') { $ext = '.'.$ext; }
						$custom[] = array( 'description'=>$description, 'extension'=>$ext, 'mime'=>$mime );
					}
				}
				update_option('wpeft_custom_types',$custom);
			} else {
			  update_option('wpeft_custom_types',array());
			}
			if (isset($_POST['no_strict']) && $_POST['no_strict']) {
				update_option('wpeft_no_strict',true);
			} else {
				update_option('wpeft_no_strict',false);
			}
			if (isset($_POST['no_wp']) && $_POST['no_wp']) {
				update_option('wpeft_no_wp',true);
			} else {
				update_option('wpeft_no_wp',false);
			}
            if (isset($_POST['gf_hack']) && $_POST['gf_hack']) {
                update_option('wpeft_gf_hack',true);
            } else {
                update_option('wpeft_gf_hack',false);
            }
		}
		$token    = $this->token();
		$selected = get_option('wpeft_types','');
		if (!$selected) {
			$selected = $this->defaults();
		}
		if (!is_array($selected)) {
			$selected = array();
		}
		$exts = array_keys($selected);
		
		$nostrict = get_option('wpeft_no_strict',false);
		$nowp = get_option('wpeft_no_wp',false);
		$custom = get_option('wpeft_custom_types','');
        $gf_hack = get_option('wpeft_gf_hack',false);
		if (!$custom) {
			$custom = array();
		}		
		?>
		<script>
		    function showAll() {
		    	var els = document.getElementsByClassName('in_wp');
			var i=0,m=els.length;
			for (i=0;i<m;i++) {
			    els[i].style.display = 'table-row';
			}
		    }
		    function hideSome() {
			var els = document.getElementsByClassName('in_wp');
			var i=0,m=els.length;
			for (i=0;i<m;i++) {
			    els[i].style.display = 'none';
			}
		    }
		    function ShowOrHide() {
			var f = document.wpeft_form;
			var c = f.no_wp.checked;
			if (c) {
			    showAll();
			} else {
			    hideSome();
			}
		    }
		    function setupShowOrHide() {
			var f = document.wpeft_form;
			var c = f.no_wp;
			c.onchange = function() {
			    if (this.checked) { showAll();  }
			    else              { hideSome(); }
			}
		    }
		</script>
		<style>
		    .not_in_wp {
			display: table-row;
		    }
		    .in_wp {
			display: none;
		    }
		    .in_wp td {
			background-color: #F5A9A9;
		    }
		</style>
		<div class="wrap">
		<h2><?php echo htmlentities($this->lang['ADMIN_PAGE_TITLE']);?></h2>
		<p><?php echo htmlentities($this->lang['TEXT_CHOOSE']);?></p>
		<form  method="post" action="options-general.php?page=wp-extra-file-types-page" name="wpeft_form" onsubmit="return checkExt()">
			<input type="hidden" name="do_save" value="1" />
			<input type="hidden" name="token"   value="<?php echo esc_attr($token); ?>" />
			<?php settings_fields( 'wp-extra-file-types-page' ); ?>
			<?php do_settings_sections( 'wp-extra-file-types-page' ); ?>
			<table> 
				<tr>
					<td valign="top"><?php echo esc_html($this->lang['TEXT_NO_STRICT']);?></td>
					<td valign="top">&nbsp;</td>
					<td valign="top"><input type="checkbox" name="no_strict" <?php if ($nostrict) { echo 'checked="checked" '; } ?>> <?php echo esc_html($this->lang['TEXT_NO_STRICT_1']);?></td>
				</tr>
				<tr>
					<td valign="top"><?php echo esc_html($this->lang['TEXT_SKIP_WP']);?></td>
					<td valign="top">&nbsp;</td>
					<td valign="top"><input type="checkbox" name="no_wp" <?php if ($nowp) { echo 'checked="checked" '; } ?>> <?php echo esc_html($this->lang['TEXT_SKIP_WP_1']);?></td>
				</tr>
				<tr>
					<td valign="top"><?php echo esc_html($this->lang['TEXT_GF_HACK']);?></td>
					<td valign="top">&nbsp;</td>
					<td valign="top"><input type="checkbox" name="gf_hack" <?php if ($gf_hack) { echo 'checked="checked" '; } ?>> <?php echo esc_html($this->lang['TEXT_GF_HACK_1']);?></td>
				</tr>
				<tr>
				    <td colspan="3">
					<hr />
				    </td>
				</tr>
				<?php
				foreach ($this->types_list as $type) {
					foreach ($type->extensions as $ext) {
						$class = "not_in_wp";
						$ext0 = str_replace('.','',$ext);
						if ($this->_inWP($ext0)) { 
						    $class = "in_wp";
						}
						if (''==$ext0) { continue; }
						?>
						<tr class="<?php echo $class;?>">
							<td valign="top"><?php echo esc_html($type->application);?></td>
							<td valign="top"><?php echo esc_html($ext);?></td>
							<td valign="top">
								<input type="checkbox" name="ext[]" value="<?php echo esc_attr($ext0);?>" <?php if (in_array($ext0,$exts)) echo 'checked="checked"'; ?> >
							</td>
						</tr>
						<?php
					}
				}
				?>
			</table>
			<script>
				var wpeft_ext_position = 0;
				function checkExt() {
					var f   = document.wpeft_form;
					var els = f.elements;
					var i   = 0;
					var m   = els.length;
					var el  = null;
					for (i=0;i<m;i++) {
						el = els[i];
						if (el.name.match(/^custom\_/) && el.value=='') {
							alert('<?php echo esc_js($this->lang['MSG_REQUIREDS']); ?>');
							return false;
						}
					}
					return true;
				}
				function addExt(a,b,c,force_remove) {
					++ wpeft_ext_position;
					var t = document.getElementById('wpeft_ext_table');
					if (!a) { a = ''; }
					if (!b) { b = ''; }
					if (!c) { c = ''; }
					var tr0, td0, td1, td2, td3, i0, i1, i2;
					tr0 = document.createElement('tr');
					tr0.setAttribute('id','wpeft_ext_'+wpeft_ext_position);
					td0 = document.createElement('td');    td1 = document.createElement('td');    td2 = document.createElement('td');
					i0  = document.createElement('input'); i1  = document.createElement('input'); i2  = document.createElement('input');
					i0.setAttribute('type','text');        i1.setAttribute('type','text');        i2.setAttribute('type','text');
					i0.setAttribute('name','custom_d[]');  i1.setAttribute('name','custom_e[]');  i2.setAttribute('name','custom_m[]');
					i0.value = a;                          i1.value = b;                          i2.value = c;
					td0.appendChild(i0); td1.appendChild(i1); td2.appendChild(i2);
					td3 = document.createElement('td'); 
					td3.innerHTML = '';
					td3.innerHTML = td3.innerHTML + '<input type="button" value="+" onclick="addExt(\'\',\'\',\'\',true)"> ';
					if (a || force_remove) {
						td3.innerHTML = td3.innerHTML + ' <input type="button" value="-" onclick="removeExt('+wpeft_ext_position+')"> ';
					}					
					tr0.appendChild(td0); tr0.appendChild(td1); tr0.appendChild(td2); tr0.appendChild(td3);
					t.appendChild(tr0);
				}				
				function removeExt(pos) {
					var x = document.getElementById('wpeft_ext_'+pos);
					x.parentNode.removeChild(x);
				}
			</script>
			<p><b><?php echo esc_html($this->lang['ADD_EXTRAS']); ?></b> <input type="button" value="+" onclick="addExt('','','',true)" /></p>
			<table id="wpeft_ext_table" border="1">
				<tr>
					<td><?php echo esc_html($this->lang['DESCRIPTION']); ?> (*)</td>
					<td><?php echo esc_html($this->lang['EXTENSION']); ?> (*)</td>
					<td><?php echo esc_html($this->lang['MIME_TYPE']); ?> (*)</td>
					<td>&nbsp;</td>
				</tr>
			</table>
			(*) <?php echo esc_html($this->lang['REQUIRED']); ?><br><br>
			<?php foreach ($custom as $element) { ?>
			<script>addExt('<?php echo esc_js($element['description']); ?>','<?php echo esc_js($element['extension']); ?>','<?php echo esc_js($element['mime']);?>');</script>
			<?php } ?>
			<?php submit_button(); ?>
		</form>
		<script>
		    ShowOrHide();
		    setupShowOrHide();
		</script>
		<?php
	}
	
	public function mime($mimes) {
		$nowp = get_option('wpeft_no_wp');
		$usr = $this->_buildList();
		if ($nowp) {
		    $ret =  $usr;
		} else {
		    $ret =  array_merge($mimes,$usr);
		}
		return $ret;
	}
	
	public function mime2($info,$tmpfile,$filename,$mimes) {
		// extra checks to handle situations where "finfo mimetype" is different from "user mimetype"
		$ret = array('ext'=>'','type'=>'','proper_filename'=>'');
		$nostrict = get_option('wpeft_no_strict');
		$nowp = get_option('wpeft_no_wp');
		foreach ($info as $k=>$v) {
			if ($v!=='') {
				$ret[$k] = $v;
			}
		}
		$parts = explode('.',$filename);
		$ext   = array_pop($parts);
		$ext   = strtolower($ext);
		$inwp = $this->_inWP($ext);
		if (!$nowp) {
		    // if the user want to use also WordPress internals (default case) ...
		    if ($inwp || !$nostrict) {
			    // do nothing for WordPress file types or when we do not have to force anything ...
			    return $ret;
		    }
		}
		$usr = $this->_buildList();
		if (isset($usr[$ext])) {
			$ret['ext']  = $ext;
			$ret['type'] = $usr[$ext];
			$ret['proper_filename'] = $filename;
		}
		return $ret;
	}

    public function gf_hack($extensions)
    {
        $gf_hack = get_option('wpeft_gf_hack');
        if (!$gf_hack) {
            return $extensions;
        }
        $ok_list = $this->_buildList();
        foreach ($extensions as $key => $ext) {
            if ($ok_list && isset($ok_list[$ext])) {
                unset($extensions[$key]);
            }
        }
        return $extensions;
    }
    
	protected function _inWP($ext) {
		$ext    = strtolower($ext);
		$wpmime = wp_get_mime_types();
		foreach ($wpmime as $k=>$v) {
			if ($k == $ext) { return true; }
			if (preg_match('#^'.$ext.'\|#',$k))  { return true; }
			if (preg_match('#\|'.$ext.'$#',$k))  { return true; }
			if (preg_match('#\|'.$ext.'\|#',$k)) { return true; }
		}
		return false;
	}
	
	protected function _buildList() {
		$opt = get_option('wpeft_types','');
		if (!$opt) {
			update_option('wpeft_types',$this->defaults());
			$opt = $this->defaults();
		}
		$optc = get_option('wpeft_custom_types','');
		if (!$optc) {
			$optc = array();
		}
		if (!is_array($opt) && is_string($opt)) {
				$opt = array();
		}
		if (!is_array($optc) && is_string($optc)) {
				$optc = array();
		} else {
			$_optc = array();
			foreach ($optc as $c) {
				if (substr($c['extension'],0,1)=='.') {
                    $c['extension'] = substr($c['extension'],1);
                }
				$_optc[ $c['extension'] ] = $c['mime'];
			}
			$optc  = $_optc;
		}
		return array_merge($opt,$optc);
	}
	
	public function init() {
	  ob_start();
	}
}


$wpeft_obj = new \WPEFT();

add_action( 'init', array($wpeft_obj,'init'));

add_action('admin_menu', array($wpeft_obj,'admin'));
add_filter('upload_mimes',array($wpeft_obj,'mime'));

add_filter('wp_check_filetype_and_ext',array($wpeft_obj,'mime2'),10,4);

add_filter('gform_disallowed_file_extensions',array($wpeft_obj,'gf_hack'));

/* add_filter('upload_mimes','add_extra_mime_types');

function add_extra_mime_types($mimes){
	return array_merge($mimes,array (
		// text
		'txt' => 'text/plain',
		// compressed
		'7z'  => 'application/x-7z-compressed',
		'bz2' => 'application/x-bzip2',
		'gz'  => 'application/x-gzip',
		'tgz' => 'application/x-gzip',
		'txz' => 'application/x-xz',
		'xz'  => 'application/x-xz',
		'zip' => 'application/zip'
	));
}
*/
	
?>
