<?php

/**
 * This file is responsible to call actions or add actions.
 *
 * @link        https://lenderd.com
 * @since       1.0.0
 * @package     mortgage_application
 * @sub-package mortgage_application/inc
 */

// If this file is called directly, abort.
defined( 'ABSPATH' ) or die( 'Access denied!' );

// Hourly event reminder action
add_action( 'mortgate_hourly_event', 'mapp_mortgate_hourly_event_callback' );

// Enqueue custom scripts and styles
add_action( 'wp_enqueue_scripts', 'mapp_mortgage_application_enqueue_scripts' );

// Add shortcode to show application form
add_shortcode( 'mortgage_application_form', 'mapp_mortgage_application_form_callback' );
add_shortcode( 'mortgage_application_file_uploads', 'mapp_mortgage_application_file_uploads_callback' );

// Application ajax save action call for login or non login user
add_action( 'init', 'mapp_mortgage_application_register_file_uploads_custom_post_func', 0 );
add_action( 'wp_ajax_mortgate_application_data_save', 'mapp_mortgate_application_data_save_callback' );
add_action( 'wp_ajax_nopriv_mortgate_application_data_save', 'mapp_mortgate_application_data_save_callback' );
add_action( 'wp_footer', 'mapp_mortgate_application_add_edit_form' );
add_action( 'wp_ajax_mortgate_application_download_file', 'mortgate_application_download_file_callback' );
add_action( 'wp_ajax_mortgate_application_download_files', 'mortgate_application_download_filess_callback' );

// Hourly event to check delete days action
add_action( 'mortgate_check_hourly_event', 'mapp_mortgate_check_hourly_event_callback' );


// Update applications addresses to geocoded addresses
add_action( 'admin_init', 'mapp_check_version' );
function mapp_check_version() {
	$installed_version = get_option( 'mapp_mortgage_app_version', false );
	if ( ! $installed_version ) {
		// The plugin was just installed. Check if we need to run the update.
		$remaining_applications = mapp_count_remaining_applications();
		if ( $remaining_applications > 0 ) {
			// There are applications that need to be updated.
			if ( is_multisite() ) {
				add_action( 'network_admin_notices', 'mapp_update_applications_admin_notice' );
			} else {
				add_action( 'admin_notices', 'mapp_update_applications_admin_notice' );
			}
		}
		return;
	} elseif ( version_compare( MAPP_MORTGAGE_APP_VERSION, $installed_version, '>' ) ) {
		// The new version is higher than the installed version, so an update is needed.
		if ( is_multisite() ) {
			add_action( 'network_admin_notices', 'mapp_update_applications_admin_notice' );
		} else {
			add_action( 'admin_notices', 'mapp_update_applications_admin_notice' );
		}
	}
}


function mapp_update_applications_admin_notice() {
	// You can use the current page URL and add the query argument to it.
	$url = add_query_arg( 'mapp_update_applications_addresses', '1' );
	?>
	<div class="notice notice-info is-dismissible">
	<p><?php esc_html_e( '1003 Mortgage Application has been updated. Please click the button to update your database. This may take some time to complete.', '1003-mortgage-application' ); ?></p>
	<p><button class="button-primary mapp-update-button"><?php esc_html_e( 'Update Database', '1003-mortgage-application' ); ?></button></p>
	<h4 id="mapp-updating-sitename"></h4>
	<div class="mapp-update-progress progress">
		<div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
		0%
		</div>
	</div>
	</div>
	<style>
	.mapp-update-progress {
		display: none;
	}

	.progress {
		height: 20px;
		background-color: #e9ecef;
		border-radius: 5px;
		box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
	}

	.progress-bar {
		height: 100%;
		background-color: #007bff;
		transition: width 0.6s ease;
		text-align: center;
		color: #fff;
		line-height: 20px;
		border-radius: 5px;
	}
	</style>
	<?php
}



add_action( 'admin_enqueue_scripts', 'mapp_update_enqueue_scripts' );
function mapp_update_enqueue_scripts() {
	wp_enqueue_script( 'mapp_update_script', MAPP_MORTGAGE_APP_BASE_URL . 'assets/js/update.js', array( 'jquery' ), MAPP_MORTGAGE_APP_VERSION, true );
	wp_localize_script(
		'mapp_update_script',
		'mappUpdate',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'mapp_update_nonce' ),
		)
	);
}

