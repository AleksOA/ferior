<?php
/*
  Template Name: SighUp
 */
?>

<?php get_header(); ?>
<main>
    <section class="form-sing-up">
        <div class="container">
            <div class="form-sing-up__form main-form">
                <?php echo do_shortcode('[network_signup]') ?>
<!--                --><?php //echo multi_network_signup_main(); ?>
            </div>
        </div>
    </section>
</main>
<?php get_footer(); ?>


