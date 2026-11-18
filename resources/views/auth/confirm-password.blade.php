<x-guest-layout title="Konfirmasi sandi">
    <h1 class="text-3xl font-extrabold tracking-tight">Konfirmasi kata sandi</h1>
    <p class="mt-2 text-sm text-slate-400">Ini area aman. Masukkan kata sandi Anda untuk melanjutkan.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <x-input-label for="password" value="Kata sandi" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>
        <button type="submit" class="btn-primary btn-lg btn-block">Konfirmasi</button>
    </form>
</x-guest-layout>