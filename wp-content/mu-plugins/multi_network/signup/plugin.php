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