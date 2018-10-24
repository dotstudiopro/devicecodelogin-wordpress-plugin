<?php


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
    wp_enqueue_style( 'dspdl-style', plugin_dir_url( __FILE__ ) . '/style.css');
    wp_enqueue_script( 'dspdl-main', plugin_dir_url( __FILE__ ) . '/main.js', array('jquery') , '1.0.0', true );
    wp_localize_script( 'dspdl-main', 'dspdl_ajax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}

