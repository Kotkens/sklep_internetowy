<?php
/**
 * Archive Template
 */

get_header(); ?>

<div class="shop-container">
    <main class="shop-content">
        <!-- Breadcrumbs -->
        <nav class="breadcrumb">
            <a href="<?php echo home_url(); ?>">Strona główna</a>
            > <?php
            if (is_category()) {
                echo single_cat_title('', false);
            } elseif (is_tag()) {
                echo single_tag_title('', false);
            } elseif (is_author()) {
                echo 'Autor: ' . get_the_author();
            } elseif (is_date()) {
                echo 'Archiwum: ' . get_the_date();
            } else {
                echo 'Archiwum';
            }
            ?>
        </nav>
        
        <!-- Nagłówek archiwum -->
        <header class="archive-header">
            <h1><?php
            if (is_category()) {
                single_cat_title();
            } elseif (is_tag()) {
                single_tag_title();
            } elseif (is_author()) {
                echo 'Wszystkie wpisy autora: ' . get_the_author();
            } elseif (is_date()) {
                echo 'Archiwum: ' . get_the_date();
            } else {
                echo 'Archiwum';
            }
            ?></h1>
            
            <?php if (is_category() && category_description()) : ?>
                <div class="archive-description">
                    <?php echo category_description(); ?>
                </div>
            <?php endif; ?>
        </header>
        
        <!-- Lista postów -->
        <div class="posts-grid">
            <?php if (have_posts()) : ?>
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
                            </div>
                            
                            <div class="post-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            
                            <a href="<?php the_permalink(); ?>" class="read-more-btn">
                                Czytaj więcej
                            </a>
                        </div>
                    </article>
                <?php endwhile; ?>
                
                <!-- Paginacja -->
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
                <div class="no-posts">
                    <h2>Brak postów</h2>
                    <p>Nie znaleziono żadnych postów w tej kategorii.</p>
                    <a href="<?php echo home_url(); ?>" class="back-home-btn">Powrót do strony głównej</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php get_footer(); ?>
