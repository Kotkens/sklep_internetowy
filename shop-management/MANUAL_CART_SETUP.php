<?php
/**
 * MANUAL SETUP SCRIPT - Uruchom to jeśli chcesz skonfigurować koszyk ręcznie
 * 
 * Skopiuj ten kod do functions.php na końcu pliku, zapisz, 
 * następnie usuń go po wykonaniu konfiguracji.
 */

// UWAGA: Ten kod uruchomi się NATYCHMIAST po zapisaniu functions.php
// Usuń go po wykonaniu konfiguracji!

add_action('init', 'preomar_manual_cart_setup_now', 1);

function preomar_manual_cart_setup_now() {
    // Sprawdź czy już uruchomione (żeby nie powtarzać)
    if (get_transient('preomar_manual_setup_done')) {
        return;
    }
    
    // Loguj początek
    error_log('=== PREOMAR MANUAL SETUP START ===');
    
    // 1. Konfiguracja permalinków
    $permalink_structure = get_option('permalink_structure');
    if (empty($permalink_structure)) {
        update_option('permalink_structure', '/%postname%/');
        error_log('PreoMarket Manual: Permalinki ustawione na /%postname%/');
    } else {
        error_log('PreoMarket Manual: Permalinki już skonfigurowane: ' . $permalink_structure);
    }
    
    // 2. Tworzenie/konfiguracja strony koszyka
    $cart_page = get_page_by_path('koszyk');
    
    if (!$cart_page) {
        // Utwórz nową stronę koszyka
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
            error_log('PreoMarket Manual: Strona koszyka utworzona (ID: ' . $cart_page_id . ')');
            
            // Ustaw jako stronę koszyka WooCommerce
            if (class_exists('WooCommerce')) {
                update_option('woocommerce_cart_page_id', $cart_page_id);
                error_log('PreoMarket Manual: WooCommerce cart page ID ustawione: ' . $cart_page_id);
            }
        } else {
            error_log('PreoMarket Manual: BŁĄD przy tworzeniu strony koszyka: ' . print_r($cart_page_id, true));
        }
    } else {
        $cart_page_id = $cart_page->ID;
        error_log('PreoMarket Manual: Strona koszyka już istnieje (ID: ' . $cart_page_id . ')');
        
        // Sprawdź ustawienia WooCommerce
        if (class_exists('WooCommerce')) {
            $current_cart_id = get_option('woocommerce_cart_page_id');
            if ($current_cart_id != $cart_page_id) {
                update_option('woocommerce_cart_page_id', $cart_page_id);
                error_log('PreoMarket Manual: WooCommerce cart page ID zaktualizowane z ' . $current_cart_id . ' na ' . $cart_page_id);
            } else {
                error_log('PreoMarket Manual: WooCommerce cart page ID już poprawne: ' . $current_cart_id);
            }
        }
        
        // Sprawdź szablon
        $current_template = get_post_meta($cart_page_id, '_wp_page_template', true);
        if ($current_template !== 'page-koszyk.php') {
            update_post_meta($cart_page_id, '_wp_page_template', 'page-koszyk.php');
            error_log('PreoMarket Manual: Szablon strony zaktualizowany na page-koszyk.php');
        } else {
            error_log('PreoMarket Manual: Szablon strony już poprawny: ' . $current_template);
        }
    }
    
    // 3. Flush rewrite rules
    flush_rewrite_rules();
    error_log('PreoMarket Manual: Rewrite rules przeładowane');
    
    // 4. Sprawdzenie końcowego statusu
    $final_cart_page = get_page_by_path('koszyk');
    $final_permalinks = get_option('permalink_structure');
    $final_woo_cart = class_exists('WooCommerce') ? get_option('woocommerce_cart_page_id') : 'N/A';
    
    error_log('=== PREOMAR MANUAL SETUP RESULTS ===');
    error_log('Cart page exists: ' . ($final_cart_page ? 'YES (ID: ' . $final_cart_page->ID . ')' : 'NO'));
    error_log('Permalinks: ' . ($final_permalinks ? $final_permalinks : 'PLAIN'));
    error_log('WooCommerce cart ID: ' . $final_woo_cart);
    error_log('Cart URL should work: ' . home_url('/koszyk/'));
    error_log('=== PREOMAR MANUAL SETUP END ===');
    
    // Oznacz jako wykonane (na 1 godzinę)
    set_transient('preomar_manual_setup_done', true, HOUR_IN_SECONDS);
    
    // Jeśli to admin, pokaż komunikat
    if (is_admin()) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>PreoMarket Manual Setup:</strong> Konfiguracja koszyka wykonana! ';
            echo 'Sprawdź <a href="' . home_url('/koszyk/') . '" target="_blank">/koszyk/</a> ';
            echo '- <em>Usuń teraz kod manual setup z functions.php</em></p>';
            echo '</div>';
        });
    }
}

/*
INSTRUKCJA UŻYCIA:

1. Skopiuj kod powyżej na KONIEC pliku functions.php
2. Zapisz plik
3. Odśwież dowolną stronę WordPress (front-end lub admin)
4. Sprawdź logi błędów lub admin notices
5. Przetestuj URL /koszyk/
6. USUŃ ten kod z functions.php po udanej konfiguracji

SPRAWDZENIE LOGÓW:
- cPanel → File Manager → logs/error_log
- lub Hosting Panel → Error Logs
- Szukaj wpisów "PreoMarket Manual"

TESTOWANIE:
- Przejdź na: http://your-site.com/koszyk/
- Kliknij "Koszyk" w headerze
- Sprawdź czy stara strona przekierowuje
*/
?>
