<header>
    <h2 class="font-bold">Ubah kata sandi</h2>
    <p class="mt-1 text-sm text-slate-400">Gunakan kata sandi panjang dan acak agar akun tetap aman.</p>
</header>

<form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
    @csrf
    @method('put')

    <div><x-input-label for="update_password_current_password" value="Kata sandi saat ini" /><x-text-input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" /><x-input-error :messages="$errors->updatePassword->get('current_password')" /></div>
    <div class="grid gap-5 sm:grid-cols-2">
        <div><x-input-label for="update_password_password" value="Kata sandi baru" /><x-text-input id="update_password_password" name="password" type="password" autocomplete="new-password" /><x-input-error :messages="$errors->updatePassword->get('password')" /></div>
        <div><x-input-label for="update_password_password_confirmation" value="Ulangi kata sandi baru" /><x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" /><x-input-error :messages="$errors->updatePassword->get('password_confirmation')" /></div>
    </div>

    <div class="flex items-center gap-4">
        <button type="submit" class="btn-primary">Perbarui sandi</button>
        @if (session('status') === 'password-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)" class="flex items-center gap-1.5 text-sm font-semibold text-emerald-300"><x-icon name="check" class="h-4 w-4" />Tersimpan</p>
        @endif
    </div>
</form>