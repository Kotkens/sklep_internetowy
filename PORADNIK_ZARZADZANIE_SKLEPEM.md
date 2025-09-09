# Kompletny poradnik zarządzania sklepem (WordPress + WooCommerce)

Ten dokument ma pomóc właścicielowi / administratorowi sprawnie kontrolować sklep: produkty, zamówienia, klienci, treści prawne, moderacja ofert od użytkowników oraz elementy specyficzne motywu.

---
## 1. Podstawowa architektura
- **WordPress** – system zarządzania treścią (strony, użytkownicy, media).
- **WooCommerce** – warstwa e‑commerce (produkty, koszyk, płatności, wysyłka, zamówienia).
- **Motyw (krystian_k_sklep)** – wygląd + funkcje: ukrywanie przycisków dla niezalogowanych, strona "Moje zakupy", frontowa sprzedaż ("Sprzedawaj"), wishlisty, dopasowane komunikaty.
- **Wtyczki** – dodatkowe funkcjonalności (płatności, wysyłka, SEO, itp.).

---
## 2. Role i uprawnienia
| Rola | Przeznaczenie | Kluczowe możliwości |
|------|---------------|---------------------|
| Administrator | Pełna kontrola | Wszystko, także wtyczki i motyw |
| Shop Manager (jeśli używany) | Operacje sklepowe | Produkty, zamówienia, kupony, raporty |
| Customer (Klient) | Zakupy | Przeglądanie, kupowanie, konto |
| (Front-end sprzedający)* | Nadal Customer | Dodaje produkty przez stronę "Sprzedawaj" → *Oczekujące* |

> Produkty dodane z frontu ZAWSZE trafiają jako **Oczekujące** – wymagają publikacji w panelu.

---
## 3. Menu WooCommerce (panel admina)
- **WooCommerce → Ustawienia**: ogólne, płatności, wysyłka, konta i prywatność, e‑maile, integracje, zaawansowane.
- **Produkty**: lista, dodawanie, kategorie, tagi, atrybuty.
- **Zamówienia**: obsługa cyklu zamówień i statusów.
- **Kupony / Rabaty** (jeśli włączone): kody promocyjne.
- **Raporty / Analityka**: sprzedaż, produkty, stany magazynowe.

---
## 4. Produkty – cykl życia
1. **Dodanie** (ręcznie lub importem).
2. **Szkic / Oczekujące** – dopracowanie treści.
3. **Publikacja** – widoczne dla kupujących.
4. **Aktualizacje** – zmiana ceny, opisów, galerii.
5. **Wycofanie** – zmień status magazynowy na Brak lub ustaw widoczność katalogową na "Ukryty".

**Produkty z frontu (Sprzedawaj)**:
- Trafiają jako *pending* (Oczekujące publikacji).
- Znajdziesz je: Produkty → filtruj po statusie *Oczekujące*.
- Sprawdź: tytuł, kategorie, cena, stan magazynu, nadużycia/spam, obrazy.
- Popraw / uzupełnij → **Opublikuj**.

---
## 5. Kategorie, tagi, atrybuty
| Element | Cel | Kiedy używać |
|---------|-----|--------------|
| Kategorie | Hierarchiczne grupowanie | Główna nawigacja sklepu |
| Tagi | Swobodne etykiety | Mikrofiltrowanie / kampanie |
| Atrybuty | Cechy (kolor, rozmiar) | Warianty + filtry |

**Atrybut globalny** (Produkty → Atrybuty) oszczędza czas przy wielu produktach.

---
## 6. Stany magazynowe i ukrywanie OOS
- Włączone jest globalne ukrywanie produktów *Out of stock* (Brak w magazynie) z list.
- Sprawdzisz / zmienisz: WooCommerce → Ustawienia → Produkty → **Magazyn** → odznacz "Ukryj produkty bez stanu magazynowego", jeśli chcesz je pokazywać.
- Przy edycji produktu:
  - SKU (unikalny kod) – pomocny przy imporcie.
  - Zaznacz "Zarządzaj stanem" → podaj ilość.
  - Ilość = 0 → automatycznie "Brak" (niewidoczny w listingach przy obecnym ustawieniu).

