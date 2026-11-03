<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

batasi_akses(['admin']);

$sql = "SELECT p.id, p.kode, p.nama, p.poin, p.deskripsi, p.status_aktif,
               p.created_at, p.updated_at, k.nama AS nama_kategori
        FROM t_pelanggaran p
        LEFT JOIN t_pelanggaran_kategori k ON p.pelanggaran_kategori_id = k.id
        ORDER BY p.nama ASC, p.id ASC";
$hasil = mysqli_query($koneksi, $sql);
if (!$hasil) {
    error_log('Gagal mengambil daftar jenis pelanggaran: ' . mysqli_error($koneksi));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jenis Pelanggaran - SIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="layout/style.css">
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <?php include 'layout/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">
            <header class="admin-page-header">
                <div>
                    <h1>Kelola Jenis Pelanggaran</h1>
                    <p>Atur nama, kategori, poin, dan status jenis pelanggaran.</p>
                </div>
            </header>

            <?php foreach (['pesan_sukses' => 'success', 'pesan_error' => 'danger'] as $key => $type): ?>
                <?php if (isset($_SESSION[$key])): ?>
                    <div class="alert alert-<?= $type ?> alert-dismissible fade show mb-3" role="alert">
                        <?= htmlspecialchars($_SESSION[$key]) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION[$key]); ?>
                <?php endif; ?>
            <?php endforeach; ?>

            <div class="card border-0 shadow-sm rounded-3 mb-5">
                <div class="card-header bg-white py-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <h5 class="card-title m-0 fw-bold">Data Jenis Pelanggaran</h5>
                    <a href="tambah_jenis_pelanggaran.php" class="btn btn-primary btn-sm fw-semibold">Tambah Jenis Pelanggaran</a>
                </div>
                <div class="card-body p-3">
                    <?php if (!$hasil): ?>
                        <div class="alert alert-warning mb-0" role="status">Daftar jenis pelanggaran belum dapat dimuat.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table id="tabelJenisPelanggaran" class="table table-striped table-hover align-middle w-100">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center">No.</th>
                                        <th>Kode</th>
                                        <th>Nama Jenis Pelanggaran</th>
                                        <th>Kategori</th>
                                        <th class="text-center">Poin</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($hasil) > 0): ?>
                                        <?php $no = 1; while ($row = mysqli_fetch_assoc($hasil)): ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td><?= htmlspecialchars($row['kode'] ?: '-') ?></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($row['nama'] ?? '-') ?></strong>
                                                    <?php if (!empty($row['deskripsi'])): ?>
                                                        <div class="small text-muted"><?= htmlspecialchars($row['deskripsi']) ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($row['nama_kategori'] ?? '-') ?></td>
                                                <td class="text-center"><?= number_format((int)$row['poin']) ?></td>
                                                <td class="text-center">
                                                    <span class="badge <?= (int)$row['status_aktif'] === 1 ? 'bg-success' : 'bg-secondary' ?>">
                                                        <?= (int)$row['status_aktif'] === 1 ? 'Aktif' : 'Tidak Aktif' ?>
                                                    </span>
                                                </td>
                                                <td class="text-center text-nowrap">
                                                    <a href="edit_jenis_pelanggaran.php?id=<?= (int)$row['id'] ?>" class="btn btn-warning btn-sm text-white">Edit</a>
                                                    <?php if ((int)$row['status_aktif'] === 1): ?>
                                                        <form action="hapus_jenis_pelanggaran.php" method="POST" class="d-inline"
                                                              onsubmit="return confirm('Nonaktifkan jenis pelanggaran ini? Riwayat yang sudah tercatat tetap disimpan.');">
                                                            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                                            <button type="submit" class="btn btn-outline-danger btn-sm">Nonaktifkan</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function () {
    $('#tabelJenisPelanggaran').DataTable({
        pageLength: 10,
        order: [[2, 'asc']],
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ jenis pelanggaran',
            infoEmpty: 'Belum ada jenis pelanggaran',
            emptyTable: 'Belum ada jenis pelanggaran',
            zeroRecords: 'Jenis pelanggaran tidak ditemukan',
            paginate: { first: 'Pertama', last: 'Terakhir', next: 'Berikutnya', previous: 'Sebelumnya' }
        }
    });
});
</script>
</body>
</html>
