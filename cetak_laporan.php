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
    <style>
        body { color: #17202a; font: 14px Arial, sans-serif; margin: 24px; }
        h1 { margin-bottom: 6px; }
        .meta { color: #566573; margin-bottom: 18px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #aab7b8; padding: 7px; text-align: left; vertical-align: top; }
        th { background: #eaf0f2; }
        .toolbar { margin-bottom: 16px; }
        @media print { body { margin: 0; } .toolbar { display: none; } }
    </style>
</head>
<body>
    <div class="toolbar"><button type="button" onclick="window.print()">Cetak</button></div>
    <h1>Laporan Pelanggaran Siswa</h1>
    <p class="meta">Periode: <?= htmlspecialchars($mulai ?: 'Semua') ?> sampai <?= htmlspecialchars($sampai ?: 'Semua') ?> | Status: <?= htmlspecialchars($status ?: 'Semua') ?> | Dicetak: <?= date('d-m-Y H:i') ?></p>
    <table>
        <thead><tr><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Pelanggaran</th><th>Guru</th><th>Poin</th><th>Tindakan</th><th>Status</th></tr></thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($hasil)): $total_poin += (int)$row['poin']; $jumlah_catatan++; ?>
            <tr><td><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td><td><?= htmlspecialchars($row['nama_siswa']) ?></td><td><?= htmlspecialchars($row['nama_kelas']) ?></td><td><?= htmlspecialchars($row['nama_pelanggaran']) ?></td><td><?= htmlspecialchars($row['nama_guru']) ?></td><td><?= (int)$row['poin'] ?></td><td><?= htmlspecialchars($row['tindakan']) ?></td><td><?= htmlspecialchars($row['status']) ?></td></tr>
        <?php endwhile; ?>
        </tbody>
        <tfoot><tr><th colspan="5">Total (<?= $jumlah_catatan ?> catatan)</th><th><?= $total_poin ?></th><th colspan="2"></th></tr></tfoot>
    </table>
</body>
</html>
