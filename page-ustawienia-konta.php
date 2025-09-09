<?php
/*
 * Template Name: Ustawienia konta
 */

// Zabezpieczenie
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Przetwarzanie akcji kart płatniczych (musi być przed jakimkolwiek outputem)
if ( is_user_logged_in() && function_exists('WC') ) {
    $current_user_tmp = wp_get_current_user();
    if ( isset($_GET['cc_action'], $_GET['token_id'], $_GET['cc_nonce']) ) {
        $action   = sanitize_key( $_GET['cc_action'] );
        $token_id = (int) $_GET['token_id'];
        $nonce    = $_GET['cc_nonce'];
        if ( wp_verify_nonce( $nonce, 'cc_action_'.$token_id ) ) {
            $token = WC_Payment_Tokens::get( $token_id );
            if ( $token && (int)$token->get_user_id() === (int)$current_user_tmp->ID ) {
                if ( $action === 'delete' ) {
                    WC_Payment_Tokens::delete( $token_id );
                    // Używamy własnej tablicy komunikatów (wc notices wyciszone)
                    $GLOBALS['preomar_card_messages'][] = ['type'=>'success','text'=>'Karta została usunięta.'];
                } elseif ( $action === 'default' ) {
                    // Ustaw jako domyślną
                    foreach ( WC_Payment_Tokens::get_customer_tokens( $current_user_tmp->ID ) as $t ) {
                        if ( (int)$t->get_id() === $token_id ) {
                            $t->set_default( true );
                            $t->save();
                        } elseif ( $t->get_is_default() ) {
                            $t->set_default( false );
                            $t->save();
                        }
                    }
                    $GLOBALS['preomar_card_messages'][] = ['type'=>'success','text'=>'Ustawiono kartę jako domyślną.'];
                }
            } else {
                $GLOBALS['preomar_card_messages'][] = ['type'=>'error','text'=>'Nie znaleziono karty lub brak uprawnień.'];
            }
        } else {
            $GLOBALS['preomar_card_messages'][] = ['type'=>'error','text'=>'Nieprawidłowy token bezpieczeństwa.'];
        }
    }
}

get_header();

if ( ! is_user_logged_in() ) : ?>
	<div class="wishlist-container" style="max-width:900px;margin:60px auto;padding:60px 50px;background:#fff;border:1px solid #e5ebf2;border-radius:20px;text-align:center;box-shadow:0 10px 30px -8px rgba(28,44,72,.1);">
		<h1 style="font-size:2rem;margin:0 0 25px;font-weight:800;color:#1d2e49;">Musisz być zalogowany</h1>
		<p style="color:#5b6b80;font-size:1rem;margin:0 0 30px;">Zaloguj się aby zobaczyć i edytować ustawienia swojego konta.</p>
		<a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="login-btn" style="display:inline-block;background:linear-gradient(135deg,#ff6b00,#ff8329);color:#fff;text-decoration:none;font-weight:700;padding:16px 32px;border-radius:16px;box-shadow:0 8px 24px -6px rgba(255,107,0,.45);transition:.35s;">Zaloguj się</a>
	</div>
<?php get_footer(); return; endif; 

$current_user = wp_get_current_user();

