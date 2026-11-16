
<?php
// proses_tambah_walikelas.php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Hanya admin yang bisa menambahkan wali kelas
batasi_akses(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: tambah_walikelas.php');
    exit;
}

// Ambil data dari form
$kelas_id = (int) ($_POST['kelas_id'] ?? 0);
$guru_id = (int) ($_POST['guru_id'] ?? 0);
$tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
$tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
$status_aktif = $_POST['status_aktif'] ?? '';

// Validasi input
if (
    !$kelas_id ||
    !$guru_id ||
    $tanggal_mulai === '' ||
    $tanggal_selesai === '' ||
    !in_array((string)$status_aktif, ['0', '1'], true)
) {
    $_SESSION['pesan_error'] = 'Lengkapi semua data wali kelas.';
    header('Location: tambah_walikelas.php');
    exit;
}

// Validasi tanggal
$mulai = DateTime::createFromFormat('!Y-m-d', $tanggal_mulai);
$selesai = DateTime::createFromFormat('!Y-m-d', $tanggal_selesai);

if (
    !$mulai || !$selesai ||
    $mulai->format('Y-m-d') !== $tanggal_mulai ||
    $selesai->format('Y-m-d') !== $tanggal_selesai ||
    $tanggal_selesai < $tanggal_mulai
) {
    $_SESSION['pesan_error'] =
        'Tanggal selesai harus sama atau setelah tanggal mulai.';
    header('Location: tambah_walikelas.php');
    exit;
}

// ==================================================
// AMBIL TAHUN AJARAN YANG SEDANG AKTIF
// ==================================================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id FROM t_tahun_ajaran
     WHERE status_aktif = 1
     ORDER BY tanggal_mulai DESC
     LIMIT 1"
);

if (!$stmt) {
    error_log(mysqli_error($koneksi));
    $_SESSION['pesan_error'] = 'Gagal memeriksa tahun ajaran aktif.';
    header('Location: tambah_walikelas.php');
    exit;
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data_tahun = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data_tahun) {
    $_SESSION['pesan_error'] =
        'Tidak ditemukan tahun ajaran yang aktif.';
    header('Location: tambah_walikelas.php');
    exit;
}

$tahun_ajaran_id = (int) $data_tahun['id'];

// ==================================================
// AMBIL USER ID BERDASARKAN GURU YANG DIPILIH
// ==================================================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT user_id FROM t_guru WHERE id = ?"
);

if (!$stmt) {
    error_log(mysqli_error($koneksi));
    $_SESSION['pesan_error'] = 'Gagal memeriksa data guru.';
    header('Location: tambah_walikelas.php');
    exit;
}

mysqli_stmt_bind_param($stmt, 'i', $guru_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data_guru = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data_guru || empty($data_guru['user_id'])) {
    $_SESSION['pesan_error'] =
        'Guru belum memiliki akun pengguna. Hubungkan akun guru terlebih dahulu.';
    header('Location: tambah_walikelas.php');
    exit;
}

$user_id = (int) $data_guru['user_id'];

// ==================================================
// SIMPAN DATA WALI KELAS
// ==================================================

$sql = "INSERT INTO t_wali_kelas
        (tahun_ajaran_id, kelas_id, guru_id,
         tanggal_mulai, tanggal_selesai, status_aktif, user_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    error_log(mysqli_error($koneksi));
    $_SESSION['pesan_error'] =
        'Gagal menyiapkan penyimpanan wali kelas.';
    header('Location: kelola_walikelas.php');
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    'iiissii',
    $tahun_ajaran_id,
    $kelas_id,
    $guru_id,
    $tanggal_mulai,
    $tanggal_selesai,
    $status_aktif,
    $user_id
);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['pesan_sukses'] =
        'Data wali kelas berhasil ditambahkan menggunakan tahun ajaran aktif.';
} else {
    error_log(mysqli_stmt_error($stmt));
    $_SESSION['pesan_error'] =
        'Wali kelas gagal ditambahkan.';
}

mysqli_stmt_close($stmt);

header('Location: kelola_walikelas.php');
exit;
?>
