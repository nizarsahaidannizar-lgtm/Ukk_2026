
<?php

include __DIR__ . '/includes/cek_session.php';
include __DIR__ . '/config/koneksi.php';

batasi_akses(['admin']);

// ======================================
// AMBIL ID PENEMPATAN
// ======================================

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['pesan_error'] = "ID penempatan tidak valid!";
    header("Location: penempatan_siswa.php");
    exit;
}

// ======================================
// AMBIL DATA PENEMPATAN
// ======================================

$stmt = mysqli_prepare($koneksi, "
    SELECT *
    FROM t_kelas_siswa
    WHERE id = ?
");

if (!$stmt) {
    die("Query gagal: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$query = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($query);

mysqli_stmt_close($stmt);

if (!$data) {
    $_SESSION['pesan_error'] = "Data penempatan tidak ditemukan!";
    header("Location: penempatan_siswa.php");
    exit;
}

// ======================================
// DATA SISWA
// ======================================

$siswa = mysqli_query($koneksi, "
    SELECT id, nis, nama
    FROM t_siswa
    ORDER BY nama ASC
");

// ======================================
// DATA KELAS
// ======================================

$kelas = mysqli_query($koneksi, "
    SELECT id, nama, tingkat, jurusan
    FROM t_kelas
    ORDER BY nama ASC
");

// ======================================
// DATA TAHUN AJARAN
// ======================================

$tahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    ORDER BY nama ASC
");

if (!$siswa || !$kelas || !$tahun) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Edit Penempatan Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="layout/style.css">

</head>

<body class="bg-light">

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <?php include __DIR__ . '/layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">

            <!-- JUDUL -->
            <h2 class="mb-4">Edit Penempatan Siswa</h2>

            <!-- TOMBOL KEMBALI -->
            <div class="mb-4">

                <a href="penempatan_siswa.php" class="btn btn-secondary">

                    <i class="fas fa-arrow-left me-1"></i>
                    Kembali

                </a>

            </div>

            <!-- CARD FORM -->
            <div class="card border-0 shadow-sm rounded-3">

                <div class="card-header bg-white py-3">

                    <h5 class="fw-bold mb-0">
                        Form Edit Penempatan Siswa
                    </h5>

                </div>

                <div class="card-body p-4">

                    <form action="proses_edit_penempatan.php" method="POST">

                        <input type="hidden" name="id"
                               value="<?= (int)$data['id']; ?>">

                        <div class="row g-3">

                            <!-- SISWA -->
                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Nama Siswa
                                </label>

                                <select name="siswa_id"
                                        class="form-select"
                                        required>

                                    <option value="">Pilih Siswa</option>

                                    <?php while ($s = mysqli_fetch_assoc($siswa)) { ?>

                                        <option value="<?= (int)$s['id']; ?>"
                                            <?= $s['id'] == $data['siswa_id'] ? 'selected' : ''; ?>>

                                            <?= htmlspecialchars($s['nis'].' - '.$s['nama']); ?>

                                        </option>

                                    <?php } ?>

                                </select>

                            </div>

                            <!-- KELAS -->
                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Kelas
                                </label>

                                <select name="kelas_id"
                                        class="form-select"
                                        required>

                                    <option value="">Pilih Kelas</option>

                                    <?php while ($k = mysqli_fetch_assoc($kelas)) { ?>

                                        <option value="<?= (int)$k['id']; ?>"
                                            <?= $k['id'] == $data['kelas_id'] ? 'selected' : ''; ?>>

                                            <?= htmlspecialchars(
                                                $k['nama'].' - Tingkat '.
                                                $k['tingkat'].' - '.
                                                $k['jurusan']
                                            ); ?>

                                        </option>

                                    <?php } ?>

                                </select>

                            </div>

                            <!-- TAHUN AJARAN -->
                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Tahun Ajaran
                                </label>

                                <select name="tahun_ajaran_id"
                                        class="form-select"
                                        required>

                                    <option value="">Pilih Tahun Ajaran</option>

                                    <?php while ($t = mysqli_fetch_assoc($tahun)) { ?>

                                        <option value="<?= (int)$t['id']; ?>"
                                            <?= $t['id'] == $data['tahun_ajaran_id'] ? 'selected' : ''; ?>>

                                            <?= htmlspecialchars($t['nama']); ?>

                                        </option>

                                    <?php } ?>

                                </select>

                            </div>

                            <!-- STATUS -->
                            <div class="col-md-6">

                                <label class="form-label fw-semibold">
                                    Status Aktif
                                </label>

                                <select name="status_aktif"
                                        class="form-select"
                                        required>

                                    <option value="1"
                                        <?= $data['status_aktif'] == 1 ? 'selected' : ''; ?>>
                                        Aktif
                                    </option>

                                    <option value="0"
                                        <?= $data['status_aktif'] == 0 ? 'selected' : ''; ?>>
                                        Tidak Aktif
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
                                       value="<?= htmlspecialchars($data['tanggal_mulai'] ?? ''); ?>"
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
                                       value="<?= htmlspecialchars($data['tanggal_selesai'] ?? ''); ?>">

                            </div>

                            <!-- TOMBOL -->
                            <div class="col-12 mt-4">

                                <button type="submit"
                                        class="btn btn-warning px-4">

                                    <i class="fas fa-save me-1"></i>
                                    Simpan

                                </button>

                                <a href="penempatan_siswa.php"
                                   class="btn btn-outline-secondary px-4">

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
