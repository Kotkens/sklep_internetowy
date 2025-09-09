# Poradnik: Dodawanie kategorii i produktów w panelu WordPress (WooCommerce)

## 1. Logowanie
1. Wejdź: /wp-admin
2. Zaloguj się (Administrator lub Shop Manager).

## 2. Kategorie produktów
Menu: Produkty → Kategorie.
1. Wpisz *Nazwa* (np. Elektronika).
2. *Slug* zostaw pusty (utworzy się automatycznie) albo wpisz bez spacji/PL znaków.
3. *Rodzic* wybierz tylko jeśli to podkategoria.
4. *Opis* – wyświetlany w górze strony kategorii (opcjonalnie, ale pomaga w SEO).
5. *Miniatura* – kliknij „Wgraj/Przypisz obrazek”.
6. Zapisz. Powtórz dla podkategorii.
7. Kolejność wyświetlania: przeciągnij (jeśli włączone sortowanie) lub użyj kolumny „ID / Nazwa” (domyślnie alfabetycznie).

## 3. Nowy produkt (prost y)
Menu: Produkty → Dodaj nowy.
1. *Tytuł produktu*.
2. *Opis główny* (duże pole – może zawierać formatowanie, listy, zdjęcia, video).
3. Sekcja „Dane produktu” (domyślnie „Produkt prosty”).
4. *Cena regularna* (Cena) i opcjonalnie *Cena promocyjna*.
5. Zakładka *Magazyn*: ustaw SKU (unikalny kod), zaznacz „Zarządzaj stanem magazynowym”, podaj ilość.
6. Zakładka *Wysyłka*: waga + wymiary (jeśli konieczne do metod wysyłki / kurierów).
7. *Krótki opis produktu* – pojawia się obok zdjęcia (zwięzłe korzyści / najważniejsze cechy).
8. Po prawej: przypisz *Kategorie* (możesz tworzyć nowe – link „+ Dodaj nową kategorię”).
9. Dodaj *Tagi* (opcjonalne – frazy wyszukiwane, oddziel przecinkami).
10. Ustaw *Obrazek produktu* + *Galeria produktu* (kilka zdjęć dodatkowych).
11. Status: „Opublikuj” (lub „Szkic” jeśli chcesz dokończyć później).

## 4. Cena promocyjna
1. Wpisz cenę promocyjną → (opcjonalnie) kliknij „Harmonogram” i ustaw daty od / do.
2. Po zakończeniu okresu system wróci do ceny regularnej.

## 5. Magazyn
- *SKU* – unikalny identyfikator (bez spacji – np. PROD-001). Pomaga przy imporcie i synchronizacji.
- Zaznacz „Zarządzaj stanem” + ilość.
- *Próg niskiego stanu* – opcjonalny (powiadomienie / badge jeśli wdrożone).
- *Status magazynowy*: W magazynie / Brak w magazynie (jeśli ilość = 0 i nie zezwalasz na zamówienia wsteczne).

## 6. Zdjęcia – zalecenia
- JPG dla zdjęć, PNG / SVG dla ikon / grafik płaskich.
- Wymiary minimum: 800×800 (kwadrat) lub 1200 szer. w proporcji 4:3.
- Nazwa pliku czytelna (np. kurtka-softshell-niebieska.jpg) – drobna pomoc dla SEO.

## 7. Atrybuty / produkt z wariantami
1. Zmień „Produkt prosty” → „Produkt z wariantami”.
2. Zakładka *Atrybuty*: wybierz globalny (jeśli utworzony w Produkty → Atrybuty) lub „Niestandardowy atrybut produktu”.
3. Dodaj wartości (oddziel pionową kreską | np. Czerwony | Niebieski | Zielony) → zaznacz „Używane dla wariantów” → Zapisz atrybuty.
4. Zakładka *Warianty*: „Dodaj warianty ze wszystkich atrybutów”. Zapisz.
5. Klikaj każdy wariant: ustaw cenę, opcjonalnie własne zdjęcie i stan magazynowy.
6. Opublikuj / zaktualizuj.

## 8. Widoczność & status
- Panel „Opublikuj”: *Widoczność*: Publiczny / Prywatny / Hasłem.
- *Katalogowa widoczność* (po lewej w „Dane produktu” → Zaawansowane / w zależności od wersji): katalog + wyszukiwanie / tylko katalog / tylko wyszukiwanie / ukryty.

