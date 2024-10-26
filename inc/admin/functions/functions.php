<?php

/**
 * This file is responsible to call actions or add actions.
 *
 * @link        https://lenderd.com
 * @since       1.0.0
 *
 * @package     mortgage_application
 * @sub-package mortgage_application/inc
 *
 * phpcs:disable WordPress.WP.AlternativeFunctions, WordPress.Security.NonceVerification
 */
// If this file is called directly, abort.
defined( 'ABSPATH' ) or die( 'Access denied!' );
/**
 * Enqueue a script callback in the admin.
 *
 * @param int $hook Hook suffix for the current admin page.
 */
function mapp_mortgage_application_enqueue_admin_scripts( $hook ) {
	wp_register_script( 'ma_js_admin', MAPP_MORTGAGE_APP_BASE_URL . 'assets/js/admin-js.js', array( 'jquery', 'jquery-ui-autocomplete' ), '1.0', false );
	// Localize the script with url
	global $mortgage_application_form_fields;
	$url_array = array(
		'ajax_url'    => admin_url( 'admin-ajax.php' ),
		'home_url'    => site_url(),
		'plugin_path' => MAPP_MORTGAGE_APP_BASE_URL,
		'post_meta'   => $mortgage_application_form_fields,
	);
	wp_localize_script( 'ma_js_admin', 'mortgage_application', $url_array );
	wp_enqueue_script( 'ma_js_admin' );
	// enqueue select to script
	wp_enqueue_style( 'ma_css_admin', MAPP_MORTGAGE_APP_BASE_URL . 'assets/css/admin-style.css', array(), true );
}
/* add custom post type callback*/
function mapp_mortgage_application_create_posttype() {

	/*register mortgage application post type*/
	register_post_type(
		'mortgage_application',
		array(
			'labels'          => array(
				'name'          => __( 'Applications', '1003-mortgage-application' ),
				'singular_name' => __( 'Mortgage Application', '1003-mortgage-application' ),
				'add_new_item'  => __( 'Add New Application', '1003-mortgage-application' ),
				'new_item'      => __( 'New Application', '1003-mortgage-application' ),
				'edit_item'     => __( 'Edit Application', '1003-mortgage-application' ),
				'update_item'   => __( 'Update Application', '1003-mortgage-application' ),
				'view_item'     => __( 'View Application', '1003-mortgage-application' ),
				'not_found'     => __( 'Application Not found', '1003-mortgage-application' ),
			),
			'public'          => true,
			'has_archive'     => false,
			'menu_icon'       => 'https://8blocks.s3.amazonaws.com/plugins/1003/website/icon.png',

			'rewrite'         => array( 'slug' => 'mortgage_application' ),
			'supports'        => array( 'title', 'author', 'custom-fields' ),
			// 'supports'     => array( 'title', 'author'),
			'capability_type' => 'post',
			'capabilities'    => array(
				'create_posts' => 'do_not_allow', // false < WP 4.5, credit @Ewout
			),
			'map_meta_cap'    => true, // Set to `false`, if users are not allowed to edit/delete existing posts
		)
	);
}
/**
 * Add mortgage application status filtering
 *
 * @param string $post_type is show current post type
 **/
function mapp_mortgage_application_status_filtering( $post_type ) {
	if ( 'mortgage_application' !== $post_type ) {
		return; // filter your post
	}
	$selected = '';
	$results  = array(
		'80'  => __( 'Incomplete Applications', '1003-mortgage-application' ),
		'100' => __( 'Completed Applications', '1003-mortgage-application' ),
	);
	if ( isset( $_REQUEST['ma_status'] ) ) {
		$selected = sanitize_text_field( $_REQUEST['ma_status'] );
	}
	// build a dropdown list of status to filter by
	echo '<select id="ma_status" name="ma_status">';
	echo '<option value="">' . esc_html__( 'All Applications', '1003-mortgage-application' ) . ' </option>';
	foreach ( $results as $status => $status_text ) {
		echo '<option value="' . esc_attr( $status ) . '"' . selected( $selected, $status ) . '>' . esc_html( $status_text ) . ' </option>';
	}
	echo '</select>';
}
function mapp_mortgage_application_filter_request_query( $query ) {
	// modify the query only if it is admin and main query.
	if ( ! ( is_admin() && $query->is_main_query() ) ) {
		return $query;
	}
	// checking current post type is mortgage_application.
	if ( 'mortgage_application' !== $query->query['post_type'] ) {
		return $query;
	}
	// checking ma_status filter is selected.
	if ( isset( $_REQUEST['ma_status'] ) && '' !== $_REQUEST['ma_status'] ) {
		$term                            = sanitize_text_field( $_REQUEST['ma_status'] );
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$query->query_vars['meta_query'] = array(
			array(
				'key'     => 'application_status',
				'value'   => $term,
				'compate' => '=',
			),
		);
	}
	return $query;
}
/**
 * remove view link and change edit to view link
 *
 * @param $action show action list
 **/
function mapp_mortgage_application_remove_actions( $actions ) {
	if ( get_post_type() === 'mortgage_application' ) {
		unset( $actions['view'] );
		unset( $actions['inline hide-if-no-js'] );

		$urlQuery = array(
			'post'        => get_the_ID(),
			'export_type' => 'csv',
			'action'      => 'mortgage_application_export_applications',
		);

		$actions['edit'] = str_replace( 'Edit', 'View', $actions['edit'] );
		$actions['edit'] = str_replace( 'Edit', 'View', $actions['edit'] );

		$url = wp_nonce_url( admin_url( 'admin-post.php?' . http_build_query( $urlQuery, '', '&' ) ), 'mortgage_application_export_applications', 'mortgage_application_export_nonce' );

		$actions['csv'] = '<a href="' . $url . '">' . esc_html__( 'Export to CSV', '1003-mortgage-application' ) . '</a>';
		// retrieve the license from the database
		$license = trim( get_mortgage_application_option( 'ma_license_key' ) );
		$status  = get_mortgage_application_option( 'ma_license_key_status' );

		$urlQuery['export_type'] = 'fnm';

		if ( $status !== false && $status == 'valid' && $license !== false ) {
			$url = wp_nonce_url( admin_url( 'admin-post.php?' . http_build_query( $urlQuery, '', '&' ) ), 'mortgage_application_export_applications', 'mortgage_application_export_nonce' );

			$actions['fnm'] = '<a href="' . $url . '">' . esc_html__( 'Export to FNM 3.2', '1003-mortgage-application' ) . '</a>';

			$urlQuery['export_type'] = 'mismo';
			$url                     = wp_nonce_url( admin_url( 'admin-post.php?' . http_build_query( $urlQuery, '', '&' ) ), 'mortgage_application_export_applications', 'mortgage_application_export_nonce' );
			$actions['mismo']        = '<a href="' . $url . '">' . esc_html__( 'Export to MISMO 3.4', '1003-mortgage-application' ) . '</a>';
		}
	}
	return $actions;
}
/**
 * add actions in bulk dropdown
 *
 * @parma $actions array action list in array
 **/
function mapp_mortgage_application_bulk_actions( $actions ) {
	$actions['export_csv'] = __( 'Export to CSV', '1003-mortgage-application' );
	$license               = trim( get_mortgage_application_option( 'ma_license_key' ) );
	$status                = get_mortgage_application_option( 'ma_license_key_status' );
	if ( $status !== false && $status == 'valid' && $license !== false ) {
		$actions['export_fnm']   = __( 'Export to FNM 3.2', '1003-mortgage-application' );
		$actions['export_mismo'] = __( 'Export to MISMO 3.4', '1003-mortgage-application' );
	}

	unset( $actions['edit'] ); // remove edit action
	return $actions;
}
/**
 * register custom field metabox
 **/
function mapp_mortgage_application_register_meta_boxes() {
	add_meta_box( 'type_of_loan', __( 'Application Data', '1003-mortgage-application' ), 'mapp_mortgage_application_template_callback', 'mortgage_application' );
	add_meta_box( 'mortgage_application_action', __( 'Mortgage Application Action', '1003-mortgage-application' ), 'mapp_mortgage_application_action_template_callback', 'mortgage_application', 'side' );
}
/**
 * Meta box display callback.
 *
 * @param WP_Post $post Current post object.
 */
function mapp_mortgage_application_template_callback( $post ) {
	// add metabox template
	include_once MAPP_MORTGAGE_APP_BASE_PATH . 'inc/templates/mortgage_metabox.php';
}
/**
 * Meta box display callback.
 *
 * @param WP_Post $post Current post object.
 */
