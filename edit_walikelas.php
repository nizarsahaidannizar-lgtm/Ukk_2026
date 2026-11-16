
<?php
// edit_walikelas.php
include 'includes/cek_session.php';
include 'config/koneksi.php';

$id = (int) ($_GET['id'] ?? 0);

// Ambil data wali kelas
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM t_wali_kelas WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$hasil = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($hasil);
mysqli_stmt_close($stmt);

if (!$data) {
    header("Location: kelola_walikelas.php");
    exit();
}

// ==================================================
// AMBIL TAHUN AJARAN YANG SEDANG AKTIF
// ==================================================

$tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_tahun_ajaran
     WHERE status_aktif = 1
     ORDER BY tanggal_mulai DESC, id DESC
     LIMIT 1"
);

$tahun_aktif = ($tahun) ? mysqli_fetch_assoc($tahun) : null;

// AMBIL SEMUA KELAS
$kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_kelas ORDER BY nama ASC"
);

// AMBIL SEMUA GURU
$guru = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_guru ORDER BY nama ASC"
);
?>

<!DOCTYPE html>
<html lang="id">

<head>
<meta charset="UTF-8">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Wali Kelas</title>

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
                <h1 class="h2 m-0">Edit Data Wali Kelas</h1>
            </div>

            <!-- TOMBOL KEMBALI -->
            <div class="mb-4">
                <a href="kelola_walikelas.php" class="btn btn-secondary fw-semibold">
                    Kembali
                </a>
            </div>

            <!-- CARD FORM EDIT WALI KELAS -->
            <div class="card shadow-sm border-0 rounded-3 mb-5">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title m-0 fw-bold">Form Edit Wali Kelas</h5>
                </div>
                <div class="card-body p-4">

                    <?php if (!$tahun_aktif) { ?>

                        <div class="alert alert-danger">
                            Tidak ada tahun ajaran yang aktif.
                            Silakan aktifkan tahun ajaran terlebih dahulu.
                        </div>

                    <?php } else { ?>

                    <form action="proses_edit_walikelas.php" method="POST">

                        <input type="hidden" name="id" value="<?= (int)$data['id']; ?>">

                        <!-- TAHUN AJARAN OTOMATIS -->
                        <input type="hidden"
                               name="tahun_ajaran_id"
                               value="<?= (int)$tahun_aktif['id']; ?>">

                        <div class="row g-3">

                            <!-- Tahun Ajaran -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tahun Ajaran</label>

                                <input type="text"
                                       class="form-control"
                                       value="<?= htmlspecialchars($tahun_aktif['nama']); ?>"
                                       readonly>

                                <small class="text-muted">
                                    Tahun ajaran mengikuti tahun ajaran yang sedang aktif.
                                </small>
                            </div>

                            <!-- Kelas -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kelas</label>
                                <select name="kelas_id" class="form-select" required>
                                    <?php while ($row = mysqli_fetch_assoc($kelas)) { ?>
                                        <option value="<?= (int)$row['id']; ?>" <?= ($row['id'] == $data['kelas_id']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($row['nama']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <!-- Guru -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Guru</label>
                                <select name="guru_id" class="form-select" required>
                                    <?php while ($row = mysqli_fetch_assoc($guru)) { ?>
                                        <option value="<?= (int)$row['id']; ?>" <?= ($row['id'] == $data['guru_id']) ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($row['nama']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <!-- Status Aktif -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status_aktif" class="form-select" required>
                                    <option value="1" <?= ($data['status_aktif'] == 1) ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="0" <?= ($data['status_aktif'] == 0) ? 'selected' : ''; ?>>Tidak Aktif</option>
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
                                    Simpan
                                </button>
                                <a href="kelola_walikelas.php" class="btn btn-outline-secondary px-4 ms-2">
                                    Kembali
                                </a>
                            </div>

                        </div>
                    </form>

                    <?php } ?>

                </div>
            </div>

        </main>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/bootstrap.bundle.min.js"></script>

</body>
</html>
