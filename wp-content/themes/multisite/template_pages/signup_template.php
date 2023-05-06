<?php
/*
  Template Name: SighUp
 */
?>

<?php get_header(); ?>
<main>
    <section class="top">
        <div class="container">
            <a class="btn-signUp btn-link" id="btnSignUp" href="<?php echo home_url(); ?>">HOME</a>
            <a class="btn-signUp btn-link" id="btnSignUp" href="<?php echo home_url('wp-login.php'); ?>">LOGIN</a>
        </div>
    </section>
    <section class="form-sing-up">
        <div class="container">
            <div class="form-sing-up__form main-form">
                <?php echo do_shortcode('[network_signup]') ?>
            </div>
        </div>
    </section>
</main>
<?php get_footer(); ?>


