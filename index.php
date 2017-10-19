<?php
/*
Plugin Name: ADVANCED LOGIN SYSTEM BY SHIRSHAK
Description: Change boring login to custom login .
Author: Shirshak Bajgain
Version: 1.0
Text Domain: shirshak
License: MIT
*/
defined('ABSPATH') or die("Cannot access pages directly.");
if ( ! defined( 'ADVANCED_LOGIN_DIR' ) )
    define( 'ADVANCED_LOGIN_DIR', plugin_dir_path( __FILE__ ) );

define("LOGIN_URL",get_site_url()."/login");
define("REGISTER_URL",get_site_url()."/register");
define("RESET_URL",get_site_url()."/reset");
define("REQUEST_EMAIL_ACTIVATION_URL",REGISTER_URL."/email-activation");

require_once("rewrite.php");
require_once("add_more_settings.php");
require_once("mailing.php");
require_once("functions.php");

function verify_recaptcha() {
    if ( isset ( $_POST['g-recaptcha-response'] ) ) {
        $captcha_response = $_POST['g-recaptcha-response'];
    } else {
        return false;
    }
 
    $response = wp_remote_post(
        'https://www.google.com/recaptcha/api/siteverify',
        array(
            'body' => array(
                'secret' => get_option( 'shirshak_theme_option' )['recaptcha_secret_key'],
                'response' => $captcha_response
            )
        )
    );
 
    $success = false;
    if ( $response && is_array( $response ) ) {
        $decoded_response = json_decode( $response['body'] );
        $success = $decoded_response->success;
    }
 
    return $success;
}