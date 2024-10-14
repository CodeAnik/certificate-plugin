<?php
add_action('admin_menu', 'cac_add_admin_menu');

function cac_add_admin_menu() {
    add_menu_page(
    'Certificate Checker',
    'Certificates', // Change the menu name to 'Certificates'
    'manage_options',
    'certificate-checker',
    'cac_display_certificates',
    'dashicons-awards', // Change the icon to 'Award'
    6
    );


    add_submenu_page(
    'certificate-checker',
    'Add Certificates',
    'Add Certificates', // Update submenu name
    'manage_options',
    'add-certificate',
    'cac_add_certificate_form'
    );

    add_submenu_page(
        'certificate-checker',
        'All Certificates',
        'All Certificates', // Update submenu name
        'manage_options',
        'all-certificates',
        'cac_display_all_certificates'
    );
}

function cac_add_certificate_form() {

    $is_editing = isset($_GET['id']);
    $certificate = $is_editing ? cac_get_certificate_by_serial($_GET['id']) : null;

    if (isset($_POST['submit_certificate'])) {
        // Process form submission
        $data = $_POST;
        $data['signed_by']['player_picture'] = ''; // Placeholder for the picture
        $data['item_images'] = ''; // Placeholder for item images
        if ($is_editing) {
            cac_update_certificate_by_id($_GET['id'], $data); // Update certificate
        } else {
            cac_add_certificate($data); // Save new certificate
        }

        echo '<div class="notice notice-success is-dismissible"><p>Certificate ' . ($is_editing ? 'updated' : 'added') . ' successfully!</p></div>';
    }

    // Prepopulate fields if editing
    $title = $is_editing ? $certificate->title : '';
    $certificate_number = $is_editing ? $certificate->certificate_number : '';
    $item_description = $is_editing ? $certificate->item_description : '';
    $match_used = $is_editing ? $certificate->match_used : '';
    $match_details = $is_editing ? $certificate->match_details : '';
    $signed_by_player_name = $is_editing ? $certificate->signed_by_player_name : '';
    $signed_by_profession = $is_editing ? $certificate->signed_by_profession : '';
    $signed_date = $is_editing ? $certificate->signed_date : '';
    $item_images = $is_editing ? $certificate->item_images : '';

    echo '<h2>' . ($is_editing ? 'Edit Certificate' : 'Add New Certificate') . '</h2>';
    ?>
    <!-- <form method="post" action="" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Title" required />
        <input type="text" name="certificate_number" placeholder="Certificate Number" required maxlength="7" />
        <textarea name="item_description" placeholder="Item Description" required></textarea>
        <input type="text" name="match_used" placeholder="Match Used (Yes/No)" required />
        <textarea name="match_details" placeholder="Match Details"></textarea>
        <textarea name="item_details" placeholder="Item Details"></textarea>
        
        <label for="signed_by_player_name">Signed By Player Name:</label>
        <input type="text" name="signed_by[player_name]" placeholder="Player Name" required />

        <label for="signed_by_profession">Signed By Profession:</label>
        <input type="text" name="signed_by[occupation_or_professional_career]" placeholder="Professional Career" required />

        <label for="signed_by_picture">Signed By Picture:</label>
        <button type="button" class="button" id="upload_signed_by_picture">Upload Picture</button>
        
        <label for="item_images">Item Images:</label>
        <button type="button" class="button" id="upload_item_images">Upload Item Image</button>

        <input type="date" name="signed_date" required />
        <input type="submit" name="submit_certificate" value="Add Certificate" />
    </form> -->
    <form method="post" action="" enctype="multipart/form-data">
        <input type="text" name="title" value="<?php echo esc_attr($title); ?>" placeholder="Title" required />
        <input type="text" name="certificate_number" value="<?php echo esc_attr($certificate_number); ?>" placeholder="Certificate Number" required maxlength="7" />
        <textarea name="item_description" placeholder="Item Description" required><?php echo esc_textarea($item_description); ?></textarea>
        <input type="text" name="match_used" value="<?php echo esc_attr($match_used); ?>" placeholder="Match Used (Yes/No)" required />
        <textarea name="match_details" placeholder="Match Details"><?php echo esc_textarea($match_details); ?></textarea>

        <!-- Add other fields as necessary with prepopulated values -->
        
        <input type="submit" name="submit_certificate" value="<?php echo $is_editing ? 'Update Certificate' : 'Add Certificate'; ?>" />
    </form>
    <?php
    
    
    
    // Enqueue the media uploader script
    wp_enqueue_media();
    ?>
    <script>
        jQuery(document).ready(function($) {
            var mediaUploader;

            $('#upload_signed_by_picture').click(function(e) {
                e.preventDefault();
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                mediaUploader = wp.media({
                    title: 'Upload Picture',
                    button: {
                        text: 'Select Image'
                    },
                    multiple: false // Set to true to allow multiple images to be selected
                });

                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('[name="signed_by[player_picture]"]').val(attachment.url); // Set the URL of the uploaded image
                });
                mediaUploader.open();
            });

            $('#upload_item_images').click(function(e) {
                e.preventDefault();
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                mediaUploader = wp.media({
                    title: 'Upload Item Image',
                    button: {
                        text: 'Select Image'
                    },
                    multiple: false // Set to true to allow multiple images to be selected
                });

                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    $('[name="item_images"]').val(attachment.url); // Set the URL of the uploaded image
                });
                mediaUploader.open();
            });
        });
    </script>
    <?php
}

function cac_display_all_certificates() {
    $certificates = cac_get_all_certificates();

    echo '<h2>All Certificates</h2>';
    echo '<table class="widefat">';
    echo '<thead><tr><th>ID</th><th>Title</th><th>Certificate Number</th><th>Actions</th></tr></thead>';
    echo '<tbody>';
    foreach ($certificates as $certificate) {
    $view_url = admin_url('admin.php?page=view-certificate&id=' . $certificate->id);
    $edit_url = admin_url('admin.php?page=edit-certificate&id=' . $certificate->id);
    $delete_url = admin_url('admin.php?page=delete-certificate&id=' . $certificate->id);

    echo '<tr>';
    echo '<td>' . esc_html($certificate->id) . '</td>';
    echo '<td>' . esc_html($certificate->title) . '</td>';
    echo '<td>' . esc_html($certificate->certificate_number) . '</td>';
    echo '<td>
        <a href="' . $view_url . '" class="button">View</a>
        <a href="' . $edit_url . '" class="button">Edit</a>
        <a href="' . $delete_url . '" class="button" onclick="return confirm(\'Are you sure you want to delete this certificate?\')">Delete</a>
    </td>';
    echo '</tr>';
    }

}

add_action('admin_init', 'cac_handle_certificate_actions');

function cac_handle_certificate_actions() {
    if (isset($_GET['page']) && $_GET['page'] === 'delete-certificate' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        cac_delete_certificate_by_id($id);
        wp_redirect(admin_url('admin.php?page=all-certificates'));
        exit;
    }

    if (isset($_GET['page']) && $_GET['page'] === 'edit-certificate' && isset($_GET['id'])) {
        $id = intval($_GET['id']);
        // Display the edit form or process edit logic here.
        // You can reuse the form used in `cac_add_certificate_form()` to prepopulate the data.
    }
}






