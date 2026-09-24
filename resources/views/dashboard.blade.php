<x-app-layout>
    {{-- Background Utama: Biru Navy Pekat (#0A1128) --}}
    <div style="background-color: #0A1128; min-height: 100vh; color: #E2E8F0; padding-bottom: 3rem;">
        
        <!-- Navbar Atas: Biru Navy Sedang (#1C2541) -->
        <nav style="background-color: #1C2541; border-bottom: 1px solid #2A3859; padding: 1rem 1.5rem;">
            <div style="max-width: 80rem; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
                
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none;">
                    <div style="background-color: #2563EB; padding: 0.5rem; border-radius: 0.75rem; color: #FFFFFF; font-weight: bold;">⚡</div>
                    <div>
                        <span style="font-size: 1.25rem; font-weight: bold; color: #FFFFFF;">EVCharge<span style="color: #60A5FA;">Hub</span></span>
                        <span style="display: block; font-size: 0.65rem; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.05em;">SPKLU Platform</span>
                    </div>
                </a>

                <!-- Menu Navigasi Utama -->
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <a href="#" style="padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.875rem; color: #CBD5E1; text-decoration: none;">Cari SPKLU</a>
                    <a href="#" style="padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.875rem; color: #CBD5E1; text-decoration: none;">Mobil Saya</a>
                    <a href="#" style="padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.875rem; color: #CBD5E1; text-decoration: none;">Riwayat Pengisian</a>
                    
                    {{-- Hanya tampil jika role user adalah operator atau admin --}}
                    @if (in_array(Auth::user()->role, ['operator', 'admin']))
                        <a href="#" style="padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; background-color: #1D4ED8; color: #FFFFFF; text-decoration: none;">
                            Dashboard Operator
                        </a>
                    @endif
                </div>

                <!-- Profil Pengguna & Dropdown -->
                <div style="display: flex; align-items: center; gap: 1rem;">
                    
                    <!-- Widget Saldo -->
                    <div style="background-color: #0A1128; padding: 0.35rem 0.75rem; border-radius: 0.5rem; border: 1px solid #2A3859; display: flex; align-items: center; gap: 0.75rem;">
                        <div style="text-align: right;">
                            <span style="display: block; font-size: 0.65rem; color: #94A3B8;">Saldo</span>
                            <span style="color: #60A5FA; font-weight: bold; font-size: 0.875rem;">
                                Rp {{ number_format(Auth::user()->balance ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                        <a href="#" title="Top Up Saldo" style="background-color: #2563EB; color: #FFFFFF; text-decoration: none; padding: 0.2rem 0.5rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: bold;">
                            + Isi
                        </a>
                    </div>

                    <!-- Dropdown Profil Menggunakan Alpine.js -->
                    <div x-data="{ open: false }" style="position: relative;">
                        <button @click="open = !open" style="display: flex; align-items: center; gap: 0.75rem; background-color: rgba(42,56,89,0.5); padding: 0.35rem 0.75rem; border-radius: 0.75rem; border: 1px solid #2A3859; cursor: pointer; color: inherit; text-align: left;">
                            <div style="width: 2rem; height: 2rem; border-radius: 9999px; background-color: #2563EB; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #FFFFFF; font-size: 0.875rem;">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <div>
                                <span style="display: block; font-size: 0.75rem; font-weight: 600; color: #FFFFFF;">{{ Auth::user()->name }}</span>
                                <span style="display: block; font-size: 0.6rem; color: #60A5FA; text-transform: uppercase;">PERAN: {{ Auth::user()->role }}</span>
                            </div>
                        </button>

                        <!-- Menu Pop-up Dropdown -->
                        <div x-show="open" @click.away="open = false" style="position: absolute; right: 0; margin-top: 0.5rem; width: 12rem; background-color: #1C2541; border: 1px solid #2A3859; border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.5); z-index: 50; display: none;" x-bind:style="{ display: open ? 'block' : 'none' }">
                            <a href="{{ route('profile.edit') }}" style="display: block; padding: 0.5rem 1rem; font-size: 0.875rem; color: #CBD5E1; text-decoration: none; border-bottom: 1px solid #2A3859;">Pengaturan Profil</a>
                            <a href="#" style="display: block; padding: 0.5rem 1rem; font-size: 0.875rem; color: #CBD5E1; text-decoration: none; border-bottom: 1px solid #2A3859;">Bantuan / Support</a>
                            
                            <!-- Form Logout Laravel (Bawaan Breeze/Jetstream) -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" style="width: 100%; text-align: left; padding: 0.5rem 1rem; font-size: 0.875rem; color: #F87171; background: none; border: none; cursor: pointer;">
                                    Keluar (Logout)
                                </button>
                            </form>
                        </div>
                    </div>

                </div>

            </div>
        </nav>

        <!-- Konten Utama -->
        <div style="max-width: 80rem; margin: 0 auto; padding: 2rem 1rem;">
            
            @if (Auth::user()->role === 'operator')

                <!-- ============================================== -->
                <!-- TAMPILAN OPERATOR                              -->
                <!-- ============================================== -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                            <span style="background-color: rgba(37,99,235,0.2); color: #60A5FA; border: 1px solid rgba(96,165,250,0.3); font-size: 0.65rem; text-transform: uppercase; font-weight: bold; padding: 0.15rem 0.5rem; border-radius: 9999px;">
                                Panel Kontrol Operasional
                            </span>
                            <span style="font-size: 0.75rem; color: #94A3B8;">EVChargeHub Operations Hub</span>
                        </div>
                        <h1 style="font-size: 1.875rem; font-weight: 800; color: #FFFFFF; margin: 0;">Dashboard Manajemen Operator & SPKLU</h1>
                        <p style="font-size: 0.875rem; color: #CBD5E1; margin-top: 0.25rem;">Pantau utilisasi perangkat charger, sesuaikan tarif per kWh, dan tangani gangguan perangkat secara real-time.</p>
                    </div>
                    <div>
                        <a href="#" style="display: inline-flex; align-items: center; background-color: #2563EB; color: #FFFFFF; font-weight: bold; padding: 0.65rem 1rem; border-radius: 0.75rem; font-size: 0.875rem; text-decoration: none;">
                            + Tambah Stasiun SPKLU Baru
                        </a>
                    </div>
                </div>

                <!-- Kartu Statistik -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                    <div style="background-color: #1C2541; border: 1px solid #2A3859; border-radius: 1rem; padding: 1.25rem;">
                        <span style="font-size: 0.75rem; font-weight: 500; color: #94A3B8;">Total Pendapatan</span>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #60A5FA; margin-top: 0.5rem;">Rp 281.117</div>
                        <span style="font-size: 0.65rem; color: #94A3B8; margin-top: 0.25rem; display: block;">Dari seluruh transaksi lunas</span>
                    </div>

                    <div style="background-color: #1C2541; border: 1px solid #2A3859; border-radius: 1rem; padding: 1.25rem;">
                        <span style="font-size: 0.75rem; font-weight: 500; color: #94A3B8;">Total Energi Disalurkan</span>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #FFFFFF; margin-top: 0.5rem;">112.0 <span style="font-size: 0.875rem; font-weight: normal; color: #94A3B8;">kWh</span></div>
                        <span style="font-size: 0.65rem; color: #94A3B8; margin-top: 0.25rem; display: block;">Daya tersuplay ke kendaraan</span>
                    </div>

                    <div style="background-color: #1C2541; border: 1px solid #2A3859; border-radius: 1rem; padding: 1.25rem;">
                        <span style="font-size: 0.75rem; font-weight: 500; color: #94A3B8;">Tingkat Utilisasi Charger</span>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #FBBF24; margin-top: 0.5rem;">11.1%</div>
                        <span style="font-size: 0.65rem; color: #94A3B8; margin-top: 0.25rem; display: block;">Port aktif terhadap total port</span>
                    </div>

                    <div style="background-color: #1C2541; border: 1px solid #2A3859; border-radius: 1rem; padding: 1.25rem;">
                        <span style="font-size: 0.75rem; font-weight: 500; color: #94A3B8;">Sesi Pengisian Aktif</span>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #FFFFFF; margin-top: 0.5rem;">0</div>
                        <span style="font-size: 0.65rem; color: #94A3B8; margin-top: 0.25rem; display: block;">Sedang menyalurkan daya</span>
                    </div>

                    <div style="background-color: #1C2541; border: 1px solid #2A3859; border-radius: 1rem; padding: 1.25rem; border-left: 4px solid #F43F5E;">
                        <span style="font-size: 0.75rem; font-weight: 500; color: #94A3B8;">Gangguan / Fault Unit</span>
                        <div style="font-size: 1.5rem; font-weight: bold; color: #FB7185; margin-top: 0.5rem;">1</div>
                        <span style="font-size: 0.65rem; color: #FDA4AF; margin-top: 0.25rem; display: block;">Memerlukan perhatian teknis</span>
                    </div>
                </div>

                <!-- Tabel Telemetri -->
                <div style="background-color: #1C2541; border: 1px solid #2A3859; border-radius: 1rem; padding: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                        <div>
                            <h3 style="font-size: 1.125rem; font-weight: bold; color: #FFFFFF; margin: 0;">Status & Kendali Unit Charger (Telemetri)</h3>
                            <p style="font-size: 0.75rem; color: #94A3B8; margin: 0;">Ubah status perangkat untuk pemeliharaan, simulasi kerusakan, atau pembukaan kunci dispenser.</p>
                        </div>
                        <span style="background-color: #0A1128; border: 1px solid #2A3859; color: #60A5FA; font-size: 0.75rem; padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-weight: 600;">
                            9 Total Unit
                        </span>
                    </div>

                    <div style="overflow-x: auto;">
                        <table style="width: 100%; text-align: left; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 1px solid #2A3859; font-size: 0.7rem; text-transform: uppercase; color: #94A3B8;">
                                    <th style="padding: 0.75rem 1rem;">Kode & Nama Perangkat</th>
                                    <th style="padding: 0.75rem 1rem;">Lokasi Stasiun</th>
                                    <th style="padding: 0.75rem 1rem;">Konektor & Daya</th>
                                    <th style="padding: 0.75rem 1rem;">Status Terkini</th>
                                    <th style="padding: 0.75rem 1rem;">Sesi Berjalan</th>
                                    <th style="padding: 0.75rem 1rem; text-align: right;">Ubah Status (Kontrol Cepat)</th>
                                </tr>
                            </thead>
                            <tbody style="font-size: 0.875rem;">
                                <tr style="border-bottom: 1px solid rgba(42,56,89,0.4);">
                                    <td style="padding: 1rem;">
                                        <span style="display: block; font-weight: bold; color: #60A5FA;">SNC-DC-01</span>
                                        <span style="font-size: 0.75rem; color: #94A3B8;">Ultra Fast Charger A (150 kW)</span>
                                    </td>
                                    <td style="padding: 1rem; color: #E2E8F0;">SPKLU Senayan City Mall</td>
                                    <td style="padding: 1rem; color: #E2E8F0; font-weight: 500;">CCS2 • 150 kW</td>
                                    <td style="padding: 1rem;">
                                        <span style="display: inline-flex; align-items: center; padding: 0.25rem 0.65rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; background-color: rgba(37,99,235,0.15); color: #60A5FA; border: 1px solid rgba(96,165,250,0.3);">
                                            ● Tersedia
                                        </span>
                                    </td>
                                    <td style="padding: 1rem; color: #94A3B8;">-</td>
                                    <td style="padding: 1rem; text-align: right;">
                                        <select style="background-color: #0A1128; border: 1px solid #2A3859; color: #E2E8F0; font-size: 0.75rem; border-radius: 0.5rem; padding: 0.35rem 0.75rem;">
                                            <option>Set: Tersedia</option>
                                            <option>Set: Occupied</option>
                                            <option>Set: Maintenance</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            @else

                <!-- ============================================== -->
                <!-- TAMPILAN DRIVER                                -->
                <!-- ============================================== -->
                <div style="margin-bottom: 1.5rem;">
                    <h1 style="font-size: 1.875rem; font-weight: 800; color: #FFFFFF; margin: 0;">Selamat Datang, {{ Auth::user()->name }}!</h1>
                    <p style="font-size: 0.875rem; color: #CBD5E1; margin-top: 0.25rem;">Kelola kendaraan listrikmu dan pantau aktivitas pengisian daya.</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                    <!-- Kartu Mobil Saya -->
                    <div style="background-color: #1C2541; border: 1px solid #2A3859; border-radius: 1rem; padding: 1.5rem;">
                        <h3 style="font-size: 1.125rem; font-weight: bold; color: #FFFFFF; margin-bottom: 0.5rem;">Mobil Saya</h3>
                        <p style="font-size: 0.875rem; color: #94A3B8; margin-bottom: 1.25rem;">Kelola daftar dan spesifikasi kendaraan listrik milikmu.</p>
                        <a href="#" style="display: inline-block; background-color: #2563EB; color: #FFFFFF; font-weight: bold; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.875rem;">Lihat Kendaraan</a>
                    </div>

                    <!-- Kartu Riwayat Pengisian -->
                    <div style="background-color: #1C2541; border: 1px solid #2A3859; border-radius: 1rem; padding: 1.5rem;">
                        <h3 style="font-size: 1.125rem; font-weight: bold; color: #FFFFFF; margin-bottom: 0.5rem;">Riwayat Pengisian</h3>
                        <p style="font-size: 0.875rem; color: #94A3B8; margin-bottom: 1.25rem;">Cek catatan transaksi dan total kWh pengisian sebelumnya.</p>
                        <a href="#" style="display: inline-block; background-color: #2A3859; color: #FFFFFF; font-weight: bold; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none; font-size: 0.875rem;">Lihat Riwayat</a>
                    </div>
                </div>

            @endif

        </div>
    </div>
</x-app-layout>