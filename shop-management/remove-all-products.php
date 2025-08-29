<?php
/**
 * Skrypt do usunięcia wszystkich produktów z WooCommerce
 * UWAGA: To usunie WSZYSTKIE produkty! Użyj ostrożnie.
 * Uruchom w przeglądarce: /wp-content/themes/krystian_k_sklep/remove-all-products.php
 */

// Załaduj WordPress
require_once('../../../../wp-load.php');

// Sprawdź czy WooCommerce jest aktywne
if (!class_exists('WooCommerce')) {
    die('WooCommerce nie jest zainstalowane lub aktywne.');
}

// Sprawdź czy użytkownik ma uprawnienia
if (!current_user_can('manage_woocommerce')) {
    die('Brak uprawnień do zarządzania WooCommerce.');
}

// Potwierdź usunięcie
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    echo '<h1>🚨 UWAGA! 🚨</h1>';
    echo '<p>Ten skrypt usunie <strong>WSZYSTKIE</strong> produkty z WooCommerce!</p>';
    echo '<p><a href="?confirm=yes" style="background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">TAK, usuń wszystkie produkty</a></p>';
    echo '<p><a href="' . home_url() . '">Anuluj</a></p>';
    exit;
}

echo '<h1>Usuwanie wszystkich produktów...</h1>';

// Pobierz wszystkie produkty
$products = get_posts(array(
    'post_type' => 'product',
    'posts_per_page' => -1,
    'post_status' => array('publish', 'draft', 'trash')
));

$deleted_count = 0;

foreach ($products as $product) {
    $result = wp_delete_post($product->ID, true); // true = force delete (bypass trash)
    if ($result) {
        echo '<p>✅ Usunięto: ' . $product->post_title . ' (ID: ' . $product->ID . ')</p>';
        $deleted_count++;
    } else {
        echo '<p>❌ Błąd podczas usuwania: ' . $product->post_title . '</p>';
    }
}

echo '<h2>Gotowe!</h2>';
echo '<p>Usunięto <strong>' . $deleted_count . '</strong> produktów.</p>';
echo '<p><a href="' . admin_url('edit.php?post_type=product') . '">Sprawdź listę produktów</a></p>';
echo '<p><a href="add-sample-products.php">Dodaj przykładowe produkty</a></p>';
?>
