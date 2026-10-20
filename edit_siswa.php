<?php
// edit_siswa.php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Pastikan ID disaring agar aman dari SQL Injection
$id = mysqli_real_escape_string($koneksi, $_GET['id'] ?? '');

$sql = "SELECT * FROM t_siswa WHERE id = '$id'";
$hasil = mysqli_query($koneksi, $sql);

if (!$hasil || mysqli_num_rows($hasil) == 0) {
    header("Location: kelola_siswa.php");
    exit();
}

$data = mysqli_fetch_assoc($hasil);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa</title>

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

            <!-- HEADER Halaman -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h2 m-0">Edit Data Siswa</h1>
            </div>

            <!-- TOMBOL KEMBALI -->
            <div class="mb-4">
                <a href="kelola_siswa.php" class="btn btn-secondary fw-semibold">
                    Kembali ke Kelola Siswa
                </a>
            </div>

            <!-- CARD FORM EDIT SISWA -->
            <div class="card shadow-sm border-0 rounded-3 mb-5">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title m-0 fw-bold">Form Edit Siswa</h5>
                </div>
                <div class="card-body p-4">
                    
                    <form action="proses_edit_siswa.php" method="POST">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($data['id']); ?>">

                        <div class="row g-3">
                            
                            <!-- NIS -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NIS</label>
                                <input type="text" 
                                       name="nis" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($data['nis']); ?>" 
                                       required>
                            </div>

                            <!-- NISN -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NISN</label>
                                <input type="text" 
                                       name="nisn" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($data['nisn']); ?>" 
                                       required>
                            </div>

                            <!-- Nama Siswa -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Siswa</label>
                                <input type="text" 
                                       name="nama" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($data['nama']); ?>" 
                                       required>
                            </div>

                            <!-- Jenis Kelamin -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="form-select" required>
                                    <option value="L" <?= ($data['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>
                                        Laki-laki
                                    </option>
                                    <option value="P" <?= ($data['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>
                                        Perempuan
                                    </option>
                                </select>
                            </div>

                            <!-- Tanggal Lahir -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Lahir</label>
                                <input type="date" 
                                       name="tanggal_lahir" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($data['tanggal_lahir']); ?>" 
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

                            <!-- Alamat -->
                            <div class="col-12">
                                <label class="form-label fw-semibold">Alamat</label>
                                <textarea name="alamat" 
                                          class="form-control" 
                                          rows="3" 
                                          required><?= htmlspecialchars($data['alamat']); ?></textarea>
                            </div>

                            <!-- Tombol Submit -->
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-warning px-4 fw-semibold text-white">
                                    Update Data Siswa
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