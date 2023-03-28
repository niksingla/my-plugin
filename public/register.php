<?php
if (!defined('ABSPATH')) {
    exit;
}

?>
<myplugin>
    <h1>Register</h1>
    <p>Already having an account? <span><a href="<?php echo site_url('login'); ?>">Login</a></span></p>
    <form class="form-control" action="<?php echo admin_url('admin-ajax.php'); ?>" method="post" id="registration-form">
        <div class="form-group">
            <label for="fname">First Name</label>
            <input class="form-control" type="text" name="fname" id="register-fname" required>
            <div class="invalid-feedback" id="fnameError"></div>
        </div>
        <div class="form-group">
            <label for="lname">Last Name</label>
            <input class="form-control" type="text" name="lname" id="register-lname" required>
            <div class="invalid-feedback" id="lnameError"></div>
        </div>
        <div class="form-group">
            <label for="username">Username</label> 
            <input class="form-control" type="text" name="username" id="register-username" required>
            <div class="invalid-feedback" id="usernameError"></div>
        </div>
        <div class="form-group">
            <label for="email">Email address</label>
            <input class="form-control" type="email" name="email" id="register-email" required>
            <div class="invalid-feedback" id="emailError"></div>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input class="form-control" type="password" name="password" id="register-password" required>
            <div class="invalid-feedback" id="passwordError"></div>
        </div>
        
        <div class="form-group">
            <label for="con_password">Confirm Password</label>
            <input class="form-control" type="password" name="con_password" id="con-password" required>
            <div class="invalid-feedback" id="con_passwordError"></div>
        </div>
        
        <div class="form-group">
            <label for="tel">Phone Number</label>
            <input class="form-control" ng-minlength="10" ng-maxlength="10" type="tel" name="tel" id="register-phone" required>
            <div class="invalid-feedback" id="telError"></div>
        </div>
        <div class="form-group">
            <label for="dob">Date of Birth</label>
            <input class="form-control" type="date" name="dob" id="register-dob" required>
            <div class="invalid-feedback" id="emailError"></div>
        </div>
        <img src="" alt="" width="100" height="100" id="profile-pic" style="object-fit:cover;"> <br>
        <div class="form-group">
            <label for="file">Profile Picture</label>
            <input type="file" id="profile-img" name="file" accept="image/*" enctype="multipart/form-data">
            <div class="invalid-feedback" id="fileError"></div>
        </div>
        <div class="form-group">
            <label for="about">About Yourself</label>
            <textarea type="text" name="about" id="register-about" style="width: fit-content;"></textarea>
            <div class="invalid-feedback" id="aboutError"></div>
        </div>
        <input type="submit" name="register" value="Register">
    </form>
    <script src="https://cdn.jsdelivr.net/npm/pristinejs@1.0.0/dist/pristine.min.js" type="text/javascript"></script>
</myplugin>