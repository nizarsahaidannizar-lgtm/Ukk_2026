
<?php
// includes/tahun_ajaran.php

function normalisasi_tahun_ajaran_aktif($koneksi): bool
{
    // Mengaktifkan seluruh tahun ajaran
    $sql = "UPDATE t_tahun_ajaran
            SET status_aktif = 1,
                updated_at = CURRENT_TIMESTAMP";

    if (mysqli_query($koneksi, $sql)) {
        return true;
    }

    return false;
}
