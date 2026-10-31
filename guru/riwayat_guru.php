<?php
include '../includes/cek_session.php';
include '../config/koneksi.php';
include '../includes/akses_pelanggaran.php';

batasi_akses(['guru']);
$konteks = konteks_akses_pelanggaran($koneksi);
$guru_id = (int)($konteks['guru_id'] ?? 0);

// QUERY SQL DENGAN LEFT JOIN UNTUK MEMASTIKAN NAMA TERISI
$sql = "SELECT 
            ps.id, 
            ps.tanggal, 
            ps.poin, 
            ps.tindakan, 
            ps.status, 
            ps.updated_at,
            COALESCE(s.nama, ps.nama_siswa, 'Siswa Tidak Ditemukan') AS nama_siswa,
            COALESCE(k.nama, ps.nama_kelas, '-') AS nama_kelas,
            COALESCE(p.nama, ps.nama_pelanggaran, '-') AS nama_pelanggaran
        FROM t_pelanggaran_siswa ps
        LEFT JOIN t_siswa s ON ps.siswa_id = s.id
        LEFT JOIN t_kelas k ON ps.kelas_id = k.id
        LEFT JOIN t_pelanggaran p ON ps.pelanggaran_id = p.id
        WHERE ps.guru_id = $guru_id
        ORDER BY ps.updated_at DESC, ps.id DESC";

$hasil = mysqli_query($koneksi, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Saya - SIP</title>

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
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4 guru-content">
            <header class="admin-page-header">
                <div class="admin-page-heading">
                    <div>
                        <h1>Riwayat Pelanggaran</h1>
                        <p>SIP · Lihat kembali pelanggaran yang pernah Anda catat.</p>
                    </div>
                </div>
            </header>

            <!-- CARD TABLE DATA -->
            <div class="card border-0 shadow-sm rounded-3 mb-5">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title m-0 fw-bold">Daftar Laporan Pelanggaran Dicatat</h5>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="tabelRiwayatSaya" class="table table-striped table-hover align-middle mb-0 w-100 history-table">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Pelanggaran</th>
                                    <th>Poin</th>
                                    <th>Tindakan</th>
                                    <th>Status</th>
                                    <th>Terakhir Diubah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($hasil && mysqli_num_rows($hasil) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($hasil)): ?>
                                        <tr>
                                            <td class="text-center small text-nowrap"><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
                                            <td class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_siswa']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                                            <td><?= htmlspecialchars($row['nama_pelanggaran']) ?></td>
                                            <td class="text-center fw-bold text-danger"><?= (int)$row['poin'] ?></td>
                                            <td><?= htmlspecialchars($row['tindakan'] ?? '-') ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary px-2 py-1">
                                                    <?= htmlspecialchars(ucfirst($row['status'] ?? '-')) ?>
                                                </span>
                                            </td>
                                            <td class="text-center small text-muted"><?= htmlspecialchars($row['updated_at'] ?? '-') ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
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
        $('#tabelRiwayatSaya').DataTable({
            "autoWidth": false,
            "columnDefs": [
                { "targets": 0, "width": "10%" },
                { "targets": 1, "width": "14%" },
                { "targets": 2, "width": "9%" },
                { "targets": 3, "width": "18%" },
                { "targets": 4, "width": "6%", "className": "text-center" },
                { "targets": 5, "width": "17%" },
                { "targets": 6, "width": "12%", "className": "text-center" },
                { "targets": 7, "width": "14%", "className": "text-center" }
            ],
            "language": {
                "lengthMenu": "Tampilkan _MENU_ data",
                "zeroRecords": "Belum ada riwayat pelanggaran yang dicatat",
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