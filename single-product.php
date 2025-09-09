<?php
/**
 * Single Product Template
 */

get_header(); ?>

<div class="shop-container">
    <main class="shop-content single-product">
        
        <?php while (have_posts()) : the_post(); ?>
            <div class="single-product-wrapper">
                <?php woocommerce_content(); ?>
            </div>
        <?php endwhile; ?>
        
    <!-- Related Products -->
        <div class="related-products-section">
            <h3>Podobne produkty</h3>
            <?php
            // Pokaż powiązane produkty
            global $product;
            $related_ids = wc_get_related_products($product->get_id(), 4);
            
            if ($related_ids) {
                $related_products = new WP_Query(array(
                    'post_type' => 'product',
                    'post__in' => $related_ids,
                    'posts_per_page' => 4
                ));
                
                if ($related_products->have_posts()) {
                    echo '<div class="products-grid">';
                    while ($related_products->have_posts()) {
                        $related_products->the_post();
                        global $product;
                        ?>
                        <div class="product-card">
                            <a href="<?php the_permalink(); ?>" class="product-image-wrapper">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('preomar_related', ['class'=>'product-image']); ?>
                                <?php else: ?>
                                    <img src="<?php echo esc_url(get_template_directory_uri().'/assets/images/default-product.svg'); ?>" alt="Brak zdjęcia" class="product-image placeholder" />
                                <?php endif; ?>
                            </a>
                            
                            <div class="product-info">
                                <h3 class="product-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                
                                <div class="product-price">
                                    <?php echo $product->get_price_html(); ?>
                                </div>
                                
                                <button class="add-to-cart-btn" data-product-id="<?php echo $product->get_id(); ?>">
                                    Dodaj do koszyka
                                </button>
                            </div>
                        </div>
                        <?php
                    }
                    echo '</div>';
                    wp_reset_postdata();
                }
            }
            ?>
        </div>
    </main>
</div>

<?php get_footer(); ?>
