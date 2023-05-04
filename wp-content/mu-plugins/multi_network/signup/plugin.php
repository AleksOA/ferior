<?php
function multi_network_signup_page( $url ) {
    return home_url( 'signup' );
}
add_filter ('wp_signup_location', 'multi_network_signup_page', 99);



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
    if ( ! apply_filters( 'wpmu_signup_user_notification', $user_login, $user_email, $key, $meta ) ) {
        return false;
    }

    $user            = get_user_by( 'login', $user_login );
    $switched_locale = $user && switch_to_user_locale( $user->ID );

    // Send email with activation link.
//    $admin_email = get_site_option( 'admin_email' ); WAS
    $blog_id = get_current_blog_id();
    switch_to_blog( $blog_id );
    $admin_email = get_site_option( 'admin_email' );


    if ( '' === $admin_email ) {
//        $admin_email = 'support@' . wp_parse_url( network_home_url(), PHP_URL_HOST );  ==== WAS ====
        $admin_email = 'support@' . wp_parse_url( home_url(), PHP_URL_HOST );
    }

    $from_name       = ( '' !== get_site_option( 'site_name' ) ) ? esc_html( get_site_option( 'site_name' ) ) : 'WordPress';
    $message_headers = "From: \"{$from_name}\" <{$admin_email}>\n" . 'Content-Type: text/plain; charset="' . get_option( 'blog_charset' ) . "\"\n";
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
//        site_url( "wp-activate.php?key=$key" ) WAS
        site_url( "activate?key=$key" )
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
    restore_current_blog();
    return false;
}

//add_filter( 'wpmu_signup_user_notification', 'multi_network_wpmu_signup_user_notification', 10, 4 );






//function multi_network_wpmu_signup_user_notification( $user, $user_email, $key, $meta = array() ) {
//    // Генерируем заголовок, текст и заголовки письма
//    // ...
//    // Отправляем письмо или добавляем Cron-задачу для отправки письма
//    wp_mail( $user_email, wp_specialchars_decode( $subject ), $message, $message_headers );
//
//    // Отдаем false, чтобы WordPress не отправил письмо активации дважды
//    return false;
//}

