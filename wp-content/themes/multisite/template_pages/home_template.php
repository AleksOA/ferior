<?php
/*
  Template Name: Home
 */
?>

<?php get_header(); ?>
<main>
    <section class="top">
        <div class="container">
            <a class="btn-sign-up btn-link" id="btnSign-Up" href="<?php echo get_permalink(17) ?>">SIGN UP 1</a>
            <a class="btn-signUp btn-link" id="btnSignUp" href="<?php echo get_permalink(11) ?>">SIGN UP 2</a>
            <a class="activate btn-link" id="activate" href="<?php echo get_permalink(13) ?>">ACTIVATE</a>
        </div>
    </section>

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
</main>
<?php
myone();
function myone(){
    $site_url = 'site2';
    $site_name = 'site';
    $current_user_ID = 15;

    $data_site = array(
        'domain' => $site_url. '.multisite.loc',
        'title' => $site_name,
        'user_id' => $current_user_ID,
        'options' => array(
            'template' => 'twentytwentytwo',
            'stylesheet' => 'twentytwentytwo',
            'current_theme' => 'Twenty Twenty-Two'
        )
    );
//wp_insert_site( $data_site );
    $login_url = 'http://' . $data_site["domain"] . '/wp-login.php';
//wp_safe_redirect($login_url, 301);
//    wp_redirect($login_url);
//    wp_redirect('http://site2.multisite.loc/wp-login.php');
//    exit;
//    echo '<meta http-equiv="refresh" content="0;url=http://site2.multisite.loc/wp-login.php">';

}

?>

    <?php echo button(); ?>

<?php get_footer(); ?>