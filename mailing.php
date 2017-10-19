<?php

if(!function_exists('new_user_registration_email')){
	function new_user_registration_email($user_id){
				$user = new WP_User($user_id);
				$user_email=$user->user_email;
				$user_login=$user->user_login;

				
				$key = sha1(wp_generate_password( 10, true, true ) . $user_email . uniqid(time(), true));
				$activation_code=get_user_meta( $user->ID, 'email_activation_key', true );


				if(!empty($activation_code) OR $activation_code!="ACTIVATED"){
					$activation_code=$key;
					update_user_meta($user_id,"email_activation_key",$key);
				}

		        $message  = sprintf(__('Username: %s', 'Shirshak'), $user_login) . "\r\n"; 
                $message .= sprintf(__('Activation Code: %s', 'Shirshak'), $activation_code) . "\r\n\n"; 
                $message .= REQUEST_EMAIL_ACTIVATION_URL."?user_id=$user_id&email_activation_key=".urlencode( $activation_code )."\r\n"; 
                
                $subject ="Activate your Email";

                if(wp_mail($user_email, $subject, $message)==true){
                   return "done";
                }
                return "not_sent";
	}
}
if(!function_exists('mail_reset_key')){
	function mail_reset_key($user_id){
				$user = new WP_User($user_id);
				$user_email=$user->user_email;
				$user_login=$user->user_login;
				$reset_key = sha1(wp_generate_password( 10, true, true ) . $user_email . uniqid(time(), true));

				update_user_meta($user_id,"reset_key",$reset_key);
				update_user_meta($user_id,"reset_key_requested_time",time());
				if(function_exists('get_client_ip'))update_user_meta($user_id,"reset_key_requestor_ip",get_client_ip());

		        $message  = sprintf(__('Username: %s', 'Shirshak'), $user_login) . "\r\n"; 
                $message .= sprintf(__('Activation Code: %s', 'Shirshak'), $reset_key) . "\r\n\n"; 
                $message .= RESET_URL."?user_id=$user_id&reset_key=".urlencode( $reset_key )."\r\n";
                $message .= "If you have not requested it then simply ignore it nothing gonna happen.". "\r\n\n"; 
                
                $subject ="Reset Your Password";

                if(wp_mail($user_email, $subject, $message)==true){
                   return "done";
                }
                return "not_sent";
	}
}
if(!function_exists('mail_password_reseted')){
	function mail_password_reseted($user_id){
				$user = new WP_User($user_id);
				$user_email=$user->user_email;
				$user_login=$user->user_login;

                $message ="Hello, We inform you that your password has been reseted. Thanks"; 
                
                $subject ="Your Password Has been reseted";

                if(wp_mail($user_email, $subject, $message)==true){
                   return "done";
                }
                return "not_sent";
	}
}