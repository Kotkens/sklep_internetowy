<?php
/**
 * Archive Product Template Override
 * Renders within theme wrapper (woocommerce.php). Do not output header/footer here.
 */

defined('ABSPATH') || exit;

/**
 * Hook: woocommerce_before_main_content.
 */
do_action('woocommerce_before_main_content');

echo "<!-- PREOMAR ARCHIVE_CONTENT_START -->";

if ( apply_filters( 'woocommerce_product_loop', wc_get_loop_prop( 'total' ) ) ) {
    /**
     * Hook: woocommerce_before_shop_loop.
     */
    do_action('woocommerce_before_shop_loop');

    echo "<!-- PREOMAR ARCHIVE_LOOP_START -->";

    woocommerce_product_loop_start();

    if ( wc_get_loop_prop( 'total' ) === 0 ) {
        // Defensive: ensure loop total reflects the main query when missing.
        global $wp_query;
        if ( $wp_query ) {
            wc_set_loop_prop( 'total', (int) $wp_query->found_posts );
        }
    }

    while ( have_posts() ) {
        the_post();
        wc_get_template_part( 'content', 'product' );
    }

    woocommerce_product_loop_end();

    echo "<!-- PREOMAR ARCHIVE_LOOP_END -->";

    /**
     * Hook: woocommerce_after_shop_loop.
     */
    do_action('woocommerce_after_shop_loop');

} else {
    /**
     * Hook: woocommerce_no_products_found.
     */
    do_action('woocommerce_no_products_found');
}

echo "<!-- PREOMAR ARCHIVE_CONTENT_END -->";

/**
 * Hook: woocommerce_after_main_content.
 */
do_action('woocommerce_after_main_content');
