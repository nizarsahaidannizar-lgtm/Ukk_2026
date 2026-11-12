
<?php
// guru/kelas_saya.php

// ======================================
// INCLUDE FILE
// ======================================

include __DIR__ . '/../includes/cek_session.php';
include __DIR__ . '/../config/koneksi.php';

// ======================================
// CEK ROLE
// ======================================

$role = normalisasi_role($_SESSION['role'] ?? '');

batasi_akses(['guru_wali_kelas']);

// ======================================
// AMBIL ID USER LOGIN
// ======================================

$id_user = (int)($_SESSION['id_user'] ?? 0);

if ($id_user <= 0) {
    die("ID akun tidak ditemukan. Silakan login kembali.");
}

// ======================================
// AMBIL DATA KELAS WALI
// ======================================

$sql_kelas = "SELECT
                tw.id,
                tw.kelas_id,
                tw.tahun_ajaran_id,
                k.nama AS nama_kelas,
                ta.nama AS tahun_ajaran
              FROM t_wali_kelas tw
              INNER JOIN t_guru g
                ON tw.guru_id = g.id
              INNER JOIN t_kelas k
                ON tw.kelas_id = k.id
              LEFT JOIN t_tahun_ajaran ta
                ON tw.tahun_ajaran_id = ta.id
              WHERE g.user_id = ?
                AND tw.status_aktif = 1
              ORDER BY k.nama ASC";

$stmt_kelas = mysqli_prepare($koneksi, $sql_kelas);

if (!$stmt_kelas) {
    die("Gagal menyiapkan data kelas: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt_kelas, "i", $id_user);
mysqli_stmt_execute($stmt_kelas);

$hasil_kelas = mysqli_stmt_get_result($stmt_kelas);

if (!$hasil_kelas) {
    die("Gagal mengambil data kelas: " . mysqli_error($koneksi));
}

$daftar_kelas = [];

while ($kelas = mysqli_fetch_assoc($hasil_kelas)) {
    $daftar_kelas[] = $kelas;
}

mysqli_stmt_close($stmt_kelas);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Kelas Saya - SIP</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DATATABLES -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <!-- CSS UTAMA -->
    <link rel="stylesheet" href="../layout/style.css">

</head>

<body class="bg-light">

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <?php include __DIR__ . '/../layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4 guru-content">

            <!-- HEADER -->
            <header class="admin-page-header mb-4">

                <h1>Kelas Saya</h1>

                <p class="text-muted">
                    Informasi kelas dan daftar siswa yang menjadi tanggung jawab Anda.
                </p>

            </header>

            <?php if (count($daftar_kelas) > 0): ?>

                <?php foreach ($daftar_kelas as $kelas): ?>

                    <?php

                    // ======================================
                    // AMBIL SISWA DALAM KELAS
                    // ======================================

                    $sql_siswa = "SELECT
                                    s.id,
                                    s.nisn,
                                    s.nama
                                  FROM t_kelas_siswa ks
                                  INNER JOIN t_siswa s
                                    ON ks.siswa_id = s.id
                                  WHERE ks.kelas_id = ?
                                    AND ks.tahun_ajaran_id = ?
                                    AND ks.status_aktif = 1
                                  ORDER BY s.nama ASC";

                    $stmt_siswa = mysqli_prepare($koneksi, $sql_siswa);

                    if (!$stmt_siswa) {
                        die("Gagal menyiapkan data siswa: " . mysqli_error($koneksi));
                    }

                    $kelas_id = (int)$kelas['kelas_id'];
                    $tahun_ajaran_id = (int)$kelas['tahun_ajaran_id'];

                    mysqli_stmt_bind_param(
                        $stmt_siswa,
                        "ii",
                        $kelas_id,
                        $tahun_ajaran_id
                    );

                    mysqli_stmt_execute($stmt_siswa);

                    $hasil_siswa = mysqli_stmt_get_result($stmt_siswa);

                    if (!$hasil_siswa) {
                        die("Gagal mengambil data siswa: " . mysqli_error($koneksi));
                    }

                    $jumlah_siswa = mysqli_num_rows($hasil_siswa);

                    ?>

                    <!-- CARD KELAS -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">

                        <div class="card-header bg-white py-3">

                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                                <div>

                                    <h5 class="fw-bold mb-1">
                                        <i class="fas fa-school me-2 text-primary"></i>
                                        <?= htmlspecialchars($kelas['nama_kelas']) ?>
                                    </h5>

                                    <small class="text-muted">
                                        Tahun Ajaran:
                                        <?= htmlspecialchars($kelas['tahun_ajaran'] ?? '-') ?>
                                    </small>

                                </div>

                                <span class="badge bg-primary px-3 py-2">
                                    <?= $jumlah_siswa ?> Siswa
                                </span>

                            </div>

                        </div>

                        <div class="card-body">

                            <div class="table-responsive">

                                <table class="table table-striped table-hover align-middle">

                                    <thead class="table-dark">

                                        <tr>
                                            <th class="text-center" width="60">No.</th>
                                            <th>NISN</th>
                                            <th>Nama Siswa</th>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        <?php if ($jumlah_siswa > 0): ?>

                                            <?php
                                            $no = 1;

                                            while ($siswa = mysqli_fetch_assoc($hasil_siswa)):
                                            ?>

                                                <tr>

                                                    <td class="text-center">
                                                        <?= $no++ ?>
                                                    </td>

                                                    <td>
                                                        <?= htmlspecialchars($siswa['nisn'] ?? '-') ?>
                                                    </td>

                                                    <td class="fw-semibold">
                                                        <?= htmlspecialchars($siswa['nama'] ?? '-') ?>
                                                    </td>

                                                </tr>

                                            <?php endwhile; ?>

                                        <?php else: ?>

                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-3">
                                                    Belum ada siswa dalam kelas ini.
                                                </td>
                                            </tr>

                                        <?php endif; ?>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                    <?php mysqli_stmt_close($stmt_siswa); ?>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="alert alert-warning border-0 shadow-sm">

                    <i class="fas fa-exclamation-circle me-2"></i>

                    Anda belum memiliki penempatan sebagai wali kelas yang aktif.

                </div>

            <?php endif; ?>

        </main>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
