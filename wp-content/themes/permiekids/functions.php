<?php

if ( file_exists( STYLESHEETPATH . '/admin/class.wp-bootstrap-options.php' ) ) {
	require_once( STYLESHEETPATH . '/admin/class.wp-bootstrap-options.php' );
}

if ( file_exists( STYLESHEETPATH . '/permiekids_custom_field_class.php' ) ) {
	require_once( STYLESHEETPATH . '/permiekids_custom_field_class.php' );
}


add_filter( 'gettext', 'ts_edit_password_email_text' );
function ts_edit_password_email_text ( $text ) {
	if ( $text == 'A password will be e-mailed to you.' ) {
		$text = 'If you leave password fields empty one will be generated for you. Password must be at least eight characters long.';
	}
	return $text;
}

function mytheme_option( $option ) {
	$options = get_option( 'wp_bootstrap_options' );
	if ( isset( $options[$option] ) )
		return $options[$option];
	else
		return false;
}

function wpbootstrap_scripts_with_jquery()
{
	wp_register_script( 'custom-script', get_template_directory_uri() . '/bootstrap/js/bootstrap.js', array( 'jquery' ) );
	wp_enqueue_script( 'custom-script' );
}
add_action( 'wp_enqueue_scripts', 'wpbootstrap_scripts_with_jquery' );

function wpbootstrap_setup() {

register_nav_menus(
	array(
	'footer_nav' => __( 'Footer Menu', 'bootpress' ),
	'top_menu' => __( 'Top Menu', 'bootpress' )
	)
);

}
add_action( 'after_setup_theme', 'wpbootstrap_setup' );


require_once('wp_bootstrap_navwalker.php');

register_sidebar( array(
    'name'         => __( 'Home Page Sidebar' ),
    'id'           => 'home-page-sidebar',
    'description'  => __( 'Widget area for home page.' ),
    'before_title' => '<h2>',
    'after_title'  => '</h2>',
) );

register_sidebar( array(
    'name'         => __( 'Inner Pages Sidebar' ),
    'id'           => 'inner-pages-sidebar',
    'description'  => __( 'Widget area for inner pages.' ),
    'before_title' => '<h2>',
    'after_title'  => '</h2>',
) );

register_sidebar( array(
    'name'         => __( 'Footer Menu Widget' ),
    'id'           => 'footer-menu-widget',
    'description'  => __( 'A widget position for footer menu.' ),
    'before_title' => '<h2>',
    'after_title'  => '</h2>',
) );

register_sidebar( array(
    'name'         => __( 'Footer Bottom Widget' ),
    'id'           => 'footer-bottom-widget',
    'description'  => __( 'A widget position at footer.' ),
    'before_title' => '<h2>',
    'after_title'  => '</h2>',
) );

register_sidebar( array(
    'name'         => __( 'Footer Top Widget' ),
    'id'           => 'footer-top-widget',
    'description'  => __( 'A widget position at top of footer.' ),
    'before_title' => '<h2>',
    'after_title'  => '</h2>',
) );

register_sidebar( array(
    'name'         => __( 'Content Bottom A' ),
    'id'           => 'content-bottom-a',
    'description'  => __( 'A widget position at the bottom of content.' ),
    'before_title' => '<h2>',
    'after_title'  => '</h2>',
) );

register_sidebar( array(
    'name'         => __( 'Content Bottom B' ),
    'id'           => 'content-bottom-b',
    'description'  => __( 'A widget position at the bottom of content.' ),
    'before_title' => '<h2>',
    'after_title'  => '</h2>',
) );

add_theme_support( 'post-thumbnails' ); 

