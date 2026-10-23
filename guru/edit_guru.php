<?php
// edit_guru.php
include '../includes/cek_session.php';
include '../config/koneksi.php';

// Pastikan parameter ID disaring agar aman
$id = mysqli_real_escape_string($koneksi, $_GET['id'] ?? '');

$sql = "SELECT * FROM t_guru WHERE id = '$id'";
$hasil = mysqli_query($koneksi, $sql);

if (!$hasil || mysqli_num_rows($hasil) == 0) {
    header("Location: kelola_guru.php");
    exit();
}

$data = mysqli_fetch_assoc($hasil);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Guru</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../layout/style.css">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include '../layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">

            <!-- HEADER HALAMAN -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h2 m-0">Edit Data Guru</h1>
            </div>

            <!-- TOMBOL KEMBALI -->
            <div class="mb-4">
                <a href="kelola_guru.php" class="btn btn-secondary fw-semibold">
                    Kembali ke Kelola Guru
                </a>
            </div>

            <!-- CARD FORM EDIT GURU -->
            <div class="card shadow-sm border-0 rounded-3 mb-5">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title m-0 fw-bold">Form Edit Data Guru</h5>
                </div>
                <div class="card-body p-4">
                    
                    <form action="proses_edit_guru.php" method="POST">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($data['id']); ?>">

                        <div class="row g-3">
                            
                            <!-- NIP -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NIP</label>
                                <input type="text" 
                                       name="nip" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($data['nip']); ?>" 
                                       required>
                            </div>

                            <!-- Nama Guru -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Guru</label>
                                <input type="text" 
                                       name="nama" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($data['nama']); ?>" 
                                       required>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" 
                                       name="email" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($data['email']); ?>" 
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

                            <!-- User ID -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">User ID</label>
                                <input type="number" 
                                       name="user_id" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($data['user_id'] ?? ''); ?>" 
                                       placeholder="Opsional">
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-warning px-4 fw-semibold text-white">
                                    Update Data Guru
                                </button>
                                <a href="kelola_guru.php" class="btn btn-outline-secondary px-4 ms-2">
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