function mapp_mortgage_application_action_template_callback( $post ) {
	// add action metabox template
	include_once MAPP_MORTGAGE_APP_BASE_PATH . 'inc/templates/mortgage_action_metabox.php';
}
/**
 * add new field in post table list.
 *
 * @param columns $columns Current post columns array.
 */
function mapp_mortgage_application_filter_posts_columns( $columns ) {
	/*add new fields after title field*/
	$keys    = array_keys( $columns );
	$index   = array_search( 'title', $keys );
	$pos     = false === $index ? count( $array ) : $index + 1;
	$columns = array_merge(
		array_slice( $columns, 0, $pos ),
		array(
			'status'   => __( 'Status', '1003-mortgage-application' ),
			'loanType' => __( 'Loan Type', '1003-mortgage-application' ),
		),
		array_slice( $columns, $pos )
	);
	return $columns;
}
/**
 * show new field value in post table columns list.
 *
 * @param columns $columns Current post columns array,
 *          post_id $post_id to show post id
 */

function mapp_mortgage_application_show_posts_column_value( $column, $post_id ) {
	/*add new fields after title field*/
	switch ( $column ) {
		case 'status':
			$status = esc_attr( get_post_meta( $post_id, 'application_status', true ) );
			if ( isset( $status ) && ! empty( $status ) ) {
				echo '<b>' . esc_html( $status ) . '% ' . esc_html__( 'Completed', '1003-mortgage-application' ) . '</b>';
			}
			break;
		case 'loanType':
			$purpose = esc_attr( get_post_meta( $post_id, 'purpose', true ) );
			if ( isset( $purpose ) && ! empty( $purpose ) ) {
				echo esc_html( $purpose );
			}
			break;
	}
}
/**
 * make a sortable columns in post listing table callback
 *
 * @param columns $columns Current post sortable columns array,
 */
function mapp_mortgage_application_sortable_columns( $columns ) {
	$columns['status'] = 'application_status';
	return $columns;
}
/**
 * sort post listing data by custom sort field callback
 *
 * @param query $query Current post query object,
 **/
function mapp_mortgage_application_posts_orderby_status( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( 'application_status' === $query->get( 'orderby' ) ) {
		$query->set( 'orderby', 'meta_value' );
		$query->set( 'meta_key', 'application_status' );
		$query->set( 'meta_type', 'numeric' );
	}
}
/**
 * Adds a submenu setting page under a Mortgage Application type.
 */
function mapp_mortgage_application_admin_menu() {
	if ( is_network_admin() ) {
		add_menu_page(
			__( 'Mortgage Applications', '1003-mortgage-application' ),
			__( 'MTG Application', '1003-mortgage-application' ),
			'manage_options',
			'ma_setting',
			'mapp_mortgage_application_setting_callback',
			'https://8blocks.s3.amazonaws.com/plugins/1003/website/icon.png'
		);
	} else {
		// retrieve the license from the database
		add_submenu_page( 'edit.php?post_type=mortgage_application', __( 'Bulk Export', '1003-mortgage-application' ), __( 'Bulk Export', '1003-mortgage-application' ), 'manage_options', 'ma_export', 'mapp_mortgage_application_export_callback' );
		add_submenu_page( 'edit.php?post_type=mortgage_application', __( 'Settings', '1003-mortgage-application' ), __( 'Settings', '1003-mortgage-application' ), 'manage_options', 'ma_setting', 'mapp_mortgage_application_setting_callback' );
		// add_submenu_page( 'edit.php?post_type=mortgage_application', __( 'Files Uploads', '1003-mortgage-application' ), __( 'Files Uploads', '1003-mortgage-application' ), 'manage_options', 'ma_file_upload', 'mapp_mortgage_application_file_upload_callback');
	}
}

/* Display callback for Mortgage Application File Upload page.  */
function mapp_mortgage_application_file_upload_callback() {
	require_once MAPP_MORTGAGE_APP_BASE_PATH . 'inc/templates/mortgage_file_upload.php';
}


/**
 * Display callback for the Mortgage Application setting page.
 */
function mapp_mortgage_application_setting_callback() {
	// get user network setting option
	$options         = get_mortgage_application_option( 'mortgage_application_use_network_settings' );
	$use_network_val = ( $options == '0' ) ? '0' : '1';
	// get user network setting option
	$field_options         = get_mortgage_application_option( 'mortgage_application_use_form_network_settings' );
	$use_field_network_val = ( $field_options == '0' ) ? '0' : '1';

	// get user network submissions setting option
	$submissions_options         = get_mortgage_application_option( 'mortgage_submissions_use_form_network_settings' );
	$use_submissions_network_val = ( $submissions_options == '0' ) ? '0' : '1';

	// retrieve the license from the database
	$license = trim( get_mortgage_application_option( 'ma_license_key' ) );
	$status  = get_mortgage_application_option( 'ma_license_key_status' );
	// include setting template
	require_once MAPP_MORTGAGE_APP_BASE_PATH . 'inc/templates/mortgage_setting.php';
}

/**
 * update Network site setting callback
 */
function mapp_mortgage_application_update_network_options() {
	// print_r($_POST);
	// Check if current user is a site administrator
	if ( ! current_user_can( 'manage_network_options' ) ) {
		wp_die( 'You don\t have the privileges to do this operation (should be: site administrator).' );
	}
	// $_POST[ 'option_page' ] below comes from a hidden input that WordPress automatically generates for admin forms. The value equals to the admin page slug.
	$page_slug = sanitize_text_field( $_POST['option_page'] );
	// Check that the request is coming from the administration area
	check_admin_referer( $page_slug . '-options' );
	// Cycle through the settings we're submitting. If there are any changes, update them.
	global $new_whitelist_options;
	$options = $new_whitelist_options[ $page_slug ];
	// print_r( $options );

	foreach ( $options as $option ) {
		if ( isset( $_POST[ $option ] ) && ! empty( $_POST[ $option ] ) && ! isset( $_POST['mortgage_application_reset'] ) && empty( $_POST['mortgage_application_reset'] ) ) {
			// echo $option;
			// echo "<br/>";
			if ( 'mortgage_application_user_mail_message' == $option || 'mortgage_application_mail_message' == $option || 'mortgage_application_reminder_mail_message' == $option || 'mortgage_application_success_message' == $option || 'disclaimer_field_2' == $option || 'disclaimer_field_1' == $option || 'mortgage_application_webhooks' == $option ) {
				update_site_option( $option, wp_kses_post( $_POST[ $option ] ) );
			} elseif ( is_array( $_POST[ $option ] ) ) {
					// update_site_option( $option, sanitize_text_field($_POST[ $option ]));
					/*
					if($option == "mortgage_application_form_fields")
					{
						echo $option;
						print_r( $_POST[ $option ] );
						die("224545");
					}*/
					update_site_option( $option, serialize( $_POST[ $option ] ) );
			} else {
				// update_site_option( $option, sanitize_text_field($_POST[ $option ]));
				update_site_option( $option, $_POST[ $option ] );
			}
		} else {
			$default_value = MAPP_MORTGAGE_APP_BASE_DATA[ $option ];
			if ( 'mortgage_application_user_mail_message' == $option || 'mortgage_application_mail_message' == $option || 'mortgage_application_reminder_mail_message' == $option || 'mortgage_application_success_message' == $option || 'disclaimer_field_2' == $option || 'disclaimer_field_1' == $option || 'mortgage_application_webhooks' == $option ) {
				update_site_option( $option, wp_kses_post( $default_value ) );
			} elseif ( is_array( $default_value ) ) {
					// update_site_option( $option, sanitize_text_field($default_value));
					update_site_option( $option, $default_value );
			} else {
				// update_site_option( $option, sanitize_text_field($default_value));
					update_site_option( $option, $default_value );
			}
		}
	}
	// die();
	// Finally, after saving the settings, redirect to the settings page. ()
	$query_args = array( 'page' => 'ma_setting' );
	if ( $page_slug == 'ma_setting' ) {
		$query_args['action'] = 'general';
	} elseif ( $page_slug == 'license' ) {
		$query_args['action'] = 'license';
	} elseif ( $page_slug == 'ma_form_setting' ) {
		$query_args['action'] = 'form';
	} elseif ( $page_slug == 'ma_submissions_uploaded' ) {
		// retrieve the license from the database
		$license = trim( get_mortgage_application_option( 'ma_license_key' ) );
		$status  = get_mortgage_application_option( 'ma_license_key_status' );
		if ( $status !== false && $status == 'valid' && $license !== false ) {
			$query_args['action'] = 'ma_file_upload';
		}
	}
	$query_args['ma-settings-updated'] = 'true';
	wp_redirect( add_query_arg( $query_args, network_admin_url( 'admin.php' ) ) );
	exit();
}
/**
 * admin init callback.
 */
