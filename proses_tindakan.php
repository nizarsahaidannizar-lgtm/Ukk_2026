<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';
include 'includes/akses_pelanggaran.php';

$konteks = konteks_akses_pelanggaran($koneksi);
$lingkup = lingkup_sql_pelanggaran($konteks, 'ps');
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$tindakan = trim($_POST['tindakan'] ?? '');
$status = $_POST['status'] ?? '';
$status_diizinkan = ['Ditindaklanjuti', 'Proses', 'Selesai'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id || $tindakan === '' || !in_array($status, $status_diizinkan, true)) {
    $_SESSION['pesan_error'] = 'Data tindak lanjut tidak valid.';
    header('Location: ' . (($_SESSION['role'] ?? '') === 'guru' ? 'guru/tindakan_guru.php' : 'dashboard.php'));
    exit;
}

$stmt = mysqli_prepare(
    $koneksi,
    "UPDATE t_pelanggaran_siswa ps
     SET ps.tindakan = ?, ps.status = ?, ps.updated_at = CURRENT_TIMESTAMP
     WHERE ps.id = ? AND $lingkup"
);
mysqli_stmt_bind_param($stmt, 'ssi', $tindakan, $status, $id);

if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) >= 0) {
    $_SESSION['pesan_sukses'] = 'Tindak lanjut berhasil disimpan.';
} else {
    $_SESSION['pesan_error'] = 'Tindak lanjut gagal disimpan.';
}

header('Location: ' . (($_SESSION['role'] ?? '') === 'guru' ? 'guru/tindakan_guru.php' : 'dashboard.php'));
exit;
?>
