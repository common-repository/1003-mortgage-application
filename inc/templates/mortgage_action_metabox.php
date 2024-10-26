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
// retrieve the license from the database
$license = trim( get_mortgage_application_option( 'ma_license_key' ) );
$status  = get_mortgage_application_option( 'ma_license_key_status' );
?>
<div class="send-on-webhook-button-container">
<?php
if ( $status !== false && $status == 'valid' && $license !== false ) {
	/* Create Nonce */
	$nonce = wp_create_nonce( 'send_post_on_webhook' );
	?>
			<a class="button button-large button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?post=' . get_the_ID() . '&export_type=fnm&action=mortgage_application_export_applications' ), 'mortgage_application_export_applications', 'mortgage_application_export_nonce' ) ); ?>"><?php esc_html_e( 'Export to FNM 3.2', '1003-mortgage-application' ); ?></a>

			<a class="button button-large button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?post=' . get_the_ID() . '&export_type=mismo&action=mortgage_application_export_applications' ), 'mortgage_application_export_applications', 'mortgage_application_export_nonce' ) ); ?>"><?php esc_html_e( 'Export to MISMO 3.4', '1003-mortgage-application' ); ?></a>

			<input name="send_on_webhook_button" type="button" data-id="<?php echo esc_attr( ( ! empty( $post ) ? $post->ID : false ) ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>" class="send-on-webhook-button button-large button-primary" value="Send to Webhooks" />
			<?php
}
// retrieve the application status from the database
$status = esc_attr( get_post_meta( get_the_ID(), 'application_status', true ) );
if ( isset( $status ) && ! empty( $status ) && $status == 80 ) {
	/* Create Nonce */
	$nonce = wp_create_nonce( 'edit_post_reminder' );
	?>
	<input type="button" class="button button-large button-primary" data-id="<?php echo (int) get_the_ID(); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>" id="edit_post_reminder" value="<?php esc_attr_e( 'Send Reminder', '1003-mortgage-application' ); ?>" />
	<?php
}
?>
<a class="button button-large button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?post=' . get_the_ID() . '&export_type=csv&action=mortgage_application_export_applications' ), 'mortgage_application_export_applications', 'mortgage_application_export_nonce' ) ); ?>"><?php esc_html_e( 'Export to CSV', '1003-mortgage-application' ); ?></a>
</div>
