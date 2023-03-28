<?php
if (!defined('ABSPATH')) {
    exit;
}
if (isset($_POST['user_login'])) {
    if (is_user_logged_in()) echo 'Already Logged In...!';
}
?>
<myplugin>
    <h1>Login</h1>
    <p>Don't having an account? <span><a href="<?php echo site_url('register'); ?>">Register</a></span></p>
    <form action="<?php echo get_the_permalink(); ?>" id="login-form" method="post">
        Username: <input required type="text" name="username" id="login-username"> <br>
        Password: <input required type="password" name="password" id="login-password"> <br>
        <input type="submit" name="user_login" value="Login">
    </form>
    <script src="https://cdn.jsdelivr.net/npm/pristinejs@1.0.0/dist/pristine.min.js" type="text/javascript"></script>
</myplugin>