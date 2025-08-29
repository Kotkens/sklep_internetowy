<?php
// NATYCHMIASTOWA NAPRAWA KOSZYKA
// Uruchom ten plik przez przeglądarkę: http://localhost/wordpress/wp-content/themes/krystian_k_sklep/fix-cart.php

require_once('../../../wp-config.php');
require_once('../../../wp-load.php');

if (!current_user_can('manage_options')) {
    die('Brak uprawnień administratora');
}

echo '<h1>🔧 NAPRAWA KOSZYKA</h1>';
echo '<style>body{font-family:Arial;padding:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;}</style>';

// 1. Sprawdź obecny stan
echo '<h2>📊 Obecny stan:</h2>';

$cart_page = get_page_by_path('koszyk');
if ($cart_page) {
    echo '<p class="ok">✅ Strona koszyka istnieje (ID: ' . $cart_page->ID . ')</p>';
} else {
    echo '<p class="error">❌ Strona koszyka NIE ISTNIEJE</p>';
}

$permalinks = get_option('permalink_structure');
if ($permalinks) {
    echo '<p class="ok">✅ Permalinki: ' . $permalinks . '</p>';
} else {
    echo '<p class="error">❌ Permalinki: PLAIN (trzeba zmienić)</p>';
}

$woo_cart_id = get_option('woocommerce_cart_page_id');
echo '<p class="' . ($woo_cart_id ? 'ok' : 'warning') . '">WooCommerce cart ID: ' . ($woo_cart_id ?: 'BRAK') . '</p>';

echo '<hr>';

// 2. NAPRAW PERMALINKI
echo '<h2>🔧 KROK 1: Naprawa permalinków</h2>';
if (empty($permalinks)) {
    update_option('permalink_structure', '/%postname%/');
    echo '<p class="ok">✅ Permalinki zmienione na /%postname%/</p>';
} else {
    echo '<p class="ok">✅ Permalinki już są skonfigurowane</p>';
}

// 3. UTWÓRZ STRONĘ KOSZYKA
echo '<h2>🔧 KROK 2: Tworzenie strony koszyka</h2>';
if (!$cart_page) {
    $cart_page_id = wp_insert_post(array(
        'post_title'    => 'Koszyk',
        'post_name'     => 'koszyk',
        'post_content'  => '[woocommerce_cart]',
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_author'   => 1,
        'meta_input'    => array(
            '_wp_page_template' => 'page-koszyk.php'
        )
    ));
    
    if ($cart_page_id && !is_wp_error($cart_page_id)) {
        echo '<p class="ok">✅ Strona koszyka utworzona (ID: ' . $cart_page_id . ')</p>';
        $cart_page = get_post($cart_page_id);
    } else {
        echo '<p class="error">❌ Błąd tworzenia strony: ' . (is_wp_error($cart_page_id) ? $cart_page_id->get_error_message() : 'Nieznany błąd') . '</p>';
    }
} else {
    echo '<p class="ok">✅ Strona koszyka już istnieje</p>';
}

// 4. KONFIGURACJA WOOCOMMERCE
echo '<h2>🔧 KROK 3: Konfiguracja WooCommerce</h2>';
if ($cart_page && class_exists('WooCommerce')) {
    update_option('woocommerce_cart_page_id', $cart_page->ID);
    echo '<p class="ok">✅ WooCommerce cart page ID ustawione na: ' . $cart_page->ID . '</p>';
} else {
    echo '<p class="warning">⚠️ Nie można skonfigurować WooCommerce (brak strony lub WooCommerce nieaktywne)</p>';
}

// 5. FLUSH REWRITE RULES
echo '<h2>🔧 KROK 4: Flush rewrite rules</h2>';
flush_rewrite_rules();
echo '<p class="ok">✅ Rewrite rules przeładowane</p>';

echo '<hr>';

// 6. SPRAWDZENIE KOŃCOWE
echo '<h2>🎯 SPRAWDZENIE KOŃCOWE:</h2>';

$final_cart_page = get_page_by_path('koszyk');
$final_permalinks = get_option('permalink_structure');
$final_woo_cart = get_option('woocommerce_cart_page_id');

echo '<p class="' . ($final_cart_page ? 'ok' : 'error') . '">Strona koszyka: ' . ($final_cart_page ? 'OK (ID: ' . $final_cart_page->ID . ')' : 'BRAK') . '</p>';
echo '<p class="' . ($final_permalinks ? 'ok' : 'error') . '">Permalinki: ' . ($final_permalinks ?: 'PLAIN') . '</p>';
echo '<p class="' . ($final_woo_cart ? 'ok' : 'warning') . '">WooCommerce: ' . ($final_woo_cart ?: 'BRAK') . '</p>';

echo '<hr>';

// 7. LINKI TESTOWE
echo '<h2>🚀 TESTOWANIE:</h2>';
echo '<p><strong>Sprawdź te linki:</strong></p>';
echo '<ul>';
echo '<li><a href="' . home_url('/koszyk/') . '" target="_blank">🛒 ' . home_url('/koszyk/') . '</a></li>';
echo '<li><a href="' . home_url() . '" target="_blank">🏠 Strona główna</a></li>';
if (class_exists('WooCommerce')) {
    echo '<li><a href="' . get_permalink(wc_get_page_id('shop')) . '" target="_blank">🛍️ Sklep</a></li>';
}
echo '</ul>';

echo '<hr>';
echo '<h2>✅ GOTOWE!</h2>';
echo '<p><strong>Jeśli wszystko jest OK, usuń ten plik (fix-cart.php) z bezpieczeństwa.</strong></p>';
echo '<p>URL koszyka: <a href="' . home_url('/koszyk/') . '" target="_blank"><strong>' . home_url('/koszyk/') . '</strong></a></p>';
?>
