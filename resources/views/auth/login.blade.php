<x-guest-layout title="Masuk">
    <p class="eyebrow">Selamat datang kembali</p>
    <h1 class="mt-2 text-3xl font-extrabold tracking-tight">Masuk ke akun Anda</h1>
    <p class="mt-2 text-sm text-slate-400">Kelola pengisian daya, reservasi, dan operasional SPKLU dalam satu tempat.</p>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5" x-data="{ show: false }">
        @csrf

        <div>
            <x-input-label for="email" value="Alamat email" />
            <div class="relative">
                <x-icon name="mail" class="pointer-events-none absolute left-4 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-slate-500" />
                <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@email.com" class="input-icon {{ $errors->has('email') ? 'is-invalid' : '' }}" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" value="Kata sandi" />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="mb-1.5 text-xs font-semibold text-emerald-400 transition hover:text-emerald-300">Lupa sandi?</a>
                @endif
            </div>
            <div class="relative">
                <x-icon name="lock" class="pointer-events-none absolute left-4 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-slate-500" />
                <input id="password" name="password" required autocomplete="current-password" placeholder="••••••••" :type="show ? 'text' : 'password'" class="input input-icon pr-12">
                <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-slate-500 transition hover:text-white" aria-label="Tampilkan kata sandi">
                    <span x-show="!show"><x-icon name="eye" class="h-[18px] w-[18px]" /></span>
                    <span x-show="show" x-cloak><x-icon name="eye-off" class="h-[18px] w-[18px]" /></span>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2.5">
            <input id="remember_me" type="checkbox" name="remember" class="checkbox">
            <span class="text-sm text-slate-400">Ingat perangkat ini</span>
        </label>

        <button type="submit" class="btn-primary btn-lg btn-block">Masuk <x-icon name="arrow-right" class="h-4 w-4" /></button>
    </form>

    @if (app()->environment('local'))
        <div class="mt-6 rounded-2xl border border-dashed border-white/10 p-4" x-data>
            <p class="mb-3 text-[11px] font-bold uppercase tracking-widest text-slate-500">Akun demo (lokal) · klik untuk mengisi</p>
            <div class="grid grid-cols-3 gap-2">
                @foreach (['driver' => 'Driver', 'operator' => 'Operator', 'admin' => 'Admin'] as $key => $label)
                    <button type="button" class="chip justify-center" @click="document.getElementById('email').value = '{{ $key }}@evcharge.test'; document.getElementById('password').value = 'password'">{{ $label }}</button>
                @endforeach
            </div>
        </div>
    @endif

    @if (Route::has('register'))
        <p class="mt-8 text-center text-sm text-slate-400">Belum punya akun pengemudi? <a href="{{ route('register') }}" class="font-bold text-emerald-400 transition hover:text-emerald-300">Daftar sekarang</a></p>
    @endif
</x-guest-layout>