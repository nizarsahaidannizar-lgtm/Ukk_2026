
<?php
session_start();
include "config/koneksi.php";

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['login']) ||
    !is_string($_POST['email'] ?? null) ||
    !is_string($_POST['password'] ?? null) ||
    strlen($_POST['email']) > 255 ||
    strlen($_POST['password']) > 255
) {
    $_SESSION['pesan_error'] = "Input login tidak valid.";
    header("Location: login.php");
    exit();
}

$email = trim($_POST['email']);
$password = $_POST['password'];

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, name, email, password, role
     FROM t_users
     WHERE email = ?
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) === 1) {

    $user = mysqli_fetch_assoc($result);

    $password_db = (string) ($user['password'] ?? '');

    $password_valid =
        password_verify($password, $password_db) ||
        hash_equals($password_db, $password);

    $role = strtolower(trim($user['role'] ?? ''));

    if (
        $password_valid &&
        in_array($role, ['admin', 'guru', 'guru_wali_kelas'], true)
    ) {

        session_regenerate_id(true);

        $_SESSION['status'] = 'login';
        $_SESSION['id'] = $user['id'];
        $_SESSION['id_user'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['nama'] = $user['name'];
        $_SESSION['role'] = $role;
        $_SESSION['role_verified'] = true;

        // PENGALIHAN DASHBOARD
        if ($role === 'admin') {
            header("Location: dashboard.php");
        } else {
            header("Location: guru/dashboard_guru.php");
        }

        exit();

    }
}

$_SESSION['pesan_error'] = "Email atau password tidak valid.";
header("Location: login.php");
exit();
?>
