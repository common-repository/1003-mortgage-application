<?php
/*
 * @wordpress-plugin
 * Plugin Name:       1003 Mortgage Application
 * Plugin URI:        https://mortgageapplicationplugin.com
 * Description:       1003 Mortgage Application is a very easy-to-use WordPress plugin built with the purpose of providing financial industry professionals with a quick and easy way to capture client information.
 * Version:           1.87
 * Author:            Lenderd
 * Author URI:        https://lenderd.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       1003-mortgage-application
 * Domain Path:       /languages
 * @link              https://mortgageapplicationplugin.com
 * @since             1.0.0
 * @package           mortgage_application
*/

// If this file is called directly, abort.
defined( 'ABSPATH' ) || die( 'Access denied!' );

define( 'MAPP_MORTGAGE_APP_VERSION', '1.87' );

// DEFINED CONSTANT
defined( 'MAPP_MORTGAGE_APP_BASE_PATH' ) || define( 'MAPP_MORTGAGE_APP_BASE_PATH', plugin_dir_path( __FILE__ ) );
defined( 'MAPP_MORTGAGE_APP_BASE_URL' ) || define( 'MAPP_MORTGAGE_APP_BASE_URL', plugin_dir_url( __FILE__ ) );
defined( 'MAPP_MORTGAGE_APP_BASE_FILE' ) || define( 'MAPP_MORTGAGE_APP_BASE_FILE', __FILE__ );

$option_data = array(
	'mortgage_application_button_color'             => '#0073aa',
	'mortgage_application_progress_bar_color'       => '#1dbc60',
	'mortgage_application_email_recipients'         => get_option( 'admin_email' ),
	'mortgage_application_mail_from_name'           => get_option( 'blogname' ),
	'mortgage_application_mail_from'                => get_option( 'admin_email' ),
	'mortgage_application_mail_reply_to'            => '',
	'mortgage_application_success_message'          => 'Your application has been submitted successfully! We will follow up with you asap.',
	'mortgage_application_mail_subject'             => 'New Application ({first_name} {last_name})',
	'mortgage_application_mail_message'             => '{all_fields}
{csv_file_url}',
	'mortgage_application_user_mail_subject'        => 'Your Application Receipt',
	'mortgage_application_user_mail_message'        => '<h3 style="margin: 0 0 20px; font-size: 24px;">Thank You!</h3>
Thank you very much for submitting your application. We look forward to guiding you through your home loan process and will follow up with you shortly!
{all_fields}',
	'mortgage_application_reminder_mail_subject'    => 'Incomplete Application',
	'mortgage_application_reminder_mail_message'    => '<h4 style="margin: 0 0 25px; font-size: 24px;">Reminder</h4>
To complete your mortgage application please click the following link:
{edit_url}',
	'mortgage_application_webhooks'                 => '',
	'disclaimer_field_1'                            => 'I hereby certify that the information given in my submission is complete and correct and is given for the purpose of potentially obtaining a mortgage loan and/or financial services applied for.',
	'mortgage_ma_submissions_subject'               => '{name} Uploaded Files',
	'mortgage_ma_submissions_client_subject'        => 'File Upload Receipt',
	'mortgage_application_submision_client_message' => 'Your files have been uploaded successfully!',

);

defined( 'MAPP_MORTGAGE_APP_BASE_DATA' ) || define( 'MAPP_MORTGAGE_APP_BASE_DATA', $option_data );

$vendor_file = MAPP_MORTGAGE_APP_BASE_PATH . '/vendor/autoload.php';
if ( is_readable( $vendor_file ) ) {
	require_once $vendor_file;
}

// First time initializetion
require_once MAPP_MORTGAGE_APP_BASE_PATH . 'inc/initialize-plugin.php';

/**
 * Plugin textdomain.
 */
function mortgate_application_pro_textdomain() {
	load_plugin_textdomain( '1003-mortgage-application', false, basename( __DIR__ ) . '/languages' );
}
add_action( 'plugins_loaded', 'mortgate_application_pro_textdomain' );


// Register activation method
register_activation_hook( __FILE__, 'mortgate_application_pro_activation' );
if ( ! function_exists( 'mortgate_application_pro_activation' ) ) {
	function mortgate_application_pro_activation() {
		// Check for plugin using plugin name
		if ( is_plugin_active( 'mortgage-application/mortgage-application.php' ) ) {
			// deactivate free plugin
			deactivate_plugins( 'mortgage-application/mortgage-application.php' );
		}

		// Set hourly event reminder to send email notification for incomplete application
		if ( ! wp_next_scheduled( 'mortgate_hourly_event' ) ) {
			wp_schedule_event( time(), 'hourly', 'mortgate_hourly_event' );
		}
		// Set hourly event to check uploaded file delete days.

		$option_data = MAPP_MORTGAGE_APP_BASE_DATA;
		foreach ( $option_data as $option_name => $value ) {
			if ( is_multisite() ) {
				if ( ! get_site_option( $option_name ) ) {
					update_site_option( $option_name, $value );
				}
			} elseif ( ! get_option( $option_name ) ) {
					update_option( $option_name, $value );
			}
		}
	}
}



// add_action('admin_init', 'mapp_revert_applications_batch');
function mapp_revert_applications_batch() {
	// If it's a multisite setup
	if ( is_multisite() ) {
		$blog_ids = get_sites( array( 'fields' => 'ids' ) );
		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );

			revert_applications();

			delete_option( 'mapp_mortgage_app_version' );

			delete_geocoded_transients();
		}

		restore_current_blog();
	} else {
		// If it's not a multisite setup, just execute the function
		revert_applications();
		delete_option( 'mapp_mortgage_app_version' );
		delete_geocoded_transients();
	}
}

function revert_applications() {
	$args = array(
		'post_type'      => 'mortgage_application',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		'meta_query'     => array(
			array(
				'key'     => 'mapp_geocoded_address',
				'compare' => 'EXISTS',
			),
		),
	);

	$query = new WP_Query( $args );

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();

			$meta = get_post_meta( get_the_ID(), 'mapp_geocoded_address', true );

			// delete the geocoded addresses
			delete_post_meta( get_the_ID(), 'mapp_geocoded_address' );
			delete_post_meta( get_the_ID(), 'mapp_geocoded_new_address' );
		}
	}
}

function delete_geocoded_transients() {
	global $wpdb;

	// Prefix for the option name of the transient
	$prefix = '_transient_geocoded_';

	$sql = "DELETE FROM $wpdb->options WHERE option_name LIKE '%s'";

	// Use 'esc_like' to sanitize the 'LIKE' expression and 'prepare' to create a safe SQL query
	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$query = $wpdb->prepare( $sql, $wpdb->esc_like( $prefix ) . '%' );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared
	$wpdb->query( $query );
}
