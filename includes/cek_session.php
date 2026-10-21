<?php
// includes/cek_session.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah pengguna sudah login
if (!isset($_SESSION['status']) || $_SESSION['status'] !== "login") {
    header("Location: index.php");
    exit();
}

/**
 * Fungsi Pembatasan Hak Akses (RBAC)
 * @param array $roles_diizinkan Array role yang boleh mengakses halaman ini
 */
function batasi_akses(array $roles_diizinkan) {
    $role_aktif = strtolower($_SESSION['role'] ?? '');

    // Jika role user tidak terdaftar dalam $roles_diizinkan
    if (!in_array($role_aktif, array_map('strtolower', $roles_diizinkan))) {
        $_SESSION['pesan_error'] = "Akses ditolak! Anda tidak memiliki izin untuk mengakses halaman tersebut.";

        // Kembalikan ke dashboard (menyesuaikan jika halaman berada dalam subfolder guru/)
        if (strpos($_SERVER['PHP_SELF'], '/guru/') !== false) {
            header("Location: ../dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    }
}
?>