function mapp_mortgage_application_admin_init() {
	// retrieve the license from the database
	$license = trim( get_mortgage_application_option( 'ma_license_key' ) );
	$status  = get_mortgage_application_option( 'ma_license_key_status' );

	// register a new section in the "ma_setting" section, inside the "ma_setting" page
	add_settings_section( 'ma_setting', __( 'General Settings', '1003-mortgage-application' ), 'mapp_mortgage_application_general_setting_callback', 'ma_setting' );

	if ( is_multisite() && ! is_network_admin() ) {
		add_settings_field(
			'mortgage_application_use_network_settings',
			__( 'Use Network Settings', '1003-mortgage-application' ),
			'mapp_mortgage_application_checkbox',
			'ma_setting',
			'ma_setting',
			array(
				'name' => 'mortgage_application_use_network_settings',
				/*'placeholder' => 'Email address your application will reply to if different from above...',*/

			)
		);
		register_setting( 'ma_setting', 'mortgage_application_use_network_settings' );
	}
	// register a new fields in the "ma_setting" section, inside the "ma_setting" page
	// $text = 'mapp_mortgage_application_display_text_element';
	$text                             = 'mapp_mortgage_application_general_display_text_element_with_toggle';
	$toggle_input                     = 'mapp_mortgage_application_display_toggle_element';
	$textarea                         = 'mapp_mortgage_application_textarea';
	$text_editor                      = 'mapp_mortgage_application_text_editor';
	$color_input                      = 'mapp_mortgage_application_color_input';
	$select                           = 'mapp_mortgage_application_display_select_element';
	$download_file                    = ( $status !== false && $status == 'valid' && $license !== false ) ? '{mismo_file_url}, {fnm_file_url}, {csv_file_url}' : '';
	$client_receipt_value             = get_mortgage_application_option( 'mortgage_application_client_email_recipients' );
	$client_email_recipients_show_cls = ' client_email_recipients_show';
	if ( $client_receipt_value == '' || empty( $client_receipt_value ) ) {
		$client_email_recipients_show_cls = ' client_email_recipients_hide';
	}
	$allfields = array(
		array(
			'name'         => 'mortgage_application_button_color',
			'type'         => 'mapp_mortgage_application_color_input',
			'section_name' => 'ma_setting',
			'label'        => __( 'Button Color', '1003-mortgage-application' ),
			'group'        => 'ma_setting',
			'other'        => array(
				'name'  => 'mortgage_application_button_color',
				'class' => 'ma_setting',
				'id'    => 'mortgage_application_button_color',
			),
		),
		array(
			'name'         => 'mortgage_application_progress_bar_color',
			'type'         => 'mapp_mortgage_application_color_input',
			'section_name' => 'ma_setting',
			'label'        => __( 'Progress Bar Color', '1003-mortgage-application' ),
			'group'        => 'ma_setting',
			'other'        => array(
				'name'  => 'mortgage_application_progress_bar_color',
				'class' => 'ma_setting',
				'id'    => 'mortgage_application_progress_bar_color',
			),
		),
		array(
			'name'         => 'mortgage_application_email_recipients',
			'type'         => $text,
			'section_name' => 'ma_setting',
			'label'        => __( 'Email Recipient(s)', '1003-mortgage-application' ),
			'group'        => 'ma_setting',
			'other'        => array(
				'name'        => 'mortgage_application_email_recipients',
				'placeholder' => 'Enter Email Address (comma separated if multiple)',
				'class'       => 'ma_setting',
			),
		),
		array(
			'name'         => 'mortgage_application_mail_from_name',
			'type'         => $text,
			'section_name' => 'ma_setting',
			'label'        => __( 'From Name', '1003-mortgage-application' ),
			'group'        => 'ma_setting',
			'other'        => array(
				'name'        => 'mortgage_application_mail_from_name',
				'placeholder' => 'Name that your applicant will receive email from...',
				'class'       => 'ma_setting',
			),
		),
		array(
			'name'         => 'mortgage_application_mail_from',
			'type'         => $text,
			'section_name' => 'ma_setting',
			'label'        => __( 'From Email', '1003-mortgage-application' ),
			'group'        => 'ma_setting',
			'other'        => array(
				'name'        => 'mortgage_application_mail_from',
				'placeholder' => 'Email address your applicant will receive email from...',
				'class'       => 'ma_setting',
			),
		),
		array(
			'name'         => 'mortgage_application_mail_reply_to',
			'type'         => $text,
			'section_name' => 'ma_setting',
			'label'        => __( 'Reply-To Email', '1003-mortgage-application' ),
			'group'        => 'ma_setting',
			'other'        => array(
				'name'        => 'mortgage_application_mail_reply_to',
				'placeholder' => 'Email address your applicant will reply to...',
				'class'       => 'ma_setting',
			),
		),
		array(
			'name'         => 'mortgage_application_success_message',
			'type'         => $textarea,
			'section_name' => 'ma_setting',
			'label'        => __( 'Success Message', '1003-mortgage-application' ),
			'group'        => 'ma_setting',
			'other'        => array(
				'name'        => 'mortgage_application_success_message',
				'placeholder' => 'This message will appear after an application is completed successfully...',
				'class'       => 'ma_setting',
			),
		),
		array(
			'name'         => 'mortgage_application_client_email_recipients',
			'type'         => $toggle_input,
			'section_name' => 'ma_setting',
			'label'        => __( 'Client Email Send', '1003-mortgage-application' ),
			'group'        => 'ma_setting',
			'other'        => array(
				'name'  => 'mortgage_application_client_email_recipients',
				'class' => 'ma_setting',
				'id'    => 'mortgage_application_client_email_recipients_id',
			),
		),
		array(
			'name'         => 'mortgage_application_user_mail_subject',
			'type'         => $text,
			'section_name' => 'ma_setting',
			'label'        => __( 'Success Email Subject', '1003-mortgage-application' ),
			'group'        => 'ma_setting',
			'other'        => array(
				'name'        => 'mortgage_application_user_mail_subject',
				'placeholder' => 'This message will appear in subject of the emails sent to user',
				'class'       => 'ma_setting mortgage_application_client_email_recipients_cls' . $client_email_recipients_show_cls,
			),
		),
		array(
			'name'         => 'mortgage_application_user_mail_message',
			'type'         => $text_editor,
			'section_name' => 'ma_setting',
			'label'        => __( 'Success Email Message', '1003-mortgage-application' ),
			'group'        => 'ma_setting',
			'other'        => array(
				'name'        => 'mortgage_application_user_mail_message',
				'placeholder' => 'This message will appear at the top of the emails sent to your application user...',
				'class'       => 'ma_setting mortgage_application_client_email_recipients_cls' . $client_email_recipients_show_cls,
				'description' => '<b>NOTE:</b> Use {all_fields}, {first_name}, {last_name}, {email}, {edit_url} to show fields values',
			),
		),
		array(
			'name'         => 'mortgage_application_mail_subject',
			'type'         => $text,
			'section_name' => 'ma_setting',
			'label'        => __( 'Admin Email Subject', '1003-mortgage-application' ),
			'group'        => 'ma_setting',
			'other'        => array(
				'name'        => 'mortgage_application_mail_subject',
				'placeholder' => 'The subject line of the email sent to applicants upon completion...',
				'class'       => 'ma_setting',
			),
		),
		array(
			'name'         => 'mortgage_application_mail_message',
			'type'         => $text_editor,
			'section_name' => 'ma_setting',
			'label'        => __( 'Admin Email Message', '1003-mortgage-application' ),
			'group'        => 'ma_setting',
			'other'        => array(
				'name'        => 'mortgage_application_mail_message',
				'placeholder' => 'This message will appear at the top of the emails sent to your application...',
				'class'       => 'ma_setting',
				'description' => '<b>NOTE:</b> Use {all_fields}, {first_name}, {last_name}, {email}, {edit_url} ' . $download_file . ' to show fields values',
			),
		),
		array(
			'name'         => 'mortgage_application_reminder_mail_subject',
			'type'         => $text,
			'section_name' => 'ma_setting',
			'label'        => __( 'Reminder Email Subject', '1003-mortgage-application' ),
			'group'        => 'ma_setting',
			'other'        => array(
				'name'        => 'mortgage_application_reminder_mail_subject',
				'placeholder' => 'This message will appear in subject of the emails sent to...',
				'class'       => 'ma_setting',
			),
		),
		array(
			'name'         => 'mortgage_application_reminder_mail_message',
			'type'         => $text_editor,
			'section_name' => 'ma_setting',
			'label'        => __( 'Reminder Email Message', '1003-mortgage-application' ),
			'group'        => 'ma_setting',
			'other'        => array(
				'name'        => 'mortgage_application_reminder_mail_message',
				'placeholder' => 'This message will appear at the top of the emails sent to your application...',
				'class'       => 'ma_setting',
				'description' => '<b>NOTE:</b> Use {all_fields}, {first_name}, {last_name}, {email}, {edit_url}, ' . $download_file . ' to show fields values',
			),
		),
		/*
		array(
			'name' => 'mortgage_application_google_map_api_key',
			'type'=> $text,
			'section_name'=>'ma_setting',
			'label'=> __( 'Google Map API Key', '1003-mortgage-application' ),
			'group'=>'ma_setting',
			'other' => array(
						'name' => 'mortgage_application_google_map_api_key',
						'placeholder' => 'Google Map API key for activating Google Map',
						'class' => 'ma_setting'
				)
			),*/
	);

	if ( $status !== false && $status == 'valid' && $license !== false ) {
		$allfields[] = array(
			'name'         => 'mortgage_application_webhooks',
			'type'         => $textarea,
			'section_name' => 'ma_setting',
			'label'        => __( 'Webhooks', '1003-mortgage-application' ),
			'group'        => 'ma_setting',
			'other'        => array(
				'name'        => 'mortgage_application_webhooks',
				'placeholder' => 'Webhook URL (one per line)',
				'class'       => 'ma_setting',
				'description' => 'Optional webhook URLs to send new application data to. One webhook URL per line.',
			),
		);
		$allfields[] = array(
			'name'         => 'mortgage_application_test_webhooks',
			'type'         => 'mapp_mortgage_application_button',
			'section_name' => 'ma_setting',
			'label'        => __( 'Test webhooks', '1003-mortgage-application' ),
			'group'        => 'ma_setting',
			'other'        => array(
				'name'        => 'mortgage_application_test_webhooks',
				'id'          => 'mortgage_application_test_webhooks',
				'class'       => 'ma_setting',
				'description' => 'Send a test lead to each specified webhook above (Must Save Webhooks prior to test.)',
				'data-nonce'  => wp_create_nonce( 'mortgage_application_test_webhooks' ),
				'value'       => __( 'Send Test Webhook', '1003-mortgage-application' ),
			),
		);
	}

	$allfields[] = array(
		'name'         => 'mortgage_application_reset',
		'type'         => 'mapp_mortgage_application_reset_checkbox',
		'section_name' => 'ma_setting',
		'label'        => __( 'Restore Defaults', '1003-mortgage-application' ),
		'group'        => 'ma_setting',
		'other'        => array(
			'name'  => 'mortgage_application_reset',
			'class' => 'ma_setting',
		),
	);

	foreach ( $allfields as $key => $val ) {
		add_settings_field(
			$val['name'],
			$val['label'],
			$val['type'],
			$val['section_name'],
			$val['group'],
			$val['other']
		);

		register_setting( $val['section_name'], $val['name'] );
	}
	register_setting( 'ma_setting', 'mortgage_application_ma_setting' );
	if ( $status !== false && $status == 'valid' && $license !== false ) {
		/* register fields for submissions uploaded */
		add_settings_section( 'ma_submissions_uploaded', __( 'Uploaded Files', '1003-mortgage-application' ), 'mapp_mortgage_application_ma_submissions_uploaded_callback', 'ma_submissions_uploaded' );
		if ( is_multisite() && ! is_network_admin() ) {
			add_settings_field(
				'mortgage_submissions_use_form_network_settings',
				__( 'Use Network Settings', '1003-mortgage-application' ),
				'mapp_mortgage_application_checkbox',
				'ma_submissions_uploaded',
				'ma_submissions_uploaded',
				array(
					'name' => 'mortgage_submissions_use_form_network_settings',
				)
			);
			register_setting( 'ma_submissions_uploaded', 'mortgage_submissions_use_form_network_settings' );
		}

		add_settings_field(
			'mortgage_ma_submissions_email_to',
			__( 'Email Recipient(s)', '1003-mortgage-application' ),
			$text,
			'ma_submissions_uploaded',
			'ma_submissions_uploaded',
			array(
				'name'        => 'mortgage_ma_submissions_email_to',
				'class'       => 'ma_submissions_uploaded',
				'placeholder' => 'Enter Email Address (comma separated if multiple)',

			)
		);
		register_setting( 'ma_submissions_uploaded', 'mortgage_ma_submissions_email_to' );
		add_settings_field(
			'mortgage_ma_submissions_reply_to',
			__( 'Reply-To Email', '1003-mortgage-application' ),
			$text,
			'ma_submissions_uploaded',
			'ma_submissions_uploaded',
			array(
				'name'        => 'mortgage_ma_submissions_reply_to',
				'class'       => 'ma_submissions_uploaded',
				'placeholder' => 'Email address your applicant will reply to...',
			)
		);
		register_setting( 'ma_submissions_uploaded', 'mortgage_ma_submissions_reply_to' );

		add_settings_field(
			'mortgage_ma_submissions_subject',
			__( 'Email Subject', '1003-mortgage-application' ),
			$text,
			'ma_submissions_uploaded',
			'ma_submissions_uploaded',
			array(
				'name'        => 'mortgage_ma_submissions_subject',
				'class'       => 'ma_submissions_uploaded',
				'placeholder' => 'Ex. New File(s) Uploaded',
				'value'       => 'New File(s) Uploaded',

			)
		);
		register_setting( 'ma_submissions_uploaded', 'mortgage_ma_submissions_subject' );

		add_settings_field(
			'mortgage_application_submision_message',
			__( 'Message', '1003-mortgage-application' ),
			$text_editor,
			'ma_submissions_uploaded',
			'ma_submissions_uploaded',
			array(
				'name'        => 'mortgage_application_submision_message',
				'class'       => 'ma_submissions_uploaded',
				'description' => '<b>NOTE:</b> Use {docs}, {name} to show field values from submission',
				'value'       => '<h3 style="margin: 0 0 20px; font-size: 24px;">New Files Uploaded by {name}</h3>
{docs}',
			)
		);
		register_setting( 'ma_submissions_uploaded', 'mortgage_application_submision_message' );

		add_settings_field(
			'mortgage_ma_submissions_client_subject',
			__( 'Client Email Subject', '1003-mortgage-application' ),
			$text,
			'ma_submissions_uploaded',
			'ma_submissions_uploaded',
			array(
				'name'        => 'mortgage_ma_submissions_client_subject',
				'class'       => 'ma_submissions_uploaded',
				'placeholder' => 'Client Form Submission',
				'value'       => 'Thank You {name}!',

			)
		);
		register_setting( 'ma_submissions_uploaded', 'mortgage_ma_submissions_client_subject' );

		add_settings_field(
			'mortgage_application_submision_client_message',
			__( 'Client Email Message', '1003-mortgage-application' ),
			$text_editor,
			'ma_submissions_uploaded',
			'ma_submissions_uploaded',
			array(
				'name'        => 'mortgage_application_submision_client_message',
				'class'       => 'ma_submissions_uploaded',
				'description' => '<b>NOTE:</b> Use {docs}, {name} to show field values from submission',
				'value'       => '<h3 style="margin: 0 0 20px; font-size: 24px;">Your files have been uploaded successfully!</h3><br>{docs}',
			)
		);
		register_setting( 'ma_submissions_uploaded', 'mortgage_application_submision_client_message' );

		add_settings_field(
			'mortgage_ma_submissions_download_limit',
			__( 'Download Limit', '1003-mortgage-application' ),
			$select,
			'ma_submissions_uploaded',
			'ma_submissions_uploaded',
			array(
				'name'        => 'mortgage_ma_submissions_download_limit',
				'description' => '<b>NOTE:</b> Number of times file(s) can be downloaded',
				'class'       => 'ma_submissions_uploaded',
				'options'     => 5,
			)
		);
		register_setting( 'ma_submissions_uploaded', 'mortgage_ma_submissions_download_limit' );

		add_settings_field(
			'mortgage_ma_submissions_deleted_file',
			__( 'Delete File After', '1003-mortgage-application' ),
			$select,
			'ma_submissions_uploaded',
			'ma_submissions_uploaded',
			array(
				'name'        => 'mortgage_ma_submissions_deleted_file',
				'description' => '<b>NOTE:</b> Number of days before file(s) delete automatically',
				'class'       => 'ma_submissions_uploaded',
				'options'     => 10,
			)
		);
		register_setting( 'ma_submissions_uploaded', 'mortgage_ma_submissions_deleted_file' );

		add_settings_field(
			'mortgage_ma_submissions_file_extension',
			__( 'File Extension', '1003-mortgage-application' ),
			$text,
			'ma_submissions_uploaded',
			'ma_submissions_uploaded',
			array(
				'name'        => 'mortgage_ma_submissions_file_extension',
				'description' => '<b>NOTE:</b> Only Set file extensions can be uploaded.',
				'class'       => 'ma_submissions_uploaded',
				'placeholder' => 'pdf,jpg,jpeg,png',
			)
		);
		register_setting( 'ma_submissions_uploaded', 'mortgage_ma_submissions_file_extension' );
	}

	// register a new section in the "ma_form_setting" section, inside the "ma_setting" page
	add_settings_section( 'ma_form_setting', __( 'Form Settings', '1003-mortgage-application' ), 'mapp_mortgage_application_form_setting_callback', 'ma_form_setting' );

	if ( is_multisite() && ! is_network_admin() ) {
		add_settings_field(
			'mortgage_application_use_form_network_settings',
			__( 'Use Network Settings', '1003-mortgage-application' ),
			'mapp_mortgage_application_checkbox',
			'ma_form_setting',
			'ma_form_setting',
			array(
				'name' => 'mortgage_application_use_form_network_settings',
			)
		);
		register_setting( 'ma_form_setting', 'mortgage_application_use_form_network_settings' );
	}
	// register a new fields in the "ma_form_setting" section, inside the "ma_setting" page
	$text        = 'mapp_mortgage_application_display_text_element_with_toggle';
	$textarea    = 'mapp_mortgage_application_textarea';
	$text_editor = 'mapp_mortgage_application_text_editor';
	$allfields   = array();
	global $mortgage_application_form_fields, $mortgage_application_required_form_fields;

	if ( isset( $mortgage_application_form_fields ) && is_array( $mortgage_application_form_fields ) ) {
		foreach ( $mortgage_application_form_fields as $form_field_key => $form_field_label ) {
			$allfields[] = array(
				'name'         => 'mortgage_application_label_' . $form_field_key,
				'type'         => $text,
				'section_name' => 'ma_form_setting',
				'label'        => $form_field_label,
				'group'        => 'ma_form_setting',
				'other'        => array(
					'name'        => 'mortgage_application_label_' . $form_field_key,
					'class'       => 'ma_form_setting',
					'placeholder' => 'Default: ' . $form_field_label,
					'field'       => ( is_array( $mortgage_application_required_form_fields ) && in_array( $form_field_key, $mortgage_application_required_form_fields ) ? '' : $form_field_key ),
				),
			);
		}
	}
	/**
	 * register all fields and call(add) all fields at setting page
	 */
	if ( isset( $allfields ) && is_array( $allfields ) ) {
		foreach ( $allfields as $key => $val ) {
			add_settings_field(
				$val['name'],
				$val['label'],
				$val['type'],
				$val['section_name'],
				$val['group'],
				$val['other']
			);
			register_setting( $val['section_name'], $val['name'] );
		}
	}
	// register form fields
	register_setting( 'ma_form_setting', 'mortgage_application_form_fields' );
	// Disclaimer fields
	add_settings_field(
		'disclaimer_field_1',
		__( 'Application Disclaimer', '1003-mortgage-application' ),
		$text_editor,
		'ma_form_setting',
		'ma_form_setting',
		array(
			'name'  => 'disclaimer_field_1',
			'class' => 'ma_form_setting',
		)
	);
	register_setting( 'ma_form_setting', 'disclaimer_field_1' );

	add_settings_field(
		'mortgage_application_reset',
		__( 'Restore Defaults', '1003-mortgage-application' ),
		'mapp_mortgage_application_reset_checkbox',
		'ma_form_setting',
		'ma_form_setting',
		array(
			'name'  => 'mortgage_application_reset',
			'class' => 'ma_form_setting',
		)
	);
	register_setting( 'ma_form_setting', 'mortgage_application_reset' );
}

