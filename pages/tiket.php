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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $tanggal = $_POST['tanggal'];
    $jumlah = $_POST['jumlah'];
    $kategori = $_POST['kategori'];

    // Validasi sederhana
    if (!$nama || !$email || !$tanggal || !$jumlah || !$kategori) {
        echo '<div class="text-red-600 text-center mb-4">Semua field harus diisi.</div>';
    } else {
        $insert = $conn->prepare("INSERT INTO orders (user_id, nama, email, tanggal, jumlah, kategori, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $insert->bind_param("isssis", $user_id, $nama, $email, $tanggal, $jumlah, $kategori);
        if ($insert->execute()) {
            $order_id = $conn->insert_id;
            header("Location: tiket_konfirmasi.php?order_id=" . $order_id);
            exit;
        } else {
            echo '<div class="text-red-600 text-center mb-4">Gagal memproses pesanan. Silakan coba lagi.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Tiket - ZooDash</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="../assets/css/tiket-tailwind.css" rel="stylesheet">
    <style>
      body { font-family: 'Inter', 'Comic Sans MS', 'Comic Sans', cursive, sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-green-50 to-blue-50 min-h-screen flex flex-col">
    <div class="flex-1 flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 ticket-form animate-fade-in">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-gradient-to-br from-green-200 to-blue-200 rounded-full opacity-30 animate-bounce-slow"></div>
            <div class="text-center">
                <span class="inline-block w-16 h-16 rounded-full bg-green-100 flex items-center justify-center text-4xl border-4 border-green-200 mb-2 animate-pop">🎟️</span>
                <h2 class="mt-2 text-2xl font-extrabold text-green-900">Pesan Tiket Safari</h2>
                <p class="mt-1 text-green-700">Isi formulir di bawah untuk memesan tiket kunjungan Anda ke Kebun Binatang Safari.</p>
            </div>
            <form class="mt-8 space-y-6" action="#" method="POST" autocomplete="off">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div class="mb-4">
                        <label for="nama" class="block text-sm font-medium text-green-800 mb-2">Nama Lengkap</label>
                        <input id="nama" name="nama" type="text" required placeholder="Nama Lengkap" value="<?= htmlspecialchars($user['username']) ?>" class="w-full rounded-lg border border-green-200 px-3 py-2 text-green-900 placeholder-green-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-green-400 transition-all">
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-green-800 mb-2">Email</label>
                        <input id="email" name="email" type="email" required placeholder="Email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full rounded-lg border border-green-200 px-3 py-2 text-green-900 placeholder-green-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-green-400 transition-all">
                    </div>
                    <div class="mb-4">
                        <label for="tanggal" class="block text-sm font-medium text-green-800 mb-2">Tanggal Kunjungan</label>
                        <input id="tanggal" name="tanggal" type="date" required class="w-full rounded-lg border border-green-200 px-3 py-2 text-green-900 placeholder-green-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-green-400 transition-all">
                    </div>
                    <div class="mb-4">
                        <label for="jumlah" class="block text-sm font-medium text-green-800 mb-2">Jumlah Tiket</label>
                        <input id="jumlah" name="jumlah" type="number" min="1" max="10" required placeholder="Jumlah Tiket" class="w-full rounded-lg border border-green-200 px-3 py-2 text-green-900 placeholder-green-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-green-400 transition-all">
                    </div>
                    <div class="mb-4">
                        <label for="kategori" class="block text-sm font-medium text-green-800 mb-2">Kategori Tiket</label>
                        <select id="kategori" name="kategori" required class="w-full rounded-lg border border-green-200 px-3 py-2 text-green-900 placeholder-green-400 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-green-400 transition-all">
                            <option value="">Pilih Kategori</option>
                            <option value="dewasa">Dewasa</option>
                            <option value="anak">Anak-anak</option>
                            <option value="keluarga">Keluarga</option>
                        </select>
                    </div>
                </div>
                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-6 text-lg font-bold rounded-lg text-white bg-gradient-to-r from-green-400 to-blue-400 hover:from-green-500 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-400 shadow-lg transition-all animate-pulse-once relative">
                        Pesan Sekarang
                        <span class="absolute right-4 top-1/2 transform -translate-y-1/2 text-2xl animate-bounce">👉</span>
                    </button>
                </div>
            </form>
            <div class="mt-6 text-center text-xs text-gray-400">&copy; <?= date('Y') ?> Kebun Binatang Safari</div>
        </div>
    </div>
    <script>
      // Animasi fade-in
      document.addEventListener('DOMContentLoaded', function() {
        const fadeInElement = document.querySelector('.animate-fade-in');
        if (fadeInElement) {
          fadeInElement.style.opacity = 0;
          setTimeout(() => {
            fadeInElement.style.transition = 'opacity 1s';
            fadeInElement.style.opacity = 1;
          }, 100);
        }

        // Animasi pop
        const popElement = document.querySelector('.animate-pop');
        if (popElement) {
          popElement.animate([
            { transform: 'scale(0.7)' },
            { transform: 'scale(1.15)' },
            { transform: 'scale(1)' }
          ], {
            duration: 600,
            easing: 'cubic-bezier(.4,0,.2,1)'
          });
        }

        // Animasi pulse sekali
        const pulseElement = document.querySelector('.animate-pulse-once');
        if (pulseElement) {
          pulseElement.animate([
            { boxShadow: '0 0 0 0 #6ee7b7' },
            { boxShadow: '0 0 0 12px #6ee7b700' }
          ], {
            duration: 800,
            easing: 'ease-out'
          });
        }

        // Animasi bounce lambat
        const bounceElements = document.querySelectorAll('.animate-bounce-slow');
        bounceElements.forEach(el => {
          el.animate([
            { transform: 'translateY(0px)' },
            { transform: 'translateY(-18px)' },
            { transform: 'translateY(0px)' }
          ], {
            duration: 2200,
            iterations: Infinity,
            easing: 'ease-in-out'
          });
        });
      });
    </script>
</body>
</html> 