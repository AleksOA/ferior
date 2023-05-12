<?php

function custom_retrieve_password( $user_login = null ) {
    $errors    = new WP_Error();
    $user_data = false;

    // Use the passed $user_login if available, otherwise use $_POST['user_login'].
    if ( ! $user_login && ! empty( $_POST['user_login'] ) ) {
        $user_login = $_POST['user_login'];
    }

    $user_login = trim( wp_unslash( $user_login ) );

    if ( empty( $user_login ) ) {
        $errors->add( 'empty_username', __( '<strong>Error:</strong> Please enter a username or email address.' ) );
    } elseif ( strpos( $user_login, '@' ) ) {
        $user_data = get_user_by( 'email', $user_login );

        if ( empty( $user_data ) ) {
            $user_data = get_user_by( 'login', $user_login );
        }

        if ( empty( $user_data ) ) {
            $errors->add( 'invalid_email', __( '<strong>Error:</strong> There is no account with that username or email address.' ) );
        }
    } else {
        $user_data = get_user_by( 'login', $user_login );
    }

    /**
     * Filters the user data during a password reset request.
     *
     * Allows, for example, custom validation using data other than username or email address.
     *
     * @since 5.7.0
     *
     * @param WP_User|false $user_data WP_User object if found, false if the user does not exist.
     * @param WP_Error      $errors    A WP_Error object containing any errors generated
     *                                 by using invalid credentials.
     */
    $user_data = apply_filters( 'lostpassword_user_data', $user_data, $errors );

    /**
     * Fires before errors are returned from a password reset request.
     *
     * @since 2.1.0
     * @since 4.4.0 Added the `$errors` parameter.
     * @since 5.4.0 Added the `$user_data` parameter.
     *
     * @param WP_Error      $errors    A WP_Error object containing any errors generated
     *                                 by using invalid credentials.
     * @param WP_User|false $user_data WP_User object if found, false if the user does not exist.
     */
    do_action( 'lostpassword_post', $errors, $user_data );

    /**
     * Filters the errors encountered on a password reset request.
     *
     * The filtered WP_Error object may, for example, contain errors for an invalid
     * username or email address. A WP_Error object should always be returned,
     * but may or may not contain errors.
     *
     * If any errors are present in $errors, this will abort the password reset request.
     *
     * @since 5.5.0
     *
     * @param WP_Error      $errors    A WP_Error object containing any errors generated
     *                                 by using invalid credentials.
     * @param WP_User|false $user_data WP_User object if found, false if the user does not exist.
     */
    $errors = apply_filters( 'lostpassword_errors', $errors, $user_data );

    if ( $errors->has_errors() ) {
        return $errors;
    }

    if ( ! $user_data ) {
        $errors->add( 'invalidcombo', __( '<strong>Error:</strong> There is no account with that username or email address.' ) );
        return $errors;
    }

    /**
     * Filters whether to send the retrieve password email.
     *
     * Return false to disable sending the email.
     *
     * @since 6.0.0
     *
     * @param bool    $send       Whether to send the email.
     * @param string  $user_login The username for the user.
     * @param WP_User $user_data  WP_User object.
     */
    if ( ! apply_filters( 'send_retrieve_password_email', true, $user_login, $user_data ) ) {
        return true;
    }

    // Redefining user_login ensures we return the right case in the email.
    $user_login = $user_data->user_login;
    $user_email = $user_data->user_email;
    $key        = get_password_reset_key( $user_data );

    if ( is_wp_error( $key ) ) {
        return $key;
    }

    // Localize password reset message content for user.
    $locale = get_user_locale( $user_data );

    $switched_locale = switch_to_user_locale( $user_data->ID );

    if ( is_multisite() ) {
//        $site_name = get_network()->site_name; //WAS
        $site_name = get_option( 'blogname' ); // my code
    } else {
        /*
         * The blogname option is escaped with esc_html on the way into the database
         * in sanitize_option. We want to reverse this for the plain text arena of emails.
         */
        $site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
    }

    $message = __( 'Someone has requested a password reset for the following account:' ) . "\r\n\r\n";
    /* translators: %s: Site name. */
    $message .= sprintf( __( 'Site Name: %s' ), $site_name ) . "\r\n\r\n";
    /* translators: %s: User login. */
    $message .= sprintf( __( 'Username: %s' ), $user_login ) . "\r\n\r\n";
    $message .= __( 'If this was a mistake, ignore this email and nothing will happen.' ) . "\r\n\r\n";
    $message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
//    $message .= network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . '&wp_lang=' . $locale . "\r\n\r\n"; //WAS
    $message .= home_url( "login?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . '&wp_lang=' . $locale . "\r\n\r\n"; // my code

    if ( ! is_user_logged_in() ) {
        $requester_ip = $_SERVER['REMOTE_ADDR'];
        if ( $requester_ip ) {
            $message .= sprintf(
                /* translators: %s: IP address of password reset requester. */
                    __( 'This password reset request originated from the IP address %s.' ),
                    $requester_ip
                ) . "\r\n";
        }
    }

    /* translators: Password reset notification email subject. %s: Site title. */
    $title = sprintf( __( '[%s] Password Reset' ), $site_name );

    /**
     * Filters the subject of the password reset email.
     *
     * @since 2.8.0
     * @since 4.4.0 Added the `$user_login` and `$user_data` parameters.
     *
     * @param string  $title      Email subject.
     * @param string  $user_login The username for the user.
     * @param WP_User $user_data  WP_User object.
     */
    $title = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );

    /**
     * Filters the message body of the password reset mail.
     *
     * If the filtered message is empty, the password reset email will not be sent.
     *
     * @since 2.8.0
     * @since 4.1.0 Added `$user_login` and `$user_data` parameters.
     *
     * @param string  $message    Email message.
     * @param string  $key        The activation key.
     * @param string  $user_login The username for the user.
     * @param WP_User $user_data  WP_User object.
     */
    $message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user_data );

    // Short-circuit on falsey $message value for backwards compatibility.
    if ( ! $message ) {
        return true;
    }

    /*
     * Wrap the single notification email arguments in an array
     * to pass them to the retrieve_password_notification_email filter.
     */
    $defaults = array(
        'to'      => $user_email,
        'subject' => $title,
        'message' => $message,
        'headers' => '',
    );

    /**
     * Filters the contents of the reset password notification email sent to the user.
     *
     * @since 6.0.0
     *
     * @param array $defaults {
     *     The default notification email arguments. Used to build wp_mail().
     *
     *     @type string $to      The intended recipient - user email address.
     *     @type string $subject The subject of the email.
     *     @type string $message The body of the email.
     *     @type string $headers The headers of the email.
     * }
     * @type string  $key        The activation key.
     * @type string  $user_login The username for the user.
     * @type WP_User $user_data  WP_User object.
     */
    $notification_email = apply_filters( 'retrieve_password_notification_email', $defaults, $key, $user_login, $user_data );


    if ( $switched_locale ) {
        restore_previous_locale();
    }

    if ( is_array( $notification_email ) ) {
        // Force key order and merge defaults in case any value is missing in the filtered array.
        $notification_email = array_merge( $defaults, $notification_email );
    } else {
        $notification_email = $defaults;
    }

    list( $to, $subject, $message, $headers ) = array_values( $notification_email );

    $subject = wp_specialchars_decode( $subject );

    $admin_email = get_option('admin_email');
    $from_name       = ( '' !== get_option( 'blogname' ) ) ? esc_html( get_option( 'blogname' ) ) : 'WordPress';
    $headers = "From: \"{$from_name}\" <{$admin_email}>\n" . 'Content-Type: text/plain; charset="' . get_option( 'blog_charset' ) . "\"\n";


    if ( ! wp_mail( $to, $subject, $message, $headers ) ) {
        $errors->add(
            'retrieve_password_email_failure',
            sprintf(
            /* translators: %s: Documentation URL. */
                __( '<strong>Error:</strong> The email could not be sent. Your site may not be correctly configured to send emails. <a href="%s">Get support for resetting your password</a>.' ),
                esc_url( __( 'https://wordpress.org/documentation/article/reset-your-password/' ) )
            )
        );
        return $errors;
    }

    return true;
}

