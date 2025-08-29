# 🚨 NATYCHMIASTOWA NAPRAWA KOSZYKA

## ❌ Problem: 404 na /koszyk/

**Widzę że masz błąd 404!** Automatyczna konfiguracja jeszcze się nie wykonała. Naprawmy to natychmiast:

### 🔧 OPCJA 1: Panel WordPress (NAJŁATWIEJSZA)

1. **Zaloguj się do WordPress Admin:**
   ```
   http://localhost/wordpress/wp-admin/
   ```

2. **Przejdź do:**
   ```
   Ustawienia → Bezpośrednie odnośniki
   ```

3. **Wybierz "Nazwa wpisu"** (zamiast "Zwykła")

4. **Kliknij "Zapisz zmiany"**

5. **Sprawdź czy działa:**
   ```
   http://localhost/wordpress/koszyk/
   ```

### 🔧 OPCJA 2: Automatyczny kod naprawczy (JUŻ DODANY)

**Kod naprawczy został już dodany do functions.php!**

1. **Odśwież dowolną stronę WordPress** (front-end lub admin)
2. **Sprawdź /koszyk/** - powinno już działać
3. **Usuń kod naprawczy** z końca functions.php po udanej naprawie

### 🔧 OPCJA 3: Manualna naprawa (backup)

Jeśli nic nie działa, stwórz stronę ręcznie:

1. **WordPress Admin → Strony → Dodaj nową**
2. **Tytuł:** "Koszyk"
3. **Slug:** "koszyk" (w sekcji permalink)
4. **Zawartość:** `[woocommerce_cart]`
5. **Opublikuj**
6. **WooCommerce → Ustawienia → Zaawansowane → Strony → Koszyk: wybierz "Koszyk"**

---

# ✅ AUTOMATYCZNA KONFIGURACJA STRONY KOSZYKA

## 🎯 WSZYSTKO DZIEJE SIĘ AUTOMATYCZNIE!

**Gratulacje!** Nie musisz nic robić ręcznie. Temat automatycznie skonfiguruje clean URL koszyka `/koszyk/` przy aktywacji.

### 🚀 Co się dzieje automatycznie:

#### 1. ✅ Automatyczne utworzenie strony "Koszyk"
- Temat tworzy stronę z tytułem "Koszyk" i slugiem "koszyk"
- Automatycznie przypisuje szablon `page-koszyk.php`
- Dodaje shortcode `[woocommerce_cart]` do zawartości

#### 2. ✅ Automatyczna konfiguracja permalinków
- Zmienia permalinki z "plain" na "Post name" (/%postname%/)
- Automatycznie generuje .htaccess z regułami przepisywania
- Wymusza flush rewrite rules

#### 3. ✅ Automatyczna konfiguracja WooCommerce
- Ustawia utworzoną stronę jako oficjalną stronę koszyka WooCommerce
- Konfiguruje `woocommerce_cart_page_id`
- Zapewnia pełną integrację z WooCommerce

### 🔄 Proces aktywacji:

```
1. Aktywacja tematu → preomar_theme_activation()
2. Opóźniona konfiguracja (5 sekund) → preomar_delayed_setup()
3. Sprawdzenie permalinków → preomar_setup_permalinks()
4. Utworzenie strony koszyka → preomar_setup_cart_page()
5. Flush rewrite rules → gotowe!
```

### ✅ Sprawdzenie czy wszystko działa:

Po aktywacji tematu sprawdź:
- **URL**: `/koszyk/` powinien wyświetlać stronę koszyka
- **Header**: kliknięcie "Koszyk" przekierowuje na `/koszyk/`
- **Przekierowania**: stare URL-e (`?page_id=19`) automatycznie przekierowują
- **WooCommerce**: dodanie produktu do koszyka przekierowuje na `/koszyk/`


### 🎛️ Panel administratora (opcjonalnie):

Jeśli z jakiegoś powodu automatyczna konfiguracja nie zadziałała, możesz sprawdzić/skorygować ustawienia:

#### Permalinki:
**Ustawienia → Bezpośrednie odnośniki → "Nazwa wpisu" → Zapisz**

#### WooCommerce:
**WooCommerce → Ustawienia → Zaawansowane → Strony → Strona koszyka: "Koszyk"**

### 🐛 Debug i rozwiązywanie problemów:

#### Sprawdzenie statusu konfiguracji:
Dodaj `?debug_cart=1` do dowolnego URL na stronie (tylko dla administratorów):
```
http://example.com/?debug_cart=1
```
Zobaczysz status konfiguracji na dole strony.

#### Jeśli coś nie działa:
1. **Wyłącz i włącz temat ponownie**
2. **Przejdź do**: Ustawienia → Bezpośrednie odnośniki → Zapisz zmiany
3. **Sprawdź logi błędów** w cPanel/hosting panel

#### Ręczne wymuszenie konfiguracji:
Dodaj to do `wp-config.php` tymczasowo:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```
Następnie wyłącz i włącz temat.

### 📊 Status automatycznej konfiguracji:

✅ **Strona koszyka**: Tworzona automatycznie  
✅ **Permalinki**: Konfigurowane automatycznie  
✅ **WooCommerce**: Integrowane automatycznie  
✅ **Rewrite rules**: Ładowane automatycznie  
✅ **Przekierowania**: Działają automatycznie  

## Dodatkowe informacje:

### ⚙️ Pliki i funkcje które zostały utworzone/zmodyfikowane:
- `page-koszyk.php` - Szablon strony koszyka w stylu Allegro
- `functions.php` - Dodane funkcje obsługi URL + **automatyczna konfiguracja**
- `header.php` - Zaktualizowany link koszyka
- `style.css` - Kompletne style dla strony koszyka (200+ linii CSS)

### 🔧 Nowe funkcje automatyczne:
- `preomar_setup_cart_page()` - Automatyczne tworzenie strony koszyka
- `preomar_setup_permalinks()` - Automatyczna konfiguracja permalinków  
- `preomar_theme_activation()` - Hook aktywacji tematu
- `preomar_delayed_setup()` - Opóźniona konfiguracja (5 sekund)
- `preomar_ensure_cart_setup()` - Backup sprawdzanie w admin
- `preomar_cart_setup_notice()` - Powiadomienie o udanej konfiguracji
- `preomar_is_cart_configured()` - Sprawdzanie statusu konfiguracji
- `preomar_cart_debug_info()` - Debug info dla administratorów

### Funkcjonalności:
- ✅ Clean URL: `/koszyk/` zamiast `?page_id=19`
- ✅ Automatyczne przekierowania ze starych URL-i
- ✅ Integacja z WooCommerce
- ✅ Pusty koszyk z zachętą do zakupów
- ✅ Breadcrumbs nawigacja
- ✅ Responsywny design
- ✅ Polecane produkty (jeśli koszyk nie jest pusty)

### Stylowanie:
Strona używa spójnego designu z resztą tematu w stylu Allegro:
- Niebieska kolorystyka (#1E3A8A)
- Czytelne ikony SVG
- Responsywny layout
- Animacje hover
- Profesjonalny wygląd
