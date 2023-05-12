<?php
function multi_network_signup_page( $url ) {
    return home_url( 'signup' );
}
add_filter ('wp_signup_location', 'multi_network_signup_page', 99);


function custom_signup_user( $user, $user_email, $meta = array() ) {
    global $wpdb;

    // Format data.
    $user       = preg_replace( '/\s+/', '', sanitize_user( $user, true ) );
    $user_email = sanitize_email( $user_email );
    $key        = substr( md5( time() . wp_rand() . $user_email ), 0, 16 );

    /**
     * Filters the metadata for a user signup.
     *
     * The metadata will be serialized prior to storing it in the database.
     *
     * @since 4.8.0
     *
     * @param array  $meta       Signup meta data. Default empty array.
     * @param string $user       The user's requested login name.
     * @param string $user_email The user's email address.
     * @param string $key        The user's activation key.
     */
    $meta = apply_filters( 'signup_user_meta', $meta, $user, $user_email, $key );

    $wpdb->insert(
        $wpdb->signups,
        array(
            'domain'         => '',
            'path'           => '',
            'title'          => '',
            'user_login'     => $user,
            'user_email'     => $user_email,
            'registered'     => current_time( 'mysql', true ),
            'activation_key' => $key,
            'meta'           => serialize( $meta ),
        )
    );

    /**
     * Fires after a user's signup information has been written to the database.
     *
     * @since 4.4.0
     *
     * @param string $user       The user's requested login name.
     * @param string $user_email The user's email address.
     * @param string $key        The user's activation key.
     * @param array  $meta       Signup meta data. Default empty array.
     */
    do_action( 'custom_after_signup_user', $user, $user_email, $key, $meta );
//    do_action( 'after_signup_user', $user, $user_email, $key, $meta );
}

function multi_network_wpmu_signup_user_notification( $user_login, $user_email, $key, $meta = array() ) {
    /**
     * Filters whether to bypass the email notification for new user sign-up.
     *
     * @since MU (3.0.0)
     *
     * @param string $user_login User login name.
     * @param string $user_email User email address.
     * @param string $key        Activation key created in wpmu_signup_user().
     * @param array  $meta       Signup meta data. Default empty array.
     */
    if ( ! apply_filters( 'multi_network_wpmu_signup_user_notification', $user_login, $user_email, $key, $meta ) ) {
        return false;
    }

    $user            = get_user_by( 'login', $user_login );
    $switched_locale = $user && switch_to_user_locale( $user->ID );

    // Send email with activation link.
//    $admin_email = get_site_option( 'admin_email' ); // ==== WAS ====

    $blog_id = get_current_blog_id();// my code
    switch_to_blog( $blog_id );// my code
    $admin_email = get_option('admin_email');// my code
    restore_current_blog(); // my code



    if ( '' === $admin_email ) {
//        $admin_email = 'support@' . wp_parse_url( network_home_url(), PHP_URL_HOST ); // ==== WAS ====
        $admin_email = 'support@' . wp_parse_url( home_url(), PHP_URL_HOST ); // my code
    }

//    $from_name       = ( '' !== get_site_option( 'site_name' ) ) ? esc_html( get_site_option( 'site_name' ) ) : 'WordPress'; // ==== WAS ====
//    $message_headers = "From: \"{$from_name}\" <{$admin_email}>\n" . 'Content-Type: text/plain; charset="' . get_option( 'blog_charset' ) . "\"\n"; // ==== WAS ====

    switch_to_blog( $blog_id );// my code
    $from_name       = ( '' !== get_option( 'blogname' ) ) ? esc_html( get_option( 'blogname' ) ) : 'WordPress'; // my code
    $message_headers = "From: \"{$from_name}\" <{$admin_email}>\n" . 'Content-Type: text/plain; charset="' . get_option( 'blog_charset' ) . "\"\n"; // my code
    restore_current_blog(); // my code
    $message         = sprintf(
    /**
     * Filters the content of the notification email for new user sign-up.
     *
     * Content should be formatted for transmission via wp_mail().
     *
     * @since MU (3.0.0)
     *
     * @param string $content    Content of the notification email.
     * @param string $user_login User login name.
     * @param string $user_email User email address.
     * @param string $key        Activation key created in wpmu_signup_user().
     * @param array  $meta       Signup meta data. Default empty array.
     */
        apply_filters(
            'wpmu_signup_user_notification_email',
            /* translators: New user notification email. %s: Activation URL. */
            __( "To activate your user, please click the following link:\n\n%s\n\nAfter you activate, you will receive *another email* with your login." ),
            $user_login,
            $user_email,
            $key,
            $meta
        ),
//        site_url( "wp-activate.php?key=$key" ) // ==== WAS ====
        site_url( "activate?key=$key" ) // my code
    );

    $subject = sprintf(
    /**
     * Filters the subject of the notification email of new user signup.
     *
     * @since MU (3.0.0)
     *
     * @param string $subject    Subject of the notification email.
     * @param string $user_login User login name.
     * @param string $user_email User email address.
     * @param string $key        Activation key created in wpmu_signup_user().
     * @param array  $meta       Signup meta data. Default empty array.
     */
        apply_filters(
            'wpmu_signup_user_notification_subject',
            /* translators: New user notification email subject. 1: Network title, 2: New user login. */
            _x( '[%1$s] Activate %2$s', 'New user notification email subject' ),
            $user_login,
            $user_email,
            $key,
            $meta
        ),
        $from_name,
        $user_login
    );

    wp_mail( $user_email, wp_specialchars_decode( $subject ), $message, $message_headers );

    if ( $switched_locale ) {
        restore_previous_locale();
    }

    return true;
}

