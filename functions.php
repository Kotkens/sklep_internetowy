<?php
/**
 * PreoMarket - Motyw WordPress w stylu Allegro
 */

// Zapobieganie bezpośredniemu dostępowi
    // Add version number to force cache refresh
    $theme_version = wp_get_theme()->get('Version') . '.' . time();

if (!defined('ABSPATH')) {
    exit;
}

// Konfiguracja motywu
function preomar_setup() {
    // Wsparcie dla logo (konfigurowalne z panelu: Wygląd > Dostosuj > Tożsamość witryny)
    add_theme_support('custom-logo', [
        'height'               => 80,      // sugerowana wysokość
        'width'                => 80,      // sugerowana szerokość
        'flex-height'          => true,    // elastyczne wymiary – brak konieczności dokładnego przycięcia
        'flex-width'           => true,
        'header-text'          => [ 'site-title', 'site-description' ],
        'unlink-homepage-logo' => true     // na stronie głównej logo nie będzie linkiem (lepsza semantyka + SEO)
    ]);
    
    // Dodaj wsparcie dla miniaturek
    add_theme_support('post-thumbnails');
    // Dedykowany rozmiar dla podobnych produktów (twarde przycięcie 4:3)
    add_image_size('preomar_related', 600, 450, true);
    
    // Dodaj wsparcie dla tytułów
    add_theme_support('title-tag');
    
    // Dodaj wsparcie dla HTML5
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    
    // Dodaj wsparcie dla WooCommerce
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    
    // Zarejestruj menu
    register_nav_menus(array(
        'main-menu' => __('Menu główne', 'preomar'),
        'category-menu' => __('Menu kategorii', 'preomar'),
    ));
}
add_action('after_setup_theme', 'preomar_setup');

// Zapewnij poprawny atrybut alt dla logo jeśli nie ustawiono w bibliotece mediów
add_filter('get_custom_logo', function($html){
    if(!$html) return $html;
    $alt = esc_attr( get_bloginfo('name', 'display') );
    if(strpos($html, 'alt=') === false){
        $html = str_replace('<img ', '<img alt="'.$alt.'" ', $html);
    }
    return $html;
});

// ================== CUSTOMIZER: Tekst stopki ==================
add_action('customize_register', function($wp_customize){
    // Sekcja – jeśli chcesz osobną, tworzymy prostą sekcję motywu
    $wp_customize->add_section('preomar_footer_section', [
        'title'    => __('Stopka', 'preomar'),
        'priority' => 160,
    ]);

    $wp_customize->add_setting('preomar_footer_copyright', [
        'default'           => '© {YEAR} PreoMarket. Wszystkie prawa zastrzeżone.',
        'sanitize_callback' => function($val){
            $val = wp_kses_post($val); // pozwól na proste znaczniki (np. <a>)
            return $val;
        },
        'transport' => 'refresh',
    ]);

    $wp_customize->add_control('preomar_footer_copyright', [
        'label'       => __('Tekst praw autorskich', 'preomar'),
        'description' => __('Możesz użyć {YEAR} aby automatycznie wstawić bieżący rok.', 'preomar'),
        'section'     => 'preomar_footer_section',
        'type'        => 'textarea',
    ]);
});

// Helper do pobrania tekstu stopki (z makrem {YEAR})
function preomar_get_footer_copyright(){
    $raw = get_theme_mod('preomar_footer_copyright');
    if(!$raw){
        $raw = '© {YEAR} '.get_bloginfo('name').' – Wszystkie prawa zastrzeżone.';
    }
    $year = date('Y');
    $out = str_replace('{YEAR}', $year, $raw);
    return wp_kses_post($out);
}

