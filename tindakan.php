<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';
include 'includes/akses_pelanggaran.php';

$konteks = konteks_akses_pelanggaran($koneksi);
$lingkup = lingkup_sql_pelanggaran($konteks, 'ps');
$sql = "SELECT ps.id, ps.nama_siswa, ps.nama_kelas, ps.nama_pelanggaran,
               ps.tanggal, ps.poin, ps.tindakan, ps.status
        FROM t_pelanggaran_siswa ps
        WHERE $lingkup
        ORDER BY ps.tanggal DESC, ps.id DESC";
$hasil = mysqli_query($koneksi, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tindakan Pelanggaran - SIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="layout/style.css">
</head>
<body class="bg-light">
<div class="container-fluid"><div class="row">
    <?php include 'layout/sidebar.php'; ?>
    <main class="col-md-9 col-lg-10 p-4">
        <h1 class="h3 mb-4">Tindak Lanjut Pelanggaran</h1>
        <?php if (isset($_SESSION['pesan_sukses'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['pesan_sukses']) ?></div>
            <?php unset($_SESSION['pesan_sukses']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['pesan_error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['pesan_error']) ?></div>
            <?php unset($_SESSION['pesan_error']); ?>
        <?php endif; ?>
        <div class="table-responsive bg-white border rounded p-3">
            <table class="table table-striped align-middle">
                <thead><tr><th>Siswa</th><th>Kelas</th><th>Pelanggaran</th><th>Tanggal</th><th>Poin</th><th>Tindakan dan Status</th></tr></thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($hasil)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nama_siswa']) ?></td>
                        <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                        <td><?= htmlspecialchars($row['nama_pelanggaran']) ?></td>
                        <td><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
                        <td><?= (int)$row['poin'] ?></td>
                        <td>
                            <form action="proses_tindakan.php" method="post" class="d-flex flex-column gap-2">
                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                <textarea name="tindakan" class="form-control" rows="2" required><?= htmlspecialchars($row['tindakan']) ?></textarea>
                                <div class="d-flex gap-2">
                                    <select name="status" class="form-select" required>
                                        <?php foreach (['Ditindaklanjuti', 'Proses', 'Selesai'] as $status): ?>
                                            <option value="<?= $status ?>" <?= $row['status'] === $status ? 'selected' : '' ?>><?= $status ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-primary" type="submit">Simpan</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div></div>
</body>
</html>
