<?php
/*
Template Name: Zamówienie (domyślne)
*/

get_header(); ?>

<?php
// Minimalny szablon: oddaj pełną kontrolę WooCommerce, bez własnego CSS i wrapperów
if (have_posts()) :
    while (have_posts()) : the_post();
    echo '<div class="preomar-checkout-container">';
    the_content();
    echo '</div>';
    endwhile;
endif;
?>

<?php get_footer(); ?>
