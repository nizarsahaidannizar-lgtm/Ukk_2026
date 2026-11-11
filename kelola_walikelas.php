
<?php
// kelola_walikelas.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// Hanya admin yang bisa mengakses
batasi_akses(['admin']);

// Ambil notifikasi sebelum ditampilkan
$pesan_sukses = $_SESSION['pesan_sukses'] ?? '';
$pesan_error  = $_SESSION['pesan_error'] ?? '';

// Hapus notifikasi dari session setelah diambil
unset($_SESSION['pesan_sukses']);
unset($_SESSION['pesan_error']);

// Query data wali kelas
$sql = "SELECT
            w.*,
            k.nama AS nama_kelas,
            g.nama AS nama_guru
        FROM t_wali_kelas w
        LEFT JOIN t_kelas k ON w.kelas_id = k.id
        LEFT JOIN t_guru g ON w.guru_id = g.id
        ORDER BY w.id DESC";

$hasil = mysqli_query($koneksi, $sql);

if (!$hasil) {
    die("Query gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Wali Kelas - SIP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="layout/style.css">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">

            <header class="admin-page-header">
                <h1>Kelola Data Wali Kelas</h1>
                <p>Atur penugasan wali kelas berdasarkan kelas dan tahun ajaran.</p>
            </header>

            <!-- NOTIFIKASI SUKSES -->
            <?php if (!empty($pesan_sukses)) { ?>
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>
                    <?= htmlspecialchars($pesan_sukses); ?>

                    <button type="button" class="btn-close"
                        data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <!-- NOTIFIKASI ERROR -->
            <?php if (!empty($pesan_error)) { ?>
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>
                    <?= htmlspecialchars($pesan_error); ?>

                    <button type="button" class="btn-close"
                        data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <!-- TABEL WALI KELAS -->
            <div class="card shadow-sm border-0 rounded-3 mb-5">

                <div class="card-header bg-white py-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">

                    <h5 class="card-title m-0 fw-bold">
                        Data Wali Kelas
                    </h5>

                    <a href="tambah_walikelas.php"
                       class="btn btn-primary btn-sm fw-semibold text-nowrap">
                        <i class="fa-solid fa-plus me-1"></i>
                        Tambah Wali Kelas
                    </a>

                </div>

                <div class="card-body p-4">

                    <div class="table-responsive">

                        <table id="tabelWaliKelas"
                            class="table table-striped table-bordered table-hover align-middle w-100 admin-walikelas-table">

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

                                while ($row = mysqli_fetch_assoc($hasil)) {
                                ?>

                                <tr>

                                    <td><?= $no++; ?></td>

                                    <td>
                                        <?= htmlspecialchars($row['nama_kelas'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['nama_guru'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['tanggal_mulai'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($row['tanggal_selesai'] ?? '-'); ?>
                                    </td>

                                    <td>
                                        <?php if ($row['status_aktif'] == 1) { ?>
                                            <span class="badge bg-success">Aktif</span>
                                        <?php } else { ?>
                                            <span class="badge bg-danger">Tidak Aktif</span>
                                        <?php } ?>
                                    </td>

                                    <td class="text-center text-nowrap">

                                        <a href="edit_walikelas.php?id=<?= urlencode($row['id']); ?>"
                                            class="btn btn-warning btn-sm text-white"
                                            title="Edit">

                                            <i class="fa-solid fa-pen-to-square"></i>

                                        </a>

                                        <a href="hapus_walikelas.php?id=<?= urlencode($row['id']); ?>"
                                            class="btn btn-danger btn-sm"
                                            title="Hapus"
                                            onclick="return confirm('Yakin ingin menghapus data wali kelas ini?');">

                                            <i class="fa-solid fa-trash-can"></i>

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

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {

    $('#tabelWaliKelas').DataTable({

        pageLength: 10,

        lengthMenu: [5, 10, 25, 50, 100],

        order: [[1, 'asc']],

        autoWidth: false,

        columnDefs: [
            { targets: 0, width: '6%', orderable: false },
            { targets: 1, width: '15%' },
            { targets: 2, width: '20%' },
            { targets: 3, width: '16%' },
            { targets: 4, width: '16%' },
            { targets: 5, width: '12%' },
            { targets: 6, width: '15%', orderable: false }
        ],

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
