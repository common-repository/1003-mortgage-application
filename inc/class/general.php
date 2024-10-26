<?php

/**
 * This file is responsible for general functionality(common)
 *
 * @link        https://lenderd.com
 * @since       1.0.0
 *
 * @package     mortgage_application
 * @sub-package mortgage_application/inc/class
 */
// If this file is called directly, abort.
defined( 'ABSPATH' ) or die( 'Access denied!' );

if ( ! class_exists( 'Mapp_Mortgage_general_functionality' ) ) {
	class Mapp_Mortgage_general_functionality {

		private $plugin_name;
		private $version;
		protected $license;
		protected $status;
		/**
		Initialize common function class.
		 **/
		public function __construct() {
			$this->plugin_name = 'mortgage_application';
			$this->version     = '1.0.0';
			// retrieve the license from the database
			$this->license = trim( get_mortgage_application_option( 'ma_license_key' ) );
			$this->status  = get_mortgage_application_option( 'ma_license_key_status' );
		}
		/**
		 * send notification as user mortgage setting
		 *
		 * @parameter: $to = recipiant, $subject, $message, $header_value, $attachments
		 **/
		public function mortgage_mail( $to, $subject, $message, $header_value = array(), $attachments = array() ) {
			if ( isset( $to ) && ! empty( $to ) && isset( $subject ) && ! empty( $subject ) && isset( $message ) && ! empty( $message ) ) {
				// $to = sanitize_email($to);
				$subject = sanitize_text_field( $subject );
				$message = wp_kses_post( $message );

				// get email setting data
				$mail_from = sanitize_email( get_front_mortgage_application_option( 'mortgage_application_mail_from', 'mortgage_application_form_network_settings' ) );
				write_log( 'From name' );
				write_log( $mail_from );
				if ( strpos( $mail_from, '[' ) !== false ) {
					$mail_from = do_shortcode( $mail_from );
				}

				$from_name = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_application_mail_from_name', 'mortgage_application_form_network_settings' ) );
				if ( strpos( $from_name, '[' ) !== false ) {
					$from_name = do_shortcode( $from_name );
				}

				$reply_to = sanitize_email( get_front_mortgage_application_option( 'mortgage_application_mail_reply_to', 'mortgage_application_form_network_settings' ) );
				if ( strpos( $reply_to, '[' ) !== false ) {
					$reply_to = do_shortcode( $reply_to );
				}

				$header_value_str = '';
				if ( ! empty( $header_value ) && isset( $header_value ) ) {
					$header_value_str = implode( ',', $header_value );
				}
				// update header

				if ( ! empty( $from_name ) && isset( $from_name ) && ! empty( $mail_from ) && isset( $mail_from ) && stristr( $header_value_str, 'From:' ) === false ) {
					$header_value[] = 'From: ' . $from_name . ' <' . $mail_from . '>';
				} else {
					$header_value[] = 'From: ' . get_bloginfo( 'name' ) . ' <' . $mail_from . '>';
				}
				if ( ! empty( $reply_to ) && isset( $reply_to ) && stristr( $header_value_str, 'Reply-To:' ) === false ) {
					$header_value[] = 'Reply-To: <' . $reply_to . '>';
				}

				$header_value[] = 'MIME-Version: 1.0';
				$header_value[] = 'Content-Type: text/html; charset=UTF-8';

				/**call php mail function to send mail*/
				return $result_email = wp_mail( $to, $subject, $message, $header_value, $attachments );
			}
		}

		/**
		 * replace field shortcode by field value
		 *
		 * @parameter: $content, $post_id
		 **/

		public function replace_values( $content, $post_id ) {
			if ( isset( $content ) && $content != '' && isset( $post_id ) && $post_id != '' ) {
				$first_name = get_post_meta( $post_id, 'first_name', true );
				$last_name  = get_post_meta( $post_id, 'last_name', true );
				$email      = get_post_meta( $post_id, 'email', true );

				$all_fields = '
                        <hr style="width:600px;height:1px; background:#ddd; margin:25px 0 35px -40px;border:0">
                        <h2 style="font-size:20px">Application Details</h2>
                        <table style="width: 520px;padding: 20px 30px;background:#f7f7f7;border-radius:5px;margin:25px 0 35px;border-spacing:0">
				<tbody style="font-size: 13px;line-height:20px">';
				global $mortgage_application_form_fields;
				if ( ! empty( $mortgage_application_form_fields ) && isset( $mortgage_application_form_fields ) ) {
					foreach ( $mortgage_application_form_fields as $form_field_key => $form_field_label ) {
						$field_value = get_post_meta( $post_id, $form_field_key, true );
						if ( isset( $field_value ) && $field_value != '' ) {
							if ( isset( $form_field_key ) && ! empty( $form_field_key ) && $form_field_key == 'ss_number' ) {
								$encrypted_value                = $field_value;
								list($encrypted_value, $enc_iv) = explode( '::', $encrypted_value );
								$cipher_method                  = 'aes-128-ctr';
								$enc_key                        = openssl_digest( php_uname(), 'SHA256', true );
								$decrypted_value                = openssl_decrypt( $encrypted_value, $cipher_method, $enc_key, 0, hex2bin( $enc_iv ) );

								$field_value = $decrypted_value;
							}
							$all_fields = $all_fields . '<tr>
							<td style="color: #333;padding:12px 0;margin:0;text-align: left" align="right">' . esc_html( $form_field_label ) . '</td>
							<td style="color: #333;font-weight:bold;padding:12px 0" align="right">' . esc_attr( $field_value ) . '</td>
							</tr>';
						}
					}
				}
				$all_fields = $all_fields . '</tbody></table>';

				/* get the short url */
				$bitly           = new mapp_bitly_shortURL();
				$long_url        = get_site_url( null, '?ma_mode=ma_edit&id=' . $post_id, 'https' );
				$short_url_json  = $bitly->get_short_url( $long_url );
				$short_url_array = json_decode( $short_url_json, true );
				$edit_url        = '';
				if ( is_array( $short_url_array ) && isset( $short_url_array['link'] ) && ! empty( $short_url_array['link'] ) ) {
					$edit_url = '<a href="' . $short_url_array['link'] . '">' . $short_url_array['link'] . '</a>';
				}

				$content = str_replace( '{first_name}', $first_name, $content );
				$content = str_replace( '{last_name}', $last_name, $content );
				$content = str_replace( '{email}', $email, $content );
				$content = str_replace( '{edit_url}', $edit_url, $content );
				$content = str_replace( '{all_fields}', $all_fields, $content );

				if ( $this->status !== false && $this->status == 'valid' && $this->license !== false ) {

					// get fnm file Download url
					$fnm_url = '<a style="background: #1dbc60;padding: 15px;color: #fff;text-decoration: none; border-radius: 3px;font-size: 14px;" href="' . wp_nonce_url( admin_url( 'admin-post.php?post=' . $post_id . '&export_type=fnm&action=mortgage_application_export_applications' ), 'mortgage_application_export_applications', 'mortgage_application_export_nonce' ) . '">' . esc_html__( 'Export to FNM 3.2', '1003-mortgage-application' ) . '</a>';

					// get csv file Download url
					$csv_url = '<a style="background:#1dbc60;padding: 15px;color: #fff;text-decoration: none; font-size: 14px;border-radius: 3px;" href="' . wp_nonce_url( admin_url( 'admin-post.php?post=' . $post_id . '&export_type=csv&action=mortgage_application_export_applications' ), 'mortgage_application_export_applications', 'mortgage_application_export_nonce' ) . '">' . esc_html__( 'Export to CSV', '1003-mortgage-application' ) . '</a>';

					$mismo_url = '<a style="background:#1dbc60;padding: 15px;color: #fff;text-decoration: none; font-size: 14px;border-radius: 3px;" href="' . wp_nonce_url( admin_url( 'admin-post.php?post=' . $post_id . '&export_type=mismo&action=mortgage_application_export_applications' ), 'mortgage_application_export_applications', 'mortgage_application_export_nonce' ) . '">' . esc_html__( 'Export to MISMO 3.4', '1003-mortgage-application' ) . '</a>';

					$content = str_replace( '{fnm_file_url}', $fnm_url, $content );
					$content = str_replace( '{csv_file_url}', $csv_url, $content );
					$content = str_replace( '{mismo_file_url}', $mismo_url, $content );
				}

				$content = '<div style="background-color: #f7f7f7;margin: -8px;font-family:Arial;font-size:13px;line-height: 20px;padding: 25px 0;">
					<div style="max-width: 600px; margin: 0 auto;">
					<div style="background-color: #fff; margin: 0px; padding: 50px 40px; border: 1px solid #ddd;border-radius:5px">
					' . $content . '
					</div>
					</div>
					</div>';
			}
			return $content;
		}
	}
}
