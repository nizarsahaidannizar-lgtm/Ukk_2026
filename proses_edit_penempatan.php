
<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

batasi_akses(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: penempatan_siswa.php");
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$siswa_id = filter_input(INPUT_POST, 'siswa_id', FILTER_VALIDATE_INT);
$kelas_id = filter_input(INPUT_POST, 'kelas_id', FILTER_VALIDATE_INT);
$tahun_ajaran_id = filter_input(INPUT_POST, 'tahun_ajaran_id', FILTER_VALIDATE_INT);

$tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
$tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
$status_aktif = filter_input(INPUT_POST, 'status_aktif', FILTER_VALIDATE_INT);

// Validasi data
if (
    !$id || !$siswa_id || !$kelas_id || !$tahun_ajaran_id ||
    !$tanggal_mulai || !$tanggal_selesai || !in_array($status_aktif, [0, 1], true)
) {
    $_SESSION['pesan'] = "Data belum lengkap!";
    $_SESSION['tipe'] = "danger";

    header("Location: penempatan_siswa.php");
    exit;
}

// Validasi tanggal
$tgl_mulai_obj = DateTime::createFromFormat('!Y-m-d', $tanggal_mulai);
$tgl_selesai_obj = DateTime::createFromFormat('!Y-m-d', $tanggal_selesai);
if (
    !$tgl_mulai_obj || !$tgl_selesai_obj ||
    $tgl_mulai_obj->format('Y-m-d') !== $tanggal_mulai ||
    $tgl_selesai_obj->format('Y-m-d') !== $tanggal_selesai ||
    $tanggal_selesai < $tanggal_mulai
) {
    $_SESSION['pesan'] = "Tanggal selesai tidak boleh sebelum tanggal mulai!";
    $_SESSION['tipe'] = "danger";

    header("Location: penempatan_siswa.php");
    exit;
}


$stmt = mysqli_prepare($koneksi, "SELECT status_aktif FROM t_siswa WHERE id = ? LIMIT 1");
if (!$stmt) {
    $_SESSION['pesan'] = "Gagal memeriksa status siswa.";
    $_SESSION['tipe'] = "danger";
    header("Location: penempatan_siswa.php"); exit;
}
mysqli_stmt_bind_param($stmt, "i", $siswa_id);
mysqli_stmt_execute($stmt);
$res_siswa = mysqli_stmt_get_result($stmt);
$row_siswa = mysqli_fetch_assoc($res_siswa);
mysqli_stmt_close($stmt);
if (!$row_siswa) {
    $_SESSION['pesan'] = "Siswa tidak ditemukan.";
    $_SESSION['tipe'] = "danger";
    header("Location: penempatan_siswa.php"); exit;
}
if ((int)$row_siswa['status_aktif'] !== 1) {
    $status_aktif = 0;
}

$stmt = mysqli_prepare($koneksi, "SELECT id FROM t_tahun_ajaran WHERE id = ? AND status_aktif = 1 LIMIT 1");
if (!$stmt) {
    $_SESSION['pesan'] = "Gagal memeriksa tahun ajaran aktif.";
    $_SESSION['tipe'] = "danger";
    header("Location: penempatan_siswa.php"); exit;
}
mysqli_stmt_bind_param($stmt, "i", $tahun_ajaran_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
$tahun_valid = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);
if (!$tahun_valid) {
    $_SESSION['pesan'] = "Penempatan hanya dapat menggunakan tahun ajaran aktif.";
    $_SESSION['tipe'] = "danger";
    header("Location: penempatan_siswa.php"); exit;
}

$stmt = mysqli_prepare($koneksi, "SELECT id FROM t_kelas WHERE id = ? AND status_aktif = 1 LIMIT 1");
if (!$stmt) {
    $_SESSION['pesan'] = "Gagal memeriksa kelas.";
    $_SESSION['tipe'] = "danger";
    header("Location: penempatan_siswa.php"); exit;
}
mysqli_stmt_bind_param($stmt, "i", $kelas_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
$kelas_valid = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);
if (!$kelas_valid) {
    $_SESSION['pesan'] = "Kelas tidak ditemukan atau tidak aktif.";
    $_SESSION['tipe'] = "danger";
    header("Location: penempatan_siswa.php"); exit;
}

// Query update
$stmt = mysqli_prepare($koneksi, "
    UPDATE t_kelas_siswa SET
        siswa_id = ?,
        kelas_id = ?,
        tahun_ajaran_id = ?,
        tanggal_mulai = ?,
        tanggal_selesai = ?,
        status_aktif = ?
    WHERE id = ?
");

if (!$stmt) {
    $_SESSION['pesan'] = "Gagal menyiapkan query: " . mysqli_error($koneksi);
    $_SESSION['tipe'] = "danger";

    header("Location: penempatan_siswa.php");
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "iiissii",
    $siswa_id,
    $kelas_id,
    $tahun_ajaran_id,
    $tanggal_mulai,
    $tanggal_selesai,
    $status_aktif,
    $id
);

// Eksekusi update
if (mysqli_stmt_execute($stmt)) {

    $_SESSION['pesan'] = "Data penempatan siswa berhasil diperbarui!";
    $_SESSION['tipe'] = "success";

} else {

    $_SESSION['pesan'] = "Gagal memperbarui penempatan siswa: " . mysqli_stmt_error($stmt);
    $_SESSION['tipe'] = "danger";

}

mysqli_stmt_close($stmt);

// Kembali ke halaman penempatan siswa
header("Location: penempatan_siswa.php");
exit;
?>
