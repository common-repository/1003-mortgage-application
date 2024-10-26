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
 * phpcs:disable WordPress.WP.AlternativeFunctions, Generic.PHP.ForbiddenFunctions.Found, WordPress.Security.NonceVerification
 */
// If this file is called directly,    abort.
defined( 'ABSPATH' ) or die( 'Access denied!' );
// activation callback method

/*enqueue scripts callback*/
function mapp_mortgage_application_enqueue_scripts() {
	wp_register_script( 'ma_js', MAPP_MORTGAGE_APP_BASE_URL . 'assets/js/bundle.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-slider' ), '1.0.0', true );
	wp_enqueue_script( 'jquery-ui-datepicker' );
}
/* include classes. */

// retrieve the license from the database


function mortgate_application_download_file_callback() {
	// verify the nonce
	if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'mortgate_application_download_file' ) ) {
		wp_send_json_error();
	}

	if ( isset( $_POST['download_url'] ) && $_POST['download_url'] != '' ) {
		$fileurl = $_POST['download_url'];
		// Check if the file is in the allowed directory
		if ( strpos( $fileurl, MAPP_MORTGAGE_APP_BASE_URL ) === 0 ) {
			header( 'Content-type:application/png' );
			header( 'Content-Disposition: attachment; filename=' . $fileurl );
			readfile( $fileurl );
			wp_send_json_success();
		} else {
			wp_send_json_error( 'File is not in the allowed directory' );
		}
	} else {
		wp_send_json_error( 'No file URL provided' );
	}
}

// function mortgate_application_download_file_callback()
// {
// if (isset($_POST["download_url"]) && $_POST["download_url"] != "") {
// $fileurl = $_POST["download_url"];
// header("Content-type:application/png");
// header('Content-Disposition: attachment; filename=' . $fileurl);
// readfile($fileurl);
// wp_send_json_success();
// }
// wp_send_json_error();
// }


function map_send_email_notification_func( $to, $subject, $message, $map_reply_to ) {
	$from_name = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_application_mail_from_name', 'mortgage_application_form_network_settings' ) );

	if ( ! empty( $to ) && ! empty( $subject ) && ! empty( $message ) && ! empty( $map_reply_to ) ) {
		$headers        = array( 'Reply-To: <' . $map_reply_to . '>' );
		if ( strpos( $from_name, '[' ) !== false ) {
			$from_name = do_shortcode( $from_name );
		}
		$headers[]      = 'From: ' . $from_name . ' <' . $map_reply_to . '>';
		$header_value[] = 'MIME-Version: 1.0';
		$header_value[] = 'Content-Type: text/html; charset=UTF-8';
		$ret            = wp_mail( $to, $subject, $message, $headers );
	}
}

function mapp_mortgage_application_register_file_uploads_custom_post_func() {
	$license = trim( get_mortgage_application_option( 'ma_license_key' ) );
	$status  = get_mortgage_application_option( 'ma_license_key_status' );
	if ( $status !== false && $status == 'valid' && $license !== false ) {
		// Set UI labels for Custom Post Type
		$labels = array(
			'name'               => _x( 'File Uploads', 'Post Type General Name', 'twentytwenty' ),
			'singular_name'      => _x( 'File Upload', 'Post Type Singular Name', 'twentytwenty' ),
			'menu_name'          => __( 'File Uploads', 'twentytwenty' ),
			'parent_item_colon'  => __( 'Parent File Uploads', 'twentytwenty' ),
			'all_items'          => __( 'File Uploads', 'twentytwenty' ),
			'view_item'          => __( 'View Files', 'twentytwenty' ),
			'add_new_item'       => esc_html__( 'Add New File', 'twentytwenty' ),
			'add_new'            => __( 'Add New', 'twentytwenty' ),
			'edit_item'          => __( 'Edit File', 'twentytwenty' ),
			'update_item'        => __( 'Update File', 'twentytwenty' ),
			'search_items'       => __( 'Search File', 'twentytwenty' ),
			'not_found'          => __( 'Not Found', 'twentytwenty' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'twentytwenty' ),
		);

		// Set other options for Custom Post Type

		$args = array(
			'label'               => __( 'File Uploads', 'twentytwenty' ),
			'description'         => __( 'File Uploads using Shortcode', 'twentytwenty' ),
			'labels'              => $labels,
			// Features this CPT supports in Post Editor
			'supports'            => array( 'title' ),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=mortgage_application',
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'can_export'          => false,
			'has_archive'         => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => false,
			'capability_type'     => 'post',
			'capabilities'        => array(
				'create_posts' => 'do_not_allow',
				/*
				'edit_post' => 'do_not_allow',
					'delete_post' => 'do_not_allow',*/
			),
			'show_in_rest'        => false,
		);

		// Registering your Custom Post Type
		register_post_type( 'mapp_file_uploads', $args );
	}
}

add_filter( 'manage_mapp_file_uploads_posts_columns', 'mapp_mortgage_application_filter_posts_columns_func' );
function mapp_mortgage_application_filter_posts_columns_func( $columns ) {
	$columns['file_uploads_email'] = __( 'Email', 'file-uploads' );
	$columns['file_uploads_date']  = __( 'Upload Date', 'file-uploads' );
	$columns['file_delete_files']  = __( 'Deletion Time', 'file-uploads' );
	$columns['file_uploads_files'] = __( 'Files', 'file-uploads' );
	unset( $columns['page-title'] );
	unset( $columns['date'] );
	unset( $columns['page-meta-robots'] );
	$columns['title'] = 'Name';
	return $columns;
}


