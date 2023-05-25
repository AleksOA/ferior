<?php
/*
  Template Name: Home
 */
?>
<?php session_start(); ?>
<?php get_header(); ?>
<main>
    <section class="top">
        <div class="container">
            <a class="btn-signUp btn-link" id="btnSignUp" href="<?php echo home_url('signup') ?>">SIGN UP</a>
            <a class="btn-signUp btn-link" id="btnSignUp" href="<?php echo home_url('login') ?>">LOGIN</a>
        </div>
    </section>
    <?php
    $siteName = wp_parse_url( home_url(), PHP_URL_HOST );
    if($siteName == 'ferior.com.ua') : ?>
<!--        <section class="form">-->
<!--            --><?php
//            $title_form_main_form_home_template = get_field('title_form_main_form_home_template');
//            ?>
<!--            <div class="container">-->
<!--                --><?php //if( $title_form_main_form_home_template ) : ?>
<!--                    <h2 class="form__title">--><?php //echo $title_form_main_form_home_template ?><!--</h2>-->
<!--               --><?php //endif?>
<!--                <div class="main-form">-->
<!--                    --><?php //echo do_shortcode('[contact-form-7 id="15" title="Main form"]') ?>
<!--                </div>-->
<!--            </div>-->
<!--        </section>-->


        <?php if(!is_user_logged_in() ) :  ?>
            <section class="form">
                <?php
                $title_form_main_form_home_template = get_field('title_form_main_form_home_template');
                ?>
                <div class="container">
                    <?php if( $title_form_main_form_home_template ) : ?>
                        <h2 class="form__title"><?php echo $title_form_main_form_home_template ?></h2>
                    <?php endif?>
                    <div class="main-form">
                        <?php echo do_shortcode('[gravityform id="1" title="true"]') ?>
                    </div>
                </div>
            </section>
        <?php endif;

        if(is_user_logged_in() ) :  ?>
            <section class="form">
                <?php
                $title_form_main_form_home_template = 'Fill out the form to create your website';
                ?>
                <div class="container">
                    <?php if( $title_form_main_form_home_template ) : ?>
                        <h2 class="form__title"><?php echo $title_form_main_form_home_template ?></h2>
                    <?php endif?>
                    <div class="main-form">
                        <?php echo do_shortcode('[gravityform id="3" title="true"]') ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    <?php endif; ?>

    <?php
    $siteName = wp_parse_url( home_url(), PHP_URL_HOST );
    if($siteName != 'ferior.com.ua') : ?>
        <?php if(!is_user_logged_in() ) : ?>
        <section class="form">
            <div class="container">
                    <h2 class="form__title">Welcome to  <?php bloginfo('name'); ?> homepage!</h2>
            </div>
        </section>
        <?php endif; ?>

        <?php if(is_user_logged_in() ) :
            $current_user = wp_get_current_user();
            $username = $current_user->display_name;
        ?>
            <section class="form">
                <div class="container">
                    <h2 class="form__title"><?php echo $username ?>, welcome to  <?php bloginfo('name'); ?> homepage!</h2>
                </div>
            </section>
        <?php endif; ?>

    <?php endif; ?>
</main>

    <?php
global $wpdb;
$user_email = 'ovcharenko.dev@ukr.net';
$user_ID = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM wpfe_users WHERE user_email = %s", $user_email ) )->ID;
$user_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM wpfe_usermeta WHERE user_id = %s", $user_ID ) );
$blog_ID = get_current_blog_id();
$user_email_exists = '';
$nickname = '';
foreach ($user_data as $value) {
    if($value->meta_key == 'wpfe_' . $blog_ID . '_user_level'){
        $user_email_exists = true;
//        $nickname = $value->meta_value;
        $nickname = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM wpfe_users WHERE ID = %s", $user_ID ) )->user_nicename;
    }
}
?>

<?php get_footer(); ?>