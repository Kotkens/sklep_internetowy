jQuery(document).ready(function($) {
    // Obsługa dodawania do koszyka bez przeładowania strony
    $('.add-to-cart-btn').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var productId = $button.data('product-id');
        
        if (!productId) return;
        
        // Zmiana tekstu przycisku
        var originalText = $button.text();
        $button.text('Dodawanie...');
        $button.prop('disabled', true);
        
        // AJAX request
        $.ajax({
            url: wc_add_to_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: 'woocommerce_add_to_cart',
                product_id: productId,
                quantity: 1
            },
            success: function(response) {
                if (response.error) {
                    alert('Błąd: ' + response.error);
                } else {
                    // Aktualizacja licznika koszyka
                    $('.cart-count').text(response.cart_count);
                    $button.text('Dodano!');
                    
                    // Powrót do oryginalnego tekstu po 2 sekundach
                    setTimeout(function() {
                        $button.text(originalText);
                        $button.prop('disabled', false);
                    }, 2000);
                }
            },
            error: function() {
                alert('Wystąpił błąd podczas dodawania produktu do koszyka');
                $button.text(originalText);
                $button.prop('disabled', false);
            }
        });
    });
    
    // Obsługa mobilnego menu
    $('.mobile-menu-toggle').on('click', function() {
        $('.categories-menu').toggleClass('mobile-open');
    });
    
    // Obsługa filtrów produktów
    $('.filter-options input[type="checkbox"]').on('change', function() {
        // Tutaj można dodać logikę filtrowania produktów
        console.log('Filter changed:', $(this).val(), $(this).is(':checked'));
    });
    
    // Smooth scroll dla anchor linków
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $(this.hash);
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 500);
        }
    });
    
    // Obsługa wyszukiwania z sugestiami
    var searchTimer;
    $('.search-input').on('input', function() {
        var query = $(this).val();
        
        clearTimeout(searchTimer);
        
        if (query.length > 2) {
            searchTimer = setTimeout(function() {
                // Tutaj można dodać AJAX dla sugestii wyszukiwania
                console.log('Searching for:', query);
            }, 300);
        }
    });
    
    // Obsługa sticky header
    $(window).on('scroll', function() {
        var scrollTop = $(window).scrollTop();
        
        if (scrollTop > 100) {
            $('.site-header').addClass('scrolled');
        } else {
            $('.site-header').removeClass('scrolled');
        }
    });
    
    // Lazy loading dla obrazów
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Obsługa wishlist
    $('.wishlist-btn').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var productId = $button.data('product-id');
        var isInWishlist = $button.hasClass('in-wishlist');
        
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: isInWishlist ? 'remove_from_wishlist' : 'add_to_wishlist',
                product_id: productId,
                nonce: ajax_object.wishlist_nonce
            },
            success: function(response) {
                if (response.success) {
                    $button.toggleClass('in-wishlist');
                    $button.text(isInWishlist ? '🤍' : '❤️');
                    $button.attr('title', isInWishlist ? 'Dodaj do ulubionych' : 'Usuń z ulubionych');
                    // Aktualizacja licznika w nagłówku – preferuj wartość z serwera
                    var $wc = $('#wishlistCount');
                    if ($wc.length) {
                        if (response.data && typeof response.data.count !== 'undefined') {
                            $wc.text(parseInt(response.data.count,10));
                        } else {
                            var current = parseInt($wc.text(), 10) || 0;
                            if (isInWishlist && current > 0) current--; else if (!isInWishlist) current++;
                            $wc.text(current);
                        }
                    }
                    
                    var msg = response.data && response.data.message ? response.data.message : 'Zaktualizowano obserwowane';
                    showNotification(msg, 'success');
                } else {
                    var emsg = response.data && response.data.message ? response.data.message : 'Błąd';
                    showNotification(emsg, 'error');
                }
            },
            error: function() {
                showNotification('Wystąpił błąd', 'error');
            }
        });
    });
    
    // Obsługa compare
    $('.compare-btn').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var productId = $button.data('product-id');
        
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'add_to_compare',
                product_id: productId
            },
            success: function(response) {
                if (response.success) {
                    $button.addClass('in-compare');
                    showNotification(response.data.message, 'success');
                } else {
                    showNotification(response.data.message, 'error');
                }
            },
            error: function() {
                showNotification('Wystąpił błąd', 'error');
            }
        });
    });
    
    // Funkcja do pokazywania notyfikacji
    function showNotification(message, type) {
        var notification = $('<div class="cart-notification ' + type + '">' + message + '</div>');
        $('body').append(notification);
        
        setTimeout(function() {
            notification.addClass('show');
        }, 100);
        
        setTimeout(function() {
            notification.removeClass('show');
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 3000);
    }
    
    // Obsługa filtrów ceny
    $('#price-min, #price-max').on('input', function() {
        var minPrice = $('#price-min').val();
        var maxPrice = $('#price-max').val();
        
        $('#min-price').val(minPrice);
        $('#max-price').val(maxPrice);
    });
    
    $('#min-price, #max-price').on('input', function() {
        var minPrice = $('#min-price').val();
        var maxPrice = $('#max-price').val();
        
        $('#price-min').val(minPrice);
        $('#price-max').val(maxPrice);
    });

    // Kategorie – drzewko (toggle children)
    $(document).on('click','.cat-toggle', function(){
        var $btn = $(this);
        var $li = $btn.closest('.cat-node');
        var $children = $li.children('ul.children');
        if(!$children.length) return;
        var isHidden = $children.is('[hidden]');
        if(isHidden){
            $children.slideDown(180, function(){ $children.removeAttr('hidden'); });
            $btn.text('−');
        } else {
            $children.slideUp(160, function(){ $children.attr('hidden','hidden'); });
            $btn.text('+');
        }
    });

    // Zastosuj filtry (przekierowanie z parametrami)
    $(document).on('click','.filter-apply-btn', function(e){
        e.preventDefault();
        var minPrice = $('#min-price').val() || 0;
        var maxPrice = $('#max-price').val() || '';
        var cats = [];
        document.querySelectorAll('input[name="category[]"]:checked').forEach(function(cb){
            cats.push(cb.value);
        });
        var url = new URL(window.location.href);
        if(cats.length){
            url.searchParams.set('product_cat', cats.join(',')); // custom multi-cat param
        } else {
            url.searchParams.delete('product_cat');
        }
        url.searchParams.set('min_price', minPrice);
        if(maxPrice) url.searchParams.set('max_price', maxPrice); else url.searchParams.delete('max_price');
        url.searchParams.delete('paged');
        window.location.href = url.toString();
    });

    // Reset filtrów
    $(document).on('click','.filter-reset-btn', function(e){
        e.preventDefault();
        var url = new URL(window.location.href);
        ['product_cat','min_price','max_price','orderby','paged'].forEach(p=>url.searchParams.delete(p));
        window.location.href = url.toString();
    });
    
    // Obsługa sortowania (redirect + zapamiętanie preferencji)
    function applySortingRedirect(value){
        // Uruchamiaj tylko jeśli realnie jesteśmy na stronie listy produktów (select istnieje)
        if(!document.getElementById('shop-sort')) return;
        if (typeof(Storage)!=='undefined') {
            localStorage.setItem('preomar_sort_preference', value);
        }
        var url = new URL(window.location.href);
        url.searchParams.set('orderby', value);
        url.searchParams.delete('paged');
        window.location.href = url.toString();
    }
    $(document).on('change','#shop-sort', function(){
        applySortingRedirect(this.value);
    });
    
    // Obsługa quick view (modal)
    $('.quick-view-btn').on('click', function(e) {
        e.preventDefault();
        
        var productId = $(this).data('product-id');
        
        // Tutaj można dodać AJAX do ładowania danych produktu
        console.log('Quick view for product:', productId);
    });
    
    // Obsługa quantity inputs
    $('.quantity-input').on('input', function() {
        var quantity = $(this).val();
        var $button = $(this).closest('.product-card').find('.add-to-cart-btn');
        
        if (quantity > 1) {
            $button.text('Dodaj ' + quantity + ' do koszyka');
        } else {
            $button.text('Dodaj do koszyka');
        }
    });
    
    // Obsługa infinite scroll
    var page = 2;
    var loading = false;
    
    function loadMoreProducts() {
        if (loading) return;
        
        loading = true;
        
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'load_more_products',
                page: page,
                query: ajax_object.query_vars
            },
            success: function(response) {
                if (response.success && response.data.html) {
                    $('.products-grid').append(response.data.html);
                    page++;
                    
                    if (!response.data.has_more) {
                        $(window).off('scroll', handleScroll);
                        $('.products-grid').after('<p class="no-more-products">Brak więcej produktów</p>');
                    }
                }
                loading = false;
            },
            error: function() {
                loading = false;
            }
        });
    }
    
    function handleScroll() {
        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 1000) {
            loadMoreProducts();
        }
    }
    
    // Włącz infinite scroll tylko na stronach z produktami
    if ($('.products-grid').length && $('.pagination').length) {
        $(window).on('scroll', handleScroll);
    }
    
    // Obsługa galerii produktów
    $('.product-gallery img').on('click', function() {
        var src = $(this).attr('src');
        var alt = $(this).attr('alt');
        
        // Prosta lightbox
        var lightbox = $('<div class="lightbox-overlay"><div class="lightbox-content"><img src="' + src + '" alt="' + alt + '"><button class="lightbox-close">×</button></div></div>');
        
        $('body').append(lightbox);
        lightbox.fadeIn();
        
        lightbox.on('click', function(e) {
            if (e.target === this || $(e.target).hasClass('lightbox-close')) {
                lightbox.fadeOut(function() {
                    lightbox.remove();
                });
            }
        });
    });
    
    // Obsługa tabs
    $('.tab-nav-item').on('click', function(e) {
        e.preventDefault();
        
        var $tab = $(this);
        var targetId = $tab.attr('href');
        
        // Usuń aktywne klasy
        $('.tab-nav-item').removeClass('active');
        $('.tab-content').removeClass('active');
        
        // Dodaj aktywne klasy
        $tab.addClass('active');
        $(targetId).addClass('active');
    });
    
    // Obsługa sticky elements (header + dynamiczny offset sidebaru)
    function updateSticky() {
        var scrollTop = $(window).scrollTop();
        var headerH = $('.site-header').outerHeight() || 0;
        document.documentElement.style.setProperty('--sidebar-top', (headerH + 20) + 'px');
        if (scrollTop > 100) {
            $('.site-header').addClass('scrolled');
        } else {
            $('.site-header').removeClass('scrolled');
        }
    }
    $(window).on('scroll resize', updateSticky);
    updateSticky();
    
    // Obsługa local storage dla preferencji użytkownika
    function saveUserPreference(key, value) {
        if (typeof(Storage) !== "undefined") {
            localStorage.setItem('preomar_' + key, value);
        }
    }
    
    function getUserPreference(key) {
        if (typeof(Storage) !== "undefined") {
            return localStorage.getItem('preomar_' + key);
        }
        return null;
    }
    
    // (Drugi dawny listener sortowania usunięty – obsługę robi applySortingRedirect powyżej)
    
    // Przywróć preferencje sortowania
    var savedSort = getUserPreference('sort_preference');
    // Migracja starych wartości (price-low -> price, price-high -> price-desc, default -> menu_order)
    var params = new URL(window.location.href).searchParams;
    var orderby = params.get('orderby');
    if(document.getElementById('shop-sort')){
        if (savedSort) {
            if (savedSort === 'price-low') savedSort = 'price';
            else if (savedSort === 'price-high') savedSort = 'price-desc';
            else if (savedSort === 'default') savedSort = 'menu_order';
            $('#shop-sort').val(savedSort);
            if (!orderby) {
                applySortingRedirect(savedSort);
            }
        } else if (orderby) {
            $('#shop-sort').val(orderby);
        }
    }
    
    // Obsługa cookie consent (jeśli potrzebne)
    function showCookieConsent() {
        if (!getUserPreference('cookie_consent')) {
            var consent = $('<div class="cookie-consent">Ta strona używa plików cookie. <button class="accept-cookies">Akceptuj</button></div>');
            $('body').append(consent);
            
            consent.find('.accept-cookies').on('click', function() {
                saveUserPreference('cookie_consent', 'true');
                consent.fadeOut(function() {
                    consent.remove();
                });
            });
        }
    }
    
    // Pokaż cookie consent po załadowaniu strony
    setTimeout(showCookieConsent, 2000);
    
    // Obsługa touch events dla mobile
    if ('ontouchstart' in window) {
        $('.product-card').on('touchstart', function() {
            $(this).addClass('touch-active');
        });
        
        $('.product-card').on('touchend', function() {
            $(this).removeClass('touch-active');
        });
    }
    
    // Performance optimization - debounce function
    function debounce(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }
    
    // Debounce scroll events
    $(window).on('scroll', debounce(function() {
        // Scroll handling code here
    }, 100));
    
    // Preload critical images
    function preloadImages() {
        $('.product-card img').each(function() {
            var $img = $(this);
            var src = $img.data('src');
            
            if (src) {
                var img = new Image();
                img.onload = function() {
                    $img.attr('src', src);
                    $img.removeClass('lazy');
                };
                img.src = src;
            }
        });
    }
    
    // Initialize preloading
    preloadImages();
    
    // Add to cart animation
    $('.add-to-cart-btn').on('click', function() {
        var $button = $(this);
        var $cart = $('.cart-link');
        
        if ($cart.length) {
            var $product = $button.closest('.product-card');
            var $productImg = $product.find('img').first();
            
            if ($productImg.length) {
                var $flyImg = $productImg.clone();
                $flyImg.addClass('fly-to-cart');
                
                var productOffset = $productImg.offset();
                var cartOffset = $cart.offset();
                
                $flyImg.css({
                    position: 'fixed',
                    top: productOffset.top,
                    left: productOffset.left,
                    width: $productImg.width(),
                    height: $productImg.height(),
                    'z-index': 9999
                });
                
                $('body').append($flyImg);
                
                $flyImg.animate({
                    top: cartOffset.top,
                    left: cartOffset.left,
                    width: 30,
                    height: 30,
                    opacity: 0.7
                }, 800, function() {
                    $flyImg.remove();
                    $cart.addClass('cart-shake');
                    setTimeout(function() {
                        $cart.removeClass('cart-shake');
                    }, 500);
                });
            }
        }
    });
    
    // Obsługa responsive kategorii - 6 w rzędzie
    function adjustCategoriesLayout() {
        var $categoriesGrid = $('.categories-grid');
        var windowWidth = $(window).width();
        
        // Usuń wszystkie klasy modyfikujące
        $categoriesGrid.removeClass('cols-2 cols-3 cols-4 cols-5 cols-6');
        
        // Dodaj odpowiednią klasę na podstawie szerokości ekranu
        if (windowWidth >= 1200) {
            $categoriesGrid.addClass('cols-6'); // 6 kolumn
        } else if (windowWidth >= 1000) {
            $categoriesGrid.addClass('cols-5'); // 5 kolumn
        } else if (windowWidth >= 768) {
            $categoriesGrid.addClass('cols-4'); // 4 kolumny
        } else if (windowWidth >= 480) {
            $categoriesGrid.addClass('cols-3'); // 3 kolumny
        } else {
            $categoriesGrid.addClass('cols-2'); // 2 kolumny
        }
    }
    
    // Wywołaj przy załadowaniu i zmianie rozmiaru okna
    adjustCategoriesLayout();
    $(window).on('resize', debounce(adjustCategoriesLayout, 250));
    
    // Hover effect dla kategorii
    $('.category-card').hover(
        function() {
            $(this).addClass('hover-active');
        },
        function() {
            $(this).removeClass('hover-active');
        }
    );
    
    // Animacja pojawiania się kategorii przy scroll
    function animateCategoriesOnScroll() {
        var $categories = $('.category-card');
        var windowBottom = $(window).scrollTop() + $(window).height();
        
        $categories.each(function(index) {
            var $category = $(this);
            var categoryTop = $category.offset().top;
            
            if (windowBottom > categoryTop + 100 && !$category.hasClass('animated')) {
                setTimeout(function() {
                    $category.addClass('animated fadeInUp');
                }, index * 100); // Opóźnienie dla każdej kategorii
            }
        });
    }
    
    // Sprawdź animację przy scroll
    $(window).on('scroll', debounce(animateCategoriesOnScroll, 100));
    
    // Wywołaj animację przy załadowaniu strony
    animateCategoriesOnScroll();
});

