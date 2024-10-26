<?php

/**
 * This file is responsible to application form front view.
 *
 * @link        https://lenderd.com
 * @since       1.0.0
 *
 * @package     mortgage_application
 * @sub-package mortgage_application/inc/templates
 */
// If this file is called directly, abort.
defined( 'ABSPATH' ) or die( 'Access denied!' );



$exampleListTable = new map_file_List_Table();
$exampleListTable->prepare_items();
?>
<div class="wrap">
	<div id="icon-users" class="icon32"></div>
	<h2>Uploaded Files List</h2>
	<?php $exampleListTable->display(); ?>
</div>
<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class map_file_List_Table extends WP_List_Table {

	/**
	 * Prepare the items for the table to process
	 *
	 * @return Void
	 */
	public function prepare_items() {
		$columns = $this->get_columns();

		$data = $this->table_data();
		usort( $data, array( &$this, 'sort_data' ) );

		$perPage     = 10;
		$currentPage = $this->get_pagenum();
		$totalItems  = count( $data );

		$this->set_pagination_args(
			array(
				'total_items' => $totalItems,
				'per_page'    => $perPage,
			)
		);

		$data = array_slice( $data, ( ( $currentPage - 1 ) * $perPage ), $perPage );

		$this->_column_headers = array( $columns );
		$this->items           = $data;
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return Array
	 */
	public function get_columns() {
		$columns = array(
			's_n'          => 'S.No.',
			'file_name'    => 'File Name',
			'created_date' => 'Created Date',
			'delete_date'  => 'Delete Date',
			'download'     => 'Download',
		);
		return $columns;
	}


	/**
	 * Get the table data
	 *
	 * @return Array
	 */
	private function table_data() {
		$current_blog_id      = get_current_blog_id();
		$path                 = MAPP_MORTGAGE_APP_BASE_PATH . 'uploads/' . $current_blog_id;
		$locate               = MAPP_MORTGAGE_APP_BASE_URL . 'uploads/' . $current_blog_id;
		$all_files            = scandir( $path );
		$download_source      = MAPP_MORTGAGE_APP_BASE_URL . 'inc/templates/mortgage_download_file.php';
		$saved_delete_days    = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_ma_submissions_deleted_file', 'mortgage_submissions_use_form_network_settings' ) );
		$donwload_limit       = sanitize_text_field( get_front_mortgage_application_option( 'mortgage_ma_submissions_download_limit', 'mortgage_submissions_use_form_network_settings' ) );
		$get_file_delete_date = '';
		foreach ( $all_files as $var => $val ) {
			if ( $val !== '.' && $val !== '..' ) {
				$stat                 = stat( $path . '/' . $val );
				$get_file_delete_date = gmdate( 'm-d-Y h:i:s', strtotime( gmdate( 'm-d-Y h:i:s', $stat['mtime'] ) . '+ ' . $saved_delete_days . ' days' ) );
				$index_limit          = 0;
				$file_ext             = substr( $val, strrpos( $val, '.' ) + 1 );
				$str                  = wp_rand();
				$enc_result           = hash( 'sha256', $str );
				$dec_result           = $enc_result . '.' . $file_ext;
				$map_click_limit      = get_option( $val );
				$current_date         = gmdate( 'm-d-Y h:i:s' );
				if ( $donwload_limit <= $map_click_limit || $current_date >= $get_file_delete_date ) {
					$data[] = array(
						's_n'          => $var - 1,
						'file_name'    => $val,
						'created_date' => gmdate( 'm-d-Y h:i:s', $stat['mtime'] ),
						'delete_date'  => $get_file_delete_date,
						'download'     => '<form action="' . $download_source . '"  method="post">
														<input type="hidden" name="map_source_file" value="' . $path . '/' . $val . '" />
                            							<input type="hidden" name="map_dest_file" value="' . $path . '/' . $dec_result . '" />
                            							<input type="hidden" name="map_donwload_limit_val" class="map_donwload_limit_val" value="' . $donwload_limit . '" />
														
														<input type="submit" disabled="disabled" class="map_dwn_file"  data-id="' . $val . '" value="Download" />
														
												  </form>',
					);
				} else {
					$data[] = array(
						's_n'          => $var - 1,
						'file_name'    => $val,
						'created_date' => gmdate( 'm-d-Y h:i:s', $stat['mtime'] ),
						'delete_date'  => $get_file_delete_date,
						'download'     => '<form action="' . $download_source . '"  method="post">
														<input type="hidden" name="map_source_file" value="' . $path . '/' . $val . '" />
                            							<input type="hidden" name="map_dest_file" value="' . $path . '/' . $dec_result . '" />
                            							<input type="hidden" name="map_donwload_limit_val" class="map_donwload_limit_val" value="' . $donwload_limit . '" />
														
														<input type="submit"  class="map_dwn_file"  data-id="' . $val . '" value="Download" />
														
												  </form>',
					);
				}
			}
		}

		return $data;
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  Array  $item        Data
	 * @param  String $column_name - Current column name
	 *
	 * @return Mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 's_n':
			case 'file_name':
			case 'created_date':
			case 'delete_date':
			case 'download':
				return $item[ $column_name ];

			default:
				return print_r( $item, true );
		}
	}
}
?>
<script type="text/javascript">
	jQuery(document).ready(function(e) {

		jQuery(".map_dwn_file").click(function(e) {

			var file_name = jQuery(this).data("id");
			var click_btn = jQuery(this);
			var donwload_limit_val = jQuery(".map_donwload_limit_val").val();
			var donwload_limit_val_num = Number(donwload_limit_val);
			jQuery.ajax({
				type: "POST",
				dataType: "json",
				url: "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>",
				data: {
					action: "mortgate_application_download_files",
					file_name: file_name
				},
				success: function(response) {
					if (response.data.status == donwload_limit_val_num) {
						jQuery(click_btn).prop('disabled', true);
					}
				}
			});
		});
	});
</script>

<style>
	.wp-list-table.fixed {
		table-layout: auto;
	}
</style>
