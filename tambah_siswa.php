<?php
// tambah_siswa.php
include 'includes/cek_session.php';
include 'config/koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Siswa</title>

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
                <h1 class="h2 m-0">Tambah Data Siswa</h1>
            </div>

            <!-- TOMBOL KEMBALI -->
            <div class="mb-4">
                <a href="kelola_siswa.php" class="btn btn-secondary fw-semibold">
                    Kembali ke Kelola Siswa
                </a>
            </div>

            <!-- CARD FORM TAMBAH SISWA -->
            <div class="card shadow-sm border-0 rounded-3 mb-5">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title m-0 fw-bold">Form Input Siswa Baru</h5>
                </div>
                <div class="card-body p-4">
                    
                    <form action="proses_tambah_siswa.php" method="POST">
                        <div class="row g-3">
                            
                            <!-- NIS -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NIS</label>
                                <input type="text" 
                                       name="nis" 
                                       class="form-control" 
                                       placeholder="Masukkan NIS" 
                                       required>
                            </div>

                            <!-- NISN -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NISN</label>
                                <input type="text" 
                                       name="nisn" 
                                       class="form-control" 
                                       placeholder="Masukkan NISN" 
                                       required>
                            </div>

                            <!-- Nama Siswa -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Siswa</label>
                                <input type="text" 
                                       name="nama" 
                                       class="form-control" 
                                       placeholder="Masukkan Nama Lengkap" 
                                       required>
                            </div>

                            <!-- Jenis Kelamin -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-select" required>
                                    <option value="">-- Pilih Jenis Kelamin --</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>

                            <!-- Tanggal Lahir -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Lahir</label>
                                <input type="date" 
                                       name="tanggal_lahir" 
                                       class="form-control" 
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

                            <!-- Alamat -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Alamat</label>
                                <textarea name="alamat" 
                                          class="form-control" 
                                          rows="3" 
                                          placeholder="Masukkan Alamat Lengkap" 
                                          required></textarea>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                    Simpan Data Siswa
                                </button>
                                <a href="kelola_siswa.php" class="btn btn-outline-secondary px-4 ms-2">
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