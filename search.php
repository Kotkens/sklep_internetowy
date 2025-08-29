<?php
/**
 * Search Results Template
 */

get_header(); ?>

<div class="shop-container">
    <main class="shop-content">
        <!-- Search Results Header -->
        <header class="search-results-header">
            <h1>Wyniki wyszukiwania</h1>
            <p>Szukałeś: <span class="search-query">"<?php echo get_search_query(); ?>"</span></p>
            <p>Znaleziono: <strong><?php echo $wp_query->found_posts; ?></strong> wyników</p>
        </header>
        
        <!-- Search Results -->
        <div class="search-results">
            <?php if (have_posts()) : ?>
                <div class="posts-grid">
                    <?php while (have_posts()) : the_post(); ?>
                        <article class="post-card">
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>">
                                    <img src="<?php the_post_thumbnail_url('medium'); ?>" alt="<?php the_title(); ?>" class="post-image">
                                </a>
                            <?php endif; ?>
                            
                            <div class="post-content">
                                <h2 class="post-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                
                                <div class="post-meta">
                                    <span class="post-date"><?php echo get_the_date(); ?></span>
                                    <span class="post-author">przez <?php the_author(); ?></span>
                                    <span class="post-type">
                                        <?php
                                        $post_type = get_post_type();
                                        if ($post_type == 'product') {
                                            echo 'Produkt';
                                        } elseif ($post_type == 'post') {
                                            echo 'Artykuł';
                                        } else {
                                            echo ucfirst($post_type);
                                        }
                                        ?>
                                    </span>
                                </div>
                                
                                <div class="post-excerpt">
                                    <?php
                                    if (get_post_type() == 'product') {
                                        // Dla produktów pokaż cenę
                                        global $product;
                                        if ($product) {
                                            echo '<div class="product-price">' . $product->get_price_html() . '</div>';
                                        }
                                    }
                                    the_excerpt();
                                    ?>
                                </div>
                                
                                <a href="<?php the_permalink(); ?>" class="read-more-btn">
                                    <?php echo (get_post_type() == 'product') ? 'Zobacz produkt' : 'Czytaj więcej'; ?>
                                </a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                
                <!-- Pagination -->
                <div class="pagination">
                    <?php
                    echo paginate_links(array(
                        'prev_text' => '« Poprzednia',
                        'next_text' => 'Następna »',
                        'type' => 'list',
                    ));
                    ?>
                </div>
                
            <?php else : ?>
                <div class="no-results">
                    <h2>Brak wyników</h2>
                    <p>Nie znaleziono żadnych wyników dla frazy: <strong>"<?php echo get_search_query(); ?>"</strong></p>
                    
                    <!-- Sugestie -->
                    <div class="search-suggestions">
                        <h3>Spróbuj ponownie:</h3>
                        <ul>
                            <li>Sprawdź pisownię wprowadzonych słów</li>
                            <li>Spróbuj użyć innych słów kluczowych</li>
                            <li>Użyj bardziej ogólnych terminów</li>
                            <li>Sprawdź nasze popularne kategorie poniżej</li>
                        </ul>
                    </div>
                    
                    <!-- Popularne kategorie -->
                    <div class="popular-categories">
                        <h3>Popularne kategorie</h3>
                        <div class="categories-grid">
                            <?php
                            $categories = get_terms(array(
                                'taxonomy' => 'product_cat',
                                'hide_empty' => false,
                                'number' => 6
                            ));
                            
                            foreach ($categories as $category) :
                            ?>
                                <div class="category-card">
                                    <a href="<?php echo get_term_link($category); ?>">
                                        <h4><?php echo $category->name; ?></h4>
                                        <p><?php echo $category->count; ?> produktów</p>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Nowe wyszukiwanie -->
                    <div class="new-search">
                        <h3>Nowe wyszukiwanie</h3>
                        <form class="search-form" method="get" action="<?php echo home_url(); ?>">
                            <input type="text" name="s" class="search-input" placeholder="Wpisz nowe słowa kluczowe...">
                            <button type="submit" class="search-button">Szukaj</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php get_footer(); ?>
