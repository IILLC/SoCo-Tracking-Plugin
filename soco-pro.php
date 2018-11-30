<?php
/*
Plugin Name:  SoCo Tracker
Plugin URI:   imageinnovationsllc.com
Description:  SoCo plugin adds multiple shortcodes to handle things for this website. 
Version:      1.0.0
Author:       Troy Whitney
Author URI:   imageinnovationsllc.com
License:      Plugin Copyright @ Image Innovations, LLC
*/

//other code for plugin
require_once('soco-pro-lists.php'); //functions for the lists
require_once('soco-contributions.php'); //functions for the contributions
require_once('soco-donor.php'); //functions for the donors
require_once('soco-expenditures.php'); //functions expenditures
require_once('soco-organizations.php'); //functions expenditures


//required enqueue scripts and styles
function add_scripts(){
  	wp_enqueue_script( 'jquery' );
	
	//setting array for donor selection
	$donor_data = soco_get_donor_list ();
	$donor_array = array();
	foreach ( $donor_data as $dd ) {
		$entry = $dd->last_name.', '.$dd->first_name.' '.$dd->address1;
		$donor_array[] = array(
			'label' => $entry,
			'value' => $dd->iddonor,
			'address1' => $dd->address1
		);
	}
	
	wp_register_script( 'soco_js', plugin_dir_url( __FILE__ ) . 'js/soco.js' );
	wp_localize_script( 'soco_js', 'soco_donor_array', json_encode( $donor_array ) );
	wp_enqueue_script( 'soco_js' );
	
	wp_enqueue_script( 'jquery-ui-autocomplete' );
	
	//styles
	wp_enqueue_style( 'soco_css', plugin_dir_url( __FILE__ ) . 'css/soco.css' );
}
add_action( 'wp_enqueue_scripts', 'add_scripts' );

//adds the shortcode to insert the contributions view mode.  
add_shortcode( 'view_contributions', 'soco_view_contributions_shortcode' );
function soco_view_contributions_shortcode() { 
	$ob_html_string = soco_display_contributions();
	return $ob_html_string;
}

//adds the shortcode to insert the add/edit contributions. View is to search and download CSV files. 
add_shortcode( 'edit_contribution', 'soco_edit_contributions_shortcode' );
function soco_edit_contributions_shortcode() { 
	$ob_html_string = soco_display_contribution_form();
	return $ob_html_string;
}

//adds the shortcode to insert the add/edit donor.  
add_shortcode( 'add_donor', 'soco_add_donor_shortcode' );
function soco_add_donor_shortcode() { 
	$ob_html_string = soco_display_donor_form();
	return $ob_html_string;
}

//adds the shortcode to insert the expense view mode.  
add_shortcode( 'view_expenditures', 'soco_view_expenses_shortcode' );
function soco_view_expenses_shortcode() { 
	$ob_html_string = soco_display_expenses();
	return $ob_html_string;
}

//adds the shortcode to insert the expense form.  
add_shortcode( 'expenditure_form', 'soco_expenditure_form_shortcode' );
function soco_expenditure_form_shortcode() { 
	$ob_html_string = soco_display_expenditure_form();
	return $ob_html_string;
}

//adds the shortcode to insert the organization form.  
add_shortcode( 'organization_form', 'soco_organization_form_shortcode' );
function soco_organization_form_shortcode() { 
	$ob_html_string = soco_display_organization_form();
	return $ob_html_string;
}

?>
