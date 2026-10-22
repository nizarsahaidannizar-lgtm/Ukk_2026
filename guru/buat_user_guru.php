<?php
//buat_user_guru.php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

include __DIR__ . '/../config/koneksi.php';

$name = 'Guru';
$email= 'guru@gmail.com';
$password = password_hash('guru123', PASSWORD_DEFAULT);
$role = 'guru';

$sql = "INSERT INTO t_users(name,email,password,role)";
$sql .= "VALUES('$name','$email','$password','$role')";

if (mysqli_query($koneksi, $sql)) {
    echo 'User admin berhasil dibuat, silakan hapus file ini';
} else{
    echo 'Gagal memuat user: '. mysqli_error($koneksi);
}
?>