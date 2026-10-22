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
    <title>Laporan Pelanggaran - SIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="layout/style.css">
</head>
<body class="bg-light">
<div class="container-fluid"><div class="row">
    <?php include 'layout/sidebar.php'; ?>
    <main class="col-md-9 col-lg-10 p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <h1 class="h3 m-0">Laporan Pelanggaran</h1>
            <a class="btn btn-outline-primary" href="cetak_laporan.php?<?= htmlspecialchars(http_build_query(['mulai' => $mulai, 'sampai' => $sampai, 'status' => $status])) ?>" target="_blank">Cetak Laporan</a>
        </div>
        <form method="get" class="row g-2 align-items-end bg-white border rounded p-3 mb-3">
            <div class="col-sm-4"><label class="form-label" for="mulai">Dari tanggal</label><input id="mulai" type="date" name="mulai" value="<?= htmlspecialchars($mulai) ?>" class="form-control"></div>
            <div class="col-sm-4"><label class="form-label" for="sampai">Sampai tanggal</label><input id="sampai" type="date" name="sampai" value="<?= htmlspecialchars($sampai) ?>" class="form-control"></div>
            <div class="col-sm-3"><label class="form-label" for="status">Status</label><select id="status" name="status" class="form-select"><option value="">Semua status</option><?php foreach (['tercatat', 'Ditindaklanjuti', 'Proses', 'Selesai'] as $opsi): ?><option value="<?= $opsi ?>" <?= $status === $opsi ? 'selected' : '' ?>><?= htmlspecialchars($opsi) ?></option><?php endforeach; ?></select></div>
            <div class="col-sm-1"><button class="btn btn-primary w-100" type="submit">Cari</button></div>
        </form>
        <div class="table-responsive bg-white border rounded p-3">
            <table class="table table-striped align-middle">
                <thead><tr><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Pelanggaran</th><th>Guru</th><th>Poin</th><th>Tindakan</th><th>Status</th></tr></thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($hasil)): $total_poin += (int)$row['poin']; $jumlah_catatan++; ?>
                    <tr><td><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td><td><?= htmlspecialchars($row['nama_siswa']) ?></td><td><?= htmlspecialchars($row['nama_kelas']) ?></td><td><?= htmlspecialchars($row['nama_pelanggaran']) ?></td><td><?= htmlspecialchars($row['nama_guru']) ?></td><td><?= (int)$row['poin'] ?></td><td><?= htmlspecialchars($row['tindakan']) ?></td><td><?= htmlspecialchars($row['status']) ?></td></tr>
                <?php endwhile; ?>
                </tbody>
                <tfoot><tr><th colspan="5">Total (<?= $jumlah_catatan ?> catatan)</th><th><?= $total_poin ?></th><th colspan="2"></th></tr></tfoot>
            </table>
        </div>
    </main>
</div></div>
</body>
</html>
