<nav style="background-color: #0F172A; border-bottom: 1px solid #1E293B; color: #FFFFFF; position: sticky; top: 0; z-index: 50;">
    <div style="max-width: 80rem; margin: 0 auto; padding: 0 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; height: 4rem;">
            
            <!-- Logo & Navigation -->
            <div style="display: flex; align-items: center; gap: 2rem;">
                <a href="{{ route('dashboard') }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none;">
                    <div style="background-color: #2563EB; color: #FFFFFF; padding: 0.5rem; border-radius: 0.75rem; font-weight: bold; width: 2.25rem; height: 2.25rem; display: flex; align-items: center; justify-content: center; font-size: 1rem;">⚡</div>
                    <div>
                        <span style="font-size: 1.125rem; font-weight: 800; color: #FFFFFF; letter-spacing: 0.025em;">EVCharge<span style="color: #60A5FA;">Hub</span></span>
                        <span style="display: block; font-size: 0.6rem; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin-top: -2px;">SPKLU PLATFORM</span>
                    </div>
                </a>

                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <a href="{{ route('spklu.index') }}" style="padding: 0.4rem 0.8rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 600; text-decoration: none; color: {{ request()->routeIs('spklu.*') ? '#60A5FA' : '#CBD5E1' }}; background-color: {{ request()->routeIs('spklu.*') ? 'rgba(37,99,235,0.2)' : 'transparent' }};">Cari SPKLU</a>
                    <a href="{{ route('cars.index') }}" style="padding: 0.4rem 0.8rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 600; text-decoration: none; color: {{ request()->routeIs('cars.*') ? '#60A5FA' : '#CBD5E1' }}; background-color: {{ request()->routeIs('cars.*') ? 'rgba(37,99,235,0.2)' : 'transparent' }};">Mobil Saya</a>
                    <a href="{{ route('reservations.index') }}" style="padding: 0.4rem 0.8rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 600; text-decoration: none; color: {{ request()->routeIs('reservations.*') ? '#60A5FA' : '#CBD5E1' }}; background-color: {{ request()->routeIs('reservations.*') ? 'rgba(37,99,235,0.2)' : 'transparent' }};">Reservasi Saya</a>
                    
                    <!-- DITAMBAHKAN: Menu Scan QR -->
                    <a href="{{ route('driver.scan') }}" style="padding: 0.4rem 0.8rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 600; text-decoration: none; color: {{ request()->routeIs('driver.scan') ? '#60A5FA' : '#CBD5E1' }}; background-color: {{ request()->routeIs('driver.scan') ? 'rgba(37,99,235,0.2)' : 'transparent' }};">📷 Scan QR</a>
                </div>
            </div>

            <!-- Right Area: Saldo & Profile -->
            <div style="display: flex; align-items: center; gap: 1rem;">
                <!-- Widget Saldo Pas & Rapi -->
                <div style="background-color: #1E293B; border: 1px solid #334155; border-radius: 0.75rem; padding: 0.35rem 0.75rem; display: flex; align-items: center; gap: 0.75rem;">
                    <div style="text-align: right; line-height: 1.1;">
                        <span style="display: block; font-size: 0.6rem; color: #94A3B8; font-weight: 600; text-transform: uppercase;">Saldo</span>
                        <span style="font-size: 0.85rem; font-weight: 800; color: #34D399;">Rp {{ number_format(Auth::user()->balance ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <a href="{{ route('topup.index') }}" style="background-color: #2563EB; color: #FFFFFF; font-size: 0.75rem; font-weight: 700; padding: 0.25rem 0.6rem; border-radius: 0.5rem; text-decoration: none;">+ Isi</a>
                </div>

                <!-- Profile Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button style="display: flex; align-items: center; gap: 0.6rem; background-color: #1E293B; border: 1px solid #334155; padding: 0.35rem 0.75rem; border-radius: 0.75rem; cursor: pointer; color: inherit; text-align: left;">
                            <div style="width: 1.75rem; height: 1.75rem; border-radius: 0.5rem; background-color: #2563EB; color: #FFFFFF; font-weight: bold; font-size: 0.75rem; display: flex; align-items: center; justify-content: center;">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div style="line-height: 1.1;">
                                <span style="display: block; font-size: 0.8rem; font-weight: 700; color: #FFFFFF;">{{ Auth::user()->name }}</span>
                                <span style="display: block; font-size: 0.6rem; color: #60A5FA; font-weight: 700; text-transform: uppercase;">DRIVER</span>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Profil Saya</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Keluar (Logout)</x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

        </div>
    </div>
</nav>