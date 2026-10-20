<?php
// hapus_kelas.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/cek_session.php';
include 'config/koneksi.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['pesan_error'] = "ID Kelas tidak valid!";
    header('Location: kelola_kelas.php');
    exit();
}

// 1. Ambil nama kelas sebelum dihapus
$stmt_cek = mysqli_prepare(
    $koneksi,
    "SELECT nama FROM t_kelas WHERE id = ?"
);
mysqli_stmt_bind_param($stmt_cek, "i", $id);
mysqli_stmt_execute($stmt_cek);

$res_cek = mysqli_stmt_get_result($stmt_cek);
$data = mysqli_fetch_assoc($res_cek);
mysqli_stmt_close($stmt_cek);

if (!$data) {
    $_SESSION['pesan_error'] = "Data kelas tidak ditemukan!";
    header('Location: kelola_kelas.php');
    exit();
}

// 2. Eksekusi Hapus Data Kelas (dengan Try-Catch)
$stmt_del = mysqli_prepare(
    $koneksi,
    "DELETE FROM t_kelas WHERE id = ?"
);
mysqli_stmt_bind_param($stmt_del, "i", $id);

try {
    if (mysqli_stmt_execute($stmt_del)) {
        $_SESSION['pesan_sukses'] = "Kelas <strong>" . htmlspecialchars($data['nama']) . "</strong> berhasil dihapus!";
    } else {
        $_SESSION['pesan_error'] = "Gagal menghapus data kelas!";
    }
} catch (mysqli_sql_exception $e) {
    // Jika data terikat di tabel penempatan siswa / wali kelas
    $_SESSION['pesan_error'] = "Kelas <strong>" . htmlspecialchars($data['nama']) . "</strong> tidak dapat dihapus karena masih digunakan di data penempatan siswa atau wali kelas!";
}

mysqli_stmt_close($stmt_del);

header('Location: kelola_kelas.php');
exit();
?>