/**
 * Settings section to display Mortgage Application General Settings callback.
 *
 * @param array $args Display arguments.
 */
function mapp_mortgage_application_general_setting_callback( $args ) {
}
/**
 * Settings section to display Mortgage Application Form Settings callback.
 *
 * @param array $args Display arguments.
 */
function mapp_mortgage_application_form_setting_callback( $args ) {
}

/**
 * Settings section to display Mortgage Application Submissions Uploaded callback.
 */
function mapp_mortgage_application_ma_submissions_uploaded_callback( $args ) {
}


/**
 * input text type callback
 *
 * @perm $args input attribute as name, class etc.
 **/
function mapp_mortgage_application_display_select_element( $args ) {
	$options = sanitize_text_field( get_mortgage_application_option( $args['name'] ) ); ?>
	<div class="input_itself">
		<select name="<?php echo esc_attr( $args['name'] ); ?>" class="<?php echo ( isset( $args['class'] ) && ! empty( $args['class'] ) ? esc_attr( $args['class'] ) : '' ); ?>" id="<?php echo ( isset( $args['id'] ) ? esc_attr( $args['id'] ) : '' ); ?>">
			<option value="">Select Download Limit</option>
			<?php
			for ( $option_count = 1; $option_count <= $args['options']; $option_count++ ) {
				if ( isset( $options ) && $options == $option_count ) {
					?>
			<option value="<?php echo esc_attr( $option_count ); ?>" selected="selected"><?php echo esc_html( $option_count ); ?></option>
										<?php
				} else {
					?>
					<option value="<?php echo esc_attr( $option_count ); ?>"><?php echo esc_html( $option_count ); ?></option>
					<?php
				}
			}
			?>
		</select>
	</div>

	<?php

	// print_r($options);
	if ( isset( $args['description'] ) && ! empty( $args['description'] ) ) {
		?>
		<div class="input_description">
			<?php echo esc_html( $args['description'] ); ?>
		</div>
		<?php
	}
}

