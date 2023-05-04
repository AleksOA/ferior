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

// console_log
// ==============================
function console_log($data){ // сама функция
    if(is_array($data) || is_object($data)){
        echo("<script>console.log('php_array: ".json_encode($data)."');</script>");
    } else {
        echo("<script>console.log('php_string: ".$data."');</script>");
    }
}

//$test='dsa';
//console_log($test);
// =========================================

function myfunctuin(){
//    $out = '<h2>wpcf7_after_submit</h2>';
//  echo $out;
    header ('Location: http://site2.multisite.loc/wp-login.php');
    exit;
//    echo '<meta http-equiv="refresh" content="0;url=http://site2.multisite.loc/wp-login.php">';
}



function wpcf7_after_submit() {

}

// ===========================
//wp_delete_user( $id, $reassign );


// ===========================


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
                'template' => 'twentytwentytwo',
                'stylesheet' => 'twentytwentytwo',
                'current_theme' => 'Twenty Twenty-Two'
            )
    );
    wp_insert_site( $data_site );
    $login_url = 'http://' . $data_site["domain"] . '/wp-login.php';
//    wp_safe_redirect($login_url, 301);
//    exit;

//    header ('Location: http://site2.multisite.loc/wp-login.php');
//    exit;

//    echo '<meta http-equiv="refresh" content="0;url=http://site2.multisite.loc/wp-login.php">';


//    do_action( 'my_action');
}


// Validate form
// ===================================
function custom_validation_input_main_form($result,$tag){
    $input_name = $tag['name'];
    $type = $tag['type'];

    if($input_name == 'email-547') {
        $the_value = $_POST[$input_name];
        $current_user_ID = email_exists($the_value);
        if($current_user_ID != false){
            $error_text_of_field_email = get_field('error_text_of_field_email', 'options');
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
            $current_domain = $the_current_value . '.multisite.loc';
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



function button() {
    echo '<button id="btnTest"> TEST </button>';
}

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
