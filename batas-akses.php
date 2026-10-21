<?php
// includes/cek_session.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek status login
if (!isset($_SESSION['status']) || $_SESSION['status'] !== "login") {
    header("Location: index.php");
    exit();
}

// FUNGSI PEMBATAS HAK AKSES
function batasi_akses(array $roles_diizinkan) {
    $role_aktif = strtolower(trim($_SESSION['role'] ?? ''));
    $allowed    = array_map('strtolower', $roles_diizinkan);

    // Jika role user TIDAK ADA dalam daftar yang diizinkan
    if (!in_array($role_aktif, $allowed, true)) {
        $_SESSION['pesan_error'] = "Akses ditolak! Anda tidak memiliki izin ke halaman ini.";

        // Alihkan kembali ke Dashboard
        if (strpos($_SERVER['PHP_SELF'], '/guru/') !== false) {
            header("Location: ../dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit(); // PENTING: Wajib dipanggil agar kode di bawahnya tidak dieksekusi!
    }
}
?>