// ================== FAVICON (fallback -> logo_strona.png) ==================
// WordPress: jeśli użytkownik nie ustawi Ikony witryny (Site Icon) w Dostosuj > Tożsamość witryny,
// użyjemy pliku motywu assets/images/logo_strona.png jako faviconu.
add_filter('get_site_icon_url', function($url){
    if (empty($url)) {
        $fallback = get_template_directory_uri() . '/assets/images/logo_strona.png';
        return $fallback;
    }
    return $url;
});
// Dodatkowo dołóż tagi w <head>, gdy brak oficjalnej ikony (dla przeglądarek które nie wołają filtra).
add_action('wp_head', function(){
    if (!has_site_icon()) {
        $fallback = esc_url(get_template_directory_uri() . '/assets/images/logo_strona.png');
        echo "\n<link rel=\"icon\" href=\"{$fallback}\" sizes=\"32x32\">"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo "\n<link rel=\"icon\" href=\"{$fallback}\" sizes=\"192x192\">"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo "\n<link rel=\"apple-touch-icon\" href=\"{$fallback}\">"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo "\n<meta name=\"msapplication-TileImage\" content=\"{$fallback}\">"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}, 5);

/**
 * ================= HERO SLIDER (dynamiczny) =================
 * CPT: hero_slide + meta pola. Umożliwia edycję slidera z panelu WP.
 * Pola:
 * - heading_before (tekst przed <strong>)
 * - heading_strong (wyróżnione słowo w <strong>)
 * - discount (np. -40%)
 * - description (krótki opis w 2 liniach)
 * - button_text / button_url (opcjonalny przycisk)
 * - gradient (background CSS linear-gradient)
 * - active (checkbox – czy wyświetlać)
 * Wyrenderowanie: patrz template-parts/hero/slider.php
 */
add_action('init', function(){
    $labels = [
        'name' => 'Hero Slides',
        'singular_name' => 'Hero Slide',
        'menu_name' => 'Hero Slides',
        'add_new' => 'Dodaj slajd',
        'add_new_item' => 'Dodaj nowy slajd',
        'edit_item' => 'Edytuj slajd',
        'new_item' => 'Nowy slajd',
        'view_item' => 'Podgląd slajdu',
        'search_items' => 'Szukaj slajdów'
    ];
    register_post_type('hero_slide',[
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-images-alt2',
        'supports' => ['title','thumbnail','page-attributes'],
        'hierarchical' => false,
        'show_in_rest' => false,
    ]);
});

// Meta box
add_action('add_meta_boxes', function(){
    add_meta_box('preomar_hero_slide_meta','Ustawienia slajdu','preomar_render_hero_slide_meta','hero_slide','normal','high');
});

function preomar_render_hero_slide_meta($post){
    wp_nonce_field('preomar_save_hero_slide','preomar_hero_slide_nonce');
    $get = function($k) use ($post){ return get_post_meta($post->ID,$k,true); };
    $heading_before = esc_attr($get('_hs_heading_before'));
    $heading_strong = esc_attr($get('_hs_heading_strong'));
    $discount = esc_attr($get('_hs_discount'));
    $description = esc_textarea($get('_hs_description'));
    $button_text = esc_attr($get('_hs_button_text'));
    $button_url = esc_url($get('_hs_button_url'));
    $gradient = esc_attr($get('_hs_gradient'));
    $active = $get('_hs_active') === '1';
    echo '<p><label><strong>Nagłówek – część przed pogrubieniem</strong><br><input type="text" style="width:100%" name="_hs_heading_before" value="'.$heading_before.'" placeholder="Stylowy" /></label></p>';
    echo '<p><label><strong>Wyróżnione słowo (w <strong>)</strong><br><input type="text" style="width:100%" name="_hs_heading_strong" value="'.$heading_strong.'" placeholder="salon vintage" /></label></p>';
    echo '<p><label><strong>Discount (np. -40%)</strong><br><input type="text" name="_hs_discount" value="'.$discount.'" placeholder="-30%" /></label></p>';
    echo '<p><label><strong>Opis (linia 1 / 2 – użyj \n dla nowej linii)</strong><br><textarea name="_hs_description" rows="3" style="width:100%" placeholder="meble i akcesoria retro\nw najlepszych cenach">'.$description.'</textarea></label></p>';
    echo '<p style="display:flex;gap:12px;"><label style="flex:1"><strong>Przycisk – tekst</strong><br><input type="text" name="_hs_button_text" value="'.$button_text.'" placeholder="Zobacz oferty" /></label>';
    echo '<label style="flex:1"><strong>Przycisk – URL</strong><br><input type="url" name="_hs_button_url" value="'.$button_url.'" placeholder="/kategoria/dom-ogrod" /></label></p>';
    echo '<p><label><strong>Gradient tła (linear-gradient)</strong><br><input type="text" style="width:100%" name="_hs_gradient" value="'.$gradient.'" placeholder="linear-gradient(135deg,#ff5722 0%,#e91e63 100%)" /></label></p>';
    echo '<p><label><input type="checkbox" name="_hs_active" value="1" '.checked($active,true,false).'/> Aktywny (pokazuj w sliderze)</label></p>';
    echo '<p style="font-size:11px;color:#555">Ustaw obrazek wyróżniający jako grafikę slajdu (po prawej). Kolejność slajdów ustaw za pomocą "Atrybuty wpisu &gt; Kolejność".</p>';
}

add_action('save_post_hero_slide', function($post_id){
    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return; 
    if(!isset($_POST['preomar_hero_slide_nonce']) || !wp_verify_nonce($_POST['preomar_hero_slide_nonce'],'preomar_save_hero_slide')) return;
    $fields = [
        '_hs_heading_before' => 'sanitize_text_field',
        '_hs_heading_strong' => 'sanitize_text_field',
        '_hs_discount' => 'sanitize_text_field',
        '_hs_description' => function($v){ return wp_kses_post($v); },
        '_hs_button_text' => 'sanitize_text_field',
        '_hs_button_url' => 'esc_url_raw',
        '_hs_gradient' => 'sanitize_text_field',
    ];
    foreach($fields as $k=>$cb){ if(isset($_POST[$k])) { $val = $_POST[$k]; if(is_callable($cb)) $val = call_user_func($cb,$val); update_post_meta($post_id,$k,$val); } }
    update_post_meta($post_id,'_hs_active', isset($_POST['_hs_active']) ? '1':'0');
    // Invalidate cache
    delete_transient('preomar_hero_slides');
});

add_action('before_delete_post', function($pid){ if(get_post_type($pid)==='hero_slide'){ delete_transient('preomar_hero_slides'); } });

if(!function_exists('preomar_get_hero_slides')){
    function preomar_get_hero_slides(){
        $cached = get_transient('preomar_hero_slides');
        if($cached !== false) return $cached;
        // Pobierz wszystkie slajdy i później odfiltruj (aby nowe – bez meta aktywne – też się pokazały)
        $q = new WP_Query([
            'post_type' => 'hero_slide',
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'post_status' => 'publish'
        ]);
        $slides = [];
        if($q->have_posts()){
            while($q->have_posts()){ $q->the_post();
                $id = get_the_ID();
                $active_meta = get_post_meta($id,'_hs_active',true);
                if($active_meta === '0') continue; // jawnie wyłączony
                $gradient = get_post_meta($id,'_hs_gradient',true);
                // Prosta walidacja gradientu – tylko linear-gradient i brak średników
                if($gradient && !preg_match('/^linear-gradient\([^;]+\)$/i',$gradient)) $gradient = '';
                if(!$gradient) $gradient = 'linear-gradient(135deg,#4caf50 0%,#2e7d32 100%)';
                $desc = (string) get_post_meta($id,'_hs_description',true);
                $desc = str_replace(["\r\n","\r"],"\n",$desc);
                $lines = array_map('trim', explode("\n", $desc));
                $slides[] = [
                    'ID' => $id,
                    'title' => get_the_title(),
                    'heading_before' => get_post_meta($id,'_hs_heading_before',true),
                    'heading_strong' => get_post_meta($id,'_hs_heading_strong',true),
                    'discount' => get_post_meta($id,'_hs_discount',true),
                    'lines' => $lines,
                    'button_text' => get_post_meta($id,'_hs_button_text',true),
                    'button_url' => get_post_meta($id,'_hs_button_url',true),
                    'gradient' => $gradient,
                    'image_id' => get_post_thumbnail_id($id),
                ];
            }
            wp_reset_postdata();
        }
        set_transient('preomar_hero_slides', $slides, 5*MINUTE_IN_SECONDS);
        return $slides;
    }
}
// ==============================================================
// Kolumny w adminie dla CPT hero_slide
add_filter('manage_hero_slide_posts_columns', function($cols){
    $new = [];
    foreach($cols as $k=>$v){
        if($k==='title') $new['thumbnail']='Obraz';
        $new[$k]=$v;
        if($k==='title'){ $new['discount']='Rabat'; $new['active']='Aktywny'; }
    }
    return $new;
});
add_action('manage_hero_slide_posts_custom_column', function($col,$post_id){
    if($col==='discount') echo esc_html(get_post_meta($post_id,'_hs_discount',true));
    elseif($col==='active'){ $a = get_post_meta($post_id,'_hs_active',true); echo $a==='0' ? '<span style="color:#dc2626">Nie</span>' : '<span style="color:#16a34a">Tak</span>'; }
    elseif($col==='thumbnail'){ if(has_post_thumbnail($post_id)) echo get_the_post_thumbnail($post_id,'thumbnail',['style'=>'width:60px;height:auto;border-radius:4px;']); }
},10,2);
add_action('admin_notices', function(){
    $screen = get_current_screen();
    if(!$screen || $screen->post_type!=='hero_slide' || $screen->base!=='edit') return;
    echo '<div class="notice notice-info"><p><strong>Hero Slider:</strong> Nowo utworzone slajdy są domyślnie aktywne. Aby ukryć – edytuj slajd i odznacz pole "Aktywny". Kolejność ustawiasz w Kolumnie "Kolejność" (Quick Edit).</p></div>';
});

// ================== MINIMALNA INFORMACJA O CIASTECZKACH (WP + WooCommerce) ==================
// Shortcode: [preomar_cookies_info]
// Wstaw w treści Polityki prywatności. Zawiera wyłącznie technicznie niezbędne cookies motywu / WP / Woo.
add_shortcode('preomar_cookies_info', function(){
    $cookies = [
        [ 'name' => 'wordpress_test_cookie', 'czas' => 'Sesja', 'cel' => 'Sprawdza czy przeglądarka akceptuje ciasteczka (techniczne).' ],
        [ 'name' => 'wordpress_[hash]', 'czas' => 'Sesja', 'cel' => 'Uwierzytelnienie podczas logowania (panel / użytkownik zalogowany).' ],
        [ 'name' => 'wordpress_logged_in_[hash]', 'czas' => 'Sesja', 'cel' => 'Utrzymanie stanu zalogowania użytkownika.' ],
        [ 'name' => 'wp-settings-* / wp-settings-time-*', 'czas' => '1 rok', 'cel' => 'Personalizacja widoku panelu / interfejsu użytkownika WordPress.' ],
        [ 'name' => 'woocommerce_cart_hash', 'czas' => 'Sesja', 'cel' => 'Śledzi zmiany w koszyku aby odświeżać jego zawartość.' ],
        [ 'name' => 'woocommerce_items_in_cart', 'czas' => 'Sesja', 'cel' => 'Przechowuje liczbę pozycji w koszyku (wyświetlanie).' ],
        [ 'name' => 'wp_woocommerce_session_[hash]', 'czas' => '48 h', 'cel' => 'Przypisuje unikalny identyfikator anonimowej sesji klienta (odtworzenie koszyka).' ],
    ];
    ob_start();
    echo '<div class="preomar-cookies-info"><h3>Technicznie niezbędne ciasteczka</h3><p>Poniższa tabela opisuje podstawowe ciasteczka wykorzystywane przez WordPress i WooCommerce w celu zapewnienia działania logowania oraz koszyka. Podstawą prawną przetwarzania jest art. 6 ust. 1 lit. b RODO (realizacja umowy lub działania przed jej zawarciem) oraz art. 6 ust. 1 lit. f RODO (prawnie uzasadniony interes – zapewnienie bezpieczeństwa i funkcjonalności serwisu). Ciasteczka te są zawsze aktywne i nie wymagają zgody, ponieważ bez nich sklep nie działa prawidłowo.</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo '<table class="cookies-table" style="width:100%;border-collapse:collapse;margin:18px 0;font-size:.85rem;">';
    echo '<thead><tr style="background:#f1f5f9;"><th style="text-align:left;padding:10px 12px;border:1px solid #dbe1e8;">Nazwa</th><th style="text-align:left;padding:10px 12px;border:1px solid #dbe1e8;">Czas przechowywania</th><th style="text-align:left;padding:10px 12px;border:1px solid #dbe1e8;">Cel</th></tr></thead><tbody>';
    foreach($cookies as $c){
        echo '<tr><td style="padding:8px 12px;border:1px solid #e2e8f0;">'.esc_html($c['name']).'</td><td style="padding:8px 12px;border:1px solid #e2e8f0;white-space:nowrap;">'.esc_html($c['czas']).'</td><td style="padding:8px 12px;border:1px solid #e2e8f0;">'.esc_html($c['cel']).'</td></tr>';
    }
    echo '</tbody></table>';
    echo '<p style="font-size:.7rem;color:#64748b;">Uwaga: symbole <code>[hash]</code> oznaczają unikalny skrót generowany indywidualnie; <code>*</code> – część nazwy zależna od identyfikatora użytkownika. W przypadku dodania narzędzi analitycznych lub marketingowych należy zaktualizować tę sekcję i wdrożyć baner zgód.</p></div>';
    return ob_get_clean();
});
// =====================================================================

// ================== AUTOUTWORZENIE STRON: Regulamin / Polityka prywatności / Kontakt ==================
add_action('init', function(){
    // Wykonaj tylko jeśli nie ma transientu (aby nie sprawdzać na każdym request) – odśwież co 12h
    if (get_transient('preomar_legal_pages_checked')) return;
    set_transient('preomar_legal_pages_checked', 1, 12 * HOUR_IN_SECONDS);

    $pages = [
        [
            'title'   => 'Regulamin',
            'slug'    => 'regulamin',
            'option'  => 'woocommerce_terms_page_id', // Woo – jeżeli aktywne
            'content' => '<h2>Regulamin sklepu</h2><p>(Treść w przygotowaniu. Uzupełnij właściwą treść prawną: definicje, zakres usług, zamówienia, płatności, dostawa, odstąpienie, reklamacje, dane kontaktowe przedsiębiorcy.)</p>'
        ],
        [
            'title'   => 'Polityka prywatności',
            'slug'    => 'polityka-prywatnosci',
            'option'  => 'wp_page_for_privacy_policy', // WordPress core
            'content' => '<h2>Polityka prywatności</h2><p>(Szkielet – uzupełnij administratora, cele, podstawy prawne, odbiorców, okresy przechowywania, prawa osób.)</p><h3>Ciasteczka techniczne</h3>[preomar_cookies_info]'
        ],
        [
            'title'   => 'Kontakt',
            'slug'    => 'kontakt',
            'option'  => null,
            'content' => '<h2>Kontakt</h2><p>Masz pytania? Skorzystaj z formularza.</p>[preomar_contact_form]'
        ],
        [
            'title'   => 'Moje zakupy',
            'slug'    => 'moje-zakupy',
            'option'  => null,
            'content' => '<h2>Moje zakupy</h2>[preomar_purchased_products]'
        ],
    ];

    foreach($pages as $p){
        if (!get_page_by_path($p['slug'])) {
            $id = wp_insert_post([
                'post_title'   => $p['title'],
                'post_name'    => $p['slug'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => $p['content']
            ]);
            if ($id && !is_wp_error($id)) {
                // Ustaw nasz szablon legal
                update_post_meta($id, '_wp_page_template', 'page-legal.php');
                // Ustaw opcję jeśli wskazana i pusta
                if ($p['option'] && !get_option($p['option'])) {
                    update_option($p['option'], $id);
                }
                // WooCommerce własna opcja terms (jeśli aktywne) – już ustawiliśmy poprzez option w strukturze dla regulaminu
            }
        } else {
            // Jeśli strona istnieje, ewentualnie ustaw brakującą opcję
            $existing = get_page_by_path($p['slug']);
            if ($existing && $p['option'] && !get_option($p['option'])) {
                update_option($p['option'], $existing->ID);
            }
            // Ustaw nasz szablon legal jeśli jeszcze nie jest
            if ($existing && get_post_meta($existing->ID, '_wp_page_template', true) !== 'page-legal.php') {
                update_post_meta($existing->ID, '_wp_page_template', 'page-legal.php');
            }
        }
    }
    // Jednorazowa naprawa treści gdy wstawiono placeholder "coming soon"
    $fix_slugs = [
        'regulamin' => '<h2>Regulamin sklepu</h2><p>(Treść w przygotowaniu. Uzupełnij właściwą treść prawną: definicje, zakres usług, zamówienia, płatności, dostawa, odstąpienie, reklamacje, dane kontaktowe przedsiębiorcy.)</p>',
        'polityka-poprawnosci' => '<h2>Polityka prywatności</h2><p>(Szkielet – uzupełnij administratora, cele, podstawy prawne, odbiorców, okresy przechowywania, prawa osób.)</p><h3>Ciasteczka techniczne</h3>[preomar_cookies_info]',
        'kontakt' => '<h2>Kontakt</h2><p>Masz pytania? Skorzystaj z formularza.</p>[preomar_contact_form]'
    ];
    foreach ($fix_slugs as $slug => $fallback_html) {
        $p = get_page_by_path($slug);
        if (!$p || is_wp_error($p)) continue;
        $text = strtolower(wp_strip_all_tags($p->post_content));
        if ($text === '' || strpos($text, 'wielkie rzeczy') !== false || strpos($text, 'coming soon') !== false || strpos($text, 'wkrótce uruchomimy') !== false) {
            wp_update_post([ 'ID' => $p->ID, 'post_status' => 'publish', 'post_content' => $fallback_html ]);
            update_post_meta($p->ID, '_wp_page_template', 'page-legal.php');
        }
    }
});
// =====================================================================

// ================== SHORTCODE FORMULARZA KONTAKTOWEGO ==================
add_shortcode('preomar_contact_form', function(){
    ob_start();
    // Feedback po przekierowaniu
    $status = isset($_GET['contact']) ? sanitize_text_field($_GET['contact']) : '';
    $code   = isset($_GET['code']) ? sanitize_text_field($_GET['code']) : '';
    if ($status === 'ok') {
        echo '<div class="preomar-contact-alert success">Dziękujemy! Wiadomość została wysłana.</div>';
    } elseif ($status === 'err') {
        $msg = 'Nie udało się wysłać wiadomości.';
        if ($code === 'fields') $msg = 'Uzupełnij wymagane pola: Imię, Email i Wiadomość.';
        elseif ($code === 'email') $msg = 'Podaj prawidłowy adres e‑mail.';
        elseif ($code === 'nonce') $msg = 'Upłynął czas sesji. Odśwież stronę i spróbuj ponownie.';
        elseif ($code === 'send') $msg = 'Wystąpił błąd przy wysyłaniu. Spróbuj ponownie później.';
        echo '<div class="preomar-contact-alert error">'.esc_html($msg).'</div>';
    }

    echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'" class="preomar-contact-form preomar-contact-form-shortcode">';
    echo '<input type="hidden" name="action" value="preomar_simple_contact" />';
    // Nonce zabezpieczający
    echo wp_nonce_field('preomar_contact','preomar_contact_nonce', true, false);
    // Honeypot (ukryte pole – jeżeli wypełnione, to spam)
    echo '<div style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden" aria-hidden="true" tabindex="-1">'
        .'<label>Nie wypełniaj tego pola<input type="text" name="preomar_hp" value="" autocomplete="off"></label>'
        .'</div>';
    echo '<p><label>Imię<br><input type="text" name="name" required></label></p>';
    echo '<p><label>Email<br><input type="email" name="email" required></label></p>';
    echo '<p><label>Wiadomość<br><textarea name="message" rows="5" required></textarea></label></p>';
    echo '<p><button type="submit">Wyślij</button></p>';
    echo '</form>';
    return ob_get_clean();
});

// Obsługa formularza kontaktowego (admin-post.php)
function preomar_handle_simple_contact(){
    $redirect = home_url('/kontakt/');
    // Weryfikacja nonce
    if (!isset($_POST['preomar_contact_nonce']) || !wp_verify_nonce($_POST['preomar_contact_nonce'], 'preomar_contact')) {
        wp_safe_redirect(add_query_arg(['contact'=>'err','code'=>'nonce'], $redirect));
        exit;
    }
    // Honeypot – jeżeli bot wypełni, udaj sukces (nic nie wysyłamy)
    if (!empty($_POST['preomar_hp'])) {
        wp_safe_redirect(add_query_arg(['contact'=>'ok'], $redirect));
        exit;
    }

    $name = sanitize_text_field($_POST['name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $message = trim(wp_kses_post($_POST['message'] ?? ''));

    if ($name === '' || $email === '' || $message === '') {
        wp_safe_redirect(add_query_arg(['contact'=>'err','code'=>'fields'], $redirect));
        exit;
    }
    if (!is_email($email)) {
        wp_safe_redirect(add_query_arg(['contact'=>'err','code'=>'email'], $redirect));
        exit;
    }

    $to = get_option('admin_email');
    $subject = sprintf('Wiadomość z formularza kontaktowego – %s', $name);
    $body = sprintf("<p><strong>Imię:</strong> %s</p><p><strong>Email:</strong> %s</p><p><strong>Wiadomość:</strong><br>%s</p><hr><p>Strona: %s</p>", esc_html($name), esc_html($email), nl2br(esc_html($message)), esc_url(home_url('/')));
    $headers = [ 'Content-Type: text/html; charset=UTF-8', 'Reply-To: '. $name .' <'. $email .'>' ];

    $sent = wp_mail($to, $subject, $body, $headers);
    if ($sent) {
        wp_safe_redirect(add_query_arg(['contact'=>'ok'], $redirect));
    } else {
        wp_safe_redirect(add_query_arg(['contact'=>'err','code'=>'send'], $redirect));
    }
    exit;
}
add_action('admin_post_nopriv_preomar_simple_contact', 'preomar_handle_simple_contact');
add_action('admin_post_preomar_simple_contact', 'preomar_handle_simple_contact');
// =====================================================================

// ================== SHORTCODE: MOJE ZAKUPY (lista kupionych produktów) ==================
// [preomar_purchased_products]
add_shortcode('preomar_purchased_products', function(){
    if ( ! is_user_logged_in() ) {
        return '<p>Musisz być zalogowany aby zobaczyć listę zakupionych produktów.</p>';
    }
    if ( ! class_exists('WooCommerce') ) {
        return '<p>WooCommerce nie jest aktywny.</p>';
    }

    $customer_id = get_current_user_id();
    $orders = wc_get_orders([
        'limit'        => 50,
        'customer_id'  => $customer_id,
        'orderby'      => 'date',
        'order'        => 'DESC',
        'status'       => array('wc-completed','wc-processing','wc-on-hold'),
        'return'       => 'objects'
    ]);
    if ( ! $orders ) {
        return '<p>Brak zakupionych produktów.</p>';
    }

    $product_map = [];
    foreach ( $orders as $order ) {
        $order_date_ts = strtotime( $order->get_date_created()->date( 'Y-m-d H:i:s' ) );
        foreach ( $order->get_items() as $item ) {
            $pid = $item->get_product_id();
            if ( ! $pid ) continue;
            $product = $item->get_product();
            if ( ! $product ) continue;
            $qty_line = (float) $item->get_quantity();
            $line_price_single = (float) $product->get_price();
            if ( isset( $product_map[ $pid ] ) ) {
                $product_map[$pid]['qty'] += $qty_line;
                $product_map[$pid]['times'] += 1; // liczba linii (osobnych zakupów)
                $product_map[$pid]['value'] += $qty_line * $line_price_single;
                if ( $order_date_ts > $product_map[$pid]['last_date'] ) {
                    $product_map[$pid]['last_date'] = $order_date_ts;
                }
            } else {
                $product_map[$pid] = [
                    'product'   => $product,
                    'qty'       => $qty_line,
                    'times'     => 1,
                    'value'     => $qty_line * $line_price_single,
                    'last_date' => $order_date_ts,
                ];
            }
        }
    }

    if ( ! $product_map ) {
        return '<p>Brak zakupionych produktów.</p>';
    }

    // Sortuj – najpierw ostatnio kupione
    uasort( $product_map, function( $a, $b ) {
        return $b['last_date'] <=> $a['last_date'];
    });

    ob_start();

    echo '<div class="products-grid purchased-products-grid" id="purchasedProductsGrid">';
    foreach( $product_map as $pid => $row ) {
        /** @var WC_Product $product */
        $product = $row['product'];
        $qty = (int)$row['qty'];
        $times = (int)$row['times'];
        $last_date_h = date_i18n( 'd.m.Y', $row['last_date'] );
        $permalink = get_permalink( $pid );
        $name = $product->get_name();
        $regular = (float)$product->get_regular_price();
        $sale = (float)$product->get_sale_price();
        $on_sale = $product->is_on_sale() && $sale > 0 && $sale < $regular;
        $price_html = '';
        if ( $on_sale ) {
            $price_html = '<span class="price"><del>'.wc_price($regular).'</del> <ins>'.wc_price($sale).'</ins></span>';
        } else {
            $price_html = '<span class="price">'.wc_price($product->get_price()).'</span>';
        }
        $discount_badge = '';
        if ( $on_sale && $regular > 0 ) {
            $percent = round( ( ( $regular - $sale ) / $regular ) * 100 );
            if ( $percent > 0 ) {
                $discount_badge = '<span class="discount-badge">-'.intval($percent).'%</span>';
            }
        }
        // Add to cart link – użyj standardowych klas Woo aby działał AJAX i fragmenty
        $add_url = esc_url( $product->add_to_cart_url() );
        $add_text = esc_html( $product->add_to_cart_text() );
        $add_btn = '';
        if ( $product->is_purchasable() && $product->is_in_stock() ) {
            // Keep Woo default classes for AJAX + our theme class for styling
            $classes = [ 'button', 'add_to_cart_button', 'ajax_add_to_cart', 'add-to-cart-btn', 'product_type_'.esc_attr($product->get_type()) ];
            $attrs = ' href="'.$add_url.'" data-product_id="'.esc_attr($pid).'" data-quantity="1" rel="nofollow" aria-label="Dodaj do koszyka: '.esc_attr($name).'"';
            $add_btn = '<a class="'.implode(' ', $classes).'"'.$attrs.'>'.$add_text.'</a>';
        }
        echo '<div class="product-card purchased" data-product-id="'.esc_attr($pid).'" data-title="'.esc_attr( mb_strtolower( wp_strip_all_tags( $name ) ) ).'">';
        echo '<a href="'.esc_url($permalink).'" class="product-image-wrapper">';
        // Image
        $image_html = $product->get_image( 'woocommerce_thumbnail', [ 'class' => 'product-image' ] );
        echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $discount_badge; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<span class="pp-qty-badge" title="Kupiono łącznie">×'.$qty.'</span>';
        echo '</a>';
        echo '<div class="product-info">';
        echo '<h3 class="product-title"><a href="'.esc_url($permalink).'">'.esc_html($name).'</a></h3>';
        echo '<div class="product-price-wrapper">'.$price_html.'</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    // (opcjonalne meta można przywrócić: liczba zakupów / data)
        echo $add_btn; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
    // Prosty filtr JS
    echo '<script>(function(){const i=document.querySelector(".purchased-search-input");if(!i)return;const cards=[...document.querySelectorAll("#purchasedProductsGrid .product-card")];i.addEventListener("input",()=>{const q=i.value.trim().toLowerCase();cards.forEach(c=>{const t=c.getAttribute("data-title");c.style.display = !q || (t&&t.indexOf(q)>-1)?"":"none";});});})();</script>';
    return ob_get_clean();
});
// =====================================================================

// ================== PANEL ADMIN: EDYCJA TREŚCI REGULAMIN / POLITYKA / KONTAKT ==================
add_action('admin_menu', function(){
    add_submenu_page(
        'options-general.php',
        'Treści prawne',
        'Treści prawne',
        'manage_options',
        'preomar-legal-pages',
        'preomar_render_legal_pages_admin'
    );
});

function preomar_get_or_create_page($slug,$title,$default=''){
    $page = get_page_by_path($slug);
    if(!$page){
        $id = wp_insert_post([
            'post_title'=>$title,
            'post_name'=>$slug,
            'post_status'=>'publish',
            'post_type'=>'page',
            'post_content'=>$default
        ]);
        if(!is_wp_error($id)) $page = get_post($id);
    }
    return $page;
}

function preomar_render_legal_pages_admin(){
    if(!current_user_can('manage_options')) return;
    $updated = false;
    if(isset($_POST['preomar_legal_nonce']) && wp_verify_nonce($_POST['preomar_legal_nonce'],'preomar_legal_save')){
        // Zapisuj treść pod aktualnym slugi em polityki (polityka-poprawnosci). Jeśli jeszcze jej nie ma – utwórz.
        $map = [
            'regulamin' => ['title'=>'Regulamin','field'=>'preomar_regulamin'],
            'polityka-poprawnosci' => ['title'=>'Polityka poprawności','field'=>'preomar_polityka'],
            'kontakt' => ['title'=>'Kontakt','field'=>'preomar_kontakt'],
        ];
        foreach($map as $slug=>$info){
            $content = wp_kses_post($_POST[$info['field']] ?? '');
            $page = preomar_get_or_create_page($slug,$info['title']);
            if($page && !is_wp_error($page)){
                wp_update_post(['ID'=>$page->ID,'post_content'=>$content]);
                $updated = true;
            }
        }
        if($updated){
            echo '<div class="updated notice"><p>Treści zapisane.</p></div>';
        }
    }

    // Prefer the new slug polityka-poprawnosci; fall back to the old one if it doesn't exist yet
    $reg = preomar_get_or_create_page('regulamin','Regulamin');
    $pol = get_page_by_path('polityka-poprawnosci');
    if (!$pol) { $pol = preomar_get_or_create_page('polityka-prywatnosci','Polityka prywatności'); }
    $kon = preomar_get_or_create_page('kontakt','Kontakt');
    echo '<div class="wrap"><h1>Treści prawne / kontakt</h1><p>Edytuj treści stron. Formularz kontaktowy: użyj shortcode <code>[preomar_contact_form]</code>. Tabela ciasteczek: <code>[preomar_cookies_info]</code>.</p>';
    echo '<form method="post">';
    wp_nonce_field('preomar_legal_save','preomar_legal_nonce');
    echo '<h2>Regulamin</h2>';
    wp_editor($reg? $reg->post_content:'','preomar_regulamin',['textarea_name'=>'preomar_regulamin','textarea_rows'=>12]);
    echo '<p><a target="_blank" href="'.esc_url(get_permalink($reg)).'">Podgląd Regulaminu</a></p>';
    echo '<h2>Polityka poprawności</h2>';
    wp_editor($pol? $pol->post_content:'','preomar_polityka',['textarea_name'=>'preomar_polityka','textarea_rows'=>12]);
    echo '<p><a target="_blank" href="'.esc_url(get_permalink($pol)).'">Podgląd Polityki poprawności</a></p>';
    echo '<h2>Kontakt</h2>';
    wp_editor($kon? $kon->post_content:'','preomar_kontakt',['textarea_name'=>'preomar_kontakt','textarea_rows'=>10]);
    echo '<p><a target="_blank" href="'.esc_url(get_permalink($kon)).'">Podgląd strony kontakt</a></p>';
    submit_button('Zapisz treści');
    echo '</form></div>';
}
// =====================================================================
// PROSTE UI ilości: pojedynczy filtr generujący kompletny markup z liczbą sztuk
add_filter('woocommerce_quantity_input', function($html, $product){
    if(!$product) return $html;
    $manages = $product->managing_stock();
    $stock = $manages ? $product->get_stock_quantity() : null;
    $low = (int)apply_filters('preomar_low_stock_threshold',5,$product);
    $max = ($manages && $stock !== null) ? (int)$stock : 999999; // jeśli brak zarządzania – brak limitu realnego
    $max_attr = $manages && $stock !== null ? ' max="'.esc_attr($max).'"' : '';
    $value = isset($_REQUEST['quantity']) ? intval($_REQUEST['quantity']) : max(1,$product->get_min_purchase_quantity());
    if($value < 1) $value = 1; if($value > $max) $value = $max;
    $input = '<input type="number" class="qty" name="quantity" min="1"'.$max_attr.' step="1" value="'.esc_attr($value).'" inputmode="numeric" />';
    // Usunięto dopisek "Ostatnie sztuki!" przy niskim stanie magazynowym
    $stock_note = $manages && $stock !== null ? ('<span class="preomar-qty-available '.($stock <= $low ? 'preomar-qty-low':'').'">z '.number_format_i18n($stock).' sztuk</span>') : '<span class="preomar-qty-available">Dostępne</span>';
    $html = '<label class="preomar-qty-label">Liczba sztuk</label><div class="preomar-qty-wrapper"><div class="preomar-qty-box"><button type="button" class="preomar-qty-minus" aria-label="Zmniejsz">−</button>'.$input.'<button type="button" class="preomar-qty-plus" aria-label="Zwiększ">+</button></div>'.$stock_note.'</div>';
    if(!defined('PREOMAR_QTY_INLINE_LOADED')){define('PREOMAR_QTY_INLINE_LOADED',true); $html .= '<style>.preomar-qty-label{display:block;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#475569;margin:0 0 4px;} .preomar-qty-wrapper{display:flex;align-items:center;gap:14px;margin:10px 0 18px;flex-wrap:nowrap;} .preomar-qty-box{display:inline-flex;align-items:stretch;border:1px solid #cfd8e3;border-radius:10px;overflow:hidden;background:#fff;} .preomar-qty-box button{background:#f1f5f9;color:#0f172a;border:none;width:42px;font-size:1rem;cursor:pointer;font-weight:700;display:flex;align-items:center;justify-content:center;transition:.15s;} .preomar-qty-box button:hover{background:#e2e8f0;} .preomar-qty-box button:disabled{opacity:.35;cursor:default;} .preomar-qty-box input.qty{border:none;width:58px;text-align:center;font-weight:600;font-size:.9rem;padding:0;background:#fff;} .preomar-qty-available{font-size:.7rem;color:#64748b;white-space:nowrap;} .preomar-qty-low{color:#b45309;font-weight:600;} @media(max-width:640px){.preomar-qty-wrapper{flex-wrap:wrap;gap:8px;}}</style>'; }
    $html .= '<script>(function(){const wrap=document.querySelector(".preomar-qty-wrapper");if(!wrap) return;const minus=wrap.querySelector(".preomar-qty-minus"),plus=wrap.querySelector(".preomar-qty-plus"),inp=wrap.querySelector("input.qty");const max=parseInt(inp.getAttribute("max"),10)||999999;function clamp(v){if(v<1)v=1;if(v>max)v=max;return v;}function refresh(){inp.value=clamp(parseInt(inp.value,10)||1);minus.disabled=parseInt(inp.value,10)<=1;plus.disabled=parseInt(inp.value,10)>=max;}minus.addEventListener("click",()=>{inp.value=clamp(parseInt(inp.value,10)-1);refresh();inp.dispatchEvent(new Event("change"));});plus.addEventListener("click",()=>{inp.value=clamp(parseInt(inp.value,10)+1);refresh();inp.dispatchEvent(new Event("change"));});inp.addEventListener("input",refresh);refresh();})();</script>';
    return $html;
},20,2);

// Enqueue styles and scripts
function preomar_scripts() {
    register_post_type('promotions', array(
        'labels' => array(
            'name' => 'Promocje',
            'singular_name' => 'Promocja',
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail'),
    ));
    // Add version number to force cache refresh
    $theme_version = wp_get_theme()->get('Version') . '.' . time();
    
    wp_enqueue_style('preomar-style', get_stylesheet_uri(), array(), $theme_version);
    wp_enqueue_style('preomar-main', get_template_directory_uri() . '/assets/css/main.css', array('preomar-style'), $theme_version);
    wp_enqueue_style('preomar-product-fix', get_template_directory_uri() . '/assets/css/product-fix.css', array('preomar-main'), $theme_version);
    // Minimalny fix siatki produktów – ładowany na stronach sklepu/WooCommerce
    if (class_exists('WooCommerce') && (is_woocommerce() || is_shop() || is_product_category() || is_product_tag())) {
        wp_enqueue_style('preomar-product-fix', get_template_directory_uri() . '/assets/css/product-fix.css', array('preomar-main'), $theme_version);
    // Skrypt filtrów (cena + kategorie)
    wp_enqueue_script('preomar-filters', get_template_directory_uri() . '/assets/js/filters.js', array(), $theme_version, true);
    }
    wp_enqueue_script('preomar-js', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), $theme_version, true);
    // Hero slider – tylko na stronie głównej
    if (is_front_page()) {
        wp_enqueue_script('preomar-hero-slider', get_template_directory_uri() . '/assets/js/hero-slider.js', array(), $theme_version, true);
    }
    if (function_exists('is_product') && is_product()) {
        wp_enqueue_script('preomar-single-product-qty', get_template_directory_uri() . '/assets/js/single-product-qty.js', array(), $theme_version, true);
        // AJAX add-to-cart for single product (prevents full page reload -> avoids maintenance page intercept)
        wp_enqueue_script('preomar-single-add-to-cart-ajax', get_template_directory_uri() . '/assets/js/single-add-to-cart-ajax.js', array('jquery','wc-add-to-cart'), $theme_version, true);
    }
    // Subtelne wycentrowanie checkoutu bez zmiany układu
    if (function_exists('is_checkout') && is_checkout()) {
        wp_enqueue_style('preomar-checkout-center', get_template_directory_uri() . '/assets/css/checkout-center.css', array('preomar-main'), $theme_version);
    }
    // Wymuś skrypt Woo do AJAX add-to-cart na stronie "Moje zakupy"
    if (function_exists('is_page') && is_page('moje-zakupy')) {
        wp_enqueue_script('wc-add-to-cart');
    }
    // Sell page stylesheet (Sprzedawaj)
    if (is_page_template('page-sprzedawaj.php') || is_page('sprzedawaj')) {
        wp_enqueue_style('preomar-sell', get_template_directory_uri() . '/assets/css/sell.css', array('preomar-product-fix'), $theme_version);
    }
    
    // NATYCHMIASTOWA NAPRAWA LOGOWANIA z AJAX - ładuj wszędzie - WERSJA WORKING
    // ŁADUJ skrypt naprawy logowania TYLKO na stronach konta (wcześniej ładował się wszędzie i
    // błędnie zamieniał strony produktów na ekran logowania, bo wykrywał klasę .woocommerce)
    if (class_exists('WooCommerce') && (
        (function_exists('is_account_page') && is_account_page()) ||
        (isset($_SERVER['REQUEST_URI']) && (
            strpos($_SERVER['REQUEST_URI'], 'my-account') !== false ||
            strpos($_SERVER['REQUEST_URI'], 'moje-konto') !== false ||
            strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false ||
            (isset($_GET['action']) && in_array($_GET['action'], ['register','lostpassword']))
        ))
    )) {
        wp_enqueue_script('preomar-login-fix', get_template_directory_uri() . '/assets/js/login-fix-working.js', array('jquery'), $theme_version . '.working', true);
    }

    // Przekazanie danych AJAX (wishlist / obserwowane)
    wp_localize_script('preomar-js', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'wishlist_nonce' => wp_create_nonce('wishlist_nonce')
    ));
    
    // Przekaż poprawne URL-e do JavaScriptu
    wp_localize_script('preomar-login-fix', 'preomar_login_vars', array(
        'site_url' => site_url(),
        'home_url' => home_url(),
        'lostpassword_url' => wp_lostpassword_url(),
        'registration_url' => wp_registration_url(),
        'login_url' => wp_login_url(),
        'account_url' => class_exists('WooCommerce') ? wc_get_page_permalink('myaccount') : wp_login_url(),
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('preomar_login_nonce'),
        'registration_enabled' => get_option('users_can_register') ? 'yes' : 'no',
        'woo_registration' => class_exists('WooCommerce') && get_option('woocommerce_enable_myaccount_registration') === 'yes' ? 'yes' : 'no'
    ));    // Enhanced login styles and scripts - load on all WooCommerce pages
    if (class_exists('WooCommerce') && (is_woocommerce() || is_cart() || is_checkout() || is_account_page() || 
        strpos($_SERVER['REQUEST_URI'], 'my-account') !== false || 
        strpos($_SERVER['REQUEST_URI'], 'moje-konto') !== false)) {
        wp_enqueue_style('preomar-login', get_template_directory_uri() . '/login-styles.css', array('preomar-style'), $theme_version);
        // wp_enqueue_script('preomar-login-js', get_template_directory_uri() . '/assets/js/login-enhancements.js', array('jquery'), $theme_version, true);
    }
    
    // Dodaj skrypt do ładowania kategorii tylko na stronie głównej
    if (is_front_page()) {
        wp_enqueue_script('preomar-categories', get_template_directory_uri() . '/assets/js/categories-loader.js', array(), $theme_version, true);
    }
    // Styl dla strony Kontakt (tylko na /kontakt/)
    if (is_page('kontakt')) {
        wp_enqueue_style('preomar-contact', get_template_directory_uri() . '/assets/css/contact.css', array('preomar-main'), $theme_version);
    }
}
add_action('wp_enqueue_scripts', 'preomar_scripts');

// Do not redirect to cart after add-to-cart (stay on product page)
add_filter('woocommerce_add_to_cart_redirect', function($url){ return false; });
add_filter('woocommerce_cart_redirect_after_add', '__return_false');
// Also prevent redirect on error (e.g., quantity/stock errors)
add_filter('woocommerce_cart_redirect_after_error', '__return_false');

// Whitelist WooCommerce AJAX endpoints from any maintenance/coming-soon interception
add_action('init', function(){
    if (isset($_GET['wc-ajax'])) {
        // Avoid auth_redirect or similar
        remove_action('template_redirect', 'auth_redirect');
        // Strip typical maintenance callbacks early
        add_action('template_redirect', function(){
            global $wp_filter;
            if (empty($wp_filter['template_redirect'])) return;
            $patterns = [ 'maint', 'coming', 'soon', 'under', 'construction', 'seedprod', 'cmp', 'comingsoon' ];
            foreach ($wp_filter['template_redirect']->callbacks as $prio => $callbacks) {
                foreach ($callbacks as $id => $cb) {
                    $fn = $cb['function']; $name='';
                    if (is_string($fn)) { $name = $fn; }
                    elseif (is_array($fn)) { $name = (is_object($fn[0])? get_class($fn[0]) : (string)$fn[0]) . '::' . (string)$fn[1]; }
                    elseif ($fn instanceof Closure) { $name = 'Closure@template_redirect'; }
                    $low = strtolower($name);
                    foreach($patterns as $p){ if ($low !== '' && strpos($low, $p) !== false) { remove_action('template_redirect', $cb['function'], $prio); break; } }
                }
            }
        }, -10000);
    }
}, 0);

// Widgets
function preomar_widgets_init() {
    register_sidebar(array(
        'name' => __('Sidebar główny', 'preomar'),
        'id' => 'main-sidebar',
        'description' => __('Sidebar główny', 'preomar'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));
}
add_action('widgets_init', 'preomar_widgets_init');

// AJAX Cart
function preomar_add_to_cart_fragment($fragments) {
    $fragments['span.cart-count'] = '<span class="cart-count">' . WC()->cart->get_cart_contents_count() . '</span>';
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'preomar_add_to_cart_fragment');

// Custom post type for promotions
function preomar_promotions_post_type() {
    register_post_type('promotions', array(
        'labels' => array(
            'name' => 'Promocje',
            'singular_name' => 'Promocja',
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor', 'thumbnail'),
    ));
}
add_action('init', 'preomar_promotions_post_type');

/* CLEANUP LOGIN OVERRIDES
   Usunięto wcześniejsze agresywne hacki blokujące / nadpisujące auth_redirect
   bo powodowały one sztuczne "wylogowanie" (determine_current_user => 0)
   na stronach produktów i pętle przekierowań do logowania.
   Domyślne zachowanie WooCommerce: produkty są publiczne – nic nie trzeba robić.
*/

// Minimalny bezpieczny filtr: jeśli jakieś zewnętrzne rozszerzenie ukrywa produkty
// przez auth_redirect, zatrzymaj tylko to konkretne przekierowanie dla pojedynczego produktu.
function preomar_allow_public_products_minimal() {
    if (function_exists('is_singular') && is_singular('product')) {
        // Jeśli nagłówki jeszcze nie wysłane i użytkownik jest widziany jako zalogowany
        // nic nie rób. Jeśli jest zalogowany a mimo to redirect nastąpiłby przez plugin,
        // usuń tylko ten hook.
        remove_action('template_redirect', 'auth_redirect');
    }
}
add_action('template_redirect', 'preomar_allow_public_products_minimal', 5);

// ================= DEBUG WYMUSZONYCH PRZEKIEROWAŃ DO LOGOWANIA =================
// Włącz przez ustawienie stałej PREOMAR_DEBUG_LOGIN na true (tu domyślnie włączone – wyłącz po diagnozie)
if (!defined('PREOMAR_DEBUG_LOGIN')) {
    define('PREOMAR_DEBUG_LOGIN', true);
}

if (PREOMAR_DEBUG_LOGIN) {
    // Helper: aktualny URL
    function preomar_current_url_debug() {
        $scheme = (is_ssl() ? 'https://' : 'http://');
        $host   = $_SERVER['HTTP_HOST'] ?? '';
        $uri    = $_SERVER['REQUEST_URI'] ?? '';
        return $scheme . $host . $uri;
    }

    // Loguj każdą próbę przekierowania na wp-login.php (lub moje-konto / my-account)
    add_filter('wp_redirect', function($location, $status) {
        if (strpos($location, 'wp-login.php') !== false || strpos($location, 'moje-konto') !== false || strpos($location, 'my-account') !== false) {
            $user_id = get_current_user_id();
            $msg = "[PREOMAR DEBUG LOGIN REDIRECT] user=" . ($user_id ? $user_id : '0') .
                " from=" . preomar_current_url_debug() . " -> " . $location . " status=" . $status;
            error_log($msg);

            // Spróbuj ustalić funkcję wywołującą (stack skrócony)
            if (function_exists('debug_backtrace')) {
                $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);
                $stack_lines = [];
                foreach ($bt as $frame) {
                    if (!empty($frame['function'])) {
                        $file = isset($frame['file']) ? basename($frame['file']) : '';
                        $line = isset($frame['line']) ? $frame['line'] : '';
                        $stack_lines[] = $frame['function'] . '(' . $file . ':' . $line . ')';
                    }
                }
                if ($stack_lines) {
                    error_log('[PREOMAR DEBUG STACK] ' . implode(' <- ', $stack_lines));
                }
            }
        }
        return $location;
    }, 1, 2);

    // Spróbuj zidentyfikować podejrzane hooki na init, które mogą wywołać auth_redirect()
    add_action('init', function() {
        global $wp_filter;
        if (!empty($wp_filter['init'])) {
            $suspects = [];
            foreach ($wp_filter['init']->callbacks as $prio => $callbacks) {
                foreach ($callbacks as $id => $cb) {
                    $fn = $cb['function'];
                    $name = '';
                    if (is_string($fn)) {
                        $name = $fn;
                    } elseif (is_array($fn)) {
                        if (is_object($fn[0])) {
                            $name = get_class($fn[0]) . '->' . $fn[1];
                        } else {
                            $name = $fn[0] . '::' . $fn[1];
                        }
                    } elseif ($fn instanceof Closure) {
                        $name = 'Closure@init';
                    }
                    if ($name && (stripos($name, 'force') !== false || stripos($name, 'login') !== false)) {
                        $suspects[] = "prio=$prio name=$name";
                    }
                }
            }
            if ($suspects) {
                error_log('[PREOMAR DEBUG INIT HOOKS SUSPECT] ' . implode(' | ', $suspects));
            }
        }
    }, 0); // priority 0 – przed większością innych
}
// ===============================================================================

// DODATKOWY DEBUG STRONY PRODUKTU – sprawdza czy faktycznie ładuje się pojedynczy produkt
if (!function_exists('preomar_debug_product_page')) {
    add_action('template_redirect', function() {
        if (is_singular('product')) {
            global $post;
            // Log w error_log
            if ($post) {
                error_log('[PREOMAR PRODUCT DEBUG] id=' . $post->ID . ' status=' . $post->post_status . ' type=' . $post->post_type . ' URL=' . (isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'') );
            } else {
                error_log('[PREOMAR PRODUCT DEBUG] BRAK $post dla strony produktu');
            }
            // Wstaw komentarz do HTML aby zobaczyć w źródle
            add_action('wp_footer', function() use ($post) {
                if ($post) {
                    $template = function_exists('get_single_template') ? basename(get_single_template()) : 'n/a';
                    echo "<!-- PRODUCT DEBUG id={$post->ID} status={$post->post_status} template={$template} -->"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                }
            });
        }
    }, 1);
}

    // BEZPIECZNY BYPASS dla "coming soon"/maintenance lub przymusowych ekranów logowania na stronie pojedynczego produktu
    // Cel: gdy goście lub nieadmini widzą zamiast produktu ekran "wkrótce" (z wtyczki), wymuś render prawdziwego szablonu produktu.
    // Działa bardzo wcześnie w template_redirect, ale tylko dla zwykłych wejść GET (nie przeszkadza w add-to-cart itp.).
    // (Wyłączono wcześniejszy bypass maintenance dla single product – wracamy do standardowego renderowania)

    // (Nie usuwamy hooków maintenance – pełny powrót do wcześniejszej logiki)

    // Wymuś prawidłowy szablon single-product nawet jeśli wtyczka maintenance próbuje go podmienić
    add_filter('template_include', function($template){
        if (function_exists('is_singular') && is_singular('product')) {
            $pref = locate_template(['woocommerce/single-product.php','single-product.php']);
            if ($pref) {
                // Debug marker do HTML (podgląd w źródle)
                add_action('wp_footer', function() use ($pref){ echo '<!-- PREOMAR FORCE_SINGLE_PRODUCT tpl='.esc_html(basename($pref)).' -->'; });
                return $pref;
            }
        }
        return $template;
    }, 1000);

    // HARDEN: Wymuś widoczność stron sklepu mimo trybu Maintenance/Coming-Soon (dla użytkowników nie-admin)
    add_action('template_redirect', function(){
        // Krytyczne strony sklepu
        $is_shop_critical = (function_exists('is_cart') && is_cart())
            || (function_exists('is_checkout') && is_checkout())
            || (function_exists('is_singular') && is_singular('product'))
            || (function_exists('is_shop') && is_shop())
            || (function_exists('is_product_category') && is_product_category())
            || (function_exists('is_product_tag') && is_product_tag());
        if (!$is_shop_critical) return;

        // Usuń podejrzane callbacki maintenance/coming-soon z template_redirect
        global $wp_filter;
        if (!empty($wp_filter['template_redirect'])) {
            $patterns = [ 'maint', 'coming', 'soon', 'under', 'construction', 'seedprod', 'cmp', 'comingsoon' ];
            foreach ($wp_filter['template_redirect']->callbacks as $prio => $callbacks) {
                foreach ($callbacks as $id => $cb) {
                    $fn = $cb['function']; $name='';
                    if (is_string($fn)) { $name = $fn; }
                    elseif (is_array($fn)) { $name = (is_object($fn[0])? get_class($fn[0]) : (string)$fn[0]) . '::' . (string)$fn[1]; }
                    elseif ($fn instanceof Closure) { $name = 'Closure@template_redirect'; }
                    $low = strtolower($name);
                    foreach($patterns as $p){
                        if ($low !== '' && strpos($low, $p) !== false) {
                            remove_action('template_redirect', $cb['function'], $prio);
                            break;
                        }
                    }
                }
            }
        }
    }, -10000);

    // ALSO enforce cart template include very late (in case a plugin still tries to swap it)
    add_filter('template_include', function($template){
        if ((function_exists('is_cart') && is_cart()) || (is_page('koszyk'))) {
            $tpl = get_template_directory() . '/page-koszyk.php';
            if (file_exists($tpl)) {
                add_action('wp_footer', function(){ echo '<!-- PREOMAR FORCE_CART_TEMPLATE -->'; });
                return $tpl;
            }
        }
        return $template;
    }, 99999);

    // ULTRA-EARLY BYPASS: render cart page for logged-in users even if maintenance tries to hijack
    add_action('template_redirect', function(){
        // Only for logged-in non-admins and regular users; admins already see everything
        if (!is_user_logged_in()) return;
        $is_cart_req = (function_exists('is_cart') && is_cart()) || is_page('koszyk');
        if (!$is_cart_req && isset($_SERVER['REQUEST_URI'])) {
            $path = $_SERVER['REQUEST_URI'];
            // Fallback detection by path (handles XAMPP /wordpress/ prefix too)
            if (stripos($path, '/koszyk') !== false) { $is_cart_req = true; }
        }
        if ($is_cart_req) {
            // Avoid auth_redirect edge cases
            remove_action('template_redirect', 'auth_redirect');
            $tpl = get_template_directory() . '/page-koszyk.php';
            if (file_exists($tpl)) {
                status_header(200);
                nocache_headers();
                // Mark in HTML for debug
                add_action('wp_head', function(){ echo '<!-- PREOMAR CART_EARLY_BYPASS -->'; });
                include $tpl;
                exit;
            }
        }
    }, -20000);
function preomar_woocommerce_customization() {
    // Remove default WooCommerce wrappers
    remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
    remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
    
    // Add custom wrappers
    add_action('woocommerce_before_main_content', 'preomar_wrapper_start', 10);
    add_action('woocommerce_after_main_content', 'preomar_wrapper_end', 10);
    
    // Change products per page
    add_filter('loop_shop_per_page', 'preomar_products_per_page', 20);
    
    // Dodaj custom fields do produktów
    add_action('woocommerce_product_options_general_product_data', 'preomar_add_custom_fields');
    add_action('woocommerce_process_product_meta', 'preomar_save_custom_fields');
    
    // Dodaj stan produktu (nowy/używany)
    add_action('woocommerce_single_product_summary', 'preomar_display_product_condition', 25);
        // Dodaj przycisk obserwowania produktu w sekcji pod tytułem (priorytet 29)
        add_action('woocommerce_single_product_summary', 'preomar_follow_button_single', 29);
    
    // Customize cart fragments
    add_filter('woocommerce_add_to_cart_fragments', 'preomar_cart_fragments');

    // Usuń licznik wyników i domyślne sortowanie (select "Domyślne sortowanie")
    remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
}
add_action('init', 'preomar_woocommerce_customization');

// Usuń domyślne cart_totals i cross-sells z wewnątrz [woocommerce_cart], zostawiamy własny panel (.summary-card)
add_action('wp', function(){
    if (function_exists('is_cart') && is_cart()) {
        remove_action('woocommerce_cart_collaterals', 'woocommerce_cart_totals', 10);
        remove_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display');
    }
}, 20);

// Uprawnienia: pozwól klientom zgłaszać produkty do moderacji (pending)
add_action('init', function(){
    foreach (['customer','subscriber'] as $role_name) {
        $role = get_role($role_name);
        if ($role) {
            foreach (['upload_files','create_products','edit_products','delete_products'] as $cap) {
                if (!$role->has_cap($cap)) {
                    $role->add_cap($cap);
                }
            }
        }
    }
}, 11);

// Globalnie ukryj produkty wyprzedane z katalogu (zachowaj strony pojedyncze)
add_action('init', function(){
    if (get_option('woocommerce_hide_out_of_stock_items') !== 'yes') {
        update_option('woocommerce_hide_out_of_stock_items', 'yes');
    }
});

// Dodatkowa asekuracja: wyklucz outofstock w głównej pętli katalogu (shop, kategorie, tagi, wyszukiwarka Woo)
add_action('pre_get_posts', function($q){
    if (is_admin() || !$q->is_main_query()) return;
    // Nie dotykaj stron pojedynczych produktów ani stron statycznych
    if ($q->is_singular) return;

    $is_catalog = false;
    if (function_exists('is_woocommerce') && is_woocommerce()) $is_catalog = true;
    if (function_exists('is_shop') && is_shop()) $is_catalog = true;
    if (function_exists('is_product_category') && is_product_category()) $is_catalog = true;
    if (function_exists('is_product_tag') && is_product_tag()) $is_catalog = true;
    if ($q->get('post_type') === 'product') $is_catalog = true;

    if (!$is_catalog) return;

    $meta_query = (array) $q->get('meta_query');
    $meta_query[] = [
        'key' => '_stock_status',
        'value' => 'outofstock',
        'compare' => '!=',
    ];
    $q->set('meta_query', $meta_query);
});

// Wrapper start
function preomar_wrapper_start() {
    // Bezpieczna naprawa treści tylko dla strony "Regulamin"
    if (function_exists('is_page') && is_page()) {
        $p = get_post();
        if ($p instanceof WP_Post) {
            $slug = isset($p->post_name) ? $p->post_name : '';
            if ($slug === 'regulamin') {
                $raw = isset($p->post_content) ? (string) $p->post_content : '';
                $txt = strtolower(wp_strip_all_tags($raw));
                if ($txt === '' || strpos($txt, 'wielkie rzeczy') !== false || strpos($txt, 'coming soon') !== false || strpos($txt, 'wkrótce uruchomimy') !== false) {
                    wp_update_post([
                        'ID' => $p->ID,
                        'post_content' => '<h2>Regulamin sklepu</h2><p>(Treść w przygotowaniu. Uzupełnij właściwą treść prawną: definicje, zakres usług, zamówienia, płatności, dostawa, odstąpienie, reklamacje, dane kontaktowe przedsiębiorcy.)</p>'
                    ]);
                }
            }
        }
    }
    echo '<div class="shop-container"><main class="shop-content">';
}

// Wrapper end
function preomar_wrapper_end() {
    echo '</main></div>';
}

// Produkty na stronie
function preomar_products_per_page($cols) {
    return 12;
}

// Custom fields dla produktów
function preomar_add_custom_fields() {
    global $woocommerce, $post;
    
    echo '<div class="product_custom_field">';
    
    // Stan produktu
    woocommerce_wp_select(
        array(
            'id' => '_product_condition',
            'label' => __('Stan produktu', 'preomar'),
            'placeholder' => 'Wybierz stan',
            'desc_tip' => 'true',
            'description' => __('Wybierz stan produktu', 'preomar'),
            'options' => array(
                'new' => __('Nowy', 'preomar'),
                'used' => __('Używany', 'preomar'),
                'refurbished' => __('Odnowiony', 'preomar'),
                'damaged' => __('Uszkodzony', 'preomar'),
            )
        )
    );
    
    // Lokalizacja sprzedawcy
    woocommerce_wp_text_input(
        array(
            'id' => '_seller_location',
            'label' => __('Lokalizacja sprzedawcy', 'preomar'),
            'placeholder' => 'np. Warszawa, Kraków',
            'desc_tip' => 'true',
            'description' => __('Podaj lokalizację sprzedawcy', 'preomar'),
        )
    );
    
    // Czy można negocjować cenę
    woocommerce_wp_checkbox(
        array(
            'id' => '_price_negotiable',
            'label' => __('Cena do negocjacji', 'preomar'),
            'description' => __('Zaznacz jeśli cena jest do negocjacji', 'preomar'),
        )
    );
    
    echo '</div>';
}

// Zapisz custom fields
function preomar_save_custom_fields($post_id) {
    // Stan produktu
    $product_condition = $_POST['_product_condition'];
    if (!empty($product_condition)) {
        update_post_meta($post_id, '_product_condition', esc_attr($product_condition));
    }
    
    // Lokalizacja sprzedawcy
    $seller_location = $_POST['_seller_location'];
    if (!empty($seller_location)) {
        update_post_meta($post_id, '_seller_location', esc_attr($seller_location));
    }
    
    // Cena do negocjacji
    $price_negotiable = isset($_POST['_price_negotiable']) ? 'yes' : 'no';
    update_post_meta($post_id, '_price_negotiable', $price_negotiable);
}

// Wyświetl stan produktu
function preomar_display_product_condition() {
    global $post;
    
    $product_condition = get_post_meta($post->ID, '_product_condition', true);
    $seller_location = get_post_meta($post->ID, '_seller_location', true);
    $price_negotiable = get_post_meta($post->ID, '_price_negotiable', true);
    
    // Usunięto wyświetlanie wiersza "Stan:" – pozostawiono tylko lokalizację i informację o negocjacji ceny
    if ($seller_location || $price_negotiable == 'yes') {
        echo '<div class="product-additional-info">';
        if ($seller_location) {
            echo '<div class="seller-location"><strong>Lokalizacja:</strong> ' . esc_html($seller_location) . '</div>';
        }
        if ($price_negotiable == 'yes') {
            echo '<div class="price-negotiable"><strong>Cena do negocjacji</strong></div>';
        }
        echo '</div>';
    }
}

// Cart fragments
function preomar_cart_fragments($fragments) {
    $fragments['span.cart-count'] = '<span class="cart-count">' . WC()->cart->get_cart_contents_count() . '</span>';
    return $fragments;
}

// Wishlist functionality
function preomar_wishlist_init() {
    // Obsługa dodawania do wishlist
    add_action('wp_ajax_add_to_wishlist', 'preomar_add_to_wishlist');
    add_action('wp_ajax_nopriv_add_to_wishlist', 'preomar_add_to_wishlist');
    
    // Obsługa usuwania z wishlist
    add_action('wp_ajax_remove_from_wishlist', 'preomar_remove_from_wishlist');
    add_action('wp_ajax_nopriv_remove_from_wishlist', 'preomar_remove_from_wishlist');
}
add_action('init', 'preomar_wishlist_init');

// Body class: add 'shop-empty' when on shop archive and no products
add_filter('body_class', function($classes){
    if( (is_post_type_archive('product') || is_shop() || is_product_category() || is_product_tag()) && function_exists('wc_get_loop_prop')){
        global $wp_query; 
        if(isset($wp_query) && (int)$wp_query->found_posts === 0){
            $classes[] = 'shop-empty';
        }
    }
    return $classes;
});

// Shortcode i strona "Obserwowane" (wykorzystuje istniejącą meta 'wishlist')
add_action('init', function() {
    // Rejestruj shortcode
    add_shortcode('preomar_wishlist', 'preomar_render_wishlist_page');
    // Automatycznie utwórz stronę jeśli nie istnieje
    if (!get_page_by_path('obserwowane')) {
        $pid = wp_insert_post([
            'post_title' => 'Obserwowane',
            'post_name' => 'obserwowane',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_content' => '[preomar_wishlist]'
        ]);
    }
});

function preomar_render_wishlist_page() {
    if (!is_user_logged_in()) {
        return '<div class="wishlist-login-prompt"><p>Musisz być zalogowany aby zobaczyć obserwowane produkty.</p><a class="login-btn" href="' . esc_url( home_url('/moje-konto/') ) . '">Zaloguj się</a></div>';
    }
    $wishlist = get_user_meta(get_current_user_id(), 'wishlist', true);
    if (empty($wishlist) || !is_array($wishlist)) {
        return '<div class="wishlist-empty"><h2>Brak obserwowanych produktów</h2><p>Dodaj produkty klikając przycisk "Obserwuj" na stronie produktu.</p></div>';
    }
    $args = [
        'post_type' => 'product',
        'post__in' => array_map('intval', $wishlist),
        'posts_per_page' => -1
    ];
    $q = new WP_Query($args);
    ob_start();
    echo '<div class="wishlist-container">';
    // Usunięto nagłówek "Obserwowane" na życzenie użytkownika – pozostaje sam grid
    if ($q->have_posts()) {
    echo '<div class="products-grid wishlist-grid enhanced-wishlist">';
        while ($q->have_posts()) { $q->the_post(); global $product; ?>
        <div class="wishlist-item card" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
            <div class="wi-thumb-wrap">
                <a class="wi-thumb" href="<?php the_permalink(); ?>">
                    <?php if (has_post_thumbnail()) { 
                        the_post_thumbnail('preomar_related', ['class' => 'product-image']); 
                    } else { 
                        echo '<img src="' . esc_url( get_template_directory_uri() . '/assets/images/default-product.svg' ) . '" class="product-image placeholder" alt="Brak zdjęcia" />'; 
                    } ?>
                </a>
            </div>
            <div class="wi-info">
                <h3 class="wi-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                <div class="wi-price product-price"><?php echo $product->get_price_html(); ?></div>
                <button class="remove-from-wishlist-btn" data-product-id="<?php echo esc_attr($product->get_id()); ?>" aria-label="Usuń z obserwowanych">Usuń</button>
            </div>
        </div>
        <?php }
        echo '</div>';
        wp_reset_postdata();
    }
    echo '</div>';
    return ob_get_clean();
}

// Przyciski obserwowania (wishlist) – single product
function preomar_follow_button_single() {
    if (!is_singular('product')) return;

// Ukryj tytuł strony "Obserwowane" (frontend) bez wpływu na administrację
add_filter('the_title','preomar_hide_obserwowane_title',10,2);
function preomar_hide_obserwowane_title($title,$post_id){
    if(is_admin()) return $title;
    // Działa tylko w głównej pętli dla strony obserwowane
    if(in_the_loop() && get_post_field('post_name',$post_id)==='obserwowane' && is_page($post_id)){
        return '';
    }
    return $title;
}
    global $product;
    if (!$product) return;

    // Gość: pokaż link do logowania zamiast przycisku obserwowania (bez ikonek)
    if (!is_user_logged_in()) {
        $login_url = class_exists('WooCommerce') ? wc_get_page_permalink('myaccount') : wp_login_url();
        echo '<a class="follow-product-btn login-required" href="' . esc_url($login_url) . '"><span class="text">Zaloguj się, aby obserwować</span></a>';
        return;
    }
    $wishlist = is_user_logged_in() ? get_user_meta(get_current_user_id(), 'wishlist', true) : [];
    $in = is_array($wishlist) && in_array($product->get_id(), $wishlist);
    $label = $in ? 'Obserwujesz' : 'Obserwuj';
    echo '<button class="follow-product-btn ' . ($in? 'is-following':'') . '" data-product-id="' . esc_attr($product->get_id()) . '">'
        . '<span class="text">' . esc_html($label) . '</span></button>';
}

// Usuń zakładkę opinie (reviews)
add_filter('woocommerce_product_tabs', function($tabs) { unset($tabs['reviews']); return $tabs; }, 98);

// === PLAIN OPIS PRODUKTU (bez zakładek) ===
// Usuń całkowicie standardowe zakładki WooCommerce (opis / dodatkowe informacje / opinie)
add_action('init', function(){
    // standardowy hook dodawany w wc-template-hooks.php
    remove_action('woocommerce_after_single_product_summary','woocommerce_output_product_data_tabs',10);
});

// Wypisz sam opis w prostym kontenerze (po galerii i podsumowaniu – tam gdzie były zakładki)
function preomar_single_product_plain_description(){
    if(!is_singular('product')) return;
    global $post;
    if(!$post) return;
    // Pobierz pełną treść produktu
    $content = apply_filters('the_content', $post->post_content);
    if(!trim(strip_tags($content))) return; // brak opisu – nic nie pokazuj
    echo '<div class="single-product-description-card">'
        . '<div class="spd-header">Opis</div>'
        . '<div class="spd-body">' . $content . '</div>'
        . '</div>';
}
add_action('woocommerce_after_single_product_summary','preomar_single_product_plain_description',10);

// AJAX toggle follow (łączymy add/remove w jedno)
add_action('wp_ajax_preomar_toggle_follow', 'preomar_toggle_follow');
add_action('wp_ajax_nopriv_preomar_toggle_follow', 'preomar_toggle_follow');
function preomar_toggle_follow() {
    if (!is_user_logged_in()) wp_send_json_error('Musisz być zalogowany');
    check_ajax_referer('wishlist_nonce','nonce');
    $product_id = intval($_POST['product_id'] ?? 0);
    if (!$product_id) wp_send_json_error('Brak ID');
    $wishlist = get_user_meta(get_current_user_id(), 'wishlist', true);
    if (!is_array($wishlist)) $wishlist = [];
    $index = array_search($product_id, $wishlist);
    if ($index === false) { $wishlist[] = $product_id; $state='added'; }
    else { unset($wishlist[$index]); $wishlist = array_values($wishlist); $state='removed'; }
    update_user_meta(get_current_user_id(), 'wishlist', $wishlist);
    wp_send_json_success(['state'=>$state,'count'=>count($wishlist)]);
}

// Dodaj do wishlist
function preomar_add_to_wishlist() {
    if (!wp_verify_nonce($_POST['nonce'], 'wishlist_nonce')) {
        wp_die('Błąd bezpieczeństwa');
    }
    
    $product_id = intval($_POST['product_id']);
    $wishlist = get_user_meta(get_current_user_id(), 'wishlist', true);
    
    if (!is_array($wishlist)) {
        $wishlist = array();
    }
    
    if (!in_array($product_id, $wishlist)) {
        $wishlist[] = $product_id;
        update_user_meta(get_current_user_id(), 'wishlist', $wishlist);
        wp_send_json_success(array(
            'message' => 'Dodano do obserwowanych',
            'count'   => count($wishlist)
        ));
    } else {
        wp_send_json_error(array(
            'message' => 'Produkt już obserwujesz',
            'count'   => count($wishlist)
        ));
    }
}

// Usuń z wishlist
function preomar_remove_from_wishlist() {
    if (!wp_verify_nonce($_POST['nonce'], 'wishlist_nonce')) {
        wp_die('Błąd bezpieczeństwa');
    }
    
    $product_id = intval($_POST['product_id']);
    $wishlist = get_user_meta(get_current_user_id(), 'wishlist', true);
    
    if (is_array($wishlist)) {
        $key = array_search($product_id, $wishlist);
        if ($key !== false) {
            unset($wishlist[$key]);
            update_user_meta(get_current_user_id(), 'wishlist', $wishlist);
            wp_send_json_success(array(
                'message' => 'Usunięto z obserwowanych',
                'count'   => count($wishlist)
            ));
        }
    }
    
    wp_send_json_error(array(
        'message' => 'Nie można usunąć produktu',
        'count'   => is_array($wishlist) ? count($wishlist) : 0
    ));
}

// Dodaj przycisk wishlist
function preomar_add_wishlist_button() {
    global $product;
    // Tylko dla zalogowanych – gościom nie pokazuj przycisku serca na listach
    if (!is_user_logged_in()) return;

    $wishlist = get_user_meta(get_current_user_id(), 'wishlist', true);
    $is_in_wishlist = is_array($wishlist) && in_array($product->get_id(), $wishlist);

    echo '<button class="wishlist-btn ' . ($is_in_wishlist ? 'in-wishlist' : '') . '" data-product-id="' . $product->get_id() . '" title="' . ($is_in_wishlist ? 'Usuń z listy życzeń' : 'Dodaj do listy życzeń') . '">❤️</button>';
}
add_action('woocommerce_after_shop_loop_item', 'preomar_add_wishlist_button', 15);

// === BLOKADA KOSZYKA I OBSERWOWANIA DLA NIEZALOGOWANYCH ===
// 1) Serwerowo: zablokuj dodanie do koszyka dla gości
add_filter('woocommerce_add_to_cart_validation', function($passed, $product_id, $quantity){
    if (!is_user_logged_in()) {
        if (function_exists('wc_add_notice')) {
            wc_add_notice(__('Zaloguj się, aby dodać produkt do koszyka.', 'preomar'), 'notice');
        }
        return false;
    }
    return $passed;
}, 10, 3);

// 2) Ukryj / podmień przyciski "Dodaj do koszyka" w listach dla gości
add_filter('woocommerce_loop_add_to_cart_link', function($html, $product){
    if (!is_user_logged_in()) {
        $login_url = class_exists('WooCommerce') ? wc_get_page_permalink('myaccount') : wp_login_url();
        return '<a class="button login-required" href="' . esc_url($login_url) . '">' . esc_html__('Zaloguj się, aby kupić', 'preomar') . '</a>';
    }
    return $html;
}, 10, 2);

// 3) Na stronie produktu: usuń formularz koszyka i pokaż CTA logowania dla gości
add_action('wp', function(){
    if (function_exists('is_product') && is_product() && !is_user_logged_in()) {
        // Usuń standardowy przycisk dodania do koszyka (i formularz z ilością)
        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
        // Wstaw przycisk logowania w tym samym miejscu
        add_action('woocommerce_single_product_summary', function(){
            $login_url = class_exists('WooCommerce') ? wc_get_page_permalink('myaccount') : wp_login_url();
            echo '<a class="button single_add_to_cart_button login-required" href="' . esc_url($login_url) . '">' . esc_html__('Zaloguj się, aby dodać do koszyka', 'preomar') . '</a>';
        }, 30);
    }
});

// Dodaj breadcrumbs
function preomar_breadcrumbs() {
    // Nie pokazuj na stronie koszyka
    if (function_exists('is_cart') && is_cart()) return;
    if (function_exists('woocommerce_breadcrumb')) {
        woocommerce_breadcrumb(array(
            'delimiter' => ' > ',
            'wrap_before' => '<nav class="breadcrumb">',
            'wrap_after' => '</nav>',
            'before' => '',
            'after' => '',
            'home' => _x('Strona główna', 'breadcrumb', 'preomar'),
        ));
    }
}

// Ukryj breadcrumbs na stronie pojedynczego produktu
add_action('wp', function(){
    if (function_exists('is_product') && is_product()) {
        // Domyślne okruszki WooCommerce
        remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
        // Na wypadek gdyby nasza funkcja była gdzieś podpięta
        remove_action('woocommerce_before_main_content', 'preomar_breadcrumbs', 20);
    }
}, 5);

// Dodaj obsługę porównywania produktów
function preomar_compare_init() {
    add_action('wp_ajax_add_to_compare', 'preomar_add_to_compare');
    add_action('wp_ajax_nopriv_add_to_compare', 'preomar_add_to_compare');
    
    add_action('wp_ajax_remove_from_compare', 'preomar_remove_from_compare');
    add_action('wp_ajax_nopriv_remove_from_compare', 'preomar_remove_from_compare');
}
add_action('init', 'preomar_compare_init');

// Dodaj do porównania
function preomar_add_to_compare() {
    $product_id = intval($_POST['product_id']);
    $compare_list = get_user_meta(get_current_user_id(), 'compare_list', true);
    
    if (!is_array($compare_list)) {
        $compare_list = array();
    }
    
    if (count($compare_list) >= 4) {
        wp_send_json_error('Możesz porównać maksymalnie 4 produkty');
    }
    
    if (!in_array($product_id, $compare_list)) {
        $compare_list[] = $product_id;
        update_user_meta(get_current_user_id(), 'compare_list', $compare_list);
        wp_send_json_success('Dodano do porównania');
    } else {
        wp_send_json_error('Produkt już jest na liście porównań');
    }
}

// Usuń z porównania
function preomar_remove_from_compare() {
    $product_id = intval($_POST['product_id']);
    $compare_list = get_user_meta(get_current_user_id(), 'compare_list', true);
    
    if (is_array($compare_list)) {
        $key = array_search($product_id, $compare_list);
        if ($key !== false) {
            unset($compare_list[$key]);
            update_user_meta(get_current_user_id(), 'compare_list', $compare_list);
            wp_send_json_success('Usunięto z porównania');
        }
    }
    
    wp_send_json_error('Nie można usunąć produktu z porównania');
}

// Ukryj kategorię "Bez kategorii" z dropdown w header
function preomar_hide_uncategorized_category($terms, $taxonomies, $args) {
    // Pracuj tylko na kategoriach produktów
    if (!in_array('product_cat', (array)$taxonomies, true)) {
        return $terms;
    }

    // Jeśli zwracane są tylko ID (fields=ids / id=>parent itd.) – nie mamy obiektów,
    // więc pomijamy (brak potrzeby filtrowania po slug, a usunięcie ID może zaburzyć mapowanie)
    if (!empty($args['fields']) && !in_array($args['fields'], ['all','all_with_object_id'], true)) {
        return $terms; // nic nie ruszamy przy polach typu 'ids', 'id=>parent', 'names' itd.
    }

    foreach ($terms as $key => $term) {
        // Upewnij się że mamy obiekt termu – jeśli to ID pobierz obiekt
        if (!is_object($term)) {
            $term_obj = get_term($term);
            if (!$term_obj || is_wp_error($term_obj)) {
                continue; // niepoprawny term
            }
        } else {
            $term_obj = $term;
        }

        $slug = isset($term_obj->slug) ? $term_obj->slug : '';
        $name = isset($term_obj->name) ? $term_obj->name : '';
    // Usuń także kategorię "Nieruchomości" oraz jej ewentualne dzieci
    if ($slug === 'uncategorized' || $name === 'Bez kategorii' || $slug === 'nieruchomosci' || $name === 'Nieruchomości') {
            unset($terms[$key]);
        }
    }
    return $terms;
}
add_filter('get_terms', 'preomar_hide_uncategorized_category', 10, 3);

// Custom cart URL handling - Allegro style
function preomar_custom_cart_url() {
    return home_url('/koszyk/');
}

// Rewrite rules for cart page
function preomar_cart_rewrite_rules() {
    add_rewrite_rule('^koszyk/?$', 'index.php?pagename=koszyk', 'top');
}
add_action('init', 'preomar_cart_rewrite_rules');

// Redirect WooCommerce cart to custom URL
function preomar_redirect_cart_to_custom_url() {
    if (class_exists('WooCommerce') && is_cart() && !is_admin()) {
        if ($_SERVER['REQUEST_URI'] !== '/koszyk/' && strpos($_SERVER['REQUEST_URI'], 'page_id=') !== false) {
            wp_redirect(home_url('/koszyk/'), 301);
            exit;
        }
    }
}
add_action('template_redirect', 'preomar_redirect_cart_to_custom_url');

// Filter WooCommerce cart URL
function preomar_woocommerce_get_cart_url($cart_url) {
    return home_url('/koszyk/');
}
add_filter('woocommerce_get_cart_url', 'preomar_woocommerce_get_cart_url');

// Ensure cart page uses our template
function preomar_cart_page_template($template) {
    if (is_page('koszyk')) {
        $new_template = get_template_directory() . '/page-koszyk.php';
        if (file_exists($new_template)) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('page_template', 'preomar_cart_page_template');

// Add custom body class for cart page
function preomar_cart_page_body_class($classes) {
    if (is_page('koszyk')) {
        $classes[] = 'woocommerce-cart';
        $classes[] = 'woocommerce-page';
    }
    return $classes;
}
add_filter('body_class', 'preomar_cart_page_body_class');

// Ominięcie placeholdera na stronie koszyka przed załadowaniem contentu (jeżeli jakaś wtyczka nadpisuje the_content)
add_action('template_redirect', function(){
    // Blokada dostępu do koszyka dla niezalogowanych – szybki redirect
    if ( function_exists('is_page') && ! is_user_logged_in() && ( is_page('koszyk') || is_page('sprzedawaj') || is_page('obserwowane') ) ) {
        // Przekieruj do strony logowania (konto) z parametrem powrotu
        $login = get_permalink( get_option('woocommerce_myaccount_page_id') );
        if ($login) {
            $slug = 'koszyk';
            if ( is_page('sprzedawaj') ) $slug = 'sprzedawaj';
            elseif ( is_page('obserwowane') ) $slug = 'obserwowane';
            wp_safe_redirect( add_query_arg('redirect_to',$slug,$login) );
            exit;
        }
    }
    if ( function_exists('is_page') && is_page('koszyk') ) {
        $cart_id = get_the_ID();
        $raw = get_post_field('post_content', $cart_id);
        $plain = strtolower( trim( wp_strip_all_tags( $raw ) ) );
        $is_placeholder = ($plain === '' || strpos($plain,'wielkie rzeczy') !== false || strpos($plain,'coming soon') !== false || strpos($plain,'wkrótce uruchomimy') !== false);
        if ( $is_placeholder ) {
            // Wymuś natychmiastowy własny minimalny render koszyka
            status_header(200); nocache_headers();
            echo '<!DOCTYPE html><html '; language_attributes(); echo '><head><meta charset="'; bloginfo('charset'); echo '"><meta name="viewport" content="width=device-width,initial-scale=1">';
            wp_head();
            echo '</head><body class="woocommerce-cart woocommerce-page preomar-cart-bypass">';
            // Nagłówek motywu
            if ( function_exists('wp_body_open') ) wp_body_open();
            // Spróbuj wczytać nagłówek szablonu jeśli istnieje
            get_header();
            echo '<main class="site-main preomar-cart-main" style="max-width:1180px;margin:60px auto 80px;padding:0 20px;">';
            echo do_shortcode('[woocommerce_cart]');
            echo '</main>';
            get_footer();
            echo '</body></html>';
            exit;
        }
    }
}, 1); // bardzo wcześnie

// Dodaj przycisk porównywania
function preomar_add_compare_button() {
    global $product;
    
    echo '<button class="compare-btn" data-product-id="' . $product->get_id() . '" title="Dodaj do porównania">⚖️</button>';
}
add_action('woocommerce_after_shop_loop_item', 'preomar_add_compare_button', 20);

// Automatyczne ustawienie strony koszyka przy aktywacji tematu
function preomar_setup_cart_page() {
    // Sprawdź czy strona "koszyk" już istnieje
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
            // Ustaw jako stronę koszyka WooCommerce
            if (class_exists('WooCommerce')) {
                update_option('woocommerce_cart_page_id', $cart_page_id);
            }
            
            // Wymuszenie flush rewrite rules
            flush_rewrite_rules();
            
            // Log sukcesu
            error_log('PreoMarket: Strona koszyka została automatycznie utworzona (ID: ' . $cart_page_id . ')');
        }
    } else {
        // Strona istnieje, upewnij się że ma poprawne ustawienia
        $cart_page_id = $cart_page->ID;
        // Jeśli treść wygląda jak placeholder "Wielkie rzeczy..." albo brak shortcode – napraw
        $raw = (string) $cart_page->post_content;
        $plain = strtolower( trim( wp_strip_all_tags( $raw ) ) );
        $needs_fix = false;
        if ($plain === '' || strpos($plain,'wielkie rzeczy') !== false || strpos($plain,'coming soon') !== false || strpos($plain,'wkrótce uruchomimy') !== false) {
            $needs_fix = true;
        }
        if ( ! has_shortcode( $raw, 'woocommerce_cart' ) ) {
            $needs_fix = true;
        }
        if ( $needs_fix ) {
            wp_update_post([
                'ID' => $cart_page_id,
                'post_content' => '[woocommerce_cart]'
            ]);
        }
        
        // Ustaw jako stronę koszyka WooCommerce jeśli nie jest
        if (class_exists('WooCommerce') && get_option('woocommerce_cart_page_id') != $cart_page_id) {
            update_option('woocommerce_cart_page_id', $cart_page_id);
        }
        
        // Ustaw szablon jeśli nie jest ustawiony
        $current_template = get_post_meta($cart_page_id, '_wp_page_template', true);
        if (!$current_template || $current_template === 'default') {
            update_post_meta($cart_page_id, '_wp_page_template', 'page-koszyk.php');
        }
        
        error_log('PreoMarket: Strona koszyka została skonfigurowana (ID: ' . $cart_page_id . ')');
    }
}

// Automatyczne ustawienie permalinków
function preomar_setup_permalinks() {
    // Sprawdź czy permalinki są ustawione na "plain"
    $permalink_structure = get_option('permalink_structure');
    
    if (empty($permalink_structure)) {
        // Ustaw struktura permalinków na "Post name"
        update_option('permalink_structure', '/%postname%/');
        
        // Wymuszenie flush rewrite rules
        flush_rewrite_rules();
        
        error_log('PreoMarket: Permalinki zostały automatycznie skonfigurowane');
    }
}

// Automatyczne konfigurowanie po aktywacji tematu
function preomar_theme_activation() {
    // Małe opóźnienie żeby WordPress był gotowy
    wp_schedule_single_event(time() + 5, 'preomar_delayed_setup');
}

// Opóźniona konfiguracja
function preomar_delayed_setup() {
    preomar_setup_permalinks();
    preomar_setup_cart_page();
    
    // Dodatkowe flush rewrite rules dla pewności
    flush_rewrite_rules();
    
    error_log('PreoMarket: Automatyczna konfiguracja zakończona');
}

// Hook aktywacji tematu
add_action('after_switch_theme', 'preomar_theme_activation');
add_action('preomar_delayed_setup', 'preomar_delayed_setup');

// Sprawdzenie i konfiguracja przy każdym ładowaniu (jako backup)
function preomar_ensure_cart_setup() {
    // Sprawdzaj tylko w admin area i tylko raz na sesję
    if (is_admin() && !get_transient('preomar_cart_checked')) {
        $cart_page = get_page_by_path('koszyk');
        
        if (!$cart_page) {
            preomar_setup_cart_page();
        } else {
            // Sprawdź czy WooCommerce ma poprawnie ustawioną stronę koszyka
            if (class_exists('WooCommerce') && get_option('woocommerce_cart_page_id') != $cart_page->ID) {
                update_option('woocommerce_cart_page_id', $cart_page->ID);
                flush_rewrite_rules();
            }
        }
        
        // Ustaw transient na 1 godzinę żeby nie sprawdzać za często
        set_transient('preomar_cart_checked', true, HOUR_IN_SECONDS);
    }
}
add_action('admin_init', 'preomar_ensure_cart_setup');

// Admin notice o automatycznej konfiguracji
function preomar_cart_setup_notice() {
    if (isset($_GET['activated']) && $_GET['activated'] == 'true') {
        $cart_page = get_page_by_path('koszyk');
        if ($cart_page) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>PreoMarket:</strong> Strona koszyka została automatycznie skonfigurowana! ';
            echo 'Clean URL <code>/koszyk/</code> jest gotowy do użycia. ';
            echo '<a href="' . home_url('/koszyk/') . '" target="_blank">Sprawdź stronę koszyka</a></p>';
            echo '</div>';
        }
    }
}
add_action('admin_notices', 'preomar_cart_setup_notice');

// Funkcja sprawdzenia czy wszystko jest skonfigurowane
function preomar_is_cart_configured() {
    $cart_page = get_page_by_path('koszyk');
    $permalinks_ok = !empty(get_option('permalink_structure'));
    $woo_cart_ok = class_exists('WooCommerce') ? (get_option('woocommerce_cart_page_id') == ($cart_page ? $cart_page->ID : 0)) : true;
    
    return $cart_page && $permalinks_ok && $woo_cart_ok;
}

// Debug info w footer dla admina
function preomar_cart_debug_info() {
    if (current_user_can('manage_options') && isset($_GET['debug_cart'])) {
        $cart_page = get_page_by_path('koszyk');
        $permalinks = get_option('permalink_structure');
        $woo_cart_id = class_exists('WooCommerce') ? get_option('woocommerce_cart_page_id') : 'N/A';
        
        echo '<!-- PreoMarket Cart Debug -->';
        echo '<div style="position:fixed;bottom:0;left:0;background:#000;color:#fff;padding:10px;font-size:12px;z-index:9999;">';
        echo 'Cart Page: ' . ($cart_page ? 'OK (ID: ' . $cart_page->ID . ')' : 'MISSING') . ' | ';
        echo 'Permalinks: ' . ($permalinks ? 'OK' : 'PLAIN') . ' | ';
        echo 'WooCommerce Cart ID: ' . $woo_cart_id . ' | ';
        echo 'Configuration: ' . (preomar_is_cart_configured() ? 'OK' : 'NEEDS SETUP');
        echo '</div>';
    }
    
    // Debug logowania
    if (current_user_can('manage_options') && isset($_GET['debug_login'])) {
        include get_template_directory() . '/login-debug.php';
    }
}
add_action('wp_footer', 'preomar_cart_debug_info');

// NATYCHMIASTOWA NAPRAWA KOSZYKA - usuń po naprawieniu
add_action('wp_loaded', function() {
    if (get_transient('cart_setup_executed')) return;
    
    // 1. Napraw permalinki
    if (empty(get_option('permalink_structure'))) {
        update_option('permalink_structure', '/%postname%/');
    }
    
    // 2. Utwórz stronę koszyka jeśli nie istnieje
    $cart_page = get_page_by_path('koszyk');
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
        
        // 3. Skonfiguruj WooCommerce
        if ($cart_page_id && class_exists('WooCommerce')) {
            update_option('woocommerce_cart_page_id', $cart_page_id);
        }
    } else {
        // Sprawdź WooCommerce config
        if (class_exists('WooCommerce') && get_option('woocommerce_cart_page_id') != $cart_page->ID) {
            update_option('woocommerce_cart_page_id', $cart_page->ID);
        }
    }
    
    // 4. Przeładuj rewrite rules
    flush_rewrite_rules();
    
    // Oznacz jako wykonane (24h)
    set_transient('cart_setup_executed', true, DAY_IN_SECONDS);
    
    // Debug info
    if (is_admin() && current_user_can('manage_options')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>🎯 Koszyk naprawiony!</strong> ';
            echo 'Sprawdź: <a href="' . home_url('/koszyk/') . '" target="_blank">' . home_url('/koszyk/') . '</a> ';
            echo '- <em>Usuń kod naprawczy z functions.php</em></p>';
            echo '</div>';
        });
    }
}, 1);

// BACKUP SOLUTION: Koszyk URL bez mod_rewrite (dla XAMPP)
add_action('parse_request', function($wp) {
    // Handle /koszyk/ URL even without mod_rewrite
    if (isset($wp->request) && ($wp->request === 'koszyk' || $wp->request === 'koszyk/')) {
        $wp->query_vars['pagename'] = 'koszyk';
        $wp->matched_rule = 'koszyk/?$';
        $wp->matched_query = 'pagename=koszyk';
    }
    // Fallback dla stron prawnych – działają nawet bez pretty permalinks
    if (isset($wp->request) && in_array($wp->request, ['regulamin','regulamin/','polityka-poprawnosci','polityka-poprawnosci/','kontakt','kontakt/'], true)) {
        $slug = rtrim($wp->request, '/');
        $wp->query_vars['pagename'] = $slug;
        $wp->matched_rule = $slug.'/?$';
        $wp->matched_query = 'pagename='.$slug;
    }
});

// ====== CHECKOUT: silny bypass i konfiguracja (jak dla koszyka) ======
// Ultra-late template include for checkout (avoid maintenance hijack)
add_filter('template_include', function($template){
    if ((function_exists('is_checkout') && is_checkout()) || is_page('zamowienie')) {
        // Nie nadpisuj strony "zamówienie otrzymane"/"order-pay"
        if (function_exists('is_order_received_page') && is_order_received_page()) return $template;
        if (function_exists('is_checkout_pay_page') && is_checkout_pay_page()) return $template;
        $tpl = get_template_directory() . '/page-zamowienie.php';
        if (file_exists($tpl)) {
            add_action('wp_footer', function(){ echo '<!-- PREOMAR FORCE_CHECKOUT_TEMPLATE -->'; });
            return $tpl;
        }
    }
    return $template;
}, 99999);

// Ultra-early include+exit for checkout for zalogowani (omijaj maintenance)
add_action('template_redirect', function(){
    if (!is_user_logged_in()) return;
    $is_co = (function_exists('is_checkout') && is_checkout()) || is_page('zamowienie');
    if (!$is_co && isset($_SERVER['REQUEST_URI'])) {
        $p = $_SERVER['REQUEST_URI'];
        if (stripos($p, '/zamowienie') !== false || stripos($p, '/checkout') !== false) { $is_co = true; }
    }
    if ($is_co) {
        remove_action('template_redirect', 'auth_redirect');
        $tpl = get_template_directory() . '/page-zamowienie.php';
        if (file_exists($tpl)) {
            status_header(200); nocache_headers(); add_action('wp_head', function(){ echo '<!-- PREOMAR CHECKOUT_EARLY_BYPASS -->'; }); include $tpl; exit;
        }
    }
}, -20000);

// Rewrite rule /zamowienie/ and unify checkout URL
add_action('init', function(){ add_rewrite_rule('^zamowienie/?$', 'index.php?pagename=zamowienie', 'top'); });
add_filter('woocommerce_get_checkout_url', function($url){ return home_url('/zamowienie/'); });

// CHECKOUT: otocz formularz checkout dedykowanym kontenerem o stałej szerokości (łatwe centrowanie)
add_action('woocommerce_before_checkout_form', function(){
    echo '<div class="preomar-checkout-container">';
}, 1);
add_action('woocommerce_after_checkout_form', function(){
    echo '</div>';
}, 9999);

// Wymuś szybkie centrowanie kontenera (inline CSS) – działa nawet przy twardym cache przeglądarki
add_action('wp_head', function(){
    if (function_exists('is_checkout') && is_checkout()) {
        echo '<style>.preomar-checkout-container{max-width:1080px;margin-left:auto;margin-right:auto;padding-left:16px;padding-right:16px}</style>';
    }
}, 99);

// Zmiana URL podstrony polityki na /polityka-poprawnosci/ (+redirect ze starego /polityka-prywatnosci/)
add_action('init', function(){
    if (get_transient('preomar_policy_slug_migrated')) return;
    // Znajdź stronę polityki: po opcji lub po slugu
    $policy_id = (int) get_option('wp_page_for_privacy_policy');
    $page = $policy_id ? get_post($policy_id) : get_page_by_path('polityka-prywatnosci');
    if ($page && !is_wp_error($page)) {
        $desired = 'polityka-poprawnosci';
        // Jeśli nie ma już strony o docelowym slugu – zmień slug bieżącej
        $existing = get_page_by_path($desired);
        if (!$existing) {
            wp_update_post([ 'ID' => $page->ID, 'post_name' => $desired, 'post_status' => 'publish' ]);
            // Ustaw tę stronę jako stronę polityki prywatności w ustawieniach WordPress
            update_option('wp_page_for_privacy_policy', $page->ID);
            // Po zmianie slugu odśwież rewrite rules (raz)
            flush_rewrite_rules();
        }
        set_transient('preomar_policy_slug_migrated', 1, DAY_IN_SECONDS);
    }
});

// 301 Redirect: /polityka-prywatnosci/ -> /polityka-poprawnosci/
add_action('template_redirect', function(){
    if (!isset($_SERVER['REQUEST_URI'])) return;
    $uri = $_SERVER['REQUEST_URI'];
    if (stripos($uri, 'polityka-prywatnosci') !== false) {
        wp_redirect(home_url('/polityka-poprawnosci/'), 301);
        exit;
    }
});

// Ensure legal pages are public/published for guests (avoid 404 when status is private/draft)
add_action('init', function(){
    $changed = false;
    foreach (['regulamin','polityka-poprawnosci','kontakt'] as $slug) {
        $p = get_page_by_path($slug);
        if ($p && !is_wp_error($p)) {
            if ($p->post_status !== 'publish') {
                wp_update_post(['ID'=>$p->ID, 'post_status'=>'publish']);
                $changed = true;
            }
        }
    }
    // Make sure WP knows which page is the privacy policy
    $pp = get_page_by_path('polityka-poprawnosci');
    if ($pp && !is_wp_error($pp)) {
        if ((int)get_option('wp_page_for_privacy_policy') !== (int)$pp->ID) {
            update_option('wp_page_for_privacy_policy', $pp->ID);
            $changed = true;
        }
    }
    if ($changed && !get_transient('preomar_legal_rules_flushed')) {
        flush_rewrite_rules();
        set_transient('preomar_legal_rules_flushed', 1, HOUR_IN_SECONDS);
    }
}, 12);

// Manual fixer: go to /wp-admin/?preomar_fix_regulamin=1 (admin only) to replace placeholder content
add_action('admin_init', function(){
    if (!current_user_can('manage_options')) return;
    if (!isset($_GET['preomar_fix_regulamin'])) return;
    $p = get_page_by_path('regulamin');
    if ($p && !is_wp_error($p)) {
        wp_update_post([
            'ID' => $p->ID,
            'post_status' => 'publish',
            'post_content' => '<h2>Regulamin sklepu</h2><p>(Treść w przygotowaniu. Uzupełnij właściwą treść prawną: definicje, zakres usług, zamówienia, płatności, dostawa, odstąpienie, reklamacje, dane kontaktowe przedsiębiorcy.)</p>'
        ]);
        update_post_meta($p->ID, '_wp_page_template', 'page-legal.php');
        wp_safe_redirect( get_permalink($p) );
        exit;
    }
});

// ====== LEGAL PAGES BYPASS (Regulamin / Polityka prywatności / Kontakt) ======
// Ultra-early include+exit – zawsze pokaż prawdziwy szablon strony, nawet jeśli maintenance próbuje przejąć
add_action('template_redirect', function(){
    if (!function_exists('is_page')) return;
    $is_legal = is_page(array('regulamin','polityka-prywatnosci','polityka-poprawnosci','kontakt'));
    if (!$is_legal) return;
    // Usuń auth redirect i ewentualne maintenance
    remove_action('template_redirect','auth_redirect');
    // Renderuj bezpośrednio i wyjdź
    // Prefer our dedicated legal template to avoid any injected content
    $tpl = get_template_directory() . '/page-legal.php';
    if (!file_exists($tpl)) {
        // Fallbacks
        $fallback = locate_template('page.php');
        $tpl = $fallback ? $fallback : get_query_template('page');
    }
    if ($tpl && file_exists($tpl)) {
        status_header(200); nocache_headers();
        add_action('wp_head', function(){ echo '<!-- PREOMAR LEGAL_EARLY_BYPASS -->'; });
        include $tpl; exit;
    }
}, -20000);

// Late fallback: nawet jeśli wtyczka próbuje nadpisać template_include, wymuś page.php
add_filter('template_include', function($template){
    if (function_exists('is_page') && is_page(array('regulamin','polityka-prywatnosci','polityka-poprawnosci','kontakt'))) {
        $tpl = locate_template('page-legal.php');
        if ($tpl) {
            add_action('wp_footer', function(){ echo '<!-- PREOMAR LEGAL_FORCE_TEMPLATE page-legal.php -->'; });
            return $tpl;
        }
    }
    return $template;
}, 99999);

// 3) Ostateczny bezpiecznik: nadpisz wynik the_content na tych stronach (z zachowaniem shortcode + formatowania)
add_filter('the_content', function($content){
    if (function_exists('is_page') && is_page(array('regulamin','polityka-prywatnosci','polityka-poprawnosci','kontakt'))) {
        $raw = get_post_field('post_content', get_the_ID());
        // Jeśli Regulamin ma placeholder „coming soon” albo jest pusty – pokaż sensowny szkic regulaminu
        $is_placeholder = false;
        if (is_page('regulamin')) {
            $txt = strtolower(wp_strip_all_tags($raw));
            if (trim($txt) === '' || strpos($txt, 'wielkie rzeczy') !== false || strpos($txt, 'coming soon') !== false) {
                $is_placeholder = true;
            }
        }
        if ($is_placeholder) {
            $raw = '<h2>Regulamin sklepu</h2>'
                 . '<p>(Treść w przygotowaniu. Uzupełnij właściwą treść prawną: definicje, zakres usług, zamówienia, płatności, dostawa, odstąpienie, reklamacje, dane kontaktowe przedsiębiorcy.)</p>';
        }
        // Przepuść przez shortcody i auto-paragrafy; bez innych filtrów wtyczek
        $raw = do_shortcode($raw);
        $raw = wpautop($raw);
        return $raw;
    }
    return $content;
}, PHP_INT_MAX);

// ====== PUBLICZNE PODSTRONY (Regulamin / Polityka / Kontakt): obejście maintenance i podmian treści ======
// 1) Usuń podejrzane przekierowania maintenance na tych stronach
add_action('template_redirect', function(){
    if (!function_exists('is_page')) return;
    if (!is_page(array('regulamin','polityka-prywatnosci','polityka-poprawnosci','kontakt'))) return;
    // Usuń potencjalne przymusowe logowanie
    remove_action('template_redirect','auth_redirect');
    // Usuń podejrzane callbacki maintenance/coming soon
    global $wp_filter;
    if (!empty($wp_filter['template_redirect'])) {
        $patterns = [ 'maint', 'coming', 'soon', 'under', 'construction', 'seedprod', 'cmp', 'comingsoon' ];
        foreach ($wp_filter['template_redirect']->callbacks as $prio => $callbacks) {
            foreach ($callbacks as $id => $cb) {
                $fn = $cb['function']; $name='';
                if (is_string($fn)) { $name = $fn; }
                elseif (is_array($fn)) { $name = (is_object($fn[0])? get_class($fn[0]) : (string)$fn[0]) . '::' . (string)$fn[1]; }
                elseif ($fn instanceof Closure) { $name = 'Closure@template_redirect'; }
                $low = strtolower($name);
                foreach($patterns as $p){ if ($low !== '' && strpos($low, $p) !== false) { remove_action('template_redirect', $cb['function'], $prio); break; } }
            }
        }
    }
}, -10000);

// 2) Usuń podmianę zawartości (the_content) przez wtyczki typu "coming soon" na tych stronach
add_action('wp', function(){
    if (!function_exists('is_page')) return;
    if (!is_page(array('regulamin','polityka-prywatnosci','polityka-poprawnosci','kontakt'))) return;
    // Najprościej: usuń WSZYSTKIE filtry the_content, a następnie dodaj nasz minimalny render (shortcode + autop)
    remove_all_filters('the_content');
    add_filter('the_content', function($unused){
        $raw = get_post_field('post_content', get_the_ID());
        return wpautop(do_shortcode($raw));
    }, 0);
}, 0);

// ====== KOSZYK: diagnostyka i agresywne czyszczenie filtrów mogących wstawiać placeholder ======
// 1) Debug URL: /koszyk/?preomar_cart_debug=1 – wypisuje surową treść i listę filtrów the_content
add_action('template_redirect', function(){
    if ( function_exists('is_page') && is_page('koszyk') && isset($_GET['preomar_cart_debug']) ) {
        global $wp_filter;
        header('Content-Type: text/plain; charset=utf-8');
        $cart_id = get_the_ID();
        $raw = get_post_field('post_content', $cart_id);
        echo "=== PREOMAR CART DEBUG ===\n";
        echo 'Post ID: ' . $cart_id . "\nSlug: koszyk\n";
        echo "Has shortcode [woocommerce_cart]? " . ( has_shortcode($raw,'woocommerce_cart') ? 'YES' : 'NO' ) . "\n\n";
        echo "--- Raw post_content (as stored) ---\n" . $raw . "\n\n";
        echo "--- Stripped text ---\n" . strtolower(trim(wp_strip_all_tags($raw))) . "\n\n";
        echo "--- the_content filters (priority => callback) ---\n";
        if ( isset($wp_filter['the_content']) ) {
            foreach ($wp_filter['the_content']->callbacks as $prio => $cbs) {
                foreach ($cbs as $id => $cb) {
                    $fn = $cb['function']; $name='';
                    if (is_string($fn)) { $name = $fn; }
                    elseif (is_array($fn)) { $name = (is_object($fn[0])? get_class($fn[0]) : (string)$fn[0]) . '::' . (string)$fn[1]; }
                    elseif ($fn instanceof Closure) { $name = 'Closure'; }
                    echo $prio . ' => ' . $name . "\n";
                }
            }
        } else {
            echo "(brak filtrów)\n";
        }
        echo "\n=== END DEBUG ===";
        exit;
    }
}, 0);

// 2) Usuń podejrzane filtry maintenance / coming soon z template_redirect dla koszyka (jak dla stron prawnych)
add_action('template_redirect', function(){
    if ( ! function_exists('is_page') || ! is_page('koszyk') ) return;
    global $wp_filter;
    if (!empty($wp_filter['template_redirect'])) {
        $patterns = [ 'maint', 'coming', 'soon', 'under', 'construction', 'seedprod', 'cmp', 'comingsoon' ];
        foreach ($wp_filter['template_redirect']->callbacks as $prio => $callbacks) {
            foreach ($callbacks as $id => $cb) {
                $fn = $cb['function']; $name='';
                if (is_string($fn)) { $name = $fn; }
                elseif (is_array($fn)) { $name = (is_object($fn[0])? get_class($fn[0]) : (string)$fn[0]) . '::' . (string)$fn[1]; }
                elseif ($fn instanceof Closure) { $name = 'Closure@template_redirect'; }
                $low = strtolower($name);
                foreach($patterns as $p){ if ($low !== '' && strpos($low, $p) !== false) { remove_action('template_redirect', $cb['function'], $prio); break; } }
            }
        }
    }
}, -10050); // tuż przed prawnymi (-10000)

// 3) Wczesne czyszczenie filtrów the_content dla koszyka z podejrzanych wtyczek bez kasowania naszych
add_action('wp', function(){
    if ( ! function_exists('is_page') || ! is_page('koszyk') ) return;
    global $wp_filter;
    if ( empty($wp_filter['the_content']) ) return;
    $patterns = [ 'coming', 'soon', 'maint', 'construction', 'seedprod', 'cmp', 'underconstruction' ];
    foreach ($wp_filter['the_content']->callbacks as $prio => $callbacks) {
        foreach ($callbacks as $id => $cb) {
            $fn = $cb['function']; $name='';
            if (is_string($fn)) { $name = $fn; }
            elseif (is_array($fn)) { $name = (is_object($fn[0])? get_class($fn[0]) : (string)$fn[0]) . '::' . (string)$fn[1]; }
            elseif ($fn instanceof Closure) { $name = 'Closure@the_content'; }
            $low = strtolower($name);
            foreach($patterns as $p){ if ($low !== '' && strpos($low,$p)!==false) { remove_filter('the_content', $cb['function'], $prio); break; } }
        }
    }
}, 0);

// 4) Ostateczne zabezpieczenie: jeśli po wszystkich filtrach treść nadal wygląda jak placeholder – wymuś shortcode
add_filter('the_content', function($content){
    if ( function_exists('is_page') && is_page('koszyk') ) {
        $plain = strtolower(trim(wp_strip_all_tags($content)));
        if ($plain === '' || strpos($plain,'wielkie rzeczy') !== false || strpos($plain,'coming soon') !== false || strpos($plain,'wkrótce uruchomimy') !== false) {
            $content = '[woocommerce_cart]';
        }
        // Upewnij się że jest shortcode (jeśli nie – dodaj na końcu)
        if ( strpos($content,'[woocommerce_cart') === false ) {
            $content .= "\n[woocommerce_cart]";
        }
        // Renderuj ręcznie, aby ominąć dalsze filtry nadpisujące
        return do_shortcode($content);
    }
    return $content;
}, PHP_INT_MAX - 1000); // bardzo późno, ale jeszcze przed absolutnym MAX

// Ensure checkout page exists and is assigned
function preomar_setup_checkout_page(){
    $co_page = get_page_by_path('zamowienie');
    if (!$co_page) {
        $id = wp_insert_post([
            'post_title'   => 'Zamówienie',
            'post_name'    => 'zamowienie',
            'post_content' => '[woocommerce_checkout]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_author'  => 1,
            'meta_input'   => ['_wp_page_template' => 'page-zamowienie.php']
        ]);
        if ($id && class_exists('WooCommerce')) { update_option('woocommerce_checkout_page_id', $id); }
    } else {
        if (class_exists('WooCommerce') && get_option('woocommerce_checkout_page_id') != $co_page->ID) {
            update_option('woocommerce_checkout_page_id', $co_page->ID);
        }
        $tpl = get_post_meta($co_page->ID, '_wp_page_template', true);
        if (!$tpl || $tpl === 'default') { update_post_meta($co_page->ID, '_wp_page_template', 'page-zamowienie.php'); }
    }
}
add_action('preomar_delayed_setup', 'preomar_setup_checkout_page');
add_action('admin_init', function(){ if (!get_transient('preomar_checkout_checked')) { preomar_setup_checkout_page(); set_transient('preomar_checkout_checked', true, HOUR_IN_SECONDS); } });

// Runtime naprawa strony koszyka (placeholder -> shortcode) + jednorazowe w danej sesji
add_action('init', function(){
    if ( is_admin() ) return;
    $cart_id = (int) get_option('woocommerce_cart_page_id');
    if ( ! $cart_id ) {
        $p = get_page_by_path('koszyk');
        if ( $p ) { $cart_id = $p->ID; update_option('woocommerce_cart_page_id', $cart_id); }
    }
    if ( ! $cart_id ) return;
    $p = get_post($cart_id);
    if ( ! $p ) return;
    $raw = (string) $p->post_content;
    $plain = strtolower( trim( wp_strip_all_tags( $raw ) ) );
    if ( $plain === '' || strpos($plain,'wielkie rzeczy') !== false || strpos($plain,'coming soon') !== false || strpos($plain,'wkrótce uruchomimy') !== false || ! has_shortcode($raw,'woocommerce_cart') ) {
        wp_update_post(['ID'=>$cart_id,'post_content'=>'[woocommerce_cart]']);
    }
});

// Friendly guest message on cart page (do NOT duplicate existing functions)
add_filter('the_content', function($content){
    if ( function_exists('is_cart') && is_cart() && ! is_user_logged_in() ) {
        // Jeśli strona koszyka nie zawiera shortcode koszyka – nie ruszaj (może to specjalny landing)
        if ( strpos( $content, '[woocommerce_cart' ) === false ) {
            // fallback: wyświetl i tak kartę – placeholder prawdopodobnie
        }
        ob_start();
        echo '<div class="preomar-guest-cart" style="max-width:820px;margin:70px auto 40px;background:#fff;border:1px solid #e5ebf2;border-radius:28px;padding:70px 60px;text-align:center;box-shadow:0 16px 42px -12px rgba(28,44,72,.18);">';
        echo '<h1 style="margin:0 0 26px;font-size:2.15rem;font-weight:800;color:#1d2e49;">Zaloguj się aby korzystać z koszyka</h1>';
        echo '<p style="margin:0 0 38px;font-size:1rem;line-height:1.55;color:#4a5664;">Posiadanie konta pozwala dodawać produkty, zapisywać historię zakupów i szybciej finalizować zamówienia.</p>';
        $login_url = esc_url( wp_login_url( home_url('/koszyk/') ) );
        $register_url = function_exists('wp_registration_url') ? esc_url( wp_registration_url() ) : esc_url( wp_login_url( home_url('/koszyk/') ) );
        echo '<div style="display:flex;flex-wrap:wrap;justify-content:center;gap:18px;">';
        echo '<a href="'.$login_url.'" class="login-btn" style="display:inline-block;background:#ff6b00;color:#fff;text-decoration:none;font-weight:700;padding:18px 44px;border-radius:18px;box-shadow:0 10px 30px -8px rgba(255,107,0,.5);transition:.35s;">Zaloguj się</a>';
        echo '<a href="'.$register_url.'" class="login-btn" style="display:inline-block;background:#1E3A8A;color:#fff;text-decoration:none;font-weight:700;padding:18px 44px;border-radius:18px;box-shadow:0 10px 30px -8px rgba(30,58,138,.45);transition:.35s;">Załóż konto</a>';
        echo '</div>';
        echo '<p style="margin:46px 0 0;font-size:.78rem;letter-spacing:.5px;color:#6b7280;">Rejestracja bezpłatna. Brak spamu – tylko potwierdzenia transakcji.</p>';
        echo '</div>';
        return ob_get_clean();
    }
    return $content;
}, 40); // priority po ewentualnych placeholderach

// AJAX HANDLERS FOR CUSTOM LOGIN FORMS
add_action('wp_ajax_nopriv_preomar_login', 'preomar_ajax_login');
add_action('wp_ajax_preomar_login', 'preomar_ajax_login');
add_action('wp_ajax_nopriv_preomar_register', 'preomar_ajax_register');
add_action('wp_ajax_preomar_register', 'preomar_ajax_register');
add_action('wp_ajax_nopriv_preomar_lost_password', 'preomar_ajax_lost_password');
add_action('wp_ajax_preomar_lost_password', 'preomar_ajax_lost_password');

// AJAX Login Handler
function preomar_ajax_login() {
    // Sprawdź nonce
    if (!wp_verify_nonce($_POST['nonce'], 'preomar_login_nonce')) {
        wp_send_json_error(array('message' => 'Błąd bezpieczeństwa. Odśwież stronę i spróbuj ponownie.'));
    }

    $username = sanitize_text_field($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    if (empty($username) || empty($password)) {
        wp_send_json_error(array('message' => 'Nazwa użytkownika i hasło są wymagane.'));
    }

    // Próba logowania
    $user = wp_authenticate($username, $password);

    if (is_wp_error($user)) {
        $error_message = $user->get_error_message();
        wp_send_json_error(array('message' => $error_message));
    }

    // Logowanie pomyślne
    wp_clear_auth_cookie();
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, $remember);

    wp_send_json_success(array(
        'message' => 'Logowanie pomyślne!',
        'redirect' => home_url()
    ));
}

// AJAX Registration Handler
function preomar_ajax_register() {
    // Sprawdź nonce
    if (!wp_verify_nonce($_POST['nonce'], 'preomar_login_nonce')) {
        wp_send_json_error(array('message' => 'Błąd bezpieczeństwa. Odśwież stronę i spróbuj ponownie.'));
    }

    // Sprawdź czy rejestracja jest włączona
    if (!get_option('users_can_register')) {
        wp_send_json_error(array('message' => 'Rejestracja jest obecnie wyłączona.'));
    }

    $username = sanitize_text_field($_POST['username']);
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password'];

    // Walidacja
    if (empty($username) || empty($email) || empty($password)) {
        wp_send_json_error(array('message' => 'Wszystkie pola są wymagane.'));
    }

    if (!is_email($email)) {
        wp_send_json_error(array('message' => 'Podaj prawidłowy adres email.'));
    }

    if (username_exists($username)) {
        wp_send_json_error(array('message' => 'Ta nazwa użytkownika jest już zajęta.'));
    }

    if (email_exists($email)) {
        wp_send_json_error(array('message' => 'Użytkownik z tym adresem email już istnieje.'));
    }

    // Stwórz użytkownika
    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
        wp_send_json_error(array('message' => $user_id->get_error_message()));
    }

    // Automatyczne logowanie po rejestracji
    wp_clear_auth_cookie();
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id);

    // Wyślij email powitalny
    wp_new_user_notification($user_id, null, 'user');

    wp_send_json_success(array(
        'message' => 'Konto zostało utworzone pomyślnie!',
        'redirect' => home_url()
    ));
}