function save_custom_fields () {
	if (isset($_POST['it-exchange-save-profile'])) {
		$user_id = it_exchange_get_current_customer_id();
		update_user_meta( $user_id, 'motto', sanitize_text_field(esc_attr($_POST['motto'])));
		update_user_meta( $user_id, 'who_said_it', sanitize_text_field(esc_attr($_POST['who_said_it'])));
		update_user_meta( $user_id, 'location', sanitize_text_field(esc_attr($_POST['location'])));	
		update_user_meta( $user_id, 'family',sanitize_text_field(esc_attr($_POST['family'])));
		update_user_meta( $user_id, 'occupation',sanitize_text_field(esc_attr($_POST['occupation'])));
		update_user_meta( $user_id, 'experience',sanitize_text_field(esc_attr($_POST['experience'])));
		update_user_meta( $user_id, 'facebook',sanitize_text_field(esc_attr($_POST['facebook'])));
		update_user_meta( $user_id, 'twitter',sanitize_text_field(esc_attr($_POST['twitter'])));	
		update_user_meta( $user_id, 'linkedin',sanitize_text_field(esc_attr($_POST['linkedin'])));
		update_user_meta( $user_id, 'what_are_you_working_on',sanitize_text_field(esc_attr($_POST['what_are_you_working_on'])));
		update_user_meta( $user_id, 'biography',sanitize_text_field(esc_attr($_POST['biography'])));	
		update_user_meta( $user_id, 'custom_avatar',sanitize_text_field(esc_attr($_POST['custom_avatar'])));					
	}
}

add_action ('handle_it_exchange_save_profile_action', 'save_custom_fields');

function registration_process_hook() {
	if (isset($_POST['email']) && isset($_POST['password'])) {
		if ( !wp_verify_nonce($_POST['add-nonce'],'adduserfield') ) {
			wp_die('Sorry! That was secure, guess you\'re cheatin huh!');
		} else {
			$userdata = array(
				'user_pass' => esc_attr( $_POST['password'] ),
				'user_login' => esc_attr( $_POST['email'] ),
				'user_email' => esc_attr( $_POST['email'] ),
				'role' => get_option( 'default_role' ),
			);
			if ( !$userdata['user_login'] )
				$error = 'A username is required for registration.';
			elseif ( username_exists($userdata['user_login']) )
				$error = 'Sorry, that username already exists!';
			elseif ( !is_email($userdata['user_email'], true) )
				$error = 'You must enter a valid email address.';
			elseif ( email_exists($userdata['user_email']) )
				$error = 'Sorry, that email address is already used!';
			else{
				$new_user = wp_insert_user( $userdata );
				wp_new_user_notification($new_user, $user_pass);
			}
		}
	}
	if ( $new_user ) : ?>

	<p class="alert">
	<?php
		$user = get_user_by('id',$new_user);
		echo 'Thank you for registering ' . $user->user_login;
	?>
	</p>
	
	<?php else : ?>
	
		<?php if ( $error ) : ?>
			<p class="error">
				<?php echo $error; ?>
			</p>
		<?php endif; ?>
	
	<?php endif;

}
add_action('process_customer_registration_form', 'registration_process_hook');


