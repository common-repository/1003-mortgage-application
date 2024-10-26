<?php
/**
 * This file is responsible to bootstrap the plugin. It also responsible to load only that particular section's files/code which is being used right now.
 *
 * @link       https://lenderd.com
 * @since      1.0.0
 *
 * @package    mortgage_application
 */

// If this file is called directly, abort.
defined( 'ABSPATH' ) or die( 'Access denied!' );
define( 'SSN_ENCRYPTION_KEY', '__^%&Q@$&*!@#$%^&*^__' );

global $mortgage_application_form_fields, $mortgage_application_required_form_fields, $mortgage_application_fannie_fields;

// Define form (post meta)fields and form label
$mortgage_application_form_fields = array(
	'purpose'               => 'Type of Loan',
	'home_description'      => 'Home Description',
	'credit_rating'         => 'Your Credit Profile',
	'property_use'          => 'Property Use',
	'zip_code'              => 'Property Location',
	// 'address' => 'Address',
	// 'city' => 'City',
	// 'state' => 'State',
	// 'zip_code_only' => 'Zip Code',
	'purchase_year'         => 'Purchase Year',
	'first_time_buyer'      => 'Are you a first-time home buyer?',
	'loan_purpose_purchase' => 'When Do You Plan to Purchase',
	// 'property_use2' => 'Home will be used for',
	'purchase_price'        => 'Purchase price of the new home',
	'down_payment'          => 'Estimated Down Payment',
	'desired_rate_type'     => 'Desired Type of Rate',
	'home_value'            => 'Estimated Home Value',
	'mortgage_balance'      => '1st Mortgage Balance',
	'loan_interest_rate'    => '1st Mortgage Interest Rate',
	'loan_vendor'           => '1st Mortgage With',
	'rate_type'             => 'Existing Type of Rate',
	'second_mortgage'       => 'Do you have a 2nd mortgage?',
	'additional_funds'      => 'How much additional cash do you wish to borrow?',
	'age'                   => 'How old are you?',
	'reverse_mortgage'      => 'Can we interest you in a reverse mortgage?',
	'refinanced_before'     => 'Have you ever refinanced before?',
	'employment_status'     => 'Employment status',
	'late_payments'         => 'Number of late mortgage payments in the past 12 months?',
	'bankruptcy'            => 'Any bankruptcy in the past 7 years?',
	'has_FHA'               => 'Do you currently have an FHA loan?',
	'foreclosure'           => 'Any foreclosure in the past 3 years?',
	'monthly_income'        => 'Income Amount (Monthly Income)',
	'proof_of_income'       => 'Can you show proof of your income?',
	'mailing_address'       => 'Current Mailing Address Street',
	// 'city_state' => 'City / State',
	'email'                 => 'Email Address',
	'cash_out_box'          => 'Cash-Out Amount',
	'military'              => 'Active or previous U.S. military service?',
	'agent_contact'         => 'Would you like to be contacted by a real estate agent in your area?',
	'use_va_loans'          => 'Are you or your spouse currently in a VA loan?',
	'first_name'            => 'First Name',
	'last_name'             => 'Last Name',
	'phone_number'          => 'Phone Number',
	'dob'                   => 'Date of Birth',
	'ss_number'             => 'Social Security Number',
);


// define application required fields
$mortgage_application_required_form_fields = array(
	'purpose',
	'home_description',
	'credit_rating',
	'property_use',
	'zip_code',
	'purchase_price',
	'down_payment',
	'desired_rate_type',
	'purchase_year',
	'home_value',
	'additional_funds',
	'employment_status',
	'email',
	'bankruptcy',
	'first_name',
	'last_name',
	'phone_number',
);