/**
 * input text type callback
 *
 * @perm $args input attribute as name, class etc.
 **/
function mapp_mortgage_application_display_toggle_element( $args ) {
	$options = get_mortgage_application_option( $args['name'] );
	$checked = '';
	if ( ! empty( $options ) ) {
		$checked = 'checked';
	}
	?>
	<div class="input-field">
		<label class="switch">
			<input class="<?php echo ( isset( $args['class'] ) && ! empty( $args['class'] ) ? esc_attr( $args['class'] ) : '' ); ?>" type="checkbox" id="<?php echo ( isset( $args['id'] ) ? esc_attr( $args['id'] ) : '' ); ?>" name="<?php echo esc_attr( $args['name'] ); ?>" <?php echo esc_attr( $checked ); ?> id="<?php echo ( isset( $args['id'] ) ? esc_attr( $args['id'] ) : '' ); ?>">
			<span class="slider round"></span>
		</label>
	</div>
	<?php
}

/**
 * input text type callback
 *
 * @perm $args input attribute as name, class etc.
 **/
function mapp_mortgage_application_general_display_text_element_with_toggle( $args ) {
	$options        = get_mortgage_application_option( $args['name'] );
	$fields_options = get_mortgage_application_option( 'mortgage_application_ma_setting' );
	if ( ! empty( $fields_options ) && ! is_array( $fields_options ) ) {
		$fields_options = unserialize( $fields_options );
	}
	$checked = '';
	if ( isset( $args['field'] ) && ! empty( $args['field'] ) && is_array( $fields_options ) && array_key_exists( $args['field'], $fields_options ) ) {
		$checked = 'checked';
	}
	?>
	<div class="input-field">
		<input type="text" name="<?php echo esc_attr( $args['name'] ); ?>" class="<?php echo ( isset( $args['class'] ) && ! empty( $args['class'] ) ? esc_attr( $args['class'] ) : '' ); ?>" placeholder="<?php echo esc_html( ( isset( $args['placeholder'] ) && ! empty( $args['placeholder'] ) ? esc_attr( $args['placeholder'] ) : '' ) ); ?>" value="<?php echo isset( $options ) ? esc_attr( $options ) : ''; ?>" id="<?php echo ( isset( $args['id'] ) ? esc_attr( $args['id'] ) : '' ); ?>" size="64" />
	</div>
	<?php
	if ( ! empty( $args['field'] ) && isset( $args['field'] ) ) {
		?>
		<label class="switch">
			<input type="checkbox" value="1" name="mortgage_application_ma_setting[<?php echo ( isset( $args['field'] ) ? esc_attr( $args['field'] ) : '' ); ?>]" <?php echo esc_attr( $checked ); ?>>
			<span class="slider round"></span>
		</label>
		<?php
	}
}
/**
 * input text type callback
 *
 * @perm $args input attribute as name, class etc.
 **/
