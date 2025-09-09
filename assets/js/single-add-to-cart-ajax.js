(function($){
  function getAjaxUrl(endpoint){
    if (window.wc_add_to_cart_params && wc_add_to_cart_params.wc_ajax_url) {
      return wc_add_to_cart_params.wc_ajax_url.replace('%%endpoint%%', endpoint);
    }
    // Fallback
    var base = window.location.origin + (window.location.pathname.indexOf('/wordpress/') !== -1 ? '/wordpress' : '');
    return base + '/?wc-ajax=' + endpoint;
  }

  function showSuccessNotice(name){
    var $wrap = $('.woocommerce-notices-wrapper');
    if (!$wrap.length) {
      // Try to place at top of product summary
      var $target = $('.product').first();
      if ($target.length) {
        $wrap = $('<div class="woocommerce-notices-wrapper" />').prependTo($target);
      } else {
        $wrap = $('<div class="woocommerce-notices-wrapper" />').prependTo('body');
      }
    }
    var safe = $('<div/>').text(name || '').html();
    var html = '<ul class="woocommerce-message" role="alert"><li>"'+ (safe || 'Produkt') +'" został dodany do koszyka.</li></ul>';
    $wrap.html(html);
    try { window.scrollTo({top: 0, behavior: 'smooth'}); } catch(e){}
    setTimeout(function(){ $('.woocommerce-message').fadeOut(250, function(){ $(this).remove(); }); }, 3500);
  }

  $(document).on('submit', 'form.cart', function(e){
    // AJAX-submit add-to-cart on single product pages
    var $form = $(this);
    if ($form.data('preomar-ajaxified')) return; // avoid double bind
    e.preventDefault();

    var $btn = $form.find('.single_add_to_cart_button');
    if ($btn.hasClass('disabled')) return;

    $btn.addClass('loading');

    var data = $form.serializeArray();
    // Ensure correct action
    if (!data.find(function(p){return p.name==='add-to-cart';})){
      var pid = $form.find('input[name="product_id"]').val() || $btn.val() || $btn.data('product_id');
      if (pid) data.push({name:'add-to-cart', value: String(pid)});
    }

    $.post(getAjaxUrl('add_to_cart'), $.param(data))
      .done(function(resp){
        if (resp && resp.error && resp.product_url) {
          window.location = resp.product_url; return;
        }
        // Update fragments (cart count etc.)
        if (resp && resp.fragments) {
          $.each(resp.fragments, function(selector, html){
            $(selector).replaceWith(html);
          });
        } else {
          // Fallback refresh
          $.get(getAjaxUrl('get_refreshed_fragments')).done(function(r){
            if (r && r.fragments) {
              $.each(r.fragments, function(selector, html){ $(selector).replaceWith(html); });
            }
          });
        }

        // Try to read product title to compose message
        var name = $('.product_title').first().text().trim();
        showSuccessNotice(name);
        $(document.body).trigger('added_to_cart', [resp && resp.fragments ? resp.fragments : {}, resp && resp.cart_hash ? resp.cart_hash : '', $btn]);
      })
      .fail(function(){ /* optional: show error */ })
      .always(function(){ $btn.removeClass('loading'); });
  });
})(jQuery);
