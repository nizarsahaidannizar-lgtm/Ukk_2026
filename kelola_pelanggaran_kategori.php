<?php
// kelola_pelanggaran_kategori.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

$sql = "SELECT * FROM t_pelanggaran_kategori ORDER BY id DESC";
$hasil = mysqli_query($koneksi, $sql);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori Pelanggaran</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="layout/style.css">
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">

            <!-- HEADER & TOMBOL TAMBAH KATEGORI PELANGGARAN -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2 m-0">Kelola Kategori Pelanggaran</h1>

                <a href="tambah_pelanggaran_kategori.php" class="btn btn-primary fw-semibold">
                    + Tambah Kategori Pelanggaran
                </a>
            </div>

            <!-- TOMBOL KEMBALI KE DASHBOARD -->
            <a href="dashboard.php" class="btn btn-secondary mb-3">
                Kembali ke Dashboard
            </a>

            <!-- CARD TABEL DATA KATEGORI PELANGGARAN -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body">
                    <h5 class="card-title m-0 fw-bold">Data Tahun Ajaran</h5>


                    <div class="table-responsive">
                        <table id="tabelKategori" class="table table-sm table-striped table-bordered table-hover align-middle w-100">

                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center" style="width: 50px;">No.</th>
                                    <th>Nama Kategori</th>
                                    <th>Deskripsi</th>
                                    <th class="text-center">Status Aktif</th>
                                    <th>Dibuat</th>
                                    <th>Diubah</th>
                                    <th class="text-center" style="width: 120px;">Aksi</th>
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

                                    <td>
                                        <?= htmlspecialchars($row['deskripsi'] ?? '-') ?>
                                    </td>

                                    <td class="text-center">
                                        <?php if ($row['status_aktif'] == 1) { ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php } else { ?>
                                            <span class="badge bg-danger">Tidak Aktif</span>
                                        <?php } ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['created_at']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['updated_at']) ?>
                                    </td>

                                    <td class="text-center text-nowrap">
                                        <a href="edit_pelanggaran_kategori.php?id=<?= urlencode($row['id']) ?>" class="btn btn-warning btn-sm me-1">
                                            Edit
                                        </a>

                                        <a href="hapus_pelanggaran_kategori.php?id=<?= urlencode($row['id']) ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Yakin ingin menghapus kategori pelanggaran ini?');">
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
    $('#tabelKategori').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [[1, 'asc']], // Urutkan berdasarkan Nama Kategori
        scrollX: true,
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data kategori",
            infoEmpty: "Tidak ada data kategori",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Data kategori tidak ditemukan",
            emptyTable: "Belum ada data kategori",
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