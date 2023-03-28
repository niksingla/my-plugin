<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://niksingla.xyz/
 * @since             1.0.0
 * @package           My_Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       My Plugin
 * Plugin URI:        https://https://niksingla.xyz/
 * Description:       This is a custom WordPress plugin developed by Nikhil Singla. <strong>Use [custom-login-form], [custom-profile], [custom-registration]' on 'login', 'profile' and 'register' pages respectively</strong>. Slugs should be - login, register and profile.
 * Version:           1.0.0
 * Author:            Nikhil Singla
 * Author URI:        https://https://niksingla.xyz/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       my-plugin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('MY_PLUGIN_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-my-plugin-activator.php
 */
function activate_my_plugin()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-my-plugin-activator.php';
	My_Plugin_Activator::activate();
	global $wpdb, $table_prefix;
	$wp_mypages = $table_prefix . 'mypages';
	$q= "CREATE TABLE IF NOT EXISTS `mtest`.`$wp_mypages` (`ID` INT NOT NULL AUTO_INCREMENT , 
	`page_slug` VARCHAR(50) NULL DEFAULT NULL , 
	`my_plugin` VARCHAR(50) NULL DEFAULT NULL , 
	PRIMARY KEY (`ID`)) ENGINE = InnoDB;";
	$wpdb->query($q);
	do_action('create_defaults');
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-my-plugin-deactivator.php
 */
function deactivate_my_plugin(){
	require_once plugin_dir_path(__FILE__) . 'includes/class-my-plugin-deactivator.php';
	My_Plugin_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_my_plugin');
register_deactivation_hook(__FILE__, 'deactivate_my_plugin');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-my-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */


// Custom Functions
function defining_global(){
	global $wpdb,$table_prefix;
	$wp_mypages = $table_prefix.'mypages';
	$results = $wpdb->get_results("SELECT * FROM $wp_mypages;");
	if(count($results)==3 && !is_wp_error($results)){
		foreach($results as $result){
			if($result->page_slug == 'Login') define('LOGIN_ID', $result->my_plugin);
			if($result->page_slug == 'Profile') define('PROFILE_ID', $result->my_plugin);
			if($result->page_slug == 'Register') define('REGISTER_ID', $result->my_plugin);
		}
	}
}
add_action('init','defining_global',1);

//Scripts and styles enqueue commented in includes
function my_scripts(){
	$path = plugins_url('public/', __FILE__);
	$dep = array('jquery');
	wp_enqueue_style('my-plugin-style', $path . 'css/my-plugin-public.css', $ver = '1.0');
	wp_enqueue_script('my-plugin-js', $path . 'js/my-plugin-public.js', $dep, '1.0', true);
	wp_enqueue_script('validation-js', $path . 'js/validation.js', $dep, '1.0', true);
}
add_action('wp_enqueue_scripts', 'my_scripts');

//On activation database creation
function on_plugin_activation_custom(){
	global $wpdb, $table_prefix;
	$wp_mypages = $table_prefix.'mypages';
	$mypages = ['Login', 'Register', 'Profile'];
	$start_time = microtime(true);
	foreach($mypages as $mypage){
		$q = "SELECT * FROM `$wp_mypages` WHERE `page_slug` = '$mypage'";
		$results = $wpdb->get_results($q);
		if(count($results) == 0){
			$wp_posts = $table_prefix.'posts';
			$q="SELECT * FROM `$wp_posts` WHERE `post_name` = '$mypage' AND `post_type` = 'page'";
			if(count($wpdb->get_results($q))==1){
				$mypage_id = $wpdb->get_results($q)[0]->ID;
				$q = "INSERT INTO `$wp_mypages` (`page_slug`, `my_plugin`) VALUES ('$mypage', '$mypage_id')";
				$wpdb->query($q);
			}
			else{
				$mypage_data = array(
					'post_title' => $mypage,
					'post_status' => 'publish',
					'post_type' => 'page',
					'post_name' => $mypage		
				);
				$mypage_id = wp_insert_post($mypage_data);
				if(!is_wp_error($mypage_id)){
					$q = "INSERT INTO `$wp_mypages` (`page_slug`, `my_plugin`) VALUES ('$mypage', '$mypage_id')";
					$wpdb->query($q);
				}
			}
		}
	}
	$time_taken = number_format((float)(microtime(true)-$start_time), 4);
	//print_r($time_taken." seconds");
}
add_action('create_defaults', 'on_plugin_activation_custom');

include 'ajax.php';
// Custom Login page shortcode
function my_login(){
	ob_start();
	include 'public/login.php';
	return ob_get_clean();
}
add_shortcode('custom-login-form', 'my_login');

// Custom profile page shortcode
function my_profile(){
	ob_start();
	include 'public/profile.php';
	return ob_get_clean();
}
add_shortcode('custom-profile', 'my_profile');

//On login Redirect
function my_login_redirect(){
	if (isset($_POST['user_login'])) {
		global $wpdb, $table_prefix;
		$wp_users = $table_prefix.'users';
		$wp_usermeta = $table_prefix.'usermeta';
		$username = esc_sql($_POST['username']);
		$password = esc_sql($_POST['password']);
		$q = "SELECT * FROM `$wp_usermeta` WHERE `meta_key`='phone' AND `meta_value` = '$username'";
		$by_phone = (count($wpdb->get_results($q))>0);
		if($by_phone){
			$user = get_user_by('ID', ($wpdb->get_results($q))[0]->user_id);
			if($user!=false)
			$username = $user->user_login;
		}
		$credentials = array(
			'user_login' => $username,
			'user_password' => $password,
		);
		$user = wp_signon($credentials);
		if (!is_wp_error($user)) {
			$profile_page=get_post_field('post_name',PROFILE_ID,'page'); 
			wp_redirect($profile_page);
			exit;
		} else {

			echo $user->get_error_message();
		}
	}
}
add_action('template_redirect', 'my_login_redirect');

// Custom Registration page shortcode
function my_registration(){
	ob_start();
	include 'public/register.php';
	return ob_get_clean();
}
add_shortcode('custom-registration', 'my_registration');

//Redirect for direct access to a page
function my_redirect_check(){	
	$is_user_logged_in = is_user_logged_in();
	if ($is_user_logged_in == true) {
		$userdata = get_userdata(get_current_user_id());
		$user_role = $userdata->roles;
		if (in_array('administrator', $user_role)) return;

		if (is_page(LOGIN_ID) || is_page(REGISTER_ID)) {
			$profile_page=get_post_field('post_name',PROFILE_ID,'page'); 
			wp_redirect($profile_page);
			exit;
		}
	} else {
		if (is_page(PROFILE_ID)) {
			$login_page=get_post_field('post_name',LOGIN_ID,'page'); 
			wp_redirect($login_page);
		}
	}
}
add_action('template_redirect', 'my_redirect_check');

//Logout Redirect
function logout_redirect(){
	$login_page=get_post_field('post_name',LOGIN_ID,'page'); 
	wp_redirect($login_page);
	exit;
}
add_action('wp_logout', 'logout_redirect');

function run_my_plugin()
{

	$plugin = new My_Plugin();
	$plugin->run();
}
run_my_plugin();