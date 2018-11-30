<?php
//this file holds all the funcitons for the lists needed for the plugin

//list to get contribution category type
function soco_contribution_category_type_list (){
	global $wpdb;
	$sql = "
	SELECT
	  soco_contribution_category_list.idcontribution_category_list,
	  soco_contribution_category_list.category_name,
	  soco_contribution_category_list.category_description
	FROM
	  soco_contribution_category_list
	ORDER BY
	  soco_contribution_category_list.idcontribution_category_list";
	$results = $wpdb->get_results( $sql );

	return $results;
}

//list to get contribution type
function soco_contribution_type_list (){
	global $wpdb;
	$sql = "
	SELECT
	  soco_contribution_type_list.idcontribution_type_list,
	  soco_contribution_type_list.tracer_value,
	  soco_contribution_type_list.contribution_type_name
	FROM
	  soco_contribution_type_list
	ORDER BY
	  soco_contribution_type_list.tracer_value";
	$results = $wpdb->get_results( $sql );

	return $results;
}

//list to get receipt type
function soco_receipt_type_list (){
	global $wpdb;
	$sql = "
	SELECT
	  soco_receipt_or_payment_type_list.idreceipt_type_list,
	  soco_receipt_or_payment_type_list.receipt_value,
	  soco_receipt_or_payment_type_list.receipt_name
	FROM
	  soco_receipt_or_payment_type_list
	ORDER BY
	  soco_receipt_or_payment_type_list.receipt_value";
	$results = $wpdb->get_results( $sql );

	return $results;
}

//list to get contributor type
function soco_contributor_type_list (){
	global $wpdb;
	$sql = "
	SELECT
	  soco_contributor_type_list.idcontributor_type_list,
	  soco_contributor_type_list.tracer_value,
	  soco_contributor_type_list.contributor_type_name
	FROM
	  soco_contributor_type_list
	ORDER BY
	  soco_contributor_type_list.tracer_value";
	$results = $wpdb->get_results( $sql );

	return $results;
}

//list to get occupation type
function soco_occupation_type_list (){
	global $wpdb;
	$sql = "
	SELECT
	  soco_occupation_list.idoccupation_list,
	  soco_occupation_list.tacer_value,
	  soco_occupation_list.occupation_name
	FROM
	  soco_occupation_list
	ORDER BY
	  soco_occupation_list.tacer_value";
	$results = $wpdb->get_results( $sql );

	return $results;
}

//list to get gift type
function soco_gift_type_list (){
	global $wpdb;
	$sql = "
	SELECT
	  soco_gift_type_list.idsoco_gift_type_list,
	  soco_gift_type_list.gift_name,
	  soco_gift_type_list.gict_description
	FROM
	  soco_gift_type_list
	ORDER BY
	  soco_gift_type_list.gift_name";
	$results = $wpdb->get_results( $sql );

	return $results;
}


//function to retrieve the donor list
function soco_get_donor_list (){
	global $wpdb;
	$sql = "
	SELECT
	  soco_donor_list.iddonor,
	  soco_donor_list.last_name,
	  soco_donor_list.address1,
	  soco_donor_list.city,
	  soco_donor_list.state_id,
	  soco_donor_list.zip_code,
	  soco_donor_list.address2,
	  soco_donor_list.first_name,
	  soco_donor_list.middle_name,
	  soco_donor_list.email,
	  soco_donor_list.phone,
	  soco_donor_list.employer,
	  soco_occupation_list.occupation_name,
	  soco_occupation_list.tacer_value,
	  soco_donor_list.donor_notes,
	  soco_donor_list.volunteer,
	  soco_donor_list.organization_name
	FROM
	  soco_donor_list
	  INNER JOIN soco_occupation_list ON soco_donor_list.occupation_id = soco_occupation_list.idoccupation_list
	ORDER BY
	  soco_donor_list.last_name";
	$results = $wpdb->get_results( $sql );

	return $results;
}

