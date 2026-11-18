<x-app-layout title="Profil Saya">
    @php $u = auth()->user(); @endphp

    <x-page-header eyebrow="Akun" title="Profil saya" subtitle="Kelola informasi akun dan keamanan Anda." />

    <div class="grid gap-5 lg:grid-cols-3">
        <aside class="reveal lg:col-span-1" style="--i:1">
            <div class="card relative overflow-hidden p-6 text-center">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-emerald-400/20 to-transparent"></div>
                <span class="relative mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-emerald-400 to-cyan-400 text-3xl font-extrabold text-ink-950 shadow-lg shadow-emerald-500/30">{{ strtoupper(mb_substr($u->name, 0, 1)) }}</span>
                <h2 class="relative mt-4 text-lg font-bold">{{ $u->name }}</h2>
                <p class="relative text-sm text-slate-500">{{ $u->email }}</p>
                <span class="badge-emerald relative mt-3">{{ ['admin' => 'Administrator', 'operator' => 'Operator SPKLU', 'driver' => 'Pengemudi'][$u->role] ?? $u->role }}</span>
                @if ($u->role === 'driver')
                    <div class="relative mt-5 grid grid-cols-2 divide-x divide-white/5 rounded-xl bg-white/[0.04] py-3 text-center">
                        <div><p class="text-lg font-extrabold text-white">Rp {{ number_format((float) $u->balance, 0, ',', '.') }}</p><p class="text-[11px] text-slate-500">Saldo</p></div>
                        <div><a href="{{ route('cars.index') }}" class="block"><p class="text-lg font-extrabold text-white">{{ $u->vehicles()->count() }}</p><p class="text-[11px] text-emerald-400">Mobil saya →</p></a></div>
                    </div>
                @endif
            </div>
        </aside>

        <div class="space-y-5 lg:col-span-2">
            <section class="card reveal p-6" style="--i:2">@include('profile.partials.update-profile-information-form')</section>
            <section class="card reveal p-6" style="--i:3">@include('profile.partials.update-password-form')</section>
            <section class="card reveal border-rose-400/20 p-6" style="--i:4">@include('profile.partials.delete-user-form')</section>
        </div>
    </div>
</x-app-layout>