// AJAX Lost Password Handler
function preomar_ajax_lost_password() {
    // Sprawdź nonce
    if (!wp_verify_nonce($_POST['nonce'], 'preomar_login_nonce')) {
        wp_send_json_error(array('message' => 'Błąd bezpieczeństwa. Odśwież stronę i spróbuj ponownie.'));
    }

    $username_or_email = sanitize_text_field($_POST['user_login']);

    if (empty($username_or_email)) {
        wp_send_json_error(array('message' => 'Podaj nazwę użytkownika lub adres email.'));
    }

    // Sprawdź czy użytkownik istnieje
    if (strpos($username_or_email, '@')) {
        $user = get_user_by('email', $username_or_email);
    } else {
        $user = get_user_by('login', $username_or_email);
    }

    if (!$user) {
        wp_send_json_error(array('message' => 'Nie znaleziono użytkownika z podanymi danymi.'));
    }

    // Wyślij email z linkiem do resetowania hasła
    $result = retrieve_password($user->user_login);

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
    }

    wp_send_json_success(array(
        'message' => 'Link do resetowania hasła został wysłany na Twój adres email.'
    ));
}

// NAPRAWA PRZEKIEROWAŃ LOGOWANIA I REJESTRACJI
add_filter('woocommerce_login_redirect', 'preomar_woocommerce_login_redirect', 10, 2);
add_filter('woocommerce_registration_redirect', 'preomar_redirect_after_registration');
add_filter('lostpassword_url', 'preomar_fix_lostpassword_url');

