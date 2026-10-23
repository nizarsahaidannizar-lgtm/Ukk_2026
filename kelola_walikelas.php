<?php
// kelola_walikelas.php
include 'includes/cek_session.php'; // Pakai '../includes/cek_session.php' jika di dalam folder guru/
include 'config/koneksi.php';

// HANYA ROLE ADMIN YANG BISA AKSES
batasi_akses(['admin']);

// QUERY DATABASE (Dengan JOIN ke tabel kelas & guru)
$sql = "SELECT
            w.*,
            k.nama AS nama_kelas,
            g.nama AS nama_guru
        FROM t_wali_kelas w
        LEFT JOIN t_kelas k ON w.kelas_id = k.id
        LEFT JOIN t_guru g ON w.guru_id = g.id
        ORDER BY w.id DESC";

$hasil = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Wali Kelas - SIP</title>

    <!-- Bootstrap 5 CSS & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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

            <!-- TOMBOL KEMBALI & TAMBAH WALI KELAS -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="dashboard.php" class="btn btn-secondary fw-semibold">
                    Kembali ke Dashboard
                </a>
                <a href="tambah_walikelas.php" class="btn btn-primary fw-semibold">
                    + Tambah Wali Kelas
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

            <!-- CARD TABEL WALI KELAS -->
            <div class="card shadow-sm border-0 rounded-3 mb-5">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title m-0 fw-bold">Data Wali Kelas</h5>
                </div>
                <div class="card-body p-4">

                    <div class="table-responsive">
                        <table id="tabelWaliKelas" class="table table-striped table-bordered table-hover align-middle w-100">

                            <thead class="table-dark">
                                <tr>
                                    <th>No.</th>
                                    <th>Kelas</th>
                                    <th>Guru</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($hasil)) { ?>

                                <tr>
                                    <td><?= $no++ ?></td>

                                    <td><?= htmlspecialchars($row['nama_kelas'] ?? '-') ?></td>

                                    <td><?= htmlspecialchars($row['nama_guru'] ?? '-') ?></td>

                                    <td><?= htmlspecialchars($row['tanggal_mulai'] ?? '-') ?></td>

                                    <td><?= htmlspecialchars($row['tanggal_selesai'] ?? '-') ?></td>

                                    <td>
                                        <?php if ($row['status_aktif'] == 1) { ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php } else { ?>
                                            <span class="badge bg-danger">Tidak Aktif</span>
                                        <?php } ?>
                                    </td>

                                    <td class="text-center text-nowrap">
                                        <a href="edit_walikelas.php?id=<?= urlencode($row['id']) ?>" class="btn btn-warning btn-sm me-1 fw-semibold text-white">
                                            Edit
                                        </a>

                                        <a href="hapus_walikelas.php?id=<?= urlencode($row['id']) ?>" 
                                           class="btn btn-danger btn-sm fw-semibold"
                                           onclick="return confirm('Yakin ingin menghapus data wali kelas ini?');">
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {
    $('#tabelWaliKelas').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [[1, 'asc']], // Urutkan berdasarkan Nama Kelas
        scrollX: true,
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data wali kelas",
            infoEmpty: "Tidak ada data wali kelas",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Data wali kelas tidak ditemukan",
            emptyTable: "Belum ada data wali kelas",
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