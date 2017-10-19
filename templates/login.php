<?php
add_filter('body_class',function($classes){$classes[]="login_page";return $classes;});
add_action( 'wp_print_footer_scripts', function(){
    echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
});

if (isset($_REQUEST['redirect_to'])) {
    $redirect_to = $_REQUEST['redirect_to'];
} else {
    $redirect_to = '/user/dashboard';
}
if (is_user_logged_in()) {
    wp_redirect("/user");
    die();
}
$errors=new WP_Error;
if(!empty($_POST)){
    if (! isset( $_POST['login_name'] ) || ! wp_verify_nonce( $_POST['login_name'], 'login_action' ) ) {
       $errors->add('security','There is security problem . Retry to login else contact admins.');
    }
    if (empty($_POST['user_login'])) {
        $errors->add('username','The username field is empty.');
    }else{
        $user_login=stripslashes(trim($_POST['user_login'])) ;
        if(is_email($user_login)){
            $user_id=email_exists($user_login );
        }else{
            $user_id=username_exists($user_login );         
        }
        if(empty($user_id)){
            $errors->add('incorrect_username','Username or password you supplied is incorrect.');
        }else{
            $get_login_try=(int)get_user_meta($user_id,"login_try",true);
            update_user_meta( $user_id, "login_try",$get_login_try+1);


            if(isset($get_login_try) AND $get_login_try>=10){
                $time=time();
                $get_login_locked_time=(int)get_user_meta($user_id,"login_locked_time",true);
                if($get_login_locked_time==0){
                    update_user_meta($user_id, "login_locked_time",time());
                }
                if($get_login_locked_time!=0){
                    $time_in_min=intval((time()-$get_login_locked_time)/60);
                }else{
                    $time_in_min=0;
                }

                if($time_in_min<20){
                    $errors->add('wrong_password_many_times', __(sprintf("You have submitted wrong password many many times. So you cannot login for %d minutes.",20-$time_in_min), 'Shirshak'));
                }
                if($time_in_min>=20){
                    delete_user_meta( $user_id, "login_try");
                    delete_user_meta( $user_id, "login_locked_time");
                }
            }

            if(isset($get_login_try) AND $get_login_try>=4){
                $show_captcha=true;
                if(function_exists('verify_recaptcha')){
                    if(!verify_recaptcha()){
                        $errors->add('invalid_captcha', __('You have submitted wrong password many times. So you need to verify captcha first .', 'Shirshak'));
                    }
                };
            }


            $activation_key=get_user_meta($user_id,'email_activation_key', true);
            if(!empty($activation_key)){
                if($activation_key!="ACTIVATED") $errors->add('not_verified','You need to verify your email before sign in . Check your email . You can rerequest email verification link from <a href="'.REQUEST_EMAIL_ACTIVATION_URL.'">here.');
            };

        }
    }
    if (empty($_POST['user_password'])) {
        $errors->add('password','The password field is empty.');
    }

    if(is_wp_error( $errors) AND empty( $errors->errors )){
        $user_login = !empty($_POST['user_login']) ? sanitize_user($_POST['user_login']) : null;
        $user_pass = !empty($_POST['user_password']) ? $_POST['user_password'] : null;
        $rememberme = !empty($_POST['rememberme']) ? true : false;

        $creds = [];
        $creds['user_login'] = $user_login;
        $creds['user_password'] = $user_pass;
        $creds['remember'] = $rememberme;
        $user = wp_signon($creds, false);

        if (!is_wp_error($user)) {
            delete_user_meta( $user->ID, "login_try");
            delete_user_meta( $user->ID, "login_locked_time");
            wp_set_auth_cookie($user->ID, $rememberme);
            wp_redirect($redirect_to);
            die();
        }else{
            $errors->add('invalid_username_or_password','Username or password you supplied is incorrect.');
        }
    }    
}
get_header();
?>
<div class="grid group">
    <div class=" grid-2-3">
        <div class="module">
            <h2><?php _e("Sign In", 'Shirshak'); ?> at <?php bloginfo('name'); ?></h2>
            <hr>
            <?php require_once("small_navigation.php"); ?>

            <?php if (isset($_GET['password_changed']) && $_GET['password_changed'] == "yes"):?>
            <div class="explanation">
                <?php _e('Ok your Password has been changed. Now you login.', 'Shirshak'); ?>                    
            </div>              
            <?php endif; ?>
    

            <?php if (is_wp_error( $errors) AND !empty( $errors->errors )): ?>
                <div class="important red"> <ul>
                    <?php foreach($errors->get_error_messages() as $error):?>
                    <li><strong>Error</strong>: <?php echo $error;?></li>
                    <?php endforeach;?>
                </ul></div>
            <?php endif; ?>
            
            <form action="" method="post" class="respond">
                <?php wp_nonce_field( 'login_action', 'login_name' ); ?>
                <div class="form-input">
                    <label for="user_login" class="image-replace signin-username"><?php _e('Username', 'Shirshak') ?></label>
                    <input class="full-width paddingleft50" type="text" class="do_input" name="user_login" id="user_login" value="<?php echo !empty($_POST['user_login'])?esc_attr($_POST['user_login']):""; ?>" placeholder="Username"/>
                </div>
                <div class="form-input">
                    <label for="user_password" class="image-replace signin-password"><?php _e('Password', 'Shirshak'); ?></label>
                    <input class="full-width paddingleft50" type="password" name="user_password" id="user_password" value="<?php echo !empty($_POST['user_password'])?esc_attr($_POST['user_password']):""; ?>" placeholder="Enter Password"/>
                </div>
                <div class="form-input">
                    <label for="rememberme"><?php _e('Keep me logged in', 'Shirshak'); ?>
                    <input type="checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" <?php echo !empty($_POST['rememberme'])?"checked":"";?>>
                </div>
                
                <?php if(isset($show_captcha)):?>
                    <div class="recaptcha-container">
                        <div class="g-recaptcha" data-sitekey="<?php echo get_option( 'shirshak_theme_option' )['recaptcha_site_key']; ?>"></div>
                    </div>
                <?php endif;?>

                <input type="submit" name="submit" class="button" id="submit" value="<?php _e('Sign In', 'Shirshak') ?>"/>
            </form>

            <hr>

            <p>If you want to re request the email activation link then you can get it from <a href="<?php echo REQUEST_EMAIL_ACTIVATION_URL; ?>">here</a></p>  
        </div>
    </div>
    <?php require_once("sidebar.php") ?>
</div>
<?php get_footer();?>