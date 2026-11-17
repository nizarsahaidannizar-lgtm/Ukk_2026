
<?php
// proses_login.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/config/koneksi.php";

// ========================================
// VALIDASI INPUT
// ========================================

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['login']) ||
    !is_string($_POST['email'] ?? null) ||
    !is_string($_POST['password'] ?? null) ||
    strlen($_POST['email']) > 255 ||
    strlen($_POST['password']) > 255
) {
    $_SESSION['pesan_error'] = "Input login tidak valid.";
    header("Location: /Ukk_2026/login.php");
    exit;
}

$email = trim($_POST['email']);
$password = $_POST['password'];

// ========================================
// NORMALISASI ROLE
// ========================================

function normalisasi_role_login($role)
{
    $role = strtolower(trim((string) $role));

    $alias = [
        'wali_kelas'      => 'guru_wali_kelas',
        'walikelas'       => 'guru_wali_kelas',
        'wali kelas'      => 'guru_wali_kelas',
        'wali-kelas'      => 'guru_wali_kelas',
        'guru wali kelas' => 'guru_wali_kelas',
        'guru-wali-kelas' => 'guru_wali_kelas'
    ];

    return $alias[$role] ?? $role;
}

// ========================================
// AMBIL DATA AKUN
// ========================================

$sql = "
    SELECT
        u.id,
        u.name,
        u.email,
        u.password,
        u.role,

        g.id AS guru_id,
        g.status_aktif AS status_guru,

        (
            SELECT COUNT(*)
            FROM t_wali_kelas w
            WHERE w.status_aktif = 1
              AND (
                    w.guru_id = g.id
                    OR w.user_id = u.id
              )
        ) AS wali_kelas_aktif

    FROM t_users u

    LEFT JOIN t_guru g
        ON g.user_id = u.id

    WHERE u.email = ?

    LIMIT 1
";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    error_log("Query login gagal: " . mysqli_error($koneksi));

    $_SESSION['pesan_error'] = "Terjadi kesalahan sistem.";
    header("Location: /Ukk_2026/login.php");
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $email);

if (!mysqli_stmt_execute($stmt)) {
    error_log("Eksekusi login gagal: " . mysqli_stmt_error($stmt));

    mysqli_stmt_close($stmt);

    $_SESSION['pesan_error'] = "Terjadi kesalahan sistem.";
    header("Location: /Ukk_2026/login.php");
    exit;
}

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// ========================================
// VERIFIKASI AKUN
// ========================================

if ($user) {

    $role = normalisasi_role_login($user['role'] ?? '');

    $password_db = (string) ($user['password'] ?? '');

    // ====================================
    // VERIFIKASI PASSWORD
    // ====================================

    $password_valid = (
        $password_db !== '' &&
        password_verify($password, $password_db)
    );

    // ====================================
    // CEK STATUS GURU
    // ====================================

    $guru_aktif = true;

    if (in_array($role, ['guru', 'guru_wali_kelas'], true)) {

        $guru_aktif = (
            $user['guru_id'] !== null &&
            (int) $user['status_guru'] === 1
        );
    }

    // ====================================
    // CEK PENUGASAN WALI KELAS
    // ====================================

    $wali_kelas_aktif = (
        (int) ($user['wali_kelas_aktif'] ?? 0) > 0
    );

    // ====================================
    // TENTUKAN HAK LOGIN
    // ====================================

    $akses_login = false;

    if ($role === 'admin') {

        $akses_login = true;

    } elseif ($role === 'guru') {

        // Guru biasa tetap bisa login.
        $akses_login = $guru_aktif;

    } elseif ($role === 'guru_wali_kelas') {

        // Guru wali kelas harus memiliki penugasan aktif.
        $akses_login = (
            $guru_aktif &&
            $wali_kelas_aktif
        );
    }

    // ====================================
    // LOGIN BERHASIL
    // ====================================

    if (
        $password_valid &&
        $akses_login &&
        in_array(
            $role,
            ['admin', 'guru', 'guru_wali_kelas'],
            true
        )
    ) {

        session_regenerate_id(true);

        $_SESSION['status'] = 'login';

        // ID USER
        $_SESSION['id'] = (int) $user['id'];
        $_SESSION['id_user'] = (int) $user['id'];
        $_SESSION['user_id'] = (int) $user['id'];

        // DATA PENGGUNA
        $_SESSION['email'] = $user['email'];
        $_SESSION['nama'] = $user['name'];
        $_SESSION['nama_user'] = $user['name'];

        // ROLE
        $_SESSION['role'] = $role;
        $_SESSION['role_verified'] = true;

        // ID GURU
        if ($user['guru_id'] !== null) {
            $_SESSION['guru_id'] = (int) $user['guru_id'];
        } else {
            unset($_SESSION['guru_id']);
        }

        // ====================================
        // REDIRECT BERDASARKAN ROLE
        // ====================================

        if ($role === 'admin') {

            header("Location: /Ukk_2026/dashboard.php");

        } else {

            header("Location: /Ukk_2026/guru/dashboard_guru.php");
        }

        exit;
    }
}

// ========================================
// LOGIN GAGAL
// ========================================

$_SESSION['pesan_error'] =
    "Email atau password tidak valid, atau akun guru/Wali Kelas belum aktif atau belum memiliki penugasan.";

header("Location: /Ukk_2026/login.php");
exit;
?>