// Obsługa zapisu
$messages = [];
if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    // Edycja danych podstawowych
    if ( isset( $_POST['preomar_account_settings_nonce'] ) && wp_verify_nonce( $_POST['preomar_account_settings_nonce'], 'preomar_save_account_settings' ) ) {
        if ( current_user_can( 'edit_user', $current_user->ID ) ) {
            $display_name = sanitize_text_field( wp_unslash( $_POST['display_name'] ?? '' ) );
            $first_name   = sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
            $last_name    = sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) );
            $email        = sanitize_email( wp_unslash( $_POST['user_email'] ?? '' ) );
            $errors = [];
            if ( empty( $display_name ) ) $errors[] = 'Nazwa wyświetlana jest wymagana';
            if ( empty( $email ) || ! is_email( $email ) ) $errors[] = 'Niepoprawny email';
            if ( $email && $email !== $current_user->user_email ) {
                $owner = get_user_by( 'email', $email );
                if ( $owner && (int)$owner->ID !== (int)$current_user->ID ) $errors[] = 'Podany email jest już używany.';
            }
            if ( empty( $errors ) ) {
                $update_args = ['ID'=>$current_user->ID,'display_name'=>$display_name,'user_email'=>$email];
                wp_update_user( $update_args );
                update_user_meta( $current_user->ID, 'first_name', $first_name );
                update_user_meta( $current_user->ID, 'last_name',  $last_name );
                $messages[] = ['type'=>'success','text'=>'Zapisano zmiany profilu.'];
                $current_user = wp_get_current_user();
            } else {
                foreach ( $errors as $e ) { $messages[] = ['type'=>'error','text'=>$e]; }
            }
        } else {
            $messages[] = ['type'=>'error','text'=>'Brak uprawnień.'];
        }
    }
    // Zmiana hasła
    if ( isset( $_POST['preomar_change_password_nonce'] ) && wp_verify_nonce( $_POST['preomar_change_password_nonce'], 'preomar_change_password' ) ) {
        $current_pass = $_POST['current_pass'] ?? '';
        $new_pass     = $_POST['new_pass'] ?? '';
        $new_pass2    = $_POST['new_pass_confirm'] ?? '';
        if ( empty( $current_pass ) || empty( $new_pass ) || empty( $new_pass2 ) ) {
            $messages[] = ['type'=>'error','text'=>'Wypełnij wszystkie pola hasła.'];
        } elseif ( ! wp_check_password( $current_pass, $current_user->user_pass, $current_user->ID ) ) {
            $messages[] = ['type'=>'error','text'=>'Aktualne hasło nieprawidłowe.'];
        } elseif ( $new_pass !== $new_pass2 ) {
            $messages[] = ['type'=>'error','text'=>'Nowe hasła nie są identyczne.'];
        } elseif ( strlen( $new_pass ) < 8 ) {
            $messages[] = ['type'=>'error','text'=>'Nowe hasło musi mieć minimum 8 znaków.'];
        } else {
            wp_set_password( $new_pass, $current_user->ID );
            wp_set_auth_cookie( $current_user->ID ); // utrzymanie sesji
            $messages[] = ['type'=>'success','text'=>'Hasło zostało zmienione.'];
        }
    }
    // Adresy wysyłki (oraz opcjonalnie fakturowania)
    if ( isset( $_POST['preomar_change_address_nonce'] ) && wp_verify_nonce( $_POST['preomar_change_address_nonce'], 'preomar_change_address' ) ) {
        $fields = [
            'shipping_first_name','shipping_last_name','shipping_company','shipping_country',
            'shipping_address_1','shipping_address_2','shipping_postcode','shipping_city','shipping_state','shipping_phone'
        ];
        $sanitized = [];
        foreach ( $fields as $f ) {
            $val = wp_unslash( $_POST[$f] ?? '' );
            if ( strpos($f,'postcode') !== false ) $sanitized[$f] = sanitize_text_field( $val );
            elseif ( strpos($f,'country') !== false || strpos($f,'state') !== false ) $sanitized[$f] = strtoupper( preg_replace('/[^A-Z0-9_-]/i','', $val ) );
            else $sanitized[$f] = sanitize_text_field( $val );
        }
        // Minimalna walidacja
        if ( empty( $sanitized['shipping_first_name'] ) || empty( $sanitized['shipping_last_name'] ) || empty( $sanitized['shipping_address_1'] ) || empty( $sanitized['shipping_postcode'] ) || empty( $sanitized['shipping_city'] ) ) {
            $messages[] = ['type'=>'error','text'=>'Uzupełnij wymagane pola adresu (imię, nazwisko, adres, kod, miasto).'];
        } else {
            foreach ( $sanitized as $k=>$v ) {
                update_user_meta( $current_user->ID, $k, $v );
            }
            // Jeśli brak osobnego billing_* kopiuj
            if ( ! get_user_meta( $current_user->ID, 'billing_first_name', true ) ) {
                update_user_meta( $current_user->ID, 'billing_first_name', $sanitized['shipping_first_name'] );
                update_user_meta( $current_user->ID, 'billing_last_name',  $sanitized['shipping_last_name'] );
                update_user_meta( $current_user->ID, 'billing_address_1', $sanitized['shipping_address_1'] );
                update_user_meta( $current_user->ID, 'billing_address_2', $sanitized['shipping_address_2'] );
                update_user_meta( $current_user->ID, 'billing_postcode',  $sanitized['shipping_postcode'] );
                update_user_meta( $current_user->ID, 'billing_city',      $sanitized['shipping_city'] );
                update_user_meta( $current_user->ID, 'billing_country',   $sanitized['shipping_country'] );
                update_user_meta( $current_user->ID, 'billing_state',     $sanitized['shipping_state'] );
                update_user_meta( $current_user->ID, 'billing_phone',     $sanitized['shipping_phone'] );
            }
            $messages[] = ['type'=>'success','text'=>'Adres wysyłki zapisany.'];
        }
    }
}

