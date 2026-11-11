
<?php
// proses_edit_walikelas.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

batasi_akses(['admin']);

// Pastikan menggunakan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kelola_walikelas.php");
    exit;
}

// Ambil data dari form
$id = (int) ($_POST['id'] ?? 0);
$tahun_ajaran_id = (int) ($_POST['tahun_ajaran_id'] ?? 0);
$kelas_id = (int) ($_POST['kelas_id'] ?? 0);
$guru_id = (int) ($_POST['guru_id'] ?? 0);
$status_aktif = (int) ($_POST['status_aktif'] ?? 0);
$tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
$tanggal_selesai = $_POST['tanggal_selesai'] ?? '';

// Validasi data
if (
    $id <= 0 ||
    $tahun_ajaran_id <= 0 ||
    $kelas_id <= 0 ||
    $guru_id <= 0 ||
    !in_array($status_aktif, [0, 1], true) ||
    empty($tanggal_mulai) ||
    empty($tanggal_selesai)
) {
    $_SESSION['pesan_error'] = "Data wali kelas tidak lengkap.";
    header("Location: kelola_walikelas.php");
    exit;
}

// Validasi tanggal
if ($tanggal_selesai < $tanggal_mulai) {
    $_SESSION['pesan_error'] = "Tanggal selesai tidak boleh lebih awal dari tanggal mulai.";
    header("Location: kelola_walikelas.php");
    exit;
}

// Pastikan data wali kelas tersedia
$cek = mysqli_prepare(
    $koneksi,
    "SELECT id FROM t_wali_kelas WHERE id = ?"
);

mysqli_stmt_bind_param($cek, "i", $id);
mysqli_stmt_execute($cek);

$data_ada = mysqli_stmt_get_result($cek)->num_rows > 0;

mysqli_stmt_close($cek);

if (!$data_ada) {
    $_SESSION['pesan_error'] = "Data wali kelas tidak ditemukan.";
    header("Location: kelola_walikelas.php");
    exit;
}

// Pastikan tahun ajaran tersedia dan aktif
$cek_tahun = mysqli_prepare(
    $koneksi,
    "SELECT id FROM t_tahun_ajaran
     WHERE id = ? AND status_aktif = 1"
);

mysqli_stmt_bind_param($cek_tahun, "i", $tahun_ajaran_id);
mysqli_stmt_execute($cek_tahun);

$tahun_valid = mysqli_stmt_get_result($cek_tahun)->num_rows > 0;

mysqli_stmt_close($cek_tahun);

if (!$tahun_valid) {
    $_SESSION['pesan_error'] = "Tahun ajaran yang dipilih tidak aktif.";
    header("Location: kelola_walikelas.php");
    exit;
}

// Proses update data
$sql = "UPDATE t_wali_kelas SET
            tahun_ajaran_id = ?,
            kelas_id = ?,
            guru_id = ?,
            status_aktif = ?,
            tanggal_mulai = ?,
            tanggal_selesai = ?
        WHERE id = ?";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    $_SESSION['pesan_error'] = "Gagal menyiapkan proses edit wali kelas.";
    header("Location: kelola_walikelas.php");
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "iiiissi",
    $tahun_ajaran_id,
    $kelas_id,
    $guru_id,
    $status_aktif,
    $tanggal_mulai,
    $tanggal_selesai,
    $id
);

// Jalankan update
if (mysqli_stmt_execute($stmt)) {

    $_SESSION['pesan_sukses'] =
        "Data wali kelas berhasil diperbarui.";

} else {

    $_SESSION['pesan_error'] =
        "Data wali kelas gagal diperbarui: " . mysqli_stmt_error($stmt);

}

mysqli_stmt_close($stmt);

// Kembali ke halaman kelola wali kelas
header("Location: kelola_walikelas.php");
exit;
