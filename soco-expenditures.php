<?php

//this is the code that works with EXPENDITURES
// --> VIEW 
// --> ADD

//function to add action for download expense CSV :: download_expenditure_csv
add_action( "init", "download_expenditure_csv" );

function download_expenditure_csv() {

	//only run if the download_csv button has been clicked
	if ( isset( $_POST['download_expenditure_csv'] ) ) {

		$data = soco_get_expenditure_view();
		
		//only output csv if there is returned date
		if ( !empty( $data ) ){
			$delimiter = ",";
			$filename = "expenditures_" . date( 'Y-m-d' ) . ".csv";

			//create a file pointer
			$f = fopen( 'php://memory', 'w' );

			//set column headers
			$fields = array (
				'expenditureId',
				'exDisbursementType',
				'exExpenditureType',
				'exAmount',
				'exDate',
				'exPaymentType',
				'exAccountNumber',
				'exPayeeId'
			);
			fputcsv( $f, $fields, $delimiter );

			//output each row of the data, format line as csv and write to file pointer
			foreach ( $data as $d ){
				//formatting a few entries so they appear correctly
				$amount = number_format( $d->amount,2 );
				$expenditure_date = DateTime::createFromFormat( 'Y-m-d', $d->expenditure_date )->format( 'm/d/Y' );
				if ( $d->electioneering ){
					$electioneering = "Y";
				} else {
					$electioneering = "N";
				}
				if ( $d->independent ){
					$independent = "Y";
				} else {
					$independent = "N";
				}

				$line_data = array (
					$d->idexpenditures,
					$d->tracer_value,
					$d->expenditure_type_value,
					$amount,
					$expenditure_date,
					$d->receipt_value,
					'',
					$d->payee_number
				);
				fputcsv( $f, $line_data, $delimiter );
			}

			//move back to beginning of file
			fseek( $f, 0 );

			//set headers to download file rather than displayed
			header( 'Content-Type: text/csv' );
			header( 'Content-Disposition: attachment; filename="' . $filename . '";' );

			//output all remaining data on a file pointer
			fpassthru( $f );
		}
		exit;
	}
}


	//ajax to change if a expenditure is active
add_action( 'wp_ajax_change_expenditureAJAX', 'change_expenditureAJAX_callback' );
function change_expenditureAJAX_callback() {
	global $wpdb;
	$expenditure_id = $_POST['expenditure_id'];
	$active_record = $_POST['active_record'];
	$dateTimeNow = date( 'Y-m-d H:i:s' );
	
	$wpdb->update(
					'soco_expenditures',
					array (	
						'active' => $active_record,
						'date_deleted' => $dateTimeNow
					),
					array ( 'idexpenditures' => $expenditure_id ),
					array ( '%d', '%s' ),
					array ( '%d')
				);
}

function change_expenditureAJAX_javascript() {
	$adminAJAX =  admin_url( 'admin-ajax.php' );  
?>
<script type="text/javascript" language="JavaScript">
jQuery( document ).ready( function( $ ) { 	
	jQuery( function( $ ) {
		$( "button[name=btn_change_expenditure]" )
			.button()
			.click( function( event ) {
				var active_record = $( this ).val();
				var expenditure_id = $( this ).next().val();
			
				if ( active_record == 1 ) {
					var action_name = "return";
					var message = "Expenditure Returned";
				} else {
					var action_name = "remove";
					var message = "Expenditure Removed";
				}
			
				var action = confirm( "Are you sure you want to " + action_name + " this expenditure?" );
					
				if ( action ){
					var ajaxurl = <?php echo json_encode( $adminAJAX ); ?>;
					var data = {
								action: 'change_expenditureAJAX',
								expenditure_id: expenditure_id,
								active_record: active_record
							   };
					
					$.post( ajaxurl, data )
					  .done( function( data, success ) {
						$( '#spn-buttons-' + expenditure_id ).html( '<div class="alert alert-success" role="alert"><strong>' + message + '</strong></div>' );
					  })
					.fail(function() {
						$( '#spn-buttons-' + expenditure_id ).html( '<div class="alert alert-danger" role="alert"><strong>Oops!</strong> Something went wrong. Pleas reload the page and try again.</div>');
					  });

				}
      });
  });
});
</script>

<?php 
}; 

