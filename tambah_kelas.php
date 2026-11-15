<?php
// tambah_kelas.php
include 'includes/cek_session.php';
include 'config/koneksi.php';
batasi_akses(['admin']);

$daftar_jurusan = mysqli_query($koneksi, "SELECT DISTINCT jurusan FROM t_kelas WHERE jurusan <> '' ORDER BY jurusan ASC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kelas - SIP</title>

    <!-- Bootstrap 5 CSS & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <h1 class="h2 m-0">Tambah Data Kelas</h1>
            </div>

            <!-- TOMBOL KEMBALI -->
            <div class="mb-4">
                <a href="kelola_kelas.php" class="btn btn-secondary fw-semibold">
                    Kembali
                </a>
            </div>

            <!-- CARD FORM TAMBAH KELAS -->
            <div class="card shadow-sm border-0 rounded-3 mb-5">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title m-0 fw-bold">Form Input Kelas Baru</h5>
                </div>
                <div class="card-body p-4">
                    
                    <form action="proses_tambah_kelas.php" method="POST">
                        <div class="row g-3">
                            
                            <!-- Nama Kelas -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Kelas</label>
                                <input type="text" 
                                       name="nama" 
                                       class="form-control" 
                                       placeholder="Contoh: X RPL 1" 
                                       required>
                            </div>

                            <!-- Tingkat -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tingkat</label>
                                <select name="tingkat" class="form-select" required>
                                    <option value="" selected disabled>-- Pilih Tingkat --</option>
                                    <option value="X">X</option>
                                    <option value="XI">XI</option>
                                    <option value="XII">XII</option>
                                </select>
                            </div>

                            <!-- Jurusan -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jurusan</label>
                                <select name="jurusan" class="form-select" required>
                                    <option value="" selected disabled>-- Pilih Jurusan --</option>
                                    <?php $ada_jurusan = false; ?>
                                    <?php if ($daftar_jurusan): ?>
                                        <?php while ($j = mysqli_fetch_assoc($daftar_jurusan)): $ada_jurusan = true; ?>
                                            <option value="<?= htmlspecialchars($j['jurusan'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <?= htmlspecialchars($j['jurusan']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                    <?php if (!$ada_jurusan): ?>
                                        <option value="Rekayasa Perangkat Lunak">Rekayasa Perangkat Lunak</option>
                                    <?php endif; ?>
                                </select>
                                <small class="text-muted">Pilihan jurusan mengikuti data jurusan yang sudah tersedia.</small>
                            </div>

                            <!-- Status Aktif -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status Aktif</label>
                                <select name="status_aktif" class="form-select" required>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                    Simpan
                                </button>
                                <a href="kelola_kelas.php" class="btn btn-outline-secondary px-4 ms-2">
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/bootstrap.bundle.min.js"></script>

</body>
</html>