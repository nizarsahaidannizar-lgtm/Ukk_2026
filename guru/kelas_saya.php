<?php
// guru/kelas_saya.php

require_once __DIR__ . '/../includes/cek_session.php';
require_once __DIR__ . '/../config/koneksi.php';

// ========================================
// CEK ROLE GURU WALI KELAS
// ========================================

$role = strtolower(trim($_SESSION['role'] ?? ''));

if (function_exists('normalisasi_role')) {
    $role = normalisasi_role($role);
}

if ($role !== 'guru_wali_kelas') {
    $_SESSION['pesan_error'] = 'Akses Kelas Saya hanya diberikan kepada Guru Wali Kelas.';
    header('Location: dashboard_guru.php');
    exit;
}

// ========================================
// AMBIL IDENTITAS AKUN LOGIN
// ========================================

$user_id = (int)(
    $_SESSION['id_user']
    ?? $_SESSION['user_id']
    ?? $_SESSION['id']
    ?? 0
);

if ($user_id <= 0) {
    $_SESSION['pesan_error'] = 'ID pengguna tidak ditemukan. Silakan login kembali.';
    header('Location: ../logout.php');
    exit;
}

// ========================================
// AMBIL DATA GURU YANG TERHUBUNG
// ========================================

$stmt_guru = mysqli_prepare(
    $koneksi,
    "SELECT id, nama
     FROM t_guru
     WHERE user_id = ?
       AND status_aktif = 1
     LIMIT 1"
);

if (!$stmt_guru) {
    die('Query data guru gagal: ' . htmlspecialchars(mysqli_error($koneksi)));
}

mysqli_stmt_bind_param($stmt_guru, 'i', $user_id);
mysqli_stmt_execute($stmt_guru);

$data_guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_guru));
mysqli_stmt_close($stmt_guru);

if (!$data_guru) {
    die('Data guru aktif belum terhubung dengan akun ini. Periksa relasi t_guru.user_id.');
}

$guru_id = (int)$data_guru['id'];
$nama_guru = $data_guru['nama'] ?? ($_SESSION['nama'] ?? 'Guru');

// ========================================
// AMBIL SEMUA KELAS WALI PADA TAHUN AJARAN AKTIF
// RELASI MENGGUNAKAN USER_ID DAN GURU_ID
// ========================================

$sql_kelas = "
    SELECT DISTINCT
        tw.id AS penugasan_id,
        tw.kelas_id,
        tw.tahun_ajaran_id,
        k.nama AS nama_kelas,
        ta.nama AS tahun_ajaran
    FROM t_wali_kelas tw
    INNER JOIN t_kelas k
        ON k.id = tw.kelas_id
    INNER JOIN t_tahun_ajaran ta
        ON ta.id = tw.tahun_ajaran_id
    WHERE tw.status_aktif = 1
      AND ta.status_aktif = 1
      AND tw.kelas_id IS NOT NULL
      AND (
            tw.user_id = ?
            OR tw.guru_id = ?
      )
    ORDER BY k.nama ASC
";

$stmt_kelas = mysqli_prepare($koneksi, $sql_kelas);

if (!$stmt_kelas) {
    die('Query kelas gagal: ' . htmlspecialchars(mysqli_error($koneksi)));
}

mysqli_stmt_bind_param($stmt_kelas, 'ii', $user_id, $guru_id);
mysqli_stmt_execute($stmt_kelas);

$result_kelas = mysqli_stmt_get_result($stmt_kelas);
$data_kelas = [];

while ($kelas = mysqli_fetch_assoc($result_kelas)) {

    // ========================================
    // AMBIL SISWA AKTIF DI KELAS DAN TAHUN AJARAN YANG SAMA
    // ========================================

    $sql_siswa = "
        SELECT
            s.id,
            s.nisn,
            s.nama
        FROM t_kelas_siswa ks
        INNER JOIN t_siswa s
            ON s.id = ks.siswa_id
        WHERE ks.kelas_id = ?
          AND ks.tahun_ajaran_id = ?
          AND ks.status_aktif = 1
          AND s.status_aktif = 1
        ORDER BY s.nama ASC
    ";

    $stmt_siswa = mysqli_prepare($koneksi, $sql_siswa);

    if (!$stmt_siswa) {
        die('Query siswa gagal: ' . htmlspecialchars(mysqli_error($koneksi)));
    }

    $kelas_id = (int)$kelas['kelas_id'];
    $tahun_ajaran_id = (int)$kelas['tahun_ajaran_id'];

    mysqli_stmt_bind_param($stmt_siswa, 'ii', $kelas_id, $tahun_ajaran_id);
    mysqli_stmt_execute($stmt_siswa);

    $result_siswa = mysqli_stmt_get_result($stmt_siswa);
    $siswa = [];

    while ($row = mysqli_fetch_assoc($result_siswa)) {
        $siswa[] = $row;
    }

    mysqli_stmt_close($stmt_siswa);

    $kelas['siswa'] = $siswa;
    $kelas['jumlah_siswa'] = count($siswa);

    $data_kelas[] = $kelas;
}

mysqli_stmt_close($stmt_kelas);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelas Saya</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../layout/style.css">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/../layout/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 p-4 guru-content">
            <div class="mb-4">
                <h3 class="fw-bold mb-1">
                    <i class="fa-solid fa-chalkboard-user me-2"></i>
                    Kelas Saya
                </h3>
                <p class="text-muted mb-0">
                    Daftar kelas dan siswa yang menjadi tanggung jawab Anda pada tahun ajaran aktif.
                </p>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Wali Kelas</h6>
                    <h5 class="fw-bold mb-0"><?= htmlspecialchars($nama_guru); ?></h5>
                </div>
            </div>

            <?php if (empty($data_kelas)): ?>
                <div class="alert alert-warning">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    Belum ada penugasan wali kelas aktif untuk akun ini pada tahun ajaran aktif.
                    Silakan periksa menu Kelola Wali Kelas dan pastikan akun guru memiliki role Guru Wali Kelas.
                </div>
            <?php else: ?>

                <?php foreach ($data_kelas as $kelas): ?>
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h5 class="fw-bold mb-1">
                                        <i class="fa-solid fa-users-rectangle text-primary me-2"></i>
                                        <?= htmlspecialchars($kelas['nama_kelas']); ?>
                                    </h5>
                                    <small class="text-muted">
                                        Tahun Ajaran: <?= htmlspecialchars($kelas['tahun_ajaran'] ?? '-'); ?>
                                    </small>
                                </div>
                                <span class="badge bg-primary">
                                    <?= (int)$kelas['jumlah_siswa']; ?> Siswa
                                </span>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 70px;">No</th>
                                            <th>NISN</th>
                                            <th>Nama Siswa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($kelas['siswa'])): ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">
                                                    Belum ada siswa aktif yang ditempatkan pada kelas dan tahun ajaran ini.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $no = 1; ?>
                                            <?php foreach ($kelas['siswa'] as $siswa): ?>
                                                <tr>
                                                    <td><?= $no++; ?></td>
                                                    <td><?= htmlspecialchars($siswa['nisn'] ?? '-'); ?></td>
                                                    <td class="fw-semibold"><?= htmlspecialchars($siswa['nama'] ?? '-'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php endif; ?>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