add_action('custom_after_signup_user', 'multi_network_wpmu_signup_user_notification', 10 ,3);




function custom_activate_signup( $key ) {
    global $wpdb;

    $signup = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->signups WHERE activation_key = %s", $key ) );

    if ( empty( $signup ) ) {
        return new WP_Error( 'invalid_key', __( 'Invalid activation key.' ) );
    }

    if ( $signup->active ) {
        if ( empty( $signup->domain ) ) {
            return new WP_Error( 'already_active', __( 'The user is already active.' ), $signup );
        } else {
            return new WP_Error( 'already_active', __( 'The site is already active.' ), $signup );
        }
    }

    $meta     = maybe_unserialize( $signup->meta );
    $password = wp_generate_password( 12, false );

    $user_id = username_exists( $signup->user_login );

    if ( ! $user_id ) {
        $user_id = wpmu_create_user( $signup->user_login, $password, $signup->user_email );
    } else {
        $user_already_exists = true;
    }

    if ( ! $user_id ) {
        return new WP_Error( 'create_user', __( 'Could not create user' ), $signup );
    }

    $now = current_time( 'mysql', true );

    if ( empty( $signup->domain ) ) {
        $wpdb->update(
            $wpdb->signups,
            array(
                'active'    => 1,
                'activated' => $now,
            ),
            array( 'activation_key' => $key )
        );

        if ( isset( $user_already_exists ) ) {
            return new WP_Error( 'user_already_exists', __( 'That username is already activated.' ), $signup );
        }

        /**
         * Fires immediately after a new user is activated.
         *
         * @since MU (3.0.0)
         *
         * @param int    $user_id  User ID.
         * @param string $password User password.
         * @param array  $meta     Signup meta data.
         */
//        do_action( 'wpmu_activate_user', $user_id, $password, $meta ); // ==== WAS ====
        do_action( 'custom_activate_user', $user_id, $password, $meta );

        return array(
            'user_id'  => $user_id,
            'password' => $password,
            'meta'     => $meta,
        );
    }

    $blog_id = wpmu_create_blog( $signup->domain, $signup->path, $signup->title, $user_id, $meta, get_current_network_id() );

    // TODO: What to do if we create a user but cannot create a blog?
    if ( is_wp_error( $blog_id ) ) {
        /*
         * If blog is taken, that means a previous attempt to activate this blog
         * failed in between creating the blog and setting the activation flag.
         * Let's just set the active flag and instruct the user to reset their password.
         */
        if ( 'blog_taken' === $blog_id->get_error_code() ) {
            $blog_id->add_data( $signup );
            $wpdb->update(
                $wpdb->signups,
                array(
                    'active'    => 1,
                    'activated' => $now,
                ),
                array( 'activation_key' => $key )
            );
        }
        return $blog_id;
    }

    $wpdb->update(
        $wpdb->signups,
        array(
            'active'    => 1,
            'activated' => $now,
        ),
        array( 'activation_key' => $key )
    );

    /**
     * Fires immediately after a site is activated.
     *
     * @since MU (3.0.0)
     *
     * @param int    $blog_id       Blog ID.
     * @param int    $user_id       User ID.
     * @param string $password      User password.
     * @param string $signup_title  Site title.
     * @param array  $meta          Signup meta data. By default, contains the requested privacy setting and lang_id.
     */
    do_action( 'wpmu_activate_blog', $blog_id, $user_id, $password, $signup->title, $meta );

    return array(
        'blog_id'  => $blog_id,
        'user_id'  => $user_id,
        'password' => $password,
        'title'    => $signup->title,
        'meta'     => $meta,
    );
}