//// DISPLAY VIEW FOR EXPENSES :  this creates the expense HTML for the shortcode ////////////////////////////
function soco_display_expenses() {
		
	$start_date = $_GET["start-date"];
	if ( empty( $start_date ) ){ $start_date = date( 'Y-m-d', strtotime( '-90 days' ) ); }

	$end_date = $_GET["end-date"];
	if ( empty( $end_date ) ){ $end_date = date( 'Y-m-d' ); }

	$min_amount = $_GET["min-amount"];
	if ( empty( $min_amount ) ){ $min_amount = 0; }

	$max_amount = $_GET["max-amount"];
	if ( empty( $max_amount ) ){ $max_amount = 999999; }
	
	$organization_id = $_GET["slct-organization-id"];
	
	$expenditure_type_id = $_GET["slct-expenditure-type"];
	
	$payment_id = $_GET["slct-payment-type"];
	
	if ( isset( $_GET['ckbx-active'] ) ){
		$deleted_checked = "checked";
	} else {
		$deleted_checked = "";
	}

//	//calling in ajax for remove/return buttons
	change_expenditureAJAX_javascript();
	
	$expenditure_results = soco_get_expenditure_view();
	
	$record_count = count( $expenditure_results );
	
	if ( $record_count > 999 ){
		$too_many_message = '<div class="alert alert-warning" role="alert">
			  <strong>Too Many Records!</strong> Please narrow your search range to limit results.
			</div>';
	}

	//disables csv button if there are no results
	if ( empty( $expenditure_results ) ) { $disabled = "disabled";}

//////////////search form start//////////////////
	
	$expenditure_output = '

	<div name="div-output-container">
		<h3>Use the fields below to search Expenditures.</h3>
		<form name="frm-search-contributions" id="frm-search-contributions" >
		
			<div class="flex-container"> <!-- starts flex row -->
				<div class="flex-item"><strong>Start Date</strong>: <br /><input type="date" name="start-date" value="'.$start_date.'"></div>
				<div class="flex-item"><strong>End Date</strong>: <br /><input type="date" name="end-date" value="'.$end_date.'"></div>
				<div class="flex-item"><strong>Minimum</strong>: <br /><input type="number" name="min-amount" min="0" value="'.$min_amount.'"></div>
				<div class="flex-item"><strong>Maximum</strong>: <br /><input type="number" name="max-amount" min="0" value="'.$max_amount.'"></div>
			</div > <!-- ends flex row -->
			
			<div class="flex-container"> <!-- starts flex row -->
				<div class="flex-item"><strong>Organization</strong>: <br />
					<select name="slct-organization-id" >	
					<option value="" >All</option>';

					$organization_list = soco_organization_list ();
					foreach ($organization_list as $ol) { 
						$expenditure_output  .= '<option value="'.$ol->idorganization_list.'"'.( ( $organization_id == $ol->idorganization_list ) ? ' selected="selected">' : '>' ).' '.$ol->organization_name.'</option>';
					} 

	$expenditure_output  .= '</select>
				</div>
				<div class="flex-item"><strong>Expenditure Types</strong>: <br />
					<select name="slct-expenditure-type" >	
					<option value="" >All Types</option>';

					$expenditure_type_list = soco_expenditure_type_list ();
					foreach ( $expenditure_type_list as $el ) { 
						$expenditure_output  .= '<option value="'.$el->idexpenditure_type_list.'"'.( ( $expenditure_type_id == $el->idexpenditure_type_list ) ? ' selected="selected">' : '>' ).' '.$el->expenditure_type_name.'</option>';
					} 

	$expenditure_output  .= '</select>
				</div>
				<div class="flex-item">
					<strong>Payment Type</strong>: <br />
					<select name="slct-payment-type" >
					<option value="" selected>All Types</option>';
	//this is the event list. Strangely named
	$payment_type_list = soco_payment_type_list ();
	foreach ( $payment_type_list as $pl ) { 
		$expenditure_output .= '<option value="'.$pl->idreceipt_type_list.'"'.( ( $payment_id == $pl->idreceipt_type_list ) ? ' selected="selected">' : '>' ).' '.$pl->receipt_name.'</option>';
	}
	
	$expenditure_output  .= '</select>
				</div>
				
			</div > <!-- ends flex row -->
		 
			<input type="submit" value="Search">
			<input type="reset">
			<input type="checkbox" name="ckbx-active" '.$deleted_checked.'> Search Deleted Records
			
		</form> <!-- closes search form -->
	
	<br />
	'.$too_many_message.'
	<br />
	<h3>'.$record_count.' Expenditures Found</h3>
	<div name="expenditures-results-container" >
		<table width="100%" border="0">
		  <tbody>
			<tr>
			  <th scope="col">Date</th>
			  <th scope="col">Amount</th>
			  <th scope="col">Organization</th>
			  <th scope="col">Expenditure Type</th>
			  <th scope="col">Payment Type</th>
			  <th scope="col">Action</th>
			</tr>';
 	
	 	foreach ( $expenditure_results as $er ) {  
			$expenditure_output.= '
			<tr>
			  <td>'.$er->expenditure_date.'</td>
			  <td>'.$er->amount.'</td>
			  <td>'.$er->organization_name.'</td>
			  <td>'.$er->expenditure_type_name.'</td>
			  <td>'.$er->receipt_name.'</td>
			  <td><span id="spn-buttons-'.$er->idexpenditures.'" ><button name="btn_edit_contribution" name="btn_edit_expediture" title="Click this to edit this record."><a href="../add-expenditure?expenditure_id='.$er->idexpenditures.'" title="Click this to edit this expenditure"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a></button>';
			if ( $deleted_checked == "checked" ){
				//showing deleted things - need add button
				$expenditure_output  .= '<button name="btn_change_expenditure" name="btn_change_expenditure" value="1" title="Click this button to return this record to an active expenditure."><i class="fa fa-undo" aria-hidden="true"></i></button>
				<input type="hidden" name="hdn_expenditure_id" value="'.$er->idexpenditures.'">';
			} else {
				//showing active records - need delete button
				$expenditure_output  .= '<button name="btn_change_expenditure" name="btn_change_expenditure" value="0" title="Click this button to remove this record from expenditure."><i class="fa fa-trash" aria-hidden="true"></i></button>
				<input type="hidden" name="hdn_expenditure_id" value="'.$er->idexpenditures.'">';
			}
				  
		$expenditure_output  .= '</span></td>
			</tr>';
		}  
	
	$expenditure_output  .= '</tbody>
		</table>

		<form method="post" id="download_form" action="">
			<input type="submit" name="download_expenditure_csv" class="button-primary" value="Download CSV File" '.$disabled.' />
			
		</form>
	</div><!-- end results containter -->
	</div><!-- ends output containter -->';
	
		return $expenditure_output;
	}

