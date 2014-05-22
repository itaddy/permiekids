<?php

function wpbootstrap_scripts_with_jquery()
{
	// Register the script like this for a theme:
	wp_register_script( 'custom-script', get_template_directory_uri() . '/bootstrap/js/bootstrap.js', array( 'jquery' ) );
	// For either a plugin or a theme, you can then enqueue the script:
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
?>