function custom_welcome_user_notification( $user_id, $password, $meta = array() ) {
    $current_network = get_network();

    /**
     * Filters whether to bypass the welcome email after user activation.
     *
     * Returning false disables the welcome email.
     *
     * @since MU (3.0.0)
     *
     * @param int    $user_id  User ID.
     * @param string $password User password.
     * @param array  $meta     Signup meta data. Default empty array.
     */
    if ( ! apply_filters( 'custom_welcome_user_notification', $user_id, $password, $meta ) ) {
        return false;
    }

    $welcome_email = get_site_option( 'welcome_user_email' );

    $user = get_userdata( $user_id );

    $switched_locale = switch_to_user_locale( $user_id );

    /**
     * Filters the content of the welcome email after user activation.
     *
     * Content should be formatted for transmission via wp_mail().
     *
     * @since MU (3.0.0)
     *
     * @param string $welcome_email The message body of the account activation success email.
     * @param int    $user_id       User ID.
     * @param string $password      User password.
     * @param array  $meta          Signup meta data. Default empty array.
     */
    $welcome_email = apply_filters( 'update_welcome_user_email', $welcome_email, $user_id, $password, $meta );
//    $welcome_email = str_replace( 'SITE_NAME', $current_network->site_name, $welcome_email ); // ==== WAS ====
    $welcome_email = str_replace( 'SITE_NAME', get_option( 'blogname' ), $welcome_email ); // my code
    $welcome_email = str_replace( 'USERNAME', $user->user_login, $welcome_email );
    $welcome_email = str_replace( 'PASSWORD', $password, $welcome_email );
    $welcome_email = str_replace( 'LOGINLINK', wp_login_url(), $welcome_email );

    $admin_email = get_site_option( 'admin_email' );

    if ( '' === $admin_email ) {
        $admin_email = 'support@' . wp_parse_url( home_url(), PHP_URL_HOST );
    }

    $from_name       = ( '' !== get_option( 'blogname' ) ) ? esc_html( get_option( 'blogname' ) ) : 'WordPress';
    $message_headers = "From: \"{$from_name}\" <{$admin_email}>\n" . 'Content-Type: text/plain; charset="' . get_option( 'blog_charset' ) . "\"\n";
    $message         = $welcome_email;

    if ( empty( $current_network->site_name ) ) {
        $current_network->site_name = 'WordPress';
    }

    /* translators: New user notification email subject. 1: Network title, 2: New user login. */
    $subject = __( 'New %1$s User: %2$s' ); // ==== WAS ====
    $subject = __( 'New ' . get_option( 'blogname' ) .  ' User: %2$s' ); // my code

    /**
     * Filters the subject of the welcome email after user activation.
     *
     * @since MU (3.0.0)
     *
     * @param string $subject Subject of the email.
     */
    $subject = apply_filters( 'update_welcome_user_subject', sprintf( $subject, $current_network->site_name, $user->user_login ) );

    wp_mail( $user->user_email, wp_specialchars_decode( $subject ), $message, $message_headers );

    if ( $switched_locale ) {
        restore_previous_locale();
    }

    return true;
}

