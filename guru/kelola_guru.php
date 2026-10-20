<?php
// guru/kelola_guru.php
include '../includes/cek_session.php';
include '../config/koneksi.php';

$sql = "SELECT * FROM t_guru ORDER BY nama ASC";
$hasil = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Guru - SIP</title>

    <!-- Bootstrap 5 CSS & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../layout/style.css">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include '../layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">

            <!-- HEADER BRANDING SIP & LOGO SEKOLAH -->
            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                <div class="bg-white p-3 rounded-3 shadow-sm me-3 border">
                    <i class="fa-solid fa-school fs-2 text-dark"></i>
                </div>
                <div>
                    <h1 class="h2 fw-bold text-dark m-0">SIP</h1>
                    <p class="text-muted m-0 small">Sistem Informasi Pelanggaran Siswa</p>
                </div>
            </div>

            <!-- TOMBOL KEMBALI & TAMBAH GURU -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="../dashboard.php" class="btn btn-secondary fw-semibold">
                    Kembali ke Dashboard
                </a>
                <a href="tambah_guru.php" class="btn btn-primary fw-semibold">
                    + Tambah Guru
                </a>
            </div>

            <!-- ALERT NOTIFIKASI SUKSES / ERROR -->
            <?php if (isset($_SESSION['pesan_sukses'])) { ?>
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    <?= $_SESSION['pesan_sukses']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['pesan_sukses']); ?>
            <?php } ?>

            <?php if (isset($_SESSION['pesan_error'])) { ?>
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                    <?= $_SESSION['pesan_error']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['pesan_error']); ?>
            <?php } ?>

            <!-- CARD TABEL GURU -->
            <div class="card shadow-sm border-0 rounded-3 mb-5">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title m-0 fw-bold">Data Guru</h5>
                </div>
                <div class="card-body p-4">

                    <div class="table-responsive">
                        <table id="tabelGuru" class="table table-striped table-bordered table-hover align-middle w-100">

                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>NIP</th>
                                    <th>Nama Guru</th>
                                    <th>Email</th>
                                    <th>Status Aktif</th>
                                    <th>Dibuat</th>
                                    <th>Diubah</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php 
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($hasil)) { ?>
                                    <tr>
                                        <td><?= $no++ ?></td>

                                        <td><?= htmlspecialchars($row['nip'] ?? '') ?></td>

                                        <td><?= htmlspecialchars($row['nama'] ?? '') ?></td>

                                        <td><?= htmlspecialchars($row['email'] ?? '') ?></td>

                                        <td>
                                            <?php if ($row['status_aktif'] == 1) { ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php } else { ?>
                                                <span class="badge bg-secondary">Tidak Aktif</span>
                                            <?php } ?>
                                        </td>

                                        <td><?= htmlspecialchars($row['created_at'] ?? '') ?></td>

                                        <td><?= htmlspecialchars($row['updated_at'] ?? '') ?></td>

                                        <td class="text-nowrap">
                                            <a href="edit_guru.php?id=<?= urlencode($row['id']) ?>"
                                               class="btn btn-warning btn-sm fw-semibold text-white">
                                                Edit
                                            </a>

                                            <a href="hapus_guru.php?id=<?= urlencode($row['id']) ?>"
                                               class="btn btn-danger btn-sm fw-semibold"
                                               onclick="return confirm('Yakin ingin menghapus data guru ini?');">
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

<!-- DataTables -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Inisialisasi DataTables -->
<script>
$(document).ready(function () {
    $('#tabelGuru').DataTable({
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data guru",
            infoEmpty: "Tidak ada data guru",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Data guru tidak ditemukan",
            emptyTable: "Belum ada data guru",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        },
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [[2, 'asc']],
        columnDefs: [
            {
                targets: [7],
                orderable: false,
                searchable: false
            }
        ]
    });
});
</script>

</body>
</html>