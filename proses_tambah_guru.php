<?php
// proses_tambah_guru.php
include 'includes/cek_session.php';
include 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: guru/tambah_guru.php');
    exit;
}

$nip = trim($_POST['nip'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = (string)($_POST['password'] ?? '');
$status_aktif = (string)($_POST['status_aktif'] ?? '');

if (
    $nip === '' || strlen($nip) > 30 ||
    $nama === '' || strlen($nama) > 150 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150 ||
    strlen($password) < 8 ||
    !in_array($status_aktif, ['0', '1'], true)
) {
    $_SESSION['pesan_error'] = 'Data guru tidak valid. Periksa kembali semua isian.';
    header('Location: guru/tambah_guru.php');
    exit;
}

$cek_email = mysqli_prepare($koneksi, 'SELECT id FROM t_users WHERE email = ? LIMIT 1');
mysqli_stmt_bind_param($cek_email, 's', $email);
mysqli_stmt_execute($cek_email);
$email_sudah_digunakan = mysqli_fetch_assoc(mysqli_stmt_get_result($cek_email));
mysqli_stmt_close($cek_email);

$cek_nip = mysqli_prepare($koneksi, 'SELECT id FROM t_guru WHERE nip = ? LIMIT 1');
mysqli_stmt_bind_param($cek_nip, 's', $nip);
mysqli_stmt_execute($cek_nip);
$nip_sudah_digunakan = mysqli_fetch_assoc(mysqli_stmt_get_result($cek_nip));
mysqli_stmt_close($cek_nip);

if ($email_sudah_digunakan || $nip_sudah_digunakan) {
    $_SESSION['pesan_error'] = $email_sudah_digunakan
        ? 'Email sudah digunakan akun lain.'
        : 'NIP sudah terdaftar.';
    header('Location: guru/tambah_guru.php');
    exit;
}

try {
    mysqli_begin_transaction($koneksi);

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt_user = mysqli_prepare(
        $koneksi,
        "INSERT INTO t_users (name, email, password, remember_token, role)
         VALUES (?, ?, ?, '', 'guru')"
    );
    if (!$stmt_user) {
        throw new RuntimeException('Gagal menyiapkan akun guru.');
    }
    mysqli_stmt_bind_param($stmt_user, 'sss', $nama, $email, $password_hash);
    if (!mysqli_stmt_execute($stmt_user)) {
        throw new RuntimeException('Gagal menyimpan akun guru.');
    }
    $user_id = mysqli_insert_id($koneksi);
    mysqli_stmt_close($stmt_user);

    $stmt_guru = mysqli_prepare(
        $koneksi,
        'INSERT INTO t_guru (nip, nama, email, status_aktif, user_id) VALUES (?, ?, ?, ?, ?)'
    );
    if (!$stmt_guru) {
        throw new RuntimeException('Gagal menyiapkan profil guru.');
    }
    $status_integer = (int)$status_aktif;
    mysqli_stmt_bind_param($stmt_guru, 'sssii', $nip, $nama, $email, $status_integer, $user_id);
    if (!mysqli_stmt_execute($stmt_guru)) {
        throw new RuntimeException('Gagal menyimpan profil guru.');
    }
    mysqli_stmt_close($stmt_guru);

    mysqli_commit($koneksi);
    $_SESSION['pesan_sukses'] = 'Akun dan data guru berhasil ditambahkan.';
    header('Location: guru/kelola_guru.php');
    exit;
} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    error_log($e->getMessage());
    $_SESSION['pesan_error'] = 'Data guru gagal disimpan. Periksa data dan coba kembali.';
    header('Location: guru/tambah_guru.php');
    exit;
}
?>