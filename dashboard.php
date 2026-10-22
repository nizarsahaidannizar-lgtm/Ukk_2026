
<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

batasi_akses(['admin']);

$role = strtolower($_SESSION['role'] ?? '');
$lingkup = '1 = 1';

if ($role === 'guru') {
    include_once 'includes/akses_pelanggaran.php';
    $konteks = konteks_akses_pelanggaran($koneksi);
    $lingkup = lingkup_sql_pelanggaran($konteks, 'ps');
}

if ($role === 'admin') {
    $q_siswa = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_siswa");
    $total_siswa = $q_siswa ? (int)mysqli_fetch_assoc($q_siswa)['total'] : 0;

    $q_guru = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_guru");
    $total_guru = $q_guru ? (int)mysqli_fetch_assoc($q_guru)['total'] : 0;

    $q_kat = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_pelanggaran_kategori");
    $total_kat = $q_kat ? (int)mysqli_fetch_assoc($q_kat)['total'] : 0;

    $q_wk = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_wali_kelas");
    $total_wk = $q_wk ? (int)mysqli_fetch_assoc($q_wk)['total'] : 0;

    $q_pelanggaran = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_pelanggaran_siswa");
    $total_pelanggaran = $q_pelanggaran ? (int)mysqli_fetch_assoc($q_pelanggaran)['total'] : 0;
    $total_tindak_lanjut = 0;
    $total_poin = 0;
} else {
    $q_siswa = mysqli_query(
        $koneksi,
        "SELECT COUNT(DISTINCT ps.siswa_id) AS total FROM t_pelanggaran_siswa ps WHERE $lingkup"
    );
    $total_siswa = $q_siswa ? (int)mysqli_fetch_assoc($q_siswa)['total'] : 0;

    $q_pelanggaran = mysqli_query(
        $koneksi,
        "SELECT COUNT(*) AS total FROM t_pelanggaran_siswa ps WHERE $lingkup"
    );
    $total_pelanggaran = $q_pelanggaran ? (int)mysqli_fetch_assoc($q_pelanggaran)['total'] : 0;

    $q_tindak_lanjut = mysqli_query(
        $koneksi,
        "SELECT COUNT(*) AS total FROM t_pelanggaran_siswa ps
         WHERE $lingkup AND ps.status <> 'Selesai'"
    );
    $total_tindak_lanjut = $q_tindak_lanjut ? (int)mysqli_fetch_assoc($q_tindak_lanjut)['total'] : 0;

    $q_poin = mysqli_query(
        $koneksi,
        "SELECT COALESCE(SUM(ps.poin), 0) AS total FROM t_pelanggaran_siswa ps WHERE $lingkup"
    );
    $total_poin = $q_poin ? (int)mysqli_fetch_assoc($q_poin)['total'] : 0;
}

// STATISTIK PELANGGARAN PER BULAN
$label_bulan = [];
$data_bulan = [];

$nama_bulan = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

// Asumsi nama kolom tanggal adalah tanggal
$q_bulan = mysqli_query($koneksi, "
        SELECT MONTH(ps.tanggal) AS bulan, COUNT(*) AS total
        FROM t_pelanggaran_siswa ps
        WHERE ps.tanggal IS NOT NULL
            AND YEAR(ps.tanggal) = YEAR(CURDATE())
            AND $lingkup
        GROUP BY MONTH(ps.tanggal)
        ORDER BY MONTH(ps.tanggal)
");

if ($q_bulan) {
    while ($row = mysqli_fetch_assoc($q_bulan)) {
        $label_bulan[] = $nama_bulan[(int)$row['bulan']];
        $data_bulan[] = (int)$row['total'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="layout/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">

            <!-- HEADER -->
            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                <div class="bg-white p-3 rounded-3 shadow-sm me-3 border">
                    <i class="fa-solid fa-school fs-2 text-dark"></i>
                </div>
                <div>
                    <h1 class="h2 fw-bold text-dark m-0">SIP</h1>
                    <p class="text-muted m-0 small">
                        Sistem Informasi Pelanggaran Siswa
                    </p>
                </div>
            </div>

            <!-- SELAMAT DATANG -->
            <div class="alert alert-primary alert-dismissible fade show mb-4 shadow-sm">
                Selamat Datang di <strong>SIP (Sistem Informasi Pelanggaran)</strong>,
                Anda login sebagai
                <strong><?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?></strong>.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

            <!-- KARTU STATISTIK -->
            <div class="row g-3 mb-4">

                <div class="col-md-4 col-xl">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3">
                                <i class="fa-solid fa-user-graduate fs-3"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Total Siswa</p>
                                <h3 class="fw-bold mb-0"><?= $total_siswa ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($role === 'admin'): ?>
                <div class="col-md-4 col-xl">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 text-success p-3 rounded-3 me-3">
                                <i class="fa-solid fa-chalkboard-user fs-3"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Total Guru</p>
                                <h3 class="fw-bold mb-0"><?= $total_guru ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($role === 'admin'): ?>
                <div class="col-md-4 col-xl">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3 me-3">
                                <i class="fa-solid fa-list-check fs-3"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Kategori Pelanggaran</p>
                                <h3 class="fw-bold mb-0"><?= $total_kat ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-xl">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-info bg-opacity-10 text-info p-3 rounded-3 me-3">
                                <i class="fa-solid fa-user-shield fs-3"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Wali Kelas</p>
                                <h3 class="fw-bold mb-0"><?= $total_wk ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="col-md-4 col-xl">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-3 me-3">
                                <i class="fa-solid fa-triangle-exclamation fs-3"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Total Pelanggaran</p>
                                <h3 class="fw-bold mb-0"><?= $total_pelanggaran ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($role === 'guru'): ?>
                <div class="col-md-4 col-xl">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3 me-3">
                                <i class="fa-solid fa-hourglass-half fs-3"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Perlu Tindak Lanjut</p>
                                <h3 class="fw-bold mb-0"><?= $total_tindak_lanjut ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-xl">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-info bg-opacity-10 text-info p-3 rounded-3 me-3">
                                <i class="fa-solid fa-star fs-3"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Total Poin</p>
                                <h3 class="fw-bold mb-0"><?= $total_poin ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- GRAFIK PELANGGARAN -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-0 p-3">
                    <h5 class="fw-bold mb-1">
                        <i class="fa-solid fa-chart-column text-primary me-2"></i>
                        Statistik Pelanggaran Siswa
                    </h5>
                    <small class="text-muted">
                        Jumlah pelanggaran setiap bulan pada tahun berjalan
                    </small>
                </div>

                <div class="card-body">
                    <div style="position: relative; height: 350px;">
                        <canvas id="grafikPelanggaran"></canvas>
                    </div>

                    <?php if (empty($data_bulan)): ?>
                        <p class="text-muted text-center mt-3 mb-0">
                            Belum ada data pelanggaran pada tahun ini.
                        </p>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
const labelBulan = <?= json_encode($label_bulan) ?>;
const dataBulan = <?= json_encode($data_bulan) ?>;

new Chart(document.getElementById('grafikPelanggaran'), {
    type: 'bar',
    data: {
        labels: labelBulan,
        datasets: [{
            label: 'Jumlah Pelanggaran',
            data: dataBulan,
            backgroundColor: 'rgba(13, 110, 253, 0.7)',
            borderColor: 'rgba(13, 110, 253, 1)',
            borderWidth: 1,
            borderRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        },
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

</body>
</html>
```