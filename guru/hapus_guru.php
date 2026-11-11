
<?php
// guru/hapus_guru.php

include '../includes/cek_session.php';
include '../config/koneksi.php';

// Pastikan hanya admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

// Pastikan ID tersedia
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    $_SESSION['pesan_error'] = "ID guru tidak valid.";
    header("Location: kelola_guru.php");
    exit;
}

$id = (int) $_GET['id'];

// Ambil nama guru sebelum dihapus
$stmt = mysqli_prepare($koneksi, "SELECT nama FROM t_guru WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    $_SESSION['pesan_error'] = "Data guru tidak ditemukan.";
    header("Location: kelola_guru.php");
    exit;
}

$nama_guru = $data['nama'];

// Hapus data guru
$stmt_hapus = mysqli_prepare($koneksi, "DELETE FROM t_guru WHERE id = ?");
mysqli_stmt_bind_param($stmt_hapus, "i", $id);

if (mysqli_stmt_execute($stmt_hapus)) {

    $_SESSION['pesan_sukses'] =
        "Data guru <strong>" .
        htmlspecialchars($nama_guru, ENT_QUOTES, 'UTF-8') .
        "</strong> berhasil dihapus!";

} else {

    $_SESSION['pesan_error'] =
        "Gagal menghapus data guru: " . mysqli_error($koneksi);

}

header("Location: kelola_guru.php");
exit;
?>
