<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah session status sudah 'login'
if (!isset($_SESSION['status']) || $_SESSION['status'] !== "login") {
    // Jika belum login, tendang kembali ke halaman login (index.php)
    header("Location: index.php");
    exit();
}
?>