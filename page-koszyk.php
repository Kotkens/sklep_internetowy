<?php
/*
Template Name: Koszyk - Allegro Style
*/

get_header(); ?>

<main class="main-content cart-page">
    <div class="container">
    <!-- Breadcrumb usunięty -->

        <div class="cart-header">
            <h1 class="page-title">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7 18c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03L21.7 4H5.21l-.94-2H1zm16 16c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                </svg>
                Twój koszyk
            </h1>
        </div>

        <?php if (class_exists('WooCommerce')) : ?>
            <!-- WooCommerce Cart Content -->
            <div class="cart-content">
                <?php if (WC()->cart->is_empty()) : ?>
                    <div class="empty-cart">
                        <div class="empty-cart-icon">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="#ccc"><path d="M7 18c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03L21.7 4H5.21l-.94-2H1zm16 16c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg>
                        </div>
                        <h2>Twój koszyk jest pusty</h2>
                        <p>Dodaj produkty, które chcesz kupić i wróć tutaj, aby sfinalizować zakup.</p>
                        <div class="empty-cart-actions">
                            <a href="<?php echo esc_url( function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/sklep/') ); ?>" class="btn btn-primary">Przeglądaj produkty</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="cart-two-col">
                        <div class="cart-left">
                            <?php echo do_shortcode('[woocommerce_cart]'); ?>
                        </div>
                        <aside class="cart-right">
                            <div class="summary-card">
                                <?php woocommerce_cart_totals(); ?>
                                <div class="summary-actions">
                                    <?php do_action('woocommerce_proceed_to_checkout'); ?>
                                    <a class="continue-link" href="<?php echo esc_url( function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/sklep/') ); ?>">Kontynuuj zakupy</a>
                                </div>
                            </div>
                        </aside>
                    </div>
                <?php endif; ?>
            </div>
        <?php else : ?>
            <div class="no-woocommerce">
                <h2>WooCommerce nie jest aktywny</h2>
                <p>Aby korzystać z koszyka, należy aktywować wtyczkę WooCommerce.</p>
            </div>
        <?php endif; ?>

    <!-- Sekcja polecanych produktów usunięta -->
    </div>
</main>

<style>
.cart-page {
    padding: 30px 0;
    background: #f8f9fa;
    min-height: 500px;
}

.cart-page .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Breadcrumb style usunięty */