---
## 7. Zamówienia – statusy
| Status | Znaczenie | Co zrobić |
|--------|-----------|-----------|
| Pending payment | Zamówione, brak płatności | Czeka / anuluj po czasie |
| On hold | Wstrzymane (np. oczekiwanie) | Zweryfikuj / zmień na Processing |
| Processing | Opłacone, do realizacji | Spakuj / wyślij |
| Completed | Zrealizowane | Koniec cyklu |
| Cancelled | Anulowane | Bez akcji (log) |
| Refunded | Zwrot pełny/częściowy | Potwierdź księgowo |
| Failed | Błąd płatności | Skontaktuj się / klient ponawia |

**Zmiana statusu**: lista Zamówień → kliknij numer → wybierz nowy status → Aktualizuj.

---
## 8. Płatności
- Konfiguracja: WooCommerce → Ustawienia → **Płatności**.
- Każdą metodę można: włączyć / wyłączyć / zmienić kolejność.
- Testy (sandbox) – upewnij się, że tryb produkcyjny jest aktywny po wdrożeniu.

---
## 9. Wysyłka
1. WooCommerce → Ustawienia → **Wysyłka**.
2. Strefy (np. Polska, UE, Świat) – dodaj metody (Kurier, Odbiór osobisty, Darmowa wysyłka, itp.).
3. Jeśli pojawia się brak stawek – sprawdź: adres klienta, strefy, warunki darmowej wysyłki.

---
## 10. Kupony / Promocje
- WooCommerce → Kupony → Dodaj.
- Typy: Stała kwota koszyka / procent / na produkt / darmowa wysyłka.
- Ograniczenia: minimalne kwoty, konkretne kategorie, liczba użyć globalnie / na użytkownika, data ważności.

---
## 11. Podatki (jeśli włączone)
- WooCommerce → Ustawienia → Podatek.
- Tabele stawek: Kraj / Kod pocztowy / Stawka / Nazwa / Uwzględnij w cenie.
- Monitoruj zmiany legislacyjne.

---
## 12. Klienci i konta
- Użytkownicy → Wszyscy użytkownicy: przegląd kont.
- Rola "Customer" tworzy się automatycznie przy rejestracji / pierwszym zamówieniu.
- Reset hasła: link "Edytuj" → wyślij reset z ekranu logowania (lub klient sam używa "Nie pamiętasz hasła".)

**Logowanie / rejestracja**: motyw blokuje dodawanie do koszyka dla niezalogowanych → zachęca do założenia konta.

---
## 13. Strony prawne i treści (specjalna obsługa motywu)
Panel: **Ustawienia → Treści prawne**.
- Edytujesz: Regulamin, Polityka poprawności, Kontakt.
- Motyw:
  - Gwarantuje ich publikację (jeśli placeholder – można naprawić z poziomu admina).
  - Omija filtry maintenance / coming soon, więc zawsze dostępne.
  - Szablon `page-legal.php` renderuje "surową" treść (bez niepożądanych wtrąceń).

**Kontakt**: możesz wstawić shortcode `[preomar_contact_form]` (obsługa maili już jest).

---
## 14. Funkcje motywu (wyróżniające)
| Funkcja | Opis |
|---------|------|
| Blokada koszyka dla gości | Przyciski kupna ukryte, dopóki użytkownik się nie zaloguje |
| Strona "Moje zakupy" | Shortcode wyświetla wcześniej kupione produkty z szybkimi przyciskami koszyka |
| Front "Sprzedawaj" | Formularz dodawania produktów → status *Oczekujące* |
| Wishlist / Obserwowane | Ikona serca (dla zalogowanych) |
| Ukrywanie OUT OF STOCK | Włączone globalnie (nie zaśmieca list) |
| Custom komunikaty koszyka | Usunięty link "Zobacz koszyk", uproszczone treści |

