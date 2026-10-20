<?php
// kelola_tahun_ajaran.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

$sql = "SELECT * FROM t_tahun_ajaran ORDER BY tanggal_mulai DESC";
$hasil = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tahun Ajaran</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="layout/style.css">

    <!-- Custom CSS Penyelaras Ukuran Header Tabel -->
    <style>
        #tabelTahunAjaran thead th {
            font-size: 0.875rem; /* Ukuran teks header lebih proporsional */
            vertical-align: middle;
            white-space: nowrap; /* Mencegah teks bertumpuk berlebihan */
        }
        #tabelTahunAjaran tbody td {
            font-size: 0.875rem;
        }
    </style>
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">

            <!-- HEADER & TOMBOL TAMBAH TAHUN AJARAN -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2 m-0">Kelola Tahun Ajaran</h1>

                <a href="tambah_tahun_ajaran.php" class="btn btn-primary fw-semibold">
                    + Tambah Tahun Ajaran
                </a>
            </div>

            <!-- TOMBOL KEMBALI KE DASHBOARD -->
            <a href="dashboard.php" class="btn btn-secondary mb-3">
                Kembali ke Dashboard
            </a>

                <!-- CARD TABEL TAHUN AJARAN -->
            <div class="card shadow-sm border-0 rounded-3 mb-5">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title m-0 fw-bold">Data Tahun Ajaran</h5>
                </div>


                   
                    <div class="table-responsive">
                        <table id="tabelTahunAjaran" class="table table-sm table-striped table-bordered table-hover align-middle w-100">

                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center" style="width: 40px;">No.</th>
                                    <th>Nama Tahun Ajaran</th>
                                    <th class="text-center">Tanggal Mulai</th>
                                    <th class="text-center">Tanggal Selesai</th>
                                    <th class="text-center">Status Aktif</th>
                                    <th class="text-center">Dibuat</th>
                                    <th class="text-center">Diubah</th>
                                    <th class="text-center" style="width: 110px;">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($hasil)) { ?>

                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>

                                    <td>
                                        <?= htmlspecialchars($row['nama']) ?>
                                    </td>

                                    <td class="text-center">
                                        <?= htmlspecialchars($row['tanggal_mulai']) ?>
                                    </td>

                                    <td class="text-center">
                                        <?= htmlspecialchars($row['tanggal_selesai']) ?>
                                    </td>

                                    <td class="text-center">
                                        <?php if ($row['status_aktif'] == 1) { ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php } else { ?>
                                            <span class="badge bg-danger">Tidak Aktif</span>
                                        <?php } ?>
                                    </td>

                                    <td class="text-center">
                                        <?= htmlspecialchars($row['created_at']) ?>
                                    </td>

                                    <td class="text-center">
                                        <?= htmlspecialchars($row['updated_at']) ?>
                                    </td>

                                    <td class="text-center text-nowrap">
                                        <a href="edit_tahun_ajaran.php?id=<?= urlencode($row['id']) ?>" class="btn btn-warning btn-sm me-1">
                                            Edit
                                        </a>

                                        <a href="hapus_tahun_ajaran.php?id=<?= urlencode($row['id']) ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Yakin ingin menghapus tahun ajaran ini?');">
                                            Hapus
                                        </a>
                                    </td>
                                </tr>

                                <?php } ?>
                            </tbody>

                        </table>
                    </div>

                </div>
            </div>

        </main>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>

<!-- DataTables Bootstrap 5 JS -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {
    $('#tabelTahunAjaran').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [[2, 'desc']], // Urutkan berdasarkan Tanggal Mulai secara descending
        scrollX: true,
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data tahun ajaran",
            infoEmpty: "Tidak ada data tahun ajaran",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Data tahun ajaran tidak ditemukan",
            emptyTable: "Belum ada data tahun ajaran",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        }
    });
});
</script>

</body>
</html>