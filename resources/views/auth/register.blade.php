<x-guest-layout title="Daftar">
    <p class="eyebrow">Akun pengemudi</p>
    <h1 class="mt-2 text-3xl font-extrabold tracking-tight">Buat akun baru</h1>
    <p class="mt-2 text-sm text-slate-400">Gratis dan hanya butuh satu menit. Setelah itu daftarkan mobil listrik Anda.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5" x-data="{ pw: '' }">
        @csrf

        <div>
            <x-input-label for="name" value="Nama lengkap" />
            <div class="relative">
                <x-icon name="user" class="pointer-events-none absolute left-4 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-slate-500" />
                <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Nama Anda" class="input-icon" />
            </div>
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="nama@email.com" />
                <x-input-error :messages="$errors->get('email')" />
            </div>
            <div>
                <x-input-label for="phone_number" value="No. HP (opsional)" />
                <x-text-input id="phone_number" type="tel" name="phone_number" :value="old('phone_number')" autocomplete="tel" placeholder="08xxxxxxxxxx" />
                <x-input-error :messages="$errors->get('phone_number')" />
            </div>
        </div>

        <div>
            <x-input-label for="password" value="Kata sandi" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" x-model="pw" />
            <div class="mt-2 flex gap-1.5" aria-hidden="true">
                @foreach ([1, 2, 3, 4] as $n)
                    <span class="h-1 flex-1 rounded-full bg-white/10 transition-colors duration-300"
                          :class="{ 'bg-rose-400': pw.length > 0 && pw.length < 8 && {{ $n }} <= 1, 'bg-amber-400': pw.length >= 8 && pw.length < 12 && {{ $n }} <= 2, 'bg-emerald-400': pw.length >= 12 && {{ $n }} <= 4 }"></span>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Ulangi kata sandi" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ketik ulang kata sandi" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <button type="submit" class="btn-primary btn-lg btn-block">Daftar &amp; mulai <x-icon name="arrow-right" class="h-4 w-4" /></button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-400">Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-emerald-400 transition hover:text-emerald-300">Masuk</a></p>
</x-guest-layout>