---
## 15. SEO – podstawa
- Strony kategorii: własny unikalny opis (min. 2 akapity) + słowa kluczowe naturalne.
- Produkty: unikalny tytuł + krótki opis bazujący na korzyściach.
- Obrazy: opisowe nazwy plików + tekst alternatywny.
- Unikaj duplikacji (kopiuj, modyfikuj – nie zostawiaj identycznych bloków).

---
## 16. Raporty i analityka
- WooCommerce → Raporty / Analityka: sprzedaż dzienna, najlepiej sprzedające, stany.
- Eksport CSV – analiza w arkuszach.
- Monitoruj: przychód / średnia wartość koszyka / liczba unikalnych klientów.

---
## 17. Czynności rutynowe
| Częstotliwość | Lista |
|---------------|-------|
| Codziennie | Sprawdź nowe zamówienia, produkty oczekujące, płatności nieudane |
| 2× w tygodniu | Aktualizacje wtyczek/motywu (po backupie), kontrola stanów magazynowych |
| Tygodniowo | Raport sprzedaży, optymalizacja cen/promocji |
| Miesięcznie | Backup testowy odtworzenia, przegląd kont użytkowników, czyszczenie starych draftów |
| Kwartalnie | Audyt SEO (duplikaty, brakujące opisy), test procesu zakupowego |

---
## 18. Kopie zapasowe i aktualizacje
- Backup przed większymi zmianami (plugin aktualizacje, wersja WP / WooCommerce).
- Testuj nową wersję WooCommerce (jeśli możliwe) na środowisku staging.
- Nigdy nie aktualizuj wielu dużych wtyczek jednocześnie bez backupu.

---
## 19. Wydajność
- Optymalizuj obrazy (kompresja automatyczna / WebP jeśli wtyczka dostępna).
- Unikaj zbędnych wtyczek (każda = potencjalne spowolnienie / wektor ataku).
- Cache stron (jeśli włączysz wtyczkę cache – wyklucz dynamiczne strony: koszyk, konto, checkout).

---
## 20. Bezpieczeństwo
- Silne hasła (menedżer haseł).
- Ogranicz liczbę kont admina.
- Regularnie aktualizuj core / motyw / wtyczki.
- Usuń nieużywane wtyczki i motywy.
- (Opcjonalnie) Włącz 2FA dla kont admina.

---
## 21. Tryb maintenance / dostępność krytycznych stron
Motyw ma ochronę: strony prawne + kluczowe strony WooCommerce (koszyk, checkout) są wyłączone spod blokady "coming soon" – użytkownik je zobaczy nawet przy częściowych ograniczeniach. Nie nadpisuj ręcznie głównego pliku motywu z tymi hakami bez konsultacji.

---
## 22. Moderacja front-end produktów (Sprzedawaj)
1. Produkty → filtruj status *Oczekujące*.
2. Otwórz produkt → sprawdź treść (brak spamu, język ok, realna cena).
3. Uzupełnij brakujące: kategorie, obrazek główny, SEO krótki opis.
4. Zmień status na **Opublikuj**.
5. Jeśli odrzucasz – możesz zmienić status na "Szkic" i skontaktować się z autorem (email profilu).

---
## 23. Najczęstsze problemy i szybkie rozwiązania
| Problem | Przyczyna | Rozwiązanie |
|---------|----------|-------------|
| Produkt nie pojawia się w liście | Brak stanu magazynu / status szkic / ukryty | Sprawdź widoczność, stan, publikację |
| Klient zgłasza brak maila potwierdzenia | Blokada na serwerze pocztowym / spam | Sprawdź log (jeśli wtyczka loguje), zasugeruj sprawdzenie spamu |
| Dodane z frontu nie widać | Status oczekujące | Opublikuj w panelu |
| Cena wariantu pusta | Nie ustawiono ceny w każdym wariancie | Edytuj każdy wariant → ustaw cenę |
| Zielony komunikat "Usunięto" przeszkadza | (W motywie wyłączone / ukryte) | Już obsłużone – brak reakcji potrzebnej |
| Zdjęcie obcina się | Zły format proporcji / brak regeneracji miniatur | Użyj 4:3 lub kwadrat, ewentualnie regeneruj miniatury |