//function state list
function soco_state_list (){
	global $wpdb;
	$sql = "
	SELECT
	  soco_state_list.state_id,
	  soco_state_list.state_name,
	  soco_state_list.state_abbr
	FROM
	  soco_state_list
	ORDER BY
	  soco_state_list.state_name";
	$results = $wpdb->get_results( $sql );

	return $results;
}

//funciton to generate the list of occupations
function soco_occupation_list (){
	global $wpdb;
	$sql = "
	SELECT
	  soco_occupation_list.idoccupation_list,
	  soco_occupation_list.tacer_value,
	  soco_occupation_list.occupation_name
	FROM
	  soco_occupation_list
	ORDER BY
	  soco_occupation_list.occupation_name";
	$results = $wpdb->get_results( $sql );

	return $results;
	
}

//function for internal payer list
function soco_payer_list (){
	global $wpdb;
	$sql = "
	SELECT
	  soco_payer_list.idsoco_payer_list,
	  soco_payer_list.soco_payer_name,
	  soco_payer_list.soco_payer_description
	FROM
	  soco_payer_list
	ORDER BY
	  soco_payer_list.idsoco_payer_list";
	$results = $wpdb->get_results( $sql );

	return $results;
}

//function to retrieve data for the display contributions view function (or other functions).
function soco_get_contributions_view ( $start_date = null, $end_date = null, $min_amount = null, $max_amount = null, $donor_id = null, $event_id = null ){
	global $wpdb;

	$start_date = $_GET["start-date"];
	if ( empty( $start_date ) ){ $start_date = date( 'Y-m-d', strtotime( '-90 days' ) ); }

	$end_date = $_GET["end-date"];
	if ( empty( $end_date ) ){ $end_date = date( 'Y-m-d' ); }

	$min_amount = $_GET["min-amount"];
	if ( empty( $min_amount ) ){ $min_amount = 0; }

	$max_amount = $_GET["max-amount"];
	if ( empty( $max_amount ) ){ $max_amount = 999999; }

	$donor_id = $_GET["slct-donor"];
	if ( !empty( $donor_id ) ){ $donor_sql = "AND soco_contributions.donor_id = $donor_id"; } //add line to sql

	$event_id = $_GET["slct-category-type"];
	if ( !empty( $event_id ) ){ $event_sql = "AND soco_contributions.event_id = $event_id"; } //add line to sql
	
	if ( $_GET['ckbx-active'] == "on" ){
		$active_checked = 0;
	} else {
		$active_checked = 1;
	}
	
	$sql = "
	SELECT
	  soco_contributions.idcontributions,
	  soco_contribution_type_list.tracer_value AS contribution_tracer_value,
	  soco_contributions.amount,
	  soco_contributions.cycle_amount,
	  soco_contributions.contribution_date,
	  soco_receipt_or_payment_type_list.receipt_value,
	  soco_contributor_type_list.tracer_value AS contributor_tracer_value,
	  soco_donor_list.organization_name,
	  soco_donor_list.first_name,
	  soco_donor_list.middle_name,
	  soco_donor_list.last_name,
	  soco_donor_list.address1,
	  soco_donor_list.address2,
	  soco_donor_list.city,
	  soco_state_list.state_abbr,
	  soco_donor_list.zip_code,
	  soco_donor_list.employer,
	  soco_occupation_list.tacer_value AS occupation_tracer_value,
	  soco_contributions.event_id,
	  soco_contributions.notes,
	  soco_contributions.donor_id,
	  soco_contribution_category_list.category_name,
	  soco_donor_list.phone,
	  soco_donor_list.email
	FROM
	  soco_contributions
	  LEFT JOIN soco_donor_list ON soco_contributions.donor_id = soco_donor_list.iddonor
	  LEFT JOIN soco_receipt_or_payment_type_list ON soco_contributions.receipt_type_id =
		soco_receipt_or_payment_type_list.idreceipt_type_list
	  LEFT JOIN soco_contribution_type_list ON soco_contributions.contribution_type_id =
		soco_contribution_type_list.idcontribution_type_list
	  LEFT JOIN soco_contributor_type_list ON soco_contributions.contributor_type_id =
		soco_contributor_type_list.idcontributor_type_list
	  LEFT JOIN soco_state_list ON soco_donor_list.state_id = soco_state_list.state_id
	  LEFT JOIN soco_occupation_list ON soco_donor_list.occupation_id = soco_occupation_list.idoccupation_list
	  LEFT JOIN soco_contribution_category_list ON
		soco_contributions.event_id = soco_contribution_category_list.idcontribution_category_list
	WHERE
	  soco_contributions.contribution_date BETWEEN '$start_date' AND '$end_date' AND
	  soco_contributions.amount BETWEEN $min_amount AND $max_amount AND
  	  soco_contributions.active_record = $active_checked

	  $donor_sql

	  $event_sql

	ORDER BY
	  soco_contributions.contribution_date,
	  soco_donor_list.last_name,
	  soco_contributions.amount
	LIMIT 1000";
	$results = $wpdb->get_results( $sql );

	return $results;
}

