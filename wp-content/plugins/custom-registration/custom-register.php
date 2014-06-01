<?php
/*
Plugin Name: Custom Register
Plugin URI: 
Description: a customized registration widget
Author: Chad H. Alvarez
Version: 1.0
Author URI: 
*/
 
function custom_user_registration()
{
?>
	<div class="sign-up-form">
		<center>
			<?php do_action ('process_customer_registration_form'); ?>
			<form action="<?php echo site_url() ?>" method="post" class="form-inline">
				<input type="text" name="email" placeholder = "email" class="signup_fields_join_us">
				<input type="password" name="password" placeholder="password" class="signup_fields_join_us">
				<?php wp_nonce_field( 'adduserfield', 'add-nonce' ) ?>
				<input name="action" type="hidden" id="action" value="adduser" />
				<input type="submit" value="Join Us" class="join_us">
			</form>
		</center>
	</div>
<?php
}
 
function widget_custom_register($args){
  extract($args);
  echo$before_widget;
  custom_user_registration();
  echo$after_widget;
}
 
function custom_register_init()
{
  register_sidebar_widget(__('Custom Register'),'widget_custom_register');    
}
add_action("plugins_loaded","custom_register_init");
?>