// this creates the edit/new expenditures HTML for the shortcode ////////////////////////////
function soco_display_expenditure_form() {
	global $wpdb;	
	$expenditure_id = $_GET["expenditure_id"];
	
	$alert = "";

	//sets a flag to whether we are adding a new record or updating an old one
	if ( isset( $_GET['expenditure_id'] ) ) {
		//update
		$database_action = "update";
		$text_action = "update";
	} else {
		//insert
		$database_action = "insert";
		$text_action = "add";
	}

	//Is this a POST - If so time to modify the database
	if ( ( isset( $_POST["hdn-form-post"] ) ) && ( $_POST["hdn-form-post"] == 1 ) ){
		//checks for entrys in required fields
		if ( ( isset ($_POST['slct-payee-type'] ) ) && 
			( isset( $_POST['slct-organization'] ) ) && 
			( isset( $_POST['expenditure-date'] ) ) && 
			( isset( $_POST['expenditure-amount'] ) ) && 
			( isset( $_POST['slct-expenditure-type'] ) ) &&
			( isset( $_POST['slct-payment-type'] ) ) &&
			( isset( $_POST['slct-disbursement-type'] ) ) &&
			( isset( $_POST['slct-payer-type'] ) ) ) {

			$user_id = get_current_user_id();
			
			if ( $database_action == "insert" ) { 
				$db_action = $wpdb->insert( 
					'soco_expenditures', 
					array( 
						'payee_type_id' => $_POST['slct-payee-type'],
						'organization_id' => $_POST['slct-organization'], 
						'expenditure_date' => $_POST['expenditure-date'], 
						'amount' => $_POST['expenditure-amount'], 
						'expenditure_type_id' => $_POST['slct-expenditure-type'], 
						'payment_type_id' => $_POST['slct-payment-type'], 
						'electioneering' => $_POST['slct-electioneering'],
						'independent' => $_POST['slct-independent'],
						'disbursement_type_id' => $_POST['slct-disbursement-type'],
						'reimbursement' => $_POST['slct-reimbursement'],
						'payer_id' => $_POST['slct-payer-type'],
						'expenditure_notes' => $_POST['txar-expenditure-notes'], 
						'user_id' => $user_id
					)
				);
			
				if ( $db_action == 1 ){ //checks to see if WP returned an error or not. 0 is FAIL, over 0 is success
					$added = 1;
					$new_expenditure_id = $wpdb->insert_id;
				} else {
					$added = 0;	
				}
			
			} elseif ( $database_action == "update" ) { 
				$db_action = $wpdb->update( 
					'soco_expenditures', 
					array( 
						'payee_type_id' => $_POST['slct-payee-type'],
						'organization_id' => $_POST['slct-organization'], 
						'expenditure_date' => $_POST['expenditure-date'], 
						'amount' => $_POST['expenditure-amount'], 
						'expenditure_type_id' => $_POST['slct-expenditure-type'], 
						'payment_type_id' => $_POST['slct-payment-type'], 
						'electioneering' => $_POST['slct-electioneering'],
						'independent' => $_POST['slct-independent'],
						'disbursement_type_id' => $_POST['slct-disbursement-type'],
						'reimbursement' => $_POST['slct-reimbursement'],
						'payer_id' => $_POST['slct-payer-type'],
						'expenditure_notes' => $_POST['txar-expenditure-notes'], 
						'user_id' => $user_id
					),
					array( 'idexpenditures' => $expenditure_id )
				);

				if ($db_action == 1){ //checks to see if WP returned an error or not. 0 is FAIL, over 0 is success
					$added = 1;
				} else {
					$added = 0;	
				}

			}

		} elseif ( $_POST['hdn-form-post'] == 1 ) {
			//something missing. Return data and add error to page
			$alert = '<div class="alert alert-danger" role="alert">
		  Please check the form and try to save it again. Make sure all required fields are filled in. 
		</div>';

		}
		
	} // end of post code
	
//adds alert to page if something was added or errored.	
	if ( $added == 1 ){
		$alert .= '<div class="alert alert-success" role="alert">
					  Expenditure has been saved!
					</div>';
	} elseif ( ( isset( $added ) ) && ( $added == 0 ) ){
		$alert .= '<div class="alert alert-danger" role="alert">
					  Uh oh! There was an error with the database. Please try again. 
					</div>';
	} else {
		$alert .= "";
	}

	if ( $expenditure_id > 0 ){
		//lets get contibution data and set it.
		$expenditure_data = soco_expenditure_data( $expenditure_id );
	}
	
	
/// start of form string
	$form_output_string = '
	<div name="div-output-container"> 
		'.$alert.'
		
		<h3>Use the form below to '.$text_action.' an expenditure.</h3> 
	<form name="frm-edit-expenditures" id="frm-edit-expenditures" method="post" action="" >
	
	<div class="alert alert-info" role="alert">
  		<em>TRACER Data</em>';
	
		$form_output_string .= '<div class="flex-container">
			<div class="flex-item">
				<strong>Payee Type</strong>: <br />
				<select name="slct-payee-type" required>
					<option value="" selected>Select...</option>';

	$payee_type_list = soco_contributor_type_list ();
	foreach ( $payee_type_list as $pt ) { 
		$form_output_string .= '<option value="'.$pt->idcontributor_type_list.'"'.( ( $expenditure_data->payee_type_value == $pt->idcontributor_type_list) ? ' selected="selected">' : '>' ).' '.$pt->contributor_type_name.'</option>';
	} 
	
	$form_output_string .= '</select>
			</div>
			<div class="flex-item"><strong>Organization</strong>: <br />
				<select name="slct-organization" required>	
					<option value="" selected>Click first letter of the name to Select...</option>';

	$organization_list = soco_organization_list ();
	foreach ( $organization_list as $ol ) { 
		$form_output_string .= '<option value="'.$ol->idorganization_list.'"'.(($expenditure_data->organization_id == $ol->idorganization_list) ? ' selected="selected">' : '>' ).' '.$ol->organization_name.'</option>';
	} 
	$form_output_string .= '</select>
			</div>

			<div class="flex-item">
				<br />
				<button name="btn-new-organization" ><a href="../add-organization" title="Click to add an organization.">Add New Organization</a></button>
				<button name="btn-edit-organization" type="button" formnovalidate>Edit Organization</button>
			</div>
		</div> <!-- closes flexrow -->';
	
	$form_output_string .= '<div class="flex-container">
			<div class="flex-item"><strong>Date</strong>: <br /><input type="date" name="expenditure-date" value="'.$expenditure_data->expenditure_date.'" required></div>
			<div class="flex-item"><strong>Amount</strong>: <br />
				<input type="number" name="expenditure-amount" step="0.01" min="0" max="999999" value="'.$expenditure_data->amount.'" required ></div>
			<div class="flex-item"><strong>Expenditure Type</strong>: <br />
				<select name="slct-expenditure-type" required>
					<option value="" selected>Select...</option>';
                		
							$expenditure_list = soco_expenditure_type_list ();
						 	foreach ( $expenditure_list as $cl ) { 
                        	    $form_output_string .= '<option value="'.$cl->idexpenditure_type_list.'"'.( ( $expenditure_data->expenditure_type_id == $cl->idexpenditure_type_list ) ? ' selected="selected">' : '>' ).' '.$cl->expenditure_type_name.'</option>';
                        	} 
	$form_output_string .= '</select>
			</div>
			<div class="flex-item">
				<strong>Payment</strong>: <br />
				<select name="slct-payment-type" required>
					<option value="" selected>Select...</option>';

	$receipt_list = soco_receipt_type_list ();
	foreach ( $receipt_list as $rl ) { 
		$form_output_string .= '<option value="'.$rl->idreceipt_type_list.'"'.( ( $expenditure_data->payment_type_id == $rl->idreceipt_type_list ) ? ' selected="selected">' : '>' ).' '.$rl->receipt_name.'</option>';
	}  
    
	$form_output_string .= '</select>
			</div>
		</div> <!-- closes flexrow -->
			
		<div class="flex-container"> <!-- starts flexrow -->
			<div class="flex-item">
				<strong>Electioneering</strong>: <br />
				<select name="slct-electioneering" required>';
				
				if ( $expenditure_data->electioneering == 1 ) {
					$form_output_string .=
						'<option value="0" >No</option>
						<option value="1" selected>Yes</option>';
				} else {
					$form_output_string .=
						'<option value="0" selected>No</option>
						<option value="1" >Yes</option>';
				}
				
	$form_output_string .= '</select>
			</div>
			
			<div class="flex-item">
				<strong>Independent</strong>: <br />
				<select name="slct-independent" required>';
				
				if ( $expenditure_data->independent == 1 ) {
					$form_output_string .=
						'<option value="0" >No</option>
						<option value="1" selected>Yes</option>';
				} else {
					$form_output_string .=
						'<option value="0" selected>No</option>
						<option value="1" >Yes</option>';
				}
				
	$form_output_string .= '</select>
			</div>
			
			<div class="flex-item"><strong>Disbursement Type</strong>: <br />
				<select name="slct-disbursement-type" required>';
                		
					$disbursement_list = soco_contribution_type_list ();
					foreach ( $disbursement_list as $cl ) { 
                        $form_output_string .= '<option value="'.$cl->idcontribution_type_list.'"'.( ( $expenditure_data->disbursement_type_id == $cl->idcontribution_type_list ) ? ' selected="selected">' : '>' ).' '.$cl->contribution_type_name.'</option>';
                    } 
	$form_output_string .= '</select>
			</div>			
			
		</div> <!-- closes flexrow -->
		</div> <!-- closes altertbox -->';
		
	$form_output_string .= 'Please enter the below information for internal use.
	<div class="flex-container">
		<div class="flex-item">
			<strong>Reimbursement</strong>: <br />
			<select name="slct-reimbursement" required>';
				
				if ( $expenditure_data->reimbursement == 1 ) {
					$form_output_string .=
						'<option value="0" >No</option>
						<option value="1" selected>Yes</option>';
				} else {
					$form_output_string .=
						'<option value="0" selected>No</option>
						<option value="1" >Yes</option>';
				}
				
	$form_output_string .= '</select>
			</div>	
			<div class="flex-item">
				<strong>Payer</strong>: <br />
				<select name="slct-payer-type" required>
					<option value="" selected>Select...</option>';

	//this is the event list. Strangely named
	$payer_list = soco_payer_list ();
	foreach ( $payer_list as $pl ) { 
		$form_output_string .= '<option value="'.$pl->idsoco_payer_list.'"'.( ( $expenditure_data->payer_id == $pl->idsoco_payer_list ) ? ' selected="selected">' : '>' ).' '.$pl->soco_payer_name.'</option>';
	} 
	$form_output_string .=  '</select>
			</div>

			<div class="flex-item">';
	$textLeft = 500 - strlen( $expenditure_data->expenditure_notes );             
	$form_output_string .=  '<strong>Notes</strong>: <span id="counterEditNotes">Characters Left: '.$textLeft.'</span> <br />
				<textarea name="txar-expenditure-notes" id="txar-expenditure-notes" maxlength="500" cols="50" rows="3">'.$expenditure_data->expenditure_notes.'</textarea>
<!-- quick little counter script for usability --> 
  				<script type="text/javascript">
                    jQuery( "#txar-expenditure-notes" ).keyup( function () {
                        var left = 500 - jQuery( this ).val().length;
                        jQuery( "#counterEditNotes" ).text( "Characters Left: " + left );
                    });
                    </script>
<!-- End counter usability script -->
			</div>
		</div> <!-- closes flexrow -->

		<br />
		<input type="hidden" id="txtPhoneNo" name="hdn-form-post" value="1" >
		<input type="hidden" name="hdn-expenditure-id" value="'.$expenditure_data->idexpenditures.'" >
		<input type="hidden" name="hdn-form-post" value="1" >
		<input type="submit" value="Save">
		<input type="reset">
		
	</form>';
	
	return $form_output_string;
	}

?>
