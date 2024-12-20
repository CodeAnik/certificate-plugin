<?php
/*
Plugin Name: Certificate Authentic Checker
Description: A custom plugin to check certificate authenticity.
Version: 2.0.1
Last Upadate: 05/11/2024
Author: Dgency
Author URI: https://dgency.com/our-team/

*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.it
}

// Include core functions
require_once plugin_dir_path(__FILE__) . 'includes/certificate-crud.php';
require_once plugin_dir_path(__FILE__) . 'includes/certificate-admin.php';


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

// Include API functions
require_once plugin_dir_path(__FILE__) . 'includes/certificate-api.php';

// Enqueue CSS for the admin form
add_action('admin_enqueue_scripts', 'certificate_admin_styles');
function certificate_admin_styles() {
    wp_enqueue_style('certificate-admin-css', plugin_dir_url(__FILE__) . 'css/admin-styles.css');
}
