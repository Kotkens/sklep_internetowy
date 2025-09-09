# Poradnik obsługi sklepu (dla właściciela)
Zwięzły przewodnik – tylko realnie potrzebne rzeczy do codziennej obsługi i prostych zmian wyglądu (slider, logo, stopka).

---
## 1. Co masz?
Masz sklep na WordPress + WooCommerce. Panel logowania: /wp-admin (login + hasło od nas). Po zalogowaniu zobaczysz menu po lewej.

Najważniejsze sekcje dla Ciebie:
- WooCommerce → Zamówienia
- Produkty → Wszystkie / Dodaj nowy
- Produkty → Kategorie
- WooCommerce → Raporty / Analityka
- Użytkownicy (opcjonalnie)

---
## 2. Codzienna checklista (5 minut)
[ ] Sprawdź nowe zamówienia (Zamówienia)
[ ] Zmień status na "Przetwarzanie", jeśli opłacone
[ ] Zobacz czy są produkty oczekujące (jeśli użytkownicy mogą dodawać)
[ ] Szybki rzut oka na stany magazynowe kluczowych produktów

---
## 3. Dodawanie produktu (najczęściej używane)
1. Wejdź: Produkty → Dodaj nowy.
2. Wpisz tytuł (krótki i jasny).
3. Dodaj krótki opis (sprzedażowy, kilka zdań) – wyświetla się obok zdjęcia.
4. W treści na dole możesz dodać dłuższy opis (opcjonalnie).
5. Po prawej: wybierz kategorię (zaznacz przynajmniej jedną).
6. U dołu: Ustaw cenę (Regularna; Promocyjna – jeśli chcesz obniżkę).
7. Zakładka Magazyn: wpisz SKU (może być prosty np. PROD-001) + ilość > 0.
8. Ustaw zdjęcie główne (dobre, wyraźne) + dodatkowe w galerii.
9. Kliknij Opublikuj.

Gotowe – produkt pojawi się w sklepie, jeśli ma stan magazynowy.

---
## 4. Edycja istniejącego produktu
Produkty → Najedź na nazwę → Edytuj → zmień (cena, opis, zdjęcia) → Zaktualizuj.

Zmiana ceny promocyjnej: wpisz niższą cenę w "Cena promocyjna" – system pokaże przekreśloną starą.

---
## 5. Kategorie
Produkty → Kategorie → Dodaj nową.
- Nazwa: to co widzi klient.
- Slug (może się wygenerować automatycznie – zostaw).
- Możesz ustawić kategorię nadrzędną (tworząc drzewko).

Przeciągaj, żeby porządkować (wtyczka nie zawsze – podstawowo alfabetycznie). Najważniejsze: nie dubluj nazw.

---
## 6. Zamówienia – szybka obsługa
WooCommerce → Zamówienia.

Kolory statusów:
- Szare: Oczekujące (jeszcze brak płatności)
- Żółte: Wstrzymane
- Niebieskie: Przetwarzanie (pracujesz nad nim)
- Zielone: Zakończone
- Czerwone: Anulowane / Nieudane

Co robisz:
1. Klikasz zamówienie.
2. Sprawdzasz dane klienta + produkty.
3. Jeśli wszystko ok i opłacone → przygotowujesz wysyłkę.
4. Po wysyłce zmieniasz status na "Zakończone".

---
## 7. Stany magazynowe (ważne)
Przy edycji produktu:
- Zaznacz "Zarządzaj stanem magazynowym".
- Wpisz ilość.
- Ilość 0 = produkt znika z list (mamy ustawione ukrywanie braków).

Masowa korekta?
Produkty → zaznacz kilka → Edycja zbiorcza → Ustaw magazyn.

---
## 8. Produkty dodane przez użytkowników (jeśli funkcja włączona)
Strona "Sprzedawaj" pozwala użytkownikom zgłaszać produkty.
- Trafiają jako status: Oczekujące.
- Panel: Produkty → filtruj po "Oczekujące".
- Sprawdź tytuł / cenę / zdjęcie → Opublikuj.
- Jeśli śmieć → Zmień na Szkic lub Usuń.

