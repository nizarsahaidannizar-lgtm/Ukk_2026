<?php
include '../includes/cek_session.php';
include '../config/koneksi.php';
include '../includes/akses_pelanggaran.php';

$konteks = konteks_akses_pelanggaran($koneksi);
$lingkup = lingkup_sql_pelanggaran($konteks, 'ps');
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION['pesan_error'] = 'Catatan pelanggaran tidak valid.';
    header('Location: catat_pelanggaran.php');
    exit;
}

$stmt = mysqli_prepare($koneksi, "SELECT ps.* FROM t_pelanggaran_siswa ps WHERE ps.id = ? AND $lingkup");
if (!$stmt) {
    error_log('Gagal menyiapkan data edit catatan: ' . mysqli_error($koneksi));
    $_SESSION['pesan_error'] = 'Data catatan belum dapat dimuat.';
    header('Location: catat_pelanggaran.php');
    exit;
}
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$catatan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$catatan) {
    $_SESSION['pesan_error'] = 'Catatan tidak ditemukan atau Anda tidak memiliki akses.';
    header('Location: catat_pelanggaran.php');
    exit;
}

$siswa = mysqli_query(
    $koneksi,
    'SELECT id, nis, nama FROM t_siswa WHERE status_aktif = 1 OR id = ' . (int)$catatan['siswa_id'] . ' ORDER BY nama ASC'
);
$tahun = mysqli_query(
    $koneksi,
    'SELECT id, nama FROM t_tahun_ajaran WHERE status_aktif = 1 OR id = ' . (int)$catatan['tahun_ajaran_id'] . ' ORDER BY id DESC'
);
$filter_kelas = '';
if ($konteks['is_wali']) {
    $kelas_ids = implode(',', array_map('intval', $konteks['kelas_ids']));
    $filter_kelas = ' AND (id IN (' . $kelas_ids . ') OR id = ' . (int)$catatan['kelas_id'] . ')';
}
$kelas = mysqli_query(
    $koneksi,
    'SELECT id, nama, tingkat, jurusan FROM t_kelas WHERE (status_aktif = 1 OR id = ' . (int)$catatan['kelas_id'] . ')' .
        $filter_kelas .
        ' ORDER BY nama ASC'
);
$pelanggaran = mysqli_query(
    $koneksi,
    'SELECT id, nama, poin, pelanggaran_kategori_id FROM t_pelanggaran WHERE status_aktif = 1 OR id = ' . (int)$catatan['pelanggaran_id'] . ' ORDER BY nama ASC'
);

if (!$siswa || !$tahun || !$kelas || !$pelanggaran) {
    error_log('Gagal memuat pilihan edit catatan: ' . mysqli_error($koneksi));
    $_SESSION['pesan_error'] = 'Form edit catatan belum dapat dimuat.';
    header('Location: catat_pelanggaran.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Catatan Pelanggaran - SIP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../layout/style.css">
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <?php include '../layout/sidebar.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4 guru-content">
            <header class="admin-page-header">
                <div class="admin-page-heading">
                    <div>
                        <h1>Edit Catatan Pelanggaran</h1>
                        <p>SIP · Perbarui informasi catatan pelanggaran siswa.</p>
                    </div>
                </div>
            </header>

            <?php if (isset($_SESSION['pesan_sukses'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['pesan_sukses']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['pesan_sukses']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['pesan_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['pesan_error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['pesan_error']); ?>
            <?php endif; ?>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-0 px-4 pt-4 pb-2">
                    <h5 class="fw-bold mb-1 text-dark">Data Pelanggaran</h5>
                    <p class="text-muted small mb-0">Periksa kembali data sebelum menyimpan perubahan.</p>
                </div>
                <div class="card-body p-4">
                    <form action="proses_edit_catatan_pelanggaran.php" method="POST">
                        <input type="hidden" name="id" value="<?= (int)$catatan['id'] ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="siswa" class="form-label">Siswa</label>
                                <select id="siswa" name="siswa_id" class="form-select" required>
                                    <?php while ($row = mysqli_fetch_assoc($siswa)): ?>
                                        <option value="<?= (int)$row['id'] ?>" <?= (int)$row['id'] === (int)$catatan['siswa_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($row['nis'] . ' - ' . $row['nama']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tahun" class="form-label">Tahun Ajaran</label>
                                <select id="tahun" name="tahun_ajaran_id" class="form-select" required>
                                    <?php while ($row = mysqli_fetch_assoc($tahun)): ?>
                                        <option value="<?= (int)$row['id'] ?>" <?= (int)$row['id'] === (int)$catatan['tahun_ajaran_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($row['nama']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="kelas" class="form-label">Kelas</label>
                                <select id="kelas" name="kelas_id" class="form-select" required>
                                    <?php while ($row = mysqli_fetch_assoc($kelas)): ?>
                                        <option value="<?= (int)$row['id'] ?>" <?= (int)$row['id'] === (int)$catatan['kelas_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($row['nama'] . ' - ' . $row['tingkat'] . ' - ' . $row['jurusan']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="pelanggaran" class="form-label">Jenis Pelanggaran</label>
                                <select id="pelanggaran" name="pelanggaran_id" class="form-select" required>
                                    <?php while ($row = mysqli_fetch_assoc($pelanggaran)): ?>
                                        <option value="<?= (int)$row['id'] ?>"
                                                data-poin="<?= (int)$row['poin'] ?>"
                                                data-kategori="<?= (int)$row['pelanggaran_kategori_id'] ?>"
                                                <?= (int)$row['id'] === (int)$catatan['pelanggaran_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($row['nama'] . ' (' . $row['poin'] . ' poin)') ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal" class="form-label">Tanggal</label>
                                <input id="tanggal" type="date" name="tanggal" class="form-control"
                                       value="<?= htmlspecialchars($catatan['tanggal'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="poin" class="form-label">Poin Pelanggaran</label>
                                <input id="poin" type="number" class="form-control"
                                       value="<?= (int)$catatan['poin'] ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-select" required>
                                    <?php foreach (['tercatat', 'Ditindaklanjuti', 'Proses', 'Selesai'] as $status): ?>
                                        <option value="<?= htmlspecialchars($status) ?>" <?= $catatan['status'] === $status ? 'selected' : '' ?>>
                                            <?= htmlspecialchars(ucfirst($status)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="keterangan" class="form-label">Keterangan</label>
                                <textarea id="keterangan" name="keterangan" class="form-control" rows="3" maxlength="1000"><?= htmlspecialchars($catatan['keterangan'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tindakan" class="form-label">Tindakan</label>
                                <textarea id="tindakan" name="tindakan" class="form-control" rows="3" maxlength="1000"><?= htmlspecialchars($catatan['tindakan'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary fw-semibold px-4">Simpan</button>
                            <a href="catat_pelanggaran.php" class="btn btn-outline-secondary fw-semibold px-4">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
<script>
document.getElementById('pelanggaran').addEventListener('change', function () {
    const option = this.options[this.selectedIndex];
    document.getElementById('poin').value = option.dataset.poin || '0';
});
</script>
</body>
</html>
