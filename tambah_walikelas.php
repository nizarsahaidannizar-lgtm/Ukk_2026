
<?php
// tambah_walikelas.php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// HANYA ADMIN YANG BISA MENGAKSES
batasi_akses(['admin']);

// AMBIL TAHUN AJARAN YANG AKTIF
$tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM t_tahun_ajaran
     WHERE status_aktif = 1
     ORDER BY tanggal_mulai DESC, id DESC
     LIMIT 1"
);

$tahun_aktif = ($tahun) ? mysqli_fetch_assoc($tahun) : null;

// AMBIL SEMUA KELAS
$kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM t_kelas
     ORDER BY nama ASC"
);

// AMBIL SEMUA GURU
$guru = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM t_guru
     ORDER BY nama ASC"
);
?>

<!DOCTYPE html>
<html lang="id">

<head>
<meta charset="UTF-8">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Wali Kelas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="layout/style.css">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">

            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h2 m-0">Tambah Wali Kelas</h1>
            </div>

            <!-- KEMBALI -->
            <div class="mb-4">
                <a href="kelola_walikelas.php" class="btn btn-secondary fw-semibold">
                    <i class="fas fa-arrow-left me-1"></i>
                    Kembali
                </a>
            </div>

            <!-- FORM -->
            <div class="card shadow-sm border-0 rounded-3 mb-5">

                <div class="card-header bg-white py-3">
                    <h5 class="card-title m-0 fw-bold">
                        Form Input Wali Kelas Baru
                    </h5>
                </div>

                <div class="card-body p-4">

                    <?php if (!$tahun_aktif) { ?>

                        <div class="alert alert-danger">
                            Tidak ada tahun ajaran yang aktif.
                            Silakan aktifkan tahun ajaran terlebih dahulu.
                        </div>

                    <?php } else { ?>

                    <form action="proses_tambah_walikelas.php" method="POST">

                        <!-- TAHUN AJARAN OTOMATIS -->
                        <input type="hidden"
                               name="tahun_ajaran_id"
                               value="<?= (int)$tahun_aktif['id']; ?>">

                        <div class="row g-3">

                            <!-- TAHUN AJARAN -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Tahun Ajaran
                                </label>

                                <input type="text"
                                       class="form-control"
                                       value="<?= htmlspecialchars($tahun_aktif['nama']); ?>"
                                       readonly>

                                <small class="text-muted">
                                    Tahun ajaran diambil otomatis dari tahun ajaran aktif.
                                </small>
                            </div>

                            <!-- KELAS -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Kelas
                                </label>

                                <select name="kelas_id" class="form-select" required>
                                    <option value="">-- Pilih Kelas --</option>

                                    <?php if ($kelas) { ?>
                                        <?php while ($row = mysqli_fetch_assoc($kelas)) { ?>
                                            <option value="<?= (int)$row['id']; ?>">
                                                <?= htmlspecialchars($row['nama']); ?>
                                            </option>
                                        <?php } ?>
                                    <?php } ?>

                                </select>
                            </div>

                            <!-- GURU -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Guru
                                </label>

                                <select name="guru_id" class="form-select" required>
                                    <option value="">-- Pilih Guru --</option>

                                    <?php if ($guru) { ?>
                                        <?php while ($row = mysqli_fetch_assoc($guru)) { ?>
                                            <option value="<?= (int)$row['id']; ?>">
                                                <?= htmlspecialchars($row['nama']); ?>
                                            </option>
                                        <?php } ?>
                                    <?php } ?>

                                </select>
                            </div>

                            <!-- STATUS -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Status
                                </label>

                                <select name="status_aktif" class="form-select" required>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
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
                                       required>
                            </div>

                            <!-- TOMBOL -->
                            <div class="col-12 mt-4">

                                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                    <i class="fas fa-save me-1"></i>
                                    Simpan
                                </button>

                                <a href="kelola_walikelas.php"
                                   class="btn btn-outline-secondary px-4 ms-2">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
