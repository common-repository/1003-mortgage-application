<?php

/**
 * This file is responsible to application form front view.
 *
 * @link        https://lenderd.com
 * @since       1.0.0
 *
 * @package     mortgage_application
 * @sub-package mortgage_application/inc/templates
 * phpcs:disable WordPress.Security.NonceVerification
 */
// If this file is called directly, abort.
defined( 'ABSPATH' ) or die( 'Access denied!' );
// check user capabilities
if ( ! current_user_can( 'manage_options' ) ) {
	return;
}
$admin_url = is_network_admin()
	? 'network/admin.php?page=ma_setting'
	: 'edit.php?post_type=mortgage_application&page=ma_setting';
$license_screen =
	isset( $_GET['action'] ) && 'license' == $_GET['action'] ? true : false;
$form_screen =
	isset( $_GET['action'] ) &&
	! empty( $_GET['action'] ) &&
	'form' == $_GET['action']
		? true
		: false;
$submission_screen =
	isset( $_GET['action'] ) &&
	! empty( $_GET['action'] ) &&
	'ma_file_upload' == $_GET['action']
		? true
		: false;
?>
<div class="wrap">
	<?php
	if ( is_multisite() ) {
		$terms = get_site_option( 'mortgage_application_admin_terms' );
	} else {
		$terms = get_option( 'mortgage_application_admin_terms' );
	}
	if ( empty( $terms ) && isset( $terms ) ) {
		?>
		<div class="mortgage-application-setting-overlay">
			<div class="overlay-container">
				<h1 class="title"><?php echo esc_html__( 'Terms & Conditions', '1003-mortgage-application' ); ?></h1>
				<div class="content">
					<strong class="u-Block">
					<?php
					echo esc_html__(
						'Communication Consent:',
						'1003-mortgage-application'
					);
					?>
				</strong>
					<p>
					<?php
					echo esc_html__(
						'Read the following Carefully, as use of our plugin implies that you have read and accepted our Terms and Conditions of Use.',
						'1003-mortgage-application'
					);
					?>
	 </p>
					<p>
					<?php
					echo esc_html__(
						'1. You (website owner) agree to only provide this application in a secure (SSL) environment.',
						'1003-mortgage-application'
					);
					?>
	 </p>
					<p>
					<?php
					echo esc_html__(
						'2. You (website owner) are solely responsible for all information collected through your website utilizing this plugin and release any and all liability of 8 Blocks LLC (plugin developer).',
						'1003-mortgage-application'
					);
					?>
	 </p>
					<p>
					<?php
					echo esc_html__(
						'3. You (website owner) agree to not manipulate any form field names to collect any personal financial information from website visitors such as bank account numbers or any tax related information.',
						'1003-mortgage-application'
					);
					?>
	 </p>
				</div>
				<?php
				/* Create Nonce */
				$nonce = wp_create_nonce( 'mortgage_application_admin_terms' );
				?>
				<input type="button" value="I Agree" name="mortgage_application_admin_terms" data-nonce="
				<?php
				echo esc_attr(
					$nonce
				);
				?>
	" id="mortgage_application_admin_terms" />
			</div>
		</div>
		<div class="mortgage-application-setting-fade"></div>
		<?php
	}
	?>
	<div class="mortgage-setting-container">
		<?php
		// check license and show error message
		$license = trim( get_mortgage_application_option( 'ma_license_key' ) );
		$status = get_mortgage_application_option( 'ma_license_key_status' );
		if ( $status === false || $status == 'invalid' || $license === false ) {
			add_settings_error(
				'license-status',
				'ma-license-status',
				wp_kses_post(
					'To upgrade your plugin or get support please visit <a href="https://mortgageapplicationplugin.com">https://mortgageapplicationplugin.com</a>',
					'1003-mortgage-application'
				),
				'error'
			);
			settings_errors( 'license-status' );
		}
		// show success and error message on setting update
		if (
		isset( $_GET['ma-settings-updated'] ) &&
		! empty( $_GET['ma-settings-updated'] )
		) {
			add_settings_error(
				'ma_setting_messages',
				'ma_setting_messages',
				esc_html__( 'Settings Saved', '1003-mortgage-application' ),
				'updated'
			);
			// show error/update messages
			settings_errors( 'ma_setting_messages' );
		}
		?>
		<h1>
			<?php
			// check active dispaly
			if (
			( ! isset( $_GET['action'] ) && empty( $_GET['action'] ) ) ||
			( isset( $_GET['action'] ) &&
			! empty( $_GET['action'] ) &&
			$_GET['action'] === 'general' )
			) {
				$title = 'General Settings';
			} elseif (
			isset( $form_screen ) &&
			! empty( $form_screen ) &&
			$form_screen === true
			) {
				$title = 'Form Settings';
			} /*
			else if(isset($submission_screen) && !empty($submission_screen) && $submission_screen === true)
			{
				$title = 'Submissions Uploaded';
			}*/ elseif (
			isset( $license_screen ) &&
			$license_screen === true
   ) {
	   $title = 'License Options';
}
   // print the title
   echo esc_html( $title );
