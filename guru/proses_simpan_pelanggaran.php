
<?php
// guru/proses_simpan_pelanggaran.php

date_default_timezone_set('Asia/Jakarta');

include __DIR__ . '/../includes/cek_session.php';
include __DIR__ . '/../config/koneksi.php';
include __DIR__ . '/../includes/akses_pelanggaran.php';

// ======================================
// IZINKAN GURU DAN WALI KELAS
// ======================================

batasi_akses(['guru', 'guru_wali_kelas']);

$konteks_akses = konteks_akses_pelanggaran($koneksi);

// ======================================
// CEK METODE PENGIRIMAN
// ======================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: catat_pelanggaran.php');
    exit;
}

// ======================================
// MENGAMBIL DATA FORM
// ======================================

$siswa_id = (int)($_POST['siswa_id'] ?? 0);
$tahun_id = (int)($_POST['tahun_ajaran_id'] ?? 0);
$kelas_id = (int)($_POST['kelas_id'] ?? 0);
$pelanggaran_id = (int)($_POST['pelanggaran_id'] ?? 0);

$keterangan = trim($_POST['keterangan'] ?? '');
$tindakan = trim($_POST['tindakan'] ?? '');
$status = trim($_POST['status'] ?? '');

// TANGGAL OTOMATIS SAAT DATA DISIMPAN
$tanggal = date('Y-m-d');

// ======================================
// MENGAMBIL ID GURU DARI AKUN LOGIN
// ======================================

$guru_id = (int)($konteks_akses['guru_id'] ?? 0);

if ($guru_id <= 0) {

    $id_user = (int)($_SESSION['id_user'] ?? $_SESSION['id'] ?? 0);

    if ($id_user > 0) {

        $stmtGuru = mysqli_prepare(
            $koneksi,
            "SELECT id
             FROM t_guru
             WHERE user_id = ?
             AND status_aktif = 1
             LIMIT 1"
        );

        mysqli_stmt_bind_param($stmtGuru, 'i', $id_user);
        mysqli_stmt_execute($stmtGuru);

        $dataGuru = mysqli_fetch_assoc(
            mysqli_stmt_get_result($stmtGuru)
        );

        mysqli_stmt_close($stmtGuru);

        $guru_id = (int)($dataGuru['id'] ?? 0);
    }
}

// ======================================
// VALIDASI DATA FORM
// ======================================

$status_diizinkan = [
    'Ditindaklanjuti',
    'Proses',
    'Selesai'
];

if (
    $siswa_id <= 0 ||
    $tahun_id <= 0 ||
    $kelas_id <= 0 ||
    $pelanggaran_id <= 0 ||
    $guru_id <= 0 ||
    !in_array($status, $status_diizinkan, true)
) {
    $_SESSION['pesan_error'] = 'Data pelanggaran tidak valid.';
    header('Location: catat_pelanggaran.php');
    exit;
}

// ======================================
// VALIDASI KELAS WALI
// ======================================

if (!empty($konteks_akses['is_wali'])) {

    $kelas_ids = array_map(
        'intval',
        $konteks_akses['kelas_ids'] ?? []
    );

    if (!in_array($kelas_id, $kelas_ids, true)) {

        $_SESSION['pesan_error'] =
            'Kelas yang dipilih bukan kelas wali yang menjadi tanggung jawab Anda.';

        header('Location: catat_pelanggaran.php');
        exit;
    }
}

// ======================================
// MENGAMBIL DATA SISWA
// ======================================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT nama
     FROM t_siswa
     WHERE id = ?
     AND status_aktif = 1
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, 'i', $siswa_id);
mysqli_stmt_execute($stmt);

$data_siswa = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);

mysqli_stmt_close($stmt);

// ======================================
// MENGAMBIL TAHUN AJARAN
// ======================================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT nama
     FROM t_tahun_ajaran
     WHERE id = ?
     AND status_aktif = 1
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, 'i', $tahun_id);
mysqli_stmt_execute($stmt);

$data_tahun = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);

mysqli_stmt_close($stmt);

// ======================================
// MENGAMBIL DATA KELAS
// ======================================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT nama
     FROM t_kelas
     WHERE id = ?
     AND status_aktif = 1
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, 'i', $kelas_id);
mysqli_stmt_execute($stmt);

$data_kelas = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);

mysqli_stmt_close($stmt);

// ======================================
// MENGAMBIL PELANGGARAN
// POIN DAN KATEGORI DARI DATABASE
// ======================================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT nama, poin, pelanggaran_kategori_id
     FROM t_pelanggaran
     WHERE id = ?
     AND status_aktif = 1
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, 'i', $pelanggaran_id);
mysqli_stmt_execute($stmt);

$data_pelanggaran = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);

mysqli_stmt_close($stmt);

// ======================================
// MENGAMBIL DATA GURU
// ======================================

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT nama
     FROM t_guru
     WHERE id = ?
     AND status_aktif = 1
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, 'i', $guru_id);
mysqli_stmt_execute($stmt);

$data_guru = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt)
);

mysqli_stmt_close($stmt);

// ======================================
// VALIDASI DATA DATABASE
// ======================================

if (
    !$data_siswa ||
    !$data_tahun ||
    !$data_kelas ||
    !$data_pelanggaran ||
    !$data_guru
) {
    $_SESSION['pesan_error'] =
        'Data siswa, kelas, tahun ajaran, pelanggaran, atau guru tidak ditemukan.';

    header('Location: catat_pelanggaran.php');
    exit;
}

// ======================================
// DATA YANG AKAN DISIMPAN
// ======================================

$nama_siswa = $data_siswa['nama'];
$nama_kelas = $data_kelas['nama'];
$nama_pelanggaran = $data_pelanggaran['nama'];
$nama_guru = $data_guru['nama'];

$poin = (int)$data_pelanggaran['poin'];

$kategori_id = (int)$data_pelanggaran['pelanggaran_kategori_id'];

// ======================================
// SIMPAN CATATAN PELANGGARAN
// ======================================

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

    $_SESSION['pesan_error'] =
        'Gagal menyiapkan penyimpanan: ' . mysqli_error($koneksi);

    header('Location: catat_pelanggaran.php');
    exit;
}

// URUTAN TIPE DATA SESUAI 15 PARAMETER
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

// ======================================
// HASIL PENYIMPANAN
// ======================================

if (mysqli_stmt_execute($stmt)) {

    $_SESSION['pesan_sukses'] =
        'Catatan pelanggaran dan tindakan berhasil disimpan.';

} else {

    $_SESSION['pesan_error'] =
        'Gagal menyimpan catatan: ' . mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);

header('Location: catat_pelanggaran.php');
exit;
