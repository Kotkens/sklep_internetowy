// NATYCHMIASTOWA NAPRAWA LOGOWANIA - JAVASCRIPT v2.0
console.log('🚀 Ładowanie ulepszonego logowania v2.0 - ' + new Date().toLocaleTimeString() + '...');

// Czekaj aż strona się załaduje
document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 Strona załadowana, szukam formularza...');
    
    // Sprawdź czy to strona rejestracji
    const urlParams = new URLSearchParams(window.location.search);
    const isRegisterPage = urlParams.get('action') === 'register';
    
    console.log('📋 Tryb strony:', isRegisterPage ? 'Rejestracja' : 'Logowanie');
    
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
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
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
                        radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(255, 107, 0, 0.15) 0%, transparent 50%),
                        radial-gradient(circle at 40% 40%, rgba(30, 58, 138, 0.1) 0%, transparent 50%);
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
            `;
            document.head.appendChild(styleElement);
        }
        
        // Funkcja tworząca formularz logowania
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
                <!-- Elementy dekoracyjne -->
                <div style="
                    position: absolute;
                    top: 10%;
                    left: 10%;
                    width: 100px;
                    height: 100px;
                    background: rgba(255, 255, 255, 0.1);
                    border-radius: 50%;
                    animation: float 6s ease-in-out infinite;
                "></div>
                <div style="
                    position: absolute;
                    top: 20%;
                    right: 15%;
                    width: 60px;
                    height: 60px;
                    background: rgba(255, 107, 0, 0.2);
                    border-radius: 50%;
                    animation: float 4s ease-in-out infinite reverse;
                "></div>
                <div style="
                    position: absolute;
                    bottom: 20%;
                    left: 15%;
                    width: 80px;
                    height: 80px;
                    background: rgba(30, 58, 138, 0.15);
                    border-radius: 50%;
                    animation: float 5s ease-in-out infinite;
                "></div>
                
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
                            background: linear-gradient(135deg, #1E3A8A, #2563eb);
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: white;
                            box-shadow: 0 15px 40px rgba(30, 58, 138, 0.3);
                            font-size: 28px;
                        ">
                            👤
                        </div>
                        <h2 style="
                            color: #1E3A8A;
                            font-size: 1.6rem;
                            font-weight: 800;
                            margin: 0 0 5px;
                            letter-spacing: -0.8px;
                        ">Witaj ponownie!</h2>
                        <p style="
                            color: #64748b;
                            font-size: 14px;
                            margin: 0;
                            font-weight: 600;
                        ">Zaloguj się do swojego konta</p>
                    </div>
                    
                    <div class="enhanced-login-form" style="padding: 25px;">
                        <p style="
                            color: #1E3A8A;
                            font-size: 13px;
                            text-align: center;
                            font-weight: 500;
                            margin-bottom: 20px;
                        ">
                            Wprowadź swoje dane logowania:
                        </p>
                        
                        <!-- Prawdziwy formularz logowania -->
                        <form method="post" action="/wordpress/moje-konto/" class="woocommerce-form woocommerce-form-login login" style="margin-top: 15px;">
                            <input type="hidden" name="woocommerce_login_redirect" value="/wordpress/" />
                            <div class="form-row" style="margin-bottom: 15px;">
                                <label style="
                                    color: #1E3A8A;
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
                                    color: #1E3A8A;
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
                                <input type="checkbox" name="rememberme" value="forever" style="
                                    margin-right: 8px;
                                    width: 16px;
                                    height: 16px;
                                    accent-color: #1E3A8A;
                                " />
                                <label style="font-size: 13px; color: #475569;">Zapamiętaj mnie</label>
                            </div>
                            
                            <input type="hidden" name="woocommerce-login-nonce" value="" />
                            <input type="hidden" name="_wp_http_referer" value="/wordpress/" />
                            
                            <button type="submit" name="login" value="Zaloguj się" style="
                                background: linear-gradient(135deg, #1E3A8A, #2563eb);
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
                                box-shadow: 0 8px 25px rgba(30, 58, 138, 0.25);
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                gap: 8px;
                            " onclick="console.log('Formularz wysłany z danymi:', this.form.username.value, this.form.password.value ? 'hasło podane' : 'brak hasła');">
                                <span>Zaloguj się</span>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path d="M15 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H15" stroke="currentColor" stroke-width="2"/>
                                    <path d="M10 17L15 12L10 7" stroke="currentColor" stroke-width="2"/>
                                    <path d="M15 12H3" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </button>
                        </form>
                        
                        <div style="text-align: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(226, 232, 240, 0.5);">
                            <a href="/wordpress/wp-login.php?action=lostpassword" style="
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
                                Nie masz jeszcze konta? <a href="/wordpress/moje-konto/?action=register" style="color: #1E3A8A; text-decoration: none; font-weight: 600;">Zarejestruj się</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>`;
        
        // Dodaj nowy formularz do kontenera
        const targetContainer = wooContainer || accountContainer;
        targetContainer.innerHTML = newLoginHTML;
        console.log('✅ Nowy formularz został dodany!');
        
        // Pobierz nonce dla WooCommerce
        fetch('/wordpress/moje-konto/')
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const nonceField = doc.querySelector('input[name="woocommerce-login-nonce"]');
                if (nonceField) {
                    const formNonceField = document.querySelector('input[name="woocommerce-login-nonce"]');
                    if (formNonceField) {
                        formNonceField.value = nonceField.value;
                    }
                }
            })
            .catch(err => console.log('Nie udało się pobrać nonce:', err));
        
        // Dodaj funkcję toggle hasła
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
        
        // Dodaj hover effects
        const loginButton = document.querySelector('button[type="submit"]');
        if (loginButton) {
            loginButton.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 15px 40px rgba(30, 58, 138, 0.35)';
            });
            
            loginButton.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 8px 25px rgba(30, 58, 138, 0.25)';
            });
        }
        
        // Dodaj focus effects dla inputów
        const inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.style.borderColor = '#1E3A8A';
                this.style.boxShadow = '0 0 0 3px rgba(30, 58, 138, 0.1)';
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

console.log('📁 Skrypt instant-login-fix.js załadowany poprawnie');