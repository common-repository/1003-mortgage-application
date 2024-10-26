<?php

// https://my.outsystemscloud.com/FannieMaeReader/HomePage.aspx
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
class mapp_exportMortgageApplicationsMismo extends mapp_exportMortgageApplicationsBase {

	private $mismo_fields;
	private $usStates;
	public function __construct() {
		parent::__construct();
		global $mortgage_application_fannie_fields;
		$this->mismo_fields = $mortgage_application_fannie_fields;
		$this->usStates     = $this->getUsStates();
	}
	public function create_file( $folder_name, $data ) {
		$generatedDate   = gmdate( 'd-m-Y-His' );
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

				$status     = esc_attr( get_post_meta( $application_id, 'application_status', true ) );
				$mismo_data = $this->get_user_data( $mortgage_application_form_fields, $post_id );

				$mismo_data['mismo_values']['application_id'] = $post_id;
				$mismo_data['mismo_values']['typeOfLoan']     = $mismo_data['map_fields']['Type_of_Loan'];

				if ( isset( $mismo_data['mismo_values']['mailing_address'] ) && ! empty( $mismo_data['mismo_values']['mailing_address'] ) ) {
					// $addressNew = $this->geocode($mismo_data['mismo_values']['mailing_address']);
					$addressNew                                        = get_post_meta( $application_id, 'mapp_geocoded_address', true );
					$mismo_data['mismo_values']['mailing_address_all'] = $addressNew;
					$mismo_data['mismo_values']['mailing_address_format'] = $this->getAddressNew( $addressNew, $mismo_data, 'mailing_address' );
				}
				if ( isset( $mismo_data['mismo_values']['new_address'] ) && ! empty( $mismo_data['mismo_values']['new_address'] ) ) {
					// $addressNew = $this->geocode($mismo_data['mismo_values']['new_address']);
					$addressNew                                       = get_post_meta( $application_id, 'mapp_geocoded_new_address', true );
					$mismo_data['mismo_values']['new_address_format'] = $this->getAddressNew( $addressNew, $mismo_data, 'new_address' );
					$mismo_data['mismo_values']['new_address_all']    = $addressNew;
				}
				$file_name = sanitize_title( wp_strip_all_tags( get_post_meta( $application_id, 'email', true ) ) );
				$xmlData   = array(
					'data' => $mismo_data,
					'name' => $file_name . '-' . $generatedDate . '-' . $application_id . '.xml',
					'dir'  => $upload_dir_path,
				);

				$create_xml = $this->create_xml( $xmlData );
				// $this->pre_code($create_xml);
			} // en foreach
		} // en if
		$rootPath = realpath( $upload_dir_path );
		$zip_url  = $this->create_zip( $rootPath );
		$this->download_zip( $zip_url );
		// die();
	}
	public function getAddressNew( $address, $data, $type ) {
		$mismo                   = $data['mismo_values'];
		$new_address             = array();
		$new_address['address']  = ! empty( $address['address']['road'] ) ? $address['address']['house_number'] . ' ' . $address['address']['road'] : $address[0];
		$singleCityZip           = explode( ' ', $address[2] );
		$new_address['state']    = ! empty( $address['address']['state'] ) ? array_search( $address['address']['state'], $this->usStates ) : $singleCityZip[0];
		$new_address['city']     = ! empty( $address['address']['city'] ) ? $address['address']['city'] : $address['address']['town'];
		$new_address['city']     = ! empty( $new_address['city'] ) ? $new_address['city'] : $address[1];
		$new_address['postcode'] = ! empty( $address['address']['postcode'] ) ? $address['address']['postcode'] : $singleCityZip[1];
		$new_address['county']   = ! empty( $address['address']['county'] ) ? $address['address']['county'] : $new_address['city'];
		// $new_address['address'] = !empty($address['address']['road']) ? $address['address']['house_number'].' '.$address['address']['road'] : $address[0];
		$id          = $mismo['application_id'];
		$addressType = 'property_location_';
		if ( $type == 'new_address' ) {
			$addressType = 'mailing_address_';
		}
		$new_address['address'] = ! empty( $this->returnMetaValue( $id, $addressType . 'route_long_name' ) ) ? $this->returnMetaValue( $id, $addressType . 'street_number_long_name' ) . ' ' . $this->returnMetaValue( $id, $addressType . 'route_long_name' ) : $new_address['address'];

		$new_address['city'] = ! empty( $this->returnMetaValue( $id, $addressType . 'locality_long_name' ) ) ? $this->returnMetaValue( $id, $addressType . 'locality_long_name' ) : $new_address['city'];

		$new_address['postcode'] = ! empty( $this->returnMetaValue( $id, $addressType . 'postal_code_long_name' ) ) ? $this->returnMetaValue( $id, $addressType . 'postal_code_long_name' ) : $new_address['postcode'];

		$new_address['state'] = ! empty( $this->returnMetaValue( $id, $addressType . 'administrative_area_level_1_long_name' ) ) ? array_search( $this->returnMetaValue( $id, $addressType . 'administrative_area_level_1_long_name' ), $this->usStates ) : $new_address['state'];

		$new_address['county'] = ! empty( $this->returnMetaValue( $id, $addressType . 'administrative_area_level_2_long_name' ) ) ? $this->returnMetaValue( $id, $addressType . 'administrative_area_level_2_long_name' ) : $new_address['county'];

		return $new_address;
	}

	// public function geocode($address)
	// {
	// if (is_array($address) || empty($address)) {
	// return $address;
	// }

	// $encodedAddress = urlencode($address);
	// if (empty($encodedAddress) || $encodedAddress == '%0A') {
	// return $address;
	// }
	// $url = "https://nominatim.openstreetmap.org/search?addressdetails=1&q={$encodedAddress}&format=json&limit=1";

	// $opts = [
	// 'http' => [
	// 'header' => "User-Agent: MarcosWorld 3.7.6\r\n"
	// ]
	// ];
	// $context = stream_context_create($opts);

	// $responseJson = file_get_contents($url, false, $context);
	// $response = json_decode($responseJson, true);

	// if (isset($response[0])) {
	// $geocodedAddress = $response[0];

	// foreach ($geocodedAddress['address'] as $key => $val) {
	// $geocodedAddress[$key] = $val;
	// }

	// unset($geocodedAddress['address']);
	// return $geocodedAddress;
	// } else {
	// $addressArray = explode(', ', $address);
	// return $this->geocode($addressArray);
	// }
	// }

	public function create_xml( $data ) {

		$mismo_values  = $data['data']['mismo_values'];
		$xmlFile       = simplexml_load_file( MAPP_MORTGAGE_APP_BASE_PATH . 'inc/class/fnm/mismo.xml' ) or die( 'Error: Cannot create object' );
		$generatedDate = gmdate( 'Y-m-d\TH:i:s\Z' );
		foreach ( $xmlFile->children() as $child ) {
			$section_name = $child->getName();
			// echo $child->getName() . ": " . $child . "<br>";
			switch ( $section_name ) {
				case 'ABOUT_VERSIONS':
					$xmlFile->ABOUT_VERSIONS->ABOUT_VERSION->CreatedDatetime = $generatedDate;
					break;
				case 'DEAL_SETS':
					$assets      = $child->DEAL_SET->DEALS->DEAL->ASSETS;
					$collaterals = $child->DEAL_SET->DEALS->DEAL->COLLATERALS;
					$loans       = $child->DEAL_SET->DEALS->DEAL->LOANS;
					$parties     = $child->DEAL_SET->DEALS->DEAL->PARTIES->PARTY;
					if ( ! empty( $mismo_values['loan_vendor'] ) ) {
						// $doc = $xmlFile->addChild("doc"); // add <doc></doc>
						$assets->ASSET->ASSET_HOLDER->NAME->FullName = $mismo_values['loan_vendor'];
					}
					$collaterals->COLLATERAL->SUBJECT_PROPERTY->ADDRESS->AddressLineText                      = $mismo_values['mailing_address_format']['address'];
					$collaterals->COLLATERAL->SUBJECT_PROPERTY->ADDRESS->CityName                             = $mismo_values['mailing_address_format']['city'];
					$collaterals->COLLATERAL->SUBJECT_PROPERTY->ADDRESS->CountyName                           = $mismo_values['mailing_address_format']['county'];
					$collaterals->COLLATERAL->SUBJECT_PROPERTY->ADDRESS->PostalCode                           = $mismo_values['mailing_address_format']['postcode'];
					$collaterals->COLLATERAL->SUBJECT_PROPERTY->ADDRESS->StateCode                            = $mismo_values['mailing_address_format']['state'];
					$collaterals->COLLATERAL->SUBJECT_PROPERTY->PROPERTY_DETAIL->PropertyEstimatedValueAmount = $mismo['home_value'];

					$collaterals->COLLATERAL->SUBJECT_PROPERTY->PROPERTY_VALUATIONS->PROPERTY_VALUATION->PROPERTY_VALUATION_DETAIL->PropertyValuationAmount = $mismo['home_value'];

					$loans->LOAN->AMORTIZATION->AMORTIZATION_RULE->AmortizationType                                = $mismo_values['desired_rate_type'];
					$loans->LOAN->CLOSING_INFORMATION->CLOSING_INFORMATION_DETAIL->CashFromBorrowerAtClosingAmount = $mismo_values['loan_amount'];
					// LOAN_DETAIL
					// $loans->LOAN->HOUSING_EXPENSES->HOUSING_EXPENSE->HousingExpensePaymentAmount = '';
					// $loans->LOAN->HOUSING_EXPENSES->HOUSING_EXPENSE->HousingExpenseTimingType = '';
					// $loans->LOAN->HOUSING_EXPENSES->HOUSING_EXPENSE->HousingExpenseType = '';
					// TERMS_OF_LOAN
					$loans->LOAN->TERMS_OF_LOAN->BaseLoanAmount  = $mismo_values['loan_amount'];
					$loans->LOAN->TERMS_OF_LOAN->LoanPurposeType = ( $mismo_values['typeOfLoan'] == 'Home Purchase' ) ? 'Purchase' : 'Refinance';
					// $loans->LOAN->TERMS_OF_LOAN->MortgageType = '';
					$parties[0]->INDIVIDUAL->NAME->FirstName = $mismo_values['first_name'];
					$parties[0]->INDIVIDUAL->NAME->LastName  = $mismo_values['last_name'];
					$parties[0]->INDIVIDUAL->CONTACT_POINTS->CONTACT_POINT[0]->CONTACT_POINT_TELEPHONE->ContactPointTelephoneValue = $mismo_values['phone_number'];

					$parties[0]->INDIVIDUAL->CONTACT_POINTS->CONTACT_POINT[0]->CONTACT_POINT_DETAIL->ContactPointRoleType = 'Home';

					$parties[0]->INDIVIDUAL->CONTACT_POINTS->CONTACT_POINT[1]->CONTACT_POINT_EMAIL->ContactPointEmailValue = $mismo_values['email'];

					$originalDate = $mismo_values['dob'];
					$newDate      = gmdate( 'Y-m-d', strtotime( $originalDate ) );
					// update date format
					$parties[0]->ROLES->ROLE->BORROWER->BORROWER_DETAIL->BorrowerBirthDate = $newDate;

					$parties[0]->ROLES->ROLE->BORROWER->CURRENT_INCOME->CURRENT_INCOME_ITEMS->CURRENT_INCOME_ITEM->CURRENT_INCOME_ITEM_DETAIL->CurrentIncomeMonthlyTotalAmount = $mismo_values['monthly_income'];

					$parties[0]->ROLES->ROLE->BORROWER->CURRENT_INCOME->CURRENT_INCOME_ITEMS->CURRENT_INCOME_ITEM->CURRENT_INCOME_ITEM_DETAIL->EmploymentIncomeIndicator = 'true';

					$parties[0]->ROLES->ROLE->BORROWER->CURRENT_INCOME->CURRENT_INCOME_ITEMS->CURRENT_INCOME_ITEM->CURRENT_INCOME_ITEM_DETAIL->IncomeType = 'Base';

					$parties[0]->ROLES->ROLE->BORROWER->DECLARATION->DECLARATION_DETAIL->BankruptcyIndicator = ( $mismo_values['bankruptcy'] == 'Yes' ) ? 'true' : 'false';

					$parties[0]->ROLES->ROLE->ROLE_DETAIL->PartyRoleType = 'Borrower';
					// $party->ROLES->ROLE->BORROWER->DECLARATION->DECLARATION_DETAIL->HomeownerPastThreeYearsType = 'HomeownerPastThreeYearsType';
					// $party->ROLES->ROLE->BORROWER->EMPLOYERS->EMPLOYER = '';

					$parties[0]->ROLES->ROLE->BORROWER->RESIDENCES->RESIDENCE->ADDRESS->AddressLineText = $mismo_values['new_address_format']['address'];
					$parties[0]->ROLES->ROLE->BORROWER->RESIDENCES->RESIDENCE->ADDRESS->CityName        = $mismo_values['new_address_format']['city'];
					$parties[0]->ROLES->ROLE->BORROWER->RESIDENCES->RESIDENCE->ADDRESS->PostalCode      = $mismo_values['new_address_format']['postcode'];
					$parties[0]->ROLES->ROLE->BORROWER->RESIDENCES->RESIDENCE->ADDRESS->StateCode       = $mismo_values['new_address_format']['state'];
					// $party->ROLES->ROLE->BORROWER->RESIDENCES->RESIDENCE->LANDLORD->LANDLORD_DETAIL->MonthlyRentAmount = '';
					// $party->ROLES->ROLE->BORROWER->RESIDENCES->RESIDENCE->RESIDENCE_DETAIL->BorrowerResidencyBasisType = '';
					$parties[0]->TAXPAYER_IDENTIFIERS->TAXPAYER_IDENTIFIER->TaxpayerIdentifierType  = 'SocialSecurityNumber';
					$parties[0]->TAXPAYER_IDENTIFIERS->TAXPAYER_IDENTIFIER->TaxpayerIdentifierValue = str_replace( '-', '', $mismo_values['ss_number'] );
					// $parties->
					// HOUSING_EXPENSES
					// $loans->LOAN->LOAN_DETAIL-> = '';
					break;
				case 'DOCUMENT_SETS':
					$xmlFile->DOCUMENT_SETS->DOCUMENT_SET->DOCUMENTS->DOCUMENT->SIGNATORIES->SIGNATORY[0]->EXECUTION->EXECUTION_DETAIL->ExecutionDate = $generatedDate;
					$xmlFile->DOCUMENT_SETS->DOCUMENT_SET->DOCUMENTS->DOCUMENT->SIGNATORIES->SIGNATORY[1]->EXECUTION->EXECUTION_DETAIL->ExecutionDate = $generatedDate;

					break;
				default:
					// echo "Your favorite color is neither red, blue, nor green!";
			}
			// enf dor each
		}
		$xmlFile->asXml( $data['dir'] . '' . $data['name'] );

		return $xmlFile;
	}

	public function pre_code( $array ) {
		echo '<pre>';
		print_r( $array );
		echo '</pre>';
	}

	public function get_user_data( $mortgage_application_form_fields, $post_id ) {
		$application_id = $post_id;
		$data_mismo     = array();
		$mismo_data     = array();
		// novalues
		$enc_iv = array();

		$data_mismo['mismo_values']['mailing_address_administrative_area_level_1_long_name']  = $this->returnMetaValue( $application_id, 'mailing_address_administrative_area_level_1_long_name' );
		$data_mismo['mismo_values']['mailing_address_administrative_area_level_2_long_name']  = $this->returnMetaValue( $application_id, 'mailing_address_administrative_area_level_2_long_name' );
		$data_mismo['mismo_values']['mailing_address_administrative_area_level_2_long_name']  = $this->returnMetaValue( $application_id, 'mailing_address_administrative_area_level_2_long_name' );
		$data_mismo['mismo_values']['mailing_address_administrative_area_level_2_short_name'] = $this->returnMetaValue( $application_id, 'mailing_address_administrative_area_level_2_short_name' );
		$data_mismo['mismo_values']['mailing_address_country_long_name']                      = $this->returnMetaValue( $application_id, 'mailing_address_country_long_name' );
		$data_mismo['mismo_values']['mailing_address_country_short_name']                     = $this->returnMetaValue( $application_id, 'mailing_address_country_short_name' );
		$data_mismo['mismo_values']['mailing_address_locality_long_name']                     = $this->returnMetaValue( $application_id, 'mailing_address_locality_long_name' );
		$data_mismo['mismo_values']['mailing_address_locality_short_name']                    = $this->returnMetaValue( $application_id, 'mailing_address_locality_short_name' );
		$data_mismo['mismo_values']['mailing_address_neighborhood_long_name']                 = $this->returnMetaValue( $application_id, 'mailing_address_neighborhood_long_name' );
		$data_mismo['mismo_values']['mailing_address_neighborhood_short_name']                = $this->returnMetaValue( $application_id, 'mailing_address_neighborhood_short_name' );
		$data_mismo['mismo_values']['mailing_address_postal_code_long_name']                  = $this->returnMetaValue( $application_id, 'mailing_address_postal_code_long_name' );
		$data_mismo['mismo_values']['mailing_address_postal_code_short_name']                 = $this->returnMetaValue( $application_id, 'mailing_address_postal_code_short_name' );
		// $data_mismo['mismo_values']['mailing_address_long_name'] = get_post_meta($application_id, 'mailing_address_postal_code_suffix_long_name', true);
		// $data_mismo['mismo_values']['mailing_address_long_name'] = get_post_meta($application_id, 'mailing_address_postal_code_suffix_short_name', true);
		$data_mismo['mismo_values']['mailing_address_route_long_name']                          = $this->returnMetaValue( $application_id, 'mailing_address_route_long_name' );
		$data_mismo['mismo_values']['mailing_address_route_short_name']                         = $this->returnMetaValue( $application_id, 'mailing_address_route_short_name' );
		$data_mismo['mismo_values']['mailing_address_street_number_long_name']                  = $this->returnMetaValue( $application_id, 'mailing_address_street_number_long_name' );
		$data_mismo['mismo_values']['mailing_address_street_number_short_name']                 = $this->returnMetaValue( $application_id, 'mailing_address_street_number_short_name' );
		$data_mismo['mismo_values']['property_location_administrative_area_level_1_long_name']  = $this->returnMetaValue( $application_id, 'property_location_administrative_area_level_1_long_name' );
		$data_mismo['mismo_values']['property_location_administrative_area_level_2_long_name']  = $this->returnMetaValue( $application_id, 'property_location_administrative_area_level_2_long_name' );
		$data_mismo['mismo_values']['property_location_administrative_area_level_2_long_name']  = $this->returnMetaValue( $application_id, 'property_location_administrative_area_level_2_long_name' );
		$data_mismo['mismo_values']['property_location_administrative_area_level_2_short_name'] = $this->returnMetaValue( $application_id, 'property_location_administrative_area_level_2_short_name' );
		$data_mismo['mismo_values']['property_location_country_long_name']                      = $this->returnMetaValue( $application_id, 'property_location_country_long_name' );
		$data_mismo['mismo_values']['property_location_country_short_name']                     = $this->returnMetaValue( $application_id, 'property_location_country_short_name' );
		$data_mismo['mismo_values']['property_location_locality_long_name']                     = $this->returnMetaValue( $application_id, 'property_location_locality_long_name' );
		$data_mismo['mismo_values']['property_location_locality_short_name']                    = $this->returnMetaValue( $application_id, 'property_location_locality_short_name' );
		$data_mismo['mismo_values']['property_location_neighborhood_long_name']                 = $this->returnMetaValue( $application_id, 'property_location_neighborhood_long_name' );
		$data_mismo['mismo_values']['property_location_neighborhood_short_name']                = $this->returnMetaValue( $application_id, 'property_location_neighborhood_short_name' );
		$data_mismo['mismo_values']['property_location_postal_code_long_name']                  = $this->returnMetaValue( $application_id, 'property_location_postal_code_long_name' );
		$data_mismo['mismo_values']['property_location_postal_code_short_name']                 = $this->returnMetaValue( $application_id, 'property_location_postal_code_short_name' );
		// $data_mismo['mismo_values']['property_location_long_name'] = get_post_meta($application_id, 'property_location_postal_code_suffix_long_name', true);
		// $data_mismo['mismo_values']['property_location_long_name'] = get_post_meta($application_id, 'property_location_postal_code_suffix_short_name', true);
		$data_mismo['mismo_values']['property_location_route_long_name']          = $this->returnMetaValue( $application_id, 'property_location_route_long_name' );
		$data_mismo['mismo_values']['property_location_route_short_name']         = $this->returnMetaValue( $application_id, 'property_location_route_short_name' );
		$data_mismo['mismo_values']['property_location_street_number_long_name']  = $this->returnMetaValue( $application_id, 'property_location_street_number_long_name' );
		$data_mismo['mismo_values']['property_location_street_number_short_name'] = $this->returnMetaValue( $application_id, 'property_location_street_number_short_name' );

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
			$data_mismo['map_fields'][ $form_field_label ] = esc_attr( $field_data );

			$data_mismo['map_fields']['new_address'] = get_post_meta( $application_id, 'zip_code', true );
		}

		if ( ! empty( $this->mismo_fields ) ) {
			foreach ( $this->mismo_fields as $meta_key => $mismo_field ) {
				$field_id     = $mismo_field['field_id'];
				$field_id_arr = explode( '-', $field_id );
				if ( ! empty( $field_id_arr[0] ) ) {
					$row_id      = $field_id_arr[0];
					$field_value = '';

					// $data_mismo['faaaields'][$meta_key] = $mismo_field;
					$data_mismo['fields'][ $meta_key ] = get_post_meta( $application_id, $meta_key, true );

					if ( isset( $mismo_field['value'] ) && is_array( $mismo_field['value'] ) ) {
						$field_value = array_search( get_post_meta( $application_id, $meta_key, true ), $mismo_field['value'] );
					} elseif ( $meta_key === 'file_version' || $meta_key === 'social_security_numbers' || $meta_key === 'applicant_indicator' ) {
						$field_value = $mismo_field['value'];
					} elseif ( $meta_key === 'title_holder_name' ) {
						$first_name  = get_post_meta( $application_id, 'first_name', true );
						$last_name   = get_post_meta( $application_id, 'last_name', true );
						$field_value = ( isset( $first_name ) && isset( $last_name ) ? $first_name . ' ' . $last_name : '' );
					} elseif ( $meta_key === 'loan_amount' ) {
						$home_value   = get_post_meta( $application_id, 'purchase_price', true );
						$down_payment = get_post_meta( $application_id, 'down_payment', true );
						// $data_mismo['mismo_values'][111][] = $home_value;
						// $data_mismo['mismo_values'][1211][] = $down_payment;
						$field_value = ( ( isset( $home_value ) && isset( $down_payment ) && ! empty( $home_value ) && ! empty( $down_payment ) ) ? ( $home_value - $down_payment ) : 0 );
						$field_value = sprintf( '% ' . $mismo_field['field_length'] . '.' . $mismo_field['float_limit'] . 'f', $field_value );
					} elseif ( isset( $mismo_field['value'] ) && $mismo_field['value'] == 'flot_number' ) {
						$field_value = get_post_meta( $application_id, $meta_key, true );
						$field_value = sprintf( '% ' . $mismo_field['field_length'] . '.' . $mismo_field['float_limit'] . 'f', $field_value );
					} elseif ( isset( $mismo_field['value'] ) && $mismo_field['value'] == 'number' ) {
						$field_value = get_post_meta( $application_id, $meta_key, true );
						$field_value = sprintf( '% ' . $mismo_field['field_length'] . 'd', $field_value );
					} elseif ( isset( $mismo_field['value'] ) && $meta_key == 'ss_number' ) {
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
							$val         = ( $data_mismo['map_fields']['Any_bankruptcy_in_the_past_7_years'] == 'Yes' ) ? 'Yes' : 'No';
							$field_value = $val;

							break;
						case 'second_mortgage':
							$val         = ( $data_mismo['map_fields']['Do_you_have_a_2nd_mortgage'] == 'Yes' ) ? 'Yes' : 'No';
							$field_value = $val;

							break;
						case 'property_use2':
							$val         = ( $data_mismo['map_fields']['Do_you_have_a_2nd_mortgage'] == 'Yes' ) ? 'Yes' : 'No';
							$field_value = $val;

							break;

						case 'foreclosure':
							$val         = ( $data_mismo['map_fields']['Any_foreclosure_in_the_past_3_years'] == 'Yes' ) ? 'Yes' : 'No';
							$field_value = $val;
							break;

						case 'employment_status':
							$val         = ( $data_mismo['map_fields']['Employment_status'] == 'Employed' ) ? 'Y' : 'N';
							$field_value = $val;
							break;

						case 'rate_type':
						case 'desired_rate_type':
							if ( $data_mismo['map_fields']['Existing_Type_of_Rate'] == 'Fixed' ) {
								$amortization_type = '05';
							} elseif ( $data_mismo['map_fields']['Existing_Type_of_Rate'] == 'Adjustable' ) {
								$amortization_type = '01';
							} else {
								$amortization_type = '';
							}
							$field_value = $amortization_type;
							break;
						default:
					}
					$data_mismo['mismo_values'][ $meta_key ] = $field_value;
					// $data_mismo['mismo_row'][$row_id.'_'.$meta_key] = $field_value;
					if ( isset( $mismo_data[ $row_id ] ) && $mismo_data[ $row_id ] != '' ) {
						$mismo_data[ $row_id ] = str_pad( $mismo_data[ $row_id ], ( $mismo_field['position'] - 1 ) );
						$mismo_data[ $row_id ] = $mismo_data[ $row_id ] . $field_value;
						// $mismo_data[$row_id] = str_pad($mismo_data[$row_id], ($mismo_field['position'] +               $mismo_field['field_length']),  $field_value);
					} else {
						$mismo_data[ $row_id ] = str_pad( $row_id, ( $mismo_field['position'] - 1 ) );
						$mismo_data[ $row_id ] = $mismo_data[ $row_id ] . $field_value;
					}
					$data_mismo['mismo_values']['new_address']       = get_post_meta( $application_id, 'zip_code', true );
					$data_mismo['mismo_values']['rate_type']         = $data_mismo['fields']['rate_type'];
					$data_mismo['mismo_values']['desired_rate_type'] = ! isset( $data_mismo['fields']['desired_rate_type'] ) ? $data_mismo['fields']['desired_rate_type'] : '';
					$data_mismo['mismo_values']['purpose']           = $data_mismo['fields']['purpose'];
					$data_mismo['mismo_values']['property_use']      = $data_mismo['fields']['property_use'];
					$data_mismo['mismo_values']['employment_status'] = $data_mismo['fields']['employment_status'];
					$data_mismo['mismo_values']['home_description']  = $data_mismo['fields']['home_description'];
				}
			}
		}
		// $data_mismo['mismo_data'] = $mismo_data;
		return $data_mismo;
	}
	public function getUsStates() {
		return array(
			'AL' => 'Alabama',
			'AK' => 'Alaska',
			'AZ' => 'Arizona',
			'AR' => 'Arkansas',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DE' => 'Delaware',
			'DC' => 'District Of Columbia',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'HI' => 'Hawaii',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'IA' => 'Iowa',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'ME' => 'Maine',
			'MD' => 'Maryland',
			'MA' => 'Massachusetts',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MS' => 'Mississippi',
			'MO' => 'Missouri',
			'MT' => 'Montana',
			'NE' => 'Nebraska',
			'NV' => 'Nevada',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NY' => 'New York',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PA' => 'Pennsylvania',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VT' => 'Vermont',
			'VA' => 'Virginia',
			'WA' => 'Washington',
			'WV' => 'West Virginia',
			'WI' => 'Wisconsin',
			'WY' => 'Wyoming',
		);
	}
	public function returnMetaValue( $id, $name ) {
		$meta = get_post_meta( $id, $name, true );
		return ! empty( $meta ) ? $meta : '';
	}
}
