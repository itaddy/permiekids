<?php

/*
Written by Chris Jean for iThemes.com
Version 2.0.0

Version History
	1.0.0 - 2010-07-27
		Initial release version
	1.1.0 - 2011-10-24 - Chris Jean
		Added it_custom_taxonomy_{$this->_var}_filter_settings filter
	2.0.0 - 2011-12-09 - Chris Jean
		Structural rewrite to better handle updated WordPress taxonomy API
*/


if ( ! class_exists( 'ITTaxonomy' ) ) {
	it_classes_load( 'it-filterable-templates.php' );
	
	class ITTaxonomy {
		var $_var = '';
		var $_name = '';
		var $_name_plural = '';
		var $_attach_to_post_types = array();
		
		var $_settings = array();
		
		var $_default_terms = array();
		
		var $_priority = '';
		
		
		function ITTaxonomy() {
			$this->_setup_default_settings();
			
			if ( empty( $this->_template_file_base ) )
				$this->_template_file_base = str_replace( '_', '-', $this->_var );
			
			add_action( 'init', array( &$this, 'init' ) );
			add_action( 'deactivated_plugin', array( &$this, 'deactivate_plugin' ) );
			
			add_filter( 'template_include', array( &$this, '__filter_template_include' ) );
		}
		
		function init() {
			register_taxonomy( $this->_var, $this->_attach_to_post_types, $this->_settings );
			
			foreach ( (array) $this->_attach_to_post_types as $post_type ) {
				add_action( "manage_{$post_type}_posts_custom_column", array( &$this, '__display_custom_list_columns' ), 0, 2 );
				add_action( "manage_{$post_type}_posts_columns", array( &$this, '__add_custom_list_columns' ) );
			}
			
			if ( false === get_option( $this->_var . '_activated' ) )
				$this->_activate();
		}
		
		function __add_custom_list_columns( $columns ) {
			$columns[$this->_var] = $this->_name_plural;
			
			return $columns;
		}
		
		function __display_custom_list_columns( $column_name, $post_id ) {
			if ( $this->_var != $column_name )
				return;
			
			$terms = get_the_terms( $post_id, $this->_var );
			
			if ( ! empty( $terms ) ) {
				$post_type = get_post_type( $post_id );
				$out = array();
				
				foreach ( $terms as $term ) {
					$out[] = sprintf( '<a href="%s">%s</a>',
						esc_url( add_query_arg( array( 'post_type' => $post_type, $this->_var => $term->slug ), 'edit.php' ) ),
						esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, $this->_var, 'display' ) )
					);
				}
				echo join( ', ', $out );
			}
			else
				echo "&nbsp;";
		}
		
		function _activate() {
			$this->_flush_rewrite_rules();
			$this->_load_default_entries();
		}
		
		function _load_default_entries() {
			if ( wp_count_terms( $this->_var ) > 0 )
				return;
			
			foreach ( (array) $this->_default_terms as $term => $args )
				wp_insert_term( $term, $this->_var, $args );
		}
		
		function _flush_rewrite_rules() {
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
			
			update_option( $this->_var . '_activated', true );
		}
		
		function __filter_template_include( $template ) {
			if ( $this->_var !== get_query_var( 'taxonomy' ) )
				return $template;
			
			if ( is_archive() ) {
				$term = get_query_var( 'term' );
				
				if ( ! empty( $term ) )
					$new_template = locate_template( array( "{$this->_template_file_base}/archive-$term.php", "archive-{$this->_template_file_base}-$term.php", "{$this->_template_file_base}/archive.php", "archive-{$this->_template_file_base}.php", 'archive.php' ) );
				else
					$new_template = locate_template( array( "{$this->_template_file_base}/archive.php", "archive-{$this->_template_file_base}.php", 'archive.php' ) );
			}
			
			if ( ! empty( $new_template ) )
				return $new_template;
			return $template;
		}
		
		function deactivate_plugin() {
			delete_option( $this->_var . '_activated' );
		}
		
/*		function remove_meta_box_ui() {
			global $wp_meta_boxes;
			
			print_r( $wp_meta_boxes );
		}*/
		
		function register_meta_boxes() {
			global $post;
			$post_type = $post->post_type;
			
			if ( ! is_taxonomy_hierarchical( $this->_var ) )
				add_meta_box( 'tagsdiv-' . $this->_var, $this->_settings['labels']['name'], 'post_tags_meta_box', $post_type, 'side', $this->_priority, array( 'taxonomy' => $this->_var ) );
			else
				add_meta_box( $this->_var . 'div', $this->_settings['labels']['name'], 'post_categories_meta_box', $post_type, 'side', $this->_priority, array( 'taxonomy' => $this->_var ) );
		}
		
		function _setup_default_settings() {
			// For full list of available settings, see the register_taxonomy() function in wp-includes/taxonomy.php
			// Common settings: hierarchical, update_count_callback, query_var, public, show_ui, show_in_nav_menus,
			//   show_tagcloud, labels, rewrite
			$default_settings = array();
			
			$default_settings['labels'] = $this->_get_default_labels();
			
			if ( ! empty( $this->_slug ) )
				$default_settings['rewrite'] = array( 'slug' => $this->_slug );
			
			if ( method_exists( $this, 'update_count' ) )
				$default_settings['update_count_callback'] = array( &$this, 'update_count' );
			
			
			$this->_settings = array_merge( $default_settings, $this->_settings );
			
			$slug = apply_filters( "it_custom_taxonomy_{$this->_var}_filter_slug", '' );
			
			if ( ! empty( $slug ) )
				$this->_settings['rewrite'] = array( 'slug' => $slug );
			
			$this->_settings = apply_filters( "it_custom_taxonomy_{$this->_var}_filter_settings", $this->_settings );
		}
		
		function _get_default_labels() {
			$labels = array(
				'name'                       => $this->_name_plural,
				'singular_name'              => $this->_name,
				'search_items'               => "Search {$this->_name_plural}",
				'popular_items'              => "Popular {$this->_name_plural}",
				'all_items'                  => "All {$this->_name_plural}",
				'parent_item'                => "Parent {$this->_name}",
				'parent_item_colon'          => "Parent {$this->_name}:",
				'edit_item'                  => "Edit {$this->_name}",
				'update_item'                => "Update {$this->_name}",
				'add_new_item'               => "Add New {$this->_name}",
				'new_item_name'              => "New {$this->_name} Name",
				'separate_items_with_commas' => 'Separate ' . strtolower( $this->_name_plural ) . ' with commas',
				'add_or_remove_items'        => 'Add or remove ' . strtolower( $this->_name_plural ),
				'choose_from_most_used'      => 'Choose from the most used ' . strtolower( $this->_name_plural ),
			);
			
			return $labels;
		}
	}
}


?>
