<?php

/**
 * Simplify the cURL execution for various API commands within the curl commands class
 *
 * @return null
 */
function dspdl_save_admin_options() {
	if(!isset($_POST['dspdl_dsp_api_key'])) {
		return wp_send_json_failure(400, array("message" => "Missing API Key"));
	}
	update_option("dspdl_dsp_api_key", $_POST['dspdl_dsp_api_key']);
	return wp_send_json_success(200, array("message" => "Key saved successfully"));
}

/**
 * Simplify the cURL execution for various API commands within the curl commands class
 *
 * @param string $curl_url The URL to do the cUrl request to
 * @param string $curl_request_type The type of request, generally POST or GET
 * @param string $curl_post_fields The fields we want to POST, if it's a POST request
 * @param object $curl_header Any necessary header values, like an API token
 *
 * @return Object The curl response object
 */
function dspdl_api_run_curl_command($curl_url, $curl_request_type, $curl_post_fields, $curl_header) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL            => $curl_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => "",
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => $curl_request_type,
        CURLOPT_POSTFIELDS     => $curl_request_type == 'POST' ? $curl_post_fields : "",
        CURLOPT_HTTPHEADER     => $curl_header,
    ));

    $response = curl_exec($curl);
    $err      = curl_error($curl);

    curl_close($curl);
    return (object) compact('response', 'err');
}

/**
 * Get the client token for the current user
 *
 * @param string $code The device code to send to the DSP API
 *
 * @return Boolean Whether or not we succeeded in sending the customer code
 */
function dspdl_get_client_token () {
    $user_id = get_current_user_id();
    if (empty($user_id)) return false; // User isn't logged in
    $token = get_user_meta( $user_id, "dotstudiopro_client_token", true);
    $expiration = get_user_meta( $user_id, "dotstudiopro_client_token_expiration", true);
    // The user didn't log in via the Auth0 Lock if this empty, so we have
    // no way of connecting the user to DSP; we cannot do anything token-related
    if (empty($token)) return false;
    // If the token isn't expired yet...
    if ($expiration - time() > 0) return $token;
    $dspapi = new Dwg_Dotstudio();
    $spotlight = $dspapi->DotApiCommand("refresh-client-token", array("client_token" => $token));
    // If we failed to get a token from the API, we can't proceed
    if (!$spotlight) return false;
    // Looks like everything worked, save the new token
    update_user_meta( $user_id, "dotstudiopro_client_token", $spotlight);
    update_user_meta( $user_id, "dotstudiopro_client_token_expiration", time() + 5400);
    return $spotlight;
}

/**
 * Send a device login code to the DSP API
 *
 * @param string $code The device code to send to the DSP API
 *
 * @return Boolean Whether or not we succeeded in sending the customer code
 */
function dspdl_send_customer_code ( $code ) {
	$token = dspdl_get_client_token();
	if (empty($token)) return false;
	if (empty($code)) return false;

	$dspapi = new Dwg_Dotstudio();
	return $dspapi->DotApiCommand("send-device-code", array("code" => $code));
}

/**
 * Process AJAX request to send customer code and return response based on how sending goes.
 */
function dspdl_ajax_customer_code() {
	$token = dspdl_get_client_token();
	$toReturn = new stdClass;
	$toReturn->success = false;
	$toReturn->message = "Could not complete the device code connection: ";
	if (empty($token)) {
		// We can't get a client token, so we can't connect the code with a customer
		$toReturn->message .= "Invalid or missing client token.";
		die(json_encode($toReturn));
	}
	if (empty($_POST['code'])) {
		// We weren't given a code, so nothing to do
		$toReturn->message .= "Code was not sent with request.";
		die(json_encode($toReturn));
	}
	$send = dspdl_send_customer_code ( $_POST['code'] );
	// If sending failed, nothing to do
	if (!$send->success) {
		$toReturn->message .= $send->error;
		die(json_encode($toReturn));
	}

	$toReturn->success = true;
	$toReturn->message = "Account connected successfully! Please check your device.";
	die(json_encode($toReturn));
}
/**
 * The form for submitting a device login code
 *
 * @return String The HTML for the form
 */

function dspdl_customer_form_shortcode() {
	$user_id = get_current_user_id();
	$token = dspdl_get_client_token();

	if (empty($user_id) || empty($token)) {
		ob_start(); ?>
			<div class='dspdl-customer-form-container'>
				<h3 class='dspdl-customer-form-login-message'>You must be logged in to submit a code. <button class='dspdl-customer-login-button'>Log in or sign up here</button></h3>
			</div>
		<?php
		$form = ob_get_contents();
		ob_end_clean();
	} else {
		ob_start(); ?>
			<div class='dspdl-customer-form-container'>
				<div class='dspdl-customer-form-code'>
					<input type='text' name='dspdl-customer-code' />
				</div>
				<div class='dspdl-customer-form-button'>
					<div class='dspdl-customer-form-message'></div>
					<button class='btn btn-primary' type='button'>Submit Code</button>
				</div>
			</div>
		<?php
		$form = ob_get_contents();
		ob_end_clean();
	}
	return $form;
}

/**
 * Enqueue scripts and styles.
 */
function dspdl_scripts() {
    wp_enqueue_style( 'dspdl-style', plugin_dir_url( __FILE__ ) . '/css/style.css');
    wp_enqueue_script( 'dspdl-main', plugin_dir_url( __FILE__ ) . '/js/main.js', array('jquery') , '1.0.0', true );
    wp_localize_script( 'dspdl-main', 'dspdl_ajax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}

/**
 * Enqueue scripts for admin.
 */
function dspdl_admin_scripts() {
    wp_enqueue_style( 'dspdl-admin-style', plugin_dir_url( __FILE__ ) . '/css/admin.css');
    wp_enqueue_script( 'dspdl-admin-main', plugin_dir_url( __FILE__ ) . '/js/admin.js', array('jquery') , '1.0.0', true );
    wp_localize_script( 'dspdl-admin-main', 'dspdl_ajax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}

