<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — {{ config('app.name', 'Alima') }}</title>
    <link rel="icon" type="image/x-icon" href="/favicon/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    <link rel="apple-touch-icon" href="/favicon/apple-touch-icon.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#14532d">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden"
    style="background: linear-gradient(135deg, #064e3b 0%, #065f46 30%, #047857 60%, #0d9488 100%);">

    {{-- Blur blobs --}}
    <div style="position:absolute;width:400px;height:400px;border-radius:50%;background:rgba(16,185,129,0.35);filter:blur(80px);top:-80px;left:-100px;"></div>
    <div style="position:absolute;width:350px;height:350px;border-radius:50%;background:rgba(6,182,212,0.25);filter:blur(90px);bottom:-60px;right:-80px;"></div>
    <div style="position:absolute;width:250px;height:250px;border-radius:50%;background:rgba(167,243,208,0.2);filter:blur(70px);top:50%;left:50%;transform:translate(-50%,-50%);"></div>

    <div class="w-full max-w-sm relative z-10">

        <div class="text-center mb-8">
            <img src="/favicon/android-chrome-192x192.png" alt="Alima"
                class="w-16 h-16 rounded-2xl mx-auto mb-4 shadow-lg">
            <h1 class="text-3xl font-bold text-white">Alima</h1>
            <p class="text-sm mt-1" style="color:rgba(255,255,255,0.65)">Panel Administrasi</p>
        </div>

        <div class="rounded-2xl p-6 sm:p-8"
            style="background:#ffffff;box-shadow:0 8px 40px rgba(0,0,0,0.25);">

        <style>
            .login-label { color: #374151; }
            .login-input {
                background: #f9fafb !important;
                border: 1px solid #d1d5db !important;
                color: #111827 !important;
            }
            .login-input::placeholder { color: #9ca3af; }
            .login-input:focus { outline: none; box-shadow: 0 0 0 2px rgba(22,163,74,0.3); border-color: #16a34a !important; }
            .login-error { background:#fef2f2; border-color:#fca5a5; color:#dc2626; }
        </style>

            <h2 class="text-base font-semibold mb-6 text-gray-800">Masuk ke akun Anda</h2>

            @if($errors->any())
                <div class="mb-4 rounded-lg px-4 py-3 text-sm login-error border">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium mb-1 login-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        autocomplete="email" autofocus
                        class="login-input w-full rounded-lg px-3 py-2 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 login-label">Password</label>
                    <input type="password" name="password"
                        autocomplete="current-password"
                        class="login-input w-full rounded-lg px-3 py-2 text-sm">
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="remember" id="remember"
                           class="rounded border-gray-300 text-green-400 focus:ring-green-400">
                    <label for="remember" class="text-sm text-gray-600">Ingat saya</label>
                </div>

                @if(config('services.turnstile.enabled'))
                <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="light"></div>
                @endif

                <button type="submit"
                    class="w-full mt-2 py-2.5 rounded-lg text-sm font-semibold transition"
                    style="background:rgba(52,211,153,0.85);color:#fff;backdrop-filter:blur(4px);">
                    Masuk
                </button>

            </form>

        </div>

    </div>

</body>
</html>
