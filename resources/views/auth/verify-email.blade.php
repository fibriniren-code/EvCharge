<x-guest-layout title="Verifikasi email">
    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-400/10 text-emerald-300 ring-pulse relative"><x-icon name="mail" class="h-7 w-7" /></span>
    <h1 class="mt-6 text-3xl font-extrabold tracking-tight">Verifikasi email Anda</h1>
    <p class="mt-2 text-sm leading-relaxed text-slate-400">Kami telah mengirim tautan verifikasi ke email Anda. Klik tautan tersebut untuk mengaktifkan akun. Belum menerima? Kirim ulang di bawah ini.</p>

    @if (session('status') == 'verification-link-sent')
        <x-auth-session-status class="mt-6" status="Tautan verifikasi baru telah dikirim ke email Anda." />
    @endif

    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
        <form method="POST" action="{{ route('verification.send') }}" class="flex-1">
            @csrf
            <button type="submit" class="btn-primary btn-block">Kirim ulang email</button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-secondary btn-block">Keluar</button>
        </form>
    </div>
</x-guest-layout>