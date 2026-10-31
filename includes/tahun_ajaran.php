<?php
function normalisasi_tahun_ajaran_aktif($koneksi): bool
{
    $sql = "UPDATE t_tahun_ajaran
            SET status_aktif = 0, updated_at = CURRENT_TIMESTAMP
            WHERE status_aktif = 1
              AND id <> (
                  SELECT id FROM (
                      SELECT id
                      FROM t_tahun_ajaran
                      WHERE status_aktif = 1
                      ORDER BY tanggal_mulai DESC, id DESC
                      LIMIT 1
                  ) AS tahun_aktif
              )";

    return mysqli_query($koneksi, $sql) !== false;
}