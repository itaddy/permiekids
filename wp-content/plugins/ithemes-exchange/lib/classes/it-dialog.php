<?php

/*
Written by Chris Jean for iThemes.com
Version 1.0.2

This class provides a reliable and flexible Javascript-based dialog. It was
forked from Thickbox's code and the ITThickbox class. The Thickbox code was
improved to include features such as adaptive resizing to handle changing
content and window sizes automatically.

Version History
	1.0.0 - 2012-07-26 - Chris Jean
		Initial development version.
	1.0.1 - 2013-01-09 - Chris Jean
		Improved the body classes.
	1.0.2 - 2013-06-25 - Chris Jean
		Changed function declarations to "public static".
*/


if ( ! class_exists( 'ITDialog' ) ) {
	class ITDialog {
		public static function get_link( $link, $options = '' ) {
			$default_options = array();
			
			$options = wp_parse_args( $options, $default_options );
			
			$link .= ( false === strpos( $link, '?' ) ) ? '?' : '&';
			$link .= "render_clean=dialog";
			
			foreach ( $options as $var => $val )
				$link .= "&it-dialog-$var=$val";
			
			return $link;
		}
		
		public static function add_enqueues() {
			$plugin_url = ITUtility::get_url_from_file( dirname( __FILE__ ) );
			
			wp_enqueue_style( 'it-dialog-outside-dialog-style', "$plugin_url/css/it-dialog-outside-dialog.css" );
			wp_enqueue_script( 'it-dialog-outside-dialog-script', "$plugin_url/js/it-dialog-outside-dialog.js", array( 'jquery' ), false, true );
			
			$vars = array(
				'no_iframes_message' => __( 'This feature requires inline frames. You have iframes disabled or your browser does not support them.', 'it-l10n-ithemes-exchange' ),
				'loading_image'      => ITDialog::get_loading_image_url(),
				'close_message'      => __( 'Close', 'it-l10n-ithemes-exchange' ),
				'close_image'        => "$plugin_url/images/it-dialog-close.png",
			);
			
			wp_localize_script( 'it-dialog-outside-dialog-script', 'it_dialog_vars', $vars );
		}
		
		public static function get_loading_image_url() {
			$plugin_url = ITUtility::get_url_from_file( dirname( __FILE__ ) );
			
			return "$plugin_url/images/it-dialog-loading.gif";
		}
		
		public static function render( $content_callback, $options = array() ) {
			global $current_user;
			
			if ( ! isset( $current_user ) )
				$current_user = wp_get_current_user();
			
			
			remove_action( 'admin_enqueue_scripts', 'wp_auth_check_load' );
			
			
			$default_options = array(
				'styles'                => array( 'global', 'wp-admin', 'colors', 'media', 'ie' ),
				'hook_suffix'           => 'it-dialog',
				'content_callback_args' => array(),
				'body_id'               => '',
			);
			$options = array_merge( $default_options, $options );
			
?><!DOCTYPE html>

<!--[if IE 8]>
	<html xmlns="http://www.w3.org/1999/xhtml" class="ie8 it-dialog-html" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 8) ]><!-->
	<html xmlns="http://www.w3.org/1999/xhtml" class="it-dialog-html" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<!--<![endif]-->

<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php echo get_option( 'blog_charset' ); ?>" />
	<title>IT Dialog Window</title>
	
	<?php
		foreach ( $options['styles'] as $style )
			wp_enqueue_style( $style );
		
		$plugin_url = ITUtility::get_url_from_file( dirname( __FILE__ ) );
		
		wp_enqueue_style( 'it-dialog-inside-dialog-style', "$plugin_url/css/it-dialog-inside-dialog.css" );
		wp_enqueue_script( 'it-dialog-inside-dialog-script', "$plugin_url/js/it-dialog-inside-dialog.js", array( 'jquery' ), false, true );
	?>
	
	<script type="text/javascript">
		//<![CDATA[
		addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
		var userSettings = { 'url': '<?php echo SITECOOKIEPATH; ?>', 'uid': '<?php echo $current_user->ID; ?>', 'time': '<?php echo time(); ?>' };
		var ajaxurl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
		var pagenow = '<?php echo esc_js( $options['hook_suffix'] ); ?>';
		var adminpage = '<?php echo esc_js( $options['hook_suffix'] ); ?>';
		var isRtl = <?php echo (int) is_rtl(); ?>;
		//]]>
	</script>
	
	<?php
		do_action( 'admin_enqueue_scripts', $options['hook_suffix'] );
		do_action( "admin_print_styles-{$options['hook_suffix']}" );
		do_action( 'admin_print_styles' );
		do_action( "admin_print_scripts-{$options['hook_suffix']}" );
		do_action( 'admin_print_scripts' );
		do_action( "admin_head-{$options['hook_suffix']}" );
		do_action( 'admin_head' );
		
		if ( is_string( $content_callback ) )
			do_action( "admin_head_{$content_callback}" );
	?>
</head>

<body<?php if ( ! empty( $options['body_id'] ) ) echo " id='{$options['body_id']}'"; ?> class="wp-admin wp-core-ui no-js">
	<script type="text/javascript">
		document.body.className = document.body.className.replace( 'no-js', 'js' );
	</script>
	
	<div id="it-dialog-content-container">
		<?php call_user_func_array( $content_callback, $options['content_callback_args'] ); ?>
	</div>
	
	
	<?php do_action( 'admin_print_footer_scripts' ); ?>
	
	<script type="text/javascript">if ( 'function' == typeof wpOnload ) wpOnload();</script>
</body>

</html><?php
			
		}
		
		public static function remove( $js = '' ) {
			
?>
	<script type="text/javascript">
		jQuery( function() {
			<?php echo $js; ?>
			
			it_dialog_remove();
		} );
	</script>
<?php
			
		}
		
		public static function add_form_submission_message( $message = '' ) {
			
?>
	<script type="text/javascript">
		jQuery( function() {
			it_dialog_add_form_submission_message( "<?php echo esc_js( $message ); ?>" );
		} );
	</script>
<?php
			
		}
	}
}