// Obsługa strony ustawień konta bez tworzenia fizycznej strony
add_action('init', 'preomar_handle_account_settings_page');
add_action('init', 'preomar_create_account_settings_page');

// Strona "Sprzedawaj" – upewnij się że istnieje jedna strona z przypisanym szablonem
add_action('init', function(){
    if (get_page_by_path('sprzedawaj')) return;
    $page_id = wp_insert_post([
        'post_title'   => 'Sprzedawaj',
        'post_name'    => 'sprzedawaj',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_content' => ''
    ]);
    if ($page_id && !is_wp_error($page_id)) {
        update_post_meta($page_id, '_wp_page_template', 'page-sprzedawaj.php');
    }
});

function preomar_handle_account_settings_page() {
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'ustawienia-konta') !== false) {
        add_filter('template_include', function() {
            return get_template_directory() . '/page-ustawienia-konta.php';
        });
    }
}

// Jednorazowe utworzenie strony ustawień konta jeśli brak
function preomar_create_account_settings_page(){
    if (get_page_by_path('ustawienia-konta')) return;
    $page_id = wp_insert_post([
        'post_title'   => 'Ustawienia konta',
        'post_name'    => 'ustawienia-konta',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_content' => ''
    ]);
    if ($page_id && !is_wp_error($page_id)) {
        update_post_meta($page_id, '_wp_page_template', 'page-ustawienia-konta.php');
    }
}

