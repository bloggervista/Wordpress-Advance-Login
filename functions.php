<?php
function delete_password_reset_metas($user_id){
    delete_user_meta($user_id,"reset_key");
    delete_user_meta($user_id,"reset_key_requested_time");
    delete_user_meta($user_id,"reset_key_requestor_ip");
}