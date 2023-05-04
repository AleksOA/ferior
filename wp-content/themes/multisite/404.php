<?php get_header(); ?>
<main>
    <?php
    $text_404_page = get_field('text_404_page', 'options');
    ?>
    <section class="top-404">
        <div class="container">
            <div class="top-404__wrapper">
                <div class="top-404__name">
                    <h1 class="name">404</h1>
                </div>
                <div class="top-404__content">
                    <?php if( $text_404_page ) : ?>
                        <div class="top-404__text">
                            <?php echo $text_404_page ?>
                        </div>
                    <?php endif?>
                    <a class="top-404__btn" href="<?php echo home_url(); ?>">Return to homepage</a>
                </div>
            </div>
        </div>
    </section>
</main>
<?php get_footer(); ?>


