<?php

/**
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.dotstudiopro.com
 * @since             1.0.0
 * @package           Dotstudiopro_Device_Logins
 *
 * @wordpress-plugin
 * Plugin Name:       dotstudioPRO Device Login Codes
 * Plugin URI:        https://www.dotstudiopro.com
 * Description:       Gets a DSP customer via Auth0 (during login) and sets up the ability to connect a device login code to an account
 * Version:           1.4.3
 * Author:            dotstudioPRO
 * Author URI:        http://www.dotstudiopro.com
 * License:           GPLv3
 * Text Domain:       dsp-auth0
 */


/**
 * A script/plugin that communicates with our WP Updater service to determine plugin updates
 */
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://updates.wordpress.dotstudiopro.com/wp-update-server/?action=get_metadata&slug=dspdev-device-login-plugin',
    __FILE__,
    'dspdev-device-login-plugin'
);

// Require the DeviceCodes class so we can manipulate/send codes
require("class.php");

require("functions.php");

// Functionality to be able to process the post from the device code form
add_action( 'wp_ajax_dspdl_ajax_customer_code', 'dspdl_ajax_customer_code' );
add_action( 'wp_ajax_nopriv_dspdl_ajax_customer_code', 'dspdl_ajax_customer_code' );

// Shortcode to show the form for customers to add a device login code
add_shortcode( 'dspdl_show_form', 'dspdl_customer_form_shortcode' );

/** Add Menu Entry **/
function dspdl_options_menu() {
    add_options_page('Device Login Options', 'Device Login Options', 'manage_options', 'dspdl-device-login-options', 'dspdl_menu_page');
}

add_action('admin_menu', 'dspdl_options_menu');

// Set up the page for the plugin, pulling the content based on various $_GET global variable contents
function dspdl_menu_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    echo "<div class='wrap'>";

    require "menu.tpl.php";

    echo "</div>";

}
/** End Menu Entry **/

// Scripts and styles
add_action( 'admin_enqueue_scripts', 'dspdl_admin_scripts' );

/** Save Admin Menu Options **/
add_action('admin_post_dspdl_save_admin_options', 'dspdl_save_admin_options');
add_action('wp_ajax_dspdl_save_admin_options', 'dspdl_save_admin_options');
/** End Save Admin Menu Options **/