// Przekierowanie po udanym logowaniu WooCommerce
function preomar_woocommerce_login_redirect($redirect, $user) {
    // Sprawdź czy użytkownik próbował się dostać do konkretnej strony
    if (isset($_REQUEST['redirect_to']) && !empty($_REQUEST['redirect_to'])) {
        $redirect_to = esc_url($_REQUEST['redirect_to']);
        // Nie przekierowuj do admin panelu ani login strony
        if (strpos($redirect_to, 'wp-admin') === false && strpos($redirect_to, 'wp-login') === false) {
            return $redirect_to;
        }
    }
    
    // Sprawdź czy w sesji jest zapisana strona z której przyszedł
    if (isset($_SESSION['redirect_after_login']) && !empty($_SESSION['redirect_after_login'])) {
        $redirect_url = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
        return $redirect_url;
    }
    
    // Sprawdź referrer
    $referrer = wp_get_referer();
    if ($referrer && strpos($referrer, home_url()) === 0) {
        // Tylko jeśli referrer to nasza strona i nie zawiera login
        if (strpos($referrer, 'wp-login') === false && strpos($referrer, 'my-account') === false) {
            return $referrer;
        }
    }
    
    // Domyślnie przekieruj na stronę główną zamiast konta
    return home_url('/sklep/');
}

// Przekierowanie po udanej rejestracji
function preomar_redirect_after_registration($redirect) {
    if (isset($_POST['woocommerce_register_redirect'])) {
        $redirect_to = sanitize_url($_POST['woocommerce_register_redirect']);
        if ($redirect_to) {
            return $redirect_to;
        }
    }
    
    return home_url();
}

// Napraw URL resetowania hasła
function preomar_fix_lostpassword_url($url) {
    // Upewnij się że URL jest poprawny dla różnych konfiguracji
    if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) {
        // Środowisko lokalne - może potrzebować /wordpress/ prefix
        return home_url('/wp-login.php?action=lostpassword');
    }
    
    // Środowisko produkcyjne
    return wp_lostpassword_url();
}

// Obsługa błędów logowania i rejestracji
add_action('wp_login_failed', 'preomar_login_failed_redirect');
add_action('authenticate', 'preomar_handle_login_errors', 30, 3);

function preomar_login_failed_redirect($username) {
    $referrer = wp_get_referer();
    
    // Sprawdź czy to jest request z naszej strony logowania
    if ($referrer && (strpos($referrer, 'moje-konto') !== false || strpos($referrer, 'my-account') !== false)) {
        $redirect_url = add_query_arg(array(
            'login_error' => 'failed',
            'username' => urlencode($username)
        ), $referrer);
        wp_redirect($redirect_url);
        exit;
    }
}

