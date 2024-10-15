<?php
add_action('admin_menu', 'cac_add_admin_menu');

function cac_add_admin_menu() {
    add_menu_page(
        'Certificate Checker',
        'Certificates',
        'manage_options',
        'certificate-checker',
        'cac_display_certificates',
        'dashicons-awards',
        6
    );
    add_submenu_page(
        'certificate-checker',
        'Add Certificate',
        'Add Certificate',
        'manage_options',
        'add-certificate',
        'cac_add_certificate_form'
    );

    add_submenu_page(
        'certificate-checker',
        'All Certificates',
        'All Certificates',
        'manage_options',
        'all-certificates',
        'cac_display_all_certificates'
    );
    add_submenu_page(
        null, // We don't want this page to appear in the menu, so we set it to null
        'View Certificate',
        'View Certificate',
        'manage_options',
        'view-certificate',
        'cac_view_certificate'
    );
    add_submenu_page(
    null, // No menu parent, so this page is hidden from the menu
        'Edit Certificate',
        'Edit Certificate',
        'manage_options',
        'edit-certificate',
        'cac_edit_certificate_form'
    );
}

function cac_add_certificate_form() {
    if (isset($_POST['submit_certificate'])) {
        // Process form submission
        $data = $_POST;

        // Ensure URLs are captured
        $data['signed_by']['player_picture'] = sanitize_text_field($data['signed_by']['player_picture']); // Capture the signed by image URL
        $data['item_images'] = sanitize_text_field($data['item_images']); // Capture the item image URL

        $result = cac_add_certificate($data); // Save to database

        if (is_wp_error($result)) {
            // Display error message if the certificate number is not unique
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($result->get_error_message()) . '</p></div>';
        } else {
            echo '<div class="notice notice-success is-dismissible"><p>Certificate added successfully!</p></div>';
        }
    }

    echo '<h2>Add New Certificate</h2>';
    ?>
    <form method="post" action="" enctype="multipart/form-data">
        <label for="title">Certificate Title:</label>
        <input type="text" name="title" placeholder="Title" required />

        <label for="certificate_number">Certificate Number:</label>
        <input type="text" name="certificate_number" placeholder="Certificate Number" required maxlength="7" />

        <label for="item_description">Item Description:</label>
        <textarea name="item_description" placeholder="Item Description" required></textarea>

        <label for="match_used">Match Used:</label>
        <select name="match_used" id="match_used" required>
            <option value="">Select Option</option>
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select>

        <label id="match_details_label" for="match_details" style="display: none;">Match Details:</label>
        <textarea name="match_details" id="match_details" placeholder="Match Details" style="display: none;"></textarea>

        <label for="item_details">Item Details:</label>
        <textarea name="item_details" placeholder="Item Details"></textarea>
        
        <label for="signed_by_player_name">Signed By Player Name:</label>
        <input type="text" name="signed_by[player_name]" placeholder="Player Name" required />

        <label for="signed_by_profession">Signed By Profession:</label>
        <input type="text" name="signed_by[occupation_or_professional_career]" placeholder="Professional Career" required />

       <!-- Signed By Picture -->
        <label for="signed_by_picture">Signed By Picture:</label>
        <button type="button" class="button" id="upload_signed_by_picture">Upload Picture</button>
        <input type="hidden" name="signed_by[player_picture]" id="signed_by_player_picture_url" />
        <div id="signed_by_player_picture_preview"></div>
        
        <!-- Item Images -->
        <label for="item_images">Item Images:</label>
        <button type="button" class="button" id="upload_item_images">Upload Item Image</button>
        <input type="hidden" name="item_images" id="item_images_url" />
        <div id="item_images_preview"></div>

        <label for="signed_date">Date:</label>
        <input type="date" name="signed_date" required />

        <input type="submit" name="submit_certificate" value="Add Certificate" />
    </form>

    <script>
        jQuery(document).ready(function($) {
            $('#match_used').change(function() {
                if ($(this).val() === 'Yes') {
                    $('#match_details').show(); // Show textarea if "Yes" is selected
                    $('#match_details_label').show(); // Show textarea if "Yes" is selected
                } else {
                    $('#match_details').hide(); // Hide textarea if "No" is selected
                    $('#match_details_label').hide(); // Hide textarea if "No" is selected
                    $('#match_details').val('No'); // Clear the textarea when hiding
                }
            });
        });
    </script>
    <?php
    
    
    
    // Enqueue the media uploader script
    wp_enqueue_media();
    ?>
 <script>
    jQuery(document).ready(function($) {
    var mediaUploader_signedBy, mediaUploader_itemImages;

    // Signed By Picture Uploader
    $('#upload_signed_by_picture').click(function(e) {
        e.preventDefault();
        if (mediaUploader_signedBy) {
            mediaUploader_signedBy.open();
            return;
        }
        mediaUploader_signedBy = wp.media({
            title: 'Upload Signed By Picture',
            button: {
                text: 'Select Image'
            },
            multiple: false
        });
        mediaUploader_signedBy.on('select', function() {
            var attachment = mediaUploader_signedBy.state().get('selection').first().toJSON();
            $('#signed_by_player_picture_url').val(attachment.url); // Set the URL in the hidden field
            $('#signed_by_player_picture_preview').html('<img src="' + attachment.url + '" style="max-width: 150px;" />'); // Show preview
        });
        mediaUploader_signedBy.open();
    });

    // Item Images Uploader
    $('#upload_item_images').click(function(e) {
        e.preventDefault();
        if (mediaUploader_itemImages) {
            mediaUploader_itemImages.open();
            return;
        }
        mediaUploader_itemImages = wp.media({
            title: 'Upload Item Image',
            button: {
                text: 'Select Image'
            },
            multiple: false
        });
        mediaUploader_itemImages.on('select', function() {
            var attachment = mediaUploader_itemImages.state().get('selection').first().toJSON();
            $('#item_images_url').val(attachment.url); // Set the URL in the hidden field
            $('#item_images_preview').html('<img src="' + attachment.url + '" style="max-width: 150px;" />'); // Show preview
        });
        mediaUploader_itemImages.open();
    });
});

    </script>
    <?php
}

