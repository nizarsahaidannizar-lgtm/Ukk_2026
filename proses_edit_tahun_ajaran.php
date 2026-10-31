
<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';
require_once 'includes/tahun_ajaran.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola_tahun_ajaran.php');
    exit;
}

$id = $_POST['id'] ?? '';
$nama = trim($_POST['nama'] ?? '');
$tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
$tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
$status_aktif = $_POST['status_aktif'] ?? '';

if (
    !ctype_digit((string) $id) ||
    (int) $id < 1 ||
    !preg_match('/^[0-9]{4}\/[0-9]{4}$/', $nama) ||
    $tanggal_mulai === '' ||
    $tanggal_selesai === '' ||
    !in_array($status_aktif, ['0', '1'], true)
) {
    $_SESSION['pesan_error'] = 'Data tahun ajaran tidak valid.';
    header('Location: kelola_tahun_ajaran.php');
    exit;
}

$mulai = DateTime::createFromFormat('!Y-m-d', $tanggal_mulai);
$selesai = DateTime::createFromFormat('!Y-m-d', $tanggal_selesai);

if (
    !$mulai ||
    !$selesai ||
    $mulai->format('Y-m-d') !== $tanggal_mulai ||
    $selesai->format('Y-m-d') !== $tanggal_selesai ||
    $tanggal_mulai > $tanggal_selesai
) {
    $_SESSION['pesan_error'] = 'Tanggal tahun ajaran tidak valid.';
    header('Location: kelola_tahun_ajaran.php');
    exit;
}

$id = (int) $id;
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
    "UPDATE t_tahun_ajaran
     SET nama = ?, tanggal_mulai = ?, tanggal_selesai = ?, status_aktif = ?, updated_at = CURRENT_TIMESTAMP
     WHERE id = ?"
);

if (!$stmt) {
    mysqli_rollback($koneksi);
    $_SESSION['pesan_error'] = 'Gagal menyiapkan perubahan tahun ajaran.';
    header('Location: kelola_tahun_ajaran.php');
    exit;
}

mysqli_stmt_bind_param($stmt, 'sssii', $nama, $tanggal_mulai, $tanggal_selesai, $status_aktif, $id);
$berhasil = mysqli_stmt_execute($stmt);
$baris_diubah = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

if (!$berhasil || $baris_diubah < 0 || !mysqli_commit($koneksi)) {
    mysqli_rollback($koneksi);
    $_SESSION['pesan_error'] = 'Gagal mengubah tahun ajaran.';
    header('Location: kelola_tahun_ajaran.php');
    exit;
}

$_SESSION['pesan_sukses'] = $status_aktif === 1
    ? 'Tahun ajaran berhasil diperbarui dan menjadi satu-satunya yang aktif.'
    : 'Tahun ajaran berhasil diperbarui.';
header('Location: kelola_tahun_ajaran.php');
exit;
