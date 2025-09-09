    <footer class="site-footer">
        <div class="footer-minimal">
            <ul class="footer-links">
                <?php
                $links = [
                    'Regulamin' => 'regulamin',
                    'Polityka poprawności' => 'polityka-poprawnosci',
                    'Kontakt' => 'kontakt',
                ];
                foreach($links as $label => $slug){
                    // Użyj zawsze ładnego adresu; parse_request fallback zadba o obsługę bez mod_rewrite
                    $url = home_url('/'.$slug.'/');
                    echo '<li><a href="'.esc_url($url).'">'.esc_html($label).'</a></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                }
                ?>
            </ul>
        </div>
        <div class="footer-bottom">
            <div class="footer-container">
                <p><?php echo preomar_get_footer_copyright(); ?></p>
            </div>
        </div>
    </footer>
    
    <?php wp_footer(); ?>
</div><!-- #page -->
</body>
</html>
