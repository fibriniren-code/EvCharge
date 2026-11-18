<header>
    <h2 class="font-bold text-rose-300">Hapus akun</h2>
    <p class="mt-1 text-sm text-slate-400">Seluruh data dan riwayat akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.</p>
</header>

<button type="button" class="btn-soft-danger mt-5" x-data x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"><x-icon name="trash" class="h-4 w-4" />Hapus akun saya</button>

<x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" maxWidth="md">
    <form method="post" action="{{ route('profile.destroy') }}" class="space-y-5">
        @csrf
        @method('delete')
        <div class="flex items-start gap-4">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-rose-400/25 bg-rose-400/10 text-rose-300"><x-icon name="alert" class="h-5 w-5" /></span>
            <div><h2 class="text-base font-bold text-white">Yakin menghapus akun?</h2><p class="mt-1.5 text-sm text-slate-400">Masukkan kata sandi untuk mengonfirmasi penghapusan akun secara permanen.</p></div>
        </div>
        <div><x-input-label for="password" value="Kata sandi" class="sr-only" /><x-text-input id="password" name="password" type="password" placeholder="Kata sandi" /><x-input-error :messages="$errors->userDeletion->get('password')" /></div>
        <div class="flex justify-end gap-2"><button type="button" class="btn-secondary" x-on:click="$dispatch('close')">Batal</button><button type="submit" class="btn-danger">Hapus akun</button></div>
    </form>
</x-modal>