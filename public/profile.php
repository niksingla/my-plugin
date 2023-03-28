<?php
if (!defined('ABSPATH')) {
    exit;
}
if (is_user_logged_in()) :
    global $wpdb, $table_prefix;
    $user_id = get_current_user_id();
    $user_data = get_userdata($user_id);
    $is_admin = in_array('administrator', $user_data->roles);

?>
    <myplugin>
        <h1>Profile</h1>

        <h1>Hi, <?php echo ((get_userdata($user_id))->display_name != "") ? (get_userdata($user_id))->display_name : (get_userdata($user_id))->user_login; ?>
            <?php
            if ($is_admin) {
                echo '(admin)';
            }
            ?>
            <span style="font-size:20px;"><small><a href="<?php echo wp_logout_url() ?>">(Logout?)</a></small></span>
        </h1>
        <hr>
        <?php if (!$is_admin) : ?>
            <h2>Profile Information</h2>

            <form action="<?php echo get_the_permalink(); ?>" method="post" id="profile-update" update-url="<?php echo admin_url('admin-ajax.php'); ?>">
                <input type="hidden" id="user-info" name="current_user" value="<?php echo $user_data->user_login; ?>" user-id="<?php echo $user_id; ?>">
                <img src="<?php if (metadata_exists('user', $user_id, 'user_profile_pic_url')) echo get_user_meta($user_id, 'user_profile_pic_url')[0]; ?>" alt="" id="profile-pic" width="100" height="100" style="object-fit:cover;"><br>
                <input type="file" id="profile-img" name="file" accept="image/*"> <br>
                First Name: <input type="text" name="fname" id="profile-fname" value="<?php echo $user_data->first_name; ?>" <?php if ($is_admin) echo 'disabled'; ?> required>
                Last Name: <input type="text" name="lname" id="profile-lname" value="<?php echo $user_data->last_name; ?>" <?php if ($is_admin) echo 'disabled'; ?> required> <br>
                Username: <input type="text" name="username" id="profile-username" value="<?php echo $user_data->user_login; ?>" <?php if ($is_admin) echo 'disabled'; ?> required> <br>
                Email address: <input type="email" name="email" id="profile-email" value="<?php echo $user_data->user_email; ?>" <?php if ($is_admin) echo 'disabled'; ?> required> <br>
                Phone Number: <input type="tel" name="tel" id="profile-phone" value="<?php echo $user_data->phone; ?>" <?php if ($is_admin) echo 'disabled'; ?> required> <br>
                Date of Birth: <input type="date" name="dob" id="profile-dob" value="<?php echo $user_data->dob; ?>" <?php if ($is_admin) echo 'disabled'; ?> required> <br>
                About Yourself: <textarea type="text" name="about" id="profile-about" style="width: fit-content;"><?php echo $user_data->about; ?></textarea> <br>
                <?php if (true) : ?>
                    <input type="submit" name="update_profile" value="Update">
                <?php endif; ?>
            </form>
        <?php endif; ?>
        <script src="https://cdn.jsdelivr.net/npm/pristinejs@1.0.0/dist/pristine.min.js" type="text/javascript"></script>
        <?php if (!$is_admin) : ?>
            <div>
                <p>Want to delete this account?
                    <a class="delete-user" user="<?php echo $user_id; ?>" href="<?php echo admin_url('admin-ajax.php') ?>">
                        Delete
                    </a>
                </p>
            </div>
        <?php endif; ?>
        <?php if ($is_admin) :
            global $wpdb;
            $wp_users = '' . $wpdb->prefix . 'users';
            $q = "SELECT * FROM $wp_users";
            $results = $wpdb->get_results($q);
            if (count($results) > 1) : ?>
                <div class="registered-users">
                    <h3>Registered Users</h3>
                    <table>
                        <tbody>
                            <tr>
                                <th>
                                    ID
                                </th>
                                <th>
                                    Name
                                </th>
                                <th>
                                    Username
                                </th>
                                <th>
                                    Email
                                </th>
                                <th>
                                    Password
                                </th>
                                <th>
                                    <button type="reset" onclick="location.reload();">Reload</button>
                                </th>
                                <th>
                                    Delete
                                </th>
                            </tr>
                            <?php foreach ($results as $row) :
                                $is_row_admin = in_array('administrator', get_userdata((int)$row->ID)->roles);
                                if (!$is_row_admin) :
                            ?>
                                    <tr user="<?php echo $row->ID ?>">
                                        <td>
                                            <?php echo $row->ID ?>.
                                        </td>
                                        <td>
                                            <input type="text" name="name" value="<?php echo $row->display_name ?>">
                                        </td>
                                        <td>
                                            <input type="text" name="username" value="<?php echo $row->user_login ?>">
                                        </td>
                                        <td>
                                            <input type="email" name="email" value="<?php echo $row->user_email ?>">
                                        </td>
                                        <td>
                                            <input type="password" name="password" placeholder="New Password">
                                        </td>
                                        <td>
                                            <input type="submit" user="<?php echo $row->ID ?>" update-url="<?php echo admin_url('admin-ajax.php') ?>" value="Update">
                                        </td>
                                        <td>
                                            <a href="<?php echo admin_url('admin-ajax.php') ?>" class="delete-user-admin">X</a>
                                        </td>
                                    </tr>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </tbody>

                    </table>
                </div>
        <?php endif;
        endif; ?>
    </myplugin>
<?php
endif;
?>