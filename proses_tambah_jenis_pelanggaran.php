<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: kelola_jenis_pelanggaran.php');
    exit;
}

$kode = trim($_POST['kode'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$kategori_id = filter_input(INPUT_POST, 'pelanggaran_kategori_id', FILTER_VALIDATE_INT);
$poin = filter_input(INPUT_POST, 'poin', FILTER_VALIDATE_INT);
$deskripsi = trim($_POST['deskripsi'] ?? '');
$status_aktif = filter_input(INPUT_POST, 'status_aktif', FILTER_VALIDATE_INT);

if (
    $nama === '' || $kategori_id === false || !$kategori_id ||
    $poin === false || $poin === null || $poin < 0 ||
    !in_array($status_aktif, [0, 1], true)
) {
    $_SESSION['pesan_error'] = 'Data jenis pelanggaran tidak valid.';
    header('Location: tambah_jenis_pelanggaran.php');
    exit;
}

$stmt = mysqli_prepare($koneksi, 'SELECT id FROM t_pelanggaran_kategori WHERE id = ? AND status_aktif = 1');
if (!$stmt) {
    error_log('Gagal memeriksa kategori jenis pelanggaran: ' . mysqli_error($koneksi));
    $_SESSION['pesan_error'] = 'Jenis pelanggaran gagal disimpan.';
    header('Location: tambah_jenis_pelanggaran.php');
    exit;
}
mysqli_stmt_bind_param($stmt, 'i', $kategori_id);
mysqli_stmt_execute($stmt);
$kategori_valid = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$kategori_valid) {
    $_SESSION['pesan_error'] = 'Kategori tidak ditemukan atau tidak aktif.';
    header('Location: tambah_jenis_pelanggaran.php');
    exit;
}

$kode_db = $kode === '' ? null : $kode;
$stmt = mysqli_prepare(
    $koneksi,
    'INSERT INTO t_pelanggaran (pelanggaran_kategori_id, kode, nama, poin, deskripsi, status_aktif)
     VALUES (?, ?, ?, ?, ?, ?)'
);
if (!$stmt) {
    error_log('Gagal menyiapkan penyimpanan jenis pelanggaran: ' . mysqli_error($koneksi));
    $_SESSION['pesan_error'] = 'Jenis pelanggaran gagal disimpan.';
    header('Location: tambah_jenis_pelanggaran.php');
    exit;
}
mysqli_stmt_bind_param($stmt, 'issisi', $kategori_id, $kode_db, $nama, $poin, $deskripsi, $status_aktif);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['pesan_sukses'] = 'Jenis pelanggaran berhasil ditambahkan.';
} else {
    error_log('Gagal menyimpan jenis pelanggaran: ' . mysqli_stmt_error($stmt));
    $_SESSION['pesan_error'] = 'Jenis pelanggaran gagal disimpan.';
}
mysqli_stmt_close($stmt);
header('Location: kelola_jenis_pelanggaran.php');
exit;
