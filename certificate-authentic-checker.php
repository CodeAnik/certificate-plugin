<?php
/*
Plugin Name: Certificate Authentic Checker
Description: A custom plugin to check certificate authenticity.
Version: 1.0
Author: Md. Anik Khan
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Include core functions
require_once plugin_dir_path(__FILE__) . 'includes/certificate-crud.php';
require_once plugin_dir_path(__FILE__) . 'includes/certificate-admin.php';

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