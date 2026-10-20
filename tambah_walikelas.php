<?php
// tambah_walikelas.php
include 'includes/cek_session.php';
include 'config/koneksi.php';

$tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_tahun_ajaran ORDER BY id DESC"
);

$kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_kelas ORDER BY nama ASC"
);

$guru = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_guru ORDER BY nama ASC"
);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Wali Kelas</title>

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
                <h1 class="h2 m-0">Tambah Wali Kelas</h1>
            </div>

            <!-- TOMBOL KEMBALI -->
            <div class="mb-4">
                <a href="kelola_walikelas.php" class="btn btn-secondary fw-semibold">
                    Kembali ke Kelola Wali Kelas
                </a>
            </div>

            <!-- CARD FORM TAMBAH WALI KELAS -->
            <div class="card shadow-sm border-0 rounded-3 mb-5">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title m-0 fw-bold">Form Input Wali Kelas Baru</h5>
                </div>
                <div class="card-body p-4">
                    
                    <form action="proses_tambah_walikelas.php" method="POST">
                        <div class="row g-3">
                            
                            <!-- Tahun Ajaran -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tahun Ajaran</label>
                                <select name="tahun_ajaran_id" class="form-select" required>
                                    <option value="">-- Pilih Tahun Ajaran --</option>
                                    <?php while ($row = mysqli_fetch_assoc($tahun)) { ?>
                                        <option value="<?php echo $row['id']; ?>">
                                            <?php echo htmlspecialchars($row['nama']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <!-- Kelas -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kelas</label>
                                <select name="kelas_id" class="form-select" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    <?php while ($row = mysqli_fetch_assoc($kelas)) { ?>
                                        <option value="<?php echo $row['id']; ?>">
                                            <?php echo htmlspecialchars($row['nama']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <!-- Guru -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Guru</label>
                                <select name="guru_id" class="form-select" required>
                                    <option value="">-- Pilih Guru --</option>
                                    <?php while ($row = mysqli_fetch_assoc($guru)) { ?>
                                        <option value="<?php echo $row['id']; ?>">
                                            <?php echo htmlspecialchars($row['nama']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <!-- Status Aktif -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status_aktif" class="form-select" required>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>

                            <!-- Tanggal Mulai -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Mulai</label>
                                <input type="date" name="tanggal_mulai" class="form-control" required>
                            </div>

                            <!-- Tanggal Selesai -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Selesai</label>
                                <input type="date" name="tanggal_selesai" class="form-control" required>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                    Simpan Wali Kelas
                                </button>
                                <a href="kelola_walikelas.php" class="btn btn-outline-secondary px-4 ms-2">
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