function mapp_mortgage_application_display_text_element_with_toggle( $args ) {
	$options        = get_mortgage_application_option( $args['name'] );
	$fields_options = get_mortgage_application_option( 'mortgage_application_form_fields' );
	if ( ! empty( $fields_options ) && ! is_array( $fields_options ) ) {
		$fields_options = unserialize( $fields_options );
	}
	$checked = '';
	if ( isset( $args['field'] ) && ! empty( $args['field'] ) && is_array( $fields_options ) && array_key_exists( $args['field'], $fields_options ) ) {
		$checked = 'checked';
	}
	?>
	<div class="input-field">
		<input type="text" name="<?php echo esc_attr( $args['name'] ); ?>" class="<?php echo ( isset( $args['class'] ) && ! empty( $args['class'] ) ? esc_attr( $args['class'] ) : '' ); ?>" placeholder="<?php echo esc_html( ( isset( $args['placeholder'] ) && ! empty( $args['placeholder'] ) ? esc_attr( $args['placeholder'] ) : '' ) ); ?>" value="<?php echo isset( $options ) ? esc_attr( $options ) : ''; ?>" id="<?php echo ( isset( $args['id'] ) ? esc_attr( $args['id'] ) : '' ); ?>" size="64" />
	</div>
	<?php
	if ( ! empty( $args['field'] ) && isset( $args['field'] ) ) {
		?>
		<label class="switch">
			<input type="checkbox" value="1" name="mortgage_application_form_fields[<?php echo ( isset( $args['field'] ) ? esc_attr( $args['field'] ) : '' ); ?>]" <?php echo esc_attr( $checked ); ?>>
			<span class="slider round"></span>
		</label>
		<?php
	}
}
/**
 * input text type callback
 *
 * @perm $args input attribute as name, class etc.
 **/
function mapp_mortgage_application_display_text_element( $args ) {
	$options = sanitize_text_field( get_mortgage_application_option( $args['name'] ) );
	?>
	<div class="input_itself">
		<input type="text" name="<?php echo esc_attr( $args['name'] ); ?>" class="<?php echo ( isset( $args['class'] ) && ! empty( $args['class'] ) ? esc_attr( $args['class'] ) : '' ); ?>" placeholder="<?php echo esc_html( ( isset( $args['placeholder'] ) && ! empty( $args['placeholder'] ) ? esc_attr( $args['placeholder'] ) : '' ) ); ?>" value="<?php echo isset( $options ) ? esc_attr( $options ) : ''; ?>" id="<?php echo ( isset( $args['id'] ) ? esc_attr( $args['id'] ) : '' ); ?>" size="64" />
	</div>

	<?php

	// print_r($options);
	if ( isset( $args['description'] ) && ! empty( $args['description'] ) ) {
		?>
		<div class="input_description">
			<?php echo esc_html( $args['description'] ); ?>
		</div>
		<?php
	}
}

/**
 * input text type callback
 *
 * @perm $args input attribute as name, class etc.
 **/
function mapp_mortgage_application_color_input( $args ) {
	$options = get_mortgage_application_option( $args['name'] );
	?>
	<input type="text" name="<?php echo esc_attr( $args['name'] ); ?>" class="<?php echo esc_attr( $args['class'] ); ?> color-picker" placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" value="<?php echo isset( $options ) && ! empty( $options ) ? esc_attr( $options ) : '#bada55'; ?>" id="<?php echo esc_attr( $args['id'] ); ?>" size="64" />
	<?php
}



/**
 * input textarea type callback
 *
 * @perm $args input attribute as name, class etc.
 **/
function mapp_mortgage_application_textarea( $args ) {
	$options = get_mortgage_application_option( $args['name'] );
	?>
	<div class="input_itself">
		<textarea name="<?php echo esc_attr( $args['name'] ); ?>" class="<?php echo ( isset( $args['class'] ) ? esc_attr( $args['class'] ) : '' ); ?>" placeholder="<?php echo esc_html( ( isset( $args['placeholder'] ) ? $args['placeholder'] : '' ), '1003-mortgage-application' ); ?>" rows="5" cols="65" size="64"><?php echo isset( $options ) ? esc_textarea( $options ) : ''; ?></textarea>
	</div>
	<?php
	if ( isset( $args['description'] ) && ! empty( $args['description'] ) ) {
		?>
		<div class="input_description">
			<?php echo esc_html( $args['description'] ); ?>
		</div>
		<?php
	}
}
/**
 * input wp_editor type callback
 *
 * @perm $args input attribute as name, class etc.
 **/
function mapp_mortgage_application_text_editor( $args ) {
	$options = get_mortgage_application_option( $args['name'] );
	$setting = array(
		'editor_class' => ( isset( $args['class'] ) && ! empty( $args['class'] ) ? $args['class'] : '' ),
	);

	echo '<div class="input_itself">' . wp_kses_post( wp_editor( $options, $args['name'] ) ) . '</div>';
	if ( isset( $args['description'] ) && ! empty( $args['description'] ) ) {
		?>
		<div class="input_description">
			<?php echo esc_html( $args['description'] ); ?>
		</div>
		<?php
	}
}
/**
 * input check box type callback
 *
 * @perm $args input attribute as name, class etc.
 **/
function mapp_mortgage_application_button( $args ) {
	$options = get_mortgage_application_option( $args['name'] );
	$class   = ( isset( $args['class'] ) && ! empty( $args['class'] ) ? $args['class'] : '' );
	echo '<div class="input_itself checkbox">';
	echo '<input type="button" name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( $class ) . '  button-primary" data-nonce="' . esc_attr( $args['data-nonce'] ) . '" value="' . esc_attr( $args['value'] ) . '" />';
	echo '</div>';
	if ( isset( $args['description'] ) && ! empty( $args['description'] ) ) {
		?>
		<div class="input_description checkbox">
			<?php echo esc_html( $args['description'] ); ?>
		</div>
		<?php
	}
}
/**
 * input check box type callback
 *
 * @perm $args input attribute as name, class etc.
 **/
function mapp_mortgage_application_checkbox( $args ) {
	$options = get_mortgage_application_option( $args['name'] );
	$class   = ( isset( $args['class'] ) && ! empty( $args['class'] ) ? $args['class'] : '' );
	echo '<div class="input_itself">';
	if ( ! empty( $args['options'] ) && isset( $args['options'] ) ) {
		foreach ( $args['options'] as $option_key => $option_name ) {
			$checked = '';
			if ( ! empty( $options ) && array_key_exists( $option_key, $options ) ) {
				$checked = 'checked';
			}
			printf(
				'<div class="single-row"><input type="checkbox" name="%1$s[%2$s]" value="%4$s" class="%5$s" %6$s><label>%3$s</label></div>',
				esc_attr( $args['name'] ),
				esc_attr( $option_key ),
				esc_html( $option_name ),
				1,
				esc_attr( $class ),
				esc_attr( $checked )
			);
		}
	} else {
		echo '<input type="checkbox" name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( $class ) . '" value="1"' . ( ! isset( $options ) || ( isset( $options ) && $options == '0' ) ? 'checked' : '' ) . '/>';
	}
	echo '</div>';
	if ( isset( $args['description'] ) && ! empty( $args['description'] ) ) {
		?>
		<div class="input_description">
			<?php echo esc_html( $args['description'] ); ?>
		</div>
		<?php
	}
}
/**

/**
 * input check box type callback
 *
 * @perm $args input attribute as name, class etc.
 **/
function mapp_mortgage_application_reset_checkbox( $args ) {
	$class = ( isset( $args['class'] ) && ! empty( $args['class'] ) ? $args['class'] : '' );
	echo '<div class="input_itself">';
	echo '<input type="checkbox" name="' . esc_attr( $args['name'] ) . '" id="' . esc_attr( $args['id'] ) . '" class="' . esc_attr( $class ) . '" value="1"/>';
	echo '</div>';
	if ( isset( $args['description'] ) && ! empty( $args['description'] ) ) {
		?>
		<div class="input_description">
			<?php echo esc_html( $args['description'] ); ?>
		</div>
		<?php
	}
}
/**
 * get option base on site type
 *
 * @perm $option_name name of option
 **/
function get_mortgage_application_option( $option_name ) {
	if ( is_network_admin() || ( is_multisite() && $option_name == 'ma_license_key' ) || ( is_multisite() && $option_name == 'ma_license_key_status' ) ) {
		return get_site_option( $option_name );
	} else {
		return get_option( $option_name );
	}
}
/**
 * update option base on site type
 *
 * @perm $option_name name of option
 **/
function mapp_update_mortgage_application_option( $option_name, $value ) {
	if ( is_multisite() ) {
		update_site_option( $option_name, sanitize_text_field( $value ) );
	} else {
		update_option( $option_name, sanitize_text_field( $value ) );
	}
}
/**
 * delete option base on site type
 *
 * @perm $option_name name of option
 **/