//function to get contribution data
function soco_contribution_data( $contribution_id ){
	global $wpdb;
	if ( $contribution_id > 0 ) {
		$contribution_data_sql = "
		SELECT
		  soco_contributions.idcontributions,
		  soco_contributions.contribution_type_id,
		  soco_contributions.amount,
		  soco_contributions.cycle_amount,
		  soco_contributions.contribution_date,
		  soco_contributions.receipt_type_id,
		  soco_contributions.contributor_type_id,
		  soco_contributions.donor_id,
		  soco_contributions.electioneering,
		  soco_contributions.notes,
		  soco_contributions.event_id,
		  soco_contributions.contribution_category_id,
		  soco_contributions.gift_type_id
		FROM
		  soco_contributions
		WHERE
		  soco_contributions.idcontributions = $contribution_id";
		$contribution_data = $wpdb->get_row( $contribution_data_sql );
	}
	return $contribution_data;
}

//function to get the donor informations
function soco_donor_data ( $donor_id ){
	global $wpdb;
	if ( $donor_id > 0 ) {
		$sql = "
		SELECT
		  soco_donor_list.individual,
		  soco_donor_list.first_name,
		  soco_donor_list.middle_name,
		  soco_donor_list.last_name,
		  soco_donor_list.address1,
		  soco_donor_list.address2,
		  soco_donor_list.city,
		  soco_state_list.state_name,
		  soco_donor_list.zip_code,
		  soco_donor_list.email,
		  soco_donor_list.phone,
		  soco_donor_list.occupation_id,
		  soco_donor_list.employer,
		  soco_donor_list.organization_name,
		  soco_donor_list.volunteer,
		  soco_donor_list.donor_notes,
  	  	  soco_donor_list.state_id
		FROM
		  soco_donor_list
		  INNER JOIN soco_state_list ON soco_donor_list.state_id = soco_state_list.state_id
		WHERE
		  soco_donor_list.iddonor = $donor_id";
		$results = $wpdb->get_row( $sql );
	}
	return $results;
}


