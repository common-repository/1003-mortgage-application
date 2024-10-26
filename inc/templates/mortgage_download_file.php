<?php
/**
 * This file is responsible to application form front view.
 *
 * @link        https://lenderd.com
 * @since       1.0.0
 *
 * @package     mortgage_application
 * @sub-package mortgage_application/inc/templates
 *
 * phpcs:disable WordPress.WP.AlternativeFunctions, WordPress.Security.NonceVerification
 */
// If this file is called directly, abort.
$dest_file   = '';
$source_file = '';
if ( isset( $_POST['map_source_file'] ) && $_POST['map_source_file'] != '' ) {
	$source_file = explode( ',', $_POST['map_source_file'] );
}
if ( isset( $_POST['map_dest_file'] ) && $_POST['map_dest_file'] != '' ) {
	$dest_file = explode( ',', $_POST['map_dest_file'] );
}
if ( ! empty( $source_file ) && $source_file != '' && ! empty( $dest_file ) && $dest_file != '' ) {

	decryptFile( $source_file, '__^%&Q@$&*!@#$%^&*^__', $dest_file );
}
function decryptFile( $source, $key, $dest ) {
	if ( is_array( $source ) && is_array( $dest ) ) {
		$add_zip_files = array();
		$key           = substr( sha1( $key, true ), 0, 16 );
		for ( $count_dest = 0; $count_dest < count( $dest ); $count_dest++ ) {
			$error = false;
			if ( $fpOut = fopen( $dest[ $count_dest ], 'w' ) ) {
				if ( $fpIn = fopen( $source[ $count_dest ], 'rb' ) ) {
					$iv = fread( $fpIn, 16 );
					while ( ! feof( $fpIn ) ) {
						$ciphertext = fread( $fpIn, 16 * ( 10000 + 1 ) );
						$plaintext  = openssl_decrypt( $ciphertext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv );
						$iv         = substr( $ciphertext, 0, 16 );
						fwrite( $fpOut, $plaintext );
					}
					fclose( $fpIn );
					$add_zip_files[] = $dest[ $count_dest ];
				} else {
					$error = true;
				}
				fclose( $fpOut );
			} else {
				$error = true;
			}
		}
		$unling_files  = array();
		$zip_filesname = time() . 'attachedfiles.zip';
		$zip           = new ZipArchive();
		if ( $zip->open( $zip_filesname, ZipArchive::CREATE ) === true ) {
			for ( $count_zip = 0; $count_zip < count( $add_zip_files ); $count_zip++ ) {
				$zip->addFile( $add_zip_files[ $count_zip ], basename( $add_zip_files[ $count_zip ] ) );
				$unling_files[] = $add_zip_files[ $count_zip ];
			}
			$zip->close();
		}
		$unling_files[] = $zip_filesname;
		ob_clean();
		flush();
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . basename( $zip_filesname ) . '"' );
		header( 'Content-Length: ' . filesize( $zip_filesname ) );
		readfile( $zip_filesname );
		for ( $count_link = 0;  $count_link < count( $unling_files ); $count_link++ ) {
			unlink( $unling_files[ $count_link ] );
		}
	}
}