// Define fannie file fields and value
$mortgage_application_fannie_fields = array(
	'file_version'        => array(
		'field_id'     => '000-030',
		'position'     => 7,
		'field_length' => 5,
		'value'        => '3.20',
	),
	'loan_amount'         => array(
		'field_id'     => '01A-060',
		'position'     => 131,
		'field_length' => 15,
		'value'        => 'flot_number',
		'float_limit'  => 2,
	),
	'loan_interest_rate'  => array(
		'field_id'     => '01A-070',
		'position'     => 146,
		'field_length' => 7,
		'value'        => 'flot_number',
		'float_limit'  => 3,
	),
	'rate_type'           => array(
		'field_id'     => '01A-090',
		'position'     => 156,
		'field_length' => 2,
		'value'        => array(
			'01' => 'adjustable',
			'05' => 'fixed',
		),
	),
	'desired_rate_type'   => array(
		'field_id'     => '01A-090',
		'position'     => 156,
		'field_length' => 2,
		'value'        => array(
			'01' => 'adjustable',
			'05' => 'fixed',
		),
	),
	'mailing_address'     => array(
		'field_id'     => '02A-020',
		'position'     => 4,
		'field_length' => 50,
		'value'        => 'value',
	),
	'city'                => array(
		'field_id'     => '02A-030',
		'position'     => 54,
		'field_length' => 35,
		'value'        => 'value',
	),
	'state'               => array(
		'field_id'     => '02A-040',
		'position'     => 89,
		'field_length' => 2,
		'value'        => 'value',
	),

	'zip_code_only'       => array(
		'field_id'     => '02A-050',
		'position'     => 91,
		'field_length' => 5,
		'value'        => 'value',
	),
	'purpose'             => array(
		'field_id'     => '02B-030',
		'position'     => 6,
		'field_length' => 2,
		'value'        => array(
			'05' => 'Home Refinance',
			'16' => 'Home Purchase',
		),
	),
	'property_use'        => array(
		'field_id'     => '02B-050',
		'position'     => 88,
		'field_length' => 1,
		'value'        => array(
			'1' => 'Primary Residence',
			'2' => 'Secondary Home',
			'D' => 'Investment Property',
		),
	),
	'title_holder_name'   => array(
		'field_id'     => '02C-020',
		'position'     => 4,
		'field_length' => 60,
		'value'        => 'value',
	),
	'purchase_year'       => array(
		'field_id'     => '02D-020',
		'position'     => 4,
		'field_length' => 4,
		'value'        => 'value',
	),
	'home_value'          => array(
		'field_id'     => '02D-050',
		'position'     => 38,
		'field_length' => 15,
		'value'        => 'flot_number',
		'float_limit'  => 2,
	),
	'additional_funds'    => array(
		'field_id'     => '02D-100',
		'position'     => 151,
		'field_length' => 15,
		'value'        => 'flot_number',
		'float_limit'  => 2,
	),
	'down_payment'        => array(
		'field_id'     => '02E-030',
		'position'     => 6,
		'field_length' => 15,
		'value'        => 'flot_number',
		'float_limit'  => 2,
	),
	'applicant_indicator' => array(
		'field_id'     => '03A-020',
		'position'     => 4,
		'field_length' => 2,
		'value'        => 'BW',
	),
	/*
												'social_security_numbers' => array(
																		'field_id'=> '03A-030',
																		'position'=> 6,
																		'field_length'=> 9,
																		'value' => '000000000'
																	),*/
												'first_name' => array(
													'field_id' => '03A-040',
													'position' => 15,
													'field_length' => 35,
													'value' => 'value',
												),
	'last_name'           => array(
		'field_id'     => '03A-060',
		'position'     => 85,
		'field_length' => 35,
		'value'        => 'value',
	),
	'phone_number'        => array(
		'field_id'     => '03A-080',
		'position'     => 124,
		'field_length' => 10,
		'value'        => 'value',
	),
	'age'                 => array(
		'field_id'     => '03A-090',
		'position'     => 134,
		'field_length' => 3,
		'value'        => 'number',
	),
	'email'               => array(
		'field_id'     => '03A-160',
		'position'     => 160,
		'field_length' => 80,
		'value'        => 'value',
	),
	'employment_status'   => array(
		'field_id'     => '04B-100',
		'position'     => 130,
		'field_length' => 1,
		'value'        => array(
			'Y' => 'employed',
			'N' => 'retired',
		),
	),
	'monthly_income'      => array(
		'field_id'     => '05I-040',
		'position'     => 15,
		'field_length' => 15,
		'value'        => 'flot_number',
		'float_limit'  => 2,
	),
	'home_description'    => array(
		'field_id'     => '06G-090',
		'position'     => 95,
		'field_length' => 2,
		'value'        => array(
			'14' => 'Single Family',
			'04' => 'Condominium',
			'16' => 'Townhouse',
			'18' => 'Multi-Family',
		),
	),
	'loan_vendor'         => array(
		'field_id'     => '06L-040',
		'position'     => 15,
		'field_length' => 35,
		'value'        => 'value',
	),
	'mortgage_balance'    => array(
		'field_id'     => '06L-130',
		'position'     => 179,
		'field_length' => 15,
		'value'        => 'flot_number',
		'float_limit'  => 2,
	),
	'purchase_price'      => array(
		'field_id'     => '07A-020',
		'position'     => 4,
		'field_length' => 15,
		'value'        => 'flot_number',
		'float_limit'  => 2,
	),
	'foreclosure'         => array(
		'field_id'     => '08A-070',
		'position'     => 17,
		'field_length' => 1,
		'value'        => array(
			'Y' => 'yes',
			'N' => 'no',
		),
	),
	'property_use2'       => array(
		'field_id'     => '08A-150',
		'position'     => 26,
		'field_length' => 1,
		'value'        => array(
			'1' => 'Primary Residence',
			'2' => 'Secondary or Vacation',
			'D' => 'Rental or Investment',
		),
	),
	'bankruptcy'          => array(
		'field_id'     => '08A-040',
		'position'     => 14,
		'field_length' => 1,
		'value'        => array(
			'Y' => 'yes',
			'N' => 'no',
		),
	),
	'second_mortgage'     => array(
		'field_id'     => 'LNC-020',
		'position'     => 4,
		'field_length' => 1,
		'value'        => array(
			'1' => 'yes',
			'F' => 'no',
		),
	),
	'credit_rating'       => array(
		'field_id'     => 'SCA-030',
		'position'     => 7,
		'field_length' => 3,
		'value'        => array(
			'720' => 'Excellent',
			'660' => 'Good',
			'620' => 'Average',
			'579' => 'Poor',
		),
	),
	'ss_number'           => array(
		'field_id'     => '03A-030',
		'position'     => 6,
		'field_length' => 9,
		'value'        => 'value',
	),
	'dob'                 => array(
		'field_id'     => '03A-150',
		'position'     => 152,
		'field_length' => 8,
		'value'        => 'value',
	),
);

