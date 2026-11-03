<?php
include '../includes/cek_session.php';
include '../config/koneksi.php';
include '../includes/akses_pelanggaran.php';

$konteks = konteks_akses_pelanggaran($koneksi);
$lingkup = lingkup_sql_pelanggaran($konteks, 'ps');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: catat_pelanggaran.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['pesan_error'] = 'Catatan pelanggaran tidak valid.';
    header('Location: catat_pelanggaran.php');
    exit;
}

$stmt = mysqli_prepare($koneksi, "DELETE ps FROM t_pelanggaran_siswa ps WHERE ps.id = ? AND $lingkup");
if (!$stmt) {
    error_log('Gagal menyiapkan penghapusan catatan: ' . mysqli_error($koneksi));
    $_SESSION['pesan_error'] = 'Catatan pelanggaran gagal dihapus.';
    header('Location: catat_pelanggaran.php');
    exit;
}
mysqli_stmt_bind_param($stmt, 'i', $id);

if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) === 1) {
    $_SESSION['pesan_sukses'] = 'Catatan pelanggaran berhasil dihapus.';
} else {
    if (mysqli_stmt_error($stmt) !== '') {
        error_log('Gagal menghapus catatan pelanggaran: ' . mysqli_stmt_error($stmt));
    }
    $_SESSION['pesan_error'] = 'Catatan tidak ditemukan, tidak dapat dihapus, atau Anda tidak memiliki akses.';
}

mysqli_stmt_close($stmt);
header('Location: catat_pelanggaran.php');
exit;