//add_action( 'custom_activate_user', 'custom_add_new_user_to_blog', 10, 3 );
add_action( 'custom_activate_user', 'custom_welcome_user_notification', 10, 3 );


/**
 * Sanitizes and validates data required for a user sign-up.
 *
 * Verifies the validity and uniqueness of user names and user email addresses,
 * and checks email addresses against allowed and disallowed domains provided by
 * administrators.
 *
 * The {@see 'wpmu_validate_user_signup'} hook provides an easy way to modify the sign-up
 * process. The value $result, which is passed to the hook, contains both the user-provided
 * info and the error messages created by the function. {@see 'wpmu_validate_user_signup'}
 * allows you to process the data in any way you'd like, and unset the relevant errors if
 * necessary.
 *
 * @since MU (3.0.0)
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param string $user_name  The login name provided by the user.
 * @param string $user_email The email provided by the user.
 * @return array {
 *     The array of user name, email, and the error messages.
 *
 *     @type string   $user_name     Sanitized and unique username.
 *     @type string   $orig_username Original username.
 *     @type string   $user_email    User email address.
 *     @type WP_Error $errors        WP_Error object containing any errors found.
 * }
 */
function custom_validate_user_signup( $user_name, $user_email ) {
    global $wpdb;

    $errors = new WP_Error();

    $orig_username = $user_name;
    $user_name     = preg_replace( '/\s+/', '', sanitize_user( $user_name, true ) );

    if ( $user_name != $orig_username || preg_match( '/[^a-z0-9]/', $user_name ) ) {
        $errors->add( 'user_name', __( 'Usernames can only contain lowercase letters (a-z) and numbers.' ) );
        $user_name = $orig_username;
    }

    $user_email = sanitize_email( $user_email );

    if ( empty( $user_name ) ) {
        $errors->add( 'user_name', __( 'Please enter a username.' ) );
    }

    $illegal_names = get_site_option( 'illegal_names' );
    if ( ! is_array( $illegal_names ) ) {
        $illegal_names = array( 'www', 'web', 'root', 'admin', 'main', 'invite', 'administrator' );
        add_site_option( 'illegal_names', $illegal_names );
    }
    if ( in_array( $user_name, $illegal_names, true ) ) {
        $errors->add( 'user_name', __( 'Sorry, that username is not allowed.' ) );
    }

    /** This filter is documented in wp-includes/user.php */
    $illegal_logins = (array) apply_filters( 'illegal_user_logins', array() );

    if ( in_array( strtolower( $user_name ), array_map( 'strtolower', $illegal_logins ), true ) ) {
        $errors->add( 'user_name', __( 'Sorry, that username is not allowed.' ) );
    }

    if ( ! is_email( $user_email ) ) {
        $errors->add( 'user_email', __( 'Please enter a valid email address.' ) );
    } elseif ( is_email_address_unsafe( $user_email ) ) {
        $errors->add( 'user_email', __( 'You cannot use that email address to signup. There are problems with them blocking some emails from WordPress. Please use another email provider.' ) );
    }

    if ( strlen( $user_name ) < 4 ) {
        $errors->add( 'user_name', __( 'Username must be at least 4 characters.' ) );
    }

    if ( strlen( $user_name ) > 60 ) {
        $errors->add( 'user_name', __( 'Username may not be longer than 60 characters.' ) );
    }

    // All numeric?
    if ( preg_match( '/^[0-9]*$/', $user_name ) ) {
        $errors->add( 'user_name', __( 'Sorry, usernames must have letters too!' ) );
    }

    $limited_email_domains = get_site_option( 'limited_email_domains' );
    if ( is_array( $limited_email_domains ) && ! empty( $limited_email_domains ) ) {
        $limited_email_domains = array_map( 'strtolower', $limited_email_domains );
        $emaildomain           = strtolower( substr( $user_email, 1 + strpos( $user_email, '@' ) ) );
        if ( ! in_array( $emaildomain, $limited_email_domains, true ) ) {
            $errors->add( 'user_email', __( 'Sorry, that email address is not allowed!' ) );
        }
    }

    // Check if the username has been used already.
    if ( username_exists( $user_name ) ) {
        $errors->add( 'user_name', __( 'Sorry, that username already exists!' ) );
    }

    // Check if the email address has been used already.
    if ( email_exists( $user_email ) ) {
        // my code start ===================================

        // is there the email in this blog ===========
        $user_ID = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM wpfe_users WHERE user_email = %s", $user_email ) )->ID;
        $user_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM wpfe_usermeta WHERE user_id = %s", $user_ID ) );
        $blog_ID = get_current_blog_id();
        $user_email_exists = false;
        $nickname = '';
        foreach ($user_data as $value) {
            if($value->meta_key == 'wpfe_' . $blog_ID . '_user_level'){
                $user_email_exists = true;
            }
        }

        if($user_email_exists == false) {
            $nickname = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM wpfe_users WHERE ID = %s", $user_ID ) )->user_nicename;
        }
        // =================================


        if($user_email_exists == true) {
            $errors->add(
                'user_email',
                sprintf(
                /* translators: %s: Link to the login page. */
                    __( '<strong>Error:</strong> This email address is already registered. <a href="%s">Log in</a> with this address or choose another one.' ),
                    wp_login_url()
                )
            );
        }

        if($user_email_exists == false) {
            // =================================

            session_start();
            $user_data = [
                'user_name'=> $nickname,
                'user_id'=> $user_ID,
                'blog_id'=> $blog_ID
            ];
            $_SESSION['user_data']= $user_data;
            // ==========================================

            $errors->add(
                'user_email',
                sprintf(
                /* translators: %s: Link to the login page. */
                    __( '<strong>Error:</strong> Our site is one of the network sites shops where you bought goods or were registered as <strong>' . $nickname . '</strong>. <a href="%s">Log in</a> with this address or choose another one.' ),
                    wp_login_url()

                )
            );
        }



        // my code finish ========================================



//        $errors->add(
//            'user_email',
//            sprintf(
//            /* translators: %s: Link to the login page. */
//                __( '<strong>Error:</strong> This email address is already registered. <a href="%s">Log in</a> with this address or choose another one.' ),
//                wp_login_url()
//            )
//        );
    }

    // Has someone already signed up for this username?
    $signup = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->signups WHERE user_login = %s", $user_name ) );
    if ( $signup instanceof stdClass ) {
        $registered_at = mysql2date( 'U', $signup->registered );
        $now           = time();
        $diff          = $now - $registered_at;
        // If registered more than two days ago, cancel registration and let this signup go through.
        if ( $diff > 2 * DAY_IN_SECONDS ) {
            $wpdb->delete( $wpdb->signups, array( 'user_login' => $user_name ) );
        } else {
            $errors->add( 'user_name', __( 'That username is currently reserved but may be available in a couple of days.' ) );
        }
    }

    $signup = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->signups WHERE user_email = %s", $user_email ) );
    if ( $signup instanceof stdClass ) {
        $diff = time() - mysql2date( 'U', $signup->registered );
        // If registered more than two days ago, cancel registration and let this signup go through.
        if ( $diff > 2 * DAY_IN_SECONDS ) {
            $wpdb->delete( $wpdb->signups, array( 'user_email' => $user_email ) );
        } else {
            $errors->add( 'user_email', __( 'That email address has already been used. Please check your inbox for an activation email. It will become available in a couple of days if you do nothing.' ) );
        }
    }

    $result = array(
        'user_name'     => $user_name,
        'orig_username' => $orig_username,
        'user_email'    => $user_email,
        'errors'        => $errors,
    );

    /**
     * Filters the validated user registration details.
     *
     * This does not allow you to override the username or email of the user during
     * registration. The values are solely used for validation and error handling.
     *
     * @since MU (3.0.0)
     *
     * @param array $result {
     *     The array of user name, email, and the error messages.
     *
     *     @type string   $user_name     Sanitized and unique username.
     *     @type string   $orig_username Original username.
     *     @type string   $user_email    User email address.
     *     @type WP_Error $errors        WP_Error object containing any errors found.
     * }
     */
    return apply_filters( 'wpmu_validate_user_signup', $result );
}