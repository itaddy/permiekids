<?php

/*
Written by Chris Jean for iThemes.com
Version 1.0.1

Version History
	1.0.0 - 2009-07-14
		Release-ready
	1.0.1 - 2013-06-25 - Chris Jean
		Changed function declaration to "public static".
*/


if ( ! class_exists( 'ITBackground' ) ) {
	class ITBackground {
		public static function render_background() {
			$settings = apply_filters( 'it_filter_background_settings', array() );
			
			$options = array( 'background_image', 'background_color', 'background_position', 'background_attachment', 'background_repeat' );
			
?>
	<style type="text/css">
		body {
			<?php if ( 'custom_color' == $settings['background_option'] ) : ?>
				background-color: <?php echo $settings['background_color']; ?>;
				background-image: none;
			<?php else : ?>
				<?php foreach ( (array) $options as $option ) : ?>
					<?php if ( ! empty( $settings[$option] ) ) : ?>
						<?php if ( 'background_image' == $option ) : ?>
							<?php echo str_replace( '_', '-', $option ); ?>: url(<?php echo $settings[$option]; ?>);
						<?php else : ?>
							<?php echo str_replace( '_', '-', $option ); ?>: <?php echo $settings[$option]; ?>;
						<?php endif; ?>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
			
		}
	</style>
<?php
			
		}
	}
	
	add_action( 'wp_head', array( 'ITBackground', 'render_background' ) );
}
