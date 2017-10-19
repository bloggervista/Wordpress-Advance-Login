<?php 
add_filter('body_class',function($classes){$classes[]="registration_page"; return $classes; });
add_action( 'wp_print_footer_scripts', function(){
    echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
});

$errors =new WP_Error;
if (!empty($_POST)) {
    $user_login = !empty($_POST['user_login']) ?sanitize_user(str_replace(" ", "", $_POST['user_login'])):null;
    $user_email = !empty($_POST['user_email']) ? trim($_POST['user_email']) : null;
    $user_password = !empty($_POST['user_password']) ? trim($_POST['user_password']) : null;
    $user_repassword = !empty($_POST['user_repassword']) ? trim($_POST['user_repassword']) : null;

    if (! isset( $_POST['register_name'] ) || ! wp_verify_nonce( $_POST['register_name'], 'register_action' ) ) {
       $errors->add('security','There is security problem . Retry to register else contact admins.');
    }

    if(function_exists('verify_recaptcha')){
        if(!verify_recaptcha()){
            $errors->add('invalid_captcha', __('Please check the Captcha. Are you robot?', 'Shirshak'));
        }
    };

    if (empty($user_login)) {
        $errors->add('empty_username', __('Please enter a valid username.', 'Shirshak'));
    }elseif (!validate_username($user_login)) {
        $errors->add('invalid_username', __('This username is invalid because it uses illegal characters. Please enter a valid username.', 'Shirshak'));
    }elseif (username_exists($user_login)) {
        $errors->add('username_exists', __('This username is already registered, please choose another one.', 'Shirshak'));
    }

    if (empty($user_email)) {
        $errors->add('empty_email', __('Please type your e-mail address.', 'Shirshak'));
    }elseif (!is_email($user_email)) {
        $errors->add('invalid_email', __('The email address isn&#8217;t correct.', 'Shirshak'));
    }elseif (email_exists($user_email)) {
        $errors->add('email_exists', __('This email is already registered, please choose another one.', 'Shirshak'));
    }

    if (empty($user_password)) {
        $errors->add('empty_password', __('Please type password.', 'Shirshak'));
    }elseif (strlen($user_password) < 6) {
        $errors->add('invalid_password', __('Password must be at least 6 charater long.', 'Shirshak'));
    }

    if($user_password!=$user_repassword){
        $errors->add('passwords_not_maching', __('Both Password must be same.', 'Shirshak'));
    }
    $salt = wp_generate_password(20); // 20 character "random" string
    $key = sha1($salt . $user_email . uniqid(time(), true));

    if(is_wp_error( $errors) AND empty( $errors->errors )){
        $user_data=[
        'user_login'=>$user_login,
        'user_pass'=>$user_password,
        'user_email'=>$user_email,
        ];
        $user_id = wp_insert_user($user_data);
        add_user_meta( $user_id, "new_user",true, true );
        add_user_meta( $user_id, "email_activation_key", $key, true );
        if ($user_id && !is_wp_error( $user_id )) {
            if(new_user_registration_email($user_id)=="not_sent")
                 $errors->add('mail_not_sent','Sorry we could not email you activation link . Please retry.');
            $registration_completed = "done";
        }else{
            $errors->add('registerfail', sprintf(__('Couldn&#8217;t register you... please contact the  admins !', 'Shirshak') , get_option('admin_email')));
        }
    }
    
}
get_header();
?>
<div class="grid group">
    <div class=" grid-2-3">
        <div class="module">
            <?php if(empty($registration_completed)):?>
                <h2><?php _e("Register", 'Shirshak'); ?> at <?php bloginfo('name'); ?></h2>
                <hr>
                <?php require_once("small_navigation.php"); ?>

                <?php if (is_wp_error( $errors) AND !empty( $errors->errors )): ?>
                    <div class="important red"> <ul>
                        <?php foreach($errors->get_error_messages() as $error):?>
                        <li><strong>Error</strong>: <?php echo $error;?></li>
                        <?php endforeach;?>
                    </ul></div>
                <?php endif; ?>


                <form id="signupform" action="" method="post" class="respond">
                    <?php wp_nonce_field( 'register_action', 'register_name' ); ?>

                    <div class="form-input">
                        <div class="form-input">
                             <label for="user_login" class="image-replace signin-username"><?php _e('Username', 'Shirshak') ?></label>
                            <input class="full-width paddingleft50" type="text" class="do_input" name="user_login" id="user_login" value="<?php echo !empty($user_login)?esc_html($user_login):""; ?>" placeholder="Username"/>
                        </div>
                    </div>
                    <div class="form-input">
                        <div class="form-input">
                            <label for="user_email" class="image-replace signin-email"><?php _e('Email Address', 'Shirshak'); ?></label>
                            <input class="full-width paddingleft50" type="text" name="user_email" id="user_email" value="<?php echo !empty($user_email)?esc_html($user_email):""; ?>" placeholder="Email Address"/>
                        </div>
                    </div>
                    <div class="form-input">
                        <div class="form-input">
                            <label for="user_password" class="image-replace signin-password"><?php _e('Password', 'Shirshak'); ?></label>
                            <input class="full-width paddingleft50" type="password" name="user_password" id="user_password" value="<?php echo !empty($user_password)?esc_html($user_password):""; ?>" placeholder="Enter Password"/>
                        </div>
                    </div>
                    <div class="form-input">
                        <div class="form-input">
                            <label for="user_repassword" class="image-replace signin-password"><?php _e('Repassword', 'Shirshak'); ?></label>
                            <input class="full-width paddingleft50" type="password" name="user_repassword" id="user_repassword" value="<?php echo !empty($user_repassword)?esc_html($user_repassword):""; ?>" placeholder="Renter Password"/>
                        </div>
                    </div>
                    <div class="recaptcha-container">
                        <div class="g-recaptcha" data-sitekey="<?php echo get_option( 'shirshak_theme_option' )['recaptcha_site_key']; ?>"></div>
                    </div>

                    <hr>

                    <div class="explanation">By clicking the register button you agree with our <a href="/privacy-policy">privacy policy</a>. And by the way we never reveal your information. We are here to educate people.</div>

                    <input type="submit" name="submit" class="button" id="submit" value="<?php _e('Register', 'Shirshak') ?>"/>
                        
                     
                </form>
            <?php else: ?>
                <h2><?php _e("Registration Completed", 'Shirshak'); ?></h2>
                <hr>
                <?php require_once("small_navigation.php"); ?>
                <div class="explanation red">Activate your account through email so that you can enjoy our facilites as fast as possible.</div>
                <blockquote>Congratulations ! You are sucessfully registered at our site. If you have any problem be sure to contact with us. Print the below informations as they may be valuable in future.</blockquote>
                <p><?php printf(__('Username:   %s', 'Shirshak') , "<strong>" . esc_html($user_login) . "</strong>") ?></p>
                <p><?php printf(__('E-mail  :   %s', 'Shirshak') , "<strong>" . esc_html($user_email) . "</strong>") ?></p>
                <p>You can try to login at <a href="/login">here</a></p>
                <div class="explanation"><?php _e("Please check your <strong>Junk Mail</strong> if your account information does not appear within 5 minutes.", 'Shirshak'); ?></div>
                <?php endif;?>       
        </div>
    </div>
    <?php require_once("sidebar.php") ?>
</div>
<?php get_footer();?>