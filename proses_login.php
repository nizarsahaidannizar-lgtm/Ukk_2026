<?php
session_start();
include "config/koneksi.php";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];

    // Nama tabel disesuaikan menjadi t_users
    $query = mysqli_query($koneksi, "SELECT * FROM t_users WHERE email = '$email'");

    if ($query && mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);

        // Pengecekan password (ubah jika menggunakan md5 atau password_verify)
        if ($password == $user['password']) {
            $_SESSION['status']   = "login";
            $_SESSION['id_user']  = $user['id'];
            $_SESSION['email']    = $user['email'];
            $_SESSION['nama']     = $user['name']; // Kolom di database adalah 'name'
            $_SESSION['role']     = $user['role'];

            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Password salah!";
            header("Location: index.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Email tidak ditemukan!";
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>