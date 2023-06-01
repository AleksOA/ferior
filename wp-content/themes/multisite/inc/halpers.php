<?php
/**
 * Convert file url to path
 *
 * @param string $url Link to file
 *
 * @return bool|mixed|string
 */

function convert_url_to_path( $url ) {
    if ( ! $url ) {
        return false;
    }
    $url       = str_replace( array( 'https://', 'http://' ), '', $url );
    $home_url  = str_replace( array( 'https://', 'http://' ), '', site_url() );
    $file_part = ABSPATH . str_replace( $home_url, '', $url );
    $file_part = str_replace( '//', '/', $file_part );
    if ( file_exists( $file_part ) ) {
        return $file_part;
    }

    return false;
}

/**
 * Return/Output SVG as html
 *
 * @param array|string $img Image link or array
 * @param string $class Additional class attribute for img tag
 * @param string $size Image size if $img is array
 *
 * @return void
 */
function display_svg( $img, $class = '', $size = 'medium' ) {
    echo return_svg( $img, $class, $size );
}

function return_svg( $img, $class = '', $size = 'medium' ) {
    if ( ! $img ) {
        return '';
    }

    $file_url = is_array( $img ) ? $img['url'] : $img;

    $file_info = pathinfo( $file_url );
    if ( $file_info['extension'] == 'svg' ) {
        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer"      => false,
                "verify_peer_name" => false,
            ),
        );
        $image             = file_get_contents( convert_url_to_path( $file_url ), false, stream_context_create( $arrContextOptions ) );
        if ( $class ) {
            $image = str_replace( '<svg ', '<svg class="' . esc_attr( $class ) . '" ', $image );
        }
    } elseif ( is_array( $img ) ) {
        $image = '<img class="' . esc_attr( $class ) . '" src="' . esc_url( $img['sizes'][$size] ) . '" alt="' . esc_attr( $img['alt'] ) . '"/>';
    } else {
        $image = '<img class="' . esc_attr( $class ) . '" src="' . esc_url( $img ) . '" alt="' . esc_attr( $file_info['filename'] ) . '"/>';
    };

    return $image;
}


// set cookies with user id after login =============================
function set_cookies_user_id_after_login($user_login, $user) {
    $blog_ID = get_current_blog_id();
    $main_site_ID = get_main_site_id();

    if($blog_ID == $main_site_ID){
        $rp_path = '/';
        $user_id = $user->ID;
        $user_id_cookie = 'user_id_login_main-' . COOKIEHASH;
        $siteName = wp_parse_url( home_url(), PHP_URL_HOST );
        setcookie( $user_id_cookie, $user_id, 0, $rp_path, $siteName, is_ssl(), true );
    }

    if($blog_ID != $main_site_ID){
        $rp_path = '/';
        $user_id = $user->ID;
        $user_id_cookie = 'user_id_login-' . COOKIEHASH;
        $siteName = wp_parse_url( home_url(), PHP_URL_HOST );
        setcookie( $user_id_cookie, $user_id, 0, $rp_path, $siteName, is_ssl(), true );
    }

}
add_action('wp_login', 'set_cookies_user_id_after_login', 10, 2);
// ==================================================================

// Deleting cookies when user logs out  ===========================
function deleting_cookies_when_user_logs_out( $user_id ){
    $blog_ID = get_current_blog_id();
    $main_site_ID = get_main_site_id();

    if($blog_ID == $main_site_ID){
        $user_id_cookie = 'user_id_login_main-' . COOKIEHASH;
        unset($_COOKIE[$user_id_cookie]);
        $siteName = wp_parse_url( home_url(), PHP_URL_HOST );
        setcookie( $user_id_cookie, '', time() - 3600, '/', $siteName, is_ssl(), true );
    }

    if($blog_ID != $main_site_ID){
        $user_id_cookie = 'user_id_login-' . COOKIEHASH;
        unset($_COOKIE[$user_id_cookie]);
        $siteName = wp_parse_url( home_url(), PHP_URL_HOST );
        setcookie( $user_id_cookie, '', time() - 3600, '/', $siteName, is_ssl(), true );
        setcookie( $user_id_cookie, '', time() - 3600, '/', '.' . wp_parse_url( network_site_url(), PHP_URL_HOST ), is_ssl(), true );
    }
}
add_action( 'wp_logout', 'deleting_cookies_when_user_logs_out' );
// ======================================================================