function preomar_handle_login_errors($user, $username, $password) {
    // Sprawdź różne typy błędów
    if (empty($username) || empty($password)) {
        $error = new WP_Error();
        if (empty($username)) {
            $error->add('empty_username', __('Nazwa użytkownika jest wymagana.'));
        }
        if (empty($password)) {
            $error->add('empty_password', __('Hasło jest wymagane.'));
        }
        return $error;
    }
    
    return $user;
}

// Dodaj obsługę przekierowań dla różnych środowisk
add_action('init', 'preomar_fix_login_redirects');

function preomar_fix_login_redirects() {
    // Dla darmowych hostingów często trzeba naprawić permalinki
    if (!get_option('permalink_structure')) {
        // Automatycznie ustaw permalinki jeśli są puste
        update_option('permalink_structure', '/%postname%/');
        flush_rewrite_rules();
    }
    
    // Sprawdź czy WooCommerce jest aktywne i skonfigurowane
    if (class_exists('WooCommerce')) {
        $myaccount_page_id = get_option('woocommerce_myaccount_page_id');
        if (!$myaccount_page_id || !get_page($myaccount_page_id)) {
            // Stwórz stronę moje-konto jeśli nie istnieje
            $page = array(
                'post_title' => 'Moje konto',
                'post_content' => '[woocommerce_my_account]',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => 'moje-konto'
            );
            $page_id = wp_insert_post($page);
            if ($page_id) {
                update_option('woocommerce_myaccount_page_id', $page_id);
            }
        }
    }
}

// Force WordPress to recognize /koszyk/ URL
add_action('init', function() {
    if (isset($_SERVER['REQUEST_URI'])) {
        $request_uri = trim($_SERVER['REQUEST_URI'], '/');
        $base_path = trim(str_replace(home_url(), '', site_url()), '/');
        
        // Remove base path if exists
        if ($base_path && strpos($request_uri, $base_path) === 0) {
            $request_uri = trim(substr($request_uri, strlen($base_path)), '/');
        }
        
        // Check if this is cart URL
        if ($request_uri === 'koszyk' || $request_uri === 'koszyk/') {
            global $wp_query;
            $cart_page = get_page_by_path('koszyk');
            if ($cart_page) {
                $wp_query->queried_object = $cart_page;
                $wp_query->queried_object_id = $cart_page->ID;
                $wp_query->is_page = true;
                $wp_query->is_singular = true;
                $wp_query->is_home = false;
                $wp_query->is_front_page = false;
                
                // Set page template
                add_filter('page_template', function() {
                    return get_template_directory() . '/page-koszyk.php';
                });
            }
        }
    }
}, 5);

// Handle cart URL in JavaScript/AJAX calls
add_action('wp_head', function() {
    ?>
    <script>
    // Ensure cart links use correct URL
    document.addEventListener('DOMContentLoaded', function() {
        var cartLinks = document.querySelectorAll('a[href*="koszyk"]');
        cartLinks.forEach(function(link) {
            if (link.href.indexOf('koszyk') !== -1 && link.href.indexOf('?') === -1) {
                // Already clean URL, no action needed
            }
        });
    });
    </script>
    <?php
});

// Obsługa filtra promocji w WooCommerce
function preomar_filter_products_by_sale($query) {
    // Sprawdź czy to jest strona sklepu i czy nie jest to admin
    if (!is_admin() && $query->is_main_query()) {
        // Sprawdź czy jest parametr filter_sale
        if (isset($_GET['filter_sale']) && $_GET['filter_sale'] === 'yes') {
            // Sprawdź czy to jest strona sklepu lub kategoria produktów
            if (is_shop() || is_product_category() || is_product_tag()) {
                // Dodaj meta_query do filtrowania tylko produktów promocyjnych
                $meta_query = $query->get('meta_query');
                if (!$meta_query) {
                    $meta_query = array();
                }
                
                $meta_query[] = array(
                    'key' => '_sale_price',
                    'value' => '',
                    'compare' => '!='
                );
                
                $meta_query[] = array(
                    'key' => '_stock_status',
                    'value' => 'instock'
                );
                
                $query->set('meta_query', $meta_query);
            }
        }
    }
}
add_action('pre_get_posts', 'preomar_filter_products_by_sale');

// Dodaj informację o liczbie produktów promocyjnych w tytule strony
function preomar_modify_shop_title($title) {
    if (is_shop() && isset($_GET['filter_sale']) && $_GET['filter_sale'] === 'yes') {
        return 'Produkty w promocji';
    }
    return $title;
}
add_filter('woocommerce_page_title', 'preomar_modify_shop_title');

// Dodaj breadcrumb dla promocji
function preomar_modify_breadcrumb($crumbs) {
    if (is_shop() && isset($_GET['filter_sale']) && $_GET['filter_sale'] === 'yes') {
        $crumbs[1] = array('Promocje', '');
    }
    return $crumbs;
}
add_filter('woocommerce_get_breadcrumb', 'preomar_modify_breadcrumb');

function preomar_single_product_styles_debug(){
    if( function_exists('is_product') && is_product() ){
        $handle='preomar-single-product-enhanced';
        $path=get_template_directory().'/assets/css/single-product-enhanced.css';
        $ver=file_exists($path)?filemtime($path):time();
        wp_enqueue_style($handle, get_template_directory_uri().'/assets/css/single-product-enhanced.css', ['preomar-product-fix'], $ver);
        if(file_exists($path)){
            $raw=@file_get_contents($path);
            if($raw){
                wp_add_inline_style($handle, '/*inline duplicate for debug*/\n'.$raw); // wymusza obecność styli
            }
        }
    }
}
add_action('wp_enqueue_scripts','preomar_single_product_styles_debug',99);

