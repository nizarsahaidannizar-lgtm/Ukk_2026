<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: kelola_jenis_pelanggaran.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['pesan_error'] = 'Jenis pelanggaran tidak valid.';
    header('Location: kelola_jenis_pelanggaran.php');
    exit;
}

$stmt = mysqli_prepare(
    $koneksi,
    'UPDATE t_pelanggaran SET status_aktif = 0, updated_at = CURRENT_TIMESTAMP
     WHERE id = ? AND status_aktif = 1'
);
if (!$stmt) {
    error_log('Gagal menyiapkan penonaktifan jenis pelanggaran: ' . mysqli_error($koneksi));
    $_SESSION['pesan_error'] = 'Jenis pelanggaran gagal dinonaktifkan.';
    header('Location: kelola_jenis_pelanggaran.php');
    exit;
}
mysqli_stmt_bind_param($stmt, 'i', $id);

if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) === 1) {
    $_SESSION['pesan_sukses'] = 'Jenis pelanggaran berhasil dinonaktifkan. Riwayat yang sudah tercatat tetap tersimpan.';
} else {
    if (mysqli_stmt_error($stmt) !== '') {
        error_log('Gagal menonaktifkan jenis pelanggaran: ' . mysqli_stmt_error($stmt));
    }
    $_SESSION['pesan_error'] = 'Jenis pelanggaran tidak ditemukan atau sudah tidak aktif.';
}
mysqli_stmt_close($stmt);
header('Location: kelola_jenis_pelanggaran.php');
exit;