// If the current user does not register on the current site, the current user is logged out.
// ============================================================================================
function login_or_logout_user(){
    global $wpdb;
    $blog_ID = get_current_blog_id();
    $main_site_ID = get_main_site_id();

    if($blog_ID == $main_site_ID){
        $user_id_cookie = 'user_id_login_main-' . COOKIEHASH;
        if ( isset( $_COOKIE[ $user_id_cookie ] ) ) {
            $user_ID = wp_unslash( $_COOKIE[ $user_id_cookie ] );
        }

        $user_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM wpfe_usermeta WHERE user_id = %s", $user_ID ) );
        $user_email_exists = false;
        foreach ($user_data as $value) {
            if($value->meta_key == 'wpfe_user_level'){
                $user_email_exists = true;
            }
        }
        if($user_email_exists == false){
            wp_logout();
        }

        if($user_email_exists == true){
            if(!is_user_logged_in()) {
                wp_set_auth_cookie( $user_ID  );
                header("Refresh: 0");
                exit();
            }
        }
    }

    if($blog_ID != $main_site_ID){
        if(current_user_can('Super Admin' )) {
            return true;
        }else{
//            $user_ID = get_current_user_id();
            $user_id_cookie = 'user_id_login-' . COOKIEHASH;
            if ( isset( $_COOKIE[ $user_id_cookie ] ) ) {
                $user_ID = wp_unslash( $_COOKIE[ $user_id_cookie ] );
            }

            $user_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM wpfe_usermeta WHERE user_id = %s", $user_ID ) );
            $blog_ID = get_current_blog_id();
            $user_email_exists = false;
            foreach ($user_data as $value) {
                if($value->meta_key == 'wpfe_' . $blog_ID . '_user_level'){
                    $user_email_exists = true;
                }
            }
            if($user_email_exists == false){
                wp_logout();
            }

            if($user_email_exists == true){
                if(!is_user_logged_in()) {
                    wp_set_auth_cookie( $user_ID );
                    header("Refresh: 0");
                    exit();
                }
            }
        }
    }
}
add_action('init', 'login_or_logout_user' );
// ========================================================================================

function wpcf7_before_send_mail_action() {
    $submission = WPCF7_Submission::get_instance();
    if ( $submission ) {
        $posted_data = $submission->get_posted_data();

        $first_name = $posted_data['text-58'];
        $last_name = $posted_data['text-128'];
        $username = $posted_data['username-main-form'];
        $email = $posted_data['email-547'];
        $site_url = $posted_data['site_url'];
        $site_name = $posted_data['text-403'];
        $password = $posted_data['password-469'];
    }



    $userdata = array(
//        'ID' => $current_user_ID,
        'user_login' => $username,
        'user_email' => $email,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'user_pass' => $password,
        'role' => 'subscriber'
    );
    wp_insert_user( $userdata );
    $current_user_ID = email_exists($email);

    $data_site = array(
        'domain' => $site_url. '.ferior.com.ua',
        'title' => $site_name,
        'user_id' => $current_user_ID,
        'options' => array(
                'template' => 'multisite',
                'stylesheet' => 'multisite'
//                'current_theme' => 'Twenty Twenty-Two'
            )
    );
    wp_insert_site( $data_site );
}

// Create user and blog ====================================
function create_user_and_blog( $entry, $form ) {
    $first_name = rgar( $entry, '1.3' );
    $last_name = rgar( $entry, '1.6' );
    $username = rgar( $entry, '8' );
    $email = rgar( $entry, '5' );
    $site_url = rgar( $entry, '9' );
    $site_name = rgar( $entry, '10' );

    $userdata = array(
        'spot' => 'create user and blog',
        'first_name' => $first_name,
        'last_name' => $last_name,
        'domain' => $site_url. '.' . wp_parse_url( network_site_url(), PHP_URL_HOST ),
        'title' => $site_name,
        'role' => 'subscriber'
    );
    custom_signup_user( $username, $email, $userdata );
}
add_action( 'gform_after_submission_1', 'create_user_and_blog', 10, 2 );

