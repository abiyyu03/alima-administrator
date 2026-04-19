<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — {{ config('app.name', 'Alima') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-green-50 flex items-center justify-center p-4">

    <div class="w-full max-w-sm">

        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-green-900">Alima</h1>
            <p class="text-sm text-gray-500 mt-1">Panel Administrasi</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8">

            <h2 class="text-lg font-semibold text-gray-800 mb-6">Masuk ke akun Anda</h2>

            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <x-input
                    label="Email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    autofocus
                    :error="$errors->first('email')"
                />

                <x-input
                    label="Password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    :error="$errors->first('password')"
                />

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="remember" id="remember"
                           class="rounded border-gray-300 text-green-600 focus:ring-green-400">
                    <label for="remember" class="text-sm text-gray-600">Ingat saya</label>
                </div>

                <x-btn type="submit" class="w-full justify-center mt-2">
                    Masuk
                </x-btn>

            </form>

        </div>

    </div>

</body>
</html>
