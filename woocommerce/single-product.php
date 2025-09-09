<?php
/**
 * WooCommerce Single Product Template (theme override)
 *
 * Ensures proper rendering of single product pages when this override exists.
 */

defined('ABSPATH') || exit;

get_header('shop');

// Output opening wrappers (theme hooks into these to render centered container)
do_action('woocommerce_before_main_content');

while (have_posts()) :
	the_post();
	if (function_exists('wc_get_template_part')) {
		wc_get_template_part('content', 'single-product');
	} else {
		the_content();
	}
endwhile; // end of the loop.

// Output closing wrappers
do_action('woocommerce_after_main_content');

get_footer('shop');