function create_blog( $entry, $form ){
    $site_url = rgar($entry, '1');
    $site_name = rgar($entry, '3');
    $user_id = rgar($entry, 'created_by');
    $_SESSION['user_data'] = null;

    $data_site = array(
        'domain' => $site_url . '.' . wp_parse_url( network_site_url(), PHP_URL_HOST ),
        'title' => $site_name,
        'user_id' => $user_id,
        'options' => array(
            'template' => 'multisite',
            'stylesheet' => 'multisite'
            //                'current_theme' => 'Twenty Twenty-Two'
        )
    );
    $blog_id = wp_insert_site($data_site);

    if(!is_wp_error($blog_id)) {
        $rp_path = '/';
        $user_id_cookie = 'user_id_login-' . COOKIEHASH;
        $siteName = wp_parse_url( home_url(), PHP_URL_HOST );
        setcookie( $user_id_cookie, $user_id, 0, $rp_path, $siteName, is_ssl(), true );


        $blog_url = get_site_url( $blog_id );
        $meta = [
            'blog_url' => $blog_url,
            'spot' => 'create blog'
        ];
        do_action('custom_activate_user', $user_id, '', $meta);
        $url_redirect = $blog_url . '/wp-admin';
        wp_redirect($url_redirect);
        exit;
    }

}
add_action( 'gform_after_submission_3', 'create_blog', 10, 2 );
// ========================================================================



// Validate form Create user and blog
// ===================================

function custom_validation_input_main_form_gravity_1_5($result, $value, $form, $field)
{
    global $wpdb;
    $input_name = 'input_5';

    $the_value = $_POST[$input_name];
    $current_user_ID = email_exists($the_value);
    $nickname = $wpdb->get_row($wpdb->prepare("SELECT * FROM wpfe_users WHERE ID = %s", $current_user_ID))->user_nicename;
    if ($current_user_ID != false) {
        $error_text_of_field_email = 'Our site is one of the network sites shops where you bought goods or were registered as <strong>' . $nickname . '</strong>. <a id="login_input_1_5" href="' . home_url('login') . '">Log in</a> with this address or choose another one.';
        $result['is_valid'] = false;
        $result['message'] = $error_text_of_field_email;
        // =================================
        $blog_ID = get_current_blog_id();
        session_start();
        $user_data = [
            'user_name'=> $nickname,
            'user_id'=> $current_user_ID,
            'blog_id'=> $blog_ID,
            'action'=> 'new blog'
        ];
        $_SESSION['user_data']= $user_data;
        // ==========================================
    }
    return $result;
}
add_filter( 'gform_field_validation_1_5', 'custom_validation_input_main_form_gravity_1_5', 10, 5 );

function custom_validation_input_main_form_gravity_1_8($result, $value, $form, $field)
{

    $input_name = 'input_8';
    $the_current_value = $_POST[$input_name];
    $current_user_ID = username_exists($the_current_value);
    if($current_user_ID != false){
        $error_text_of_field_username = get_field('error_text_of_field_username', 'options');
        $result['is_valid'] = false;
        $result['message'] = $error_text_of_field_username;
    }

    return $result;

}
add_filter( 'gform_field_validation_1_8', 'custom_validation_input_main_form_gravity_1_8', 10, 5 );

function custom_validation_input_main_form_gravity_1_9($result, $value, $form, $field)
{
    $input_name = 'input_9';
    $the_current_value = $_POST[$input_name];
    $current_domain = $the_current_value . '.ferior.com.ua';
    $object_sites_name = get_sites();
    $sites_name = [];
    if( $object_sites_name ) : foreach ( $object_sites_name as $item ) :
        $item_domain = $item->domain;
        if($item_domain == $current_domain) {
            array_push($sites_name, $item_domain);
        }
    endforeach;
    endif;

    if(count($sites_name) > 0){
        $error_text_of_field_site_address_url = get_field('error_text_of_field_site_address_url', 'options');
        $result['is_valid'] = false;
        $result['message'] = $error_text_of_field_site_address_url;
    }
    return $result;
}
add_filter( 'gform_field_validation_1_9', 'custom_validation_input_main_form_gravity_1_9', 10, 5 );
// ======================================


