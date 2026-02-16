<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family VIP Gallery</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow: hidden;
            font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .splash-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
        }

        .logo-wrapper {
            position: relative;
            width: 120px;
            height: 120px;
        }

        /* Shimmer glow ring */
        .logo-wrapper::before {
            content: '';
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            background: conic-gradient(
                from 0deg,
                transparent 0%,
                rgba(0, 255, 255, 0.4) 25%,
                transparent 50%,
                rgba(0, 255, 255, 0.2) 75%,
                transparent 100%
            );
            animation: shimmer-rotate 2s linear infinite;
            filter: blur(6px);
        }

        /* Inner glow pulse */
        .logo-wrapper::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0, 255, 255, 0.08) 0%, transparent 70%);
            animation: glow-pulse 2s ease-in-out infinite;
        }

        .logo-wrapper img {
            width: 100%;
            height: 100%;
            position: relative;
            z-index: 2;
            filter: brightness(0) invert(1) drop-shadow(0 0 20px rgba(0, 255, 255, 0.3));
            animation: logo-breathe 3s ease-in-out infinite;
        }

        .splash-text {
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .splash-text h1 {
            font-size: 0.7rem;
            font-weight: 300;
            letter-spacing: 0.5em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.3);
            animation: text-fade 2s ease-in-out infinite alternate;
        }

        .loading-bar {
            width: 120px;
            height: 1px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 1px;
            overflow: hidden;
            position: relative;
            z-index: 2;
        }

        .loading-bar::after {
            content: '';
            position: absolute;
            left: -40%;
            top: 0;
            width: 40%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 255, 255, 0.6), transparent);
            animation: loading-sweep 1.5s ease-in-out infinite;
        }

        @keyframes shimmer-rotate {
            to { transform: rotate(360deg); }
        }

        @keyframes glow-pulse {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.1); }
        }

        @keyframes logo-breathe {
            0%, 100% { transform: scale(1); filter: brightness(0) invert(1) drop-shadow(0 0 20px rgba(0, 255, 255, 0.3)); }
            50% { transform: scale(1.03); filter: brightness(0) invert(1) drop-shadow(0 0 30px rgba(0, 255, 255, 0.5)); }
        }

        @keyframes text-fade {
            0% { opacity: 0.3; }
            100% { opacity: 0.6; }
        }

        @keyframes loading-sweep {
            0% { left: -40%; }
            100% { left: 100%; }
        }
    </style>
</head>
<body>
    <div class="splash-container">
        <div class="logo-wrapper">
            <img src="{{ asset('storage/photos/tornado-svgrepo-com.svg') }}" alt="VIP Gallery">
        </div>
        <div class="splash-text">
            <h1>Family VIP Gallery</h1>
        </div>
        <div class="loading-bar"></div>
    </div>

    <script>
        // Check session then redirect
        setTimeout(async () => {
            try {
                const res = await fetch('{{ route("auth.check") }}');
                const data = await res.json();
                window.location.href = data.redirect;
            } catch {
                window.location.href = '{{ route("login") }}';
            }
        }, 2000);
    </script>
</body>
</html>
