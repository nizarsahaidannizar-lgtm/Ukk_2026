
<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

batasi_akses(['admin']);

// ===============================
// FILTER TANGGAL
// ===============================
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

// ===============================
// KONDISI FILTER
// ===============================
$conditions = [];

$conditions[] = 'YEAR(ps.tanggal) = ' . (int)$filter_tahun;

if ($filter_bulan !== '') {
    $conditions[] = 'MONTH(ps.tanggal) = ' . (int)$filter_bulan;
}

if ($filter_hari !== '') {
    $conditions[] = 'DAY(ps.tanggal) = ' . (int)$filter_hari;
}

$where_sql = 'WHERE ' . implode(' AND ', $conditions);

// ===============================
// REKAP KARTU DASHBOARD
// SEMUA DATA DARI SEMUA GURU
// ===============================

// Jumlah siswa yang melanggar
$q_siswa = mysqli_query(
    $koneksi,
    "SELECT COUNT(DISTINCT ps.siswa_id) AS total
     FROM t_pelanggaran_siswa ps
     $where_sql"
);

$total_siswa = $q_siswa
    ? (int)mysqli_fetch_assoc($q_siswa)['total']
    : 0;

// Jumlah kasus pelanggaran
$q_pelanggaran = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM t_pelanggaran_siswa ps
     $where_sql"
);

$total_pelanggaran = $q_pelanggaran
    ? (int)mysqli_fetch_assoc($q_pelanggaran)['total']
    : 0;

// Pelanggaran yang belum selesai
$q_tindak_lanjut = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total
     FROM t_pelanggaran_siswa ps
     $where_sql
     AND ps.status <> 'Selesai'"
);

$total_tindak_lanjut = $q_tindak_lanjut
    ? (int)mysqli_fetch_assoc($q_tindak_lanjut)['total']
    : 0;

// Total seluruh poin pelanggaran
$q_poin = mysqli_query(
    $koneksi,
    "SELECT COALESCE(SUM(ps.poin), 0) AS total
     FROM t_pelanggaran_siswa ps
     $where_sql"
);

$total_poin = $q_poin
    ? (int)mysqli_fetch_assoc($q_poin)['total']
    : 0;

// ===============================
// DATA GRAFIK
// ===============================
$chart_labels = [];
$chart_data = [];
$chart_title = 'Tren Pelanggaran';
$chart_description = '';
$chart_selected_index = null;
$chart_error = '';

if ($filter_bulan !== '') {

    // Grafik harian
    $chart_query = mysqli_query(
        $koneksi,
        "SELECT DAY(ps.tanggal) AS label, COUNT(*) AS total
         FROM t_pelanggaran_siswa ps
         WHERE YEAR(ps.tanggal) = " . (int)$filter_tahun . "
         AND MONTH(ps.tanggal) = " . (int)$filter_bulan . "
         GROUP BY DAY(ps.tanggal)
         ORDER BY DAY(ps.tanggal)"
    );

    $chart_title = 'Tren Pelanggaran Harian';

    $jumlah_hari = cal_days_in_month(
        CAL_GREGORIAN,
        (int)$filter_bulan,
        (int)$filter_tahun
    );

    if ($filter_hari !== '' && (int)$filter_hari <= $jumlah_hari) {
        $chart_selected_index = (int)$filter_hari - 1;

        $chart_description = 'Tanggal ' . $filter_hari .
            ' pada bulan ' . $nama_bulan[(int)$filter_bulan] .
            ' ' . $filter_tahun;
    } else {
        $chart_description = 'Jumlah kasus setiap tanggal pada bulan ' .
            $nama_bulan[(int)$filter_bulan] . ' ' . $filter_tahun;
    }

    $jumlah_per_hari = array_fill(1, $jumlah_hari, 0);

    if ($chart_query) {
        while ($row = mysqli_fetch_assoc($chart_query)) {
            $jumlah_per_hari[(int)$row['label']] = (int)$row['total'];
        }
    } else {
        $chart_error = 'Statistik belum dapat dimuat.';
        error_log('Dashboard admin grafik harian: ' . mysqli_error($koneksi));
    }

    for ($hari = 1; $hari <= $jumlah_hari; $hari++) {
        $chart_labels[] = (string)$hari;
        $chart_data[] = $jumlah_per_hari[$hari];
    }

} else {

    // Grafik bulanan
    $chart_query = mysqli_query(
        $koneksi,
        "SELECT MONTH(ps.tanggal) AS label, COUNT(*) AS total
         FROM t_pelanggaran_siswa ps
         WHERE YEAR(ps.tanggal) = " . (int)$filter_tahun . "
         GROUP BY MONTH(ps.tanggal)
         ORDER BY MONTH(ps.tanggal)"
    );

    $chart_title = 'Tren Pelanggaran Bulanan';
    $chart_description = 'Jumlah kasus setiap bulan pada tahun ' . $filter_tahun;

    $jumlah_per_bulan = array_fill(1, 12, 0);

    if ($chart_query) {
        while ($row = mysqli_fetch_assoc($chart_query)) {
            $jumlah_per_bulan[(int)$row['label']] = (int)$row['total'];
        }
    } else {
        $chart_error = 'Statistik belum dapat dimuat.';
        error_log('Dashboard admin grafik bulanan: ' . mysqli_error($koneksi));
    }

    foreach ($nama_bulan as $nomor_bulan => $nama) {
        $chart_labels[] = $nama;
        $chart_data[] = $jumlah_per_bulan[$nomor_bulan];
    }
}

