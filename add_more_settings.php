<?php
add_action( 'show_user_profile', 'extra_user_profile_fields' );
add_action( 'edit_user_profile', 'extra_user_profile_fields' );

function extra_user_profile_fields( $user ) { ?>
<h3><?php _e("Extra profile information {By Shirshak}", "blank"); ?></h3>
<table class="form-table">
    <tr>
        <th><label for="college_name"><?php _e("College | School | Institution Name"); ?></label></th>
        <td>
            <input type="text" name="college_name" id="college_name" value="<?php echo esc_attr( get_the_author_meta( 'college_name', $user->ID ) ); ?>" class="regular-text" /><br />
            <span class="description"><?php _e("Please enter your college_name."); ?></span>
        </td>
    </tr>
        <tr>
        <th><label for="phone_number"><?php _e("Type"); ?></label></th>
        <td>
            <label for="type" class="screen-reader-text"><?php _e('I Am ', 'Shirshak'); ?></label>
        <?php $types=["Student","Teacher","Parent"] ?>
        <select name="type" id="type" class="full-width">
            <?php foreach($types as $type):?>
                <option value="<?php echo $type ?>" <?php if(esc_attr( get_the_author_meta( 'phone_number', $user->ID ) )==$type) echo "selected";?>>&nbsp;&nbsp;&nbsp;<?php echo $type;?></option>
            <?php endforeach; ?>
        </select>
        </td>
    </tr>
    <tr>
        <th><label for="class"><?php _e("Class or Level "); ?></label></th>
        <td>
            <input type="text" name="class" id="class" value="<?php echo esc_attr( get_the_author_meta( 'class', $user->ID ) ); ?>" class="regular-text" /><br />
            <span class="description"><?php _e("Please enter your class."); ?></span>
        </td>
    </tr>
    <tr>
        <th><label for="favorite_posts"><?php _e("Favorite Posts"); ?></label></th>
        <td>
            <input type="text" name="favorite_posts" id="favorite_posts" value="<?php echo esc_attr( get_the_author_meta( 'favorite_posts', $user->ID ) ); ?>" class="regular-text" /><br />
        <span class="description"><?php _e("Please enter your favorite posts ID."); ?></span>
        </td>
    </tr>
    <tr>
        <th><label for="email_activation_key"><?php _e("Email Activation Key"); ?></label></th>
        <td>
            <input type="text" name="email_activation_key" id="email_activation_key" value="<?php echo esc_attr( get_the_author_meta( 'email_activation_key', $user->ID ) ); ?>" class="regular-text" /><br />
        </td>
    </tr>
</table>
<?php }

add_action( 'personal_options_update', 'save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'save_extra_user_profile_fields' );

function save_extra_user_profile_fields( $user_id ) {
if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
update_user_meta( $user_id, 'college_name', $_POST['college_name'] );
update_user_meta( $user_id, 'type', $_POST['type'] );
update_user_meta( $user_id, 'favorite_posts', $_POST['favorite_posts'] );
update_user_meta( $user_id, 'email_activation_key', $_POST['email_activation_key'] );
update_user_meta( $user_id, 'class', $_POST['class'] );
}

add_filter('user_contactmethods', function ($user_contactmethods) {
    
    $user_contactmethods['twitter'] = 'Twitter Username';
    $user_contactmethods['facebook'] = 'Facebook Username';
    $user_contactmethods['instagram'] = 'Instagram Username';
    
    return $user_contactmethods;
});
?>