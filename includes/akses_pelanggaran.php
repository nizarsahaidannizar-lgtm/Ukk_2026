<?php
function konteks_akses_pelanggaran(mysqli $koneksi, bool $wali_saja = false): array
{
    $role = strtolower(trim($_SESSION['role'] ?? ''));

    if ($role === 'admin') {
        return [
            'role' => 'admin',
            'guru_id' => null,
            'kelas_ids' => [],
            'is_wali' => false,
        ];
    }

    if ($role !== 'guru') {
        tolak_akses_pelanggaran('Halaman ini hanya untuk admin atau guru.');
    }

    $user_id = (int)($_SESSION['id_user'] ?? 0);
    $stmt = mysqli_prepare(
        $koneksi,
        'SELECT id FROM t_guru WHERE user_id = ? AND status_aktif = 1 LIMIT 1'
    );
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$guru) {
        tolak_akses_pelanggaran('Akun ini belum terhubung dengan data guru aktif.');
    }

    $guru_id = (int)$guru['id'];
    $stmt = mysqli_prepare(
        $koneksi,
        'SELECT DISTINCT kelas_id FROM t_wali_kelas
         WHERE guru_id = ? AND status_aktif = 1 AND kelas_id IS NOT NULL'
    );
    mysqli_stmt_bind_param($stmt, 'i', $guru_id);
    mysqli_stmt_execute($stmt);
    $hasil_kelas = mysqli_stmt_get_result($stmt);
    $kelas_ids = [];

    while ($row = mysqli_fetch_assoc($hasil_kelas)) {
        $kelas_ids[] = (int)$row['kelas_id'];
    }

    if ($wali_saja && !$kelas_ids) {
        tolak_akses_pelanggaran('Laporan hanya tersedia untuk guru yang ditugaskan sebagai wali kelas.');
    }

    return [
        'role' => 'guru',
        'guru_id' => $guru_id,
        'kelas_ids' => $kelas_ids,
        'is_wali' => (bool)$kelas_ids,
    ];
}

function lingkup_sql_pelanggaran(array $konteks, string $alias = 'ps', bool $kelas_wali = false): string
{
    if ($konteks['role'] === 'admin') {
        return '1 = 1';
    }

    if ($kelas_wali || $konteks['is_wali']) {
        if (!$konteks['kelas_ids']) {
            return '1 = 0';
        }

        return $alias . '.kelas_id IN (' . implode(',', array_map('intval', $konteks['kelas_ids'])) . ')';
    }

    return $alias . '.guru_id = ' . (int)$konteks['guru_id'];
}

function tolak_akses_pelanggaran(string $pesan): void
{
    http_response_code(403);
    echo '<!DOCTYPE html><html lang="id"><meta charset="UTF-8"><title>Akses Ditolak</title>';
    echo '<body style="font-family: sans-serif; padding: 2rem"><h1>Akses ditolak</h1><p>';
    echo htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8');
    echo '</p><a href="login.php">Kembali ke login</a></body></html>';
    exit;
}
?>
