
<?php

// ==================================================
// MENGAMBIL KONTEKS AKSES GURU DAN ADMIN
// ==================================================

function konteks_akses_pelanggaran(mysqli $koneksi, bool $wali_saja = false): array
{
    // ==================================================
    // NORMALISASI ROLE
    // ==================================================

    $role = strtolower(trim($_SESSION['role'] ?? ''));

    if (function_exists('normalisasi_role')) {
        $role = normalisasi_role($role);
    } else {
        if (in_array($role, [
            'wali_kelas',
            'walikelas',
            'wali kelas',
            'wali-kelas',
            'guru wali kelas',
            'guru-wali-kelas'
        ], true)) {
            $role = 'guru_wali_kelas';
        }
    }

    // ==================================================
    // ADMIN
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
    // IZINKAN AKUN GURU
    // ==================================================

    if (!in_array($role, ['guru', 'guru_wali_kelas'], true)) {
        tolak_akses_pelanggaran(
            'Halaman ini hanya untuk admin dan guru.'
        );
    }

    // ==================================================
    // ID USER LOGIN
    // ==================================================

    $user_id = (int)(
        $_SESSION['id_user']
        ?? $_SESSION['user_id']
        ?? $_SESSION['id']
        ?? 0
    );

    if ($user_id <= 0) {
        tolak_akses_pelanggaran(
            'ID pengguna tidak ditemukan. Silakan login kembali.'
        );
    }

    // ==================================================
    // CARI DATA GURU BERDASARKAN USER LOGIN
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
            'Gagal memeriksa hubungan akun dengan data guru.'
        );
    }

    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);

    $hasil_guru = mysqli_stmt_get_result($stmt);
    $guru = $hasil_guru ? mysqli_fetch_assoc($hasil_guru) : null;

    mysqli_stmt_close($stmt);

    if (!$guru) {
        tolak_akses_pelanggaran(
            'Data guru tidak ditemukan atau belum aktif.'
        );
    }

    $guru_id = (int)$guru['id'];

    // ==================================================
    // AMBIL SEMUA PENUGASAN WALI KELAS YANG AKTIF
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
            'Gagal memeriksa penugasan wali kelas.'
        );
    }

    mysqli_stmt_bind_param($stmt, 'i', $guru_id);
    mysqli_stmt_execute($stmt);

    $hasil_kelas = mysqli_stmt_get_result($stmt);

    $kelas_ids = [];

    if ($hasil_kelas) {
        while ($row = mysqli_fetch_assoc($hasil_kelas)) {
            $kelas_ids[] = (int)$row['kelas_id'];
        }
    }

    mysqli_stmt_close($stmt);

    $kelas_ids = array_values(array_unique($kelas_ids));

    // ==================================================
    // STATUS WALI KELAS DITENTUKAN DARI DATABASE
    // ==================================================

    $is_wali = !empty($kelas_ids);

    // ==================================================
    // HALAMAN KHUSUS WALI KELAS
    // ==================================================

    if ($wali_saja && !$is_wali) {
        tolak_akses_pelanggaran(
            'Akses ditolak. Akun ini tidak memiliki penugasan wali kelas aktif.'
        );
    }

    // ==================================================
    // HASIL KONTEKS
    // ==================================================

    return [
        'role' => $role,
        'guru_id' => $guru_id,
        'kelas_ids' => $kelas_ids,
        'is_wali' => $is_wali,
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

    // WALI KELAS MELIHAT DATA KELASNYA
    if (!empty($konteks['is_wali']) || $kelas_wali) {

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

    echo '<a href="../guru/dashboard_guru.php">Kembali ke Dashboard</a>';

    echo '</body>';
    echo '</html>';

    exit;
}

?>
