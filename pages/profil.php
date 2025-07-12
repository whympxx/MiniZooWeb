<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Proses update profil
$update_success = null;
$update_error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['username']);
    $new_password = trim($_POST['password']);
    $old_password = isset($_POST['old_password']) ? trim($_POST['old_password']) : '';
    $profile_picture = $_FILES['profile_picture'];
    $update_fields = [];
    $params = [];
    $types = '';

    // Update nama
    if (!empty($new_name)) {
        $update_fields[] = 'username = ?';
        $params[] = $new_name;
        $types .= 's';
    }

    // Update password jika diisi
    if (!empty($new_password)) {
        // Cek password lama
        if (empty($old_password)) {
            $update_error = 'Masukkan password lama untuk mengganti password.';
        } else if (!password_verify($old_password, $user['password'])) {
            $update_error = 'Password lama salah.';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_fields[] = 'password = ?';
            $params[] = $hashed_password;
            $types .= 's';
        }
    }

    // Update foto profil jika diupload
    if ($profile_picture['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($profile_picture['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($ext), $allowed)) {
            $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
            $upload_path = 'uploads/' . $new_filename;
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }
            if (move_uploaded_file($profile_picture['tmp_name'], $upload_path)) {
                $update_fields[] = 'profile_picture = ?';
                $params[] = $upload_path;
                $types .= 's';
            } else {
                $update_error = 'Gagal mengupload foto.';
            }
        } else {
            $update_error = 'Format foto tidak didukung.';
        }
    }

    if (count($update_fields) > 0 && !$update_error) {
        $params[] = $user_id;
        $types .= 'i';
        $query = "UPDATE users SET ".implode(', ', $update_fields).", updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            $update_success = 'Profil berhasil diperbarui!';
            // Refresh data user
            $query = "SELECT * FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $update_error = 'Gagal memperbarui profil.';
        }
    }
}

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
    <title>Profil Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="../assets/css/profil-tailwind.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-green-100 via-blue-100 to-yellow-100 min-h-screen flex flex-col items-center justify-center">
    <div class="w-full max-w-xl mx-auto mt-12 animate-fade-in-up">
        <div class="bg-white rounded-3xl shadow-2xl p-8 flex flex-col items-center relative profil-card">
            <div class="absolute -top-10 left-1/2 transform -translate-x-1/2">
                <?php if (!empty($user['profile_picture'])): ?>
                    <img src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Foto Profil" class="inline-block w-24 h-24 rounded-full border-4 border-green-300 shadow-lg object-cover" />
                <?php else: ?>
                    <span class="inline-block w-24 h-24 rounded-full bg-gradient-to-br from-green-200 to-blue-200 flex items-center justify-center text-6xl border-4 border-green-300 shadow-lg animate-bounce-slow">🧑</span>
                <?php endif; ?>
            </div>
            <div class="mt-20 text-center z-10">
                <h2 class="text-2xl font-extrabold text-green-900 mb-1 animate-slide-in"><?= htmlspecialchars($user['username']) ?></h2>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium <?= $user['role'] === 'admin' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?> zoo-role animate-pop">
                    <?= ucfirst($user['role']) ?>
                </span>
            </div>
            <div class="w-full grid grid-cols-1 md:grid-cols-2 gap-6 mt-8 animate-fade-in">
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
            <a href="dashboard.php" class="mt-8 inline-block bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg font-semibold shadow transition-colors animate-fade-in-up">Kembali ke Dashboard</a>
            <?php if ($update_success): ?>
                <div class="mt-4 text-green-600 font-semibold text-center"> <?= $update_success ?> </div>
            <?php elseif ($update_error): ?>
                <div class="mt-4 text-red-600 font-semibold text-center"> <?= $update_error ?> </div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data" class="w-full mt-8 bg-gray-50 rounded-xl p-6 shadow-inner animate-fade-in z-10">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Pengguna</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-300" required />
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Password Lama <span class="text-xs text-gray-400">(Wajib diisi jika ingin mengganti password)</span></label>
                    <input type="password" name="old_password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-300" />
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Password Baru <span class="text-xs text-gray-400">(Kosongkan jika tidak ingin mengubah)</span></label>
                    <input type="password" name="password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-300" />
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Foto Profil</label>
                    <input type="file" name="profile_picture" accept="image/*" class="w-full" />
                </div>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold shadow transition-colors">Simpan Perubahan</button>
            </form>
            <div class="absolute -bottom-10 right-10 w-24 h-24 bg-gradient-to-tr from-yellow-200 to-green-200 rounded-full opacity-60 blur-2xl animate-pulse z-0"></div>
            <div class="absolute -top-8 left-8 w-16 h-16 bg-gradient-to-tr from-blue-200 to-green-100 rounded-full opacity-50 blur-2xl animate-pulse z-0"></div>
        </div>
    </div>
</body>
</html> 