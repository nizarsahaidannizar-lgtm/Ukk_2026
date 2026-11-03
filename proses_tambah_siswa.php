<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: tambah_siswa.php');
    exit;
}

$post_string = static function (string $key): ?string {
    $value = $_POST[$key] ?? null;
    return is_string($value) ? trim($value) : null;
};

$nis = $post_string('nis');
$nisn = $post_string('nisn');
$nama = $post_string('nama');
$jenis_kelamin = $post_string('jenis_kelamin');
$tanggal_lahir = $post_string('tanggal_lahir');
$alamat = $post_string('alamat');
$status_aktif = $post_string('status_aktif');

$tanggal_valid = false;
if (is_string($tanggal_lahir)) {
    $tanggal = DateTime::createFromFormat('!Y-m-d', $tanggal_lahir);
    $tanggal_valid = $tanggal && $tanggal->format('Y-m-d') === $tanggal_lahir;
}
$panjang_teks = static function (string $value): int {
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
};
$data_valid = is_string($nis)
    && preg_match('/\A[0-9]{1,8}\z/', $nis)
    && is_string($nisn)
    && preg_match('/\A[0-9]{10}\z/', $nisn)
    && is_string($nama)
    && $nama !== ''
    && $panjang_teks($nama) <= 150
    && !preg_match('/[0-9]/', $nama)
    && in_array($jenis_kelamin, ['L', 'P'], true)
    && $tanggal_valid
    && is_string($alamat)
    && $alamat !== ''
    && $panjang_teks($alamat) <= 1000
    && in_array($status_aktif, ['0', '1'], true);

if (!$data_valid) {
    $_SESSION['pesan_error'] = 'Data siswa tidak valid. Periksa kembali isian formulir.';
    header('Location: tambah_siswa.php');
    exit;
}

$stmt = mysqli_prepare(
    $koneksi,
    'SELECT id FROM t_siswa WHERE nis = ? OR nisn = ? LIMIT 1'
);
if (!$stmt) {
    error_log('Gagal menyiapkan pemeriksaan duplikasi siswa: ' . mysqli_error($koneksi));
    $_SESSION['pesan_error'] = 'Data siswa gagal diperiksa. Silakan coba kembali.';
    header('Location: tambah_siswa.php');
    exit;
}

mysqli_stmt_bind_param($stmt, 'ss', $nis, $nisn);
if (!mysqli_stmt_execute($stmt)) {
    error_log('Gagal memeriksa duplikasi siswa: ' . mysqli_stmt_error($stmt));
    mysqli_stmt_close($stmt);
    $_SESSION['pesan_error'] = 'Data siswa gagal diperiksa. Silakan coba kembali.';
    header('Location: tambah_siswa.php');
    exit;
}

$siswa_ada = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($siswa_ada) {
    $_SESSION['pesan_error'] = 'NIS atau NISN sudah terdaftar.';
    header('Location: tambah_siswa.php');
    exit;
}

$stmt = mysqli_prepare(
    $koneksi,
    'INSERT INTO t_siswa
        (nis, nisn, nama, jenis_kelamin, tanggal_lahir, alamat, status_aktif)
     VALUES (?, ?, ?, ?, ?, ?, ?)'
);
if (!$stmt) {
    error_log('Gagal menyiapkan penyimpanan siswa: ' . mysqli_error($koneksi));
    $_SESSION['pesan_error'] = 'Data siswa gagal disimpan. Silakan coba kembali.';
    header('Location: tambah_siswa.php');
    exit;
}

$status_integer = (int)$status_aktif;
mysqli_stmt_bind_param(
    $stmt,
    'ssssssi',
    $nis,
    $nisn,
    $nama,
    $jenis_kelamin,
    $tanggal_lahir,
    $alamat,
    $status_integer
);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    $_SESSION['pesan_sukses'] = 'Data siswa berhasil ditambahkan.';
    header('Location: kelola_siswa.php');
    exit;
}

error_log('Gagal menyimpan data siswa: ' . mysqli_stmt_error($stmt));
mysqli_stmt_close($stmt);
$_SESSION['pesan_error'] = 'Data siswa gagal disimpan. Silakan periksa data dan coba kembali.';
header('Location: tambah_siswa.php');
exit;
