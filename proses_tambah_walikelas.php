<?php
// proses_tambah_walikelas.php
require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

batasi_akses(['admin']);

$halaman_kembali = '/Ukk_2026/tambah_walikelas.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $halaman_kembali);
    exit;
}

$kelas_id = (int)($_POST['kelas_id'] ?? 0);
$guru_id = (int)($_POST['guru_id'] ?? 0);
$tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
$tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');
$status_aktif = (int)($_POST['status_aktif'] ?? -1);

if (
    $kelas_id <= 0 ||
    $guru_id <= 0 ||
    !in_array($status_aktif, [0, 1], true) ||
    $tanggal_mulai === '' ||
    $tanggal_selesai === ''
) {
    $_SESSION['pesan_error'] = 'Lengkapi semua data wali kelas.';
    header('Location: ' . $halaman_kembali);
    exit;
}

$mulai = DateTime::createFromFormat('!Y-m-d', $tanggal_mulai);
$selesai = DateTime::createFromFormat('!Y-m-d', $tanggal_selesai);

if (
    !$mulai || !$selesai ||
    $mulai->format('Y-m-d') !== $tanggal_mulai ||
    $selesai->format('Y-m-d') !== $tanggal_selesai ||
    $tanggal_selesai < $tanggal_mulai
) {
    $_SESSION['pesan_error'] = 'Tanggal selesai harus sama atau setelah tanggal mulai.';
    header('Location: ' . $halaman_kembali);
    exit;
}

mysqli_begin_transaction($koneksi);

try {
    // Ambil tahun ajaran yang aktif.
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id
         FROM t_tahun_ajaran
         WHERE status_aktif = 1
         ORDER BY tanggal_mulai DESC, id DESC
         LIMIT 1"
    );
    if (!$stmt) {
        throw new Exception('Gagal menyiapkan pemeriksaan tahun ajaran.');
    }

    mysqli_stmt_execute($stmt);
    $tahun = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$tahun) {
        throw new Exception('Tidak ditemukan tahun ajaran aktif.');
    }

    $tahun_ajaran_id = (int)$tahun['id'];

    // Ambil user_id pemilik data guru.
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT user_id, status_aktif
         FROM t_guru
         WHERE id = ?
         LIMIT 1"
    );
    if (!$stmt) {
        throw new Exception('Gagal menyiapkan pemeriksaan data guru.');
    }

    mysqli_stmt_bind_param($stmt, 'i', $guru_id);
    mysqli_stmt_execute($stmt);
    $guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$guru || (int)$guru['user_id'] <= 0) {
        throw new Exception('Data guru atau akun pengguna belum terhubung.');
    }

    if ($status_aktif === 1 && (int)$guru['status_aktif'] !== 1) {
        throw new Exception('Guru harus berstatus aktif sebelum ditugaskan sebagai wali kelas.');
    }

    $user_id = (int)$guru['user_id'];

    // Simpan penugasan beserta user_id yang benar.
    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO t_wali_kelas
            (tahun_ajaran_id, kelas_id, guru_id, tanggal_mulai,
             tanggal_selesai, status_aktif, user_id)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        throw new Exception('Gagal menyiapkan penyimpanan wali kelas.');
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

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Wali kelas gagal ditambahkan: ' . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);

    // Akun guru yang memiliki penugasan aktif otomatis menjadi guru_wali_kelas.
    if ($status_aktif === 1) {
        $stmt_role = mysqli_prepare(
            $koneksi,
            "UPDATE t_users SET role = 'guru_wali_kelas' WHERE id = ?"
        );
        if (!$stmt_role) {
            throw new Exception('Gagal menyiapkan sinkronisasi role akun.');
        }

        mysqli_stmt_bind_param($stmt_role, 'i', $user_id);

        if (!mysqli_stmt_execute($stmt_role)) {
            throw new Exception('Gagal menyinkronkan role akun wali kelas.');
        }
        mysqli_stmt_close($stmt_role);
    }

    mysqli_commit($koneksi);

    $_SESSION['pesan_sukses'] = $status_aktif === 1
        ? 'Wali kelas berhasil ditambahkan. Role akun otomatis disinkronkan menjadi Guru Wali Kelas.'
        : 'Data penugasan wali kelas berhasil ditambahkan dalam status nonaktif.';

} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    error_log('Tambah wali kelas: ' . $e->getMessage());
    $_SESSION['pesan_error'] = $e->getMessage();
}

header('Location: /Ukk_2026/kelola_walikelas.php');
exit;
?>
