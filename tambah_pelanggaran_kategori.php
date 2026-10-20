<?php
// tambah_pelanggaran_kategori.php

include 'includes/cek_session.php';
include 'config/koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kategori Pelanggaran</title>

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
                <h1 class="h2 m-0">Tambah Kategori Pelanggaran</h1>
            </div>

            <!-- TOMBOL KEMBALI -->
            <div class="mb-4">
                <a href="kelola_pelanggaran_kategori.php" class="btn btn-secondary fw-semibold">
                    Kembali ke Kelola Kategori Pelanggaran
                </a>
            </div>

            <!-- CARD FORM TAMBAH KATEGORI PELANGGARAN -->
            <div class="card shadow-sm border-0 rounded-3 mb-5">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title m-0 fw-bold">Form Input Kategori Pelanggaran Baru</h5>
                </div>
                <div class="card-body p-4">
                    
                    <form action="proses_tambah_pelanggaran_kategori.php" method="POST">
                        <div class="row g-3">
                            
                            <!-- Nama Kategori -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Kategori</label>
                                <input type="text" 
                                       name="nama" 
                                       class="form-control" 
                                       placeholder="Contoh: Pelanggaran Ringan" 
                                       maxlength="100" 
                                       required>
                            </div>

                            <!-- Status Aktif -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status Aktif</label>
                                <select name="status_aktif" class="form-select" required>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>

                            <!-- Deskripsi -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Deskripsi</label>
                                <textarea name="deskripsi" 
                                          class="form-control" 
                                          rows="3" 
                                          placeholder="Masukkan deskripsi kategori pelanggaran"></textarea>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                    Simpan Kategori Pelanggaran
                                </button>
                                <a href="kelola_pelanggaran_kategori.php" class="btn btn-outline-secondary px-4 ms-2">
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