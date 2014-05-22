<?php
/**
 * Controls the Category Widget
 *
 * @since 0.4.0
 * @package IT_Exchange
*/

/**
 * Categories widget class
 *
 * @since 0.4.0
 */
class IT_Exchange_Category_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'it_exchange_widget_categories', 'description' => __( 'A list or dropdown of Product categories', 'it-l10n-ithemes-exchange' ) );
		parent::__construct('product-categories', __( 'Product Categories', 'it-l10n-ithemes-exchange' ), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Product Categories', 'it-l10n-ithemes-exchange' ) : $instance['title'], $instance, $this->id_base);
		$c = ! empty( $instance['count'] ) ? '1' : '0';
		$h = ! empty( $instance['hierarchical'] ) ? '1' : '0';
		$d = ! empty( $instance['dropdown'] ) ? '1' : '0';

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		$cat_args = array('orderby' => 'name', 'show_count' => $c, 'hierarchical' => $h);

		if ( $d ) {
			$cat_args['show_option_none'] = __( 'Select Product Category', 'it-l10n-ithemes-exchange' );
			$cat_args['taxonomy'] = 'it_exchange_category';
			$cat_args['id'] = 'product_cats';
			it_exchange_dropdown_taxonomies( apply_filters('it_exchange_widget_categories_dropdown_args', $cat_args) );

?>
	<script type='text/javascript'>
		/* <![CDATA[ */
		var exchange_cat_dropdown = document.getElementById("product_cats");
		function onProductCatChange() {
			if ( exchange_cat_dropdown.options[exchange_cat_dropdown.selectedIndex].value != 0 
				&& exchange_cat_dropdown.options[exchange_cat_dropdown.selectedIndex].value != -1 ) {
				location.href = "<?php echo home_url(); ?>/?taxonomy=it_exchange_category&term="+exchange_cat_dropdown.options[exchange_cat_dropdown.selectedIndex].value;
			}
		}
		exchange_cat_dropdown.onchange = onProductCatChange;
		/* ]]> */
	</script>
<?php

		} else {

?>
	<ul>
		<?php
			$cat_args['title_li'] = '';
			$cat_args['taxonomy'] = 'it_exchange_category';
			wp_list_categories(apply_filters('it_exchange_widget_categories_args', $cat_args));
		?>
	</ul>
<?php

		}

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance                 = $old_instance;
		$instance['title']        = strip_tags($new_instance['title']);
		$instance['count']        = !empty($new_instance['count']) ? 1 : 0;
		$instance['hierarchical'] = !empty($new_instance['hierarchical']) ? 1 : 0;
		$instance['dropdown']     = !empty($new_instance['dropdown']) ? 1 : 0;

		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = esc_attr( $instance['title'] );
		$count = isset($instance['count']) ? (bool) $instance['count'] :false;
		$hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
		$dropdown = isset( $instance['dropdown'] ) ? (bool) $instance['dropdown'] : false;

?>
	<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'it-l10n-ithemes-exchange' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

	<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>"<?php checked( $dropdown ); ?> />
	<label for="<?php echo $this->get_field_id('dropdown'); ?>"><?php _e( 'Display as dropdown', 'it-l10n-ithemes-exchange' ); ?></label><br />

	<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $count ); ?> />
	<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Show product counts', 'it-l10n-ithemes-exchange' ); ?></label><br />

	<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name('hierarchical'); ?>"<?php checked( $hierarchical ); ?> />
	<label for="<?php echo $this->get_field_id('hierarchical'); ?>"><?php _e( 'Show hierarchy', 'it-l10n-ithemes-exchange' ); ?></label></p>
<?php

	}

}