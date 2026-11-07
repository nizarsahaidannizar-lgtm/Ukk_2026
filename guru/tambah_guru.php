<?php
// guru/tambah_guru.php
include '../includes/cek_session.php';
include '../config/koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Guru - SIP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../layout/style.css">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include '../layout/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 p-4 guru-content">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h2 m-0">Tambah Data Guru</h1>
            </div>

            <?php if (isset($_SESSION['pesan_error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['pesan_error']); unset($_SESSION['pesan_error']); ?></div>
            <?php endif; ?>

            <div class="mb-4">
                <a href="kelola_guru.php" class="btn btn-secondary fw-semibold">
                    Kembali
                </a>
            </div>

            <div class="card shadow-sm border-0 rounded-3 mb-5">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title m-0 fw-bold">Form Input Guru Baru</h5>
                </div>
                <div class="card-body p-4">

                    <form action="proses_tambah_guru.php" method="POST">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NIP</label>
                                <!-- Batas maksimal diatur menjadi 18 karakter -->
                                <input type="text" name="nip" class="form-control" placeholder="Masukkan NIP Guru (18 Angka)" maxlength="18" pattern="[0-9]{1,18}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 18);" title="NIP hanya boleh berisi angka dan maksimal 18 karakter" required>
                                <small class="text-muted">Maksimal 18 digit angka.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Guru</label>
                                <input type="text" name="nama" class="form-control" placeholder="Masukkan Nama Lengkap" maxlength="150" pattern="[^0-9]+" oninput="this.value = this.value.replace(/[0-9]/g, '');" title="Nama guru tidak boleh mengandung angka" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="contoh@sekolah.sch.id" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password Awal</label>
                                <input type="password" name="password" class="form-control" minlength="8" autocomplete="new-password" required>
                                <small class="text-muted">Minimal 8 karakter.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status Aktif</label>
                                <select name="status_aktif" class="form-select" required>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                    Simpan
                                </button>
                                <a href="kelola_guru.php" class="btn btn-outline-secondary px-4 ms-2">
                                    Kembali
                                </a>
                            </div>

                        </div>
                    </form>

                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>