add_action( 'manage_mapp_file_uploads_posts_custom_column', 'mapp_mortgage_application_realestate_column_func', 10, 2 );
function mapp_mortgage_application_realestate_column_func( $column, $post_id ) {
	if ( 'file_uploads_email' === $column ) {
		echo esc_html( get_post_meta( $post_id, 'file_uploads_email', true ) );
		;
	}
	if ( 'file_uploads_date' === $column ) {
		$upload_timestamp = get_post_meta( $post_id, 'file_uploads_date', true );
		// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		echo esc_html( gmdate( 'm/d/Y @ g:ia', $upload_timestamp ) );
	}
	if ( 'file_delete_files' === $column ) {
		$saved_delete_days = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_ma_submissions_deleted_file', 'mortgage_submissions_use_form_network_settings' ) );
		$upload_timestamp  = get_post_meta( $post_id, 'file_uploads_date', true );
		$delete_timestamp  = strtotime( '+' . $saved_delete_days . ' days', $upload_timestamp );
		// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		echo esc_html( gmdate( 'm/d/Y @ g:ia', $delete_timestamp ) );
	}
	if ( 'file_uploads_files' === $column ) {
		?><table>
		<?php
			$current_blog_id = get_current_blog_id();
			$download_source = MAPP_MORTGAGE_APP_BASE_URL . 'inc/templates/mortgage_download_file.php';
			$donwload_limit  = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_ma_submissions_download_limit', 'mortgage_submissions_use_form_network_settings' ) );
			$path            = MAPP_MORTGAGE_APP_BASE_PATH . 'uploads/' . $current_blog_id;
			$uploaded_files  = get_post_meta( $post_id, 'file_uploads_files', true );

			write_log( 'POST META' );
			$post_meta = get_post_meta( $post_id );
			write_log( $post_meta );

			$dec_result    = array();
			$source_result = array();
		if ( is_array( $uploaded_files ) && count( $uploaded_files ) > 0 ) {
			for ( $file_loop = 0; $file_loop < count( $uploaded_files ); $file_loop++ ) {
				$file_ext        = substr( $uploaded_files[ $file_loop ], strrpos( $uploaded_files[ $file_loop ], '.' ) + 1 );
				$str             = rand();
				$enc_result      = hash( 'sha256', $str );
				$dec_result[]    = $path . '/' . $enc_result . '.' . $file_ext;
				$source_result[] = $path . '/' . $uploaded_files[ $file_loop ];
			}
			$saved_delete_days = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_ma_submissions_deleted_file', 'mortgage_submissions_use_form_network_settings' ) );
			$upload_timestamp  = get_post_meta( $post_id, 'file_uploads_date', true );
			$delete_timestamp  = strtotime( '+' . $saved_delete_days . ' days', $upload_timestamp );
			$disabled_download = '';
			if ( $delete_timestamp < time() ) {
				$disabled_download = 'disabled';
			}
			?>
				<tr>
					<td>
					<?php
					echo '<form><input type="submit" class="map_dwn_file" value="Download" style="display:none;" /></form><form action="' . esc_url( admin_url( 'admin-ajax.php' ) ) . '" method="post">
                                        <input type="hidden" name="post_id" value="' . (int) $post_id . '" />
                                        <input type="hidden" name="security" value="' . esc_attr( wp_create_nonce( 'mortgate_application_download_file' ) ) . '" />
                                        <input type="hidden" name="action" value="mortgage_application_download_file" />
														<input title="Download Files as zip" type="submit" class="map_dwn_file" value="Download File(s)" ' . esc_attr( $disabled_download ) . '/>
												  </form>';
					?>
					</td>
				</tr>
						<?php
		}
		?>
					</table>
					<?php
	}
}
function mapp_mortgage_application_file_uploads_callback( $atts = array() ) {
	wp_enqueue_style( 'ma_css', MAPP_MORTGAGE_APP_BASE_URL . 'assets/css/style.css', array(), true );
	$license      = trim( get_mortgage_application_option( 'ma_license_key' ) );
	$status       = get_mortgage_application_option( 'ma_license_key_status' );
	$button_color = get_front_mortgage_application_option( 'mortgage_application_button_color', 'mortgage_application_use_network_settings' );
	if ( $status !== false && $status == 'valid' && $license !== false ) {
		$map_set_extension = get_front_mortgage_application_option( 'mortgage_ma_submissions_file_extension', 'mortgage_submissions_use_form_network_settings' );
		if ( isset( $_POST['btn'] ) && is_array( $_FILES ) && count( $_FILES ) ) {
			$map_extension_array = explode( ',', $map_set_extension );
			$current_blog_id     = get_current_blog_id();
			$uploads_dir         = MAPP_MORTGAGE_APP_BASE_PATH . 'uploads/' . $current_blog_id . '/';
			$map_to_email        = sanitize_email( $_POST['ma_submission_email_text'] );
			$map_subject         = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_ma_submissions_subject', 'mortgage_submissions_use_form_network_settings' ) );
			$client_map_subject  = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_ma_submissions_client_subject', 'mortgage_submissions_use_form_network_settings' ) );
			$map_reply_to        = get_front_mortgage_application_option( 'mortgage_ma_submissions_reply_to', 'mortgage_submissions_use_form_network_settings' );
			if ( strpos( $map_reply_to, '[' ) !== false ) {
				$map_reply_to = do_shortcode( $map_reply_to );
			}

			$map_user_name = sanitize_text_field( $_POST['ma_submission_name_text'] );

			$files_not_upload = array();

			if ( ! is_dir( $uploads_dir ) ) {
				mkdir( $uploads_dir );
			}
			$no_of_files        = count( $_FILES['doc']['name'] );
			$file_creation_date = array();
			$map_is_file_saved  = 'no';
			$index              = 0;
			$upload_index       = 0;
			$zip_url            = '';
			$zip                = new ZipArchive();
			if ( $zip->open( $uploads_dir . time() . 'uploadedfiles.zip', ZipArchive::CREATE ) === true ) {
				$target_dir = $uploads_dir;
				for ( $zip_count = 0; $zip_count < $no_of_files; $zip_count++ ) {
					$check_target_file = $uploads_dir . basename( $_FILES['doc']['name'][ $zip_count ] );
					$check_file_type   = strtolower( pathinfo( $check_target_file, PATHINFO_EXTENSION ) );
					if ( in_array( $check_file_type, $map_extension_array ) ) {
						$target_file = $target_dir . basename( $_FILES['doc']['name'][ $zip_count ] );
						if ( move_uploaded_file( $_FILES['doc']['tmp_name'][ $zip_count ], $target_file ) ) {
							chmod( $target_file, 0777 );
							$zip->addFile( $target_file, basename( $target_file ) );
						}
					}
				}
				$zip_base = 'uploads/' . $current_blog_id . '/' . time() . 'uploadedfiles.zip';
				$zip_url  = MAPP_MORTGAGE_APP_BASE_URL . $zip_base;
				$zip->close();
				chmod( MAPP_MORTGAGE_APP_BASE_PATH . $zip_base, 0777 );
			}

			for ( $i = 0; $i < $no_of_files; $i++ ) {
				$check_target_file = $uploads_dir . basename( $_FILES['doc']['name'][ $i ] );
				$check_file_type   = strtolower( pathinfo( $check_target_file, PATHINFO_EXTENSION ) );

				/*
				* Checking that uploaded files extensions are equal to set extensions in backend.
				* If file types are in arrays of set extensions than files will uploaded.
				* Else file will not upload and name will display in frontend.
				*/
				if ( in_array( $check_file_type, $map_extension_array ) ) {
					$name     = $_FILES['doc']['name'][ $i ];
					$tmp_name = $_FILES['doc']['tmp_name'][ $i ];
					move_uploaded_file( $tmp_name, "$uploads_dir/$name" );
					chmod( "$uploads_dir/$name", 0777 );
					$file_ext = substr( $name, strrpos( $name, '.' ) + 1 );
					$str      = rand();
					$result   = hash( 'sha256', $str );
					$name2    = $result . '.' . $file_ext;
					// rename($name, $name2);
					// $name     = sanitize_file_name( $name );
					$List[ $i ]                      = implode( ', ', array( $name ) );
					$list_file_name[ $upload_index ] = implode( ', ', array( $name ) );
					$fileName                        = $uploads_dir . $name;

					$dest = $uploads_dir . $name;
					$key  = '__^%&Q@$&*!@#$%^&*^__';
					encryptFile( $fileName, $key, $dest );
					$map_is_file_saved = 'yes';
					unlink( $fileName );
					++$upload_index;
				} elseif ( ! in_array( $check_file_type, $map_extension_array ) ) {
					$name                       = $_FILES['doc']['name'][ $i ];
					$files_not_upload[ $index ] = array( $name );
					++$index;
				}
			}

			if ( isset( $map_is_file_saved ) && $map_is_file_saved == 'yes' ) {
				$map_selected_file_saved = 'Selected Files are successfully saved. <br>';

				$client_map_dynamic_fields = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_application_submision_client_message', 'mortgage_submissions_use_form_network_settings' ) );
				$client_map_files_lists    = implode( ', ', $List );
				$client_temp_string        = stripslashes( $client_map_dynamic_fields );
				$client_mail_message       = str_replace( '{name}', $map_user_name, $client_temp_string );
				$client_mail_message       = str_replace( '{docs}', $client_map_files_lists, $client_mail_message );

				$client_temp_sub_string = stripslashes( $client_map_subject );
				$client_map_subject     = str_replace( '{name}', $map_user_name, $client_temp_sub_string );

				map_send_email_notification_func( $map_to_email, $client_map_subject, $client_mail_message, $map_reply_to );

				$map_dynamic_fields   = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_application_submision_message', 'mortgage_submissions_use_form_network_settings' ) );
				$map_files_lists      = implode( ', ', $List );
				$temp_string          = stripslashes( $map_dynamic_fields );
				$mail_message         = str_replace( '{name}', $map_user_name, $temp_string );
				$mail_message         = str_replace( '{docs}', $map_files_lists, $mail_message );
				$map_backend_email_to = get_front_mortgage_application_option( 'mortgage_ma_submissions_email_to', 'mortgage_submissions_use_form_network_settings' );
				if ( strpos( $map_backend_email_to, '[' ) !== false ) {
					$map_backend_email_to = do_shortcode( $map_backend_email_to );
				}
				$mail_days      = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_ma_submissions_deleted_file', 'mortgage_submissions_use_form_network_settings' ) );
				$mail_downloads = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_ma_submissions_download_limit', 'mortgage_submissions_use_form_network_settings' ) );
				if ( isset( $map_backend_email_to ) && ! empty( $map_backend_email_to ) ) {
					if ( $zip_url != '' ) {
						$mail_message .= '<br/><br/><a target="_blank" href="' . $zip_url . '" style="background:#1dbc60;color:#fff;text-decoration:none;border-radius: 2px; margin:0 0 10px; display:inline-block; padding:8px 15px; line-height:25px">Click Here to Download</a><br /> <small>Files will expire in ' . $mail_days . 'day(s) or ' . $mail_downloads . ' download(s), whichever comes first.';
					}
					$temp_sub_string = stripslashes( $map_subject );
					$map_subject     = str_replace( '{name}', $map_user_name, $temp_sub_string );
					map_send_email_notification_func( $map_backend_email_to, $map_subject, $mail_message, $map_reply_to );
				}
				$mapp_file_uploads_args = array(
					'post_title'  => $map_user_name,
					'post_type'   => 'mapp_file_uploads',
					'post_status' => 'publish',
				);
				$mapp_file_uploads_id   = wp_insert_post( $mapp_file_uploads_args );
				if ( $mapp_file_uploads_id > 0 ) {
					update_post_meta( $mapp_file_uploads_id, 'file_uploads_email', $map_to_email );
					update_post_meta( $mapp_file_uploads_id, 'file_uploads_files', $list_file_name );
					update_post_meta( $mapp_file_uploads_id, 'file_uploads_date', time() );
				}

				$new_url = add_query_arg( 'upload', 'yes', get_permalink() );
				header( 'Location:' . $new_url . '' );
			}
			$map_error_files = array();
			if ( isset( $files_not_upload ) && ! empty( $files_not_upload ) ) {
				$map_error_msg = 'File(s) not uploaded, only the following file types are acceptable: (' . $map_set_extension . ') <br>';
				$count         = 1;
				foreach ( $files_not_upload as $get_single_file ) {
					// echo $count.":".$get_single_file[0]."<br>";
					$map_error_files[ $count ] = array( $get_single_file[0] );
					++$count;
				}
			}
			?>
			<script type="text/javascript">
				var $mapply_map2 = jQuery.noConflict();
				$mapply_map2(function($) {

					jQuery('html, body').animate({
						scrollTop: jQuery("#map_upload_form").offset().top
					}, 2000);

				});
			</script>
			<?php
		}
		ob_start();
		?>
		<style>
			.map_upload_submit {
				background: <?php echo sanitize_hex_color( $button_color ); ?>;
				color: #fff
			}
		</style>
		<div id="map_upload_form" class="map_upload_form">
			<div class="map_frontend_form_section">
				<form action="" method="post" id="map_main_frontend_form" enctype="multipart/form-data">
					<input type="text" name="ma_submission_name_text" aria-value="Your Name" id="ma_submission_name" placeholder="Your Name" value="" required>
					<input type="text" name="ma_submission_email_text" aria-value="Your Email Address" id="ma_submission_email" placeholder="Your Email Address" value="" required>
					<div class="map_upload_file">
						<label for="myfile">Upload File(s)</label>
						<input type="file" multiple="multiple" id="map_file_name" name="doc[]" required>
					</div>
					<input type="submit" name="btn" id="map_upload_file_check" class="map_upload_submit" value="Submit File(s)" />
					<input type="button" id="map_reset" style="display:none" value="Reset" />
					<small>Note: Only <?php echo esc_html( $map_set_extension ); ?> file types are allowed.</small>
				</form>
			</div>
			<div class="map_file_upload_section">
				<span class="map_file_upload_success_msg">
				<?php
				if ( ! empty( $_GET['upload'] ) && $_GET['upload'] == 'yes' ) {
																echo 'File(s) have been submitted successfully!';
					?>
						<script type="text/javascript">
							var $mapply_map = jQuery.noConflict();
							$mapply_map(function($) {

								jQuery('html, body').animate({
									scrollTop: jQuery("#map_upload_form").offset().top
								}, 2000);
							});
						</script>
					<?php
				}
				?>
				</span>
				<span class="map_file_upload_error_msg" style="color:red">
				<?php
				echo isset( $map_error_msg ) ? esc_html( $map_error_msg ) : '';
				$index = 1;
				$map_error_files = isset( $map_error_files ) ? $map_error_files : array();
				foreach ( $map_error_files as $get_single_error_file ) {
					echo esc_html( $index . ':' . $get_single_error_file[0] ) . '<br>';
					++$index;
				}
				?>
																			</span>
			</div>
			<div class="map_show_success_msg_section" style="display:none">
				<span class="map_show_fomr_validate_msg" style="color:red;"></span>
			</div>
		</div>
		<script type="text/javascript">
			var $mapply_foot = jQuery.noConflict();
			$mapply_foot(function($) {


				jQuery("#map_upload_file_check").click(function(e) {
					var user_name = jQuery("#ma_submission_name").val();
					var user_mail = jQuery("#ma_submission_email").val();
					var file_name = jQuery("#map_file_name").val();
					var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
					if (user_name == '') {
						jQuery(".map_show_fomr_validate_msg").html("Please enter your name.");
						jQuery(".map_show_success_msg_section").show();

						return false;
					}
					if (user_mail == '') {
						jQuery(".map_show_fomr_validate_msg").html("Please enter an email address");
						jQuery(".map_show_success_msg_section").show();

						return false;
					}
					if (!emailReg.test(user_mail)) {
						jQuery(".map_show_fomr_validate_msg").html("Please enter a valid email address");
						jQuery(".map_show_success_msg_section").show();

						return false;
					}
					if (file_name == '') {
						jQuery(".map_show_fomr_validate_msg").html("You must upload at least 1 file");
						jQuery(".map_show_success_msg_section").show();

						return false;
					}
				});
			});
		</script>

		<?php
	} else {
		ob_start();
		echo 'Featured Available Only for Premium Users.';
	}
	$result = ob_get_clean();
	return $result;
}
function encryptFile( $source, $key, $dest ) {
	$key = substr( sha1( $key, true ), 0, 16 );
	$iv  = openssl_random_pseudo_bytes( 16 );

	$error = false;
	if ( $fpOut = fopen( $dest, 'w' ) ) {
		fwrite( $fpOut, $iv );
		if ( $fpIn = fopen( $source, 'rb' ) ) {
			while ( ! feof( $fpIn ) ) {
				$plaintext  = fread( $fpIn, 16 * 10000 );
				$ciphertext = openssl_encrypt( $plaintext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv );

				$iv = substr( $ciphertext, 0, 16 );
				fwrite( $fpOut, $ciphertext );
			}
			fclose( $fpIn );
		} else {
			$error = true;
		}
		fclose( $fpOut );
	} else {
		$error = true;
	}

	return $error ? false : $dest;
}

