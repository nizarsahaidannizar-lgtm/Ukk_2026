
<?php
include "config/koneksi.php";

$sql = "SELECT id, password FROM t_users";
$result = mysqli_query($koneksi, $sql);

if (!$result) {
    die("Gagal mengambil data pengguna.");
}

$berhasil = 0;

while ($user = mysqli_fetch_assoc($result)) {

    $password_lama = (string) $user['password'];

    if ($password_lama === '') {
        continue;
    }

    // Lewati password yang sudah menggunakan hash
    $info = password_get_info($password_lama);

    if (!empty($info['algo'])) {
        continue;
    }

    $password_hash = password_hash(
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
        $password_hash,
        $user['id']
    );

    if (mysqli_stmt_execute($stmt)) {
        $berhasil++;
    }

    mysqli_stmt_close($stmt);
}

echo "Migrasi password berhasil.";
echo "<br>Jumlah akun yang diubah: " . $berhasil;
?>
