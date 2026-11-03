<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: kelola_jenis_pelanggaran.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$kode = trim($_POST['kode'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$kategori_id = filter_input(INPUT_POST, 'pelanggaran_kategori_id', FILTER_VALIDATE_INT);
$poin = filter_input(INPUT_POST, 'poin', FILTER_VALIDATE_INT);
$deskripsi = trim($_POST['deskripsi'] ?? '');
$status_aktif = filter_input(INPUT_POST, 'status_aktif', FILTER_VALIDATE_INT);

if (
    !$id || $nama === '' || $kategori_id === false || !$kategori_id ||
    $poin === false || $poin === null || $poin < 0 ||
    !in_array($status_aktif, [0, 1], true)
) {
    $_SESSION['pesan_error'] = 'Data jenis pelanggaran tidak valid.';
    header('Location: kelola_jenis_pelanggaran.php');
    exit;
}

$stmt = mysqli_prepare($koneksi, 'SELECT id, pelanggaran_kategori_id FROM t_pelanggaran WHERE id = ?');
if (!$stmt) {
    error_log('Gagal memeriksa jenis pelanggaran: ' . mysqli_error($koneksi));
    $_SESSION['pesan_error'] = 'Jenis pelanggaran gagal diperbarui.';
    header('Location: kelola_jenis_pelanggaran.php');
    exit;
}
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$jenis_ada = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$jenis_ada) {
    $_SESSION['pesan_error'] = 'Jenis pelanggaran tidak ditemukan.';
    header('Location: kelola_jenis_pelanggaran.php');
    exit;
}

$stmt = mysqli_prepare(
    $koneksi,
    'SELECT id FROM t_pelanggaran_kategori WHERE id = ? AND (status_aktif = 1 OR id = ?)'
);
if (!$stmt) {
    error_log('Gagal memeriksa kategori edit jenis pelanggaran: ' . mysqli_error($koneksi));
    $_SESSION['pesan_error'] = 'Jenis pelanggaran gagal diperbarui.';
    header('Location: edit_jenis_pelanggaran.php?id=' . $id);
    exit;
}
$kategori_asal_id = (int)$jenis_ada['pelanggaran_kategori_id'];
mysqli_stmt_bind_param($stmt, 'ii', $kategori_id, $kategori_asal_id);
mysqli_stmt_execute($stmt);
$kategori_valid = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$kategori_valid) {
    $_SESSION['pesan_error'] = 'Kategori tidak ditemukan atau tidak aktif.';
    header('Location: edit_jenis_pelanggaran.php?id=' . $id);
    exit;
}

$kode_db = $kode === '' ? null : $kode;
$stmt = mysqli_prepare(
    $koneksi,
    'UPDATE t_pelanggaran
     SET pelanggaran_kategori_id = ?, kode = ?, nama = ?, poin = ?,
         deskripsi = ?, status_aktif = ?, updated_at = CURRENT_TIMESTAMP
     WHERE id = ?'
);
if (!$stmt) {
    error_log('Gagal menyiapkan pembaruan jenis pelanggaran: ' . mysqli_error($koneksi));
    $_SESSION['pesan_error'] = 'Jenis pelanggaran gagal diperbarui.';
    header('Location: edit_jenis_pelanggaran.php?id=' . $id);
    exit;
}
mysqli_stmt_bind_param($stmt, 'issisii', $kategori_id, $kode_db, $nama, $poin, $deskripsi, $status_aktif, $id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['pesan_sukses'] = 'Jenis pelanggaran berhasil diperbarui.';
} else {
    error_log('Gagal memperbarui jenis pelanggaran: ' . mysqli_stmt_error($stmt));
    $_SESSION['pesan_error'] = 'Jenis pelanggaran gagal diperbarui.';
}
mysqli_stmt_close($stmt);
header('Location: kelola_jenis_pelanggaran.php');
exit;
