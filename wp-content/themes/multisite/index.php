<?php if (have_posts()) : while (have_posts() ) : the_post();
    echo the_title();
    echo the_content();
    endwhile;
 endif;