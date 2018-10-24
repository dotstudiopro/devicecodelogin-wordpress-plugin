<?php
/*
Plugin Name:  DSP Device Login Codes
Description:  Gets a DSP customer via Auth0 (during login) and sets up the ability to connect a device login code to an account
Version:      1.0
Author:       DSP
Text Domain:  dsp-auth0
*/

require("functions.php");

// Functionality to be able to process the post from the device code form
add_action( 'wp_ajax_dspdl_ajax_customer_code', 'dspdl_ajax_customer_code' );
add_action( 'wp_ajax_nopriv_dspdl_ajax_customer_code', 'dspdl_ajax_customer_code' );

// Shortcode to show the form for customers to add a device login code
add_shortcode( 'dspdl_show_form', 'dspdl_customer_form_shortcode' );

// Scripts and styles
add_action( 'wp_enqueue_scripts', 'dspdl_scripts' );