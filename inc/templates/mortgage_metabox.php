<?php
/**
 * metabox is responsible to show custom filed meta box in mortgate_application custom post admin panel
 *
 * @link        https://lenderd.com
 * @since       1.0.0
 *
 * @package     mortgage_application
 * @sub-package mortgage_application/inc/templates
 **/
// If this file is called directly, abort.
defined( 'ABSPATH' ) or die( 'Access denied!' );
?>
<div class="mortgage_application_data_box">
	<p class="meta-options mortgage_application_field">
		<label for="<?php echo esc_attr( 'application_status' ); ?>"><?php esc_html_e( 'Application Status', '1003-mortgage-application' ); ?>: </label>
		<span id="<?php echo esc_attr( 'application_status' ); ?>">
			<?php
			$status = esc_attr( get_post_meta( get_the_ID(), 'application_status', true ) );
			if ( isset( $status ) && ! empty( $status ) ) {
				echo '<b>' . esc_html( $status ) . '% ' . esc_html__( 'completed', '1003-mortgage-application' ) . '</b>';
			}
			?>
		</span>
		<input type ="hidden" id="<?php echo esc_attr( 'application_status' ); ?>" type="text" name="<?php echo esc_attr( 'application_status' ); ?>" value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'application_status', true ) ); ?>">
	</p>
	<?php
	if ( isset( $status ) && ! empty( $status ) && $status == 80 ) {
		?>
	<p class="meta-options mortgage_application_field">
		<label for="<?php echo esc_attr( 'application_short_url' ); ?>"><?php esc_html_e( 'Application Edit Short URL', '1003-mortgage-application' ); ?>: </label>
		<span id="<?php echo esc_attr( 'application_short_url' ); ?>">
		<?php
			/* get the short url */
			$bitly           = new mapp_bitly_shortURL();
			$long_url        = get_site_url( null, '?ma_mode=ma_edit&id=' . get_the_ID(), 'https' );
			$short_url_json  = $bitly->get_short_url( $long_url );
			$short_url_array = json_decode( $short_url_json, true );
		if ( is_array( $short_url_array ) && isset( $short_url_array['link'] ) ) {
			echo '<a href="' . esc_url( $short_url_array['link'] ) . '">' . esc_html( $short_url_array['link'] ) . '</a>';
		}
		?>
		
		</span>	
	</p>
		<?php
	}
	global $mortgage_application_form_fields;
	foreach ( $mortgage_application_form_fields as $form_field_key => $form_field_label ) {
		if ( isset( $form_field_key ) && ! empty( $form_field_key ) && $form_field_key == 'city_state' ) {
			$property_city  = get_post_meta( get_the_ID(), 'property_city', true );
			$property_state = get_post_meta( get_the_ID(), 'property_state', true );
			$field_data     = $property_city . ' ' . $property_state;
		} elseif ( isset( $form_field_key ) && ! empty( $form_field_key ) && $form_field_key == 'ss_number' ) {
			$encrypted_value                = get_post_meta( get_the_ID(), $form_field_key, true );
			list($encrypted_value, $enc_iv) = explode( '::', $encrypted_value );
			$cipher_method                  = 'aes-128-ctr';
			$enc_key                        = openssl_digest( php_uname(), 'SHA256', true );
			$decrypted_value                = openssl_decrypt( $encrypted_value, $cipher_method, $enc_key, 0, hex2bin( $enc_iv ) );

			$field_data = $decrypted_value;
		} else {
			$field_data = get_post_meta( get_the_ID(), $form_field_key, true );
		}
		if ( isset( $field_data ) && ! empty( $field_data ) && $field_data != '' ) {
			?>
		<p class="meta-options mortgage_application_field">
			<label for="<?php echo esc_attr( $form_field_key ); ?>"><?php echo esc_html( $form_field_label ); ?>: </label>
			<span><?php echo esc_attr( $field_data ); ?></span>
		</p>
			<?php
		}
	}
	?>
	
</div>
