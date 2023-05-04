<?php
add_filter( 'use_block_editor_for_post', '__return_false' );

add_action('wp_enqueue_scripts','style_theme');
function style_theme(){
    wp_enqueue_style('style', get_stylesheet_uri());
    wp_enqueue_style('main', get_template_directory_uri() . '/assets/css/main.css');
    wp_enqueue_style('custom-google-fonts-Montserrat:wght@300;400;600;700', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&display=swap');
    wp_enqueue_style('custom-google-fonts-Libre+Franklin:wght@700', 'https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@700&display=swap');
    wp_enqueue_style('custom-google-fonts-Noto+Sans:wght@800', 'https://fonts.googleapis.com/css2?family=Noto+Sans:wght@800&display=swap');
    wp_enqueue_style('custom-google-fonts-Open+Sans&display', 'https://fonts.googleapis.com/css2?family=Open+Sans&display=swap');
}


add_action('wp_footer', 'scripts_theme');
function scripts_theme() {
    wp_enqueue_script('slick_js', get_template_directory_uri() . '/assets/js/jquery.js');
    wp_enqueue_script('script', get_template_directory_uri() . '/assets/js/main.js');
}

add_action('init', 'true_jquery_register' );
function true_jquery_register() {
    if ( !is_admin() ) {
        wp_deregister_script( 'jquery' );
        wp_register_script( 'jquery', ( 'http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js' ), false, null, true );
        wp_enqueue_script( 'jquery' );
    }
}

include_once get_stylesheet_directory() . '/inc/halpers.php';



// Hide dashboard on top
// ===========================
add_action('after_setup_theme', 'remove_admin_bar');

function remove_admin_bar() {
    if (!current_user_can('administrator') && !is_admin()) {
        show_admin_bar(false);
    }
}
// =========================================



// ACF Register options page
// =============================
add_action('acf/init', 'my_acf_op_init');
function my_acf_op_init() {

    // Check function exists.
    if( function_exists('acf_add_options_page') ) {

        // Register options page.
        $option_page = acf_add_options_page(array(
            'page_title'    => __('Theme General Settings'),
            'menu_title'    => __('Theme Settings'),
            'menu_slug'     => 'theme-general-settings',
            'capability'    => 'edit_posts',
            'redirect'      => false
        ));
    }
}
// ================================


// Allow SVG admin upload
//=========================
add_filter( 'wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {

    global $wp_version;
    if ( $wp_version !== '4.7.1' ) {
        return $data;
    }

    $filetype = wp_check_filetype( $filename, $mimes );

    return [
        'ext'             => $filetype['ext'],
        'type'            => $filetype['type'],
        'proper_filename' => $data['proper_filename']
    ];

}, 10, 4 );

function cc_mime_types( $mimes ){
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter( 'upload_mimes', 'cc_mime_types' );
//======================================

// Contact form 7
// ==================================
// Submit form ==============
//add_action( 'wpcf7_submit', 'wpcf7_after_submit');
add_action( 'wpcf7_before_send_mail', 'wpcf7_before_send_mail_action');
add_action( 'wpcf7_before_send_mail', 'wpcf7_before_send_mail_sign_up');

//my action
add_action('my_action', 'myfunctuin');


// ======================================

// Validate inputs main form ===============
add_filter( 'wpcf7_validate_text*', 'custom_validation_input_main_form', 10, 2 );
add_filter( 'wpcf7_validate_email*', 'custom_validation_input_main_form', 10, 2 );
// ============================

// Validate inputs sign up form ===============
add_filter( 'wpcf7_validate_text*', 'custom_validation_input_sign_up_form', 10, 2 );
add_filter( 'wpcf7_validate_email*', 'custom_validation_input_sign_up_form', 10, 2 );
// ============================


// ============================







//===================================
//ob_start();
//$mypost = ob_get_clean();
//
//wp_send_json(array(
//    'post' => $mypost,
//    'hide' =>  $post > 8 ? true : false
//));
//====================================================