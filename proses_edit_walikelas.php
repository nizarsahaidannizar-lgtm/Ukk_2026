
<?php
// proses_edit_walikelas.php

require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

batasi_akses(['admin']);

// ========================================
// ALAMAT REDIRECT
// ========================================

$halaman_kembali = '/Ukk_2026/kelola_walikelas.php';

// ========================================
// CEK REQUEST
// ========================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . $halaman_kembali);
    exit;
}

// ========================================
// AMBIL DATA FORM
// ========================================

$id = (int) ($_POST['id'] ?? 0);
$kelas_id = (int) ($_POST['kelas_id'] ?? 0);
$guru_id = (int) ($_POST['guru_id'] ?? 0);
$status_aktif = (int) ($_POST['status_aktif'] ?? -1);

$tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
$tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');

// ========================================
// VALIDASI DATA
// ========================================

if (
    $id <= 0 ||
    $kelas_id <= 0 ||
    $guru_id <= 0 ||
    !in_array($status_aktif, [0, 1], true) ||
    empty($tanggal_mulai) ||
    empty($tanggal_selesai)
) {
    $_SESSION['pesan_error'] = "Data wali kelas tidak lengkap.";
    header("Location: " . $halaman_kembali);
    exit;
}

// ========================================
// VALIDASI TANGGAL
// ========================================

if ($tanggal_selesai < $tanggal_mulai) {
    $_SESSION['pesan_error'] =
        "Tanggal selesai tidak boleh lebih awal dari tanggal mulai.";

    header("Location: " . $halaman_kembali);
    exit;
}

// ========================================
// MULAI TRANSAKSI
// ========================================

mysqli_begin_transaction($koneksi);

try {

    // ========================================
    // CEK DATA WALI KELAS
    // ========================================

    $cek = mysqli_prepare(
        $koneksi,
        "SELECT id, guru_id
         FROM t_wali_kelas
         WHERE id = ?
         LIMIT 1"
    );

    if (!$cek) {
        throw new Exception("Gagal memeriksa data wali kelas.");
    }

    mysqli_stmt_bind_param($cek, "i", $id);
    mysqli_stmt_execute($cek);

    $hasil_cek = mysqli_stmt_get_result($cek);
    $data_ada = mysqli_fetch_assoc($hasil_cek);

    mysqli_stmt_close($cek);

    if (!$data_ada) {
        throw new Exception("Data wali kelas tidak ditemukan.");
    }

    // ========================================
    // CEK TAHUN AJARAN AKTIF
    // ========================================

    $cek_tahun = mysqli_prepare(
        $koneksi,
        "SELECT id
         FROM t_tahun_ajaran
         WHERE status_aktif = 1
         ORDER BY tanggal_mulai DESC, id DESC
         LIMIT 1"
    );

    if (!$cek_tahun) {
        throw new Exception("Gagal mengambil tahun ajaran aktif.");
    }

    mysqli_stmt_execute($cek_tahun);

    $hasil_tahun = mysqli_stmt_get_result($cek_tahun);
    $data_tahun = mysqli_fetch_assoc($hasil_tahun);

    mysqli_stmt_close($cek_tahun);

    if (!$data_tahun) {
        throw new Exception("Tidak ada tahun ajaran yang aktif.");
    }

    $tahun_ajaran_id = (int) $data_tahun['id'];

    // ========================================
    // CEK GURU DAN ROLE AKUN
    // ========================================

    $cek_guru = mysqli_prepare(
        $koneksi,
        "SELECT
            g.id,
            g.status_aktif,
            u.role
         FROM t_guru g
         LEFT JOIN t_users u ON u.id = g.user_id
         WHERE g.id = ?
         LIMIT 1"
    );

    if (!$cek_guru) {
        throw new Exception("Gagal memeriksa data guru.");
    }

    mysqli_stmt_bind_param($cek_guru, "i", $guru_id);
    mysqli_stmt_execute($cek_guru);

    $hasil_guru = mysqli_stmt_get_result($cek_guru);
    $data_guru = mysqli_fetch_assoc($hasil_guru);

    mysqli_stmt_close($cek_guru);

    if (!$data_guru) {
        throw new Exception("Guru yang dipilih tidak ditemukan.");
    }

    // ========================================
    // VALIDASI ROLE JIKA WALI KELAS AKTIF
    // ========================================

    if ($status_aktif === 1) {

        $role_guru = strtolower(trim($data_guru['role'] ?? ''));

        if ($role_guru !== 'guru_wali_kelas') {
            throw new Exception(
                "Role akun guru harus diubah menjadi Wali Kelas terlebih dahulu melalui Kelola Guru."
            );
        }
    }

    // ========================================
    // UPDATE DATA WALI KELAS
    // ========================================

    $sql = "UPDATE t_wali_kelas SET
                tahun_ajaran_id = ?,
                kelas_id = ?,
                guru_id = ?,
                status_aktif = ?,
                tanggal_mulai = ?,
                tanggal_selesai = ?
            WHERE id = ?";

    $stmt = mysqli_prepare($koneksi, $sql);

    if (!$stmt) {
        throw new Exception("Gagal menyiapkan proses edit wali kelas.");
    }

    mysqli_stmt_bind_param(
        $stmt,
        "iiiissi",
        $tahun_ajaran_id,
        $kelas_id,
        $guru_id,
        $status_aktif,
        $tanggal_mulai,
        $tanggal_selesai,
        $id
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(
            "Gagal memperbarui data: " . mysqli_stmt_error($stmt)
        );
    }

    mysqli_stmt_close($stmt);

    // ========================================
    // SINKRONISASI STATUS TABEL T_GURU
    // ========================================

    $update_guru = mysqli_prepare(
        $koneksi,
        "UPDATE t_guru
         SET status_aktif = ?
         WHERE id = ?"
    );

    if (!$update_guru) {
        throw new Exception(
            "Gagal menyiapkan sinkronisasi status guru."
        );
    }

    mysqli_stmt_bind_param(
        $update_guru,
        "ii",
        $status_aktif,
        $guru_id
    );

    if (!mysqli_stmt_execute($update_guru)) {
        throw new Exception(
            "Gagal menyinkronkan status Wali Kelas dan Guru."
        );
    }

    mysqli_stmt_close($update_guru);

    // ========================================
    // SIMPAN PERUBAHAN
    // ========================================

    mysqli_commit($koneksi);

    if ($status_aktif === 0) {

        $_SESSION['pesan_sukses'] =
            "Data Wali Kelas berhasil dinonaktifkan. Status guru juga berhasil dinonaktifkan.";

    } else {

        $_SESSION['pesan_sukses'] =
            "Data Wali Kelas berhasil diaktifkan. Status guru juga berhasil diaktifkan.";
    }

} catch (Throwable $e) {

    mysqli_rollback($koneksi);

    error_log("Edit Wali Kelas: " . $e->getMessage());

    $_SESSION['pesan_error'] = $e->getMessage();
}

// ========================================
// KEMBALI KE KELOLA WALI KELAS
// ========================================

header("Location: " . $halaman_kembali);
exit;
