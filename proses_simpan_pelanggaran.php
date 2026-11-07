
<?php
// proses_simpan_pelanggaran.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: catat_pelanggaran.php');
    exit;
}

// ==================================================
// AMBIL DATA FORM
// ==================================================

$role = strtolower(trim($_SESSION['role'] ?? ''));

$siswa_id = (int)($_POST['siswa_id'] ?? 0);
$tahun_id = (int)($_POST['tahun_ajaran_id'] ?? 0);
$kelas_id = (int)($_POST['kelas_id'] ?? 0);
$pelanggaran_id = (int)($_POST['pelanggaran_id'] ?? 0);

$tanggal = trim($_POST['tanggal'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');
$tindakan = trim($_POST['tindakan'] ?? '');
$status = trim($_POST['status'] ?? '');

$status_diizinkan = [
    'Ditindaklanjuti',
    'Proses',
    'Selesai'
];

// ==================================================
// AMBIL ID GURU OTOMATIS DARI AKUN LOGIN
// ==================================================

$guru_id = 0;

if ($role === 'guru') {

    $user_id = (int)($_SESSION['id_user'] ?? 0);

    if ($user_id <= 0) {
        $_SESSION['pesan_error'] = 'Session guru tidak ditemukan.';
        header('Location: catat_pelanggaran.php');
        exit;
    }

    $stmt_guru = mysqli_prepare(
        $koneksi,
        "SELECT id
         FROM t_guru
         WHERE user_id = ?
         AND status_aktif = 1
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt_guru, 'i', $user_id);
    mysqli_stmt_execute($stmt_guru);

    $data_guru = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt_guru)
    );

    mysqli_stmt_close($stmt_guru);

    if (!$data_guru) {
        $_SESSION['pesan_error'] = 'Data guru tidak ditemukan.';
        header('Location: catat_pelanggaran.php');
        exit;
    }

    // ID guru tidak diambil dari form
    $guru_id = (int)$data_guru['id'];

} elseif ($role === 'admin') {

    // Admin menggunakan guru yang dipilih dari form
    $guru_id = (int)($_POST['guru_id'] ?? 0);

} else {

    $_SESSION['pesan_error'] = 'Akses tidak diizinkan.';
    header('Location: login.php');
    exit;
}

// ==================================================
// VALIDASI DATA
// ==================================================

if (
    $siswa_id <= 0 ||
    $tahun_id <= 0 ||
    $kelas_id <= 0 ||
    $pelanggaran_id <= 0 ||
    $guru_id <= 0 ||
    !in_array($status, $status_diizinkan, true)
) {
    $_SESSION['pesan_error'] = 'Data pelanggaran tidak lengkap atau tidak valid.';
    header('Location: catat_pelanggaran.php');
    exit;
}

// Validasi tanggal
$format_tanggal = DateTime::createFromFormat('!Y-m-d', $tanggal);

if (
    !$format_tanggal ||
    $format_tanggal->format('Y-m-d') !== $tanggal
) {
    $_SESSION['pesan_error'] = 'Tanggal pelanggaran tidak valid.';
    header('Location: catat_pelanggaran.php');
    exit;
}

// ==================================================
// AMBIL DATA SISWA
// ==================================================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT nama
     FROM t_siswa
     WHERE id = ?
     AND status_aktif = 1"
);

mysqli_stmt_bind_param($stmt, 'i', $siswa_id);
mysqli_stmt_execute($stmt);

$data_siswa = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);

mysqli_stmt_close($stmt);

// ==================================================
// AMBIL DATA TAHUN AJARAN
// Status aktif tidak dibatasi agar tahun lama tetap bisa dipilih
// ==================================================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT nama
     FROM t_tahun_ajaran
     WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, 'i', $tahun_id);
mysqli_stmt_execute($stmt);

$data_tahun = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);

mysqli_stmt_close($stmt);

// ==================================================
// AMBIL DATA KELAS
// ==================================================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT nama
     FROM t_kelas
     WHERE id = ?
     AND status_aktif = 1"
);

mysqli_stmt_bind_param($stmt, 'i', $kelas_id);
mysqli_stmt_execute($stmt);

$data_kelas = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);

mysqli_stmt_close($stmt);

// ==================================================
// AMBIL POIN ASLI DARI DATABASE
// POIN TIDAK DIPERCAYA DARI FORM
// ==================================================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT nama, poin, pelanggaran_kategori_id
     FROM t_pelanggaran
     WHERE id = ?
     AND status_aktif = 1"
);

mysqli_stmt_bind_param($stmt, 'i', $pelanggaran_id);
mysqli_stmt_execute($stmt);

$data_pelanggaran = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);

mysqli_stmt_close($stmt);

// ==================================================
// AMBIL NAMA GURU
// ==================================================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT nama
     FROM t_guru
     WHERE id = ?
     AND status_aktif = 1"
);

mysqli_stmt_bind_param($stmt, 'i', $guru_id);
mysqli_stmt_execute($stmt);

$data_guru = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);

mysqli_stmt_close($stmt);

// ==================================================
// PERIKSA SEMUA DATA
// ==================================================

if (
    !$data_siswa ||
    !$data_tahun ||
    !$data_kelas ||
    !$data_pelanggaran ||
    !$data_guru
) {
    $_SESSION['pesan_error'] = 'Data pilihan tidak ditemukan atau tidak aktif.';
    header('Location: catat_pelanggaran.php');
    exit;
}

// Ambil data asli dari database
$nama_siswa = $data_siswa['nama'];
$nama_kelas = $data_kelas['nama'];
$nama_pelanggaran = $data_pelanggaran['nama'];
$kategori_id = (int)$data_pelanggaran['pelanggaran_kategori_id'];
$nama_guru = $data_guru['nama'];

// POIN ASLI DARI TABEL T_PELANGGARAN
$poin = (int)$data_pelanggaran['poin'];

// ==================================================
// SIMPAN CATATAN PELANGGARAN
// ==================================================

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

if (!$stmt) {
    $_SESSION['pesan_error'] = 'Gagal menyiapkan penyimpanan data.';
    header('Location: catat_pelanggaran.php');
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "iisisisiisssiss",
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

// ==================================================
// HASIL PENYIMPANAN
// ==================================================

if (mysqli_stmt_execute($stmt)) {

    $_SESSION['pesan_sukses'] =
        'Catatan pelanggaran berhasil disimpan. Poin: ' . $poin;

} else {

    $_SESSION['pesan_error'] =
        'Catatan pelanggaran gagal disimpan: ' .
        mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);

header('Location: catat_pelanggaran.php');
exit;
