
<?php
// proses_simpan_penempatan.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

batasi_akses(['admin']);

// KEMBALI JIKA BUKAN POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: penempatan_siswa.php");
    exit;
}

// AMBIL DATA FORM
$siswa_id = filter_input(INPUT_POST, 'siswa_id', FILTER_VALIDATE_INT);
$tahun_ajaran_id = filter_input(INPUT_POST, 'tahun_ajaran_id', FILTER_VALIDATE_INT);
$kelas_id = filter_input(INPUT_POST, 'kelas_id', FILTER_VALIDATE_INT);

$tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
$tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');
$status_aktif = $_POST['status_aktif'] ?? '';

// FUNGSI REDIRECT ERROR
function kembali_error($pesan) {
    $_SESSION['pesan_error'] = $pesan;
    header("Location: penempatan_siswa.php");
    exit;
}

// VALIDASI INPUT
if (
    !$siswa_id ||
    !$tahun_ajaran_id ||
    !$kelas_id ||
    $tanggal_mulai === '' ||
    $tanggal_selesai === '' ||
    !in_array((string)$status_aktif, ['0', '1'], true)
) {
    kembali_error("Semua data penempatan wajib diisi.");
}

// VALIDASI TANGGAL
$mulai = DateTime::createFromFormat('!Y-m-d', $tanggal_mulai);
$selesai = DateTime::createFromFormat('!Y-m-d', $tanggal_selesai);

if (
    !$mulai ||
    !$selesai ||
    $mulai->format('Y-m-d') !== $tanggal_mulai ||
    $selesai->format('Y-m-d') !== $tanggal_selesai ||
    $tanggal_selesai < $tanggal_mulai
) {
    kembali_error("Tanggal penempatan tidak valid.");
}

// VALIDASI SISWA
$sql = "SELECT id FROM t_siswa
        WHERE id = ? AND status_aktif = 1";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    kembali_error("Gagal memeriksa data siswa.");
}

mysqli_stmt_bind_param($stmt, "i", $siswa_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

$siswa_valid = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);

if (!$siswa_valid) {
    kembali_error("Siswa tidak ditemukan atau tidak aktif.");
}

// VALIDASI TAHUN AJARAN
$sql = "SELECT id FROM t_tahun_ajaran WHERE id = ?";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    kembali_error("Gagal memeriksa tahun ajaran.");
}

mysqli_stmt_bind_param($stmt, "i", $tahun_ajaran_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

$tahun_valid = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);

if (!$tahun_valid) {
    kembali_error("Tahun ajaran tidak ditemukan.");
}

// VALIDASI KELAS
$sql = "SELECT id FROM t_kelas
        WHERE id = ? AND status_aktif = 1";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    kembali_error("Gagal memeriksa data kelas.");
}

mysqli_stmt_bind_param($stmt, "i", $kelas_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

$kelas_valid = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);

if (!$kelas_valid) {
    kembali_error("Kelas tidak ditemukan atau tidak aktif.");
}

// CEK DUPLIKASI PENEMPATAN SISWA
$sql = "SELECT id FROM t_kelas_siswa
        WHERE siswa_id = ?
        AND tahun_ajaran_id = ?
        AND status_aktif = 1";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    kembali_error("Gagal memeriksa penempatan siswa.");
}

mysqli_stmt_bind_param($stmt, "ii", $siswa_id, $tahun_ajaran_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

$sudah_ditempatkan = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);

if ($sudah_ditempatkan && $status_aktif === '1') {
    kembali_error("Siswa sudah memiliki penempatan aktif pada tahun ajaran tersebut.");
}

// SIMPAN PENEMPATAN
$sql = "INSERT INTO t_kelas_siswa
        (
            siswa_id,
            tahun_ajaran_id,
            kelas_id,
            tanggal_mulai,
            tanggal_selesai,
            status_aktif
        )
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    kembali_error("Gagal menyiapkan penyimpanan penempatan.");
}

$status_aktif_int = (int)$status_aktif;

mysqli_stmt_bind_param(
    $stmt,
    "iiissi",
    $siswa_id,
    $tahun_ajaran_id,
    $kelas_id,
    $tanggal_mulai,
    $tanggal_selesai,
    $status_aktif_int
);

// EKSEKUSI PENYIMPANAN
if (mysqli_stmt_execute($stmt)) {

    $_SESSION['pesan_sukses'] = "Penempatan siswa berhasil disimpan.";

} else {

    error_log("Gagal menyimpan penempatan: " . mysqli_stmt_error($stmt));

    $_SESSION['pesan_error'] = "Gagal menyimpan penempatan siswa.";
}

mysqli_stmt_close($stmt);

header("Location: penempatan_siswa.php");
exit;
?>
