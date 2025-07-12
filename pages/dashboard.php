<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: Login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kebun Binatang</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="../assets/css/dashboard-zoo.css" rel="stylesheet">
    <style>
      body { font-family: 'Inter', 'Comic Sans MS', 'Comic Sans', cursive, sans-serif; }
      .sidebar-open { transform: translateX(0) !important; }
      .sidebar-closed { transform: translateX(-100%); }
      .sidebar-transition { transition: transform 0.3s cubic-bezier(.4,0,.2,1); }
      .active-menu { background: linear-gradient(90deg, #bbf7d0 0%, #fef9c3 100%); color: #166534 !important; border-radius: 0.75rem; }
      .dropdown-menu { display: none; position: absolute; right: 0; top: 100%; background: white; box-shadow: 0 4px 24px 0 rgba(34,197,94,0.08); border-radius: 0.5rem; min-width: 140px; z-index: 50; }
      .dropdown-open .dropdown-menu { display: block; }
    </style>
</head>
<body class="zoo-bg min-h-screen flex flex-col">
    <div class="flex flex-1 min-h-0 relative">
        <!-- Sidebar -->
        <aside id="sidebar" class="fixed top-0 left-0 h-full w-72 bg-gradient-to-b from-white to-green-50 shadow-xl flex flex-col z-30 sidebar-transition sidebar-open border-r border-green-100">
            <!-- Header Sidebar -->
            <div class="p-6 border-b border-green-100">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <span class="inline-block w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-xl flex items-center justify-center text-2xl shadow-lg">🦁</span>
                            <div class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
                        </div>
                        <div>
                            <span class="font-extrabold text-2xl text-green-900 tracking-wide">ZooDash</span>
                            <p class="text-xs text-green-600 font-medium">Safari Dashboard</p>
                        </div>
                    </div>
                    <button class="flex items-center justify-center w-8 h-8 rounded-lg hover:bg-green-100 transition" onclick="toggleSidebar()" aria-label="Close sidebar">
                        <svg class="w-5 h-5 text-green-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 p-4">
                <div class="mb-6">
                    <h3 class="text-xs font-semibold text-green-600 uppercase tracking-wider mb-3 px-3">Menu Utama</h3>
                    <ul class="space-y-1">
                        <li>
                            <a href="home.php" class="flex items-center gap-3 px-4 py-3 font-medium rounded-xl hover:bg-green-100 hover:text-green-700 transition-all duration-200 text-green-800 group" id="menu-home">
                                <span class="text-xl group-hover:scale-110 transition-transform">🏠</span>
                                <span>Home</span>
                                <div class="ml-auto w-2 h-2 bg-green-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            </a>
                        </li>
                        <li>
                            <a href="peta.php" class="flex items-center gap-3 px-4 py-3 font-medium rounded-xl hover:bg-green-100 hover:text-green-700 transition-all duration-200 text-green-800 group" id="menu-peta">
                                <span class="text-xl group-hover:scale-110 transition-transform">🗺️</span>
                                <span>Peta</span>
                                <div class="ml-auto w-2 h-2 bg-green-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            </a>
                        </li>
                        <li>
                            <a href="tiket.php" class="flex items-center gap-3 px-4 py-3 font-medium rounded-xl hover:bg-green-100 hover:text-green-700 transition-all duration-200 text-green-800 group" id="menu-tiket">
                                <span class="text-xl group-hover:scale-110 transition-transform">🎟️</span>
                                <span>Pesan Tiket</span>
                                <div class="ml-auto w-2 h-2 bg-green-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            </a>
                        </li>
                        <li>
                            <a href="tiket_riwayat.php" class="flex items-center gap-3 px-4 py-3 font-medium rounded-xl hover:bg-green-100 hover:text-green-700 transition-all duration-200 text-green-800 group" id="menu-riwayat">
                                <span class="text-xl group-hover:scale-110 transition-transform">📋</span>
                                <span>Riwayat Tiket</span>
                                <div class="ml-auto w-2 h-2 bg-green-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            </a>
                        </li>
                        <li>
                            <a href="statistik.php" class="flex items-center gap-3 px-4 py-3 font-medium rounded-xl hover:bg-green-100 hover:text-green-700 transition-all duration-200 text-green-800 group" id="menu-statistik">
                                <span class="text-xl group-hover:scale-110 transition-transform">📊</span>
                                <span>Statistik</span>
                                <div class="ml-auto w-2 h-2 bg-green-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            </a>
                        </li>
                        
                    </ul>
                </div>
                
                <div class="mb-6">
                    <h3 class="text-xs font-semibold text-green-600 uppercase tracking-wider mb-3 px-3">Akun</h3>
                    <ul class="space-y-1">
                        <li>
                            <a href="profil.php" class="flex items-center gap-3 px-4 py-3 font-medium rounded-xl hover:bg-green-100 hover:text-green-700 transition-all duration-200 text-green-800 group" id="menu-profil">
                                <span class="text-xl group-hover:scale-110 transition-transform">👤</span>
                                <span>Profil</span>
                                <div class="ml-auto w-2 h-2 bg-green-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            </a>
                        </li>
                        <li>
                            <a href="logout.php" class="flex items-center gap-3 px-4 py-3 font-medium rounded-xl hover:bg-red-100 hover:text-red-700 transition-all duration-200 text-red-600 group" id="menu-logout">
                                <span class="text-xl group-hover:scale-110 transition-transform">🚪</span>
                                <span>Logout</span>
                                <div class="ml-auto w-2 h-2 bg-red-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Footer Sidebar -->
            <div class="p-4 border-t border-green-100">
                <div class="bg-green-50 rounded-xl p-3 mb-3">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-green-600">👋</span>
                        <span class="text-sm font-medium text-green-800">Hi, <?= htmlspecialchars($user['username']) ?>!</span>
                    </div>
                    <p class="text-xs text-green-600">Selamat datang di ZooDash</p>
                </div>
                <div class="text-xs text-gray-400 text-center">&copy; <?= date('Y') ?> Kebun Binatang Safari</div>
            </div>
        </aside>
        <!-- Overlay for sidebar -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-30 z-20 hidden" onclick="toggleSidebar()"></div>
        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-h-0 ml-72">
            <!-- Header -->
            <header class="sticky top-0 z-10 bg-white/80 backdrop-blur shadow flex items-center justify-between px-4 md:px-6 py-4 relative">
                <div class="flex items-center gap-3">
                    <button class="flex items-center justify-center w-10 h-10 rounded-lg hover:bg-green-100 transition" onclick="toggleSidebar()" aria-label="Open sidebar">
                        <svg class="w-7 h-7 text-green-800" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <span class="font-bold text-xl text-green-900 tracking-wide">Dashboard Kebun Binatang</span>
                </div>
                <div class="flex items-center gap-4 relative">
                    <div class="relative dropdown" id="userDropdown">
                        <button onclick="toggleDropdown()" class="flex items-center gap-2 px-2 py-1 rounded-lg hover:bg-green-100 transition">
                            <span class="inline-block w-9 h-9 rounded-full bg-green-200 flex items-center justify-center text-2xl border-2 border-green-300 font-bold">
                                <?= strtoupper(substr($user['username'],0,1)) ?>
                            </span>
                            <span class="text-green-800 font-semibold hidden sm:block">Hi, <?= htmlspecialchars($user['username']) ?></span>
                            <svg class="w-4 h-4 text-green-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div class="dropdown-menu absolute right-0 mt-2 py-2 w-40 bg-white rounded-lg shadow-xl border border-gray-100">
                            <a href="profil.php" class="block px-4 py-2 text-green-800 hover:bg-green-50">Profil</a>
                            <a href="logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50">Logout</a>
                        </div>
                    </div>
                </div>
            </header>
            <!-- Content -->
            <main class="flex-1 overflow-y-auto p-4 md:p-10 bg-transparent">
                <!-- Info Card -->
                <section id="profil" class="mb-8">
                    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8 flex flex-col md:flex-row gap-8 items-center md:items-start zoo-card zoo-card-hover transition-all">
                        <div class="flex-shrink-0 flex flex-col items-center gap-2">
                            <span class="inline-block w-20 h-20 rounded-full bg-green-100 flex items-center justify-center text-5xl border-4 border-green-200">🧑</span>
                            <span class="text-green-900 font-bold text-lg mt-2"><?= htmlspecialchars($user['username']) ?></span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $user['role'] === 'admin' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?> zoo-role">
                                <?= ucfirst($user['role']) ?>
                            </span>
                        </div>
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500">Email</label>
                                <div class="text-base text-gray-900 mt-1"><?= htmlspecialchars($user['email']) ?></div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500">Phone</label>
                                <div class="text-base text-gray-900 mt-1"><?= htmlspecialchars($user['phone']) ?></div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500">Member Sejak</label>
                                <div class="text-base text-gray-900 mt-1"><?= date('M Y', strtotime($user['created_at'])) ?></div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500">Update Terakhir</label>
                                <div class="text-base text-gray-900 mt-1"><?= date('M d', strtotime($user['updated_at'])) ?></div>
                            </div>
                        </div>
                    </div>
                </section>
                <!-- Statistik Card -->
                <section id="stat" class="mb-8">
                    <?php
                    // Ambil statistik tiket user
                    $ticket_query = "SELECT 
                        COUNT(*) as total_orders,
                        SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_orders,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                        SUM(jumlah) as total_tickets
                    FROM orders WHERE user_id = ?";
                    $ticket_stmt = $conn->prepare($ticket_query);
                    $ticket_stmt->bind_param("i", $user_id);
                    $ticket_stmt->execute();
                    $ticket_stats = $ticket_stmt->get_result()->fetch_assoc();
                    ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                        <div class="bg-green-50 rounded-xl p-6 flex flex-col items-center shadow zoo-stat zoo-stat-hover transition-all">
                            <span class="text-3xl mb-2">📅</span>
                            <div class="text-sm text-green-800 font-semibold mb-1">Member Sejak</div>
                            <div class="text-2xl font-bold text-green-900 zoo-info-text"><?= date('M Y', strtotime($user['created_at'])) ?></div>
                        </div>
                        <div class="bg-blue-50 rounded-xl p-6 flex flex-col items-center shadow zoo-stat zoo-stat-hover transition-all">
                            <span class="text-3xl mb-2">🎟️</span>
                            <div class="text-sm text-blue-800 font-semibold mb-1">Total Tiket</div>
                            <div class="text-2xl font-bold text-blue-900 zoo-info-text"><?= $ticket_stats['total_tickets'] ?? 0 ?></div>
                        </div>
                        <div class="bg-green-100 rounded-xl p-6 flex flex-col items-center shadow zoo-stat zoo-stat-hover transition-all">
                            <span class="text-3xl mb-2">✅</span>
                            <div class="text-sm text-green-800 font-semibold mb-1">Tiket Lunas</div>
                            <div class="text-2xl font-bold text-green-900 zoo-info-text"><?= $ticket_stats['paid_orders'] ?? 0 ?></div>
                        </div>
                        <div class="bg-yellow-50 rounded-xl p-6 flex flex-col items-center shadow zoo-stat zoo-stat-hover transition-all">
                            <span class="text-3xl mb-2">⏳</span>
                            <div class="text-sm text-yellow-800 font-semibold mb-1">Menunggu Bayar</div>
                            <div class="text-2xl font-bold text-yellow-900 zoo-info-text"><?= $ticket_stats['pending_orders'] ?? 0 ?></div>
                        </div>
                    </div>
                </section>
                <!-- Zoo Map Section -->
                <section id="peta" class="mb-8">
                    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
                        <h2 class="text-xl font-bold text-green-800 mb-4 flex items-center gap-2">
                            <span class="text-2xl">🗺️</span> Peta Kebun Binatang Safari
                        </h2>
                        <p class="mb-4 text-green-700">Jelajahi area Kebun Binatang Safari langsung dari dashboard! Temukan lokasi Taman Safari Indonesia di peta berikut:</p>
                        <div class="w-full h-80 rounded-lg overflow-hidden border-4 border-green-200 zoo-map-embed flex items-center justify-center bg-gradient-to-br from-green-100 to-blue-100">
                            <!-- SVG Animasi Peta Kebun Binatang Safari Profesional (clean version) -->
                            <svg viewBox="0 0 800 350" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                  <linearGradient id="bgGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#fef9c3"/>
                                    <stop offset="100%" stop-color="#bbf7d0"/>
                                  </linearGradient>
                                  <radialGradient id="lakeGrad" cx="50%" cy="50%" r="50%">
                                    <stop offset="0%" stop-color="#bae6fd"/>
                                    <stop offset="100%" stop-color="#38bdf8"/>
                                  </radialGradient>
                                  <linearGradient id="savannahGrad" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#fef08a"/>
                                    <stop offset="100%" stop-color="#fde68a"/>
                                  </linearGradient>
                                  <linearGradient id="forestGrad" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#bbf7d0"/>
                                    <stop offset="100%" stop-color="#22c55e"/>
                                  </linearGradient>
                                </defs>
                                <rect width="800" height="350" rx="36" fill="url(#bgGrad)" />
                                <ellipse cx="200" cy="250" rx="120" ry="60" fill="url(#savannahGrad)" opacity="0.8" />
                                <text x="200" y="245" text-anchor="middle" font-size="18" fill="#b45309" font-family="Comic Sans MS, Comic Sans, cursive">Savannah</text>
                                <ellipse cx="600" cy="120" rx="110" ry="55" fill="url(#forestGrad)" opacity="0.85" />
                                <text x="600" y="115" text-anchor="middle" font-size="18" fill="#166534" font-family="Comic Sans MS, Comic Sans, cursive">Hutan</text>
                                <rect x="340" y="270" width="120" height="40" rx="18" fill="#fca5a5" opacity="0.7" />
                                <text x="400" y="295" text-anchor="middle" font-size="18" fill="#b91c1c" font-family="Comic Sans MS, Comic Sans, cursive">Playground</text>
                                <ellipse cx="400" cy="120" rx="60" ry="28" fill="url(#lakeGrad)" opacity="0.7" />
                                <path d="M340 120 Q360 110 380 120 T420 120 T460 120" stroke="#7dd3fc" stroke-width="4" fill="none">
                                  <animate attributeName="d" values="M340 120 Q360 110 380 120 T420 120 T460 120;M340 120 Q360 130 380 120 T420 120 T460 120;M340 120 Q360 110 380 120 T420 120 T460 120" dur="2s" repeatCount="indefinite" />
                                </path>
                                <g>
                                  <rect x="700" y="60" width="18" height="60" rx="8" fill="#38bdf8" opacity="0.8">
                                    <animate attributeName="y" values="60;70;60" dur="1.5s" repeatCount="indefinite" />
                                  </rect>
                                  <ellipse cx="709" cy="120" rx="16" ry="6" fill="#bae6fd" opacity="0.7">
                                    <animate attributeName="ry" values="6;10;6" dur="1.5s" repeatCount="indefinite" />
                                  </ellipse>
                                </g>
                                <path d="M80 320 Q200 200 400 320 T720 320" stroke="#a3a3a3" stroke-width="16" fill="none" stroke-linecap="round" opacity="0.7" filter="url(#shadow)" />
                                <g class="zoo-tree">
                                  <rect x="170" y="170" width="18" height="50" fill="#a3a07e" />
                                  <ellipse cx="179" cy="170" rx="32" ry="28" fill="#22c55e">
                                    <animate attributeName="cy" values="170;165;170" dur="2.2s" repeatCount="indefinite" />
                                  </ellipse>
                                </g>
                                <g class="zoo-tree">
                                  <rect x="650" y="90" width="18" height="50" fill="#a3a07e" />
                                  <ellipse cx="659" cy="90" rx="32" ry="28" fill="#16a34a">
                                    <animate attributeName="cy" values="90;85;90" dur="2.5s" repeatCount="indefinite" />
                                  </ellipse>
                                </g>
                                <g>
                                  <path id="birdPath" d="M100 60 Q400 10 700 60" fill="none" />
                                  <image href="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/1f426.png" width="32" height="32">
                                    <animateMotion dur="7s" repeatCount="indefinite">
                                      <mpath href="#birdPath" />
                                    </animateMotion>
                                  </image>
                                </g>
                                <image href="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/1f981.png" x="160" y="250" width="40" height="40">
                                  <animate attributeName="y" values="250;245;250" dur="1.2s" repeatCount="indefinite" />
                                </image>
                                <image href="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/1f993.png" x="220" y="260" width="40" height="40">
                                  <animate attributeName="y" values="260;255;260" dur="1.3s" repeatCount="indefinite" />
                                </image>
                                <image href="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/1f418.png" x="600" y="140" width="40" height="40">
                                  <animate attributeName="y" values="140;135;140" dur="1.4s" repeatCount="indefinite" />
                                </image>
                                <image href="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/1f43c.png" x="400" y="285" width="40" height="40">
                                  <animate attributeName="y" values="285;280;285" dur="1.2s" repeatCount="indefinite" />
                                </image>
                                <image href="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/1f42f.png" x="680" y="120" width="40" height="40">
                                  <animate attributeName="y" values="120;115;120" dur="1.3s" repeatCount="indefinite" />
                                </image>
                                <image href="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/1f992.png" x="250" y="220" width="40" height="40">
                                  <animate attributeName="y" values="220;215;220" dur="1.3s" repeatCount="indefinite" />
                                </image>
                                <text x="400" y="50" text-anchor="middle" font-size="32" fill="#166534" font-family="Comic Sans MS, Comic Sans, cursive" filter="url(#shadow)">Safari Zoo Map</text>
                                <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
                                  <feDropShadow dx="0" dy="4" stdDeviation="4" flood-color="#a3a3a3" flood-opacity="0.3"/>
                                </filter>
                            </svg>
                        </div>
                        <div class="flex justify-end mt-2">
                          <button class="px-4 py-2 bg-green-100 text-green-800 rounded-lg font-semibold hover:bg-green-200 transition">Lihat Lebih Besar</button>
                        </div>
                    </div>
                </section>
            </main>
            <!-- Footer -->
            <footer class="bg-white/80 backdrop-blur shadow-inner text-center py-4 text-xs text-gray-500 mt-auto sticky bottom-0">
                &copy; <?= date('Y') ?> Kebun Binatang Safari. All rights reserved.
            </footer>
        </div>
    </div>
    <script>
      // Sidebar toggle for all screen sizes
      function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const mainContent = document.querySelector('.flex-1.flex.flex-col');
        
        if (sidebar.classList.contains('sidebar-closed')) {
          sidebar.classList.remove('sidebar-closed');
          sidebar.classList.add('sidebar-open');
          overlay.classList.remove('hidden');
          mainContent.style.marginLeft = '18rem';
        } else {
          sidebar.classList.add('sidebar-closed');
          sidebar.classList.remove('sidebar-open');
          overlay.classList.add('hidden');
          mainContent.style.marginLeft = '0';
        }
      }
      
      // Initialize sidebar as open by default
      document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const mainContent = document.querySelector('.flex-1.flex.flex-col');
        sidebar.classList.remove('sidebar-closed');
        sidebar.classList.add('sidebar-open');
        overlay.classList.add('hidden');
        mainContent.style.marginLeft = '18rem';
      });
      // Dropdown user
      function toggleDropdown() {
        const dropdown = document.getElementById('userDropdown');
        dropdown.classList.toggle('dropdown-open');
      }
      // Highlight active menu
      const path = window.location.pathname.split('/').pop();
      if (path === 'home.php') document.getElementById('menu-home').classList.add('active-menu');
      if (path === 'peta.php') document.getElementById('menu-peta').classList.add('active-menu');
      if (path === 'tiket.php') document.getElementById('menu-tiket').classList.add('active-menu');
      if (path === 'tiket_riwayat.php') document.getElementById('menu-riwayat').classList.add('active-menu');
      if (path === 'statistik.php') document.getElementById('menu-statistik').classList.add('active-menu');
      if (path === 'profil.php') document.getElementById('menu-profil').classList.add('active-menu');
      if (path === 'logout.php') document.getElementById('menu-logout').classList.add('active-menu');
      // Close dropdown on outside click
      document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('userDropdown');
        if (!dropdown.contains(e.target)) dropdown.classList.remove('dropdown-open');
      });
    </script>
</body>
</html>
