<?php
/*
Plugin Name: GV Design Filters & Functions
Plugin URI: https://github.com/hreilly/gv-design-f-and-f/
description: Filters and functions for the GV Design website, including AJAX query modifications and pagination.
Version: 1.0
Author: Hannah Reilly
Author URI: https://github.com/hreilly/
*/

// Outline for All Plans

// define AJAX hooks for action

add_action( 'wp_ajax_gvff_plan_init', 'gvff_plan_init' );
add_action( 'wp_ajax_nopriv_gvff_plan_init', 'gvff_plan_init' );

function gvff_plan_init(){
   global $wp_query;
   wp_enqueue_script( 'gvff_plan_script', plugin_dir_url( __FILE__ ) . '/js/script.js', array(), '1.0', true );

   $max = $wp_query->max_num_pages;
   $paged = ( get_query_var('paged') > 1 ) ? get_query_var('paged') : 1;

// Pass PHP variables and functions to JS
   wp_localize_script( 'gvff_plan_script', 'gvff_plan_vars', array(
		 'startPage'  => $paged,
		 'maxPages'   => $max,
  	 'pagination' => gv_pagination(),
     'ajax_url'   => admin_url('admin-ajax.php'),
	    )
   );
}
add_action('template_redirect', 'gvff_plan_init');

?>
