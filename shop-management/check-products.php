<?php
/**
 * Skrypt do sprawdzenia istniejących produktów w WooCommerce
 * Uruchom w przeglądarce: /wp-content/themes/krystian_k_sklep/check-products.php
 */

// Załaduj WordPress
require_once('../../../../wp-load.php');

// Sprawdź czy WooCommerce jest aktywne
if (!class_exists('WooCommerce')) {
    die('WooCommerce nie jest zainstalowane lub aktywne.');
}

echo '<h1>Lista produktów w WooCommerce</h1>';

// Pobierz wszystkie produkty
$products = get_posts(array(
    'post_type' => 'product',
    'posts_per_page' => -1,
    'post_status' => 'publish'
));

if (empty($products)) {
    echo '<p>❌ Brak produktów w sklepie.</p>';
    echo '<p><a href="add-sample-products.php">Dodaj przykładowe produkty</a></p>';
} else {
    echo '<p>✅ Znaleziono <strong>' . count($products) . '</strong> produktów:</p>';
    echo '<table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">';
    echo '<tr><th>ID</th><th>Nazwa</th><th>Cena</th><th>Kategorie</th><th>Status</th></tr>';
    
    foreach ($products as $product) {
        $wc_product = wc_get_product($product->ID);
        $categories = get_the_terms($product->ID, 'product_cat');
        $category_names = $categories ? implode(', ', wp_list_pluck($categories, 'name')) : 'Brak kategorii';
        
        echo '<tr>';
        echo '<td>' . $product->ID . '</td>';
        echo '<td>' . $product->post_title . '</td>';
        echo '<td>' . ($wc_product ? wc_price($wc_product->get_price()) : 'Brak ceny') . '</td>';
        echo '<td>' . $category_names . '</td>';
        echo '<td>' . $product->post_status . '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

// Sprawdź kategorie
$categories = get_terms(array(
    'taxonomy' => 'product_cat',
    'hide_empty' => false
));

echo '<h2>Kategorie produktów</h2>';
if (empty($categories)) {
    echo '<p>Brak kategorii produktów.</p>';
} else {
    echo '<ul>';
    foreach ($categories as $category) {
        $parent = $category->parent ? ' (podkategoria)' : ' (kategoria główna)';
        echo '<li>' . $category->name . ' (' . $category->count . ' produktów)' . $parent . '</li>';
    }
    echo '</ul>';
}

echo '<hr>';
echo '<p><a href="add-sample-products.php">Dodaj przykładowe produkty</a></p>';
echo '<p><a href="remove-all-products.php">Usuń wszystkie produkty</a></p>';
echo '<p><a href="' . admin_url('edit.php?post_type=product') . '">Panel admin - produkty</a></p>';
echo '<p><a href="' . home_url() . '">Strona główna</a></p>';
?>
