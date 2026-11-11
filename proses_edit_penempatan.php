
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
    !$tanggal_mulai || !in_array($status_aktif, [0, 1], true)
) {
    $_SESSION['pesan'] = "Data belum lengkap!";
    $_SESSION['tipe'] = "danger";

    header("Location: penempatan_siswa.php");
    exit;
}

// Validasi tanggal
if ($tanggal_selesai !== '' && $tanggal_selesai < $tanggal_mulai) {
    $_SESSION['pesan'] = "Tanggal selesai tidak boleh sebelum tanggal mulai!";
    $_SESSION['tipe'] = "danger";

    header("Location: penempatan_siswa.php");
    exit;
}

if ($tanggal_selesai === '') {
    $tanggal_selesai = null;
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

if (mysqli_stmt_execute($stmt)) {

    $_SESSION['pesan'] = "Penempatan siswa berhasil diperbarui!";
    $_SESSION['tipe'] = "success";

} else {

    $_SESSION['pesan'] = "Gagal memperbarui penempatan siswa: " . mysqli_stmt_error($stmt);
    $_SESSION['tipe'] = "danger";

}

mysqli_stmt_close($stmt);

header("Location: penempatan_siswa.php");
exit;
?>
