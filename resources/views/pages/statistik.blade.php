@extends('layouts.zoo')

@section('title', 'Statistik')

@push('head')
<style>
    .chart3d { display: flex; align-items: flex-end; justify-content: space-around; gap: 6px; height: 300px; padding: 30px 18px 0 0; }
    .bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; }
    .bar3d { --d: 14px; position: relative; width: 58%; max-width: 46px; height: 0; transition: height 1.4s cubic-bezier(.2,.8,.2,1); background: linear-gradient(180deg, var(--c1), var(--c2)); border-radius: 4px 4px 0 0; }
    .reveal.in .bar3d { height: var(--h); }
    .bar3d::before { /* sisi atas */
        content: ''; position: absolute; left: 0; right: calc(var(--d) * -1); top: calc(var(--d) * -1); height: var(--d);
        background: var(--c1); filter: brightness(1.2); clip-path: polygon(var(--d) 0, 100% 0, calc(100% - var(--d)) 100%, 0 100%);
    }
    .bar3d::after { /* sisi kanan */
        content: ''; position: absolute; right: calc(var(--d) * -1); top: calc(var(--d) * -1); bottom: 0; width: var(--d);
        background: var(--c2); filter: brightness(.75); clip-path: polygon(0 var(--d), 100% 0, 100% calc(100% - var(--d)), 0 100%);
    }
    .bar3d .val { position: absolute; top: -44px; left: 50%; transform: translateX(-50%); font-weight: 800; font-size: 12px; color: #14532d; white-space: nowrap; opacity: 0; transition: opacity .4s 1.2s; }
    .reveal.in .bar3d .val { opacity: 1; }
    .bar-col:hover .bar3d { filter: brightness(1.1) saturate(1.2); }
    .bar-label { margin-top: 10px; font-weight: 800; font-size: 12px; color: #4b6354; }

    .ring { --p: 0; width: 120px; height: 120px; border-radius: 50%; display: grid; place-items: center;
        background: conic-gradient(var(--c) calc(var(--p) * 1%), #e5f3e2 0); box-shadow: 0 14px 24px -12px rgba(5,46,22,.4); }
    .ring > div { width: 86px; height: 86px; border-radius: 50%; background: #fffef7; display: grid; place-items: center; font-size: 2rem; }
</style>
@endpush

@section('content')
@php
    $weekly = [['Sen', 1200], ['Sel', 1500], ['Rab', 1100], ['Kam', 1800], ['Jum', 2000], ['Sab', 2500], ['Min', 2240]];
    $max = max(array_column($weekly, 1));
    $colors = [['#86efac', '#16a34a'], ['#fde68a', '#f59e0b'], ['#7dd3fc', '#0284c7'], ['#fdba74', '#ea580c'], ['#c4b5fd', '#7c3aed'], ['#f9a8d4', '#db2777'], ['#5eead4', '#0d9488']];
    $collection = [['🦁', 'Mamalia', 42, '#f59e0b'], ['🦜', 'Burung', 28, '#16a34a'], ['🐊', 'Reptil', 11, '#0284c7'], ['🐠', 'Akuatik', 6, '#db2777']];
    $total = array_sum(array_column($collection, 2));
@endphp

<div class="max-w-6xl mx-auto px-4 pt-10">
    <div class="text-center reveal">
        <span class="wood-sign">📊 Data Safari</span>
        <h1 class="section-title mt-4">Statistik Pengunjung & Koleksi</h1>
    </div>

    <section class="grid md:grid-cols-3 gap-6 mt-8">
        @foreach ([['🦓', 'Pengunjung Bulan Ini', '12340', ''], ['🦒', 'Koleksi Hewan', (string) $total, ' ekor'], ['⭐', 'Rating Pengunjung', '4.8', '/5']] as $i => [$icon, $label, $value, $suffix])
            <div class="zoo-card stat-3d reveal" data-delay="{{ $i + 1 }}" data-tilt="12">
                <div class="pop"><span class="stat-icon float-animal" style="font-size: 3rem; animation-delay: -{{ $i }}s">{{ $icon }}</span></div>
                <div class="stat-value pop-sm" style="font-size: 2.4rem" data-count="{{ $value }}" data-suffix="{{ $suffix }}">{{ $value }}{{ $suffix }}</div>
                <div class="stat-label">{{ $label }}</div>
            </div>
        @endforeach
    </section>

    <section class="grid lg:grid-cols-3 gap-6 mt-8">
        <div class="zoo-card p-6 lg:col-span-2 reveal">
            <h2 class="font-display text-2xl font-bold text-green-900">📈 Pengunjung Mingguan</h2>
            <p class="section-sub text-sm">Jumlah pengunjung per hari minggu ini</p>
            <div class="chart3d mt-4">
                @foreach ($weekly as $i => [$day, $count])
                    <div class="bar-col">
                        <div class="bar3d" style="--h: {{ round($count / $max * 88) }}%; --c1: {{ $colors[$i][0] }}; --c2: {{ $colors[$i][1] }}; transition-delay: {{ $i * 0.1 }}s">
                            <span class="val">{{ number_format($count, 0, ',', '.') }}</span>
                        </div>
                        <div class="bar-label">{{ $day }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="zoo-card p-6 reveal" data-delay="2">
            <h2 class="font-display text-2xl font-bold text-green-900">🧬 Komposisi Koleksi</h2>
            <div class="grid grid-cols-2 gap-5 mt-5">
                @foreach ($collection as [$icon, $label, $n, $color])
                    <div class="flex flex-col items-center text-center">
                        <div class="ring" style="--p: {{ round($n / $total * 100) }}; --c: {{ $color }}"><div>{{ $icon }}</div></div>
                        <div class="font-bold text-green-900 mt-2">{{ $label }}</div>
                        <div class="text-xs font-bold text-green-700">{{ $n }} spesies · {{ round($n / $total * 100) }}%</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
@endsection
