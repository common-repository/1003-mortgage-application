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
 * phpcs:disable WordPress.Security.NonceVerification
 */
// If this file is called directly, abort.
defined( 'ABSPATH' ) or die( 'Access denied!' );
$app_id = ( isset( $_GET['ma_mode'] ) && ! empty( $_GET['ma_mode'] ) && sanitize_text_field( $_GET['ma_mode'] ) == 'ma_edit' && isset( $_GET['id'] ) && ! empty( $_GET['id'] ) && intval( sanitize_text_field( $_GET['id'] ) ) > 0 ? intval( sanitize_text_field( $_GET['id'] ) ) : 0 );
$data   = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'application_status', true ) : 0 );
?>
<div class="mortgage-form-main-container">
	<?php
	global $mortgage_application_form_fields, $mortgage_application_required_form_fields;
	$options = get_front_mortgage_application_option( 'mortgage_application_form_fields' );
	if ( ! empty( $options ) && is_serialized( $options ) ) {
		$options = unserialize( $options );
	}
	$button_color       = get_front_mortgage_application_option( 'mortgage_application_button_color', 'mortgage_application_use_network_settings' );
	$progress_bar_color = get_front_mortgage_application_option( 'mortgage_application_progress_bar_color', 'mortgage_application_use_network_settings' );
	?>
	<style>body span.bar-score,body .mortgage-Progress-bar span.bar,body .mortgage-form-container .action .button.btn-step-next,body .mortgage-form-container .action .button.btn-step-next:hover,body .mortgage-form-container .action input.submit.button,body .mortgage-form-container fieldset .field .mortgage_sub_field .ui-slider-horizontal span,body .ui-slider-horizontal .ui-slider-range-min,body .mortgage-form-container .action input.submit.button:hover {background:<?php echo sanitize_hex_color( $progress_bar_color ); ?>} body span.bar-score:after {border-color:<?php echo sanitize_hex_color( $progress_bar_color ); ?> transparent transparent transparent} body .mortgage-form-container fieldset .field .mortgage_sub_field .mortgage_button_style,body .mortgage-form-container fieldset .field .mortgage_sub_field .mortgage_sub_field_buttons [type=radio]:checked+label:before,body .mortgage-form-container fieldset .field .mortgage_sub_field .mortgage_sub_field_buttons [type=radio]:checked+label:after {background:<?php echo sanitize_hex_color( $button_color ); ?>}</style>
	<div class="mortgage-Progress-bar">
		<span class="bar" style="width:<?php echo esc_attr( $data ); ?>%"><span class="bar-score" data-score="<?php echo esc_attr( $data ); ?>"><?php echo esc_html( $data ); ?>%</span></span>
	</div>
	<div class="mortgage-form-container">
		<form action="#" method="post" enctype="multipart/form-data" class="mortgage-form" id="myform">
		<?php
		if ( ! empty( $atts['email'] ) ) {
			echo '<input type="hidden" name="dynamic_email" value="' . esc_attr( $atts['email'] ) . '">';
		}
		if ( ( is_array( $options ) && array_key_exists( 'purpose', $options ) ) || in_array( 'purpose', $mortgage_application_required_form_fields ) ) {
			$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_purpose' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_purpose' ) : $mortgage_application_form_fields['purpose'] );
			$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'purpose', true ) : '' );
			?>
			<fieldset class="<?php echo( ! empty( $app_id ) ? '' : 'active' ); ?>" data-progress="10">
				<div class="field">
					<label for="purpose"><?php printf( '%s:', esc_html( $option_label ) ); ?></label>
					<div class="mortgage_sub_field">
						<div class="mortgage_sub_field_buttons">
							<input type="radio" name="purpose" id="purpose_purchase" value="Home Purchase" <?php checked( $data, 'Home Purchase' ); ?> />
							<label for="purpose_purchase" class="mortgage_button_style"><?php esc_html_e( 'Purchase', '1003-mortgage-application' ); ?></label>
						</div>
						<div class="mortgage_sub_field_buttons">
							<input type="radio" name="purpose" id="purpose_refinance" value="Home Refinance" <?php checked( $data, 'Home Refinance' ); ?> />
							<label for="purpose_refinance" class="mortgage_button_style"><?php esc_html_e( 'Refinance', '1003-mortgage-application' ); ?></label>
						</div>
					</div>
				</div>
			</fieldset>
			<?php
		}

		if ( ( is_array( $options ) && array_key_exists( 'home_description', $options ) ) || in_array( 'home_description', $mortgage_application_required_form_fields ) ) {
			$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_home_description' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_home_description' ) : $mortgage_application_form_fields['home_description'] );
			$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'home_description', true ) : '' );
			?>
			<fieldset data-progress="10">
			<div class="field">
				<label for="home_description"><?php printf( '%s:', esc_html( $option_label ) ); ?></label>
				<div class="mortgage_sub_field">

					<div class="mortgage_sub_field_buttons">
					<input type="radio" name="home_description" id="home_description_single_family" class="required" required value="Single Family" <?php checked( $data, 'Single Family' ); ?> />
					<label for="home_description_single_family" class="mortgage_button_style"><?php esc_html_e( 'Single Family', '1003-mortgage-application' ); ?></label>
					</div>
					<div class="mortgage_sub_field_buttons">
					<input type="radio" name="home_description" id="home_description_multi_family" class="required" required value="Multi-Family" <?php checked( $data, 'Multi-Family' ); ?> />
					<label for="home_description_multi_family" class="mortgage_button_style"><?php esc_html_e( 'Multi-Family', '1003-mortgage-application' ); ?></label>
					</div>
					<div class="mortgage_sub_field_buttons">
					<input type="radio" name="home_description" id="home_description_condominium" class="required" required value="Condominium" <?php checked( $data, 'Condominium' ); ?> />
					<label for="home_description_condominium" class="mortgage_button_style"><?php esc_html_e( 'Condominium', '1003-mortgage-application' ); ?></label>
					</div>
					<div class="mortgage_sub_field_buttons">
					<input type="radio" name="home_description" id="home_description_townhouse" class="required" required value="Townhouse" <?php checked( $data, 'Townhouse' ); ?> />
					<label for="home_description_townhouse" class="mortgage_button_style"><?php esc_html_e( 'Townhouse', '1003-mortgage-application' ); ?></label>
					</div>
				</div>
			</div>
			<div class="action">
				<input type="button" class="button btn-step-prev" value="<?php esc_html_e( '<< Back', '1003-mortgage-application' ); ?>" />
			</div>
		</fieldset>
			<?php
		}

		if ( ( is_array( $options ) && array_key_exists( 'credit_rating', $options ) ) || in_array( 'credit_rating', $mortgage_application_required_form_fields ) ) {
			$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_credit_rating' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_credit_rating' ) : $mortgage_application_form_fields['credit_rating'] );
			$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'credit_rating', true ) : '' );
			?>
			<fieldset data-progress="10">
				<div class="field">
				<label for="credit_rating"><?php printf( '%s:', esc_html( $option_label ) ); ?></label>
				<div class="mortgage_sub_field scrollbar_class">

				<div class="mortgage_sub_field_buttons">
				<input type="radio" name="credit_rating" id="credit_rating_excellent" class="required" required value="Excellent" <?php checked( $data, 'Excellent' ); ?> />
				<label for="credit_rating_excellent" class="mortgage_button_style"><?php esc_html_e( 'Excellent (720 and above)', '1003-mortgage-application' ); ?></label>
				</div>
				<div class="mortgage_sub_field_buttons">
				<input type="radio" name="credit_rating" id="credit_rating_good" class="required" required value="Good" <?php checked( $data, 'Good' ); ?> />
				<label for="credit_rating_good" class="mortgage_button_style"><?php esc_html_e( 'Good (660 – 719)', '1003-mortgage-application' ); ?></label>
				</div>
				<div class="mortgage_sub_field_buttons">
				<input type="radio" name="credit_rating" id="credit_rating_average" class="required" required value="Average" <?php checked( $data, 'Average' ); ?> />
				<label for="credit_rating_average" class="mortgage_button_style"><?php esc_html_e( 'Average (620 – 659)', '1003-mortgage-application' ); ?></label>
				</div>
				<div class="mortgage_sub_field_buttons">
				<input type="radio" name="credit_rating" id="credit_rating_below_average" class="required" required value="Below Average" <?php checked( $data, 'Below Average' ); ?> />
				<label for="credit_rating_below_average" class="mortgage_button_style"><?php esc_html_e( 'Below Average (580 – 619)', '1003-mortgage-application' ); ?></label>
				</div>
				<div class="mortgage_sub_field_buttons">
				<input type="radio" name="credit_rating" id="credit_rating_poor" class="required" required value="Poor" <?php checked( $data, 'Poor' ); ?> />
				<label for="credit_rating_poor" class="mortgage_button_style"><?php esc_html_e( 'Poor (579 and below)', '1003-mortgage-application' ); ?></label>
				</div>

				</div>
				</div>
				<div class="action">
					<input type="button" class="button btn-step-prev" value="<?php esc_html_e( '<< Back', '1003-mortgage-application' ); ?>" />
				</div>
			</fieldset>
			<?php
		}

		if ( ( is_array( $options ) && array_key_exists( 'property_use', $options ) ) || in_array( 'property_use', $mortgage_application_required_form_fields ) ) {
			$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_property_use' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_property_use' ) : $mortgage_application_form_fields['property_use'] );
			$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'property_use', true ) : '' );
			?>
			<fieldset data-progress="10">
				<div class="field">
				<label for="property_use"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
				<div class="mortgage_sub_field">
				<div class="mortgage_sub_field_buttons">
				<input type="radio" name="property_use" id="property_use_primary_residence" class="required" required value="Primary Residence" <?php checked( $data, 'Primary Residence' ); ?> />
				<label for="property_use_primary_residence" class="mortgage_button_style"><?php esc_html_e( 'Primary Residence', '1003-mortgage-application' ); ?></label>
				</div>
				<div class="mortgage_sub_field_buttons">
				<input type="radio" name="property_use" id="property_use_secondary_home" class="required" required value="Secondary Home" <?php checked( $data, 'Secondary Home' ); ?> />
				<label for="property_use_secondary_home" class="mortgage_button_style"><?php esc_html_e( 'Secondary Home', '1003-mortgage-application' ); ?></label>
				</div>
				<div class="mortgage_sub_field_buttons">
				<input type="radio" name="property_use" id="property_use_investment_property" class="required" required value="Investment Property" <?php checked( $data, 'Investment Property' ); ?> />
				<label for="property_use_investment_property" class="mortgage_button_style"><?php esc_html_e( 'Investment Property', '1003-mortgage-application' ); ?></label>
				</div>
				</div>
				</div>
				<div class="action">
				<input type="button" class="button btn-step-prev" value="<?php esc_html_e( '<< Back', '1003-mortgage-application' ); ?>" />
				</div>
			</fieldset>
			<?php
		}

		if ( ( is_array( $options ) && array_key_exists( 'zip_code', $options ) ) || in_array( 'zip_code', $mortgage_application_required_form_fields ) ) {
			$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_zip_code' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_zip_code' ) : $mortgage_application_form_fields['zip_code'] );
			$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'zip_code', true ) : '' );
			?>
			<fieldset data-progress="15">
				<div class="field">
				<label for="zip_code"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
				<div class="mortgage_sub_field">
					<input type="text" name="zip_code" id="zip_code" value="<?php echo esc_attr( $data ); ?>" class="required" required/>

					<input type="hidden" name="address" id="address" value="<?php echo esc_attr( $data ); ?>" />
					<input type="hidden" name="city" id="city" value="<?php echo esc_attr( $data ); ?>" />
					<input type="hidden" name="zip_code_only" id="zip_code_only" value="<?php echo esc_attr( $data ); ?>" />
					<input type="hidden" name="state" id="state" value="<?php echo esc_attr( $data ); ?>" />
					<p class="error-zip-message"></p>
				</div>
				</div>
				<div class="action">
				<input type="button" class="button btn-step-next" value="<?php esc_html_e( 'Continue >>', '1003-mortgage-application' ); ?>"/>
				<input type="button" class="button btn-step-prev" value="<?php esc_html_e( '<< Back', '1003-mortgage-application' ); ?>" />
				</div>
			</fieldset>
			<?php
		}

		?>
			<fieldset data-progress="15">
			<?php
			/*******************refinance field:start******************/
			if ( ( is_array( $options ) && array_key_exists( 'purchase_year', $options ) ) || in_array( 'purchase_year', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_purchase_year' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_purchase_year' ) : $mortgage_application_form_fields['purchase_year'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'purchase_year', true ) : '' );
				?>
			<div class="field purpose-refi">
			<label for="purchase_year"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
			<div class="mortgage_sub_field">
				<div class="purchase_year_display"></div>
					<input type="text" name="purchase_year" id="purchase_year">
					<div id="purchase_year_range"></div>
					<?php
					$purchase_year_values = array();
					$current_year         = gmdate( 'Y' );
					for ( $current_year = ( gmdate( 'Y' ) - 1 ); $current_year >= 1957; $current_year-- ) {
							$purchase_year_values[] = $current_year;
					}
					?>
			</div>
			</div>
				<?php
			}
			/*******************refinance field:end*/
			if ( ( is_array( $options ) && array_key_exists( 'zip_code', $options ) ) || in_array( 'zip_code', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_zip_code' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_zip_code' ) : $mortgage_application_form_fields['zip_code'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'zip_code', true ) : '' );
				?>
				<div class="field">
					<label for="zip_code"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
					<div class="mortgage_sub_field">
						<span class="zip_code"><?php echo esc_html( $data ); ?></span>
					</div>
				</div>
				<?php
			}


			/*******************purchase field:start*/
			if ( ( is_array( $options ) && array_key_exists( 'first_time_buyer', $options ) ) || in_array( 'first_time_buyer', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_first_time_buyer' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_first_time_buyer' ) : $mortgage_application_form_fields['first_time_buyer'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'first_time_buyer', true ) : '' );
				?>
				<div class="field purpose-purch">
				<label for="first_time_buyer"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
				<div class="mortgage_sub_field">
					<div class="mortgage_sub_field_buttons">
					<input type="radio" name="first_time_buyer" id="first_time_buyer_yes" class="required" required value="Yes" <?php echo checked( $data, 'Yes', false ); ?>>
					<label for="first_time_buyer_yes" class="mortgage_button_style">
						<?php esc_html_e( 'Yes', '1003-mortgage-application' ); ?>
					</label>
					</div>
					<div class="mortgage_sub_field_buttons">
					<input type="radio" name="first_time_buyer" id="first_time_buyer_no" value="No" class="required" required <?php echo( ! empty( $data ) ? checked( $data, 'No', false ) : 'checked="checked"' ); ?>>
					<label for="first_time_buyer_no" class="mortgage_button_style">
							<?php esc_html_e( 'No', '1003-mortgage-application' ); ?>
					</label>
					</div>
				</div>
				</div>
				<?php
			}
			if ( ( is_array( $options ) && array_key_exists( 'loan_purpose_purchase', $options ) ) || in_array( 'loan_purpose_purchase', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_loan_purpose_purchase' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_loan_purpose_purchase' ) : $mortgage_application_form_fields['loan_purpose_purchase'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'loan_purpose_purchase', true ) : '' );
				?>
			<div class="field purpose-purch">
			<label for="loan_purpose_purchase"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
			<div class="mortgage_sub_field scrollbar_class">
					<div class="mortgage_sub_field_buttons">
				<input type="radio" name="loan_purpose_purchase" id="loan_purpose_purchase_isapa" class="required" required value="Immediately: Signed a Purchase Agreement" <?php echo checked( $data, 'Immediately: Signed a Purchase Agreement', false ); ?> />
				<label for="loan_purpose_purchase_isapa" class="mortgage_button_style"><?php esc_html_e( 'Immediately: Signed a Purchase Agreement', '1003-mortgage-application' ); ?></label>
				</div>
					<div class="mortgage_sub_field_buttons">
				<input type="radio" name="loan_purpose_purchase" id="loan_purpose_purchase_afahop" class="required" required value="ASAP: Found a House/Offer Pending" <?php echo checked( $data, 'ASAP: Found a House/Offer Pending', false ); ?> />
				<label for="loan_purpose_purchase_afahop" class="mortgage_button_style"><?php esc_html_e( 'ASAP: Found a House/Offer Pending', '1003-mortgage-application' ); ?></label>
				</div>
					<div class="mortgage_sub_field_buttons">
				<input type="radio" name="loan_purpose_purchase" id="loan_purpose_purchase_w3d" class="required" required value="Within 30 Days" <?php echo checked( $data, 'Within 30 Days', false ); ?> />
				<label for="loan_purpose_purchase_w3d" class="mortgage_button_style"><?php esc_html_e( 'Within 30 Days', '1003-mortgage-application' ); ?></label>
				</div>
					<div class="mortgage_sub_field_buttons">
				<input type="radio" name="loan_purpose_purchase" id="loan_purpose_purchase_23m" class="required" required value="2 - 3 Months" <?php echo checked( $data, '2 - 3 Months', false ); ?> />
				<label for="loan_purpose_purchase_23m" class="mortgage_button_style"><?php esc_html_e( '2 - 3 Months', '1003-mortgage-application' ); ?></label>
				</div>
					<div class="mortgage_sub_field_buttons">
				<input type="radio" name="loan_purpose_purchase" id="loan_purpose_purchase_36m" class="required" required value="3 - 6 Months" <?php echo checked( $data, '3 - 6 Months', false ); ?> />
				<label for="loan_purpose_purchase_36m" class="mortgage_button_style"><?php esc_html_e( '3 - 6 Months', '1003-mortgage-application' ); ?></label>
				</div>
					<div class="mortgage_sub_field_buttons">
				<input type="radio" name="loan_purpose_purchase" id="loan_purpose_purchase_6mp" class="required" required value="6+ Months" <?php echo checked( $data, '6+ Months', false ); ?> />
				<label for="loan_purpose_purchase_6mp" class="mortgage_button_style"><?php esc_html_e( '6+ Months', '1003-mortgage-application' ); ?></label>
				</div>
					<div class="mortgage_sub_field_buttons">
				<input type="radio" name="loan_purpose_purchase" id="loan_purpose_purchase_ntfiaro" class="required" required value="No Time Frame; I am Researching Options" <?php echo checked( $data, 'No Time Frame; I am Researching Options', false ); ?> />
				<label for="loan_purpose_purchase_ntfiaro" class="mortgage_button_style"><?php esc_html_e( 'No Time Frame; I am Researching Options', '1003-mortgage-application' ); ?></label>
				</div>

			</div>
			</div>
				<?php
			}

			if ( ( is_array( $options ) && array_key_exists( 'purchase_price', $options ) ) || in_array( 'purchase_price', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_purchase_price' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_purchase_price' ) : $mortgage_application_form_fields['purchase_price'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'purchase_price', true ) : '' );
				?>
				<div class="field purpose-purch">
				<label for="purchase_price"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
				<div class="mortgage_sub_field">
					<div class="purchase_price_display"></div>
					<input type="hidden" name="purchase_price" id="purchase_price" value="<?php echo ( isset( $data ) && '' !== $data ) ? esc_attr( $data ) : ''; ?>">
					<div id="purchase_price_range"></div>
					<?php
						$home_purchase_price_text = array();
					$home_purchase_price_values   = array();
					for ( $start_home_value = 50000; $start_home_value <= 7000000; ) {
						if ( $start_home_value == 50000 && is_numeric( $start_home_value ) ) {
							$home_purchase_price_text[]   = '$' . number_format( $start_home_value ) . ' - ' . '$' . number_format( ( $start_home_value + 10000 ) );
							$home_purchase_price_values[] = $start_home_value;
							$start_home_value             = ( $start_home_value + 10000 );
						} elseif ( $start_home_value > 50000 && $start_home_value < 400000 ) {
							$home_purchase_price_text[]   = '$' . number_format( ( $start_home_value + 1 ) ) . ' - ' . '$' . number_format( ( $start_home_value + 10000 ) );
							$home_purchase_price_values[] = ( $start_home_value + 1 );
							$start_home_value             = ( $start_home_value + 10000 );
						} elseif ( $start_home_value >= 400000 && $start_home_value < 1000000 ) {
							$home_purchase_price_text[]   = '$' . number_format( ( $start_home_value + 1 ) ) . ' - ' . '$' . number_format( ( $start_home_value + 50000 ) );
							$home_purchase_price_values[] = ( $start_home_value + 1 );
							$start_home_value             = ( $start_home_value + 50000 );
						} elseif ( $start_home_value >= 400000 && $start_home_value < 7000000 ) {
							$home_purchase_price_text[]   = '$' . number_format( ( $start_home_value + 1 ) ) . ' - ' . '$' . number_format( ( $start_home_value + 200000 ) );
							$home_purchase_price_values[] = ( $start_home_value + 1 );
							$start_home_value             = ( $start_home_value + 200000 );
						} else {
							$home_purchase_price_text[]   = '$' . number_format( ( $start_home_value + 1 ) ) . ' + ';
							$home_purchase_price_values[] = ( $start_home_value + 1 );
							$start_home_value             = ( $start_home_value + 200000 );
						}
					}
					?>
				</div>
				</div>
				<?php
			}
			if ( ( is_array( $options ) && array_key_exists( 'down_payment', $options ) ) || in_array( 'down_payment', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_down_payment' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_down_payment' ) : $mortgage_application_form_fields['down_payment'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'down_payment', true ) : '' );
				?>
			<div class="field purpose-purch">
			<label for="down_payment"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
			<div class="mortgage_sub_field">
				<div class="down_payment_display"></div>
				<input type="hidden" name="down_payment" id="down_payment" value="<?php echo ( isset( $data ) && '' !== $data ) ? esc_attr( $data ) : ''; ?>">
				<div id="down_payment_range"></div>

					<?php
						$down_payment_price_text = array();
					$down_payment_price_values   = array();
					for ( $start_balance = 0; $start_balance <= 4000000; ) {
						if ( $start_balance == 0 && is_numeric( $start_home_value ) ) {
							$down_payment_price_text[]   = '$' . number_format( ( $start_balance + 1000 ) ) . ' - ' . '$' . number_format( ( $start_balance + 10000 ) );
							$down_payment_price_values[] = ( $start_balance + 10000 );
							$start_balance               = ( $start_balance + 10000 );
						} elseif ( $start_balance < 250000 ) {
							$down_payment_price_text[]   = '$' . number_format( ( $start_balance + 1 ) ) . ' - ' . '$' . number_format( ( $start_balance + 10000 ) );
							$down_payment_price_values[] = ( $start_balance + 10000 );
							$start_balance               = ( $start_balance + 10000 );
						} elseif ( $start_balance < 300000 ) {
							$down_payment_price_text[]   = '$' . number_format( ( $start_balance + 1 ) ) . ' - ' . '$' . number_format( ( $start_balance + 50000 ) );
							$down_payment_price_values[] = ( $start_balance + 50000 );
							$start_balance               = ( $start_balance + 50000 );
						} elseif ( $start_balance < 1000000 ) {
							$down_payment_price_text[]   = '$' . number_format( ( $start_balance + 1 ) ) . ' - ' . '$' . number_format( ( $start_balance + 100000 ) );
							$down_payment_price_values[] = ( $start_balance + 100000 );
							$start_balance               = ( $start_balance + 100000 );
						} elseif ( $start_balance <= 3800000 ) {
							$down_payment_price_text[]   = '$' . number_format( ( $start_balance + 1 ) ) . ' - ' . '$' . number_format( ( $start_balance + 200000 ) );
							$down_payment_price_values[] = ( $start_balance + 200000 );
							$start_balance               = ( $start_balance + 200000 );
						} else {
							$down_payment_price_text[]   = '$' . number_format( ( $start_balance + 1 ) ) . '+';
							$down_payment_price_values[] = ( $start_balance + 1 );
							$start_balance               = ( $start_balance + 1 );
						}
					}
					?>

			</div>
			</div>
				<?php
			}
			if ( ( is_array( $options ) && array_key_exists( 'desired_rate_type', $options ) ) || in_array( 'desired_rate_type', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_desired_rate_type' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_desired_rate_type' ) : $mortgage_application_form_fields['desired_rate_type'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'desired_rate_type', true ) : '' );
				?>
			<div class="field purpose-purch">
			<label for="desired_rate_type"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
			<div class="mortgage_sub_field">
				<div class="mortgage_sub_field_buttons">
				<input type="radio" name="desired_rate_type" id="desired_rate_type_fixed" value="Fixed" class="required" required <?php echo( ! empty( $data ) ? checked( $data, 'Fixed', false ) : 'checked="checked"' ); ?>>
				<label for="desired_rate_type_fixed" class="mortgage_button_style">
					<?php esc_html_e( 'Fixed', '1003-mortgage-application' ); ?>
				</label>
				</div>
				<div class="mortgage_sub_field_buttons">
				<input type="radio" name="desired_rate_type" id="desired_rate_type_arm" value="Adjustable" class="required" required <?php echo checked( $data, 'Adjustable', false ); ?>>
				<label for="desired_rate_type_arm" class="mortgage_button_style">
						<?php esc_html_e( 'Adjustable', '1003-mortgage-application' ); ?>
				</label>
				</div>
			</div>
			</div>
				<?php
			}
			/*******************purchase field:end*/

			/*******************refinance field:start*/
			if ( ( is_array( $options ) && array_key_exists( 'home_value', $options ) ) || in_array( 'home_value', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_home_value' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_home_value' ) : $mortgage_application_form_fields['home_value'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'home_value', true ) : '' );
				?>
			<div class="field purpose-refi">
				<label for="home_value"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
				<div class="mortgage_sub_field">
					<div class="home_value_display"></div>
					<input type="hidden" name="home_value" id="home_value" value="<?php echo ( isset( $data ) && '' !== $data ) ? esc_attr( $data ) : ''; ?>">
					<div id="home_value_range"></div>

						<?php
						$home_value_price_text   = array();
						$home_value_price_values = array();
						for ( $start_home_value = 50000; $start_home_value <= 7000000; ) {
							if ( $start_home_value == 50000 && is_numeric( $start_home_value ) ) {
								$home_value_price_text[]   = '$' . number_format( $start_home_value ) . ' - ' . '$' . number_format( ( $start_home_value + 10000 ) );
								$home_value_price_values[] = $start_home_value;
								$start_home_value          = ( $start_home_value + 10000 );
							} elseif ( $start_home_value > 50000 && $start_home_value < 400000 ) {
								$home_value_price_text[]   = '$' . number_format( ( $start_home_value + 1 ) ) . ' - ' . '$' . number_format( ( $start_home_value + 10000 ) );
								$home_value_price_values[] = ( $start_home_value + 1 );
								$start_home_value          = ( $start_home_value + 10000 );
							} elseif ( $start_home_value >= 400000 && $start_home_value < 1000000 ) {
								$home_value_price_text[]   = '$' . number_format( ( $start_home_value + 1 ) ) . ' - ' . '$' . number_format( ( $start_home_value + 50000 ) );
								$home_value_price_values[] = ( $start_home_value + 1 );
								$start_home_value          = ( $start_home_value + 50000 );
							} elseif ( $start_home_value >= 400000 && $start_home_value < 7000000 ) {
								$home_value_price_text[]   = '$' . number_format( ( $start_home_value + 1 ) ) . ' - ' . '$' . number_format( ( $start_home_value + 200000 ) );
								$home_value_price_values[] = ( $start_home_value + 1 );
								$start_home_value          = ( $start_home_value + 200000 );
							} else {
								$home_value_price_text[]   = '$' . number_format( ( $start_home_value + 1 ) ) . ' + ';
								$home_value_price_values[] = ( $start_home_value + 1 );
								$start_home_value          = ( $start_home_value + 200000 );
							}
						}
						?>
				</div>
				</div>
				<?php
			}
			if ( ( is_array( $options ) && array_key_exists( 'mortgage_balance', $options ) ) || in_array( 'mortgage_balance', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_mortgage_balance' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_mortgage_balance' ) : $mortgage_application_form_fields['mortgage_balance'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'mortgage_balance', true ) : '' );
				?>
			<div class="field purpose-refi">
			<label for="mortgage_balance"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
			<div class="mortgage_sub_field">
				<div class="mortgage_balance_display"></div>
				<input type="hidden" name="mortgage_balance" id="mortgage_balance" value="<?php echo ( isset( $data ) && '' !== $data ) ? esc_attr( $data ) : ''; ?>">
				<div id="mortgage_balance_range"></div>

					<?php
						$mortgage_balance_price_text = array();
					$mortgage_balance_price_values   = array();
					$mortgage_balance_price_text[]   = "I don't have a mortgage";
					$mortgage_balance_price_values[] = 0;

					$mortgage_balance_price_text[]   = '$0';
					$mortgage_balance_price_values[] = 0;

					for ( $start_balance = 0; $start_balance < 440000; ) {
						if ( $start_balance < 200000 ) {
							$mortgage_balance_price_text[]   = '$' . number_format( ( $start_balance + 1 ) ) . ' - ' . '$' . number_format( ( $start_balance + 5000 ) );
							$mortgage_balance_price_values[] = ( $start_balance + 1 );
							$start_balance                   = ( $start_balance + 5000 );
						} elseif ( $start_balance < 400000 ) {
							$mortgage_balance_price_text[]   = '$' . number_format( ( $start_balance + 1 ) ) . ' - ' . '$' . number_format( ( $start_balance + 10000 ) );
							$mortgage_balance_price_values[] = ( $start_balance + 1 );
							$start_balance                   = ( $start_balance + 10000 );
						} else {
							$mortgage_balance_price_text[]   = '$' . number_format( ( $start_balance + 1 ) ) . ' - ' . '$' . number_format( ( $start_balance + 20000 ) );
							$mortgage_balance_price_values[] = ( $start_balance + 1 );
							$start_balance                   = ( $start_balance + 20000 );
						}
					}
					?>

			</div>
			</div>
				<?php
			}
			if ( ( is_array( $options ) && array_key_exists( 'loan_interest_rate', $options ) ) || in_array( 'loan_interest_rate', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_loan_interest_rate' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_loan_interest_rate' ) : $mortgage_application_form_fields['loan_interest_rate'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'loan_interest_rate', true ) : '' );
				?>
			<div class="field purpose-refi">
			<label for="loan_interest_rate"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
			<div class="mortgage_sub_field">
				<div class="loan_interest_rate_display"></div>
				<input type="hidden" name="loan_interest_rate" id="loan_interest_rate" value="<?php echo ( isset( $data ) && '' !== $data ) ? esc_attr( $data ) : ''; ?>">
				<div id="loan_interest_rate_range"></div>

					<?php
					$loan_interest_rate_text     = array();
					$loan_interest_rate_values   = array();
					$loan_interest_rate_text[]   = "I don't have a mortgage";
					$loan_interest_rate_values[] = 0;
					$loan_interest_rate_text[]   = '11+';
					$loan_interest_rate_values[] = 11;
					for ( $interest_rate = 10.75; $interest_rate >= 2; $interest_rate = ( $interest_rate - 0.25 ) ) {
							$loan_interest_rate_text[]   = number_format( $interest_rate, 2 );
							$loan_interest_rate_values[] = $interest_rate;
					}
					?>
			</div>
			</div>
				<?php
			}
			if ( ( is_array( $options ) && array_key_exists( 'loan_vendor', $options ) ) || in_array( 'loan_vendor', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_loan_vendor' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_loan_vendor' ) : $mortgage_application_form_fields['loan_vendor'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'loan_vendor', true ) : '' );
				?>
			<div class="field purpose-refi">
			<label for="loan_vendor"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
			<div class="mortgage_sub_field scrollbar_class">

				<div class="mortgage_sub_field_buttons">
				<input type="radio" name="loan_vendor" id="loan_vendor_idnham" class="required" required value="I do not have a mortgage" <?php echo checked( $data, 'I do not have a mortgage', false ); ?> />
				<label for="loan_vendor_idnham" class="mortgage_button_style"><?php esc_html_e( "I don't have a mortgage", '1003-mortgage-application' ); ?></label>
				</div>
				<div class="mortgage_sub_field_buttons">
				<input type="radio" name="loan_vendor" id="loan_vendor_boa" class="required" required value="Bank of America" <?php echo checked( $data, 'Bank of America', false ); ?> />
				<label for="loan_vendor_boa" class="mortgage_button_style"><?php esc_html_e( 'Bank of America', '1003-mortgage-application' ); ?></label>
				</div>
				<div class="mortgage_sub_field_buttons">
				<input type="radio" name="loan_vendor" id="loan_vendor_chase" class="required" required value="Chase" <?php echo checked( $data, 'Chase', false ); ?> />
				<label for="loan_vendor_chase" class="mortgage_button_style"><?php esc_html_e( 'Chase', '1003-mortgage-application' ); ?></label>
				</div>
				<div class="mortgage_sub_field_buttons">
				<input type="radio" name="loan_vendor" id="loan_vendor_citibank" class="required" required value="Citibank" <?php echo checked( $data, 'Citibank', false ); ?> />
				<label for="loan_vendor_citibank" class="mortgage_button_style"><?php esc_html_e( 'Citibank', '1003-mortgage-application' ); ?></label>
				</div>
				<div class="mortgage_sub_field_buttons">
				<input type="radio" name="loan_vendor" id="loan_vendor_quicken_loans" class="required" required value="Quicken Loans" <?php echo checked( $data, 'Quicken Loans', false ); ?> />
				<label for="loan_vendor_quicken_loans" class="mortgage_button_style"><?php esc_html_e( 'Quicken Loans', '1003-mortgage-application' ); ?></label>
				</div>
				<div class="mortgage_sub_field_buttons">
				<input type="radio" name="loan_vendor" id="loan_vendor_wells_fargo" class="required" required value="Wells Fargo" <?php echo checked( $data, 'Wells Fargo', false ); ?> />
				<label for="loan_vendor_wells_fargo" class="mortgage_button_style"><?php esc_html_e( 'Wells Fargo', '1003-mortgage-application' ); ?></label>
				</div>
				<div class="mortgage_sub_field_buttons">
				<input type="radio" name="loan_vendor" id="loan_vendor_other" class="required" required value="Other" <?php echo checked( $data, 'Other', false ); ?> />
				<label for="loan_vendor_other" class="mortgage_button_style"><?php esc_html_e( 'Other', '1003-mortgage-application' ); ?></label>
				</div>

			</div>
			</div>
				<?php
			}
			if ( ( is_array( $options ) && array_key_exists( 'rate_type', $options ) ) || in_array( 'rate_type', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_rate_type' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_rate_type' ) : $mortgage_application_form_fields['rate_type'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'rate_type', true ) : '' );
				?>
			<div class="field purpose-refi">
			<label for="rate_type"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
			<div class="mortgage_sub_field">
				<div class="mortgage_sub_field_buttons">
				<input type="radio" name="rate_type" id="rate_type_fixed" value="Fixed" class="required" required <?php echo( ! empty( $data ) ? checked( $data, 'Fixed', false ) : 'checked="checked"' ); ?>>
				<label for="rate_type_fixed" class="mortgage_button_style">
					<?php esc_html_e( 'Fixed', '1003-mortgage-application' ); ?>
				</label>
				</div>
				<div class="mortgage_sub_field_buttons">
				<input type="radio" name="rate_type" id="rate_type_arm" value="Adjustable" class="required" required <?php echo checked( $data, 'Adjustable', false ); ?>>
				<label for="rate_type_arm" class="mortgage_button_style">
						<?php esc_html_e( 'Adjustable', '1003-mortgage-application' ); ?>
				</label>
				</div>
			</div>
			</div>
				<?php
			}
			if ( ( is_array( $options ) && array_key_exists( 'second_mortgage', $options ) ) || in_array( 'second_mortgage', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_second_mortgage' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_second_mortgage' ) : $mortgage_application_form_fields['second_mortgage'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'second_mortgage', true ) : '' );
				?>
						<div class="field purpose-refi">
						<label for="second_mortgage"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
						<div class="mortgage_sub_field">
							<div class="mortgage_sub_field_buttons">
							<input type="radio" name="second_mortgage" id="second_mortgage_yes" value="Yes" class="required" required <?php echo( ! empty( $data ) ? checked( $data, 'Yes', false ) : 'checked="checked"' ); ?>>
							<label for="second_mortgage_yes" class="mortgage_button_style">
									<?php esc_html_e( 'Yes', '1003-mortgage-application' ); ?>
							</label>
							</div>
							<div class="mortgage_sub_field_buttons">
							<input type="radio" name="second_mortgage" id="second_mortgage_no" value="No" class="required" required <?php echo checked( $data, 'No', false ); ?>>
							<label for="second_mortgage_no" class="mortgage_button_style">
									<?php esc_html_e( 'No', '1003-mortgage-application' ); ?>
							</label>
							</div>
						</div>
						</div>


				<?php
			}
			if ( ( is_array( $options ) && array_key_exists( 'additional_funds', $options ) ) || in_array( 'additional_funds', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_additional_funds' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_additional_funds' ) : $mortgage_application_form_fields['additional_funds'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'additional_funds', true ) : '' );
				?>
						<div class="field purpose-refi">
						<label for="additional_funds"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
						<div class="mortgage_sub_field">
							<div class="additional_funds_display"></div>
							<input type="text" name="additional_funds" id="additional_funds" value="<?php echo ( isset( $data ) && '' !== $data) ? esc_attr( $data ) : ''; ?>">
							<div id="additional_funds_range"></div>

									<?php
									$additional_funds_text   = array();
									$additional_funds_values = array();

									$additional_funds_text[]   = '0';
									$additional_funds_values[] = 0;
									$additional_funds_text[]   = '$2,000 - $5,000';
									$additional_funds_values[] = 5000;
									$additional_funds_text[]   = '$5,001 - $10,000';
									$additional_funds_values[] = 10000;
									$additional_funds_text[]   = '$10,001 - $15,000';
									$additional_funds_values[] = 15000;
									$additional_funds_text[]   = '$15,001 - $20,000';
									$additional_funds_values[] = 20000;
									$additional_funds_text[]   = '$20,001 - $30,000';
									$additional_funds_values[] = 30000;

									for ( $cash_borrow = 30000; $cash_borrow < 3000000; ) {
										if ( $cash_borrow == 30000 && is_numeric( $cash_borrow ) ) {
											$additional_funds_text[]   = '$' . number_format( $cash_borrow ) . ' - ' . '$' . number_format( ( $cash_borrow + 10000 ) );
											$additional_funds_values[] = ( $cash_borrow + 10000 );
											$cash_borrow               = ( $cash_borrow + 10000 );
										} elseif ( $cash_borrow > 30000 && $cash_borrow < 400000 ) {
											$additional_funds_text[]   = '$' . number_format( ( $cash_borrow + 1 ) ) . ' - ' . '$' . number_format( ( $cash_borrow + 10000 ) );
											$additional_funds_values[] = ( $cash_borrow + 10000 );
											$cash_borrow               = ( $cash_borrow + 10000 );
										} elseif ( $cash_borrow >= 400000 && $cash_borrow < 1000000 ) {
											$additional_funds_text[]   = '$' . number_format( ( $cash_borrow + 1 ) ) . ' - ' . '$' . number_format( ( $cash_borrow + 50000 ) );
											$additional_funds_values[] = ( $cash_borrow + 50000 );
											$cash_borrow               = ( $cash_borrow + 50000 );
										} else {
											$additional_funds_text[]   = '$' . number_format( ( $cash_borrow + 1 ) ) . ' - ' . '$' . number_format( ( $cash_borrow + 200000 ) );
											$additional_funds_values[] = ( $cash_borrow + 200000 );
											$cash_borrow               = ( $cash_borrow + 200000 );
										}
									}
									?>
						</div>
						</div>
				<?php
			}
			/*******************refinance field:end*/
			?>
			<div class="action">
				<input type="button" class="button btn-step-next" value="<?php esc_html_e( 'Continue >>', '1003-mortgage-application' ); ?>"/>
				<input type="button" class="button btn-step-prev" value="<?php esc_html_e( '<< Back', '1003-mortgage-application' ); ?>" />
			</div>
			</fieldset>
			<fieldset data-progress="10">
			<?php
				/*****************refinance field:start********************/
			if ( ( is_array( $options ) && array_key_exists( 'age', $options ) ) || in_array( 'age', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_age' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_age' ) : $mortgage_application_form_fields['age'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'age', true ) : '' );
				?>
				<div class="field purpose-refi">
				<label for="age"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
				<div class="mortgage_sub_field">
					<div class="age_display"></div>
					<input type="hidden" name="age" id="age" value="<?php echo ( isset( $data ) && '' !== $data ) ? esc_attr( $data ) : ''; ?>">
					<div id="age_range"></div>
						<?php
						$age_text   = array();
						$age_values = array();
						for ( $age = 18; $age <= 62; $age++ ) {
							$age_text[]   = ( ( $age > 61 ) ? $age . ' or over' : $age );
							$age_values[] = $age;
						}
						?>
				</div>
				</div>
				<?php
			}
			if ( ( is_array( $options ) && array_key_exists( 'reverse_mortgage', $options ) ) || in_array( 'reverse_mortgage', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_reverse_mortgage' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_reverse_mortgage' ) : $mortgage_application_form_fields['reverse_mortgage'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'reverse_mortgage', true ) : '' );
				$age_data     = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'age', true ) : '' );
				?>
				<div class="field" style=" <?php echo( ( isset( $age_data ) && intval( trim( $age_data ) ) == 62 ) ? '' : 'display:none' ); ?>">
					<label for="reverse_mortgage"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
					<div class="mortgage_sub_field">
						<div class="mortgage_sub_field_buttons">
						<input type="radio" name="reverse_mortgage" id="reverse_mortgage_yes" value="Yes" class="required" required <?php echo checked( $data, 'Yes', false ); ?> disabled="disabled">
						<label for="reverse_mortgage_yes" class="mortgage_button_style">
							<?php esc_html_e( 'Yes', '1003-mortgage-application' ); ?>
						</label>
						</div>
						<div class="mortgage_sub_field_buttons">
						<input type="radio" name="reverse_mortgage" id="reverse_mortgage_no" value="No" class="required" required <?php echo( ! empty( $data ) ? checked( $data, 'No', false ) : 'checked="checked"' ); ?> disabled="disabled">
						<label for="reverse_mortgage_no" class="mortgage_button_style">
							<?php esc_html_e( 'No', '1003-mortgage-application' ); ?>
						</label>
						</div>
					</div>
					</div>
					<?php
			}
			if ( ( is_array( $options ) && array_key_exists( 'refinanced_before', $options ) ) || in_array( 'refinanced_before', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_refinanced_before' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_refinanced_before' ) : $mortgage_application_form_fields['refinanced_before'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'refinanced_before', true ) : '' );
				?>
					<div class="field purpose-refi">
					<label for="refinanced_before"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
					<div class="mortgage_sub_field scrollbar_class">
					<div class="mortgage_sub_field_buttons">
					<input type="radio" name="refinanced_before" id="refinanced_before_yihrb" class="required" required value="Yes, I have refinanced before" <?php echo checked( $data, 'Yes, I have refinanced before', false ); ?> />
					<label for="refinanced_before_yihrb" class="mortgage_button_style"><?php esc_html_e( 'Yes, I have refinanced before', '1003-mortgage-application' ); ?></label>
					</div>
					<div class="mortgage_sub_field_buttons">
					<input type="radio" name="refinanced_before" id="refinanced_before_niwtb" class="required" required value="No, I was too busy" <?php echo checked( $data, 'No, I was too busy', false ); ?> />
					<label for="refinanced_before_niwtb" class="mortgage_button_style"><?php esc_html_e( 'No, I was too busy', '1003-mortgage-application' ); ?></label>
					</div>
					<div class="mortgage_sub_field_buttons">
					<input type="radio" name="refinanced_before" id="refinanced_before_nihabe" class="required" required value="No, I had a bad experience" <?php echo checked( $data, 'No, I had a bad experience', false ); ?> />
					<label for="refinanced_before_nihabe" class="mortgage_button_style"><?php esc_html_e( 'No, I had a bad experience', '1003-mortgage-application' ); ?></label>
					</div>
					<div class="mortgage_sub_field_buttons">
					<input type="radio" name="refinanced_before" id="refinanced_before_nicnstfb" class="required" required value="No, I could not see the financial benefit" <?php echo checked( $data, "No, I couldn't see the financial benefit", false ); ?> />
					<label for="refinanced_before_nicnstfb" class="mortgage_button_style"><?php esc_html_e( 'No, I could not see the financial benefit', '1003-mortgage-application' ); ?></label>
					</div>
					<div class="mortgage_sub_field_buttons">
					<input type="radio" name="refinanced_before" id="refinanced_before_nidntiwq" class="required" required value="No, I did not think I would qualify" <?php echo checked( $data, "No, I didn't think I would qualify", false ); ?> />
					<label for="refinanced_before_nidntiwq" class="mortgage_button_style"><?php esc_html_e( 'No, I did not think I would qualify', '1003-mortgage-application' ); ?></label>
					</div>
					</div>
					</div>
					<?php
			}
				/*****************refinance field:end*/
			if ( ( is_array( $options ) && array_key_exists( 'employment_status', $options ) ) || in_array( 'employment_status', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_employment_status' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_employment_status' ) : $mortgage_application_form_fields['employment_status'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'employment_status', true ) : '' );
				?>
				<div class="field">
				<label for="employment_status"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
				<div class="mortgage_sub_field">
					<div class="mortgage_sub_field_buttons">
				<input type="radio" name="employment_status" id="employment_status_employed" class="required" required value="Employed" <?php echo( ! empty( $data ) ? checked( $data, 'Employed', false ) : 'checked="checked"' ); ?> />
				<label for="employment_status_employed" class="mortgage_button_style"><?php esc_html_e( 'Employed', '1003-mortgage-application' ); ?></label>
				</div>
				<div class="mortgage_sub_field_buttons">
				<input type="radio" name="employment_status" id="employment_status_retired" class="required" required value="retired" <?php echo checked( $data, 'retired' ); ?> />
				<label for="employment_status_retired" class="mortgage_button_style"><?php esc_html_e( 'Retired', '1003-mortgage-application' ); ?></label>
				</div>
				</div>
				</div>
				<?php
			}
			if ( ( is_array( $options ) && array_key_exists( 'late_payments', $options ) ) || in_array( 'late_payments', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_late_payments' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_late_payments' ) : $mortgage_application_form_fields['late_payments'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'late_payments', true ) : '' );
				?>
				<div class="field">
				<label for="late_payments"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
				<div class="mortgage_sub_field">
					<div class="mortgage_sub_field_buttons">
					<input type="radio" name="late_payments" id="late_payments0" class="required" required value="0" <?php echo( ! empty( $data ) ? checked( $data, '0', false ) : 'checked="checked"' ); ?> />
					<label for="late_payments0" class="mortgage_button_style"><?php esc_html_e( 'None', '1003-mortgage-application' ); ?></label>
				</div>
				<div class="mortgage_sub_field_buttons">
					<input type="radio" name="late_payments" id="late_payments1" class="required" required value="1" <?php echo checked( $data, '1', false ); ?> />
					<label for="late_payments1" class="mortgage_button_style">1</label>
				</div>
				<div class="mortgage_sub_field_buttons">
					<input type="radio" name="late_payments" id="late_payments2" class="required" required value="2+" <?php echo checked( $data, '2+', false ); ?> />
					<label for="late_payments2" class="mortgage_button_style">2+</label>
				</div>
				</div>
				</div>
				<?php
			}
			if ( ( is_array( $options ) && array_key_exists( 'bankruptcy', $options ) ) || in_array( 'bankruptcy', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_bankruptcy' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_bankruptcy' ) : $mortgage_application_form_fields['bankruptcy'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'bankruptcy', true ) : '' );
				?>
				<div class="field">
				<label for="bankruptcy"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
					<div class="mortgage_sub_field">
						<div class="mortgage_sub_field_buttons">
						<input type="radio" name="bankruptcy" id="bankruptcy_yes" value="Yes" class="required" required <?php echo checked( $data, 'Yes', false ); ?>>
						<label for="bankruptcy_yes" class="mortgage_button_style">
							<?php esc_html_e( 'Yes', '1003-mortgage-application' ); ?>
						</label>
						</div>
						<div class="mortgage_sub_field_buttons">
						<input type="radio" name="bankruptcy" id="bankruptcy_no" value="No" class="required" required <?php echo( ! empty( $data ) ? checked( $data, 'No', false ) : 'checked="checked"' ); ?>>
						<label for="bankruptcy_no" class="mortgage_button_style">
							<?php esc_html_e( 'No', '1003-mortgage-application' ); ?>
						</label>
						</div>
					</div>
				</div>
				<?php
			}
				/*****************refinance field:start*/
			if ( ( is_array( $options ) && array_key_exists( 'has_FHA', $options ) ) || in_array( 'has_FHA', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_has_FHA' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_has_FHA' ) : $mortgage_application_form_fields['has_FHA'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'has_FHA', true ) : '' );
				?>
				<div class="field purpose-refi">
				<label for="has_FHA"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
				<div class="mortgage_sub_field">
					<div class="mortgage_sub_field_buttons">
					<input type="radio" name="has_FHA" id="has_FHA_yes" value="Yes" class="required" required <?php echo checked( $data, 'Yes', false ); ?>>
					<label for="has_FHA_yes" class="mortgage_button_style">
						<?php esc_html_e( 'Yes', '1003-mortgage-application' ); ?>
					</label>
					</div>
					<div class="mortgage_sub_field_buttons">
				<input type="radio" name="has_FHA" id="has_FHA_no" value="No" class="required" required <?php echo( ! empty( $data ) ? checked( $data, 'No', false ) : 'checked="checked"' ); ?>>
					<label for="has_FHA_no" class="mortgage_button_style">
						<?php esc_html_e( 'No', '1003-mortgage-application' ); ?>
					</label>
					</div>
					<div class="mortgage_sub_field_buttons">
					<input type="radio" name="has_FHA" id="has_FHA_iDontKnow" value="I do not know" class="required" required <?php echo checked( $data, 'I do not know', false ); ?>>
					<label for="has_FHA_iDontKnow" class="mortgage_button_style">
						<?php esc_html_e( "I don't know", '1003-mortgage-application' ); ?>
					</label>
					</div>
				</div>
				</div>
				<?php
			}
				/*****************refinance field:end*/

				/*****************purchase field:start*/
			if ( ( is_array( $options ) && array_key_exists( 'foreclosure', $options ) ) || in_array( 'foreclosure', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_foreclosure' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_foreclosure' ) : $mortgage_application_form_fields['foreclosure'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'foreclosure', true ) : '' );
				?>
				<div class="field purpose-purch">
				<label for="foreclosure"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
				<div class="mortgage_sub_field">
					<div class="mortgage_sub_field_buttons">
					<input type="radio" name="foreclosure" id="foreclosure_yes" value="Yes" class="required" required <?php echo checked( $data, 'Yes', false ); ?>>
					<label for="foreclosure_yes" class="mortgage_button_style">
						<?php esc_html_e( 'Yes', '1003-mortgage-application' ); ?>
					</label>
					</div>
					<div class="mortgage_sub_field_buttons">
					<input type="radio" name="foreclosure" id="foreclosure_no" value="No" class="required" required <?php echo( ! empty( $data ) ? checked( $data, 'No', false ) : 'checked="checked"' ); ?>>
					<label for="foreclosure_no" class="mortgage_button_style">
						<?php esc_html_e( 'No', '1003-mortgage-application' ); ?>
					</label>
					</div>
				</div>
				</div>
				<?php
			}
			if ( ( is_array( $options ) && array_key_exists( 'monthly_income', $options ) ) || in_array( 'monthly_income', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_monthly_income' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_monthly_income' ) : $mortgage_application_form_fields['monthly_income'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'monthly_income', true ) : '' );
				?>
				<div class="field purpose-purch">
				<label for="proof_of_income"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
				<div class="mortgage_sub_field">
					<input type="text" name="monthly_income" id="monthly_income" value="<?php echo esc_attr( $data ); ?>" class="required" required>
				</div>
				</div>
				<?php
			}
			if ( ( is_array( $options ) && array_key_exists( 'proof_of_income', $options ) ) || in_array( 'proof_of_income', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_proof_of_income' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_proof_of_income' ) : $mortgage_application_form_fields['proof_of_income'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'proof_of_income', true ) : '' );
				?>
				<div class="field purpose-purch">
				<label for="proof_of_income"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
				<div class="mortgage_sub_field">
					<div class="mortgage_sub_field_buttons">
					<input type="radio" name="proof_of_income" id="proof_of_income_yes" value="Yes" class="required" required <?php echo checked( $data, 'Yes', false ); ?>>
					<label for="proof_of_income_yes" class="mortgage_button_style">
						<?php esc_html_e( 'Yes', '1003-mortgage-application' ); ?>
					</label>
					</div>
					<div class="mortgage_sub_field_buttons">
					<input type="radio" name="proof_of_income" id="proof_of_income_no" value="No" class="required" required <?php echo( ! empty( $data ) ? checked( $data, 'No', false ) : 'checked="checked"' ); ?>>
					<label for="proof_of_income_no" class="mortgage_button_style">
						<?php esc_html_e( 'No', '1003-mortgage-application' ); ?>
					</label>
					</div>
				</div>
				</div>
				<?php
			}

				/*****************purchase field:end*/
			if ( ( is_array( $options ) && array_key_exists( 'mailing_address', $options ) ) || in_array( 'mailing_address', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_mailing_address' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_mailing_address' ) : $mortgage_application_form_fields['mailing_address'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'mailing_address', true ) : '' );
				?>
				<div class="field">
				<label for="mailing_address"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
				<div class="mortgage_sub_field">
					<input type="text" name="mailing_address" id="mailing_address" value="<?php echo esc_attr( $data ); ?>" class="required" required/>
				</div>
				</div>
				<?php
			}
				/*
				if((is_array($options) && array_key_exists('city_state', $options)) || in_array('city_state', $mortgage_application_required_form_fields))
				{
					$option_label = (!empty(get_front_mortgage_application_option('mortgage_application_label_city_state')) ? get_front_mortgage_application_option('mortgage_application_label_city_state') : $mortgage_application_form_fields['city_state']);
					$data = (!empty($app_id) ? get_post_meta($app_id, 'property_city', true): "");
					$state_data = (!empty($app_id) ? get_post_meta($app_id, 'property_state', true): "");
			?>
				<div class="field">
				<label for="city_state"><?php esc_html_e($option_label . ':', '1003-mortgage-application'); ?><span>*</span></label>
				<div class="mortgage_sub_field">
					<span class="city_state"><?php echo $data . " " . $state_data; ?></span>
					<input type="hidden" value="<?php echo $data; ?>" name="property_city" id="property_city"/>
					<input type="hidden" value="<?php echo $state_data; ?>" name="property_state" id="property_state"/>
				</div>
				</div>
				<?php
				}*/

			if ( ( is_array( $options ) && array_key_exists( 'zip_code', $options ) ) || in_array( 'zip_code', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_zip_code' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_zip_code' ) : $mortgage_application_form_fields['zip_code'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'zip_code', true ) : '' );
				?>
				<div class="field">
				<label for="zip_code"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
				<div class="mortgage_sub_field">
					<span class="zip_code"><?php echo esc_html( $data ); ?></span>
				</div>
				</div>
				<?php
			}
			if ( ( is_array( $options ) && array_key_exists( 'email', $options ) ) || in_array( 'email', $mortgage_application_required_form_fields ) ) {
				$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_email' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_email' ) : $mortgage_application_form_fields['email'] );
				$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'email', true ) : '' );
				?>
				<div class="field">
				<label for="email"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
				<div class="mortgage_sub_field">
					<input type="email" value="<?php echo esc_attr( $data ); ?>" name="email" id="email"class="required" required/>
				</div>
				</div>
				<?php
				if ( ! empty( $app_id ) ) {
					?>
								<script type="text/javascript">
								jQuery(window).load(function(e) {
									jQuery(".button.prev").one("blur", function(){
										jQuery("#email").blur();
									});
								});
								</script>
						<?php
				}
			}
			?>
			<div class="action">
				<input type="button" class="button  mail" value="<?php esc_html_e( 'Continue >>', '1003-mortgage-application' ); ?>"/>
				<input type="button" class="button btn-step-prev" value="<?php esc_html_e( '<< Back', '1003-mortgage-application' ); ?>" />
			</div>
			</fieldset>
			<fieldset data-progress="20"  id="hide" class="<?php echo( ! empty( $app_id ) ? 'active' : '' ); ?>">
				<?php
				if ( ( is_array( $options ) && array_key_exists( 'cash_out_box', $options ) ) || in_array( 'cash_out_box', $mortgage_application_required_form_fields ) ) {
					$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_cash_out_box' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_cash_out_box' ) : $mortgage_application_form_fields['cash_out_box'] );
					$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'cash_out_box', true ) : '' );
					?>
					<div class="field purpose-refi">
					<label for="cash_out_box"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
					<div class="mortgage_sub_field">
						<span id="cash_out_box"><?php echo number_format( $data ); ?></span>
						<input type="hidden" name="cash_out_box" value="<?php echo esc_attr( $data ); ?>" />
					</div>
					</div>
					<?php
				}
				if ( ( is_array( $options ) && array_key_exists( 'military', $options ) ) || in_array( 'military', $mortgage_application_required_form_fields ) ) {
					$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_military' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_military' ) : $mortgage_application_form_fields['military'] );
					$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'military', true ) : '' );
					?>
					<div class="field">
					<label for="military"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
					<div class="mortgage_sub_field">
						<div class="mortgage_sub_field_buttons">
						<input type="radio" name="military" id="military_yes" value="Yes" class="required" required <?php echo checked( $data, 'Yes', false ); ?>>
						<label for="military_yes" class="mortgage_button_style">
							<?php esc_html_e( 'Yes', '1003-mortgage-application' ); ?>
						</label>
						</div>
						<div class="mortgage_sub_field_buttons">
						<input type="radio" name="military" id="military_no" value="No" class="required" required <?php echo( ! empty( $data ) ? checked( $data, 'No', false ) : 'checked="checked"' ); ?>>
						<label for="military_no" class="mortgage_button_style">
								<?php esc_html_e( 'No', '1003-mortgage-application' ); ?>
						</label>
						</div>
					</div>
					</div>
					<?php
				}
				if ( ( is_array( $options ) && array_key_exists( 'agent_contact', $options ) ) || in_array( 'agent_contact', $mortgage_application_required_form_fields ) ) {
					$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_agent_contact' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_agent_contact' ) : $mortgage_application_form_fields['agent_contact'] );
					$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'agent_contact', true ) : '' );
					?>
					<div class="field purpose-purch">
					<label for="agent_contact"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
					<div class="mortgage_sub_field">
						<div class="mortgage_sub_field_buttons">
						<input type="radio" name="agent_contact" id="agent_contact_yes" value="Yes" class="required" required <?php echo checked( $data, 'Yes', false ); ?>>
						<label for="agent_contact_yes" class="mortgage_button_style">
							<?php esc_html_e( 'Yes', '1003-mortgage-application' ); ?>
						</label>
						</div>
						<div class="mortgage_sub_field_buttons">
						<input type="radio" name="agent_contact" id="agent_contact_no" value="No" class="required" required <?php echo( ! empty( $data ) ? checked( $data, 'No', false ) : 'checked="checked"' ); ?>>
						<label for="agent_contact_no" class="mortgage_button_style">
								<?php esc_html_e( 'No', '1003-mortgage-application' ); ?>
						</label>
						</div>
					</div>
					</div>
					<?php
				}
				if ( ( is_array( $options ) && array_key_exists( 'use_va_loans', $options ) ) || in_array( 'use_va_loans', $mortgage_application_required_form_fields ) ) {
					$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_use_va_loans' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_use_va_loans' ) : $mortgage_application_form_fields['use_va_loans'] );
					$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'use_va_loans', true ) : '' );
					?>
					<div class="field purpose-refi">
					<label for="use_va_loans"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
					<div class="mortgage_sub_field">
						<div class="mortgage_sub_field_buttons">
						<input type="radio" name="use_va_loans" id="use_va_loans_yes" value="Yes" class="required" required <?php echo checked( $data, 'Yes', false ); ?>>
						<label for="use_va_loans_yes" class="mortgage_button_style">
							<?php esc_html_e( 'Yes', '1003-mortgage-application' ); ?>
						</label>
						</div>
						<div class="mortgage_sub_field_buttons">
						<input type="radio" name="use_va_loans" id="use_va_loans_no" value="No" class="required" required <?php echo( ! empty( $data ) ? checked( $data, 'No', false ) : 'checked="checked"' ); ?>>
						<label for="use_va_loans_no" class="mortgage_button_style">
								<?php esc_html_e( 'No', '1003-mortgage-application' ); ?>
						</label>
						</div>
					</div>
					</div>
					<?php
				}

				if ( ( is_array( $options ) && array_key_exists( 'mailing_address', $options ) ) || in_array( 'mailing_address', $mortgage_application_required_form_fields ) ) {
					$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_mailing_address' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_mailing_address' ) : $mortgage_application_form_fields['mailing_address'] );
					// $data = (!empty($app_id) ? get_post_meta($app_id, 'mailing_address', true): "");
					?>
					<div class="field">
						<label for="mailing_address"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
						<div class="mortgage_sub_field">
							<span class="mailing_address"></span>
						</div>
					</div>
					<?php
				}

				if ( ( is_array( $options ) && array_key_exists( 'first_name', $options ) ) || in_array( 'first_name', $mortgage_application_required_form_fields ) ) {
					$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_first_name' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_first_name' ) : $mortgage_application_form_fields['first_name'] );
					$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'first_name', true ) : '' );
					?>
					<div class="field">
					<label for="first_name"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
					<div class="mortgage_sub_field">
						<input type="text" name="first_name" id="first_name" value="<?php echo esc_attr( $data ); ?>" class="required" required/>
					</div>
					</div>
					<?php
				}
				if ( ( is_array( $options ) && array_key_exists( 'last_name', $options ) ) || in_array( 'last_name', $mortgage_application_required_form_fields ) ) {
					$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_last_name' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_last_name' ) : $mortgage_application_form_fields['last_name'] );
					$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'last_name', true ) : '' );
					?>
					<div class="field">
					<label for="last_name"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
					<div class="mortgage_sub_field">
						<input type="text" name="last_name" id="last_name" value="<?php echo esc_attr( $data ); ?>" class="required" required/>
					</div>
					</div>
					<?php
				}
				/*
				if((is_array($options) && array_key_exists('city_state', $options)) || in_array('city_state', $mortgage_application_required_form_fields))
				{
					$option_label = (!empty(get_front_mortgage_application_option('mortgage_application_label_city_state')) ? get_front_mortgage_application_option('mortgage_application_label_city_state') : $mortgage_application_form_fields['city_state']);
					$data = (!empty($app_id) ? get_post_meta($app_id, 'property_city', true): "");
					$data_state = (!empty($app_id) ? get_post_meta($app_id, 'property_state', true): "");
				?>
					<div class="field">
						<label for="city_state"><?php esc_html_e($option_label . ':', '1003-mortgage-application'); ?><span>*</span></label>
						<div class="mortgage_sub_field">
							<span class="city_state"><?php echo $data . ' ' . $data_state; ?></span>
						</div>
					</div>
				<?php
				}*/
				if ( ( is_array( $options ) && array_key_exists( 'zip_code', $options ) ) || in_array( 'zip_code', $mortgage_application_required_form_fields ) ) {
					$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_zip_code' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_zip_code' ) : $mortgage_application_form_fields['zip_code'] );
					$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'zip_code', true ) : '' );
					?>
					<div class="field">
						<label for="zip_code"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
						<div class="mortgage_sub_field">
							<span class="zip_code"><?php echo esc_html( $data ); ?></span>
						</div>
					</div>
					<?php
				}
				if ( ( is_array( $options ) && array_key_exists( 'phone_number', $options ) ) || in_array( 'phone_number', $mortgage_application_required_form_fields ) ) {
					$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_phone_number' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_phone_number' ) : $mortgage_application_form_fields['phone_number'] );
					$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'phone_number', true ) : '' );
					?>
				<div class="field">
					<label for="phone_number"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
					<div class="mortgage_sub_field">
						<input type="tel" name="phone_number" id="phone_number" value="<?php echo esc_attr( $data ); ?>" class="required phoneUS" required/>
					</div>
				</div>
					<?php
				}

				/*
				* Dated: January 30th, 2020: PV
				* Added below two new fields.
				*/

				if ( ( is_array( $options ) && array_key_exists( 'dob', $options ) ) || in_array( 'dob', $mortgage_application_required_form_fields ) ) {
					$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_dob' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_dob' ) : $mortgage_application_form_fields['dob'] );
					$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'dob', true ) : '' );
					?>
				<div class="field">
					<label for="phone_number"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
					<div class="mortgage_sub_field">
						<input type="text" name="dob" id="dob" value="<?php echo esc_attr( $data ); ?>" class="required" required placeholder="MM/DD/YYYY"/>
					</div>
				</div>
					<?php
				}
				if ( ( is_array( $options ) && array_key_exists( 'ss_number', $options ) ) || in_array( 'ss_number', $mortgage_application_required_form_fields ) ) {
					$option_label = ( ! empty( get_front_mortgage_application_option( 'mortgage_application_label_ss_number' ) ) ? get_front_mortgage_application_option( 'mortgage_application_label_ss_number' ) : $mortgage_application_form_fields['ss_number'] );
					$data         = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'ss_number', true ) : '' );
					?>
				<div class="field">
					<label for="phone_number"><?php printf( '%s:', esc_html( $option_label ) ); ?><span>*</span></label>
					<div class="mortgage_sub_field">
						<input type="text" name="ss_number" id="ss_number" value="<?php echo esc_attr( $data ); ?>" class="required" required/>
					</div>
				</div>
					<?php
				}

				?>
				<div class="field">
					<div class="disclaimer-agree-access">
						<input type="checkbox" name="agree_access" value="yes" id="agree_access" class="required" required/>
						<?php esc_html_e( 'I agree to the following terms & conditions', '1003-mortgage-application' ); ?>
						<?php echo esc_html( wpautop( get_front_mortgage_application_option( 'disclaimer_field_1' ) ) ); ?>
					</div>
				</div>
			</fieldset>
			<div class="action">
				<?php wp_nonce_field( 'mortgate_application_data_save', 'application_data_save' ); ?>
				<input type="hidden" name="crud" id="crud" value="<?php echo( ! empty( $app_id ) ? 'ma_update' : 'ma_add' ); ?>" />
				<input type="hidden" name="application_status" id="application_status" value="<?php echo( ! empty( $app_id ) ? esc_attr( get_post_meta( $app_id, 'application_status', true ) ) : 0 ); ?>" />
				<input type="hidden" name="rec_id" id="rec_id" value="<?php echo( ! empty( $app_id ) ? esc_attr( $app_id ) : '' ); ?>"  />
				<?php $application_status = ( ! empty( $app_id ) ? get_post_meta( $app_id, 'application_status', true ) : 0 ); ?>
				<input type="button" style=" <?php echo( ! empty( $application_status ) && $application_status != '' ? '' : 'display:none' ); ?>" class="submit button" value="<?php esc_html_e( 'Submit', '1003-mortgage-application' ); ?>"/>
				<input type="button" class="button next" value="<?php esc_html_e( 'Continue >>', '1003-mortgage-application' ); ?>" style="display:none"/>
				<input type="button" class="button prev" value="<?php esc_html_e( '<< Back', '1003-mortgage-application' ); ?>" style=" <?php echo( ! empty( $application_status ) && $application_status != '' ? '' : 'display:none' ); ?>"/>
			</div>
		</form>
	</div>
</div>
<script>
	(function($){

		jQuery(window).on('load',function(){

			jQuery(".scrollbar_class").mCustomScrollbar({
				theme: "dark"
			});
			jQuery('#ss_number').keyup(function () {
				var val = this.value.replace(/\D/g, '');
				val = val.replace(/^(\d{3})/, '$1-');
				val = val.replace(/-(\d{2})/, '-$1-');
				val = val.replace(/(\d)-(\d{4}).*/, '$1-$2');
				this.value = val;
			});
			jQuery("#dob").datepicker({
				changeMonth: true,
				changeYear: true,
				yearRange: '1920:<?php echo esc_html( gmdate( 'Y' ) - 18 ); ?>'
			});

		});

	})(jQuery);
</script>