function mapp_delete_mortgage_application_option( $option_name ) {
	if ( is_multisite() ) {
		delete_site_option( $option_name );
	} else {
		delete_option( $option_name );
	}
}
/**
 * custom admin application reminder action callback
 **/
function mapp_mortgage_application_admin_send_reminder_callback() {
	/* check nonce */
	check_ajax_referer( 'edit_post_reminder', 'nonce_data' );
	// get rquest data
	$post_id = $_POST['post_id'];
	/*get email general settings*/
	$recipient_list = array();
	$message        = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_application_reminder_mail_message', 'mortgage_application_use_network_settings' ) );
	if ( strpos( $message, '[' ) !== false ) {
		$message = do_shortcode( $message );
	}
	$message = ( ! empty( $message ) ? $message : __( 'Please update your application, application is incomplate.', '1003-mortgage-application' ) );
	// get user email
	$to = sanitize_email( get_post_meta( $post_id, 'email', true ) );
	if ( ! empty( $to ) ) {
		$subject = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_application_reminder_mail_subject', 'mortgage_application_use_network_settings' ) );
		if ( strpos( $subject, '[' ) !== false ) {
			$subject = do_shortcode( $subject );
		}
		$subject = ( ! empty( $subject ) ? $subject : __( 'Mortgage Application Pending Notification', '1003-mortgage-application' ) );
		// get general functionality
		$general = new Mapp_Mortgage_general_functionality();
		$message = $general->replace_values( $message, $post_id );
		// send email
		$result = $general->mortgage_mail( $to, $subject, $message );
		// send success output
		if ( isset( $result ) && ! empty( $result ) ) {
			return wp_send_json_success( $result );
		}
		// send error output
		return wp_send_json_error( __( 'Something went wrong, Please try again or contact support if issue persists.', '1003-mortgage-application' ) );
	}
	// send error output
	return wp_send_json_error( __( 'Recipient email is not defined.', '1003-mortgage-application' ) );
}
/**
 * accepts admin terms and condition callback
 * This is ajax request callback
 **/
function mapp_mortgage_application_admin_terms_accept_callback() {
	/* check nonce */
	check_ajax_referer( 'mortgage_application_admin_terms', 'nonce_data' );
	/*
	accepts terma and condition*/
	// check is multisite
	if ( is_multisite() ) {
		update_site_option( 'mortgage_application_admin_terms', 'yes' );
	} else {
		update_option( 'mortgage_application_admin_terms', 'yes' );
	}
	return wp_send_json_success( true );
}


/**
 * Adding Sub Menu in Mortgage application for exporting custom Post type by srv
 * Start
 **/
function mapp_mortgage_application_export_callback() {
	/* Add fields for the Condition repeater field in jquery(admin.js) Srv*/
	global $mortgage_application_form_fields;
	/* Including Template File of Export Form */
	require_once MAPP_MORTGAGE_APP_BASE_PATH . 'inc/templates/mortgage_export_form.php';
}
function escape_csv_value( $value ) {
	$potentially_harmful_first_chars = array( '=', '-', '+', '@', "\t", "\r" );
	if ( in_array( mb_substr( $value, 0, 1 ), $potentially_harmful_first_chars ) ) {
		$value = "'" . $value;
	}
	return $value;
}
/**
 * export mortgage application in file as selected format
 **/
function mapp_mortgage_application_export_applications_callback() {
	// add_filter('nonce_user_logged_out', 'mortgage_application_nonce_user_logged_out',99, 2);
	if ( ! empty( $_REQUEST['action'] ) && isset( $_REQUEST['action'] ) && sanitize_text_field( $_REQUEST['action'] ) == 'mortgage_application_export_applications' && isset( $_REQUEST['mortgage_application_export_nonce'] ) && ( is_user_logged_in() && wp_verify_nonce( $_REQUEST['mortgage_application_export_nonce'], 'mortgage_application_export_applications' ) || ! is_user_logged_in() ) ) {
		/*
		if ( ! current_user_can( 'manage_options' ) )
		return;*/
		global $wpdb;
		$args = array(
			'post_type'      => 'mortgage_application',
			'posts_per_page' => -1,
		);
		// check post id is exists
		if ( ! empty( $_GET['post'] ) && isset( $_GET['post'] ) && intval( $_GET['post'] ) > 0 ) {
			$args['post__in'] = array( intval( sanitize_text_field( $_GET['post'] ) ) );
		}
		// choose status
		if ( isset( $_POST['application_type'] ) && ! empty( $_POST['application_type'] ) && ( sanitize_text_field( $_POST['application_type'] ) === '100' || sanitize_text_field( $_POST['application_type'] ) === '80' ) ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$args['meta_query'] = array(
				array(
					'key'     => 'application_status',
					'value'   => sanitize_text_field( $_POST['application_type'] ),
					'compate' => '=',
				),
			);
		}

		if ( ! empty( $_POST['startDate'] ) && isset( $_POST['startDate'] ) && isset( $_POST['endDate'] ) && ! empty( $_POST['endDate'] ) ) {
			$args['date_query'] = array(
				array(
					'after'     => sanitize_text_field( $_POST['startDate'] ),
					'before'    => sanitize_text_field( $_POST['endDate'] ),
					'inclusive' => true,
				),
			);
		}

		$condition = array();
		$count     = 0;
		if ( ! empty( $_POST['key'] ) && isset( $_POST['key'] ) ) {
			foreach ( $_POST['key'] as $key => $value ) {
				// check meta key is exists
				if ( empty( $value ) ) {
					continue;
				}

				if ( $count == 0 && count( $_POST['key'] ) > 1 ) {
					$condition['relation'] = 'AND';
				}

				if ( sanitize_text_field( trim( $_POST['compare'][ $count ] ) ) == 'contains' ) {
					$condition[] = array(
						'key'     => $value,
						'value'   => sanitize_text_field( $_POST['value'][ $count ] ),
						'compare' => 'LIKE',
					);
				} elseif ( sanitize_text_field( trim( $_POST['compare'][ $count ] ) ) == 'is' ) {
					$condition[] = array(
						'key'     => $value,
						'value'   => sanitize_text_field( $_POST['value'][ $count ] ),
						'compare' => '=',
					);
				}
				++$count;
			}
		}

		if ( ! empty( $condition ) && isset( $condition ) ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$args['meta_query'] = $condition;
		}
		$mortgage_application = get_posts( $args );
		if ( ! empty( $mortgage_application ) && isset( $mortgage_application ) ) {
			global $export_fields;
			$export_fields = array();
			if ( isset( $_POST['application_Fields_type'] ) && ! empty( $_POST['application_Fields_type'] ) && sanitize_text_field( $_POST['application_Fields_type'] ) === 'specific' ) {
				$export_fields = sanitize_text_field( $_POST['application_fields'] );
			}
			if ( ! empty( $_REQUEST['export_type'] ) && isset( $_REQUEST['export_type'] ) && sanitize_text_field( $_REQUEST['export_type'] ) === 'csv' ) {
				$export_csv = new mapp_exportMortgageApplicationsCSV();
				$export_csv->create_file( 'csv', $mortgage_application );
			} elseif ( ! empty( $_REQUEST['export_type'] ) && sanitize_text_field( $_REQUEST['export_type'] ) === 'fnm' ) {
				$license = trim( get_mortgage_application_option( 'ma_license_key' ) );
				$status  = get_mortgage_application_option( 'ma_license_key_status' );

				if ( $status !== false && $status == 'valid' && $license !== false ) {
					$export_fannie = new mapp_exportMortgageApplicationsFannie();
					// print_r( $export_fannie );
					// die("1111111111111111111111111111");
					$export_fannie->create_file( 'fannie', $mortgage_application );
				} else {
					$query_args = array( 'page' => 'ma_setting' );
					if ( is_network_admin() ) {
						$base_url = network_admin_url( 'admin.php' );
					} else {
						$query_args['post_type'] = 'mortgage_application';
						$redirect                = admin_url( 'edit.php' );
					}
					$query_args['post_type']       = 'mortgage_application';
					$query_args['activate-status'] = 'error';
					$query_args['message']         = urlencode( __( 'To upgrade your plugin or get support please visit <a href="https://mortgageapplicationplugin.com">https://mortgageapplicationplugin.com</a>', '1003-mortgage-application' ) );
					wp_redirect( add_query_arg( $query_args, $base_url ) );
					exit();
				}
			} elseif ( ! empty( $_REQUEST['export_type'] ) && sanitize_text_field( $_REQUEST['export_type'] ) === 'mismo' ) {
				$license = trim( get_mortgage_application_option( 'ma_license_key' ) );
				$status  = get_mortgage_application_option( 'ma_license_key_status' );

				if ( $status != false && $status == 'valid' && $license !== false ) {
					$export_fannie = new mapp_exportMortgageApplicationsMismo();
					// print_r( $export_fannie );
					// die("1111111111111111111111111111");
					$export_fannie->create_file( 'mismo', $mortgage_application );
				} else {

					$query_args = array( 'page' => 'ma_setting' );
					if ( is_network_admin() ) {
						$base_url = network_admin_url( 'admin.php' );
					} else {
						$query_args['post_type'] = 'mortgage_application';
						$redirect                = admin_url( 'edit.php' );
					}
					$query_args['post_type']       = 'mortgage_application';
					$query_args['activate-status'] = 'error';
					$query_args['message']         = urlencode( __( 'To upgrade your plugin or get support please visit <a href="https://mortgageapplicationplugin.com">https://mortgageapplicationplugin.com</a>', '1003-mortgage-application' ) );
					wp_redirect( add_query_arg( $query_args, $base_url ) );
					exit();
				}
			}
		} else {
			$query_args                  = array( 'page' => 'ma_export' );
			$query_args['post_type']     = 'mortgage_application';
			$query_args['export-status'] = 'error';
			wp_redirect( add_query_arg( $query_args, admin_url( 'edit.php' ) ) );
			exit();
		}
	}
	die( 'end out' );
}
/*
function mortgage_application_nonce_user_logged_out($uid, $action)
{

	return true;
}*/
/**
 * bulk action callback for export file
 *
 * @parma $redirect_to url application screen url
		$action str action type as export_fnm or export_csv
		$post_ids array post id list array
 */

