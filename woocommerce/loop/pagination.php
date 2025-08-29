<?php
/**
 * Custom pagination template override.
 * Wzór: pierwsze 8 stron, wielokropek, ostatnie 3. Gdy łączna liczba <= 12 – pokaż wszystkie.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// WooCommerce przekazuje $total, $current. Dla pewności ustaw defaulty.
$total   = isset( $total ) ? (int)$total : 1;
$current = isset( $current ) ? (int)$current : max( 1, get_query_var( 'paged' ) );
if ( $total <= 1 ) { return; }

$current = max( 1, intval( $current ) );
$total   = intval( $total );

$pages = [];
if ( $total <= 12 ) {
    // show all pages
    for ( $i = 1; $i <= $total; $i++ ) $pages[] = $i;
} else {
    // first 8
    for ( $i = 1; $i <= 8; $i++ ) $pages[] = $i;
    // last 3
    for ( $i = $total - 2; $i <= $total; $i++ ) $pages[] = $i;
}
$pages = array_unique( $pages );
$pages = array_filter( $pages, function( $p ) use ( $total ) { return $p >= 1 && $p <= $total; } );
sort( $pages );

// Build output
echo '<nav class="woocommerce-pagination"><ul class="page-numbers">';

// Prev
if ( $current > 1 ) {
    echo '<li><a class="prev page-numbers" href="' . esc_url( get_pagenum_link( $current - 1 ) ) . '">←</a></li>';
}

$last_printed = 0;
foreach ( $pages as $p ) {
    if ( $p - $last_printed > 1 ) {
        // gap -> ellipsis
        echo '<li><span class="page-numbers dots">…</span></li>';
    }
    if ( $p == $current ) {
        echo '<li><span class="page-numbers current">' . esc_html( $p ) . '</span></li>';
    } else {
        echo '<li><a class="page-numbers" href="' . esc_url( get_pagenum_link( $p ) ) . '">' . esc_html( $p ) . '</a></li>';
    }
    $last_printed = $p;
}
// Next
if ( $current < $total ) {
    echo '<li><a class="next page-numbers" href="' . esc_url( get_pagenum_link( $current + 1 ) ) . '">→</a></li>';
}

// Skok do strony – placeholder pattern
$pattern_raw = get_pagenum_link( 999999 );
$pattern = str_replace( '999999', '{page}', esc_url( $pattern_raw ) );
echo '</ul>';
echo '<div class="page-jump-wrapper" style="margin:18px 0 0;display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:center;">';
echo '<label for="wc-page-jump" style="font-size:12px;font-weight:600;">Idź do strony:</label>';
echo '<input type="number" id="wc-page-jump" min="1" max="' . esc_attr( $total ) . '" value="' . esc_attr( $current ) . '" style="width:70px;padding:6px 8px;border:1px solid #cbd5e1;border-radius:4px;" />';
echo '<button type="button" id="wc-page-jump-btn" style="padding:6px 14px;border:1px solid #334155;background:#1e3a8a;color:#fff;font-size:12px;border-radius:4px;cursor:pointer;">OK</button>';
echo '</div>';
echo '<script>(function(){var inp=document.getElementById("wc-page-jump"),btn=document.getElementById("wc-page-jump-btn");if(!inp||!btn)return;var pattern="' . $pattern . '";function go(){var p=parseInt(inp.value,10);if(!p||p<1)p=1;var max=' . (int)$total . ';if(p>max)p=max;window.location.href=pattern.replace("{page}",p);}btn.addEventListener("click",go);inp.addEventListener("keydown",function(e){if(e.key==="Enter"){e.preventDefault();go();}});}());</script>';
echo '</nav>';
