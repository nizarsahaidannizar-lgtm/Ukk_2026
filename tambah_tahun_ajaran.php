<?php
// tambah_tahun_ajaran.php
include 'includes/cek_session.php';
include 'config/koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Tahun Ajaran</title>

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
                <h1 class="h2 m-0">Tambah Data Tahun Ajaran</h1>
            </div>

            <!-- TOMBOL KEMBALI -->
            <div class="mb-4">
                <a href="kelola_tahun_ajaran.php" class="btn btn-secondary fw-semibold">
                    Kembali
                </a>
            </div>

            <!-- CARD FORM TAMBAH TAHUN AJARAN -->
            <div class="card shadow-sm border-0 rounded-3 mb-5">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title m-0 fw-bold">Form Input Tahun Ajaran Baru</h5>
                </div>
                <div class="card-body p-4">
                    
                    <form action="proses_tambah_tahun_ajaran.php" method="POST">
                        <div class="row g-3">
                            
                            <!-- Tahun Ajaran (Dropdown) -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tahun Ajaran</label>
                                <select name="nama" class="form-select" required>
                                    <option value="" disabled selected>Pilih Tahun Ajaran</option>
                                    <?php
                                    // Menentukan tahun mulai dari 2023 hingga beberapa tahun ke depan secara dinamis
                                    $tahun_mulai = 2023;
                                    $tahun_sekarang = (int) date('Y');
                                    $tahun_maksimal = max($tahun_sekarang + 4, 2030); // Otomatis bertambah ke tahun-tahun berikutnya

                                    for ($tahun = $tahun_mulai; $tahun <= $tahun_maksimal; $tahun++) {
                                        $tahun_berikutnya = $tahun + 1;
                                        $format_tahun = $tahun . '/' . $tahun_berikutnya;
                                        echo "<option value=\"{$format_tahun}\">{$format_tahun}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Status Aktif -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status Aktif</label>
                                <select name="status_aktif" class="form-select" required>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>

                            <!-- Tanggal Mulai -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Mulai</label>
                                <input type="date" 
                                       name="tanggal_mulai" 
                                       class="form-control" 
                                       required>
                            </div>

                            <!-- Tanggal Selesai -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Selesai</label>
                                <input type="date" 
                                       name="tanggal_selesai" 
                                       class="form-control" 
                                       required>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                    Simpan
                                </button>
                                <a href="kelola_tahun_ajaran.php" class="btn btn-outline-secondary px-4 ms-2">
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

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>