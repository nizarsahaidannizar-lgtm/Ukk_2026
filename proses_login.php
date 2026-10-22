<?php
session_start();
include "config/koneksi.php";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];

    // Nama tabel disesuaikan menjadi t_users
    $query = mysqli_query(
        $koneksi,
        "SELECT u.*, g.id AS profil_guru_id
         FROM t_users u
         LEFT JOIN t_guru g ON g.user_id = u.id
         WHERE u.email = '$email'
         LIMIT 1"
    );

    if ($query && mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);

        $password_tersimpan = (string)($user['password'] ?? '');
        $password_valid = password_verify($password, $password_tersimpan)
            || hash_equals($password_tersimpan, $password);
        $role_db = strtolower(trim($user['role'] ?? ''));
        $role_login = !empty($user['profil_guru_id']) && in_array($role_db, ['admin', 'guru'], true)
            ? 'guru'
            : $role_db;

        if ($password_valid && in_array($role_login, ['admin', 'guru'], true)) {
            session_regenerate_id(true);
            $_SESSION['status']   = "login";
            $_SESSION['id_user']  = $user['id'];
            $_SESSION['email']    = $user['email'];
            $_SESSION['nama']     = $user['name']; // Kolom di database adalah 'name'
            $_SESSION['role']     = $role_login;
            $_SESSION['role_verified'] = true;

            header("Location: " . ($_SESSION['role'] === 'admin' ? 'dashboard.php' : 'catat_pelanggaran.php'));
            exit();
        } else {
            $_SESSION['pesan_error'] = "Email, password, atau hak akses tidak valid.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['pesan_error'] = "Email, password, atau hak akses tidak valid.";
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>