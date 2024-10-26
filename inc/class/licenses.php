<?php
/**
 * This file is responsible for licenses
 *
 * @link        https://lenderd.com
 * @since       1.0.0
 *
 * @package     mortgage_application
 * @sub-package mortgage_application/inc/class
 *
 * phpcs:disable WordPress.Security.NonceVerification
 */
// If this file is called directly, abort.
defined( 'ABSPATH' ) or die( 'Access denied!' );

if ( ! class_exists( 'MortgageAppLicenses' ) ) {
	class MortgageAppLicenses {
		private $plugin_name;
		private $version;
		private $app_store_url;
		private $item_id;
		private $page_id;
		public function __construct() {
			$this->plugin_name   = 'mortgage_application';
			$this->version       = '1.0.0';
			$this->app_store_url = 'https://mortgageapplicationplugin.com';
			$this->item_id       = 1726;
			$this->page_id       = 'ma_setting';
			// add_action( 'admin_init', array($this, 'register_licenses_field'), 0 );
			add_action( 'admin_init', array( $this, 'plugin_updater' ), 0 );
			add_action( 'wp_ajax_mortgage_application_activate_licenses_key', array( $this, 'activate_license' ) );
			add_action( 'wp_ajax_mortgage_application_deactivate_licenses_key', array( $this, 'deactivate_licenses' ) );
		}

		public function register_licenses_field() {
			add_settings_section( 'ma_license', '', array( $this, 'licenses_heading' ), 'ma_license' );
			add_settings_field(
				'ma_license_key',
				__( 'License Key', '1003-mortgage-application' ),
				'mapp_mortgage_application_display_text_element',
				'ma_license',
				'ma_license',
				array(
					'name'  => 'ma_license_key',
					'class' => 'ma_license_key',
				)
			);
			register_setting( 'ma_license', 'ma_license_key' );
		}

		public function licenses_heading() {
			$license      = trim( get_mortgage_application_option( 'ma_license_key' ) );
			$status       = get_mortgage_application_option( 'ma_license_key_status' );
			$responseBody = $this->get_licenses_data();
			if ( ! empty( $responseBody ) ) {
				/*show licenses details*/
				require_once MAPP_MORTGAGE_APP_BASE_PATH . 'inc/templates/mortgage_license_details.php';
			}
		}

		public function get_licenses_data() {
			// retrieve the license from the database
			$license = trim( get_mortgage_application_option( 'ma_license_key' ) );
			$status  = get_mortgage_application_option( 'ma_license_key_status' );
			// data to send in our API request
			$api_params = array(
				'edd_action' => 'check_license',
				'license'    => $license,
				'item_id'    => $this->item_id, // the name of our product in EDD
				'url'        => home_url(),
			);
			// Call the custom API.
			$response            = wp_remote_post(
				$this->app_store_url,
				array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params,
				)
			);
			return $responseBody = json_decode( $response['body'] );
		}

		public function plugin_updater() {
			if ( class_exists( 'Alledia\EDD_SL_Plugin_Updater' ) ) {
				// retrieve our license key from the DB
				$license_key = trim( get_mortgage_application_option( 'ma_license_key' ) );
				// setup the updater.
				$edd_updater = new Alledia\EDD_SL_Plugin_Updater(
					$this->app_store_url,
					MAPP_MORTGAGE_APP_BASE_FILE,
					array(
						'version' => $this->version,              // current version number
						'license' => $license_key,        // license key (used get_wpmc_option above to retrieve from DB)
						'item_id' => $this->item_id,    // name of this plugin
						'author'  => 'Todd Helvik',  // author of this plugin
						'beta'    => false,
					)
				);
			}
		}
		// License Key Activation
		public function activate_license() {
				// define base url and result data
				$query_args                    = array( 'page' => $this->page_id );
				$query_args['action']          = 'license';
				$query_args['activate-status'] = 'error';
			if ( is_network_admin() ) {
				$base_url = network_admin_url( 'admin.php' );
			} else {
				$query_args['post_type'] = 'mortgage_application';
				$redirect                = admin_url( 'edit.php' );
			}
			if ( isset( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) === 'mortgage_application_activate_licenses_key' && isset( $_POST['licenses_key'] ) && sanitize_text_field( $_POST['licenses_key'] ) ) {
				/* check nonce */
				if ( ! check_ajax_referer( 'mortgage_app_activate', 'nonce_data' ) ) {
					return; // get out if we didn't click the Activate button
				}
				// get new licenses key from request
				$new_licenses_key = sanitize_text_field( $_POST['licenses_key'] );
				// retrieve the license from the database
				$license = trim( get_mortgage_application_option( 'ma_license_key' ) );
				// check licenses key is new or old, update in data base
				if ( isset( $new_licenses_key ) && $new_licenses_key != $license ) {
					mapp_update_mortgage_application_option( 'ma_license_key', $new_licenses_key );
					$license = $new_licenses_key;
				}
				// data to send in our API request
				$api_params = array(
					'edd_action' => 'activate_license',
					'license'    => $license,
					'item_id'    => $this->item_id, // the name of our product in EDD
					'url'        => home_url(),
				);

				// Call the custom API.
				$response = wp_remote_post(
					$this->app_store_url,
					array(
						'timeout'   => 15,
						'sslverify' => false,
						'body'      => $api_params,
					)
				);
				// make sure the response came back okay
				if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
					if ( is_wp_error( $response ) ) {
						$message = $response->get_error_message();
					} else {
						$message = __( 'An error occurred, please try again.' );
					}
				} else {

					$license_data = json_decode( wp_remote_retrieve_body( $response ) );
					if ( false === $license_data->success ) {
						switch ( $license_data->error ) {
							case 'expired':
								$message = sprintf(
									// phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
									__( 'Your license key expired on %s.' ),
									// phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
									date_i18n( get_mortgage_application_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
								);
								break;
							case 'revoked':
								$message = __( 'Your license key has been disabled.' );
								break;
							case 'missing':
								$message = __( 'Invalid license.' );
								break;
							case 'invalid':
							case 'site_inactive':
								$message = __( 'Your license is not active for this URL.' );
								break;
							case 'item_name_mismatch':
								// phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
								$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), $this->item_id );
								break;
							case 'no_activations_left':
								$message = __( 'Your license key has reached its activation limit.' );
								break;
							default:
								$message = __( 'An error occurred, please try again.' );
								break;
						}
					}
				}

				// Check if anything passed on a message constituting a failure
				if ( ! empty( $message ) ) {
					$query_args['message'] = urlencode( $message );
				} else {
					$query_args['activate-status'] = 'updated';
					// $license_data->license will be either "valid" or "invalid"
					mapp_update_mortgage_application_option( 'ma_license_key_status', $license_data->license );
				}
			}
			$result['redirect_url'] = add_query_arg( $query_args, $base_url );
			$result['message']      = __( 'Licenses key is not define.', '1003-mortgage-application' );
			return wp_send_json_error( $result );
		}
		// Deactivation
		public function deactivate_licenses() {
				// define base url and result data
				$query_args                    = array( 'page' => $this->page_id );
				$query_args['action']          = 'license';
				$query_args['activate-status'] = 'error';
			if ( is_network_admin() ) {
				$base_url = network_admin_url( 'admin.php' );
			} else {
				$query_args['post_type'] = 'mortgage_application';
				$redirect                = admin_url( 'edit.php' );
			}
			if ( isset( $_POST['action'] ) && sanitize_text_field( $_POST['action'] ) === 'mortgage_application_deactivate_licenses_key' ) {
				/* check nonce */
				if ( ! check_ajax_referer( 'mortgage_app_deactivate', 'nonce_data' ) ) {
					return; // get out if we didn't click the deactivate button
				}
				// retrieve the license from the database
				$license = trim( get_mortgage_application_option( 'ma_license_key' ) );

				// data to send in our API request
				$api_params = array(
					'edd_action' => 'deactivate_license',
					'license'    => $license,
					'item_id'    => urlencode( $this->item_id ), // the name of our product in EDD
					'url'        => home_url(),
				);

				// Call the custom API.
				$response = wp_remote_post(
					$this->app_store_url,
					array(
						'timeout'   => 15,
						'sslverify' => false,
						'body'      => $api_params,
					)
				);
				// make sure the response came back okay
				if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
					if ( is_wp_error( $response ) ) {
						$message = $response->get_error_message();
					} else {
						$message = __( 'An error occurred, please try again.' );
					}
					$query_args['message'] = urlencode( $message );
				} else {
					// decode the license data
					$license_data = json_decode( wp_remote_retrieve_body( $response ) );
					// $license_data->license will be either "deactivated" or "failed"
					if ( $license_data->license == 'deactivated' || $license_data->license == 'failed' ) {
						mapp_delete_mortgage_application_option( 'ma_license_key_status' );
					}
					$query_args['message'] = urlencode( __( 'License key successfully deactivated.', '1003-mortgage-application' ) );
				}
			}
			$result['redirect_url'] = add_query_arg( $query_args, $base_url );
			$result['message']      = __( 'Licenses key is not defined.', '1003-mortgage-application' );
			return wp_send_json_error( $result );
		}
	}
}
$mortgage_app_licenses = new MortgageAppLicenses();
