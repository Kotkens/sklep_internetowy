<?php
/**
 * Template Name: Strona Promocji
 * Template dla strony promocji z produktami w promocyjnych cenach
 */

get_header(); ?>

<div class="promocje-page">
    <div class="container">
        <!-- Header sekcji -->
        <div class="page-header">
            <h1 class="page-title">🏷️ Produkty w promocji</h1>
            <p class="page-description">Odkryj najlepsze okazje! Wszystkie produkty z obniżonymi cenami w jednym miejscu.</p>
            
            <!-- Statystyki promocji -->
            <?php
            $promo_count_query = new WP_Query(array(
                'post_type' => 'product',
                'posts_per_page' => -1,
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
            $total_promo = $promo_count_query->found_posts;
            wp_reset_postdata();
            ?>
            
            <div class="promo-stats">
                <div class="stat-item">
                    <span class="stat-number"><?php echo $total_promo; ?></span>
                    <span class="stat-label">produktów w promocji</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">do -70%</span>
                    <span class="stat-label">maksymalna zniżka</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">Codziennie</span>
                    <span class="stat-label">nowe promocje</span>
                </div>
            </div>
        </div>
        
        <!-- Filtry promocji -->
        <div class="promo-filters">
            <h3>Filtruj promocje:</h3>
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">Wszystkie</button>
                <button class="filter-btn" data-filter="elektronika">Elektronika</button>
                <button class="filter-btn" data-filter="ubrania">Ubrania</button>
                <button class="filter-btn" data-filter="dom-i-ogrod">Dom i Ogród</button>
                <button class="filter-btn" data-filter="sport-i-rekreacja">Sport</button>
                <button class="filter-btn" data-filter="ksiazki">Książki</button>
                <button class="filter-btn" data-filter="samochody">Samochody</button>
                <button class="filter-btn" data-filter="artykuly-dla-dzieci">Dzieci</button>
            </div>
        </div>
        
        <!-- Produkty promocyjne -->
        <div class="promocje-grid">
            <?php
            $promo_products = new WP_Query(array(
                'post_type' => 'product',
                'posts_per_page' => -1, // Pokazuj wszystkie produkty promocyjne
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
                ),
                'orderby' => 'meta_value_num',
                'meta_key' => '_sale_price',
                'order' => 'ASC'
            ));
            
            if ($promo_products->have_posts()) :
                while ($promo_products->have_posts()) :
                    $promo_products->the_post();
                    global $product;
                    
                    $regular_price = $product->get_regular_price();
                    $sale_price = $product->get_sale_price();
                    $discount = $regular_price && $sale_price ? round((($regular_price - $sale_price) / $regular_price) * 100) : 0;
                    
                    // Pobierz kategorię produktu
                    $categories = get_the_terms(get_the_ID(), 'product_cat');
                    $category_class = '';
                    if ($categories && !is_wp_error($categories)) {
                        $category_class = $categories[0]->slug;
                    }
                    ?>
                    <div class="promocja-card" data-category="<?php echo $category_class; ?>">
                        <a href="<?php the_permalink(); ?>" class="promocja-link">
                            <div class="promocja-image-wrapper">
                                <?php if (has_post_thumbnail()) : ?>
                                    <img src="<?php the_post_thumbnail_url('medium'); ?>" alt="<?php the_title(); ?>" class="promocja-image">
                                <?php else : ?>
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/default-product.svg" alt="<?php the_title(); ?>" class="promocja-image">
                                <?php endif; ?>
                                
                                <div class="promocja-badges">
                                    <span class="discount-badge">-<?php echo $discount; ?>%</span>
                                    <span class="promo-badge">PROMOCJA</span>
                                </div>
                                
                                <div class="promocja-overlay">
                                    <span class="view-details">Zobacz szczegóły</span>
                                </div>
                            </div>
                            
                            <div class="promocja-info">
                                <h3 class="promocja-title"><?php the_title(); ?></h3>
                                
                                <div class="promocja-prices">
                                    <span class="price-current"><?php echo wc_price($sale_price); ?></span>
                                    <?php if ($regular_price) : ?>
                                        <span class="price-old"><?php echo wc_price($regular_price); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="promocja-savings">
                                    <span class="savings-text">Oszczędzasz: <?php echo wc_price($regular_price - $sale_price); ?></span>
                                </div>
                                
                                <div class="promocja-category">
                                    <?php if ($categories && !is_wp_error($categories)) : ?>
                                        <span class="category-tag"><?php echo $categories[0]->name; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php
                endwhile;
                wp_reset_postdata();
            else :
                ?>
                <div class="no-promos">
                    <h3>Brak aktywnych promocji</h3>
                    <p>Obecnie nie ma produktów w promocyjnych cenach. Sprawdź ponownie wkrótce!</p>
                    <a href="<?php echo home_url('/shop'); ?>" class="btn-primary">Przeglądaj wszystkie produkty</a>
                </div>
                <?php
            endif;
            ?>
        </div>
    </div>
</div>

<style>
/* Styles for promocje page */
.promocje-page {
    background: var(--light-gray);
    min-height: 100vh;
    padding: 30px 0;
}

.page-header {
    text-align: center;
    margin-bottom: 40px;
    background: white;
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.page-title {
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 15px;
    background: linear-gradient(135deg, #ff6b35, #e91e63);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.page-description {
    font-size: 18px;
    color: #666;
    margin-bottom: 30px;
}

.promo-stats {
    display: flex;
    justify-content: center;
    gap: 40px;
    flex-wrap: wrap;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 28px;
    font-weight: 700;
    color: var(--primary-color);
}

.stat-label {
    font-size: 14px;
    color: #666;
}

.promo-filters {
    background: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.promo-filters h3 {
    margin-bottom: 15px;
    color: var(--text-color);
}

.filter-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 8px 16px;
    border: 2px solid var(--border-color);
    background: white;
    border-radius: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
}

.filter-btn:hover,
.filter-btn.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.promocje-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
}

.promocja-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    position: relative;
}

.promocja-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.promocja-image-wrapper {
    position: relative;
    overflow: hidden;
    height: 200px;
}

.promocja-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.promocja-card:hover .promocja-image {
    transform: scale(1.1);
}

.promocja-badges {
    position: absolute;
    top: 15px;
    left: 15px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    z-index: 2;
}

.discount-badge {
    background: #e91e63;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 700;
}

.promo-badge {
    background: #ff6b35;
    color: white;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 12px;
    font-weight: 600;
}

.promocja-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.promocja-card:hover .promocja-overlay {
    opacity: 1;
}

.view-details {
    color: white;
    font-weight: 600;
    background: var(--primary-color);
    padding: 10px 20px;
    border-radius: 25px;
}

.promocja-info {
    padding: 20px;
}

.promocja-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 12px;
    color: var(--text-color);
    line-height: 1.4;
}