function mapp_mortgage_application_form_callback( $atts = array(), $content = null, $tag = '' ) {
	// normalize attribute keys, lowercase
	$atts = array_change_key_case( (array) $atts, CASE_LOWER );
	// override default attributes with user attributes
	$atts = shortcode_atts(
		array(
			'bar'          => true,
			'notification' => true,
			'email'        => '',
		),
		$atts,
		'mortgage_application_form'
	);

	// Turn on output buffering
	ob_start();
	// check domain is ssl certified
	if ( is_ssl() ) {
		$default_google_api_key = 'AIzaSyBcTQ-NdFKPejoWRTtO6ZJzMIwTuIxc_1c';
		// $default_google_api_key =  sanitize_text_field(get_front_mortgage_application_option('mortgage_application_google_map_api_key', 'mortgage_application_use_network_settings'));

		/*add css file*/
		wp_enqueue_style( 'ma_css', MAPP_MORTGAGE_APP_BASE_URL . 'assets/css/style.css', array(), true );
		wp_enqueue_style( 'ma_mCustomScrollbar_css', MAPP_MORTGAGE_APP_BASE_URL . 'assets/css/jquery.mCustomScrollbar.css', array(), true );

		if ( ! has_shortcode( get_the_content(), '8b_home_value' ) ) {
			wp_enqueue_script( 'google-mps-api', 'https://maps.googleapis.com/maps/api/js?key=' . $default_google_api_key . '&libraries=places', array( 'jquery' ), '1.0.0', 'true' );
		}

		// add form template
		include MAPP_MORTGAGE_APP_BASE_PATH . 'inc/templates/mortgage_form.php';
		/*add js file*/
		wp_enqueue_script( 'ma_js', MAPP_MORTGAGE_APP_BASE_URL . 'assets/js/bundle.js', array(), true, true );
		// Localize the script with url
		$url_array = array(
			'ajax_url'                      => admin_url( 'admin-ajax.php' ),
			'home_url'                      => site_url(),
			'home_purchase_price_text'      => $home_purchase_price_text,
			'home_purchase_price_values'    => $home_purchase_price_values,
			/*'home_purchase_price_default' => $home_purchase_price_text,*/
			'down_payment_price_text'       => $down_payment_price_text,
			'down_payment_price_values'     => $down_payment_price_values,
			'home_value_price_text'         => $home_value_price_text,
			'home_value_price_values'       => $home_value_price_values,
			'mortgage_balance_price_text'   => $mortgage_balance_price_text,
			'mortgage_balance_price_values' => $mortgage_balance_price_values,
			'loan_interest_rate_text'       => $loan_interest_rate_text,
			'loan_interest_rate_values'     => $loan_interest_rate_values,
			'additional_funds_text'         => $additional_funds_text,
			'additional_funds_values'       => $additional_funds_values,
			'purchase_year_values'          => $purchase_year_values,
			'age_text'                      => $age_text,
			'age_values'                    => $age_values,
			'dynamic_email'                 => isset( $atts['email'] ) ? $atts['email'] : '',
		);
		wp_localize_script( 'ma_js', 'mortgage_application', $url_array );
		wp_enqueue_style( 'uicsshandle', MAPP_MORTGAGE_APP_BASE_URL . 'assets/css/jquery-ui.css', array(), true );
	} else {
		echo '<p class="error-message">' . esc_html__( 'Oops! This is not an SSL site and our plugin works with SSL sites only.', '1003-mortgage-application' ) . '</p>';
	}
	// Get current buffer contents and delete current output buffer
	return $result = ob_get_clean();
}
						/**
						 * get application setting base on multisite setting.
						 *
						 * @perma option name (string), network setting option name (string) to check network setting is enable
						 **/
