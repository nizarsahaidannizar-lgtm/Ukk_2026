<?php
include '../includes/cek_session.php';
include '../config/koneksi.php';

batasi_akses(['guru']);

include_once '../includes/akses_pelanggaran.php';
$konteks = konteks_akses_pelanggaran($koneksi);
$lingkup = lingkup_sql_pelanggaran($konteks, 'ps');

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

$conditions = [];
if ($filter_tahun) {
    $conditions[] = 'YEAR(ps.tanggal) = ' . (int)$filter_tahun;
}
if ($filter_bulan) {
    $conditions[] = 'MONTH(ps.tanggal) = ' . (int)$filter_bulan;
}
if ($filter_hari) {
    $conditions[] = 'DAY(ps.tanggal) = ' . (int)$filter_hari;
}

$where_sql = $conditions ? "WHERE $lingkup AND " . implode(' AND ', $conditions) : "WHERE $lingkup";

$q_siswa = mysqli_query(
    $koneksi,
    "SELECT COUNT(DISTINCT ps.siswa_id) AS total FROM t_pelanggaran_siswa ps $where_sql"
);
$total_siswa = $q_siswa ? (int)mysqli_fetch_assoc($q_siswa)['total'] : 0;

$q_pelanggaran = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM t_pelanggaran_siswa ps $where_sql"
);
$total_pelanggaran = $q_pelanggaran ? (int)mysqli_fetch_assoc($q_pelanggaran)['total'] : 0;

$q_tindak_lanjut = mysqli_query(
    $koneksi,
    "SELECT COUNT(*) AS total FROM t_pelanggaran_siswa ps $where_sql AND ps.status <> 'Selesai'"
);
$total_tindak_lanjut = $q_tindak_lanjut ? (int)mysqli_fetch_assoc($q_tindak_lanjut)['total'] : 0;

$q_poin = mysqli_query(
    $koneksi,
    "SELECT COALESCE(SUM(ps.poin), 0) AS total FROM t_pelanggaran_siswa ps $where_sql"
);
$total_poin = $q_poin ? (int)mysqli_fetch_assoc($q_poin)['total'] : 0;

$chart_labels = [];
$chart_data = [];
$chart_title = 'Statistik Pelanggaran';

if (!empty($filter_hari)) {
    $chart_query = mysqli_query(
        $koneksi,
        "SELECT DATE(ps.tanggal) AS label, COUNT(*) AS total
         FROM t_pelanggaran_siswa ps
         WHERE $lingkup AND YEAR(ps.tanggal) = " . (int)$filter_tahun . "
           AND MONTH(ps.tanggal) = " . (int)$filter_bulan . "
           AND DAY(ps.tanggal) = " . (int)$filter_hari . "
         GROUP BY DATE(ps.tanggal)
         ORDER BY DATE(ps.tanggal)"
    );
    $chart_title = 'Rekap pelanggaran berdasarkan tanggal';
} elseif (!empty($filter_bulan)) {
    $chart_query = mysqli_query(
        $koneksi,
        "SELECT DAY(ps.tanggal) AS label, COUNT(*) AS total
         FROM t_pelanggaran_siswa ps
         WHERE $lingkup AND YEAR(ps.tanggal) = " . (int)$filter_tahun . "
           AND MONTH(ps.tanggal) = " . (int)$filter_bulan . "
         GROUP BY DAY(ps.tanggal)
         ORDER BY DAY(ps.tanggal)"
    );
    $chart_title = 'Rekap pelanggaran per hari - ' . $nama_bulan[(int)$filter_bulan] . ' ' . $filter_tahun;
} else {
    $chart_query = mysqli_query(
        $koneksi,
        "SELECT MONTH(ps.tanggal) AS label, COUNT(*) AS total
         FROM t_pelanggaran_siswa ps
         WHERE $lingkup AND YEAR(ps.tanggal) = " . (int)$filter_tahun . "
         GROUP BY MONTH(ps.tanggal)
         ORDER BY MONTH(ps.tanggal)"
    );
    $chart_title = 'Rekap pelanggaran per bulan - ' . $filter_tahun;
}

if ($chart_query) {
    while ($row = mysqli_fetch_assoc($chart_query)) {
        if (!empty($filter_hari)) {
            $chart_labels[] = date('d-m-Y', strtotime($row['label']));
        } elseif (!empty($filter_bulan)) {
            $chart_labels[] = 'Hari ' . (int)$row['label'];
        } else {
            $chart_labels[] = $nama_bulan[(int)$row['label']];
        }
        $chart_data[] = (int)$row['total'];
    }
}

$rekap_poin = mysqli_query(
    $koneksi,
    "SELECT ps.siswa_id, ps.nama_siswa, ps.nama_kelas,
            COUNT(*) AS jumlah_pelanggaran,
            COALESCE(SUM(ps.poin), 0) AS total_poin,
            MAX(ps.tanggal) AS terakhir
     FROM t_pelanggaran_siswa ps
     $where_sql
     GROUP BY ps.siswa_id, ps.nama_siswa, ps.nama_kelas
     ORDER BY total_poin DESC, ps.nama_siswa ASC
     LIMIT 10"
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - SIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../layout/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <?php include '../layout/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">
            <?php include '../layout/brand_header.php'; ?>

            <div class="alert alert-primary alert-dismissible fade show mb-4 shadow-sm" role="alert">
                Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama'] ?? 'Guru'); ?></strong>. Berikut ringkasan pelanggaran dan rekap poin Anda.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body py-3">
                    <form method="GET" class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label class="fw-semibold text-secondary"><i class="fa-solid fa-filter me-1"></i> Filter Rekap:</label>
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
                            <a href="dashboard_guru.php" class="btn btn-secondary btn-sm px-3 fw-semibold">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

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
                                <p class="text-muted small mb-1">Jumlah Kasus Pelanggaran</p>
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

            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-0 p-3">
                    <h5 class="fw-bold mb-1">
                        <i class="fa-solid fa-chart-column text-primary me-2"></i>
                        <?= htmlspecialchars($chart_title) ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 340px;">
                        <canvas id="grafikGuru"></canvas>
                    </div>
                    <?php if (empty($chart_data)): ?>
                        <p class="text-muted text-center mt-3 mb-0">Belum ada data yang cocok dengan filter saat ini.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-0 p-3">
                    <h5 class="fw-bold mb-1">
                        <i class="fa-solid fa-ranking-star text-success me-2"></i>
                        Rekap Poin Siswa
                    </h5>
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
                                <?php $no = 1; while ($row = mysqli_fetch_assoc($rekap_poin)): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($row['nama_siswa'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['nama_kelas'] ?? '-') ?></td>
                                        <td><?= (int)($row['jumlah_pelanggaran'] ?? 0) ?></td>
                                        <td><strong><?= number_format((int)($row['total_poin'] ?? 0)) ?></strong></td>
                                        <td><?= htmlspecialchars($row['terakhir'] ?? '-') ?></td>
                                    </tr>
                                <?php endwhile; ?>
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
const labelsGuru = <?= json_encode($chart_labels) ?>;
const dataGuru = <?= json_encode($chart_data) ?>;

new Chart(document.getElementById('grafikGuru'), {
    type: 'bar',
    data: {
        labels: labelsGuru,
        datasets: [{
            label: 'Jumlah Pelanggaran',
            data: dataGuru,
            backgroundColor: 'rgba(13, 110, 253, 0.7)',
            borderColor: 'rgba(13, 110, 253, 1)',
            borderWidth: 1,
            borderRadius: 6
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
