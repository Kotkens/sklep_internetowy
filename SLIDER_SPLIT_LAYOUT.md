# Hero Slider - Split Layout w stylu Allegro

## Opis
Nowy slider wykorzystuje prawdziwy split layout (opcja 2), dokładnie jak na Allegro:
- **Lewa strona**: kolorowe tło (gradient) z tekstem promocyjnym
- **Prawa strona**: obraz produktu/kategorii
- **Layout**: flexbox row (50/50 split na desktop)

## Struktura HTML
```html
<div class="slide">
    <div class="slide-split">
        <!-- Lewa strona: kolorowe tło + tekst -->
        <div class="slide-left" style="background: linear-gradient(...);">
            <div class="slide-content">
                <!-- Treść promocyjna -->
            </div>
        </div>
        <!-- Prawa strona: obraz -->
        <div class="slide-right">
            <img src="..." alt="..." class="slide-image">
        </div>
    </div>
</div>
```

## Funkcjonalności
✅ **3 slajdy** z różnymi kategoriami
✅ **Automatyczne przełączanie** (5 sekund)
✅ **Pauza na hover**
✅ **Nawigacja strzałkami**
✅ **Kropki nawigacyjne**
✅ **Obsługa gestów** (swipe na mobile)
✅ **Płynne przejścia** (fade)
✅ **Animacje treści** (slide-in z opóźnieniem)
✅ **Responsywność** (mobile stack vertically)

## Kolory gradientów
- **Slide 1** (Allegro Days): `#ff5722` → `#e91e63` (czerwono-różowy)
- **Slide 2** (Dom i Ogród): `#4caf50` → `#2e7d32` (zielony)
- **Slide 3** (Sport i Rowery): `#2196f3` → `#1565c0` (niebieski)

## Responsive Design
- **Desktop (1024px+)**: Split 45%/55%
- **Tablet (768px)**: Split 50%/50%
- **Mobile (768px-)**: Stack vertically (text top, image bottom)
- **Mobile (480px-)**: Kompaktowy layout, ukryte strzałki

## Obrazy
- `salon-vintage.png` - Slide 1 (Allegro Days)
- `ogrod-vintage.png` - Slide 2 (Dom i Ogród)
- `rowery-vintage.jpg` - Slide 3 (Sport i Rowery)

## Efekty wizualne
- Smooth fade transitions (0.8s)
- Content slide-in animation (0.3s delay)
- Hover effects na kontrolach
- Backdrop blur na przyciskach
- Subtle scale effect na obrazach

## Edycja treści
Aby zmienić treść slajdów, edytuj plik `front-page.php`:
1. Znajdź sekcję `<section class="hero-slider">`
2. Edytuj treść w `<div class="slide-content">`
3. Zmień gradient w `style="background: linear-gradient(...)"`
4. Zamień obrazy w `assets/images/slider/`

## Zgodność z Allegro
Layout jest wierną kopią stylu Allegro:
- Identyczne proporcje (45%/55%)
- Podobne kolory i gradiety
- Analogiczna typografia
- Matching hover effects i transitions
