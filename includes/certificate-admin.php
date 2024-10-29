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

// In your main plugin file
add_action('admin_menu', 'cac_display_certificates');

function cac_display_certificates() {
    echo 'Displaying certificates...';
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

    echo '<h2 class="label_heading">Add New Certificate</h2>';
    ?>
    <form method="post" action="" class="certificate_form" enctype="multipart/form-data">

        <label for="title" class="label">Certificate Title:</label>
        <input type="text" name="title" placeholder="Certificate Title" class="input_form" required />

        <label for="certificate_number" class="label">Certificate Number:</label>
        <input type="text" name="certificate_number" placeholder="Certificate Number" class="input_form" required maxlength="7" />

        <label for="item_description" class="label">Item Description:</label>
        <textarea name="item_description" rows="6" placeholder="Item Description" class="resize_textarea" required></textarea>

        <label for="match_used" class="label">Match Used:</label>
        <select name="match_used" id="match_used" class="input_form" required>
            <option value="">Select Option</option>
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select>

        <label id="match_details_label" for="match_details" style="display: none;" class="label">Match Details:</label>
        <textarea name="match_details" rows="6" id="match_details" placeholder="Match Details" style="display: none;" class="resize_textarea"></textarea>

        <label for="item_details" class="label">Item Details:</label>
        <textarea name="item_details" rows="6" placeholder="Item Details" class="resize_textarea"></textarea>
        
        <label for="signed_by_player_name" class="label">Signed By Player Name:</label>
        <input type="text" name="signed_by[player_name]" placeholder="Player Name" class="input_form" required />

        <label for="signed_by_profession" class="label">Signed By Profession:</label>
        <input type="text" name="signed_by[occupation_or_professional_career]" placeholder="Professional Career" class="input_form" required />

       <!-- Signed By Picture -->
        <label for="signed_by_picture" class="label">Signed By Picture:</label>
        <button type="button" class="img_button" id="upload_signed_by_picture" >Upload Picture</button>
        <input type="hidden" name="signed_by[player_picture]" id="signed_by_player_picture_url" />
        <div id="signed_by_player_picture_preview"></div>
        
        <!-- Item Images -->
        <label for="item_images" class="label">Item Images:</label>
        <button type="button" class="img_button" id="upload_item_images">Upload Item Image</button>
        <input type="hidden" name="item_images" id="item_images_url" />
        <div id="item_images_preview"></div>

        <label for="signed_date" class="label">Date:</label>
        <input type="date" name="signed_date" class="input_form certificate_date" required />


        <input type="submit" name="submit_certificate" class="certificate_button" value="Add Certificate" />

        
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
            $('#signed_by_player_picture_preview').html('<img src="' + attachment.url + '" style="max-width: 250px; margin-bottom:30px;" />'); // Show preview
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
            $('#item_images_preview').html('<img src="' + attachment.url + '" style="max-width: 250px; margin-bottom:30px;" />'); // Show preview
        });
        mediaUploader_itemImages.open();
    });
});

    </script>
    <?php
}



