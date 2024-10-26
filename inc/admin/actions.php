<?php

/**
 * This file is responsible to call actions or add actions.
 *
 * @link        https://lenderd.com
 * @since       1.0.0
 *
 * @package     mortgage_application
 * @sub-package mortgage_application/inc
 */

// If this file is called directly, abort.
defined( 'ABSPATH' ) or die( 'Access denied!' );

// Enqueue a script in the admin.
add_action( 'admin_enqueue_scripts', 'mapp_mortgage_application_enqueue_admin_scripts' );

// Add custom post type
add_action( 'init', 'mapp_mortgage_application_create_posttype' );

// Register custom field metabox
add_action( 'add_meta_boxes', 'mapp_mortgage_application_register_meta_boxes' );

// Add setting page
if ( is_admin() ) {
	add_action( 'admin_menu', 'mapp_mortgage_application_admin_menu' );
}

// For network site(Multisite) admin menu
if ( is_network_admin() ) {
	add_filter( 'network_admin_menu', 'mapp_mortgage_application_admin_menu' );
}

// Call network admin edit action to update network options
add_action( 'network_admin_edit_mapp_mortgage_application_update_network_options', 'mapp_mortgage_application_update_network_options' );

// Add or call admin default functionality
add_action( 'admin_init', 'mapp_mortgage_application_admin_init' );

// Add new status field in post table list.
add_filter( 'manage_mortgage_application_posts_columns', 'mapp_mortgage_application_filter_posts_columns' );

// Show new status field value in post table columns list.
add_action( 'manage_posts_custom_column', 'mapp_mortgage_application_show_posts_column_value', 10, 2 );

// Make a sortable columns in post listing table
add_filter( 'manage_edit-mortgage_application_sortable_columns', 'mapp_mortgage_application_sortable_columns' );

// Sort post listing data by custom sort field
add_action( 'pre_get_posts', 'mapp_mortgage_application_posts_orderby_status' );

// Change actions list or remove view button
add_filter( 'post_row_actions', 'mapp_mortgage_application_remove_actions', 10, 1 );

// Add status filtering
add_action( 'restrict_manage_posts', 'mapp_mortgage_application_status_filtering', 10 );
add_filter( 'parse_query', 'mapp_mortgage_application_filter_request_query', 10 );

// Add new actions in bulk actions
add_filter( 'bulk_actions-edit-mortgage_application', 'mapp_mortgage_application_bulk_actions', 20, 1 );
add_filter( 'handle_bulk_actions-edit-mortgage_application', 'mapp_mortgage_application_handle_bulk_actions', 10, 3 );

// This action allows logged in users to export application
add_action( 'admin_post_mortgage_application_export_applications', 'mapp_mortgage_application_export_applications_callback' );
add_action( 'admin_post_nopriv_mortgage_application_export_applications', 'mapp_mortgage_application_export_applications_callback' );

// Ajax actions

// Custom admin application reminder action
add_action( 'wp_ajax_mortgage_application_admin_send_reminder', 'mapp_mortgage_application_admin_send_reminder_callback' );
add_filter( 'wp_mail_content_type', 'mapp_set_content_type' );
function mapp_set_content_type( $content_type ) {
	return 'text/html';
}

// Color Picker
wp_enqueue_script( 'jquery' );
wp_enqueue_style( 'wp-color-picker' );
wp_enqueue_script( 'sl-script-handle', MAPP_MORTGAGE_APP_BASE_URL . 'assets/js/admin-js.js', array( 'wp-color-picker', 'jquery' ), true, true );

// add_filter('wp_mail_from_name', 'mortgage_wp_mail_from_name');
function mortgage_wp_mail_from_name( $from_name ) {
	// $from_name =  sanitize_text_field(get_front_mortgage_application_option('mortgage_application_mail_from_name', 'mortgage_application_form_network_settings'));
	$from_name = get_front_mortgage_application_option( 'mortgage_application_mail_from_name', 'mortgage_application_form_network_settings' );
	if ( strpos( $from_name, '[' ) !== false ) {
		$from_name = do_shortcode( $from_name );
	}
	return $from_name;
}
// Admin application send on webhook
add_action( 'wp_ajax_mortgage_application_admin_send_on_webhook', 'mortgage_application_admin_send_on_webhook_callback' );

// Send test webhook request
add_action( 'wp_ajax_mortgage_application_send_test_webhooks_request', 'mortgage_application_send_test_webhooks_request_callback' );

// Custom admin application reminder action
add_action( 'wp_ajax_mortgage_application_admin_terms_accept', 'mapp_mortgage_application_admin_terms_accept_callback' );
add_action( 'wp_ajax_mortgage_application_autocomplete_post_by_name', 'mortgage_application_autocomplete_post_by_name_callback' );
add_action( 'wp_ajax_mortgage_application_autocomplete_fields_by_name', 'mapp_mortgage_application_autocomplete_fields_by_name_callback' );
