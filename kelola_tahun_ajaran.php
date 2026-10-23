<?php
// kelola_tahun_ajaran.php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// HANYA ROLE ADMIN YANG BISA AKSES
batasi_akses(['admin']);

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

            <!-- TOMBOL KEMBALI KE DASHBOARD & TAMBAH TAHUN AJARAN -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="dashboard.php" class="btn btn-secondary fw-semibold">
                    Kembali ke Dashboard
                </a>
                <a href="tambah_tahun_ajaran.php" class="btn btn-primary fw-semibold">
                    + Tambah Tahun Ajaran
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

            <!-- CARD TABEL BOOTSTRAP TAHUN AJARAN -->
            <div class="card shadow-sm border-0 rounded-3 mb-5">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title m-0 fw-bold">Data Tahun Ajaran</h5>
                </div>
                <div class="card-body p-3">

                    <div class="table-responsive">
                        <!-- TABEL DENGAN DATATABLES -->
                        <table id="tabelTahunAjaran" class="table table-striped table-hover align-middle m-0 w-100">

                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center py-3" style="width: 50px;">No.</th>
                                    <th class="py-3">Nama Tahun Ajaran</th>
                                    <th class="text-center py-3">Tanggal Mulai</th>
                                    <th class="text-center py-3">Tanggal Selesai</th>
                                    <th class="text-center py-3">Status Aktif</th>
                                    <th class="text-center py-3">Dibuat</th>
                                    <th class="text-center py-3">Diubah</th>
                                    <th class="text-center py-3" style="width: 140px;">Aksi</th>
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
                                                <span class="badge bg-success px-3 py-2">Aktif</span>
                                            <?php } else { ?>
                                                <span class="badge bg-danger px-3 py-2">Tidak Aktif</span>
                                            <?php } ?>
                                        </td>

                                        <td class="text-center small text-muted">
                                            <?= htmlspecialchars($row['created_at'] ?? '-') ?>
                                        </td>

                                        <td class="text-center small text-muted">
                                            <?= htmlspecialchars($row['updated_at'] ?? '-') ?>
                                        </td>

                                        <td class="text-center text-nowrap">
                                            <a href="edit_tahun_ajaran.php?id=<?= urlencode($row['id']) ?>" 
                                               class="btn btn-warning btn-sm fw-semibold text-white me-1">
                                                Edit
                                            </a>

                                            <a href="hapus_tahun_ajaran.php?id=<?= urlencode($row['id']) ?>" 
                                               class="btn btn-danger btn-sm fw-semibold"
                                               onclick="return confirm('Yakin ingin menghapus tahun ajaran ini?');">
                                                Hapus
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