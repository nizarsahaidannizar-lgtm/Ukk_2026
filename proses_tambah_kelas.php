<?php
// proses_tambah_kelas.php
include 'includes/cek_session.php';
include 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tambah_kelas.php');
    exit;
}

$nama = trim($_POST['nama'] ?? '');
$tingkat = $_POST['tingkat'] ?? '';
$jurusan = trim($_POST['jurusan'] ?? '');
$status_aktif = $_POST['status_aktif'] ?? '';

if (
    $nama === '' || strlen($nama) > 100 ||
    !in_array($tingkat, ['X', 'XI', 'XII'], true) ||
    $jurusan === '' || strlen($jurusan) > 100 ||
    !in_array((string)$status_aktif, ['0', '1'], true)
) {
    $_SESSION['pesan_error'] = 'Data kelas tidak valid. Periksa kembali isian Anda.';
    header('Location: tambah_kelas.php');
    exit;
}

$stmt = mysqli_prepare(
    $koneksi,
    "INSERT INTO t_kelas (nama, tingkat, jurusan, status_aktif)
     VALUES (?, ?, ?, ?)"
);

if ($stmt) {
    $status_aktif = (int)$status_aktif;
    mysqli_stmt_bind_param($stmt, 'sssi', $nama, $tingkat, $jurusan, $status_aktif);
    $berhasil = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
} else {
    $berhasil = false;
    error_log('Gagal menyiapkan penambahan data kelas: ' . mysqli_error($koneksi));
}

if ($berhasil) {
    $_SESSION['pesan_sukses'] = 'Data kelas berhasil ditambahkan.';
} else {
    $_SESSION['pesan_error'] = 'Data kelas gagal ditambahkan. Silakan coba lagi.';
}

header('Location: kelola_kelas.php');
exit;
?>