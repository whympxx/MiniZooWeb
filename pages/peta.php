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
    <title>Peta Kebun Binatang Safari</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="../assets/css/peta-tailwind.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-green-100 via-blue-100 to-yellow-100 min-h-screen flex flex-col">
    <header class="bg-white/80 backdrop-blur shadow flex items-center justify-between px-6 py-4 sticky top-0 z-10">
        <div class="flex items-center gap-3">
            <a href="dashboard.php" class="inline-block w-10 h-10 bg-green-200 rounded-full flex items-center justify-center text-2xl hover:scale-110 transition-transform">🦁</a>
            <span class="font-extrabold text-2xl text-green-900">ZooDash</span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-green-800 font-semibold flex items-center gap-2"><span>👋</span> <?= htmlspecialchars($user['username']) ?></span>
            <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg transition-colors text-sm">Logout</a>
        </div>
    </header>
    <main class="flex-1 flex flex-col items-center justify-center p-6 md:p-10">
        <section class="w-full max-w-4xl bg-white rounded-2xl shadow-lg p-6 md:p-10 mt-8 animate-fade-in">
            <h1 class="text-3xl md:text-4xl font-bold text-green-800 mb-4 flex items-center gap-2">
                <span class="text-4xl">🗺️</span> Peta Kebun Binatang Safari
            </h1>
            <p class="mb-6 text-green-700 text-lg">Jelajahi area Kebun Binatang Safari dengan peta interaktif dan animasi berikut:</p>
            <div class="w-full h-96 rounded-lg overflow-hidden border-4 border-green-200 flex items-center justify-center bg-gradient-to-br from-green-100 to-blue-100 relative zoo-map-embed">
                <!-- SVG Animasi Peta Kebun Binatang Safari Profesional -->
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
                <div class="absolute top-4 right-4 animate-bounce">
                    <a href="dashboard.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg shadow transition-colors flex items-center gap-2"><span>🏠</span> Dashboard</a>
                </div>
            </div>
        </section>
    </main>
    <footer class="bg-white/80 backdrop-blur shadow-inner text-center py-4 text-xs text-gray-500 mt-8">
        &copy; <?= date('Y') ?> Kebun Binatang Safari. All rights reserved.
    </footer>
</body>
</html> 