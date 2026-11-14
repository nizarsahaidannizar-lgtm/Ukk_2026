
<?php
// kelola_kelas.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// Hanya admin
batasi_akses(['admin']);

// ========================================
// AMBIL DATA KELAS
// ========================================

$sql = "SELECT * FROM t_kelas ORDER BY nama ASC";
$hasil = mysqli_query($koneksi, $sql);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Kelas - SIP</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <link rel="stylesheet" href="layout/style.css">

</head>

<body class="bg-light">

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">

            <!-- HEADER -->
            <header class="admin-page-header mb-4">

                <h1>Kelola Data Kelas</h1>

                <p>Atur daftar kelas, tingkat, jurusan, dan statusnya.</p>

            </header>

            <!-- ================================= -->
            <!-- NOTIFIKASI -->
            <!-- ================================= -->

            <?php if (isset($_SESSION['pesan_sukses'])): ?>

                <div class="alert alert-success alert-dismissible fade show mb-3">

                    <i class="fa-solid fa-circle-check me-2"></i>

                    <?= htmlspecialchars($_SESSION['pesan_sukses']) ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

                </div>

                <?php unset($_SESSION['pesan_sukses']); ?>

            <?php endif; ?>


            <?php if (isset($_SESSION['pesan_error'])): ?>

                <div class="alert alert-danger alert-dismissible fade show mb-3">

                    <i class="fa-solid fa-triangle-exclamation me-2"></i>

                    <?= htmlspecialchars($_SESSION['pesan_error']) ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

                </div>

                <?php unset($_SESSION['pesan_error']); ?>

            <?php endif; ?>


            <!-- ERROR DATABASE -->
            <?php if (!$hasil): ?>

                <div class="alert alert-danger">

                    Gagal mengambil data kelas:
                    <?= htmlspecialchars(mysqli_error($koneksi)) ?>

                </div>

            <?php endif; ?>


            <!-- ================================= -->
            <!-- TABEL KELAS -->
            <!-- ================================= -->

            <div class="card shadow-sm border-0 rounded-3 mb-5">

                <div class="card-header bg-white py-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">

                    <h5 class="card-title m-0 fw-bold">

                        <i class="fa-solid fa-school me-2"></i>

                        Data Kelas

                    </h5>

                    <a href="tambah_kelas.php" class="btn btn-primary btn-sm fw-semibold text-nowrap">

                        <i class="fa-solid fa-plus me-1"></i>

                        Tambah Kelas

                    </a>

                </div>


                <div class="card-body p-4">

                    <div class="table-responsive">

                        <table id="tabelKelas"
                            class="table table-striped table-bordered table-hover align-middle w-100">

                            <thead class="table-dark">

                                <tr>

                                    <th>No.</th>
                                    <th>Nama Kelas</th>
                                    <th>Tingkat</th>
                                    <th>Jurusan</th>
                                    <th>Status Aktif</th>
                                    <th>Dibuat</th>
                                    <th>Diubah</th>
                                    <th class="text-center action-column">Aksi</th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php if ($hasil): ?>

                                    <?php $no = 1; ?>

                                    <?php while ($row = mysqli_fetch_assoc($hasil)): ?>

                                        <tr>

                                            <td>
                                                <?= $no++ ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['nama'] ?? '-') ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['tingkat'] ?? '-') ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['jurusan'] ?? '-') ?>
                                            </td>

                                            <td>

                                                <?php if ((int)($row['status_aktif'] ?? 0) === 1): ?>

                                                    <span class="badge bg-success">
                                                        Aktif
                                                    </span>

                                                <?php else: ?>

                                                    <span class="badge bg-danger">
                                                        Tidak Aktif
                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                            <td class="text-nowrap">

                                                <?php
                                                $created = $row['created_at'] ?? '';

                                                echo !empty($created)
                                                    ? date('d-m-Y H:i', strtotime($created))
                                                    : '-';
                                                ?>

                                            </td>

                                            <td class="text-nowrap">

                                                <?php
                                                $updated = $row['updated_at'] ?? '';

                                                echo !empty($updated)
                                                    ? date('d-m-Y H:i', strtotime($updated))
                                                    : '-';
                                                ?>

                                            </td>

                                            <td class="text-center text-nowrap action-column">

                                                <!-- EDIT -->
                                                <a href="edit_kelas.php?id=<?= urlencode($row['id']) ?>"
                                                    class="btn btn-warning btn-sm me-1 fw-semibold text-white"
                                                    title="Edit">

                                                    <i class="fa-solid fa-pen-to-square"></i>

                                                </a>


                                                <!-- HAPUS -->
                                                <a href="hapus_kelas.php?id=<?= urlencode($row['id']) ?>"
                                                    class="btn btn-danger btn-sm fw-semibold"
                                                    title="Hapus"
                                                    onclick="return confirm('Yakin ingin menghapus data kelas ini?');">

                                                    <i class="fa-solid fa-trash-can"></i>

                                                </a>

                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                    <?php if ($hasil && mysqli_num_rows($hasil) === 0): ?>

                        <div class="alert alert-info mt-3 mb-0">

                            Belum ada data kelas.

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </main>

    </div>

</div>


<!-- SCRIPT -->

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<script>

$(document).ready(function () {

    $('#tabelKelas').DataTable({

        pageLength: 10,

        lengthMenu: [5, 10, 25, 50, 100],

        order: [[1, 'asc']],

        autoWidth: false,

        columnDefs: [
            { targets: [0, -1], orderable: false }
        ],

        language: {

            search: "Cari:",

            lengthMenu: "Tampilkan _MENU_ data",

            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data kelas",

            infoEmpty: "Tidak ada data kelas",

            infoFiltered: "(difilter dari _MAX_ total data)",

            zeroRecords: "Data kelas tidak ditemukan",

            emptyTable: "Belum ada data kelas",

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