function custom_post_type_ethics() {

	$labels = array(
		'name'                => _x( 'Post Types', 'Ethics', 'text_domain' ),
		'singular_name'       => _x( 'Post Type', 'Ethic', 'text_domain' ),
		'menu_name'           => __( 'Ethics', 'text_domain' ),
		'parent_item_colon'   => __( 'Ethics:', 'text_domain' ),
		'all_items'           => __( 'All Items', 'text_domain' ),
		'view_item'           => __( 'View Item', 'text_domain' ),
		'add_new_item'        => __( 'Add New Item', 'text_domain' ),
		'add_new'             => __( 'Add New', 'text_domain' ),
		'edit_item'           => __( 'Edit Item', 'text_domain' ),
		'update_item'         => __( 'Update Item', 'text_domain' ),
		'search_items'        => __( 'Search Item', 'text_domain' ),
		'not_found'           => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
	);
	$args = array(
		'label'               => __( 'Ethic', 'text_domain' ),
		'description'         => __( 'Ethic Description', 'text_domain' ),
		'labels'              => $labels,
		'supports'            => array( ),
		'taxonomies'          => array( 'category', 'post_tag' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'menu_icon'           => '',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'Ethic', $args );

}

add_action( 'init', 'custom_post_type_survey', 0 );

function custom_post_type_survey() {

	$labels = array(
		'name'                => _x( 'Post Types', 'Surveys', 'text_domain' ),
		'singular_name'       => _x( 'Post Type', 'Survey', 'text_domain' ),
		'menu_name'           => __( 'Survey', 'text_domain' ),
		'parent_item_colon'   => __( 'Surveys:', 'text_domain' ),
		'all_items'           => __( 'All Items', 'text_domain' ),
		'view_item'           => __( 'View Item', 'text_domain' ),
		'add_new_item'        => __( 'Add New Item', 'text_domain' ),
		'add_new'             => __( 'Add New', 'text_domain' ),
		'edit_item'           => __( 'Edit Item', 'text_domain' ),
		'update_item'         => __( 'Update Item', 'text_domain' ),
		'search_items'        => __( 'Search Item', 'text_domain' ),
		'not_found'           => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
	);
	$args = array(
		'label'               => __( 'Survey', 'text_domain' ),
		'description'         => __( 'Survey Description', 'text_domain' ),
		'labels'              => $labels,
		'supports'            => array( ),
		'taxonomies'          => array( 'category', 'post_tag' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'menu_icon'           => '',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'Survey', $args );

}

function custom_roles() {
	// basic
	add_role('basic', 'Basic', array(
		'read' => true, 
		'edit_posts' => false,
		'delete_posts' => false
	));
	
	add_role('contributing', 'Contributing', array(
		'read' => true,
		'edit_posts' => true,
		'delete_posts' => false, 
	));
}

add_action('init', 'custom_roles', 1);

add_action( 'init', 'custom_post_type_ethics', 0 );

function custom_post_type_principles() {

	$labels = array(
		'name'                => _x( 'Post Types', 'Principles', 'text_domain' ),
		'singular_name'       => _x( 'Post Type', 'Principle', 'text_domain' ),
		'menu_name'           => __( 'Principles', 'text_domain' ),
		'parent_item_colon'   => __( 'Principles:', 'text_domain' ),
		'all_items'           => __( 'All Items', 'text_domain' ),
		'view_item'           => __( 'View Item', 'text_domain' ),
		'add_new_item'        => __( 'Add New Item', 'text_domain' ),
		'add_new'             => __( 'Add New', 'text_domain' ),
		'edit_item'           => __( 'Edit Item', 'text_domain' ),
		'update_item'         => __( 'Update Item', 'text_domain' ),
		'search_items'        => __( 'Search Item', 'text_domain' ),
		'not_found'           => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
	);
	$args = array(
		'label'               => __( 'Principle', 'text_domain' ),
		'description'         => __( 'Principle Description', 'text_domain' ),
		'labels'              => $labels,
		'supports'            => array( ),
		'taxonomies'          => array( 'category', 'post_tag' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'menu_icon'           => '',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'Principle', $args );

}

add_action( 'init', 'custom_post_type_principles', 0 );

function custom_post_type_characteristics() {

	$labels = array(
		'name'                => _x( 'Post Types', 'Characteristics', 'text_domain' ),
		'singular_name'       => _x( 'Post Type', 'Characteristic', 'text_domain' ),
		'menu_name'           => __( 'Characteristics', 'text_domain' ),
		'parent_item_colon'   => __( 'Characteristic:', 'text_domain' ),
		'all_items'           => __( 'All Items', 'text_domain' ),
		'view_item'           => __( 'View Item', 'text_domain' ),
		'add_new_item'        => __( 'Add New Item', 'text_domain' ),
		'add_new'             => __( 'Add New', 'text_domain' ),
		'edit_item'           => __( 'Edit Item', 'text_domain' ),
		'update_item'         => __( 'Update Item', 'text_domain' ),
		'search_items'        => __( 'Search Item', 'text_domain' ),
		'not_found'           => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'text_domain' ),
	);
	$args = array(
		'label'               => __( 'Characteristic', 'text_domain' ),
		'description'         => __( 'Characteristic Description', 'text_domain' ),
		'labels'              => $labels,
		'supports'            => array( ),
		'taxonomies'          => array( 'category', 'post_tag' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 5,
		'menu_icon'           => '',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'Characteristic', $args );

}

add_action( 'init', 'custom_post_type_characteristics', 0 );

function the_breadcrumb() {
    global $post;
    echo '<ul id="breadcrumbs">';
    if (!is_home()) {
        echo '<li><a href="';
        echo get_option('home');
        echo '">';
        echo 'Home';
        echo '</a></li><li class="separator"> &raquo; </li>';
        if (is_category() || is_single()) {
            echo '<li>';
            the_category(' </li><li class="separator"> &raquo; </li><li> ');
            if (is_single()) {
                echo '</li><li class="separator"> &raquo; </li><li>';
                the_title();
                echo '</li>';
            }
        } elseif (is_page()) {
            if($post->post_parent){
                $anc = get_post_ancestors( $post->ID );
                $title = get_the_title();
                foreach ( $anc as $ancestor ) {
                    $output = '<li><a href="'.get_permalink($ancestor).'" title="'.get_the_title($ancestor).'">'.get_the_title($ancestor).'</a></li> <li class="separator">&raquo;</li>';
                }
                echo $output;
                echo '<strong title="'.$title.'"> '.$title.'</strong>';
            } else {
                echo '<li><strong> '.get_the_title().'</strong></li>';
            }
        }
    }
    elseif (is_tag()) {single_tag_title();}
    elseif (is_day()) {echo"<li>Archive for "; the_time('F jS, Y'); echo'</li>';}
    elseif (is_month()) {echo"<li>Archive for "; the_time('F, Y'); echo'</li>';}
    elseif (is_year()) {echo"<li>Archive for "; the_time('Y'); echo'</li>';}
    elseif (is_author()) {echo"<li>Author Archive"; echo'</li>';}
    elseif (isset($_GET['paged']) && !empty($_GET['paged'])) {echo "<li>Blog Archives"; echo'</li>';}
    elseif (is_search()) {echo"<li>Search Results"; echo'</li>';}
    echo '</ul>';
}

add_filter( 'wp_nav_menu_items', 'search_button', 10, 2 );
function search_button ( $items, $args ) {
    if ($args->theme_location == 'top_menu') {
        $items .= '<li><button class="search-icon">Search</button></li>';
    }
    return $items;
}




add_action('admin_enqueue_scripts', 'upload_script');

function upload_script() {
	wp_enqueue_media();
	wp_register_script('my-upload-script', get_template_directory_uri() .'/upload-script.js', array('jquery'));
	wp_enqueue_script('my-upload-script');
}


function custom_avatar_field( $user ) { 


?>
	<h3>Custom Avatar</h3>
	 
	<table class="form-table">
	<tr>
	<th><label for="custom_avatar">Custom Avatar URL:</label></th>
	<td>
	<input type="text" id="upload_image" class="regular-text" name="ad_image" value="<?php echo esc_attr( get_the_author_meta( 'custom_avatar', $user->ID ) ); ?>" /> <input id="upload_image_button" class="button" type="button" value="Upload Image" /><br />
	<span>Type in the URL of the image you'd like to use as your avatar. This will override your default Gravatar, or show up if you don't have a Gravatar. <br /><strong>Image should be 200x200 pixels.</strong></span>
	
	</td>
	</tr>
	</table>
	<?php 
}
add_action( 'show_user_profile', 'custom_avatar_field' );
add_action( 'edit_user_profile', 'custom_avatar_field' );
 

function save_custom_avatar_field( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
		update_usermeta( $user_id, 'custom_avatar', $_POST['ad_image'] );

}

add_action( 'personal_options_update', 'save_custom_avatar_field' );
add_action( 'edit_user_profile_update', 'save_custom_avatar_field' );

function add_media_upload_scripts() {
    if ( is_admin() ) {
         return;
       }
    wp_enqueue_media();
	
}
add_action('wp_enqueue_scripts', 'add_media_upload_scripts');

add_action( 'post_submitbox_misc_actions', 'survey_ended' );

function survey_ended($post)
{
	
		global $post;
		if ($post->post_type=='survey') {
		$value = get_post_meta($post->ID, '_survey_ended', true);
		
			echo '<div class="misc-pub-section misc-pub-section-last">
				 <span id="timestamp">'; ?>
			
			<?php 
			if ($value=='yes') {
				$yes='yes';
				$no ='no'; 
			} else {
				$no = 'yes';
				$yes='no';
			}
			?>	 
			Survey Ended 
			<input type="radio" name="survey_ended" <?php echo ($yes=='yes' ? 'checked' :''); ?> value="yes">Yes
			<input type="radio" name="survey_ended" <?php echo ($no=='yes' ? 'checked' :''); ?> value="no">No
			
			<?php 
		}
}

add_action( 'save_post', 'save_postdata');

function save_postdata($postid)
{   
	global $post;
	if ($post->post_type=='survey') {
		if($_POST['action'] == 'editpost'){
			delete_post_meta($postid, '_survey_ended');
		}
	
		add_post_meta($postid, '_survey_ended', $_POST['survey_ended']);
	}
}




// starts here //

function update_user_role ($transaction_id) {
	$customer_id = it_exchange_get_transaction_customer($transaction_id);
	$customer_id = get_current_user_id();
	$customer = new WP_User($customer_id);
	$monthly_basic = mytheme_option('basic_registration_url_for_monthly');
	$monthly_contributing = mytheme_option('contributing_registration_url_for_monthly');

 	$yearly_basic = mytheme_option('basic_registration_url_for_yearly');
 	$yearly_contributing = mytheme_option('contributing_registration_url_for_yearly');
 
 	$transaction = it_exchange_get_transaction( $transaction_id );
 	$transaction_id= $transaction->ID;
 	$products = $transaction->cart_details->products;

  	foreach ( $products as $product ) {
		$product_id = $product['product_id'];
		if ($product_id == $monthly_basic) {
			// Remove role
			$customer->remove_role( 'subscriber' );
			// Add role
			$customer->add_role( 'basic' );
		}
		if ($product_id== $monthly_contributing) {
			// Remove role
			$customer->remove_role( 'subscriber' );
			// Add role
			 $customer->add_role( 'basic' );
		}  
		if ($product_id == $yearly_basic ) {
			// Remove role
			$customer->remove_role( 'subscriber' );
			// Add role
			$customer->add_role( 'contributing' );
			} 
		if ($product_id == $yearly_contributing) {
			// Remove role
			$customer->remove_role( 'subscriber' );
			// Add role
			$customer->add_role( 'contributing' );
		} 
	}
}

add_action ('it_exchange_add_transaction_success', 'update_user_role');


// * Determine if we need to track this transaction,
// * when the transaction status has been updated
// *
// * @param $transaction        IT_Exchange_Transaction
// * @param $old_status         string
// * @param $old_status_cleared bool
// 
//function pk_mixpanel_track_transaction_change_status( $transaction, $old_status, $old_status_cleared ) {
// if ( it_exchange_transaction_is_cleared_for_delivery( $transaction ) === true && $old_status_cleared === false ) {
//  	update_user_role($transaction->ID);
// }
//}
//
//add_action('it_exchange_update_transaction_status', 'pk_mixpanel_track_transaction_change_status', 10, 3);


?>