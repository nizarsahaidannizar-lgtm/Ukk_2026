<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';
include 'includes/akses_pelanggaran.php';

$konteks = konteks_akses_pelanggaran($koneksi);
$lingkup = lingkup_sql_pelanggaran($konteks);
$hasil = mysqli_query(
    $koneksi,
    "SELECT ps.siswa_id, ps.nama_siswa, ps.nama_kelas,
            COUNT(*) AS jumlah_pelanggaran, SUM(ps.poin) AS total_poin,
            MAX(ps.tanggal) AS terakhir
     FROM t_pelanggaran_siswa ps
     WHERE $lingkup
     GROUP BY ps.siswa_id, ps.nama_siswa, ps.kelas_id, ps.nama_kelas
     ORDER BY total_poin DESC, ps.nama_siswa ASC"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekap Poin - SIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="layout/style.css">
</head>
<body class="bg-light">
<div class="container-fluid"><div class="row">
    <?php include 'layout/sidebar.php'; ?>
    <main class="col-md-9 col-lg-10 p-4">
        <h1 class="h3 mb-4">Rekap Poin Siswa</h1>
        <div class="table-responsive bg-white border rounded p-3">
            <table class="table table-striped align-middle">
                <thead><tr><th>No.</th><th>Siswa</th><th>Kelas</th><th>Jumlah Pelanggaran</th><th>Total Poin</th><th>Terakhir Tercatat</th></tr></thead>
                <tbody>
                <?php $no = 1; while ($row = mysqli_fetch_assoc($hasil)): ?>
                    <tr><td><?= $no++ ?></td><td><?= htmlspecialchars($row['nama_siswa']) ?></td><td><?= htmlspecialchars($row['nama_kelas']) ?></td><td><?= (int)$row['jumlah_pelanggaran'] ?></td><td><strong><?= (int)$row['total_poin'] ?></strong></td><td><?= htmlspecialchars($row['terakhir'] ?? '-') ?></td></tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div></div>
</body>
</html>
