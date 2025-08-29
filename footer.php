    <footer class="site-footer">
        <div class="footer-minimal">
            <ul class="footer-links">
                <?php
                $links = [
                    'Regulamin' => 'regulamin',
                    'Polityka prywatności' => 'polityka-prywatnosci',
                    'Kontakt' => 'kontakt',
                ];
                foreach($links as $label => $slug){
                    $page = get_page_by_path($slug);
                    $url = $page ? get_permalink($page->ID) : home_url('/'.$slug.'/');
                    echo '<li><a href="'.esc_url($url).'">'.esc_html($label).'</a></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                }
                ?>
            </ul>
        </div>
        <div class="footer-bottom">
            <div class="footer-container">
                <p>&copy; <?php echo date('Y'); ?> PreoMarket. Wszystkie prawa zastrzeżone.</p>
            </div>
        </div>
    </footer>
    
    <?php wp_footer(); ?>
</div><!-- #page -->
</body>
</html>