// === OBSŁUGA PRZYCISKU OBSERWUJ (single product) ===
(function($){
    $(document).on('click','.follow-product-btn',function(e){
        e.preventDefault();
        var $btn = $(this);
        if($btn.data('loading')) return; // simple lock
        var productId = $btn.data('product-id');
        if(!productId) return;
        $btn.data('loading',true).addClass('is-loading');
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'preomar_toggle_follow',
                product_id: productId,
                nonce: ajax_object.wishlist_nonce
            },
            success: function(res){
                if(res && res.success){
                    if(res.data.state === 'added'){
                        $btn.addClass('is-following');
                        $btn.find('.icon').text('★');
                        $btn.find('.text').text('Obserwujesz');
                    } else {
                        $btn.removeClass('is-following');
                        $btn.find('.icon').text('☆');
                        $btn.find('.text').text('Obserwuj');
                    }
                } else {
                    var msg = (res && res.data)? (res.data.message || res.data) : 'Błąd';
                    if(window.alert) alert(msg);
                }
            },
            error: function(xhr){
                if(xhr && xhr.responseJSON && xhr.responseJSON.data){
                    alert(xhr.responseJSON.data);
                } else {
                    alert('Wystąpił błąd');
                }
            },
            complete: function(){
                $btn.data('loading',false).removeClass('is-loading');
            }
        });
    });
})(jQuery);

