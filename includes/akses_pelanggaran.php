
<?php

// ==================================================
// MENGAMBIL KONTEKS AKSES GURU DAN ADMIN
// ==================================================

function konteks_akses_pelanggaran(mysqli $koneksi, bool $wali_saja = false): array
{
    $role = strtolower(trim($_SESSION['role'] ?? ''));

    // ADMIN BISA MELIHAT SEMUA DATA
    if ($role === 'admin') {
        return [
            'role' => 'admin',
            'guru_id' => null,
            'kelas_ids' => [],
            'is_wali' => false,
        ];
    }

    // HANYA ADMIN DAN GURU YANG BOLEH MASUK
    if ($role !== 'guru') {
        tolak_akses_pelanggaran(
            'Halaman ini hanya untuk admin atau guru.'
        );
    }

    // ID USER YANG LOGIN
    $user_id = (int)($_SESSION['id_user'] ?? 0);

    if ($user_id <= 0) {
        tolak_akses_pelanggaran(
            'ID pengguna tidak ditemukan. Silakan login kembali.'
        );
    }

    // CARI ID GURU
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

    // AMBIL KELAS WALI
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

    if ($wali_saja && empty($kelas_ids)) {
        tolak_akses_pelanggaran(
            'Laporan hanya tersedia untuk guru yang ditugaskan sebagai wali kelas.'
        );
    }

    return [
        'role' => 'guru',
        'guru_id' => $guru_id,
        'kelas_ids' => $kelas_ids,
        'is_wali' => !empty($kelas_ids),
    ];
}


// ==================================================
// AKSES DATA BERSAMA
// SEMUA GURU BISA MELIHAT SELURUH REKAP PELANGGARAN
// ==================================================

function lingkup_sql_semua_pelanggaran(
    array $konteks,
    string $alias = 'ps'
): string {

    if ($konteks['role'] === 'admin' ||
        $konteks['role'] === 'guru') {

        return '1 = 1';
    }

    return '1 = 0';
}


// ==================================================
// AKSES DATA PRIBADI
// RIWAYAT DAN TINDAKAN HANYA MILIK GURU SENDIRI
// ==================================================

function lingkup_sql_pelanggaran(
    array $konteks,
    string $alias = 'ps',
    bool $kelas_wali = false
): string {

    // ADMIN BISA MELIHAT SEMUA
    if ($konteks['role'] === 'admin') {
        return '1 = 1';
    }

    // GURU HANYA MELIHAT CATATAN MILIKNYA
    return $alias . '.guru_id = ' .
           (int)$konteks['guru_id'];
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
