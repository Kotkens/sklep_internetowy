<?php get_header(); ?>

<main class="main-content">
    <!-- Mała przestrzeń po headerze -->
    <div style="height: 10px;"></div>
    
    <!-- Hero Slider w stylu Allegro -->
    <section class="hero-slider">
        <div class="slider-container">
            <div class="slider-track" id="heroSlider">
                <!-- Slide 1: Salon vintage -->
                <div class="slide active">
                    <div class="slide-split">
                        <div class="slide-left" style="background: linear-gradient(135deg, #ff5722 0%, #e91e63 100%);">
                            <div class="slide-content">
                                <div class="promo-logo">
                                    <span class="promo-text">PreoMarket</span>
                                    <span class="days-badge">VINTAGE</span>
                                </div>
                                <div class="promo-text">
                                    <h2>Stylowy <strong>salon vintage</strong><br>dla Twojego domu</h2>
                                    <h3>do <span class="discount">-40%</span></h3>
                                    <p class="promo-date">meble i akcesoria retro<br>w najlepszych cenach</p>
                                </div>
                            </div>
                        </div>
                        <div class="slide-right">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/slider/salon-vintage.png" alt="Salon vintage" class="slide-image">
                        </div>
                    </div>
                </div>
                
                <!-- Slide 2: Odnowiony ogród -->
                <div class="slide">
                    <div class="slide-split">
                        <div class="slide-left" style="background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);">
                            <div class="slide-content">
                                <div class="promo-text">
                                    <h2>Odnowiony <strong>ogród</strong><br>na nowy sezon</h2>
                                    <h3>do <span class="discount">-30%</span></h3>
                                    <p class="promo-category">narzędzia, meble ogrodowe<br>i rośliny w super cenach</p>
                                    <a href="/kategoria/dom-ogrod" class="promo-btn">Zobacz oferty</a>
                                </div>
                            </div>
                        </div>
                        <div class="slide-right">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/slider/ogrod-vintage.png" alt="Ogród vintage" class="slide-image">
                        </div>
                    </div>
                </div>
                
                <!-- Slide 3: Rowery vintage -->
                <div class="slide">
                    <div class="slide-split">
                        <div class="slide-left" style="background: linear-gradient(135deg, #2196f3 0%, #1565c0 100%);">
                            <div class="slide-content">
                                <div class="promo-text">
                                    <h2>Rowery <strong>vintage</strong><br>w stylu retro</h2>
                                    <h3>do <span class="discount">-25%</span></h3>
                                    <p class="promo-category">klasyczne rowery miejskie<br>i akcesoria w vintage stylu</p>
                                    <a href="/kategoria/sport-turystyka" class="promo-btn">Znajdź swój rower</a>
                                </div>
                            </div>
                        </div>
                        <div class="slide-right">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/slider/rowery-vintage.jpg" alt="Rowery vintage" class="slide-image">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Navigation dots -->
            <div class="slider-dots">
                <span class="dot active" onclick="currentSlide(1)"></span>
                <span class="dot" onclick="currentSlide(2)"></span>
                <span class="dot" onclick="currentSlide(3)"></span>
            </div>
            
            <!-- Navigation arrows -->
            <div class="slider-controls">
                <button class="slider-btn prev" onclick="prevSlide()">❮</button>
                <button class="slider-btn next" onclick="nextSlide()">❯</button>
            </div>
        </div>
    </section>

    <!-- Kategorie (dynamiczne) -->
    <section class="categories-section">
        <div class="categories-grid">
            <?php
            // Pobierz wszystkie kategorie najwyższego poziomu (product_cat)
            $all_top_categories = get_terms([
                'taxonomy'   => 'product_cat',
                'parent'     => 0,
                'hide_empty' => false,
            ]);

            // Opcjonalna preferowana kolejność (dodaj tutaj slugi jeśli chcesz ręcznie ustawić kolejność)
            $preferred_order = [
                'elektronika',
                'moda',
                'dom-ogrod',
                'supermarket',
                'dziecko',
                'uroda',
                'zdrowie',
                'kultura-rozrywka',
                'sport-turystyka',
                'motoryzacja',
                'kolekcje-sztuka',
                'firma-uslugi',      // przykładowe potencjalnie brakujące
                'nieruchomosci',     // przykładowe potencjalnie brakujące
            ];

            $ordered = [];
            $used_ids = [];
            if (!is_wp_error($all_top_categories)) {
                // Indeksuj po slug dla szybkiego dostępu
                $by_slug = [];
                foreach ($all_top_categories as $term) {
                    $by_slug[$term->slug] = $term;
                }
                // Najpierw preferowana kolejność
                foreach ($preferred_order as $slug) {
                    if (isset($by_slug[$slug])) {
                        $ordered[] = $by_slug[$slug];
                        $used_ids[] = $by_slug[$slug]->term_id;
                    }
                }
                // Dodaj wszystkie pozostałe, które nie zostały jeszcze użyte
                foreach ($all_top_categories as $term) {
                    if (!in_array($term->term_id, $used_ids, true) && $term->slug !== 'uncategorized') {
                        $ordered[] = $term;
                    }
                }
            }

            // Renderuj karty
            foreach ($ordered as $index => $term) :
                $link = get_term_link($term);
                if (is_wp_error($link)) { $link = '#'; }
                $thumb_id = get_term_meta($term->term_id, 'thumbnail_id', true);
                $img_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'medium') : '';
                $style_bg = $img_url
                    ? "background-image:url('" . esc_url($img_url) . "');background-size:cover;background-position:center;"
                    : "background:linear-gradient(135deg,#f1f5f9,#e2e8f0);";
            ?>
                <div class="category-card category-<?php echo esc_attr($term->slug); ?>"
                     style="animation-delay: <?php echo esc_attr($index * 0.1); ?>s;<?php echo $style_bg; ?>"
                     data-category="<?php echo esc_attr($term->slug); ?>">
                    <a href="<?php echo esc_url($link); ?>">
                        <h3><?php echo esc_html($term->name); ?></h3>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Nowo dodane produkty -->
    <section class="latest-products">
        <div class="section-header fancy-heading">
            <h2><span>Nowo dodane</span></h2>
        </div>
        <div class="products-grid">
            <?php
            $latest_products = new WP_Query(array(
                'post_type' => 'product',
                'posts_per_page' => 20,
                'orderby' => 'date',
                'order' => 'DESC',
                'meta_query' => array(
                    array(
                        'key' => '_stock_status',
                        'value' => 'instock'
                    )
                )
            ));
            
            if ($latest_products->have_posts()) :
                while ($latest_products->have_posts()) :
                    $latest_products->the_post();
                    global $product;
                    ?>
                    <div class="product-card product-card">
                        <a href="<?php the_permalink(); ?>" class="product-link">
                            <div class="product-image-wrapper">
                                <?php if (has_post_thumbnail()) : ?>
                                    <img src="<?php the_post_thumbnail_url('medium'); ?>" alt="<?php the_title(); ?>" class="product-image">
                                <?php else : ?>
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/default-product.svg" alt="<?php the_title(); ?>" class="product-image">
                                <?php endif; ?>
                                <?php if ($product->is_on_sale()) : ?>
                                    <span class="sale-badge">PROMOCJA</span>
                                <?php endif; ?>
                                <!-- Usunięto condition-badge -->
                            </div>
                            <div class="product-info">
                                <h3 class="product-title"><?php the_title(); ?></h3>
                                <div class="product-price">
                                    <?php echo $product->get_price_html(); ?>
                                </div>
                                <!-- Usunięto delivery, rating i meta -->
                            </div>
                        </a>
                    </div>
                    <?php
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>
    </section>

    <!-- Produkty w promocji -->
    <section class="promo-products">
        <div class="section-header fancy-heading">
            <h2><span>Promocje</span></h2>
        </div>
        <div class="products-grid">
            <?php
            $promo_products = new WP_Query(array(
                'post_type' => 'product',
                'posts_per_page' => 20,
                'meta_query' => array(
                    array(
                        'key' => '_sale_price',
                        'value' => '',
                        'compare' => '!='
                    ),
                    array(
                        'key' => '_stock_status',
                        'value' => 'instock'
                    )
                )
            ));
            
            if ($promo_products->have_posts()) :
                while ($promo_products->have_posts()) :
                    $promo_products->the_post();
                    global $product;
                    $regular_price = $product->get_regular_price();
                    $sale_price = $product->get_sale_price();
                    $discount = $regular_price && $sale_price ? round((($regular_price - $sale_price) / $regular_price) * 100) : 0;
                    ?>
                    <div class="product-card product-card promo-card">
                        <a href="<?php the_permalink(); ?>" class="product-link">
                            <div class="product-image-wrapper">
                                <?php if (has_post_thumbnail()) : ?>
                                    <img src="<?php the_post_thumbnail_url('medium'); ?>" alt="<?php the_title(); ?>" class="product-image">
                                <?php else : ?>
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/default-product.svg" alt="<?php the_title(); ?>" class="product-image">
                                <?php endif; ?>
                                <?php if ($discount > 0) : ?>
                                    <span class="discount-badge">-<?php echo $discount; ?>%</span>
                                <?php endif; ?>
                                <div class="product-badges">
                                    <span class="promo-badge">PROMOCJA</span>
                                </div>
                            </div>
                            <div class="product-info">
                                <h3 class="product-title"><?php the_title(); ?></h3>
                                <div class="product-price">
                                    <span class="price-current"><?php echo wc_price($sale_price); ?></span>
                                    <?php if ($regular_price) : ?>
                                        <span class="price-old"><?php echo wc_price($regular_price); ?></span>
                                    <?php endif; ?>
                                </div>
                                <!-- Usunięto delivery, rating i meta -->
                            </div>
                        </a>
                    </div>
                    <?php
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>
    </section>
