<?php
// hapus_kategori_pelanggaran.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'includes/cek_session.php';
include 'config/koneksi.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['pesan_error'] = "ID Kategori Pelanggaran tidak valid!";
    header('Location: kelola_pelanggaran_kategori.php');
    exit();
}

// 1. Ambil nama kategori sebelum dihapus
$stmt_cek = mysqli_prepare(
    $koneksi,
    "SELECT nama FROM t_pelanggaran_kategori WHERE id = ?"
);
mysqli_stmt_bind_param($stmt_cek, "i", $id);
mysqli_stmt_execute($stmt_cek);

$res_cek = mysqli_stmt_get_result($stmt_cek);
$data = mysqli_fetch_assoc($res_cek);
mysqli_stmt_close($stmt_cek);

if (!$data) {
    $_SESSION['pesan_error'] = "Data kategori pelanggaran tidak ditemukan!";
    header('Location: kelola_pelanggaran_kategori.php');
    exit();
}

// 2. Eksekusi Hapus Data Kategori Pelanggaran
$stmt_del = mysqli_prepare(
    $koneksi,
    "DELETE FROM t_pelanggaran_kategori WHERE id = ?"
);
mysqli_stmt_bind_param($stmt_del, "i", $id);

try {
    if (mysqli_stmt_execute($stmt_del)) {
        $_SESSION['pesan_sukses'] = "Kategori pelanggaran <strong>" . htmlspecialchars($data['nama']) . "</strong> berhasil dihapus!";
    } else {
        $_SESSION['pesan_error'] = "Gagal menghapus kategori pelanggaran!";
    }
} catch (mysqli_sql_exception $e) {
    // Menagani error jika kategori masih terikat di tabel jenis/poin pelanggaran
    $_SESSION['pesan_error'] = "Kategori <strong>" . htmlspecialchars($data['nama']) . "</strong> tidak dapat dihapus karena masih digunakan di data pelanggaran!";
}

mysqli_stmt_close($stmt_del);

header('Location: kelola_pelanggaran_kategori.php');
exit();
?>