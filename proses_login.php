
<?php
session_start();
include "config/koneksi.php";

// Validasi input
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

// Ambil akun dan status guru
$sql = "
    SELECT
        u.id,
        u.name,
        u.email,
        u.password,
        u.role,
        g.status_aktif
    FROM t_users u
    LEFT JOIN t_guru g ON g.user_id = u.id
    WHERE u.email = ?
    LIMIT 1
";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    error_log("Gagal menyiapkan query login: " . mysqli_error($koneksi));

    $_SESSION['pesan_error'] = "Terjadi kesalahan sistem. Silakan coba lagi.";
    header("Location: login.php");
    exit();
}

mysqli_stmt_bind_param($stmt, "s", $email);

if (!mysqli_stmt_execute($stmt)) {
    error_log("Gagal menjalankan query login: " . mysqli_stmt_error($stmt));

    mysqli_stmt_close($stmt);

    $_SESSION['pesan_error'] = "Terjadi kesalahan sistem. Silakan coba lagi.";
    header("Location: login.php");
    exit();
}

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if ($user) {

    $role = strtolower(trim($user['role'] ?? ''));
    $password_db = (string) ($user['password'] ?? '');

    // Verifikasi password hash
    $password_valid = (
        !empty($password_db) &&
        password_verify($password, $password_db)
    );

    // Pemeriksaan akun guru
    $guru_aktif = true;

    if ($role === 'guru' || $role === 'guru_wali_kelas') {
        $guru_aktif = (
            $user['status_aktif'] !== null &&
            (int)$user['status_aktif'] === 1
        );
    }

    if (
        $password_valid &&
        $guru_aktif &&
        in_array($role, ['admin', 'guru', 'guru_wali_kelas'], true)
    ) {

        session_regenerate_id(true);

        $_SESSION['status'] = 'login';
        $_SESSION['id'] = $user['id'];
        $_SESSION['id_user'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['nama'] = $user['name'];
        $_SESSION['nama_user'] = $user['name'];
        $_SESSION['role'] = $role;
        $_SESSION['role_verified'] = true;

        if ($role === 'admin') {
            header("Location: dashboard.php");
        } else {
            header("Location: guru/dashboard_guru.php");
        }

        exit();
    }
}

$_SESSION['pesan_error'] =
    "Email atau password tidak valid, atau akun guru tidak aktif.";

header("Location: login.php");
exit();
?>