function get_front_mortgage_application_option( $option, $network_option = 'mortgage_application_use_form_network_settings' ) {
	$check_network_form_enbale = get_option( $network_option );
	if ( is_multisite() && isset( $check_network_form_enbale ) && $check_network_form_enbale == '0' ) {
		return $options = get_site_option( $option );
	} else {
		return $options = get_option( $option );
	}
}
						/**
						 * check application is exists
						 *
						 * @param string $email_id
						 **/
function mapp_mortgate_application_check_application_existence( $email_id, $post_id = 0 ) {
	$email_exists = false;
	$args         = array(
		'post_type'  => 'mortgage_application',
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		'meta_query' => array(
			array(
				'key'     => 'email',
				'value'   => $email_id,
				'compare' => '==',
			),
		),
		'order'      => 'ASC',
	);
	// check for update
	if ( isset( $post_id ) && ! empty( $post_id ) && $post_id > 0 ) {
		// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
		$args['post__not_in'] = array( $post_id );
	}

	// get post
	$result = get_posts( $args );
	if ( count( $result ) > 0 ) {
		$email_exists = true;
		foreach ( $result as $application ) {
			$post_id            = $application->ID;
			$application_status = esc_attr( get_post_meta( $post_id, 'application_status', true ) );
			if ( isset( $application_status ) && ! empty( $application_status ) && $application_status == 80 ) {
				$login_date   = strtotime( gmdate( 'Y-m-d H:i:s', get_post_time( 'U', false, $post_id ) ) ); // change x with your login date var
				$current_date = strtotime( gmdate( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) ); // change y with your current date var
				$datediff     = $current_date - $login_date;
				$days         = floor( $datediff / ( 60 * 60 * 24 ) );
				if ( isset( $days ) && ! empty( $days ) && intval( $days ) < 30 ) {
					$recipient_list = array();
					// $message = get_front_mortgage_application_option('mortgage_application_reminder_mail_message', 'mortgage_application_use_network_settings');
					$message = ( ! empty( $message ) ? $message : __( 'You need to use an alternate email if you want to complete a new application before the other is completed.', '1003-mortgage-application' ) );
					// get user email
					$to = sanitize_email( get_post_meta( $post_id, 'email', true ) );
					if ( ! empty( $to ) && isset( $to ) ) {
						// $subject = get_front_mortgage_application_option('mortgage_application_reminder_mail_subject', 'mortgage_application_use_network_settings');
						$subject = ( ! empty( $subject ) ? $subject : __( 'Mortgage Application Email Already Exists', '1003-mortgage-application' ) );
						// get general functionality
						$general = new Mapp_Mortgage_general_functionality();
						$message = $general->replace_values( $message, $post_id );
						// send email
						$general->mortgage_mail( $to, $subject, $message );
					}
					$email_exists = true;
				} else {
					$email_exists = false;
				}
			} elseif ( isset( $application_status ) && ! empty( $application_status ) && $application_status == 100 ) {
				return 1; // return number of application found
			}
		}
	}
	// check validation
	if ( isset( $email_exists ) && ! empty( $email_exists ) && $email_exists === true ) {
		return count( $result ); // return nubmer of application found
	} else {
		return 0; // return nubmer of application found
	}
}

						/* application ajax save callback*/
