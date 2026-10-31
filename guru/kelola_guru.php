<?php
// guru/kelola_guru.php
include '../includes/cek_session.php';
include '../config/koneksi.php';

// PROTEKSI ROLE: Hanya role 'admin' yang bisa membuka halaman Data Guru
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['pesan_error'] = "Akses ditolak! Anda tidak memiliki izin untuk mengelola Data Guru.";
    header("Location: ../dashboard.php");
    exit();
}

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
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4 admin-guru-list">

            <header class="admin-page-header mb-4">
                <h1>Kelola Data Guru</h1>
                <p>Kelola informasi, akun, dan status guru.</p>
            </header>

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
                <div class="card-header bg-white py-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <h5 class="card-title m-0 fw-bold">Data Guru</h5>
                    <a href="tambah_guru.php" class="btn btn-primary btn-sm fw-semibold text-nowrap">
                        <i class="fas fa-plus me-1"></i> Tambah Guru
                    </a>
                </div>
                <div class="card-body p-3">

                    <div class="table-responsive">
                        <!-- Tambahkan kelas table-sm agar lebih ringkas -->
                        <table id="tabelGuru" class="table table-striped table-bordered table-hover table-sm align-middle w-100" style="font-size: 0.9rem;">

                            <thead class="table-dark text-center">
                                <tr>
                                    <th width="5%">No</th>
                                    <th class="text-center">NIP</th>
                                    <th>Nama Guru</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th width="10%" class="action-column">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php 
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($hasil)) { ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="text-center"><?= htmlspecialchars($row['nip'] ?? '') ?></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($row['nama'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
                                        <td class="text-center">
                                            <?php if ($row['status_aktif'] == 1) { ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php } else { ?>
                                                <span class="badge bg-secondary">Nonaktif</span>
                                            <?php } ?>
                                        </td>
                                        <td class="text-center text-nowrap action-column">
                                            <!-- Tombol Edit Berbentuk Ikon -->
                                            <a href="edit_guru.php?id=<?= urlencode($row['id']) ?>"
                                               class="btn btn-warning btn-sm text-white px-2 py-1" title="Edit" aria-label="Edit guru">
                                                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                            </a>

                                            <!-- Tombol Hapus Berbentuk Ikon -->
                                            <a href="hapus_guru.php?id=<?= urlencode($row['id']) ?>"
                                               class="btn btn-danger btn-sm px-2 py-1" title="Hapus" aria-label="Hapus guru"
                                               onclick="return confirm('Yakin ingin menghapus data guru ini?');">
                                                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
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
        autoWidth: false,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [[2, 'asc']], // Urutkan berdasarkan Nama Guru (indeks kolom ke-2)
        columnDefs: [
            {
                targets: [0, -1], // Kolom No dan Aksi tidak bisa di-sort dan di-search
                orderable: false,
                searchable: false
            }
        ]
    });
});
</script>

</body>
</html>