// --- Ikony wykrzyknika dla wszystkich komunikatów WooCommerce (sukces / błąd / info) + ukrycie linku Cofnij ---
add_action('wp_head', function(){
    echo '<style>
    /* Reset domyślnych pseudo-elementów WooCommerce */
    .woocommerce-message::before,
    .woocommerce-error::before,
    .woocommerce-info::before { display:none; }

    .woocommerce-message,
    .woocommerce-error,
    .woocommerce-info { position:relative; padding-left:64px !important; }

    /* Wspólna baza ikony */
    .woocommerce-message:after,
    .woocommerce-error:after,
    .woocommerce-info:after {
        content:"!";
        position:absolute; left:24px; top:50%; transform:translateY(-50%);
        width:32px; height:32px; border-radius:50%;
        font-weight:700; font-size:18px; line-height:32px; text-align:center; font-family:inherit;
        box-shadow:0 2px 4px rgba(0,0,0,.15);
    }
    .woocommerce-message:after { background:#16a34a; color:#fff; }
    .woocommerce-error:after  { background:#dc2626; color:#fff; }
    .woocommerce-info:after   { background:#2563eb; color:#fff; }

    /* Ukryj link cofnięcia przy usuwaniu z koszyka */
    .woocommerce-message a.restore-item,
    .woocommerce-error a.restore-item,
    .woocommerce-info a.restore-item { display:none !important; }

    /* Całkowicie ukryj komunikat usunięcia pozycji z koszyka (ma link przywracania) */
    .woocommerce-message:has(a.restore-item) { display:none !important; }

    /* Ukryj link "Zobacz koszyk" (różne warianty WooCommerce) */
    a.added_to_cart, .woocommerce a.added_to_cart { display:none !important; visibility:hidden !important; }
    .woocommerce-message a.button.wc-forward, .woocommerce-message a.wc-forward,
    .woocommerce-error a.button.wc-forward, .woocommerce-error a.wc-forward,
    .woocommerce-info a.button.wc-forward, .woocommerce-info a.wc-forward { display:none !important; visibility:hidden !important; }
    </style>';
});

// Usuń przycisk/link "Zobacz koszyk" z komunikatu po dodaniu do koszyka (różne warianty klas)
add_filter('wc_add_to_cart_message_html', function($message, $products){
    // Zbuduj własny komunikat "Dodano do koszyka …" po polsku
    $count = 0; $names = [];
    if (is_array($products)) {
        foreach ($products as $pid => $qty) {
            $qty = (int)$qty; if ($qty < 1) $qty = 1;
            $count += $qty;
            $t = get_the_title($pid);
            if ($t) { $names[] = '„'.wp_strip_all_tags($t).'”'; }
        }
    }
    if ($count > 0) {
        if (count($names) === 1 && $count === 1) {
            $message = 'Dodano do koszyka: ' . $names[0] . '.';
        } elseif (count($names) > 1) {
            $message = 'Dodano do koszyka: ' . implode(', ', $names) . '.';
        } else {
            $message = 'Dodano do koszyka (' . $count . ' szt.).';
        }
    } else {
        $message = wp_strip_all_tags($message);
    }
    // Usuń zbędne linki w treści komunikatu (różne warianty)
    $message = preg_replace('#<a[^>]*class="[^"]*added_to_cart[^"]*"[^>]*>.*?</a>#i','', $message);
    $message = preg_replace('#<a[^>]*class="[^"]*wc-forward[^"]*"[^>]*>.*?</a>#i','', $message);
    return trim($message);
}, 10, 2);
// Kompatybilność ze starszym hookiem (jeśli używany przez wtyczki/tłumaczenia)
add_filter('woocommerce_add_to_cart_message', function($message){
    // Starszy hook – zwięzły komunikat bez linków
    return 'Dodano do koszyka.';
}, 10, 1);

// Ujednolić komunikat po usunięciu z koszyka
add_filter('woocommerce_cart_item_removed_message', function($msg, $cart_item){
    // Nie pokazuj komunikatu o usunięciu pozycji
    return '';
}, 10, 2);

// Komunikat przy przywróceniu pozycji
add_filter('woocommerce_cart_item_restored_message', function($msg, $cart_item){
    // Nie pokazuj komunikatu o przywróceniu pozycji
    return '';
}, 10, 2);

// Dla pewności: po usunięciu/przywróceniu pozycji wyczyść notyfikacje Woo
add_action('woocommerce_cart_item_removed', function(){
    if (function_exists('wc_clear_notices')) wc_clear_notices();
}, 100);
add_action('woocommerce_cart_item_restored', function(){
    if (function_exists('wc_clear_notices')) wc_clear_notices();
}, 100);
?><?php
// ================== DODATKOWE SORTOWANIE ==================
// Dodajemy opcję 'date-asc' (najstarsze) oraz upewniamy się że 'price-desc' działa.
// front-end wysyła parametry orderby=menu_order|price|price-desc|date|date-asc

// Modyfikuj dostępne opcje (gdyby WooCommerce generował select – u nas custom, ale dla spójności)
add_filter('woocommerce_catalog_orderby', function($sortby){
    // Zachowaj tylko używane klucze
    $allowed = [
        'menu_order' => __('Domyślne sortowanie','woocommerce'),
        'price'      => __('Sortuj po cenie: od najniższej','woocommerce'),
        'price-desc' => __('Sortuj po cenie: od najwyższej','woocommerce'),
        'date'       => __('Najnowsze','woocommerce'),
        'date-asc'   => __('Najstarsze','woocommerce')
    ];
    return $allowed;
});

// Przekładanie customowych wartości na argumenty WP_Query / WC_Query
add_filter('woocommerce_get_catalog_ordering_args', function($args, $orderby, $order){
    switch($orderby){
        case 'price-desc':
            // Użyj natywnego sortowania WooCommerce po cenie (tabela wc_product_meta_lookup)
            $args['orderby'] = 'price';
            $args['order'] = 'DESC';
            if (isset($args['meta_key'])) unset($args['meta_key']);
            break;
        case 'price':
            // Użyj natywnego sortowania WooCommerce po cenie (tabela wc_product_meta_lookup)
            $args['orderby'] = 'price';
            $args['order'] = 'ASC';
            if (isset($args['meta_key'])) unset($args['meta_key']);
            break;
        case 'date-asc':
            $args['orderby'] = 'date';
            $args['order'] = 'ASC';
            break;
        case 'date':
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
        case 'menu_order': // default
        default:
            // pozostaw domyślne
            break;
    }
    return $args;
}, 9999, 3);

// Upewnij się że query_vars akceptuje nasze wartości (opcjonalne, głównie dla bezpieczeństwa)
add_filter('request', function($vars){
    if(isset($vars['orderby'])){
        // Dozwolone wartości
        $allowed = ['menu_order','price','price-desc','date','date-asc'];
        if(!in_array($vars['orderby'], $allowed, true)){
            $vars['orderby'] = 'menu_order';
        }
    }
    // Usuń puste product_cat z URL (np. product_cat=) – może generować dziwne tax_query
    if (isset($vars['product_cat']) && $vars['product_cat'] === '') {
        unset($vars['product_cat']);
    }
    // Usuń domyślne zakresy cen (0/10000) wstawiane przez formularz gdy użytkownik nie filtrował
    if (isset($vars['min_price']) && isset($vars['max_price'])) {
        if ($vars['min_price'] === '0' && $vars['max_price'] === '10000') {
            unset($vars['min_price']);
            unset($vars['max_price']);
            add_action('wp_footer', function(){ echo '<!-- PREOMAR DEFAULT_PRICE_RANGE_REMOVED -->'; });
        }
    }
    return $vars;
});
// ==========================================================

// ============== AWARYJNA REGENERACJA TABEL LOOKUP CEN (WooCommerce) ==============
// Objaw: strona sklepu/kategorii pusta dla gości, a na stronie głównej produkty widać.
// Częsta przyczyna: pusta tabela wp_wc_product_meta_lookup (np. po imporcie bazy).
// Rozwiązanie: jednorazowo zregeneruj lookupy oraz do czasu regeneracji omiń sortowanie/filtry po cenie.

// 1) Jednorazowa regeneracja (z bezpiecznym ograniczeniem przez transient)
add_action('init', function(){
    if (!class_exists('WooCommerce')) return;
    if (get_transient('preomar_lookup_regen_running')) return;
    global $wpdb;
    $table = $wpdb->prefix . 'wc_product_meta_lookup';
    // Sprawdź istnienie tabeli
    $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
    if ($exists !== $table) return;
    // Jeśli pusta – uruchom regenerację
    $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM `$table`");
    if ($count === 0 && function_exists('wc_update_product_lookup_tables')) {
        set_transient('preomar_lookup_regen_running', 1, 5 * MINUTE_IN_SECONDS);
        // Uruchom regenerację synchronicznie (mała baza dev). W produkcji lepiej użyć Action Scheduler.
        wc_update_product_lookup_tables();
        delete_transient('preomar_lookup_regen_running');
        add_action('wp_footer', function(){ echo '<!-- PREOMAR LOOKUP_REGENERATED -->'; });
    }
}, 1);

// 2) Jeśli lookup nadal pusty – usuń filtry po cenie i sortowanie po cenie, aby przywrócić listę produktów
add_filter('request', function($vars){
    if (!class_exists('WooCommerce')) return $vars;
    // Działaj tylko na archiwach produktów / sklepie – heurystyka po parametrach
    $is_product_archive = (isset($vars['post_type']) && $vars['post_type'] === 'product') || isset($_GET['min_price']) || isset($_GET['max_price']) || isset($_GET['orderby']);
    if (!$is_product_archive) return $vars;
    global $wpdb; $table = $wpdb->prefix . 'wc_product_meta_lookup';
    $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
    if ($exists !== $table) return $vars;
    $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM `$table`");
    if ($count === 0) {
        // Usuń sortowanie / filtry po cenie – do czasu aż lookupy będą gotowe
        if (isset($vars['orderby']) && ($vars['orderby'] === 'price' || $vars['orderby'] === 'price-desc')) {
            $vars['orderby'] = 'menu_order';
        }
        if (isset($vars['min_price'])) unset($vars['min_price']);
        if (isset($vars['max_price'])) unset($vars['max_price']);
        add_action('wp_footer', function(){ echo '<!-- PREOMAR LOOKUP_EMPTY_PRICE_FILTERS_REMOVED -->'; });
    }
    return $vars;
}, 20);

// =============================================================================

// Wczesne czyszczenie zapytań do strony Sklepu – usuń puste 's', pusty product_cat oraz domyślne min/max
add_filter('request', function($vars){
    // Wykryj stronę Sklepu po ID/slug
    $shop_id = function_exists('wc_get_page_id') ? wc_get_page_id('shop') : (int) get_option('woocommerce_shop_page_id');
    $shop_slug = $shop_id ? get_post_field('post_name', $shop_id) : '';
    $is_shop_request = false;
    if ($shop_id) {
        if ((isset($vars['page_id']) && (int)$vars['page_id'] === (int)$shop_id) || (isset($vars['p']) && (int)$vars['p'] === (int)$shop_id)) {
            $is_shop_request = true;
        }
    }
    if (!$is_shop_request && $shop_slug) {
        if (isset($vars['pagename']) && $vars['pagename'] === $shop_slug) { $is_shop_request = true; }
        // Obsługa struktur /sklep/page/2 – pagename może być z prefiksem
        if (isset($vars['pagename']) && strpos($vars['pagename'], $shop_slug . '/') === 0) { $is_shop_request = true; }
    }

    if ($is_shop_request) {
        $changed = false;
        if (isset($vars['s']) && $vars['s'] === '') { unset($vars['s']); $changed = true; }
        if (isset($vars['product_cat']) && $vars['product_cat'] === '') { unset($vars['product_cat']); $changed = true; }
        if (isset($vars['min_price']) && isset($vars['max_price']) && $vars['min_price'] === '0' && $vars['max_price'] === '10000') {
            unset($vars['min_price']); unset($vars['max_price']); $changed = true;
        }
        if ($changed) { add_action('wp_footer', function(){ echo '<!-- PREOMAR REQUEST_SHOP_CLEAN -->'; }); }
    }
    return $vars;
}, 1);

// OSTATECZNA KOREKTA GŁÓWNEGO ZAPYTANIA SKLEPU (bardzo późno):
// - usuń puste 's'
// - zamień meta_key=_price + orderby=meta_value(_num) na orderby=price (lookup)
add_action('pre_get_posts', function($q){
    if (is_admin() || !$q->is_main_query()) return;
    if (!(function_exists('is_shop') && (is_shop() || is_product_category() || is_product_tag()))) return;
    // Usuń puste s
    $s = $q->get('s');
    if ($s === '') { $q->set('s', null); if (property_exists($q, 'is_search')) { $q->is_search = false; } }
    // Napraw sortowanie po cenie przez postmeta
    $meta_key = $q->get('meta_key');
    $orderby  = $q->get('orderby');
    if ($meta_key === '_price' || in_array($orderby, ['meta_value','meta_value_num'], true)) {
        $q->set('meta_key', null);
        $q->set('orderby', 'price');
        add_action('wp_footer', function(){ echo '<!-- PREOMAR ORDER_FIX_ENFORCED -->'; });
    }
}, 999);

// ================== FILTRY BOCZNE (KATEGORIE) – WERSJA POD NATYWNY PRICE FILTER WooCommerce ==================
// Usunięto ręczne meta_query na _price aby korzystać z wbudowanej logiki WooCommerce,
// która używa tabeli lookup i poprawnie obsługuje różne typy produktów. Zostawiamy tylko obsługę parametru ?categories=slug1,slug2
add_action('pre_get_posts', function($q){
    if (is_admin() || !$q->is_main_query()) return;
    if (!(is_shop() || is_product_category() || is_product_tag())) return;

    $q->set('post_type','product');
    // Tylko opublikowane produkty na listach sklepu (goście muszą je widzieć)
    $q->set('post_status','publish');
    if (isset($_GET['s']) && $_GET['s'] === '') {
        unset($q->query_vars['s']);
        if (property_exists($q, 'is_search')) $q->is_search = false;
    }

    // Kategorie (lista slugów) – budujemy tax_query tylko jeśli podano parametry
    if (!empty($_GET['categories'])) {
        $cat_slugs = array_filter(array_map('sanitize_title', explode(',', wp_unslash($_GET['categories']))));
        if ($cat_slugs) {
            $term_ids = [];
            foreach ($cat_slugs as $slug) {
                $t = get_term_by('slug', $slug, 'product_cat');
                if ($t && !is_wp_error($t)) {
                    $term_ids[] = $t->term_id;
                    $children = get_term_children($t->term_id, 'product_cat');
                    if (!is_wp_error($children) && $children) $term_ids = array_merge($term_ids, $children);
                }
            }
            if ($term_ids) {
                $tax_query = (array) $q->get('tax_query');
                $tax_query[] = [
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => array_unique($term_ids),
                    'operator' => 'IN'
                ];
                $q->set('tax_query', $tax_query);
            }
        }
    }

    // Debug (bez ceny)
    add_action('wp_footer', function() use ($q){
        if (!($q->is_main_query())) return;
        $tax_query = $q->get('tax_query');
        $cat_ids = [];
        if (is_array($tax_query)) {
            foreach ($tax_query as $clause) {
                if (is_array($clause) && isset($clause['taxonomy']) && $clause['taxonomy'] === 'product_cat' && !empty($clause['terms'])) {
                    $cat_ids = array_merge($cat_ids, (array) $clause['terms']);
                }
            }
        }
        global $wp_query; $found = $wp_query ? $wp_query->found_posts : 0;
        echo '<!-- PREOMAR CATEGORY FILTER ONLY term_ids=' . esc_html(implode(',', array_unique($cat_ids))) . ' found=' . esc_html($found) . ' -->';
    });
}, 12);

// DODATKOWY HOOK na końcu - upewnienie się że nasz tax_query nie zostanie nadpisany
add_action('pre_get_posts', function($q) {
    if (is_admin() || !$q->is_main_query()) return;
    if (!(is_shop() || is_product_category() || is_product_tag())) return;
    
    // Jeśli mamy categories w URL, upewnij się że tax_query zawiera nasze kategorie
    if (!empty($_GET['categories'])) {
        $current_tax = $q->get('tax_query');
        $has_our_categories = false;
        
        if (is_array($current_tax)) {
            foreach ($current_tax as $clause) {
                if (is_array($clause) && isset($clause['taxonomy']) && $clause['taxonomy'] === 'product_cat') {
                    $has_our_categories = true;
                    break;
                }
            }
        }
        
        // Jeśli brak naszych kategorii - dodaj awaryjnie
        if (!$has_our_categories) {
            $slugs = array_filter(array_map('sanitize_title', explode(',', wp_unslash($_GET['categories']))));
            $q->set('tax_query', [[
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $slugs,
                'operator' => 'IN',
                'include_children' => true
            ]]);
            
            add_action('wp_footer', function() {
                echo "<!-- PREOMAR EMERGENCY RESTORE tax_query -->";
            });
        }
    }
}, 99); // Bardzo późny priority

// Usuń product_visibility filtr WooCommerce gdy używamy własnych kategorii (może ukrywać wszystkie testowe produkty)
add_filter('woocommerce_product_query_tax_query', function($tax_query){
    if (empty($_GET['categories'])) return $tax_query;
    $removed = false;
    foreach($tax_query as $i => $clause){
        if (is_array($clause) && isset($clause['taxonomy']) && $clause['taxonomy'] === 'product_visibility') {
            unset($tax_query[$i]);
            $removed = true;
        }
    }
    if ($removed) {
        add_action('wp_footer', function(){ echo '<!-- PREOMAR VISIBILITY_FILTER_REMOVED -->'; });
    }
    return $tax_query;
});

// Twarda normalizacja filtra widoczności produktów: upewnij się, że nie jest odwrócony.
// Jeśli jakiś plugin ustawi operator na IN dla termów wykluczających, może to wyzerować wyniki dla gości.
add_filter('woocommerce_product_query_tax_query', function($tax_query){
    // Nie dotykaj, jeśli używamy własnych kategorii (tam i tak usuwamy filtr widoczności wyżej)
    if (!empty($_GET['categories'])) return $tax_query;
    $changed = false;
    foreach ($tax_query as $idx => $clause) {
        if (!is_array($clause)) continue;
        if (!isset($clause['taxonomy']) || $clause['taxonomy'] !== 'product_visibility') continue;
        $terms = isset($clause['terms']) ? (array)$clause['terms'] : [];
        $operator = isset($clause['operator']) ? strtoupper($clause['operator']) : '';
        // Kluczowe termy wykluczające w WooCommerce
        $exclude_candidates = ['exclude-from-catalog','exclude-from-search','outofstock'];
        $has_exclude_terms = false;
        foreach ($terms as $t) { if (in_array($t, $exclude_candidates, true)) { $has_exclude_terms = true; break; } }
        if ($has_exclude_terms && ($operator === '' || $operator === 'IN')) {
            // Zamień na NOT IN aby nie zawężać do samych "wykluczonych"
            $tax_query[$idx]['operator'] = 'NOT IN';
            $changed = true;
        }
    }
    if ($changed) {
        add_action('wp_footer', function(){ echo '<!-- PREOMAR VISIBILITY_FORCE_NOT_IN -->'; });
    }
    return $tax_query;
}, 15);

// Pełny DEBUG końcowego zapytania głównego (po wykonaniu) – zobaczmy finalną SQL i kluczowe query_vars
add_action('wp_footer', function(){
    if (!(is_shop() || is_product_category() || is_product_tag())) return;
    global $wp_query;
    if (!$wp_query || !$wp_query->is_main_query()) return;
    $vars = $wp_query->query_vars;
    $out = [];
    foreach(['s','post_type','post_status','orderby','meta_key'] as $k){ if(isset($vars[$k])) $out[]=$k.'='.$vars[$k]; }
    // Skrócona SQL (pierwsze 400 znaków)
    $sql = isset($wp_query->request)? substr($wp_query->request,0,400):'';
    $sql = str_replace(["\n","\r"],' ',$sql);
    $mq = isset($vars['meta_query']) && is_array($vars['meta_query']) ? count($vars['meta_query']) : 0;
    $tq = isset($vars['tax_query']) && is_array($vars['tax_query']) ? count($vars['tax_query']) : 0;
    echo '<!-- PREOMAR MAINQUERY vars: '.esc_html(implode(' ', $out)).' meta_clauses='.$mq.' tax_clauses='.$tq.' sql: '.esc_html($sql).' -->';
    // Debug summary of hooks
    if (isset($GLOBALS['PREOMAR_DBG']) && is_array($GLOBALS['PREOMAR_DBG'])){
        $d = $GLOBALS['PREOMAR_DBG'];
        $flags = 'bm=' . (int)!empty($d['before_main']) .
                 ' am=' . (int)!empty($d['after_main']) .
                 ' bl=' . (int)!empty($d['before_loop']) .
                 ' al=' . (int)!empty($d['after_loop']) .
                 ' np=' . (int)!empty($d['no_products']) .
                 ' wls=' . (int)!empty($d['wp_loop_start']) .
                 ' wle=' . (int)!empty($d['wp_loop_end']) .
                 ' tpl=' . (!empty($d['template']) ? esc_html($d['template']) : '-') .
                 ' arch=' . (int)!empty($d['archive_tpl']);
        echo '<!-- PREOMAR DBG_SUMMARY ' . $flags . ' -->';
    }
});

// Usuń pusty parametr wyszukiwania dla zapytań produktów zanim powstanie is_search=1 (zapobiega AND 0=1 w SQL)
add_filter('request', function($vars){
    if (isset($vars['s']) && $vars['s']==='') {
        // Jeśli zapytanie dotyczy produktów lub zawiera nasze parametry filtrów – usuń 's'
        $is_product = !empty($vars['post_type']) && $vars['post_type']==='product';
        $has_filters = isset($_GET['categories']) || isset($_GET['min_price']) || isset($_GET['max_price']);
        if ($is_product || $has_filters) {
            unset($vars['s']);
        }
    }
    return $vars;
});
// ================================================================
// ==================================================================

// Debug pętli sklepu: oznacz start/koniec oraz przypadek braku produktów
// Global debug flags
if (!isset($GLOBALS['PREOMAR_DBG'])) {
    $GLOBALS['PREOMAR_DBG'] = [
        'before_main' => false,
        'after_main' => false,
        'before_loop' => false,
        'after_loop' => false,
        'no_products' => false,
        'wp_loop_start' => false,
        'wp_loop_end' => false,
        'template' => '',
        'archive_tpl' => false,
    ];
}

add_action('woocommerce_before_shop_loop', function(){
    $GLOBALS['PREOMAR_DBG']['before_loop'] = true;
    echo '<!-- PREOMAR LOOP_START -->';
}, 1);
add_action('woocommerce_after_shop_loop', function(){
    $GLOBALS['PREOMAR_DBG']['after_loop'] = true;
    echo '<!-- PREOMAR LOOP_END -->';
}, 999);
add_action('woocommerce_no_products_found', function(){
    $GLOBALS['PREOMAR_DBG']['no_products'] = true;
    echo '<!-- PREOMAR LOOP_NO_PRODUCTS -->';
});

// Dodatkowe markery: przed/po głównej treści WooCommerce
add_action('woocommerce_before_main_content', function(){
    $GLOBALS['PREOMAR_DBG']['before_main'] = true;
    echo '<!-- PREOMAR BEFORE_MAIN -->';
}, 1);
add_action('woocommerce_after_main_content', function(){
    $GLOBALS['PREOMAR_DBG']['after_main'] = true;
    echo '<!-- PREOMAR AFTER_MAIN -->';
}, 999);

// Markery ogólnej pętli WP dla głównego zapytania na stronach sklepu/kategorii/tagu
add_action('loop_start', function($query){
    if (!is_a($query, 'WP_Query')) return;
    if (!$query->is_main_query()) return;
    if (function_exists('is_shop') && (is_shop() || is_product_category() || is_product_tag())) {
        $GLOBALS['PREOMAR_DBG']['wp_loop_start'] = true;
        echo '<!-- PREOMAR WP_LOOP_START -->';
    }
}, 1);
add_action('loop_end', function($query){
    if (!is_a($query, 'WP_Query')) return;
    if (!$query->is_main_query()) return;
    if (function_exists('is_shop') && (is_shop() || is_product_category() || is_product_tag())) {
        $GLOBALS['PREOMAR_DBG']['wp_loop_end'] = true;
        echo '<!-- PREOMAR WP_LOOP_END -->';
    }
}, 999);

// Śledzenie głównego pliku szablonu użytego do renderu
add_filter('template_include', function($template){
    $base = basename($template);
    $GLOBALS['PREOMAR_DBG']['template'] = $base;
    if ($base === 'archive-product.php') { $GLOBALS['PREOMAR_DBG']['archive_tpl'] = true; }
    echo '<!-- PREOMAR TEMPLATE_INCLUDE ' . esc_html($base) . ' -->';
    return $template;
}, 1000);

// Bardzo wczesny render single produktu – omija maintenance/coming-soon które podmieniają treść gościom
function preomar_render_product_early_bypass(){
    if (!function_exists('is_singular') || !is_singular('product')) return;
    if (is_admin()) return;
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if ($method !== 'GET') return;
    if (!empty($_GET['add-to-cart'])) return;

    // Wymuś 200 OK i brak cache, po czym wyrenderuj Woo szablon i zakończ zanim wtyczki przejmą ster
    status_header(200);
    nocache_headers();

    $tpl = locate_template(['woocommerce/single-product.php','single-product.php']);
    if ($tpl) {
        include $tpl; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
        exit;
    }
    // Fallback gdyby nie znalazł szablonu – użyj części Woo
    if (function_exists('wc_get_template_part')) {
        get_header('shop');
        while (have_posts()) { the_post(); wc_get_template_part('content','single-product'); }
        get_footer('shop');
        exit;
    }
}
add_action('template_redirect','preomar_render_product_early_bypass', -10000);

// Markery wokół części szablonów WooCommerce (np. content-product.php)
add_action('woocommerce_before_template_part', function($template_name, $template_path, $located){
    echo '<!-- PREOMAR BEFORE_TPL name=' . esc_html($template_name) . ' located=' . esc_html(basename($located)) . ' -->';
}, 10, 3);
add_action('woocommerce_after_template_part', function($template_name, $template_path, $located){
    echo '<!-- PREOMAR AFTER_TPL name=' . esc_html($template_name) . ' located=' . esc_html(basename($located)) . ' -->';
}, 10, 3);

// Marker podczas wykonywania shortcode'ów produktowych (np. [products])
add_filter('do_shortcode_tag', function($output, $tag, $attr){
    if (in_array($tag, ['products','product_category','product_categories','featured_products','sale_products','best_selling_products','top_rated_products','recent_products'], true)){
        echo '<!-- PREOMAR SHORTCODE ' . esc_html($tag) . ' -->';
    }
    return $output;
}, 10, 3);

// Wymuś użycie wrappera woocommerce.php dla archiwów sklepu (omija bloki/template parts)
add_filter('template_include', function($template){
    if (function_exists('is_shop') && (is_shop() || is_product_category() || is_product_tag())) {
        $wc_template = locate_template('woocommerce.php');
        if ($wc_template) {
            echo '<!-- PREOMAR FORCED_TEMPLATE woocommerce.php -->';
            return $wc_template;
        }
    }
    return $template;
}, 50);

// Ostateczny fallback: wymuś włączenie woocommerce.php i zakończ, zanim WordPress załaduje inny szablon
add_action('template_redirect', function(){
    if (function_exists('is_shop') && (is_shop() || is_product_category() || is_product_tag())) {
        $wc_template = locate_template('woocommerce.php');
        if ($wc_template) {
            status_header(200);
            include $wc_template;
            exit;
        }
    }
}, 999);

// OSTATECZNE WYMUSZENIE KATEGORII PO MODYFIKACJACH WooCommerce (jeśli wcześniejsze pre_get_posts zostało nadpisane)
add_action('woocommerce_product_query', function($q){
    // Upewnij się, że lista produktów pokazuje tylko opublikowane pozycje (publiczny katalog)
    if (function_exists('is_shop') && (is_shop() || is_product_category() || is_product_tag())) {
        $q->set('post_status','publish');
        // Ostateczne wymuszenie sortowania: pozbądź się meta_key=_price i meta_value(_num)
        $meta_key = $q->get('meta_key');
        $orderby  = $q->get('orderby');
        if ($meta_key === '_price' || in_array($orderby, ['meta_value','meta_value_num'], true)) {
            $q->set('meta_key', null);
            $q->set('orderby', 'price');
            add_action('wp_footer', function(){ echo '<!-- PREOMAR ORDER_FIX_WC_QUERY -->'; });
        }
    }
    if (empty($_GET['categories'])) return;
    $slugs = array_filter(array_map('sanitize_title', explode(',', wp_unslash($_GET['categories']))));
    if (!$slugs) return;
    $all_term_ids = [];
    foreach ($slugs as $slug){
        $term = get_term_by('slug',$slug,'product_cat');
        if($term && !is_wp_error($term)){
            $all_term_ids[] = $term->term_id;
            $children = get_term_children($term->term_id,'product_cat');
            if(!is_wp_error($children) && $children){ $all_term_ids = array_merge($all_term_ids,$children); }
        }
    }
    if(!$all_term_ids) return;
    $tax_query = (array)$q->get('tax_query');
    // Usuń istniejące klauzule product_cat aby nie dublować
    foreach($tax_query as $i=>$clause){
        if(is_array($clause) && isset($clause['taxonomy']) && $clause['taxonomy']==='product_cat') unset($tax_query[$i]);
    }
    $tax_query[] = [
        'taxonomy' => 'product_cat',
        'field'    => 'term_id',
        'terms'    => array_unique($all_term_ids),
        'operator' => 'IN'
    ];
    $q->set('tax_query', $tax_query);
    add_action('wp_footer', function() use ($all_term_ids, $slugs){
    echo '<!-- PREOMAR WOO_CAT_APPLIED term_ids=' . esc_html(implode(',', $all_term_ids)) . ' slugs=' . esc_html(implode(',', $slugs)) . ' -->';
    });
}, 40);

// ================== WYSYŁKA: DODATKOWE METODY (InPost / Kurier / Odbiór) ==================
// Uwaga: Standardowo lepiej umieścić to w wtyczce, ale na potrzeby motywu dodajemy tutaj.
if ( ! function_exists('preomar_register_shipping_methods') ) {
    add_action('woocommerce_shipping_init', function(){
        if (class_exists('WC_Shipping_Method')) {
            // Bazowa klasa pomocnicza by nie powtarzać kodu
            if (!class_exists('Preomar_Base_Shipping_Method')) {
                abstract class Preomar_Base_Shipping_Method extends WC_Shipping_Method {
                    public function init(){
                        $this->init_form_fields();
                        $this->init_settings();
                        add_action('woocommerce_update_options_shipping_' . $this->id, [$this,'process_admin_options']);
                    }
                    public function init_form_fields(){
                        $this->form_fields = [
                            'enabled' => [
                                'title'       => __('Aktywna','preomar'),
                                'type'        => 'checkbox',
                                'label'       => __('Włącz metodę','preomar'),
                                'default'     => 'yes'
                            ],
                            'title' => [
                                'title'       => __('Nazwa wyświetlana','preomar'),
                                'type'        => 'text',
                                'default'     => $this->method_title,
                            ],
                            'cost' => [
                                'title'       => __('Koszt (zł)','preomar'),
                                'type'        => 'price',
                                'default'     => '12.99',
                                'description' => __('Podstawowy koszt wysyłki','preomar'),
                            ],
                            'free_shipping_threshold' => [
                                'title'       => __('Darmowa od kwoty','preomar'),
                                'type'        => 'price',
                                'default'     => '',
                                'description' => __('Pozostaw puste aby nie stosować progu darmowej wysyłki','preomar'),
                            ],
                        ];
                    }
                    protected function get_cost_after_threshold($package){
                        $cost = (float) $this->get_option('cost', 0);
                        $threshold = $this->get_option('free_shipping_threshold');
                        if ($threshold !== '' && WC()->cart && WC()->cart->get_subtotal() >= (float)$threshold) {
                            $cost = 0;
                        }
                        return $cost;
                    }
                    public function calculate_shipping($package = []){
                        $rate = [
                            'id'    => $this->id,
                            'label' => $this->get_option('title', $this->method_title),
                            'cost'  => $this->get_cost_after_threshold($package),
                        ];
                        $this->add_rate($rate);
                    }
                }
            }

            if (!class_exists('Preomar_Shipping_InPost_Locker')) {
                class Preomar_Shipping_InPost_Locker extends Preomar_Base_Shipping_Method {
                    public function __construct(){
                        $this->id = 'preomar_inpost_locker';
                        $this->method_title = 'InPost Paczkomat';
                        $this->method_description = __('Dostawa do paczkomatu InPost.','preomar');
                        $this->supports = ['shipping-zones'];
                        $this->init();
                    }
                }
            }
            if (!class_exists('Preomar_Shipping_InPost_Courier')) {
                class Preomar_Shipping_InPost_Courier extends Preomar_Base_Shipping_Method {
                    public function __construct(){
                        $this->id = 'preomar_inpost_courier';
                        $this->method_title = 'Kurier InPost';
                        $this->method_description = __('Kurier InPost pod wskazany adres.','preomar');
                        $this->supports = ['shipping-zones'];
                        $this->init();
                    }
                }
            }
            if (!class_exists('Preomar_Shipping_DPD_Courier')) {
                class Preomar_Shipping_DPD_Courier extends Preomar_Base_Shipping_Method {
                    public function __construct(){
                        $this->id = 'preomar_dpd_courier';
                        $this->method_title = 'Kurier DPD';
                        $this->method_description = __('Kurier DPD pod wskazany adres.','preomar');
                        $this->supports = ['shipping-zones'];
                        $this->init();
                    }
                }
            }
            if (!class_exists('Preomar_Shipping_Local_Pickup')) {
                class Preomar_Shipping_Local_Pickup extends Preomar_Base_Shipping_Method {
                    public function __construct(){
                        $this->id = 'preomar_local_pickup';
                        $this->method_title = 'Odbiór osobisty';
                        $this->method_description = __('Odbiór zamówienia w punkcie / sklepie.','preomar');
                        $this->supports = ['shipping-zones'];
                        $this->init();
                    }
                    protected function get_cost_after_threshold($package){ return 0; }
                }
            }
        }
    });

    add_filter('woocommerce_shipping_methods', function($methods){
        $methods['preomar_inpost_locker']  = 'Preomar_Shipping_InPost_Locker';
        $methods['preomar_inpost_courier'] = 'Preomar_Shipping_InPost_Courier';
        $methods['preomar_dpd_courier']    = 'Preomar_Shipping_DPD_Courier';
        $methods['preomar_local_pickup']   = 'Preomar_Shipping_Local_Pickup';
        return $methods;
    });
}

// Automatyczne utworzenie strefy wysyłki i dodanie metod (jednorazowo po aktywacji motywu)
if ( ! function_exists('preomar_setup_shipping_zone') ) {
    add_action('after_switch_theme', function(){
        if ( ! class_exists('WC_Shipping_Zones') ) return; // WooCommerce nieaktywne
        $existing = WC_Shipping_Zones::get_zones();
        foreach($existing as $zone){
            if (isset($zone['zone_name']) && $zone['zone_name'] === 'Polska (Auto)') return; // już jest
        }
        $zone = new WC_Shipping_Zone();
        $zone->set_zone_name('Polska (Auto)');
        $zone->set_locations([
            [ 'code' => 'PL', 'type' => 'country' ]
        ]);
        $zone_id = $zone->save();
        if ($zone_id){
            // Dodaj metody - domyślne koszty
            $zone_obj = new WC_Shipping_Zone($zone_id);
            foreach(['preomar_inpost_locker','preomar_inpost_courier','preomar_dpd_courier','preomar_local_pickup'] as $method_id){
                $zone_obj->add_shipping_method($method_id);
            }
        }
    });
}
// ==================================================================

// ================== PODSTAWOWE METODY PŁATNOŚCI + WYSYŁKI (AUTO SETUP) ==================
// Cel: uniknięcie komunikatu "Żadna metoda płatności nie jest dostępna" na develop / fresh install.
add_action('init', function(){
    if (!class_exists('WooCommerce')) return;
    // Uruchom tylko raz (możesz usunąć transient jeśli chcesz ponownie wymusić)
    if (get_transient('preomar_basic_checkout_done')) return;

    // --- Podstawowe bramki płatności ---
    // BACS (przelew bankowy)
    $bacs_settings = get_option('woocommerce_bacs_settings', []);
    if (empty($bacs_settings['enabled']) || $bacs_settings['enabled'] !== 'yes') {
        $bacs_settings = array_merge([
            'enabled'      => 'yes',
            'title'        => 'Przelew bankowy',
            'description'  => 'Opłać zamówienie zwykłym przelewem bankowym. W tytule podaj numer zamówienia.',
            'instructions' => 'Po zaksięgowaniu środków rozpoczniemy realizację.',
        ], $bacs_settings);
        update_option('woocommerce_bacs_settings', $bacs_settings);
        // Dane konta – przykładowe (zmień w panelu WooCommerce > Ustawienia > Płatności > Przelew)
        if (!get_option('woocommerce_bacs_accounts')) {
            update_option('woocommerce_bacs_accounts', [
                [
                    'account_name'   => 'Test Firma Sp. z o.o.',
                    'account_number' => '11112222333344445555666677',
                    'bank_name'      => 'Bank Test',
                    'sort_code'      => '',
                    'iban'           => 'PL11112222333344445555666677',
                    'bic'            => 'TESTPLPW'
                ]
            ]);
        }
    }
    // COD (pobranie)
    $cod_settings = get_option('woocommerce_cod_settings', []);
    if (empty($cod_settings['enabled']) || $cod_settings['enabled'] !== 'yes') {
        $cod_settings = array_merge([
            'enabled'      => 'yes',
            'title'        => 'Płatność przy odbiorze',
            'description'  => 'Zapłata gotówką kurierowi lub w paczkomacie (jeśli dostępne).',
            'instructions' => 'Przygotuj odliczoną kwotę.',
            'enable_for_virtual' => 'no'
        ], $cod_settings);
        update_option('woocommerce_cod_settings', $cod_settings);
    }
    // Ustal kolejność (opcjonalnie)
    $order = get_option('woocommerce_gateway_order');
    if (!is_array($order)) $order = [];
    foreach(['bacs','cod'] as $g){ if(!in_array($g,$order,true)) $order[]=$g; }
    update_option('woocommerce_gateway_order', $order);

    // --- Standardowe metody wysyłki w istniejącej strefie (np. Polska (Auto)) ---
    if (class_exists('WC_Shipping_Zones')) {
        $target_zone = null; $zones = WC_Shipping_Zones::get_zones();
        foreach($zones as $z){ if(isset($z['zone_name']) && stripos($z['zone_name'],'polska')!==false){ $target_zone = new WC_Shipping_Zone($z['id']); break; } }
        if(!$target_zone){ // fallback – utwórz małą strefę PL jeśli brak
            $target_zone = new WC_Shipping_Zone();
            $target_zone->set_zone_name('Polska (Basic)');
            $target_zone->set_locations([[ 'code'=>'PL','type'=>'country' ]]);
            $target_zone->save();
        }
        if ($target_zone) {
            $existing = []; foreach($target_zone->get_shipping_methods() as $m){ $existing[] = $m->id; }
            // Flat rate
            if (!in_array('flat_rate',$existing,true)) {
                $instance_id = $target_zone->add_shipping_method('flat_rate');
                if ($instance_id) {
                    update_option('woocommerce_flat_rate_'.$instance_id.'_settings', [
                        'enabled'=>'yes','title'=>'Kurier standard','cost'=>'14.99','tax_status'=>'taxable','type'=>'class'
                    ]);
                }
            }
            // Free shipping (od kwoty)
            if (!in_array('free_shipping',$existing,true)) {
                $instance_id = $target_zone->add_shipping_method('free_shipping');
                if ($instance_id) {
                    update_option('woocommerce_free_shipping_'.$instance_id.'_settings', [
                        'enabled'=>'yes','title'=>'Darmowa wysyłka','requires'=>'min_amount','min_amount'=>'300'
                    ]);
                }
            }
            // Local pickup
            if (!in_array('local_pickup',$existing,true)) {
                $instance_id = $target_zone->add_shipping_method('local_pickup');
                if ($instance_id) {
                    update_option('woocommerce_local_pickup_'.$instance_id.'_settings', [
                        'enabled'=>'yes','title'=>'Odbiór osobisty','cost'=>'0','tax_status'=>'none'
                    ]);
                }
            }
        }
    }

    set_transient('preomar_basic_checkout_done', 1, DAY_IN_SECONDS); // można usunąć w razie potrzeby ponownego uruchomienia
});
// ==================================================================

// ================== KOSZYK: UKRYJ KUPONY NA STRONIE KOSZYKA ==================
add_filter('woocommerce_coupons_enabled', function($enabled){
    // Wyłącz kupony zarówno na koszyku jak i na checkout
    if ((function_exists('is_cart') && is_cart()) || (function_exists('is_checkout') && is_checkout())) return false;
    return $enabled;
});
// Usuń formularz kuponu (klasyczny checkout) na wszelki wypadek
add_action('init', function(){ remove_action('woocommerce_before_checkout_form','woocommerce_checkout_coupon_form',10); });
// ==================================================================

// === Checkout layout fix: blokujemy poziome przewijanie i ustawiamy sensowną szerokość ===
add_action('wp_head', function(){
    if (function_exists('is_checkout') && is_checkout() && !is_cart()) {
        echo '<style>
        body.woocommerce-checkout { overflow-x:hidden; }
        .woocommerce-checkout .entry-content, .woocommerce-checkout form.checkout { max-width:1280px; margin:0 auto; }
        .woocommerce-checkout form.checkout { display:grid; grid-template-columns: minmax(0,2fr) minmax(340px,1fr); gap:48px; align-items:start; }
        .woocommerce-checkout .col2-set { width:100%; float:none; display:contents; }
        .woocommerce-checkout #customer_details { display:contents; }
        .woocommerce-checkout #order_review_heading { grid-column:2; margin-top:0; }
    .woocommerce-checkout #order_review { grid-column:2; position:static; top:auto; background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:26px 26px 10px; box-shadow:0 6px 18px -6px rgba(0,0,0,.08); overflow:hidden; }
        .woocommerce-checkout #order_review * { box-sizing:border-box; }
        .woocommerce-checkout #order_review table.shop_table { width:100%; table-layout:fixed; }
        /* Usuń kolumnę product-total */
        #order_review table.shop_table thead th.product-total, #order_review table.shop_table tbody td.product-total { display:none !important; }
        /* Przytnij i zawijaj opisy */
        #order_review .product-name { overflow:hidden; max-width:100%; }
        #order_review .product-name p { margin:.4em 0 0; font-size:12px; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; word-break:break-word; overflow-wrap:anywhere; }
        #order_review .product-name span, #order_review .product-name div { word-break:break-word; overflow-wrap:anywhere; }
        @supports not (-webkit-line-clamp:2){ #order_review .product-name p { max-height:4.4em; position:relative; } #order_review .product-name p::after { content:""; position:absolute; right:0; bottom:0; width:50%; height:1.3em; background:linear-gradient(90deg, rgba(255,255,255,0), #fff); }}
        #order_review img { max-width:48px; height:auto; }
    /* Ostateczna bariera: żaden kontent z product-name nie może wyjść poza kartę */
    #order_review .product-name { position:relative; max-height:140px; }
    #order_review .product-name:after { content:""; position:absolute; left:0; right:0; bottom:0; height:42px; pointer-events:none; background:linear-gradient(180deg, rgba(255,255,255,0), #fff); }
    #order_review .product-name * { max-width:100%; word-break:break-word; overflow-wrap:anywhere; }
    /* Długie nieprzerwane ciagi znaków */
    #order_review .product-name a, #order_review .product-name span, #order_review .product-name strong { word-break:break-word; overflow-wrap:anywhere; }

        /* === WOO BLOCKS CHECKOUT (nowy blokowy checkout) – przycięcie opisów w panelu podsumowania === */
        /* Podstawowe zawijanie + clamp (2 linie) */
        .wc-block-components-order-summary-item__description { margin:.25rem 0 0; font-size:12px; line-height:1.35; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; word-break:break-word; overflow-wrap:anywhere; }
        .wc-block-components-order-summary-item__description * { margin:0; font-size:inherit; line-height:inherit; }
        @supports not (-webkit-line-clamp:2){
            .wc-block-components-order-summary-item__description { max-height:2.7em; position:relative; }
            .wc-block-components-order-summary-item__description:after { content:""; position:absolute; right:0; bottom:0; width:45%; height:1.2em; background:linear-gradient(90deg, rgba(255,255,255,0), #fff); }
        }
        /* Fail-safe: jeśli element ma klasę .is-long (możemy dodać JS-em w przyszłości), całkiem ukryj */
        .wc-block-components-order-summary-item__description.is-long { display:none !important; }
        /* Opcja całkowitego ukrycia opisów – odkomentuj jeśli chcesz zniknęły: */
        /* .wc-block-components-order-summary-item__description { display:none !important; } */
        /* Kontener boczny podsumowania – upewnij się że nie rośnie przez padding */
        .wc-block-checkout__sidebar .wp-block-woocommerce-checkout-order-summary-block { overflow:hidden; }
        /* Silne łamanie bardzo długich ciągów bez spacji */
        .wc-block-components-order-summary-item__description, .wc-block-components-order-summary-item__description span, .wc-block-components-order-summary-item__description p { word-break:break-word; overflow-wrap:anywhere; }

        .woocommerce-checkout h3 { margin-top:26px; font-size:19px; }
        .woocommerce-checkout h3#order_review_heading { font-size:18px; margin:0 0 18px; padding:0 0 12px; border-bottom:1px solid #e5e7eb; }
        .woocommerce-checkout .woocommerce-billing-fields__field-wrapper, .woocommerce-checkout .woocommerce-shipping-fields__field-wrapper { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:14px 18px; }
        .woocommerce-checkout p.form-row { margin:0; }
        .woocommerce-checkout .woocommerce-additional-fields { grid-column:1; }
        @media(max-width:1100px){ .woocommerce-checkout form.checkout { grid-template-columns:1fr; } .woocommerce-checkout #order_review, .woocommerce-checkout #order_review_heading { grid-column:1; position:static; box-shadow:none; border-radius:12px; } }
    /* === DODANE: UPIĘKSZENIE STRONY ZAMÓWIENIA W KOLORACH MOTYWU === */
    body.woocommerce-checkout { background:#f1f5f9; }
    .woocommerce-checkout form.checkout { padding-top:10px; }
    .woocommerce-checkout h3 { font-weight:700; letter-spacing:.3px; color:#1E3A8A; }
    .woocommerce-checkout .woocommerce-billing-fields__field-wrapper,
    .woocommerce-checkout .woocommerce-shipping-fields__field-wrapper { --fld-gap:18px; gap:var(--fld-gap); }
    .woocommerce-checkout p.form-row { position:relative; }
    .woocommerce-checkout p.form-row label { font-size:.65rem; font-weight:600; text-transform:uppercase; letter-spacing:.6px; color:#475569; margin-bottom:6px; display:block; }
    .woocommerce-checkout p.form-row input.input-text,
    .woocommerce-checkout p.form-row textarea,
    .woocommerce-checkout p.form-row select { width:100%; padding:12px 14px; border:1px solid #d4dde7; border-radius:10px; background:#fff; font-size:.9rem; line-height:1.25; color:#1e293b; transition:.25s; box-shadow:0 1px 2px rgba(0,0,0,.04) inset; }
    .woocommerce-checkout p.form-row input:focus,
    .woocommerce-checkout p.form-row textarea:focus,
    .woocommerce-checkout p.form-row select:focus { outline:none; border-color:#1E3A8A; box-shadow:0 0 0 3px rgba(30,58,138,.25); }
    .select2-container--default .select2-selection--single { border:1px solid #d4dde7; height:44px; border-radius:10px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height:42px; padding-left:14px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height:42px; }

    /* Sekcje (classic) */
    .woocommerce-checkout #order_review { border-top:4px solid #1E3A8A; }
    #order_review table.shop_table thead { display:none; }
    #order_review table.shop_table tbody tr { border-bottom:1px solid #edf2f7; }
    #order_review table.shop_table tbody td { padding:12px 4px 14px; }
    #order_review .product-name { font-weight:600; font-size:.86rem; }
    #order_review .product-name a { color:#1E3A8A; text-decoration:none; }
    #order_review .product-name a:hover { color:#FF6B00; }
    #order_review .cart_item:last-of-type { border-bottom:1px solid #e2e8f0; }
    #order_review .order-total th, #order_review .order-total td { font-size:1rem; font-weight:700; color:#1E3A8A; }
    #order_review .woocommerce-Price-amount { font-weight:600; }

    /* Shipping methods (classic) */
    #shipping_method li { list-style:none; margin:0; }
    #shipping_method li + li { margin-top:6px; }
    #shipping_method input[type=radio] { accent-color:#1E3A8A; }
    #shipping_method label { display:flex; justify-content:space-between; align-items:center; width:100%; background:#fff; border:1px solid #dbe3ec; padding:14px 18px; border-radius:14px; font-size:.85rem; font-weight:600; color:#1e293b; cursor:pointer; transition:.25s; }
    #shipping_method label:hover { border-color:#1E3A8A; box-shadow:0 0 0 3px rgba(30,58,138,.2); }
    #shipping_method input:checked + label { border-color:#1E3A8A; background:linear-gradient(135deg,#f4f7fb,#eef4ff); box-shadow:0 0 0 3px rgba(30,58,138,.25); }
    #shipping_method label .amount { font-weight:700; color:#1E3A8A; }

    /* Shipping methods (Blocks) */
    .wc-block-components-shipping-rates-control__package .wc-block-components-radio-control__option { border:1px solid #dbe3ec; border-radius:14px; padding:14px 18px; transition:.25s; }
    .wc-block-components-shipping-rates-control__package .wc-block-components-radio-control__option[aria-checked=true] { border-color:#1E3A8A; background:linear-gradient(135deg,#f3f7fb,#eef4ff); box-shadow:0 0 0 3px rgba(30,58,138,.25); }

    /* Payment methods */
    .woocommerce-checkout .wc_payment_methods { padding:0; margin:0 0 24px; list-style:none; border:1px solid #e2e8f0; background:#fff; border-radius:18px; overflow:hidden; }
    .woocommerce-checkout .wc_payment_methods li { border-bottom:1px solid #e2e8f0; padding:4px 0; margin:0; }
    .woocommerce-checkout .wc_payment_methods li:last-child { border-bottom:none; }
    .woocommerce-checkout .wc_payment_methods input[type=radio] { accent-color:#1E3A8A; margin-left:10px; }
    .woocommerce-checkout .wc_payment_methods label { display:flex; align-items:center; gap:10px; font-weight:600; padding:12px 24px; cursor:pointer; }
    .woocommerce-checkout .payment_box { margin:-4px 24px 20px; background:#f8fafc; border:1px solid #e2e8f0; padding:14px 18px; border-radius:14px; font-size:.8rem; line-height:1.45; color:#334155; animation:fadeSlide .4s ease; }

    /* Place order button */
    #place_order, .wc-block-components-checkout-place-order-button { background:#FF6B00 !important; color:#fff !important; font-weight:700; font-size:1rem; padding:16px 34px !important; border:none !important; border-radius:14px !important; letter-spacing:.5px; box-shadow:0 10px 30px -8px rgba(255,107,0,.55),0 4px 14px -6px rgba(0,0,0,.25); transition:.35s !important; width:100%; }
    #place_order:hover, .wc-block-components-checkout-place-order-button:hover { background:#e85f00 !important; transform:translateY(-2px); box-shadow:0 14px 34px -10px rgba(255,107,0,.55),0 6px 18px -8px rgba(0,0,0,.25); }
    #place_order:active { transform:translateY(0); }

    /* Blocks order summary header */
    .wc-block-components-order-summary .wc-block-components-title { font-size:1rem; font-weight:700; color:#1E3A8A; }
    .wc-block-components-order-summary-item { border-bottom:1px solid #e5edf4; padding:14px 0; }
    .wc-block-components-order-summary-item__image { border-radius:10px; overflow:hidden; }
    .wc-block-components-order-summary-item__quantity-badge { background:#1E3A8A; }
    .wc-block-components-order-summary-item__description { font-size:.7rem; }
    .wc-block-components-totals-wrapper .wc-block-components-totals-item { font-size:.85rem; }
    .wc-block-components-totals-wrapper .wc-block-components-totals-item--order-total { font-size:1rem; font-weight:700; color:#1E3A8A; }
    /* Obrazki produktów – pełne bez przycinania */
    #order_review table.shop_table td.product-name img { width:56px; height:auto; border-radius:10px; object-fit:contain; }
    .wc-block-components-order-summary-item__image, .wc-block-components-order-summary-item__image a { width:56px !important; min-width:56px !important; height:auto !important; }
    .wc-block-components-order-summary-item__image img { width:56px !important; height:auto !important; object-fit:contain !important; border-radius:10px; }

    /* Notices */
    .woocommerce-NoticeGroup-checkout .woocommerce-error, .woocommerce-NoticeGroup-checkout .woocommerce-info, .woocommerce-NoticeGroup-checkout .woocommerce-message { border-radius:14px; }

    /* Kupon (jeśli pokaże się na checkout) */
    .woocommerce-checkout .checkout_coupon { display:none !important; }
    /* WooCommerce Blocks – ukryj wszystkie elementy kuponów */
    .wc-block-components-totals-coupon, .wc-block-components-checkout__coupon-form, .wc-block-components-order-summary__coupon-form { display:none !important; }

    /* Loader spinner (blokowy) w kolorach motywu */
    .wc-block-components-spinner { --wc-blocks-spinner-color:#1E3A8A; }

    /* Responsywność drobna */
    @media(max-width:780px){ #place_order { font-size:.95rem; } .woocommerce-checkout #order_review { padding:22px 20px 6px; } }
        </style>';
    }
});

// (Usunięto niestandardowe dopisywanie parametrów do linku sklepu – pozostaje domyślne zachowanie WooCommerce)

// ================== WYMUSZENIE SORTOWANIA (FALLBACK) ==================
// Zapewnia poprawne sortowanie po cenie / dacie jeśli inne pluginy nadpisują args
add_action('pre_get_posts', function($q){
    if (is_admin() || !$q->is_main_query()) return;
    $is_product_context = (function_exists('is_shop') && (is_shop() || is_product_category() || is_product_tag()))
        || (is_search() && (isset($_GET['post_type']) && $_GET['post_type']==='product'));
    if (!$is_product_context) return;
    if (empty($_GET['orderby'])) return;
    $orderby = sanitize_text_field(wp_unslash($_GET['orderby']));
    switch($orderby){
        case 'price':
            $q->set('meta_key','_price');
            $q->set('orderby','meta_value_num');
            $q->set('order','ASC');
            break;
        case 'price-desc':
            $q->set('meta_key','_price');
            $q->set('orderby','meta_value_num');
            $q->set('order','DESC');
            break;
        case 'date-asc':
            $q->set('orderby','date');
            $q->set('order','ASC');
            break;
        case 'date':
            $q->set('orderby','date');
            $q->set('order','DESC');
            break;
        default:
            // pozostaw
            break;
    }
}, 50);
// ================================================================

/** ======================================================================
 * JEDNORAZOWE USUWANIE KATEGORII "Nieruchomości" + jej podkategorii + produktów
 * Użycie (tylko dla administratora – zalogowany):
 * 1. DRY RUN (podgląd co zostanie usunięte – nic nie kasuje):
 *    /wp-admin/?purge_nieruchomosci=1
 * 2. POTWIERDZENIE i REALNE USUNIĘCIE:
 *    /wp-admin/?purge_nieruchomosci=1&confirm=1
 * Po wykonaniu ustawia transient aby przypadkiem nie uruchomić ponownie.
 * Po zakończeniu usuń ten blok kodu z functions.php.
 * ZRÓB KOPIĘ BAZY przed confirm=1.
 * ===================================================================== */
if (is_admin() && current_user_can('manage_options') && isset($_GET['purge_nieruchomosci'])) {
    add_action('admin_init', function(){
        if (get_transient('preomar_purged_nieruchomosci')) return; // już wykonane
        $slug = 'nieruchomosci';
        $main_term = get_term_by('slug', $slug, 'product_cat');
        global $preomar_purge_report; $preomar_purge_report = [];
        if (!$main_term || is_wp_error($main_term)) {
            $preomar_purge_report['error'] = 'Nie znaleziono termu o slug "'.$slug.'"';
            return;
        }
        // Zbierz wszystkie potomne termy (rekurencyjnie)
        $collect = function($parent_id, &$bag) use (&$collect){
            $children = get_terms([
                'taxonomy' => 'product_cat',
                'parent'   => $parent_id,
                'hide_empty' => false
            ]);
            if (!is_wp_error($children) && $children) {
                foreach($children as $c){
                    $bag[] = $c->term_id;
                    $collect($c->term_id, $bag);
                }
            }
        };
        $term_ids = [$main_term->term_id];
        $collect($main_term->term_id, $term_ids);

        // Produkty przypisane do któregokolwiek z termów
        $products = get_posts([
            'post_type' => 'product',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'tax_query' => [[
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $term_ids,
                'include_children' => true,
                'operator' => 'IN'
            ]]
        ]);
        $preomar_purge_report['term_count'] = count($term_ids);
        $preomar_purge_report['product_count'] = count($products);
        $do_delete = isset($_GET['confirm']) && $_GET['confirm']=='1';
        $preomar_purge_report['will_delete'] = $do_delete;
        if ($do_delete) {
            // Usuń produkty (force delete)
            foreach($products as $pid){
                wp_delete_post($pid, true);
            }
            // Usuń termy (najpierw potomne)
            foreach(array_reverse($term_ids) as $tid){
                wp_delete_term($tid, 'product_cat');
            }
            set_transient('preomar_purged_nieruchomosci', 1, DAY_IN_SECONDS*30);
        }
        add_action('admin_notices', function() use (&$preomar_purge_report){
            if (isset($preomar_purge_report['error'])) {
                echo '<div class="notice notice-error"><p><strong>Purge Nieruchomości:</strong> '.esc_html($preomar_purge_report['error']).'</p></div>';
                return;
            }
            $msg = 'Znaleziono '.$preomar_purge_report['term_count'].' termów (łącznie z głównym) i '.$preomar_purge_report['product_count'].' produktów.';
            if ($preomar_purge_report['will_delete']) {
                echo '<div class="notice notice-success"><p><strong>Purge Nieruchomości:</strong> '.$msg.' Usunięto. Blokadę ponownego uruchomienia ustawiono.</p></div>';
            } else {
                $url_confirm = add_query_arg(['purge_nieruchomosci'=>1,'confirm'=>1]);
                echo '<div class="notice notice-warning"><p><strong>DRY RUN – Purge Nieruchomości:</strong> '.$msg.'<br>Nic jeszcze nie usunięto. Aby USUNĄĆ trwale kliknij: <a href="'.esc_url($url_confirm).'" style="color:#b32d2e;font-weight:600;">Potwierdź usunięcie</a><br><em>Wykonaj kopię zapasową bazy przed potwierdzeniem!</em></p></div>';
            }
        });
    });
}
// ======================================================================

/** =====================================================================
 * NAPRAWA BRAKUJĄCEGO META _price (jednorazowo)
 * URL podglądu (DRY RUN): /wp-admin/?repair_prices=1
 * Wykonanie aktualizacji: /wp-admin/?repair_prices=1&confirm=1
 * Działanie:
 *  - Wyszukuje produkty (product) bez meta _price lub z pustym _price
 *  - Wartość ustala: najpierw _sale_price (jeśli niepuste), inaczej _regular_price
 *  - Aktualizuje _price i po wszystkim odświeża tabele lookup WooCommerce
 *  - Pokazuje statystyki per kategoria ile produktów brakowało _price (przed naprawą)
 * Po zakończeniu usuń ten blok.
 * ===================================================================== */
if (is_admin() && current_user_can('manage_options') && isset($_GET['repair_prices'])) {
    add_action('admin_init', function(){
        global $wpdb; $dry = empty($_GET['confirm']);
        $product_ids = $wpdb->get_col("SELECT p.ID FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON (pm.post_id=p.ID AND pm.meta_key='_price')
            WHERE p.post_type='product' AND p.post_status IN ('publish','pending','draft')
            AND (pm.meta_id IS NULL OR pm.meta_value='')");
        $updated=0;$skipped=0;$category_count=[];
        if ($product_ids){
            $ids_sql = implode(',', array_map('intval',$product_ids));
            $tax_rows = $wpdb->get_results("SELECT tr.object_id AS product_id, tt.term_id
                FROM {$wpdb->term_relationships} tr
                INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id=tr.term_taxonomy_id AND tt.taxonomy='product_cat'
                WHERE tr.object_id IN ($ids_sql)");
            foreach((array)$tax_rows as $r){ if(!isset($category_count[$r->term_id])) $category_count[$r->term_id]=0; $category_count[$r->term_id]++; }
            if(!$dry){
                foreach($product_ids as $pid){
                    $sale = get_post_meta($pid,'_sale_price',true);
                    $reg  = get_post_meta($pid,'_regular_price',true);
                    $val = ($sale !== '' && $sale !== false) ? $sale : $reg;
                    if($val !== '' && $val !== false){
                        update_post_meta($pid,'_price', wc_format_decimal($val));
                        if(function_exists('wc_delete_product_transients')) wc_delete_product_transients($pid);
                        $updated++; } else { $skipped++; }
                }
                if(function_exists('wc_update_product_lookup_tables')) wc_update_product_lookup_tables();
            } else { $skipped = count($product_ids); }
        }
        add_action('admin_notices', function() use($dry,$product_ids,$updated,$skipped,$category_count){
            $totalMissing = $product_ids?count($product_ids):0;
            echo '<div class="notice '.($dry?'notice-warning':'notice-success') .'"><p><strong>Naprawa _price:</strong> ';
            if($dry){
                echo 'TRYB PODGLĄDU – znaleziono <strong>'.esc_html($totalMissing).'</strong> produktów bez _price.';
                if($totalMissing){ $url = add_query_arg(['repair_prices'=>1,'confirm'=>1]); echo ' <a href="'.esc_url($url).'" style="font-weight:600;color:#b32d2e;">Wykonaj aktualizację</a>'; }
            } else {
                echo 'Zaktualizowano <strong>'.esc_html($updated).'</strong>, pominięto <strong>'.esc_html($skipped).'</strong>.';
            }
            echo '</p>';
            if($category_count){
                $parts=[]; foreach($category_count as $tid=>$cnt){ $t=get_term($tid,'product_cat'); $parts[] = esc_html(($t?$t->name:'#'.$tid).'='.$cnt); }
                echo '<p><em>Kategorie z brakami:</em> '.implode(', ',$parts).'</p>';
            }
            echo '</div>';
        });
    });
}
// ======================================================================

// ================== NARZĘDZIA DIAGNOSTYCZNE CEN ==================
// 1) Reindeks całej tabeli lookup + synchronizacja _price dla wszystkich produktów:
//    /wp-admin/?reindex_prices=1          -> podgląd ile produktów
//    /wp-admin/?reindex_prices=1&confirm=1 -> wykonanie
// 2) Debug cen w kategorii:
//    /wp-admin/?debug_price_cat=slug-kategorii
if (is_admin() && current_user_can('manage_options')) {
    // Reindeks
    if (isset($_GET['reindex_prices'])) {
        add_action('admin_init', function(){
            $dry = empty($_GET['confirm']);
            $args = [ 'post_type'=>'product', 'post_status'=>['publish'], 'fields'=>'ids', 'posts_per_page'=>-1 ];
            $ids = get_posts($args);
            $updated = 0; $total = count($ids);
            if (!$dry && $ids) {
                foreach($ids as $pid){
                    $sale = get_post_meta($pid,'_sale_price',true);
                    $reg  = get_post_meta($pid,'_regular_price',true);
                    $val = ($sale !== '' && $sale !== false) ? $sale : $reg;
                    if ($val !== '' && $val !== false){ update_post_meta($pid,'_price', wc_format_decimal($val)); $updated++; }
                }
                if (function_exists('wc_update_product_lookup_tables')) wc_update_product_lookup_tables();
            }
            add_action('admin_notices', function() use($dry,$total,$updated){
                echo '<div class="notice '.($dry?'notice-warning':'notice-success') .'"><p><strong>Reindeks cen:</strong> ';
                if ($dry) {
                    $url = add_query_arg(['reindex_prices'=>1,'confirm'=>1]);
                    echo 'TRYB PODGLĄDU – produktów: '.esc_html($total).'. <a href="'.esc_url($url).'" style="font-weight:600;color:#b32d2e;">Wykonaj reindeks</a>';
                } else {
                    echo 'Zaktualizowano _price dla '.esc_html($updated).' / '.esc_html($total).' produktów i przebudowano indeks.';
                }
                echo '</p></div>';
            });
        });
    }
    // Debug kategorii
    if (isset($_GET['debug_price_cat'])) {
        add_action('admin_init', function(){
            $slug = sanitize_title(wp_unslash($_GET['debug_price_cat']));
            $term = get_term_by('slug',$slug,'product_cat');
            $rows = [];
            if ($term && !is_wp_error($term)) {
                $q = new WP_Query([
                    'post_type'=>'product',
                    'posts_per_page'=>200,
                    'tax_query'=>[[ 'taxonomy'=>'product_cat','field'=>'term_id','terms'=>[$term->term_id], 'include_children'=>true ]]
                ]);
                while($q->have_posts()){ $q->the_post(); $pid=get_the_ID();
                    $reg=get_post_meta($pid,'_regular_price',true); $sale=get_post_meta($pid,'_sale_price',true); $price=get_post_meta($pid,'_price',true);
                    $rows[] = [ 'id'=>$pid,'title'=>get_the_title(),'reg'=>$reg,'sale'=>$sale,'price'=>$price ];
                }
                wp_reset_postdata();
            }
            add_action('admin_notices', function() use($term,$rows,$slug){
                echo '<div class="notice notice-info" style="max-height:400px;overflow:auto;">';
                if(!$term){ echo '<p><strong>Debug cen:</strong> brak kategorii '.esc_html($slug).'</p></div>'; return; }
                echo '<p><strong>Debug cen kategorii:</strong> '.esc_html($term->name).' (slug: '.esc_html($slug).') – '.count($rows).' produktów.</p>';
                if($rows){ echo '<table class="widefat"><thead><tr><th>ID</th><th>Tytuł</th><th>_regular_price</th><th>_sale_price</th><th>_price</th></tr></thead><tbody>'; foreach($rows as $r){
                    echo '<tr><td>'.esc_html($r['id']).'</td><td>'.esc_html($r['title']).'</td><td>'.esc_html($r['reg']).'</td><td>'.esc_html($r['sale']).'</td><td>'.esc_html($r['price']).'</td></tr>';
                } echo '</tbody></table>'; }
                echo '</div>';
            });
        });
    }
}
// ==============================================================

// === POWRÓT NA STRONĘ 1 JEŚLI NUMER /page/N/ PRZEKRACZA DOSTĘPNE (zapobiega 404 po zmianie filtrów / kategorii) ===
add_action('template_redirect', function(){
    if (!class_exists('WooCommerce')) return;
    global $wp_query;
    $paged = (int)get_query_var('paged');
    $is_product_ctx = (is_shop() || is_product_category() || is_product_tag() || (function_exists('is_post_type_archive') && is_post_type_archive('product')) || (isset($_GET['post_type']) && $_GET['post_type']==='product'));
    if (!$is_product_ctx) return;
    if ($paged < 2 && !is_404()) return; // nic do roboty
    $max = (int)$wp_query->max_num_pages;
    // Przypadki do redirectu: 1) mamy max i paged > max 2) 404 z segmentem /page/ oraz filtrami produktu
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    $has_page_segment = (bool)preg_match('#/page/\d+/#',$request_uri);
    $has_product_filter = isset($_GET['product_cat']) || isset($_GET['product_tag']);
    $need_redirect = false;
    if ($paged > 1 && $max && $paged > $max) { $need_redirect = true; }
    elseif (is_404() && $has_page_segment && $has_product_filter) { $need_redirect = true; }
    if (!$need_redirect) return;

    // Bazowy URL: jeśli kategoria w GET -> pozostaw query string bez /page/N/
    $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/');
    // Usuń segment /page/N/ z aktualnej ścieżki i zbuduj URL na bazie shop_url (bez ryzyka błędnej struktury)
    $params = $_GET; unset($params['paged']);
    $target = $shop_url;
    if (!empty($params)) { $target = add_query_arg(array_map('wp_unslash',$params), $target); }
    wp_safe_redirect($target, 302);
    exit;
});
// =====================================================================

