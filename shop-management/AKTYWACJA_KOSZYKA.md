# 🚀 NATYCHMIASTOWA AKTYWACJA KOSZYKA

## Opcja 1: Automatyczna (przy aktywacji tematu)
1. Przejdź do **Wygląd → Motywy**  
2. **Aktywuj** temat `PreoMarket` (krystian_k_sklep)
3. Poczekaj 5 sekund na automatyczną konfigurację
4. Sprawdź `/koszyk/` - powinno działać!

## Opcja 2: Manualna (natychmiastowa)
Jeśli automatyczna konfiguracja nie zadziałała:

### Krok 1: Dodaj kod tymczasowy
Skopiuj ten kod na **KONIEC** pliku `functions.php`:

```php
// TYMCZASOWY KOD - usuń po konfiguracji
add_action('init', function() {
    if (get_transient('cart_setup_done')) return;
    
    // Permalinki
    if (empty(get_option('permalink_structure'))) {
        update_option('permalink_structure', '/%postname%/');
    }
    
    // Strona koszyka
    $cart_page = get_page_by_path('koszyk');
    if (!$cart_page) {
        $cart_page_id = wp_insert_post([
            'post_title' => 'Koszyk',
            'post_name' => 'koszyk',
            'post_content' => '[woocommerce_cart]',
            'post_status' => 'publish',
            'post_type' => 'page',
            'meta_input' => ['_wp_page_template' => 'page-koszyk.php']
        ]);
        if (class_exists('WooCommerce')) {
            update_option('woocommerce_cart_page_id', $cart_page_id);
        }
    }
    
    flush_rewrite_rules();
    set_transient('cart_setup_done', true, 3600);
    
    if (is_admin()) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success"><p>✅ Koszyk skonfigurowany! <a href="' . home_url('/koszyk/') . '" target="_blank">Sprawdź /koszyk/</a></p></div>';
        });
    }
}, 1);
```

### Krok 2: Zapisz i odśwież
1. **Zapisz** plik `functions.php`
2. **Odśwież** dowolną stronę WordPress
3. **Sprawdź** czy działa: `/koszyk/`
4. **Usuń** kod tymczasowy z `functions.php`

## Opcja 3: Permalinki ręcznie
Jeśli nadal nie działa:

1. **Ustawienia → Bezpośrednie odnośniki**
2. Wybierz **"Nazwa wpisu"**  
3. **Zapisz zmiany**
4. Sprawdź `/koszyk/`

## ✅ Sprawdzenie czy działa:

### Test 1: Podstawowy URL
- Wejdź na: `http://twoja-strona.com/koszyk/`
- Powinno wyświetlać stronę koszyka w stylu Allegro

### Test 2: Link w header
- Kliknij "Koszyk" w górnym menu
- Powinno przekierować na `/koszyk/`

### Test 3: Przekierowania  
- Wejdź na starą stronę: `http://twoja-strona.com/?page_id=19`
- Powinno automatycznie przekierować na `/koszyk/`

### Test 4: WooCommerce
- Dodaj produkt do koszyka
- Powinno przekierować na `/koszyk/`

## 🐛 Rozwiązywanie problemów:

### Problem: 404 na /koszyk/
**Rozwiązanie**: Ustawienia → Bezpośrednie odnośniki → Zapisz zmiany

### Problem: Przekierowuje na stronę domyślną WooCommerce
**Rozwiązanie**: WooCommerce → Ustawienia → Zaawansowane → Strony → Koszyk: "Koszyk"

### Problem: Szablon nie wygląda jak Allegro
**Rozwiązanie**: Sprawdź czy plik `page-koszyk.php` istnieje w folderze tematu

## 📞 Status konfiguracji:

Dodaj `?debug_cart=1` do URL (tylko dla administratorów):
```
http://twoja-strona.com/?debug_cart=1
```

Zobaczysz debug info na dole strony:
- Cart Page: OK/MISSING
- Permalinks: OK/PLAIN  
- WooCommerce: ID
- Configuration: OK/NEEDS SETUP

---

**Po pomyślnej konfiguracji URL `/koszyk/` będzie działać w stylu Allegro! 🎉**