// Validate form Create blog
// ===================================
function custom_validation_input_main_form_gravity_3_1($result, $value, $form, $field)
{
    $input_name = 'input_1';
    $the_current_value = $_POST[$input_name];
    $current_domain = $the_current_value . '.ferior.com.ua';
    $object_sites_name = get_sites();
    $sites_name = [];
    if( $object_sites_name ) : foreach ( $object_sites_name as $item ) :
        $item_domain = $item->domain;
        if($item_domain == $current_domain) {
            array_push($sites_name, $item_domain);
        }
    endforeach;
    endif;

    if(count($sites_name) > 0){
        $error_text_of_field_site_address_url = get_field('error_text_of_field_site_address_url', 'options');
        $result['is_valid'] = false;
        $result['message'] = $error_text_of_field_site_address_url;
    }
    return $result;
}

add_filter( 'gform_field_validation_3_1', 'custom_validation_input_main_form_gravity_3_1', 10, 5 );
// ===================================

// Validate form
// ===================================
function custom_validation_input_main_form($result,$tag){
    global $wpdb;
    $input_name = $tag['name'];
    $type = $tag['type'];

    if($input_name == 'email-547') {
        $the_value = $_POST[$input_name];
        $current_user_ID = email_exists($the_value);
        $nickname = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM wpfe_users WHERE ID = %s", $current_user_ID ) )->user_nicename;
        if($current_user_ID != false){
//            $error_text_of_field_email = get_field('error_text_of_field_email', 'options');
//            function hello_world_cf7_func() {
//                global $nickname;
//                return 'Our site is one of the network sites shops where you bought goods or were registered as <strong>' . $nickname . '</strong>. <a href="' . home_url('login') . '">Log in</a> with this address or choose another one.';
//            }
//            add_shortcode('hello_world', 'hello_world_cf7_func');

//            $error_text_of_field_email = 'Our site is one of the network sites shops where you bought goods or were registered as <strong>' . $nickname . '</strong>. <a href="' . home_url('login') . '">Log in</a> with this address or choose another one.';
            $error_text_of_field_email = '<a href="' . home_url('login') . '">Log in</a>';
//            $error_text_of_field_email = do_shortcode('[hello_world]');

            $result->invalidate($tag, $error_text_of_field_email);
        }
        return $result;
    }

    if($type == 'text*') {
        if($input_name == 'username-main-form' ) {
            $the_current_value = $_POST[$input_name];
            $current_user_ID = username_exists($the_current_value);
            if($current_user_ID != false){
                $error_text_of_field_username = get_field('error_text_of_field_username', 'options');
                $result->invalidate($tag, $error_text_of_field_username);
            }
            return $result;
        }
    }

    if($type == 'text*') {
        if($input_name == 'site_url' ) {
            $the_current_value = $_POST[$input_name];
            $current_domain = $the_current_value . '.ferior.com.ua';
            $object_sites_name = get_sites();
            $sites_name = [];
            if( $object_sites_name ) : foreach ( $object_sites_name as $item ) :
                $item_domain = $item->domain;
                if($item_domain == $current_domain) {
                    array_push($sites_name, $item_domain);
                }
                endforeach;
            endif;

            if(count($sites_name) > 0){
                $error_text_of_field_site_address_url = get_field('error_text_of_field_site_address_url', 'options');
                $result->invalidate($tag, $error_text_of_field_site_address_url);
            }
            return $result;
        }
    }

    return $result;
//    echo '<pre>'; var_dump($current_user_ID); die();
}
// =====================================



// Sign Up form
// ==================================
function wpcf7_before_send_mail_sign_up(){
    $submission = WPCF7_Submission::get_instance();
    if ( $submission ) {
        $posted_data = $submission->get_posted_data();

        $username = $posted_data['usernameSignUp'];
        $email = $posted_data['emailSignUp'];
        $password = $posted_data['passwordSignUp-1'];
    }


    $userdata = array(
//        'ID' => $current_user_ID,
        'user_login' => $username,
        'user_email' => $email,
        'user_pass' => $password,
        'role' => 'subscriber'
    );
    wp_insert_user( $userdata );
}

