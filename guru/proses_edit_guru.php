
<?php
// proses_edit_guru.php

include '../includes/cek_session.php';
include '../config/koneksi.php';

// ========================================
// CEK REQUEST
// ========================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kelola_guru.php');
    exit;
}

// ========================================
// AMBIL DATA POST
// ========================================

$id      = trim($_POST['id'] ?? '');
$nip     = trim($_POST['nip'] ?? '');
$nama    = trim($_POST['nama'] ?? '');
$email   = trim($_POST['email'] ?? '');
$role    = trim($_POST['role'] ?? '');

$raw_status = $_POST['status_aktif'] ?? '';

$status_aktif = in_array((string)$raw_status, ['0', '1'], true)
    ? (int)$raw_status
    : -1;

// ========================================
// VALIDASI DATA
// ========================================

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
    !in_array($status_aktif, [0, 1], true) ||
    !in_array($role, ['guru', 'guru_wali_kelas'], true)
) {
    $_SESSION['pesan_error'] =
        'Data tidak valid. Periksa NIP, nama, email, status, dan role guru.';

    header('Location: edit_guru.php?id=' . urlencode($id));
    exit;
}

$id = (int)$id;

// ========================================
// PROSES UPDATE
// ========================================

try {

    mysqli_begin_transaction($koneksi);

    // 1. Ambil data guru dan akun pengguna

    $cek = mysqli_prepare(
        $koneksi,
        'SELECT
            g.id,
            g.user_id,
            u.id AS id_akun
         FROM t_guru g
         LEFT JOIN t_users u ON u.id = g.user_id
         WHERE g.id = ?
         LIMIT 1'
    );

    mysqli_stmt_bind_param($cek, 'i', $id);
    mysqli_stmt_execute($cek);

    $data_guru = mysqli_fetch_assoc(
        mysqli_stmt_get_result($cek)
    );

    mysqli_stmt_close($cek);

    if (!$data_guru) {
        throw new Exception('Data guru tidak ditemukan.');
    }

    $user_id = (int)($data_guru['id_akun'] ?? 0);

    // 2. Pastikan akun pengguna terhubung

    if ($user_id <= 0) {
        throw new Exception(
            'Akun pengguna guru belum terhubung dengan data guru. Role belum dapat diperbarui.'
        );
    }

    // 3. Cek NIP ganda

    $cek_nip = mysqli_prepare(
        $koneksi,
        'SELECT id
         FROM t_guru
         WHERE nip = ? AND id != ?
         LIMIT 1'
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

    // 4. Cek email ganda di tabel guru

    $cek_email = mysqli_prepare(
        $koneksi,
        'SELECT id
         FROM t_guru
         WHERE email = ? AND id != ?
         LIMIT 1'
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

    // 5. UPDATE TABEL T_GURU

    $stmt_guru = mysqli_prepare(
        $koneksi,
        'UPDATE t_guru
         SET nip = ?,
             nama = ?,
             email = ?,
             status_aktif = ?
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

    if (!mysqli_stmt_execute($stmt_guru)) {
        throw new Exception('Gagal memperbarui data guru.');
    }

    mysqli_stmt_close($stmt_guru);

    // 6. SINKRONISASI TABEL T_USERS

    $stmt_user = mysqli_prepare(
        $koneksi,
        'UPDATE t_users
         SET name = ?,
             email = ?,
             role = ?
         WHERE id = ?'
    );

    mysqli_stmt_bind_param(
        $stmt_user,
        'sssi',
        $nama,
        $email,
        $role,
        $user_id
    );

    if (!mysqli_stmt_execute($stmt_user)) {
        throw new Exception('Gagal memperbarui akun dan role pengguna.');
    }

    mysqli_stmt_close($stmt_user);

    // ========================================
    // 7. SINKRONISASI STATUS WALI KELAS
    // ========================================

    // Status penugasan Wali Kelas pada tahun
    // ajaran aktif mengikuti status Guru.

    $stmt_wali = mysqli_prepare(
        $koneksi,
        'UPDATE t_wali_kelas
         SET status_aktif = ?
         WHERE guru_id = ?
           AND tahun_ajaran_id = (
               SELECT id
               FROM t_tahun_ajaran
               WHERE status_aktif = 1
               LIMIT 1
           )'
    );

    if (!$stmt_wali) {
        throw new Exception(
            'Gagal menyiapkan sinkronisasi status Wali Kelas.'
        );
    }

    mysqli_stmt_bind_param(
        $stmt_wali,
        'ii',
        $status_aktif,
        $id
    );

    if (!mysqli_stmt_execute($stmt_wali)) {
        throw new Exception(
            'Gagal menyinkronkan status Guru dan Wali Kelas.'
        );
    }

    mysqli_stmt_close($stmt_wali);

    // ========================================
    // 8. SIMPAN SELURUH PERUBAHAN
    // ========================================

    mysqli_commit($koneksi);

    // ========================================
    // PESAN BERHASIL
    // ========================================

    if ($status_aktif === 0) {

        $_SESSION['pesan_sukses'] =
            'Data guru berhasil dinonaktifkan. Status penugasan Wali Kelas pada tahun ajaran aktif juga berhasil dinonaktifkan.';

    } else {

        $_SESSION['pesan_sukses'] =
            'Data guru berhasil diaktifkan. Status penugasan Wali Kelas pada tahun ajaran aktif juga berhasil diaktifkan.';
    }

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
