<?php
// guru/catat_pelanggaran.php
include '../includes/cek_session.php';
include '../config/koneksi.php';
include '../includes/akses_pelanggaran.php';

$konteks_akses = konteks_akses_pelanggaran($koneksi);
$lingkup_akses = lingkup_sql_pelanggaran($konteks_akses, 'ps');

// AMBIL DATA DROP DOWN FORM
$siswa = mysqli_query($koneksi, "SELECT id, nis, nama FROM t_siswa WHERE status_aktif = 1 ORDER BY nama ASC");
$tahun = mysqli_query($koneksi, "SELECT id, nama FROM t_tahun_ajaran WHERE status_aktif = 1 ORDER BY id DESC");
$filter_kelas = '';
if ($konteks_akses['is_wali']) {
    $kelas_ids = implode(',', array_map('intval', $konteks_akses['kelas_ids']));
    $filter_kelas = ' AND id IN (' . $kelas_ids . ')';
}
$kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama, tingkat, jurusan FROM t_kelas
     WHERE status_aktif = 1 $filter_kelas
     ORDER BY nama ASC"
);
$pelanggaran = mysqli_query($koneksi, "SELECT id, nama, poin, pelanggaran_kategori_id FROM t_pelanggaran WHERE status_aktif = 1 ORDER BY nama ASC");

$stmt_guru = mysqli_prepare($koneksi, 'SELECT id, nama FROM t_guru WHERE user_id = ? AND status_aktif = 1 LIMIT 1');
$id_user = (int)($_SESSION['id_user'] ?? 0);
mysqli_stmt_bind_param($stmt_guru, 'i', $id_user);
mysqli_stmt_execute($stmt_guru);
$guru_login = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_guru)) ?: [];

// QUERY REVISI TOTAL: Menggunakan COALESCE / fallback dan menghilangkan klausa WHERE yang membatasi
$sql = "SELECT
            ps.id,
            ps.tanggal,
            ps.poin,
            ps.tindakan,
            ps.status,
            COALESCE(s.nama, 'Siswa Tidak Ditemukan') AS nama_siswa,
            COALESCE(s.nis, '-') AS nis,
            COALESCE(k.nama, '') AS nama_kelas,
            COALESCE(k.jurusan, '') AS jurusan,
            COALESCE(ta.nama, '-') AS tahun_ajaran,
            COALESCE(p.nama, '-') AS nama_pelanggaran,
            COALESCE(g.nama, 'Sistem / Admin') AS nama_guru
        FROM t_pelanggaran_siswa ps
        LEFT JOIN t_siswa s ON ps.siswa_id = s.id
        LEFT JOIN t_kelas k ON ps.kelas_id = k.id
        LEFT JOIN t_tahun_ajaran ta ON ps.tahun_ajaran_id = ta.id
        LEFT JOIN t_pelanggaran p ON ps.pelanggaran_id = p.id
        LEFT JOIN t_guru g ON ps.guru_id = g.id
        WHERE $lingkup_akses
        ORDER BY ps.id DESC";

