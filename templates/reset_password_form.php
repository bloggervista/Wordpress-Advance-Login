<?php
add_filter('body_class',function($classes){$classes[]="password_reset_form";return $classes;});
add_action( 'wp_print_footer_scripts', function(){
    echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
});

$errors=new WP_Error;

$show_password_reset_form=true;
$reset_key=esc_attr(preg_replace('/a-z0-9/i', '', $_GET['reset_key']));
$user_id=!empty($_GET["user_id"])?esc_attr($_GET["user_id"]):null;;

if(empty($user_id)){
    $errors->add('no_user', __('Please check the link again. Or Rerequest the password reset link', 'Shirshak'));
    $show_password_reset_form=false;
}

if(!empty($user_id) AND !empty($reset_key)){
    $real_reset_key=get_user_meta($user_id,"reset_key",true);
    $requested_time=get_user_meta($user_id,"reset_key_requested_time",true);

    if($real_reset_key == $reset_key){
        $let_him_reset=true;
    }else{
        $errors->add('no_key', __('The given Link is invalid.', 'Shirshak'));
        $show_password_reset_form=false;
    }


    if(function_exists('get_client_ip')){
        $requested_ip=get_user_meta($user_id,"reset_key_requestor_ip",true);
        if(!empty($requested_ip) AND $requested_ip !=get_client_ip()){
            $errors->add('invalid_ip','The reset link requestor ip and your ip don\'t match. Re request password reset link .');
        $show_password_reset_form=false;
        }
    }

    if($requested_time>0 AND ((time()-$requested_time)/3600)>12){
        $errors->add('link_expired','This link has already been expired. Request the reset link again .');
        delete_password_reset_metas($user_id);
        $show_password_reset_form=false;
    }
}

if(!empty($_POST)){
    if (! isset( $_POST['pw_reset_action'] ) || ! wp_verify_nonce( $_POST['pw_reset_action'], 'request_reset_action' ) ) {
       $errors->add('security','There is security problem . Retry else contact admins.');
    }
    if(empty($_POST['user_password'])){
        $errors->add('reenter_password','Please Enter Your Password.');
        if(strlen($_POST['user_password'])<8){
            $errors->add('password_length','Password must be minimum 8 characters.');
        }
    }
    if(empty($_POST['user_repassword'])){
        $errors->add('reenter_repassword','Please Enter Your Password in Reenter Password Field.');
    }
    if(!empty($_POST['user_password']) AND !empty($_POST['user_repassword'])){
        $password=$_POST['user_password'];
        $repassword=$_POST['user_repassword'];

        if($password!=$repassword){
            $errors->add('password_match','Both Passwords must match with each other.');
        }
    }
    if(function_exists('verify_recaptcha')){
        if(!verify_recaptcha()){
            $errors->add('invalid_captcha', __('Please check the Captcha. Are you robot?', 'Shirshak'));
        }
    };
    if(is_wp_error( $errors) AND empty( $errors->errors )){
        if(isset($let_him_reset) AND isset($user_id) AND isset($password)){
            wp_set_password( $password, $user_id );
            if(function_exists('mail_password_reseted')){
                mail_password_reseted($user_id);
            }
            $show_password_reset_form=false;
            delete_password_reset_metas($user_id);
            wp_redirect("/login?password_changed=yes");
        }else{
            $errors->add('something_is_missing', __('Something is Missing. Retry to reset.', 'Shirshak'));
        }
    }
}
get_header();
?>
<div class="grid group">
    <div class=" grid-2-3">
        <div class="module">
            <h2><?php _e("Request Email Verification Link", 'Shirshak'); ?> at <?php bloginfo('name'); ?></h2>
            <hr>

            <?php require_once("small_navigation.php"); ?>

            <?php if (is_wp_error( $errors) AND !empty( $errors->errors )): ?>
                <div class="important red"> <ul>
                    <?php foreach($errors->get_error_messages() as $error):?>
                    <li><strong>Error</strong>: <?php echo $error;?></li>
                    <?php endforeach;?>
                </ul></div>
            <?php endif; ?>

            <?php if(!empty($show_password_reset_form)):?>
                <blockquote>If you have lost your password then fill this form and we will email you. In email you get a link and after you clicked the password reset link we will change your password and email password to you.</blockquote>
                
                <form action="" method="post" class="respond">
                    <?php wp_nonce_field( 'request_reset_action', 'pw_reset_action' ); ?>

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

                    <input type="submit" name="submit" id="submit" value="<?php _e('Reset Password','Shirshak'); ?>" class="button" />
                </form>
            <?php endif;?>

        </div>
    </div>
    <?php require_once("sidebar.php") ?>
</div>
<?php get_footer();?>