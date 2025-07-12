<?php
session_start();
include '../includes/db.php';

$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($query);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Jika password di-hash gunakan password_verify, jika tidak, gunakan perbandingan biasa
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            
            // Redirect berdasarkan role
            if ($user['role'] === 'admin') {
                header("Location: ../admin/admin_dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        } else {
            $login_error = "Password salah!";
        }
    } else {
        $login_error = "Email tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/login-anim.css">
    <style>
        /* Efek 3D Card */
        #login-card {
            box-shadow: 0 10px 30px 0 rgba(34,197,94,0.25), 0 2px 4px 0 rgba(0,0,0,0.10);
            transition: transform 0.4s cubic-bezier(.25,.8,.25,1), box-shadow 0.4s;
            perspective: 1200px;
        }
        #login-card:hover {
            transform: scale(1.025) rotateY(4deg) rotateX(2deg);
            box-shadow: 0 20px 50px 0 rgba(34,197,94,0.35), 0 4px 8px 0 rgba(0,0,0,0.15);
        }
        /* Efek 3D pada gambar */
        .tilt-img {
            transition: transform 0.3s cubic-bezier(.25,.8,.25,1), box-shadow 0.3s;
            will-change: transform;
        }
        .tilt-img:hover {
            box-shadow: 0 8px 24px 0 rgba(34,197,94,0.25), 0 1.5px 3px 0 rgba(0,0,0,0.10);
        }
        /* Animasi masuk card */
        .animate-3d-in {
            animation: card3dIn 0.7s cubic-bezier(.25,.8,.25,1);
        }
        @keyframes card3dIn {
            0% { opacity: 0; transform: scale(0.92) rotateY(10deg) translateY(40px); }
            60% { opacity: 1; transform: scale(1.04) rotateY(-2deg) translateY(-8px); }
            100% { opacity: 1; transform: scale(1) rotateY(0deg) translateY(0); }
        }
        /* Efek input 3D focus */
        .input-3d:focus {
            box-shadow: 0 0 0 4px rgba(34,197,94,0.15), 0 2px 8px 0 rgba(34,197,94,0.10);
            background: #fff;
            outline: none;
        }
        /* Tombol login 3D */
        .btn-3d {
            box-shadow: 0 2px 8px 0 rgba(34,197,94,0.15);
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .btn-3d:active {
            transform: scale(0.97) translateY(2px);
            box-shadow: 0 1px 2px 0 rgba(34,197,94,0.10);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-200 via-green-100 to-green-300">
    <div id="login-card" class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden border border-green-200 opacity-0 translate-y-8 transition-all duration-700 animate-3d-in">
        <!-- Notifikasi -->
        <?php if ($login_error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 animate-shake">
                <?= $login_error ?>
            </div>
        <?php endif; ?>
        <div id="notif" class="hidden px-6 pt-6"></div>
        <!-- Header -->
        <div class="flex flex-col items-center px-6 pt-8 pb-4 bg-gradient-to-r from-green-700 to-green-500">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10" /><path d="M12 16v-4" /><path d="M12 8h.01" /></svg>
                <span class="text-2xl font-bold text-white tracking-wide">Login</span>
            </div>
            <p class="text-green-100 text-sm">Welcome back! Please login to your account</p>
        </div>
        <!-- Illustration -->
        <div class="flex justify-center items-center py-4 bg-green-50">
            <img src="../assets/images/login.jpg" alt="Forest Animals" class="w-11/12 h-36 object-cover rounded-xl shadow-md border border-green-200 tilt-img" id="tilt-img" />
        </div>
        <!-- Form -->
        <form id="login-form" method="POST" action="" class="px-8 py-6 space-y-5" autocomplete="off" novalidate>
            <div>
                <label class="block text-green-900 text-sm font-medium mb-1" for="email">Email</label>
                <input id="email" name="email" type="email" placeholder="Enter your email" class="w-full px-4 py-2 rounded-lg bg-green-100 text-green-900 placeholder-green-400 border border-green-200 focus:outline-none focus:ring-2 focus:ring-green-400 focus:bg-white transition-all duration-300 input-3d" required />
                <p id="error-email" class="text-red-500 text-xs mt-1 hidden"></p>
            </div>
            <div class="relative">
                <label class="block text-green-900 text-sm font-medium mb-1" for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Enter your password" class="w-full px-4 py-2 rounded-lg bg-green-100 text-green-900 placeholder-green-400 border border-green-200 focus:outline-none focus:ring-2 focus:ring-green-400 focus:bg-white transition-all duration-300 pr-10 input-3d" required />
                <button type="button" tabindex="-1" id="toggle-password" class="absolute right-3 top-9 text-green-500 focus:outline-none" aria-label="Show/Hide Password">
                    <svg id="eye-open" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg id="eye-closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a21.77 21.77 0 0 1 5.06-6.06M1 1l22 22"/><path d="M9.53 9.53A3 3 0 0 0 12 15a3 3 0 0 0 2.47-5.47"/></svg>
                </button>
                <p id="error-password" class="text-red-500 text-xs mt-1 hidden"></p>
            </div>
            <button type="submit" class="w-full py-2 rounded-lg bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold text-lg shadow-md hover:from-green-600 hover:to-green-700 hover:shadow-lg transition-colors duration-200 btn-3d">Login</button>
        </form>
        <div class="px-8 pb-6 text-center">
            <a href="Register.php" class="text-green-500 text-sm hover:underline">Don't have an account? Register</a>
        </div>
    </div>
<script>
// Animasi masuk card
window.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        const card = document.getElementById('login-card');
        card.classList.remove('opacity-0', 'translate-y-8');
        card.classList.add('opacity-100', 'translate-y-0', 'animate-3d-in');
    }, 100);
});

