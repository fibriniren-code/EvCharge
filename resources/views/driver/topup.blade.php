<x-app-layout>
    <div class="py-10 min-h-screen">
        <!-- Container dibuat max-w-xl agar tidak terlalu lebar -->
        <div class="max-w-xl mx-auto px-4 sm:px-6">
            
            <!-- Judul Halaman -->
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-white tracking-wide">
                    Top Up Saldo EVCharge
                </h2>
                <p class="text-gray-400 text-sm mt-1">
                    Isi saldo dompet digital untuk pembayaran charging kendaraan listrik Anda.
                </p>
            </div>

            <!-- Card Utama -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl p-6 sm:p-8">
                
                @if (session('success'))
                    <div class="mb-6 text-sm font-medium text-emerald-300 bg-emerald-950/80 border border-emerald-800 p-4 rounded-xl flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <!-- Card Info Saldo -->
                <div class="mb-6 p-5 rounded-xl bg-gradient-to-r from-blue-900/30 via-slate-800 to-slate-800 border border-blue-500/30 text-center sm:text-left">
                    <span class="text-xs font-semibold uppercase tracking-wider text-blue-400">Saldo Saat Ini</span>
                    <div class="text-3xl font-extrabold text-white mt-1">
                        Rp {{ number_format(Auth::user()->balance ?? 0, 0, ',', '.') }}
                    </div>
                </div>

                <!-- Form Top Up -->
                <form action="{{ route('topup.store') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label for="amount" class="block text-sm font-medium text-slate-300 mb-2">
                            Nominal Top Up (Minimal Rp 10.000)
                        </label>
                        
                        <!-- Wrapper Input dengan padding-left (pl-14) agar tidak bertumpukan -->
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-slate-400 font-bold text-base">Rp</span>
                            </div>
                            <input type="number" 
                                   name="amount" 
                                   id="amount" 
                                   min="10000" 
                                   step="1000" 
                                   placeholder="50000"
                                   style="padding-left: 3.25rem !important;"
                                   class="w-full bg-slate-800 border border-slate-700 rounded-xl py-3 pr-4 text-white placeholder-slate-500 text-lg font-semibold focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30 transition-all duration-200" 
                                   required>
                        </div>
                        
                        @error('amount')
                            <p class="mt-2 text-sm text-rose-400 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Shortcut Pilihan Nominal (Menyamping / Grid 3 Kolom) -->
                    <div>
                        <span class="block text-xs font-medium text-slate-400 mb-2">Pilihan Nominal Cepat:</span>
                        <div class="grid grid-cols-3 gap-3">
                            <button type="button" onclick="document.getElementById('amount').value=20000" class="py-2.5 px-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl text-xs sm:text-sm font-semibold text-slate-200 text-center transition">
                                + Rp 20rb
                            </button>
                            <button type="button" onclick="document.getElementById('amount').value=50000" class="py-2.5 px-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl text-xs sm:text-sm font-semibold text-slate-200 text-center transition">
                                + Rp 50rb
                            </button>
                            <button type="button" onclick="document.getElementById('amount').value=100000" class="py-2.5 px-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl text-xs sm:text-sm font-semibold text-slate-200 text-center transition">
                                + Rp 100rb
                            </button>
                        </div>
                    </div>

                    <!-- Tombol Submit -->
                    <div class="pt-2">
                        <button type="submit" class="w-full py-3.5 bg-blue-600 hover:bg-blue-500 active:bg-blue-700 text-white font-bold text-base rounded-xl shadow-lg shadow-blue-600/30 transition-all duration-200">
                            Konfirmasi Top Up
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>