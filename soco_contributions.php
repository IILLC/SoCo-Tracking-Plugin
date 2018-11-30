<?php
//this is the code for the contributions 
// --> VIEW
// --> ADD

//adding the action to wordpress front end init hook
//Function to output the CSV file to the browser
add_action("init", "download_csv");

function download_csv() {

	//only run if the download_csv button has been clicked
	if (isset($_POST['download_csv'])) {

		$data = soco_get_contributions_view ();
		
		//only output csv if there is returned date
		if (!empty($data)){
			$delimiter = ",";
			$filename = "contributions_" . date('Y-m-d') . ".csv";

			//create a file pointer
			$f = fopen('php://memory', 'w');

			//set column headers
			$fields = array (
				'contributionId',
				'cbContributionType',
				'cbAmount',
				'cbCycleAmount',
				'cbDate',
				'cbReceiptType',
				'cbAccountNumber',
				'cbContributorId',
				'cbContributorType',
				'cbOrgId',
				'cbOrgName',
				'cbFirstName',
				'cbMiddleName',
				'cbLastName',
				'cbNameSuffix',
				'cbAddress1',
				'cbAddress2',
				'cbCity',
				'cbState',
				'cbZip',
				'cbEmployer',
				'cbOccupation',
				'cbElectioneering',
				'cbExplanation',
				'cbEarMarked',
				'cbOriginalSourceName',
				'cbOriginalSourceAddress',
				'cbDonorParentCorporationName',
				'cbDonorParentCorporationAddress',
				'cbDonorOtherNamesUsed',
				'cbDonorColoradoAgentName',
				'cbDonorColoradoAgentAddress',
				'DonorPhone',
				'DonorEmail'
			);
			fputcsv($f, $fields, $delimiter);

			//output each row of the data, format line as csv and write to file pointer
			foreach ($data as $d){
				//formatting a few entries so they appear correctly in the csv for the traccer file
				$amount = number_format($d->amount,2);
				$contribution_date = DateTime::createFromFormat('Y-m-d', $d->contribution_date)->format('m/d/Y');
				$pretty_phone = preg_replace("/^(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $d->phone);
				
				
				$line_data = array (
				$d->idcontributions,
				$d->contribution_tracer_value,
				$amount,
				$d->cycle_amount,
				$contribution_date,
				$d->receipt_value,
				'',
				'',
				$d->contributor_tracer_value,
				'',
				$d->organization_name,
				$d->first_name,
				$d->middle_name,
				$d->last_name,
				'',
				$d->address1,
				$d->address2,
				$d->city,
				$d->state_abbr,
				$d->zip_code,
				$d->employer,
				$d->occupation_tracer_value,
				'N',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				'',
				$pretty_phone,
				$d->email
			);
				fputcsv($f, $line_data, $delimiter);
			}

			//move back to beginning of file
			fseek($f, 0);

			//set headers to download file rather than displayed
			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename="' . $filename . '";');

			//output all remaining data on a file pointer
			fpassthru($f);
		}
		exit;

	}

}





////////////////////////////external function for ajax //////////////////

//trying to add the ajax for this form here.

	//ajax to change if a contribution is active
add_action('wp_ajax_change_contributionAJAX', 'change_contributionAJAX_callback');
function change_contributionAJAX_callback() {
	global $wpdb;
	$contribution_id = $_POST['contribution_id'];
	$active_record = $_POST['active_record'];
	$dateTimeNow = date('Y-m-d H:i:s');
	
	$wpdb->update(
					'soco_contributions',
					array (	
						'active_record' => $active_record,
						'record_deleted' => $dateTimeNow
					),
					array ( 'idcontributions' => $contribution_id ),
					array ( '%d', '%s' ),
					array ( '%d')
				);
}

function change_contributionAJAX_javascript() {
	$adminAJAX =  admin_url('admin-ajax.php');  
?>
<script type="text/javascript" language="JavaScript">
jQuery(document).ready(function($) { 	
	jQuery(function($) {
		$( "button[name=btn_change_contribution]" )
			.button()
			.click(function( event ) {
				var active_record = $( this ).val();
				var contribution_id = $( this ).next().val();
			
				if (active_record == 1) {
					var action_name = "return";
					var message = "Contribution Returned";
				} else {
					var action_name = "remove";
					var message = "Contribution Removed";
				}
			
				var action = confirm("Are you sure you want to " + action_name + " this contribution?");
					
				if (action){
					var ajaxurl = <?php echo json_encode($adminAJAX); ?>;
					var data = {
								action: 'change_contributionAJAX',
								contribution_id: contribution_id,
								active_record: active_record
							   };
	//            //WP ajax call
					
					$.post(ajaxurl, data)
					  .done(function( data, success ) {
						//alert( "Data Loaded: " + data );
						$('#spn-buttons-' + contribution_id).html('<div class="alert alert-success" role="alert"><strong>' + message + '</strong></div>')
					  })
					.fail(function() {
						//alert( "error" );
						$('#spn-buttons-' + contribution_id).html('<div class="alert alert-danger" role="alert"><strong>Oops!</strong> Something went wrong. Pleas reload the page and try again.</div>');
					  });

				} else {
					//alert("You clicked NO. Action cancled.");
						
				}
      });
  });
});
</script>

<?php 
}; 

//// DISPLAY VIEW FOR CONTRIBUTIONS :  this creates the contributions HTML for the shortcode ////////////////////////////
function soco_display_contributions() {
		
	$start_date = $_GET["start-date"];
	if (empty($start_date)){ $start_date = date( 'Y-m-d', strtotime( '-90 days' ) ); }

	$end_date = $_GET["end-date"];
	if (empty($end_date)){ $end_date = date( 'Y-m-d' ); }

	$min_amount = $_GET["min-amount"];
	if (empty($min_amount)){ $min_amount = 0; }

	$max_amount = $_GET["max-amount"];
	if (empty($max_amount)){ $max_amount = 999999; }
	
	$donor_id = $_GET['slct-donor'];
	
	$event_id = $_GET["slct-category-type"];
	
	if (isset($_GET['ckbx-active'])){
		$deleted_checked = "checked";
	} else {
		$deleted_checked = "";
	}

	//calling in ajax for remove/return buttons
	change_contributionAJAX_javascript();
	
	$contribution_results = soco_get_contributions_view();
	
	$record_count = count($contribution_results);
	
	if ($record_count > 999){
		$too_many_message = '<div class="alert alert-warning" role="alert">
			  <strong>Too Many Records!</strong> Please narrow your search range to limit results.
			</div>';
	}

	//disables csv button if there are no results
	if (empty($contribution_results)) { $disabled = "disabled";}

	$contribution_output = '<div name="div-output-container"> 
	<h3>Use these fields below to search Contributions.</h3>	
	<form name="frm-search-contributions" id="frm-search-contributions" >
	
	<div class="flex-container">
		<div class="flex-item"><strong>Start Date</strong>: <br /><input type="date" name="start-date" value="'.$start_date.'"></div>
		<div class="flex-item"><strong>End Date</strong>: <br /><input type="date" name="end-date" value="'.$end_date.'"></div>
		<div class="flex-item"><strong>Minimum</strong>: <br /><input type="number" name="min-amount" min="0" value="'.$min_amount.'"></div>
		<div class="flex-item"><strong>Maximum</strong>: <br /><input type="number" name="max-amount" min="0" value="'.$max_amount.'"></div>
		<div class="flex-item"><strong>Donor</strong>: <br /><select name="slct-donor" >	
					<option value="" >All Donors</option>';

					$donor_list = soco_get_donor_list ();
					foreach ($donor_list as $dl) { 
						$contribution_output .= '<option value="'.$dl->iddonor.'"'.(($donor_id == $dl->iddonor) ? ' selected="selected">' : '>' ).' '.$dl->last_name.', '.$dl->first_name.' @ '.$dl->address1.'</option>';
					} 
					$contribution_output .= '</select>
	</div>
		<div class="flex-item">
			<strong>Event</strong>: <br />
			<select name="slct-category-type" >
					<option value="" selected>All Events</option>';

	//this is the event list. Strangely named
	$category_list = soco_contribution_category_type_list ();
	foreach ($category_list as $cl) { 
		$contribution_output .= '<option value="'.$cl->idcontribution_category_list.'"'.(($event_id == $cl->idcontribution_category_list) ? ' selected="selected">' : '>' ).' '.$cl->category_name.'</option>';
	} 
	$contribution_output .=  '</select>
		</div>
	
	</div>

	<input type="submit" value="Search">
	<input type="reset">
	<input type="checkbox" name="ckbx-active" '.$deleted_checked.'> Search Deleted Records
	
	</form>
	<br />
	'.$too_many_message.'
	<br />
	<h3>'.$record_count.' Contributions Found</h3>
	<div name="contribution-results-container" >
		<table width="100%" border="0">
		  <tbody>
			<tr>
			  <th scope="col">Date</th>
			  <th scope="col">Amount</th>
			  <th scope="col">Cycle</th>
			  <th scope="col">Name</th>
			  <th scope="col">Event</th>
			  <th scope="col">Action</th>
			</tr>';

 	foreach ($contribution_results as $cr) {  
		$contribution_output .= '
			<tr>
			  <td>'.$cr->contribution_date.'</td>
			  <td>'.$cr->amount.'</td>
			  <td>'.$cr->cycle_amount.'</td>
			  <td>'.$cr->last_name.', '.$cr->first_name.'</td>
			  <td>&nbsp;</td>
			  <td><span id="spn-buttons-'.$cr->idcontributions.'" ><button name="btn_edit_contribution" name="btn_edit_contribution" title="Click this to edit this record."><a href="../add-contribution?contribution_id='.$cr->idcontributions.'" title="Click this to edit this contribution"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a></button>';
		if ($deleted_checked == "checked"){
			//showing deleted things - need add button
			$contribution_output .= '<button name="btn_change_contribution" name="btn_return_contribution" value="1" title="Click this button to return this record to an active contribution."><i class="fa fa-undo" aria-hidden="true"></i></button>
			<input type="hidden" name="hdn_contribution_id" value="'.$cr->idcontributions.'">';
		} else {
			//showing active records - need delete button
			$contribution_output .= '<button name="btn_change_contribution" name="btn_delete_contribution" value="0" title="Click this button to remove this record from contributions."><i class="fa fa-trash" aria-hidden="true"></i></button>
			<input type="hidden" name="hdn_contribution_id" value="'.$cr->idcontributions.'">';
		}
				  
		$contribution_output .= '</span></td>
			</tr>';
	}  
	
	$contribution_output .= '</tbody>
		</table>

		<form method="post" id="download_form" action="">
			<input type="submit" name="download_csv" class="button-primary" value="Download CSV File" '.$disabled.' />
			
		</form>
	</div>
</div>';
		
		return $contribution_output;
	}

// this creates the edit/new contributions HTML for the shortcode ////////////////////////////
function soco_display_contribution_form() {
	global $wpdb;	
	$contribution_id = $_GET["contribution_id"];
	
	$alert = "";

	//sets a flag to whether we are adding a new record or updating an old one
	if (isset($_GET['contribution_id'])) {
		//update
		$database_action = "update";
		$text_action = "update";
	} else {
		//insert
		$database_action = "insert";
		$text_action = "add";
	}
///debug////////
//	print_r('$database_action= ');
//	print_r($database_action);
//	print_r('$text_action= ');
//	print_r($text_action);
///////////////////////////////	

	//Is this a POST - If so time to modify the database
	if ((isset($_POST["hdn-form-post"])) && ($_POST["hdn-form-post"] == 1)){
		
		//is post - do stuff!
		if ( (isset($_POST['contribution-date']) ) && 
			(isset($_POST['contribution-amount']) ) && 
			(isset($_POST['cycle-amount']) ) && 
			(isset($_POST['slct-contribution-type']) ) && 
			(isset($_POST['slct-receipt-type']) ) &&
			(isset($_POST['slct-electioneering']) ) && 
			(isset($_POST['slct-donor']) ) && 
			(isset($_POST['slct-category-type']) ) && 
			(isset($_POST['slct-gift-type']) ) ) {
			//all fields are filled out - lets process

			$user_id = get_current_user_id( );
////////////
//			print_r("user_id= ". $user_id);
///////////			
			
			if ($database_action == "insert") { ////////insert action/////////////
				
				$db_action = $wpdb->insert( 
					'soco_contributions', 
					array( 
						'contribution_date' => $_POST['contribution-date'],
						'contribution_type_id' => $_POST['slct-contribution-type'], 
						'amount' => $_POST['contribution-amount'], 
						'cycle_amount' => $_POST['cycle-amount'], 
						'receipt_type_id' => $_POST['slct-receipt-type'], 
						'electioneering' => $_POST['slct-electioneering'],
						'contributor_type_id' => $_POST['slct-contributor-type'],
						'donor_id' => $_POST['slct-donor'], 
						'event_id' => $_POST['slct-category-type'], 
						'gift_type_id' => $_POST['slct-gift-type'], 
						'notes' => $_POST['txar-contribution-notes'],
						'user_id' => $user_id
					)
				);

////////////// debug info /////////////////////////////			
//				$wpdb->show_errors();
//				$wpdb->print_error();
//				print_r('$$db_action= ');
//				print_r($db_action);
//////////////////////////////////////////////////////
			
				if ($db_action == 1){ //checks to see if WP returned an error or not. 0 is FAIL, over 0 is success
					$added = 1;
					$new_contribution_id = $wpdb->insert_id;
				} else {
					$added = 0;	
				}
			
			} elseif ( $database_action == "update" ) { ///////update action/////////////
				$db_action = $wpdb->update( 
					'soco_contributions', 
					array( 
						'contribution_date' => $_POST['contribution-date'],
						'contribution_type_id' => $_POST['slct-contribution-type'], 
						'amount' => $_POST['contribution-amount'], 
						'cycle_amount' => $_POST['cycle-amount'], 
						'receipt_type_id' => $_POST['slct-receipt-type'], 
						'electioneering' => $_POST['slct-electioneering'],
						'contributor_type_id' => $_POST['slct-contributor-type'],
						'donor_id' => $_POST['slct-donor'], 
						'event_id' => $_POST['slct-category-type'], 
						'gift_type_id' => $_POST['slct-gift-type'], 
						'notes' => $_POST['txar-contribution-notes'],
						'user_id' => $user_id
					),
					array( 'idcontributions' => $contribution_id )
				);

////////////// debug info /////////////////////////////			
//				$wpdb->show_errors();
//				$wpdb->print_error();
//				print_r('$db_action= ');
//				print_r($db_action);
//////////////////////////////////////////////////////
			
				if ($db_action == 1){ //checks to see if WP returned an error or not. 0 is FAIL, over 0 is success
					$added = 1;
				} else {
					$added = 0;	
				}

			}
		
//			print_r('$added= ');
//			print_r($added);
//			print_r('$new_contribution_id= ');
//			print_r($new_contribution_id);
		
		} elseif ($_POST['hdn-form-post'] == 1) {
			//something missing. Return data and add error to page
			$alert = '<div class="alert alert-danger" role="alert">
		  Please check the form and try to save it again. Make sure all required fields are filled in. 
		</div>';

		}
		
	} // end of post code
	
//adds alert to page if something was added or errored.	
	if ( $added == 1 ){
		$alert .= '<div class="alert alert-success" role="alert">
					  Contribution has been saved!
					</div>';
	} elseif ( ( isset( $added ) ) && ( $added == 0 ) ){
		$alert .= '<div class="alert alert-danger" role="alert">
					  Uh oh! There was an error with the database. Please try again. 
					</div>';
	} else {
		$alert .= "";
	}

	if ($contribution_id > 0){
		//lets get contibution data and set it.
		$contribution_data = soco_contribution_data($contribution_id);
//		print_r($contribution_data);
	}
	
/// start of form string
	$form_output_string = '
	<div name="div-output-container"> 
		'.$alert.'
		
		<h3>Use the form below to '.$text_action.' a contribution.</h3> 
	<form name="frm-edit-contributions" id="frm-edit-contributions" method="post" action="" >
	
	<div class="alert alert-info" role="alert">
  		<em>TRACER Data</em>';
	
		$form_output_string .= '<div class="flex-container">
			<div class="flex-item">
				<strong>Contributor Type</strong>: <br />
				<select name="slct-contributor-type" required>';
	if ( $contribution_data->contributor_type_id > 0 ) {
		$select_me = $contribution_data->contributor_type_id;
	} else {
		$select_me = 1;
	}

	$contributor_type_list = soco_contributor_type_list ();
	foreach ($contributor_type_list as $ct) { 
		$form_output_string .= '<option value="'.$ct->idcontributor_type_list.'"'.(( $select_me == $ct->idcontributor_type_list) ? ' selected="selected">' : '>' ).' '.$ct->contributor_type_name.'</option>';
	} 
	
	$form_output_string .= '</select>
			</div>
			<div class="flex-item"><strong>Donor</strong>: <br />
				<select name="slct-donor" required>	
					<option value="" selected>Select a donor by clicking the first letter of their last name.</option>';

	$donor_list = soco_get_donor_list ();
	foreach ($donor_list as $dl) { 
		$form_output_string .= '<option value="'.$dl->iddonor.'"'.(($contribution_data->donor_id == $dl->iddonor) ? ' selected="selected">' : '>' ).' '.$dl->last_name.', '.$dl->first_name.' @ '.$dl->address1.'</option>';
	} 
	$form_output_string .= '</select>
			</div>

			<div class="flex-item">
				<br />
				<button name="btn-new-donor" type="button" formnovalidate><a href="../add-donor" title="Click to add a donor.">Add New Donor</a></button>
				<button name="btn-edit-donor" type="button" formnovalidate>Edit Donor</button>
			</div>
		</div> <!-- closes flexrow -->';
	
	$form_output_string .= '<div class="flex-container">
			<div class="flex-item"><strong>Date</strong>: <br /><input type="date" name="contribution-date" value="'.$contribution_data->contribution_date.'" required></div>
			<div class="flex-item"><strong>Amount</strong>: <br />
				<input type="number" name="contribution-amount" step="0.01" min="0" value="'.$contribution_data->amount.'" required ></div>
			<div class="flex-item"><strong>Cycle</strong>: <br />
				<input type="number" name="cycle-amount" step="0.01" min="0" value="'.$contribution_data->cycle_amount.'" required></div>
			<div class="flex-item">
				<strong>Receipt</strong>: <br />
				<select name="slct-receipt-type" required>
					<option value="" selected>Select...</option>';

	$receipt_list = soco_receipt_type_list ();
	foreach ($receipt_list as $rl) { 
		$form_output_string .= '<option value="'.$rl->idreceipt_type_list.'"'.(($contribution_data->receipt_type_id == $rl->idreceipt_type_list) ? ' selected="selected">' : '>' ).' '.$rl->receipt_name.'</option>';
	} 
    
	$form_output_string .= '</select>
			</div>
		</div> <!-- closes flexrow -->
		<div class="flex-container">
			<div class="flex-item">
				<strong>Electioneering</strong>: <br />
				<select name="slct-electioneering" required>';
				
				if ($contribution_data->electioneering == 1 ) {
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
			
			<div class="flex-item"><strong>Contribution Type</strong>: <br />
				<select name="slct-contribution-type" required>
					<option value="" selected>Select...</option>';
                		
							$contribution_list = soco_contribution_type_list ();
						 	foreach ($contribution_list as $cl) { 
                        	    $form_output_string .= '<option value="'.$cl->idcontribution_type_list.'"'.(($contribution_data->contribution_type_id == $cl->idcontribution_type_list) ? ' selected="selected">' : '>' ).' '.$cl->contribution_type_name.'</option>';
                        	} 
	$form_output_string .= '</select>
			</div>
			
		</div> <!-- closes flexrow -->
		</div> <!-- closes alertbox -->';
		

		
	$form_output_string .= '<div class="flex-container">
			<div class="flex-item">
				<strong>Event</strong>: <br />
				<select name="slct-category-type" required>
					<option value="" selected>Select...</option>';

	//this is the event list. Strangely named
	$category_list = soco_contribution_category_type_list ();
	foreach ($category_list as $cl) { 
		$form_output_string .= '<option value="'.$cl->idcontribution_category_list.'"'.(($contribution_data->event_id == $cl->idcontribution_category_list) ? ' selected="selected">' : '>' ).' '.$cl->category_name.'</option>';
	} 
	$form_output_string .=  '</select>
			</div>

			<div class="flex-item">
				<strong>Gift Type</strong>: <br />
				<select name="slct-gift-type" required>
					<option value="" selected>Select...</option>';

	$gift_list = soco_gift_type_list ();
	foreach ($gift_list as $gl) { 
		$form_output_string .= '<option value="'.$gl->idsoco_gift_type_list.'"'.(($contribution_data->gift_type_id == $gl->idsoco_gift_type_list) ? ' selected="selected">' : '>' ).' '.$gl->gift_name.'</option>';
	} 
    $form_output_string .= '</select>
			</div>
			<div class="flex-item">';
	$textLeft = 500 - strlen($contribution_data->notes );             
	$form_output_string .=  '<strong>Notes</strong>: <span id="counterEditNotes">Characters Left: '.$textLeft.'</span> <br />
				<textarea name="txar-contribution-notes" id="txar-contribution-notes" maxlength="500" cols="50" rows="3">'.$contribution_data->notes.'</textarea>
<!-- quick little counter script for usability --> 
  				<script type="text/javascript">
                    jQuery("#txar-contribution-notes").keyup(function () {
                        var left = 500 - jQuery(this).val().length;
                        jQuery("#counterEditNotes").text("Characters Left: " + left);
                    });
                    </script>
<!-- End counter usability script -->
			</div>
		</div> <!-- closes flexrow -->
			
			
		<br />
		<input type="hidden" name="hdn-contribution-id" value="'.$contribution_data->idcontributions.'" >
		<input type="hidden" name="hdn-form-post" value="1" >
		<input type="submit" value="Save">
		<input type="reset">
		
	</form>';
	
	return $form_output_string;
	}




?>