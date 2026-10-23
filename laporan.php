<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';
include 'includes/akses_pelanggaran.php';

batasi_akses(['admin']);

$konteks = konteks_akses_pelanggaran($koneksi);
$lingkup = lingkup_sql_pelanggaran($konteks, 'ps');

$where_clause = (!empty($lingkup) && trim($lingkup) !== '') ? $lingkup : '1=1';

// QUERY REVISI: LEFT JOIN untuk mengambil data relasi lengkap jika kolom nama_ di ps bernilai NULL
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
        WHERE $where_clause
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
    <title>Laporan Admin - SIP</title>

    <!-- Bootstrap 5 CSS & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="layout/style.css">
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR NAVIGASI -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">

            <!-- HEADER BRANDING SIP -->
            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                <div class="bg-white p-3 rounded-3 shadow-sm me-3 border">
                    <i class="fa-solid fa-school fs-2 text-dark"></i>
                </div>
                <div>
                    <h1 class="h2 fw-bold text-dark m-0">SIP</h1>
                    <p class="text-muted m-0 small">Sistem Informasi Pelanggaran Siswa</p>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-dark m-0">Laporan Pelanggaran Admin</h3>
                <div>
                    <a href="dashboard.php" class="btn btn-secondary me-2 fw-semibold">
                        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Dashboard
                    </a>
                    <button class="btn btn-outline-danger me-2 fw-semibold" onclick="window.print();">
                        <i class="fa-solid fa-print me-1"></i> Cetak Laporan
                    </button>
                </div>
            </div>

            <!-- CARDS RINGKASAN DATA -->
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3">
                                <i class="fa-solid fa-list-check fs-3"></i>
                            </div>
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
                            <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-3 me-3">
                                <i class="fa-solid fa-star fs-3"></i>
                            </div>
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
                        <table id="tabelLaporan" class="table table-striped table-hover align-middle mb-0 w-100">
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
        $('#tabelLaporan').DataTable({
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
            "order": []
        });
    });
</script>
</body>
</html>