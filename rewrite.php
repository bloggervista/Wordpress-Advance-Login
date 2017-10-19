<?php
add_filter('query_vars', function ($vars) {
    $vars[] = 'login';
    $vars[] = 'logout';
    $vars[] = 'register';
    $vars[] = 'registration_page';
    $vars[] = 'reset_password';
    $vars[] = 'reset_password_page';
    $vars[] = 'email_activation_key';
    return $vars;
});
add_action('generate_rewrite_rules', function($wp_rewrite) {
  $theme_name =  wp_get_theme();
    global $wp_rewrite;
    $new_rules = array(
        '^login/?$' => 'index.php?login=true',
        '^logout/?$' => 'index.php?logout=true',
        '^register/?([^/]+)?/?$' => 'index.php?register=true&registration_page='.$wp_rewrite->preg_index(1),
        '^reset/?$' => 'index.php?reset_password=true',
    );  
    $wp_rewrite->rules = $new_rules+$wp_rewrite->rules ;
});
add_action("template_redirect", function () {
    global $wp;
    global $wp_query, $wp_rewrite;
    if(!empty($wp_query->query_vars['login'])){
        if ($wp_query->query_vars['login'] == "true") {
            require (ADVANCED_LOGIN_DIR.'templates/login.php');
            die();
        }
    }
    if(!empty($wp_query->query_vars['logout'])){
        if ($wp_query->query_vars['logout'] == "true") {
            wp_clear_auth_cookie();
            wp_logout();
            wp_safe_redirect(home_url());
            die();
        }
    }
    if(!empty($wp_query->query_vars['register']) AND $wp_query->query_vars['register'] == "true"){
        if (is_user_logged_in()) {
            wp_redirect("/user");
            die();
        }

        if (!empty($wp_query->query_vars['registration_page']) AND $wp_query->query_vars['registration_page']=="email-activation") {
            if(!empty($_GET["email_activation_key"]) AND !empty($_GET["user_id"])):
                require (ADVANCED_LOGIN_DIR.'templates/activate_email.php');
                exit;
            endif;

            require (ADVANCED_LOGIN_DIR.'templates/request_email_activation_key.php');
            exit;
        }
        require (ADVANCED_LOGIN_DIR.'templates/register.php');
        die();
    }
    if(!empty($wp_query->query_vars['reset_password'])){
        if ($wp_query->query_vars['reset_password'] == "true") {
            if(!empty($_GET["reset_key"]) AND !empty($_GET["user_id"])):
                require (ADVANCED_LOGIN_DIR.'templates/reset_password_form.php');
                exit;
            endif;

            require (ADVANCED_LOGIN_DIR.'templates/request_password_reset_form.php');
            exit;
        }
    }
});
?>