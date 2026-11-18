import Alpine from 'alpinejs';

window.Alpine = Alpine;

/* ------------------------------------------------------------------
 * Helper global
 * ------------------------------------------------------------------ */
window.rupiah = (n) => 'Rp ' + Math.round(Number(n) || 0).toLocaleString('id-ID');
window.csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const reduceMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const fmt = (v, d) => v.toLocaleString('id-ID', { minimumFractionDigits: d, maximumFractionDigits: d });

/* ------------------------------------------------------------------
 * Angka berjalan: <span data-count="1250" data-prefix="Rp " data-decimals="0">
 * ------------------------------------------------------------------ */
function renderCounter(el, value) {
    const d = parseInt(el.dataset.decimals || '0', 10);
    el.textContent = (el.dataset.prefix || '') + fmt(value, d) + (el.dataset.suffix || '');
}

function animateCounter(el) {
    const target = parseFloat(el.dataset.count);
    if (Number.isNaN(target)) return;
    if (reduceMotion()) return renderCounter(el, target);

    const t0 = performance.now();
    const duration = 1000;
    const tick = (now) => {
        const p = Math.min(1, (now - t0) / duration);
        renderCounter(el, target * (1 - Math.pow(1 - p, 3)));
        if (p < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
}

function initCounters(root = document) {
    const els = root.querySelectorAll('[data-count]');
    if (!('IntersectionObserver' in window)) return els.forEach((el) => renderCounter(el, parseFloat(el.dataset.count)));
    const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) { animateCounter(e.target); io.unobserve(e.target); }
        });
    }, { threshold: 0.4 });
    els.forEach((el) => io.observe(el));
}

/* ------------------------------------------------------------------
 * Muncul saat di-scroll: <div class="sr" data-reveal style="--i:2">
 * ------------------------------------------------------------------ */
function initReveal() {
    const els = document.querySelectorAll('[data-reveal]');
    if (!('IntersectionObserver' in window)) return els.forEach((el) => el.classList.add('is-visible'));
    const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    els.forEach((el) => io.observe(el));
}

/* ------------------------------------------------------------------
 * Region "live": isi otomatis disegarkan dari server tanpa reload.
 *   <div data-live data-live-id="board" data-interval="8000"> ... </div>
 * ------------------------------------------------------------------ */
function initLive() {
    document.querySelectorAll('[data-live]').forEach((box) => {
        const url = box.dataset.live || window.location.href;
        const every = parseInt(box.dataset.interval || '10000', 10);
        const id = box.dataset.liveId;
        let last = null;
        let busy = false;

        const tick = async () => {
            const active = document.activeElement;
            const typing = active && box.contains(active) && ['INPUT', 'TEXTAREA', 'SELECT'].includes(active.tagName);
            if (document.hidden || busy || typing || document.body.classList.contains('modal-open')) return;

            busy = true;
            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' }, credentials: 'same-origin' });
                if (!res.ok) return;
                const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
                const next = doc.querySelector(`[data-live-id="${id}"]`);
                if (next && next.innerHTML !== last) {
                    const firstRun = last === null;
                    last = next.innerHTML;
                    if (!firstRun) {
                        box.innerHTML = next.innerHTML;
                        box.querySelectorAll('[data-count]').forEach((el) => renderCounter(el, parseFloat(el.dataset.count)));
                    }
                }
            } catch (e) { /* abaikan error jaringan sesaat */ } finally { busy = false; }
        };

        tick();
        setInterval(tick, every);
    });
}

/* ------------------------------------------------------------------
 * Indikator loading: progress bar atas + spinner tombol submit
 * ------------------------------------------------------------------ */
function initLoading() {
    const bar = document.getElementById('page-progress');

    document.addEventListener('click', (e) => {
        const a = e.target.closest('a[href]');
        if (!a || !bar || a.target === '_blank' || a.hasAttribute('download') || e.metaKey || e.ctrlKey || e.shiftKey) return;
        const href = a.getAttribute('href') || '';
        if (href.startsWith('#') || href.startsWith('javascript') || a.origin !== window.location.origin) return;
        bar.classList.add('run');
    });

    document.addEventListener('submit', (e) => {
        const form = e.target;
        setTimeout(() => {
            if (e.defaultPrevented || form.dataset.noLoading !== undefined) return;
            bar?.classList.add('run');
            const btn = e.submitter || form.querySelector('[type="submit"]');
            if (btn && btn.dataset.noLoading === undefined && btn.classList.contains('btn')) {
                btn.classList.add('is-loading');
                btn.disabled = true;
            }
        }, 0);
    });

    window.addEventListener('pageshow', (e) => {
        if (!e.persisted) return;
        bar?.classList.remove('run');
        document.querySelectorAll('.btn.is-loading').forEach((b) => { b.classList.remove('is-loading'); b.disabled = false; });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initCounters();
    initReveal();
    initLive();
    initLoading();
});

Alpine.start();