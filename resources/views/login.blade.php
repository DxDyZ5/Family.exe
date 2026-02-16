<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VIP Access — Family Gallery</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('storage/photos/tornado-svgrepo-com.svg') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bg: #000; --bg-card: rgba(255,255,255,0.03); --text: #fff; --text-muted: rgba(255,255,255,0.35);
            --text-dim: rgba(255,255,255,0.15); --border: rgba(255,255,255,0.08); --accent: rgba(0,255,255,0.6);
            --accent-soft: rgba(0,255,255,0.03); --accent-border: rgba(0,255,255,0.08); --accent-strong: rgba(0,255,255,0.7);
            --btn-bg: #fff; --btn-text: #000; --grid-line: rgba(0,255,255,0.015); --error: #ff4444;
            --logo-filter: brightness(0) invert(1); --spinner-border: rgba(0,0,0,0.15); --spinner-top: #000;
        }

        [data-theme="light"] {
            --bg: #f5f5f5; --bg-card: rgba(0,0,0,0.02); --text: #111; --text-muted: rgba(0,0,0,0.45);
            --text-dim: rgba(0,0,0,0.2); --border: rgba(0,0,0,0.08); --accent: rgba(0,140,140,0.8);
            --accent-soft: rgba(0,140,140,0.04); --accent-border: rgba(0,140,140,0.12); --accent-strong: rgba(0,120,120,0.9);
            --btn-bg: #111; --btn-text: #fff; --grid-line: rgba(0,140,140,0.03); --error: #cc0000;
            --logo-filter: brightness(0); --spinner-border: rgba(255,255,255,0.2); --spinner-top: #fff;
        }

        body {
            background: var(--bg); color: var(--text);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh; display: flex; align-items: center; justify-content: center; overflow: hidden;
            transition: background 0.3s, color 0.3s;
        }

        body::before {
            content: ''; position: fixed; inset: 0; pointer-events: none;
            background-image: linear-gradient(var(--grid-line) 1px, transparent 1px), linear-gradient(90deg, var(--grid-line) 1px, transparent 1px);
            background-size: 60px 60px;
        }

        .controls-bar {
            position: fixed; top: 1rem; right: 1rem; display: flex; gap: 0.5rem; z-index: 100;
        }

        .ctrl-btn {
            font-family: 'JetBrains Mono', monospace; font-size: 0.55rem; letter-spacing: 0.08em;
            text-transform: uppercase; color: var(--text-muted); background: var(--bg-card);
            border: 1px solid var(--border); padding: 0.4rem 0.6rem; border-radius: 4px;
            cursor: pointer; transition: all 0.2s;
        }
        .ctrl-btn:hover { border-color: var(--accent); color: var(--accent); }

        .auth-container { width: 100%; max-width: 380px; padding: 2rem; }

        .logo-mark {
            width: 48px; height: 48px; margin-bottom: 2.5rem;
            filter: var(--logo-filter); opacity: 0.7;
        }

        .auth-title { font-size: 1.5rem; font-weight: 500; letter-spacing: -0.03em; margin-bottom: 0.5rem; }
        .auth-subtitle { font-size: 0.8rem; color: var(--text-muted); font-weight: 300; margin-bottom: 2.5rem; line-height: 1.6; }
        .auth-subtitle a { color: var(--accent); text-decoration: none; }
        .auth-subtitle a:hover { opacity: 0.8; }

        .input-group { position: relative; margin-bottom: 1rem; }
        .input-label {
            display: block; font-size: 0.65rem; font-weight: 500; letter-spacing: 0.12em;
            text-transform: uppercase; color: var(--text-muted); margin-bottom: 0.5rem;
        }

        .phone-wrapper {
            display: flex; align-items: center; background: var(--bg-card);
            border: 1px solid var(--border); border-radius: 8px; transition: all 0.25s ease;
        }
        .phone-wrapper:focus-within {
            border-color: var(--accent); box-shadow: 0 0 0 3px rgba(0,255,255,0.05);
        }
        .phone-prefix {
            padding: 0.85rem 0 0.85rem 1rem; font-family: 'Inter', sans-serif; font-size: 0.95rem;
            font-weight: 500; color: var(--text-muted); user-select: none; white-space: nowrap;
        }
        .phone-input {
            flex: 1; background: transparent; border: none; padding: 0.85rem 1rem 0.85rem 0.25rem;
            color: var(--text); font-family: 'Inter', sans-serif; font-size: 0.95rem; font-weight: 400;
            letter-spacing: 0.04em; outline: none;
        }
        .phone-input::placeholder { color: var(--text-dim); }

        .input-field {
            width: 100%; background: var(--bg-card); border: 1px solid var(--border); border-radius: 8px;
            padding: 0.85rem 1rem; color: var(--text); font-family: 'Inter', sans-serif; font-size: 0.95rem;
            font-weight: 400; letter-spacing: 0.02em; outline: none; transition: all 0.25s ease;
        }
        .input-field:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(0,255,255,0.05); }
        .input-field::placeholder { color: var(--text-dim); }
        .input-field.code-input { text-align: center; font-size: 1.8rem; font-weight: 600; letter-spacing: 0.5em; padding: 1rem; }

        .btn-primary {
            width: 100%; padding: 0.85rem; border: none; border-radius: 8px;
            font-family: 'Inter', sans-serif; font-size: 0.8rem; font-weight: 500; letter-spacing: 0.04em;
            cursor: pointer; transition: all 0.2s ease; background: var(--btn-bg); color: var(--btn-text);
        }
        .btn-primary:hover:not(:disabled) { opacity: 0.9; transform: translateY(-1px); }
        .btn-primary:disabled { opacity: 0.3; cursor: not-allowed; }

        .btn-secondary {
            width: 100%; padding: 0.7rem; border: 1px solid var(--border); border-radius: 8px;
            background: transparent; color: var(--text-muted); font-family: 'Inter', sans-serif;
            font-size: 0.75rem; cursor: pointer; transition: all 0.2s ease; margin-top: 0.5rem;
        }
        .btn-secondary:hover { border-color: var(--accent); color: var(--accent); }

        .resend-link {
            display: block; text-align: center; margin-top: 0.75rem; font-size: 0.7rem;
            color: var(--text-muted); cursor: pointer; transition: color 0.2s;
        }
        .resend-link:hover:not(.disabled) { color: var(--accent); }
        .resend-link.disabled { opacity: 0.3; cursor: not-allowed; }

        .error-msg { font-size: 0.75rem; color: var(--error); margin-top: 0.5rem; opacity: 0; transform: translateY(-4px); transition: all 0.2s ease; }
        .error-msg.visible { opacity: 1; transform: translateY(0); }

        .status-msg { font-size: 0.75rem; color: var(--accent); margin-bottom: 1rem; text-align: center; }

        .fade-enter { opacity: 0; transform: translateY(8px); }
        .fade-leave { opacity: 0; transform: translateY(-8px); }
        .step-panel { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }

        .spinner {
            display: inline-block; width: 14px; height: 14px;
            border: 2px solid var(--spinner-border); border-top-color: var(--spinner-top);
            border-radius: 50%; animation: spin 0.6s linear infinite; vertical-align: middle; margin-right: 6px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .telegram-info {
            background: var(--accent-soft); border: 1px solid var(--accent-border);
            border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;
        }
        .telegram-info p { font-size: 0.72rem; color: var(--text-muted); line-height: 1.7; }
        .telegram-info strong { color: var(--accent-strong); font-weight: 500; }
    </style>
</head>
<body x-data="authFlow()" :data-theme="theme">
    <!-- Controls: Theme + Language -->
    <div class="controls-bar">
        <button class="ctrl-btn" @click="toggleLang()" x-text="lang === 'en' ? 'ES' : 'EN'"></button>
        <button class="ctrl-btn" @click="toggleTheme()" x-text="theme === 'dark' ? '☀' : '☾'"></button>
    </div>

    <div class="auth-container">
        <img src="{{ asset('storage/photos/tornado-svgrepo-com.svg') }}" alt="VIP" class="logo-mark">

        <!-- STEP 1: Phone Input -->
        <div x-show="step === 'phone'" x-transition:enter="step-panel" x-transition:enter-start="fade-enter" x-transition:enter-end="" x-transition:leave="step-panel" x-transition:leave-start="" x-transition:leave-end="fade-leave">
            <h1 class="auth-title" x-text="t('title')"></h1>
            <p class="auth-subtitle">
                <span x-text="t('subtitle')"></span><br>
                <a href="https://t.me/" target="_blank" x-text="t('telegramLink')"></a>
            </p>

            <div class="telegram-info">
                <p><strong x-text="t('howTitle')"></strong> <span x-text="t('howText')"></span></p>
            </div>

            <div class="input-group">
                <label class="input-label" x-text="t('phoneLabel')"></label>
                <div class="phone-wrapper">
                    <span class="phone-prefix">+1</span>
                    <input
                        type="tel"
                        class="phone-input"
                        placeholder="000-000-0000"
                        inputmode="numeric"
                        x-model="phoneDisplay"
                        @input="formatPhone()"
                        @keydown.enter="requestCode()"
                        maxlength="12"
                        autofocus
                    >
                </div>
                <div class="error-msg" :class="{ 'visible': error && step === 'phone' }" x-text="error"></div>
            </div>

            <button class="btn-primary" @click="requestCode()" :disabled="loading || rawPhone.length < 10">
                <span x-show="loading"><span class="spinner"></span> <span x-text="t('sending')"></span></span>
                <span x-show="!loading" x-text="t('requestCode')"></span>
            </button>
        </div>

        <!-- STEP 2: OTP Code Input -->
        <div x-show="step === 'code'" x-transition:enter="step-panel" x-transition:enter-start="fade-enter" x-transition:enter-end="" x-transition:leave="step-panel" x-transition:leave-start="" x-transition:leave-end="fade-leave">
            <h1 class="auth-title" x-text="t('codeTitle')"></h1>
            <p class="auth-subtitle" x-text="t('codeSubtitle')"></p>

            <div class="status-msg" x-show="statusMsg" x-text="statusMsg"></div>

            <div class="input-group">
                <label class="input-label" x-text="t('codeLabel')"></label>
                <input
                    type="text"
                    class="input-field code-input"
                    placeholder="000000"
                    maxlength="6"
                    x-model="code"
                    @keydown.enter="verifyCode()"
                    x-ref="codeInput"
                    inputmode="numeric"
                    pattern="[0-9]*"
                >
                <div class="error-msg" :class="{ 'visible': error && step === 'code' }" x-text="error"></div>
            </div>

            <button class="btn-primary" @click="verifyCode()" :disabled="loading || code.length !== 6">
                <span x-show="loading"><span class="spinner"></span> <span x-text="t('verifying')"></span></span>
                <span x-show="!loading" x-text="t('enterGallery')"></span>
            </button>

            <button class="btn-secondary" @click="backToPhone()" x-text="t('diffNumber')"></button>

            <!-- Resend OTP -->
            <span
                class="resend-link"
                :class="{ 'disabled': resendCooldown > 0 }"
                @click="resendOtp()"
                x-text="resendCooldown > 0 ? t('resendWait').replace(':sec', resendCooldown) : t('resendNow')"
            ></span>
        </div>
    </div>

    <script>
        const translations = {
            en: {
                title: 'VIP Access', subtitle: 'Enter your phone number to receive an access code via Telegram.',
                telegramLink: "Don't have Telegram? Get it here.", howTitle: 'How it works:',
                howText: "We'll send a 6-digit code to your Telegram account. Make sure you've started a chat with our bot first.",
                phoneLabel: 'Phone Number', sending: 'Sending...', requestCode: 'Request Access Code',
                codeTitle: 'Enter Code', codeSubtitle: 'A 6-digit access code was sent to your Telegram.',
                codeLabel: 'Access Code', verifying: 'Verifying...', enterGallery: 'Enter Gallery',
                diffNumber: 'Use a different number', resendNow: "Didn't receive the code? Resend",
                resendWait: 'Resend in :sec s', networkError: 'Network error. Please try again.',
            },
            es: {
                title: 'Acceso VIP', subtitle: 'Ingresa tu número de teléfono para recibir un código de acceso por Telegram.',
                telegramLink: '¿No tienes Telegram? Descárgalo aquí.', howTitle: 'Cómo funciona:',
                howText: 'Te enviaremos un código de 6 dígitos a tu cuenta de Telegram. Asegúrate de haber iniciado un chat con nuestro bot primero.',
                phoneLabel: 'Número de Teléfono', sending: 'Enviando...', requestCode: 'Solicitar Código',
                codeTitle: 'Ingresa el Código', codeSubtitle: 'Un código de 6 dígitos fue enviado a tu Telegram.',
                codeLabel: 'Código de Acceso', verifying: 'Verificando...', enterGallery: 'Entrar a la Galería',
                diffNumber: 'Usar otro número', resendNow: '¿No recibiste el código? Reintentar',
                resendWait: 'Reenviar en :sec s', networkError: 'Error de red. Intenta de nuevo.',
            },
        };

        function authFlow() {
            return {
                step: 'phone',
                phoneDisplay: '',
                rawPhone: '',
                code: '',
                error: '',
                statusMsg: '',
                loading: false,
                resendCooldown: 0,
                resendTimer: null,
                theme: localStorage.getItem('vip-theme') || 'dark',
                lang: localStorage.getItem('vip-lang') || 'en',

                t(key) { return translations[this.lang]?.[key] || translations.en[key] || key; },

                toggleTheme() {
                    this.theme = this.theme === 'dark' ? 'light' : 'dark';
                    localStorage.setItem('vip-theme', this.theme);
                },

                toggleLang() {
                    this.lang = this.lang === 'en' ? 'es' : 'en';
                    localStorage.setItem('vip-lang', this.lang);
                },

                formatPhone() {
                    let digits = this.phoneDisplay.replace(/\D/g, '').slice(0, 10);
                    this.rawPhone = '1' + digits;
                    if (digits.length > 6) {
                        this.phoneDisplay = digits.slice(0,3) + '-' + digits.slice(3,6) + '-' + digits.slice(6);
                    } else if (digits.length > 3) {
                        this.phoneDisplay = digits.slice(0,3) + '-' + digits.slice(3);
                    } else {
                        this.phoneDisplay = digits;
                    }
                },

                startCooldown() {
                    this.resendCooldown = 60;
                    if (this.resendTimer) clearInterval(this.resendTimer);
                    this.resendTimer = setInterval(() => {
                        this.resendCooldown--;
                        if (this.resendCooldown <= 0) clearInterval(this.resendTimer);
                    }, 1000);
                },

                async requestCode() {
                    if (this.rawPhone.length < 10) return;
                    this.loading = true;
                    this.error = '';

                    try {
                        const res = await fetch('{{ route("auth.send-otp") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                            body: JSON.stringify({ phone: this.rawPhone }),
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.step = 'code';
                            this.statusMsg = data.message;
                            this.startCooldown();
                            this.$nextTick(() => this.$refs.codeInput?.focus());
                        } else {
                            this.error = data.message || data.errors?.phone?.[0] || 'Error';
                        }
                    } catch { this.error = this.t('networkError'); }

                    this.loading = false;
                },

                async verifyCode() {
                    if (this.code.length !== 6) return;
                    this.loading = true;
                    this.error = '';

                    try {
                        const res = await fetch('{{ route("auth.verify-otp") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                            body: JSON.stringify({ code: this.code }),
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            window.location.href = data.redirect;
                        } else {
                            this.error = data.message || 'Invalid code.';
                            this.code = '';
                        }
                    } catch { this.error = this.t('networkError'); }

                    this.loading = false;
                },

                async resendOtp() {
                    if (this.resendCooldown > 0) return;
                    this.error = '';
                    this.statusMsg = '';

                    try {
                        const res = await fetch('{{ route("auth.resend-otp") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                        });
                        const data = await res.json();
                        if (res.ok && data.success) {
                            this.statusMsg = data.message;
                            this.startCooldown();
                        } else {
                            this.error = data.message || 'Error';
                        }
                    } catch { this.error = this.t('networkError'); }
                },

                backToPhone() {
                    this.step = 'phone';
                    this.code = '';
                    this.error = '';
                    this.statusMsg = '';
                    if (this.resendTimer) clearInterval(this.resendTimer);
                    this.resendCooldown = 0;
                },
            };
        }
    </script>
</body>
</html>