function mapp_mortgate_application_data_save_callback() {
	$stripslashes_data = array_map( 'stripslashes_deep', $_POST );
	parse_str( $stripslashes_data['form_data'], $submitted_data );
	// check nonce and post data
	if ( isset( $submitted_data['application_data_save'] ) && ! empty( $submitted_data['application_data_save'] ) && wp_verify_nonce( $submitted_data['application_data_save'], 'mortgate_application_data_save' ) ) {
		unset( $submitted_data['application_data_save'] );
		unset( $submitted_data['_wp_http_referer'] );
		// Create post object
		$post_attr = array(
			'post_title'  => wp_strip_all_tags( $submitted_data['email'] ),
			'post_status' => 'publish',
			'post_type'   => 'mortgage_application',
		);
		if ( sanitize_text_field( $submitted_data['crud'] ) == 'ma_update' && intval( $submitted_data['rec_id'] ) > 0 && mapp_mortgate_application_check_application_existence( sanitize_email( $submitted_data['email'] ), intval( $submitted_data['rec_id'] ) ) < 1 ) {
			// get general functionality
			$general = new Mapp_Mortgage_general_functionality();

			unset( $submitted_data['crud'] );
			$rec_id = intval( $submitted_data['rec_id'] );
			unset( $submitted_data['rec_id'] );
			$ss_number = $submitted_data['ss_number'];
			unset( $submitted_data['ss_number'] );
			$post_attr['ID']         = $rec_id;
			$post_attr['post_title'] = wp_strip_all_tags( $submitted_data['first_name'] . ' ' . $submitted_data['last_name'] . ' ' . $submitted_data['email'] );
			$post_attr['meta_input'] = $submitted_data;
			// update post
			$post_id = wp_update_post( $post_attr );
			if ( isset( $ss_number ) && ! empty( $ss_number ) && $post_id > 0 ) {
				$cipher_method = 'aes-128-ctr';
				$enc_key       = openssl_digest( php_uname(), 'SHA256', true );
				$enc_iv        = openssl_random_pseudo_bytes( openssl_cipher_iv_length( $cipher_method ) );
				$encrypted     = openssl_encrypt( $ss_number, $cipher_method, $enc_key, 0, $enc_iv ) . '::' . bin2hex( $enc_iv );
				update_post_meta( $post_id, 'ss_number', $encrypted );
			}
			if ( ! is_wp_error( $post_id ) ) {
				// add action after post insert
				$result['id'] = $post_id;
				// send notification if application is complated
				if ( ! empty( $submitted_data['phone_number'] ) || ! empty( $submitted_data['first_name'] ) || ! empty( $submitted_data['last_name'] ) ) {
					$message = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_application_success_message', 'mortgage_application_form_network_settings' ) );
					/*shortcode for message*/
					if ( strpos( $message, '[' ) !== false ) {
						$message = do_shortcode( $message );
					}
					/*
											shortcode end*/
					// $result['message'] = __( $message, "mortgage_application" );
					$result['message'] = array(
						'sub_msg' => $message,
						'check'   => 'no',
					);
					// array('message' => "A new password has been sent to your email");
					/**
					 * send email notification to admin
					 */
					// get notification subject
					$subject = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_application_mail_subject', 'mortgage_application_use_network_settings' ) );
					/*shortcode for subject*/
					if ( strpos( $subject, '[' ) !== false ) {
						$subject = do_shortcode( $subject );
					}
					/*shortcode end*/
					$subject = ( ! empty( $subject ) ? $subject : __( 'Mortgage Application Submit Notification', '1003-mortgage-application' ) );
					$subject = $general->replace_values( $subject, $post_id );

					// get notification message and replace shortcode with values

					$email_message = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_application_mail_message', 'mortgage_application_form_network_settings' ) );
					/* shortcode for email_message */
					if ( strpos( $email_message, '[' ) !== false ) {
						$email_message = do_shortcode( $email_message );
					}
					/* shortcode end */
					$email_message = ( ! empty( $email_message ) ? $email_message : __( 'Mortgage application submitted successfully', '1003-mortgage-application' ) );
					$email_message = $general->replace_values( $email_message, $post_id );
					// set bcc header
					$recipient_list = array();
					if ( ! empty( $submitted_data['dynamic_email'] ) ) {
						$recipients = sanitize_email( $submitted_data['dynamic_email'] );
					} else {
						$recipients = get_front_mortgage_application_option( 'mortgage_application_email_recipients', 'mortgage_application_form_network_settings' );
						/*shortcode for recipients */
						if ( strpos( $recipients, '[' ) !== false ) {
							$recipients = do_shortcode( $recipients );
						}
					}
					/*shortcode end */
					if ( ! empty( $recipients ) && isset( $recipients ) ) {
						$recipient_list = explode( ',', $recipients );
					}
					$header = array();
					// set Reply to header
					$header[] = 'Reply-To: <' . sanitize_email( get_post_meta( $post_id, 'email', true ) ) . '>';
					// set cc emails
					if ( ! empty( $recipient_list ) && isset( $recipient_list ) ) {
						$recipient_list_str = implode( ', ', $recipient_list );
						$to                 = $recipient_list_str;
					}
					// send email
					$general->mortgage_mail( $to, $subject, $email_message, $header );

					$client_email_recipients_toggle = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_application_client_email_recipients', 'mortgage_application_use_network_settings' ) );
					if ( $client_email_recipients_toggle == 'on' ) {
						/**
						 * send email notification to user
						 */
						$to = sanitize_email( get_post_meta( $post_id, 'email', true ) );

						// get notification subject
						$subject = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_application_user_mail_subject', 'mortgage_application_use_network_settings' ) );

						/*shortcode for subject*/
						if ( strpos( $subject, '[' ) !== false ) {
							$subject = do_shortcode( $subject );
						}
						/*shortcode end*/
						$subject = ( ! empty( $subject ) ? $subject : __( 'Mortgage Application Submit Notification', '1003-mortgage-application' ) );
						$subject = $general->replace_values( $subject, $post_id );
						// get notification message and replace shortcode with values
						$email_message = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_application_user_mail_message', 'mortgage_application_form_network_settings' ) );
						/* shortcode for email_message */
						if ( strpos( $email_message, '[' ) !== false ) {
							$email_message = do_shortcode( $email_message );
						}
						/* shortcode end */

						$email_message = ( ! empty( $email_message ) ? $email_message : __( 'Mortgage application submitted successfully', '1003-mortgage-application' ) );
						$email_message = $general->replace_values( $email_message, $post_id );
						// send email
						$general->mortgage_mail( $to, $subject, $email_message );
					}
					// send application to webhook
					mapp_mortgage_application_send_application_to_webhooks( $post_id );
				}

				// send ajax result
				wp_send_json_success( $result );
			} else {
				// there was an error in the application insertion,
				$error = $post_id->get_error_message();
				wp_send_json_error( $error );
			}
		} elseif ( sanitize_text_field( $submitted_data['crud'] ) == 'ma_add' && mapp_mortgate_application_check_application_existence( sanitize_email( $submitted_data['email'] ) ) < 1 ) {
			unset( $submitted_data['crud'] );
			unset( $submitted_data['rec_id'] );
			$post_attr['meta_input'] = $submitted_data;
			// Create application object
			$post_attr = array(
				'post_title'  => wp_strip_all_tags( $submitted_data['email'] ),
				'post_status' => 'publish',
				'post_type'   => 'mortgage_application',
				'meta_input'  => $submitted_data,
			);
			// Insert the post into the database
			$post_id = wp_insert_post( $post_attr );
			if ( ! is_wp_error( $post_id ) ) {
				// add action after post insert
				$result            = array();
				$result['id']      = $post_id;
				$result['message'] = array(
					'msg'   => __( 'Your application progress has been saved!', '1003-mortgage-application' ),
					'check' => 'yes',
				);
				wp_send_json_success( $result );
			} else {
				// there was an error in the post insertion,
				$error = $post_id->get_error_message();
				wp_send_json_error( $error );
			}
		} else {
			// there was an error in the application insertion,
			$error = __( 'Email address already used.', '1003-mortgage-application' );
			wp_send_json_error( $error );
		}
	}
}
						/**
						 * show application form in edit form
						 **/
