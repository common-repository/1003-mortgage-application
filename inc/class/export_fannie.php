<?php
// phpcs:disable WordPress.WP.AlternativeFunctions

require_once MAPP_MORTGAGE_APP_BASE_PATH . 'inc/class/fnm/src/FNM.php';

use indradevzapbuild\FNM\FNM;

class mapp_exportMortgageApplicationsFannie extends mapp_exportMortgageApplicationsBase {

	private $fannie_fields;
	public $fnmObj;
	public function __construct() {
		parent::__construct();
		global $mortgage_application_fannie_fields;
		$this->fannie_fields = $mortgage_application_fannie_fields;
		$this->fnmObj        = new FNM();
	}
	public function create_file( $folder_name, $data ) {
		$generatedDate = $generatedDate = gmdate( 'd-m-Y His' );
		/**
		 * create a file pointer connected to the output stream
		 *
		 * @var [type]
		 */
		$upload_dir_path = $this->get_dir_path( $folder_name );
		// $upload_dir_path = MAPP_MORTGAGE_APP_BASE_PATH.'uploads'.'/';

		// check csv dir exists
		if ( ! file_exists( $upload_dir_path ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
			mkdir( $upload_dir_path, 0777, true );
		}
		// empty csv dir
		array_map( 'unlink', glob( $upload_dir_path . '/*' ) );
		$try = array();

		// Initialize archive object
		if ( ! empty( $data ) && isset( $data ) ) {
			$get = array();
			foreach ( $data as $key => $value ) {
				global $mortgage_application_form_fields;

				$post_id        = $value->ID;
				$application_id = $value->ID;
				$status         = esc_attr( get_post_meta( $application_id, 'application_status', true ) );
				$fanny_data     = $this->get_user_data( $mortgage_application_form_fields, $post_id );
				$file_name      = sanitize_title( wp_strip_all_tags( get_post_meta( $application_id, 'email', true ) ) );
				$new_fanny      = $this->create_fnm_std( $fanny_data['map_fields'], $fanny_data['fannie_values'] );
				// $this->pre_code($new_fanny);
				$sendFNM                    = array();
				$sendFNM['new_fanny']       = $new_fanny;
				$sendFNM['application_id']  = $application_id;
				$sendFNM['upload_dir_path'] = $upload_dir_path;
				$sendFNM['file_name']       = $file_name;
				// $this->pre_code($new_fanny);
				// echo $this->pre_code($this->tryhis($new_fanny, $file_name, $application_id, $upload_dir_path));
				$fields_string = http_build_query( $sendFNM );
				$exportFNM     = curl_init();
				curl_setopt( $exportFNM, CURLOPT_URL, MAPP_MORTGAGE_APP_BASE_URL . 'inc/class/fnm/export.php' );
				curl_setopt( $exportFNM, CURLOPT_POSTFIELDS, $fields_string );
				curl_setopt( $exportFNM, CURLOPT_RETURNTRANSFER, 1 );
				$resultsFNM = curl_exec( $exportFNM );
				curl_close( $exportFNM );
				// fclose($output);
			} // en foreach
			// echo $this->pre_code($get);
			// die();
		} // en if
		// Get real path for our folder
		$rootPath = realpath( $upload_dir_path );
		$zip_url  = $this->create_zip( $rootPath );
		$this->download_zip( $zip_url );
		// die();
		// return;
	}

	public function tryhis( $get_json, $file_name, $application_id, $upload_dir_path ) {
		ob_start();
		$fm   = new FNM();
		$text = $fm->export( $get_json, $file_name . '-' . $application_id . '.fnm', $upload_dir_path );
		ob_end_clean();
		return $text;
	}

	public function pre_code( $array ) {
		echo '<pre>';
		print_r( $array );
		echo '</pre>';
	}
	public function create_fnm_std( $map_fields, $map_after ) {
		$fnm_map_fields    = $map_fields;
		$get_fannie_values = $map_after;
		$new_fanny         = new stdClass();

		$new_fanny->file_version = $get_fannie_values['file_version'];
		// $new_fanny->mortgage_applied_for = $get_fannie_values['file_version'];
		$new_fanny->loan_amount        = $get_fannie_values['loan_amount'];
		$new_fanny->agency_case_number = '';
		$new_fanny->case_number        = '';
		$new_fanny->interest_rate      = $get_fannie_values['loan_interest_rate'];
		$new_fanny->no_of_months       = '';

		if ( $fnm_map_fields['Existing_Type_of_Rate'] == 'Fixed' ) {
			$amortization_type = '05';
		} elseif ( $fnm_map_fields['Existing_Type_of_Rate'] == 'Adjustable' ) {
			$amortization_type = '01';
		} else {
			$amortization_type = '';
		}
		$new_fanny->amortization_type = $amortization_type;

		$new_fanny->property_street_address               = $get_fannie_values['mailing_address'];
		$new_fanny->property_city                         = $get_fannie_values['city'];
		$new_fanny->property_state                        = $get_fannie_values['state'];
		$new_fanny->property_zip_code                     = $get_fannie_values['zip_code_only'];
		$new_fanny->no_of_units                           = '';
		$new_fanny->year_built                            = '';
		$new_fanny->legal_description_of_subject_property = '';
		$new_fanny->purpose_of_loan                       = $get_fannie_values['purpose'];
		$new_fanny->property_will_be                      = $get_fannie_values['property_use'];
		$new_fanny->manner_in_which_title_will_be_held    = '';

		$new_fanny->estate_will_be_held_in = '';
		$new_fanny->titleholder_name       = $get_fannie_values['title_holder_name'];

		$new_fanny->down_payment_type_code    = '';
		$new_fanny->applicant_ssn             = $get_fannie_values['ss_number'];
		$new_fanny->applicant_first_name      = $get_fannie_values['first_name'];
		$new_fanny->applicant_middle_name     = '';
		$new_fanny->applicant_last_name       = $get_fannie_values['last_name'];
		$new_fanny->applicant_generation      = '';
		$new_fanny->applicant_home_phone      = $get_fannie_values['phone_number'];
		$new_fanny->applicant_schooling_years = '';
		$new_fanny->applicant_marital_status  = '';
		$new_fanny->applicant_dependent_count = '';
		$new_fanny->applicant_birth_date      = $get_fannie_values['dob'];
		$new_fanny->email                     = $get_fannie_values['email'];

		if ( ! empty( $fnm_map_fields['Current_Mailing_Address_Street'] ) ) {
			$explode                             = explode( ' ', $fnm_map_fields['Current_Mailing_Address_Street'] );
			$new_fanny->residence_street_address = '';
			$new_fanny->residence_city           = '';
			$new_fanny->residence_state          = '';
			$new_fanny->residence_zip_code       = '';
		} else {
			$new_fanny->residence_street_address = '';
			$new_fanny->residence_city           = '';
			$new_fanny->residence_state          = '';
			$new_fanny->residence_zip_code       = '';
		}
		$new_fanny->former_employers = array(
			(object) array(
				'former_emp_name'           => '',
				'former_emp_address'        => '',
				'former_emp_state'          => '',
				'former_emp_city'           => '',
				'former_emp_zip_code'       => '',
				'former_emp_business_phone' => '',
				'former_emp_position'       => '',
				'former_emp_self_emp'       => '',
				'former_emp_date_from'      => '',
				'former_emp_date_to'        => '',
				'former_emp_monthly_income' => '',
			),
			// and so on...
		);

		$new_fanny->real_estate_owned = array(
			(object) array(
				'property_disposition'                   => '',
				'asset_property_street_address'          => '',
				'asset_property_city'                    => '',
				'asset_property_state'                   => '',
				'asset_property_zip_code'                => '',
				'asset_property_present_market_value'    => '',
				'asset_property_amount_of_mortgage_lien' => '',
				'asset_property_gross_rental_income'     => '',
				'asset_property_gross_rental_income'     => '',
				'asset_property_insurance_maintenance_taxes' => '',
				'asset_property_net_rental_income'       => '',
			),
			// and so on...
		);
		$new_fanny->outstanding_assets = array(
			(object) array(
				'creditor_name'                   => '',
				'liability_type'                  => '',
				'creditor_street_address'         => '',
				'creditor_city'                   => '',
				'creditor_state'                  => '',
				'creditor_zip_code'               => '',
				'creditor_monthly_payment_amount' => '',
				'creditor_unpaid_balance'         => '',
				'creditor_account_no'             => '',
			),
			// and so on...
		);
		$new_fanny->transaction_purchase_price            = $get_fannie_values['purchase_price'];
		$new_fanny->transaction_after_imprvt_repair       = '';
		$new_fanny->transaction_land                      = '';
		$new_fanny->transaction_refinance                 = '';
		$new_fanny->estimated_prepaid_items               = '';
		$new_fanny->estimated_closing_cost                = '';
		$new_fanny->pmi_mip_funding_fee                   = '';
		$new_fanny->transaction_discount                  = '';
		$new_fanny->subordinate_financing                 = '';
		$new_fanny->applicant_closing_cost_paid_by_seller = '';
		$new_fanny->pmi_mip_funding_fee_financed          = '';
		$new_fanny->dec_outstanding_judgement             = '';

		$new_fanny->dec_bankrupt = ( $fnm_map_fields['Any_bankruptcy_in_the_past_7_years'] == 'Yes' ) ? 'Y' : 'N';

		$new_fanny->dec_property_foreclosed = ( $fnm_map_fields['Any_foreclosure_in_the_past_3_years'] == 'Yes' ) ? 'Y' : 'N';
		$new_fanny->dec_lawsuit             = '';
		$new_fanny->dec_obligated_loan      = ( $fnm_map_fields['Any_foreclosure_in_the_past_3_years'] == 'Yes' ) ? 'Y' : 'N';

		$new_fanny->dec_delinquent                 = '';
		$new_fanny->dec_obligated_alimony          = '';
		$new_fanny->dec_down_payment_borrowed      = '';
		$new_fanny->dec_co_maker                   = '';
		$new_fanny->dec_residence_type             = '';
		$new_fanny->dec_property_primary_residence = '';
		$new_fanny->dec_ownership_interest         = '';

		$new_fanny->dec_type_of_property = $get_fannie_values['property_use2'];

		$new_fanny->dec_hold_title_to_the_home = '';
		$new_fanny->other_income_code          = '';
		$new_fanny->other_income_amount        = '';

		$sa = 1;
		if ( $fnm_map_fields['Property_Location'] != $fnm_map_fields['Current_Mailing_Address_Street'] ) {
			$sa = 0;
		}
		$new_fanny->same_mailing_address              = $sa;
		$new_fanny->co_applicant_same_mailing_address = $sa;
		$new_fanny->currently_own_real_estate         = '';

		$new_fanny->former_emp_data         = ( $fnm_map_fields['Employment_status'] == 'Employed' ) ? 'Y' : 'N';
		$new_fanny->applicant_dependent_age = $get_fannie_values['age'];
		$new_fanny->base_monthly_income     = $get_fannie_values['monthly_income'];

		$new_fanny->co_applicant_former_emp_data    = null;
		$new_fanny->employer_telephone              = null;
		$new_fanny->co_applicant_home_phone         = null;
		$new_fanny->co_applicant_employer_telephone = null;
		$new_fanny->former_emp_business_phone       = null;
		$new_fanny->co_former_emp_business_phone    = null;
		$new_fanny->co_applicant_ssn                = null;
		$new_fanny->asset_market_value              = null;

		$new_fanny->retirement_fund_cash_value          = null;
		$new_fanny->net_worth_business_owned            = null;
		$new_fanny->dec_us_citizen                      = null;
		$new_fanny->dec_permanent_resident              = null;
		$new_fanny->dec_us_citizen                      = null;
		$new_fanny->dec_permanent_resident              = null;
		$new_fanny->co_applicant_dec_us_citizen         = null;
		$new_fanny->retirement_fund_cash_value          = null;
		$new_fanny->net_worth_business_owned            = null;
		$new_fanny->dec_permanent_resident              = null;
		$new_fanny->co_applicant_dec_permanent_resident = null;
		$new_fanny->co_applicant_dec_permanent_resident = null;
		$new_fanny->is_there_co_borrower                = null;
		$new_fanny->other_income_amount_second_check    = null;
		$new_fanny->credit_score                        = $get_fannie_values['credit_rating'];
		$new_fanny->purchase_year                       = $get_fannie_values['purchase_year'];

		$new_fanny->present_value_of_lot = $get_fannie_values['home_value'];
		$new_fanny->improvent_cost       = $get_fannie_values['additional_funds'];

		$new_fanny->current_employment_flag = ( $fnm_map_fields['Employment_status'] == 'Employed' ) ? 'Y' : 'N';

		$new_fanny->type_of_property        = $get_fannie_values['home_description'];
		$new_fanny->creditor_name           = $get_fannie_values['loan_vendor'];
		$new_fanny->creditor_unpaid_balance = $get_fannie_values['mortgage_balance'];

		$new_fanny->lien_type_code = ( $fnm_map_fields['Do_you_have_a_2nd_mortgage'] == 'Yes' ) ? 2 : 1;

		// other_credit_type_code
		// amount_of_other_credit
		return $new_fanny;
	}
	public function get_user_data( $mortgage_application_form_fields, $post_id ) {
		$application_id = $post_id;
		$data_fanny     = array();
		$fannie_data    = array();
		// novalues
		$enc_iv = array();
		foreach ( $mortgage_application_form_fields as $form_field_key => $form_field_label ) {
			if ( isset( $form_field_key ) && ! empty( $form_field_key ) && $form_field_key == 'city_state' ) {
				$property_city  = get_post_meta( $application_id, 'property_city', true );
				$property_state = get_post_meta( $application_id, 'property_state', true );
				$field_data     = $property_city . ' ' . $property_state;
			} elseif ( isset( $form_field_key ) && ! empty( $form_field_key ) && $form_field_key == 'ss_number' ) {
				$encrypted_value = get_post_meta( $application_id, $form_field_key, true );

				$splota = explode( '::', $encrypted_value );
				// $this->pre_code($encrypted_value);
				list($encrypted_value, $enc_iv) = $splota;
				$cipher_method                  = 'aes-128-ctr';
				$enc_key                        = openssl_digest( php_uname(), 'SHA256', true );
				$decrypted_value                = openssl_decrypt( $encrypted_value, $cipher_method, $enc_key, 0, hex2bin( $enc_iv ) );
				$field_data                     = $decrypted_value;
			} else {
				$field_data = get_post_meta( $application_id, $form_field_key, true );
			}

			$replace          = array( ',', ' ', '?', '.', '(', ')' );
			$new              = array( '', '_', '', '' );
			$form_field_label = str_replace( $replace, $new, $form_field_label );
			$data_fanny['map_fields'][ $form_field_label ] = esc_attr( $field_data );
		}

		if ( ! empty( $this->fannie_fields ) ) {
			foreach ( $this->fannie_fields as $meta_key => $fannie_field ) {
				$field_id     = $fannie_field['field_id'];
				$field_id_arr = explode( '-', $field_id );
				if ( ! empty( $field_id_arr[0] ) ) {
					$row_id      = $field_id_arr[0];
					$field_value = '';

					$data_fanny['faaaields'][ $meta_key ] = $fannie_field;
					$data_fanny['fields'][ $meta_key ]    = get_post_meta( $application_id, $meta_key, true );

					if ( isset( $fannie_field['value'] ) && is_array( $fannie_field['value'] ) ) {
						$field_value = array_search( get_post_meta( $application_id, $meta_key, true ), $fannie_field['value'] );
					} elseif ( $meta_key === 'file_version' || $meta_key === 'social_security_numbers' || $meta_key === 'applicant_indicator' ) {
						$field_value = $fannie_field['value'];
					} elseif ( $meta_key === 'title_holder_name' ) {
						$first_name  = get_post_meta( $application_id, 'first_name', true );
						$last_name   = get_post_meta( $application_id, 'last_name', true );
						$field_value = ( isset( $first_name ) && isset( $last_name ) ? $first_name . ' ' . $last_name : '' );
					} elseif ( $meta_key === 'loan_amount' ) {
						$home_value   = get_post_meta( $application_id, 'purchase_price', true );
						$down_payment = get_post_meta( $application_id, 'down_payment', true );
						// $data_fanny['fannie_values'][111][] = $home_value;
						// $data_fanny['fannie_values'][1211][] = $down_payment;
						$field_value = ( ( isset( $home_value ) && isset( $down_payment ) && ! empty( $home_value ) && ! empty( $down_payment ) ) ? ( $home_value - $down_payment ) : 0 );
						$field_value = sprintf( '% ' . $fannie_field['field_length'] . '.' . $fannie_field['float_limit'] . 'f', $field_value );
					} elseif ( isset( $fannie_field['value'] ) && $fannie_field['value'] == 'flot_number' ) {
						$field_value = get_post_meta( $application_id, $meta_key, true );
						$field_value = sprintf( '% ' . $fannie_field['field_length'] . '.' . $fannie_field['float_limit'] . 'f', $field_value );
					} elseif ( isset( $fannie_field['value'] ) && $fannie_field['value'] == 'number' ) {
						$field_value = get_post_meta( $application_id, $meta_key, true );
						$field_value = sprintf( '% ' . $fannie_field['field_length'] . 'd', $field_value );
					} elseif ( isset( $fannie_field['value'] ) && $meta_key == 'ss_number' ) {
						$encrypted_value                = get_post_meta( $application_id, $meta_key, true );
						list($encrypted_value, $enc_iv) = explode( '::', $encrypted_value );
						$cipher_method                  = 'aes-128-ctr';
						$enc_key                        = openssl_digest( php_uname(), 'SHA256', true );
						$decrypted_value                = openssl_decrypt( $encrypted_value, $cipher_method, $enc_key, 0, hex2bin( $enc_iv ) );
						$field_value                    = $decrypted_value;
					} else {
						$field_value = get_post_meta( $application_id, $meta_key, true );
					}

					switch ( $meta_key ) {
						case 'bankruptcy':
							$val         = ( $data_fanny['map_fields']['Any_bankruptcy_in_the_past_7_years'] == 'Yes' ) ? 'Y' : 'N';
							$field_value = $val;

							break;
						case 'second_mortgage':
							$val         = ( $data_fanny['map_fields']['Do_you_have_a_2nd_mortgage'] == 'Yes' ) ? 2 : 1;
							$field_value = $val;

							break;
						case 'property_use2':
							$val         = ( $data_fanny['map_fields']['Do_you_have_a_2nd_mortgage'] == 'Yes' ) ? 2 : 1;
							$field_value = $val;

							break;

						case 'foreclosure':
							$val         = ( $data_fanny['map_fields']['Any_foreclosure_in_the_past_3_years'] == 'Yes' ) ? 2 : 1;
							$field_value = $val;
							break;

						case 'employment_status':
							$val         = ( $data_fanny['map_fields']['Employment_status'] == 'Employed' ) ? 'Y' : 'N';
							$field_value = $val;
							break;

						case 'rate_type':
						case 'desired_rate_type':
							if ( $data_fanny['map_fields']['Existing_Type_of_Rate'] == 'Fixed' ) {
								$amortization_type = '05';
							} elseif ( $data_fanny['map_fields']['Existing_Type_of_Rate'] == 'Adjustable' ) {
								$amortization_type = '01';
							} else {
								$amortization_type = '';
							}
							$field_value = $amortization_type;
							break;
						default:
					}

					$data_fanny['fannie_values'][ $meta_key ]              = $field_value;
					$data_fanny['fannie_row'][ $row_id . '_' . $meta_key ] = $field_value;

					if ( isset( $fannie_data[ $row_id ] ) && $fannie_data[ $row_id ] != '' ) {
						$fannie_data[ $row_id ] = str_pad( $fannie_data[ $row_id ], ( $fannie_field['position'] - 1 ) );
						$fannie_data[ $row_id ] = $fannie_data[ $row_id ] . $field_value;
						// $fannie_data[$row_id] = str_pad($fannie_data[$row_id], ($fannie_field['position'] +               $fannie_field['field_length']),  $field_value);
					} else {
						$fannie_data[ $row_id ] = str_pad( $row_id, ( $fannie_field['position'] - 1 ) );
						$fannie_data[ $row_id ] = $fannie_data[ $row_id ] . $field_value;
					}
				}
			}
		}
		$data_fanny['fannie_data'] = $fannie_data;
		return $data_fanny;
	}
}
