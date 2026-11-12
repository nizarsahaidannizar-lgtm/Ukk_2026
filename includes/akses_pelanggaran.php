
<?php

// ==================================================
// MENGAMBIL KONTEKS AKSES GURU DAN ADMIN
// ==================================================

function konteks_akses_pelanggaran(mysqli $koneksi, bool $wali_saja = false): array
{
    $role = strtolower(trim($_SESSION['role'] ?? ''));

    // NORMALISASI ROLE
    if (function_exists('normalisasi_role')) {
        $role = normalisasi_role($role);
    } else {
        if (in_array($role, ['wali_kelas', 'walikelas', 'guru wali kelas'], true)) {
            $role = 'guru_wali_kelas';
        }
    }

    // ==================================================
    // ADMIN BISA MELIHAT SEMUA DATA
    // ==================================================

    if ($role === 'admin') {
        return [
            'role' => 'admin',
            'guru_id' => null,
            'kelas_ids' => [],
            'is_wali' => false,
        ];
    }

    // ==================================================
    // IZINKAN GURU DAN WALI KELAS
    // ==================================================

    if (!in_array($role, ['guru', 'guru_wali_kelas'], true)) {
        tolak_akses_pelanggaran(
            'Halaman ini hanya untuk admin, guru, atau wali kelas.'
        );
    }

    // ==================================================
    // ID USER LOGIN
    // ==================================================

    $user_id = (int)($_SESSION['id_user'] ?? 0);

    if ($user_id <= 0) {
        tolak_akses_pelanggaran(
            'ID pengguna tidak ditemukan. Silakan login kembali.'
        );
    }

    // ==================================================
    // CARI ID GURU
    // ==================================================

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id
         FROM t_guru
         WHERE user_id = ?
         AND status_aktif = 1
         LIMIT 1"
    );

    if (!$stmt) {
        tolak_akses_pelanggaran(
            'Terjadi kesalahan saat mencari data guru.'
        );
    }

    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);

    $guru = mysqli_fetch_assoc(
        mysqli_stmt_get_result($stmt)
    );

    mysqli_stmt_close($stmt);

    if (!$guru) {
        tolak_akses_pelanggaran(
            'Akun ini belum terhubung dengan data guru aktif.'
        );
    }

    $guru_id = (int)$guru['id'];

    // ==================================================
    // AMBIL KELAS YANG DIWALIKAN
    // ==================================================

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT DISTINCT kelas_id
         FROM t_wali_kelas
         WHERE guru_id = ?
         AND status_aktif = 1
         AND kelas_id IS NOT NULL"
    );

    if (!$stmt) {
        tolak_akses_pelanggaran(
            'Terjadi kesalahan saat mengambil data kelas.'
        );
    }

    mysqli_stmt_bind_param($stmt, 'i', $guru_id);
    mysqli_stmt_execute($stmt);

    $hasil_kelas = mysqli_stmt_get_result($stmt);

    $kelas_ids = [];

    while ($row = mysqli_fetch_assoc($hasil_kelas)) {
        $kelas_ids[] = (int)$row['kelas_id'];
    }

    mysqli_stmt_close($stmt);

    // ==================================================
    // HALAMAN KHUSUS WALI KELAS
    // HARUS ROLE WALI KELAS DAN MEMILIKI KELAS AKTIF
    // ==================================================

    if (
        $wali_saja &&
        ($role !== 'guru_wali_kelas' || empty($kelas_ids))
    ) {
        tolak_akses_pelanggaran(
            'Halaman ini hanya tersedia untuk guru yang ditugaskan sebagai wali kelas.'
        );
    }

    // ==================================================
    // HASIL KONTEKS AKSES
    // ==================================================

    return [
        'role' => $role,
        'guru_id' => $guru_id,
        'kelas_ids' => $kelas_ids,

        // HANYA ROLE GURU_WALI_KELAS YANG DIANGGAP WALI
        'is_wali' => ($role === 'guru_wali_kelas'),
    ];
}


// ==================================================
// AKSES REKAP SEMUA PELANGGARAN
// ==================================================

function lingkup_sql_semua_pelanggaran(
    array $konteks,
    string $alias = 'ps'
): string {

    if (in_array(
        $konteks['role'] ?? '',
        ['admin', 'guru', 'guru_wali_kelas'],
        true
    )) {
        return '1 = 1';
    }

    return '1 = 0';
}


// ==================================================
// AKSES DATA PELANGGARAN
// ADMIN  : SEMUA DATA
// WALI   : DATA DI KELAS YANG DIWALIKAN
// GURU   : DATA YANG DICATAT SENDIRI
// ==================================================

function lingkup_sql_pelanggaran(
    array $konteks,
    string $alias = 'ps',
    bool $kelas_wali = false
): string {

    // ADMIN MELIHAT SEMUA DATA
    if (($konteks['role'] ?? '') === 'admin') {
        return '1 = 1';
    }

    // WALI KELAS MELIHAT DATA DI KELASNYA
    if (
        ($konteks['role'] ?? '') === 'guru_wali_kelas' &&
        ($kelas_wali || !empty($konteks['is_wali']))
    ) {

        $kelas_ids = array_map(
            'intval',
            $konteks['kelas_ids'] ?? []
        );

        $kelas_ids = array_filter($kelas_ids);

        if (empty($kelas_ids)) {
            return '1 = 0';
        }

        return $alias . '.kelas_id IN (' .
            implode(',', $kelas_ids) . ')';
    }

    // GURU BIASA HANYA MELIHAT CATATAN SENDIRI
    $guru_id = (int)($konteks['guru_id'] ?? 0);

    if ($guru_id <= 0) {
        return '1 = 0';
    }

    return $alias . '.guru_id = ' . $guru_id;
}


// ==================================================
// PENOLAKAN AKSES
// ==================================================

function tolak_akses_pelanggaran(string $pesan): void
{
    http_response_code(403);

    echo '<!DOCTYPE html>';
    echo '<html lang="id">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>Akses Ditolak</title>';
    echo '</head>';

    echo '<body style="font-family: Arial, sans-serif; padding: 30px;">';

    echo '<h2>Akses Ditolak</h2>';

    echo '<p>';
    echo htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8');
    echo '</p>';

    echo '<a href="../login.php">Kembali ke Login</a>';

    echo '</body>';
    echo '</html>';

    exit;
}

?>
