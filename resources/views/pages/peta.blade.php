@extends('layouts.zoo')

@section('title', 'Peta Kebun Binatang')

@push('head')
<style>
    .map-stage { perspective: 1400px; perspective-origin: 50% 30%; position: relative; width: 100%; padding-top: 72%; overflow: hidden; border-radius: 20px; }
    .map-board {
        --rx: 55deg; --rz: -32deg;
        position: absolute; left: 8%; top: 4%; width: 84%; height: 84%;
        transform-style: preserve-3d;
        transform: rotateX(var(--rx)) rotateZ(var(--rz));
        transition: transform .5s cubic-bezier(.2,.8,.2,1);
        border-radius: 28px;
        background:
            radial-gradient(circle at 20% 30%, rgba(255,255,255,.18) 0 2px, transparent 3px) 0 0 / 26px 26px,
            linear-gradient(135deg, #7fd07f, #4fae5c);
        box-shadow: 0 0 0 6px #3f8f45;
    }
    .map-board.dragging { transition: none; }
    .map-board::before { /* ketebalan tanah */
        content: ''; position: absolute; left: 0; top: 100%; width: 100%; height: 34px;
        transform-origin: top; transform: rotateX(-90deg);
        background: repeating-linear-gradient(0deg, #8b5a2b 0 10px, #7a4b22 10px 20px); border-radius: 0 0 14px 14px;
    }
    .map-board::after {
        content: ''; position: absolute; left: 100%; top: 0; width: 34px; height: 100%;
        transform-origin: left; transform: rotateY(90deg);
        background: repeating-linear-gradient(90deg, #6b4423 0 10px, #5e3b1e 10px 20px); border-radius: 0 14px 14px 0;
    }
    .zone {
        --h: 14px;
        position: absolute; border-radius: 16px; transform-style: preserve-3d; cursor: pointer;
        transform: translateZ(var(--h));
        transition: transform .4s cubic-bezier(.2,.8,.2,1), filter .3s;
        display: flex; align-items: flex-end; justify-content: flex-start; padding: 6px 10px;
        font-family: var(--font-display); font-weight: 700; font-size: clamp(9px, 1.4vw, 15px); color: rgba(0,0,0,.6);
    }
    .zone::before { content: ''; position: absolute; left: 6px; right: 6px; top: 100%; height: var(--h); transform-origin: top; transform: rotateX(-90deg); background: var(--side); border-radius: 0 0 8px 8px; }
    .zone::after { content: ''; position: absolute; top: 6px; bottom: 6px; left: 100%; width: var(--h); transform-origin: left; transform: rotateY(90deg); background: var(--side); filter: brightness(.85); border-radius: 0 8px 8px 0; }
    .zone:hover, .zone.hl { --h: 40px; filter: brightness(1.08) saturate(1.1); }
    .zone .pin {
        position: absolute; left: 50%; top: 40%; transform-style: preserve-3d;
        transform: translate(-50%, -50%) translateZ(26px) rotateZ(calc(var(--rz) * -1)) rotateX(calc(var(--rx) * -1));
        transform-origin: 50% 100%; pointer-events: none;
    }
    .zone .pin span { display: block; font-size: clamp(20px, 3.4vw, 38px); animation: float-3d 3s ease-in-out infinite; filter: drop-shadow(0 10px 6px rgba(0,0,0,.25)); }
    .water { background: linear-gradient(135deg, #7dd3fc, #0ea5e9); --side: #0369a1; }
    .ripple { position: absolute; inset: 20%; border-radius: 50%; border: 3px solid rgba(255,255,255,.6); animation: ripple 3s ease-out infinite; }
    @keyframes ripple { from { transform: scale(.4); opacity: 1; } to { transform: scale(1.4); opacity: 0; } }
    .map-path { position: absolute; inset: 0; transform: translateZ(1px); pointer-events: none; }
    .legend-item { transition: transform .25s, background .25s; }
    .legend-item:hover, .legend-item.hl { transform: translateX(6px); background: #f0fdf4; }
</style>
@endpush

@section('content')
@php
    $zones = [
        ['savana', 'Savana Afrika', '🦁', 'Singa, zebra, dan jerapah hidup berdampingan di padang rumput luas.', 'left:4%;top:52%;width:36%;height:42%;', 'background:linear-gradient(135deg,#fde68a,#f59e0b);--side:#b45309;', '🦓'],
        ['hutan', 'Hutan Tropis', '🐯', 'Rumah harimau Sumatera dan orangutan di antara pepohonan lebat.', 'left:58%;top:6%;width:38%;height:40%;', 'background:linear-gradient(135deg,#4ade80,#15803d);--side:#14532d;', '🦧'],
        ['danau', 'Danau Burung', '🦚', 'Merak, flamingo, dan burung air di sekitar danau.', 'left:38%;top:30%;width:22%;height:24%;', '', '🦩'],
        ['bambu', 'Hutan Bambu', '🐼', 'Panda menikmati bambu segar di area sejuk.', 'left:6%;top:8%;width:26%;height:30%;', 'background:linear-gradient(135deg,#bef264,#65a30d);--side:#3f6212;', '🎋'],
        ['gajah', 'Area Gajah', '🐘', 'Lihat gajah Asia mandi dan bermain lumpur.', 'left:62%;top:56%;width:34%;height:38%;', 'background:linear-gradient(135deg,#d6d3d1,#a8a29e);--side:#57534e;', '💦'],
        ['playground', 'Playground', '🛝', 'Taman bermain dan area piknik keluarga.', 'left:42%;top:66%;width:16%;height:26%;', 'background:linear-gradient(135deg,#fda4af,#f43f5e);--side:#9f1239;', '🎈'],
    ];
@endphp

<div class="max-w-6xl mx-auto px-4 pt-10">
    <div class="text-center reveal">
        <span class="wood-sign">🗺️ Peta Interaktif</span>
        <h1 class="section-title mt-4">Peta Kebun Binatang Safari</h1>
        <p class="section-sub">Geser kursor di atas peta untuk memutar tampilan 3D. Arahkan ke zona untuk mengangkatnya.</p>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 mt-8 items-start">
        <div class="zoo-card p-2 sm:p-4 lg:col-span-2 reveal" style="background: linear-gradient(180deg, rgba(186,230,253,.7), rgba(220,252,231,.8));">
            <div class="map-stage" id="mapStage">
                <div class="map-board" id="mapBoard">
                    <svg class="map-path" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                        <path d="M50 100 C50 80 30 70 22 50 S40 20 50 14 S80 30 78 50 S60 75 50 100" fill="none" stroke="#fef3c7" stroke-width="2.6" stroke-linecap="round"/>
                        <path d="M50 100 C50 80 30 70 22 50 S40 20 50 14 S80 30 78 50 S60 75 50 100" fill="none" stroke="#d6a65a" stroke-width=".8" stroke-dasharray="2 2"/>
                    </svg>
                    @foreach ($zones as [$key, $name, $icon, $desc, $pos, $style, $extra])
                        <div class="zone {{ $key === 'danau' ? 'water' : '' }}" data-zone="{{ $key }}" style="{{ $pos }}{{ $style }}" title="{{ $name }}">
                            @if ($key === 'danau')<span class="ripple"></span>@endif
                            <span>{{ $name }}</span>
                            <div class="pin"><span>{{ $icon }}</span></div>
                        </div>
                    @endforeach
                    <div class="zone" style="left:44%;top:93%;width:12%;height:7%;background:#fef3c7;--side:#d97706;--h:8px;font-size:10px;justify-content:center;align-items:center;">🚪 Masuk</div>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            @foreach ($zones as $i => [$key, $name, $icon, $desc, $pos, $style, $extra])
                <div class="zoo-card legend-item p-4 flex gap-3 cursor-pointer reveal" data-delay="{{ min($i + 1, 5) }}" data-zone="{{ $key }}">
                    <div class="text-3xl">{{ $icon }}</div>
                    <div>
                        <div class="font-display text-lg font-bold text-green-900">{{ $name }} {{ $extra }}</div>
                        <div class="text-sm font-semibold text-green-700">{{ $desc }}</div>
                    </div>
                </div>
            @endforeach
            <a href="{{ route('tiket.create') }}" class="btn-zoo w-full">🎟️ Pesan Tiket Sekarang</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var stage = document.getElementById('mapStage');
        var board = document.getElementById('mapBoard');
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (!reduce) {
            stage.addEventListener('pointermove', function (e) {
                var r = stage.getBoundingClientRect();
                var x = (e.clientX - r.left) / r.width - 0.5;
                var y = (e.clientY - r.top) / r.height - 0.5;
                board.style.setProperty('--rz', (-32 + x * 30).toFixed(1) + 'deg');
                board.style.setProperty('--rx', (55 + y * 14).toFixed(1) + 'deg');
            });
            stage.addEventListener('pointerleave', function () {
                board.style.setProperty('--rz', '-32deg');
                board.style.setProperty('--rx', '55deg');
            });
        }

        // Sorot zona dari legenda dan sebaliknya
        function highlight(key, on) {
            document.querySelectorAll('[data-zone="' + key + '"]').forEach(function (el) {
                el.classList.toggle('hl', on || el.classList.contains('sticky'));
            });
        }
        document.querySelectorAll('[data-zone]').forEach(function (el) {
            var key = el.getAttribute('data-zone');
            el.addEventListener('mouseenter', function () { highlight(key, true); });
            el.addEventListener('mouseleave', function () { highlight(key, false); });
            el.addEventListener('click', function () {
                var on = !el.classList.contains('sticky');
                document.querySelectorAll('.sticky').forEach(function (s) { s.classList.remove('sticky', 'hl'); });
                document.querySelectorAll('[data-zone="' + key + '"]').forEach(function (z) { z.classList.toggle('sticky', on); z.classList.toggle('hl', on); });
            });
        });
    })();
</script>
@endpush