// Include action file.
require_once MAPP_MORTGAGE_APP_BASE_PATH . 'inc/actions.php';

// Include action-callback(functions) file.
require_once MAPP_MORTGAGE_APP_BASE_PATH . 'inc/functions/functions.php';

// Include lincenses files
require_once MAPP_MORTGAGE_APP_BASE_PATH . 'inc/class/licenses.php';

// Include general file.
require_once MAPP_MORTGAGE_APP_BASE_PATH . 'inc/class/general.php';

// Include export files.
require_once MAPP_MORTGAGE_APP_BASE_PATH . 'inc/interface/export.php';
require_once MAPP_MORTGAGE_APP_BASE_PATH . 'inc/class/export_base.php';
require_once MAPP_MORTGAGE_APP_BASE_PATH . 'inc/class/export_csv.php';
require_once MAPP_MORTGAGE_APP_BASE_PATH . 'inc/class/export_fannie.php';
require_once MAPP_MORTGAGE_APP_BASE_PATH . 'inc/class/export_mismo.php';

// Include shortener api(bitly).
require_once MAPP_MORTGAGE_APP_BASE_PATH . 'inc/class/bitly_shortURL.php';

// Include admin action file.
require_once MAPP_MORTGAGE_APP_BASE_PATH . 'inc/admin/actions.php';

// Include admin action-callback(functions) file.
require_once MAPP_MORTGAGE_APP_BASE_PATH . 'inc/admin/functions/functions.php';
