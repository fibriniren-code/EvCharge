<div class="flex h-full flex-col">
    <div class="flex h-20 shrink-0 items-center px-6">
        <a href="{{ route('dashboard') }}"><x-application-logo /></a>
    </div>

    <div class="px-5">
        <div class="flex items-center gap-2 rounded-xl border border-white/[0.07] bg-white/[0.03] px-3 py-2">
            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-emerald-400/15 text-emerald-300"><x-icon :name="$role === 'admin' ? 'shield' : ($role === 'operator' ? 'settings' : 'car')" class="h-3.5 w-3.5" /></span>
            <span class="text-[11px] font-bold uppercase tracking-widest text-slate-300">{{ $roleLabel }}</span>
        </div>
    </div>

    <nav class="mt-5 flex-1 space-y-1 overflow-y-auto px-5 pb-4">
        @foreach ($nav as [$route, $label, $icon, $pattern])
            @php
                $active = request()->routeIs($pattern);
                $badge = $navBadges[$route] ?? 0;
            @endphp
            <a href="{{ route($route) }}" class="nav-item group reveal {{ $active ? 'nav-item-active' : '' }}" style="--i: {{ $loop->index }}">
                <x-icon :name="$icon" class="h-5 w-5 shrink-0 transition duration-200 group-hover:scale-110 {{ $active ? 'text-emerald-300' : '' }}" />
                <span class="flex-1">{{ $label }}</span>
                @if ($badge > 0)
                    <span class="rounded-full bg-rose-500 px-2 py-0.5 text-[10px] font-extrabold text-white">{{ $badge }}</span>
                @endif
            </a>
        @endforeach

        @if ($activeSession)
            <a href="{{ route('driver.charging.monitor', $activeSession->id_session) }}" class="mt-3 flex items-center gap-3 rounded-xl border border-emerald-400/30 bg-emerald-400/10 px-3.5 py-3 text-sm font-bold text-emerald-300 transition hover:bg-emerald-400/20">
                <span class="relative flex h-2.5 w-2.5"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400"></span></span>
                Pantau charging
                <x-icon name="arrow-right" class="ml-auto h-4 w-4" />
            </a>
        @endif
    </nav>

    <div class="shrink-0 border-t border-white/[0.06] p-4">
        <div class="flex items-center gap-3 rounded-xl bg-white/[0.03] p-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-cyan-400 text-sm font-extrabold text-ink-950">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-bold text-white">{{ $user->name }}</p>
                <p class="truncate text-[11px] text-slate-500">{{ $user->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-lg p-2 text-slate-500 transition hover:bg-rose-500/10 hover:text-rose-300" title="Keluar" aria-label="Keluar" data-no-loading><x-icon name="logout" class="h-[18px] w-[18px]" /></button>
            </form>
        </div>
    </div>
</div>
