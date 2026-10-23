<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

batasi_akses(['admin']);

$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$hari_input = filter_input(INPUT_GET, 'hari', FILTER_VALIDATE_INT);
$bulan_input = filter_input(INPUT_GET, 'bulan', FILTER_VALIDATE_INT);
$tahun_input = filter_input(INPUT_GET, 'tahun', FILTER_VALIDATE_INT);

$filter_hari = ($hari_input >= 1 && $hari_input <= 31) ? $hari_input : '';
$filter_bulan = !array_key_exists('bulan', $_GET)
    ? (int)date('m')
    : (($bulan_input >= 1 && $bulan_input <= 12) ? $bulan_input : '');
$filter_tahun = ($tahun_input >= 2000 && $tahun_input <= (int)date('Y') + 1)
    ? $tahun_input
    : (int)date('Y');

if ($filter_bulan === '') {
    $filter_hari = '';
}

// KONDISI FILTER QUERY
$conditions = ['YEAR(ps.tanggal) = ' . (int)$filter_tahun];
if ($filter_bulan !== '') {
    $conditions[] = 'MONTH(ps.tanggal) = ' . (int)$filter_bulan;
}
if ($filter_hari !== '') {
    $conditions[] = 'DAY(ps.tanggal) = ' . (int)$filter_hari;
}
$where_sql = 'WHERE ' . implode(' AND ', $conditions);

// HITUNG KARTU RINGKASAN
$q_siswa = mysqli_query($koneksi, "SELECT COUNT(DISTINCT ps.siswa_id) AS total FROM t_pelanggaran_siswa ps $where_sql");
$total_siswa = $q_siswa ? (int)mysqli_fetch_assoc($q_siswa)['total'] : 0;

$q_pelanggaran = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_pelanggaran_siswa ps $where_sql");
$total_pelanggaran = $q_pelanggaran ? (int)mysqli_fetch_assoc($q_pelanggaran)['total'] : 0;

$q_tindak_lanjut = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_pelanggaran_siswa ps $where_sql AND ps.status <> 'Selesai'");
$total_tindak_lanjut = $q_tindak_lanjut ? (int)mysqli_fetch_assoc($q_tindak_lanjut)['total'] : 0;

$q_poin = mysqli_query($koneksi, "SELECT COALESCE(SUM(ps.poin), 0) AS total FROM t_pelanggaran_siswa ps $where_sql");
$total_poin = $q_poin ? (int)mysqli_fetch_assoc($q_poin)['total'] : 0;

$q_siswa_terdaftar = mysqli_query($koneksi, 'SELECT COUNT(*) AS total FROM t_siswa');
$total_siswa_terdaftar = $q_siswa_terdaftar ? (int)mysqli_fetch_assoc($q_siswa_terdaftar)['total'] : 0;
$q_guru = mysqli_query($koneksi, 'SELECT COUNT(*) AS total FROM t_guru');
$total_guru = $q_guru ? (int)mysqli_fetch_assoc($q_guru)['total'] : 0;
$q_kat = mysqli_query($koneksi, 'SELECT COUNT(*) AS total FROM t_pelanggaran_kategori');
$total_kat = $q_kat ? (int)mysqli_fetch_assoc($q_kat)['total'] : 0;
$q_wk = mysqli_query($koneksi, 'SELECT COUNT(*) AS total FROM t_wali_kelas');
$total_wk = $q_wk ? (int)mysqli_fetch_assoc($q_wk)['total'] : 0;

// LOGIKA GRAFIK HARIAN/BULANAN
$chart_labels = [];
$chart_data = [];
$chart_title = 'Statistik Pelanggaran';

