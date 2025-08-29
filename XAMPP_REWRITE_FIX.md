# 🚨 NAPRAWA PERMALINKÓW - XAMPP mod_rewrite

## Problem: Permalinki nie działają w XAMPP

### ROZWIĄZANIE KROK PO KROK:

#### KROK 1: Włącz mod_rewrite w Apache
1. **Otwórz XAMPP Control Panel**
2. **Zatrzymaj Apache** (Stop)
3. **Kliknij "Config" przy Apache → "Apache (httpd.conf)"**
4. **Znajdź linię:** 
   ```
   #LoadModule rewrite_module modules/mod_rewrite.so
   ```
5. **Usuń # z początku** (odkomentuj):
   ```
   LoadModule rewrite_module modules/mod_rewrite.so
   ```
6. **Zapisz plik**
7. **Uruchom Apache** (Start)

#### KROK 2: Sprawdź AllowOverride
W tym samym pliku `httpd.conf` znajdź:
```
<Directory "C:/xampp/htdocs">
    AllowOverride None
</Directory>
```

**Zmień na:**
```
<Directory "C:/xampp/htdocs">
    AllowOverride All
</Directory>
```

#### KROK 3: Restartuj Apache
1. **XAMPP Control Panel → Apache → Stop**
2. **Apache → Start**

#### KROK 4: Sprawdź permalinki WordPress
1. **WordPress Admin → Ustawienia → Bezpośrednie odnośniki**
2. **Wybierz "Nazwa wpisu"**  
3. **Zapisz zmiany**

#### KROK 5: Test
```
http://localhost/wordpress/koszyk/
```

---

## ALTERNATYWNIE: Prostsze rozwiązanie

Jeśli mod_rewrite nie działa, użyj funkcji `parse_request` w WordPress:

### Dodaj do functions.php:

```php
// Backup solution - if mod_rewrite doesn't work
add_action('parse_request', function($wp) {
    if ($wp->request === 'koszyk') {
        $wp->query_vars['pagename'] = 'koszyk';
        $wp->matched_rule = 'koszyk/?$';
        $wp->matched_query = 'pagename=koszyk';
    }
});
```

To wymusi obsługę URL `/koszyk/` nawet bez mod_rewrite.

---

## Quick Fix: Index.php fallback

Jeśli nic nie pomaga, dodaj to do głównego `index.php` WordPress:

```php
// Quick cart redirect
if ($_SERVER['REQUEST_URI'] === '/wordpress/koszyk/' || $_SERVER['REQUEST_URI'] === '/wordpress/koszyk') {
    $_GET['pagename'] = 'koszyk';
    $_REQUEST['pagename'] = 'koszyk';
}
```

**WYBIERZ JEDNĄ Z OPCJI I PRZETESTUJ!**
