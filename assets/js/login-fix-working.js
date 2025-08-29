// NATYCHMIASTOWA NAPRAWA LOGOWANIA - JAVASCRIPT v5.1 WORKING
console.log('🚀 Ładowanie ulepszonego logowania v5.1 WORKING - ' + new Date().toLocaleTimeString() + '...');

// Test czy skrypt się w ogóle ładuje
window.preomar_login_debug = true;
console.log('✅ Skrypt login-fix-working.js został załadowany!');

// Pobierz URL-e z WordPressa (przekazane przez wp_localize_script)
const loginVars = window.preomar_login_vars || {
    site_url: '',
    home_url: '',
    lostpassword_url: '/wp-login.php?action=lostpassword',
    registration_url: '/wp-login.php?action=register', 
    login_url: '/wp-login.php',
    account_url: '/my-account/',
    ajax_url: '/wp-admin/admin-ajax.php',
    nonce: '',
    registration_enabled: 'yes',
    woo_registration: 'yes'
};

console.log('🔗 Używam URL-i:', loginVars);

// Czekaj aż strona się załaduje
document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 Strona załadowana, szukam formularza...');
    
    // Sprawdź czy to strona rejestracji
    const urlParams = new URLSearchParams(window.location.search);
    const isRegisterPage = urlParams.get('action') === 'register';
    const isLostPasswordPage = urlParams.get('action') === 'lostpassword';
    
    console.log('📋 Tryb strony:', isRegisterPage ? 'Rejestracja' : isLostPasswordPage ? 'Reset hasła' : 'Logowanie');
    
    // Znajdź kontener WooCommerce
    const wooContainer = document.querySelector('.woocommerce');
    const accountContainer = document.querySelector('.woocommerce-account');
    
    if (wooContainer || accountContainer) {
        console.log('✅ Znaleziono kontener WooCommerce');
        
        // Ukryj wszystkie istniejące formularze
        const existingForms = document.querySelectorAll('.u-columns, .u-column1, .u-column2, form.login, .col2-set');
        existingForms.forEach(form => {
            form.style.display = 'none';
            console.log('🚫 Ukryto stary formularz');
        });
        
        // Dodaj style do head zamiast inline
        if (!document.getElementById('enhanced-login-styles')) {
            const styleElement = document.createElement('style');
            styleElement.id = 'enhanced-login-styles';
            styleElement.textContent = `
                body {
                    background: linear-gradient(135deg, #1E3A8A 0%, #1C1C4A 100%) !important;
                    min-height: 100vh !important;
                    position: relative !important;
                }
                body::before {
                    content: '';
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background-image: 
                        radial-gradient(circle at 20% 80%, rgba(30, 58, 138, 0.3) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(255, 107, 0, 0.15) 0%, transparent 50%),
                        radial-gradient(circle at 40% 40%, rgba(28, 28, 74, 0.2) 0%, transparent 50%);
                    pointer-events: none;
                    z-index: 0;
                }
                @keyframes float {
                    0%, 100% { transform: translateY(0px); }
                    50% { transform: translateY(-20px); }
                }
                .enhanced-login-container {
                    position: relative !important;
                    z-index: 1 !important;
                }
                /* Ukryj nagłówek i stopkę na stronie logowania */
                .site-header,
                header,
                .header,
                .site-footer,
                footer,
                .footer {
                    display: none !important;
                }
                /* Ukryj nawigację */
                .main-navigation,
                .navigation,
                nav {
                    display: none !important;
                }
                /* Upewnij się że treść zajmuje całą wysokość */
                .site-content,
                .content-area,
                main {
                    padding: 0 !important;
                    margin: 0 !important;
                }
                
                /* Animacje */
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
                @keyframes spin {
                    from { transform: rotate(0deg); }
                    to { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(styleElement);
        }

        // Funkcja tworząca formularz logowania z AJAX
        function createLoginForm() {
            return `
            <div class="login-background-container" style="
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 40px 20px;
                position: relative;
            ">
                <div class="enhanced-login-container" style="
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(20px);
                    border-radius: 20px;
                    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.2);
                    border: 1px solid rgba(255, 255, 255, 0.3);
                    max-width: 400px;
                    width: 100%;
                    margin: 20px auto;
                    padding: 0;
                    overflow: hidden;
                ">
                    <div class="login-header" style="
                        text-align: center;
                        padding: 30px 30px 20px;
                        background: linear-gradient(135deg, rgba(248, 250, 252, 0.9) 0%, rgba(241, 245, 249, 0.9) 100%);
                        border-bottom: 1px solid rgba(226, 232, 240, 0.5);
                    ">
                        <div class="login-icon" style="
                            width: 60px;
                            height: 60px;
                            margin: 0 auto 15px;
                            background: linear-gradient(135deg, #3B4F7D, #2B3A5C);
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                            box-shadow: 0 15px 40px rgba(59, 79, 125, 0.3);
                            font-size: 28px;
                        ">
                            👤
                        </div>
                        <h2 style="
                            color: #3B4F7D;
                            font-size: 1.6rem;
                            font-weight: 800;
                            margin: 0 0 5px;
                            letter-spacing: -0.8px;
                        ">Zaloguj się do swojego konta</h2>
                        <p style="
                            color: #64748b;
                            font-size: 14px;
                            margin: 0;
                            font-weight: 600;
                        ">Wprowadź swoje dane logowania:</p>
                    </div>
                    
                    <div class="enhanced-login-form" style="padding: 25px;">
                        
                        <form method="post" id="custom-login-form" class="woocommerce-form woocommerce-form-login login" style="margin-top: 15px;">
                            <input type="hidden" name="action" value="preomar_login" />
                            <input type="hidden" name="nonce" value="${loginVars.nonce}" />
                            
                            <div class="form-row" style="margin-bottom: 15px;">
                                <label style="
                                    color: #3B4F7D;
                                    font-weight: 600;
                                    margin-bottom: 8px;
                                    display: block;
                                    font-size: 14px;
                                ">Nazwa użytkownika lub email *</label>
                                <div style="position: relative;">
                                    <svg style="
                                        position: absolute;
                                        left: 16px;
                                        top: 50%;
                                        transform: translateY(-50%);
                                        color: #94a3b8;
                                        width: 20px;
                                        height: 20px;
                                    " viewBox="0 0 24 24" fill="none">
                                        <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2"/>
                                        <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="currentColor" stroke-width="2"/>
                                    </svg>
                                    <input type="text" name="username" required style="
                                        width: 100%;
                                        padding: 12px 16px 12px 45px;
                                        border: 2px solid #e2e8f0;
                                        border-radius: 12px;
                                        font-size: 14px;
                                        background: rgba(248, 250, 252, 0.8);
                                        transition: all 0.3s ease;
                                    " placeholder="Wprowadź email lub nazwę użytkownika" />
                                </div>
                            </div>
                            
                            <div class="form-row" style="margin-bottom: 15px;">
                                <label style="
                                    color: #3B4F7D;
                                    font-weight: 600;
                                    margin-bottom: 8px;
                                    display: block;
                                    font-size: 14px;
                                ">Hasło *</label>
                                <div style="position: relative;">
                                    <svg style="
                                        position: absolute;
                                        left: 16px;
                                        top: 50%;
                                        transform: translateY(-50%);
                                        color: #94a3b8;
                                        width: 20px;
                                        height: 20px;
                                    " viewBox="0 0 24 24" fill="none">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                                        <circle cx="12" cy="16" r="1" fill="currentColor"/>
                                        <path d="M7 11V7C7 5.67392 7.52678 4.40215 8.46447 3.46447C9.40215 2.52678 10.6739 2 12 2C13.3261 2 14.5979 2.52678 15.5355 3.46447C16.4732 4.40215 17 5.67392 17 7V11" stroke="currentColor" stroke-width="2"/>
                                    </svg>
                                    <input type="password" name="password" id="enhanced-password" required style="
                                        width: 100%;
                                        padding: 12px 45px 12px 45px;
                                        border: 2px solid #e2e8f0;
                                        border-radius: 12px;
                                        font-size: 14px;
                                        background: rgba(248, 250, 252, 0.8);
                                        transition: all 0.3s ease;
                                    " placeholder="Wprowadź hasło" />
                                    <button type="button" onclick="togglePassword()" style="
                                        position: absolute;
                                        right: 16px;
                                        top: 50%;
                                        transform: translateY(-50%);
                                        background: none;
                                        border: none;
                                        color: #94a3b8;
                                        cursor: pointer;
                                        padding: 4px;
                                    ">👁️</button>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: center; margin: 15px 0;">
                                <input type="checkbox" name="remember" value="1" style="
                                    margin-right: 8px;
                                    width: 16px;
                                    height: 16px;
                                    accent-color: #3B4F7D;
                                " />
                                <label style="font-size: 13px; color: #475569;">Zapamiętaj mnie</label>
                            </div>
                            
                            <button type="submit" name="login" value="Zaloguj się" style="
                                background: linear-gradient(135deg, #3B4F7D, #2B3A5C);
                                color: white;
                                border: none;
                                padding: 14px 24px;
                                border-radius: 12px;
                                font-size: 15px;
                                font-weight: 600;
                                width: 100%;
                                margin: 10px 0;
                                cursor: pointer;
                                transition: all 0.3s ease;
                                box-shadow: 0 8px 25px rgba(59, 79, 125, 0.25);
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                gap: 8px;
                            ">
                                <span class="button-text">Zaloguj się</span>
                                <span class="button-spinner" style="display: none;">⏳</span>
                            </button>
                        </form>
                        
                        <div style="text-align: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(226, 232, 240, 0.5);">
                            <a href="#" onclick="showLostPasswordForm()" style="
                                color: #FF6B00;
                                text-decoration: none;
                                font-weight: 600;
                                font-size: 13px;
                            ">Nie pamiętasz hasła?</a>
                            <br><br>
                            <p style="
                                color: #64748b;
                                font-size: 12px;
                                text-align: center;
                                margin: 0;
                            ">
                                Nie masz jeszcze konta? <a href="#" onclick="showRegistrationForm()" style="color: #3B4F7D; text-decoration: none; font-weight: 600;">Zarejestruj się</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>`;
        }

        // Funkcja tworząca formularz rejestracji z AJAX (POPRAWIONE KOLORY)
        function createRegistrationForm() {
            return `
            <div class="login-background-container" style="
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 40px 20px;
                position: relative;
            ">
                <div class="enhanced-login-container" style="
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(20px);
                    border-radius: 20px;
                    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.2);
                    border: 1px solid rgba(255, 255, 255, 0.3);
                    max-width: 400px;
                    width: 100%;
                    margin: 20px auto;
                    padding: 0;
                    overflow: hidden;
                ">
                    <div class="login-header" style="
                        text-align: center;
                        padding: 30px 30px 20px;
                        background: linear-gradient(135deg, rgba(248, 250, 252, 0.9) 0%, rgba(241, 245, 249, 0.9) 100%);
                        border-bottom: 1px solid rgba(226, 232, 240, 0.5);
                    ">
                        <div class="login-icon" style="
                            width: 60px;
                            height: 60px;
                            margin: 0 auto 15px;
                            background: linear-gradient(135deg, #3B4F7D, #2B3A5C);
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                            box-shadow: 0 15px 40px rgba(59, 79, 125, 0.3);
                            font-size: 28px;
                        ">
                            ✨
                        </div>
                        <h2 style="
                            color: #3B4F7D;
                            font-size: 1.6rem;
                            font-weight: 800;
                            margin: 0 0 5px;
                            letter-spacing: -0.8px;
                        ">Dołącz do nas!</h2>
                        <p style="
                            color: #64748b;
                            font-size: 14px;
                            margin: 0;
                            font-weight: 600;
                        ">Stwórz nowe konto w sklepie</p>
                    </div>
                    
                    <div class="enhanced-login-form" style="padding: 25px;">
                        <form method="post" id="custom-register-form" class="woocommerce-form woocommerce-form-register register" style="margin-top: 15px;">
                            <input type="hidden" name="action" value="preomar_register" />
                            <input type="hidden" name="nonce" value="${loginVars.nonce}" />
                            
                            <div class="form-row" style="margin-bottom: 15px;">
                                <label style="
                                    color: #3B4F7D;
                                    font-weight: 600;
                                    margin-bottom: 8px;
                                    display: block;
                                    font-size: 14px;
                                ">Nazwa użytkownika *</label>
                                <input type="text" name="username" required style="
                                    width: 100%;
                                    padding: 12px 16px;
                                    border: 2px solid #e2e8f0;
                                    border-radius: 12px;
                                    font-size: 14px;
                                    background: rgba(248, 250, 252, 0.8);
                                    transition: all 0.3s ease;
                                " placeholder="Wprowadź nazwę użytkownika" />
                            </div>
                            
                            <div class="form-row" style="margin-bottom: 15px;">
                                <label style="
                                    color: #3B4F7D;
                                    font-weight: 600;
                                    margin-bottom: 8px;
                                    display: block;
                                    font-size: 14px;
                                ">Adres e-mail *</label>
                                <input type="email" name="email" required style="
                                    width: 100%;
                                    padding: 12px 16px;
                                    border: 2px solid #e2e8f0;
                                    border-radius: 12px;
                                    font-size: 14px;
                                    background: rgba(248, 250, 252, 0.8);
                                    transition: all 0.3s ease;
                                " placeholder="Wprowadź adres e-mail" />
                            </div>
                            
                            <div class="form-row" style="margin-bottom: 15px;">
                                <label style="
                                    color: #3B4F7D;
                                    font-weight: 600;
                                    margin-bottom: 8px;
                                    display: block;
                                    font-size: 14px;
                                ">Hasło *</label>
                                <input type="password" name="password" required style="
                                    width: 100%;
                                    padding: 12px 16px;
                                    border: 2px solid #e2e8f0;
                                    border-radius: 12px;
                                    font-size: 14px;
                                    background: rgba(248, 250, 252, 0.8);
                                    transition: all 0.3s ease;
                                " placeholder="Wprowadź hasło" />
                            </div>
                            
                            <button type="submit" name="register" value="Zarejestruj się" style="
                                background: linear-gradient(135deg, #3B4F7D, #2B3A5C);
                                color: white;
                                border: none;
                                padding: 14px 24px;
                                border-radius: 12px;
                                font-size: 15px;
                                font-weight: 600;
                                width: 100%;
                                margin: 10px 0;
                                cursor: pointer;
                                transition: all 0.3s ease;
                                box-shadow: 0 8px 25px rgba(59, 79, 125, 0.25);
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                gap: 8px;
                            ">
                                <span class="button-text">Zarejestruj się</span>
                                <span class="button-spinner" style="display: none;">⏳</span>
                            </button>
                        </form>
                        
                        <div style="text-align: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(226, 232, 240, 0.5);">
                            <p style="
                                color: #64748b;
                                font-size: 12px;
                                text-align: center;
                                margin: 0;
                            ">
                                Masz już konto? <a href="#" onclick="showLoginForm()" style="color: #3B4F7D; text-decoration: none; font-weight: 600;">Zaloguj się</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>`;
        }
        
        // Funkcja tworząca formularz resetowania hasła (POPRAWIONE KOLORY)
        function createLostPasswordForm() {
            return `
            <div class="login-background-container" style="
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 40px 20px;
                position: relative;
            ">
                <div class="enhanced-login-container" style="
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(20px);
                    border-radius: 20px;
                    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.2);
                    border: 1px solid rgba(255, 255, 255, 0.3);
                    max-width: 400px;
                    width: 100%;
                    margin: 20px auto;
                    padding: 0;
                    overflow: hidden;
                ">
                    <div class="login-header" style="
                        text-align: center;
                        padding: 30px 30px 20px;
                        background: linear-gradient(135deg, rgba(248, 250, 252, 0.9) 0%, rgba(241, 245, 249, 0.9) 100%);
                        border-bottom: 1px solid rgba(226, 232, 240, 0.5);
                    ">
                        <div class="login-icon" style="
                            width: 60px;
                            height: 60px;
                            margin: 0 auto 15px;
                            background: linear-gradient(135deg, #3B4F7D, #2B3A5C);
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                            box-shadow: 0 15px 40px rgba(59, 79, 125, 0.3);
                            font-size: 28px;
                        ">
                            🔑
                        </div>
                        <h2 style="
                            color: #3B4F7D;
                            font-size: 1.6rem;
                            font-weight: 800;
                            margin: 0 0 5px;
                            letter-spacing: -0.8px;
                        ">Zresetuj hasło</h2>
                        <p style="
                            color: #64748b;
                            font-size: 14px;
                            margin: 0;
                            font-weight: 600;
                        ">Wyślemy link resetujący na Twój e-mail</p>
                    </div>
                    
                    <div class="enhanced-login-form" style="padding: 25px;">
                        <form method="post" id="custom-lost-password-form" style="margin-top: 15px;">
                            <input type="hidden" name="action" value="preomar_lost_password" />
                            <input type="hidden" name="nonce" value="${loginVars.nonce}" />
                            
                            <div class="form-row" style="margin-bottom: 15px;">
                                <label style="
                                    color: #3B4F7D;
                                    font-weight: 600;
                                    margin-bottom: 8px;
                                    display: block;
                                    font-size: 14px;
                                ">Nazwa użytkownika lub adres e-mail *</label>
                                <input type="text" name="user_login" required style="
                                    width: 100%;
                                    padding: 12px 16px;
                                    border: 2px solid #e2e8f0;
                                    border-radius: 12px;
                                    font-size: 14px;
                                    background: rgba(248, 250, 252, 0.8);
                                    transition: all 0.3s ease;
                                " placeholder="Wprowadź nazwę użytkownika lub e-mail" />
                            </div>
                            
                            <button type="submit" style="
                                background: linear-gradient(135deg, #3B4F7D, #2B3A5C);
                                color: white;
                                border: none;
                                padding: 14px 24px;
                                border-radius: 12px;
                                font-size: 15px;
                                font-weight: 600;
                                width: 100%;
                                margin: 10px 0;
                                cursor: pointer;
                                transition: all 0.3s ease;
                                box-shadow: 0 8px 25px rgba(59, 79, 125, 0.25);
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                gap: 8px;
                            ">
                                <span class="button-text">Wyślij link</span>
                                <span class="button-spinner" style="display: none;">⏳</span>
                            </button>
                        </form>
                        
                        <div style="text-align: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(226, 232, 240, 0.5);">
                            <a href="#" onclick="showLoginForm()" style="
                                color: #3B4F7D;
                                text-decoration: none;
                                font-weight: 600;
                                font-size: 13px;
                            ">← Wróć do logowania</a>
                        </div>
                    </div>
                </div>
            </div>`;
        }
        
        // Stwórz odpowiedni formularz w zależności od trybu
        let newHTML;
        
        if (isRegisterPage) {
            newHTML = createRegistrationForm();
        } else if (isLostPasswordPage) {
            newHTML = createLostPasswordForm();
        } else {
            newHTML = createLoginForm();
        }
        
        // Dodaj nowy formularz do kontenera
        const targetContainer = wooContainer || accountContainer;
        targetContainer.innerHTML = newHTML;
        console.log('✅ Nowy formularz został dodany!', isRegisterPage ? '(Rejestracja)' : isLostPasswordPage ? '(Reset hasła)' : '(Logowanie)');
        
        // Konfiguruj AJAX formularze
        setupAjaxForms();
        
        // Dodaj funkcję toggle hasła (tylko dla logowania)
        if (!isRegisterPage && !isLostPasswordPage) {
            window.togglePassword = function() {
                const passwordField = document.getElementById('enhanced-password');
                const toggleBtn = passwordField.nextElementSibling;
                
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    toggleBtn.textContent = '🙈';
                } else {
                    passwordField.type = 'password';
                    toggleBtn.textContent = '👁️';
                }
            };
        }
        
        // Dodaj focus effects dla inputów
        const inputs = document.querySelectorAll('input[type="text"], input[type="password"], input[type="email"]');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                if (isRegisterPage) {
                    this.style.borderColor = '#3B4F7D';
                    this.style.boxShadow = '0 0 0 3px rgba(59, 79, 125, 0.1)';
                } else if (isLostPasswordPage) {
                    this.style.borderColor = '#3B4F7D';
                    this.style.boxShadow = '0 0 0 3px rgba(59, 79, 125, 0.1)';
                } else {
                    this.style.borderColor = '#3B4F7D';
                    this.style.boxShadow = '0 0 0 3px rgba(59, 79, 125, 0.1)';
                }
                this.style.background = 'rgba(255, 255, 255, 0.9)';
            });
            
            input.addEventListener('blur', function() {
                this.style.borderColor = '#e2e8f0';
                this.style.boxShadow = 'none';
                this.style.background = 'rgba(248, 250, 252, 0.8)';
            });
        });
        
    } else {
        console.log('❌ Nie znaleziono kontenera WooCommerce');
    }
});

// Funkcje do przełączania formularzy
window.showLoginForm = function() {
    window.location.href = loginVars.account_url;
};

window.showRegistrationForm = function() {
    window.location.href = loginVars.account_url + '?action=register';
};

window.showLostPasswordForm = function() {
    window.location.href = loginVars.account_url + '?action=lostpassword';
};

// Konfiguracja AJAX formularzy
function setupAjaxForms() {
    // Formularz logowania
    const loginForm = document.getElementById('custom-login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLoginSubmit);
    }
    
    // Formularz rejestracji  
    const registerForm = document.getElementById('custom-register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegisterSubmit);
    }
    
    // Formularz resetowania hasła
    const lostPasswordForm = document.getElementById('custom-lost-password-form');
    if (lostPasswordForm) {
        lostPasswordForm.addEventListener('submit', handleLostPasswordSubmit);
    }
}

// Obsługa formularza logowania
function handleLoginSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const buttonText = submitButton.querySelector('.button-text');
    const buttonSpinner = submitButton.querySelector('.button-spinner');
    
    // Pokaż spinner
    buttonText.textContent = 'Logowanie...';
    buttonSpinner.style.display = 'inline-block';
    submitButton.disabled = true;
    
    const formData = new FormData(form);
    
    fetch(loginVars.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.data.message, 'success');
            setTimeout(() => {
                window.location.href = data.data.redirect;
            }, 1000);
        } else {
            showMessage(data.data.message, 'error');
            resetButton(submitButton, buttonText, buttonSpinner, 'Zaloguj się');
        }
    })
    .catch(error => {
        showMessage('Wystąpił błąd podczas logowania. Spróbuj ponownie.', 'error');
        resetButton(submitButton, buttonText, buttonSpinner, 'Zaloguj się');
    });
}

// Obsługa formularza rejestracji
function handleRegisterSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const buttonText = submitButton.querySelector('.button-text');
    const buttonSpinner = submitButton.querySelector('.button-spinner');
    
    // Pokaż spinner
    buttonText.textContent = 'Rejestracja...';
    buttonSpinner.style.display = 'inline-block';
    submitButton.disabled = true;
    
    const formData = new FormData(form);
    
    fetch(loginVars.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.data.message, 'success');
            setTimeout(() => {
                window.location.href = data.data.redirect;
            }, 1000);
        } else {
            showMessage(data.data.message, 'error');
            resetButton(submitButton, buttonText, buttonSpinner, 'Zarejestruj się');
        }
    })
    .catch(error => {
        showMessage('Wystąpił błąd podczas rejestracji. Spróbuj ponownie.', 'error');
        resetButton(submitButton, buttonText, buttonSpinner, 'Zarejestruj się');
    });
}

// Obsługa formularza resetowania hasła
function handleLostPasswordSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const buttonText = submitButton.querySelector('.button-text');
    const buttonSpinner = submitButton.querySelector('.button-spinner');
    
    // Pokaż spinner
    buttonText.textContent = 'Wysyłanie...';
    buttonSpinner.style.display = 'inline-block';
    submitButton.disabled = true;
    
    const formData = new FormData(form);
    
    fetch(loginVars.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.data.message, 'success');
            // Po 3 sekundach wróć do formularza logowania
            setTimeout(() => {
                window.location.href = loginVars.account_url;
            }, 3000);
        } else {
            showMessage(data.data.message, 'error');
            resetButton(submitButton, buttonText, buttonSpinner, 'Wyślij link');
        }
    })
    .catch(error => {
        showMessage('Wystąpił błąd podczas wysyłania. Spróbuj ponownie.', 'error');
        resetButton(submitButton, buttonText, buttonSpinner, 'Wyślij link');
    });
}

// Przywróć przycisk do stanu początkowego
function resetButton(button, textElement, spinner, originalText) {
    textElement.textContent = originalText;
    spinner.style.display = 'none';
    button.disabled = false;
}

// Pokaż komunikat
function showMessage(message, type) {
    // Usuń poprzednie komunikaty
    const existingMessage = document.querySelector('.preomar-message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    const messageDiv = document.createElement('div');
    messageDiv.className = 'preomar-message';
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        z-index: 10000;
        font-weight: 600;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        animation: slideIn 0.3s ease-out;
    `;
    
    messageDiv.textContent = message;
    document.body.appendChild(messageDiv);
    
    // Usuń po 5 sekundach
    setTimeout(() => {
        messageDiv.style.animation = 'slideOut 0.3s ease-in forwards';
        setTimeout(() => messageDiv.remove(), 300);
    }, 5000);
}

console.log('📁 Skrypt login-fix-working.js załadowany poprawnie');
