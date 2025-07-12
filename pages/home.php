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
    <title>Home - Kebun Binatang Safari</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="../assets/css/home-tailwind.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-green-100 via-yellow-50 to-blue-100 min-h-screen flex flex-col">
    <header class="bg-white/90 shadow sticky top-0 z-10 flex items-center justify-between px-6 py-4">
        <div class="flex items-center gap-3">
            <span class="inline-block w-10 h-10 bg-green-200 rounded-full flex items-center justify-center text-2xl">🦁</span>
            <span class="font-extrabold text-2xl text-green-900">ZooDash</span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-green-800 font-semibold flex items-center gap-2"><span>👋</span> <?= htmlspecialchars($user['username']) ?></span>
            <a href="dashboard.php" class="flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-full shadow transition-all duration-200 text-sm font-semibold">
                <span class="text-lg">⬅️</span> Kembali ke Halaman Utama
            </a>
            <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg transition-colors text-sm">Logout</a>
        </div>
    </header>
    <main class="flex-1 flex flex-col items-center justify-center px-4 py-10">
        <section class="max-w-3xl w-full bg-white/90 rounded-3xl shadow-xl p-8 flex flex-col items-center gap-6">
            <h1 class="text-3xl md:text-4xl font-extrabold text-green-900 mb-2 text-center flex items-center gap-2">
                <span>Selamat Datang di</span> <span class="text-yellow-500">Kebun Binatang Safari</span>
            </h1>
            <p class="text-lg text-green-800 text-center mb-4">Temukan keajaiban dunia satwa, edukasi, dan petualangan seru bersama keluarga di Safari Zoo. Jelajahi berbagai zona, ikuti event menarik, dan dapatkan pengalaman tak terlupakan!</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full">
                <div class="bg-green-50 rounded-xl p-6 flex flex-col items-center shadow hover:scale-105 transition-transform zoo-home-card">
                    <span class="text-4xl mb-2">🦁</span>
                    <div class="text-lg font-bold text-green-900 mb-1">Zona Satwa</div>
                    <div class="text-sm text-green-700 text-center">Lihat koleksi satwa eksotis dari seluruh dunia, mulai dari mamalia, burung, hingga reptil langka.</div>
                </div>
                <div class="bg-blue-50 rounded-xl p-6 flex flex-col items-center shadow hover:scale-105 transition-transform zoo-home-card">
                    <span class="text-4xl mb-2">🎉</span>
                    <div class="text-lg font-bold text-blue-900 mb-1">Event & Edukasi</div>
                    <div class="text-sm text-blue-700 text-center">Ikuti pertunjukan, edukasi satwa, dan event seru yang diadakan setiap pekan di Safari Zoo.</div>
                </div>
                <div class="bg-yellow-50 rounded-xl p-6 flex flex-col items-center shadow hover:scale-105 transition-transform zoo-home-card">
                    <span class="text-4xl mb-2">🍃</span>
                    <div class="text-lg font-bold text-yellow-900 mb-1">Wisata Alam</div>
                    <div class="text-sm text-yellow-700 text-center">Nikmati keindahan alam, taman bermain, dan area piknik yang asri bersama keluarga.</div>
                </div>
            </div>
            <a href="#peta" class="mt-6 inline-block bg-green-500 hover:bg-green-600 text-white font-semibold px-6 py-3 rounded-full shadow transition-colors text-lg">Lihat Peta Kebun Binatang</a>
        </section>
        <section class="mt-12 w-full max-w-4xl">
            <div class="bg-gradient-to-r from-green-200 via-yellow-100 to-blue-200 rounded-2xl p-8 flex flex-col md:flex-row items-center gap-8 shadow-lg">
                <img src="../assets/images/register.jpg" alt="Safari Family" class="w-40 h-40 object-cover rounded-2xl shadow-lg border-4 border-green-200">
                <div>
                    <h2 class="text-2xl font-bold text-green-900 mb-2">Kenapa Memilih Safari Zoo?</h2>
                    <ul class="list-disc pl-5 text-green-800 space-y-1">
                        <li>Lebih dari 200 spesies satwa dari seluruh dunia</li>
                        <li>Fasilitas edukasi dan event interaktif</li>
                        <li>Area bermain dan piknik keluarga</li>
                        <li>Lingkungan asri dan ramah anak</li>
                        <li>Pengalaman wisata yang aman dan menyenangkan</li>
                    </ul>
                </div>
            </div>
        </section>
        <!-- Section Galeri Aktivitas Hewan -->
        <section class="mt-12 w-full max-w-5xl">
            <h2 class="text-2xl md:text-3xl font-extrabold text-green-900 mb-6 text-center animate-fade-in">Contoh Beberapa Hewan di Safari Zoo</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col transform transition duration-500 hover:scale-105 hover:shadow-green-300/60 border-2 border-green-100 hover:border-green-400 animate-fade-in-up">
                    <img src="../assets/images/Harimau.jpg" alt="Harimau Beristirahat" class="h-48 w-full object-cover transition-transform duration-500 hover:scale-110">
                    <div class="p-4 flex-1 flex flex-col">
                        <div class="font-bold text-lg text-green-900 mb-1">Harimau Sumatera</div>
                        <div class="text-sm text-green-700 mb-2 flex-1">Harimau sedang beristirahat di bawah rindangnya pepohonan.</div>
                        <span class="text-xs text-gray-400">Sumber: Safari Zoo</span>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col transform transition duration-500 hover:scale-105 hover:shadow-blue-200/60 border-2 border-blue-100 hover:border-blue-400 animate-fade-in-up delay-100">
                    <img src="../assets/images/Gajah.jpg" alt="Gajah Mandi" class="h-48 w-full object-cover transition-transform duration-500 hover:scale-110">
                    <div class="p-4 flex-1 flex flex-col">
                        <div class="font-bold text-lg text-green-900 mb-1">Gajah Asia</div>
                        <div class="text-sm text-green-700 mb-2 flex-1">Gajah menikmati mandi di kolam bersama kawanannya.</div>
                        <span class="text-xs text-gray-400">Sumber: Safari Zoo</span>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col transform transition duration-500 hover:scale-105 hover:shadow-yellow-200/60 border-2 border-yellow-100 hover:border-yellow-400 animate-fade-in-up delay-200">
                    <img src="../assets/images/Merak.jpg" alt="Burung Merak" class="h-48 w-full object-cover transition-transform duration-500 hover:scale-110">
                    <div class="p-4 flex-1 flex flex-col">
                        <div class="font-bold text-lg text-green-900 mb-1">Burung Merak</div>
                        <div class="text-sm text-green-700 mb-2 flex-1">Burung merak menampilkan bulu indahnya di pagi hari.</div>
                        <span class="text-xs text-gray-400">Sumber: Safari Zoo</span>
                    </div>
                </div>
                <!-- Card 4 -->
                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col transform transition duration-500 hover:scale-105 hover:shadow-orange-200/60 border-2 border-orange-100 hover:border-orange-400 animate-fade-in-up delay-300">
                    <img src="../assets/images/OrangUtan.jpg" alt="Orangutan Bermain" class="h-48 w-full object-cover transition-transform duration-500 hover:scale-110">
                    <div class="p-4 flex-1 flex flex-col">
                        <div class="font-bold text-lg text-green-900 mb-1">Orangutan</div>
                        <div class="text-sm text-green-700 mb-2 flex-1">Orangutan bermain di antara dahan pohon dengan lincah.</div>
                        <span class="text-xs text-gray-400">Sumber: Safari Zoo</span>
                    </div>
                </div>
                <!-- Card 5 -->
                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col transform transition duration-500 hover:scale-105 hover:shadow-lime-200/60 border-2 border-lime-100 hover:border-lime-400 animate-fade-in-up delay-400">
                    <img src="../assets/images/jerapah.jpg" alt="Jerapah Makan Daun" class="h-48 w-full object-cover transition-transform duration-500 hover:scale-110">
                    <div class="p-4 flex-1 flex flex-col">
                        <div class="font-bold text-lg text-green-900 mb-1">Jerapah</div>
                        <div class="text-sm text-green-700 mb-2 flex-1">Jerapah sedang memakan daun dari pohon yang tinggi.</div>
                        <span class="text-xs text-gray-400">Sumber: Safari Zoo</span>
                    </div>
                </div>
                <!-- Card 6 -->
                <div class="bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col transform transition duration-500 hover:scale-105 hover:shadow-pink-200/60 border-2 border-pink-100 hover:border-pink-400 animate-fade-in-up delay-500">
                    <img src="../assets/images/Panda.jpg" alt="Panda Makan Bambu" class="h-48 w-full object-cover transition-transform duration-500 hover:scale-110">
                    <div class="p-4 flex-1 flex flex-col">
                        <div class="font-bold text-lg text-green-900 mb-1">Panda</div>
                        <div class="text-sm text-green-700 mb-2 flex-1">Panda lucu sedang asyik makan bambu di habitatnya.</div>
                        <span class="text-xs text-gray-400">Sumber: Safari Zoo</span>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <footer class="bg-white/80 backdrop-blur shadow-inner text-center py-4 text-xs text-gray-500 mt-8">
        &copy; <?= date('Y') ?> Kebun Binatang Safari. All rights reserved.
    </footer>
</body>
</html> 