
<?php
// ==================================================
// guru/proses_tindakan.php
// ==================================================

include '../includes/cek_session.php';
include '../config/koneksi.php';
include_once '../includes/akses_pelanggaran.php';

// ==================================================
// CEK HAK AKSES
// ==================================================

batasi_akses(['guru', 'guru_wali_kelas']);

// ==================================================
// HALAMAN TUJUAN
// ==================================================

$halaman = 'tindakan_guru.php';

// ==================================================
// PASTIKAN MENGGUNAKAN POST
// ==================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $halaman");
    exit;
}

// ==================================================
// AMBIL KONTEKS AKSES
// ==================================================

$konteks_akses = konteks_akses_pelanggaran($koneksi);

// ==================================================
// TENTUKAN LINGKUP DATA
// GURU DAN WALI KELAS BISA MENINDAK SEMUA DATA
// ==================================================

$lingkup_akses = '1 = 1';

// ==================================================
// AMBIL DATA FORM
// ==================================================

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

$tindakan = trim($_POST['tindakan'] ?? '');
$status = trim($_POST['status'] ?? '');

$status_diizinkan = [
    'Ditindaklanjuti',
    'Proses',
    'Selesai'
];

// ==================================================
// VALIDASI DATA
// ==================================================

if (
    !$id ||
    !is_string($_POST['tindakan'] ?? null) ||
    !is_string($_POST['status'] ?? null) ||
    $tindakan === '' ||
    !in_array($status, $status_diizinkan, true)
) {
    $_SESSION['pesan_error'] = 'Data tindak lanjut tidak valid.';
    header("Location: $halaman");
    exit;
}

// ==================================================
// PERIKSA KEPEMILIKAN / LINGKUP DATA
// ==================================================

$sql_cek = "
    SELECT ps.id
    FROM t_pelanggaran_siswa ps
    WHERE ps.id = ?
    AND ($lingkup_akses)
    LIMIT 1
";

$stmt_cek = mysqli_prepare($koneksi, $sql_cek);

if (!$stmt_cek) {
    error_log(mysqli_error($koneksi));

    $_SESSION['pesan_error'] = 'Gagal memeriksa data pelanggaran.';
    header("Location: $halaman");
    exit;
}

mysqli_stmt_bind_param($stmt_cek, 'i', $id);
mysqli_stmt_execute($stmt_cek);

$hasil_cek = mysqli_stmt_get_result($stmt_cek);

if (!$hasil_cek || mysqli_num_rows($hasil_cek) === 0) {

    mysqli_stmt_close($stmt_cek);

    $_SESSION['pesan_error'] =
        'Data pelanggaran tidak ditemukan atau Anda tidak memiliki akses.';

    header("Location: $halaman");
    exit;
}

mysqli_stmt_close($stmt_cek);

// ==================================================
// UPDATE TINDAK LANJUT
// ==================================================

$sql_update = "
    UPDATE t_pelanggaran_siswa ps
    SET
        tindakan = ?,
        status = ?,
        updated_at = CURRENT_TIMESTAMP
    WHERE ps.id = ?
    AND ($lingkup_akses)
";

$stmt = mysqli_prepare($koneksi, $sql_update);

if (!$stmt) {
    error_log(mysqli_error($koneksi));

    $_SESSION['pesan_error'] =
        'Gagal menyiapkan penyimpanan tindakan.';

    header("Location: $halaman");
    exit;
}

// ==================================================
// SIMPAN DATA
// ==================================================

mysqli_stmt_bind_param(
    $stmt,
    'ssi',
    $tindakan,
    $status,
    $id
);

if (mysqli_stmt_execute($stmt)) {

    $_SESSION['pesan_sukses'] =
        'Tindak lanjut berhasil disimpan.';

} else {

    error_log(mysqli_stmt_error($stmt));

    $_SESSION['pesan_error'] =
        'Tindak lanjut gagal disimpan.';
}

mysqli_stmt_close($stmt);

// ==================================================
// KEMBALI KE HALAMAN TINDAKAN
// ==================================================

header("Location: $halaman");
exit;

?>
