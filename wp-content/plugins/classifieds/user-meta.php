<?php
/*
Plugin Name: PJW User Meta
Plugin URI: http://blog.ftwr.co.uk/archives/2009/07/19/adding-extra-user-meta-fields
Description: Allows users to configure some random extra meta value.
Author: Peter Westwood
Version: 0.02
Author URI: http://blog.ftwr.co.uk/

Use of the frontend as get_the_author_meta('telephone') or the_author_meta('telephone')
*/

class pjw_user_meta {

 function pjw_user_meta() {
 if ( is_admin() )
 {
 add_action('show_user_profile', array(&$this,'action_show_user_profile'));
 add_action('edit_user_profile', array(&$this,'action_show_user_profile'));
 add_action('personal_options_update', array(&$this,'action_process_option_update'));
 add_action('edit_user_profile_update', array(&$this,'action_process_option_update'));
 }

 }

 function action_show_user_profile($user)
 {
 ?>
 <h3><?php _e('Other Contact Info') ?></h3>

 <table>
 <tr>
 <th><label for="telephone"><?php _e('Telephone No.'); ?></label></th>
 <td><input type="text" name="telephone" id="telephone" value="<?php echo esc_attr(get_the_author_meta('telephone', $user->ID) ); ?>" /></td>
 </tr>
 <tr>
 <th><label for="mobile"><?php _e('Mobile No.'); ?></label></th>
 <td><input type="text" name="mobile" id="mobile" value="<?php echo esc_attr(get_the_author_meta('mobile', $user->ID) ); ?>" /></td>
 </tr>
 </table>
 <?php
 }

 function action_process_option_update($user_id)
 {
 update_usermeta($user_id, 'telephone', ( isset($_POST['telephone']) ? $_POST['telephone'] : '' ) );
 update_usermeta($user_id, 'mobile', ( isset($_POST['mobile']) ? $_POST['mobile'] : '' ) );
 }
}
/* Initialise outselves */
add_action('plugins_loaded', create_function('','global $pjw_user_meta_instance; $pjw_user_meta_instance = new pjw_user_meta();'));
?>