.promocja-prices {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}

.price-current {
    font-size: 20px;
    font-weight: 700;
    color: #e91e63;
}

.price-old {
    font-size: 16px;
    color: #999;
    text-decoration: line-through;
}

.promocja-savings {
    margin-bottom: 10px;
}

.savings-text {
    font-size: 14px;
    color: #28a745;
    font-weight: 600;
}

.category-tag {
    display: inline-block;
    background: var(--light-gray);
    color: #666;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
}

.no-promos {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
}

.btn-primary {
    display: inline-block;
    background: var(--primary-color);
    color: white;
    padding: 12px 25px;
    border-radius: 8px;
    text-decoration: none;
    margin-top: 20px;
    transition: background 0.3s ease;
}

.btn-primary:hover {
    background: #1C3B8A;
}

/* Responsive */
@media (max-width: 768px) {
    .page-title {
        font-size: 28px;
    }
    
    .promo-stats {
        gap: 20px;
    }
    
    .promocje-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
}
</style>

<script>
// Filtrowanie kategorii
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const promocjaCards = document.querySelectorAll('.promocja-card');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Usuń aktywną klasę ze wszystkich przycisków
            filterBtns.forEach(b => b.classList.remove('active'));
            
            // Dodaj aktywną klasę do klikniętego przycisku
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            
            // Pokaż/ukryj produkty
            promocjaCards.forEach(card => {
                if (filter === 'all' || card.getAttribute('data-category') === filter) {
                    card.style.display = 'block';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'scale(1)';
                    }, 100);
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 300);
                }
            });
        });
    });
});
</script>

<?php get_footer(); ?>
