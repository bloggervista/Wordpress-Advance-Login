<?php
add_filter('body_class',function($classes){$classes[]="email_activation_page";return $classes;});
$errors=new WP_Error;
$user_id=esc_attr(htmlspecialchars($_GET["user_id"]));
$email_activation_key=esc_attr(htmlspecialchars($_GET["email_activation_key"]));

if(!empty($user_id) AND !empty($email_activation_key)):
    $db_email_activation_key=get_user_meta($user_id,"email_activation_key",true);
    if(!empty($db_email_activation_key) AND $db_email_activation_key!="ACTIVATED"){
        if($db_email_activation_key==$email_activation_key){
            if(update_user_meta($user_id,"email_activation_key","ACTIVATED",$email_activation_key)){
                $errors->add('activated','Your Email Address has been activated. Now you can login easily login from <a href="/login">here</a>');
            }else{
                $errors->add('key_problem_update','<strong>Error</strong>:  There is problem with activation key. Sorry !!!');
            }
        }else{
            $errors->add('wrong_activation_key','<strong>Error</strong>:  Wrong activation key supplied . If we encounter more wrong key you may be blocked for further access to this site!!!');
        }
    }else{
         $errors->add('already_activated','<strong>Error</strong>:  You seems to be already activated . Try to login directly from <a href="/login">here</a>');
    }
endif;
get_header();
?>
<div class="grid group">
    <div class=" grid-2-3">
        <div class="module">
            <h2><?php _e("Email Activation Page", 'Shirshak'); ?> of <?php bloginfo('name'); ?></h2>
            <hr>
            <?php if (is_wp_error( $errors) AND !empty( $errors->errors )): ?>
                <div class="important red"> <ul>
                    <?php foreach($errors->get_error_messages() as $error):?>
                    <li><?php echo $error;?></li>
                    <?php endforeach;?>
                </ul></div>
            <?php endif; ?>
            <?php if(empty($email_activation_key) AND empty($user_id)):?>
                <div class="important">Activation key is missing . Re request the email activation link. </div>
            <?php endif;?>
        </div>
    </div>
    <?php require_once("sidebar.php") ?>
</div>
<?php get_footer();?>