## 9. Duplikowanie produktu
- Produkty → Lista → Najedź → „Duplikuj” (lub „Zduplikuj”). Edytuj różnice i opublikuj.

## 10. Edycja istniejącego
- Produkty → wyszukaj → kliknij tytuł → zmień → „Zaktualizuj”.

## 11. Usuwanie
- „Do kosza” (można przywrócić). Trwałe usunięcie: Kosz → „Usuń na stałe”.

## 12. Szybka edycja
- Najedź na produkt → „Szybka edycja”: zmiana tytułu, slug, kategorii, statusu, widoczności.

## 13. Filtrowanie / wyszukiwanie
- Nad listą: filtr po dacie, kategorii, stanie magazynu.
- Szukaj po tytule / SKU (jeśli Twój motyw/wtyczka to wspiera).

## 14. Import (CSV)
Produkty → Importuj.
Minimalne kolumny (nagłówki):
- name
- sku
- regular_price
- description (opcjonalnie short_description)
- categories (np. "Elektronika > Telefony")
- images (URL-e rozdzielone przecinkami lub nazwy jeśli już w bibliotece)
- stock / stock_quantity
Mapuj pola → Importuj.
Aktualizacja istniejących: zaznacz „Użyj SKU do dopasowania istniejących”.

Eksport: Produkty → Eksportuj (np. kopia zapasowa przed masową zmianą).

## 15. SEO podstawy
- Tytuł z główną frazą (bez upychania słów kluczowych).
- Krótki opis = CTA + 2–3 korzyści.
- Opis główny: struktura nagłówków (H2/H3), listy, parametry, FAQ.
- Kategorie: dodaj unikalny opis (min. 2 akapity) + jeśli trzeba grafika nagłówka.

## 16. Najczęstsze błędy
| Problem | Szybkie rozwiązanie |
|---------|---------------------|
| Produkt niewidoczny w sklepie | Sprawdź: Widoczność katalogowa, stan magazynowy >0, publikacja. |
| Cena się nie pokazuje | Brak ceny w wariancie – ustaw dla każdego wariantu. |
| Brak zdjęcia miniatury | Ustaw „Obrazek produktu”. |
| Złe kolejności kategorii | Użyj przeciągania w kategoriach lub popraw nazwy. |
| Nie działa wyszukiwarka po SKU | Zainstaluj wtyczkę rozszerzającą wyszukiwanie (opcjonalnie). |

## 17. Produkty z frontu (zakładka „Sprzedawaj”)
- Nowe zgłoszenia trafiają jako „Oczekujące”.
- Panel: Produkty → Status „Oczekujące” → Sprawdź → Opublikuj / Odrzuć.
- Możesz edytować cenę, przypisać do właściwych kategorii i dodać lepsze zdjęcia.

## 18. Checklista przed publikacją
[ ] Poprawny tytuł
[ ] Kategoria + podkategoria
[ ] Cena (i promocyjna jeśli jest)
[ ] Zdjęcie główne + min. 2 w galerii
[ ] Opis krótki + pełny
[ ] Stan magazynowy
[ ] Atrybuty / warianty (jeśli dotyczy)
[ ] SKU unikalne
[ ] Brak placeholderowego tekstu typu “lorem ipsum”

## 19. Przyspieszenie pracy
- Duplikuj podobne produkty zamiast tworzyć od zera.
- Używaj spójnych SKU (np. KAT-SUB-0001).
- Dodawaj atrybuty globalne (Produkty → Atrybuty), gdy wiele produktów używa tych samych (np. Rozmiar, Kolor). To skraca czas przy wariantach.
- Równomierne wymiary zdjęć = lepsza siatka.

## 20. Wsparcie
Jeśli coś nie działa: 
1. Odśwież stronę / wyczyść cache przeglądarki.
2. Sprawdź czy masz uprawnienia (rola).
3. Zobacz czy wtyczka WooCommerce jest aktywna.
4. Zgłoś do administratora – podaj URL produktu i krótki opis.

---
Krótko: Najpierw kategorie, potem produkty, pamiętaj o zdjęciu, cenie, stanie magazynu i opisie skróconym. To wystarczy, by produkt działał poprawnie w sklepie.