</main>

<style>
/* Mała przestrzeń */
.main-content {
    padding-top: 20px;
}

/* Alternatywnie w CSS */
.hero-slider {
    margin-top: 0px !important;
}

/* CSS dla kategorii - lepsze dopasowanie tekstu */
.category-card h3 {
    font-size: 14px !important;
    line-height: 1.2 !important;
    text-align: center !important;
    padding: 8px !important;
    word-wrap: break-word !important;
    overflow-wrap: break-word !important;
    hyphens: auto !important;
    white-space: normal !important;
    height: auto !important;
    min-height: 40px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.category-card {
    min-height: 120px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.category-card a {
    width: 100% !important;
    height: 100% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    text-decoration: none !important;
}

/* Responsive dla małych ekranów */
@media (max-width: 768px) {
    .category-card h3 {
        font-size: 12px !important;
        padding: 6px !important;
        min-height: 35px !important;
    }
    
    .category-card {
        min-height: 100px !important;
    }
}

/* Specific fix dla długich nazw */
.category-kultura-rozrywka h3,
.category-sport-turystyka h3,
.category-kolekcje-sztuka h3 {
    font-size: 13px !important;
    line-height: 1.1 !important;
}

@media (max-width: 768px) {
    .category-kultura-rozrywka h3,
    .category-sport-turystyka h3,
    .category-kolekcje-sztuka h3 {
        font-size: 11px !important;
    }
}

/* Fancy section headings (lines separated from pill) */
.section-header.fancy-heading { 
    display:flex; 
    align-items:center; 
    justify-content:center; 
    gap:48px; 
    margin:60px 0 25px; 
}
.section-header.fancy-heading::before,
.section-header.fancy-heading::after { 
    content:""; 
    flex:1; 
    height:4px; 
    background:linear-gradient(90deg,#ffb347,#ff7b00); 
    border-radius:4px; 
}
.section-header.fancy-heading h2 { 
    margin:0; 
    font-size:2.2rem; 
    font-weight:700; 
    letter-spacing:.5px; 
    color:#11336f; 
    position:relative; 
}
.section-header.fancy-heading h2 span { 
    position:relative; 
    display:inline-block; 
    padding:.55em 1.9em; 
    background:linear-gradient(135deg,#ffe9d2,#fde3cc); 
    border-radius:60px; 
    box-shadow:0 2px 4px rgba(0,0,0,.04) inset; 
}
@media (max-width:800px){
    .section-header.fancy-heading { gap:30px; }
    .section-header.fancy-heading h2 { font-size:1.8rem; }
    .section-header.fancy-heading h2 span { padding:.5em 1.4em; }
}
</style>

<script>
// Slider functionality with smooth transitions
let currentSlideIndex = 0;
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.dot');
let isTransitioning = false;

function showSlide(n) {
    if (isTransitioning) return;
    isTransitioning = true;
    
    slides[currentSlideIndex].classList.remove('active');
    dots[currentSlideIndex].classList.remove('active');
    
    currentSlideIndex = (n + slides.length) % slides.length;
    
    slides[currentSlideIndex].classList.add('active');
    dots[currentSlideIndex].classList.add('active');
    
    setTimeout(() => {
        isTransitioning = false;
    }, 800);
}

function nextSlide() {
    showSlide(currentSlideIndex + 1);
}

function prevSlide() {
    showSlide(currentSlideIndex - 1);
}

function currentSlide(n) {
    showSlide(n - 1);
}

// Auto-advance slider
let autoSlideInterval;

function startAutoSlide() {
    autoSlideInterval = setInterval(nextSlide, 5000);
}

function stopAutoSlide() {
    clearInterval(autoSlideInterval);
}

startAutoSlide();

const sliderContainer = document.querySelector('.slider-container');
if (sliderContainer) {
    sliderContainer.addEventListener('mouseenter', stopAutoSlide);
    sliderContainer.addEventListener('mouseleave', startAutoSlide);
}

// Touch support
let startX = 0;
let endX = 0;

sliderContainer.addEventListener('touchstart', (e) => {
    startX = e.touches[0].clientX;
});

sliderContainer.addEventListener('touchmove', (e) => {
    e.preventDefault();
});

sliderContainer.addEventListener('touchend', (e) => {
    endX = e.changedTouches[0].clientX;
    const threshold = 50;
    
    if (startX - endX > threshold) {
        nextSlide();
    } else if (endX - startX > threshold) {
        prevSlide();
    }
}); 

// Animacja kategorii z dodatkowymi efektami
document.addEventListener('DOMContentLoaded', function() {
    const categoryCards = document.querySelectorAll('.category-card');
    
    categoryCards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('animated');
        }, index * 100);
        
        // Dodatkowy efekt hover z opóźnieniem
        card.addEventListener('mouseenter', function() {
            this.style.zIndex = '10';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.zIndex = '1';
        });
    });
});
</script>

<?php get_footer(); ?>
