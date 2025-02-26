<?php
/*
Plugin Name: Certificate Authentic Checker
Description: A WordPress plugin to verify the authenticity of pickleball items using a seven-digit serial number.
Version: 2.0.3
Last Upadate: 26/02/2025
Author: Dgency
Author URI: https://dgency.com/our-team/
License: GPL2

*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.it
}

// Include core functions
require_once plugin_dir_path(__FILE__) . 'includes/certificate-crud.php';
require_once plugin_dir_path(__FILE__) . 'includes/certificate-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/certificate-api.php';


// Hook for plugin uninstallation
function cac_plugin_uninstall() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'certificate_information';

    // SQL to drop the table on plugin uninstallation
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
}
register_uninstall_hook(__FILE__, 'cac_plugin_uninstall');


// Plugin initialization
function cac_activate_plugin() {
    // Create the custom database table for certificates.
    cac_create_certificate_table();
}
register_activation_hook(__FILE__, 'cac_activate_plugin');

// Deactivation Hook (optional)
register_deactivation_hook(__FILE__, 'cac_deactivate_plugin');
function cac_deactivate_plugin() {
    // Actions to perform on plugin deactivation (if necessary)
}


// Enqueue CSS for the admin form
add_action('admin_enqueue_scripts', 'certificate_admin_styles');
function certificate_admin_styles() {
    wp_enqueue_style('certificate-admin-css', plugin_dir_url(__FILE__) . 'css/admin-styles.css');
}