function cac_view_certificate() {
    if (!isset($_GET['certificate_id'])) {
        echo '<div class="notice notice-error"><p>No certificate found!</p></div>';
        return;
    }

    $certificate_id = intval($_GET['certificate_id']);
    $certificate = cac_get_certificate_by_id($certificate_id); // Fetch the certificate using the ID.

    if (!$certificate) {
        echo '<div class="notice notice-error"><p>Invalid certificate ID!</p></div>';
        return;
    }

    // Display the certificate details
    echo '<h2>View Certificate</h2>';
    echo '<p><strong>Title:</strong> ' . esc_html($certificate->title) . '</p>';
    echo '<p><strong>Certificate Number:</strong> ' . esc_html($certificate->certificate_number) . '</p>';
    echo '<p><strong>Item Description:</strong> ' . esc_html($certificate->item_description) . '</p>';
    echo '<p><strong>Match Used:</strong> ' . esc_html($certificate->match_used) . '</p>';
    echo '<p><strong>Match Details:</strong> ' . esc_html($certificate->match_details) . '</p>';
    echo '<p><strong>Item Details:</strong> ' . esc_html($certificate->item_details) . '</p>';
    echo '<p><strong>Signed By:</strong> ' . esc_html($certificate->signed_by_player_name) . ' (' . esc_html($certificate->signed_by_profession) . ')</p>';
    echo '<p><strong>Signed Date:</strong> ' . esc_html(date('Y-m-d', strtotime($certificate->signed_date))) . '</p>';

    if (!empty($certificate->signed_by_picture)) {
        echo '<p><strong>Signed By Picture:</strong><br><img src="' . esc_url($certificate->signed_by_picture) . '" style="max-width:200px;"></p>';
    }

    if (!empty($certificate->item_images)) {
        echo '<p><strong>Item Images:</strong><br><img src="' . esc_url($certificate->item_images) . '" style="max-width:200px;"></p>';
    }

    echo '<a href="' . admin_url('admin.php?page=all-certificates') . '" class="button">Back to Certificates</a>';
}

//delete certificate
add_action('admin_init', 'cac_handle_delete_certificate');

function cac_handle_delete_certificate() {
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['certificate_id'])) {
        $certificate_id = intval($_GET['certificate_id']);

        // Verify the nonce for security
        if (!check_admin_referer('delete_certificate_' . $certificate_id)) {
            wp_die('Security check failed.');
        }

        // Delete the certificate from the database
        $deleted = cac_delete_certificate_by_id($certificate_id);

        if ($deleted) {
            // Redirect back to the certificate list with a success message
            wp_redirect(admin_url('admin.php?page=all-certificates&deleted=true'));
            exit;
        } else {
            // Handle the error if the certificate was not deleted
            wp_die('Failed to delete the certificate.');
        }
    }
}

