<?php
/**
 * Template Name: Test Atrybutów Produktu
 * Description: Tymczasowa strona do podglądu atrybutów produktów. Widoczna tylko dla administratora.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// Ogranicz dostęp do administratorów (usuń warunek jeśli ma być publicznie)
if ( ! current_user_can( 'manage_options' ) ) {
    status_header(403);
    wp_die( 'Brak dostępu (tylko administrator).' );
}

get_header();
?>
<style>
.attr-test-container{background:#fff;padding:32px;margin:20px auto;max-width:1100px;border-radius:12px;box-shadow:0 4px 18px rgba(0,0,0,.06);font-family:system-ui,sans-serif;font-size:14px;line-height:1.4}
.attr-test-container h1{margin-top:0;font-size:24px}
.attr-test-grid{display:grid;gap:18px;margin-top:24px}
@media(min-width:800px){.attr-test-grid{grid-template-columns:repeat(auto-fill,minmax(260px,1fr))}}
.attr-card{border:1px solid #e3e6eb;border-radius:10px;padding:14px 16px;display:flex;flex-direction:column;gap:8px;background:#fafbfc;position:relative}
.attr-card h2{font-size:15px;margin:0 0 4px;font-weight:600}
.attr-badge{display:inline-block;background:#eef3ff;color:#1d4ed8;font-size:11px;padding:2px 6px;border-radius:6px;margin:0 4px 4px 0;font-weight:500}
.attr-list{margin:0;padding:0;list-style:none;display:flex;flex-wrap:wrap;gap:6px}
.attr-list li{background:#f1f5f9;padding:4px 8px;border-radius:6px;font-size:12px}
.empty-note{color:#888;font-style:italic;font-size:12px}
.nav-links a{display:inline-block;margin:0 6px 6px 0;padding:6px 10px;border:1px solid #d0d7de;border-radius:6px;text-decoration:none;font-size:12px;background:#fff}
.nav-links a:hover{background:#f6f8fa}
.inline-form{margin-top:16px;display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.inline-form input[type=number]{width:120px;padding:6px 8px}
.inline-form button{padding:6px 14px;border:0;background:#2563eb;color:#fff;border-radius:6px;cursor:pointer}
.inline-form button:hover{background:#1d4ed8}
.back-link{display:inline-block;margin-top:20px;font-size:12px}
.code-small{font-family:monospace;font-size:12px;background:#111;color:#0f0;padding:8px 10px;border-radius:6px;max-height:280px;overflow:auto;margin-top:14px}
</style>
<div class="attr-test-container">
	<h1>Test atrybutów produktów</h1>
	<p>Podgląd techniczny atrybutów WooCommerce. Widoczny tylko dla administratorów. Usuń plik <code>page-test-atrybuty.php</code> po zakończeniu testów.</p>
<?php
// Jeśli podano param product=ID pokaż szczegóły jednego produktu
$product_id = isset($_GET['product']) ? intval($_GET['product']) : 0;
if ( $product_id ) {
    $product = wc_get_product( $product_id );
    if ( ! $product ) {
        echo '<p>Produkt o ID '.esc_html($product_id).' nie istnieje.</p>'; 
        echo '<p><a class="back-link" href="'.esc_url( remove_query_arg('product') ).'">← Wróć do listy</a></p>';
    } else {
        echo '<h2>Szczegóły produktu: '.esc_html( $product->get_name() ).' (ID '.$product_id.")</h2>";
        echo '<div class="nav-links"><a href="'.esc_url( remove_query_arg('product') ).'">← Lista produktów</a> <a target="_blank" href="'.esc_url( get_permalink($product_id) ).'">Podgląd na froncie ↗</a></div>';

        $attrs = $product->get_attributes();
        if ( empty( $attrs ) ) {
            echo '<p class="empty-note">Brak zdefiniowanych atrybutów.</p>';
        } else {
            echo '<div class="attr-test-grid">';
            foreach ( $attrs as $key => $attr ) {
                echo '<div class="attr-card">';
                $label = $attr->is_taxonomy() ? wc_attribute_label( $attr->get_name() ) : wc_attribute_label( $key );
                echo '<h2>'.esc_html($label).'</h2>';
                $values = [];
                if ( $attr->is_taxonomy() ) {
                    $terms = wp_get_post_terms( $product_id, $attr->get_name(), ['fields'=>'names'] );
                    $values = $terms; 
                } else {
                    $values = $attr->get_options();
                }
                if ( $values ) {
                    echo '<ul class="attr-list">';
                    foreach ( $values as $v ) echo '<li>'.esc_html($v).'</li>';
                    echo '</ul>';
                } else {
                    echo '<span class="empty-note">(puste)</span>';
                }
                echo '</div>';
            }
            echo '</div>';
        }

        // Surowy dump (debug)
        echo '<details style="margin-top:30px"><summary>Surowe dane (var_dump)</summary>'; 
        ob_start(); var_dump( $attrs ); $raw = ob_get_clean();
        echo '<pre class="code-small">'.esc_html($raw).'</pre></details>';
    }
} else {
    // Lista produktów z paginacją (tylko ID i nazwa + link do atrybutów)
    $paged = max(1, intval( get_query_var('paged') ) );
    $per_page = 12;
    $q = new WP_Query([
        'post_type' => 'product',
        'posts_per_page' => $per_page,
        'paged' => $paged,
        'post_status' => 'publish'
    ]);
    if ( $q->have_posts() ) {
        echo '<form class="inline-form" method="get"><label for="product">Szybki podgląd ID:</label> <input type="number" name="product" id="product" placeholder="ID produktu"> <button type="submit">Pokaż</button></form>';
        echo '<div class="attr-test-grid">';
        while ( $q->have_posts() ) { $q->the_post(); global $product; 
            $pid = get_the_ID();
            $attrs = $product ? $product->get_attributes() : [];
            echo '<div class="attr-card">';
            echo '<h2>'.esc_html( get_the_title() ).'</h2>';
            echo '<div><span class="attr-badge">ID '.$pid.'</span>';
            if ( $attrs ) echo '<span class="attr-badge">'.count($attrs).' atrybut(y)</span>'; else echo '<span class="attr-badge" style="background:#fee2e2;color:#b91c1c">0 atrybutów</span>';
            echo '</div>';
            if ( $attrs ) {
                $shown = 0; echo '<ul class="attr-list">';
                foreach ( $attrs as $key => $attr ) { if ( $shown >= 4 ) break; $label = $attr->is_taxonomy()? wc_attribute_label($attr->get_name()): wc_attribute_label($key); echo '<li>'.esc_html($label).'</li>'; $shown++; }
                echo '</ul>';
            } else {
                echo '<span class="empty-note">Brak</span>';
            }
            echo '<a class="back-link" style="margin-top:auto" href="'.esc_url( add_query_arg('product', $pid) ).'">➡ Pełne atrybuty</a>';
            echo '</div>';
        }
        echo '</div>';
        // Paginacja
        $big = 999999999; 
        $links = paginate_links([
            'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
            'format' => '?paged=%#%',
            'current' => $paged,
            'total' => $q->max_num_pages,
            'type' => 'array',
            'prev_text' => '«',
            'next_text' => '»'
        ]);
        if ( $links ) {
            echo '<div class="nav-links" style="margin-top:24px">';
            foreach ( $links as $l ) echo $l.' ';
            echo '</div>';
        }
        wp_reset_postdata();
    } else {
        echo '<p>Brak produktów.</p>';
    }
}
?>
</div>
<?php get_footer(); ?>
