<?php
// proses_edit_guru.php
include '../includes/cek_session.php';
include '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola_guru.php');
    exit;
}

$id = trim($_POST['id'] ?? '');
$nip = trim($_POST['nip'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$status_aktif = (string)($_POST['status_aktif'] ?? '');

// Validasi data (NIP wajib angka dan tepat 18 digit)
if (
    empty($id) ||
    $nip === '' || !preg_match('/^[0-9]{18}$/', $nip) ||
    $nama === '' || strlen($nama) > 150 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150 ||
    !in_array($status_aktif, ['0', '1'], true)
) {
    $_SESSION['pesan_error'] = 'Data guru tidak valid. NIP wajib berupa angka dan harus tepat 18 digit.';
    header('Location: edit_guru.php?id=' . urlencode($id));
    exit;
}

// Cek apakah NIP sudah digunakan oleh guru lain
$cek_nip = mysqli_prepare($koneksi, 'SELECT id FROM t_guru WHERE nip = ? AND id != ? LIMIT 1');
mysqli_stmt_bind_param($cek_nip, 'si', $nip, $id);
mysqli_stmt_execute($cek_nip);
$nip_sudah_digunakan = mysqli_fetch_assoc(mysqli_stmt_get_result($cek_nip));
mysqli_stmt_close($cek_nip);

// Cek apakah Email sudah digunakan oleh guru lain
$cek_email = mysqli_prepare($koneksi, 'SELECT id FROM t_guru WHERE email = ? AND id != ? LIMIT 1');
mysqli_stmt_bind_param($cek_email, 'si', $email, $id);
mysqli_stmt_execute($cek_email);
$email_sudah_digunakan = mysqli_fetch_assoc(mysqli_stmt_get_result($cek_email));
mysqli_stmt_close($cek_email);

if ($nip_sudah_digunakan || $email_sudah_digunakan) {
    $_SESSION['pesan_error'] = $nip_sudah_digunakan 
        ? 'Nip sudah terdaftar oleh guru lain.' 
        : 'Email sudah digunakan oleh guru lain.';
    header('Location: edit_guru.php?id=' . urlencode($id));
    exit;
}

try {
    // Proses update ke database
    $stmt = mysqli_prepare(
        $koneksi, 
        'UPDATE t_guru SET nip = ?, nama = ?, email = ?, status_aktif = ? WHERE id = ?'
    );
    
    if (!$stmt) {
        throw new RuntimeException('Gagal menyiapkan query update.');
    }

    $status_integer = (int)$status_aktif;
    mysqli_stmt_bind_param($stmt, 'sssii', $nip, $nama, $email, $status_integer, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        // Set pesan sukses yang akan ditangkap oleh edit_guru.php / kelola_guru.php
        $_SESSION['pesan_sukses'] = 'Data guru berhasil diperbarui.';
        header('Location: kelola_guru.php');
        exit;
    } else {
        throw new RuntimeException('Gagal menyimpan perubahan data guru.');
    }

} catch (Throwable $e) {
    error_log($e->getMessage());
    $_SESSION['pesan_error'] = 'Terjadi kesalahan sistem saat memperbarui data.';
    header('Location: edit_guru.php?id=' . urlencode($id));
    exit;
}
?>