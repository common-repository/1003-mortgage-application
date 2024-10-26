<?php
/**
 * This file is responsible to show license details.
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
?>
<h3><?php esc_html_e( 'License Details', '1003-mortgage-application' ); ?></h3>
	<table>
		<tr>
			<th style="padding: 10px 20px 20px 0px;"><?php esc_html_e( 'User Name', '1003-mortgage-application' ); ?></th>
			<th style="padding: 10px 20px 20px 0px;"><?php esc_html_e( 'Email', '1003-mortgage-application' ); ?></th>
			<th style="padding: 10px 20px 20px 0px;"><?php esc_html_e( 'License Key', '1003-mortgage-application' ); ?></th>
			<th style="padding: 10px 20px 20px 0px;"><?php esc_html_e( 'License Limit', '1003-mortgage-application' ); ?></th>
			<th style="padding: 10px 20px 20px 0px;"><?php esc_html_e( 'Site Count', '1003-mortgage-application' ); ?></th>
			<th style="padding: 10px 20px 20px 0px;"><?php esc_html_e( 'Activations Left', '1003-mortgage-application' ); ?></th>
			<th style="padding: 10px 20px 20px 0px;"><?php esc_html_e( 'Purchased', '1003-mortgage-application' ); ?></th>
			<th style="padding: 10px 20px 20px 0px;"><?php esc_html_e( 'Expires', '1003-mortgage-application' ); ?></th>
			<th style="padding: 10px 20px 20px 0px;"><?php esc_html_e( 'Status', '1003-mortgage-application' ); ?></th>
		</tr>
		<tr>
			<td style="padding: 10px 20px 20px 0px;"><?php echo esc_html( ( isset( $responseBody->customer_name ) ? $responseBody->customer_name : '' ) ); ?></td>
			<td style="padding: 10px 20px 20px 0px;"><?php echo esc_html( ( isset( $responseBody->customer_name ) ? $responseBody->customer_email : '' ) ); ?></td>
			<td style="padding: 10px 20px 20px 0px;"><?php echo esc_html( ( isset( $license ) ? $license : '' ) ); ?></td>
			<td style="padding: 10px 20px 20px 0px;"><?php echo esc_html( ( isset( $responseBody->license_limit ) ? $responseBody->license_limit : '' ) ); ?></td>
			<td style="padding: 10px 20px 20px 0px;"><?php echo esc_html( ( isset( $responseBody->site_count ) ? $responseBody->site_count : '' ) ); ?></td>
			<td style="padding: 10px 20px 20px 0px;"><?php echo esc_html( ( isset( $responseBody->activations_left ) ? $responseBody->activations_left : '' ) ); ?></td>
			<td style="padding: 10px 20px 20px 0px;"><?php echo esc_html( ( isset( $responseBody->expires ) ? gmdate( 'Y-m-d H:i:s', strtotime( $responseBody->expires . ' -1 year' ) ) : '' ) ); ?></td>
			<td style="padding: 10px 20px 20px 0px;"><?php echo esc_html( ( isset( $responseBody->expires ) ? $responseBody->expires : '' ), '1003-mortgage-application' ); ?></td>
			<td style="padding: 10px 20px 20px 0px;">
					<span class="licesnses-status <?php echo esc_attr( ( ( $status !== false && $status == 'valid' ) ? 'activated' : 'deactivated' ) ); ?>"><?php echo false !== $status && 'valid' === $status ? esc_html__( 'Activated', '1003-mortgage-application' ) : esc_html__( 'Deactivated', '1003-mortgage-application' ); ?></span>
			</td>
		</tr>
	</table>
</div>