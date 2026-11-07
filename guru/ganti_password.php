<?php
// guru/ganti_password.php
include '../includes/cek_session.php';
include '../config/koneksi.php';

batasi_akses(['guru', 'guru_wali_kelas']);

$id_user = $_SESSION['id_user'] ?? 0;
$pesan_sukses = '';
$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_lama = $_POST['password_lama'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';
    $konfirmasi   = $_POST['konfirmasi_password'] ?? '';

    if (strlen($password_baru) < 8) {
        $pesan_error = 'Password baru minimal harus 8 karakter.';
    } elseif ($password_baru !== $konfirmasi) {
        $pesan_error = 'Konfirmasi password baru tidak cocok.';
    } else {
        // Ambil password lama dari database
        $stmt = mysqli_prepare($koneksi, "SELECT password FROM t_users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id_user);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user && password_verify($password_lama, $user['password'])) {
            // Hash password baru dengan aman (BCRYPT)
            $password_hash = password_hash($password_baru, PASSWORD_BCRYPT);

            $update = mysqli_prepare($koneksi, "UPDATE t_users SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($update, "si", $password_hash, $id_user);
            
            if (mysqli_stmt_execute($update)) {
                $pesan_sukses = 'Password berhasil diperbarui!';
            } else {
                $pesan_error = 'Gagal memperbarui password ke database.';
            }
            mysqli_stmt_close($update);
        } else {
            $pesan_error = 'Password lama yang Anda masukkan salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ganti Password - SIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../layout/style.css">
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        <?php include '../layout/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 p-4 guru-content">
            <div class="admin-page-header mb-4">
                <h1>Ganti Password Akun</h1>
                <p class="text-muted">Perbarui password secara berkala untuk menjaga keamanan akun Anda.</p>
            </div>

            <?php if (!empty($pesan_sukses)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($pesan_sukses) ?></div>
            <?php endif; ?>

            <?php if (!empty($pesan_error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($pesan_error) ?></div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm rounded-3 max-w-600">
                <div class="card-body p-4">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password Lama</label>
                            <input type="password" name="password_lama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password Baru</label>
                            <input type="password" name="password_baru" class="form-control" minlength="8" required>
                            <small class="text-muted">Minimal 8 karakter.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                            <input type="password" name="konfirmasi_password" class="form-control" minlength="8" required>
                        </div>
                        <button type="submit" class="btn btn-primary fw-semibold px-4">
                            <i class="fa-solid fa-key me-1"></i> Simpan Password Baru
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>