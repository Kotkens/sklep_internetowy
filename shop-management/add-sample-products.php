<?php
/**
 * Skrypt do dodania przykładowych produktów do WooCommerce
 * Uruchom ten plik w przeglądarce: /wp-content/themes/krystian_k_sklep/add-sample-products.php
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

// Przykładowe produkty z kategoriami
$sample_products = array(
    // Elektronika
    array(
        'name' => 'iPhone 13 Pro 128GB',
        'category' => 'elektronika',
        'subcategory' => 'telefony-akcesoria',
        'price' => 3200,
        'sale_price' => 2890,
        'description' => 'Używany iPhone 13 Pro w bardzo dobrym stanie. Komplet z ładowarką.',
        'short_description' => 'iPhone 13 Pro 128GB - stan bardzo dobry'
    ),
    array(
        'name' => 'Samsung Galaxy S22',
        'category' => 'elektronika',
        'subcategory' => 'telefony-akcesoria',
        'price' => 2800,
        'description' => 'Samsung Galaxy S22 w doskonałym stanie, bez uszkodzeń.',
        'short_description' => 'Samsung Galaxy S22 - jak nowy'
    ),
    array(
        'name' => 'MacBook Air M1',
        'category' => 'elektronika',
        'subcategory' => 'komputery',
        'price' => 4200,
        'sale_price' => 3899,
        'description' => 'MacBook Air z procesorem M1, 8GB RAM, 256GB SSD. Stan idealny.',
        'short_description' => 'MacBook Air M1 - stan idealny'
    ),
    array(
        'name' => 'Dell XPS 13',
        'category' => 'elektronika',
        'subcategory' => 'komputery',
        'price' => 3500,
        'description' => 'Laptop Dell XPS 13, Intel i7, 16GB RAM, 512GB SSD.',
        'short_description' => 'Dell XPS 13 - wysoka wydajność'
    ),
    
    // Moda
    array(
        'name' => 'Kurtka zimowa North Face',
        'category' => 'moda',
        'subcategory' => 'odziez-damska',
        'price' => 450,
        'sale_price' => 320,
        'description' => 'Ciepła kurtka zimowa North Face, rozmiar M, kolor czarny.',
        'short_description' => 'Kurtka North Face - rozmiar M'
    ),
    array(
        'name' => 'Jeansy Levi\'s 501',
        'category' => 'moda',
        'subcategory' => 'odziez-meska',
        'price' => 180,
        'description' => 'Klasyczne jeansy Levi\'s 501, rozmiar 32/34, stan bardzo dobry.',
        'short_description' => 'Jeansy Levi\'s 501 - rozmiar 32/34'
    ),
    array(
        'name' => 'Sukienka letnia Zara',
        'category' => 'moda',
        'subcategory' => 'odziez-damska',
        'price' => 120,
        'description' => 'Letnia sukienka Zara, rozmiar S, wzór kwiatowy.',
        'short_description' => 'Sukienka Zara - rozmiar S'
    ),
    array(
        'name' => 'Buty Nike Air Max',
        'category' => 'moda',
        'subcategory' => 'buty',
        'price' => 280,
        'sale_price' => 220,
        'description' => 'Buty sportowe Nike Air Max, rozmiar 42, kolor biały.',
        'short_description' => 'Nike Air Max - rozmiar 42'
    ),
    
    // Dom i ogród
    array(
        'name' => 'Sofa narożna IKEA',
        'category' => 'dom-ogrod',
        'subcategory' => 'meble',
        'price' => 1200,
        'sale_price' => 899,
        'description' => 'Sofa narożna IKEA w kolorze szarym, bardzo wygodna.',
        'short_description' => 'Sofa narożna IKEA - kolor szary'
    ),
    array(
        'name' => 'Stół rozkładany dębowy',
        'category' => 'dom-ogrod',
        'subcategory' => 'meble',
        'price' => 800,
        'description' => 'Stół rozkładany z drewna dębowego, wymiary 140-180cm.',
        'short_description' => 'Stół dębowy rozkładany'
    ),
    array(
        'name' => 'Kosiarka elektryczna Bosch',
        'category' => 'dom-ogrod',
        'subcategory' => 'ogrod',
        'price' => 450,
        'description' => 'Kosiarka elektryczna Bosch, szerokość koszenia 32cm.',
        'short_description' => 'Kosiarka Bosch - 32cm'
    ),
    array(
        'name' => 'Zestaw narzędzi Stanley',
        'category' => 'dom-ogrod',
        'subcategory' => 'narzedzia',
        'price' => 350,
        'sale_price' => 280,
        'description' => 'Kompletny zestaw narzędzi Stanley w walizce.',
        'short_description' => 'Zestaw narzędzi Stanley'
    ),
    
    // Dziecko
    array(
        'name' => 'Wózek spacerowy Chicco',
        'category' => 'dziecko',
        'subcategory' => 'wozki-foteliki',
        'price' => 600,
        'sale_price' => 450,
        'description' => 'Wózek spacerowy Chicco, lekki i wygodny.',
        'short_description' => 'Wózek Chicco - lekki'
    ),
    array(
        'name' => 'Łóżeczko dziecięce białe',
        'category' => 'dziecko',
        'subcategory' => 'meble-dzieciece',
        'price' => 400,
        'description' => 'Łóżeczko dziecięce w kolorze białym, z materacem.',
        'short_description' => 'Łóżeczko białe z materacem'
    ),
    array(
        'name' => 'Klocki LEGO Creator',
        'category' => 'dziecko',
        'subcategory' => 'zabawki',
        'price' => 150,
        'description' => 'Zestaw klocków LEGO Creator, kompletny.',
        'short_description' => 'Klocki LEGO Creator'
    ),
    
    // Sport i turystyka
    array(
        'name' => 'Rower górski Trek',
        'category' => 'sport-turystyka',
        'subcategory' => 'rowery',
        'price' => 1800,
        'sale_price' => 1550,
        'description' => 'Rower górski Trek, koła 26", sprawny technicznie.',
        'short_description' => 'Rower Trek - koła 26"'
    ),
    array(
        'name' => 'Namiot 4-osobowy Coleman',
        'category' => 'sport-turystyka',
        'subcategory' => 'turystyka',
        'price' => 320,
        'description' => 'Namiot turystyczny Coleman dla 4 osób, wodoszczelny.',
        'short_description' => 'Namiot Coleman 4-osobowy'
    ),
    array(
        'name' => 'Hantle regulowane 2x20kg',
        'category' => 'sport-turystyka',
        'subcategory' => 'fitness',
        'price' => 280,
        'description' => 'Para hantli regulowanych, obciążenie do 20kg każdy.',
        'short_description' => 'Hantle 2x20kg'
    ),
    
    // Motoryzacja
    array(
        'name' => 'Opony zimowe Michelin 205/55R16',
        'category' => 'motoryzacja',
        'subcategory' => 'opony-felgi',
        'price' => 800,
        'sale_price' => 650,
        'description' => 'Komplet opon zimowych Michelin, stan bardzo dobry.',
        'short_description' => 'Opony Michelin 205/55R16'
    ),
    array(
        'name' => 'Nawigacja GPS Garmin',
        'category' => 'motoryzacja',
        'subcategory' => 'akcesoria-samochodowe',
        'price' => 220,
        'description' => 'Nawigacja samochodowa Garmin z mapami Europy.',
        'short_description' => 'Nawigacja Garmin GPS'
    )
);

echo '<h1>Dodawanie przykładowych produktów do WooCommerce</h1>';
echo '<ul>';

foreach ($sample_products as $product_data) {
    // Sprawdź czy produkt już istnieje
    $existing = get_page_by_title($product_data['name'], OBJECT, 'product');
    if ($existing) {
        echo '<li>Produkt "' . $product_data['name'] . '" już istnieje - pomijam</li>';
        continue;
    }
    
    // Utwórz nowy produkt
    $product = new WC_Product_Simple();
    
    // Podstawowe dane
    $product->set_name($product_data['name']);
    $product->set_description($product_data['description']);
    $product->set_short_description($product_data['short_description']);
    $product->set_regular_price($product_data['price']);
    
    if (isset($product_data['sale_price'])) {
        $product->set_sale_price($product_data['sale_price']);
    }
    
    // Status i widoczność
    $product->set_status('publish');
    $product->set_catalog_visibility('visible');
    $product->set_stock_status('instock');
    $product->set_manage_stock(false);
    
    // Zapisz produkt
    $product_id = $product->save();
    
    if ($product_id) {
        // Przypisz do kategorii głównej
        $main_category = get_term_by('slug', $product_data['category'], 'product_cat');
        if (!$main_category) {
            // Utwórz kategorię główną jeśli nie istnieje
            $main_category_result = wp_insert_term($product_data['category'], 'product_cat', array(
                'slug' => $product_data['category']
            ));
            if (!is_wp_error($main_category_result)) {
                $main_category = get_term($main_category_result['term_id'], 'product_cat');
            }
        }
        
        // Przypisz do podkategorii
        $subcategory = get_term_by('slug', $product_data['subcategory'], 'product_cat');
        if (!$subcategory && isset($product_data['subcategory'])) {
            // Utwórz podkategorię jeśli nie istnieje
            $subcategory_result = wp_insert_term($product_data['subcategory'], 'product_cat', array(
                'slug' => $product_data['subcategory'],
                'parent' => $main_category ? $main_category->term_id : 0
            ));
            if (!is_wp_error($subcategory_result)) {
                $subcategory = get_term($subcategory_result['term_id'], 'product_cat');
            }
        }
        
        // Przypisz kategorie do produktu
        $category_ids = array();
        if ($main_category) {
            $category_ids[] = $main_category->term_id;
        }
        if ($subcategory) {
            $category_ids[] = $subcategory->term_id;
        }
        
        if (!empty($category_ids)) {
            wp_set_object_terms($product_id, $category_ids, 'product_cat');
        }
        
        echo '<li>✅ Dodano produkt: <strong>' . $product_data['name'] . '</strong> (ID: ' . $product_id . ')</li>';
    } else {
        echo '<li>❌ Błąd podczas dodawania produktu: ' . $product_data['name'] . '</li>';
    }
}

echo '</ul>';
echo '<h2>Gotowe! Dodano przykładowe produkty.</h2>';
echo '<p><a href="' . admin_url('edit.php?post_type=product') . '">Przejdź do listy produktów w panelu admin</a></p>';
echo '<p><a href="' . home_url() . '">Przejdź na stronę główną</a></p>';
?>
