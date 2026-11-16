
<?php
// guru/reset_password_guru.php

include '../includes/cek_session.php';
include '../config/koneksi.php';

// KHUSUS ADMIN
batasi_akses(['admin']);

// Ambil ID guru
$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['pesan_error'] = "ID guru tidak valid.";
    header("Location: kelola_guru.php");
    exit;
}

// Ambil data guru
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nama, nip, user_id FROM t_guru WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$guru = mysqli_fetch_assoc($hasil);

mysqli_stmt_close($stmt);

if (!$guru) {
    $_SESSION['pesan_error'] = "Data guru tidak ditemukan.";
    header("Location: kelola_guru.php");
    exit;
}

// Proses reset password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi_password'] ?? '';

    if (empty($password) || empty($konfirmasi)) {
        $_SESSION['pesan_error'] = "Password dan konfirmasi wajib diisi.";
    } elseif ($password !== $konfirmasi) {
        $_SESSION['pesan_error'] = "Konfirmasi password tidak sama.";
    } elseif (strlen($password) < 6) {
        $_SESSION['pesan_error'] = "Password minimal 6 karakter.";
    } else {

        $user_id = (int) $guru['user_id'];
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $update = mysqli_prepare(
            $koneksi,
            "UPDATE t_users SET password = ? WHERE id = ?"
        );

        if (!$update) {
            $_SESSION['pesan_error'] = "Gagal menyiapkan perubahan password.";
        } else {
            mysqli_stmt_bind_param(
                $update,
                "si",
                $password_hash,
                $user_id
            );

            if (mysqli_stmt_execute($update) && mysqli_stmt_affected_rows($update) >= 0) {
                $_SESSION['pesan_sukses'] = "Password guru berhasil direset.";
            } else {
                $_SESSION['pesan_error'] = "Gagal mereset password: " . mysqli_stmt_error($update);
            }

            mysqli_stmt_close($update);
        }

        header("Location: kelola_guru.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password Guru - SIP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../layout/style.css">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include '../layout/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">

            <h3 class="mb-4">Reset Password Guru</h3>

            <?php if (isset($_SESSION['pesan_error'])) { ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($_SESSION['pesan_error']); ?>
                </div>
                <?php unset($_SESSION['pesan_error']); ?>
            <?php } ?>

            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <h5 class="mb-3">Informasi Guru</h5>

                    <p><strong>Nama:</strong>
                        <?= htmlspecialchars($guru['nama'] ?? ''); ?>
                    </p>

                    <p><strong>NIP:</strong>
                        <?= htmlspecialchars($guru['nip'] ?? ''); ?>
                    </p>

                    <hr>

                    <form method="POST">

                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                minlength="6"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input
                                type="password"
                                name="konfirmasi_password"
                                class="form-control"
                                minlength="6"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Simpan Password
                        </button>

                        <a href="kelola_guru.php" class="btn btn-secondary">
                            Kembali
                        </a>

                    </form>

                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
