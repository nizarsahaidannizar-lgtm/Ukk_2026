<?php
// guru/proses_reset_password_guru.php
require_once __DIR__ . '/../includes/cek_session.php';
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['pesan_error'] = 'Akses ditolak. Hanya admin yang dapat mereset password guru.';
    header('Location: ../dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola_guru.php');
    exit();
}

$id_guru = filter_input(INPUT_POST, 'id_guru', FILTER_VALIDATE_INT);
$csrf_token = $_POST['csrf_token'] ?? '';
$password_baru = $_POST['password_baru'] ?? '';
$konfirmasi = $_POST['konfirmasi_password'] ?? '';

if (
    !$id_guru || $id_guru < 1 ||
    !is_string($csrf_token) ||
    empty($_SESSION['csrf_reset_password']) ||
    !hash_equals($_SESSION['csrf_reset_password'], $csrf_token)
) {
    $_SESSION['pesan_error'] = 'Permintaan tidak valid atau sesi formulir telah berakhir. Silakan ulangi.';
    header('Location: kelola_guru.php');
    exit();
}

if (
    !is_string($password_baru) ||
    !is_string($konfirmasi) ||
    strlen($password_baru) < 8 ||
    strlen($password_baru) > 72 ||
    $password_baru !== $konfirmasi
) {
    $_SESSION['pesan_error'] = 'Password minimal 8 karakter, maksimal 72 byte, dan konfirmasi harus sama.';
    header('Location: reset_password_guru.php?id=' . (int)$id_guru);
    exit();
}

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT g.user_id
     FROM t_guru g
     INNER JOIN t_users u ON u.id = g.user_id
     WHERE g.id = ? AND u.role IN ('guru', 'guru_wali_kelas')
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, 'i', $id_guru);
mysqli_stmt_execute($stmt);
$data_guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$data_guru) {
    $_SESSION['pesan_error'] = 'Akun guru tidak ditemukan.';
    header('Location: kelola_guru.php');
    exit();
}

$user_id = (int)$data_guru['user_id'];
$password_hash = password_hash($password_baru, PASSWORD_DEFAULT);

$update = mysqli_prepare(
    $koneksi,
    'UPDATE t_users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?'
);

if (!$update) {
    error_log('Prepare reset password gagal: ' . mysqli_error($koneksi));
    $_SESSION['pesan_error'] = 'Gagal menyiapkan perubahan password.';
    header('Location: reset_password_guru.php?id=' . (int)$id_guru);
    exit();
}

mysqli_stmt_bind_param($update, 'si', $password_hash, $user_id);
$berhasil = mysqli_stmt_execute($update);
mysqli_stmt_close($update);
unset($_SESSION['csrf_reset_password']);

if ($berhasil) {
    $_SESSION['pesan_sukses'] = 'Password guru berhasil direset. Sampaikan password baru kepada guru melalui jalur pribadi.';
} else {
    $_SESSION['pesan_error'] = 'Password guru gagal diperbarui.';
}

header('Location: kelola_guru.php');
exit();
