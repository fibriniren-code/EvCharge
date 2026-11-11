<x-app-layout>
    <div style="max-width: 80rem; margin: 0 auto; padding: 1.5rem 1.5rem 3rem 1.5rem;">
        <div style="background-color: #1E293B; border: 1px solid #334155; border-radius: 1.25rem; padding: 1.75rem;">
            <span style="background-color: rgba(59,130,246,0.2); color: #93C5FD; border: 1px solid rgba(147,197,253,0.3); font-size: 0.65rem; font-weight: 800; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 9999px;">
                Operator Dashboard
            </span>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: #FFFFFF; margin: 0.75rem 0 0 0;">Selamat Datang, {{ Auth::user()->name }}!</h1>
            <p style="font-size: 0.875rem; color: #94A3B8; margin-top: 0.35rem; margin-bottom: 0;">
                Halaman operator sedang dikembangkan: pemantauan port real-time, peringatan gangguan,
                kendali status port/stasiun, dan penanganan keluhan pengemudi.
            </p>
        </div>
    </div>
</x-app-layout>