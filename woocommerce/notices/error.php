<?php
/**
 * Error Notices
 *
 * Template override to strip any "View cart" button/link from messages.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @version 8.0.0 (customized)
 */

defined('ABSPATH') || exit;

if ( empty( $notices ) ) {
    return;
}
?>
<ul class="woocommerce-error" role="alert">
    <?php foreach ( $notices as $notice ) : ?>
        <li <?php echo wc_get_notice_data_attr( $notice ); ?>>
            <?php
            $html = isset($notice['notice']) ? $notice['notice'] : '';
            // Remove anchors with wc-forward or added_to_cart classes (covers "Zobacz koszyk").
            $html = preg_replace('#<a[^>]*class=("|\')(?:(?!\1).)*\b(wc-forward|added_to_cart)\b(?:(?!\1).)*\1[^>]*>.*?<\/a>#is','', $html);
            echo wp_kses_post( $html );
            ?>
        </li>
    <?php endforeach; ?>
</ul>