//Update the certificate information
function cac_edit_certificate_form() {
   

    if (isset($_GET['certificate_id'])) {
        $certificate_id = intval($_GET['certificate_id']);
        $certificate = cac_get_certificate_by_id($certificate_id);

         // Display success message after update
        if (isset($_GET['updated']) && $_GET['updated'] == 'true') {
            echo '<div class="notice notice-success is-dismissible"><p>Certificate updated successfully!</p></div>';
        }

        if ($certificate) {
            if (isset($_POST['submit_certificate'])) {
                // Process form submission
                $data = $_POST;

                // Ensure URLs are captured
                $data['signed_by']['player_picture'] = sanitize_text_field($data['signed_by']['player_picture']); // Capture the signed by image URL
                $data['item_images'] = sanitize_text_field($data['item_images']); // Capture the item image URL

                $result = cac_update_certificate_by_id($certificate_id, $data); // Update in the database

                   if (is_wp_error($result)) {
                    // Display error message if the certificate number is not unique
                        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($result->get_error_message()) . '</p></div>';
                    } else {
                        // Redirect after update to reload the form with updated data
                      echo '<script>window.location.href = "' . admin_url('admin.php?page=edit-certificate&certificate_id=' . $certificate_id . '&updated=true') . '";</script>';
                      exit;
                    }

                
            }

            // Display the edit form pre-filled with certificate data
            ?>
            <h2>Edit Certificate</h2>
            <form method="post" action="" enctype="multipart/form-data">
                <label for="title">Certificate Title:</label>
                <input type="text" name="title" placeholder="Title" value="<?php echo esc_attr($certificate->title); ?>" required />

                <label for="certificate_number">Certificate Number:</label>
                <input type="text" name="certificate_number" placeholder="Certificate Number" value="<?php echo esc_attr($certificate->certificate_number); ?>" required maxlength="7" />

                <label for="item_description">Item Description:</label>
                <textarea name="item_description" placeholder="Item Description" required><?php echo esc_textarea($certificate->item_description); ?></textarea>

                <label for="match_used">Match Used:</label>
                <select name="match_used" id="match_used" required>
                    <option value="">Select Option</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>

                <label id="match_details_label" for="match_details">Match Details:</label>
                <textarea name="match_details" id="match_details" placeholder="Match Details" style="display: none;"></textarea>

                <label for="item_details">Item Details:</label>
                <textarea name="item_details" placeholder="Item Details"><?php echo esc_textarea($certificate->item_details); ?></textarea>

                <label for="signed_by_player_name">Signed By Player Name:</label>
                <input type="text" name="signed_by[player_name]" placeholder="Player Name" value="<?php echo esc_attr($certificate->signed_by_player_name); ?>" required />

                <label for="signed_by_profession">Signed By Profession:</label>
                <input type="text" name="signed_by[occupation_or_professional_career]" placeholder="Professional Career" value="<?php echo esc_attr($certificate->signed_by_profession); ?>" required />

                <!-- Signed By Picture -->
                <label for="signed_by_picture">Signed By Picture:</label>
                <button type="button" class="button" id="upload_signed_by_picture">Upload Picture</button>
                <input type="hidden" name="signed_by[player_picture]" id="signed_by_player_picture_url" value="<?php echo esc_url($certificate->signed_by_picture); ?>" />
                <div id="signed_by_player_picture_preview">
                    <?php if (!empty($certificate->signed_by_picture)) { ?>
                        <img src="<?php echo esc_url($certificate->signed_by_picture); ?>" style="max-width: 150px;" />
                    <?php } ?>
                </div>

                <!-- Item Images -->
                <label for="item_images">Item Images:</label>
                <button type="button" class="button" id="upload_item_images">Upload Item Image</button>
                <input type="hidden" name="item_images" id="item_images_url" value="<?php echo esc_url($certificate->item_images); ?>" />
                <div id="item_images_preview">
                    <?php if (!empty($certificate->item_images)) { ?>
                        <img src="<?php echo esc_url($certificate->item_images); ?>" style="max-width: 150px;" />
                    <?php } ?>
                </div>

                <label for="signed_date">Date:</label>
                <input type="date" name="signed_date" value="<?php echo esc_attr(date('Y-m-d', strtotime($certificate->signed_date))); ?>" required />

                <input type="submit" name="submit_certificate" value="Update Certificate" />
            </form>

             <script>
                jQuery(document).ready(function($) {
                    $('#match_used').change(function() {
                        if ($(this).val() === 'Yes') {
                            $('#match_details').show(); // Show textarea if "Yes" is selected
                            $('#match_details_label').show(); // Show textarea if "Yes" is selected
                        } else {
                            $('#match_details').hide(); // Hide textarea if "No" is selected
                            $('#match_details_label').hide(); // Hide textarea if "No" is selected
                            $('#match_details').val('No'); // Clear the textarea when hiding
                        }
                    });
                });
            </script>

            <?php
            // Enqueue the media uploader script
            wp_enqueue_media();
            ?>
             <script>
               jQuery(document).ready(function($) {
                    var mediaUploader_signedBy, mediaUploader_itemImages;

                    // Signed By Picture Uploader
                    $('#upload_signed_by_picture').click(function(e) {
                        e.preventDefault();
                        if (mediaUploader_signedBy) {
                            mediaUploader_signedBy.open();
                            return;
                        }
                        mediaUploader_signedBy = wp.media({
                            title: 'Upload Signed By Picture',
                            button: {
                                text: 'Select Image'
                            },
                            multiple: false
                        });
                        mediaUploader_signedBy.on('select', function() {
                            var attachment = mediaUploader_signedBy.state().get('selection').first().toJSON();
                            $('#signed_by_player_picture_url').val(attachment.url); // Set the URL in the hidden field
                            $('#signed_by_player_picture_preview').html('<img src="' + attachment.url + '" style="max-width: 150px;" />'); // Show preview
                        });
                        mediaUploader_signedBy.open();
                    });

                    // Item Images Uploader
                    $('#upload_item_images').click(function(e) {
                        e.preventDefault();
                        if (mediaUploader_itemImages) {
                            mediaUploader_itemImages.open();
                            return;
                        }
                        mediaUploader_itemImages = wp.media({
                            title: 'Upload Item Image',
                            button: {
                                text: 'Select Image'
                            },
                            multiple: false
                        });
                        mediaUploader_itemImages.on('select', function() {
                            var attachment = mediaUploader_itemImages.state().get('selection').first().toJSON();
                            $('#item_images_url').val(attachment.url); // Set the URL in the hidden field
                            $('#item_images_preview').html('<img src="' + attachment.url + '" style="max-width: 150px;" />'); // Show preview
                        });
                        mediaUploader_itemImages.open();
                    });
                });

            </script>
            <?php
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>Certificate not found.</p></div>';
        }
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>Invalid certificate ID.</p></div>';
    }
}


