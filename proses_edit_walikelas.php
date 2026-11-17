<?php
// proses_edit_walikelas.php
require_once __DIR__ . '/includes/cek_session.php';
require_once __DIR__ . '/config/koneksi.php';

batasi_akses(['admin']);

$halaman_kembali = '/Ukk_2026/kelola_walikelas.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $halaman_kembali);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$kelas_id = (int)($_POST['kelas_id'] ?? 0);
$guru_id = (int)($_POST['guru_id'] ?? 0);
$status_aktif = (int)($_POST['status_aktif'] ?? -1);
$tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
$tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');

if (
    $id <= 0 || $kelas_id <= 0 || $guru_id <= 0 ||
    !in_array($status_aktif, [0, 1], true) ||
    $tanggal_mulai === '' || $tanggal_selesai === ''
) {
    $_SESSION['pesan_error'] = 'Data wali kelas tidak lengkap atau tidak valid.';
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
    // Ambil penugasan lama untuk menyinkronkan akun sebelumnya.
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT user_id
         FROM t_wali_kelas
         WHERE id = ?
         LIMIT 1
         FOR UPDATE"
    );
    if (!$stmt) {
        throw new Exception('Gagal memeriksa penugasan wali kelas.');
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $lama = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$lama) {
        throw new Exception('Data wali kelas tidak ditemukan.');
    }

    $user_id_lama = (int)$lama['user_id'];

    // Ambil tahun ajaran aktif.
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id
         FROM t_tahun_ajaran
         WHERE status_aktif = 1
         ORDER BY tanggal_mulai DESC, id DESC
         LIMIT 1"
    );
    if (!$stmt) {
        throw new Exception('Gagal memeriksa tahun ajaran aktif.');
    }

    mysqli_stmt_execute($stmt);
    $tahun = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$tahun) {
        throw new Exception('Tidak ditemukan tahun ajaran aktif.');
    }

    $tahun_ajaran_id = (int)$tahun['id'];

    // Ambil user_id dan status guru yang dipilih.
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT user_id, status_aktif
         FROM t_guru
         WHERE id = ?
         LIMIT 1"
    );
    if (!$stmt) {
        throw new Exception('Gagal memeriksa data guru.');
    }

    mysqli_stmt_bind_param($stmt, 'i', $guru_id);
    mysqli_stmt_execute($stmt);
    $guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$guru || (int)$guru['user_id'] <= 0) {
        throw new Exception('Guru belum terhubung dengan akun pengguna.');
    }

    if ($status_aktif === 1 && (int)$guru['status_aktif'] !== 1) {
        throw new Exception('Guru harus berstatus aktif untuk menjadi wali kelas.');
    }

    $user_id_baru = (int)$guru['user_id'];

    // Update assignment dan user_id sekaligus agar selalu sinkron.
    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE t_wali_kelas
         SET tahun_ajaran_id = ?,
             kelas_id = ?,
             guru_id = ?,
             user_id = ?,
             status_aktif = ?,
             tanggal_mulai = ?,
             tanggal_selesai = ?
         WHERE id = ?"
    );

    if (!$stmt) {
        throw new Exception('Gagal menyiapkan perubahan data wali kelas.');
    }

    mysqli_stmt_bind_param(
        $stmt,
        'iiiiissi',
        $tahun_ajaran_id,
        $kelas_id,
        $guru_id,
        $user_id_baru,
        $status_aktif,
        $tanggal_mulai,
        $tanggal_selesai,
        $id
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Gagal memperbarui penugasan: ' . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);

    // Sinkronkan role akun guru yang dipilih.
    if ($status_aktif === 1) {
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE t_users SET role = 'guru_wali_kelas' WHERE id = ?"
        );
        if (!$stmt) {
            throw new Exception('Gagal menyiapkan sinkronisasi role guru baru.');
        }

        mysqli_stmt_bind_param($stmt, 'i', $user_id_baru);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Gagal menyinkronkan role guru baru.');
        }
        mysqli_stmt_close($stmt);
    }

    // Jika penugasan dipindahkan/dinonaktifkan, akun lama kembali menjadi guru
    // hanya jika tidak memiliki penugasan wali kelas aktif lainnya.
    if ($user_id_lama > 0 && ($user_id_lama !== $user_id_baru || $status_aktif === 0)) {
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT COUNT(*) AS total
             FROM t_wali_kelas tw
             INNER JOIN t_tahun_ajaran ta
                 ON ta.id = tw.tahun_ajaran_id
             WHERE tw.user_id = ?
               AND tw.status_aktif = 1
               AND ta.status_aktif = 1"
        );
        if (!$stmt) {
            throw new Exception('Gagal memeriksa penugasan aktif akun sebelumnya.');
        }

        mysqli_stmt_bind_param($stmt, 'i', $user_id_lama);
        mysqli_stmt_execute($stmt);
        $cek_lama = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if ((int)($cek_lama['total'] ?? 0) === 0) {
            $stmt = mysqli_prepare(
                $koneksi,
                "UPDATE t_users
                 SET role = 'guru'
                 WHERE id = ? AND role = 'guru_wali_kelas'"
            );
            if (!$stmt) {
                throw new Exception('Gagal menyiapkan pembaruan role akun sebelumnya.');
            }

            mysqli_stmt_bind_param($stmt, 'i', $user_id_lama);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Gagal memperbarui role akun sebelumnya.');
            }
            mysqli_stmt_close($stmt);
        }
    }

    mysqli_commit($koneksi);
    $_SESSION['pesan_sukses'] = 'Data wali kelas, guru, user_id, dan role akun berhasil disinkronkan.';

} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    error_log('Edit wali kelas: ' . $e->getMessage());
    $_SESSION['pesan_error'] = $e->getMessage();
}

header('Location: ' . $halaman_kembali);
exit;
?>
