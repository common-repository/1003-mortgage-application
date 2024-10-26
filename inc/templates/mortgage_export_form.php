<?php
/**
 * This file is responsible to application report export.
 *
 * @link        https://lenderd.com
 * @since       1.0.0
 *
 * @package     mortgage_application
 * @sub-package mortgage_application/inc/templates
 */
// If this file is called directly, abort.
defined( 'ABSPATH' ) or die( 'Access denied!' );
// check user capabilities
if ( ! current_user_can( 'manage_options' ) ) {
	return;
}
$license = trim( get_mortgage_application_option( 'ma_license_key' ) );
$status  = get_mortgage_application_option( 'ma_license_key_status' );
if ( $status === false || $status == 'invalid' || $license === false ) {
	add_settings_error( 'license-status', 'ma-license-status', __( 'To upgrade your plugin or get support please visit <a href="https://mortgageapplicationplugin.com">https://mortgageapplicationplugin.com</a>', '1003-mortgage-application' ), 'error' );
	settings_errors( 'license-status' );
}

?>
<div class="wrap export-application">
	<h1 class="title"><?php esc_html_e( 'Export Applications', '1003-mortgage-application' ); ?></h1>
	<?php
	// show error or success message after export applications
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['export-status'] ) && ! empty( $_GET['export-status'] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( sanitize_text_field( wp_unslash( $_GET['export-status'] ) ) === 'error' ) {
			$message = 'Application not found.';
		} else {
			$message = 'Application successfully exported.';
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		add_settings_error( 'export-status', 'ma-export-status', $message, sanitize_text_field( wp_unslash( $_GET['export-status'] ) ) );
		settings_errors( 'export-status' );
	}
	?>
	<div class="container">
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" enctype="multipart/form-data" name="mortgage_application_export_application">
			<div class="field-row specific-radio-application">
				<div>
					<input type="radio"  name="application_type" value="all" checked="checked">
					<label><?php esc_html_e( 'All Applications', '1003-mortgage-application' ); ?></label>
					<input type="radio"  name="application_type" value="100">
					<label><?php esc_html_e( 'Complete Applications', '1003-mortgage-application' ); ?></label>
					<input type="radio"  name="application_type" value="80">
					<label><?php esc_html_e( 'Incomplete Applications', '1003-mortgage-application' ); ?></label>
				</div>
			</div>
			<?php
			/*
			?><div class="field-row specific-application" style="display:none">
				<label class="" for="application">
					<?php esc_html_e( 'Applications', '1003-mortgage-application' ); ?>
				</label>
				<input type="text" name="applications_search" id="applications_search" class="w-100" placeholder="Enter application title minimum two key"/>
				<select name="applications[]" id="applications" class="w-100" multiple="multiple" style="display:none"></select>
			</div><?php */
			?>
			<div class="field-row specific-radio-application">
				<div>
					<input type="radio" class="select_type" name="application_Fields_type" value="all" checked="checked">
					<label><?php esc_html_e( 'Select All Fields', '1003-mortgage-application' ); ?></label>
					<input type="radio" class="select_type" name="application_Fields_type" value="specific">
					<label><?php esc_html_e( 'Select Specific Fields', '1003-mortgage-application' ); ?></label>
				</div>
			</div>
			<div class="field-row specific-application" style="display:none">
				<label class="" for="application">
					<?php esc_html_e( 'Application Fields', '1003-mortgage-application' ); ?>
				</label>
				<input type="text" name="application_field_search" id="application_field_search" class="w-100" placeholder="Enter field name minimum two key"/>
				<select name="application_fields[]" id="application_fields" class="w-100" multiple="multiple" style="display:none"></select>
			</div>
			<div class="field-row condition-select-box">
				<label class="" for="date_range">
					<?php esc_html_e( 'Conditional Logic', '1003-mortgage-application' ); ?>
				</label>
				<div class="ma-field-filter">
					<div class="condition_table">
								<div class="condition_table_fields">
									<select class="arg1" name="key[]">
										<option value="">Select Any field</option>
										<?php foreach ( $mortgage_application_form_fields as $key => $singleMeta ) { ?>
											<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( ucfirst( $singleMeta ) ); ?></option>
										<?php } ?>
									</select>
								</div>
								<div class="condition_table_fields">
									<select name="compare[]" class="condition"><option value="contains"><?php esc_html_e( 'contains', '1003-mortgage-application' ); ?></option><option value="is"><?php esc_html_e( 'is', '1003-mortgage-application' ); ?></option></select>
								</div>
								<div class="condition_table_fields">
									<input type="text" value="" name="value[]" class="arg2">
								</div>
								<div class="condition_table_fields">
									<img class="ma-add-button" src="<?php echo esc_url( MAPP_MORTGAGE_APP_BASE_URL ); ?>assets/img/add.png" alt="Add a condition" title="Add a condition">
									<img class="ma-remove-button" src="<?php echo esc_url( MAPP_MORTGAGE_APP_BASE_URL ); ?>assets/img/remove.png" alt="Remove a condition" title="Remove a condition">
								</div>
					</div>
				</div>
			</div>
			<div class="field-row date_range_class">
				<label class="" for="date_range">
					<?php esc_html_e( 'Select a Date range', '1003-mortgage-application' ); ?>
				</label>
				<?php esc_html_e( 'Start Date', '1003-mortgage-application' ); ?>:<input type="date" name="startDate" class="startDate" />
				<?php esc_html_e( 'End Date', '1003-mortgage-application' ); ?>:<input type="date" name="endDate" class="endDate" />
			</div>
			<div class="field-row export_type_class">
				<label class="" for="export">
					<?php esc_html_e( 'Select Export file type', '1003-mortgage-application' ); ?>
				</label>
				<select name="export_type" id="export">
					<option value="csv"><?php esc_html_e( 'CSV', '1003-mortgage-application' ); ?></option>
					<?php
					if ( $status !== false && $status == 'valid' && $license !== false ) {
						?>
								<option value="fnm"><?php esc_html_e( 'FANNIE 3.2', '1003-mortgage-application' ); ?></option>
								<option value="mismo"><?php esc_html_e( 'Mismo 3.4', '1003-mortgage-application' ); ?></option>
							<?php
					}
					?>
				</select>
			</div>
			<div class="field-row">
				<input type="hidden" name="action" value="mortgage_application_export_applications">
				<?php wp_nonce_field( 'mortgage_application_export_applications', 'mortgage_application_export_nonce' ); ?>
					<input type="submit" id="export_application" name="export_application" class="button-primary" value="<?php esc_html_e( 'Download Export File', '1003-mortgage-application' ); ?>">
			</div>
		</form>
	</div>
</div>
