
<?php
// edit_tahun_ajaran.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

batasi_akses(['admin']);

// VALIDASI ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: kelola_tahun_ajaran.php");
    exit();
}

// AMBIL DATA TAHUN AJARAN
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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="layout/style.css">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">

            <!-- HEADER -->
            <div class="mb-3">
                <h1 class="h2">Edit Data Tahun Ajaran</h1>
            </div>

            <!-- TOMBOL KEMBALI -->
            <div class="mb-4">
                <a href="kelola_tahun_ajaran.php" class="btn btn-secondary fw-semibold">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <!-- FORM EDIT -->
            <div class="card shadow-sm border-0 rounded-3 mb-5">

                <div class="card-header bg-white py-3">
                    <h5 class="card-title m-0 fw-bold">
                        Form Edit Tahun Ajaran
                    </h5>
                </div>

                <div class="card-body p-4">

                    <form action="proses_edit_tahun_ajaran.php" method="POST">

                        <input type="hidden" name="id"
                               value="<?= (int)$data['id']; ?>">

                        <div class="row g-3">

                            <!-- NAMA TAHUN AJARAN -->
                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Nama Tahun Ajaran
                                </label>

                                <input type="text"
                                       name="nama"
                                       class="form-control"
                                       value="<?= htmlspecialchars($data['nama']); ?>"
                                       maxlength="9"
                                       pattern="[0-9]{4}/[0-9]{4}"
                                       placeholder="Contoh: 2026/2027"
                                       required>

                            </div>

                            <!-- STATUS -->
                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Status Tahun Ajaran
                                </label>

                                <select name="status_aktif"
                                        class="form-select"
                                        required>

                                    <option value="1"
                                        <?= ((int)$data['status_aktif'] === 1) ? 'selected' : ''; ?>>
                                        Aktif
                                    </option>

                                    <option value="0"
                                        <?= ((int)$data['status_aktif'] === 0) ? 'selected' : ''; ?>>
                                        Nonaktif
                                    </option>

                                </select>

                            </div>

                            <!-- TANGGAL MULAI -->
                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Tanggal Mulai
                                </label>

                                <input type="date"
                                       name="tanggal_mulai"
                                       class="form-control"
                                       value="<?= htmlspecialchars($data['tanggal_mulai']); ?>"
                                       required>

                            </div>

                            <!-- TANGGAL SELESAI -->
                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Tanggal Selesai
                                </label>

                                <input type="date"
                                       name="tanggal_selesai"
                                       class="form-control"
                                       value="<?= htmlspecialchars($data['tanggal_selesai']); ?>"
                                       required>

                            </div>

                            <!-- TOMBOL SIMPAN -->
                            <div class="col-12 mt-4">

                                <button type="submit"
                                        class="btn btn-warning px-4 fw-semibold text-white">

                                    <i class="fas fa-save me-1"></i>
                                    Simpan Perubahan

                                </button>

                                <a href="kelola_tahun_ajaran.php"
                                   class="btn btn-outline-secondary px-4 ms-2">

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