// === WISHLIST: usuwanie pozycji na stronie obserwowanych ===
(function($){
    $(document).on('click','.remove-from-wishlist-btn',function(e){
        e.preventDefault();
        var $btn = $(this);
        if($btn.data('loading')) return;
        var id = $btn.data('product-id');
        if(!id) return;
        $btn.data('loading',true).addClass('is-loading');
        var $item = $btn.closest('.wishlist-item');
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            dataType:'json',
            data:{action:'remove_from_wishlist',product_id:id,nonce:ajax_object.wishlist_nonce},
            success:function(res){
                if(res && res.success){
                    // animacja usunięcia
                    $item.addClass('removing');
                    $item.fadeOut(220,function(){
                        $(this).remove();
                        if($('.wishlist-grid .wishlist-item').length===0){
                            $('.wishlist-container').append('<div class="wishlist-empty"><h2>Brak obserwowanych produktów</h2><p>Dodaj produkty klikając przycisk \"Obserwuj\" na stronie produktu.</p></div>');
                        }
                    });
                    // Zmniejsz licznik w nagłówku
                    var $wc = $('#wishlistCount');
                    if($wc.length){
                        if(res.data && typeof res.data.count !== 'undefined'){
                            $wc.text(parseInt(res.data.count,10));
                        } else {
                            var current = parseInt($wc.text(),10) || 0;
                            if(current>0){ $wc.text(current-1); }
                        }
                    }
                } else {
                    alert((res && res.data)? (res.data.message || res.data) : 'Błąd');
                }
            },
            error:function(){ alert('Wystąpił błąd'); },
            complete:function(){ $btn.data('loading',false).removeClass('is-loading'); }
        });
    });
})(jQuery);

// === Aktualizacja licznika po toggle follow na stronie produktu ===
(function($){
    $(document).on('click','.follow-product-btn',function(){
        // Aktualizacja licznika dzieje się w success poprzedniego handlera – tu nasłuch na AJAX done
    });
    // Przechwycenie globalnego ajax complete dla toggle follow aby odczytać count jeśli endpoint zwróci
    $(document).ajaxSuccess(function(event, xhr, settings){
        if(settings && settings.data && settings.data.indexOf && settings.data.indexOf('action=preomar_toggle_follow') !== -1){
            try {
                var res = xhr.responseJSON || JSON.parse(xhr.responseText);
                if(res && res.success && res.data && typeof res.data.count !== 'undefined'){
                    var $wc = $('#wishlistCount');
                    if($wc.length){ $wc.text(parseInt(res.data.count,10)); }
                }
            } catch(e) {}
        }
    });
})(jQuery);
