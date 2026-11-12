
<?php
// guru/proses_tindakan.php

include '../includes/cek_session.php';
include '../config/koneksi.php';

// Hanya guru yang boleh memberikan tindakan
batasi_akses(['guru']);

// Halaman tujuan
$halaman = 'tindakan_guru.php';

// Pastikan menggunakan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $halaman");
    exit;
}

// Ambil data dari form
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$tindakan = trim($_POST['tindakan'] ?? '');
$status = $_POST['status'] ?? '';

$status_diizinkan = [
    'Ditindaklanjuti',
    'Proses',
    'Selesai'
];

// Validasi data
if (
    !$id ||
    $tindakan === '' ||
    !in_array($status, $status_diizinkan, true)
) {
    $_SESSION['pesan_error'] = 'Data tindak lanjut tidak valid.';
    header("Location: $halaman");
    exit;
}

// Periksa data pelanggaran
$stmt_cek = mysqli_prepare(
    $koneksi,
    "SELECT id FROM t_pelanggaran_siswa
     WHERE id = ?
     LIMIT 1"
);

if (!$stmt_cek) {
    $_SESSION['pesan_error'] = 'Gagal memeriksa data pelanggaran.';
    header("Location: $halaman");
    exit;
}

mysqli_stmt_bind_param($stmt_cek, 'i', $id);
mysqli_stmt_execute($stmt_cek);

$hasil_cek = mysqli_stmt_get_result($stmt_cek);

if (!$hasil_cek || mysqli_num_rows($hasil_cek) === 0) {
    mysqli_stmt_close($stmt_cek);

    $_SESSION['pesan_error'] = 'Data pelanggaran tidak ditemukan.';
    header("Location: $halaman");
    exit;
}

mysqli_stmt_close($stmt_cek);

// Update tindak lanjut
$stmt = mysqli_prepare(
    $koneksi,
    "UPDATE t_pelanggaran_siswa
     SET tindakan = ?,
         status = ?,
         updated_at = CURRENT_TIMESTAMP
     WHERE id = ?"
);

if (!$stmt) {
    $_SESSION['pesan_error'] = 'Gagal menyiapkan penyimpanan tindakan.';
    header("Location: $halaman");
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    'ssi',
    $tindakan,
    $status,
    $id
);

// Simpan ke database
if (mysqli_stmt_execute($stmt)) {
    $_SESSION['pesan_sukses'] = 'Tindak lanjut berhasil disimpan.';
} else {
    error_log(mysqli_stmt_error($stmt));
    $_SESSION['pesan_error'] = 'Tindak lanjut gagal disimpan.';
}

mysqli_stmt_close($stmt);

// Kembali ke halaman tindakan guru
header("Location: $halaman");
exit;
?>