//View All Certificate Functions 

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
    echo '<div>';
    ?>
    <h2 class="label_heading">View Certificate</h2>

    <div class="w3-certificate">
        <!-- Certificate Header -->
        <div class="certificate-title">Certificate of Authenticity</div>
        <div class="certificate-subtitle">This certifies the authenticity of the item described below</div>
        <div class="certificate-field" ><strong>Certificate Number:</strong> <?php echo esc_html($certificate->certificate_number); ?></div>
        <div class="signature-date" style="margin-bottom: 40px;"><b>Signature Date:</b> <?php echo esc_html(date('F j, Y', strtotime($certificate->signed_date))); ?></div>
        <!-- Certificate Details -->
        <div class="certificate-details">
            <div class="certificate-field"><strong>Certificate Title:</strong> <?php echo esc_html($certificate->title); ?></div>
            <div class="certificate-field"><strong>Certificate Description:</strong> <?php echo esc_textarea($certificate->item_description); ?></div>

            <div class="certificate-field"><strong>Match Used:</strong> <?php echo esc_html($certificate->match_used); ?></div>
            <?php if ($certificate->match_used === 'Yes'): ?>
                <div class="certificate-field"><strong>Match Details:</strong> <?php echo esc_textarea($certificate->match_details); ?></div>
            <?php endif; ?>

            <div class="certificate-field"><strong>Item Details:</strong> <?php echo esc_textarea($certificate->item_details); ?></div>
        </div>

        <!-- Date and Signature Section -->
        <div class="signature-section">
            <div class="signature-block">
                <!-- Item Images -->
                <?php if (!empty($certificate->item_images)): ?>
                    <div class="items-details-img">
                        <strong>Item Images:</strong>
                        <br>
                        <img src="<?php echo esc_url($certificate->item_images); ?>" style="max-width: 250px; margin-top: 10px;" />
                    </div>
                <?php endif; ?>
            </div>
            <div class="signature-block">
                <?php if (!empty($certificate->signed_by_picture)): ?>
                <br>
                <img src="<?php echo esc_url($certificate->signed_by_picture); ?>" class="player-img" />
            <?php endif; ?>
                <div>Signed By</div>
                <div class="signature-line"></div>    
                <div class="signature-title"><?php echo esc_html($certificate->signed_by_player_name); ?></div>
                <div class="player-profession"><?php echo esc_html($certificate->signed_by_profession); ?></div>

            </div>
        </div>
    </div>
    <div class="button_container">
        <a href="<?php echo admin_url('admin.php?page=all-certificates'); ?>" class="back_button" style="text-decoration:none;"><< Back to All Certificates</a>
    </div>
    
    <?php
    echo '</div>';
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
            <h2 class="label_heading">Edit Certificate</h2>
            <form method="post" action="" class="certificate_form" enctype="multipart/form-data">
                <label for="title" class="label">Certificate Title:</label>
                <input type="text" name="title" placeholder="Title" class="input_form" value="<?php echo esc_attr($certificate->title); ?>" required />

                <label for="certificate_number" class="label">Certificate Number: <p style="color: red; font-size: 12px; margin-bottom:-4px; margin-top:0px;">You Can't Update Certificate Number</p></label>
                <input type="text" name="certificate_number" placeholder="Certificate Number" class="input_form" style="color:#000000;" value="<?php echo esc_attr($certificate->certificate_number); ?>" required maxlength="7" disabled/>

                <label for="item_description" class="label">Item Description:</label>
                <textarea name="item_description" rows="6" placeholder="Item Description" class="resize_textarea" required><?php echo esc_textarea($certificate->item_description); ?></textarea>

                <label for="match_used" class="label">Match Used:</label>
                <select name="match_used" id="match_used" required>
                    <option value="">Select Option</option>
                    <option value="Yes" <?php selected($certificate->match_used, 'Yes'); ?>>Yes</option>
                    <option value="No" <?php selected($certificate->match_used, 'No'); ?>>No</option>
                </select>

                <label id="match_details_label" for="match_details" class="label" style="<?php echo ($certificate->match_used === 'Yes') ? '' : 'display: none;'; ?>">Match Details:</label>
                <textarea name="match_details" id="match_details" rows="6" placeholder="Match Details" class="resize_textarea" style="<?php echo ($certificate->match_used === 'Yes') ? '' : 'display: none;'; ?>"><?php echo esc_textarea($certificate->match_details); ?></textarea>

                <label for="item_details" class="label">Item Details:</label>
                <textarea name="item_details" placeholder="Item Details" rows="6" class="resize_textarea"><?php echo esc_textarea($certificate->item_details); ?></textarea>

                <label for="signed_by_player_name" class="label">Signed By Player Name:</label>
                <input type="text" name="signed_by[player_name]" placeholder="Player Name" class="input_form" value="<?php echo esc_attr($certificate->signed_by_player_name); ?>" required />

                <label for="signed_by_profession" class="label">Signed By Profession:</label>
                <input type="text" name="signed_by[occupation_or_professional_career]" placeholder="Professional Career" class="input_form" value="<?php echo esc_attr($certificate->signed_by_profession); ?>" required />

                <!-- Signed By Picture -->
                <label for="signed_by_picture" class="label">Signed By Picture:</label>
                <button type="button" class="img_button" id="upload_signed_by_picture">Upload Picture</button>
                <input type="hidden" name="signed_by[player_picture]" id="signed_by_player_picture_url" value="<?php echo esc_url($certificate->signed_by_picture); ?>" />
                <div id="signed_by_player_picture_preview">
                    <?php if (!empty($certificate->signed_by_picture)) { ?>
                        <img src="<?php echo esc_url($certificate->signed_by_picture); ?>" style="max-width: 250px; margin-bottom:30px;" />
                    <?php } ?>
                </div>

                <!-- Item Images -->
                <label for="item_images" class="label">Item Images:</label>
                <button type="button" class="img_button" id="upload_item_images">Upload Item Image</button>
                <input type="hidden" name="item_images" id="item_images_url" value="<?php echo esc_url($certificate->item_images); ?>" />
                <div id="item_images_preview">
                    <?php if (!empty($certificate->item_images)) { ?>
                        <img src="<?php echo esc_url($certificate->item_images); ?>" style="max-width: 250px; margin-bottom:30px;" />
                    <?php } ?>
                </div>

                <label for="signed_date" class="label">Date:</label>
                <input type="date" name="signed_date" class="input_form certificate_date" value="<?php echo esc_attr(date('Y-m-d', strtotime($certificate->signed_date))); ?>" required />
                

                <input type="submit" class="certificate_button" name="submit_certificate" value="Update Certificate" />
                
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

    echo '<h2 class="label_heading">All Certificates</h2>';
    echo '<table class="widefat">';
    echo '<thead><tr><th>ID</th><th>Certificate Number</th><th>Signed By (Player Name)</th><th>Mactch Used(Yes/No)</th><th>Signed Date</th><th>Actions</th></tr></thead>';
    echo '<tbody>';
    foreach ($certificates as $certificate) {
        echo '<tr>';
        echo '<td>' . esc_html($certificate->id) . '</td>';
        echo '<td>' . esc_html($certificate->certificate_number) . '</td>';
        echo '<td>' . esc_html($certificate->signed_by_player_name) . '</td>';
        echo '<td>' . esc_html($certificate->match_used) . '</td>';
        echo '<td>' . esc_html(date('m-d-Y', strtotime($certificate->signed_date))) . '</td>';
        echo '<td>';
        echo '<a href="' . admin_url('admin.php?page=view-certificate&certificate_id=' . esc_attr($certificate->id)) . '" class="button_view">View</a> ';
        echo '<a href="' . admin_url('admin.php?page=edit-certificate&certificate_id=' . esc_attr($certificate->id)) . '" class="button_edit">Edit</a> ';
        echo '<a href="' . wp_nonce_url(admin_url('admin.php?page=all-certificates&action=delete&certificate_id=' . esc_attr($certificate->id)), 'delete_certificate_' . $certificate->id) . '" class="button_delete" onclick="return confirm(\'Are you sure you want to delete this certificate?\');">Delete</a>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}