{{-- Lapisan tema kebun binatang untuk halaman admin (tanpa mengubah logika JS halaman) --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@500;600;700;800&display=swap" rel="stylesheet">
<link href="{{ asset('assets/css/zoo-theme.css') }}?v=2" rel="stylesheet">
<style>
    body.zoo-admin { background: linear-gradient(180deg, #fff7d6 0%, #e9f9dc 38%, #c9efc0 70%, #a7e0a0 100%) fixed !important; font-family: var(--font-body); }
    body.zoo-admin h1, body.zoo-admin h2, body.zoo-admin h3 { font-family: var(--font-display); color: var(--jungle-900); }
    body.zoo-admin main h1, body.zoo-admin .max-w-7xl > .mb-8 h1, body.zoo-admin .admin-title { color: var(--jungle-900) !important; -webkit-text-fill-color: currentColor; opacity: 1 !important; line-height: 1.25 !important; text-shadow: 0 2px 0 rgba(255,255,255,.7); }
    body.zoo-admin .zoo-nav .nav-link { color: #dcfce7 !important; }
    body.zoo-admin .zoo-nav .nav-link.active { color: var(--jungle-900) !important; }

    /* Kartu putih -> kartu kaca bertema */
    body.zoo-admin .bg-white.rounded-xl,
    body.zoo-admin .bg-white.rounded-2xl,
    body.zoo-admin .bg-white.rounded-lg.shadow-lg,
    body.zoo-admin .glass-effect,
    body.zoo-admin .glass-effect-enhanced {
        background: var(--card) !important;
        border: 1px solid var(--card-border);
        border-radius: 22px !important;
        box-shadow: var(--shadow-3d) !important;
        backdrop-filter: blur(12px);
    }

    /* Aksen indigo/ungu -> hijau hutan & oranye senja */
    body.zoo-admin .text-indigo-600, body.zoo-admin .text-indigo-500 { color: var(--jungle-700) !important; }
    body.zoo-admin .hover\:text-indigo-600:hover, body.zoo-admin .hover\:text-indigo-900:hover { color: var(--leaf-500) !important; }
    body.zoo-admin .bg-indigo-500, body.zoo-admin .bg-indigo-600 { background: linear-gradient(180deg, #34d058, #16a34a) !important; }
    body.zoo-admin .hover\:bg-indigo-600:hover, body.zoo-admin .hover\:bg-indigo-700:hover { filter: brightness(1.08); }
    body.zoo-admin .bg-indigo-100 { background-color: #dcfce7 !important; }
    body.zoo-admin .border-indigo-600 { border-color: var(--leaf-500) !important; }
    body.zoo-admin .focus\:ring-indigo-500:focus { --tw-ring-color: rgba(34,197,94,.45) !important; }
    body.zoo-admin .text-purple-500, body.zoo-admin .text-purple-600 { color: #c2410c !important; }
    body.zoo-admin .bg-purple-100 { background-color: #ffedd5 !important; }
    body.zoo-admin .bg-purple-500 { background-color: var(--sunset-500) !important; }
    body.zoo-admin .gradient-text { background: linear-gradient(90deg, #166534, #f97316) !important; -webkit-background-clip: text !important; background-clip: text !important; }

    /* Tombol berwarna jadi tombol 3D */
    body.zoo-admin button.rounded-lg[class*="bg-"]:not(.bg-gray-300):not(.bg-gray-200),
    body.zoo-admin a.rounded-lg[class*="bg-"][class*="text-white"] {
        box-shadow: 0 4px 0 rgba(0,0,0,.18), 0 10px 16px -8px rgba(5,46,22,.45);
        transition: transform .12s, box-shadow .12s, filter .2s;
    }
    body.zoo-admin button.rounded-lg[class*="bg-"]:not(:disabled):hover { transform: translateY(-2px); }
    body.zoo-admin button.rounded-lg[class*="bg-"]:not(:disabled):active { transform: translateY(3px); box-shadow: 0 1px 0 rgba(0,0,0,.18); }

    /* Tabel */
    body.zoo-admin thead.bg-gray-50, body.zoo-admin thead .bg-gray-50, body.zoo-admin thead tr { background: linear-gradient(90deg, #14532d, #166534) !important; }
    body.zoo-admin thead th { color: #ecfdf5 !important; }
    body.zoo-admin tbody tr { transition: background .2s, transform .2s; }
    body.zoo-admin tbody tr:hover { background: #f0fdf4 !important; }

    /* Ikon statistik melayang */
    body.zoo-admin .zoo-stat-icon { animation: float-3d 5s ease-in-out infinite; }

    /* Modal */
    body.zoo-admin .fixed.inset-0 .bg-white { border-radius: 22px !important; }
</style>
<script src="{{ asset('assets/js/zoo-3d.js') }}?v=2" defer></script>
<script>
    // Beri efek tilt 3D pada kartu statistik & kartu aksi, lalu aktifkan animasi masuk
    document.addEventListener('DOMContentLoaded', function () {
        var cards = document.querySelectorAll('main .grid > .bg-white.rounded-xl, body > div .grid > .bg-white.rounded-xl, .grid > .glass-effect');
        cards.forEach(function (card) {
            if (!card.hasAttribute('data-tilt')) card.setAttribute('data-tilt', '8');
            var icon = card.querySelector('.rounded-full i, .rounded-full');
            if (icon) icon.classList.add('zoo-stat-icon');
        });
        if (window.ZooFX) window.ZooFX.initTilt();
    });
</script>
