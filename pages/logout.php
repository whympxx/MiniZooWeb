<?php
session_start();

// Hapus semua session variables
session_unset();

// Hancurkan session
session_destroy();

// Redirect ke halaman login
header("Location: Login.php");
exit;
?>
