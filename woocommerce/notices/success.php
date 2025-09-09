<?php
/**
 * Success Notices
 *
 * Template override to strip any "View cart" button/link from messages.
 *
 * @version 8.0.0 (customized)
 */
defined('ABSPATH') || exit;
if ( empty( $notices ) ) { return; }
?>
<ul class="woocommerce-message" role="alert">
    <?php foreach ( $notices as $notice ) : ?>
        <li <?php echo wc_get_notice_data_attr( $notice ); ?>>
            <?php
            $html = isset($notice['notice']) ? $notice['notice'] : '';
            $html = preg_replace('#<a[^>]*class=("|\')(?:(?!\1).)*\b(wc-forward|added_to_cart)\b(?:(?!\1).)*\1[^>]*>.*?<\/a>#is','', $html);
            echo wp_kses_post( $html );
            ?>
        </li>
    <?php endforeach; ?>
</ul>
