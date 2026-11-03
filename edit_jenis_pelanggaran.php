<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

batasi_akses(['admin']);
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['pesan_error'] = 'Jenis pelanggaran tidak valid.';
    header('Location: kelola_jenis_pelanggaran.php');
    exit;
}

$stmt = mysqli_prepare($koneksi, 'SELECT * FROM t_pelanggaran WHERE id = ?');
if (!$stmt) {
    error_log('Gagal menyiapkan data edit jenis pelanggaran: ' . mysqli_error($koneksi));
    $_SESSION['pesan_error'] = 'Data jenis pelanggaran belum dapat dimuat.';
    header('Location: kelola_jenis_pelanggaran.php');
    exit;
}
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$data) {
    $_SESSION['pesan_error'] = 'Jenis pelanggaran tidak ditemukan.';
    header('Location: kelola_jenis_pelanggaran.php');
    exit;
}

$kategori = mysqli_query(
    $koneksi,
    'SELECT id, nama FROM t_pelanggaran_kategori WHERE status_aktif = 1 OR id = ' . (int)$data['pelanggaran_kategori_id'] . ' ORDER BY nama ASC'
);
if (!$kategori) {
    error_log('Gagal mengambil kategori edit jenis pelanggaran: ' . mysqli_error($koneksi));
    $_SESSION['pesan_error'] = 'Kategori pelanggaran belum dapat dimuat.';
    header('Location: kelola_jenis_pelanggaran.php');
    exit;
}
$kode_form = preg_replace('/[^0-9]/', '', (string)($data['kode'] ?? ''));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Jenis Pelanggaran - SIP</title>
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
                    <h1>Edit Jenis Pelanggaran</h1>
                    <p>Perbarui nama, kategori, poin, atau status jenis pelanggaran.</p>
                </div>
            </header>
            <?php if (isset($_SESSION['pesan_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['pesan_error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['pesan_error']); ?>
            <?php endif; ?>
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-0 px-4 pt-4 pb-2">
                    <h5 class="fw-bold mb-1">Informasi Jenis Pelanggaran</h5>
                </div>
                <div class="card-body p-4">
                    <form action="proses_edit_jenis_pelanggaran.php" method="POST">
                        <input type="hidden" name="id" value="<?= (int)$data['id'] ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="kode" class="form-label fw-semibold">Kode (opsional, angka saja)</label>
                                <input id="kode" name="kode" type="text" inputmode="numeric" pattern="[0-9]*"
                                       class="form-control" maxlength="8" value="<?= htmlspecialchars(substr($kode_form, 0, 8)) ?>"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);"
                                       title="Kode hanya boleh berisi angka">
                            </div>
                            <div class="col-md-6">
                                <label for="nama" class="form-label fw-semibold">Nama Jenis Pelanggaran</label>
                                <input id="nama" name="nama" class="form-control" maxlength="150"
                                       pattern="[^0-9]+" oninput="this.value = this.value.replace(/[0-9]/g, '');"
                                       title="Nama jenis pelanggaran tidak boleh mengandung angka"
                                       value="<?= htmlspecialchars($data['nama'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="kategori" class="form-label fw-semibold">Kategori</label>
                                <select id="kategori" name="pelanggaran_kategori_id" class="form-select" required>
                                    <?php while ($row = mysqli_fetch_assoc($kategori)): ?>
                                        <option value="<?= (int)$row['id'] ?>" <?= (int)$row['id'] === (int)$data['pelanggaran_kategori_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($row['nama']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="poin" class="form-label fw-semibold">Poin</label>
                                <input id="poin" type="number" name="poin" class="form-control" min="0" max="2147483647" value="<?= (int)$data['poin'] ?>" required>
                            </div>
                            <div class="col-12">
                                <label for="deskripsi" class="form-label fw-semibold">Deskripsi</label>
                                <textarea id="deskripsi" name="deskripsi" class="form-control" rows="3" maxlength="1000"><?= htmlspecialchars($data['deskripsi'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label fw-semibold">Status</label>
                                <select id="status" name="status_aktif" class="form-select" required>
                                    <option value="1" <?= (int)$data['status_aktif'] === 1 ? 'selected' : '' ?>>Aktif</option>
                                    <option value="0" <?= (int)$data['status_aktif'] === 0 ? 'selected' : '' ?>>Tidak Aktif</option>
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
        </main>
    </div>
</div>
</body>
</html>