---
## 24. Checklista publikacji produktu
[ ] Tytuł unikalny
[ ] Kategoria główna + ewent. podkategoria
[ ] Cena regularna / promocyjna (jeśli potrzeba)
[ ] Magazyn: ilość > 0 (jeśli ma być widoczny)
[ ] Obrazek główny + min. 2 w galerii
[ ] Krótki opis (CTA + korzyści)
[ ] Długi opis (parametry / specyfikacja)
[ ] Atrybuty i warianty (jeśli wersje)
[ ] SKU unikalne
[ ] Sprawdzone tłumaczenia / brak literówek

---
## 25. Słowniczek (skrótowo)
| Pojęcie | Znaczenie |
|---------|-----------|
| SKU | Unikalny identyfikator produktu |
| Pending / Oczekujące | Czeka na publikację / płatność |
| Out of stock | Brak stanu magazynowego |
| Atrybut | Cecha (np. kolor) używana w wariantach i filtrach |
| Wariant | Konkretna kombinacja atrybutów (np. Koszulka / M / Czerwona) |
| Short description | Krótki opis obok zdjęcia (zajawka sprzedażowa) |
| Regular price | Cena podstawowa |
| Sale price | Cena promocyjna |

---
## 26. Konserwacja techniczna (głębiej – opcjonalne)
- **Permalinki**: Ustawienia → Bezpośrednie odnośniki → Zapisz (gdy 404 na produktach).
- **Czyszczenie transients**: wtyczka optymalizacyjna (jeśli instalowana) – usuń stare cache.
- **Optymalizacja bazy**: sporadycznie (po backupie) narzędziem optymalizacyjnym.
- **Logi błędów**: /wp-content/debug.log (jeśli WP_DEBUG_LOG = true) – monitoruj powtarzające się warningi.

---
## 27. Co robić przy większej zmianie (np. zmiana cen całej kategorii)
1. Eksport produktów (CSV) → kopia.
2. Masowa edycja (Excel / Arkusz) – tylko kolumny cena / status.
3. Import z zaznaczeniem "Mapuj po SKU".
4. Test kilku losowych produktów.

---
## 28. Procedura przed sezonową kampanią
[ ] Lista produktów promowanych + nowe zdjęcia
[ ] Kupony / kody rabatowe ustawione (test jednego zamówienia)
[ ] Stany magazynowe aktualne
[ ] Strony kategorii – odświeżony opis / baner
[ ] Sprawdzone: checkout, płatność, e-mail potwierdzenia

---
## 29. Gdzie szukać pomocy
| Zakres | Pierwszy krok |
|--------|--------------|
| WooCommerce standard | Dokumentacja WooCommerce / Forum |
| Płatności | Panel dostawcy płatności / logi webhooków |
| Wysyłka | Konfiguracja stref / wtyczka wysyłkowa |
| Błędy frontu | Console przeglądarki + debug.log |
| Motyw | Plik `functions.php` (sekcje oznaczone komentarzami) |

---
## 30. Skrócona lista codzienna (drukuj / przypnij)
- [ ] Nowe zamówienia → zmień status / przygotuj wysyłkę
- [ ] Produkty oczekujące z frontu → ocena + publikacja / odrzucenie
- [ ] Płatności nieudane → kontakt z klientem
- [ ] Stan krytyczny (0 lub < próg) ważnych produktów → uzupełnij
- [ ] Szybkie spojrzenie na raport sprzedaży (tendencja)

---
**Masz nowe wymaganie?** Dopisz notatkę w osobnym pliku lub zgłoś do developera – unikaj edycji core motywu bez wiedzy co robi dana sekcja.

Koniec poradnika.
