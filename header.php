<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    
    <?php if (is_front_page()) : ?>
    <!-- Preload critical category images -->
    <link rel="preload" as="image" href="<?php echo get_template_directory_uri(); ?>/assets/images/categories/elektronika.jpg">
    <link rel="preload" as="image" href="<?php echo get_template_directory_uri(); ?>/assets/images/categories/moda.jpg">
    <link rel="preload" as="image" href="<?php echo get_template_directory_uri(); ?>/assets/images/categories/dom-ogrod.jpg">
    <link rel="preload" as="image" href="<?php echo get_template_directory_uri(); ?>/assets/images/categories/supermarket.jpg">
    <link rel="preload" as="image" href="<?php echo get_template_directory_uri(); ?>/assets/images/categories/dziecko.jpg">
    <link rel="preload" as="image" href="<?php echo get_template_directory_uri(); ?>/assets/images/categories/uroda.jpg">
    <link rel="preload" as="image" href="<?php echo get_template_directory_uri(); ?>/assets/images/categories/zdrowie.jpg">
    <link rel="preload" as="image" href="<?php echo get_template_directory_uri(); ?>/assets/images/categories/Kultura-i-rozrywka.jpg">
    <link rel="preload" as="image" href="<?php echo get_template_directory_uri(); ?>/assets/images/categories/Sport-i-turystyka.jpg">
    <link rel="preload" as="image" href="<?php echo get_template_directory_uri(); ?>/assets/images/categories/motoryzacja.jpg">
    <link rel="preload" as="image" href="<?php echo get_template_directory_uri(); ?>/assets/images/categories/kolekcje-sztuka.jpg">
    <?php endif; ?>
    
    <!-- Style dla poprawnego wyśrodkowania ikon SVG -->
    <style>
    html,body{height:100%;}
    body{display:flex;flex-direction:column;min-height:100vh;}
    #page.site{flex:1;display:flex;flex-direction:column;}
    main, .site-content, .main-content{flex:1 0 auto;}
    .site-footer{margin-top:0;}
    :root{--header-height:72px;} /* default fallback */
    /* Fixed header (zawsze widoczny) */
    .site-header{background:#0f1640;box-shadow:0 2px 6px -2px rgba(0,0,0,.22);width:100%;}
    .site-header.fixed{position:fixed;top:0;left:0;right:0;z-index:2000;}
    body.with-fixed-header{padding-top:var(--header-height);} /* kompensacja skoku treści */
    /* Pasek kategorii pod nagłówkiem */
    .categories-navigation{top:var(--header-height);} /* pozostaje sticky ale pod headerem */
    @media (max-width:780px){ .categories-navigation{top:calc(var(--header-height));} }
        .top-bar-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .top-bar-item svg {
            vertical-align: middle;
            margin-top: 0;
        }
        
        .header-link {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 5px !important;
        }
        
        .header-link svg {
            vertical-align: middle;
            flex-shrink: 0;
        }
        
        .header-link span {
            white-space: nowrap;
        }
        
        /* Categories Dropdown Styles */
        .categories-navigation {
            background: #2e3440;
            border-bottom: 1px solid #3b4252;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .categories-nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            align-items: center;
            gap: 30px;
        }
        
        .categories-dropdown {
            position: relative;
        }
        
        .categories-btn {
            background: none;
            border: none;
            color: white;
            padding: 12px 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 4px;
        }
        
        .categories-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .categories-btn.active {
            background: rgba(255, 255, 255, 0.15);
        }
        
        .dropdown-arrow {
            transition: transform 0.3s ease;
        }
        
        .categories-btn.active .dropdown-arrow {
            transform: rotate(180deg);
        }
        
        .categories-dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            min-width: 800px;
            max-width: 1000px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .categories-dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            padding: 25px;
        }
        
        .category-column {
            border-right: 1px solid #eee;
            padding-right: 20px;
        }
        
        .category-column:last-child {
            border-right: none;
        }
        
        .category-title {
            margin: 0 0 15px 0;
        }
        
        .category-title a {
            color: #2e3440;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
        }
        
        .category-title a:hover {
            color: #ff6b35;
        }
        
        .subcategory-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .subcategory-list li {
            margin-bottom: 8px;
        }
        
        .subcategory-list a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        
        .subcategory-list a:hover {
            color: #ff6b35;
        }
        

    </style>
    
    <script>
        // Categories dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Fixed header init
            (function(){
                const header=document.querySelector('.site-header');
                if(!header) return;
                function applyHeight(){
                    const h=header.offsetHeight;document.documentElement.style.setProperty('--header-height',h+'px');
                }
                applyHeight();
                window.addEventListener('resize',applyHeight);
                // natychmiast oznacz jako fixed aby uniknąć przeskoku
                header.classList.add('fixed');
                document.body.classList.add('with-fixed-header');
            })();
            const categoriesBtn = document.getElementById('categoriesBtn');
            const categoriesMenu = document.getElementById('categoriesMenu');
            
            if (categoriesBtn && categoriesMenu) {
                categoriesBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    categoriesBtn.classList.toggle('active');
                    categoriesMenu.classList.toggle('show');
                });
                
                // Close menu when clicking outside
                document.addEventListener('click', function(e) {
                    if (!categoriesBtn.contains(e.target) && !categoriesMenu.contains(e.target)) {
                        categoriesBtn.classList.remove('active');
                        categoriesMenu.classList.remove('show');
                    }
                });
                
                // Close menu on escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        categoriesBtn.classList.remove('active');
                        categoriesMenu.classList.remove('show');
                    }
                });
                
                // Close menu on scroll
                window.addEventListener('scroll', function() {
                    if(categoriesBtn && categoriesMenu){
                        categoriesBtn.classList.remove('active');
                        categoriesMenu.classList.remove('show');
                    }
                });
            }
        });
    </script>

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <header class="site-header">
        <div class="header-container">
            <!-- Logo -->
            <a href="<?php echo home_url(); ?>" class="site-logo">
                <?php if (has_custom_logo()) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo_strona.png" alt="PreoMarket - Sklep z używanymi rzeczami" class="logo-image">
                <?php endif; ?>
            </a>
            
            <!-- Wyszukiwarka -->
            <div class="search-container">
                <?php $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/sklep/'); ?>
                <form class="search-form" method="get" action="<?php echo esc_url( $shop_url ); ?>">
                    <input type="text" 
                           name="s" 
                           class="search-input" 
                           placeholder="czego szukasz? np. telefon, sukienka, meble..."
                           value="<?php echo get_search_query(); ?>">
                    
                    <select name="product_cat" class="search-category">
                        <option value="">Wszystkie kategorie</option>
                        
                        <!-- Główne kategorie produktów -->
                        <option value="elektronika">Elektronika</option>
                        <option value="moda">Moda</option>
                        <option value="dom-ogrod">Dom i ogród</option>
                        <option value="supermarket">Supermarket</option>
                        <option value="dziecko">Dziecko</option>
                        <option value="uroda">Uroda</option>
                        <option value="zdrowie">Zdrowie</option>
                        <option value="kultura-rozrywka">Kultura i rozrywka</option>
                        <option value="sport-turystyka">Sport i turystyka</option>
                        <option value="motoryzacja">Motoryzacja</option>
                        <option value="kolekcje-sztuka">Kolekcje i sztuka</option>
                        <option value="firma-uslugi">Firma i usługi</option>
                        
                        <?php
                        // Dodatkowe kategorie z WooCommerce (jeśli istnieją)
                        $product_categories = get_terms('product_cat', array(
                            'hide_empty' => false,
                            'parent' => 0
                        ));
                        if ($product_categories && !is_wp_error($product_categories)) :
                            foreach ($product_categories as $category) :
                                // Sprawdź czy kategoria nie jest już na liście i nie jest "Bez kategorii"
                                $predefined_slugs = array('elektronika', 'moda', 'dom-ogrod', 'supermarket', 'dziecko', 'uroda', 'zdrowie', 'kultura-rozrywka', 'sport-turystyka', 'motoryzacja', 'kolekcje-sztuka', 'firma-uslugi');
                                $excluded_slugs = array('uncategorized', 'bez-kategorii');
                                if (!in_array($category->slug, $predefined_slugs) && !in_array($category->slug, $excluded_slugs) && $category->name !== 'Bez kategorii') :
                        ?>
                            <option value="<?php echo $category->slug; ?>" 
                                    <?php selected(get_query_var('product_cat'), $category->slug); ?>>
                                <?php echo $category->name; ?>
                            </option>
                        <?php 
                                endif;
                            endforeach; 
                        endif;
                        ?>
                    </select>
                    
                    <input type="hidden" name="post_type" value="product" />
                    <!-- Przekazanie aktualnego sortowania do wyników wyszukiwania -->
                    <input type="hidden" name="orderby" id="search-orderby" value="<?php echo isset($_GET['orderby']) ? esc_attr($_GET['orderby']) : ''; ?>" />
                    <!-- Domyślny zakres ceny aby URL zawsze miał min/max -->
                    <input type="hidden" name="min_price" id="search-min-price" value="<?php echo isset($_GET['min_price']) ? esc_attr($_GET['min_price']) : '0'; ?>" />
                    <input type="hidden" name="max_price" id="search-max-price" value="<?php echo isset($_GET['max_price']) ? esc_attr($_GET['max_price']) : '10000'; ?>" />
                    <button type="submit" class="search-button">SZUKAJ</button>
                </form>
                <script>
                // Ustaw hidden orderby przy submit jeśli brak w URL a jest preferencja zapisana
                (function(){
                    var form = document.querySelector('.search-form');
                    if(!form) return;
                    form.addEventListener('submit', function(){
                        var field = document.getElementById('search-orderby');
                        // Jeśli zakres cen nie został ustawiony dynamicznie (np. przez slider) zapewnij domyślne wartości
                        var minField = document.getElementById('search-min-price');
                        var maxField = document.getElementById('search-max-price');
                        if(minField && !minField.value) minField.value = '0';
                        if(maxField && !maxField.value) maxField.value = '10000';
                        if(!field) return;
                        if(field.value) return; // już ustawione z URL
                        try {
                            var saved = localStorage.getItem('preomar_sort_preference');
                            if(saved === 'price-low') saved='price';
                            else if(saved === 'price-high') saved='price-desc';
                            else if(saved === 'default') saved='menu_order';
                            if(saved) field.value = saved;
                        } catch(e) {}
                    });
                })();
                </script>
            </div>
            
            <!-- Akcje nagłówka -->
            <div class="header-actions">
                <a href="<?php echo esc_url( home_url('/sprzedawaj/') ); ?>" class="header-link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    <span>Sprzedawaj</span>
                </a>
                
                <a href="<?php echo esc_url(home_url('/obserwowane/')); ?>" class="header-link wishlist-link" data-wishlist-link>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                    <span>Obserwowane</span>
                    <?php
                    // Dynamiczny licznik obserwowanych
                    $preomar_wishlist = is_user_logged_in() ? get_user_meta(get_current_user_id(), 'wishlist', true) : [];
                    $preomar_wishlist_count = (is_array($preomar_wishlist) ? count($preomar_wishlist) : 0);
                    ?>
                    <span class="wishlist-count" id="wishlistCount"><?php echo (int)$preomar_wishlist_count; ?></span>
                </a>
                
                <a href="<?php echo home_url('/koszyk/'); ?>" class="header-link cart-link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M7 18c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03L21.7 4H5.21l-.94-2H1zm16 16c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                    </svg>
                    <span>Koszyk</span>
                    <span class="cart-count"><?php echo class_exists('WooCommerce') && WC()->cart ? WC()->cart->get_cart_contents_count() : '0'; ?></span>
                </a>
                
                <?php if (is_user_logged_in()) : ?>
                    <?php $current_user = wp_get_current_user(); ?>
                    <div class="user-menu header-link">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                        <span class="user-menu-text"><?php echo esc_html($current_user->display_name); ?></span>
                        <svg class="user-menu-icon" width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
                        </svg>
                        <div class="user-dropdown">
                            <a href="<?php echo home_url('/ustawienia-konta/'); ?>">Ustawienia konta</a>
                            <a href="<?php echo home_url('/moje-zakupy/'); ?>">Moje zakupy</a>
                            <a href="<?php echo wp_logout_url(home_url()); ?>">Wyloguj się</a>
                        </div>
                    </div>
                <?php else : ?>
                    <a href="<?php echo get_permalink(get_option('woocommerce_myaccount_page_id')); ?>" class="header-link">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11 7L9.6 8.4l2.6 2.6H2v2h10.2l-2.6 2.6L11 17l5-5-5-5zm9 12h-8v2h8c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-8v2h8v14z"/>
                        </svg>
                        <span>Zaloguj się</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Menu kategorii w nowoczesnym stylu -->
    <nav class="categories-navigation">
        <div class="categories-nav-container">
            <!-- Przycisk Kategorie -->
            <div class="categories-dropdown">
                <button class="categories-btn" id="categoriesBtn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
                    </svg>
                    Kategorie
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="dropdown-arrow">
                        <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
                    </svg>
                </button>
                
                <!-- Rozwijane menu kategorii -->
                <div class="categories-dropdown-menu" id="categoriesMenu">
                    <div class="categories-grid">
                        <?php
                        // Dynamiczne kategorie WooCommerce (top-level + dzieci)
                        $top_cats = get_terms(array(
                            'taxonomy' => 'product_cat',
                            'parent' => 0,
                            'hide_empty' => false,
                            'orderby' => 'name',
                            'order' => 'ASC'
                        ));
                        if (!is_wp_error($top_cats) && $top_cats) :
                            foreach ($top_cats as $cat) :
                                // Pomiń kategorię Nieruchomości (usunięta z projektu)
                                if ($cat->slug === 'nieruchomosci') { continue; }
                                $children = get_terms(array(
                                    'taxonomy' => 'product_cat',
                                    'parent' => $cat->term_id,
                                    'hide_empty' => false,
                                    'orderby' => 'name',
                                    'order' => 'ASC'
                                ));
                                ?>
                                <div class="category-column">
                                    <h3 class="category-title">
                                        <a href="<?php echo esc_url(get_term_link($cat)); ?>">
                                            <?php echo esc_html($cat->name); ?>
                                        </a>
                                    </h3>
                                    <?php if ($children && !is_wp_error($children)) : ?>
                                        <ul class="subcategory-list">
                                            <?php foreach ($children as $child) : ?>
                                                <li>
                                                    <a href="<?php echo esc_url(get_term_link($child)); ?>">
                                                        <?php echo esc_html($child->name); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                                <?php
                            endforeach;
                        else:
                            echo '<p style="padding:20px;">Brak kategorii produktów.</p>';
                        endif;
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

<div id="page" class="site">