$chart_has_data = array_sum($chart_data) > 0;

// ===============================
// REKAP POIN SISWA
// SEMUA GURU DAN SEMUA SISWA
// ===============================
$rekap_poin = mysqli_query(
    $koneksi,
    "SELECT
        ps.siswa_id,
        ps.nama_siswa,
        ps.nama_kelas,
        COUNT(*) AS jumlah_pelanggaran,
        COALESCE(SUM(ps.poin), 0) AS total_poin,
        MAX(ps.tanggal) AS terakhir
     FROM t_pelanggaran_siswa ps
     $where_sql
     GROUP BY ps.siswa_id, ps.nama_siswa, ps.nama_kelas
     ORDER BY total_poin DESC, ps.nama_siswa ASC
     LIMIT 10"
);

if (!$rekap_poin) {
    error_log('Dashboard admin rekap poin: ' . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Admin - SIP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="layout/style.css">

    <style>
        html, body {
            margin: 0;
            min-height: 100%;
            overflow-x: hidden;
        }

        .layout-row {
            display: block !important;
            width: 100%;
            margin: 0 !important;
        }

        .main-content {
            display: block;
            width: calc(100% - 240px);
            max-width: calc(100% - 240px);
            min-width: 0;
            margin-left: 240px;
            padding: 24px !important;
            box-sizing: border-box;
        }

        .main-content .card,
        .main-content .table-responsive {
            max-width: 100%;
        }

        .admin-chart-wrap {
            position: relative;
            height: 320px;
            width: 100%;
        }

        @media (max-width: 767.98px) {
            .main-content {
                width: 100%;
                max-width: 100%;
                margin-left: 0;
                padding: 15px !important;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <main class="main-content">

            <header class="admin-page-header mb-3">
                <div class="admin-page-heading">
                    <div>
                        <h1>Dashboard Admin</h1>
                        <p>SIP · Ringkasan pelanggaran dan aktivitas siswa.</p>
                    </div>
                </div>
            </header>

            <div class="alert alert-primary alert-dismissible fade show mb-4 shadow-sm border-0">
                <i class="fa-solid fa-circle-info me-2"></i>
                Selamat Datang di <strong>SIP (Sistem Informasi Pelanggaran)</strong>,
                Anda login sebagai
                <strong><?= htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></strong>.

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

            <!-- FILTER -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body py-3">

                    <form method="GET" action="dashboard.php" class="row g-3 align-items-center">

                        <div class="col-auto">
                            <label class="fw-semibold text-secondary">
                                <i class="fa-solid fa-filter me-1"></i>
                                Filter Rekap:
                            </label>
                        </div>

                        <div class="col-md-2">
                            <select name="hari" class="form-select form-select-sm">
                                <option value="">-- Semua Hari --</option>

                                <?php for ($h = 1; $h <= 31; $h++): ?>
                                    <option value="<?= $h ?>"
                                        <?= ($filter_hari === $h) ? 'selected' : '' ?>>
                                        Tanggal <?= $h ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select name="bulan" class="form-select form-select-sm">
                                <option value="">-- Semua Bulan --</option>

                                <?php foreach ($nama_bulan as $num => $nama): ?>
                                    <option value="<?= $num ?>"
                                        <?= ((int)$filter_bulan === $num) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($nama) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <select name="tahun" class="form-select form-select-sm">
                                <?php
                                $tahun_sekarang = (int)date('Y');

                                for ($t = $tahun_sekarang; $t >= $tahun_sekarang - 5; $t--):
                                ?>
                                    <option value="<?= $t ?>"
                                        <?= ((int)$filter_tahun === $t) ? 'selected' : '' ?>>
                                        Tahun <?= $t ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm px-3">
                                <i class="fa-solid fa-magnifying-glass me-1"></i>
                                Tampilkan
                            </button>

                            <a href="dashboard.php" class="btn btn-secondary btn-sm px-3">
                                Reset
                            </a>
                        </div>

                    </form>
                </div>
            </div>

            <!-- KARTU REKAP -->
            <div class="row g-3 mb-4">

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3">
                                <i class="fa-solid fa-user-graduate fs-3"></i>
                            </div>

                            <div>
                                <p class="text-muted small mb-1">Siswa Melanggar</p>
                                <h3 class="fw-bold mb-0">
                                    <?= number_format($total_siswa); ?>
                                </h3>
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
                                <h3 class="fw-bold mb-0">
                                    <?= number_format($total_pelanggaran); ?>
                                </h3>
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
                                <h3 class="fw-bold mb-0">
                                    <?= number_format($total_tindak_lanjut); ?>
                                </h3>
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
                                <h3 class="fw-bold mb-0 text-info">
                                    <?= number_format($total_poin); ?>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- GRAFIK -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">

                <div class="card-header bg-white border-0 px-4 pt-4 pb-2">
                    <h5 class="fw-bold mb-1 text-dark">
                        <?= htmlspecialchars($chart_title); ?>
                    </h5>

                    <p class="text-muted small mb-0">
                        <?= htmlspecialchars($chart_description); ?>
                    </p>
                </div>

                <div class="card-body px-4 pt-3 pb-4">

                    <?php if ($chart_error !== ''): ?>

                        <div class="alert alert-warning">
                            <?= htmlspecialchars($chart_error); ?>
                        </div>

                    <?php elseif (!$chart_has_data): ?>

                        <div class="admin-chart-empty">
                            <strong>Belum ada data pelanggaran.</strong>
                            <span>Grafik akan tampil setelah ada catatan pada periode yang dipilih.</span>
                        </div>

                    <?php else: ?>

                        <div class="admin-chart-wrap">
                            <canvas id="grafikAdmin"></canvas>
                        </div>

                    <?php endif; ?>

                </div>
            </div>

            <!-- TABEL REKAP POIN -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">

                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold m-0 text-dark">
                        <i class="fa-solid fa-ranking-star text-success me-2"></i>
                        Rekap Poin Siswa
                    </h5>
                    <small class="text-muted">
                        Rekap gabungan seluruh guru berdasarkan periode yang dipilih.
                    </small>
                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-striped table-hover align-middle mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Siswa</th>
                                    <th>Kelas</th>
                                    <th>Jumlah Pelanggaran</th>
                                    <th>Total Poin</th>
                                    <th>Terakhir</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if ($rekap_poin && mysqli_num_rows($rekap_poin) > 0): ?>

                                    <?php $no = 1; ?>

                                    <?php while ($row = mysqli_fetch_assoc($rekap_poin)): ?>

                                        <tr>
                                            <td><?= $no++; ?></td>

                                            <td>
                                                <?= htmlspecialchars($row['nama_siswa'] ?? '-'); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['nama_kelas'] ?? '-'); ?>
                                            </td>

                                            <td>
                                                <?= (int)$row['jumlah_pelanggaran']; ?>
                                            </td>

                                            <td>
                                                <strong>
                                                    <?= number_format((int)$row['total_poin']); ?>
                                                </strong>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['terakhir'] ?? '-'); ?>
                                            </td>
                                        </tr>

                                    <?php endwhile; ?>

                                <?php else: ?>

                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">
                                            Belum ada data rekap poin.
                                        </td>
                                    </tr>

                                <?php endif; ?>

                            </tbody>
                        </table>

                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
<?php if ($chart_error === '' && $chart_has_data): ?>

const labelsAdmin = <?= json_encode($chart_labels); ?>;
const dataAdmin = <?= json_encode($chart_data); ?>;
const selectedIndexAdmin = <?= json_encode($chart_selected_index); ?>;

const maxValueAdmin = Math.max(...dataAdmin);
const tickStepAdmin = Math.max(1, Math.ceil(maxValueAdmin / 5));

new Chart(document.getElementById('grafikAdmin'), {
    type: 'bar',

    data: {
        labels: labelsAdmin,

        datasets: [{
            label: 'Jumlah Pelanggaran',
            data: dataAdmin,

            backgroundColor: (context) =>
                context.dataIndex === selectedIndexAdmin
                    ? 'rgba(13, 110, 253, 0.95)'
                    : 'rgba(13, 110, 253, 0.58)',

            hoverBackgroundColor: 'rgba(13, 110, 253, 0.9)',
            borderRadius: 7,
            borderSkipped: false,
            maxBarThickness: 28
        }]
    },

    options: {
        responsive: true,
        maintainAspectRatio: false,

        interaction: {
            intersect: false,
            mode: 'index'
        },

        scales: {
            x: {
                grid: {
                    display: false
                },

                title: {
                    display: true,
                    text: <?= json_encode($filter_bulan !== '' ? 'Tanggal' : 'Bulan'); ?>,
                    color: '#667085'
                }
            },

            y: {
                beginAtZero: true,

                ticks: {
                    precision: 0,
                    stepSize: tickStepAdmin
                }
            }
        },

        plugins: {
            legend: {
                display: false
            },

            tooltip: {
                backgroundColor: '#17233f',
                padding: 12,
                displayColors: false,

                callbacks: {
                    label: (context) =>
                        `${context.parsed.y} kasus pelanggaran`
                }
            }
        }
    }
});

<?php endif; ?>
</script>

</body>
</html>
