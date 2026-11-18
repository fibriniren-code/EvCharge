<x-guest-layout title="Atur ulang sandi">
    <h1 class="text-3xl font-extrabold tracking-tight">Buat kata sandi baru</h1>
    <p class="mt-2 text-sm text-slate-400">Pilih kata sandi yang kuat dan belum pernah dipakai di tempat lain.</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>
        <div>
            <x-input-label for="password" value="Kata sandi baru" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>
        <div>
            <x-input-label for="password_confirmation" value="Ulangi kata sandi" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>
        <button type="submit" class="btn-primary btn-lg btn-block">Simpan kata sandi</button>
    </form>
</x-guest-layout>