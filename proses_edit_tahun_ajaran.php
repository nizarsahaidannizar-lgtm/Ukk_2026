
<?php
// proses_edit_tahun_ajaran.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

batasi_akses(['admin']);

// Pastikan request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola_tahun_ajaran.php');
    exit;
}

// AMBIL DATA FORM
$id = $_POST['id'] ?? '';
$nama = trim($_POST['nama'] ?? '');
$tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
$tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
$status_aktif = $_POST['status_aktif'] ?? '';

// VALIDASI DATA
if (
    !ctype_digit((string)$id) ||
    (int)$id < 1 ||
    !preg_match('/^[0-9]{4}\/[0-9]{4}$/', $nama) ||
    $tanggal_mulai === '' ||
    $tanggal_selesai === '' ||
    !in_array((string)$status_aktif, ['0', '1'], true)
) {
    $_SESSION['pesan_error'] = 'Data tahun ajaran tidak valid.';
    header('Location: kelola_tahun_ajaran.php');
    exit;
}

// VALIDASI TANGGAL
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

// UBAH TIPE DATA
$id = (int)$id;
$status_aktif = (int)$status_aktif;

// CEK DATA TAHUN AJARAN
$cek = mysqli_prepare(
    $koneksi,
    "SELECT id FROM t_tahun_ajaran WHERE id = ?"
);

mysqli_stmt_bind_param($cek, "i", $id);
mysqli_stmt_execute($cek);

$result = mysqli_stmt_get_result($cek);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($cek);

if (!$data) {
    $_SESSION['pesan_error'] = 'Data tahun ajaran tidak ditemukan.';
    header('Location: kelola_tahun_ajaran.php');
    exit;
}

// UPDATE TAHUN AJARAN
$stmt = mysqli_prepare(
    $koneksi,
    "UPDATE t_tahun_ajaran
     SET nama = ?,
         tanggal_mulai = ?,
         tanggal_selesai = ?,
         status_aktif = ?,
         updated_at = CURRENT_TIMESTAMP
     WHERE id = ?"
);

if (!$stmt) {
    $_SESSION['pesan_error'] = 'Gagal menyiapkan perubahan tahun ajaran.';
    header('Location: kelola_tahun_ajaran.php');
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "sssii",
    $nama,
    $tanggal_mulai,
    $tanggal_selesai,
    $status_aktif,
    $id
);

$berhasil = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// NOTIFIKASI
if ($berhasil) {

    if ($status_aktif === 1) {
        $_SESSION['pesan_sukses'] =
            "Tahun ajaran <strong>" .
            htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') .
            "</strong> berhasil diperbarui dan diaktifkan!";
    } else {
        $_SESSION['pesan_sukses'] =
            "Tahun ajaran <strong>" .
            htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') .
            "</strong> berhasil diperbarui dan dinonaktifkan!";
    }

} else {

    $_SESSION['pesan_error'] = 'Gagal mengubah tahun ajaran.';
}

header('Location: kelola_tahun_ajaran.php');
exit;
?>