?>
		</h1>
		<h2 class="nav-tab-wrapper">
			<a href="<?php echo esc_url( admin_url( $admin_url ) ); ?>" class="nav-tab
								<?php
								if (
								! isset( $_GET['action'] ) ||
								( isset( $_GET['action'] ) && 'general' == $_GET['action'] )
								) {
									echo ' nav-tab-active';
								}
								?>
			"><?php esc_html_e( 'General Settings', '1003-mortgage-application' ); ?></a>
			<a href="
			<?php
			echo esc_url(
				add_query_arg( array( 'action' => 'form' ), admin_url( $admin_url ) )
			);
			?>
   " class="nav-tab
								<?php
								if ( isset( $_GET['action'] ) && 'form' == $_GET['action'] ) {
									echo ' nav-tab-active';
								}
								?>
			"><?php esc_html_e( 'Form Settings', '1003-mortgage-application' ); ?></a>
			<?php
			if ( $status !== false && $status == 'valid' && $license !== false ) {
				?>
				<a href="
				<?php
				echo esc_url(
					add_query_arg( array( 'action' => 'ma_file_upload' ), admin_url( $admin_url ) )
				);
				?>
							" class="nav-tab
									<?php
									if ( isset( $_GET['action'] ) && 'ma_file_upload' == $_GET['action'] ) {
										echo ' nav-tab-active';
									}
									?>
				"><?php esc_html_e( 'File Uploader', '1003-mortgage-application' ); ?></a>
				<?php
			}
			if ( ! is_multisite() || ( is_multisite() && is_network_admin() ) ) {
				?>
				<a href="
				<?php
				echo esc_url(
					add_query_arg( array( 'action' => 'license' ), admin_url( $admin_url ) )
				);
				?>
							" class="nav-tab
									<?php
									if ( $license_screen ) {
										echo ' nav-tab-active';
									}
									?>
				"><?php esc_html_e( 'Premium License', '1003-mortgage-application' ); ?></a>
				<?php
			}
			?>
		</h2>
		<form method="post" action="
		<?php
		echo is_network_admin()
		? 'edit.php?action=mapp_mortgage_application_update_network_options'
		: 'options.php';
		?>
		">
												<?php
												// show shortcode info
												printf(
													'<p style="background: #fff; border-left: 4px solid #008ec2; padding: 5px 10px;">%s<strong>%s</strong>%s<strong>%s</strong>%s<strong>[mortgage_application_form]</strong></p>',
													esc_html__( 'Copy this ', '1003-mortgage-application' ),
													esc_html__( 'shortcode ', '1003-mortgage-application' ),
													esc_html__( 'and paste it into your ', '1003-mortgage-application' ),
													esc_html__( 'post, page, or text widget ', '1003-mortgage-application' ),
													esc_html__( 'content: ', '1003-mortgage-application' )
												);
												if ( $status !== false && $status == 'valid' && $license !== false ) {
													printf(
														'<p style="background: #fff; border-left: 4px solid #008ec2; padding: 5px 10px;">%s<strong>%s</strong>%s<strong>%s</strong>%s<strong>[mortgage_application_file_uploads]</strong></p>',
														esc_html__( 'Copy this ', '1003-mortgage-application' ),
														esc_html__( 'shortcode ', '1003-mortgage-application' ),
														esc_html__( 'and paste it into your ', '1003-mortgage-application' ),
														esc_html__( 'post, page, or text widget ', '1003-mortgage-application' ),
														esc_html__( 'content: ', '1003-mortgage-application' )
													);
												}
												if (
												( ! isset( $_GET['action'] ) && empty( $_GET['action'] ) ) ||
												( isset( $_GET['action'] ) &&
												! empty( $_GET['action'] ) &&
												$_GET['action'] === 'general' )
												) {
													// add nonce and page options field
													settings_fields( 'ma_setting' );
													// add custom option field
													do_settings_sections( 'ma_setting' );
													// add submit button
													submit_button( 'Save Settings' );
												} elseif ( isset( $license_screen ) && $license_screen === true ) {
													// show error or success message after export applications
													if ( isset( $_GET['activate-status'] ) ) {
														if (
															sanitize_text_field( $_GET['activate-status'] ) ===
															'error'
														) {
															$message = isset( $_GET['message'] )
																? sanitize_text_field( $_GET['message'] )
																: 'Please check your license key.';
														} else {
															$message = isset( $_GET['message'] )
																? sanitize_text_field( $_GET['message'] )
																: 'Your license key is activated.';
														}
														add_settings_error(
															'activate-status',
															'ma-activate-status',
															$message,
															sanitize_text_field( $_GET['activate-status'] )
														);
														settings_errors( 'activate-status' );
													}
													?>
				<div class="licenses-key-container">
					<label for="licenses_key"><?php esc_html_e( 'Licenses Key', '1003-mortgage-application' ); ?></label>
					<input type="text" name="ma_license_key" id="ma_license_key" class="ma_license_key" value="<?php echo isset( $license ) && $license != '' ? esc_html( $license ) : ''; ?>" />
					<span class="licesnses-status 
													<?php
													echo esc_attr(
														$status !== false && $status == 'valid' ? 'activated' : 'deactivated'
													);
													?>
													">
													<?php echo $status !== false && $status == 'valid' ? esc_html__( 'Activated', '1003-mortgage-application' ) : esc_html__( 'Deactivated', '1003-mortgage-application' );
													?>
		</span>
				</div>
																																									<?php
																																									$other_attributes = array(
																																										'id' => esc_attr(
																																											$status !== false &&
																																											$status == 'valid'
																																											? 'mortgage_app_deactivate'
																																											: 'mortgage_app_active'
																																										),
																																										'data-nonce' =>
																																										$status !== false &&
																																										$status == 'valid'
																																										? wp_create_nonce(
																																											'mortgage_app_deactivate'
																																										)
																																										: wp_create_nonce(
																																											'mortgage_app_activate'
																																										),
																																									);
																																									submit_button(
																																										esc_html(
																																											$status !== false &&
																																											$status == 'valid'
																																											? 'Deactivate'
																																											: 'Activate'
																																										),
																																										'primary ' .
																																										esc_attr(
																																											$status !== false &&
																																											$status == 'valid'
																																											? 'mortgage_app_deactivate'
																																											: 'mortgage_app_active'
																																										),
																																										'mortgage_app_license_button',
																																										true,
																																										$other_attributes
																																									);
																																									// show licenses data
																																									$licenses_obj = new MortgageAppLicenses();
																																									$licenses_obj->licenses_heading();

												} elseif (
												isset( $form_screen ) &&
												! empty( $form_screen ) &&
												$form_screen === true
												) {
													// add nonce and page options field
													settings_fields( 'ma_form_setting' );
													// add custom option field
													do_settings_sections( 'ma_form_setting' );
													echo '<input type="hidden" name="mortgage_application_form_fields[toggle_field] value="true"/>';
													// add submit button
													submit_button( 'Save Settings' );
												} elseif (
												isset( $submission_screen ) &&
												! empty( $submission_screen ) &&
												$submission_screen === true
												) {
													if (
														$status !== false &&
														$status == 'valid' &&
														$license !== false
													) {
														settings_fields( 'ma_submissions_uploaded' );
														do_settings_sections( 'ma_submissions_uploaded' );
														submit_button( 'Save Settings' );
													}
												}
												?>
		</form>
		<script>
			var is_multisite = '<?php echo is_multisite() ? true : false; ?>';
			var is_network_admin = '<?php echo is_network_admin() ? true : false; ?>';
			var use_network_settings = '<?php echo esc_html( $use_network_val ); ?>';
			var use_form_network_settings = '<?php echo esc_html( $use_field_network_val ); ?>';
			var use_submissions_network_settings = '<?php echo esc_html( $use_submissions_network_val ); ?>';

			jQuery(document).ready(function(e) {
				//check site is multisite or network setting use for general setting
				if (is_multisite && !is_network_admin && use_network_settings == '0') {
					jQuery('tr.ma_setting').hide();
				}
				//hide or show click on use network setting button in general setting
				jQuery('input[name="mortgage_application_use_network_settings"]').click(function() {
					if (jQuery(this).is(':checked')) {
						jQuery(this).val('0');
					} else {
						jQuery(this).val('1');
					}
					jQuery('tr.ma_setting').toggle();
				});

				//check site is multisite or network setting use for field setting
				if (is_multisite && !is_network_admin && use_form_network_settings == '0') {
					jQuery('tr.ma_form_setting').hide();
				}
				//hide or show click on use network setting button in field setting
				jQuery('input[name="mortgage_application_use_form_network_settings"]').click(function() {
					if (jQuery(this).is(':checked')) {
						jQuery(this).val('0');
					} else {
						jQuery(this).val('1');
					}
					jQuery('tr.ma_form_setting').toggle();
				});



				//check site is multisite or network setting use for submissions setting
				if (is_multisite && !is_network_admin && use_submissions_network_settings == '0') {
					jQuery('tr.ma_submissions_uploaded').hide();
				}
				//hide or show click on use network setting button in submissions setting
				jQuery('input[name="mortgage_submissions_use_form_network_settings"]').click(function() {

					if (jQuery(this).is(':checked')) {
						jQuery(this).val('0');
					} else {
						jQuery(this).val('1');
					}
					jQuery('tr.ma_submissions_uploaded').toggle();
				});


				jQuery(".download_ma_file").click(function(e) {
					var download_url = jQuery(this).data("url");
					jQuery.ajax({
						type: 'POST', // http method
						url: "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>",
						data: {
							action: 'mortgate_application_download_file',
							"download_url": download_url,
							"security": '<?php echo esc_html( wp_create_nonce( 'mortgate_application_download_file' ) ); ?>
							'
						},
						success: function(result) {

						},
						error: function(errorMessage) {
							if (errorMessage) {
								alert("Error: Please try again!!!");

							}
						}

					});

				});
			});
		</script>
	</div>
</div>