function mapp_mortgate_application_add_edit_form() {
	if ( isset( $_GET['ma_mode'] ) && ! empty( $_GET['ma_mode'] ) && sanitize_text_field( $_GET['ma_mode'] ) == 'ma_edit' && intval( sanitize_text_field( $_GET['id'] ) ) > 0 ) {
		?>
		<div id="mortgate_overlay"></div>
		<div id="mortgate_popup" class="cwa_mortgate_overlay_popup">
			<div id="mortgate_popup_content">
				<div id="mortgate_popup_content_inner">
					<a href="<?php echo esc_url( get_site_url() ); ?>" id="mortgate_popup_close" class="close">
						<span aria-hidden="true">×</span>
					</a>
		<?php echo do_shortcode( '[mortgage_application_form]' ); ?>
				</div>
			</div>
		</div>
		<?php
	}
}
						/**
						 * hourly event reminder callback on 80% application status
						 */
if ( ! function_exists( 'mapp_mortgate_hourly_event_callback' ) ) {
	function mapp_mortgate_hourly_event_callback() {
		// check post those have 80% status
		$post_arg = array(
			'post_type'  => 'mortgage_application',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'     => 'notification_status',
					'compare' => 'NOT EXISTS', // check key not exists
				),
				array(
					'key'     => 'application_status',
					'value'   => '80',
					'compate' => '=',
				),

			),
			'date_query' => array(
				array(
					'before'    => gmdate( 'Y-m-d H:i:s', strtotime( '- 24 hours', strtotime( gmdate( 'Y-m-d H:i:s' ) ) ) ),
					'inclusive' => true,
				),
			),
		);
		$query = new WP_Query( $post_arg );
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				// echo get_the_ID();
				// get updated post id
				$post_id = get_the_ID();
				/*get email general settings*/
				$recipient_list = array();
				$message        = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_application_reminder_mail_message', 'mortgage_application_use_network_settings' ) );
				$message        = ( ! empty( $message ) ? $message : __( 'Click the link below to complete your application', '1003-mortgage-application' ) );
				// get user email
				$to = sanitize_email( get_post_meta( $post_id, 'email', true ) );
				if ( ! empty( $to ) && isset( $to ) ) {
					$subject = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_application_reminder_mail_subject', 'mortgage_application_use_network_settings' ) );
					$subject = ( ! empty( $subject ) ? $subject : __( 'Incomplete Mortgage Application', '1003-mortgage-application' ) );
					// get general functionality
					$general = new Mapp_Mortgage_general_functionality();
					$message = $general->replace_values( $message, $post_id );
					// send email
					$general->mortgage_mail( $to, $subject, $message );
				}
				// update notification status
				update_post_meta( $post_id, 'notification_status', 'send' );
			}
		}
	}
}

						/*
* hourly event to check file delete days
*/
if ( ! function_exists( 'mapp_mortgate_check_hourly_event_callback' ) ) {
	function mapp_mortgate_check_hourly_event_callback() {
		$current_blog_id = get_current_blog_id();
		$path            = MAPP_MORTGAGE_APP_BASE_PATH . 'uploads/' . $current_blog_id . '/';

		$all_files = scandir( $path );

		$no_of_days_to_delete = get_front_mortgage_application_option( 'mortgage_ma_submissions_deleted_file', 'mortgage_submissions_use_form_network_settings' );
		$no_of_hours          = $no_of_days_to_delete * 86400;
		foreach ( $all_files as $var => $val ) {
			if ( $val !== '.' && $val !== '..' ) {
				$stat               = stat( $path . '/' . $val );
				$file_creation_time = $stat['mtime'];
				$current_time       = time();
				$difference         = $current_time - $file_creation_time;
				if ( $difference >= $no_of_hours ) {
					unlink( $path . '/' . $val );
				}
			}
		}
	}
}


						/* cron function to check file delete days */
