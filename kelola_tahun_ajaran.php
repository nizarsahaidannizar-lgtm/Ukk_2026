<?php
// kelola_tahun_ajaran.php
include 'includes/cek_session.php';
include 'config/koneksi.php';
require_once 'includes/tahun_ajaran.php';

// HANYA ROLE ADMIN YANG BISA AKSES
batasi_akses(['admin']);

if (!normalisasi_tahun_ajaran_aktif($koneksi)) {
    $_SESSION['pesan_error'] = 'Gagal memeriksa status tahun ajaran aktif.';
}

// QUERY DATABASE
$sql = "SELECT * FROM t_tahun_ajaran ORDER BY id DESC";
$hasil = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Tahun Ajaran - SIP</title>

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

        <?php include 'layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">

            <header class="admin-page-header mb-4">
                <h1>Kelola Tahun Ajaran</h1>
                <p>Atur periode tahun ajaran dan status berlakunya.</p>
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

            <!-- CARD TABEL BOOTSTRAP TAHUN AJARAN -->
            <div class="card shadow-sm border-0 rounded-3 mb-5">
                <div class="card-header bg-white py-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <h5 class="card-title m-0 fw-bold">Data Tahun Ajaran</h5>
                    <a href="tambah_tahun_ajaran.php" class="btn btn-primary btn-sm fw-semibold text-nowrap">
                        <i class="fas fa-plus me-1"></i> Tambah Tahun Ajaran
                    </a>
                </div>
                <div class="card-body p-3">

                    <div class="table-responsive">
                        <!-- Tambahkan kelas table-sm agar lebih ringkas -->
                        <table id="tabelTahunAjaran" class="table table-striped table-bordered table-hover table-sm align-middle m-0 w-100 admin-year-table">

                            <thead class="table-dark text-center">
                                <tr>
                                    <th style="width: 7%;">No.</th>
                                    <th style="width: 22%;">Nama Tahun Ajaran</th>
                                    <th style="width: 18%;">Tanggal Mulai</th>
                                    <th style="width: 18%;">Tanggal Selesai</th>
                                    <th style="width: 18%;">Status Aktif</th>
                                    <th class="action-column" style="width: 17%;">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $no = 1;
                                if (mysqli_num_rows($hasil) > 0) {
                                    while ($row = mysqli_fetch_assoc($hasil)) { ?>

                                    <tr>
                                        <td class="text-center fw-semibold"><?= $no++ ?></td>

                                        <td class="fw-semibold text-dark">
                                            <?= htmlspecialchars($row['nama']) ?>
                                        </td>

                                        <td class="text-center">
                                            <?= htmlspecialchars($row['tanggal_mulai'] ?? '-') ?>
                                        </td>

                                        <td class="text-center">
                                            <?= htmlspecialchars($row['tanggal_selesai'] ?? '-') ?>
                                        </td>

                                        <td class="text-center">
                                            <?php if ($row['status_aktif'] == 1) { ?>
                                                <span class="badge bg-success px-2 py-1">Aktif</span>
                                            <?php } else { ?>
                                                <span class="badge bg-danger px-2 py-1">Tidak Aktif</span>
                                            <?php } ?>
                                        </td>

                                        <td class="text-center text-nowrap action-column">
                                            <!-- Tombol Edit Berbentuk Ikon -->
                                            <a href="edit_tahun_ajaran.php?id=<?= urlencode($row['id']) ?>" 
                                               class="btn btn-warning btn-sm text-white px-2 py-1" title="Edit" aria-label="Edit tahun ajaran">
                                                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                            </a>

                                            <!-- Tombol Hapus Berbentuk Ikon -->
                                            <a href="hapus_tahun_ajaran.php?id=<?= urlencode($row['id']) ?>" 
                                               class="btn btn-danger btn-sm px-2 py-1" title="Hapus" aria-label="Hapus tahun ajaran"
                                               onclick="return confirm('Yakin ingin menghapus tahun ajaran ini?');">
                                                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                            </a>
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

<!-- jQuery (dibutuhkan oleh DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS & Bootstrap 5 Integration -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<!-- Inisialisasi DataTables dengan Bahasa Indonesia -->
<script>
    $(document).ready(function() {
        $('#tabelTahunAjaran').DataTable({
            "autoWidth": false,
            "columnDefs": [
                { "targets": [0, -1], "orderable": false, "searchable": false }
            ],
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