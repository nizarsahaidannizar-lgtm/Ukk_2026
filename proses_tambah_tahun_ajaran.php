<?php
// proses_tambah_tahun_ajaran.php
session_start();

include 'includes/cek_session.php';
include 'config/koneksi.php';
require_once 'includes/tahun_ajaran.php';

// Pastikan data dikirim menggunakan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola_tahun_ajaran.php');
    exit;
}

// Ambil data dari form
$nama = trim($_POST['nama'] ?? '');
$tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
$tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
$status_aktif = $_POST['status_aktif'] ?? '';

// Validasi data (Memastikan format nama sesuai pola tahun ajaran YYYY/YYYY)
if (
    $nama === '' ||
    !preg_match('/^[0-9]{4}\/[0-9]{4}$/', $nama) ||
    $tanggal_mulai === '' ||
    $tanggal_selesai === '' ||
    !in_array($status_aktif, ['0', '1'], true)
) {
    $_SESSION['pesan_error'] = 'Data tahun ajaran tidak valid. Pastikan format tahun ajaran benar.';
    header('Location: kelola_tahun_ajaran.php');
    exit;
}

// Validasi tanggal
$mulai = DateTime::createFromFormat('!Y-m-d', $tanggal_mulai);
$selesai = DateTime::createFromFormat('!Y-m-d', $tanggal_selesai);

if (
    !$mulai ||
    !$selesai ||
    $mulai->format('Y-m-d') !== $tanggal_mulai ||
    $selesai->format('Y-m-d') !== $tanggal_selesai
) {
    $_SESSION['pesan_error'] = 'Format tanggal tidak valid.';
    header('Location: kelola_tahun_ajaran.php');
    exit;
}

if ($tanggal_mulai > $tanggal_selesai) {
    $_SESSION['pesan_error'] = 'Tanggal mulai tidak boleh melebihi tanggal selesai.';
    header('Location: kelola_tahun_ajaran.php');
    exit;
}

$status_aktif = (int) $status_aktif;

mysqli_begin_transaction($koneksi);

if (!normalisasi_tahun_ajaran_aktif($koneksi)) {
    mysqli_rollback($koneksi);
    $_SESSION['pesan_error'] = 'Gagal memeriksa status tahun ajaran aktif.';
    header('Location: kelola_tahun_ajaran.php');
    exit;
}

if ($status_aktif === 1 && !mysqli_query(
    $koneksi,
    "UPDATE t_tahun_ajaran
     SET status_aktif = 0, updated_at = CURRENT_TIMESTAMP
     WHERE status_aktif = 1"
)) {
    mysqli_rollback($koneksi);
    $_SESSION['pesan_error'] = 'Gagal menonaktifkan tahun ajaran sebelumnya.';
    header('Location: kelola_tahun_ajaran.php');
    exit;
}

$stmt = mysqli_prepare(
    $koneksi,
    "INSERT INTO t_tahun_ajaran
     (nama, tanggal_mulai, tanggal_selesai, status_aktif)
     VALUES (?, ?, ?, ?)"
);

if (!$stmt) {
    mysqli_rollback($koneksi);
    $_SESSION['pesan_error'] = 'Gagal menyiapkan data tahun ajaran.';
    header('Location: kelola_tahun_ajaran.php');
    exit;
}

mysqli_stmt_bind_param($stmt, 'sssi', $nama, $tanggal_mulai, $tanggal_selesai, $status_aktif);
$berhasil = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if (!$berhasil || !mysqli_commit($koneksi)) {
    mysqli_rollback($koneksi);
    $_SESSION['pesan_error'] = 'Gagal menambahkan tahun ajaran.';
    header('Location: kelola_tahun_ajaran.php');
    exit;
}

$_SESSION['pesan_sukses'] = $status_aktif === 1
    ? 'Tahun ajaran berhasil ditambahkan dan menjadi satu-satunya yang aktif.'
    : 'Tahun ajaran berhasil ditambahkan.';
header('Location: kelola_tahun_ajaran.php');
exit;
?>