// Efek tilt 3D pada gambar
const tiltImg = document.getElementById('tilt-img');
if (tiltImg) {
    tiltImg.addEventListener('mousemove', function(e) {
        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const rotateX = ((y - centerY) / centerY) * 8; // max 8deg
        const rotateY = ((x - centerX) / centerX) * -8;
        this.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.04)`;
    });
    tiltImg.addEventListener('mouseleave', function() {
        this.style.transform = 'rotateX(0deg) rotateY(0deg) scale(1)';
    });
}

// Validasi dan notifikasi
const form = document.getElementById('login-form');
const notif = document.getElementById('notif');

// Toggle show/hide password
function togglePassword(inputId, eyeOpenId, eyeClosedId) {
    const input = document.getElementById(inputId);
    const eyeOpen = document.getElementById(eyeOpenId);
    const eyeClosed = document.getElementById(eyeClosedId);
    if (input.type === 'password') {
        input.type = 'text';
        eyeOpen.classList.add('hidden');
        eyeClosed.classList.remove('hidden');
    } else {
        input.type = 'password';
        eyeOpen.classList.remove('hidden');
        eyeClosed.classList.add('hidden');
    }
}
document.getElementById('toggle-password').addEventListener('click', function() {
    togglePassword('password', 'eye-open', 'eye-closed');
});

form.addEventListener('submit', function(e) {
    e.preventDefault();
    let valid = true;
    document.querySelectorAll('[id^="error-"]').forEach(el => { el.classList.add('hidden'); el.textContent = ''; });
    notif.classList.add('hidden');
    notif.innerHTML = '';

    const email = form.email.value.trim();
    const password = form.password.value;

    // Validasi email
    if (!/^\S+@\S+\.\S+$/.test(email)) {
        showError('email', 'Please enter a valid email address.');
        valid = false;
    }
    // Validasi password
    if (password.length < 6) {
        showError('password', 'Password must be at least 6 characters.');
        valid = false;
    }

    if (valid) {
        // Jika validasi berhasil, submit form
        this.submit();
    } else {
        notif.innerHTML = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 animate-shake">Please fix the errors below.</div>';
        notif.classList.remove('hidden');
    }
});

function showError(field, message) {
    const el = document.getElementById('error-' + field);
    el.textContent = message;
    el.classList.remove('hidden');
}

// Animasi input focus
const inputs = form.querySelectorAll('input');
inputs.forEach(input => {
    input.addEventListener('focus', () => {
        input.classList.add('ring-2', 'ring-green-400', 'scale-105');
    });
    input.addEventListener('blur', () => {
        input.classList.remove('ring-2', 'ring-green-400', 'scale-105');
    });
});

// Animasi CSS tambahan
const style = document.createElement('style');
style.innerHTML = `
@keyframes bounce-in {
  0% { transform: scale(0.9); opacity: 0; }
  60% { transform: scale(1.05); opacity: 1; }
  100% { transform: scale(1); }
}
.animate-bounce-in { animation: bounce-in 0.5s; }
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  20%, 60% { transform: translateX(-8px); }
  40%, 80% { transform: translateX(8px); }
}
.animate-shake { animation: shake 0.4s; }
`;
document.head.appendChild(style);
</script>
</body>
</html>
