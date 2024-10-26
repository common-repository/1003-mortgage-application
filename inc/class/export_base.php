<?php
class mapp_exportMortgageApplicationsBase implements mapp_mortgageApplicationExport {
	protected $file_header;
	protected $data_key;

	public function __construct() {
		global $mortgage_application_form_fields, $export_fields;

		$this->file_header = array_values( $mortgage_application_form_fields );
		$this->data_key    = array_keys( $mortgage_application_form_fields );
		// check specific fields are selected
		if ( ! empty( $export_fields ) && is_array( $export_fields ) ) {
			$this->data_key    = $export_fields;
			$this->file_header = array();
			foreach ( $this->data_key as $key ) {
				$this->file_header[] = $mortgage_application_form_fields[ $key ];
			}
		}
	}

	public function create_file( $name, $data ) {}

	public function get_dir_path( $path = '' ) {
		// get WordPress upload dir array
		$upload_dir             = wp_get_upload_dir();
		return $upload_dir_path = trailingslashit( $upload_dir['path'] . ( ! empty( $path ) ? '/' . $path : '' ) );
	}

	public function create_zip( $file_folder ) {
		// Initialize archive object
		$zip = new ZipArchive();
		// get upload dir path
		$dir_path = $this->get_dir_path();
		// create zip archive
		$zip->open( $dir_path . 'applications.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE );
		// Create recursive directory iterator
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $file_folder ),
			RecursiveIteratorIterator::LEAVES_ONLY
		);
		if ( ! empty( $files ) && isset( $files ) ) {
			foreach ( $files as $name => $file ) {
				// Skip directories (they would be added automatically)
				if ( ! $file->isDir() ) {
					// Get real and relative path for current file
					$filePath     = $file->getRealPath();
					$relativePath = substr( $filePath, strlen( $file_folder ) + 1 );
					// Add current file to archive
					$zip->addFile( $filePath, $relativePath );
				}
			}
		}
		// Zip archive will be created only after closing object
		$zip->close();
		return $dir_path . 'applications.zip';
	}

	public function download_zip( $archive_name ) {
		/**
		 * output header so that file is downloaded
		 * instead of open for reading.
		 */
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Cache-Control: private', false );
		header( 'Content-type: application/zip' );
		header( 'Content-Disposition: attachment; filename=' . basename( $archive_name ) );
		header( 'Content-length: ' . filesize( $archive_name ) );
		header( 'Content-Transfer-Encoding: binary' );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		readfile( "$archive_name" );
		exit;
	}
}
