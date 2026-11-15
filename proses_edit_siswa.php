<?php
// proses_edit_siswa.php
include 'includes/cek_session.php';
include 'config/koneksi.php';

batasi_akses(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kelola_siswa.php");
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nis = trim($_POST['nis'] ?? '');
$nisn = trim($_POST['nisn'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
$tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
$alamat = trim($_POST['alamat'] ?? '');
$status_aktif = $_POST['status_aktif'] ?? '';

$tanggal = DateTime::createFromFormat('!Y-m-d', $tanggal_lahir);
if (
    !$id || $nis === '' || $nisn === '' || $nama === '' ||
    !in_array($jenis_kelamin, ['L', 'P'], true) ||
    !$tanggal || $tanggal->format('Y-m-d') !== $tanggal_lahir ||
    !in_array((string)$status_aktif, ['0', '1'], true)
) {
    $_SESSION['pesan_error'] = 'Data siswa tidak valid. Periksa kembali formulir.';
    header("Location: edit_siswa.php?id=" . urlencode((string)$id));
    exit;
}

mysqli_begin_transaction($koneksi);
$stmt = mysqli_prepare($koneksi, "
    UPDATE t_siswa
    SET nis = ?, nisn = ?, nama = ?, jenis_kelamin = ?, tanggal_lahir = ?,
        alamat = ?, status_aktif = ?, updated_at = CURRENT_TIMESTAMP
    WHERE id = ?
");
if (!$stmt) {
    mysqli_rollback($koneksi);
    $_SESSION['pesan_error'] = 'Gagal menyiapkan perubahan data siswa.';
    header("Location: edit_siswa.php?id=" . urlencode((string)$id));
    exit;
}
$status_int = (int)$status_aktif;
mysqli_stmt_bind_param($stmt, 'ssssssii', $nis, $nisn, $nama, $jenis_kelamin, $tanggal_lahir, $alamat, $status_int, $id);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// Jika siswa dinonaktifkan, semua penempatan aktifnya ikut dinonaktifkan.
if ($ok && $status_int === 0) {
    $stmt = mysqli_prepare($koneksi, "
        UPDATE t_kelas_siswa
        SET status_aktif = 0, updated_at = CURRENT_TIMESTAMP
        WHERE siswa_id = ?
    ");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $ok = false;
    }
}

if ($ok) {
    mysqli_commit($koneksi);
    $_SESSION['pesan_sukses'] = 'Data siswa berhasil diperbarui dan status penempatannya disinkronkan.';
    header("Location: kelola_siswa.php");
    exit;
}

mysqli_rollback($koneksi);
$_SESSION['pesan_error'] = 'Gagal mengubah data siswa. Silakan periksa kembali.';
header("Location: edit_siswa.php?id=" . urlencode((string)$id));
exit;
?>
