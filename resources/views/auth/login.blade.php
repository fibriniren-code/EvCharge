<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - EVChargeHub</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4 sm:p-6 relative overflow-hidden">

    <!-- Efek Cahaya Latar Belakang -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-teal-500/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        <!-- Kartu Login -->
        <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/60 rounded-3xl p-8 shadow-2xl shadow-emerald-950/40">
            
            <!-- Logo & Header Sistem -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-slate-950 shadow-lg shadow-emerald-500/30 mb-4">
                    <!-- Ikon Charger EV -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 fill-current" viewBox="0 0 24 24">
                        <path d="M11 20V12H8L13 2V10H16L11 20Z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white tracking-tight">EVChargeHub</h1>
                <p class="text-sm text-slate-400 mt-1">Platform Ekosistem Pengisian Daya Kendaraan Listrik</p>
            </div>

            <!-- Pesan Error Validasi -->
            @if ($errors->any())
                <div class="mb-5 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-sm text-rose-400">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form Autentikasi -->
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Input Email -->
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Alamat Email</label>
                    <div class="relative">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                            placeholder="nama@email.com"
                            class="w-full px-4 py-3 bg-slate-900/60 border border-slate-700 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Input Password -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Kata Sandi</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs text-emerald-400 hover:text-emerald-300 transition-colors">Lupa sandi?</a>
                        @endif
                    </div>
                    <div class="relative">
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full px-4 py-3 bg-slate-900/60 border border-slate-700 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Checkbox Ingat Saya -->
                <div class="flex items-center justify-between">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer">
                        <input id="remember_me" type="checkbox" name="remember" 
                            class="rounded bg-slate-900 border-slate-700 text-emerald-500 focus:ring-emerald-400 focus:ring-offset-slate-800">
                        <span class="ms-2 text-xs text-slate-400">Ingat perangkat ini</span>
                    </label>
                </div>

                <!-- Tombol Submit Masuk -->
                <button type="submit" 
                    class="w-full py-3 px-4 bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-bold rounded-xl shadow-lg shadow-emerald-500/20 active:scale-[0.99] transition-all text-sm">
                    Masuk ke Sistem
                </button>
            </form>

            <!-- Tautan Registrasi Akun Pengemudi -->
            @if (Route::has('register'))
                <div class="mt-8 pt-6 border-t border-slate-700/60 text-center">
                    <p class="text-xs text-slate-400">
                        Belum memiliki akun pengemudi? 
                        <a href="{{ route('register') }}" class="font-semibold text-emerald-400 hover:text-emerald-300 ms-1 transition-colors">Daftar Sekarang</a>
                    </p>
                </div>
            @endif

        </div>

        <!-- Footer Info -->
        <p class="text-center text-xs text-slate-500 mt-6">
            &copy; 2026 EVChargeHub. Sistem Manajemen Charging Station EV.
        </p>
    </div>

</body>
</html>