$hasil = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catat Pelanggaran Siswa</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <?php include '../layout/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">
            <?php include '../layout/brand_header.php'; ?>
            <h2 class="mb-4">Catat Pelanggaran Siswa</h2>

            <?php if (isset($_SESSION['pesan_sukses'])) { ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_SESSION['pesan_sukses']); unset($_SESSION['pesan_sukses']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <?php if (isset($_SESSION['pesan_error'])) { ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_SESSION['pesan_error']); unset($_SESSION['pesan_error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <!-- FORM CATAT PELANGGARAN -->
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-white py-3 ">Form Catat Pelanggaran</div>
                <div class="card-body">
                    <form action="proses_simpan_pelanggaran.php" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Siswa</label>
                                <select name="siswa_id" class="form-select" required>
                                    <option value="">-- Pilih Siswa --</option>
                                    <?php while ($s = mysqli_fetch_assoc($siswa)) { ?>
                                        <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['nis'] . ' - ' . $s['nama']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tahun Ajaran</label>
                                <select name="tahun_ajaran_id" class="form-select" required>
                                    <option value="">-- Pilih Tahun Ajaran --</option>
                                    <?php while ($ta = mysqli_fetch_assoc($tahun)) { ?>
                                        <option value="<?php echo $ta['id']; ?>"><?php echo htmlspecialchars($ta['nama']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kelas</label>
                                <select name="kelas_id" class="form-select" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    <?php while ($k = mysqli_fetch_assoc($kelas)) { ?>
                                        <option value="<?php echo $k['id']; ?>"><?php echo htmlspecialchars($k['nama'] . ' - ' . $k['tingkat'] . ' - ' . $k['jurusan']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Pelanggaran</label>
                                <select name="pelanggaran_id" id="pelanggaran" class="form-select" required>
                                    <option value="">-- Pilih Pelanggaran --</option>
                                    <?php while ($p = mysqli_fetch_assoc($pelanggaran)) { ?>
                                        <option value="<?php echo $p['id']; ?>" data-poin="<?php echo $p['poin']; ?>" data-kategori="<?php echo $p['pelanggaran_kategori_id']; ?>"><?php echo htmlspecialchars($p['nama'] . ' (' . $p['poin'] . ' poin)'); ?></option>
                                    <?php } ?>
                                </select>
                                <input type="hidden" name="pelanggaran_kategori_id" id="kategori">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Guru Pelapor</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($guru_login['nama'] ?? $_SESSION['nama'] ?? '') ?>" readonly>
                                <input type="hidden" name="guru_id" value="<?= (int)($guru_login['id'] ?? 0) ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" name="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Poin Pelanggaran</label>
                                <input type="number" name="poin" id="poin" class="form-control" min="0" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="Ditindaklanjuti">Ditindaklanjuti</option>
                                    <option value="Proses">Proses</option>
                                    <option value="Selesai">Selesai</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tindakan</label>
                                <textarea name="tindakan" class="form-control" rows="3"></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary fw-semibold px-4">Simpan Pelanggaran</button>
                    </form>
                </div>
            </div>

            <!-- TABEL DATA CATATAN PELANGGARAN -->
            <div class="card shadow-sm border-0 mb-5">
                <div class="card-header bg-white py-3 fw-bold">Data Catatan Pelanggaran</div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="tabelPelanggaran" class="table table-striped table-hover align-middle mb-0 w-100">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center py-3">No</th>
                                    <th class="py-3">Nama Siswa</th>
                                    <th class="py-3">Kelas</th>
                                    <th class="py-3">Tahun Ajaran</th>
                                    <th class="py-3">Pelanggaran</th>
                                    <th class="py-3">Guru</th>
                                    <th class="text-center py-3">Tanggal</th>
                                    <th class="text-center py-3">Poin</th>
                                    <th class="py-3">Tindakan</th>
                                    <th class="text-center py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1; 
                                if ($hasil && mysqli_num_rows($hasil) > 0) {
                                    while ($row = mysqli_fetch_assoc($hasil)) { ?>
                                        <tr>
                                            <td class="text-center fw-semibold"><?= $no++ ?></td>
                                            <td class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_siswa']) ?></td>
                                            <td><?= htmlspecialchars(trim($row['nama_kelas'] . ' ' . $row['jurusan'])) ?: '-' ?></td>
                                            <td><?= htmlspecialchars($row['tahun_ajaran']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_pelanggaran']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_guru']) ?></td>
                                            <td class="text-center"><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
                                            <td class="text-center fw-bold"><?= htmlspecialchars($row['poin'] ?? '0') ?></td>
                                            <td><?= htmlspecialchars($row['tindakan'] ?? '-') ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary px-2 py-1">
                                                    <?= htmlspecialchars($row['status'] ?? '-') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php } 
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- jQuery & Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
    document.getElementById('pelanggaran')?.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        document.getElementById('kategori').value = selected.dataset.kategori || '';
        document.getElementById('poin').value = selected.dataset.poin || '';
    });

    $(document).ready(function() {
        $('#tabelPelanggaran').DataTable({
            "language": {
                "lengthMenu": "Tampilkan _MENU_ data",
                "zeroRecords": "Data tidak ditemukan",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                "infoFiltered": "(disaring dari total _MAX_ data)",
                "search": "Cari:",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            },
            "pageLength": 10
        });
    });
</script>
</body>
</html>