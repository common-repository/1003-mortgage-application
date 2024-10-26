<?php
/**
 * This class responsible to add or get short url with bitly v3 api
 *
 * @link        https://lenderd.com
 * @since       1.0.0
 *
 * @package     mortgage_application
 * @sub-package mortgage_application/inc/class
 **/
// If this file is called directly, abort.
defined( 'ABSPATH' ) or die( 'Access denied!' );
class mapp_bitly_shortURL {
	protected $token;
	function __construct() {
		if ( ! empty( get_option( 'ma_gitly_auth_token' ) ) ) {
			$this->token = get_option( 'ma_gitly_auth_token' );
		} else {
			$bitly_token = $this->get_auth_token();
			if ( isset( $bitly_token ) ) {
				$this->token = $bitly_token;
				update_option( 'ma_gitly_auth_token', $this->token );
			}
		}
	}
	/* returns the shortened url */
	function get_short_url( $url ) {
		$data             = array();
		$data['long_url'] = $url;

		// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_init
		$curl = curl_init();
				// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt_array
				curl_setopt_array(
					$curl,
					array(
						CURLOPT_URL            => 'https://api-ssl.bitly.com/v4/shorten',
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_ENCODING       => '',
						CURLOPT_MAXREDIRS      => 10,
						CURLOPT_TIMEOUT        => 30,
						CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST  => 'POST',
						CURLOPT_POSTFIELDS     => wp_json_encode( $data ),
						CURLOPT_HTTPHEADER     => array(
							'Authorization: Bearer ' . $this->token,
							'Content-Type: application/json',
						),
					)
				);

				// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_exec
				$response = curl_exec( $curl );
				// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_error
				$err      = curl_error( $curl );

				// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close
				curl_close( $curl );

		if ( $err ) {

		} else {
			return $response;
		}
	}

	/* returns expanded url */
	function get_long_url( $url, $login, $appkey, $format = 'txt' ) {
		$connectURL = 'http://api.bit.ly/v3/expand?login=' . $login . '&apiKey=' . $appkey . '&shortUrl=' . urlencode( $url ) . '&format=' . $format;
		return $this->curl_get_result( $connectURL );
	}

	function get_auth_token() {

		// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_init
		$curl = curl_init();
				// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt_array
				curl_setopt_array(
					$curl,
					array(
						CURLOPT_URL            => 'https://api-ssl.bitly.com/oauth/access_token',
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_ENCODING       => '',
						CURLOPT_MAXREDIRS      => 10,
						CURLOPT_TIMEOUT        => 30,
						CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST  => 'POST',
						CURLOPT_POSTFIELDS     => '',
						CURLOPT_HTTPHEADER     => array(
							'Authorization: Basic ' . base64_encode( 'dilipgenex:India123@' ),
						),
					)
				);
				// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_exec
				$response = curl_exec( $curl );
				// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_error
				$err      = curl_error( $curl );
				// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close
				curl_close( $curl );

		if ( $err ) {

		} else {
			return $response;
		}
	}
}