function map_cron_job_to_check_file_delete_days() {
	if ( ! wp_next_scheduled( 'mortgate_check_hourly_event' ) ) {
		wp_schedule_event( time(), 'hourly', 'mortgate_check_hourly_event' );
	}
}
						add_action( 'admin_init', 'map_cron_job_to_check_file_delete_days' );



						/**
						 * get application meta data
						 *
						 * @perma int post id
						 **/
function mapp_mortgage_application_get_application_meta( $post_id ) {
	global $mortgage_application_form_fields;
	$meta_data = array();
	if ( ! empty( $mortgage_application_form_fields ) && isset( $mortgage_application_form_fields ) ) {
		foreach ( $mortgage_application_form_fields as $form_field_key => $form_field_label ) {
			$field_value = esc_attr( get_post_meta( $post_id, $form_field_key, true ) );
			if ( isset( $field_value ) && $field_value != '' ) {
				if ( isset( $form_field_key ) && ! empty( $form_field_key ) && $form_field_key == 'ss_number' ) {
					$encrypted_value                = $field_value;
					list($encrypted_value, $enc_iv) = explode( '::', $encrypted_value );
					$cipher_method                  = 'aes-128-ctr';
					$enc_key                        = openssl_digest( php_uname(), 'SHA256', true );
					$decrypted_value                = openssl_decrypt( $encrypted_value, $cipher_method, $enc_key, 0, hex2bin( $enc_iv ) );

					$field_value = $decrypted_value;
				}
				$meta_data[ $form_field_key ] = $field_value;
			}
		}
	}
	return $meta_data;
}
function mapp_mortgage_application_send_application_to_webhooks( $application_id ) {
	// Load the webhooks
	$webhooks = get_front_mortgage_application_option( 'mortgage_application_webhooks', 'mortgage_application_form_network_settings' );
	if ( ! empty( $webhooks ) ) {
		// Convert them to an array.
		$webhooks = explode( "\n", $webhooks );
		$webhooks = array_filter( $webhooks );
		// get application meta data
		$meta_data = mapp_mortgage_application_get_application_meta( $application_id );

		// And now send them to each one.
		foreach ( $webhooks as $url ) {
			// $this->debug( 'Sending lead %s to %s', $lead->meta, $url );
			try {
				wp_remote_post(
					$url,
					array(
						'body'      => (array) $meta_data,
						'sslverify' => false,
						'timeout'   => '30',
					)
				);
			} catch ( Exception $e ) {
				echo wp_kses_post( $e->getMessage() );
			}
		}
		return true;
	} else {
		return __( 'Webhooks are not defined.', '1003-mortgage-application' );
	}
}



						// Handle the AJAX request
