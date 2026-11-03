<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

batasi_akses(['admin']);
$kategori = mysqli_query($koneksi, 'SELECT id, nama FROM t_pelanggaran_kategori WHERE status_aktif = 1 ORDER BY nama ASC');
if (!$kategori) {
    error_log('Gagal mengambil kategori pelanggaran: ' . mysqli_error($koneksi));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Jenis Pelanggaran - SIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="layout/style.css">
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <?php include 'layout/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">
            <header class="admin-page-header">
                <div>
                    <h1>Tambah Jenis Pelanggaran</h1>
                    <p>Tambahkan jenis pelanggaran beserta kategori dan poinnya.</p>
                </div>
            </header>
            <?php if (isset($_SESSION['pesan_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['pesan_error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['pesan_error']); ?>
            <?php endif; ?>
            <?php if (!$kategori): ?>
                <div class="alert alert-warning" role="alert">Kategori pelanggaran belum dapat dimuat.</div>
            <?php elseif (mysqli_num_rows($kategori) === 0): ?>
                <div class="alert alert-warning" role="alert">
                    Tambahkan kategori pelanggaran terlebih dahulu.
                    <a href="kelola_pelanggaran_kategori.php" class="alert-link">Kelola Kategori Pelanggaran</a>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-2">
                        <h5 class="fw-bold mb-1">Informasi Jenis Pelanggaran</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="proses_tambah_jenis_pelanggaran.php" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="kode" class="form-label fw-semibold">Kode (opsional, angka saja)</label>
                                    <input id="kode" name="kode" type="text" inputmode="numeric" pattern="[0-9]*"
                                           class="form-control" maxlength="8" placeholder="Contoh: 00000001"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);"
                                           title="Kode hanya boleh berisi angka">
                                </div>
                                <div class="col-md-6">
                                    <label for="nama" class="form-label fw-semibold">Nama Jenis Pelanggaran</label>
                                    <input id="nama" name="nama" class="form-control" maxlength="150"
                                           pattern="[^0-9]+" oninput="this.value = this.value.replace(/[0-9]/g, '');"
                                           title="Nama jenis pelanggaran tidak boleh mengandung angka" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="kategori" class="form-label fw-semibold">Kategori</label>
                                    <select id="kategori" name="pelanggaran_kategori_id" class="form-select" required>
                                        <option value="">Pilih kategori</option>
                                        <?php while ($row = mysqli_fetch_assoc($kategori)): ?>
                                            <option value="<?= (int)$row['id'] ?>"><?= htmlspecialchars($row['nama']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="poin" class="form-label fw-semibold">Poin</label>
                                    <input id="poin" type="number" name="poin" class="form-control" min="0" max="2147483647" value="0" required>
                                </div>
                                <div class="col-12">
                                    <label for="deskripsi" class="form-label fw-semibold">Deskripsi</label>
                                    <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3" maxlength="1000"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label fw-semibold">Status</label>
                                    <select id="status" name="status_aktif" class="form-select" required>
                                        <option value="1" selected>Aktif</option>
                                        <option value="0">Tidak Aktif</option>
                                    </select>
                                </div>
                                <div class="col-12 d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary px-4 fw-semibold">Simpan</button>
                                    <a href="kelola_jenis_pelanggaran.php" class="btn btn-outline-secondary px-4">Kembali</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>
</body>
</html>
