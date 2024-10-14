<?php
function cac_create_certificate_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'certificates'; // Table name with WP prefix.
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        certificate_number varchar(7) NOT NULL,
        item_description text NOT NULL,
        match_used varchar(3) NOT NULL,
        match_details text DEFAULT '' NOT NULL,
        item_details text DEFAULT '' NOT NULL,
        signed_by_player_name varchar(100) DEFAULT '' NOT NULL,
        signed_by_profession varchar(100) DEFAULT '' NOT NULL,
        signed_by_picture varchar(255) DEFAULT '' NOT NULL,
        signed_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        item_images varchar(255) DEFAULT '' NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function cac_add_certificate($data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'certificates';

    $wpdb->insert(
        $table_name,
        array(
            'title' => sanitize_text_field($data['title']),
            'certificate_number' => sanitize_text_field($data['certificate_number']),
            'item_description' => sanitize_textarea_field($data['item_description']),
            'match_used' => sanitize_text_field($data['match_used']),
            'match_details' => sanitize_textarea_field($data['match_details']),
            'item_details' => sanitize_textarea_field($data['item_details']),
            'signed_by_player_name' => sanitize_text_field($data['signed_by']['player_name']),
            'signed_by_profession' => sanitize_text_field($data['signed_by']['occupation_or_professional_career']),
            'signed_by_picture' => esc_url_raw($data['signed_by']['player_picture']),
            'signed_date' => date('Y-m-d H:i:s', strtotime($data['signed_date'])),
            'item_images' => esc_url_raw($data['item_images'])
        )
    );

    return $wpdb->insert_id; // Return the ID of the new entry.
}

function cac_get_all_certificates() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'certificates';
    return $wpdb->get_results("SELECT * FROM $table_name");
}

function cac_get_certificate_by_serial($serial_number) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'certificates';
    return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE certificate_number = %s", $serial_number));
}


function cac_update_certificate_by_id($id, $data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'certificates';

    $wpdb->update(
        $table_name,
        array(
            'title' => sanitize_text_field($data['title']),
            'certificate_number' => sanitize_text_field($data['certificate_number']),
            'item_description' => sanitize_textarea_field($data['item_description']),
            'match_used' => sanitize_text_field($data['match_used']),
            'match_details' => sanitize_textarea_field($data['match_details']),
            'item_details' => sanitize_textarea_field($data['item_details']),
            'signed_by_player_name' => sanitize_text_field($data['signed_by']['player_name']),
            'signed_by_profession' => sanitize_text_field($data['signed_by']['occupation_or_professional_career']),
            'signed_by_picture' => esc_url_raw($data['signed_by']['player_picture']),
            'signed_date' => date('Y-m-d H:i:s', strtotime($data['signed_date'])),
            'item_images' => esc_url_raw($data['item_images'])
        ),
        array('id' => $id)
    );

    return $wpdb->rows_affected; // Returns the number of affected rows
}


function cac_delete_certificate_by_id($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'certificates';

    return $wpdb->delete($table_name, array('id' => $id)); // Deletes the row with the given ID
}