---
## 9. Promocje / obniżki
Szybka obniżka pojedynczego produktu: Edytuj → Cena promocyjna.
Większe akcje (np. kod rabatowy): WooCommerce → Kupony → Dodaj kupon.
- Kod (np. WIOSNA10)
- Typ: Procent / stała kwota
- Limit użyć (opcjonalnie) – żeby ktoś nie nadużywał

---
## 10. Obrazy – szybkie zasady
- Format JPG (zdjęcia) / PNG (grafiki z przezroczystością) / WebP (jeśli dostępne).
- Rozmiar: nie przesadzaj (np. 1200px szerokości wystarczy w większości).
- Nazwa pliku: opisowa (np. koszulka-czerwona-front.jpg).

---
## 11. Klienci i konta
Zakładka Użytkownicy.
- Nie zmieniaj ról bez potrzeby.
- Reset hasła – klient może sam ("Nie pamiętasz hasła").
- Usuwając użytkownika – zdecyduj czy jego treści mają zostać przeniesione.

---
## 12. Raporty / wyniki
WooCommerce → Raporty lub Analityka.
Sprawdzaj:
- Sprzedaż wg dnia / tygodnia
- Najlepiej sprzedające produkty
- Produkty z małym stanem

Raz w tygodniu wystarczy.

---
## 13. Aktualizacje i bezpieczeństwo (prosto)
- Nie klikaj "Aktualizuj wszystko" bez backupu.
- Gdy zobaczysz dużo aktualizacji – zapisz listę, zrób kopię plików/bazy (jeśli masz narzędzie), dopiero wtedy aktualizuj.
- Używaj mocnych haseł.
- Nie twórz dodatkowych kont admina bez powodu.

---
## 14. Najczęstsze pytania
| Pytanie | Odpowiedź |
|---------|-----------|
| Produkt zniknął | Ma 0 sztuk magazynu – uzupełnij ilość |
| Nie widzę zgłoszonego produktu | Jest w "Oczekujące" |
| Klient nie dostał maila | Sprawdzić spam; jeśli wiele przypadków – zgłoś do wsparcia |
| Chcę ukryć produkt tymczasowo | Edytuj → Status magazynu = 0 lub ustaw widoczność katalogu "Ukryty" |
| Chcę masowo zmienić ceny | Eksport → edycja w Excelu → import (zapytaj przed pierwszą taką operacją) |

---
## 15. Co robić gdy coś "nie działa"
1. Odśwież stronę w trybie prywatnym (czasem cache).
2. Sprawdź czy problem dotyczy wszystkich produktów / tylko jednego.
3. Sprawdź czy niedawno coś zmieniałeś (cena, status, kategorie).
4. Jeśli krytyczne – zanotuj godzinę, zrób zrzut ekranu i prześlij wsparciu.

---
## 16. Twoje priorytety (podsumowanie)
1. Dostępność towaru (ilości > 0)
2. Szybka reakcja na zamówienia
3. Dobre zdjęcia + jasne ceny
4. Proste promocje zamiast chaosu
5. Regularnie: małe, częste poprawki – zamiast wielkich rewolucji

---
## 17. Mini słowniczek
- SKU – Twój wewnętrzny kod produktu
- Status "Przetwarzanie" – zamówienie opłacone, pracujesz nad realizacją
- Status "Zakończone" – wysłane / gotowe
- Oczekujące – czeka (płatność lub publikacja)

---
## 18. Lista przed większą kampanią
[ ] Sprawdzone stany magazynowe
[ ] Ceny i promocje ustawione
[ ] Bannery / grafiki gotowe
[ ] Test zakupu wykonany (do końca)
[ ] Pierwsze zamówienie próbne – e‑mail doszedł

