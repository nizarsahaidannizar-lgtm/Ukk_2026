<?php
// guru/reset_password_guru.php
require_once __DIR__ . '/../includes/cek_session.php';
require_once __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['pesan_error'] = 'Akses ditolak. Hanya admin yang dapat mereset password guru.';
    header('Location: ../dashboard.php');
    exit();
}

$id_guru = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_guru || $id_guru < 1) {
    $_SESSION['pesan_error'] = 'Data guru tidak valid.';
    header('Location: kelola_guru.php');
    exit();
}

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT g.id, g.nama, g.email, g.user_id, u.role
     FROM t_guru g
     INNER JOIN t_users u ON u.id = g.user_id
     WHERE g.id = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, 'i', $id_guru);
mysqli_stmt_execute($stmt);
$guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$guru || !in_array($guru['role'], ['guru', 'guru_wali_kelas'], true)) {
    $_SESSION['pesan_error'] = 'Akun guru tidak ditemukan.';
    header('Location: kelola_guru.php');
    exit();
}

if (empty($_SESSION['csrf_reset_password'])) {
    $_SESSION['csrf_reset_password'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password Guru - SIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../layout/style.css">
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <?php include '../layout/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">
            <header class="admin-page-header mb-4">
                <h1>Reset Password Guru</h1>
                <p>Atur password baru untuk akun guru yang dipilih.</p>
            </header>

            <div class="card border-0 shadow-sm rounded-3" style="max-width: 650px;">
                <div class="card-body p-4">
                    <div class="mb-4">
                        <div class="text-muted small">Akun guru</div>
                        <h5 class="mb-1"><?= htmlspecialchars($guru['nama'] ?? '', ENT_QUOTES, 'UTF-8') ?></h5>
                        <div class="text-muted"><?= htmlspecialchars($guru['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        Password lama tidak dapat dilihat. Setelah direset, berikan password baru ini kepada guru melalui jalur pribadi.
                    </div>

                    <?php if (!empty($_SESSION['pesan_error'])): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['pesan_error'], ENT_QUOTES, 'UTF-8') ?></div>
                        <?php unset($_SESSION['pesan_error']); ?>
                    <?php endif; ?>

                    <form action="proses_reset_password_guru.php" method="POST">
                        <input type="hidden" name="id_guru" value="<?= (int)$guru['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_reset_password'], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="mb-3">
                            <label for="password_baru" class="form-label fw-semibold">Password Baru</label>
                            <input type="password" class="form-control" id="password_baru" name="password_baru"
                                   minlength="8" maxlength="72" autocomplete="new-password" required>
                            <small class="text-muted">Minimal 8 karakter. Gunakan password sementara yang unik.</small>
                        </div>
                        <div class="mb-3">
                            <label for="konfirmasi_password" class="form-label fw-semibold">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password"
                                   minlength="8" maxlength="72" autocomplete="new-password" required>
                        </div>
                        <button type="submit" class="btn btn-primary fw-semibold">
                            <i class="fa-solid fa-key me-1"></i> Simpan Password Baru
                        </button>
                        <a href="kelola_guru.php" class="btn btn-secondary">Kembali</a>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const passwordBaru = document.getElementById('password_baru');
const konfirmasi = document.getElementById('konfirmasi_password');
function cekKonfirmasi() {
    konfirmasi.setCustomValidity(
        konfirmasi.value === passwordBaru.value ? '' : 'Konfirmasi password tidak sama.'
    );
}
passwordBaru.addEventListener('input', cekKonfirmasi);
konfirmasi.addEventListener('input', cekKonfirmasi);
</script>
</body>
</html>
