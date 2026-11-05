<?php
// kelola_siswa.php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// PROTEKSI HAK AKSES: Hanya role 'admin' yang diperbolehkan mengelola data siswa
batasi_akses(['admin']);

$sql = "SELECT * FROM t_siswa ORDER BY nama ASC";
$hasil = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Siswa - SIP</title>

    <!-- Bootstrap 5 CSS & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables Bootstrap 5 -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="layout/style.css">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4 admin-siswa-list">

            <header class="admin-page-header mb-4">
                <h1>Kelola Data Siswa</h1>
                <p>Kelola informasi, status, dan data siswa.</p>
            </header>

            <!-- ALERT NOTIFIKASI -->
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

            <!-- CARD TABEL SISWA -->
            <div class="card shadow-sm border-0 rounded-3 mb-5">
                <div class="card-header bg-white py-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <h5 class="card-title m-0 fw-bold">Data Siswa</h5>
                    <a href="tambah_siswa.php" class="btn btn-primary btn-sm fw-semibold text-nowrap">
                        <i class="fas fa-plus me-1"></i> Tambah Siswa
                    </a>
                </div>
                <div class="card-body p-3">

                    <div class="table-responsive">
                        <table id="tabelSiswa" class="table table-striped table-bordered table-hover table-sm align-middle w-100" style="font-size: 0.9rem;">

                            <thead class="table-dark text-center">
                                <tr>
                                    <th class="text-nowrap">No.</th>
                                    <th class="text-center">NIS</th>
                                    <th class="text-center">NISN</th> <!-- KOLOM NISN DITAMBAHKAN -->
                                    <th>Nama Siswa</th>
                                    <th>L/P</th>
                                    <th>Tgl Lahir</th>
                                    <th>Status</th>
                                    <th class="action-column">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $no = 1; 
                                while ($row = mysqli_fetch_assoc($hasil)) { ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['nis']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($row['nisn']) ?></td> <!-- DATA NISN DITAMPILKAN -->
                                    <td class="fw-semibold"><?= htmlspecialchars($row['nama']) ?></td>
                                    <td class="text-center">
                                        <?php 
                                        if ($row['jenis_kelamin'] == 'L') {
                                            echo 'L';
                                        } elseif ($row['jenis_kelamin'] == 'P') {
                                            echo 'P';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-center text-nowrap"><?= htmlspecialchars(date('d-m-Y', strtotime($row['tanggal_lahir'] ?? ''))) ?></td>
                                    <td class="text-center">
                                        <?php if ($row['status_aktif'] == 1) { ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php } else { ?>
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        <?php } ?>
                                    </td>
                                    <td class="text-center text-nowrap action-column">
                                        <!-- Tombol Edit -->
                                        <a href="edit_siswa.php?id=<?= urlencode($row['id']) ?>"
                                           class="btn btn-warning btn-sm text-white px-2 py-1" title="Edit" aria-label="Edit siswa">
                                            <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                        </a>

                                        <!-- Tombol Hapus -->
                                        <a href="hapus_siswa.php?id=<?= urlencode($row['id']) ?>"
                                           class="btn btn-danger btn-sm px-2 py-1" title="Hapus" aria-label="Hapus siswa"
                                           onclick="return confirm('Yakin ingin menghapus data siswa ini?');">
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

<script>
$(document).ready(function () {
    const tabelSiswa = $('#tabelSiswa').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [[3, 'asc']], // Urutkan berdasarkan Nama Siswa (indeks kolom ke-3 karena ada penambahan NISN)
        autoWidth: false,
        columnDefs: [
            { targets: 0, width: '6%' },
            { targets: 1, width: '12%' },
            { targets: 2, width: '13%' }, // Lebar kolom NISN
            { targets: 3, width: '22%' }, // Lebar kolom Nama Siswa
            { targets: 4, width: '7%' },
            { targets: 5, width: '14%' },
            { targets: 6, width: '13%' },
            { targets: 7, width: '13%' },
            { targets: [0, -1], orderable: false, searchable: false }
        ],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada data siswa",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        }
    });

    const inputCari = $('#tabelSiswa_wrapper input[type="search"]');
    inputCari.val('');
    tabelSiswa.search('').columns().search('').draw();
});
</script>

</body>
</html>