.cart-header {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.cart-header .page-title {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
    color: #1E3A8A;
    font-size: 24px;
}

.cart-content {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.empty-cart {
    text-align: center;
    padding: 60px 20px;
}

.empty-cart-icon {
    margin-bottom: 20px;
}

.empty-cart h2 {
    color: #333;
    margin-bottom: 10px;
}

.empty-cart p {
    color: #666;
    margin-bottom: 30px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

.btn {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-primary {
    background: #1E3A8A;
    color: white;
}

.btn-primary:hover {
    background: #1e40af;
    transform: translateY(-1px);
}

.recommended-products {
    margin-top: 40px;
    padding: 30px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.recommended-products h2 {
    margin-bottom: 20px;
    color: #333;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.product-card {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.product-card a {
    display: block;
    text-decoration: none;
    color: inherit;
}

.product-image {
    width: 100%;
    height: 160px;
    object-fit: cover;
}

.product-card h3 {
    padding: 10px 15px 5px;
    font-size: 14px;
    margin: 0;
    color: #333;
}

.product-card .price {
    padding: 0 15px 15px;
    font-weight: 600;
    color: #1E3A8A;
}

.no-woocommerce {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 8px;
}

.cart-wrapper {
    padding: 20px;
}

@media (max-width: 768px) {
    .cart-page .container {
        padding: 0 15px;
    }
    
    .cart-header {
        padding: 15px;
    }
    
    .cart-header .page-title {
        font-size: 20px;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
    }
}

/* Układ dwóch kolumn */
.cart-two-col { display: grid; grid-template-columns: 1fr 340px; gap: 30px; }
.cart-left { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 0 0; }
.cart-right { position: sticky; top: 110px; height: fit-content; }
.summary-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px 24px 18px; box-shadow:0 4px 12px rgba(0,0,0,.05); }
.summary-card .wc-proceed-to-checkout { padding:0; margin:18px 0 10px; }
.summary-card .checkout-button { width:100%; background:#1E3A8A !important; font-size:16px; padding:16px 20px; font-weight:600; border-radius:10px; }
.summary-card .checkout-button:hover { background:#142c63 !important; }
.summary-card .continue-link { display:block; text-align:center; margin-top:12px; font-size:13px; text-decoration:none; color:#1E3A8A; font-weight:500; }
.summary-card .continue-link:hover { text-decoration:underline; }

/* Ukrywamy domyślne totals w lewej kolumnie (przenieśliśmy je) */
.cart-left .cart-collaterals { display:none; }

/* Ukryj pierwszy (domyślny) przycisk przejścia do płatności wewnątrz cart_totals */
.summary-card .cart_totals .wc-proceed-to-checkout { display: none !important; }

/* Lepsze formatowanie tabeli podsumowania */
.summary-card .cart_totals table { width:100%; border-collapse:separate; border-spacing:0 6px; }
.summary-card .cart_totals table tr th { text-transform:uppercase; font-size:11px; letter-spacing:.6px; font-weight:600; color:#64748b; padding:4px 0; }
.summary-card .cart_totals table tr td { text-align:right; font-size:14px; font-weight:600; color:#111827; padding:4px 0; }
.summary-card .cart_totals table tr.order-total td { font-size:16px; color:#1E3A8A; font-weight:700; }
.summary-card .cart_totals table tr.order-total th { color:#1E3A8A; }
.summary-card .cart_totals table tr:not(.order-total) td { font-weight:500; }
.summary-card .cart_totals .woocommerce-shipping-totals td { text-align:left; }
.summary-card .cart_totals .woocommerce-shipping-totals td p { margin:4px 0 0; font-size:12px; line-height:1.4; color:#4b5563; }
.summary-card .cart_totals .woocommerce-shipping-totals td ul { margin:4px 0 0; padding-left:18px; }
.summary-card .cart_totals .woocommerce-shipping-totals td ul li { font-size:12px; }
.summary-card .cart_totals .includes_tax { display:block; margin-top:2px; font-size:11px; color:#6b7280; }
.summary-card .cart_totals .fee td, .summary-card .cart_totals .tax-total td { font-size:13px; }
.summary-card { --shadow:0 2px 4px rgba(0,0,0,0.04),0 8px 24px -6px rgba(0,0,0,0.08); box-shadow:var(--shadow); }
.summary-card .cart_totals { margin:0 0 14px; }
.summary-actions { margin-top:6px; }
.summary-actions .checkout-button { margin-top:4px; }

@media (max-width:1020px){
    .cart-two-col { grid-template-columns: 1fr; }
    .cart-right { position:static; }
}

/* ================= REFINED SUMMARY PANEL ================= */
.summary-card { padding:28px 26px 22px; background:#ffffff; border:1px solid #e5e9f1; border-radius:18px; }
.summary-card .cart_totals { background:transparent; box-shadow:none; padding:0; }
.summary-card .cart_totals table { border-spacing:0; width:100%; }
.summary-card .cart_totals table tr { display:flex; gap:14px; padding:6px 0 8px; border-bottom:1px solid #eef1f4; }
.summary-card .cart_totals table tr:last-of-type { border-bottom:none; }
.summary-card .cart_totals table th { flex:1; text-align:left; font-size:11px; letter-spacing:.7px; font-weight:600; color:#6b7280; padding:0; text-transform:uppercase; }
.summary-card .cart_totals table td { flex:0 0 auto; text-align:right; font-size:14px; font-weight:600; color:#111827; padding:0; }
.summary-card .cart_totals table tr.order-total { padding-top:10px; }
.summary-card .cart_totals table tr.order-total th { font-size:12px; color:#1E3A8A; }
.summary-card .cart_totals table tr.order-total td { font-size:20px; font-weight:700; color:#1E3A8A; }
.summary-card .cart_totals .woocommerce-shipping-totals { flex-direction:column; }
.summary-card .cart_totals .woocommerce-shipping-totals th { align-self:flex-start; }
.summary-card .cart_totals .woocommerce-shipping-totals td { width:100%; }
.summary-card .cart_totals .woocommerce-shipping-totals td ul { margin:2px 0 4px 0; padding:0; list-style:none; }
.summary-card .cart_totals .woocommerce-shipping-totals td ul li { margin:0 0 4px; display:flex; justify-content:space-between; align-items:center; gap:8px; font-size:13px; font-weight:500; background:#f5f7fa; padding:6px 10px 6px 12px; border-radius:8px; position:relative; }
.summary-card .cart_totals .woocommerce-shipping-totals td ul li input { margin-right:6px; }
.summary-card .cart_totals .woocommerce-shipping-totals td p { font-size:12px; line-height:1.45; color:#4b5563; margin:4px 0 0; }
.summary-card .free-badge { background:#10b981; color:#fff; font-size:11px; padding:2px 6px; line-height:1; border-radius:6px; font-weight:600; letter-spacing:.5px; }
.summary-card .cart_totals a { color:#1E3A8A; text-decoration:none; font-size:12px; }
.summary-card .cart_totals a:hover { text-decoration:underline; }
.summary-card .checkout-button { margin-top:4px; box-shadow:0 6px 16px -4px rgba(0,0,0,.15); }
.summary-card .checkout-button:active { transform:translateY(1px); box-shadow:0 3px 10px -4px rgba(0,0,0,.18); }
.summary-card .continue-link { font-size:12px; color:#1E3A8A; opacity:.85; }
.summary-card .continue-link:hover { opacity:1; }

/* Usuń formularz kuponu i przycisk "Zaktualizuj koszyk" */
.cart-left .coupon, .cart-left button[name="update_cart"], .cart-left .actions .button[name="update_cart"], .cart-left .actions .button[disabled][name="update_cart"] { display:none !important; }
.cart-left .actions { display:flex; justify-content:flex-end; padding:10px 18px 18px; }

/* Auto-aktualizacja ilości bez ręcznego przycisku */
</style>
<script>
document.addEventListener('input', function(e){
    if(e.target && e.target.classList.contains('qty')){
        clearTimeout(window.__pmQtyTimer);
        window.__pmQtyTimer = setTimeout(function(){
            var form = document.querySelector('form.woocommerce-cart-form');
            if(!form) return;
            // symuluj klik update
            var updateBtn = form.querySelector('button[name="update_cart"], input[name="update_cart"]');
            if(updateBtn){ updateBtn.removeAttribute('disabled'); updateBtn.click(); }
        }, 600); // debounce
    }
});
</script>
<style>

/* Remove list bullet dots if any remain */
.summary-card .cart_totals ul li::marker { content:""; }

@media (max-width:640px){
    .summary-card { border-radius:14px; padding:22px 20px 18px; }
    .summary-card .cart_totals table tr.order-total td { font-size:18px; }
}

/* JS helper (inline) to append badge to free shipping options */
</style>
<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.summary-card .woocommerce-shipping-totals li label').forEach(function(lbl){
        if(/free shipping|darmowa/i.test(lbl.textContent) && !lbl.querySelector('.free-badge')){
            var span=document.createElement('span');
            span.className='free-badge';
            span.textContent='FREE';
            lbl.appendChild(span);
        }
    });
});
</script>
<style>
</style>

<style>
/* Ulepszony styl koszyka w kolorach motywu */
:root {
    --pm-blue: #1E3A8A;
    --pm-blue-dark: #142c63;
    --pm-blue-light: #2750b5;
    --pm-accent: #ff6b35;
    --pm-accent-hover: #ff7d4f;
    --pm-bg: #f5f7fa;
    --pm-border: #e2e8f0;
}

.cart-page { background: var(--pm-bg); }

/* Tabela */
.cart-page table.shop_table { 
    border: 1px solid var(--pm-border); 
    border-radius: 10px; 
    overflow: hidden; 
    box-shadow: 0 4px 12px rgba(0,0,0,0.04);
}
.cart-page table.shop_table th { 
    background: white; 
    font-weight: 600; 
    color: var(--pm-blue-dark);
    font-size: 14px;
    border-bottom: 1px solid var(--pm-border);
}
.cart-page table.shop_table td { 
    background: #fff; 
    border-top: 1px solid var(--pm-border); 
    vertical-align: middle; 
    font-size: 14px;
}
.cart-page table.shop_table tr:hover td { background: #fdfdfd; }

/* Link usuń */
.cart-page a.remove { 
    color: #e03131 !important; 
    font-size: 18px; 
    line-height: 1; 
    transition: transform .2s, background .2s;
}
.cart-page a.remove:hover { 
    background: #ffe3e3; 
    transform: scale(1.15);
}

/* Nazwa produktu */
.cart-page td.product-name a { 
    font-weight: 600; 
    color: var(--pm-blue-dark); 
    text-decoration: none; 
}
.cart-page td.product-name a:hover { color: var(--pm-accent); }

/* Ilość */
.cart-page .quantity .qty { 
    width: 60px; 
    padding: 6px 8px; 
    border: 1px solid var(--pm-border); 
    border-radius: 6px; 
    font-size: 14px; 
    transition: border-color .2s, box-shadow .2s; 
}
.cart-page .quantity .qty:focus { 
    outline: none; 
    border-color: var(--pm-accent); 
    box-shadow: 0 0 0 2px rgba(255,107,53,0.25);
}

/* Formularz kuponu */
.cart-page .coupon { display: flex; gap: 10px; align-items: center; }
.cart-page .coupon #coupon_code { 
    padding: 10px 14px; 
    border: 1px solid var(--pm-border); 
    border-radius: 6px; 
    font-size: 14px; 
    min-width: 170px;
    transition: border-color .2s, box-shadow .2s; 
}
.cart-page .coupon #coupon_code:focus { 
    outline: none; 
    border-color: var(--pm-accent); 
    box-shadow: 0 0 0 2px rgba(255,107,53,0.25);
}

/* Przyciski */
.cart-page button.button, 
.cart-page a.button, 
.cart-page .checkout-button { 
    background: var(--pm-accent); 
    color: #fff !important; 
    border: none; 
    border-radius: 8px; 
    padding: 12px 22px; 
    font-weight: 600; 
    font-size: 14px;
    cursor: pointer; 
    display: inline-flex; 
    align-items: center; 
    gap: 6px; 
    box-shadow: 0 3px 6px rgba(0,0,0,0.08); 
    transition: background .25s, transform .2s, box-shadow .25s; 
    text-decoration: none; 
}
.cart-page button.button:hover, 
.cart-page a.button:hover, 
.cart-page .checkout-button:hover { 
    background: var(--pm-accent-hover); 
    transform: translateY(-2px); 
    box-shadow: 0 6px 12px rgba(0,0,0,0.12);
}
.cart-page button.button:disabled, 
.cart-page .button.disabled, 
.cart-page button[disabled] { 
    opacity: .55; 
    cursor: not-allowed; 
    transform: none; 
    box-shadow: none;
}

/* Podsumowanie */
.cart-page .cart_totals { 
    background: #fff; 
    border: 1px solid var(--pm-border); 
    border-radius: 12px; 
    padding: 24px 26px; 
    box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
}
.cart-page .cart_totals h2 { 
    font-size: 18px; 
    margin-top: 0; 
    color: var(--pm-blue-dark); 
    font-weight: 600; 
    display: none; /* ukryj nagłówek jeśli duplikat */
}
.cart-page .cart_totals table { border: none; }
.cart-page .cart_totals table tr th { font-weight: 500; color: #4b5563; }
.cart-page .cart_totals table tr td, 
.cart-page .cart_totals table tr th { border: none; padding: 6px 0; }
.cart-page .cart_totals .order-total strong { color: var(--pm-blue-dark); }

/* Checkout button full width */
.cart-page .wc-proceed-to-checkout { 
    padding-top: 20px; 
}
.cart-page .wc-proceed-to-checkout .checkout-button { 
    width: 100%; 
    font-size: 15px; 
    letter-spacing: .3px; 
}

/* Responsywność */
@media (max-width: 780px){
    .cart-page .coupon { flex-direction: column; align-items: stretch; }
    .cart-page .coupon #coupon_code { width: 100%; }
    .cart-page button.button, .cart-page a.button { width: 100%; justify-content: center; }
    .cart-page table.shop_table td.product-name { min-width: 140px; }
}
</style>

<?php get_footer(); ?>