---
## 19. Gdzie pytać o pomoc
- Pytania sprzedażowe: panel WooCommerce (dane zamówienia)
- Problemy techniczne: zgłoszenie do developera (krótki opis + zrzut ekranu)
- Niepewność przy imporcie / eksporcie: nie rób sam pierwszy raz – zgłoś.

---
## 20. Najkrótsza esencja (druk do biurka)
Codziennie: zamówienia + stany.
Tygodniowo: raport.
Nowy produkt: tytuł, cena, zdjęcie, kategoria, ilość.
Promocja: ustaw cenę promocyjną lub kupon.
Nie działa? – notatka + zrzut + kontakt.

---
## 21. Wygląd: slider, logo, stopka (edycja wizualna)

### 21.1 Hero Slider (baner na stronie głównej)
Panel: "Hero Slides" w menu administracyjnym.

Każdy slajd = osobny wpis:
- Obrazek wyróżniający – główna grafika slajdu.
- Pola formularza:
	- Nagłówek – część przed pogrubieniem
	- Wyróżnione słowo – pojawi się w <strong></strong>
	- Discount – np. -30% (opcjonalne)
	- Opis – Enter = druga linia
	- Przycisk (tekst + URL) – zostaw puste aby ukryć
	- Gradient tła – pełny CSS `linear-gradient(...)` (opcjonalnie; jest domyślny)
	- Aktywny – odznacz aby ukryć slajd

Kolejność: Quick Edit → pole "Kolejność" (im niższa liczba tym wcześniej). Nowe slajdy domyślnie aktywne. Brak aktywnych = pokażą się 3 domyślne (awaryjne) slajdy.

Checklist:
[ ] Nowy slajd → Dodaj → ustaw obrazek → wypełnij pola → Opublikuj.
[ ] Ukryć → edytuj → odznacz "Aktywny" → Zaktualizuj.
[ ] Zmienić kolejność → Quick Edit → liczba → Zaktualizuj.

### 21.2 Logo sklepu
Panel: Wygląd → Dostosuj → Tożsamość witryny → Logo.
- Obsługiwane: PNG / SVG (jeśli włączone), WebP.
- Alt: jeśli w bibliotece brak alt – użyje się nazwy strony.
- Strona główna: logo bez linku (lepsza semantyka). Podstrony: logo = link do strony głównej.

### 21.3 Tekst stopki (copyright)
Panel: Wygląd → Dostosuj → Stopka.
- Pole "Tekst praw autorskich" – możesz użyć `{YEAR}` aby wstawić bieżący rok.
- Dozwolone proste znaczniki (np. link `<a>`).
Przykład: `© {YEAR} PreoMarket. Wszystkie prawa zastrzeżone.`

### 21.4 Favicon (ikona witryny)
Panel: Wygląd → Dostosuj → Tożsamość witryny → Ikona witryny.

Instrukcja:
- Wybierz kwadratowy obraz minimum 512×512 px (PNG z przezroczystością lub prosty WebP). 
- Zapisz (Opublikuj). WordPress sam wygeneruje mniejsze rozmiary.
- Jeśli NIE ustawisz ikony – motyw automatycznie użyje pliku `logo_strona.png` jako faviconu (fallback).
- Aby wrócić do fallbacku: usuń ikonę z Customizera i zapisz.
- Po zmianie odśwież w trybie prywatnym / wyczyść cache przeglądarki (favicon bywa cachowany).

Wskazówki: unikaj nadmiaru detali – w 16×16 i 32×32 proste kształty wyglądają najlepiej.

### 21.5 Szybka ściąga wizualna
[ ] Dodać slajd – Hero Slides → Dodaj nowy.
[ ] Edytować/ukryć – otwórz slajd → odznacz "Aktywny".
[ ] Zmienić logo – Dostosuj → Tożsamość witryny.
[ ] Zmienić tekst stopki – Dostosuj → Stopka.

---
Jeśli chcesz wersję PDF lub krótką kartę startową – daj znać.