// if write_log is not defined, define it
if ( ! function_exists( 'write_log' ) ) {
	function write_log( $log ) {
		if ( true === WP_DEBUG ) {
			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else {
				error_log( $log );
			}
		}
	}
}

function mapp_mortgage_application_handle_bulk_actions( $redirect_to, $action, $post_ids ) {
	if ( $action === 'export_csv' || $action === 'export_fnm' || $action === 'export_mismo' ) {
		$args                 = array(
			'post_type'      => 'mortgage_application',
			'posts_per_page' => -1,
		);
		$args['post__in']     = $post_ids;
		$mortgage_application = get_posts( $args );
		if ( ! empty( $mortgage_application ) && isset( $mortgage_application ) ) {
			global $export_fields;
			$export_fields = array();
			if ( ! empty( $action ) && isset( $action ) && sanitize_text_field( $action ) === 'export_csv' ) {
				$export_csv = new mapp_exportMortgageApplicationsCSV();
				$export_csv->create_file( 'csv', $mortgage_application );
			} elseif ( ! empty( $action ) && sanitize_text_field( $action ) === 'export_fnm' ) {
				$license = trim( get_mortgage_application_option( 'ma_license_key' ) );
				$status  = get_mortgage_application_option( 'ma_license_key_status' );
				if ( $status !== false && $status == 'valid' && $license !== false ) {
					$export_fannie = new mapp_exportMortgageApplicationsFannie();
					$export_fannie->create_file( 'fannie', $mortgage_application );
				}
			} elseif ( ! empty( $action ) && sanitize_text_field( $action ) === 'export_mismo' ) {
				$license = trim( get_mortgage_application_option( 'ma_license_key' ) );
				$status  = get_mortgage_application_option( 'ma_license_key_status' );
				if ( $status !== false && $status == 'valid' && $license !== false ) {
					$export_fannie = new mapp_exportMortgageApplicationsMismo();
					$export_fannie->create_file( 'mismo', $mortgage_application );
				}
			}
		} else {
			return $redirect_to; // Exit
		}
	} else {
		return $redirect_to; // Exit
	}
}

function mapp_mortgage_application_autocomplete_fields_by_name_callback() {
	global $mortgage_application_form_fields;
	$currernt_term = '';
	if ( ! empty( $_POST['term'] ) && isset( $_POST['term'] ) ) {
		$terms         = explode( ',', $_POST['term'] );
		$currernt_term = end( $terms );
	}
	$result = preg_grep( '/(?i)(.*)' . sanitize_text_field( $currernt_term ) . '(.*)/', $mortgage_application_form_fields );
	// exclude exists terms from mached result
	if ( ! empty( $_POST['exists_terms'] ) && isset( $_POST['exists_terms'] ) ) {
		foreach ( $_POST['exists_terms'] as $key ) {
			unset( $result[ $key ] );
		}
	}
	if ( ! empty( $result ) && isset( $result ) ) {
		$data = array();
		foreach ( $result as $key => $value ) {
			$data[ $key ]['value'] = $key;
			$data[ $key ]['label'] = $value;
		}
		wp_send_json_success( $data );
	}
	wp_send_json_error( __( 'No application found.', '1003-mortgage-application' ) );
}
/**
 * admin application webook send action callback
 **/
function mortgage_application_admin_send_on_webhook_callback() {
	/* check nonce */
	check_ajax_referer( 'send_post_on_webhook', 'nonce_data' );
	if ( isset( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) == 'mortgage_application_admin_send_on_webhook' ) {
		// get rquest data
		$post_id = intval( $_POST['post_id'] );
		if ( ! empty( $post_id ) ) {
			// send application in webhook as defined in setting
			$result = mapp_mortgage_application_send_application_to_webhooks( $post_id );
			if ( isset( $result ) && $result === true ) {
				return wp_send_json_success( true );
			}
			return wp_send_json_error( $result );
		}
	}
	// send error output
	return wp_send_json_error( esc_html__( 'unexpected error', '1003-mortgage-application' ) );
}

/**
 * send test webhook request
 * This is ajax request callback
 **/
function mortgage_application_send_test_webhooks_request_callback() {
	/* check nonce */
	check_ajax_referer( 'mortgage_application_test_webhooks', 'nonce_data' );
	if ( isset( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) == 'mortgage_application_send_test_webhooks_request' && ! empty( $_POST['webhooks'] ) ) {
		$webhooks = $_POST['webhooks'];
		// Convert them to an array.
		$webhooks = explode( "\n", $webhooks );
		$webhooks = array_filter( $webhooks );
		if ( ! empty( $webhooks ) ) {
			// get application meta data
			global $mortgage_application_form_fields;
			$success = true;
			// And now send them to each one.
			foreach ( $webhooks as $url ) {
				// $this->debug( 'Sending lead %s to %s', $lead->meta, $url );
				try {
					$result = wp_remote_post(
						$url,
						array(
							'body'      => (array) $mortgage_application_form_fields,
							'sslverify' => false,
							'timeout'   => '30',
						)
					);

					if ( isset( $result ) && isset( $result['body'] ) ) {
						$result_data = json_decode( $result['body'] );
						if ( isset( $result_data ) && ! empty( $result_data->error ) ) {
							$success       = false;
							$error_message = $result_data->error->message;
						}
					}
				} catch ( Exception $e ) {
					return $e->getMessage();
				}
			}
			if ( $success === true ) {
				return wp_send_json_success( true );
			}
			$error_message = ( ( isset( $error_message ) ) ? $error_message : 'Please double-check webhook url and retest.' );
			wp_send_json_error( $error_message );
		}
		wp_send_json_error( __( 'Webhooks are not defined.', '1003-mortgage-application' ) );
	}
	// send error output
	return wp_send_json_error( __( 'unexpected error', '1003-mortgage-application' ) );
}
/*
* Ajax callback to download saved files from backend.
*/
function mortgate_application_download_filess_callback() {
	file_put_contents( ABSPATH . 'testr.txt', 'tysahgs sadjksdas' );
	$dest = ABSPATH . 'testr.txt';
	header( 'Pragma: public' );
	header( 'Expires: 0' );
	header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
	header( 'Content-Type: text/csv' );
	header( 'Content-Disposition: attachment; filename="' . basename( $dest ) . '"' );
	header( 'Content-Transfer-Encoding: binary' );
	header( 'Content-Length: ' . filesize( $dest ) );
	while ( ob_get_level() ) {
		ob_end_clean();
		@readfile( $dest );
	}
	unlink( $dest );
	return '';
	/*
	if(!empty($_POST['action']) && sanitize_text_field($_POST['action']) == 'mortgate_application_download_files')
	{

		if(isset($_POST['file_name']) && !empty($_POST['file_name']) )
		{

			$index = 1;

			$file_name = $_POST['file_name'];
			if(get_option($file_name))
			{
				$count = get_option($file_name);
				$count++;
				update_option($file_name,$count);
			}
			else
			{
				update_option($file_name,$index);
			}
			$response = array("status"=>get_option($file_name) );
		}
		$index++;
		wp_send_json_success($response);
	}*/
}
