<?php
// hapus_tahun_ajaran.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// Ambil ID dari URL
$id = $_GET['id'] ?? '';

if (!ctype_digit((string) $id) || $id === '') {
    header('Location: kelola_tahun_ajaran.php');
    exit;
}

// 1. Ambil nama tahun ajaran sebelum dihapus
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT nama FROM t_tahun_ajaran WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($hasil);
mysqli_stmt_close($stmt);

// Periksa apakah data ditemukan
if (!$data) {
    $_SESSION['pesan_error'] = "Data tahun ajaran tidak ditemukan!";
    header('Location: kelola_tahun_ajaran.php');
    exit;
}

// 2. Hapus data tahun ajaran (dengan Try-Catch jika ada Foreign Key)
$stmt_del = mysqli_prepare(
    $koneksi,
    "DELETE FROM t_tahun_ajaran WHERE id = ?"
);

mysqli_stmt_bind_param($stmt_del, "i", $id);

try {
    if (mysqli_stmt_execute($stmt_del)) {
        $_SESSION['pesan_sukses'] = "Tahun ajaran <strong>" . htmlspecialchars($data['nama']) . "</strong> berhasil dihapus!";
    } else {
        $_SESSION['pesan_error'] = "Gagal menghapus tahun ajaran!";
    }
} catch (mysqli_sql_exception $e) {
    // Jika data terikat di tabel penempatan / wali kelas
    $_SESSION['pesan_error'] = "Tahun ajaran <strong>" . htmlspecialchars($data['nama']) . "</strong> tidak dapat dihapus karena masih digunakan di data kelas/wali kelas!";
}

mysqli_stmt_close($stmt_del);

header('Location: kelola_tahun_ajaran.php');
exit;
?>