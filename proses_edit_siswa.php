<?php
// proses_edit_siswa.php
include 'includes/cek_session.php';
include 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kelola_siswa.php");
    exit;
}

$id = mysqli_real_escape_string($koneksi, $_POST['id'] ?? '');
$nis = mysqli_real_escape_string($koneksi, $_POST['nis'] ?? '');
$nisn = mysqli_real_escape_string($koneksi, $_POST['nisn'] ?? '');
$nama = mysqli_real_escape_string($koneksi, $_POST['nama'] ?? '');
$jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin'] ?? '');
$tanggal_lahir = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir'] ?? '');
$alamat = mysqli_real_escape_string($koneksi, $_POST['alamat'] ?? '');
$status_aktif = mysqli_real_escape_string($koneksi, $_POST['status_aktif'] ?? '');

$sql = "UPDATE t_siswa SET
        nis = '$nis',
        nisn = '$nisn',
        nama = '$nama',
        jenis_kelamin = '$jenis_kelamin',
        tanggal_lahir = '$tanggal_lahir',
        alamat = '$alamat',
        status_aktif = '$status_aktif',
        updated_at = CURRENT_TIMESTAMP
        WHERE id = '$id'";

if (mysqli_query($koneksi, $sql)) {
    // Set pesan sukses sebelum redirect ke halaman kelola siswa
    $_SESSION['pesan_sukses'] = 'Data siswa berhasil diperbarui.';
    header("Location: kelola_siswa.php");
    exit;
} else {
    // Set pesan error jika gagal
    $_SESSION['pesan_error'] = 'Gagal mengubah data siswa: ' . mysqli_error($koneksi);
    header("Location: edit_siswa.php?id=" . urlencode($id));
    exit;
}
?>