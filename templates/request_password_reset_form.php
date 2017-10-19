<?php
add_filter('body_class',function($classes){$classes[]="request_password_reset_key";return $classes;});
add_action( 'wp_print_footer_scripts', function(){
    echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
});

$errors=new WP_Error;
if(!empty($_POST)){
    if (! isset( $_POST['pw_reset_key_action'] ) || ! wp_verify_nonce( $_POST['pw_reset_key_action'], 'request_reset_key_action' ) ) {
       $errors->add('security','There is security problem . Retry else contact admins.');
    }
    if(empty($_POST['user_email'])){
        $errors->add('empty_email','Please Enter the Username Or Email Address.');
    }
    if(function_exists('verify_recaptcha')){
        if(!verify_recaptcha()){
            $errors->add('invalid_captcha', __('Please check the Captcha. Are you robot?', 'Shirshak'));
        }
    };
    if(is_wp_error( $errors) AND empty( $errors->errors )){

        $user_email = trim($_POST['user_email']);

        if(is_email($user_email)){
            $user_id=email_exists($user_email );
        }else{
            $user_id=username_exists($user_email );         
        }

        if($user_id){
            $last_requested_time=(int)get_user_meta($user_id,"reset_key_requested_time",true);
            $password_reset_lock=get_user_meta($user_id,"password_reset_lock",true);
            if(!empty($last_requested_time)):
                if(((time()-$last_requested_time)/3600)<6){
                    $errors->add('cannot_request_early','You have already requested password reset link. For new reset link try after 12 Hour');
                }
            endif;
            if($password_reset_lock){
                $errors->add('reset_lock','Our Security have locked this user from requesting new link.');
            }

            if(is_wp_error( $errors) AND empty( $errors->errors ) ):
                if(mail_reset_key($user_id)=='done'){
                    $sent_email=true;
                }else{
                    $errors->add('cannot_email','Sorry we couldn\'t mail you. Please try again.');
                }
            endif;
        }else{
            $invalid_username=true;
            $errors->add('empty_email','Email Or Username is invalid.');
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

            <?php if(empty($sent_email)):?>
                <blockquote>If you have lost your password then fill this form and we will email you. In email you get a link and after you clicked the password reset link we will change your password and email password to you.</blockquote>
                
                <form action="" method="post" class="respond">
                    <?php wp_nonce_field( 'request_reset_key_action', 'pw_reset_key_action' ); ?>
                    <div class="form-input">
                        <label for="user_email" class="image-replace signin-username"><?php _e('Username Or Email', 'Shirshak'); ?></label>
                        <input class="full-width paddingleft50" type="text" name="user_email" id="user_email" value="<?php echo !empty($_POST['user_email'])?esc_attr($_POST['user_email']):""; ?>" placeholder="Enter Your Email Address"/>
                    </div>
                     <div class="recaptcha-container">
                        <div class="g-recaptcha" data-sitekey="<?php echo get_option( 'shirshak_theme_option' )['recaptcha_site_key']; ?>"></div>
                    </div>
                    <input type="submit" name="submit" id="submit" value="<?php _e('Retrieve Reset Link','Shirshak'); ?>" class="button" />
                </form>
            <?php else: ?>
                <blockquote>We have sent a reset link to provided email address. Be sure to check in Spam.</blockquote>
            <?php endif; ?>

        </div>
    </div>
    <?php require_once("sidebar.php") ?>
</div>
<?php get_footer();?>