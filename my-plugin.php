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


//Ajax Delete User by admin
add_action('wp_ajax_my_plugin_delete_user_by_admin', 'my_plugin_delete_user_by_admin');
function my_plugin_delete_user_by_admin(){
	if(wp_delete_user((int)$_POST['user']))
	echo json_encode(array('status'=>true));
	wp_die();
}

add_action('wp_ajax_delete_user', 'delete_user');
function delete_user(){
	$user = $_POST['user'];
	$userdata = get_userdata($user);
	if (in_array('administrator', $userdata->roles)) {
		$del = new stdClass();
		$del->status = false;
		echo (json_encode($del));
	} else {
		if (wp_delete_user($user)) {
			$del = new stdClass();
			$del->status = true;
			echo (json_encode($del));
		} else {
			$del = new stdClass();
			$del->status = false;
			echo (json_encode($del));
		}
	}
	wp_die();
}

//Ajax Update User by admin
add_action('wp_ajax_update_user', 'update_user');
function update_user(){
	global $wpdb;
	$wp_users = ($wpdb->prefix) . 'users';
	$user = (int)$_POST['user'];
	$display_name = esc_sql($_POST['name']);
	$user_login = esc_sql($_POST['username']);
	$user_pass = esc_sql($_POST['password']);
	$user_email = esc_sql($_POST['email']);

	$q = "SELECT * from $wp_users WHERE `ID` = $user";
	$prev_username = (($wpdb->get_results($q))[0])->user_login;
	if ($prev_username != $user_login) {
		$wpdb->query("UPDATE $wp_users SET user_login = '$user_login' WHERE ID = $user");
	}
	$update_data = array(
		'ID' => $user,
		'user_email' => $user_email,
		'user_pass' => $user_pass, 
		'display_name' => $display_name,
	);
	$up = wp_update_user($update_data);
	$upd = new stdClass();
	if (!is_wp_error($up)) {
		$upd->status = true;
		echo json_encode($upd);
	} else {
		$upd->status = false;
		echo json_encode($upd);
	}
	wp_die();
}

//Ajax update user from profile
add_action('wp_ajax_update_user_profile', 'update_user_profile');
function update_user_profile(){
	$user_id = $_POST['user'];
	$fname = esc_sql($_POST['fname']);
	$lname = esc_sql($_POST['lname']);
	$username = esc_sql($_POST['username']);
	$email = esc_sql($_POST['email']);
	$phone = esc_sql($_POST['tel']);
	$dob = esc_sql($_POST['dob']);
	$about = esc_sql($_POST['about']);
	$pic = $_FILES['file'];
	if($pic['error']==0){
		$ext = explode('/', $pic['type'])[1];
		$file_name = "$user_id.$ext";
		if(!metadata_exists('user', $user_id, 'user_profile_pic_url')){
			$image = wp_upload_bits($file_name, null, file_get_contents($pic['tmp_name']));
			add_user_meta($user_id, 'user_profile_pic_url', $image['url']);
			add_user_meta($user_id, 'user_profile_pic_path', esc_sql($image['file']));	
		}
		else{
			$pic_path = get_user_meta($user_id, 'user_profile_pic_path')[0];
			wp_delete_file($pic_path);
			$image = wp_upload_bits($file_name, null, file_get_contents($pic['tmp_name']));
			update_user_meta($user_id, 'user_profile_pic_url', $image['url']);
			update_user_meta($user_id, 'user_profile_pic_path', esc_sql($image['file']));	
		}
	}
	$prev_username = esc_sql($_POST['current_user']);
	$updated_data = array(
		'ID' => $user_id,
		'user_email' => $email,
		'first_name' => $fname,
		'last_name' => $lname,
		'display_name' => $fname.' '.$lname,
	);
	$update = wp_update_user($updated_data); 
	if(is_wp_error($update)){
		echo json_encode(array('status'=>false, 'message'=>$update->get_error_message()));
	}
	else{
		if($prev_username!=$username){
			global $wpdb, $table_prefix;
            $wp_users = $table_prefix.'users';
            $wpdb->query("UPDATE $wp_users SET user_login = '$username' WHERE ID = $user_id");
        }
		echo json_encode(array('status'=>true));
	}
	update_user_meta($user_id, 'phone', esc_sql($phone));
	update_user_meta($user_id, 'dob', esc_sql($dob));
	update_user_meta($user_id, 'about', esc_sql($about));
	wp_die();
}

//Ajax registration form
add_action('wp_ajax_my_plugin_register', 'my_plugin_register');
add_action('wp_ajax_nopriv_my_plugin_register', 'my_plugin_register');
function my_plugin_register(){
	global $wpdb, $table_prefix;	
	$fname = esc_sql($_POST['fname']);
	$lname = esc_sql($_POST['lname']);
	$username = esc_sql($_POST['username']);
	$email = esc_sql($_POST['email']);
	$phone = esc_sql($_POST['tel']);	
	$usermeta = $table_prefix.'usermeta';
	$q = "SELECT * FROM `$usermeta` WHERE `meta_key`='phone' AND `meta_value` = '$phone'"; //To get existing phones
    $is_phone_exists = count($wpdb->get_results($q))>0;
	$dob = esc_sql($_POST['dob']);
	$pic = $_FILES['file'];
	$about = esc_sql($_POST['about']);
	$pass = esc_sql($_POST['password']);
    $con_pass = esc_sql($_POST['con_password']);
	if($pass != $con_pass){
        echo json_encode(array('status'=>false,'error'=>'Passwords doesn\'t match!!') );
    }else{
		if(!$is_phone_exists){
			$user_data = array(
				'user_login' => $username,
				'user_email' => $email,
				'first_name' => $fname,
				'last_name' => $lname,
				'display_name' => $fname.' '.$lname,
				'user_pass' => $pass,
			);
			$result = wp_insert_user($user_data);
			$user_id = $result;
            if(is_wp_error($result)){
                echo json_encode(array('status'=>false, 'error'=>$result->get_error_message()));
				wp_die();
            }
            else{
                add_user_meta($result, 'phone', esc_sql($phone), $unique=true);
                add_user_meta($result, 'dob', esc_sql($dob)); 
				add_user_meta($result, 'about', esc_sql($about)); 
                $test = update_user_meta($result, 'show_admin_bar_front', false );
				if($pic['error']==0){
					$ext = explode('/', $pic['type'])[1];
					$file_name = "$user_id.$ext";
					$image = wp_upload_bits($file_name, null, file_get_contents($pic['tmp_name']));
					add_user_meta($user_id, 'user_profile_pic_url', $image['url']);
					add_user_meta($user_id, 'user_profile_pic_path', esc_sql($image['file']));	
				}
            }
		$credentials = array(
			'user_login' => $username,
			'user_password' => $pass
		);
		$user = wp_signon($credentials);
		if(!is_wp_error($user)){
			echo json_encode(array('status'=>true, 'user'=>$user, 'test', $test));
			wp_die();
		}
		}
		else{
            echo json_encode(array('status'=>false, 'error'=>"This phone number already exists!"));
			wp_die();
        }
	}
	wp_die();
}


function run_my_plugin()
{

	$plugin = new My_Plugin();
	$plugin->run();
}
run_my_plugin();