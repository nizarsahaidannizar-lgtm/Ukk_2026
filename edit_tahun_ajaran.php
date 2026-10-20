<?php
// edit_tahun_ajaran.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

$id = $_GET['id'] ?? '';

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM t_tahun_ajaran WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($hasil);

if (!$data) {
    header("Location: kelola_tahun_ajaran.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tahun Ajaran</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="layout/style.css">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">

            <!-- HEADER HALAMAN -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h2 m-0">Edit Data Tahun Ajaran</h1>
            </div>

            <!-- TOMBOL KEMBALI -->
            <div class="mb-4">
                <a href="kelola_tahun_ajaran.php" class="btn btn-secondary fw-semibold">
                    Kembali ke Kelola Tahun Ajaran
                </a>
            </div>

            <!-- CARD FORM EDIT TAHUN AJARAN -->
            <div class="card shadow-sm border-0 rounded-3 mb-5">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title m-0 fw-bold">Form Edit Tahun Ajaran</h5>
                </div>
                <div class="card-body p-4">
                    
                    <form action="proses_edit_tahun_ajaran.php" method="POST">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($data['id']); ?>">

                        <div class="row g-3">
                            
                            <!-- Nama Tahun Ajaran -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Tahun Ajaran</label>
                                <input type="text" 
                                       name="nama" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($data['nama']); ?>" 
                                       placeholder="Contoh: 2026/2027" 
                                       required>
                            </div>

                            <!-- Status Aktif -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status Aktif</label>
                                <select name="status_aktif" class="form-select" required>
                                    <option value="1" <?= ($data['status_aktif'] == 1) ? 'selected' : ''; ?>>
                                        Aktif
                                    </option>
                                    <option value="0" <?= ($data['status_aktif'] == 0) ? 'selected' : ''; ?>>
                                        Tidak Aktif
                                    </option>
                                </select>
                            </div>

                            <!-- Tanggal Mulai -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Mulai</label>
                                <input type="date" 
                                       name="tanggal_mulai" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($data['tanggal_mulai']); ?>" 
                                       required>
                            </div>

                            <!-- Tanggal Selesai -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Selesai</label>
                                <input type="date" 
                                       name="tanggal_selesai" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($data['tanggal_selesai']); ?>" 
                                       required>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-warning px-4 fw-semibold text-white">
                                    Update Tahun Ajaran
                                </button>
                                <a href="kelola_tahun_ajaran.php" class="btn btn-outline-secondary px-4 ms-2">
                                    Batal
                                </a>
                            </div>

                        </div>
                    </form>

                </div>
            </div>

        </main>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/bootstrap.bundle.min.js"></script>

</body>
</html>