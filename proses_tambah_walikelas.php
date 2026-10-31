<?php
// proses_tambah_walikelas.php
include 'includes/cek_session.php';
include 'config/koneksi.php';

$tahun_ajaran_id = (int) ($_POST['tahun_ajaran_id'] ?? 0);
$kelas_id = (int) ($_POST['kelas_id'] ?? 0);
$guru_id = (int) ($_POST['guru_id'] ?? 0);
$tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
$tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
$status_aktif = $_POST['status_aktif'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tambah_walikelas.php');
    exit;
}

if (
    !$tahun_ajaran_id || !$kelas_id || !$guru_id ||
    $tanggal_mulai === '' || $tanggal_selesai === '' ||
    !in_array((string)$status_aktif, ['0', '1'], true)
) {
    $_SESSION['pesan_error'] = 'Lengkapi semua data wali kelas dengan benar.';
    header('Location: tambah_walikelas.php');
    exit;
}

$mulai = DateTime::createFromFormat('!Y-m-d', $tanggal_mulai);
$selesai = DateTime::createFromFormat('!Y-m-d', $tanggal_selesai);
if (
    !$mulai || !$selesai ||
    $mulai->format('Y-m-d') !== $tanggal_mulai ||
    $selesai->format('Y-m-d') !== $tanggal_selesai ||
    $tanggal_selesai < $tanggal_mulai
) {
    $_SESSION['pesan_error'] = 'Tanggal selesai harus sama atau setelah tanggal mulai.';
    header('Location: tambah_walikelas.php');
    exit;
}

$tahun_stmt = mysqli_prepare(
    $koneksi,
    "SELECT id FROM t_tahun_ajaran WHERE id = ? AND status_aktif = 1"
);
if (!$tahun_stmt) {
    error_log('Gagal memeriksa tahun ajaran wali kelas: ' . mysqli_error($koneksi));
    $_SESSION['pesan_error'] = 'Tahun ajaran belum dapat diperiksa. Silakan coba lagi.';
    header('Location: kelola_walikelas.php');
    exit;
}
mysqli_stmt_bind_param($tahun_stmt, 'i', $tahun_ajaran_id);
mysqli_stmt_execute($tahun_stmt);
$tahun_aktif = mysqli_stmt_get_result($tahun_stmt)->num_rows > 0;
mysqli_stmt_close($tahun_stmt);

if (!$tahun_aktif) {
    $_SESSION['pesan_error'] = 'Pilih tahun ajaran yang masih aktif.';
    header('Location: tambah_walikelas.php');
    exit;
}

$sql = "INSERT INTO t_wali_kelas
        (tahun_ajaran_id, kelas_id, guru_id,
         tanggal_mulai, tanggal_selesai, status_aktif)
        VALUES (?, ?, ?, ?, ?, ?)";

if (!$stmt = mysqli_prepare($koneksi, $sql)) {
    error_log('Gagal menyiapkan penambahan wali kelas: ' . mysqli_error($koneksi));
    $_SESSION['pesan_error'] = 'Wali kelas gagal ditambahkan. Silakan coba lagi.';
    header('Location: kelola_walikelas.php');
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    'iiissi',
    $tahun_ajaran_id,
    $kelas_id,
    $guru_id,
    $tanggal_mulai,
    $tanggal_selesai,
    $status_aktif
);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['pesan_sukses'] = 'Data wali kelas berhasil ditambahkan.';
} else {
    error_log('Gagal menambahkan wali kelas: ' . mysqli_stmt_error($stmt));
    $_SESSION['pesan_error'] = 'Wali kelas gagal ditambahkan. Silakan coba lagi.';
}
mysqli_stmt_close($stmt);

header('Location: kelola_walikelas.php');
exit;
?>