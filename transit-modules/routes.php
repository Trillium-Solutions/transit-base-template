<?php

/**
 * Creates the Route custom post type. Used by gtfs-utilities.php
 * in GTFS Update.
 *
 * @package NWOTA
 */


$labels = array(
		'name'               => _x( 'Routes', 'post type general name' ),
		'singular_name'      => _x( 'route', 'post type singular name' ),
		'menu_name'          => _x( 'Routes', 'admin menu'),
		'name_admin_bar'     => _x( 'Route', 'add new on admin bar'),
		'add_new'            => _x( 'Add New', 'route'),
		'add_new_item'       => __( 'Add New route'),
		'new_item'           => __( 'New route'),
		'edit_item'          => __( 'Edit Route'),
		'view_item'          => __( 'View Route'),
		'all_items'          => __( 'All Routes'),
		'search_items'       => __( 'Search Routes'),
		'parent_item_colon'  => __( 'Parent Routes:'),
		'not_found'          => __( 'No routes found.'),
		'not_found_in_trash' => __( 'No routes found in Trash.')
	);

$args = array(
		'menu_icon' 		 => 'dashicons-location-alt',
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => true,		
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'routes' ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'revisions', 'thumbnail', 'editor'),
        'register_meta_box_cb' => 'nwota_add_route_metabox',
	);

register_post_type( 'route', $args );

$route_fields = array(
    array(
        'uid'       => 'route_id',  // Name of the meta key
        'label'     => 'Route ID',  // Admin page label
        'type'      => 'text',      // Input type
        'helper'    => '',          // Field help text
    ),
    array(
        'uid'       => 'route_short_name',
        'label'     => 'Route Short Name',
        'type'      => 'text',
        'helper'    => '',
    ),
    array(
        'uid'       => 'route_long_name',
        'label'     => 'Route Long Name',
        'type'      => 'text',
        'helper'    => '',
    ),
    array(
        'uid'       => 'route_description',
        'label'     => 'Description',
        'type'      => 'text',
        'helper'    => '',
    ),
    array(
        'uid'       => 'route_color',
        'label'     => 'Route Color',
        'type'      => 'text',
        'helper'    => '',
    ),
    array(
        'uid'       => 'route_text_color',
        'label'     => 'Text Color',
        'type'      => 'text',
        'helper'    => '',
    ),
    array(
        'uid'       => 'route_sort_order',
        'label'     => 'Sort Order',
        'type'      => 'text',
        'helper'    => '',
    ),
    array(
        'uid'       => 'agency_id',
        'label'     => 'Agency ID',
        'type'      => 'text',
        'helper'    => '',
    ),
);

// Create Custom Meta Fields for Routes, based on GTFS fields
function nwota_add_route_metabox() {
    add_meta_box( 'nwota_route_fields', 'GTFS Route Fields', 'nwota_custom_metabox', 'route', 'normal', 'default');
}

function nwota_custom_metabox($post) {
    global $route_fields;
    // Create nonce for security, verify where data originated
    printf( '<input type="hidden" name="routemeta_noncename" id="routemeta_noncename" value="%s">', wp_create_nonce( 'save-meta-route-' . $post->ID ));
    foreach ( $route_fields as $field ) {
        $field_value = get_post_meta( $post->ID, $field['uid'], true);
        printf( '<label for="%1$s">%2$s</label>', $field['uid'], $field['label'] );
        printf( '<input type="%1$s" name="%2$s" value="%3$s" class="widefat" />', $field['type'], $field['uid'], $field_value );
        if( $helper = $field['helper'] ){
            printf( '<p class="description">%s</p>', $helper ); 
        }
    }
}

// Adapted from deluxeblogtips.com, original from Nathan Rice AgentPress theme
function nwota_save_meta($post_id, $post) {
    global $route_fields;
    
    // If the metabox hasn't been posted yet, return without saving
    if ( !isset( $_POST['routemeta_noncename'] ) ) {
            return $post_id;
    }
    
    // If wordpress is performing an autosave, return without saving
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
            return $post_id;
    }
    
    // If the post data came from a different screen, return without saving
    if ( !wp_verify_nonce( $_POST['routemeta_noncename'], 'save-meta-route-' . $post_id ) ) {
        return $post_id;
    }
    
    // If the user doesn't have proper capabilities, return without saving
    if ( !current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
    }
    
    // Save all of the metabox fields that have been created or updated
    foreach ( $route_fields as $field ) {
        $old_value = get_post_meta($post_id, $field['uid'], true);
        $new_value = $_POST[$field['uid']];
        
        // If the value has been created or changed
        if ($new_value && $new_value != $old_value) {
            update_post_meta($post_id, $field['uid'], $new_value);
        } elseif ('' == $new_value && $old_value) {
            // If value has been deleted
            delete_post_meta($post_id, $field['uid'], $old_value);
        }
    }
}

add_action('save_post', 'nwota_save_meta', 1, 2);

/******** Convenience Functions for Accessing Route Meta ************/
/* Display Route Meta with options from GTFS Settings */

// All functions the_route_[field]() are meant to be used either 
// within the loop for a route post, or with the post->ID set
// in the parameters

function the_route_title($post_id = null) {
    if ( empty($post_id) ) {
        global $post;
        $post_id = $post->ID;
    }
    $display_style = get_option('route_display');
    if ($display_style == 'long_name') {
        echo get_post_meta( $post_id, 'route_long_name', true);
    } else if ($display_style == 'short_name') {
        echo get_post_meta( $post_id, 'route_short_name', true);
    } else if ($display_style == "circle_name" ) {
        $circ = the_route_circle();
        echo $circ . get_post_meta( $post_id, 'route_long_name', true);
    } else {
        return;
    }
}

// Size is merely a class applied to the circle. Circle sizes can be 
// implemented in the CSS.
function get_route_circle($size = "medium", $post_id = null) {
    // Check first that route circles are applicable 
    if ( 'false' == get_option( 'use_route_circles' ) ) {
        return;
    }
    if ( empty($post_id) ) {
        global $post;
        $post_id = $post->ID;
    }
    $route_color = get_post_meta( $post_id, 'route_color', true);
    $text_color = get_post_meta( $post_id, 'route_text_color', true);
    $text = get_post_meta( $post_id, 'route_short_name', true);
    $html = sprintf('<span class="route-circle route-circle-%1$s" style="background-color: %2$s; color: %3$s;">%4$s</span>', $size, $route_color, $text_color, $text);
    return $html;
}

// Change to be consistent with other funtions, use post_id
function get_route_color($post_id = null) {
    if ( empty($post_id) ) {
        global $post;
        $post_id = $post->ID;
    }
	$color = get_post_meta( $post_id, 'route_color', true);
    if ( !$color ) {
        $color = "#777"; // Sets the default color when none available.
    }
	return $color;
}

function get_route_by_name($route_name) {
    $route = get_page_by_title($route_name, OBJECT, 'route');
    return $route;
}