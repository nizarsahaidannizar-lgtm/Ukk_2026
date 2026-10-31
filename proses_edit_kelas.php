<?php
// proses_edit_kelas.php
include 'includes/cek_session.php';
include 'config/koneksi.php';

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nama = trim($_POST['nama'] ?? '');
$tingkat = trim($_POST['tingkat'] ?? '');
$jurusan = trim($_POST['jurusan'] ?? '');
$status_aktif = $_POST['status_aktif'] ?? '';

if (
    !$id ||
    $nama === '' ||
    $tingkat === '' ||
    $jurusan === '' ||
    !in_array((string)$status_aktif, ['0', '1'], true)
) {
    $_SESSION['pesan_error'] = 'Data kelas tidak valid. Periksa kembali isian Anda.';
    header('Location: kelola_kelas.php');
    exit;
}

$stmt = mysqli_prepare(
    $koneksi,
    "UPDATE t_kelas
     SET nama = ?, tingkat = ?, jurusan = ?, status_aktif = ?, updated_at = CURRENT_TIMESTAMP
     WHERE id = ?"
);

if ($stmt) {
    $status_aktif = (int)$status_aktif;
    mysqli_stmt_bind_param($stmt, 'sssii', $nama, $tingkat, $jurusan, $status_aktif, $id);
    $berhasil = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
} else {
    $berhasil = false;
    error_log('Gagal menyiapkan perubahan data kelas: ' . mysqli_error($koneksi));
}

if ($berhasil) {
    $_SESSION['pesan_sukses'] = 'Data kelas berhasil diperbarui.';
} else {
    $_SESSION['pesan_error'] = 'Data kelas gagal diperbarui. Silakan coba lagi.';
}

header('Location: kelola_kelas.php');
exit;
?>