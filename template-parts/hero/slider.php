<?php
/**
 * Template Part: Hero Slider (Split Layout)
 * Wydzielony z wcześniejszej wersji front-page.php aby łatwiej utrzymywać kod.
 * Obrazy szukane są w katalogu /assets/images/slider/
 */
?>
<section class="hero-slider" role="region" aria-label="Promowane oferty">
    <div class="slider-container">
        <div class="slider-track" id="heroSlider">
            <?php
            // Pobierz dynamiczne slajdy
            $dynamic_slides = function_exists('preomar_get_hero_slides') ? preomar_get_hero_slides() : [];
            if($dynamic_slides){
                $i = 0;
                foreach($dynamic_slides as $slide){
                    $i++;
                    $active = $i === 1 ? ' active' : '';
                    $aria = $i === 1 ? 'false' : 'true';
                    $heading_before = esc_html($slide['heading_before']);
                    $heading_strong = esc_html($slide['heading_strong']);
                    $discount = esc_html($slide['discount']);
                    $lines = array_filter($slide['lines']);
                    $btn_text = trim($slide['button_text']);
                    $btn_url = trim($slide['button_url']);
                    $gradient = esc_attr($slide['gradient']);
                    $img_html = '';
                    if(!empty($slide['image_id'])){
                        $img_html = wp_get_attachment_image($slide['image_id'],'large',false,[ 'class'=>'slide-image','loading'=>'lazy' ]);
                    }
                    if(!$img_html){
                        $img_html = '<img src="'.esc_url(get_template_directory_uri().'/assets/images/slider/ogrod-vintage.png').'" alt="" class="slide-image" loading="lazy" />';
                    }
                    ?>
                    <div class="slide<?php echo $active; ?>" data-slide="<?php echo esc_attr($i); ?>" aria-hidden="<?php echo $aria; ?>">
                        <div class="slide-split">
                            <div class="slide-left" style="background: <?php echo $gradient; ?>;">
                                <div class="slide-content">
                                    <div class="promo-text">
                                        <h2><?php echo $heading_before ? $heading_before.' ' : ''; ?><strong><?php echo $heading_strong; ?></strong><?php if($lines){ echo '<br>'.esc_html(reset($lines)); array_shift($lines); } ?></h2>
                                        <?php if($discount){ ?><h3>do <span class="discount"><?php echo $discount; ?></span></h3><?php } ?>
                                        <?php if($lines){ echo '<p class="promo-category">'.esc_html(implode(' ',$lines)).'</p>'; } ?>
                                        <?php if($btn_text && $btn_url){ ?><a href="<?php echo esc_url($btn_url); ?>" class="promo-btn" aria-label="<?php echo esc_attr($btn_text); ?>"><?php echo esc_html($btn_text); ?></a><?php } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="slide-right">
                                <?php echo $img_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                // Fallback – oryginalne statyczne 3 slajdy (jeśli brak dynamicznych)
                ?>
                <div class="slide active" data-slide="1" aria-hidden="false">
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
                            <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/slider/salon-vintage.png" alt="Salon vintage - aranżacja" class="slide-image" loading="lazy">
                        </div>
                    </div>
                </div>
                <div class="slide" data-slide="2" aria-hidden="true">
                    <div class="slide-split">
                        <div class="slide-left" style="background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);">
                            <div class="slide-content">
                                <div class="promo-text">
                                    <h2>Odnowiony <strong>ogród</strong><br>na nowy sezon</h2>
                                    <h3>do <span class="discount">-30%</span></h3>
                                    <p class="promo-category">narzędzia, meble ogrodowe<br>i rośliny w super cenach</p>
                                    <a href="/kategoria/dom-ogrod" class="promo-btn" aria-label="Zobacz oferty ogród">Zobacz oferty</a>
                                </div>
                            </div>
                        </div>
                        <div class="slide-right">
                            <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/slider/ogrod-vintage.png" alt="Ogród - stolik i krzesła" class="slide-image" loading="lazy">
                        </div>
                    </div>
                </div>
                <div class="slide" data-slide="3" aria-hidden="true">
                    <div class="slide-split">
                        <div class="slide-left" style="background: linear-gradient(135deg, #2196f3 0%, #1565c0 100%);">
                            <div class="slide-content">
                                <div class="promo-text">
                                    <h2>Rowery <strong>vintage</strong><br>w stylu retro</h2>
                                    <h3>do <span class="discount">-25%</span></h3>
                                    <p class="promo-category">klasyczne rowery miejskie<br>i akcesoria w vintage stylu</p>
                                    <a href="/kategoria/sport-turystyka" class="promo-btn" aria-label="Znajdź rower">Znajdź swój rower</a>
                                </div>
                            </div>
                        </div>
                        <div class="slide-right">
                            <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/slider/rowery-vintage.jpg" alt="Rowery vintage" class="slide-image" loading="lazy">
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        <div class="slider-dots" role="tablist" aria-label="Przełącz slajdy">
            <?php
            if(!empty($dynamic_slides)){
                $c=0; foreach($dynamic_slides as $s){ $c++; ?>
                    <button class="dot <?php echo $c===1? 'active':''; ?>" role="tab" aria-selected="<?php echo $c===1? 'true':'false'; ?>" data-target="<?php echo esc_attr($c); ?>"></button>
                <?php }
            } else { ?>
                <button class="dot active" role="tab" aria-selected="true" data-target="1"></button>
                <button class="dot" role="tab" aria-selected="false" data-target="2"></button>
                <button class="dot" role="tab" aria-selected="false" data-target="3"></button>
            <?php } ?>
        </div>
        <div class="slider-controls" aria-hidden="false">
            <button class="slider-btn prev" type="button" aria-label="Poprzedni slajd">❮</button>
            <button class="slider-btn next" type="button" aria-label="Następny slajd">❯</button>
        </div>
    </div>
</section>
