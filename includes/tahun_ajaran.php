<?php
// includes/tahun_ajaran.php
// Pastikan hanya satu tahun ajaran aktif tanpa mengaktifkan semua data.

function normalisasi_tahun_ajaran_aktif($koneksi): bool
{
    $hasil = mysqli_query(
        $koneksi,
        "SELECT id
         FROM t_tahun_ajaran
         WHERE status_aktif = 1
         ORDER BY tanggal_mulai DESC, id DESC
         LIMIT 1"
    );

    if (!$hasil) {
        return false;
    }

    $aktif = mysqli_fetch_assoc($hasil);
    mysqli_free_result($hasil);

    // Tidak ada tahun ajaran aktif: tidak mengubah status apa pun.
    if (!$aktif) {
        return true;
    }

    $id_aktif = (int)$aktif['id'];
    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE t_tahun_ajaran
         SET status_aktif = 0, updated_at = CURRENT_TIMESTAMP
         WHERE status_aktif = 1 AND id <> ?"
    );

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'i', $id_aktif);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ok;
}