function custom_validation_input_sign_up_form($result,$tag){
    $input_name = $tag['name'];
    $type = $tag['type'];

    if($input_name == 'emailSignUp') {
        $the_value = $_POST[$input_name];
        $current_user_ID = email_exists($the_value);
        if($current_user_ID != false){
            $error_text_of_field_email = get_field('error_text_of_field_email', 'options');
            $result->invalidate($tag, $error_text_of_field_email);
        }
        return $result;
    }

    if($type == 'text*') {
        if($input_name == 'usernameSignUp' ) {
            $the_current_value = $_POST[$input_name];
            $current_user_ID = username_exists($the_current_value);
            if($current_user_ID != false){
                $error_text_of_field_username = get_field('error_text_of_field_username', 'options');
                $result->invalidate($tag, $error_text_of_field_username);
            }
            return $result;
        }
    }

    return $result;
//    echo '<pre>'; var_dump($current_user_ID); die();
}
// ==================================




// Create new pages: Sign Up and Activate
// ============================================
function createNewPage($title, $content, $template ){
    $new_page_title = $title;
    $new_page_content = $content;
    $new_page_template = $template;


    $page_check  = new WP_Query(
        [
            'post_type'              => 'page',
            'title'                  => $new_page_title
        ]
    );

    $new_page = array(
        'post_type' => 'page',
        'post_title' => $new_page_title,
        'post_content' => $new_page_content,
        'post_status' => 'publish',
        'post_author' => 1,
    );
    if(count($page_check->posts) == 0 ){
        $new_page_id = wp_insert_post($new_page);

        if(!empty($new_page_template)){
            update_post_meta($new_page_id, '_wp_page_template', $new_page_template);
        }
    }

    if($new_page_title == 'Home'){
        $page_ID = $page_check->posts[0]->ID;
        update_option( 'show_on_front', 'page', true);
        update_option( 'page_on_front', $page_ID, true);
    }
}

function custom_activated_plugin_action() {
    createNewPage('Home', '', 'template_pages/home_template.php' );
    createNewPage('Signup', '', 'template_pages/signup_template.php' );
    createNewPage('Activate', '', 'template_pages/activate_template.php' );
    createNewPage('Login', '', 'template_pages/login_template.php' );
}

add_action( 'admin_init', 'custom_activated_plugin_action', 10, 2 );
add_action( 'switch_theme', 'custom_activated_plugin_action', 10, 2 );
add_action( 'after_switch_theme', 'custom_activated_plugin_action', 10, 2 );
// ==========================================


// Redirect from /wp-login.pnp to /login =================
function redirect_login_page() {
    $login_page  = home_url( '/login' );
    $loggedout = home_url( '/login?loggedout=true&wp_lang=en_US' );
    $lostpassword = home_url( '/login?action=lostpassword' );
    $page_viewed = basename($_SERVER['REQUEST_URI']);

    if( $page_viewed == "wp-login.php" && $_SERVER['REQUEST_METHOD'] == 'GET') {
        wp_redirect($login_page);
        exit;
    }
    if( $page_viewed == "wp-login.php?loggedout=true&wp_lang=en_US" && $_SERVER['REQUEST_METHOD'] == 'GET') {
        wp_redirect($loggedout);
        exit;
    }
    if( $page_viewed == "wp-login.php?action=lostpassword" && $_SERVER['REQUEST_METHOD'] == 'GET') {
        wp_redirect($lostpassword);
        exit;
    }
    if( $page_viewed == "wp-login.php?redirect_to=http%3A%2F%2Fferior.com.ua%2Fwp-admin%2Fadmin.php%3Fpage%3Dgf_edit_forms&reauth=1" && $_SERVER['REQUEST_METHOD'] == 'GET') {
        wp_redirect($login_page);
        exit;
    }
}
add_action('init','redirect_login_page');
//========================================================



// redirect to custom lostpassword =====================

add_filter( 'lostpassword_url', 'custom_lost_pass_link' );

function custom_lost_pass_link(){
    return site_url( '/login?action=lostpassword' );
}
// ========================================================



// SENDING DATA TO JS
// ===========================
   $id = json_encode(array(
       'key' => 'one',
       'key2' => 'two'
   ));

function scriptmy(){
    global $id;
    echo '<h2>wpcf7_after_submit</h2>';
    echo '<script> let a = ' . $id .  ' ;</script>';
}
// =============================
