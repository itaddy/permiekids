<?php

/*
Written by Chris Jean for iThemes.com
Version 1.0.8

Version History
	1.0.0 - 2009-07-14
		Release-ready
	1.0.1 - 2009-09-24
		Updated JS URL to point to the correct location
	1.0.2 - 2009-11-23
		Commented out media style enqueue until I decide on an appropriate use
	1.0.3 - 2009-11-23
		Removed debug output
	1.0.4 - 2010-03-03
		Added title of "Thickframe Window" even though it won't show
	1.0.5 - 2011-08-23 - Chris Jean
		Added thickbox-html class to the html tag
	1.0.6 - 2012-02-13 - Chris Jean
		Improved relative path code to work with servers with odd ABSPATH configurations
	1.0.7 - 2012-09-24 - Chris Jean
		Updated $plugin_url generation code in render_thickbox().
	1.0.8 - 2013-06-25 - Chris Jean
		Changed function declarations to "public static".
*/


if ( ! class_exists( 'ITThickbox' ) ) {
	class ITThickbox {
		public static function render_thickbox( $content_func ) {
			$plugin_url = ITUtility::get_url_from_file( dirname( __FILE__ ) );
			
?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action( 'admin_xml_ns' ); ?> <?php language_attributes(); ?> class="thickbox-html">
		<head>
			<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php echo get_option( 'blog_charset' ); ?>" />
			<title>Thickframe Window</title>
			<?php
				wp_enqueue_style( 'global' );
				wp_enqueue_style( 'wp-admin' );
				wp_enqueue_style( 'colors' );
/*				if ( 0 === strpos( $content_func, 'media' ) )
					wp_enqueue_style( 'media' );*/
				wp_enqueue_style( 'ie' );
				
				wp_enqueue_script( 'it-thickbox-script', $plugin_url . '/js/it-thickbox.js' );
			?>
			<script type="text/javascript">
				//<![CDATA[
					addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
					var userSettings = {'url':'<?php echo SITECOOKIEPATH; ?>','uid':'<?php if ( ! isset($current_user) ) $current_user = wp_get_current_user(); echo $current_user->ID; ?>','time':'<?php echo time() ?>'};
				//]]>
			</script>
			<?php
				do_action( 'admin_print_styles' );
				do_action( 'admin_print_scripts' );
				do_action( 'admin_head' );
				if ( is_string( $content_func ) )
					do_action( "admin_head_{$content_func}" );
			?>
		</head>
		<body style="height:auto;"<?php if ( isset( $GLOBALS['body_id'] ) ) echo ' id="' . $GLOBALS['body_id'] . '"'; ?>>
			<div style="padding:10px;" id="thickbox-content-container">
				<?php
					$args = func_get_args();
					$args = array_slice( $args, 1 );
					call_user_func_array( $content_func, $args );
				?>
				<script type="text/javascript">if(typeof wpOnload=='function') wpOnload();</script>
				<script type="text/javascript">auto_resize_thickbox_height();</script>
				<?php do_action( 'admin_print_footer_scripts' ); ?>
			</div>
		</body>
	</html>
<?php
			
		}
		
		public static function close_thickbox( $js = '' ) {
			
?>
	<script type="text/javascript">
		/* <![CDATA[ */
			<?php echo $js; ?>
			close_thickbox();
		/* ]]> */
	</script>
<?php
			
		}
		
		public static function delete_parent_table_row( $entry_id, $table_id, $row_class_prefix = 'entry-' ) {
			
?>
	<script type="text/javascript">
		/* <![CDATA[ */
			var win = window.dialogArguments || opener || parent || top;
			win.jQuery("table<?php echo $table_id; ?> #<?php echo $row_class_prefix; ?><?php echo $entry_id; ?>").remove();
			
			win.jQuery("tr[id^='<?php echo $row_class_prefix; ?>']:even").addClass("alternate");
			win.jQuery("tr[id^='<?php echo $row_class_prefix; ?>']:odd").removeClass("alternate");
		/* ]]> */
	</script>
<?php
			
		}
		
		public static function add_parent_table_row( $entry_id, $entry_description, $entry, $table_id, $description_link_title, $row_class_prefix = 'entry-', $insert_position = 'alpha', $enable_highlight = true, $highlight_color = '#66FF66' ) {
			$entry = str_replace( "'", "\\'", $entry );
			$entry = str_replace( "\r", '', $entry );
			$entry = str_replace( "\n", '', $entry );
			
?>
	<script type="text/javascript">
		/* <![CDATA[ */
			var win = window.dialogArguments || opener || parent || top;
			
			var newRow = '<?php echo $entry; ?>';
			
			<?php if ( 'alpha' === $insert_position ) : ?>
				var rows = win.jQuery("tr[id^='<?php echo $row_class_prefix; ?>']");
				var i;
				for(i = 0; i < rows.get().length; i++) {
					if("<?php echo strtolower( $entry_description ); ?>" < win.jQuery("tr[id^='<?php echo $row_class_prefix; ?>']:eq(" + i + ") a[title='<?php echo $description_link_title; ?>']").html().toLowerCase()) {
						break;
					}
				}
				
				i--;
				
				if((rows.get().length > 0) && (i >= 0)) {
					win.jQuery("tr[id^='<?php echo $row_class_prefix; ?>']:eq(" + i + ")").after(newRow);
				}
				else {
					if(win.jQuery("table<?php echo $table_id; ?> > tbody") == undefined) {
						win.jQuery("table<?php echo $table_id; ?>").html(newRow);
					}
					else {
						win.jQuery("table<?php echo $table_id; ?> > tbody").prepend(newRow);
					}
				}
			<?php elseif ( 'first' === $insert_position ) : ?>
				win.jQuery("table<?php echo $table_id; ?> > tbody").prepend(newRow);
			<?php else : ?>
				win.jQuery("table<?php echo $table_id; ?> > tbody").append(newRow);
			<?php endif; ?>
			
			win.jQuery("tr[id^='<?php echo $row_class_prefix; ?>']:even").addClass("alternate");
			win.jQuery("tr[id^='<?php echo $row_class_prefix; ?>']:odd").removeClass("alternate");
			
			<?php if ( true === $enable_highlight ) : ?>
				var origColor = win.jQuery("#<?php echo $row_class_prefix; ?><?php echo $entry_id; ?>").css("background-color");
				win.jQuery("#<?php echo $row_class_prefix; ?><?php echo $entry_id; ?>").css("background-color", "<?php echo $highlight_color; ?>").fadeIn("slow").animate({backgroundColor:origColor}, 400).css('background-color', '');
			<?php endif; ?>
			
			win.tb_init("#<?php echo $row_class_prefix; ?><?php echo $entry_id; ?> a[href*='TB_iframe']");
		/* ]]> */
	</script>
<?php
			
		}
	}
}
