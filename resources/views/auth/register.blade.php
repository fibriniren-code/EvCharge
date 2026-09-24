<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar Akun - EVChargeHub</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background-color: #0d1520 !important;
            color: #f1f5f9;
        }
        .ev-card {
            background-color: #131d2e !important;
            border: 1px solid #1e293b;
        }
        .ev-input {
            background-color: #0b131f !important;
            border: 1px solid #334155 !important;
            color: #f8fafc !important;
        }
        .ev-input:focus {
            border-color: #10b981 !important;
            outline: none;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
        }
        .ev-btn {
            background-color: #10b981 !important;
            color: #042f2e !important;
        }
        .ev-btn:hover {
            background-color: #059669 !important;
        }
    </style>
</head>
<body class="font-sans antialiased min-h-screen flex items-center justify-center p-4">

    <!-- Card Registrasi -->
    <div class="w-full max-w-md ev-card rounded-3xl p-8 shadow-2xl my-8">
        
        <!-- Header & Logo Icon Petir Hijau -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl mb-3" style="background-color: rgba(16, 185, 129, 0.15); color: #10b981;">
                <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">EVCharge<span style="color: #10b981;">Hub</span></h1>
            <p class="text-xs text-slate-400 mt-1">Platform Ekosistem Pengisian Daya Kendaraan Listrik</p>
        </div>

        <!-- Form Registrasi -->
        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <!-- Nama Lengkap -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                <input id="name" 
                       type="text" 
                       name="name" 
                       value="{{ old('name') }}" 
                       required 
                       autofocus 
                       placeholder="Contoh: Budi Santoso"
                       class="w-full px-4 py-2.5 rounded-xl ev-input text-sm">
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>

            <!-- Alamat Email -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Alamat Email</label>
                <input id="email" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       placeholder="nama@email.com"
                       class="w-full px-4 py-2.5 rounded-xl ev-input text-sm">
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <!-- Nomor Telepon -->
            <div>
                <label for="phone_number" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Nomor Telepon</label>
                <input id="phone_number" 
                       type="text" 
                       name="phone_number" 
                       value="{{ old('phone_number') }}" 
                       placeholder="08123456789"
                       class="w-full px-4 py-2.5 rounded-xl ev-input text-sm">
                <x-input-error :messages="$errors->get('phone_number')" class="mt-1" />
            </div>

            <!-- Tipe Pengguna (Role) -->
            <div>
                <label for="role" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Peran Akun</label>
                <select id="role" 
                        name="role" 
                        class="w-full px-4 py-2.5 rounded-xl ev-input text-sm">
                    <option value="driver" selected>Pengemudi EV (Driver)</option>
                    <option value="operator">Operator Charging Station</option>
                </select>
                <x-input-error :messages="$errors->get('role')" class="mt-1" />
            </div>

            <!-- Kata Sandi -->
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Kata Sandi</label>
                <input id="password" 
                       type="password" 
                       name="password" 
                       required 
                       placeholder="••••••••"
                       class="w-full px-4 py-2.5 rounded-xl ev-input text-sm">
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <!-- Konfirmasi Sandi -->
            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Ulangi Kata Sandi</label>
                <input id="password_confirmation" 
                       type="password" 
                       name="password_confirmation" 
                       required 
                       placeholder="••••••••"
                       class="w-full px-4 py-2.5 rounded-xl ev-input text-sm">
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
            </div>

            <!-- Tombol Submit Hijau Toska -->
            <div class="pt-2">
                <button type="submit" 
                        class="w-full py-3 px-4 rounded-xl text-sm font-bold ev-btn shadow-lg transition duration-150">
                    Daftar Akun Baru
                </button>
            </div>
        </form>

        <!-- Footer Link ke Login -->
        <div class="mt-6 text-center text-xs text-slate-400">
            Sudah memiliki akun? 
            <a href="{{ route('login') }}" style="color: #10b981;" class="hover:underline font-medium ms-1">
                Masuk di sini
            </a>
        </div>
    </div>

</body>
</html>