if ($filter_hari !== '') {
    $chart_labels[] = 'Tgl ' . $filter_hari;
    $chart_data[] = $total_pelanggaran;
    $chart_title = 'Rekap Pelanggaran Tanggal ' . $filter_hari . ' ' . $nama_bulan[(int)$filter_bulan] . ' ' . $filter_tahun;
} elseif ($filter_bulan !== '') {
    $jumlah_hari = cal_days_in_month(CAL_GREGORIAN, (int)$filter_bulan, (int)$filter_tahun);
    $data_hari_temp = array_fill(1, $jumlah_hari, 0);

    $chart_query = mysqli_query(
        $koneksi,
        "SELECT DAY(ps.tanggal) AS tgl, COUNT(*) AS total
         FROM t_pelanggaran_siswa ps
         WHERE YEAR(ps.tanggal) = " . (int)$filter_tahun . "
           AND MONTH(ps.tanggal) = " . (int)$filter_bulan . "
         GROUP BY DAY(ps.tanggal)"
    );

    if ($chart_query) {
        while ($row = mysqli_fetch_assoc($chart_query)) {
            $data_hari_temp[(int)$row['tgl']] = (int)$row['total'];
        }
    }

    for ($d = 1; $d <= $jumlah_hari; $d++) {
        $chart_labels[] = $d;
        $chart_data[] = $data_hari_temp[$d];
    }
    $chart_title = 'Rekap Pelanggaran per Hari - ' . $nama_bulan[(int)$filter_bulan] . ' ' . $filter_tahun;
} else {
    $data_bulan_temp = array_fill(1, 12, 0);

    $chart_query = mysqli_query(
        $koneksi,
        "SELECT MONTH(ps.tanggal) AS bln, COUNT(*) AS total
         FROM t_pelanggaran_siswa ps
         WHERE YEAR(ps.tanggal) = " . (int)$filter_tahun . "
         GROUP BY MONTH(ps.tanggal)"
    );

    if ($chart_query) {
        while ($row = mysqli_fetch_assoc($chart_query)) {
            $data_bulan_temp[(int)$row['bln']] = (int)$row['total'];
        }
    }

    for ($m = 1; $m <= 12; $m++) {
        $chart_labels[] = $nama_bulan[$m];
        $chart_data[] = $data_bulan_temp[$m];
    }
    $chart_title = 'Rekap Pelanggaran per Bulan - ' . $filter_tahun;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIP</title>

    <!-- Bootstrap 5 CSS & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="layout/style.css">
    
    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- BOOTSTRAP SIDEBAR -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA BOOTSTRAP -->
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">

            <!-- HEADER BRANDING -->
            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                <div class="bg-white p-3 rounded-3 shadow-sm me-3 border">
                    <i class="fa-solid fa-school fs-2 text-dark"></i>
                </div>
                <div>
                    <h1 class="h2 fw-bold text-dark m-0">SIP</h1>
                    <p class="text-muted m-0 small">Sistem Informasi Pelanggaran Siswa</p>
                </div>
            </div>

            <!-- BOOTSTRAP ALERT WELCOME -->
            <div class="alert alert-primary alert-dismissible fade show mb-4 shadow-sm border-0" role="alert">
                <i class="fa-solid fa-circle-info me-2"></i> Selamat Datang di <strong>SIP (Sistem Informasi Pelanggaran)</strong>,
                Anda login sebagai <strong><?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?></strong>.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <!-- BOOTSTRAP CARD FILTER FORM -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body py-3">
                    <form method="GET" class="row g-3 align-items-center">
                        <div class="col-auto">
                            <span class="fw-bold text-secondary"><i class="fa-solid fa-filter me-1"></i> Filter Rekap:</span>
                        </div>
                        <div class="col-md-2">
                            <select name="hari" class="form-select form-select-sm">
                                <option value="">-- Semua Hari --</option>
                                <?php for ($h = 1; $h <= 31; $h++): ?>
                                    <option value="<?= $h ?>" <?= ($filter_hari === $h) ? 'selected' : '' ?>>Tanggal <?= $h ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="bulan" class="form-select form-select-sm">
                                <option value="">-- Semua Bulan --</option>
                                <?php foreach ($nama_bulan as $num => $nama): ?>
                                    <option value="<?= $num ?>" <?= ((int)$filter_bulan === $num) ? 'selected' : '' ?>><?= $nama ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="tahun" class="form-select form-select-sm">
                                <?php $tahun_sekarang = (int)date('Y');
                                for ($t = $tahun_sekarang; $t >= $tahun_sekarang - 4; $t--): ?>
                                    <option value="<?= $t ?>" <?= ((int)$filter_tahun === $t) ? 'selected' : '' ?>>Tahun <?= $t ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold">
                                <i class="fa-solid fa-magnifying-glass me-1"></i> Tampilkan
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- BOOTSTRAP CARDS: REKAP PELANGGARAN -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3">
                                <i class="fa-solid fa-user-graduate fs-3"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Siswa Melanggar</p>
                                <h3 class="fw-bold mb-0"><?= number_format($total_siswa) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-3 me-3">
                                <i class="fa-solid fa-triangle-exclamation fs-3"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Kasus Pelanggaran</p>
                                <h3 class="fw-bold mb-0"><?= number_format($total_pelanggaran) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3 me-3">
                                <i class="fa-solid fa-hourglass-half fs-3"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Perlu Tindak Lanjut</p>
                                <h3 class="fw-bold mb-0"><?= number_format($total_tindak_lanjut) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-info bg-opacity-10 text-info p-3 rounded-3 me-3">
                                <i class="fa-solid fa-star fs-3"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Total Poin</p>
                                <h3 class="fw-bold mb-0 text-info"><?= number_format($total_poin) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOOTSTRAP CARDS: DATA MASTER -->
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3">
                                <i class="fa-solid fa-users fs-3"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Siswa Terdaftar</p>
                                <h3 class="fw-bold mb-0"><?= number_format($total_siswa_terdaftar) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 text-success p-3 rounded-3 me-3">
                                <i class="fa-solid fa-chalkboard-user fs-3"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Total Guru</p>
                                <h3 class="fw-bold mb-0"><?= number_format($total_guru) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3 me-3">
                                <i class="fa-solid fa-list-check fs-3"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Kategori Pelanggaran</p>
                                <h3 class="fw-bold mb-0"><?= number_format($total_kat) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-info bg-opacity-10 text-info p-3 rounded-3 me-3">
                                <i class="fa-solid fa-user-shield fs-3"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Wali Kelas</p>
                                <h3 class="fw-bold mb-0"><?= number_format($total_wk) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOOTSTRAP CARD GRAFIK STATISTIK -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold m-0 text-dark">
                        <i class="fa-solid fa-chart-column text-primary me-2"></i>
                        <?= htmlspecialchars($chart_title) ?>
                    </h5>
                </div>
                <div class="card-body">
                    <!-- WRAPPER RESPONSIVE UNTUK CHART -->
                    <div style="position: relative; height: 350px;">
                        <canvas id="grafikAdmin"></canvas>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
const labelsAdmin = <?= json_encode($chart_labels) ?>;
const dataAdmin = <?= json_encode($chart_data) ?>;

new Chart(document.getElementById('grafikAdmin'), {
    type: 'bar',
    data: {
        labels: labelsAdmin,
        datasets: [{
            label: 'Jumlah Pelanggaran',
            data: dataAdmin,
            backgroundColor: 'rgba(13, 110, 253, 0.85)', // Warna Primary Bootstrap
            borderColor: 'rgba(13, 110, 253, 1)',
            borderWidth: 1,
            borderRadius: 6,
            maxBarThickness: 32 // MEMBATASI LEBAR BATANG DENGAN BOOTSTRAP RATIONALE
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                grid: {
                    display: false
                },
                title: {
                    display: true,
                    text: '<?= ($filter_bulan !== "") ? "Tanggal dalam Bulan" : "Bulan" ?>',
                    font: { weight: 'bold' }
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0,
                    stepSize: 1
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