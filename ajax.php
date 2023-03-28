<?php 

if (!defined('ABSPATH')) {
	die;
}

//Validate username/email/phone on input
add_action('wp_ajax_ajax_validate_exists', 'ajax_validate_exists');
add_action('wp_ajax_nopriv_ajax_validate_exists', 'ajax_validate_exists');
function ajax_validate_exists(){
    if(isset($_POST['username'])){
        $username = $_POST['username'];
        if(username_exists($username)){
            echo json_encode(array('status'=>true));
            wp_die();
        };
        echo json_encode(array('status'=>false));
        wp_die();
    }
    else if(isset($_POST['email'])){
        $email = $_POST['email'];
        if(email_exists($email)){
            echo json_encode(array('status'=>true));
            wp_die();
        }
        echo json_encode(array('status'=>false));
        wp_die();
    }
}


/*Ajax update user from profile*/
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
/*Ajax registration form*/
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
?>