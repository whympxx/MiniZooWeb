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
    <title>Statistik Kebun Binatang</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="../assets/css/statistik-tailwind.css" rel="stylesheet">
    <style>
      .fade-in {
        animation: fadeIn 1.2s ease-in;
      }
      @keyframes fadeIn {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
      }
      .bounce {
        animation: bounceAnim 1.5s infinite alternate;
      }
      @keyframes bounceAnim {
        from { transform: translateY(0); }
        to { transform: translateY(-18px); }
      }
    </style>
</head>
<body class="bg-gradient-to-br from-yellow-100 via-green-200 to-blue-100 min-h-screen flex flex-col">
    <header class="bg-white/80 backdrop-blur shadow flex items-center justify-between px-6 py-4">
        <div class="flex items-center gap-3">
            <span class="inline-block w-8 h-8 bg-green-200 rounded-full flex items-center justify-center text-xl">🦁</span>
            <span class="font-bold text-xl text-green-900">Statistik Kebun Binatang</span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-green-800 font-semibold flex items-center gap-2"><span>👋</span> <?= htmlspecialchars($user['username']) ?></span>
            <a href="dashboard.php" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg transition-colors text-sm">Dashboard</a>
            <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg transition-colors text-sm">Logout</a>
        </div>
    </header>
    <main class="flex-1 flex flex-col items-center justify-center p-6 md:p-10 fade-in">
        <h1 class="text-3xl md:text-4xl font-extrabold text-green-900 mb-8 flex items-center gap-3">
            <span class="animate-spin-slow">📊</span> Statistik Pengunjung & Koleksi
        </h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 w-full max-w-5xl mb-12">
            <div class="bg-green-50 rounded-2xl p-8 flex flex-col items-center shadow-lg hover:scale-105 transition-transform duration-300 bounce">
                <span class="text-5xl mb-3">🦓</span>
                <div class="text-lg text-green-800 font-semibold mb-1">Total Pengunjung Bulan Ini</div>
                <div class="text-3xl font-bold text-green-900">12,340</div>
            </div>
            <div class="bg-blue-50 rounded-2xl p-8 flex flex-col items-center shadow-lg hover:scale-105 transition-transform duration-300 bounce" style="animation-delay:0.5s;">
                <span class="text-5xl mb-3">🦒</span>
                <div class="text-lg text-blue-800 font-semibold mb-1">Koleksi Hewan</div>
                <div class="text-3xl font-bold text-blue-900">87</div>
            </div>
            <div class="bg-yellow-50 rounded-2xl p-8 flex flex-col items-center shadow-lg hover:scale-105 transition-transform duration-300 bounce" style="animation-delay:1s;">
                <span class="text-5xl mb-3">🦁</span>
                <div class="text-lg text-yellow-800 font-semibold mb-1">Rata-rata Rating Pengunjung</div>
                <div class="text-3xl font-bold text-yellow-900">4.8/5</div>
            </div>
        </div>
        <div class="w-full max-w-4xl bg-white rounded-2xl shadow-lg p-8 mt-4">
            <h2 class="text-2xl font-bold text-green-800 mb-6 flex items-center gap-2">
                <span class="text-2xl">📈</span> Grafik Pengunjung Mingguan
            </h2>
            <canvas id="visitorChart" class="w-full h-64"></canvas>
        </div>
    </main>
    <footer class="bg-white/80 backdrop-blur shadow-inner text-center py-4 text-xs text-gray-500">
        &copy; <?= date('Y') ?> Kebun Binatang Safari. All rights reserved.
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
      // Dummy data for chart
      const ctx = document.getElementById('visitorChart').getContext('2d');
      const visitorChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
          datasets: [{
            label: 'Pengunjung',
            data: [1200, 1500, 1100, 1800, 2000, 2500, 2240],
            backgroundColor: 'rgba(34,197,94,0.2)',
            borderColor: 'rgba(34,197,94,1)',
            borderWidth: 3,
            pointBackgroundColor: 'rgba(34,197,94,1)',
            pointRadius: 6,
            tension: 0.4,
            fill: true,
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { stepSize: 500 }
            }
          },
          animation: {
            duration: 1800,
            easing: 'easeOutBounce'
          }
        }
      });
    </script>
</body>
</html> 