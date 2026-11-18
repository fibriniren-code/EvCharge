<header>
    <h2 class="font-bold">Informasi profil</h2>
    <p class="mt-1 text-sm text-slate-400">Perbarui nama, email, dan nomor HP akun Anda.</p>
</header>

<form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

<form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
    @csrf
    @method('patch')

    <div class="grid gap-5 sm:grid-cols-2">
        <div><x-input-label for="name" value="Nama lengkap" /><x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required autofocus autocomplete="name" /><x-input-error :messages="$errors->get('name')" /></div>
        <div><x-input-label for="phone_number" value="No. HP" /><x-text-input id="phone_number" name="phone_number" type="tel" :value="old('phone_number', $user->phone_number)" autocomplete="tel" /><x-input-error :messages="$errors->get('phone_number')" /></div>
        <div class="sm:col-span-2">
            <x-input-label for="email" value="Email" /><x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" /><x-input-error :messages="$errors->get('email')" />
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="mt-3 text-sm text-amber-300">Email belum diverifikasi. <button form="send-verification" class="font-bold underline">Kirim ulang tautan verifikasi</button></p>
                @if (session('status') === 'verification-link-sent')<p class="mt-2 text-sm font-semibold text-emerald-300">Tautan verifikasi baru telah dikirim.</p>@endif
            @endif
        </div>
    </div>

    <div class="flex items-center gap-4">
        <button type="submit" class="btn-primary">Simpan</button>
        @if (session('status') === 'profile-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)" class="flex items-center gap-1.5 text-sm font-semibold text-emerald-300"><x-icon name="check" class="h-4 w-4" />Tersimpan</p>
        @endif
    </div>
</form>