<?php
include '../includes/cek_session.php';
include '../config/koneksi.php';
include '../includes/akses_pelanggaran.php';

$konteks = konteks_akses_pelanggaran($koneksi);
$lingkup = lingkup_sql_pelanggaran($konteks, 'ps');

// Gunakan WHERE 1=1 jika $lingkup kosong
$where_clause = (!empty($lingkup) && trim($lingkup) != '') ? $lingkup : "1=1";

$mulai  = $_GET['mulai'] ?? '';
$sampai = $_GET['sampai'] ?? '';
$status = $_GET['status'] ?? '';

$tanggal_valid = static function ($nilai): bool {
    if (!is_string($nilai) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $nilai)) {
        return false;
    }
    $tanggal = DateTime::createFromFormat('!Y-m-d', $nilai);
    return $tanggal && $tanggal->format('Y-m-d') === $nilai;
};

$where = [$where_clause];

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

// QUERY REVISI: LEFT JOIN agar data relasi (siswa, kelas, pelanggaran, guru) terambil lengkap
$sql = "SELECT 
            ps.id,
            ps.tanggal,
            ps.poin,
            ps.tindakan,
            ps.status,
            COALESCE(s.nama, ps.nama_siswa, 'Siswa Tidak Ditemukan') AS nama_siswa,
            COALESCE(k.nama, ps.nama_kelas, '-') AS nama_kelas,
            COALESCE(p.nama, ps.nama_pelanggaran, '-') AS nama_pelanggaran,
            COALESCE(g.nama, ps.nama_guru, 'Sistem / Admin') AS nama_guru
        FROM t_pelanggaran_siswa ps
        LEFT JOIN t_siswa s ON ps.siswa_id = s.id
        LEFT JOIN t_kelas k ON ps.kelas_id = k.id
        LEFT JOIN t_pelanggaran p ON ps.pelanggaran_id = p.id
        LEFT JOIN t_guru g ON ps.guru_id = g.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY ps.tanggal DESC, ps.id DESC";

$hasil = mysqli_query($koneksi, $sql);

$data_laporan = [];
$total_poin = 0;
$jumlah_catatan = 0;

if ($hasil && mysqli_num_rows($hasil) > 0) {
    while ($row = mysqli_fetch_assoc($hasil)) {
        $total_poin += (int)$row['poin'];
        $jumlah_catatan++;
        $data_laporan[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Guru - SIP</title>

    <!-- Bootstrap 5 CSS & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../layout/style.css">
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR NAVIGASI -->
        <?php include '../layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">

            <!-- HEADER BRANDING -->
            <?php include '../layout/brand_header.php'; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-dark m-0">Laporan Pelanggaran Guru</h3>
                <a class="btn btn-outline-primary fw-semibold shadow-sm" href="../cetak_laporan.php?<?= htmlspecialchars(http_build_query(['mulai' => $mulai, 'sampai' => $sampai, 'status' => $status])) ?>" target="_blank">
                    <i class="fa-solid fa-print me-1"></i> Cetak Laporan
                </a>
            </div>

            <!-- CARD FORM FILTER LAPORAN -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white py-3 fw-bold">
                    <i class="fa-solid fa-filter text-primary me-2"></i>Filter Laporan
                </div>
                <div class="card-body">
                    <form method="get" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary" for="mulai">Dari Tanggal</label>
                            <input id="mulai" type="date" name="mulai" value="<?= htmlspecialchars($mulai) ?>" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-secondary" for="sampai">Sampai Tanggal</label>
                            <input id="sampai" type="date" name="sampai" value="<?= htmlspecialchars($sampai) ?>" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-secondary" for="status">Status Pelanggaran</label>
                            <select id="status" name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <?php foreach (['tercatat', 'Ditindaklanjuti', 'Proses', 'Selesai'] as $opsi): ?>
                                    <option value="<?= $opsi ?>" <?= $status === $opsi ? 'selected' : '' ?>>
                                        <?= htmlspecialchars(ucfirst($opsi)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button class="btn btn-primary w-100 fw-semibold" type="submit">
                                <i class="fa-solid fa-magnifying-glass me-1"></i> Cari
                            </button>
                            <a href="laporan_guru.php" class="btn btn-secondary fw-semibold">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- CARDS RINGKASAN DATA -->
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div>
                                <p class="text-muted small mb-1">Total Catatan</p>
                                <h3 class="fw-bold mb-0"><?= number_format($jumlah_catatan) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div>
                                <p class="text-muted small mb-1">Total Poin</p>
                                <h3 class="fw-bold mb-0 text-danger"><?= number_format($total_poin) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CARD TABLE DATA -->
            <div class="card border-0 shadow-sm rounded-3 mb-5">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title m-0 fw-bold">Data Laporan Pelanggaran</h5>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="tabelLaporanGuru" class="table table-striped table-hover align-middle mb-0 w-100">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center py-3">Tanggal</th>
                                    <th class="py-3">Nama Siswa</th>
                                    <th class="py-3">Kelas</th>
                                    <th class="py-3">Pelanggaran</th>
                                    <th class="py-3">Guru Pelapor</th>
                                    <th class="text-center py-3">Poin</th>
                                    <th class="py-3">Tindakan</th>
                                    <th class="text-center py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data_laporan as $row): ?>
                                    <tr>
                                        <td class="text-center small"><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
                                        <td class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_siswa']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_pelanggaran']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_guru']) ?></td>
                                        <td class="text-center fw-bold text-danger"><?= (int)$row['poin'] ?></td>
                                        <td><?= htmlspecialchars($row['tindakan'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary px-2 py-1">
                                                <?= htmlspecialchars(ucfirst($row['status'] ?? '-')) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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

<!-- DataTables JS & Bootstrap 5 Integration -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#tabelLaporanGuru').DataTable({
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
            "pageLength": 10,
            "ordering": false,
            "order": []
        });
    });
</script>
</body>
</html>