//quick function to format numbers to make them more readable.
function localize_us_number( $phone ) {
  $numbers_only = preg_replace( "/[^\d]/", "", $phone );
  return preg_replace( "/^1?(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $numbers_only );
}

//function to retrieve data for the display expenditures view function (or other functions).
function soco_get_expenditure_view ( $start_date = null, $end_date = null, $min_amount = null, $max_amount = null, $organization_id = null, $expenditure_id = null, $payment_id = null ){
	global $wpdb;
	
	$start_date = $_GET["start-date"];
	if ( empty( $start_date ) ){ $start_date = date( 'Y-m-d', strtotime( '-90 days' ) ); }

	$end_date = $_GET["end-date"];
	if ( empty( $end_date ) ){ $end_date = date( 'Y-m-d' ); }

	$min_amount = $_GET["min-amount"];
	if ( empty( $min_amount ) ){ $min_amount = 0; }

	$max_amount = $_GET["max-amount"];
	if ( empty( $max_amount ) ){ $max_amount = 999999; }

	$organization_id = $_GET["slct-organization-id"];
	if ( !empty( $organization_id ) ){ $organization_sql = "AND soco_expenditures.organization_id = $organization_id"; } //add line to sql
	
	$expenditure_id = $_GET["slct-expenditure-type"];
	if ( !empty( $expenditure_id ) ){ $expenditure_sql = "AND soco_expenditure_type_list.idexpenditure_type_list = $expenditure_id"; } //add line to sql

	$payment_id = $_GET["slct-payment-type"];
	if ( !empty( $payment_id ) ){ $payment_sql = "AND soco_expenditures.payment_type_id = $payment_id"; } //add line to sql
	
	if ( $_GET['ckbx-active'] == "on" ){
		$active_checked = 0;
	} else {
		$active_checked = 1;
	}

	$sql = "
	SELECT
	  soco_expenditures.idexpenditures,
	  soco_disbursement_type_list.tracer_value,
	  soco_expenditure_type_list.tracer_value AS expenditure_type_value,
	  soco_expenditures.amount,
	  soco_expenditures.expenditure_date,
	  soco_receipt_or_payment_type_list.receipt_value,
	  soco_expenditures.payee_number,
	  soco_payee_type_list.tracer_value AS payee_type_value,
	  soco_organization_list.organization_name,
	  soco_organization_list.contact_first_name,
	  soco_organization_list.contact_middle_name,
	  soco_organization_list.contact_last_name,
	  soco_organization_list.address1,
	  soco_organization_list.address2,
	  soco_organization_list.city,
	  soco_state_list.state_abbr,
	  soco_organization_list.zip_code,
	  soco_expenditure_type_list.expenditure_type_name,
	  soco_payee_type_list.payee_type_name,
	  soco_expenditures.organization_id,
	  soco_expenditures.payment_type_id,
	  soco_receipt_or_payment_type_list.receipt_name,
	  soco_expenditures.idexpenditures,
	  soco_expenditures.electioneering,
	  soco_expenditures.independent
	FROM
	  soco_expenditures
	  LEFT JOIN soco_disbursement_type_list ON soco_expenditures.disbursement_type_id =
		soco_disbursement_type_list.iddisbursement_type_list
	  LEFT JOIN soco_expenditure_type_list ON soco_expenditures.expenditure_type_id =
		soco_expenditure_type_list.idexpenditure_type_list
	  LEFT JOIN soco_receipt_or_payment_type_list ON soco_expenditures.payment_type_id =
		soco_receipt_or_payment_type_list.idreceipt_type_list
	  LEFT JOIN soco_organization_list ON soco_expenditures.organization_id = soco_organization_list.idorganization_list
	  LEFT JOIN soco_payee_type_list ON soco_expenditures.payee_type_id = soco_payee_type_list.idpayee_type_list
	  LEFT JOIN soco_state_list ON soco_organization_list.state_id = soco_state_list.state_id
	WHERE
	  soco_expenditures.expenditure_date BETWEEN '$start_date' AND '$end_date' AND
	  soco_expenditures.amount BETWEEN $min_amount AND $max_amount AND
	  soco_expenditures.active = $active_checked
	
	  $organization_sql
	  
	  $expenditure_sql

	  $payment_sql

	ORDER BY
	  soco_expenditures.expenditure_date,
	  soco_organization_list.organization_name,
	  soco_expenditures.amount
	  
	LIMIT 1000";
	$results = $wpdb->get_results( $sql );
	
	return $results;
}

//function to create the expenditure list 
function soco_expenditure_type_list (){
	global $wpdb;
	$sql = "
	SELECT
	  soco_expenditure_type_list.idexpenditure_type_list,
	  soco_expenditure_type_list.tracer_value,
	  soco_expenditure_type_list.expenditure_type_name
	FROM
	  soco_expenditure_type_list
	ORDER BY
	  soco_expenditure_type_list.tracer_value";
	$results = $wpdb->get_results( $sql );

	return $results;
}

//function to create the payment type list 
function soco_payee_type_list (){
	global $wpdb;
	$sql = "
	SELECT
	  soco_payee_type_list.idpayee_type_list,
	  soco_payee_type_list.tracer_value,
	  soco_payee_type_list.payee_type_name
	FROM
	  soco_payee_type_list
	ORDER BY
	  soco_payee_type_list.tracer_value";
	$results = $wpdb->get_results( $sql );

	return $results;
}

//function to create the organization list 
function soco_organization_list (){
	global $wpdb;
	$sql = "
	SELECT
	  soco_organization_list.idorganization_list,
	  soco_organization_list.organization_name,
	  soco_organization_list.contact_first_name,
	  soco_organization_list.contact_last_name,
	  soco_organization_list.contact_phone,
	  soco_organization_list.contact_email,
	  soco_organization_list.organization_notes,
	  soco_organization_list.updated_id
	FROM
	  soco_organization_list
	ORDER BY
	  soco_organization_list.organization_name";
	$results = $wpdb->get_results( $sql );

	return $results;
}

//function to produce the payment type list
function soco_payment_type_list (){
	global $wpdb;
	$sql = "
	SELECT
	  soco_receipt_or_payment_type_list.idreceipt_type_list,
	  soco_receipt_or_payment_type_list.receipt_value,
	  soco_receipt_or_payment_type_list.receipt_name
	FROM
	  soco_receipt_or_payment_type_list
	ORDER BY
	  soco_receipt_or_payment_type_list.receipt_value";
	$results = $wpdb->get_results( $sql );

	return $results;
}

//function to get expenditure data
function soco_expenditure_data ( $expenditure_id = null ){
	global $wpdb;
	$sql = "
	SELECT
	  soco_expenditures.idexpenditures,
	  soco_disbursement_type_list.tracer_value,
	  soco_expenditure_type_list.tracer_value AS expenditure_type_value,
	  soco_expenditures.amount,
	  soco_expenditures.expenditure_date,
	  soco_receipt_or_payment_type_list.receipt_value,
	  soco_expenditures.payee_number,
	  soco_payee_type_list.tracer_value AS payee_type_value,
	  soco_organization_list.organization_name,
	  soco_organization_list.contact_first_name,
	  soco_organization_list.contact_middle_name,
	  soco_organization_list.contact_last_name,
	  soco_organization_list.address1,
	  soco_organization_list.address2,
	  soco_organization_list.city,
	  soco_state_list.state_abbr,
	  soco_organization_list.zip_code,
	  soco_expenditure_type_list.expenditure_type_name,
	  soco_payee_type_list.payee_type_name,
	  soco_expenditures.organization_id,
	  soco_expenditures.payment_type_id,
	  soco_receipt_or_payment_type_list.receipt_name,
	  soco_expenditures.idexpenditures,
	  soco_expenditures.electioneering,
	  soco_expenditures.independent,
	  soco_expenditures.reimbursement,
  	  soco_expenditures.expenditure_type_id,
	  soco_expenditures.disbursement_type_id,
	  soco_expenditures.payer_id,
	  soco_expenditures.expenditure_notes
	FROM
	  soco_expenditures
	  LEFT JOIN soco_disbursement_type_list ON soco_expenditures.disbursement_type_id =
		soco_disbursement_type_list.iddisbursement_type_list
	  LEFT JOIN soco_expenditure_type_list ON soco_expenditures.expenditure_type_id =
		soco_expenditure_type_list.idexpenditure_type_list
	  LEFT JOIN soco_receipt_or_payment_type_list ON soco_expenditures.payment_type_id =
		soco_receipt_or_payment_type_list.idreceipt_type_list
	  LEFT JOIN soco_organization_list ON soco_expenditures.organization_id = soco_organization_list.idorganization_list
	  LEFT JOIN soco_payee_type_list ON soco_expenditures.payee_type_id = soco_payee_type_list.idpayee_type_list
	  LEFT JOIN soco_state_list ON soco_organization_list.state_id = soco_state_list.state_id
	WHERE
		soco_expenditures.idexpenditures = $expenditure_id";
	$results = $wpdb->get_row( $sql );

	return $results;
}

?>
