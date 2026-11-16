
<?php
include 'config/koneksi.php';

mysqli_begin_transaction($koneksi);

try {
    $query = mysqli_query(
        $koneksi,
        "SELECT id, password FROM t_users"
    );

    $jumlah = 0;

    while ($user = mysqli_fetch_assoc($query)) {
        $password_lama = (string) $user['password'];

        if ($password_lama === '') {
            continue;
        }

        // Lewati password yang sudah berbentuk hash.
        $info = password_get_info($password_lama);

        if ($info['algo'] !== null) {
            continue;
        }

        // Hash password tanpa mengubah password aslinya.
        $password_baru = password_hash(
            $password_lama,
            PASSWORD_DEFAULT
        );

        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE t_users SET password = ? WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $password_baru,
            $user['id']
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal memperbarui password.");
        }

        mysqli_stmt_close($stmt);
        $jumlah++;
    }

    mysqli_commit($koneksi);

    echo "Berhasil! Password telah di-hash.";
    echo "<br>Jumlah akun diperbarui: " . $jumlah;

} catch (Throwable $e) {
    mysqli_rollback($koneksi);
    echo "Gagal: " . htmlspecialchars($e->getMessage());
}
?>
