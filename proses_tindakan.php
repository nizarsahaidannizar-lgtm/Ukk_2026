
<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Hanya guru yang sudah login yang boleh memberikan tindakan.
batasi_akses(['guru']);

// Pastikan hanya menerima data POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: guru/tindakan.php');
    exit;
}

// Ambil data dari form.
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$tindakan = trim($_POST['tindakan'] ?? '');
$status = $_POST['status'] ?? '';

$status_diizinkan = [
    'Ditindaklanjuti',
    'Proses',
    'Selesai'
];

// Validasi data.
if (
    !$id ||
    $tindakan === '' ||
    !in_array($status, $status_diizinkan, true)
) {
    $_SESSION['pesan_error'] = 'Data tindak lanjut tidak valid.';
    header('Location: guru/tindakan.php');
    exit;
}

// Pastikan data pelanggaran memang tersedia.
$stmt_cek = mysqli_prepare(
    $koneksi,
    "SELECT id FROM t_pelanggaran_siswa WHERE id = ? LIMIT 1"
);

mysqli_stmt_bind_param($stmt_cek, 'i', $id);
mysqli_stmt_execute($stmt_cek);

$hasil_cek = mysqli_stmt_get_result($stmt_cek);

if (!$hasil_cek || mysqli_num_rows($hasil_cek) === 0) {
    $_SESSION['pesan_error'] = 'Data pelanggaran tidak ditemukan.';
    header('Location: guru/tindakan.php');
    exit;
}

// UPDATE TANPA PEMBATASAN GURU PENCATAT.
// Semua guru dapat memberikan tindak lanjut.
$stmt = mysqli_prepare(
    $koneksi,
    "UPDATE t_pelanggaran_siswa
     SET tindakan = ?,
         status = ?,
         updated_at = CURRENT_TIMESTAMP
     WHERE id = ?"
);

if (!$stmt) {
    error_log('Prepare tindakan gagal: ' . mysqli_error($koneksi));
    $_SESSION['pesan_error'] = 'Terjadi kesalahan saat menyiapkan penyimpanan.';
    header('Location: guru/tindakan.php');
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    'ssi',
    $tindakan,
    $status,
    $id
);

// Simpan tindakan.
if (mysqli_stmt_execute($stmt)) {

    $_SESSION['pesan_sukses'] =
        'Tindak lanjut berhasil disimpan. Semua guru dapat memberikan tindakan.';

} else {

    error_log('Update tindakan gagal: ' . mysqli_stmt_error($stmt));

    $_SESSION['pesan_error'] =
        'Tindak lanjut gagal disimpan. Silakan coba kembali.';
}

mysqli_stmt_close($stmt);
mysqli_stmt_close($stmt_cek);

// Kembali ke halaman tindakan.
header('Location: guru/tindakan.php');
exit;
?>
