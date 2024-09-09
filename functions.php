<?php
function feed_instagram_setup() {
    // Soporte para menús
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'feed-instagram'),
    ));
}

add_action('after_setup_theme', 'feed_instagram_setup');

function my_theme_enqueue_scripts() {
    // Incluir el archivo JavaScript
    wp_enqueue_script('my-script', get_template_directory_uri() . '/script.js', array(), null, true);

    // Asegurarte de que el archivo script.js se cargue en el pie de página
    wp_enqueue_style('my-style', get_template_directory_uri() . '/style.css'); // Si necesitas incluir algún CSS
}
add_action('wp_enqueue_scripts', 'my_theme_enqueue_scripts');

// functions.php
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/upload', array(
        'methods' => 'POST',
        'callback' => 'handle_file_upload',
        'permission_callback' => '__return_true',
    ));
});

function handle_file_upload(WP_REST_Request $request) {
    $file = $request->get_file_params()['file'];
    
    if (!$file) {
        return new WP_Error('no_file', 'No file uploaded.', array('status' => 400));
    }

    $upload = wp_handle_upload($file, array('test_form' => false));
    
    if (isset($upload['error'])) {
        return new WP_Error('upload_error', $upload['error'], array('status' => 500));
    }

    $attachment = array(
        'guid' => $upload['url'],
        'post_mime_type' => $upload['type'],
        'post_title' => sanitize_file_name($upload['file']),
        'post_content' => '',
        'post_status' => 'inherit',
    );
    
    $attach_id = wp_insert_attachment($attachment, $upload['file']);
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
    wp_update_attachment_metadata($attach_id, $attach_data);

    return array('fileUrl' => $upload['url']);
}