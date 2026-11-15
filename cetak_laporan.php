<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';
include 'includes/akses_pelanggaran.php';

$konteks = konteks_akses_pelanggaran($koneksi);
$lingkup = lingkup_sql_pelanggaran($konteks, 'ps');

$mulai = $_GET['mulai'] ?? '';
$sampai = $_GET['sampai'] ?? '';
$status = strtolower(trim((string)($_GET['status'] ?? '')));

$halaman_kembali = ($_SESSION['role'] ?? '') === 'admin' ? 'laporan.php' : 'guru/laporan_guru.php';
$label_kembali = ($_SESSION['role'] ?? '') === 'admin' ? 'Kembali ke Laporan' : 'Kembali ke Laporan Guru';
$esc = static function ($value): string {
    return htmlspecialchars((string)($value ?? '-'), ENT_QUOTES, 'UTF-8');
};
$tanggal_valid = static function ($nilai): bool {
    if (!is_string($nilai) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $nilai)) return false;
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
$status_diizinkan = ['tercatat', 'ditindaklanjuti', 'proses', 'selesai'];
if (in_array($status, $status_diizinkan, true)) {
    $where[] = "LOWER(TRIM(COALESCE(ps.status, ''))) = '" . mysqli_real_escape_string($koneksi, $status) . "'";
} else {
    $status = '';
}

$sql = "SELECT
            ps.tanggal, ps.poin, ps.tindakan, ps.status,
            COALESCE(s.nama, ps.nama_siswa, 'Siswa Tidak Ditemukan') AS nama_siswa,
            COALESCE(k.nama, ps.nama_kelas, '-') AS nama_kelas,
            COALESCE(p.nama, ps.nama_pelanggaran, '-') AS nama_pelanggaran,
            COALESCE(g.nama, ps.nama_guru, 'Sistem / Admin') AS nama_guru
        FROM t_pelanggaran_siswa ps
        LEFT JOIN t_siswa s ON ps.siswa_id = s.id
        LEFT JOIN t_kelas k ON ps.kelas_id = k.id
        LEFT JOIN t_pelanggaran p ON ps.pelanggaran_id = p.id
        LEFT JOIN t_guru g ON ps.guru_id = g.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY ps.tanggal DESC, ps.id DESC";
$hasil = mysqli_query($koneksi, $sql);
if (!$hasil) error_log('Gagal memuat laporan cetak: ' . mysqli_error($koneksi));