function cac_display_all_certificates() {

     if (isset($_GET['deleted']) && $_GET['deleted'] === 'true') {
        echo '<div class="notice notice-success is-dismissible"><p>Certificate deleted successfully!</p></div>';
    }

    $certificates = cac_get_all_certificates();

    echo '<h2>All Certificates</h2>';
    echo '<table class="widefat">';
    echo '<thead><tr><th>ID</th><th>Title</th><th>Certificate Number</th><th>Actions</th></tr></thead>';
    echo '<tbody>';
    foreach ($certificates as $certificate) {
        echo '<tr>';
        echo '<td>' . esc_html($certificate->id) . '</td>';
        echo '<td>' . esc_html($certificate->title) . '</td>';
        echo '<td>' . esc_html($certificate->certificate_number) . '</td>';
        echo '<td>';
        echo '<a href="' . admin_url('admin.php?page=view-certificate&certificate_id=' . esc_attr($certificate->id)) . '" class="button">View</a> ';
        echo '<a href="' . admin_url('admin.php?page=edit-certificate&certificate_id=' . esc_attr($certificate->id)) . '" class="button">Edit</a> ';
        echo '<a href="' . wp_nonce_url(admin_url('admin.php?page=all-certificates&action=delete&certificate_id=' . esc_attr($certificate->id)), 'delete_certificate_' . $certificate->id) . '" class="button delete-button" onclick="return confirm(\'Are you sure you want to delete this certificate?\');">Delete</a>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}