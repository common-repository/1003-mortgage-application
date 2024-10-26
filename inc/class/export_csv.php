<?php
class mapp_exportMortgageApplicationsCSV extends mapp_exportMortgageApplicationsBase {

	public function __construct() {
		parent::__construct();
	}

	public function create_file( $folder_name, $data ) {
		$generatedDate = $generatedDate = gmdate( 'd-m-Y His' );
		/**
		 * create a file pointer connected to the output stream
		 *
		 * @var [type]
		 */
		$upload_dir_path = $this->get_dir_path( $folder_name );
		// check csv dir exists
		if ( ! file_exists( $upload_dir_path ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
			mkdir( $upload_dir_path, 0777, true );
		}
		// empty csv dir
		array_map( 'unlink', glob( $upload_dir_path . '/*' ) );
		// Initialize archive object
		if ( ! empty( $data ) && isset( $data ) ) {
			if ( count( $data ) > 1 ) {
				// create aggregate file and open
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
				$aggregate_output = fopen( $upload_dir_path . 'aggregate.csv', 'w' );
				/**
				 * output the aggregate column headings
				 */
				fputcsv( $aggregate_output, $this->file_header );
			}

			foreach ( $data as $key => $value ) {
				$temp_header    = $this->file_header; // reassign header to temp header
				$application_id = $value->ID;
				$file_name      = sanitize_title( wp_strip_all_tags( get_post_meta( $application_id, 'email', true ) ) );
				// create a file and open
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
				$output = fopen( $upload_dir_path . $file_name . '-' . $application_id . '.csv', 'w' );

				$application_fields = get_post_meta( $application_id, '', true );

				$modified_values  = array();
				$modified_header  = array();
				$aggregate_values = array();
				if ( ! empty( $this->data_key ) ) {
					// print_r( $application_fields );
					// print_r( $this->data_key );
					// die();
					foreach ( $this->data_key as $n_key => $key ) {
						if ( isset( $application_fields[ $key ] ) && $key == 'ss_number' ) {
							$encrypted_value                = $application_fields[ $key ][0];
							list($encrypted_value, $enc_iv) = explode( '::', $encrypted_value );
							$cipher_method                  = 'aes-128-ctr';
							$enc_key                        = openssl_digest( php_uname(), 'SHA256', true );
							$decrypted_value                = openssl_decrypt( $encrypted_value, $cipher_method, $enc_key, 0, hex2bin( $enc_iv ) );
							$modified_values[]              = escape_csv_value( $decrypted_value );
							$aggregate_values[]             = escape_csv_value( $decrypted_value );
						} elseif ( isset( $application_fields[ $key ] ) ) {
							$modified_values[]  = escape_csv_value( $application_fields[ $key ][0] );
							$aggregate_values[] = escape_csv_value( $application_fields[ $key ][0] );
						} else {
							unset( $temp_header[ $n_key ] );
							$aggregate_values[] = '';
						}
					}
				}

				// die();
				/**
				 *                 echo '<pre>';
								print_r($temp_header);
								print_r($modified_values);
								echo '</pre>';

				 * output the column headings
				 */
				fputcsv( $output, $temp_header );
				fputcsv( $output, $modified_values );
				if ( count( $data ) > 1 ) {
					// put aggregate values
					fputcsv( $aggregate_output, $aggregate_values );
				}
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
				fclose( $output );
			}
			if ( count( $data ) > 1 ) {
				// put aggregate values
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
				fclose( $aggregate_output );
			}
		}
		// Get real path for our folder
		$rootPath = realpath( $upload_dir_path );
		$zip_url  = $this->create_zip( $rootPath );
		$this->download_zip( $zip_url );
		// return;
	}
}