add_action( 'wp_ajax_mapp_count_total_posts', 'mapp_ajax_count_total_posts' );
function mapp_ajax_count_total_posts() {
	$total_posts = 0;

	if ( is_multisite() ) {
		$sites = get_sites();
		foreach ( $sites as $site ) {
			switch_to_blog( $site->blog_id );
			$total_posts += mapp_count_remaining_applications();
			restore_current_blog();
		}
	} else {
		$total_posts = mapp_count_remaining_applications();
	}

	wp_send_json_success( array( 'total_posts' => $total_posts ) );
}


add_action( 'wp_ajax_mapp_update_applications_addresses', 'mapp_ajax_update_applications_addresses' );
function mapp_ajax_update_applications_addresses() {
	$site_results = array();
	if ( is_multisite() ) {
		$sites = get_sites();
		foreach ( $sites as $site ) {
			switch_to_blog( $site->blog_id );
			$site_results[] = update_site();
			restore_current_blog();
		}
	} else {
		$site_results[] = update_site();
	}

	// Send back the results for all sites
	wp_send_json_success( $site_results );
}

function update_site() {
	// Define the number of posts to be updated in each batch
	$batch_size = 1;
	// Get the total number of posts still to be updated
	$remaining_posts = mapp_count_remaining_applications();

	$processed_posts = 0;
	// If there are posts to be updated, process a batch
	if ( $remaining_posts > 0 ) {
		mapp_update_applications_batch( $batch_size );
		$remaining_posts -= $batch_size;
		$processed_posts  = $batch_size;
	}

	if ( $remaining_posts <= 0 ) {
		// Update the stored version number to the current version
		update_option( 'mapp_mortgage_app_version', MAPP_MORTGAGE_APP_VERSION );
	}

	// Return the number of remaining posts to be updated, the number of processed posts, and the site name
	return array(
		'remaining_posts' => max( 0, $remaining_posts ),
		'processed_posts' => $processed_posts,
	);
}


function mapp_count_remaining_applications() {
	$args = array(
		'post_type'      => 'mortgage_application',
		'posts_per_page' => -1,
		'post_status'    => 'any',
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		'meta_query'     => array(
			array(
				'key'     => 'mapp_geocoded_address',
				'compare' => 'NOT EXISTS',
			),
		),
	);

	$query = new WP_Query( $args );
	return $query->found_posts;
}

function mapp_update_applications_batch( $batch_size ) {
	$args = array(
		'post_type'      => 'mortgage_application',
		'posts_per_page' => $batch_size,
		'post_status'    => 'any',
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		'meta_query'     => array(
			array(
				'key'     => 'mapp_geocoded_address',
				'compare' => 'NOT EXISTS',
			),
		),
	);

	$query = new WP_Query( $args );

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$address              = get_post_meta( get_the_ID(), 'mailing_address', true );
			$new_address          = get_post_meta( get_the_ID(), 'zip_code', true );
			$geocoded_address     = mapp_geocode_address( $address );
			$geocoded_new_address = mapp_geocode_address( $new_address );

			update_post_meta( get_the_ID(), 'mapp_geocoded_address', $geocoded_address );
			update_post_meta( get_the_ID(), 'mapp_geocoded_new_address', $geocoded_new_address );
		}
	}
}


function mapp_geocode_address( $address ) {
	if ( is_array( $address ) ) {
		return $address;
	}

	$encodedAddress = urlencode( $address );
	if ( empty( $encodedAddress ) || $encodedAddress == '%0A' ) {
		return $address;
	}

	// try to get the geocoded address from the transients
	$transient_name  = 'geocoded_' . md5( $address );
	$geocodedAddress = get_transient( $transient_name );

	if ( $geocodedAddress !== false ) {
		return $geocodedAddress;
	}

	// if not found in the transients, request the geocoded address from the API
	$url = "https://nominatim.openstreetmap.org/search?addressdetails=1&q={$encodedAddress}&format=json&limit=1";

	$opts    = array(
		'http' => array(
			'header' => "User-Agent: MarcosWorld 3.7.6\r\n",
		),
	);
	$context = stream_context_create( $opts );

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$responseJson = file_get_contents( $url, false, $context );
	$response     = json_decode( $responseJson, true );

	if ( isset( $response[0] ) ) {
		$geocodedAddress = $response[0];

		foreach ( $geocodedAddress['address'] as $key => $val ) {
			$geocodedAddress[ $key ] = $val;
		}

		unset( $geocodedAddress['address'] );

		// store the geocoded address in the transients for 24 hours
		set_transient( $transient_name, $geocodedAddress, 24 * HOUR_IN_SECONDS );

		return $geocodedAddress;
	} else {
		$addressArray = explode( ', ', $address );
		return mapp_geocode_address( $addressArray );
	}
}
