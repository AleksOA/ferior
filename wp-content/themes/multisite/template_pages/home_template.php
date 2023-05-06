<?php
/*
  Template Name: Home
 */
?>

<?php get_header(); ?>
<main>
    <section class="top">
        <div class="container">
            <a class="btn-signUp btn-link" id="btnSignUp" href="<?php echo home_url('signup') ?>">SIGN UP</a>
            <a class="btn-signUp btn-link" id="btnSignUp" href="<?php echo home_url('wp-login.php') ?>">LOGIN</a>
        </div>
    </section>
    <?php
    $siteName = wp_parse_url( home_url(), PHP_URL_HOST );
    if($siteName == 'ferior.com.ua') : ?>
        <section class="form">
            <?php
            $title_form_main_form_home_template = get_field('title_form_main_form_home_template');
            ?>
            <div class="container">
                <?php if( $title_form_main_form_home_template ) : ?>
                    <h2 class="form__title"><?php echo $title_form_main_form_home_template ?></h2>
               <?php endif?>
                <div class="main-form">
                    <?php echo do_shortcode('[contact-form-7 id="15" title="Main form"]') ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php
    $siteName = wp_parse_url( home_url(), PHP_URL_HOST );
    if($siteName != 'ferior.com.ua') : ?>
        <section class="form">
            <div class="container">
                    <h2 class="form__title">Welcome to  <?php bloginfo('name'); ?> homepage!</h2>
            </div>
        </section>
    <?php endif; ?>


</main>

<?php get_footer(); ?>