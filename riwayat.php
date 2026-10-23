<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

batasi_akses(['admin']);

$hasil = mysqli_query(
    $koneksi,
    "SELECT ps.id, ps.nama_siswa, ps.nama_kelas, ps.nama_pelanggaran,
            ps.tanggal, ps.poin, ps.tindakan, ps.status, ps.updated_at
     FROM t_pelanggaran_siswa ps
     ORDER BY ps.updated_at DESC, ps.id DESC"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Pelanggaran - SIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="layout/style.css">
</head>
<body class="bg-light">
<div class="container-fluid"><div class="row">
    <?php include 'layout/sidebar.php'; ?>
    <main class="col-md-9 col-lg-10 p-4">
        <?php include 'layout/brand_header.php'; ?>
        <h1 class="h3 mb-4">Riwayat Pelanggaran</h1>
        <div class="table-responsive bg-white border rounded p-3">
            <table class="table table-striped align-middle">
                <thead><tr><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Pelanggaran</th><th>Poin</th><th>Tindakan</th><th>Status</th><th>Terakhir Diubah</th></tr></thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($hasil)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['nama_siswa']) ?></td>
                        <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                        <td><?= htmlspecialchars($row['nama_pelanggaran']) ?></td>
                        <td><?= (int)$row['poin'] ?></td>
                        <td><?= htmlspecialchars($row['tindakan']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td><?= htmlspecialchars($row['updated_at']) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div></div>
</body>
</html>
