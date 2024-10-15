<?php
// Hook to initialize the REST API
add_action('rest_api_init', function () {
    register_rest_route('cac/v1', '/certificates', array(
        'methods' => 'GET',
        'callback' => 'cac_get_certificates',
        'permission_callback' => '__return_true', // Change this for better security
    ));

    register_rest_route('cac/v1', '/certificates', array(
        'methods' => 'POST',
        'callback' => 'cac_create_certificate',
        'permission_callback' => '__return_true', // Change this for better security
    ));

    register_rest_route('cac/v1', '/certificates/(?P<certificate_number>[a-zA-Z0-9]+)', array(
        'methods' => 'GET',
        'callback' => 'cac_get_certificate',
        'permission_callback' => '__return_true', // Change this for better security
    ));

    register_rest_route('cac/v1', '/certificates/(?P<id>\d+)', array(
        'methods' => 'PUT',
        'callback' => 'cac_update_certificate',
        'permission_callback' => '__return_true', // Change this for better security
    ));

    register_rest_route('cac/v1', '/certificates/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'cac_delete_certificate',
        'permission_callback' => '__return_true', // Change this for better security
    ));
});

// Callback function to get all certificates
function cac_get_certificates($request) {
    $certificates = cac_get_all_certificates();
    return new WP_REST_Response($certificates, 200);
}

// Callback function to create a new certificate
function cac_create_certificate($request) {
    $data = $request->get_json_params();
    
    // Validate data (add more validation as needed)
    if (empty($data['title']) || empty($data['certificate_number'])) {
        return new WP_Error('missing_data', 'Title and certificate number are required.', array('status' => 400));
    }

    $insert_id = cac_add_certificate($data);
    return new WP_REST_Response(array('id' => $insert_id), 201);
}


// Callback function to get a single certificate by certificate_number
function cac_get_certificate($request) {
    $certificate_number = $request['certificate_number']; // Changed from 'id'
    $certificate = cac_get_certificate_by_serial($certificate_number); // Adjusted to use certificate_number

    if (empty($certificate)) {
        return new WP_Error('no_certificate', 'Certificate not found', array('status' => 404));
    }

    return new WP_REST_Response($certificate, 200);
}

// Callback function to update a certificate
function cac_update_certificate($request) {
    $id = $request['id'];
    $data = $request->get_json_params();

    // Here you would implement the update logic, similar to cac_add_certificate()
    // For example, you can create a new function cac_update_certificate_by_id($id, $data)

    // If update is successful, return the updated data
    return new WP_REST_Response($data, 200);
}

// Callback function to delete a certificate
function cac_delete_certificate($request) {
    $id = $request['id'];
    // Here you would implement the delete logic, e.g., deleting from the database

    return new WP_REST_Response(null, 204); // No Content response
}
