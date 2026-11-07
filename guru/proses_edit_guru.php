<?php
// proses_edit_guru.php
include '../includes/cek_session.php';
include '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola_guru.php');
    exit;
}

// Ambil data POST
$id           = trim($_POST['id'] ?? '');
$user_id      = trim($_POST['user_id'] ?? '');
$nip          = trim($_POST['nip'] ?? '');
$nama         = trim($_POST['nama'] ?? '');
$email        = trim($_POST['email'] ?? '');

$raw_status = $_POST['status_aktif'] ?? '';

$status_aktif = in_array((string)$raw_status, ['0', '1'], true)
    ? (int)$raw_status
    : -1;

// Validasi data
if (
    $id === '' ||
    !filter_var($id, FILTER_VALIDATE_INT) ||
    (int)$id <= 0 ||
    $nip === '' ||
    !preg_match('/^[0-9]{1,18}$/', $nip) ||
    $nama === '' ||
    strlen($nama) > 150 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 150 ||
    !in_array($status_aktif, [0, 1], true)
) {
    $_SESSION['pesan_error'] =
        'Data tidak valid. Periksa NIP, nama, email, dan status guru.';

    header('Location: edit_guru.php?id=' . urlencode($id));
    exit;
}

$id = (int)$id;
$user_id = ($user_id !== '' && filter_var($user_id, FILTER_VALIDATE_INT))
    ? (int)$user_id
    : 0;

try {

    mysqli_begin_transaction($koneksi);

    // 1. Periksa data guru
    $cek = mysqli_prepare(
        $koneksi,
        'SELECT id, user_id FROM t_guru WHERE id = ? LIMIT 1'
    );

    mysqli_stmt_bind_param($cek, 'i', $id);
    mysqli_stmt_execute($cek);

    $data_guru = mysqli_fetch_assoc(mysqli_stmt_get_result($cek));
    mysqli_stmt_close($cek);

    if (!$data_guru) {
        throw new Exception('Data guru tidak ditemukan.');
    }

    // Gunakan user_id yang tersimpan di database
    $user_id = (int)$data_guru['user_id'];

    // 2. Cek NIP ganda
    $cek_nip = mysqli_prepare(
        $koneksi,
        'SELECT id FROM t_guru WHERE nip = ? AND id != ? LIMIT 1'
    );

    mysqli_stmt_bind_param($cek_nip, 'si', $nip, $id);
    mysqli_stmt_execute($cek_nip);

    $nip_ganda = mysqli_fetch_assoc(
        mysqli_stmt_get_result($cek_nip)
    );

    mysqli_stmt_close($cek_nip);

    if ($nip_ganda) {
        throw new Exception('NIP sudah digunakan oleh guru lain.');
    }

    // 3. Cek email ganda
    $cek_email = mysqli_prepare(
        $koneksi,
        'SELECT id FROM t_guru WHERE email = ? AND id != ? LIMIT 1'
    );

    mysqli_stmt_bind_param($cek_email, 'si', $email, $id);
    mysqli_stmt_execute($cek_email);

    $email_ganda = mysqli_fetch_assoc(
        mysqli_stmt_get_result($cek_email)
    );

    mysqli_stmt_close($cek_email);

    if ($email_ganda) {
        throw new Exception('Email sudah digunakan oleh guru lain.');
    }

    // 4. Update tabel t_guru (Kolom status_aktif ada di sini)
    $stmt_guru = mysqli_prepare(
        $koneksi,
        'UPDATE t_guru
         SET nip = ?, nama = ?, email = ?, status_aktif = ?
         WHERE id = ?'
    );

    mysqli_stmt_bind_param(
        $stmt_guru,
        'sssii',
        $nip,
        $nama,
        $email,
        $status_aktif,
        $id
    );

    mysqli_stmt_execute($stmt_guru);
    mysqli_stmt_close($stmt_guru);

    // 5. Sinkronisasi tabel t_users (Kolom status_aktif dihapus karena tidak ada di tabel t_users)
    if ($user_id > 0) {

        $stmt_user = mysqli_prepare(
            $koneksi,
            'UPDATE t_users
             SET name = ?, email = ?
             WHERE id = ?'
        );

        mysqli_stmt_bind_param(
            $stmt_user,
            'ssi',
            $nama,
            $email,
            $user_id
        );

        mysqli_stmt_execute($stmt_user);

        if (mysqli_stmt_affected_rows($stmt_user) === -1) {
            throw new Exception('Gagal memperbarui akun pengguna.');
        }

        mysqli_stmt_close($stmt_user);
    }

    // 6. Simpan seluruh perubahan
    mysqli_commit($koneksi);

    $_SESSION['pesan_sukses'] = $status_aktif === 1
        ? 'Data guru berhasil diperbarui. Status guru dan akun sekarang AKTIF.'
        : 'Data guru berhasil diperbarui. Status guru dan akun sekarang NON-AKTIF.';

    header('Location: kelola_guru.php');
    exit;

} catch (Throwable $e) {

    mysqli_rollback($koneksi);

    error_log($e->getMessage());

    $_SESSION['pesan_error'] = $e->getMessage();

    header('Location: edit_guru.php?id=' . urlencode($id));
    exit;
}
?>