$rows = [];
$total_poin = 0;
if ($hasil) {
    while ($row = mysqli_fetch_assoc($hasil)) {
        $rows[] = $row;
        $total_poin += (int)$row['poin'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Laporan Pelanggaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="layout/style.css">
    <style>
        body { background:#f3f5f9; color:#1f2937; font:13px Arial,sans-serif; }
        .print-main { max-width:1200px; margin:24px auto; }
        .report-sheet { background:#fff; padding:28px; border-radius:10px; box-shadow:0 2px 14px rgba(15,23,42,.08); }
        .report-head { text-align:center; border-bottom:2px solid #26364a; padding-bottom:16px; margin-bottom:18px; }
        .report-head h1 { font-size:21px; font-weight:700; margin:0 0 5px; letter-spacing:.3px; }
        .report-head p { margin:0; color:#64748b; }
        .report-meta { display:flex; justify-content:space-between; flex-wrap:wrap; gap:8px; margin:16px 0; color:#475569; }
        .summary { display:flex; gap:12px; margin-bottom:16px; }
        .summary-item { border:1px solid #dbe2ea; border-radius:7px; padding:9px 13px; min-width:150px; }
        .summary-item strong { display:block; font-size:16px; }
        .summary-item span { color:#64748b; font-size:11px; }
        table { width:100%; border-collapse:collapse; table-layout:fixed; }
        th,td { border:1px solid #cbd5e1; padding:7px 6px; text-align:left; vertical-align:top; overflow-wrap:anywhere; }
        thead th { background:#eaf0f6; color:#1e293b; font-weight:700; }
        tbody tr:nth-child(even) { background:#f8fafc; }
        tfoot th { background:#eef2f7; }
        .col-date{width:8%}.col-name{width:13%}.col-class{width:8%}.col-violation{width:17%}.col-teacher{width:12%}.col-point{width:5%}.col-action{width:22%}.col-status{width:10%}
        .toolbar { max-width:1200px; margin:0 auto 12px; }
        @page { size:A4 landscape; margin:12mm; }
        @media print {
            body { background:#fff !important; font-size:10px; }
            .toolbar, .sidebar-sticky, nav, aside { display:none !important; }
            .container-fluid,.row { display:block !important; margin:0 !important; padding:0 !important; }
            .print-main { display:block !important; width:100% !important; max-width:none !important; margin:0 !important; padding:0 !important; }
            .report-sheet { box-shadow:none; border-radius:0; padding:0; }
            .report-head { padding-bottom:10px; margin-bottom:10px; }
            .report-head h1 { font-size:17px; }
            table { font-size:9px; }
            th,td { padding:5px 4px; }
            thead { display:table-header-group; }
            tfoot { display:table-row-group; }
            tr { break-inside:avoid; page-break-inside:avoid; }
            * { -webkit-print-color-adjust:exact !important; print-color-adjust:exact !important; }
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include 'layout/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4 print-main">
            <div class="toolbar d-flex flex-wrap gap-2">
                <a class="btn btn-outline-secondary" href="<?= $esc($halaman_kembali) ?>?<?= $esc(http_build_query(['mulai'=>$mulai,'sampai'=>$sampai,'status'=>$status])) ?>">
                    <i class="fa-solid fa-arrow-left me-1"></i><?= $esc($label_kembali) ?>
                </a>
                <button class="btn btn-primary" type="button" onclick="window.print()">
                    <i class="fa-solid fa-print me-1"></i>Cetak Laporan
                </button>
            </div>

            <section class="report-sheet">
                <header class="report-head">
                    <h1>LAPORAN PELANGGARAN SISWA</h1>
                    <p>Sistem Informasi Pelanggaran (SIP)</p>
                </header>
                <div class="report-meta">
                    <span><strong>Periode:</strong> <?= $esc($mulai ?: 'Semua tanggal') ?> s.d. <?= $esc($sampai ?: 'Semua tanggal') ?></span>
                    <span><strong>Status:</strong> <?= $esc($status ? ucfirst($status) : 'Semua status') ?></span>
                    <span><strong>Dicetak:</strong> <?= date('d-m-Y H:i') ?></span>
                </div>
                <div class="summary">
                    <div class="summary-item"><span>Jumlah Catatan</span><strong><?= number_format(count($rows), 0, ',', '.') ?></strong></div>
                    <div class="summary-item"><span>Total Poin</span><strong><?= number_format($total_poin, 0, ',', '.') ?></strong></div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th class="col-date">Tanggal</th><th class="col-name">Nama Siswa</th><th class="col-class">Kelas</th>
                            <th class="col-violation">Pelanggaran</th><th class="col-teacher">Guru Pelapor</th><th class="col-point">Poin</th>
                            <th class="col-action">Tindakan</th><th class="col-status">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$hasil): ?>
                        <tr><td colspan="8">Laporan gagal dimuat. Silakan kembali dan coba lagi.</td></tr>
                    <?php elseif (!$rows): ?>
                        <tr><td colspan="8" class="text-center">Tidak ada catatan sesuai filter laporan.</td></tr>
                    <?php else: foreach ($rows as $row): ?>
                        <tr>
                            <td><?= $esc($row['tanggal']) ?></td><td><?= $esc($row['nama_siswa']) ?></td>
                            <td><?= $esc($row['nama_kelas']) ?></td><td><?= $esc($row['nama_pelanggaran']) ?></td>
                            <td><?= $esc($row['nama_guru']) ?></td><td class="text-center"><?= (int)$row['poin'] ?></td>
                            <td><?= $esc($row['tindakan']) ?></td><td><?= $esc($row['status']) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                    <tfoot><tr><th colspan="5">TOTAL (<?= count($rows) ?> CATATAN)</th><th><?= $total_poin ?></th><th colspan="2"></th></tr></tfoot>
                </table>
            </section>
        </main>
    </div>
</div>
</body>
</html>
