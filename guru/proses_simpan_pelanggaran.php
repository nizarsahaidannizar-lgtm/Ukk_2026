<?php
// guru/proses_simpan_pelanggaran.php
include '../includes/cek_session.php';
include '../config/koneksi.php';
include '../includes/akses_pelanggaran.php';

$konteks_akses = konteks_akses_pelanggaran($koneksi);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: catat_pelanggaran.php');
    exit;
}

$siswa_id = filter_input(INPUT_POST, 'siswa_id', FILTER_VALIDATE_INT);
$tahun_id = filter_input(INPUT_POST, 'tahun_ajaran_id', FILTER_VALIDATE_INT);
$kelas_id = filter_input(INPUT_POST, 'kelas_id', FILTER_VALIDATE_INT);
$pelanggaran_id = filter_input(INPUT_POST, 'pelanggaran_id', FILTER_VALIDATE_INT);
$kategori_id = filter_input(INPUT_POST, 'pelanggaran_kategori_id', FILTER_VALIDATE_INT);
$guru_id = (int)$konteks_akses['guru_id'];
$poin = filter_input(INPUT_POST, 'poin', FILTER_VALIDATE_INT);

$tanggal = $_POST['tanggal'] ?? '';
$keterangan = trim($_POST['keterangan'] ?? '');
$tindakan = trim($_POST['tindakan'] ?? '');
$status = $_POST['status'] ?? '';

$status_diizinkan = ['Ditindaklanjuti', 'Proses', 'Selesai'];

if (
    !$siswa_id || !$tahun_id || !$kelas_id ||
    !$pelanggaran_id || !$kategori_id || !$guru_id ||
    $poin === false || $poin === null || $poin < 0 ||
    !in_array($status, $status_diizinkan, true)
) {
    $_SESSION['pesan_error'] = 'Data pelanggaran tidak valid.';
    header('Location: catat_pelanggaran.php');
    exit;
}

if (
    $konteks_akses['is_wali'] &&
    !in_array((int)$kelas_id, $konteks_akses['kelas_ids'], true)
) {
    $_SESSION['pesan_error'] = 'Kelas yang dipilih bukan kelas wali yang menjadi tanggung jawab Anda.';
    header('Location: catat_pelanggaran.php');
    exit;
}

$format_tanggal = DateTime::createFromFormat('!Y-m-d', $tanggal);
if (!$format_tanggal || $format_tanggal->format('Y-m-d') !== $tanggal) {
    $_SESSION['pesan_error'] = 'Tanggal pelanggaran tidak valid.';
    header('Location: catat_pelanggaran.php');
    exit;
}

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT nama FROM t_siswa WHERE id = ? AND status_aktif = 1"
);
mysqli_stmt_bind_param($stmt, 'i', $siswa_id);
mysqli_stmt_execute($stmt);
$data_siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT nama FROM t_tahun_ajaran WHERE id = ? AND status_aktif = 1"
);
mysqli_stmt_bind_param($stmt, 'i', $tahun_id);
mysqli_stmt_execute($stmt);
$data_tahun = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT nama FROM t_kelas WHERE id = ? AND status_aktif = 1"
);
mysqli_stmt_bind_param($stmt, 'i', $kelas_id);
mysqli_stmt_execute($stmt);
$data_kelas = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT nama, poin, pelanggaran_kategori_id FROM t_pelanggaran WHERE id = ? AND status_aktif = 1"
);
mysqli_stmt_bind_param($stmt, 'i', $pelanggaran_id);
mysqli_stmt_execute($stmt);
$data_pelanggaran = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT nama FROM t_guru WHERE id = ? AND status_aktif = 1"
);
mysqli_stmt_bind_param($stmt, 'i', $guru_id);
mysqli_stmt_execute($stmt);
$data_guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (
    !$data_siswa || !$data_tahun || !$data_kelas ||
    !$data_pelanggaran || !$data_guru ||
    (int)$data_pelanggaran['pelanggaran_kategori_id'] !== $kategori_id ||
    (int)$data_pelanggaran['poin'] !== $poin
) {
    $_SESSION['pesan_error'] = 'Data pilihan tidak ditemukan atau sudah berubah. Silakan pilih ulang.';
    header('Location: catat_pelanggaran.php');
    exit;
}

$nama_siswa = $data_siswa['nama'];
$nama_kelas = $data_kelas['nama'];
$nama_pelanggaran = $data_pelanggaran['nama'];
$nama_guru = $data_guru['nama'];

$sql = "INSERT INTO t_pelanggaran_siswa (
            tahun_ajaran_id,
            siswa_id,
            nama_siswa,
            kelas_id,
            nama_kelas,
            pelanggaran_id,
            nama_pelanggaran,
            pelanggaran_kategori_id,
            guru_id,
            nama_guru,
            tanggal,
            keterangan,
            poin,
            tindakan,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param(
    $stmt,
    'iisisisiisssiss',
    $tahun_id,
    $siswa_id,
    $nama_siswa,
    $kelas_id,
    $nama_kelas,
    $pelanggaran_id,
    $nama_pelanggaran,
    $kategori_id,
    $guru_id,
    $nama_guru,
    $tanggal,
    $keterangan,
    $poin,
    $tindakan,
    $status
);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['pesan_sukses'] = 'Catatan pelanggaran berhasil disimpan.';
} else {
    $_SESSION['pesan_error'] = 'Catatan pelanggaran gagal disimpan.';
}

mysqli_stmt_close($stmt);
header('Location: catat_pelanggaran.php');
exit;