// Dane do formularza
$display_name = esc_attr( $current_user->display_name );
$first_name   = esc_attr( get_user_meta( $current_user->ID, 'first_name', true ) );
$last_name    = esc_attr( get_user_meta( $current_user->ID, 'last_name', true ) );
$user_email   = esc_attr( $current_user->user_email );

// Bezpieczne pobranie kraju bazowego aby uniknąć ostrzeżeń gdy WC() nie dostępne w lintingu
$wc_base_country = (function_exists('WC') && WC()) ? WC()->countries->get_base_country() : 'PL';
?>

<style>
/* Layout */
.account-settings-wrapper { width:100%; max-width:none; margin:40px 0 80px; padding:0 24px; }
.account-settings-cards { display:flex; flex-direction:column; gap:48px; width:100%; align-items:center; }
.account-settings-cards .settings-card { width:50%; max-width:960px; min-width:640px; box-sizing:border-box; padding:46px 60px; }
@media (min-width:1600px){ .account-settings-cards .settings-card { padding-left:80px; padding-right:80px; } }
@media (max-width:1200px){ .account-settings-cards .settings-card { width:70%; min-width:0; } }
@media (max-width:900px){ .account-settings-cards .settings-card { width:100%; padding:40px 28px; min-width:0; } }

/* Cards – jasna wersja spójna z motywem */
.settings-card { background:#fff; border:1px solid #e2e8f0; border-radius:20px; position:relative; overflow:hidden; box-shadow:0 8px 28px -10px rgba(30,58,138,.18),0 4px 14px -6px rgba(30,58,138,.12); transition:box-shadow .35s, transform .35s; }
.settings-card:before { content:""; position:absolute; inset:0; background:linear-gradient(135deg,rgba(30,58,138,.07),rgba(255,107,0,.05)); opacity:.9; pointer-events:none; }
.settings-card:hover { box-shadow:0 12px 34px -10px rgba(30,58,138,.28),0 6px 18px -8px rgba(30,58,138,.18); transform:translateY(-2px); }
.settings-card h2 { color:#1E3A8A; letter-spacing:.5px; }
.settings-card p { color:#5a6472; }

/* Buttons */
.settings-card button { font-weight:600; cursor:pointer; border:none; border-radius:10px; font-size:.85rem; letter-spacing:.5px; display:inline-flex; align-items:center; gap:6px; }
.btn-action { background:#1E3A8A; color:#fff; padding:12px 26px; box-shadow:0 4px 14px -4px rgba(30,58,138,.4); transition:.3s; }
.btn-action:hover { background:#172f6d; }
.btn-primary-save { background:#FF6B00; color:#fff; padding:12px 28px; box-shadow:0 6px 18px -6px rgba(255,107,0,.55); }
.btn-primary-save:hover { background:#e85f00; }
.btn-secondary { background:#94a3b8; color:#fff; padding:12px 24px; }
.btn-secondary:hover { background:#7d8b9d; }

/* Forms */
.settings-card form label { display:block; font-size:.65rem; font-weight:600; text-transform:uppercase; letter-spacing:.6px; color:#6b7280; margin:0 0 6px; }
.settings-card form input[type=text],
.settings-card form input[type=email],
.settings-card form input[type=password],
.settings-card form select { width:100%; padding:12px 14px; border:1px solid #d4dde7; border-radius:10px; background:#fff; color:#1e293b; font-size:.9rem; line-height:1.2; box-shadow:0 1px 2px rgba(0,0,0,.04) inset; transition:.25s; }
.settings-card form input:focus,
.settings-card form select:focus { outline:none; border-color:#1E3A8A; box-shadow:0 0 0 3px rgba(30,58,138,.25); }
.settings-card form input::placeholder { color:#9aa4b1; }

/* Messages */
.messages > div { backdrop-filter:blur(4px); }

/* Animacje pojawiania formularzy */
#dataForm,#passwordForm,#addressForm { animation:fadeSlide .45s ease; }
@keyframes fadeSlide { from { opacity:0; transform:translateY(-6px);} to { opacity:1; transform:translateY(0);} }

/* Nagłówek strony */
.account-settings-wrapper h1 { color:#1E3A8A; text-shadow:0 2px 4px rgba(30,58,138,.18); }

/* === Powiększone napisy (żądanie) === */
.settings-card h2 { font-size:1.65rem !important; }
.settings-card p { font-size:1.05rem !important; }
.settings-card form label { font-size:.72rem !important; }
.settings-card form input[type=text],
.settings-card form input[type=email],
.settings-card form input[type=password],
.settings-card form select { font-size:1rem !important; }
.btn-action, .btn-primary-save, .btn-secondary { font-size:.95rem !important; }
.account-settings-wrapper h1 { font-size:2.4rem !important; }

</style>

<div class="account-settings-wrapper">
    <h1 style="font-size:2.2rem;margin:0 0 40px;font-weight:800;color:#1d2e49;text-align:center;">Ustawienia konta</h1>

    <div class="account-settings-cards">
        <!-- Karta: Twoje dane -->
    <div class="settings-card" style="padding:46px 50px;width:100%;min-height:240px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h2 style="font-size:1.4rem;font-weight:700;margin:0;">Twoje dane</h2>
                <button onclick="toggleDataForm()" class="btn-action">ZMIEŃ</button>
            </div>
            <p style="margin:0;font-size:.9rem;">Informacje o Tobie i Twoim koncie</p>
            
            <!-- Formularz edycji danych (ukryty domyślnie) -->
            <div id="dataForm" style="display:none;margin-top:24px;padding-top:24px;border-top:1px solid #4a505a;">
                <?php if ( $messages ) : ?>
                    <div class="messages" style="margin:0 0 20px;display:flex;flex-direction:column;gap:8px;">
                        <?php foreach ( $messages as $m ) : $is_ok = $m['type']==='success'; ?>
                            <div style="padding:12px 16px;border:1px solid <?php echo $is_ok?'#16a34a':'#dc2626'; ?>;background:<?php echo $is_ok?'#ecfdf5':'#fef2f2'; ?>;color:<?php echo $is_ok?'#166534':'#b91c1c'; ?>;border-radius:10px;font-weight:600;font-size:.75rem;">
                                <?php echo esc_html( $m['text'] ); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="" class="account-settings-form" novalidate>
                    <?php wp_nonce_field( 'preomar_save_account_settings', 'preomar_account_settings_nonce' ); ?>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;">
                        <div>
                            <label>Nazwa wyświetlana</label>
                            <input type="text" name="display_name" value="<?php echo $display_name; ?>">
                        </div>
                        <div>
                            <label>Imię</label>
                            <input type="text" name="first_name" value="<?php echo $first_name; ?>">
                        </div>
                        <div>
                            <label>Nazwisko</label>
                            <input type="text" name="last_name" value="<?php echo $last_name; ?>">
                        </div>
                        <div>
                            <label>Email</label>
                            <input type="email" name="user_email" value="<?php echo $user_email; ?>">
                        </div>
                    </div>
                    <div style="margin:24px 0 0;display:flex;gap:12px;">
                        <button type="submit" class="btn-primary-save">Zapisz zmiany</button>
                        <button type="button" onclick="toggleDataForm()" class="btn-secondary">Anuluj</button>
                    </div>
                </form>
        </div>
    </div>

        <!-- Karta: Zmiana hasła -->
    <div class="settings-card" style="padding:46px 50px;width:100%;min-height:240px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h2 style="font-size:1.4rem;font-weight:700;margin:0;">Zmiana hasła</h2>
                <button onclick="togglePasswordForm()" class="btn-action">ZMIEŃ</button>
            </div>
            <p style="margin:0;font-size:.9rem;">Zabezpiecz swoje konto</p>
            <div id="passwordForm" style="display:none;margin-top:24px;padding-top:24px;border-top:1px solid #4a505a;">
                <form method="post" action="" autocomplete="off">
                    <?php wp_nonce_field( 'preomar_change_password', 'preomar_change_password_nonce' ); ?>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">
                        <div>
                            <label>Aktualne hasło</label>
                            <input type="password" name="current_pass">
                        </div>
                        <div>
                            <label>Nowe hasło</label>
                            <input type="password" name="new_pass">
                        </div>
                        <div>
                            <label>Powtórz nowe hasło</label>
                            <input type="password" name="new_pass_confirm">
                        </div>
                    </div>
                    <div style="margin:24px 0 0;display:flex;gap:12px;">
                        <button type="submit" class="btn-primary-save">Zapisz</button>
                        <button type="button" onclick="togglePasswordForm()" class="btn-secondary">Anuluj</button>
                    </div>
                </form>
        </div>
    </div>

        <!-- Karta: Adresy do wysyłki -->
                <div class="settings-card" style="padding:46px 50px;width:100%;min-height:240px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                        <h2 style="font-size:1.4rem;font-weight:700;margin:0;">Adresy do wysyłki</h2>
                        <button onclick="toggleAddressForm()" class="btn-action">ZMIEŃ</button>
                    </div>
                    <p style="margin:0;font-size:.9rem;">Zarządzaj swoimi adresami</p>
                    <?php
                    // Pobierz aktualne dane adresowe
                    $ship = [
                        'shipping_first_name' => get_user_meta($current_user->ID,'shipping_first_name',true),
                        'shipping_last_name'  => get_user_meta($current_user->ID,'shipping_last_name',true),
                        'shipping_company'    => get_user_meta($current_user->ID,'shipping_company',true),
                        'shipping_country'    => get_user_meta($current_user->ID,'shipping_country',true) ?: WC()->countries->get_base_country(),
                        'shipping_address_1'  => get_user_meta($current_user->ID,'shipping_address_1',true),
                        'shipping_address_2'  => get_user_meta($current_user->ID,'shipping_address_2',true),
                        'shipping_postcode'   => get_user_meta($current_user->ID,'shipping_postcode',true),
                        'shipping_city'       => get_user_meta($current_user->ID,'shipping_city',true),
                        'shipping_state'      => get_user_meta($current_user->ID,'shipping_state',true),
                        'shipping_phone'      => get_user_meta($current_user->ID,'shipping_phone',true),
                    ];
                    $countries = function_exists('WC') ? WC()->countries->get_countries() : [];
                    ?>
                    <div id="addressForm" style="display:none;margin-top:24px;padding-top:24px;border-top:1px solid #4a505a;">
                        <form method="post" action="" autocomplete="off">
                            <?php wp_nonce_field('preomar_change_address','preomar_change_address_nonce'); ?>
                            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;">
                                <div>
                                    <label>Imię *</label>
                                    <input type="text" name="shipping_first_name" value="<?php echo esc_attr($ship['shipping_first_name']); ?>">
                                </div>
                                <div>
                                    <label>Nazwisko *</label>
                                    <input type="text" name="shipping_last_name" value="<?php echo esc_attr($ship['shipping_last_name']); ?>">
                                </div>
                                <div>
                                    <label>Firma</label>
                                    <input type="text" name="shipping_company" value="<?php echo esc_attr($ship['shipping_company']); ?>">
                                </div>
                                <div>
                                    <label>Kraj</label>
                                    <select name="shipping_country">
                                        <?php foreach($countries as $code=>$name): ?>
                                            <option value="<?php echo esc_attr($code); ?>" <?php selected($ship['shipping_country'],$code); ?>><?php echo esc_html($name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div style="grid-column:1 / -1;">
                                    <label>Adres (linia 1) *</label>
                                    <input type="text" name="shipping_address_1" value="<?php echo esc_attr($ship['shipping_address_1']); ?>">
                                </div>
                                <div style="grid-column:1 / -1;">
                                    <label>Adres (linia 2)</label>
                                    <input type="text" name="shipping_address_2" value="<?php echo esc_attr($ship['shipping_address_2']); ?>">
                                </div>
                                <div>
                                    <label>Kod pocztowy *</label>
                                    <input type="text" name="shipping_postcode" value="<?php echo esc_attr($ship['shipping_postcode']); ?>">
                                </div>
                                <div>
                                    <label>Miasto *</label>
                                    <input type="text" name="shipping_city" value="<?php echo esc_attr($ship['shipping_city']); ?>">
                                </div>
                                <div>
                                    <label>Region / Woj.</label>
                                    <input type="text" name="shipping_state" value="<?php echo esc_attr($ship['shipping_state']); ?>">
                                </div>
                                <div>
                                    <label>Telefon</label>
                                    <input type="text" name="shipping_phone" value="<?php echo esc_attr($ship['shipping_phone']); ?>">
                                </div>
                            </div>
                            <div style="margin:24px 0 0;display:flex;gap:12px;">
                                <button type="submit" class="btn-primary-save">Zapisz adres</button>
                                <button type="button" onclick="toggleAddressForm()" class="btn-secondary">Anuluj</button>
                            </div>
                        </form>
                    </div>
                </div>

        <!-- Karta: Karty płatnicze -->
        <div class="settings-card" style="padding:46px 50px;width:100%;min-height:240px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h2 style="font-size:1.4rem;font-weight:700;margin:0;">Karty płatnicze</h2>
                <button onclick="toggleCardsSection()" class="btn-action" style="background:#1E3A8A;">ZOBACZ</button>
            </div>
            <p style="margin:0;font-size:.9rem;">Postaw na szybkie zakupy</p>
            <?php
            $card_messages = $GLOBALS['preomar_card_messages'] ?? [];
            $tokens = [];
            $has_add_endpoint = false;
            if ( function_exists('WC') ) {
                $tokens = WC_Payment_Tokens::get_customer_tokens( $current_user->ID );
                // Sprawdź czy istnieje bramka pozwalająca dodać metodę (supports add_payment_method & tokenization)
                $gws = WC()->payment_gateways()->get_available_payment_gateways();
                foreach ( $gws as $gw ) {
                    if ( $gw->supports('add_payment_method') && $gw->supports('tokenization') ) { $has_add_endpoint = true; break; }
                }
            }
            ?>
            <div id="cardsSection" style="display:none;margin-top:24px;padding-top:24px;border-top:1px solid #4a505a;">
                <?php if ( $card_messages ) : ?>
                    <div class="messages" style="margin:0 0 20px;display:flex;flex-direction:column;gap:8px;">
                        <?php foreach ( $card_messages as $m ) : $ok = $m['type'] === 'success'; ?>
                            <div style="padding:12px 16px;border:1px solid <?php echo $ok?'#16a34a':'#dc2626'; ?>;background:<?php echo $ok?'#ecfdf5':'#fef2f2'; ?>;color:<?php echo $ok?'#166534':'#b91c1c'; ?>;border-radius:10px;font-weight:600;font-size:.75rem;">
                                <?php echo esc_html( $m['text'] ); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <?php if ( $tokens ) : ?>
                    <ul class="saved-cards-list" style="list-style:none;margin:0 0 24px;padding:0;display:flex;flex-direction:column;gap:14px;">
                        <?php foreach ( $tokens as $t ) : if ( ! $t instanceof WC_Payment_Token_CC ) continue; ?>
                            <?php
                                $brand  = strtoupper( $t->get_card_type() );
                                $last4  = $t->get_last4();
                                $exp    = sprintf('%02d/%02d', $t->get_expiry_month(), $t->get_expiry_year() % 100 );
                                $is_def = $t->get_is_default();
                                $tid    = $t->get_id();
                                $nonce  = wp_create_nonce( 'cc_action_'.$tid );
                                $base_url = remove_query_arg( ['cc_action','token_id','cc_nonce'] );
                            ?>
                            <li style="display:flex;align-items:center;justify-content:space-between;background:#f1f5f9;border:1px solid #d8e0e9;padding:14px 18px;border-radius:14px;">
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <span style="display:inline-block;padding:6px 10px;background:#1E3A8A;color:#fff;font-size:.6rem;font-weight:700;border-radius:6px;letter-spacing:.5px;">KARTA</span>
                                    <strong style="font-size:.9rem;color:#1e293b;"><?php echo esc_html($brand); ?> •••• <?php echo esc_html($last4); ?></strong>
                                    <span style="color:#475569;font-size:.7rem;letter-spacing:.5px;">WAŻNA DO <?php echo esc_html($exp); ?></span>
                                    <?php if ( $is_def ) : ?><span style="background:#16a34a;color:#fff;font-size:.55rem;padding:4px 8px;border-radius:20px;font-weight:600;">DOMYŚLNA</span><?php endif; ?>
                                </div>
                                <div style="display:flex;gap:10px;">
                                    <?php if ( ! $is_def ) : ?>
                                        <a href="<?php echo esc_url( add_query_arg( ['cc_action'=>'default','token_id'=>$tid,'cc_nonce'=>$nonce], $base_url ) ); ?>" style="text-decoration:none;background:#1E3A8A;color:#fff;font-size:.65rem;font-weight:600;padding:8px 14px;border-radius:8px;">Ustaw domyślną</a>
                                    <?php endif; ?>
                                    <a href="<?php echo esc_url( add_query_arg( ['cc_action'=>'delete','token_id'=>$tid,'cc_nonce'=>$nonce], $base_url ) ); ?>" onclick="return confirm('Usunąć tę kartę?')" style="text-decoration:none;background:#dc2626;color:#fff;font-size:.65rem;font-weight:600;padding:8px 14px;border-radius:8px;">Usuń</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p style="margin:0 0 18px;font-size:.85rem;color:#475569;">Brak zapisanych kart.</p>
                <?php endif; ?>
                <?php if ( $has_add_endpoint ) : ?>
                    <?php $add_url = wc_get_endpoint_url( 'add-payment-method', '', wc_get_page_permalink( 'myaccount' ) ); ?>
                    <a href="<?php echo esc_url( $add_url ); ?>" class="btn-primary-save" style="text-decoration:none;display:inline-block;">Dodaj nową kartę</a>
                <?php else : ?>
                    <p style="margin:12px 0 0;font-size:.75rem;color:#64748b;">Aktualnie brak aktywnej bramki obsługującej zapis kart.</p>
                <?php endif; ?>
                <p style="margin:18px 0 0;font-size:.7rem;color:#64748b;">Dane kart są przechowywane bezpiecznie u dostawcy płatności. Tutaj widzisz jedynie maskowane końcówki.</p>
            </div>
        </div>
        </div>
    </div>
</div>

<script>
function toggleDataForm(){const f=document.getElementById('dataForm');f.style.display=(f.style.display==='none'||!f.style.display)?'block':'none';}
function togglePasswordForm(){const f=document.getElementById('passwordForm');f.style.display=(f.style.display==='none'||!f.style.display)?'block':'none';}
function toggleAddressForm(){const f=document.getElementById('addressForm');f.style.display=(f.style.display==='none'||!f.style.display)?'block':'none';}
function toggleCardsSection(){const f=document.getElementById('cardsSection');f.style.display=(f.style.display==='none'||!f.style.display)?'block':'none';}
</script><?php get_footer();
