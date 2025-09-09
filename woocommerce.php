<!-- PREOMAR THEME_WC_TEMPLATE -->
<?php
/**
 * Template WooCommerce
 */

get_header(); ?>

<div class="shop-container">
    <?php if (is_shop() || is_product_category() || is_product_tag()) : ?>
        <!-- Sidebar z filtrami -->
        <aside class="shop-sidebar">
            <div class="widget">
                <h3 class="widget-title">Filtry</h3>
                
                <!-- Filtr ceny -->
                <div class="filter-group">
                    <h4 class="filter-title">Cena</h4>
                    <div class="price-filter">
                        <input type="range" id="price-min" min="0" max="10000" value="0">
                        <input type="range" id="price-max" min="0" max="10000" value="10000">
                        <div class="price-inputs">
                            <span>od <input type="number" id="min-price" value="0"> zł</span>
                            <span>do <input type="number" id="max-price" value="10000"> zł</span>
                        </div>
                    </div>
                </div>
                
                <!-- Filtr kategorii -->
                <div class="filter-group">
                    <h4 class="filter-title">Kategorie</h4>
                    <ul class="filter-options category-tree">
                        <?php
                        $parent_cats = get_terms([
                            'taxonomy' => 'product_cat',
                            'hide_empty' => false,
                            'parent' => 0
                        ]);
                        foreach($parent_cats as $pcat):
                            $children = get_terms([
                                'taxonomy' => 'product_cat',
                                'hide_empty' => false,
                                'parent' => $pcat->term_id
                            ]);
                            $has_children = !empty($children);
                        ?>
                        <li class="cat-node level-0<?php echo $has_children? ' has-children':''; ?>">
                            <div class="cat-line">
                                <?php if($has_children): ?><button class="cat-toggle" aria-label="Rozwiń / zwiń" type="button">+</button><?php endif; ?>
                                <label>
                                    <input type="checkbox" name="category[]" value="<?php echo esc_attr($pcat->slug); ?>">
                                    <span class="cat-name"><?php echo esc_html($pcat->name); ?></span>
                                    <span class="cat-count"><?php echo intval($pcat->count); ?></span>
                                </label>
                            </div>
                            <?php if($has_children): ?>
                            <ul class="children" hidden>
                                <?php foreach($children as $child): ?>
                                <li class="cat-node level-1">
                                    <label>
                                        <input type="checkbox" name="category[]" value="<?php echo esc_attr($child->slug); ?>">
                                        <span class="cat-name"><?php echo esc_html($child->name); ?></span>
                                        <span class="cat-count"><?php echo intval($child->count); ?></span>
                                    </label>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <?php // Usunięto sekcje: Promocje, Stan, Ocena ?>
                
                <button class="filter-apply-btn">Zastosuj filtry</button>
                <button class="filter-reset-btn">Wyczyść filtry</button>
            </div>
        </aside>
    <?php endif; ?>
    
    <!-- Główna treść -->
    <main class="shop-content">
        <?php if (is_shop() || is_product_category() || is_product_tag()) : ?>
            <?php // Breadcrumb usunięty ?>
            <!-- Pasek: wynik po lewej, sortowanie po prawej -->
            <div class="shop-toolbar">
                <div class="results-count">
                    <?php
                    // Własne wyświetlenie licznika (odtworzenie uproszczonego woocommerce_result_count)
                    if (function_exists('wc_get_loop_prop')) {
                        global $wp_query;
                        $total   = $wp_query->found_posts;
                        $per_page = wc_get_loop_prop('per_page');
                        $current = wc_get_loop_prop('current_page');
                        if (!$per_page) { $per_page = get_option('posts_per_page'); }
                        if (!$current) { $current = max(1, get_query_var('paged')); }
                        $first = ($per_page * $current) - $per_page + 1;
                        $last  = min($total, $per_page * $current);
                        if ($total > 0) {
                            echo '<span>Wyświetlanie ' . esc_html($first) . '–' . esc_html($last) . ' z ' . esc_html($total) . ' wyników</span>';
                        }
                    }
                    ?>
                </div>
                <div class="shop-sorting custom-sorting-right">
                    <select id="shop-sort" aria-label="Sortowanie produktów">
                        <option value="menu_order">Domyślne sortowanie</option>
                        <option value="price">Cena: od najniższej</option>
                        <option value="price-desc">Cena: od najwyższej</option>
                        <option value="date">Najnowsze</option>
                        <option value="date-asc">Najstarsze</option>
                    </select>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- PREOMAR WC_CONTENT_START -->
        <?php woocommerce_content(); ?>
        <!-- PREOMAR WC_CONTENT_END -->

        <?php
        // Fallback: if Woo classic hooks did not run (block templates path), render main query manually
        if ((is_shop() || is_product_category() || is_product_tag()) && ! did_action('woocommerce_before_shop_loop')) {
            echo "<!-- PREOMAR FALLBACK_LOOP_START -->\n";
            if (have_posts()) {
                if (function_exists('woocommerce_product_loop_start')) {
                    woocommerce_product_loop_start();
                } else {
                    echo '<ul class="products">';
                }
                while (have_posts()) {
                    the_post();
                    if (function_exists('wc_get_template_part')) {
                        wc_get_template_part('content', 'product');
                    } else {
                        echo '<li class="product">';
                        echo '<a href="' . esc_url(get_permalink()) . '">' . get_the_title() . '</a>';
                        if (function_exists('wc_get_product')) {
                            $product = wc_get_product(get_the_ID());
                            if ($product) { echo '<span class="price">' . $product->get_price_html() . '</span>'; }
                        }
                        echo '</li>';
                    }
                }
                if (function_exists('woocommerce_product_loop_end')) {
                    woocommerce_product_loop_end();
                } else {
                    echo '</ul>';
                }
                // Basic pagination fallback
                if (function_exists('woocommerce_pagination')) {
                    woocommerce_pagination();
                } else {
                    the_posts_pagination();
                }
            } else {
                echo "<!-- PREOMAR FALLBACK_NO_PRODUCTS -->\n";
                if (function_exists('wc_no_products_found')) { wc_no_products_found(); }
            }
            echo "<!-- PREOMAR FALLBACK_LOOP_END -->\n";
        }
        ?>
        
    <?php // Sekcja "Dlaczego warto kupować u nas?" usunięta na życzenie użytkownika ?>
    </main>
</div>

<?php get_footer(); ?>
