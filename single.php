<?php
/**
 * Single Post Template
 */

get_header(); ?>

<div class="shop-container">
    <main class="shop-content">
        <?php while (have_posts()) : the_post(); ?>
            <!-- Breadcrumbs -->
            <nav class="breadcrumb">
                <a href="<?php echo home_url(); ?>">Strona główna</a>
                > <a href="<?php echo get_permalink(get_option('page_for_posts')); ?>">Blog</a>
                > <?php the_title(); ?>
            </nav>
            
            <article class="single-post-content">
                <header class="single-post-header">
                    <h1 class="single-post-title"><?php the_title(); ?></h1>
                    
                    <div class="single-post-meta">
                        <span class="post-date">
                            <strong>Data:</strong> <?php echo get_the_date(); ?>
                        </span>
                        <span class="post-author">
                            <strong>Autor:</strong> <?php the_author(); ?>
                        </span>
                        <span class="post-category">
                            <strong>Kategoria:</strong> <?php the_category(', '); ?>
                        </span>
                    </div>
                </header>
                
                <?php if (has_post_thumbnail()) : ?>
                    <img src="<?php the_post_thumbnail_url('large'); ?>" alt="<?php the_title(); ?>" class="single-post-featured-image">
                <?php endif; ?>
                
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
                
                <?php if (has_tag()) : ?>
                    <div class="post-tags">
                        <strong>Tagi:</strong> <?php the_tags('', ', ', ''); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Navigation between posts -->
                <nav class="post-navigation">
                    <div class="nav-links">
                        <?php
                        $prev_post = get_previous_post();
                        $next_post = get_next_post();
                        
                        if ($prev_post) :
                        ?>
                            <div class="nav-previous">
                                <a href="<?php echo get_permalink($prev_post->ID); ?>" rel="prev">
                                    <span class="meta-nav">← Poprzedni post</span>
                                    <span class="post-title"><?php echo get_the_title($prev_post->ID); ?></span>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($next_post) : ?>
                            <div class="nav-next">
                                <a href="<?php echo get_permalink($next_post->ID); ?>" rel="next">
                                    <span class="meta-nav">Następny post →</span>
                                    <span class="post-title"><?php echo get_the_title($next_post->ID); ?></span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </nav>
            </article>
            
            <!-- Comments Section -->
            <?php if (comments_open() || get_comments_number()) : ?>
                <div class="comments-section">
                    <h3>Komentarze</h3>
                    <?php comments_template(); ?>
                </div>
            <?php endif; ?>
            
            <!-- Related Posts -->
            <div class="related-posts-section">
                <h3>Powiązane artykuły</h3>
                <div class="posts-grid">
                    <?php
                    $categories = get_the_category();
                    $category_ids = array();
                    
                    foreach ($categories as $category) {
                        $category_ids[] = $category->term_id;
                    }
                    
                    $related_posts = new WP_Query(array(
                        'category__in' => $category_ids,
                        'post__not_in' => array(get_the_ID()),
                        'posts_per_page' => 3,
                        'orderby' => 'rand'
                    ));
                    
                    if ($related_posts->have_posts()) :
                        while ($related_posts->have_posts()) :
                            $related_posts->the_post();
                            ?>
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
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    else :
                        echo '<p>Brak powiązanych artykułów.</p>';
                    endif;
                    ?>
                </div>
            </div>
            
        <?php endwhile; ?>
    </main>
</div>

<?php get_footer(); ?>
