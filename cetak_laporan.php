<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';
include 'includes/akses_pelanggaran.php';

$konteks = konteks_akses_pelanggaran($koneksi);
$lingkup = lingkup_sql_pelanggaran($konteks);
$mulai = $_GET['mulai'] ?? '';
$sampai = $_GET['sampai'] ?? '';
$status = $_GET['status'] ?? '';
$tanggal_valid = static function ($nilai): bool {
    if (!is_string($nilai) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $nilai)) {
        return false;
    }
    $tanggal = DateTime::createFromFormat('!Y-m-d', $nilai);
    return $tanggal && $tanggal->format('Y-m-d') === $nilai;
};

$where = [$lingkup];
if ($tanggal_valid($mulai)) {
    $where[] = "ps.tanggal >= '" . mysqli_real_escape_string($koneksi, $mulai) . "'";
} else {
    $mulai = '';
}
if ($tanggal_valid($sampai)) {
    $where[] = "ps.tanggal <= '" . mysqli_real_escape_string($koneksi, $sampai) . "'";
} else {
    $sampai = '';
}
if (in_array($status, ['Ditindaklanjuti', 'Proses', 'Selesai', 'tercatat'], true)) {
    $where[] = "ps.status = '" . mysqli_real_escape_string($koneksi, $status) . "'";
} else {
    $status = '';
}

$hasil = mysqli_query(
    $koneksi,
    "SELECT ps.nama_siswa, ps.nama_kelas, ps.nama_pelanggaran, ps.nama_guru,
            ps.tanggal, ps.poin, ps.tindakan, ps.status
     FROM t_pelanggaran_siswa ps
     WHERE " . implode(' AND ', $where) . '
     ORDER BY ps.tanggal DESC, ps.id DESC'
);
$total_poin = 0;
$jumlah_catatan = 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Laporan Pelanggaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="layout/style.css">
    <style>
        body { color: #17202a; font: 14px Arial, sans-serif; }
        .report-title { margin-bottom: 6px; }
        .meta { color: #566573; margin-bottom: 18px; }
        .report-table { border-collapse: collapse; width: 100%; }
        .report-table th, .report-table td { border: 1px solid #aab7b8; padding: 7px; text-align: left; vertical-align: top; }
        .report-table th { background: #eaf0f2; }
        .toolbar { margin-bottom: 16px; }
        @media print {
            body { margin: 0; background: #fff !important; }
            .toolbar, .sidebar-sticky { display: none !important; }
            .print-main { width: 100% !important; max-width: 100% !important; margin: 0 !important; flex: 0 0 100% !important; padding: 0 !important; }
            .container-fluid, .row { display: block !important; margin: 0 !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <?php include 'layout/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4 print-main">
            <div class="toolbar d-flex flex-wrap gap-2">
                <a class="btn btn-outline-secondary" href="guru/laporan_guru.php?<?= htmlspecialchars(http_build_query(['mulai' => $mulai, 'sampai' => $sampai, 'status' => $status]), ENT_QUOTES, 'UTF-8') ?>" title="Kembali ke laporan guru">
                    <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>Kembali
                </a>
                <button class="btn btn-primary" type="button" onclick="window.print()">
                    <i class="fa-solid fa-print me-1" aria-hidden="true"></i>Cetak
                </button>
            </div>
            <h1 class="report-title">Laporan Pelanggaran Siswa</h1>
            <p class="meta">Periode: <?= htmlspecialchars($mulai ?: 'Semua') ?> sampai <?= htmlspecialchars($sampai ?: 'Semua') ?> | Status: <?= htmlspecialchars($status ?: 'Semua') ?> | Dicetak: <?= date('d-m-Y H:i') ?></p>
            <table class="report-table">
        <thead><tr><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Pelanggaran</th><th>Guru</th><th>Poin</th><th>Tindakan</th><th>Status</th></tr></thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($hasil)): $total_poin += (int)$row['poin']; $jumlah_catatan++; ?>
            <tr><td><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td><td><?= htmlspecialchars($row['nama_siswa']) ?></td><td><?= htmlspecialchars($row['nama_kelas']) ?></td><td><?= htmlspecialchars($row['nama_pelanggaran']) ?></td><td><?= htmlspecialchars($row['nama_guru']) ?></td><td><?= (int)$row['poin'] ?></td><td><?= htmlspecialchars($row['tindakan']) ?></td><td><?= htmlspecialchars($row['status']) ?></td></tr>
        <?php endwhile; ?>
        </tbody>
        <tfoot><tr><th colspan="5">Total (<?= $jumlah_catatan ?> catatan)</th><th><?= $total_poin ?></th><th colspan="2"></th></tr></tfoot>
            </table>
        </main>
    </div>
</div>
</body>
</html>