function mortgage_application_download_file() {
	$post_id = $_POST['post_id'];

	// verify the mortgate_application_download_file nonce
	// if (!wp_verify_nonce($_POST['mortgate_application_download_file'], 'mortgate_application_download_file')) {
	// wp_send_json_error(array('message' => __('Unable to verify nonce', "mortgage_app")), 400);
	// }

	$current_blog_id = get_current_blog_id();
	$download_source = MAPP_MORTGAGE_APP_BASE_URL . 'inc/templates/mortgage_download_file.php';
	$donwload_limit  = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_ma_submissions_download_limit', 'mortgage_submissions_use_form_network_settings' ) );
	$path            = MAPP_MORTGAGE_APP_BASE_PATH . 'uploads/' . $current_blog_id;
	$file_uploads    = get_post_meta( $post_id, 'file_uploads_date', true );
	$uploadedfiles   = $path . '/' . $file_uploads . 'uploadedfiles.zip';
	$zip             = new ZipArchive();
	if ( $zip->open( $uploadedfiles, ZipArchive::CREATE ) === true ) {
		$zip->extractTo( $path );
		$zip->close();
	}
	$uploaded_files = get_post_meta( $post_id, 'file_uploads_files', true );
	$source_result  = array();
	if ( is_array( $uploaded_files ) && count( $uploaded_files ) > 0 ) {
		for ( $file_loop = 0; $file_loop < count( $uploaded_files ); $file_loop++ ) {
			$file_ext        = substr( $uploaded_files[ $file_loop ], strrpos( $uploaded_files[ $file_loop ], '.' ) + 1 );
			$source_result[] = $path . '/' . $uploaded_files[ $file_loop ];
		}
		$saved_delete_days = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_ma_submissions_deleted_file', 'mortgage_submissions_use_form_network_settings' ) );
		$upload_timestamp  = get_post_meta( $post_id, 'file_uploads_date', true );
		$delete_timestamp  = strtotime( '+' . $saved_delete_days . ' days', $upload_timestamp );
		if ( $delete_timestamp < time() ) {
			wp_send_json_error( array( 'message' => __( 'File download limit has been expired.', '1003-mortgage-application' ) ), 400 );
		}
	}
	write_log( $source_result );

	if ( count( $source_result ) > 0 ) {

		$unling_files  = array();
		$uploads_dir   = MAPP_MORTGAGE_APP_BASE_PATH . 'uploads/' . $current_blog_id;
		$zip_filesname = $uploads_dir . '/' . time() . '_attachedfiles.zip';
		$zip           = new ZipArchive();
		if ( $zip->open( $zip_filesname, ZipArchive::CREATE ) === true ) {
			for (
				$count_zip = 0;
				$count_zip < count( $source_result );
				$count_zip++
			) {
				$zip->addFile( $source_result[ $count_zip ], basename( $source_result[ $count_zip ] ) );
				$unling_files[] = $source_result[ $count_zip ];
			}
			$zip->close();
			chmod( $zip_filesname, 0777 );
		}
		$unling_files[] = $zip_filesname;
		ob_clean();
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . basename( $zip_filesname ) . '"' );
		header( 'Content-Length: ' . filesize( $zip_filesname ) );
		readfile( $zip_filesname );
		flush();
		for ( $count_link = 0; $count_link < count( $unling_files ); $count_link++ ) {
			unlink( $unling_files[ $count_link ] );
		}

		// Exit the script
		exit;
	} else {
		// Handle the error if the zip file couldn't be created
		wp_send_json_error( array( 'message' => __( 'Unable to zip files', '1003-mortgage-application' ) ), 400 );
	}

	exit;
}
						add_action( 'wp_ajax_mortgage_application_download_file', 'mortgage_application_download_file' );
						add_action( 'wp_ajax_nopriv_mortgage_application_download_file', 'mortgage_application_download_file' );
